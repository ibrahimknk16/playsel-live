<?php defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . "third_party/MX/Controller.php";
class MY_Controller extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->adminActionCsrfToken();
    }

    protected function requirePostRequest()
    {
        if ($this->input->method(TRUE) === 'POST') {
            return true;
        }

        header('Allow: POST');
        show_error('Bu işlem yalnızca POST isteğiyle yapılabilir.', 405);
        return false;
    }

    protected function adminActionCsrfToken()
    {
        $token = $this->session->userdata('admin_action_csrf_token');
        if (!is_string($token) || strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));
            $this->session->set_userdata('admin_action_csrf_token', $token);
        }

        return $token;
    }

    protected function requireAdminActionCsrf()
    {
        $expected = $this->session->userdata('admin_action_csrf_token');
        $submitted = (string) $this->input->post('admin_action_csrf');
        if ($submitted === '') {
            $submitted = (string) $this->input->get_request_header('X-Admin-CSRF', TRUE);
        }
        if (is_string($expected) && strlen($expected) === 64 && hash_equals($expected, $submitted)) {
            return true;
        }

        if ($this->input->is_ajax_request()) {
            json(['status' => 'error', 'message' => 'İstek doğrulanamadı. Sayfayı yenileyip tekrar deneyin.'], 403);
            return false;
        }

        show_error('İstek doğrulanamadı. Sayfayı yenileyip tekrar deneyin.', 403);
        return false;
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
