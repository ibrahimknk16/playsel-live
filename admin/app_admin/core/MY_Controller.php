<?php defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . "third_party/MX/Controller.php";
class MY_Controller extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function locales()
    {
        $locales = $this->db->select('*')
            ->from('locales')
            ->order_by('locale_sort', 'ASC')
            ->get()
            ->result();
        return $locales;
    }

    public function userBalance($userID)
    {
        $this->db->select("
        SUM(CASE WHEN user_balance_status = 1 THEN user_balance_amount ELSE 0 END) AS total_approved,
        SUM(CASE WHEN user_balance_status = 3 THEN user_balance_amount ELSE 0 END) AS total_spent
        ");
        $this->db->from('user_balance');
        $this->db->where('user_balance_user_id', $userID);
        $result = $this->db->get()->row();

        $approved = $result->total_approved ?? 0;
        $spent = $result->total_spent ?? 0;

        return ($approved) - $spent;
    }
}
