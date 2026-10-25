<?php defined('BASEPATH') or exit('No direct script access allowed');

class Balance extends MY_Controller
{
    public $data = array();
    public function __construct()
    {
        $this->data['user'] = userCheck();
        parent::__construct();
    }

    public function index()
    {
        $data = $this->data;
        $data['banks'] = $this->db->from('banks')->get()->result();

        // ---- YENİ: Dashboard Summary Verileri ----
        // Bugünün tarihini alıyoruz (Günlük istatistikler için)
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd   = date('Y-m-d 23:59:59');

        // Bekleyen Toplam İşlem (Tutar ve Adet) - Durum: 0
        $pendingQuery = $this->db->select('COUNT(user_balance_id) as total_count, COALESCE(SUM(user_balance_amount), 0) as total_amount')
            ->from('user_balance')
            ->where('user_balance_status', 0)
            ->where('user_balance_order_id IS NULL', null, false)
            ->get()->row();

        // Bugün Onaylanan Bakiye (Tutar) - Durum: 1
        $approvedTodayQuery = $this->db->select('COALESCE(SUM(user_balance_amount), 0) as total_amount')
            ->from('user_balance')
            ->where('user_balance_status', 1)
            ->where('user_balance_change_date >=', $todayStart)
            ->where('user_balance_change_date <=', $todayEnd)
            ->get()->row();

        // Bugün Reddedilen İşlem (Adet) - Durum: 2
        $rejectedTodayQuery = $this->db->select('COUNT(user_balance_id) as total_count')
            ->from('user_balance')
            ->where('user_balance_status', 2)
            ->where('user_balance_change_date >=', $todayStart)
            ->where('user_balance_change_date <=', $todayEnd)
            ->get()->row();

        $pendingOrderQuery = $this->db->select('COUNT(order_id) as total_count, COALESCE(SUM(order_sale_price), 0) as total_amount')
            ->from('orders')
            ->where('order_status', 11)
            ->get()->row();

        // View'a gönderilecek summary dizisini oluştur
        $data['summary'] = [
            'pending_count'  => (int) $pendingQuery->total_count,
            'pending_amount' => (float) $pendingQuery->total_amount,
            'approved_today' => (float) $approvedTodayQuery->total_amount,
            'rejected_today' => (int) $rejectedTodayQuery->total_count,
            'pending_order_count' => (int) $pendingOrderQuery->total_count,
            'pending_order_amount' => (float) $pendingOrderQuery->total_amount,
        ];
        // ------------------------------------------

        // resellers
        $data['resellers'] = $this->db->from('users')->where('user_type', 3)->get()->result();

        $this->template->content->view('index', $data);
        $this->template->publish();
    }

    public function refunds()
    {
        $data = $this->data;
        $data['refunds_setup_missing'] = !$this->db->table_exists('payment_refunds');
        if ($data['refunds_setup_missing']) {
            $data['refunds'] = [];
            $this->template->content->view('refunds', $data);
            $this->template->publish();
            return;
        }
        $data['refunds'] = $this->db
            ->select('r.*, u.user_name, u.user_surname, u.user_email, ub.user_balance_amount as payment_amount, o.order_sale_price')
            ->from('payment_refunds r')
            ->join('users u', 'u.user_id = r.refund_user_id', 'left')
            ->join('user_balance ub', 'ub.user_balance_id = r.refund_balance_id', 'left')
            ->join('orders o', 'o.order_id = r.refund_order_id', 'left')
            ->order_by('r.refund_id', 'DESC')->get()->result();
        $this->template->content->view('refunds', $data);
        $this->template->publish();
    }

    public function startRefund()
    {
        $source = trim((string)$this->input->post('source', true));
        $paymentId = (int)$this->input->post('payment_id');
        $amount = round((float)$this->input->post('amount'), 2);
        $reason = trim((string)$this->input->post('reason', true));
        if (!$this->db->table_exists('payment_refunds') || $paymentId <= 0 || $amount <= 0 || !in_array($source, ['balance', 'order'], true)) {
            return json(['status' => 'error', 'message' => 'Geçerli ödeme detayından iade başlatın.'], 422);
        }
        $this->db->trans_start();
        $payment = null;
        $balanceId = null;
        $orderId = null;
        if ($source === 'balance') {
            $payment = $this->db->query('SELECT * FROM user_balance WHERE user_balance_id = ? AND user_balance_status = 1 FOR UPDATE', [$paymentId])->row();
            $balanceId = $payment ? (int)$payment->user_balance_id : null;
        } else {
            $payment = $this->db->query('SELECT * FROM orders WHERE order_id = ? AND order_status NOT IN (11, 12) FOR UPDATE', [$paymentId])->row();
            $orderId = $payment ? (int)$payment->order_id : null;
        }
        if (!$payment) {
            $this->db->trans_rollback();
            return json(['status' => 'error', 'message' => 'Ödeme bulunamadı veya iade edilebilir değil.'], 404);
        }
        $refundQuery = $this->db->select_sum('refund_amount');
        if ($source === 'balance') {
            $refundQuery->where('refund_balance_id', $balanceId);
        } else {
            $refundQuery->where('refund_order_id', $orderId);
        }
        $refunded = $refundQuery
            ->where_in('refund_status', ['pending', 'completed'])->get('payment_refunds')->row();
        $paymentAmount = $source === 'balance' ? (float)$payment->user_balance_amount : (float)$payment->order_sale_price;
        $remaining = round($paymentAmount - (float)($refunded->refund_amount ?? 0), 2);
        if ($amount > $remaining) {
            $this->db->trans_rollback();
            return json(['status' => 'error', 'message' => 'İade tutarı kalan ödeme tutarını aşamaz.'], 422);
        }
        $userId = $source === 'balance' ? (int)$payment->user_balance_user_id : (int)$payment->order_reseller_id;
        if ($source === 'balance') {
            $balance = $this->db->select('COALESCE(SUM(CASE WHEN user_balance_status = 1 THEN user_balance_amount WHEN user_balance_status = 3 THEN -user_balance_amount ELSE 0 END), 0) AS available')
                ->where('user_balance_user_id', $userId)
                ->where('user_balance_type', (int)$payment->user_balance_type)->get('user_balance')->row();
            if ((float)$balance->available < $amount) {
                $this->db->trans_rollback();
                return json(['status' => 'error', 'message' => 'Kullanılabilir bakiye iade tutarından düşük.'], 422);
            }
            $this->db->insert('user_balance', [
                'user_balance_user_id' => $userId,
                'user_balance_amount' => $amount,
                'user_balance_type' => (int)$payment->user_balance_type,
                'user_balance_status' => 3,
                'user_balance_order_id' => $payment->user_balance_order_id,
                'user_balance_note' => 'Ödeme iadesi bekliyor #' . $balanceId,
                'user_balance_admin_id' => (int)($this->data['user']['admin_id'] ?? 0),
                'user_balance_created_at' => date('Y-m-d H:i:s'),
                'user_balance_change_date' => date('Y-m-d H:i:s'),
            ]);
        }
        $this->db->insert('payment_refunds', [
            'refund_user_id' => $userId,
            'refund_order_id' => $orderId,
            'refund_balance_id' => $balanceId,
            'refund_amount' => $amount,
            'refund_status' => 'pending',
            'refund_reason' => $reason,
            'refund_requested_by' => (int)($this->data['user']['admin_id'] ?? 0),
        ]);
        $this->db->trans_complete();
        return json(['status' => $this->db->trans_status() ? 'success' : 'error', 'message' => $this->db->trans_status() ? 'İade bekleyen ödemelere eklendi.' : 'İade başlatılamadı.']);
    }

    public function completeRefund()
    {
        $id = (int)$this->input->post('refund_id');
        $refund = $this->db->where(['refund_id' => $id, 'refund_status' => 'pending'])->get('payment_refunds')->row();
        if (!$refund) {
            return json(['status' => 'error', 'message' => 'Bekleyen iade bulunamadı.'], 404);
        }
        $this->db->where('refund_id', $id)->where('refund_status', 'pending')->update('payment_refunds', [
            'refund_status' => 'completed',
            'refund_completed_at' => date('Y-m-d H:i:s'),
        ]);
        if ($this->db->affected_rows() < 1) {
            return json(['status' => 'error', 'message' => 'İade zaten tamamlanmış veya güncellenemedi.'], 409);
        }
        return json(['status' => 'success', 'message' => 'İade tamamlandı.']);
    }

    public function newBalanceTransactionsAjax()
    {
        $uriSegment = $this->uri->segment(3);
        if ($uriSegment == 2) {
            $statusArray = [0];
        }
        if ($uriSegment == 3) {
            $statusArray = [1, 2];
        }
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));

        // Filtre Verilerini AJAX'tan Yakala
        $orderID = !empty($this->input->post("order_id")) ? $this->input->post("order_id") : null;
        $statusFiltresi = $this->input->post("status");

        // YENİ: Tarih verilerini yakala ve saatleri ekle
        $startDate = !empty($this->input->post("startDate")) ? $this->input->post("startDate") . " 00:00:00" : null;
        $endDate = !empty($this->input->post("endDate")) ? $this->input->post("endDate") . " 23:59:59" : null;
        $reseller_id = !empty($this->input->post("reseller_id")) ? $this->input->post("reseller_id") : null;
        $balanceSearch = $this->input->post('balance_search');
        $balanceSearch = is_string($balanceSearch) ? trim($balanceSearch) : '';
        $balanceBankID = (int)$this->input->post('balance_bank_id');

        if ($uriSegment == 3 && $statusFiltresi !== null && $statusFiltresi !== '') {
            $statusArray = [$statusFiltresi];
        }

        // Fonksiyonlara tarih parametrelerini de gönderiyoruz
        $onlyBalanceTopups = $uriSegment == 2;
        $transactions = $this->transactions($start, $length, $orderID, $statusArray, $startDate, $endDate, $reseller_id, $onlyBalanceTopups, $balanceSearch, $balanceBankID);
        $recordsTotal = $this->allTransactionsCount($orderID, $statusArray, $startDate, $endDate, $reseller_id, $onlyBalanceTopups, $balanceSearch, $balanceBankID);

        $statusArrayFormat = [
            0 => [
                'text' => 'Beklemede',
                'icon' => 'fa fa-clock',
                'class' => 'badge badge-warning',
            ],
            1 => [
                'text' => 'Onaylandı',
                'icon' => 'fa fa-check',
                'class' => 'badge badge-success',
            ],
            2 => [
                'text' => 'Reddedildi',
                'icon' => 'fa fa-times',
                'class' => 'badge badge-danger',
            ],
        ];

        $dt = array();
        foreach ($transactions as $transaction) {
            $dt[] = array(
                $transaction->user_balance_id,
                html_escape((string)($transaction->user_balance_reference_no ?: '-')),
                $transaction->bank_name,
                '<a href="' . base_url('users/detail/' . $transaction->user_id) . '">' . html_escape((string)$transaction->user_name) . ' #' . $transaction->user_id . '</a>',
                number_format($transaction->user_balance_amount, 2, ',', '.') . ' TL',
                date('d.m.Y H:i:s', strtotime($transaction->user_balance_created_at)),
                $transaction->user_balance_change_date != null ? date('d.m.Y H:i:s', strtotime($transaction->user_balance_change_date)) : ' - ',
                $transaction->admin_name != null ? $transaction->admin_name : '-',
                $transaction->user_balance_note != null ? $transaction->user_balance_note : ' - ',
                '<span onClick="changeStatus(' . $transaction->user_balance_id . ')" class="change-status ' . $statusArrayFormat[$transaction->user_balance_status]['class'] . '">' . $statusArrayFormat[$transaction->user_balance_status]['text'] . '</span>',
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => !empty($dt) ? $dt : [],
        );
        return json($output);
    }

    public function pendingOrdersAjax()
    {
        $isPaymentPending = (int)$this->input->post('payment_pending') === 1;
        $pendingStatuses = $isPaymentPending ? [11] : [0, 3, 4, 5, 9, 13, 14, 15];
        $draw = (int)$this->input->post('draw');
        $start = max(0, (int)$this->input->post('start'));
        $length = (int)$this->input->post('length');
        $length = $length > 0 ? min($length, 500) : 20;

        $orderID = (int)$this->input->post('order_id');
        $userID = (int)$this->input->post('user_id');
        $searchInput = $this->input->post('order_search');
        if (!is_string($searchInput)) {
            $searchInput = is_array($this->input->post('search')) ? ($this->input->post('search')['value'] ?? '') : '';
        }
        $search = trim((string)$searchInput);
        $paymentType = (int)$this->input->post('payment_type');
        $startDate = trim((string)$this->input->post('startDate'));
        $endDate = trim((string)$this->input->post('endDate'));

        $queryOrders = function () use ($pendingStatuses, $orderID, $userID, $search, $paymentType, $startDate, $endDate) {
            $this->db
                ->from('orders as o')
                ->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
                ->join('products as p', 'p.product_id = o.order_product_id', 'left')
                ->join('order_status as os', 'os.order_status_code = o.order_status', 'left')
                ->where_in('o.order_status', $pendingStatuses);

            if ($orderID > 0) {
                $this->db->where('o.order_id', $orderID);
            }
            if ($userID > 0) {
                $this->db->where('o.order_reseller_id', $userID);
            }
            if ($search !== '') {
                $this->db->group_start()
                    ->where('u.user_name', $search)
                    ->or_where('o.order_id', (int)$search)
                    ->or_where('o.order_reseller_id', (int)$search)
                    ->or_like('u.user_name', $search)
                    ->or_like('p.product_name', $search)
                    ->group_end();
            }
            if ($paymentType > 0) {
                $this->db->where('o.order_payment_type', $paymentType);
            }
            if ($startDate !== '') {
                $this->db->where('o.order_created_at >=', $startDate . ' 00:00:00');
            }
            if ($endDate !== '') {
                $this->db->where('o.order_created_at <=', $endDate . ' 23:59:59');
            }
        };

        $queryOrders();
        $totalOrders = $this->db->count_all_results();

        $queryOrders();
        $orders = $this->db
            ->select('o.order_id, o.order_reseller_id, o.order_product_id, o.order_quantity, o.order_sale_price, o.order_sale_price_currency, o.order_created_at, o.order_status, o.order_payment_type, o.order_eft_id, u.user_name, p.product_name, os.order_status_name')
            ->order_by('o.order_created_at', 'DESC')
            ->limit($length, $start)
            ->get()->result();

        $statusClasses = [
            0 => 'warning',
            3 => 'info',
            4 => 'primary',
            5 => 'danger',
            9 => 'dark',
            13 => 'warning',
            14 => 'danger',
            15 => 'info',
            11 => 'warning',
        ];
        $data = [];
        foreach ($orders as $order) {
            $statusClass = $statusClasses[(int)$order->order_status] ?? 'secondary';
            $statusName = !empty($order->order_status_name) ? $order->order_status_name : 'Bilinmiyor';
            $price = is_numeric($order->order_sale_price) ? number_format((float)$order->order_sale_price, 2, ',', '.') . ' TL' : '-';
            $paymentType = [2 => 'Cüzdan', 3 => 'Havale / EFT', 4 => 'Kredi Kartı'][(int)$order->order_payment_type] ?? '-';

            if ($isPaymentPending) {
                $data[] = [
                    '<a href="' . base_url('orders/orderInvoice/' . (int)$order->order_id) . '" target="_blank" rel="noopener">#' . (int)$order->order_id . '</a>',
                    '<a href="' . base_url('users/detail/' . (int)$order->order_reseller_id) . '" target="_blank" rel="noopener">' . html_escape((string)$order->user_name) . ' #' . (int)$order->order_reseller_id . '</a>',
                    html_escape((string)$order->product_name) . ' <span class="text-muted">x' . (int)$order->order_quantity . '</span>',
                    $price,
                    html_escape($paymentType),
                    html_escape((string)($order->order_eft_id ?: '-')),
                    date('d.m.Y H:i:s', strtotime($order->order_created_at)),
                    '<span class="badge badge-' . $statusClass . '">' . html_escape($statusName) . '</span>',
                ];
            } else {
                $data[] = [
                    '<a href="' . base_url('orders/orderInvoice/' . (int)$order->order_id) . '" target="_blank" rel="noopener">#' . (int)$order->order_id . '</a>',
                    '<a href="' . base_url('users/detail/' . (int)$order->order_reseller_id) . '" target="_blank" rel="noopener">' . html_escape((string)$order->user_name) . ' #' . (int)$order->order_reseller_id . '</a>',
                    html_escape((string)$order->product_name) . ' <span class="text-muted">x' . (int)$order->order_quantity . '</span>',
                    $price,
                    date('d.m.Y H:i:s', strtotime($order->order_created_at)),
                    '<span class="badge badge-' . $statusClass . '">' . html_escape($statusName) . '</span>',
                ];
            }
        }

        return json([
            'draw' => $draw,
            'recordsTotal' => $totalOrders,
            'recordsFiltered' => $totalOrders,
            'data' => $data,
        ]);
    }

    public function allPaymentsAjax()
    {
        $draw = (int)$this->input->post('draw');
        $start = max(0, (int)$this->input->post('start'));
        $length = (int)$this->input->post('length');
        $length = $length > 0 ? min($length, 500) : 20;
        $search = trim((string)$this->input->post('payment_search'));
        $customerSearch = trim((string)$this->input->post('payment_customer_search'));
        $source = trim((string)$this->input->post('payment_source'));
        $paymentType = (int)$this->input->post('payment_type');
        $startDate = trim((string)$this->input->post('startDate'));
        $endDate = trim((string)$this->input->post('endDate'));
        $statusGroup = trim((string)$this->input->post('payment_status'));
        $amountOp = trim((string)$this->input->post('amount_op'));
        $amountRaw = trim((string)$this->input->post('amount'));
        $amountValue = is_numeric($amountRaw) ? (float)$amountRaw : null;
        $amountOperators = ['gt' => '>', 'lt' => '<', 'eq' => '='];
        $applyAmount = $amountValue !== null && isset($amountOperators[$amountOp]);
        // Durum grupları: kaynak bazında iç durum kodlarına çevrilir.
        $balanceStatusMap = ['ok' => [1], 'wait' => [0], 'bad' => [2], 'refund' => [4]];
        $orderStatusMap = ['ok' => [1], 'wait' => [0, 11, 13], 'bad' => [2, 12], 'refund' => []];
        $balanceStatuses = $statusGroup !== '' ? ($balanceStatusMap[$statusGroup] ?? []) : null;
        $orderStatuses = $statusGroup !== '' ? ($orderStatusMap[$statusGroup] ?? []) : null;

        $payments = [];
        $balanceRows = $this->db
            ->select('ub.user_balance_id, ub.user_balance_user_id, ub.user_balance_amount, ub.user_balance_status, ub.user_balance_reference_no, ub.user_balance_created_at, ub.user_balance_change_date, ub.user_balance_note, u.user_name, b.bank_name')
            ->from('user_balance as ub')
            ->join('users as u', 'u.user_id = ub.user_balance_user_id', 'left')
            ->join('banks as b', 'b.bank_id = ub.user_balance_bank_id', 'left')
            ->where('ub.user_balance_order_id IS NULL', null, false)
            // Gerçek ödeme: harcama/düşüm (3) ve yöneticinin elle eklediği bakiye (banka/kart bilgisi olmayan) listelenmez
            ->where_in('ub.user_balance_status', [0, 1, 2, 4])
            ->group_start()
                ->where('ub.user_balance_bank_id >', 0)
                ->or_where('ub.user_balance_admin_id IS NULL', null, false)
                ->or_where('ub.user_balance_vomsis_payment_id IS NOT NULL', null, false)
                ->or_like('ub.user_balance_note', '"card"', 'both')
            ->group_end();

        if ($source === 'order' || $balanceStatuses === []) {
            $balanceRows = null;
        } else {
            if ($search !== '') {
                $this->db->group_start()
                    ->like('u.user_name', $search)
                    ->or_like('ub.user_balance_reference_no', $search)
                    ->or_like('ub.user_balance_note', $search)
                    ->group_end();
            }
            if ($customerSearch !== '') {
                $this->db->group_start()
                    ->like('u.user_name', $customerSearch)
                    ->or_like('u.user_surname', $customerSearch)
                    ->or_like('u.user_email', $customerSearch)
                    ->or_like('u.user_phone', $customerSearch)
                    ->group_end();
            }
            if ($balanceStatuses !== null) {
                $this->db->where_in('ub.user_balance_status', $balanceStatuses);
            }
            if ($applyAmount) {
                $this->db->where('ub.user_balance_amount ' . $amountOperators[$amountOp], $amountValue);
            }
            if ($startDate !== '') {
                $this->db->where('ub.user_balance_created_at >=', $startDate . ' 00:00:00');
            }
            if ($endDate !== '') {
                $this->db->where('ub.user_balance_created_at <=', $endDate . ' 23:59:59');
            }
            $balanceRows = $this->db->order_by('ub.user_balance_created_at', 'DESC')->get()->result();
            foreach ($balanceRows as $row) {
                $payments[] = [
                    'source' => 'Bakiye Yükleme',
                    'source_code' => 'balance',
                    'transaction_id' => (int)$row->user_balance_id,
                    'order_id' => 0,
                    'user_id' => (int)$row->user_balance_user_id,
                    'user_name' => (string)$row->user_name,
                    'method' => !empty($row->bank_name) ? (string)$row->bank_name : 'Bakiye yükleme',
                    'amount' => (float)$row->user_balance_amount,
                    'currency' => 'TL',
                    'reference' => (string)($row->user_balance_reference_no ?: '-'),
                    'created_at' => (string)$row->user_balance_created_at,
                    'status' => (int)$row->user_balance_status,
                    'status_name' => [0 => 'Beklemede', 1 => 'Onaylandı', 2 => 'Reddedildi', 4 => 'İade edildi'][(int)$row->user_balance_status] ?? 'Bilinmiyor',
                    'detail_url' => base_url('balance/paymentDetail/balance/' . (int)$row->user_balance_id),
                ];
            }
        }

        if ($source !== 'balance' && $orderStatuses !== []) {
            $orderRows = $this->db
                ->select('o.order_id, o.order_reseller_id, o.order_sale_price, o.order_sale_price_currency, o.order_payment_type, o.order_eft_id, o.order_created_at, o.order_status, o.order_quantity, o.order_payment_info, u.user_name, p.product_name')
                ->from('orders as o')
                ->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
                ->join('products as p', 'p.product_id = o.order_product_id', 'left')
                // Bakiye (cüzdan) ile yapılan alışveriş ödeme değil, bakiye hareketidir
                ->where('o.order_payment_type !=', 2);

            if ($search !== '') {
                $this->db->group_start()
                    ->like('u.user_name', $search)
                    ->or_like('o.order_id', $search)
                    ->or_like('o.order_eft_id', $search)
                    ->group_end();
            }
            if ($customerSearch !== '') {
                $this->db->group_start()
                    ->like('u.user_name', $customerSearch)
                    ->or_like('u.user_surname', $customerSearch)
                    ->or_like('u.user_email', $customerSearch)
                    ->or_like('u.user_phone', $customerSearch)
                    ->group_end();
            }
            if ($orderStatuses !== null) {
                $this->db->where_in('o.order_status', $orderStatuses);
            }
            if ($applyAmount) {
                $this->db->where('o.order_sale_price ' . $amountOperators[$amountOp], $amountValue);
            }
            if ($paymentType > 0) {
                $this->db->where('o.order_payment_type', $paymentType);
            }
            if ($startDate !== '') {
                $this->db->where('o.order_created_at >=', $startDate . ' 00:00:00');
            }
            if ($endDate !== '') {
                $this->db->where('o.order_created_at <=', $endDate . ' 23:59:59');
            }

            foreach ($this->db->order_by('o.order_created_at', 'DESC')->get()->result() as $row) {
                $rowInfo = json_decode((string)$row->order_payment_info, true);
                $rowSnapshot = is_array($rowInfo) ? ($rowInfo['pricing_snapshot'] ?? []) : [];
                $productLabel = !empty($rowSnapshot['bundle_product_name'])
                    ? $rowSnapshot['bundle_product_name'] . ' (paket)'
                    : (string)$row->product_name;
                $payments[] = [
                    'product' => trim($productLabel . ((int)$row->order_quantity > 1 ? ' × ' . (int)$row->order_quantity : '')),
                    'source' => 'Sipariş Ödemesi',
                    'source_code' => 'order',
                    'transaction_id' => (int)$row->order_id,
                    'order_id' => (int)$row->order_id,
                    'user_id' => (int)$row->order_reseller_id,
                    'user_name' => (string)$row->user_name,
                    'method' => [2 => 'Cüzdan', 3 => 'Havale / EFT', 4 => 'Kredi Kartı', 5 => 'Mobil'][(int)$row->order_payment_type] ?? 'Sipariş ödemesi',
                    'amount' => (float)$row->order_sale_price,
                    'currency' => 'TL',
                    'reference' => (string)($row->order_eft_id ?: '-'),
                    'created_at' => (string)$row->order_created_at,
                    'status' => (int)$row->order_status,
                    'status_name' => [0 => 'İşleme alındı', 1 => 'Tamamlandı', 2 => 'İptal edildi', 11 => 'Ödeme bekleniyor', 12 => 'Ödeme başarısız', 13 => 'Fraud kontrolü'][(int)$row->order_status] ?? 'İşleniyor',
                    'detail_url' => base_url('balance/paymentDetail/order/' . (int)$row->order_id),
                ];
            }
        }

        usort($payments, static function ($left, $right) {
            return strcmp((string)$right['created_at'], (string)$left['created_at']);
        });

        $total = count($payments);
        $payments = array_slice($payments, $start, $length);
        $data = [];
        foreach ($payments as $payment) {
            $statusClass = in_array((int)$payment['status'], [1, 0], true) ? ((int)$payment['status'] === 1 ? 'ok' : 'wait') : 'bad';
            if ($payment['source_code'] === 'order' && in_array((int)$payment['status'], [11, 13], true)) {
                $statusClass = 'wait';
            }
            $sourceClass = $payment['source_code'] === 'balance' ? 'payment-source-balance' : 'payment-source-order';
            $data[] = [
                '<a class="pay-link" href="' . html_escape($payment['detail_url']) . '">#' . (int)$payment['transaction_id'] . '</a>',
                '<a class="pay-customer" href="' . base_url('users/detail/' . (int)$payment['user_id']) . '"><strong>' . html_escape($payment['user_name']) . '</strong><small class="pay-sub">#' . (int)$payment['user_id'] . ($payment['order_id'] > 0 ? ' · Sipariş #' . (int)$payment['order_id'] : '') . '</small>' . (!empty($payment['product']) ? '<small class="pay-sub">' . html_escape($payment['product']) . '</small>' : '') . '</a>',
                '<span class="pay-chip ' . $sourceClass . '"><i class="fa-solid ' . ($payment['source_code'] === 'balance' ? 'fa-wallet' : 'fa-cart-shopping') . ' me-1"></i>' . html_escape($payment['method']) . '</span>',
                number_format((float)$payment['amount'], 2, ',', '.') . ' TL',
                '<strong>' . number_format((float)$payment['amount'], 2, ',', '.') . ' TL</strong>',
                html_escape($payment['reference']),
                '<span class="pay-chip ' . $statusClass . '">' . html_escape($payment['status_name']) . '</span>',
                date('d.m.Y', strtotime($payment['created_at'])) . '<small class="pay-sub">' . date('H:i', strtotime($payment['created_at'])) . '</small>',
                '<button type="button" class="payment-detail-link" title="Ödeme detayı" aria-label="Ödeme detayı" data-detail-url="' . html_escape($payment['detail_url']) . '" style="background:transparent;border:0;color:#7f8aa3;width:30px;height:30px;border-radius:6px;cursor:pointer;" onmouseover="this.style.color=\'#ffb511\'" onmouseout="this.style.color=\'#7f8aa3\'"><i class="fa-solid fa-eye"></i></button>',
            ];
        }

        return json(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
    }

    public function paymentDetail($source = '', $id = 0)
    {
        $source = $source ?: (string)$this->uri->segment(3);
        $id = (int)($id ?: $this->uri->segment(4));
        $payment = null;

        if ($id > 0 && $source === 'balance') {
            $payment = $this->db
                ->select('ub.*, u.user_name, u.user_email, u.user_phone, b.bank_name, b.bank_iban, b.bank_owner, a.admin_name')
                ->from('user_balance as ub')
                ->join('users as u', 'u.user_id = ub.user_balance_user_id', 'left')
                ->join('banks as b', 'b.bank_id = ub.user_balance_bank_id', 'left')
                ->join('admins as a', 'a.admin_id = ub.user_balance_admin_id', 'left')
                ->where('ub.user_balance_id', $id)
                ->get()->row();
            if ($payment) {
                $walletNote = json_decode((string)$payment->user_balance_note, true);
                $walletCard = is_array($walletNote['card'] ?? null) ? $walletNote['card'] : [];
                $payment->payment_source = 'Bakiye Yükleme';
                $payment->payment_transaction_id = $payment->user_balance_id;
                $payment->payment_order_id = $payment->user_balance_order_id ?: null;
                $payment->payment_reference = $payment->user_balance_reference_no ?: '-';
                $payment->payment_amount = $payment->user_balance_amount;
                $payment->payment_currency = 'TL';
                $payment->payment_created_at = $payment->user_balance_created_at;
                $payment->payment_status = [0 => 'Beklemede', 1 => 'Onaylandı', 2 => 'Reddedildi', 4 => 'İade edildi'][(int)$payment->user_balance_status] ?? 'Bilinmiyor';
                $payment->payment_ip = !empty($payment->user_balance_request_ip) ? (string)$payment->user_balance_request_ip : '-';
                $payment->payment_port = !empty($payment->user_balance_request_port) ? (string)$payment->user_balance_request_port : '';
                $payment->payment_approval_ip = !empty($payment->user_balance_approval_ip) ? (string)$payment->user_balance_approval_ip : '-';
                $payment->payment_approval_port = !empty($payment->user_balance_approval_port) ? (string)$payment->user_balance_approval_port : '-';
                $payment->payment_card = !empty($walletCard['cardBin']) || !empty($walletCard['cardLast4'])
                    ? ($walletCard['cardBin'] ?: '******') . ' **** ' . ($walletCard['cardLast4'] ?: '****')
                    : '-';
                $payment->payment_cardholder = $walletCard['cardHolderName'] ?? '-';
                $payment->payment_card_expiry = $walletCard['cardExpiry'] ?? '-';
                $payment->payment_sender = '-';
                $payment->payment_note = is_array($walletNote)
                    ? ($walletNote['message'] ?? '-')
                    : ($payment->user_balance_note ?: '-');
                $payment->payment_method = !empty($payment->user_balance_bank_id) ? 'Havale / EFT' : (!empty($walletCard) ? 'Kredi Kartı' : 'Bakiye Yükleme');
                $payment->payment_bank = (string)($payment->bank_name ?? '');
                $payment->payment_iban = (string)($payment->bank_iban ?? '');
                $payment->payment_bank_owner = (string)($payment->bank_owner ?? '');
                $payment->payment_vomsis_id = (string)($payment->user_balance_vomsis_payment_id ?? '');
                $payment->payment_approved_at = (string)($payment->user_balance_change_date ?? '');
                $payment->payment_approved_by = (string)($payment->admin_name ?? '');
            }
        } elseif ($id > 0 && $source === 'order') {
            $payment = $this->db
                ->select('o.*, u.user_name, u.user_email, u.user_phone, a.admin_name, eb.bank_name as eft_bank_name, eb.bank_iban as eft_bank_iban, eb.bank_owner as eft_bank_owner')
                ->from('orders as o')
                ->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
                ->join('admins as a', 'a.admin_id = o.order_operator_admin', 'left')
                ->join('banks as eb', 'eb.bank_id = o.order_eft_bank_id', 'left')
                ->where('o.order_id', $id)
                ->get()->row();
            if ($payment) {
                $info = json_decode((string)$payment->order_payment_info, true);
                $info = is_array($info) ? $info : [];
                $payment->payment_source = 'Sipariş Ödemesi';
                $payment->payment_transaction_id = $info['paymentID'] ?? ($payment->order_payment_token ?: $payment->order_id);
                $payment->payment_order_id = $payment->order_id;
                $payment->payment_reference = $payment->order_eft_id ?: ($payment->order_reseller_order_id ?: '-');
                $payment->payment_amount = $payment->order_sale_price;
                $payment->payment_currency = 'TL';
                $payment->payment_created_at = $payment->order_created_at;
                $payment->payment_status = [0 => 'İşleme alındı', 1 => 'Tamamlandı', 2 => 'İptal edildi', 11 => 'Ödeme bekleniyor', 12 => 'Ödeme başarısız', 13 => 'Fraud kontrolü'][(int)$payment->order_status] ?? 'İşleniyor';
                $payment->payment_ip = $payment->order_user_ip ?: '-';
                $payment->payment_port = (string)($info['client_port'] ?? '');
                $cardBin = $info['cardBin'] ?? ($info['binNumber'] ?? '');
                $cardLast4 = $info['cardLast4'] ?? ($info['lastFourDigits'] ?? '');
                $payment->payment_card = !empty($cardBin) || !empty($cardLast4) ? ($cardBin ?: '******') . ' **** ' . ($cardLast4 ?: '****') : '-';
                $payment->payment_cardholder = $info['cardHolderName'] ?? '-';
                $payment->payment_card_expiry = $info['cardExpiry'] ?? '-';
                $payment->payment_sender = '-';
                $payment->payment_note = '-';
                $payment->payment_method = [2 => 'Cüzdan', 3 => 'Havale / EFT', 4 => 'Kredi Kartı', 5 => 'Mobil Ödeme'][(int)$payment->order_payment_type] ?? '';
                $payment->payment_bank = (string)($payment->eft_bank_name ?? '');
                $payment->payment_iban = (string)($payment->eft_bank_iban ?? '');
                $payment->payment_bank_owner = (string)($payment->eft_bank_owner ?? '');
                $payment->payment_vomsis_id = (string)($payment->order_vomsis_id ?? '');
                $payment->payment_approved_at = '';
                $payment->payment_approved_by = (string)($payment->admin_name ?? '');
                $payment->payment_info = $info;

                // Sipariş detayları: paket ise tüm parçalar, tek ürünse kendisi
                $snapshot = is_array($info['pricing_snapshot'] ?? null) ? $info['pricing_snapshot'] : [];
                $bundleRef = trim((string)($snapshot['bundle_reference'] ?? ''));
                $statusLabels = [0 => 'Yeni sipariş', 1 => 'Teslim edildi', 2 => 'İptal edildi', 3 => 'İşleniyor', 4 => 'Satıcı onayladı', 5 => 'Tedarikçi iptal etti', 9 => 'Cron işlemi', 11 => 'Ödeme bekleniyor', 12 => 'Ödeme başarısız', 13 => 'Fraud kontrolü', 14 => 'Tedarikçi değiştir', 15 => 'Teslimat bekleniyor'];
                $groupRows = $this->db->select('o.order_id, o.order_status, o.order_quantity, o.order_sale_price, o.order_require_data, o.order_payment_info, o.order_reseller_order_id, p.product_name')
                    ->from('orders as o')
                    ->join('products as p', 'p.product_id = o.order_product_id', 'left')
                    ->where('o.order_reseller_id', (int)$payment->order_reseller_id)
                    ->where('o.order_created_at', $payment->order_created_at)
                    ->order_by('o.order_id', 'ASC')->get()->result();
                $items = [];
                foreach ($groupRows as $groupRow) {
                    if ($bundleRef !== '') {
                        $rowInfo = json_decode((string)$groupRow->order_payment_info, true);
                        $rowSnapshot = is_array($rowInfo) ? ($rowInfo['pricing_snapshot'] ?? []) : [];
                        if (($rowSnapshot['bundle_reference'] ?? '') !== $bundleRef) continue;
                    } elseif ((int)$groupRow->order_id !== (int)$payment->order_id) {
                        continue;
                    }
                    $require = json_decode((string)$groupRow->order_require_data, true);
                    $items[] = [
                        'order_id' => (int)$groupRow->order_id,
                        'product' => (string)$groupRow->product_name,
                        'quantity' => (int)$groupRow->order_quantity,
                        'amount' => (float)$groupRow->order_sale_price,
                        'status' => $statusLabels[(int)$groupRow->order_status] ?? 'Bilinmiyor',
                        'supplier_order_id' => (string)$groupRow->order_reseller_order_id,
                        'require' => is_array($require) ? $require : [],
                    ];
                }
                $payment->order_items = $items;
                $payment->order_bundle_name = (string)($snapshot['bundle_product_name'] ?? '');
                $payment->order_status_label = $statusLabels[(int)$payment->order_status] ?? 'Bilinmiyor';
            }
        }

        if (!$payment) {
            show_404();
            return;
        }

        $payment->payment_source_key = $source;
        $payment->payment_source_id = $id;
        $payment->payment_refund_remaining = 0.0;
        $payment->payment_refundable = false;
        if ($this->db->table_exists('payment_refunds')) {
            $refundedQuery = $this->db->select_sum('refund_amount')->where_in('refund_status', ['pending', 'completed']);
            $refundedQuery->where($source === 'balance' ? 'refund_balance_id' : 'refund_order_id', $id);
            $alreadyRefunded = (float)($refundedQuery->get('payment_refunds')->row()->refund_amount ?? 0);
            $payment->payment_refund_remaining = max(0, round((float)$payment->payment_amount - $alreadyRefunded, 2));
            $statusOk = $source === 'balance'
                ? (int)$payment->user_balance_status === 1 && empty($payment->user_balance_order_id)
                : !in_array((int)$payment->order_status, [2, 11, 12], true);
            $payment->payment_refundable = $statusOk && $payment->payment_refund_remaining > 0;
        }

        if ($this->input->get('format') === 'json') {
            return json($payment);
        }

        $data = $this->data;
        $data['payment'] = $payment;
        $data['payment_source_key'] = $source;
        $data['payment_source_id'] = $id;
        $this->template->content->view('payment-detail', $data);
        $this->template->publish();
    }

    public function vomsisTransactionsAjax()
    {
        $draw = (int)$this->input->post('draw');
        $start = max(0, (int)$this->input->post('start'));
        $length = min(500, max(1, (int)$this->input->post('length')));
        $search = trim((string)$this->input->post('vomsis_search'));
        $match = trim((string)$this->input->post('vomsis_match'));
        $bank = trim((string)$this->input->post('vomsis_bank'));
        $startDate = trim((string)$this->input->post('startDate'));
        $endDate = trim((string)$this->input->post('endDate'));

        $begin = $startDate !== '' ? date('d-m-Y 00:00:00', strtotime($startDate)) : date('d-m-Y H:i:s', strtotime('-24 hours'));
        $end = $endDate !== '' ? date('d-m-Y 23:59:59', strtotime($endDate)) : date('d-m-Y H:i:s');
        $this->load->library('Vomsis');
        $response = $this->vomsis->vomsisTransactions($begin, $end);
        if (!is_array($response)) {
            return json(['draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Vomsis yanıtı okunamadı.']);
        }
        $transactions = is_array($response['transactions'] ?? null) ? $response['transactions'] : [];
        $paymentIds = array_values(array_filter(array_map('strval', array_column($transactions, 'id'))));
        $matchedBalances = [];
        if ($paymentIds && $this->db->field_exists('user_balance_vomsis_payment_id', 'user_balance')) {
            $matchedBalances = array_flip(array_map('strval', array_column($this->db->select('user_balance_vomsis_payment_id')->where_in('user_balance_vomsis_payment_id', $paymentIds)->get('user_balance')->result_array(), 'user_balance_vomsis_payment_id')));
        }

        $rows = [];
        foreach ($transactions as $transaction) {
            $id = (string)($transaction['id'] ?? '');
            $description = (string)($transaction['description'] ?? $transaction['desc'] ?? '');
            $account = is_array($transaction['account'] ?? null) ? $transaction['account'] : [];
            $accountBank = is_array($account['bank'] ?? null) ? $account['bank'] : [];
            $bankName = (string)($accountBank['bank_name'] ?? $accountBank['bank_title'] ?? $transaction['bank_name'] ?? '-');
            $amount = (float)($transaction['amount'] ?? $transaction['transaction_amount'] ?? $transaction['credit'] ?? 0);
            $createdAt = (string)($transaction['created_at'] ?? $transaction['transaction_date'] ?? $transaction['date'] ?? '');
            $isBalance = $id !== '' && isset($matchedBalances[$id]);
            $isOrder = false;
            if (!$isBalance && $id !== '') {
                $isOrder = (bool)$this->db->group_start()->where('order_vomsis_id', $id)->or_like('order_payment_info', '"vomsis_payment_id":"' . $this->db->escape_like_str($id) . '"', 'both')->group_end()->count_all_results('orders');
            }
            $matchName = $isBalance ? 'Bakiye ile eşleşti' : ($isOrder ? 'Sipariş ile eşleşti' : 'Eşleşmemiş');

            if ($search !== '' && stripos($id . ' ' . $description . ' ' . $bankName, $search) === false) continue;
            if ($bank !== '' && stripos($bankName, $bank) === false) continue;
            if ($match === 'unmatched' && ($isBalance || $isOrder)) continue;
            if ($match === 'balance' && !$isBalance) continue;
            if ($match === 'order' && !$isOrder) continue;
            $rows[] = compact('id', 'description', 'bankName', 'amount', 'createdAt', 'matchName', 'isBalance', 'isOrder');
        }

        usort($rows, static function ($left, $right) { return strcmp($right['createdAt'], $left['createdAt']); });
        $total = count($rows);
        $data = [];
        foreach (array_slice($rows, $start, $length) as $row) {
            $action = '<button type="button" class="btn btn-light-primary btn-sm vomsis-match-btn" data-payment-id="' . html_escape($row['id']) . '" data-description="' . html_escape($row['description']) . '" data-amount="' . html_escape((string)$row['amount']) . '" data-bank="' . html_escape($row['bankName']) . '" data-date="' . html_escape($row['createdAt']) . '"><i class="ki-outline ki-link"></i> Eşleştir</button>';
            $data[] = [
                '<span class="fw-bold">' . html_escape($row['id'] ?: '-') . '</span>',
                html_escape($row['description'] ?: '-'),
                html_escape($row['bankName']),
                number_format($row['amount'], 2, ',', '.') . ' TL',
                html_escape($row['createdAt'] ?: '-'),
                '<span class="badge badge-light-' . ($row['isBalance'] || $row['isOrder'] ? 'success' : 'warning') . '">' . html_escape($row['matchName']) . '</span>',
                $row['isBalance'] || $row['isOrder'] ? '-' : $action,
            ];
        }
        return json(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
    }

    public function matchVomsisPayment()
    {
        $paymentId = trim((string)$this->input->post('payment_id'));
        $target = trim((string)$this->input->post('target'));
        $targetId = (int)$this->input->post('target_id');
        if ($paymentId === '' || !ctype_digit($paymentId) || !in_array($target, ['balance', 'order'], true) || $targetId <= 0) return json(['status' => 'error', 'message' => 'Geçersiz eşleştirme bilgisi']);

        // Cron ve diğer adminlerle aynı Vomsis işlemi için karşılıklı dışlama
        $vomsisLockName = 'vomsis_payment_' . $paymentId;
        $vomsisLock = $this->db->query('SELECT GET_LOCK(?, 0) AS acquired', [$vomsisLockName])->row();
        if (!$vomsisLock || (int)$vomsisLock->acquired !== 1) return json(['status' => 'error', 'message' => 'Bu Vomsis ödemesi şu anda başka bir işlem tarafından eşleştiriliyor.']);
        $lockDb = $this->db;
        register_shutdown_function(function () use ($lockDb, $vomsisLockName) { @$lockDb->query('SELECT RELEASE_LOCK(?)', [$vomsisLockName]); });

        $transaction = $this->fetchVomsisTransaction($paymentId, (string)$this->input->post('payment_date'));
        if (!$transaction) return json(['status' => 'error', 'message' => 'Vomsis ödemesi Vomsis kayıtlarında bulunamadı. Tarih aralığını kontrol edip listeyi yenileyin.']);
        $transactionAmount = (float)($transaction['amount'] ?? $transaction['transaction_amount'] ?? $transaction['credit'] ?? 0);

        if ($target === 'balance') {
            $balance = $this->db->where('user_balance_id', $targetId)->get('user_balance')->row();
            if (!$balance || (int)$balance->user_balance_status !== 0) return json(['status' => 'error', 'message' => 'Bakiye talebi bulunamadı veya zaten işlendi']);
            if ($transactionAmount <= 0 || abs((float)$balance->user_balance_amount - $transactionAmount) > 0.01) return json(['status' => 'error', 'message' => 'Vomsis tutarı bakiye talebiyle eşleşmiyor.']);
            $existingBalance = $this->db->where('user_balance_vomsis_payment_id', $paymentId)->get('user_balance')->row();
            $existingOrder = $this->db->group_start()->where('order_vomsis_id', $paymentId)->or_like('order_payment_info', '"vomsis_payment_id":"' . $this->db->escape_like_str($paymentId) . '"', 'both')->group_end()->get('orders')->row();
            if ($existingBalance || $existingOrder) return json(['status' => 'error', 'message' => 'Bu Vomsis ödemesi zaten eşleştirilmiş.']);
            $bankName = (string)($transaction['account']['bank']['bank_name'] ?? '');
            $bank = $this->db->group_start()->where('bank_name', $bankName)->or_where('bank_title', $bankName)->group_end()->get('banks')->row();
            $update = ['user_balance_status' => 1, 'user_balance_vomsis_payment_id' => $paymentId, 'user_balance_change_date' => date('Y-m-d H:i:s'), 'user_balance_admin_id' => (int)$this->data['user']['admin_id']];
            if ($this->db->field_exists('user_balance_approval_ip', 'user_balance')) $update['user_balance_approval_ip'] = getIp();
            if ($this->db->field_exists('user_balance_approval_port', 'user_balance')) $update['user_balance_approval_port'] = (function_exists('getClientPort') ? getClientPort() : (int)($_SERVER['REMOTE_PORT'] ?? 0));
            if ($bank) $update['user_balance_bank_id'] = (int)$bank->bank_id;
            $this->db->trans_start();
            $this->db->where('user_balance_id', $targetId)->where('user_balance_status', 0)->where('user_balance_vomsis_payment_id IS NULL', null, false)->update('user_balance', $update);
            $this->db->trans_complete();
            if (!$this->db->trans_status() || $this->db->affected_rows() !== 1) return json(['status' => 'error', 'message' => 'Bakiye talebi başka bir işlem tarafından eşleştirilmiş olabilir.']);
        } else {
            $order = $this->db->where('order_id', $targetId)->where('order_payment_type', 3)->where_in('order_status', [11, 12])->get('orders')->row();
            if (!$order) return json(['status' => 'error', 'message' => 'Havale / EFT ödeme bekleyen sipariş bulunamadı']);
            $groupOrderIds = [(int)$order->order_id];
            $expectedAmount = (float)$order->order_pay_price;
            if (trim((string)$order->order_eft_id) !== '') {
                $this->db->select('order_id, order_pay_price')->where('order_eft_id', $order->order_eft_id)->where('order_payment_type', 3)->where_in('order_status', [11, 12]);
                if ($order->order_eft_bank_id !== null) $this->db->where('order_eft_bank_id', $order->order_eft_bank_id);
                $groupOrders = $this->db->get('orders')->result();
                $groupOrderIds = array_map(static function ($row) { return (int)$row->order_id; }, $groupOrders);
                $expectedAmount = array_sum(array_map(static function ($row) { return (float)$row->order_pay_price; }, $groupOrders));
            }
            if ($transactionAmount <= 0 || abs($expectedAmount - $transactionAmount) > 0.01) return json(['status' => 'error', 'message' => 'Vomsis tutarı sipariş tutarıyla eşleşmiyor.']);
            $existingBalance = $this->db->where('user_balance_vomsis_payment_id', $paymentId)->get('user_balance')->row();
            $existingOrder = $this->db->group_start()->where('order_vomsis_id', $paymentId)->or_like('order_payment_info', '"vomsis_payment_id":"' . $this->db->escape_like_str($paymentId) . '"', 'both')->group_end()->get('orders')->row();
            if ($existingBalance || $existingOrder) return json(['status' => 'error', 'message' => 'Bu Vomsis ödemesi zaten eşleştirilmiş.']);
            $info = json_decode((string)$order->order_payment_info, true);
            $info = is_array($info) ? $info : [];
            $info['vomsis_payment_id'] = $paymentId;
            $info['vomsis_transaction'] = $transaction;
            $this->db->trans_begin();
            $this->db->where_in('order_id', $groupOrderIds)->where_in('order_status', [11, 12])->where('order_vomsis_id IS NULL', null, false)->update('orders', [
                'order_status' => 0,
                'order_vomsis_id' => $paymentId,
                'order_payment_info' => json_encode($info, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            $updatedOrders = $this->db->affected_rows();
            if ($this->db->trans_status() === false || $updatedOrders !== count($groupOrderIds)) {
                $this->db->trans_rollback();
                return json(['status' => 'error', 'message' => 'Sipariş eşleştirilemedi veya başka bir işlem tarafından güncellenmiş olabilir.']);
            }
            $this->db->trans_commit();
        }
        return json(['status' => 'success', 'message' => 'Vomsis ödemesi eşleştirildi']);
    }

    // Vomsis işlemini kimliğiyle API'den doğrular; tarih ipucu etrafında ve giderek genişleyen aralıklarla arar.
    private function fetchVomsisTransaction($paymentId, $hintDate = '')
    {
        $this->load->library('Vomsis');
        $windows = [];
        $hintTs = $hintDate !== '' ? strtotime($hintDate) : false;
        if ($hintTs) {
            $windows[] = [strtotime('-1 day', $hintTs), strtotime('+2 days', $hintTs)];
        }
        $now = time();
        foreach (['-24 hours', '-7 days', '-30 days'] as $offset) {
            $windows[] = [strtotime($offset, $now), $now];
        }
        foreach ($windows as $window) {
            $response = $this->vomsis->vomsisTransactions(date('d-m-Y H:i:s', $window[0]), date('d-m-Y H:i:s', $window[1]));
            foreach ((is_array($response['transactions'] ?? null) ? $response['transactions'] : []) as $item) {
                if ((string)($item['id'] ?? '') === (string)$paymentId) {
                    return $item;
                }
            }
        }
        return null;
    }

    // Vomsis işleminin sipariş/bakiye ile daha önce eşleşip eşleşmediğini kontrol eder.
    private function vomsisPaymentAlreadyUsed($paymentId)
    {
        $existingBalance = $this->db->where('user_balance_vomsis_payment_id', $paymentId)->get('user_balance')->row();
        $existingOrder = $this->db->group_start()->where('order_vomsis_id', $paymentId)->or_like('order_payment_info', '"vomsis_payment_id":"' . $this->db->escape_like_str($paymentId) . '"', 'both')->group_end()->get('orders')->row();
        return (bool)($existingBalance || $existingOrder);
    }

    // Admin: bildirimi olmayan Vomsis ödemesi için müşteri arama
    public function vomsisUserSearch()
    {
        $term = trim((string)$this->input->post('term'));
        if (mb_strlen($term) < 3) return json(['status' => 'error', 'message' => 'En az 3 karakter girin.']);
        $this->db->select('user_id, user_name, user_surname, user_email, user_phone, user_status, user_bank_referance_number')
            ->from('users')
            ->group_start()
                ->where('user_email', $term)
                ->or_where('user_bank_referance_number', $term)
                ->or_like('user_email', $term, 'after')
                ->or_like('user_name', $term)
                ->or_like('user_surname', $term)
                ->or_like('user_phone', $term)
            ->group_end()
            ->limit(10);
        $users = $this->db->get()->result();
        $result = [];
        foreach ($users as $user) {
            $pending = $this->db->where('user_balance_user_id', (int)$user->user_id)->where('user_balance_status', 0)
                ->where('user_balance_order_id IS NULL', null, false)->get('user_balance')->result();
            $result[] = [
                'id' => (int)$user->user_id,
                'name' => trim($user->user_name . ' ' . $user->user_surname),
                'email' => (string)$user->user_email,
                'phone' => (string)$user->user_phone,
                'active' => (int)$user->user_status === 1,
                'pending_requests' => array_map(static function ($row) {
                    return ['id' => (int)$row->user_balance_id, 'amount' => (float)$row->user_balance_amount, 'created_at' => (string)$row->user_balance_created_at];
                }, $pending),
            ];
        }
        return json(['status' => 'success', 'users' => $result]);
    }

    // Admin: ödeme bildirimi yapılmamış Vomsis havalesini doğrudan kullanıcının bakiyesine ekler.
    public function creditVomsisToUser()
    {
        $paymentId = trim((string)$this->input->post('payment_id'));
        $userId = (int)$this->input->post('user_id');
        if ($paymentId === '' || !ctype_digit($paymentId) || $userId <= 0) return json(['status' => 'error', 'message' => 'Geçersiz bilgi.'], 422);
        $adminId = (int)($this->data['user']['admin_id'] ?? 0);

        $lockName = 'vomsis_payment_' . $paymentId;
        $lock = $this->db->query('SELECT GET_LOCK(?, 0) AS acquired', [$lockName])->row();
        if (!$lock || (int)$lock->acquired !== 1) return json(['status' => 'error', 'message' => 'Bu Vomsis ödemesi şu anda başka bir işlem tarafından kullanılıyor.'], 409);
        $lockDb = $this->db;
        register_shutdown_function(function () use ($lockDb, $lockName) { @$lockDb->query('SELECT RELEASE_LOCK(?)', [$lockName]); });

        // Tutar ve açıklama istemciden değil, Vomsis'ten alınır.
        $transaction = $this->fetchVomsisTransaction($paymentId, (string)$this->input->post('payment_date'));
        if (!$transaction) return json(['status' => 'error', 'message' => 'Vomsis ödemesi Vomsis kayıtlarında bulunamadı.'], 404);
        $amount = round((float)($transaction['amount'] ?? $transaction['transaction_amount'] ?? $transaction['credit'] ?? 0), 2);
        if ($amount <= 0) return json(['status' => 'error', 'message' => 'Vomsis tutarı geçersiz.'], 422);

        if ($this->vomsisPaymentAlreadyUsed($paymentId)) return json(['status' => 'error', 'message' => 'Bu Vomsis ödemesi zaten eşleştirilmiş.'], 409);

        $user = $this->db->where('user_id', $userId)->get('users')->row();
        if (!$user || (int)$user->user_status !== 1) return json(['status' => 'error', 'message' => 'Kullanıcı bulunamadı veya aktif değil.'], 404);

        // Aynı tutarlı bekleyen bildirimi varsa çift yükleme riski vardır; o bildirimle eşleştirilmelidir.
        $samePending = $this->db->where('user_balance_user_id', $userId)->where('user_balance_status', 0)
            ->where('user_balance_order_id IS NULL', null, false)
            ->where('ABS(user_balance_amount - ' . (float)$amount . ') <', 0.01, false)->get('user_balance')->row();
        if ($samePending) return json(['status' => 'error', 'message' => 'Bu kullanıcının aynı tutarlı bekleyen bildirimi var (#' . (int)$samePending->user_balance_id . '). Bakiye talebiyle eşleştirin.'], 409);

        $bankName = (string)($transaction['account']['bank']['bank_name'] ?? '');
        $bank = $bankName !== '' ? $this->db->group_start()->where('bank_name', $bankName)->or_where('bank_title', $bankName)->group_end()->get('banks')->row() : null;
        $now = date('Y-m-d H:i:s');
        $description = mb_substr(trim((string)($transaction['description'] ?? '')), 0, 200);

        $insert = [
            'user_balance_user_id' => $userId,
            'user_balance_user_name' => trim($user->user_name . ' ' . $user->user_surname),
            'user_balance_amount' => $amount,
            'user_balance_type' => 2,
            'user_balance_status' => 1,
            'user_balance_vomsis_payment_id' => $paymentId,
            'user_balance_reference_no' => !empty($user->user_bank_referance_number) ? (string)$user->user_bank_referance_number : 'vomsis_' . $paymentId,
            'user_balance_note' => 'Vomsis doğrudan yükleme (bildirimsiz): ' . $description,
            'user_balance_admin_id' => $adminId,
            'user_balance_created_at' => $now,
            'user_balance_payment_date' => date('Y-m-d'),
            'user_balance_change_date' => $now,
        ];
        if ($bank) $insert['user_balance_bank_id'] = (int)$bank->bank_id;
        if ($this->db->field_exists('user_balance_approval_ip', 'user_balance')) $insert['user_balance_approval_ip'] = getIp();
        if ($this->db->field_exists('user_balance_approval_port', 'user_balance')) $insert['user_balance_approval_port'] = (function_exists('getClientPort') ? getClientPort() : (int)($_SERVER['REMOTE_PORT'] ?? 0));

        $this->db->trans_begin();
        $previousDebug = $this->db->db_debug;
        $this->db->db_debug = false;
        $inserted = $this->db->insert('user_balance', $insert);
        $insertError = $this->db->error();
        $newId = $inserted ? (int)$this->db->insert_id() : 0;
        $this->db->db_debug = $previousDebug;
        if (!$inserted || $newId <= 0 || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            log_message('error', 'creditVomsisToUser insert failed. Payment: ' . $paymentId . ', User: ' . $userId . ', DB: ' . json_encode($insertError));
            return json(['status' => 'error', 'message' => 'Bakiye kaydı oluşturulamadı' . (!empty($insertError['message']) ? ': ' . $insertError['message'] : '.')], 500);
        }
        $this->db->trans_commit();

        // Kayıt gerçekten yazıldı mı ve kullanıcının güncel bakiyesi ne, commit sonrası doğrulanır.
        $saved = $this->db->where('user_balance_id', $newId)->where('user_balance_status', 1)
            ->where('user_balance_vomsis_payment_id', $paymentId)->get('user_balance')->row();
        if (!$saved) {
            return json(['status' => 'error', 'message' => 'Bakiye kaydı doğrulanamadı (#' . $newId . '). Lütfen kullanıcı bakiyesini ve Ödemeler listesini kontrol edin.'], 500);
        }
        $balanceRow = $this->db->select('COALESCE(SUM(CASE WHEN user_balance_status = 1 THEN user_balance_amount WHEN user_balance_status = 3 THEN -user_balance_amount ELSE 0 END), 0) AS available', false)
            ->where('user_balance_user_id', $userId)->where('user_balance_type', 2)->get('user_balance')->row();

        // Log başarısız olsa bile bakiye işlemi etkilenmez.
        $this->db->db_debug = false;
        $this->db->insert('logs', [
            'log_controller' => 'creditVomsisToUser',
            'log_message' => 'Vomsis #' . $paymentId . ' (' . number_format($amount, 2, '.', '') . ' TL) bildirimsiz olarak kullanıcı #' . $userId . ' bakiyesine eklendi. Bakiye kaydı #' . $newId . ', admin #' . $adminId . '.',
            'log_error_code' => 'VomsisDirectCredit',
            'log_order_id' => null,
        ]);
        $this->db->db_debug = $previousDebug;

        return json(['status' => 'success', 'message' => number_format($amount, 2, ',', '.') . ' TL ' . trim($user->user_name . ' ' . $user->user_surname) . ' kullanıcısının bakiyesine eklendi (kayıt #' . $newId . '). Güncel harcanabilir bakiye: ' . number_format((float)($balanceRow->available ?? 0), 2, ',', '.') . ' TL.']);
    }

    public function vomsisMatchCandidates()
    {
        $paymentId = trim((string)$this->input->post('payment_id'));
        $description = trim((string)$this->input->post('description'));
        $amount = (float)$this->input->post('amount');
        $bankName = trim((string)$this->input->post('bank'));
        $manualEmail = strtolower(trim((string)$this->input->post('email')));
        if ($manualEmail !== '' && !filter_var($manualEmail, FILTER_VALIDATE_EMAIL)) return json(['status' => 'error', 'message' => 'Geçerli bir e-posta adresi girin.']);
        if ($paymentId === '') return json(['status' => 'error', 'message' => 'Vomsis işlem numarası bulunamadı.']);

        preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $description, $emailMatch);
        $transactionEmail = $manualEmail !== '' ? $manualEmail : strtolower(trim((string)($emailMatch[0] ?? '')));
        $normalize = static function ($value) {
            $value = trim((string)$value);
            if (function_exists('mb_strtolower')) $value = mb_strtolower($value, 'UTF-8');
            return preg_replace('/\s+/', ' ', $value);
        };
        $normalizedBank = $normalize($bankName);
        $digits = static function ($value) { return preg_replace('/\D+/', '', (string)$value); };
        $descriptionDigits = $digits($description);
        $referenceMatches = static function ($reference) use ($description, $descriptionDigits, $digits) {
            if ($reference === '' || $description === '') return false;
            if (stripos($description, $reference) !== false) return true;
            $referenceDigits = $digits($reference);
            return strlen($referenceDigits) >= 8 && strpos($descriptionDigits, $referenceDigits) !== false;
        };
        $emailMatches = static function ($email) use ($transactionEmail) {
            return $transactionEmail !== '' && $email !== '' && strtolower(trim($email)) === $transactionEmail;
        };
        $amountMatches = static function ($value) use ($amount) {
            return $amount > 0 && abs((float)$value - $amount) < 0.01;
        };

        $balances = $this->db
            ->select('ub.user_balance_id, ub.user_balance_user_id, ub.user_balance_amount, ub.user_balance_reference_no, ub.user_balance_created_at, u.user_name, u.user_email, b.bank_name')
            ->from('user_balance as ub')
            ->join('users as u', 'u.user_id = ub.user_balance_user_id', 'left')
            ->join('banks as b', 'b.bank_id = ub.user_balance_bank_id', 'left')
            ->where('ub.user_balance_status', 0)
            ->where('ub.user_balance_order_id IS NULL', null, false)
            ->where('ub.user_balance_vomsis_payment_id IS NULL', null, false)
            ->where('(ub.user_balance_bank_id IS NULL OR ub.user_balance_bank_id NOT IN (4, 5))', null, false)
            ->order_by('ub.user_balance_created_at', 'DESC')
            ->limit(500)
            ->get()->result();

        $balanceCandidates = [];
        foreach ($balances as $balance) {
            $reference = trim((string)$balance->user_balance_reference_no);
            $amountMatch = $amountMatches($balance->user_balance_amount);
            $referenceMatch = $referenceMatches($reference);
            $emailMatch = $emailMatches($balance->user_email);
            $bankMatch = $normalizedBank !== '' && stripos($normalize($balance->bank_name), $normalizedBank) !== false;
            $score = ($referenceMatch ? 100 : 0) + ($emailMatch ? 80 : 0) + ($amountMatch ? 50 : 0) + ($bankMatch ? 25 : 0);
            if (!$amountMatch) continue;
            if ($manualEmail !== '' && !$emailMatch) continue;
            $balanceCandidates[] = [
                'id' => (int)$balance->user_balance_id,
                'user_id' => (int)$balance->user_balance_user_id,
                'user_name' => (string)($balance->user_name ?: '-'),
                'user_email' => (string)($balance->user_email ?: ''),
                'amount' => (float)$balance->user_balance_amount,
                'reference' => $reference,
                'bank' => (string)($balance->bank_name ?: '-'),
                'created_at' => (string)$balance->user_balance_created_at,
                'score' => $score,
                'reference_match' => (bool)$referenceMatch,
                'email_match' => (bool)$emailMatch,
            ];
        }

        $orders = $this->db
            ->select('o.order_id, o.order_reseller_id, o.order_pay_price, o.order_eft_id, o.order_eft_bank_id, o.order_created_at, u.user_name, u.user_email')
            ->from('orders as o')
            ->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
            ->where('o.order_payment_type', 3)
            ->where_in('o.order_status', [11, 12])
            ->where('o.order_vomsis_id IS NULL', null, false)
            ->where('o.order_eft_id IS NOT NULL', null, false)
            ->order_by('o.order_created_at', 'DESC')
            ->limit(500)
            ->get()->result();

        $groupTotals = [];
        foreach ($orders as $order) {
            $groupKey = trim((string)$order->order_eft_id) . '|' . (string)$order->order_eft_bank_id;
            $groupTotals[$groupKey] = ($groupTotals[$groupKey] ?? 0) + (float)$order->order_pay_price;
        }

        $orderCandidates = [];
        $seenGroups = [];
        foreach ($orders as $order) {
            $reference = trim((string)$order->order_eft_id);
            $groupKey = $reference . '|' . (string)$order->order_eft_bank_id;
            if (isset($seenGroups[$groupKey])) continue;
            $groupAmount = $groupTotals[$groupKey];
            $amountMatch = $amountMatches($groupAmount);
            $referenceMatch = $referenceMatches($reference);
            $emailMatch = $emailMatches($order->user_email);
            $score = ($referenceMatch ? 100 : 0) + ($emailMatch ? 80 : 0) + ($amountMatch ? 50 : 0);
            if (!$amountMatch) continue;
            if ($manualEmail !== '' && !$emailMatch) continue;
            $seenGroups[$groupKey] = true;
            $orderCandidates[] = [
                'id' => (int)$order->order_id,
                'user_id' => (int)$order->order_reseller_id,
                'user_name' => (string)($order->user_name ?: '-'),
                'user_email' => (string)($order->user_email ?: ''),
                'amount' => $groupAmount,
                'reference' => $reference,
                'created_at' => (string)$order->order_created_at,
                'score' => $score,
                'reference_match' => (bool)$referenceMatch,
                'email_match' => (bool)$emailMatch,
            ];
        }

        usort($balanceCandidates, static function ($left, $right) { return $right['score'] <=> $left['score']; });
        usort($orderCandidates, static function ($left, $right) { return $right['score'] <=> $left['score']; });
        return json(['status' => 'success', 'balances' => array_slice($balanceCandidates, 0, 20), 'orders' => array_slice($orderCandidates, 0, 20)]);
    }

    public function transactions($start, $length, $searchOrderID, $statusArray, $startDate = null, $endDate = null, $reseller_id = null, $onlyBalanceTopups = false, $balanceSearch = '', $balanceBankID = 0)
    {
        $this->db->select('user_balance.*, users.user_id, users.user_name, banks.bank_id, banks.bank_name, admins.admin_id, admins.admin_name')
            ->from('user_balance')
            ->join('users', 'users.user_id = user_balance.user_balance_user_id', 'left')
            ->join('banks', 'banks.bank_id = user_balance.user_balance_bank_id', 'left')
            ->join('admins', 'admins.admin_id = user_balance.user_balance_admin_id', 'left')
            ->where_in('user_balance.user_balance_status', $statusArray)
            ->order_by('user_balance.user_balance_id ', 'DESC')
            ->limit($length, $start);

        if ($onlyBalanceTopups) {
            $this->db->where('user_balance.user_balance_order_id IS NULL', null, false);
        }

        if ($balanceSearch !== '') {
            $this->db->group_start()
                ->like('users.user_name', $balanceSearch)
                ->or_like('user_balance.user_balance_reference_no', $balanceSearch)
                ->or_like('user_balance.user_balance_note', $balanceSearch)
                ->group_end();
        }
        if ($balanceBankID > 0) {
            $this->db->where('user_balance.user_balance_bank_id', $balanceBankID);
        }

        if (!empty($searchOrderID)) {
            $this->db->where('user_balance.user_balance_id', $searchOrderID);
        }

        if (!empty($startDate)) {
            $this->db->where('user_balance.user_balance_created_at >=', $startDate);
        }
        if (!empty($endDate)) {
            $this->db->where('user_balance.user_balance_created_at <=', $endDate);
        }
        if(!empty($reseller_id)) {
            $this->db->where('user_balance.user_balance_user_id', $reseller_id);
        }

        return $this->db->get()->result();
    }

    public function allTransactionsCount($searchOrderID, $statusArray = [0], $startDate = null, $endDate = null, $reseller_id = null, $onlyBalanceTopups = false, $balanceSearch = '', $balanceBankID = 0)
    {
        $this->db->select('user_balance.user_balance_id')
            ->from('user_balance')
            ->join('users', 'users.user_id = user_balance.user_balance_user_id', 'left')
            ->join('banks', 'banks.bank_id = user_balance.user_balance_bank_id', 'left')
            ->where_in('user_balance.user_balance_status', $statusArray);

        if ($onlyBalanceTopups) {
            $this->db->where('user_balance.user_balance_order_id IS NULL', null, false);
        }

        if ($balanceSearch !== '') {
            $this->db->group_start()
                ->like('users.user_name', $balanceSearch)
                ->or_like('user_balance.user_balance_reference_no', $balanceSearch)
                ->or_like('user_balance.user_balance_note', $balanceSearch)
                ->group_end();
        }
        if ($balanceBankID > 0) {
            $this->db->where('user_balance.user_balance_bank_id', $balanceBankID);
        }

        if (!empty($searchOrderID)) {
            $this->db->where('user_balance.user_balance_id', $searchOrderID);
        }

        if (!empty($startDate)) {
            $this->db->where('user_balance.user_balance_created_at >=', $startDate);
        }
        if (!empty($endDate)) {
            $this->db->where('user_balance.user_balance_created_at <=', $endDate);
        }
        if(!empty($reseller_id)) {
            $this->db->where('user_balance.user_balance_user_id', $reseller_id);
        }
        $total = $this->db->count_all_results();
        return $total;
    }

    public function changeStatus()
    {
        log_message('warning', 'Blocked administrator payment status change attempt.');
        return json(['status' => 'error', 'message' => 'Ödeme durumu yalnızca ilgili ödeme sisteminden gelen doğrulanmış sonuçla güncellenebilir.'], 403);
    }
}
