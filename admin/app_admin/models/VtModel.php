<?php defined('BASEPATH') or exit('No direct script access allowed');
class VtModel extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function insert($table, $datas)
	{
		if ($this->db->insert($table, $datas)) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}

	public function getRow($table, $where, $select = '*')
	{
		try {
			$this->db->select($select);
			foreach ($where as $key => $val) {
				$this->db->where($key, $val);
			}
			return $this->db->get($table)->row();
		} catch (Exception $e) {
			return false;
		}
	}

	public function getAll($table = '', $where = [], $orderBy = [], $limit = '')
	{
		$this->db->select('*');
		foreach ($where as $key => $val) {
			$this->db->where($key, $val);
		}
		foreach ($orderBy as $key => $val) {
			$this->db->order_by($key, $val);
		}
		if ($limit != '') {
			$this->db->limit($limit);
		}
		return $this->db->get($table)->result();
	}

	public function getOne($table = '', $where = [], $orderBy = [])
	{
		$this->db->select('*');
		foreach ($where as $key => $val) {
			$this->db->where($key, $val);
		}
		foreach ($orderBy as $key => $val) {
			$this->db->order_by($key, $val);
		}
		return $this->db->get($table)->row();
	}

	public function update($table = '', $datas = [], $where = [])
	{

		foreach ($where as $key => $val) {
			$this->db->where($key, $val);
		}
		return $this->db->update($table, $datas);
	}

	public function getSysSettingsGroup($groupName = '')
	{
		$this->db->select('settingGroup,settingKey,settingVal');
		$this->db->where('settingGroup', $groupName);
		$d = $this->db->get('sysSetting')->result();

		$response = new stdClass();
		foreach ($d as $key) {
			$response->{$key->settingKey} = $key->settingVal;
		}
		return $response;
	}

	public function getSysSettingsGroupMultiple($groupNames = [])
	{
		$this->db->select('settingGroup,settingKey,settingVal');
		$this->db->where_in('settingGroup', $groupNames);
		$d = $this->db->get('sysSetting')->result();

		$response = array();
		foreach ($d as $key) {
			$response[$key->settingGroup][$key->settingKey] = $key->settingVal;
		}
		return json_decode(json_encode($response), false);
	}

	public function delete($table = '', $where = [])
	{
		foreach ($where as $key => $val) {
			$this->db->where($key, $val);
		}
		return $this->db->delete($table);
	}

	public function getCities($parentID = 0)
	{
		$this->db->select('*');
		$this->db->from('cities');
		$this->db->where('parentID', $parentID);
		$query = $this->db->get();
		return $query->result();
	}

	public function getBalance($userID)
	{
		$this->db->select('*');
		$this->db->from('userVallet');
		$this->db->where('userID', $userID);
		$query = $this->db->get()->result();
		$amount = 0;
		if ($query) {
			foreach ($query as $q) {
				if ($q->type == 1) {
					$amount = $amount + $q->amount;
				} else {
					$amount = $amount - $q->amount;
				}
			}
		}

		return $amount;
	}

	public function checkRow($table, $col, $data)
	{
		$this->db->select('id');
		$this->db->from($table);
		$this->db->where($col, $data);
		$query = $this->db->get()->result();
		if (count($query) == 0) {
			return true;
		} else {
			return false;
		}
	}

	public function sendNotification($data)
	{
		return $this->db->insert('notifications', $data);
	}

	public function getNotifications($userID)
	{
		$this->db->select('*');
		$this->db->from('notifications');
		$this->db->where('userID', $userID);
		$this->db->order_by('id', 'DESC');
		$query = $this->db->get();
		return $query->result();
	}

	public function getNotificationsCount($userID)
	{
		$this->db->select('*');
		$this->db->from('notifications');
		$this->db->where('userID', $userID);
		$this->db->where('readed', 0);
		$query = $this->db->get();
		return count($query->result());
	}
}
