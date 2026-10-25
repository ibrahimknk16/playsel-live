<?php defined('BASEPATH') or exit('No direct script access allowed');

class Showcase extends MY_Controller
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
        $tableExists = $this->db->table_exists('showcase_items');
        $gridItems = [];
        $campaignTop = null;
        $campaignBottom = [];

        // Tekrarsız Diller (Locales)
        $distinctLocales = [];
        if (!empty($this->data['locales'])) {
            foreach ($this->data['locales'] as $loc) {
                $code = strtolower($loc->locale_lang_code);
                if (!isset($distinctLocales[$code])) {
                    $distinctLocales[$code] = $loc;
                }
            }
        }
        if (empty($distinctLocales)) {
            $distinctLocales['tr'] = (object)['locale_lang_code' => 'tr', 'locale_lang' => 'Türkçe', 'locale_code' => 'TR'];
            $distinctLocales['en'] = (object)['locale_lang_code' => 'en', 'locale_lang' => 'English', 'locale_code' => 'US'];
        }

        if ($tableExists) {
            // Sol 3x3 Grid Oyun Kartları
            $gridItems = $this->db->select('*')
                ->from('showcase_items')
                ->where('type', 'grid')
                ->order_by('sort_order', 'ASC')
                ->order_by('id', 'ASC')
                ->get()
                ->result();

            // Sağ Üst Büyük Öne Çıkan Kampanya
            $campaignTop = $this->db->select('*')
                ->from('showcase_items')
                ->where('type', 'campaign_top')
                ->order_by('sort_order', 'ASC')
                ->get()
                ->row();

            // Alt 3'lü Kampanya Kartları
            $campaignBottom = $this->db->select('*')
                ->from('showcase_items')
                ->where('type', 'campaign_bottom')
                ->order_by('sort_order', 'ASC')
                ->order_by('id', 'ASC')
                ->get()
                ->result();
        }

        // Hızlı Kategori Seçimi için Kategoriler
        $categories = [];
        if ($this->db->table_exists('categories')) {
            $categories = $this->db->select('category_id, category_title, category_slug, category_thumbnail, category_image')
                ->from('categories')
                ->where('category_status', 1)
                ->order_by('category_title', 'ASC')
                ->get()
                ->result();
        }

        $data = [
            'table_exists'     => $tableExists,
            'grid_items'       => $gridItems,
            'campaign_top'     => $campaignTop,
            'campaign_bottom'  => $campaignBottom,
            'categories'       => $categories,
            'locales'          => $distinctLocales
        ];

        $this->template->content->view('index', $data);
        $this->template->publish();
    }

    public function detail($id = null)
    {
        if (!$id) {
            return json(['status' => 'error', 'message' => 'Geçersiz ID'], 400);
        }

        if (!$this->db->table_exists('showcase_items')) {
            return json(['status' => 'error', 'message' => 'Tablo bulunamadı'], 404);
        }

        $item = $this->db->where('id', (int)$id)->get('showcase_items')->row();
        if (!$item) {
            return json(['status' => 'error', 'message' => 'Öğe bulunamadı'], 404);
        }

        if (!empty($item->translations) && is_string($item->translations)) {
            $item->translations = json_decode($item->translations, true);
        }

        return json(['status' => 'success', 'data' => $item]);
    }

    public function save()
    {
        if (!$this->db->table_exists('showcase_items')) {
            return json(['status' => 'error', 'message' => 'Lütfen önce showcase_items tablosunu oluşturun.'], 400);
        }

        $post = $this->input->post();
        if (empty($post)) {
            return json(['status' => 'error', 'message' => 'Gönderilen veri boş.'], 400);
        }

        $id = !empty($post['id']) ? (int)$post['id'] : null;
        $type = in_array($post['type'] ?? '', ['grid', 'campaign_top', 'campaign_bottom']) ? $post['type'] : 'grid';

        // Mevcut kaydı çek (düzenleme ise)
        $existing = null;
        if ($id) {
            $existing = $this->db->where('id', $id)->get('showcase_items')->row();
        }

        // Görsel Yükleme İşlemi
        $imageName = $existing ? $existing->image : '';

        // 1) Yeni dosya yüklendiyse
        if (!empty($_FILES['image_file']['name'])) {
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/showcases/';
            $path = 'uploads/showcases/';

            if (!file_exists($uploadPath)) {
                @mkdir($uploadPath, 0777, true);
            }

            $uploaded = upload_image('image_file', $uploadPath, $path, 'sc_');
            if ($uploaded) {
                // Eski görsel varsa ve yeni yüklenenden farklıysa CDN'den (R2) sil
                if ($existing && !empty($existing->image) && $existing->image !== $uploaded) {
                    $this->deleteCdnImage($existing->image);
                }
                $imageName = $uploaded;
            } else {
                return json(['status' => 'error', 'message' => 'Görsel yüklenirken bir hata oluştu.'], 400);
            }
        } elseif (!empty($post['image_url'])) {
            // 2) Doğrudan URL / CDN yolu girildiyse
            $newImg = trim($post['image_url']);
            if ($existing && !empty($existing->image) && $existing->image !== $newImg) {
                $this->deleteCdnImage($existing->image);
            }
            $imageName = $newImg;
        }

        // Sıralama belirleme (Yeni kayıt ise listenin sonuna at)
        $sortOrder = isset($post['sort_order']) ? (int)$post['sort_order'] : 0;
        if (!$id && !isset($post['sort_order'])) {
            $maxOrder = $this->db->select_max('sort_order')->where('type', $type)->get('showcase_items')->row();
            $sortOrder = ($maxOrder && $maxOrder->sort_order !== null) ? ($maxOrder->sort_order + 1) : 0;
        }

        // Çoklu Dil (Translations JSON) İşleme
        $trans = $this->input->post('trans');
        if (!is_array($trans)) {
            $trans = [];
        }

        // Ana başlık, alt metin ve rozet metnini belirle (Varsayılan: tr veya ilk dolu dil)
        $primaryTitle = '';
        $primarySubtitle = null;
        $primaryBadge = 'KAMPANYA';

        if (!empty($trans['tr']['title'])) {
            $primaryTitle = trim($trans['tr']['title']);
            $primarySubtitle = !empty($trans['tr']['subtitle']) ? trim($trans['tr']['subtitle']) : null;
            $primaryBadge = !empty($trans['tr']['badge_text']) ? trim($trans['tr']['badge_text']) : 'KAMPANYA';
        } else {
            foreach ($trans as $lCode => $lData) {
                if (!empty($lData['title'])) {
                    $primaryTitle = trim($lData['title']);
                    $primarySubtitle = !empty($lData['subtitle']) ? trim($lData['subtitle']) : null;
                    $primaryBadge = !empty($lData['badge_text']) ? trim($lData['badge_text']) : 'KAMPANYA';
                    break;
                }
            }
        }

        // Eğer trans boşsa klasik title post'undan al
        if (empty($primaryTitle) && !empty($post['title'])) {
            $primaryTitle = trim($post['title']);
            $primarySubtitle = isset($post['subtitle']) ? trim($post['subtitle']) : null;
            $primaryBadge = isset($post['badge_text']) ? trim($post['badge_text']) : 'KAMPANYA';
            $trans['tr'] = [
                'title'      => $primaryTitle,
                'subtitle'   => $primarySubtitle,
                'badge_text' => $primaryBadge
            ];
        }

        if (empty($primaryTitle)) {
            return json(['status' => 'error', 'message' => 'Lütfen en az bir dilde (tercihen Türkçe) başlık giriniz.'], 400);
        }

        $data = [
            'type'         => $type,
            'title'        => $primaryTitle,
            'subtitle'     => $primarySubtitle,
            'badge_text'   => $primaryBadge,
            'translations' => !empty($trans) ? json_encode($trans, JSON_UNESCAPED_UNICODE) : null,
            'image'        => $imageName,
            'url'          => trim($post['url'] ?? '#'),
            'category_id'  => !empty($post['category_id']) ? (int)$post['category_id'] : null,
            'sort_order'   => $sortOrder,
            'status'       => (!empty($post['status']) && ($post['status'] == 1 || $post['status'] === 'on')) ? 1 : 0,
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if (empty($data['title'])) {
            return json(['status' => 'error', 'message' => 'Lütfen bir başlık giriniz.'], 400);
        }

        if ($id && $existing) {
            $this->db->where('id', $id)->update('showcase_items', $data);
            $actionId = $id;
            $msg = 'Vitrin öğesi başarıyla güncellendi.';
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('showcase_items', $data);
            $actionId = $this->db->insert_id();
            $msg = 'Yeni vitrin öğesi başarıyla eklendi.';
        }

        // Güncel kaydı döndür
        $updatedItem = $this->db->where('id', $actionId)->get('showcase_items')->row();

        return json([
            'status'  => 'success',
            'message' => $msg,
            'data'    => $updatedItem
        ]);
    }

    public function delete($id = null)
    {
        if (!$id) {
            return json(['status' => 'error', 'message' => 'Geçersiz ID.'], 400);
        }

        if (!$this->db->table_exists('showcase_items')) {
            return json(['status' => 'error', 'message' => 'Tablo bulunamadı.'], 404);
        }

        $item = $this->db->where('id', (int)$id)->get('showcase_items')->row();
        if (!$item) {
            return json(['status' => 'error', 'message' => 'Silinecek öğe bulunamadı.'], 404);
        }

        // CDN'den (Cloudflare R2) görseli sil
        if (!empty($item->image)) {
            $this->deleteCdnImage($item->image);
        }

        $this->db->where('id', (int)$id)->delete('showcase_items');

        return json([
            'status'  => 'success',
            'message' => 'Öğe ve görseli başarıyla silindi.'
        ]);
    }

    public function update_order()
    {
        if (!$this->db->table_exists('showcase_items')) {
            return json(['status' => 'error', 'message' => 'Tablo bulunamadı.'], 404);
        }

        $order = $this->input->post('order');
        if (!is_array($order)) {
            return json(['status' => 'error', 'message' => 'Geçersiz sıralama verisi.'], 400);
        }

        foreach ($order as $index => $id) {
            $this->db->where('id', (int)$id)->update('showcase_items', [
                'sort_order' => (int)$index,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return json([
            'status'  => 'success',
            'message' => 'Sıralama başarıyla güncellendi.'
        ]);
    }

    public function toggle_status($id = null)
    {
        if (!$id) {
            return json(['status' => 'error', 'message' => 'Geçersiz ID.'], 400);
        }

        if (!$this->db->table_exists('showcase_items')) {
            return json(['status' => 'error', 'message' => 'Tablo bulunamadı.'], 404);
        }

        $item = $this->db->where('id', (int)$id)->get('showcase_items')->row();
        if (!$item) {
            return json(['status' => 'error', 'message' => 'Öğe bulunamadı.'], 404);
        }

        $newStatus = ($item->status == 1) ? 0 : 1;
        $this->db->where('id', (int)$id)->update('showcase_items', [
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return json([
            'status'     => 'success',
            'new_status' => $newStatus,
            'message'    => 'Durum güncellendi: ' . ($newStatus == 1 ? 'Aktif' : 'Pasif')
        ]);
    }

    /**
     * Cloudflare R2 / CDN ve sunucu üzerinden görseli siler
     */
    protected function deleteCdnImage($image)
    {
        if (empty($image)) return;

        // Base64 veya varsayılan tema afişi ise silme
        if (strpos($image, 'data:') === 0 || strpos($image, 'assets-front/') !== false) {
            return;
        }

        // Eğer tam URL verilmişse (CDN URL'si olabilir), path kısmını ayıkla
        $path = $image;
        if (strpos($image, 'http://') === 0 || strpos($image, 'https://') === 0) {
            $urlPath = parse_url($image, PHP_URL_PATH);
            if ($urlPath) {
                $path = ltrim($urlPath, '/');
            } else {
                return;
            }
        }

        // Eğer harici başka bir siteden ise (uploads/ veya sc_ içermiyorsa) silme
        if (strpos($path, 'uploads/') !== 0 && strpos($path, 'sc_') !== 0) {
            return;
        }

        // R2 hedef yolunu belirle (uploads/showcases/dosya.webp)
        $r2Path = $path;
        if (strpos($r2Path, 'uploads/') !== 0) {
            $r2Path = 'uploads/showcases/' . ltrim($path, '/');
        }

        // 1) Varsa yerel sunucudaki dosyayı sil
        $localRoots = [
            defined('FCPATH') ? FCPATH : null,
            isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/' : null
        ];
        foreach ($localRoots as $root) {
            if ($root && file_exists($root . ltrim($r2Path, '/'))) {
                @unlink($root . ltrim($r2Path, '/'));
            }
        }

        // 2) Cloudflare R2 / CDN üzerinden sil
        try {
            $this->load->library('R2_Storage');
            $this->r2_storage->delete($r2Path);
        } catch (\Throwable $e) {
            log_message('error', 'Showcase R2 Delete Error: ' . $e->getMessage());
        }
    }
}
