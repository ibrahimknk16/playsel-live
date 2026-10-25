<?php
$dashboardOverview = $dashboardOverview ?? [];
$dashboardCards = $dashboardCards ?? [];
$dailyStatusChart = $dailyStatusChart ?? [];
$monthlyFinanceChart = $monthlyFinanceChart ?? [];
$dailyStatusChartJson = $dailyStatusChartJson ?? '{}';
$monthlyFinanceChartJson = $monthlyFinanceChartJson ?? '{}';
$cardOrder = ['daily', 'weekly', 'monthly', 'yearly'];
$escape = static function ($value, $default = '-') {
    return htmlspecialchars((string)($value ?? $default), ENT_QUOTES, 'UTF-8');
};
?>

<style>
    .dashboard-shell,
    .dashboard-shell * {
        font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
    }

    .dashboard-shell {
        color: #e5e7eb;
    }

    .dashboard-panel {
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 20px;
        padding: 1.35rem;
    }

    .dashboard-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .dashboard-kicker,
    .chart-kicker {
        margin: 0 0 0.4rem;
        color: #7dd3fc;
        font-size: 0.88rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        font-weight: 800;
    }

    .dashboard-title {
        margin: 0;
        color: #f8fafc;
        font-size: clamp(1.95rem, 3vw, 2.55rem);
        line-height: 1.1;
        font-weight: 800;
    }

    .dashboard-subtitle {
        margin: 0.85rem 0 0;
        color: #cbd5e1;
        font-size: 1rem;
        line-height: 1.7;
        max-width: 66ch;
    }

    .dashboard-meta {
        margin-top: 1.1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .dashboard-meta span {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.5rem 0.85rem;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(148, 163, 184, 0.14);
        color: #dbe4f0;
        font-size: 0.95rem;
        line-height: 1;
    }

    .dashboard-overview {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .overview-chip {
        min-height: 112px;
        border-radius: 16px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid rgba(148, 163, 184, 0.14);
    }

    .overview-chip span {
        display: block;
        color: #94a3b8;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .overview-chip strong {
        display: block;
        margin-top: 0.65rem;
        color: #f8fafc;
        font-size: 1.45rem;
        line-height: 1.15;
        font-weight: 800;
    }

    .kpi-card {
        --tone-color: #94a3b8;
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-top: 3px solid var(--tone-color);
        border-radius: 18px;
        padding: 1.05rem;
        height: 100%;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
    }

    .kpi-card.tone-emerald {
        --tone-color: #22c55e;
    }

    .kpi-card.tone-indigo {
        --tone-color: #818cf8;
    }

    .kpi-card.tone-cyan {
        --tone-color: #38bdf8;
    }

    .kpi-card.tone-amber {
        --tone-color: #f59e0b;
    }

    .kpi-card.tone-slate {
        --tone-color: #94a3b8;
    }

    .kpi-card-head {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .kpi-card-head h3 {
        margin: 0;
        color: #f8fafc;
        font-size: 1.18rem;
        font-weight: 800;
    }

    .kpi-card-head p {
        margin: 0.28rem 0 0;
        color: #94a3b8;
        font-size: 0.95rem;
    }

    .kpi-total {
        color: var(--tone-color);
        font-size: 1.48rem;
        line-height: 1;
        font-weight: 800;
    }

    .kpi-quick {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 0.95rem;
    }

    .kpi-quick-item {
        border-radius: 14px;
        padding: 0.7rem 0.8rem;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(148, 163, 184, 0.12);
    }

    .kpi-quick-item span {
        display: block;
        color: #94a3b8;
        font-size: 0.76rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 0.28rem;
    }

    .kpi-quick-item strong {
        color: #f8fafc;
        font-size: 1.2rem;
        font-weight: 800;
    }

    .kpi-metrics {
        display: grid;
        gap: 0.42rem;
    }

    .kpi-row {
        display: flex;
        justify-content: space-between;
        gap: 0.9rem;
        padding-bottom: 0.4rem;
        border-bottom: 1px dashed rgba(148, 163, 184, 0.14);
        color: #d7e0ea;
        font-size: 0.96rem;
    }

    .kpi-row:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .kpi-row strong {
        color: #f8fafc;
        font-weight: 700;
    }

    .is-success {
        color: #4ade80 !important;
    }

    .is-danger {
        color: #f87171 !important;
    }

    .is-profit-positive {
        color: #4ade80 !important;
    }

    .is-profit-negative {
        color: #fb7185 !important;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
        gap: 1rem;
    }

    .chart-shell {
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 20px;
        padding: 1.1rem;
        height: 100%;
    }

    .chart-head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
    }

    .chart-title {
        margin: 0;
        color: #f8fafc;
        font-size: 1.28rem;
        font-weight: 800;
    }

    .chart-description {
        margin: 0.45rem 0 0;
        color: #9fb0c3;
        font-size: 0.97rem;
        line-height: 1.6;
        max-width: 48ch;
    }

    .chart-download-btn {
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: rgba(255, 255, 255, 0.02);
        color: #f8fafc;
        padding: 0.55rem 0.9rem;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 700;
        line-height: 1;
    }

    .chart-download-btn:hover {
        background: rgba(255, 255, 255, 0.07);
        color: #f8fafc;
    }

    .chart-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin: 1rem 0 1.05rem;
    }

    .summary-pill {
        min-width: 132px;
        border-radius: 14px;
        padding: 0.72rem 0.85rem;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(148, 163, 184, 0.12);
    }

    .summary-pill span {
        display: block;
        color: #94a3b8;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.28rem;
    }

    .summary-pill strong {
        display: block;
        color: #f8fafc;
        font-size: 1.04rem;
        font-weight: 800;
        line-height: 1.3;
    }

    .summary-pill.tone-success strong {
        color: #4ade80;
    }

    .summary-pill.tone-danger strong {
        color: #f87171;
    }

    .summary-pill.tone-warning strong {
        color: #fbbf24;
    }

    .summary-pill.tone-info strong {
        color: #38bdf8;
    }

    .summary-pill.tone-muted strong {
        color: #e2e8f0;
    }

    .chart-canvas-wrap {
        position: relative;
        min-height: 370px;
        background: rgba(255, 255, 255, 0.01);
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.12);
        padding: 0.85rem;
    }

    .chart-canvas-wrap canvas {
        width: 100% !important;
        height: 100% !important;
    }

    @media (max-width: 1399px) {
        .chart-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 1199px) {
        .dashboard-hero {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .dashboard-panel,
        .chart-shell {
            padding: 1rem;
            border-radius: 16px;
        }

        .dashboard-overview {
            grid-template-columns: 1fr;
        }

        .chart-head {
            flex-direction: column;
        }

        .chart-summary {
            gap: 0.6rem;
        }

        .summary-pill {
            min-width: calc(50% - 0.35rem);
        }

        .chart-canvas-wrap {
            min-height: 320px;
        }
    }
</style>

<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Dashboard</h3>
        </div>

        <div class="card-body">
            <div class="dashboard-shell">
                <div class="dashboard-panel">
                    <section class="dashboard-hero">
                        <div>
                            <p class="dashboard-kicker">Genel Durum</p>
                            <h2 class="dashboard-title">Sipariş ve finans özeti</h2>
                            <p class="dashboard-subtitle">Güncel sipariş hacmini, başarı oranını ve dönemsel kar görünümünü tek ekranda takip edin.</p>
                            <div class="dashboard-meta">
                                <span>Son güncelleme: <?php echo $escape(date('d.m.Y H:i')); ?></span>
                                <span>Durum: Canlı</span>
                            </div>
                        </div>

                        <div class="dashboard-overview">
                            <div class="overview-chip">
                                <span>Bugün Toplam Sipariş</span>
                                <strong><?php echo $escape($dashboardOverview['today_orders_text'] ?? '0'); ?></strong>
                            </div>
                            <div class="overview-chip">
                                <span>Bugün Başarı Oranı</span>
                                <strong><?php echo $escape($dashboardOverview['today_success_rate_text'] ?? '0,00%'); ?></strong>
                            </div>
                            <div class="overview-chip">
                                <span>Aylık Net Kar</span>
                                <strong><?php echo $escape($dashboardOverview['monthly_net_profit_text'] ?? '0,00 TL'); ?></strong>
                            </div>
                            <div class="overview-chip">
                                <span>Yıllık Net Kar</span>
                                <strong><?php echo $escape($dashboardOverview['yearly_net_profit_text'] ?? '0,00 TL'); ?></strong>
                            </div>
                        </div>
                    </section>

                <div class="row g-4 my-1">
                    <?php foreach ($cardOrder as $period) : ?>
                        <?php
                        $card = $dashboardCards[$period] ?? null;
                        if (!$card) {
                            continue;
                        }
                        $success = $card['success'] ?? [];
                        ?>
                        <div class="col-12 col-md-6 col-xxl-3">
                            <article class="kpi-card tone-<?php echo $escape($card['tone'] ?? 'slate'); ?> card-flush">
                                <header class="kpi-card-head">
                                    <div>
                                        <h3><?php echo $escape($card['title'] ?? '-'); ?></h3>
                                        <p><?php echo $escape($card['subtitle'] ?? '-'); ?></p>
                                    </div>
                                    <span class="kpi-total"><?php echo $escape($card['total_orders_text'] ?? '0'); ?></span>
                                </header>

                                <div class="kpi-quick">
                                    <div class="kpi-quick-item">
                                        <span>Başarılı</span>
                                        <strong class="is-success"><?php echo $escape($card['success_count_text'] ?? '0'); ?></strong>
                                    </div>
                                    <div class="kpi-quick-item">
                                        <span>İptal</span>
                                        <strong class="is-danger"><?php echo $escape($card['cancel_count_text'] ?? '0'); ?></strong>
                                    </div>
                                </div>

                                <div class="kpi-metrics">
                                    <div class="kpi-row">
                                        <span>Başarılı Maliyet</span>
                                        <strong><?php echo $escape($success['cost_text'] ?? '0,00 TL'); ?></strong>
                                    </div>
                                    <div class="kpi-row">
                                        <span>Başarılı Satış</span>
                                        <strong><?php echo $escape($success['sale_text'] ?? '0,00 TL'); ?></strong>
                                    </div>
                                    <div class="kpi-row">
                                        <span>Başarılı Kar</span>
                                        <strong class="<?php echo $escape($success['profit_class'] ?? 'is-profit-positive'); ?>"><?php echo $escape($success['profit_text'] ?? '0,00 TL'); ?></strong>
                                    </div>
                                    <div class="kpi-row">
                                        <span>İptal Oranı</span>
                                        <strong class="is-danger"><?php echo $escape($card['cancel_rate_text'] ?? '0,00%'); ?></strong>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                    <div class="chart-grid mt-5">
                        <section class="chart-shell">
                            <div class="chart-head">
                                <div>
                                    <p class="chart-kicker">Operasyon Akışı</p>
                                    <h4 class="chart-title">Günlük sipariş durumu</h4>
                                    <p class="chart-description">Son 30 günde başarılı, iptal veya hata alan ve bekleyen siparişlerin gün bazlı dağılımı.</p>
                                </div>
                                <button type="button" class="chart-download-btn" data-chart-download="daily">PNG İndir</button>
                            </div>

                            <div class="chart-summary">
                                <?php foreach (($dailyStatusChart['summary'] ?? []) as $item) : ?>
                                    <div class="summary-pill tone-<?php echo $escape($item['tone'] ?? 'muted'); ?>">
                                        <span><?php echo $escape($item['label'] ?? '-'); ?></span>
                                        <strong><?php echo $escape($item['value_text'] ?? '-'); ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="chart-canvas-wrap">
                                <canvas id="dailyStatusChartCanvas"></canvas>
                            </div>
                        </section>

                        <section class="chart-shell">
                            <div class="chart-head">
                                <div>
                                    <p class="chart-kicker">Finans Eğilimi</p>
                                    <h4 class="chart-title">Aylık başarılı satış ve kar</h4>
                                    <p class="chart-description">Takvim ayı bazında son 12 ayın başarılı satış ve başarılı kar eğilimini birlikte izleyin.</p>
                                </div>
                                <button type="button" class="chart-download-btn" data-chart-download="monthly">PNG İndir</button>
                            </div>

                            <div class="chart-summary">
                                <?php foreach (($monthlyFinanceChart['summary'] ?? []) as $item) : ?>
                                    <div class="summary-pill tone-<?php echo $escape($item['tone'] ?? 'muted'); ?>">
                                        <span><?php echo $escape($item['label'] ?? '-'); ?></span>
                                        <strong><?php echo $escape($item['value_text'] ?? '-'); ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="chart-canvas-wrap">
                                <canvas id="monthlyFinanceChartCanvas"></canvas>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url('assets-front/js/chart.min.js'); ?>"></script>
<script>
    (function() {
        var dailyPayload = <?php echo $dailyStatusChartJson; ?>;
        var monthlyPayload = <?php echo $monthlyFinanceChartJson; ?>;
        var numberFormatter = new Intl.NumberFormat('tr-TR');
        var currencyFormatter = new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY',
            maximumFractionDigits: 0
        });

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') {
                return;
            }

            renderDailyChart();
            renderMonthlyChart();
            bindDownloads();
        });

        function safeArray(value) {
            return Array.isArray(value) ? value : [];
        }

        function numberArray(value) {
            return safeArray(value).map(function(item) {
                var parsed = Number(item);
                return Number.isFinite(parsed) ? parsed : 0;
            });
        }

        function formatCurrency(value) {
            return currencyFormatter.format(Number(value) || 0);
        }

        function formatNumber(value) {
            return numberFormatter.format(Number(value) || 0);
        }

        function formatAxisCurrency(value) {
            var numeric = Number(value) || 0;
            var absolute = Math.abs(numeric);

            if (absolute >= 1000000) {
                return '₺' + numberFormatter.format(Math.round(numeric / 1000000)) + ' Mn';
            }

            if (absolute >= 1000) {
                return '₺' + numberFormatter.format(Math.round(numeric / 1000)) + ' Bin';
            }

            return '₺' + numberFormatter.format(numeric);
        }

        function destroyChart(name) {
            if (window[name] && typeof window[name].destroy === 'function') {
                window[name].destroy();
            }
        }

        function renderDailyChart() {
            var canvas = document.getElementById('dailyStatusChartCanvas');
            if (!canvas) {
                return;
            }

            destroyChart('dashboardDailyChart');

            var labels = safeArray(dailyPayload && dailyPayload.labels);
            var series = dailyPayload && dailyPayload.series ? dailyPayload.series : {};

            window.dashboardDailyChart = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Başarılı',
                        data: numberArray(series.success),
                        backgroundColor: '#22c55e',
                        borderRadius: 8,
                        maxBarThickness: 22,
                        stack: 'orders'
                    }, {
                        label: 'İptal / Hata',
                        data: numberArray(series.canceled),
                        backgroundColor: '#ef4444',
                        borderRadius: 8,
                        maxBarThickness: 22,
                        stack: 'orders'
                    }, {
                        label: 'Bekleyen',
                        data: numberArray(series.pending),
                        backgroundColor: '#64748b',
                        borderRadius: 8,
                        maxBarThickness: 22,
                        stack: 'orders'
                    }, {
                        type: 'line',
                        label: 'Toplam Sipariş',
                        data: numberArray(series.total),
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56, 189, 248, 0.15)',
                        borderWidth: 3,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        tension: 0.32,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#dbe4f0',
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 16,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0b1220',
                            borderColor: 'rgba(148, 163, 184, 0.18)',
                            borderWidth: 1,
                            titleColor: '#f8fafc',
                            bodyColor: '#e5e7eb',
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + formatNumber(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#9fb0c3',
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10,
                                font: {
                                    size: 11,
                                    weight: '600'
                                }
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.12)'
                            },
                            ticks: {
                                color: '#9fb0c3',
                                precision: 0,
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return formatNumber(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        function renderMonthlyChart() {
            var canvas = document.getElementById('monthlyFinanceChartCanvas');
            if (!canvas) {
                return;
            }

            destroyChart('dashboardMonthlyChart');

            var labels = safeArray(monthlyPayload && monthlyPayload.labels);
            var series = monthlyPayload && monthlyPayload.series ? monthlyPayload.series : {};

            window.dashboardMonthlyChart = new Chart(canvas.getContext('2d'), {
                data: {
                    labels: labels,
                    datasets: [{
                        type: 'bar',
                        label: 'Başarılı Satış',
                        data: numberArray(series.sales),
                        backgroundColor: 'rgba(56, 189, 248, 0.72)',
                        borderColor: '#38bdf8',
                        borderWidth: 1,
                        borderRadius: 10,
                        maxBarThickness: 28
                    }, {
                        type: 'line',
                        label: 'Başarılı Kar',
                        data: numberArray(series.profit),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.15)',
                        borderWidth: 3,
                        pointRadius: 2.5,
                        pointHoverRadius: 4,
                        tension: 0.28,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#dbe4f0',
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 16,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0b1220',
                            borderColor: 'rgba(148, 163, 184, 0.18)',
                            borderWidth: 1,
                            titleColor: '#f8fafc',
                            bodyColor: '#e5e7eb',
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + formatCurrency(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#9fb0c3',
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 12,
                                font: {
                                    size: 11,
                                    weight: '600'
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.12)'
                            },
                            ticks: {
                                color: '#9fb0c3',
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return formatAxisCurrency(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        function bindDownloads() {
            var buttons = document.querySelectorAll('[data-chart-download]');

            Array.prototype.forEach.call(buttons, function(button) {
                button.addEventListener('click', function() {
                    var chartName = button.getAttribute('data-chart-download');
                    var chart = chartName === 'daily' ? window.dashboardDailyChart : window.dashboardMonthlyChart;
                    var fileName = chartName === 'daily' ? 'gunluk-siparis-durumu.png' : 'aylik-satis-net-kar.png';

                    if (!chart || typeof chart.toBase64Image !== 'function') {
                        return;
                    }

                    var link = document.createElement('a');
                    link.href = chart.toBase64Image('image/png', 1);
                    link.download = fileName;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
            });
        }
    })();
</script>
