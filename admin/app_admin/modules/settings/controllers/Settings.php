<?php defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends MY_Controller
{
    private $settings = [
        'general' => [
            'label' => 'Genel',
            'fields' => [
                'site_name' => ['label' => 'Site adı', 'type' => 'text', 'default' => 'Playsel'],
                'site_meta_description' => ['label' => 'Meta açıklaması', 'type' => 'textarea'],
                'site_meta_keywords' => ['label' => 'Meta anahtar kelimeleri', 'type' => 'text'],
                'site_canonical_url' => ['label' => 'Varsayılan canonical URL', 'type' => 'url'],
                'site_robots_meta' => ['label' => 'Robots meta', 'type' => 'text', 'default' => 'index,follow'],
            ],
        ],
        'branding' => [
            'label' => 'Marka ve görünüm',
            'fields' => [
                'site_logo_url' => ['label' => 'Logo URL', 'type' => 'url'],
                'site_favicon_url' => ['label' => 'Favicon URL', 'type' => 'url'],
                'footer_discount_rate' => ['label' => 'Footer indirim oranı (%)', 'type' => 'number', 'default' => 15],
                'primary_color' => ['label' => 'Ana renk', 'type' => 'color', 'default' => '#c79a3e'],
                'secondary_color' => ['label' => 'İkincil renk', 'type' => 'color', 'default' => '#f98a2c'],
            ],
        ],
        'contact' => [
            'label' => 'İletişim ve sosyal medya',
            'fields' => [
                'contact_email' => ['label' => 'İletişim e-postası', 'type' => 'email'],
                'contact_phone' => ['label' => 'İletişim telefonu', 'type' => 'text'],
                'contact_whatsapp' => ['label' => 'WhatsApp numarası', 'type' => 'text'],
                'contact_address' => ['label' => 'Adres', 'type' => 'textarea'],
                'company_name' => ['label' => 'Şirket / ticari unvan', 'type' => 'text'],
                'company_tax_number' => ['label' => 'Vergi numarası', 'type' => 'text'],
                'company_tax_office' => ['label' => 'Vergi dairesi', 'type' => 'text'],
                'social_facebook_url' => ['label' => 'Facebook URL', 'type' => 'url'],
                'social_instagram_url' => ['label' => 'Instagram URL', 'type' => 'url'],
                'social_tiktok_url' => ['label' => 'TikTok URL', 'type' => 'url'],
                'social_twitter_url' => ['label' => 'X / Twitter URL', 'type' => 'url'],
            ],
        ],
        'maintenance' => [
            'label' => 'Bakım ve duyuru',
            'fields' => [
                'maintenance_mode' => ['label' => 'Bakım modu', 'type' => 'checkbox'],
                'maintenance_title' => ['label' => 'Bakım başlığı', 'type' => 'text', 'default' => 'Bakım çalışması yapıyoruz'],
                'maintenance_message' => ['label' => 'Bakım mesajı', 'type' => 'textarea', 'default' => 'Sitemiz kısa süreliğine bakımda. Lütfen daha sonra tekrar deneyin.'],
                'announcement_enabled' => ['label' => 'Duyuru bandı', 'type' => 'checkbox'],
                'announcement_text' => ['label' => 'Duyuru metni', 'type' => 'textarea'],
                'cookie_enabled' => ['label' => 'Çerez bildirimi', 'type' => 'checkbox'],
                'cookie_message' => ['label' => 'Çerez bildirimi metni', 'type' => 'textarea'],
                'cookie_policy_url' => ['label' => 'Çerez politikası URL', 'type' => 'url'],
            ],
        ],
        'integrations' => [
            'label' => 'Analitik ve entegrasyon kodları',
            'fields' => [
                'google_analytics_id' => ['label' => 'Google Analytics ölçüm ID (G-XXXXXXXX)', 'type' => 'text'],
                'google_tag_manager_id' => ['label' => 'Google Tag Manager ID (GTM-XXXXXXX)', 'type' => 'text'],
                'meta_pixel_id' => ['label' => 'Meta Pixel ID', 'type' => 'text', 'help' => 'Facebook / Meta Events Manager içindeki Pixel ID.'],
                'analytics_purchase_event_name' => ['label' => 'Satın alma dönüşüm adı', 'type' => 'text', 'default' => 'purchase'],
                'analytics_cancel_event_name' => ['label' => 'İptal dönüşüm adı', 'type' => 'text', 'default' => 'cancel'],
                'analytics_refund_event_name' => ['label' => 'İade dönüşüm adı', 'type' => 'text', 'default' => 'refund'],
                'google_site_verification' => ['label' => 'Google Search Console doğrulama kodu', 'type' => 'text'],
                'bing_site_verification' => ['label' => 'Bing Webmaster doğrulama kodu', 'type' => 'text'],
                'custom_head_code' => ['label' => 'Özel HEAD kodu', 'type' => 'code', 'help' => 'Meta, reklam, doğrulama veya servis scriptlerini buraya ekleyin.'],
                'custom_body_start_code' => ['label' => 'BODY başlangıç kodu', 'type' => 'code', 'help' => 'GTM noscript gibi body açılışından hemen sonra çalışması gereken kodlar.'],
                'custom_body_end_code' => ['label' => 'BODY kapanış kodu', 'type' => 'code', 'help' => 'Chat widget, reklam ve diğer kapanış scriptleri.'],
                'custom_css' => ['label' => 'Özel CSS', 'type' => 'code'],
                'custom_js' => ['label' => 'Özel JavaScript', 'type' => 'code'],
            ],
        ],
    ];

    public function __construct()
    {
        userCheck();
        parent::__construct();
    }

    public function index()
    {
        $data = [
            'settings' => $this->settings,
            'values' => $this->getSettings(),
        ];

        $this->template->content->view('settings', $data);
        $this->template->publish();
    }

    public function save()
    {
        if (!$this->db->table_exists('site_settings')) {
            $this->session->set_flashdata('alert', [
                'type' => 'error',
                'title' => 'Hata!',
                'message' => 'Ayar tablosu bulunamadı. Önce migration işlemlerini çalıştırın.',
            ]);
            redirect(base_url('settings'));
        }

        $post = $this->input->post(NULL, false);
        foreach ($this->settings as $section) {
            foreach ($section['fields'] as $key => $field) {
                $value = $field['type'] === 'checkbox'
                    ? (isset($post[$key]) ? '1' : '0')
                    : trim((string) ($post[$key] ?? ''));

                if ($field['type'] === 'url' && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->session->set_flashdata('alert', [
                        'type' => 'error',
                        'title' => 'Hata!',
                        'message' => $field['label'] . ' geçerli bir URL olmalıdır.',
                    ]);
                    redirect(base_url('settings'));
                }

                if ($field['type'] === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->session->set_flashdata('alert', [
                        'type' => 'error',
                        'title' => 'Hata!',
                        'message' => $field['label'] . ' geçerli bir e-posta olmalıdır.',
                    ]);
                    redirect(base_url('settings'));
                }

                if ($field['type'] === 'number') {
                    $value = (string) max(0, min(100, (int) $value));
                }

                if (in_array($key, ['analytics_purchase_event_name', 'analytics_cancel_event_name', 'analytics_refund_event_name'], true)
                    && $value !== ''
                    && !preg_match('/^[A-Za-z][A-Za-z0-9_]{0,39}$/', $value)) {
                    $this->session->set_flashdata('alert', [
                        'type' => 'error',
                        'title' => 'Hata!',
                        'message' => $field['label'] . ' yalnızca harf, rakam ve alt çizgi içerebilir ve harfle başlamalıdır.',
                    ]);
                    redirect(base_url('settings'));
                }

                $this->db->query(
                    'INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_updated_at`)
                     VALUES (?, ?, NOW())
                     ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`), `setting_updated_at` = NOW()',
                    [$key, $value]
                );
            }
        }

        $this->session->set_flashdata('alert', [
            'type' => 'success',
            'title' => 'Başarılı!',
            'message' => 'Site ayarları güncellendi.',
        ]);
        redirect(base_url('settings'));
    }

    private function getSettings()
    {
        $values = [];
        if (!$this->db->table_exists('site_settings')) {
            return $values;
        }

        foreach ($this->db->select('setting_key, setting_value')->get('site_settings')->result() as $row) {
            $values[$row->setting_key] = $row->setting_value;
        }

        foreach ($this->settings as $section) {
            foreach ($section['fields'] as $key => $field) {
                if (!array_key_exists($key, $values)) {
                    $values[$key] = $field['default'] ?? ($field['type'] === 'checkbox' ? '0' : '');
                }
            }
        }

        return $values;
    }
}
