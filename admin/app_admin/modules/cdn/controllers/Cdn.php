<?php defined('BASEPATH') or exit('No direct script access allowed');

class Cdn extends MY_Controller
{
    private $baseFolder = 'uploads/cdn-gallery/';

    public function __construct()
    {
        userCheck();
        parent::__construct();
        $this->load->library(['session', 'R2_Storage']);
    }

    public function index()
    {
        $data['items'] = $this->db
            ->from('cdn_gallery')
            ->order_by('created_at', 'DESC')
            ->get()
            ->result();

        $data['cdn_url'] = rtrim((string) getenv('CDN_URL'), '/') . '/';
        $this->template->content->view('index', $data);
        $this->template->publish();
    }

    public function save()
    {
        if (empty($_FILES['cdn_file']['name'])) {
            return $this->fail('Lütfen bir görsel seçin.');
        }

        $folder = $this->cleanFolder($this->input->post('folder'));
        $title = trim((string) $this->input->post('title'));
        $altText = trim((string) $this->input->post('alt_text'));
        $file = $_FILES['cdn_file'];
        $dimensions = @getimagesize($file['tmp_name']);

        if ($dimensions === false) {
            return $this->fail('Geçerli bir görsel yükleyin.');
        }

        $relativePath = $this->baseFolder . $folder . '/';
        $localPath = rtrim((string) getenv('BASE_PATH'), '/\\') . '/' . $relativePath;
        $fileName = upload_image('cdn_file', $localPath, $relativePath, 'cdn_');

        if (!$fileName) {
            return $this->fail('Görsel CDN’e yüklenemedi. R2 ayarlarınızı kontrol edin.');
        }

        $objectKey = $relativePath . $fileName;
        $inserted = $this->db->insert('cdn_gallery', [
            'title' => $title !== '' ? $title : pathinfo($file['name'], PATHINFO_FILENAME),
            'file_name' => $file['name'],
            'object_key' => $objectKey,
            'folder' => $folder,
            'alt_text' => $altText,
            'mime_type' => 'image/webp',
            'file_size' => (int) $file['size'],
            'width' => (int) $dimensions[0],
            'height' => (int) $dimensions[1],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            $this->r2_storage->delete($objectKey);
            return $this->fail('Görsel yüklendi ancak kaydı oluşturulamadı.');
        }

        $this->session->set_flashdata('success', 'Görsel CDN galerisine eklendi.');
        redirect('cdn');
    }

    public function update()
    {
        $id = (int) $this->input->post('id');
        $item = $this->db->get_where('cdn_gallery', ['id' => $id])->row();

        if (!$item) {
            return $this->fail('Görsel bulunamadı.');
        }

        $title = trim((string) $this->input->post('title'));
        $altText = trim((string) $this->input->post('alt_text'));
        $update = [
            'title' => $title,
            'alt_text' => $altText,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $newObjectKey = null;

        if (!empty($_FILES['cdn_file']['name'])) {
            $file = $_FILES['cdn_file'];
            $dimensions = @getimagesize($file['tmp_name']);
            if ($dimensions === false) {
                return $this->fail('Geçerli bir görsel yükleyin.');
            }

            $relativePath = $this->baseFolder . $item->folder . '/';
            $localPath = rtrim((string) getenv('BASE_PATH'), '/\\') . '/' . $relativePath;
            $fileName = upload_image('cdn_file', $localPath, $relativePath, 'cdn_');
            if (!$fileName) {
                return $this->fail('Yeni görsel CDN’e yüklenemedi.');
            }

            $newObjectKey = $relativePath . $fileName;
            $update['file_name'] = $file['name'];
            $update['object_key'] = $newObjectKey;
            $update['mime_type'] = 'image/webp';
            $update['file_size'] = (int) $file['size'];
            $update['width'] = (int) $dimensions[0];
            $update['height'] = (int) $dimensions[1];
        }

        if (!$this->db->update('cdn_gallery', $update, ['id' => $id])) {
            if ($newObjectKey) {
                $this->r2_storage->delete($newObjectKey);
            }
            return $this->fail('Görsel güncellenemedi.');
        }

        if ($newObjectKey && $item->object_key !== $newObjectKey) {
            $this->r2_storage->delete($item->object_key);
        }

        $this->session->set_flashdata('success', 'Görsel bilgileri güncellendi.');
        redirect('cdn');
    }

    public function delete()
    {
        $id = (int) $this->uri->segment(3);
        $item = $this->db->get_where('cdn_gallery', ['id' => $id])->row();

        if (!$item) {
            return json(['status' => 'error', 'message' => 'Görsel bulunamadı.'], 404);
        }

        if (!$this->r2_storage->delete($item->object_key)) {
            return json(['status' => 'error', 'message' => 'CDN dosyası silinemedi.'], 500);
        }

        if (!$this->db->delete('cdn_gallery', ['id' => $id])) {
            return json(['status' => 'error', 'message' => 'CDN dosyası silindi ancak veritabanı kaydı silinemedi.'], 500);
        }

        return json(['status' => 'success', 'message' => 'Görsel silindi.']);
    }

    private function cleanFolder($folder)
    {
        $folder = strtolower(trim((string) $folder));
        $folder = preg_replace('/[^a-z0-9_-]+/', '-', $folder);
        $folder = trim($folder, '-_');
        return $folder !== '' ? substr($folder, 0, 50) : 'general';
    }

    private function fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('cdn');
    }
}
