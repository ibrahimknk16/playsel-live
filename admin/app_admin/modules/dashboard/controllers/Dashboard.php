<?php defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public $data = array();
    private $monthShortLabels = [
        '01' => 'Oca', '02' => 'Şub', '03' => 'Mar', '04' => 'Nis',
        '05' => 'May', '06' => 'Haz', '07' => 'Tem', '08' => 'Ağu',
        '09' => 'Eyl', '10' => 'Eki', '11' => 'Kas', '12' => 'Ara',
    ];

    public function __construct()
    {
        userCheck();
        parent::__construct();
    }

    public function index()
    {
        $data = $this->data ?? [];
        $profilingEnabled = $this->input->get('profile', true) === '1';
        $profile = [];
        $totalPoint = $profilingEnabled ? $this->createProfilerPoint() : null;

        // 1. Gerekli tüm tarih aralıklarını ve bucket'ları hazırla
        $ranges = $this->buildKpiRanges();
        $monthlyBuckets = $this->buildTrailingMonthBuckets(12);
        $chartDaysBack = 30;
        $dailyBuckets = $this->buildTrailingDayBuckets($chartDaysBack);

        // En eski veriye ne kadar geriden ihtiyacımız var? (Aylık bucket'ların başı veya Yıllık başı)
        $minStart = min($monthlyBuckets[0]['start'], $ranges['yearly']['start'], $dailyBuckets[0]['start']);
        $maxEnd = $ranges['daily']['end']; // Yarın

        // 2. VERİTABANINA SADECE 1 KERE GİDİYORUZ (Master Query)
        $point = $profilingEnabled ? $this->createProfilerPoint() : null;
        $masterData = $this->fetchMasterAggregates($minStart, $maxEnd);
        if ($profilingEnabled) {
            $profile['db_master_query'] = $this->closeProfilerPoint($point);
        }

        // 3. Çekilen ham veriyi PHP tarafında (RAM'de) dağıt
        $point = $profilingEnabled ? $this->createProfilerPoint() : null;
        $distributedData = $this->distributeMasterData($masterData, $ranges, $monthlyBuckets, $dailyBuckets);
        if ($profilingEnabled) {
            $profile['php_data_distribution'] = $this->closeProfilerPoint($point);
        }

        // 4. Arayüz için verileri derle
        $kpi = $distributedData['kpi'];
        $data['kpi'] = $kpi;
        $data['dashboardCards'] = $this->buildDashboardCards($kpi, $ranges);
        $data['dashboardOverview'] = $this->buildDashboardOverview($kpi);
        
        $data['dailyStatusChart'] = $this->buildDailyStatusChartData($dailyBuckets, $distributedData['chart_daily']);
        $data['monthlyFinanceChart'] = $this->buildMonthlyFinanceChartData($monthlyBuckets, $distributedData['monthly_finance']);
        
        $data['dailyStatusChartJson'] = $this->safeJsonEncode($data['dailyStatusChart']);
        $data['monthlyFinanceChartJson'] = $this->safeJsonEncode($data['monthlyFinanceChart']);

        if ($profilingEnabled) {
            $profile['total'] = $this->closeProfilerPoint($totalPoint);
            $this->emitDashboardProfile($profile);
        }

        $this->template->content->view('index', $data);
        $this->template->publish();
    }

    public function userKpi()
    {
        $this->load->model('Sayfalar_model');
        $tomorrow = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $periods = [
            'daily' => ['label' => 'Günlük', 'subtitle' => 'Bugün', 'start' => date('Y-m-d 00:00:00'), 'end' => $tomorrow],
            'weekly' => ['label' => 'Haftalık', 'subtitle' => 'Son 7 gün', 'start' => date('Y-m-d 00:00:00', strtotime('-6 days')), 'end' => $tomorrow],
            'monthly' => ['label' => 'Aylık', 'subtitle' => 'Bu ay', 'start' => date('Y-m-01 00:00:00'), 'end' => $tomorrow],
        ];
        $data = $this->data;
        $data['periods'] = $periods;
        $data['activeCustomerWindowDays'] = 30;
        $data['userKpis'] = $this->Sayfalar_model->getUserKpis($periods, date('Y-m-d 00:00:00', strtotime('-29 days')), $tomorrow);
        $this->template->content->view('user-kpi', $data);
        $this->template->publish();
    }

    // ===================== CORE DATA ENGINE =====================

    /**
     * Tüm aggregation işlemlerini tek bir sorguda halleder.
     * Index: idx_dashboard_fin_cov (order_created_at, order_status, order_cost, order_sale_price)
     */
    private function fetchMasterAggregates($start, $end)
    {
        return $this->db->select("DATE(order_created_at) AS d, order_status, COUNT(order_id) AS c, SUM(order_cost) AS cost, SUM(order_sale_price) AS sale", false)
            ->from('orders')
            ->where('order_created_at >=', $start)
            ->where('order_created_at <', $end)
            // where_in KULLANMIYORUZ! Çünkü chart için tüm statüleri bilmemiz gerekiyor (pending dahil). 
            // Sadece gruplayıp PHP'ye alıyoruz. Çok az satır dönecek (Gün * Statü Sayısı)
            ->group_by(['d', 'order_status'])
            ->get()
            ->result_array();
    }

    /**
     * Veritabanından gelen O(1) boyutundaki sonucu array manipülasyonu ile O(N) formatına dağıtır.
     */
    private function distributeMasterData(array $masterData, array $ranges, array $monthlyBuckets, array $dailyBuckets)
    {
        // Tarih sınırları (Hızlı string karşılaştırma için)
        $todayStr = date('Y-m-d', strtotime($ranges['daily']['start']));
        $weeklyStartStr = date('Y-m-d', strtotime($ranges['weekly']['start']));
        $monthlyStartStr = date('Y-m', strtotime($ranges['monthly']['start']));
        $yearlyStartStr = date('Y', strtotime($ranges['yearly']['start']));

        $kpi = [
            'daily' => ['success' => $this->emptySummaryBucket(), 'canceled' => $this->emptySummaryBucket()],
            'weekly' => ['success' => $this->emptySummaryBucket(), 'canceled' => $this->emptySummaryBucket()],
            'monthly' => ['success' => $this->emptySummaryBucket(), 'canceled' => $this->emptySummaryBucket()],
            'yearly' => ['success' => $this->emptySummaryBucket(), 'canceled' => $this->emptySummaryBucket()],
        ];

        $chartDaily = [];
        $monthlyFinance = [];

        foreach ($masterData as $row) {
            $date = (string)$row['d'];
            $monthStr = substr($date, 0, 7);
            $yearStr = substr($date, 0, 4);
            $status = (int)$row['order_status'];
            $count = (int)$row['c'];
            $cost = (float)$row['cost'];
            $sale = (float)$row['sale'];

            $bucketData = ['count' => $count, 'cost' => $cost, 'sale' => $sale, 'profit' => $sale - $cost];

            // 1. KPI Dağıtımı (Sadece 1 ve 2 statüleri)
            if ($status === 1 || $status === 2) {
                $target = $status === 1 ? 'success' : 'canceled';

                // Günlük
                if ($date === $todayStr) {
                    $kpi['daily'][$target] = $this->addSummaryBuckets($kpi['daily'][$target], $bucketData);
                }
                // Haftalık
                if ($date >= $weeklyStartStr) {
                    $kpi['weekly'][$target] = $this->addSummaryBuckets($kpi['weekly'][$target], $bucketData);
                }
                // Aylık
                if ($monthStr === $monthlyStartStr) {
                    $kpi['monthly'][$target] = $this->addSummaryBuckets($kpi['monthly'][$target], $bucketData);
                }
                // Yıllık
                if ($yearStr === $yearlyStartStr) {
                    $kpi['yearly'][$target] = $this->addSummaryBuckets($kpi['yearly'][$target], $bucketData);
                }

                // Monthly Finance Chart Dağıtımı (Sadece Success)
                if ($status === 1) {
                    if (!isset($monthlyFinance[$monthStr])) {
                        $monthlyFinance[$monthStr] = $this->emptySummaryBucket();
                    }
                    $monthlyFinance[$monthStr] = $this->addSummaryBuckets($monthlyFinance[$monthStr], $bucketData);
                }
            }

            // 2. Günlük Chart Dağıtımı (Tüm statüler)
            $group = $this->resolveChartStatusGroup($status);
            if (!isset($chartDaily[$date])) {
                $chartDaily[$date] = ['success' => 0, 'canceled' => 0, 'pending' => 0, 'total' => 0];
            }
            $chartDaily[$date][$group] += $count;
            $chartDaily[$date]['total'] += $count;
        }

        return [
            'kpi' => $kpi,
            'chart_daily' => $chartDaily,
            'monthly_finance' => $monthlyFinance
        ];
    }

    // ===================== UI & CHART BUILDERS =====================

    private function buildDailyStatusChartData(array $buckets, array $chartDailyData)
    {
        $labels = []; $success = []; $canceled = []; $pending = []; $total = [];

        foreach ($buckets as $bucket) {
            $key = $bucket['key'];
            $data = $chartDailyData[$key] ?? ['success' => 0, 'canceled' => 0, 'pending' => 0, 'total' => 0];

            $labels[] = $bucket['label'];
            $success[] = $data['success'];
            $canceled[] = $data['canceled'];
            $pending[] = $data['pending'];
            $total[] = $data['total'];
        }

        return [
            'labels' => $labels,
            'series' => [
                'success' => $success,
                'canceled' => $canceled,
                'pending' => $pending,
                'total' => $total,
            ],
            'summary' => [
                $this->buildChartMetric('Başarılı', array_sum($success), number_format(array_sum($success), 0, ',', '.'), 'success'),
                $this->buildChartMetric('İptal / Hata', array_sum($canceled), number_format(array_sum($canceled), 0, ',', '.'), 'danger'),
                $this->buildChartMetric('Bekleyen', array_sum($pending), number_format(array_sum($pending), 0, ',', '.'), 'muted'),
                $this->buildChartMetric('Toplam', array_sum($total), number_format(array_sum($total), 0, ',', '.'), 'info'),
            ],
        ];
    }

    private function buildMonthlyFinanceChartData(array $buckets, array $monthlyFinanceData)
    {
        $labels = []; $sales = []; $profit = []; $orders = [];
        $totalSales = 0.0; $totalProfit = 0.0; $totalOrders = 0;
        $peakMonthText = '-'; $peakProfit = null;

        foreach ($buckets as $bucket) {
            $key = $bucket['key'];
            $data = $monthlyFinanceData[$key] ?? $this->emptySummaryBucket();

            $monthlySales = $data['sale'];
            $monthlyProfit = $data['profit'];
            $monthlyOrders = $data['count'];

            $labels[] = $bucket['label'];
            $sales[] = round($monthlySales, 2);
            $profit[] = round($monthlyProfit, 2);
            $orders[] = $monthlyOrders;

            $totalSales += $monthlySales;
            $totalProfit += $monthlyProfit;
            $totalOrders += $monthlyOrders;

            if ($peakProfit === null || $monthlyProfit > $peakProfit) {
                $peakProfit = $monthlyProfit;
                $peakMonthText = $bucket['label'];
            }
        }

        $averageOrders = count($orders) > 0 ? ($totalOrders / count($orders)) : 0;

        return [
            'labels' => $labels,
            'series' => [
                'sales' => $sales,
                'profit' => $profit,
                'orders' => $orders,
            ],
            'summary' => [
                $this->buildChartMetric('12 Aylık Başarılı Satış', $totalSales, $this->formatMoney($totalSales), 'success'),
                $this->buildChartMetric('12 Aylık Başarılı Kar', $totalProfit, $this->formatMoney($totalProfit), $totalProfit >= 0 ? 'warning' : 'danger'),
                $this->buildChartMetric('Aylık Ortalama Başarılı Sipariş', $averageOrders, number_format((int)round($averageOrders), 0, ',', '.'), 'info'),
                $this->buildChartMetric('En Güçlü Ay', $peakMonthText, $peakMonthText, 'muted'),
            ],
        ];
    }

    // ===================== HELPERS =====================

    private function addSummaryBuckets(array $base, array $append)
    {
        return [
            'count' => (int)($base['count'] ?? 0) + (int)($append['count'] ?? 0),
            'cost' => (float)($base['cost'] ?? 0) + (float)($append['cost'] ?? 0),
            'sale' => (float)($base['sale'] ?? 0) + (float)($append['sale'] ?? 0),
            'profit' => (float)($base['profit'] ?? 0) + (float)($append['profit'] ?? 0),
        ];
    }

    private function emptySummaryBucket()
    {
        return ['count' => 0, 'cost' => 0.0, 'sale' => 0.0, 'profit' => 0.0];
    }

    private function resolveChartStatusGroup($status)
    {
        if ($status === 1) return 'success';
        if (in_array($status, [2, 5, 12], true)) return 'canceled';
        return 'pending';
    }

    private function buildDashboardCards(array $kpi, array $ranges)
    {
        $tones = ['daily' => 'emerald', 'weekly' => 'indigo', 'monthly' => 'cyan', 'yearly' => 'amber'];
        $cards = [];

        foreach ($ranges as $period => $meta) {
            $success = $kpi[$period]['success'] ?? $this->emptySummaryBucket();
            $canceled = $kpi[$period]['canceled'] ?? $this->emptySummaryBucket();
            $totalOrders = $success['count'] + $canceled['count'];
            $cancelRate = $this->calculateRate($canceled['count'], $totalOrders);

            $cards[$period] = [
                'period' => $period,
                'title' => $meta['label'],
                'subtitle' => $meta['subtitle'],
                'tone' => $tones[$period] ?? 'slate',
                'total_orders' => $totalOrders,
                'total_orders_text' => number_format($totalOrders, 0, ',', '.'),
                'success_count' => $success['count'],
                'success_count_text' => number_format($success['count'], 0, ',', '.'),
                'cancel_count' => $canceled['count'],
                'cancel_count_text' => number_format($canceled['count'], 0, ',', '.'),
                'cancel_rate' => $cancelRate,
                'cancel_rate_text' => $this->formatPercent($cancelRate),
                'success' => $this->decorateSummary($success),
                'canceled' => $this->decorateSummary($canceled),
            ];
        }
        return $cards;
    }

    private function buildDashboardOverview(array $kpi)
    {
        $dailyTot = $kpi['daily']['success']['count'] + $kpi['daily']['canceled']['count'];
        $monthlyTot = $kpi['monthly']['success']['count'] + $kpi['monthly']['canceled']['count'];

        $todaySuccessRate = $this->calculateRate($kpi['daily']['success']['count'], $dailyTot);
        $monthCancelRate = $this->calculateRate($kpi['monthly']['canceled']['count'], $monthlyTot);

        return [
            'today_orders' => $dailyTot,
            'today_orders_text' => number_format($dailyTot, 0, ',', '.'),
            'today_success_rate' => $todaySuccessRate,
            'today_success_rate_text' => $this->formatPercent($todaySuccessRate),
            'month_cancel_rate' => $monthCancelRate,
            'month_cancel_rate_text' => $this->formatPercent($monthCancelRate),
            'monthly_net_profit' => $kpi['monthly']['success']['profit'],
            'monthly_net_profit_text' => $this->formatMoney($kpi['monthly']['success']['profit']),
            'yearly_net_profit' => $kpi['yearly']['success']['profit'],
            'yearly_net_profit_text' => $this->formatMoney($kpi['yearly']['success']['profit']),
        ];
    }

    private function buildKpiRanges()
    {
        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $monthStart = date('Y-m-01 00:00:00');

        return [
            'daily' => ['label' => 'Günlük', 'subtitle' => 'Bugün', 'start' => $todayStart, 'end' => $tomorrowStart],
            'weekly' => ['label' => 'Haftalık', 'subtitle' => 'Son 7 Gün', 'start' => date('Y-m-d 00:00:00', strtotime('-6 days')), 'end' => $tomorrowStart],
            'monthly' => ['label' => 'Aylık', 'subtitle' => 'Bu Ay', 'start' => $monthStart, 'end' => $tomorrowStart],
            'yearly' => ['label' => 'Yıllık', 'subtitle' => date('Y'), 'start' => date('Y-01-01 00:00:00'), 'end' => date('Y-01-01 00:00:00', strtotime('+1 year'))],
        ];
    }

    private function buildTrailingDayBuckets($daysBack = 30)
    {
        $cursor = new DateTime(date('Y-m-d 00:00:00', strtotime('-' . ($daysBack - 1) . ' days')));
        $buckets = [];
        for ($i = 0; $i < $daysBack; $i++) {
            $start = clone $cursor;
            $end = (clone $cursor)->modify('+1 day');
            $buckets[] = [
                'key' => $start->format('Y-m-d'),
                'label' => $start->format('d') . ' ' . ($this->monthShortLabels[$start->format('m')] ?? $start->format('m')),
                'start' => $start->format('Y-m-d 00:00:00'),
                'end' => $end->format('Y-m-d 00:00:00'),
            ];
            $cursor->modify('+1 day');
        }
        return $buckets;
    }

    private function buildTrailingMonthBuckets($monthsBack = 12)
    {
        $cursor = (new DateTime('first day of this month 00:00:00'))->modify('-' . ($monthsBack - 1) . ' months');
        $buckets = [];
        for ($i = 0; $i < $monthsBack; $i++) {
            $start = clone $cursor;
            $end = (clone $cursor)->modify('+1 month');
            $buckets[] = [
                'key' => $start->format('Y-m'),
                'label' => ($this->monthShortLabels[$start->format('m')] ?? $start->format('m')) . ' ' . $start->format('Y'),
                'start' => $start->format('Y-m-01 00:00:00'),
                'end' => $end->format('Y-m-01 00:00:00'),
            ];
            $cursor->modify('+1 month');
        }
        return $buckets;
    }

    private function decorateSummary(array $summary)
    {
        $profit = $summary['profit'];
        return [
            'count' => $summary['count'],
            'count_text' => number_format($summary['count'], 0, ',', '.'),
            'cost' => $summary['cost'],
            'sale' => $summary['sale'],
            'profit' => $profit,
            'cost_text' => $this->formatMoney($summary['cost']),
            'sale_text' => $this->formatMoney($summary['sale']),
            'profit_text' => $this->formatMoney($profit),
            'profit_tone' => $profit >= 0 ? 'positive' : 'negative',
            'profit_class' => $profit >= 0 ? 'is-profit-positive' : 'is-profit-negative',
        ];
    }

    private function calculateRate($portion, $total) { return $total > 0 ? ($portion / $total) * 100 : 0.0; }
    private function formatMoney($amount) { return number_format((float)$amount, 2, ',', '.') . ' TL'; }
    private function formatPercent($value) { return number_format((float)$value, 2, ',', '.') . '%'; }
    private function safeJsonEncode($value) { return ($json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) === false ? '[]' : $json; }
    private function buildChartMetric($label, $value, $valueText, $tone = 'muted') { return ['label' => $label, 'value' => $value, 'value_text' => $valueText, 'tone' => $tone]; }

    // ===================== PROFILER =====================
    private function createProfilerPoint() { return ['time' => microtime(true), 'db_count' => $this->getDbQueryCount(), 'db_time' => $this->getDbQueryTimeTotal()]; }
    private function closeProfilerPoint(array $point) {
        $elapsedMs = (microtime(true) - (float)$point['time']) * 1000;
        $dbCountDelta = max(0, $this->getDbQueryCount() - (int)$point['db_count']);
        $dbTimeDeltaMs = max(0, ($this->getDbQueryTimeTotal() - (float)$point['db_time']) * 1000);
        return ['elapsed_ms' => round($elapsedMs, 3), 'db_query_count' => $dbCountDelta, 'db_query_ms' => round($dbTimeDeltaMs, 3), 'app_ms' => round(max(0, $elapsedMs - $dbTimeDeltaMs), 3)];
    }
    private function getDbQueryCount() { return (isset($this->db->queries) && is_array($this->db->queries)) ? count($this->db->queries) : 0; }
    private function getDbQueryTimeTotal() { return (isset($this->db->query_times) && is_array($this->db->query_times)) ? array_sum($this->db->query_times) : 0.0; }
    private function emitDashboardProfile(array $profile) {
        if (($json = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !== false) log_message('error', 'dashboard_profile=' . $json);
        if (!headers_sent()) {
            header('X-Dashboard-Profile-Total-Ms: ' . ($profile['total']['elapsed_ms'] ?? '0'));
            header('X-Dashboard-Profile-Db-Ms: ' . ($profile['total']['db_query_ms'] ?? '0'));
        }
    }

    // ===================== EXTRAS =====================
    public function userPrice($productID, $userLocale, $userTier)
    {
        $price = $this->db->select('*')->from('product_prices')->where('product_price_product_id', $productID)->where('product_price_country', $userLocale)->get()->row();
        if (!$price) return null;
        
        $userTier = (empty($userTier) || $userTier < 1 || $userTier > 6) ? 1 : $userTier;
        
        $priceFields = [
            1 => ['price' => 'product_price_standart_price', 'multiplier' => 'product_price_standart_price_multiplier'],
            2 => ['price' => 'product_price_silver_user', 'multiplier' => 'product_price_silver_user_multiplier'],
            3 => ['price' => 'product_price_gold_user', 'multiplier' => 'product_price_gold_user_multiplier'],
            4 => ['price' => 'product_price_vip_user', 'multiplier' => 'product_price_vip_user_multiplier'],
            5 => ['price' => 'product_price_playsel', 'multiplier' => 'product_price_playsel_multiplier'],
            6 => ['price' => 'product_price_payment', 'multiplier' => 'product_price_payment_multiplier'],
        ];

        return $price->product_price_is_multiplier == 1 
            ? clone $price->product_price_cost * $price->{$priceFields[$userTier]['multiplier']} 
            : $price->{$priceFields[$userTier]['price']};
    }

    public function currencies()
    {
        $query = $this->db->select('currencies_json')->from('currency')->limit(1)->order_by('currency', 'DESC')->get()->row();
        if (!$query || empty($query->currencies_json)) {
            echo '<div class="menu-item px-3"><div class="menu-content text-danger">Veri alınamadı.</div></div>';
            return;
        }

        $currencies = json_decode($query->currencies_json, true);
        $whitelist = ['usd', 'eur', 'idr', 'aed', 'rub', 'hkd', 'jpy', 'gbp', 'chf', 'cad', 'aud'];
        $html = '';

        foreach ($whitelist as $code) {
            if (!empty($currencies[$code])) {
                $formatted_val = number_format(1 / $currencies[$code], 10, '.', '');
                $html .= '<div class="menu-item px-3 my-0"><a href="javascript:void(0);" class="menu-link px-3 py-2 d-flex justify-content-between cursor-default"><span class="menu-title fw-bold text-gray-800">' . strtoupper($code) . '</span><span class="text-muted" title="Kopyala" onClick="copy(' . $formatted_val . ')">' . $formatted_val . ' ₺</span></a></div>';
            }
        }
        echo $html;
    }

    public function notificationCounts()
    {
        $count = function ($table, $where = [], $whereIn = []) {
            if (!$this->db->table_exists($table)) {
                return 0;
            }

            $query = $this->db->from($table);
            foreach ($where as $column => $value) {
                if (strpos($column, ' IS NULL') !== false) {
                    $query->where($column, null, false);
                } else {
                    $query->where($column, $value);
                }
            }
            foreach ($whereIn as $column => $values) {
                if (!empty($values)) {
                    $query->where_in($column, $values);
                }
            }

            return (int)$query->count_all_results();
        };

        // Keep these groups aligned with the status filters used by the linked admin screens.
        $activeOrderStatuses = [0, 3, 4, 5, 9, 14, 15];
        $paymentWaitingOrderStatuses = [11, 12];

        $counts = [
            'tickets' => $count('tickets', ['tickets_status' => 0]),
            'active_orders' => $count('orders', [], ['order_status' => $activeOrderStatuses]),
            'waiting_orders' => $count('orders', [], ['order_status' => $paymentWaitingOrderStatuses]),
            'payment_requests' => $count('user_balance', [
                'user_balance_status' => 0,
                'user_balance_order_id IS NULL' => null,
            ]),
            'fraud' => $count('orders', ['order_status' => 13]),
            'reviews' => $count('reviews', ['review_status' => 0]),
            'nickname_requests' => $count('nickname_change_requests', ['nickname_request_status' => 2]),
            'streamer_requests' => $count('users', ['user_streamer_status' => 2]),
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->set_output(json_encode([
                'success' => true,
                'counts' => $counts,
                'updated_at' => date('c'),
            ], JSON_UNESCAPED_UNICODE));
    }
}