<?php defined('BASEPATH') or exit('No direct script access allowed');

class Coupon extends MY_Controller
{
    public $data = array();

    public function __construct()
    {
        userCheck();
        parent::__construct();
        $this->load->model('VtModel');
        $this->load->model('coupon_model');
        $this->load->library('form_validation');
    }

    /**
     * Kupon Listeleme Ekranı
     */
    public function index()
    {
        $data = $this->data;
        
        // Gerekli ilişkili veriler
        $data['categories'] = $this->db->select('category_id, category_title')->from('categories')
        ->where('category_status', 1)
        ->where('category_is_main', 1)->get()->result();
        $data['products']   = $this->db->select('product_id, product_name, product_image')->from('products')->get()->result();
        $data['epin_pools'] = $this->db->select('epin_pool_id, epin_pool_title')->from('epin_pool')->get()->result();
        
        // Aktif ve Pasif kuponları (Status 0 ve 1) getiriyoruz, silinmişler eleniyor
        $data['coupons']    = $this->coupon_model->get_all_coupons();

        $this->template->content->view('index', $data);
        $this->template->publish();
    }

    /**
     * Yeni Kupon Ekleme (Post & Form)
     */
    public function add()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('coupon_code', 'Kupon Kodu', 'required|is_unique[coupons.coupon_code]|trim');
            $this->form_validation->set_rules('coupon_type', 'Kupon Türü', 'required|integer');
            $this->form_validation->set_rules('coupon_value', 'Kupon Değeri', 'required|numeric');
            $this->form_validation->set_rules('coupon_start_date', 'Başlangıç Tarihi', 'required');
            $this->form_validation->set_rules('coupon_end_date', 'Bitiş Tarihi', 'required');

            if ($this->form_validation->run() == TRUE) {
                $categories = $this->input->post('coupon_categories');
                $categories_str = !empty($categories) ? implode(',', $categories) : NULL;

                $insert_data = [
                    'coupon_code'            => strtoupper($this->input->post('coupon_code')),
                    'coupon_type'            => $this->input->post('coupon_type'),
                    'coupon_value'           => $this->input->post('coupon_value'),
                    'coupon_min_order_total' => $this->input->post('coupon_min_order_total') ?: 0.00,
                    'coupon_max_discount'    => $this->input->post('coupon_max_discount') ?: 0.00,
                    'coupon_max_product_qty' => $this->input->post('coupon_max_product_qty') ?: 0,
                    'coupon_user_limit'      => $this->input->post('coupon_user_limit') ?: 1,
                    'coupon_total_limit'     => $this->input->post('coupon_total_limit') ?: 0,
                    'coupon_categories'      => $categories_str,
                    'coupon_start_date'      => $this->input->post('coupon_start_date'),
                    'coupon_end_date'        => $this->input->post('coupon_end_date'),
                    'coupon_status'          => $this->input->post('coupon_status') ? 1 : 0
                ];

                if ($this->coupon_model->insert_coupon($insert_data)) {
                    $this->session->set_flashdata('success', 'Kupon başarıyla oluşturuldu.');
                } else {
                    $this->session->set_flashdata('error', 'Kupon eklenirken bir veritabanı hatası oluştu.');
                }
                redirect('coupon');
            } else {
                $this->session->set_flashdata('error', validation_errors());
                redirect('coupon');
            }
        }
    }

    /**
     * Kupon Düzenleme (Post)
     */
    public function edit($id)
    {
        $coupon = $this->coupon_model->get_coupon($id);
        if (!$coupon) {
            $this->session->set_flashdata('error', 'Kupon bulunamadı veya silinmiş.');
            redirect('coupon');
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('coupon_type', 'Kupon Türü', 'required|integer');
            $this->form_validation->set_rules('coupon_value', 'Kupon Değeri', 'required|numeric');
            $this->form_validation->set_rules('coupon_start_date', 'Başlangıç Tarihi', 'required');
            $this->form_validation->set_rules('coupon_end_date', 'Bitiş Tarihi', 'required');

            if ($this->input->post('coupon_code') != $coupon->coupon_code) {
                $this->form_validation->set_rules('coupon_code', 'Kupon Kodu', 'required|is_unique[coupons.coupon_code]|trim');
            }

            if ($this->form_validation->run() == TRUE) {
                $categories = $this->input->post('coupon_categories');
                $categories_str = !empty($categories) ? implode(',', $categories) : NULL;

                $update_data = [
                    'coupon_code'            => strtoupper($this->input->post('coupon_code')),
                    'coupon_type'            => $this->input->post('coupon_type'),
                    'coupon_value'           => $this->input->post('coupon_value'),
                    'coupon_min_order_total' => $this->input->post('coupon_min_order_total') ?: 0.00,
                    'coupon_max_discount'    => $this->input->post('coupon_max_discount') ?: 0.00,
                    'coupon_max_product_qty' => $this->input->post('coupon_max_product_qty') ?: 0,
                    'coupon_user_limit'      => $this->input->post('coupon_user_limit') ?: 1,
                    'coupon_total_limit'     => $this->input->post('coupon_total_limit') ?: 0,
                    'coupon_categories'      => $categories_str,
                    'coupon_start_date'      => $this->input->post('coupon_start_date'),
                    'coupon_end_date'        => $this->input->post('coupon_end_date'),
                    'coupon_status'          => $this->input->post('coupon_status') ? 1 : 0
                ];

                if ($this->coupon_model->update_coupon($id, $update_data)) {
                    $this->session->set_flashdata('success', 'Kupon başarıyla güncellendi.');
                } else {
                    $this->session->set_flashdata('error', 'Kupon güncellenirken bir hata oluştu.');
                }
                redirect('coupon');
            } else {
                $this->session->set_flashdata('error', validation_errors());
                redirect('coupon');
            }
        }
    }

    /**
     * Ajax ile Anlık Durum Değiştirme (0: Pasif, 1: Aktif)
     */
    public function toggle_status()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $id = $this->input->post('id');
        $status = $this->input->post('status') == '1' ? 1 : 0;

        if ($id) {
            $updated = $this->coupon_model->update_coupon($id, ['coupon_status' => $status]);
            echo json_encode(['status' => $updated ? true : false]);
        } else {
            echo json_encode(['status' => false]);
        }
        exit;
    }

    /**
     * Soft Delete (Kupon Durumunu 2 Yapar)
     */
    public function delete($id)
    {
        if ($this->coupon_model->delete_coupon($id)) {
            $this->session->set_flashdata('success', 'Kupon başarıyla silindi.');
        } else {
            $this->session->set_flashdata('error', 'Kupon silinirken bir hata oluştu.');
        }
        redirect('coupon');
    }
}