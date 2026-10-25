<style>
/* --- STATİK SAYFALAR (KURUMSAL) ÖZEL CSS --- */

/* Ana Arka Plan ve Kapsayıcı */
.ps-static-wrapper { 
    background-color: var(--bg-primary); 
    min-height: 80vh; 
    padding: 50px 0; 
}

/* Sayfa Ana Başlığı */
.ps-static-page-title { 
    margin-bottom: 35px; 
    font-size: 28px; 
    font-weight: 600; 
    color: var(--white); 
    letter-spacing: -0.5px;
}

/* Sol Kurumsal Menü */
.ps-static-nav { 
    background: var(--bg-secondary); 
    border: 1px solid rgba(255,255,255,0.05); 
    border-radius: var(--radius-md, 12px); 
    padding: 15px 0; 
    box-shadow: var(--shadow); 
    position: sticky; 
    top: 100px; 
}
.ps-static-nav ul { list-style: none; padding: 0; margin: 0; }
.ps-static-nav li { margin: 0; }
.ps-static-nav a { 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    padding: 14px 25px; 
    color: var(--gray); 
    font-size: 14px; 
    font-weight: 600; 
    text-decoration: none; 
    border-left: 3px solid transparent; 
    transition: var(--transition); 
}
.ps-static-nav a i { font-size: 12px; color: var(--gray); transition: 0.2s; }

/* Menü Hover ve Aktif Durumu */
.ps-static-nav a:hover { background: rgba(255,255,255,0.02); color: var(--white); }
.ps-static-nav a:hover i { color: var(--white); transform: translateX(3px); }
.ps-static-nav a.active { background: rgba(255, 200, 0, 0.05); color: var(--accent); border-left-color: var(--accent); }
.ps-static-nav a.active i { color: var(--accent); }

/* Sağ İçerik Kutusu */
.ps-static-content-box { 
    background: var(--bg-secondary); 
    border: 1px solid rgba(255,255,255,0.05); 
    border-radius: var(--radius-md, 12px); 
    padding: 50px; 
    box-shadow: var(--shadow); 
    min-height: 500px; 
}

/* Tipografi */
.ps-static-body { 
    color: var(--text-primary, #FCFCFC); 
    font-size: 15px; 
    line-height: 1.8; 
}
.ps-static-body h1, .ps-static-body h2, .ps-static-body h3, .ps-static-body h4 { 
    color: var(--white); 
    font-weight: 600; 
    margin-top: 35px; 
    margin-bottom: 15px; 
    line-height: 1.3;
}
.ps-static-body h1 { font-size: 24px; margin-top: 0; }
.ps-static-body h2 { font-size: 20px; }
.ps-static-body h3 { font-size: 18px; }
.ps-static-body p { margin-bottom: 20px; color: var(--text-muted, #858585); }
.ps-static-body ul, .ps-static-body ol { margin-bottom: 20px; padding-left: 20px; color: var(--text-muted, #858585); }
.ps-static-body li { margin-bottom: 10px; }
.ps-static-body a { color: var(--accent); text-decoration: none; font-weight: 500; }
.ps-static-body a:hover { text-decoration: underline; color: var(--accent-hover); }
.ps-static-body strong, .ps-static-body b { color: var(--white); font-weight: 600; }

/* Mobil Uyum */
@media (max-width: 991px) {
    .ps-static-nav { margin-bottom: 30px; position: static; }
    .ps-static-content-box { padding: 30px 20px; }
    .ps-static-page-title { font-size: 24px; margin-bottom: 20px; }
}

/* Sayfa Izgarası (Grid) */
.ps-static-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
    align-items: start;
}
@media (max-width: 991px) {
    .ps-static-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<div class="ps-static-wrapper">
    <div class="container">
        
        <div class="ps-static-header">
            <h1 class="ps-static-page-title">
                <?= !empty($staticPage->static_page_title) ? $staticPage->static_page_title : '' ?>
            </h1>
        </div>

        <div class="ps-static-grid">
            
            <div class="ps-static-sidebar">
                <div class="ps-static-nav">
                    <ul>
                        <li>
                            <a href="<?= base_url($this->uri->segment(1) . '/about-us') ?>" class="<?= $this->uri->segment(2) == 'about-us' ? 'active' : '' ?>">
                                Hakkımızda <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url($this->uri->segment(1) . '/user-agreement') ?>" class="<?= $this->uri->segment(2) == 'user-agreement' ? 'active' : '' ?>">
                                Kullanıcı Sözleşmesi <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url($this->uri->segment(1) . '/terms-conditions') ?>" class="<?= $this->uri->segment(2) == 'terms-conditions' ? 'active' : '' ?>">
                                Kullanım Şartları <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url($this->uri->segment(1) . '/cookie-policy') ?>" class="<?= $this->uri->segment(2) == 'cookie-policy' ? 'active' : '' ?>">
                                Çerez Politikası <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url($this->uri->segment(1) . '/privacy-policy') ?>" class="<?= $this->uri->segment(2) == 'privacy-policy' ? 'active' : '' ?>">
                                Gizlilik Politikası <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url($this->uri->segment(1) . '/refund-policy') ?>" class="<?= $this->uri->segment(2) == 'refund-policy' ? 'active' : '' ?>">
                                İptal İade Politikası <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="ps-static-main">
                <div class="ps-static-content-box">
                    <div class="ps-static-body">
<?php
$content = !empty($staticPage->static_page_content) ? $staticPage->static_page_content : '<p>İçerik henüz eklenmemiş.</p>';

// Değişkenleri her durumda tanımla (giriş yapılmamışsa boş gözükecek)
$alici_ad_soyad = ''; 
$alici_eposta = '';
$alici_tckn_vkn = '';
$alici_fatura_adresi = '';
$alici_telefon = '';
$islem_ip_adresi = '';
$onay_tarih_saati = '';

// Eğer kullanıcı giriş yapmışsa, gerçek verilerle doldur
if (!empty($user) && is_array($user)) {
    $aliciFullName = trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''));
    $alici_ad_soyad = $aliciFullName !== '' ? $aliciFullName : ($user['user_name'] ?? '...'); 
    $alici_eposta = $user['user_email'] ?? '...';
    $alici_tckn_vkn = $user['user_bill_tax_number'] ?? '...';
    $alici_fatura_adresi = $user['user_bill_address'] ?? '...';
    $alici_telefon = $user['user_phone'] ?? '...';
    $islem_ip_adresi = getIp();
    $onay_tarih_saati = $user['user_created_at'] ?? '...';

    // Kullanıcı sözleşmesini onaylama logu varsa IP ve Tarihi oradan al
    $agreementLog = $this->db->where('user_logs_user_id', $user['user_id'])
                             ->where('user_logs_user_event', 'USER-AGREEMENT')
                             ->order_by('user_logs_id', 'DESC')
                             ->get('user_logs')->row();
    if ($agreementLog) {
        $islem_ip_adresi = $agreementLog->user_logs_user_ip;
        $onay_tarih_saati = $agreementLog->user_logs_created_at;
    }
}

// Her durumda { {...} } değişkenlerini değiştir (giriş yoksa [Giriş Yapılmadı] yazar)
$content = str_replace(
    ['{{alici_ad_soyad}}', '{{alici_eposta}}','{{alici_tckn_vkn}}','{{alici_fatura_adresi}}','{{alici_telefon}}','{{islem_ip_adresi}}','{{onay_tarih_saati}}'], 
    [$alici_ad_soyad, $alici_eposta, $alici_tckn_vkn, $alici_fatura_adresi, $alici_telefon, $islem_ip_adresi, $onay_tarih_saati], 
    $content
);
?>
                        <?= $content ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
