<?php defined('BASEPATH') or exit('No direct script access allowed');
class Sayfalar_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function cashTotal($type, $date = null)
	{
		$this->db->select('SUM(amount) as total');
		$this->db->from('user_vallet');
		$this->db->where('type', $type);
		$this->db->where('payment_status', 1);
		if ($date) {
			$this->db->where('DATE(created_at)', $date);
		}
		$return = $this->db->get()->row();
		return $return->total ? $return->total : 0;
	}

	public function getUsers()
	{
		$this->db->select('users.id, users.name, users.surname, users.email, users.phone, users.status, users.created_at, 
						   (SELECT SUM(amount) FROM user_vallet WHERE userID = users.id AND type = 1 AND payment_status = 1) as totalYukleme,
						   (SELECT SUM(amount) FROM user_vallet WHERE userID = users.id AND type = 2 AND payment_status = 1) as totalSatis');
		$this->db->from('users');
		$this->db->order_by('users.id', 'asc');
		$users = $this->db->get()->result();

		foreach ($users as $user) {
			$user->balance = $user->totalYukleme - $user->totalSatis;
			unset($user->totalYukleme);
			unset($user->totalSatis);
		}
		return $users;
	}

	public function getUsersTotalBalance()
	{
		$this->db->select('users.id, users.name, users.surname, users.email, users.phone, users.status, users.created_at, 
						   (SELECT SUM(amount) FROM user_vallet WHERE userID = users.id AND type = 1 AND payment_status = 1) as totalYukleme,
						   (SELECT SUM(amount) FROM user_vallet WHERE userID = users.id AND type = 2 AND payment_status = 1) as totalSatis');
		$this->db->from('users');
		$this->db->order_by('users.id', 'asc');
		$users = $this->db->get()->result();
		$totalBalance = 0;
		$eksikBakiyeliKullaniciSayisi = 0;
		foreach ($users as $user) {
			// $bakiye = $user->totalYukleme - $user->totalSatis;
			// echo 'Kullanıcı ID: ' . $user->id . '<br>';
			// echo 'Ad Soyad: ' . $user->name . ' ' . $user->surname . '<br>';
			// echo 'Yükleme: ' . $user->totalYukleme . '<br>';
			// echo 'Harcama: ' . $user->totalSatis . '<br>';
			// echo 'Bakiye: ' . $bakiye . '<br>';
			// if ($bakiye < 0) {
			// 	echo '<b>Bakiye Eksi: ' . $bakiye . '</b><br>';
			// 	$eksikBakiyeliKullaniciSayisi++;
			// }
			// echo '-----------------------------<br>';
			$totalBalance += $user->totalYukleme - $user->totalSatis;
		}
		// echo 'Toplam Eksik Bakiyeli Kullanıcı Sayısı: ' . $eksikBakiyeliKullaniciSayisi . '<br>';
		// die;
		return $totalBalance;
	}

	public function epinSellers()
	{
		$this->db->select('sellers.*, 
                       COUNT(epins.id) as totalEpin,
                       SUM(CASE WHEN epins.status = 0 THEN 1 ELSE 0 END) as inactiveEpin,
                       SUM(CASE WHEN epins.status = 1 THEN 1 ELSE 0 END) as activeEpin');
		$this->db->from('sellers');
		$this->db->join('epins', 'sellers.id = epins.selled_to', 'left');
		$this->db->group_by('sellers.id');
		$this->db->order_by('sellers.id', 'asc');
		$sellers = $this->db->get()->result();

		return $sellers;
	}

	public function getUserKpis(array $periods, $trendStart, $trendEnd)
	{
		$userTotals = $this->db
			->select('COUNT(*) AS total_users', false)
			->get('users')->row_array();

		$activeUsers = $this->db
			->select('COUNT(DISTINCT o.order_reseller_id) AS active_users', false)
			->from('orders o')
			->join('users u', 'u.user_id = o.order_reseller_id', 'inner')
			->where('u.user_type', 4)
			->where('o.order_status', 1)
			->where('o.order_created_at >=', date('Y-m-d 00:00:00', strtotime('-29 days')))
			->where('o.order_created_at <', $trendEnd)
			->get()->row_array();

		$balance = $this->db
			->select('
				COALESCE(SUM(CASE WHEN ub.user_balance_status = 1 THEN ub.user_balance_amount ELSE 0 END), 0) -
				COALESCE(SUM(CASE WHEN ub.user_balance_status = 3 THEN ub.user_balance_amount ELSE 0 END), 0) AS total_balance
			', false)
			->from('user_balance ub')
			->join('users u', 'u.user_id = ub.user_balance_user_id', 'inner')
			->where('u.user_type', 4)
			->get()->row_array();

		$periodData = [];
		foreach ($periods as $key => $period) {
			$periodData[$key] = $this->getUserKpiPeriod($period['start'], $period['end']);
		}

		$dailyTrend = $this->db
			->select('DATE(o.order_created_at) AS period_key, COUNT(DISTINCT o.order_reseller_id) AS active_users, COUNT(o.order_id) AS orders, COALESCE(SUM(o.order_sale_price), 0) AS amount', false)
			->from('orders o')
			->join('users u', 'u.user_id = o.order_reseller_id', 'inner')
			->where('u.user_type', 4)
			->where('o.order_status', 1)
			->where('o.order_created_at >=', $trendStart)
			->where('o.order_created_at <', $trendEnd)
			->group_by('period_key')
			->order_by('period_key', 'ASC')
			->get()->result_array();

		$monthlyTrend = $this->db
			->select("DATE_FORMAT(user_created_at, '%Y-%m') AS period_key, COUNT(user_id) AS registrations", false)
			->where('user_type', 4)
			->where('user_created_at >=', date('Y-m-01 00:00:00', strtotime('-5 months')))
			->where('user_created_at <', date('Y-m-01 00:00:00', strtotime('+1 month')))
			->group_by('period_key')
			->order_by('period_key', 'ASC')
			->get('users')->result_array();

		$monthlyTrendByKey = [];
		foreach ($monthlyTrend as $row) {
			$monthlyTrendByKey[(string)$row['period_key']] = (int)$row['registrations'];
		}
		$monthlyTrend = [];
		$monthCursor = new DateTime(date('Y-m-01 00:00:00', strtotime('-5 months')));
		for ($monthIndex = 0; $monthIndex < 6; $monthIndex++) {
			$periodKey = $monthCursor->format('Y-m');
			$monthlyTrend[] = [
				'period_key' => $periodKey,
				'registrations' => $monthlyTrendByKey[$periodKey] ?? 0,
			];
			$monthCursor->modify('+1 month');
		}

		$registrationTrendRows = $this->db
			->select('DATE(user_created_at) AS period_key, COUNT(user_id) AS registrations', false)
			->where('user_created_at >=', date('Y-m-d 00:00:00', strtotime('-29 days')))
			->where('user_created_at <', $trendEnd)
			->group_by('period_key')
			->order_by('period_key', 'ASC')
			->get('users')->result_array();
		$registrationTrendByKey = [];
		foreach ($registrationTrendRows as $row) {
			$registrationTrendByKey[(string)$row['period_key']] = (int)$row['registrations'];
		}
		$registrationTrend = [];
		for ($dayIndex = 29; $dayIndex >= 0; $dayIndex--) {
			$periodKey = date('Y-m-d', strtotime('-' . $dayIndex . ' days'));
			$registrationTrend[] = [
				'period_key' => $periodKey,
				'registrations' => $registrationTrendByKey[$periodKey] ?? 0,
			];
		}

		return [
			'totals' => [
				'total_users' => (int)($userTotals['total_users'] ?? 0),
				'active_users' => (int)($activeUsers['active_users'] ?? 0),
				'total_balance' => (float)($balance['total_balance'] ?? 0),
			],
			'periods' => $periodData,
			'daily_trend' => $dailyTrend,
			'monthly_trend' => $monthlyTrend,
			'registration_trend' => $registrationTrend,
		];
	}

	public function getUserAcquisitionKpis()
	{
		$empty = [
			'available' => false,
			'totals' => ['signups' => 0, 'unknown' => 0, 'paid' => 0, 'campaigns' => 0],
			'sources' => [],
			'campaigns' => [],
			'landing_pages' => [],
		];
		if (!$this->db->table_exists('user_acquisitions')) {
			return $empty;
		}

		$totals = $this->db->select(
			"COUNT(*) AS signups,
			SUM(CASE WHEN acquisition_source = 'direct' OR acquisition_source = '' THEN 1 ELSE 0 END) AS unknown,
			SUM(CASE WHEN acquisition_medium IN ('cpc', 'ppc', 'paid', 'paid_social', 'display', 'affiliate') THEN 1 ELSE 0 END) AS paid,
			SUM(CASE WHEN acquisition_campaign <> '' THEN 1 ELSE 0 END) AS campaigns",
			false
		)->get('user_acquisitions')->row_array();

		$sources = $this->db->select('acquisition_source, acquisition_medium, COUNT(*) AS signups', false)
			->group_by(['acquisition_source', 'acquisition_medium'])
			->order_by('signups', 'DESC')
			->limit(10)
			->get('user_acquisitions')->result_array();

		$campaigns = $this->db->select('acquisition_source, acquisition_medium, acquisition_campaign, COUNT(*) AS signups', false)
			->where('acquisition_campaign <>', '')
			->group_by(['acquisition_source', 'acquisition_medium', 'acquisition_campaign'])
			->order_by('signups', 'DESC')
			->limit(10)
			->get('user_acquisitions')->result_array();

		$landingPages = $this->db->select('acquisition_landing_page, COUNT(*) AS signups', false)
			->group_by('acquisition_landing_page')
			->order_by('signups', 'DESC')
			->limit(10)
			->get('user_acquisitions')->result_array();

		return [
			'available' => true,
			'totals' => [
				'signups' => (int)($totals['signups'] ?? 0),
				'unknown' => (int)($totals['unknown'] ?? 0),
				'paid' => (int)($totals['paid'] ?? 0),
				'campaigns' => (int)($totals['campaigns'] ?? 0),
			],
			'sources' => $sources,
			'campaigns' => $campaigns,
			'landing_pages' => $landingPages,
		];
	}

	private function getUserKpiPeriod($start, $end)
	{
		$registrations = $this->db
			->where('user_type', 4)
			->where('user_created_at >=', $start)
			->where('user_created_at <', $end)
			->count_all_results('users');
		$summary = $this->db
			->select('COUNT(o.order_id) AS orders, COUNT(DISTINCT o.order_reseller_id) AS active_users, COALESCE(SUM(o.order_sale_price), 0) AS amount', false)
			->from('orders o')
			->join('users u', 'u.user_id = o.order_reseller_id', 'inner')
			->where('u.user_type', 4)
			->where('o.order_status', 1)
			->where('o.order_created_at >=', $start)
			->where('o.order_created_at <', $end)
			->get()->row_array();

		$topOrders = $this->getTopUsersByPeriod($start, $end, 'COUNT(o.order_id)', 'orders');
		$topAmount = $this->getTopUsersByPeriod($start, $end, 'SUM(o.order_sale_price)', 'amount');

		return [
			'registrations' => (int)$registrations,
			'orders' => (int)($summary['orders'] ?? 0),
			'active_users' => (int)($summary['active_users'] ?? 0),
			'amount' => (float)($summary['amount'] ?? 0),
			'average_order' => (int)($summary['orders'] ?? 0) > 0 ? (float)$summary['amount'] / (int)$summary['orders'] : 0,
			'top_orders' => $topOrders,
			'top_amount' => $topAmount,
		];
	}

	private function getTopUsersByPeriod($start, $end, $aggregate, $alias)
	{
		return $this->db
			->select("o.order_reseller_id AS user_id, u.user_name, u.user_surname, u.user_email, {$aggregate} AS {$alias}", false)
			->from('orders o')
			->join('users u', 'u.user_id = o.order_reseller_id', 'left')
			->where('o.order_status', 1)
			->where('u.user_type', 4)
			->where('o.order_created_at >=', $start)
			->where('o.order_created_at <', $end)
			->group_by(['o.order_reseller_id', 'u.user_name', 'u.user_surname', 'u.user_email'])
			->order_by($alias, 'DESC')
			->limit(10)
			->get()->result_array();
	}
}
