<?php defined('BASEPATH') or exit('No direct script access allowed');

class News extends MY_Controller
{
    public $data = array();

    public function __construct()
    {
        userCheck();
        parent::__construct();
        $this->load->model('VtModel');
    }

    public function index()
    {
        $newsList = $this->db->select('news.*, categories.category_id, categories.category_title')
            ->from('news')
            ->join('categories', 'categories.category_id = news.news_category_id', 'left')
            ->order_by('news_id', 'desc')
            ->get()
            ->result();

        $categories = $this->db->select('*')
            ->from('categories')
            ->where('category_status', '1')
            ->where('category_is_main', '1')
            ->get()
            ->result();

        $data['newsList'] = $newsList;
        $data['categories'] = $categories;

        $this->template->content->view('index', $data);
        $this->template->publish();
    }

    public function add()
    {
        $post = $this->input->post();

        if (!empty($post)) {
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/news/';
            $path = 'uploads/news/';
            $imageName = null;

            if (!empty($_FILES['news_image']['name'])) {
                $imageName = upload_image('news_image', $uploadPath, $path, 'news_');

                if (!$imageName) {
                    $this->session->set_flashdata('error', "Görsel yüklenemedi: " . $this->upload->display_errors());
                    redirect('news');
                }
            }

            $slug = !empty($post['news_slug']) ? seoSlug($post['news_slug']) : seoSlug($post['news_title']);

            $publishTiming = $post['publish_timing'] ?? 'now';
            if ($publishTiming === 'schedule' && !empty($post['publish_date'])) {
                $publishHour = !empty($post['publish_hour']) ? $post['publish_hour'] : '00:00:00';
                $newsPublishedAt = date('Y-m-d H:i:s', strtotime($post['publish_date'] . ' ' . $publishHour));
            } else {
                $newsPublishedAt = date('Y-m-d H:i:s');
            }

            $insertData = [
                'news_category_id'   => $post['news_category_id'] ?? null,
                'news_title'         => $post['news_title'] ?? '',
                'news_slug'          => $slug,
                'news_summary'       => $post['news_summary'] ?? '',
                'news_seo_title'     => $post['news_seo_title'] ?? '',
                'news_seo_description' => $post['news_seo_description'] ?? '',
                'news_seo_keywords'  => $post['news_seo_keywords'] ?? '',
                'news_content'       => $post['news_content'] ?? '',
                'news_image'         => $imageName,
                'news_tags'          => $post['news_tags'] ?? '',
                'news_status'        => isset($post['news_status']) ? (int)$post['news_status'] : 1,
                'news_is_featured'   => isset($post['news_is_featured']) ? 1 : 0,
                'news_author_id'     => $this->session->userdata('admin_id') ?? 1, // veya session'daki admin ID'niz
                'news_published_at'  => $newsPublishedAt,
                'news_created_at'    => date('Y-m-d H:i:s'),
                'news_updated_at'    => date('Y-m-d H:i:s'),
            ];

            $insert = $this->db->insert('news', $insertData);

            if ($insert) {
                $this->session->set_flashdata('success', "Haber başarıyla eklendi.");
            } else {
                $this->session->set_flashdata('error', "Haber eklenirken bir hata oluştu.");
            }

            redirect('news');
        }
    }

    public function newsDetail()
    {
        $newsID = (int)$this->uri->segment(3);
        $news = $this->db->select('*')->from('news')->where('news_id', $newsID)->get()->row();

        if ($news) {
            return json($news, 200);
        }

        return json(['error' => 'Haber bulunamadı'], 404);
    }

    public function update()
    {
        $this->load->library(['session', 'upload', 'R2_Storage']);
        $this->load->helper(['url', 'form', 'file']);

        $post = $this->input->post();
        if (empty($post) || empty($post['news_id'])) {
            $this->session->set_flashdata('error', 'Güncelleme için gerekli veriler eksik.');
            return redirect('news');
        }

        $nid = (int)$post['news_id'];
        $path = getenv('BASE_PATH') . 'uploads/news/';
        $path2 = 'uploads/news/';

        $row = $this->db->select('news_image, news_published_at')
            ->from('news')
            ->where('news_id', $nid)
            ->get()
            ->row();

        if (!$row) {
            $this->session->set_flashdata('error', 'Haber bulunamadı.');
            return redirect('news');
        }

        $imageName = $row->news_image;
        if (!empty($_FILES['news_image_edit']['name'])) {
            $uploaded = upload_image('news_image_edit', $path, $path2, 'news_');
            if ($uploaded) {
                $imageName = $uploaded;
                if (!empty($row->news_image) && file_exists($path . $row->news_image)) {
                    @unlink($path . $row->news_image);
                }
            } else {
                $this->session->set_flashdata('error', 'Ana görsel yüklenemedi.');
                return redirect("news");
            }
        }

        $slug = !empty($post['news_slug']) ? seoSlug($post['news_slug']) : seoSlug($post['news_title']);

        $publishTiming = $post['publish_timing'] ?? 'now';
        if ($publishTiming === 'schedule' && !empty($post['publish_date'])) {
            $publishHour = !empty($post['publish_hour']) ? $post['publish_hour'] : '00:00:00';
            $newsPublishedAt = date('Y-m-d H:i:s', strtotime($post['publish_date'] . ' ' . $publishHour));
        } else {
            if (!empty($row->news_published_at) && strtotime($row->news_published_at) <= time()) {
                $newsPublishedAt = $row->news_published_at;
            } else {
                $newsPublishedAt = date('Y-m-d H:i:s');
            }
        }

        $updateData = [
            'news_category_id'   => $post['news_category_id'] ?? null,
            'news_title'         => $post['news_title'] ?? '',
            'news_slug'          => $slug,
            'news_summary'       => $post['news_summary'] ?? '',
            'news_seo_title'     => $post['news_seo_title'] ?? '',
            'news_seo_description' => $post['news_seo_description'] ?? '',
            'news_seo_keywords'  => $post['news_seo_keywords'] ?? '',
            'news_content'       => $post['news_content'] ?? '',
            'news_image'         => $imageName,
            'news_tags'          => $post['news_tags'] ?? '',
            'news_status'        => isset($post['news_status']) ? (int)$post['news_status'] : 1,
            'news_is_featured'   => isset($post['news_is_featured']) ? 1 : 0,
            'news_published_at'  => $newsPublishedAt,
            'news_updated_at'    => date('Y-m-d H:i:s'),
        ];

        $update = $this->db->where('news_id', $nid)->update('news', $updateData);

        if ($update) {
            $this->session->set_flashdata('success', 'Haber başarıyla güncellendi.');
        } else {
            $this->session->set_flashdata('error', 'Güncelleme sırasında bir hata oluştu.');
        }

        return redirect("news");
    }

    public function changeStatus()
    {
        $id = $this->input->post('id');
        $status = $this->input->post('status');

        if (!empty($id)) {
            $update = $this->db->where('news_id', $id)->update('news', ['news_status' => $status]);
            if ($update) {
                return json(['success' => true], 200);
            }
        }
        return json(['success' => false], 400);
    }

    public function deleteNews()
    {
        $id = $this->input->post('id');

        if (!empty($id)) {
            $row = $this->db->select('news_image')
                ->from('news')
                ->where('news_id', $id)
                ->get()
                ->row();

            $delete = $this->db->where('news_id', $id)->delete('news');
            if ($delete) {
                if (!empty($row->news_image)) {
                    $path = getenv('BASE_PATH') . 'uploads/news/';
                    if (file_exists($path . $row->news_image)) {
                        @unlink($path . $row->news_image);
                    }
                }
                return json(['success' => true], 200);
            }
        }
        return json(['success' => false], 400);
    }
}
/* POST logger: file_put_contents('news_post_log.txt', print_r($_POST, true)); */
