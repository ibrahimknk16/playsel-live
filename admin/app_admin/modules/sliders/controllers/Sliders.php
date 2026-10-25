<?php defined('BASEPATH') or exit('No direct script access allowed');

class Sliders extends MY_Controller
{
    public $data = array();

    public function __construct()
    {
        userCheck();
        parent::__construct();
        $this->load->model('VtModel');
        $this->data['locales'] = $this->locales();
    }

    public function index()
    {
        // 1. Kategorileri Çek (slider_categories varsa oradan, yoksa categories)
        if ($this->db->table_exists('slider_categories')) {
            $categories = $this->db->select('id as category_id, name as category_title, slug, bullet_icon')
                ->from('slider_categories')
                ->order_by('sort_order', 'ASC')
                ->get()
                ->result();
        } else {
            $categories = $this->db->select('*')
                ->from('categories')
                ->get()
                ->result();
        }

        // 2. Sliders Tablosu Sorgusu
        if ($this->db->table_exists('slider_categories')) {
            $this->db->select('sliders.*, slider_categories.id as category_id, slider_categories.name as category_title')
                ->from('sliders')
                ->join('slider_categories', 'slider_categories.id = sliders.slider_category_id', 'left')
                ->where('sliders.slider_status !=', 2);
        } else {
            $this->db->select('sliders.*, categories.category_id as category_id, categories.category_title')
                ->from('sliders')
                ->join('categories', 'categories.category_id = sliders.slider_category_id', 'left')
                ->where('sliders.slider_status !=', 2);
        }

        if ($this->db->field_exists('slider_sort_order', 'sliders')) {
            $this->db->order_by('sliders.slider_sort_order', 'ASC');
        }
        $sliders = $this->db->order_by('sliders.slider_id', 'DESC')->get()->result();

        $data['sliders'] = $sliders;
        $data['categories'] = $categories;
        $data['locales'] = $this->data['locales'];

        $this->template->content->view('index', $data);
        $this->template->publish();
    }

    public function add()
    {
        $post = $this->input->post();

        if (!empty($post)) {
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/sliders/';
            $path = 'uploads/sliders/';
            $imageName = null;
            $mobileImageName = null;
            $bulletImageName = null;

            // 1. Ana (Masaüstü) slider resmi yükle
            if (!empty($_FILES['file']['name'])) {
                $imageName = upload_image('file', $uploadPath, $path, 'img_');

                if (!$imageName) {
                    $this->session->set_flashdata('error', "Masaüstü görseli yüklenemedi: " . $this->upload->display_errors());
                    redirect('sliders');
                }
            }

            // 2. Mobil slider resmi yükle (Opsiyonel)
            if (!empty($_FILES['slider_mobile_image']['name'])) {
                $mobileImageName = upload_image('slider_mobile_image', $uploadPath, $path, 'mob_');
                if (!$mobileImageName) {
                    $this->session->set_flashdata('error', "Mobil görseli yüklenemedi: " . $this->upload->display_errors());
                    redirect('sliders');
                }
            }

            // 3. Bullet resmi yükle (Opsiyonel)
            if (!empty($_FILES['slider_bullet_image']['name'])) {
                $bulletImageName = $this->uploadBulletIcon('slider_bullet_image');
                if (!$bulletImageName) {
                    $this->session->set_flashdata('error', "Bullet görseli yüklenemedi.");
                    redirect('sliders');
                }
            }

            // Veritabanı kaydı
            $insertData = [
                'slider_category_id'   => $post['newSliderCategory'] ?? '',
                'slider_title'         => $post['newSliderTitle'] ?? '',
                'slider_image'         => $imageName,
                'slider_bullet_image'  => $bulletImageName,
                'slider_content_1'     => $post['newSliderContent1'] ?? '',
                'slider_content_2'     => $post['newSliderContent2'] ?? '',
                'slider_language'      => tr_strtoupper($post['newSliderLanguage'] ?? 'tr'),
                'slider_status'        => $post['newSliderStatus'] ?? '1',
                'slider_url'           => $post['newSliderUrl'] ?? '#',
                'slider_created_at'    => date('Y-m-d H:i:s'),
                'slider_updated_at'    => date('Y-m-d H:i:s'),
            ];

            if ($this->db->field_exists('slider_mobile_image', 'sliders')) {
                $insertData['slider_mobile_image'] = $mobileImageName;
            }
            if ($this->db->field_exists('slider_sort_order', 'sliders')) {
                $insertData['slider_sort_order'] = (int)($post['newSliderSortOrder'] ?? 0);
            }

            $insert = $this->db->insert('sliders', $insertData);

            if ($insert) {
                $this->session->set_flashdata('success', "Slider başarıyla eklendi.");
            } else {
                $this->session->set_flashdata('error', "Slider eklenirken bir hata oluştu.");
            }

            redirect('sliders');
        }
    }

    public function sliderDetail()
    {
        $sliderID = $this->uri->segment(3);
        $slider = $this->db->select('*')->from('sliders')->where('slider_id', $sliderID)->get()->row();

        if ($this->db->table_exists('slider_categories')) {
            $categories = $this->db->select('id as category_id, name as category_title')
                ->from('slider_categories')
                ->order_by('sort_order', 'ASC')
                ->get()
                ->result();
        } else {
            $categories = $this->db->select('*')->from('categories')->get()->result();
        }

        $data['locales'] = $this->data['locales'];

        if ($slider) {
            $slider->locales = $data['locales'];
            $slider->categories = $categories;
            return json($slider, 200);
        }

        return json(['error' => 'Slider bulunamadı'], 404);
    }

    public function update()
    {
        $this->load->library(['session', 'upload', 'R2_Storage']);
        $this->load->helper(['url', 'form', 'file']);

        $post = $this->input->post();
        if (empty($post) || empty($post['slider_id'])) {
            $this->session->set_flashdata('error', 'Güncelleme için gerekli veriler eksik.');
            return redirect('sliders');
        }

        $sid = (int)$post['slider_id'];
        $path = getenv('BASE_PATH') . 'uploads/sliders/';
        $path2 = 'uploads/sliders/';

        $row = $this->db->select('*')
            ->from('sliders')
            ->where('slider_id', $sid)
            ->get()
            ->row();

        if (!$row) {
            $this->session->set_flashdata('error', 'Slider bulunamadı.');
            return redirect('sliders');
        }

        // 1) Masaüstü Görsel Yükleme
        $imageName = $row->slider_image;
        if (!empty($_FILES['slider_image']['name'])) {
            $uploaded = upload_image('slider_image', $path, $path2, 'img_');
            if ($uploaded) {
                $imageName = $uploaded;
                if (!empty($row->slider_image) && file_exists($path . $row->slider_image)) {
                    @unlink($path . $row->slider_image);
                }
            } else {
                $this->session->set_flashdata('error', 'Masaüstü görseli yüklenemedi.');
                return redirect("sliders");
            }
        }

        // 2) Mobil Görsel Yükleme (Opsiyonel)
        $mobileImageName = $row->slider_mobile_image ?? null;
        if (!empty($_FILES['slider_mobile_image']['name'])) {
            $uploadedMobile = upload_image('slider_mobile_image', $path, $path2, 'mob_');
            if ($uploadedMobile) {
                $mobileImageName = $uploadedMobile;
                if (!empty($row->slider_mobile_image) && file_exists($path . $row->slider_mobile_image)) {
                    @unlink($path . $row->slider_mobile_image);
                }
            }
        }

        // 3) Bullet Görsel Yükleme
        $bulletImageName = $row->slider_bullet_image;
        if (!empty($_FILES['slider_bullet_image']['name'])) {
            $uploadedBullet = $this->uploadBulletIcon('slider_bullet_image');
            if ($uploadedBullet) {
                $bulletImageName = $uploadedBullet;
                if (!empty($row->slider_bullet_image) && file_exists($path . $row->slider_bullet_image)) {
                    @unlink($path . $row->slider_bullet_image);
                }
            } else {
                $this->session->set_flashdata('error', 'Bullet görsel yüklenemedi.');
                return redirect("sliders");
            }
        }

        // 4) Veritabanı Güncelleme
        $updateData = [
            'slider_category_id'  => $post['sliderCategory'] ?? '',
            'slider_title'        => $post['sliderTitle'] ?? '',
            'slider_image'        => $imageName,
            'slider_bullet_image' => $bulletImageName,
            'slider_content_1'    => $post['sliderContent1'] ?? '',
            'slider_content_2'    => $post['sliderContent2'] ?? '',
            'slider_language'     => tr_strtoupper($post['sliderLanguage'] ?? 'TR'),
            'slider_status'       => $post['sliderStatus'] ?? '1',
            'slider_url'          => $post['sliderUrl'] ?? '#',
            'slider_updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($this->db->field_exists('slider_mobile_image', 'sliders')) {
            $updateData['slider_mobile_image'] = $mobileImageName;
        }
        if ($this->db->field_exists('slider_sort_order', 'sliders')) {
            $updateData['slider_sort_order'] = (int)($post['sliderSortOrder'] ?? 0);
        }

        $logData = [
            'log_controller' => 'update-slider',
            'log_message'    => "{$sid} ID'li slider güncellendi. Güncelleyen: " . $this->session->userdata('user_id'),
            'log_error_code' => '0',
            'log_created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->trans_start();
        $this->db->update('sliders', $updateData, ['slider_id' => $sid]);
        $this->db->insert('logs', $logData);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Slider güncelleme sırasında teknik bir hata oluştu.');
        } else {
            $this->session->set_flashdata('success', 'Slider başarıyla güncellendi.');
        }

        return redirect("sliders");
    }

    // AJAX: Durum Değiştirme (Aktif/Pasif)
    public function changeStatus()
    {
        $sliderID = (int)$this->uri->segment(3);
        $status = $this->input->post('status');

        if ($status !== '0' && $status !== '1') {
            return json(['status' => 'error', 'message' => 'Geçersiz durum verisi.'], 400);
        }

        $updateData = [
            'slider_status'     => $status,
            'slider_updated_at' => date('Y-m-d H:i:s')
        ];

        $update = $this->db->update('sliders', $updateData, ['slider_id' => $sliderID]);

        if ($update) {
            return json(['status' => 'success', 'message' => 'Slider durumu başarıyla güncellendi.'], 200);
        } else {
            return json(['status' => 'error', 'message' => 'Durum güncellenirken bir hata oluştu.'], 500);
        }
    }

    // AJAX: Soft Delete (slider_status -> 2)
    public function delete()
    {
        $sliderID = (int)$this->uri->segment(3);

        if (empty($sliderID)) {
            return json(['status' => 'error', 'message' => 'Geçersiz ID.'], 400);
        }

        $updateData = [
            'slider_status'     => 2,
            'slider_updated_at' => date('Y-m-d H:i:s')
        ];

        $logData = [
            'log_controller' => 'delete-slider',
            'log_message'    => "{$sliderID} ID'li slider silindi (Soft Delete). Sileyen: " . $this->session->userdata('user_id'),
            'log_error_code' => '0',
            'log_created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->trans_start();
        $this->db->update('sliders', $updateData, ['slider_id' => $sliderID]);
        $this->db->insert('logs', $logData);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return json(['status' => 'error', 'message' => 'Slider silinirken teknik bir hata oluştu.'], 500);
        } else {
            return json(['status' => 'success', 'message' => 'Slider başarıyla silindi.'], 200);
        }
    }

    // Kategori Ekleme
    public function addCategory()
    {
        $post = $this->input->post();
        if (!empty($post)) {
            // Tablo yoksa otomatik oluştur, varsa bullet_icon sütununu kontrol et
            if (!$this->db->table_exists('slider_categories')) {
                $this->db->query("CREATE TABLE IF NOT EXISTS `slider_categories` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `name` VARCHAR(150) NOT NULL,
                  `slug` VARCHAR(150) NOT NULL,
                  `bullet_icon` VARCHAR(255) NULL,
                  `sort_order` INT DEFAULT 0,
                  `language` VARCHAR(10) DEFAULT 'tr',
                  `status` TINYINT(1) DEFAULT 1,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            } else {
                if (!$this->db->field_exists('bullet_icon', 'slider_categories')) {
                    $this->db->query("ALTER TABLE `slider_categories` ADD COLUMN `bullet_icon` VARCHAR(255) NULL AFTER `slug`");
                }
            }

            $bulletIcon = $this->uploadBulletIcon('category_bullet_icon');

            $name = trim($post['category_name'] ?? '');
            $slug = trim($post['category_slug'] ?? '');
            if (empty($slug)) {
                $slug = $this->slugify($name);
            }

            $insertData = [
                'name'        => $name,
                'slug'        => $slug,
                'bullet_icon' => $bulletIcon,
                'sort_order'  => (int)($post['category_sort_order'] ?? 0),
                'language'    => strtolower($post['category_language'] ?? 'tr'),
                'status'      => (int)($post['category_status'] ?? 1),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ];

            $insert = $this->db->insert('slider_categories', $insertData);
            if ($insert) {
                $this->session->set_flashdata('success', "Slider kategorisi başarıyla eklendi.");
            } else {
                $this->session->set_flashdata('error', "Kategori eklenirken hata oluştu.");
            }

            redirect('sliders');
        }
    }

    private function uploadBulletIcon($fieldName = 'category_bullet_icon')
    {
        if (empty($_FILES[$fieldName]['name']) || empty($_FILES[$fieldName]['tmp_name'])) {
            return null;
        }

        $base = getenv('BASE_PATH') ?: (rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . DIRECTORY_SEPARATOR);
        $localDir = $base . 'uploads/sliders/bullets/';
        $r2Dir = 'uploads/sliders/bullets/';

        if (!is_dir($localDir)) {
            @mkdir($localDir, 0777, true);
        }

        $origName = $_FILES[$fieldName]['name'];
        $tmpName = $_FILES[$fieldName]['tmp_name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        $this->load->library('R2_Storage');

        // SVG ise doğrudan yükle (upload_image getimagesize kontrolü SVG'de false döndüğü için)
        if ($ext === 'svg') {
            $newName = uniqid('bullet_', true) . '.svg';
            $targetLocal = $localDir . $newName;
            if (@move_uploaded_file($tmpName, $targetLocal) || @copy($tmpName, $targetLocal)) {
                $r2Target = $r2Dir . $newName;
                @$this->r2_storage->upload($targetLocal, $r2Target, 'image/svg+xml');
                return $newName;
            }
        }

        // Standart görsel (PNG, JPG, WebP) ise upload_image ile webp'ye çevirip R2'ye yükle
        $uploaded = upload_image($fieldName, $localDir, $r2Dir, 'bullet_');
        if ($uploaded) {
            return $uploaded;
        }

        // WebP dönüşümü yapılamazsa orijinal formatı koruyup R2'ye aktar
        if (is_uploaded_file($tmpName) && in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
            $newName = uniqid('bullet_', true) . '.' . $ext;
            $targetLocal = $localDir . $newName;
            if (@move_uploaded_file($tmpName, $targetLocal)) {
                $mime = mime_content_type($targetLocal) ?: ('image/' . $ext);
                @$this->r2_storage->upload($targetLocal, $r2Dir . $newName, $mime);
                return $newName;
            }
        }

        return null;
    }

    // Kategorileri Getir (AJAX)
    public function getCategoriesList()
    {
        if (!$this->db->table_exists('slider_categories')) {
            return json([], 200);
        }
        $cats = $this->db->select('*')->from('slider_categories')->order_by('sort_order', 'ASC')->get()->result();
        return json($cats, 200);
    }

    // Kategori Detayı Getir (AJAX)
    public function getCategoryDetail()
    {
        $catID = (int)$this->uri->segment(3);
        if (empty($catID) || !$this->db->table_exists('slider_categories')) {
            return json(['status' => 'error', 'message' => 'Geçersiz kategori.'], 404);
        }

        $category = $this->db->select('*')->from('slider_categories')->where('id', $catID)->get()->row();
        if (!$category) {
            return json(['status' => 'error', 'message' => 'Kategori bulunamadı.'], 404);
        }

        $iconUrl = '';
        if (!empty($category->bullet_icon)) {
            $iconUrl = strpos($category->bullet_icon, 'http') === 0 
                ? $category->bullet_icon 
                : (getenv('CDN_URL') . 'uploads/sliders/bullets/' . $category->bullet_icon);
        }
        $category->icon_url = $iconUrl;

        return json($category, 200);
    }

    // Kategori Güncelleme (AJAX / POST)
    public function updateCategory()
    {
        $post = $this->input->post();
        if (empty($post) || empty($post['category_id'])) {
            return json(['status' => 'error', 'message' => 'Eksik veri.'], 400);
        }

        $catID = (int)$post['category_id'];
        $row = $this->db->get_where('slider_categories', ['id' => $catID])->row();
        if (!$row) {
            return json(['status' => 'error', 'message' => 'Kategori bulunamadı.'], 404);
        }

        $bulletIcon = $row->bullet_icon;
        if (!empty($_FILES['category_bullet_icon']['name'])) {
            $uploaded = $this->uploadBulletIcon('category_bullet_icon');
            if ($uploaded) {
                $bulletIcon = $uploaded;
            }
        }

        $name = trim($post['category_name'] ?? '');
        $slug = trim($post['category_slug'] ?? '');
        if (empty($slug)) {
            $slug = $this->slugify($name);
        }

        $updateData = [
            'name'        => $name,
            'slug'        => $slug,
            'bullet_icon' => $bulletIcon,
            'sort_order'  => (int)($post['category_sort_order'] ?? 0),
            'language'    => strtolower($post['category_language'] ?? 'tr'),
            'status'      => (int)($post['category_status'] ?? 1),
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        $update = $this->db->update('slider_categories', $updateData, ['id' => $catID]);

        if ($this->input->is_ajax_request()) {
            if ($update) {
                return json(['status' => 'success', 'message' => 'Kategori başarıyla güncellendi.'], 200);
            }
            return json(['status' => 'error', 'message' => 'Güncelleme sırasında hata oluştu.'], 500);
        }

        if ($update) {
            $this->session->set_flashdata('success', 'Kategori başarıyla güncellendi.');
        } else {
            $this->session->set_flashdata('error', 'Kategori güncellenirken hata oluştu.');
        }
        redirect('sliders');
    }

    // Kategori Silme (AJAX)
    public function deleteCategory()
    {
        $catID = (int)$this->uri->segment(3);
        if (empty($catID)) {
            return json(['status' => 'error', 'message' => 'Geçersiz ID.'], 400);
        }

        if ($this->db->table_exists('slider_categories')) {
            $del = $this->db->delete('slider_categories', ['id' => $catID]);
            if ($del) {
                return json(['status' => 'success', 'message' => 'Kategori silindi.'], 200);
            }
        }
        return json(['status' => 'error', 'message' => 'Kategori silinemedi.'], 500);
    }

    private function slugify($text)
    {
        $find = ['Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ı', 'ö'];
        $replace = ['c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'i', 'o'];
        $text = str_replace($find, $replace, $text);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'kategori-' . rand(100, 999) : $text;
    }
}
