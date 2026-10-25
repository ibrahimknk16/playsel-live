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
}
