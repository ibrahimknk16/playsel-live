<?php
$discountRate = (int) site_setting('footer_discount_rate', 15);
$siteName = site_setting('site_name', getenv('SITE_NAME') ?: 'Playsel');
$siteLogo = site_setting('site_logo_url', topupcenter() . 'assets-front/images/logo/playsel-beyaz.webp');
$footerRights = preg_replace('/^\s*\d{4}\s+/u', '', (string)$this->lang->line('footer_all_rights_reserved'));
?>

<footer class="footer" id="footer">
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <div class="footer-logo">
          <a href="<?= base_url($this->uri->segment(1)) ?>">
            <img src="<?= html_escape($siteLogo) ?>" alt="<?= html_escape($siteName . '-Logo') ?>" class="ps-footer-logo"></a>
        </div>
        <div class="footer-company-info">
          <div class="footer-company-item">
            <i class="fa-solid fa-building-shield"></i>
            <div>
              <span class="company-label">Unvan:</span>
              <span class="company-val">Playsel Elektronik Ticaret Limited Şirketi</span>
            </div>
          </div>
          <div class="footer-company-item">
            <i class="fa-solid fa-location-dot"></i>
            <div>
              <span class="company-label">Adres:</span>
              <span class="company-val">Adalet Mh, Anadolu Cd. No 41/1 İç Kapı No: 1402 My Plaza - Bayraklı / İzmir</span>
            </div>
          </div>
        </div>
        <!-- <p class="footer-promo">
          <?=
          str_replace(
            '{discount_rate}',
            $discountRate,
            $this->lang->line('footer_register_discount_offer')
          );
          ?>
        </p>
        <form class="newsletter-form" id="newsletter-form">
          <input type="email" aria-label="Newsletter Email" placeholder="<?= $this->lang->line('view_login_email') ?>" id="newsletter-email">
          <button type="submit" aria-label="Subscribe"><?= $this->lang->line('view_login_text') ?></button>
        </form> -->

        <div style="margin-top: 35px;">
          <h4 style="font-size: 14px; font-weight: 600; color: #fff; margin-bottom: 15px; text-transform: uppercase;"><?= $this->lang->line('footer_payment_methods') ?></h4>
          <div class="footer-payments" style="margin-bottom: 15px; justify-content: flex-start;">
            <div class="payment-icon">VISA</div>
            <div class="payment-icon">AMEX</div>
            <div class="payment-icon">TROY</div>
            <div class="payment-icon">PAPARA</div>
            <div class="payment-icon">EFT</div>
          </div>
        </div>
      </div>
      <div class="footer-col">
        <h4>Popüler Oyunlar</h4>
        <ul>
          <li><a href="<?= base_url($this->uri->segment(1) . '/league-of-legends-rp') ?>">LoL RP</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/metin2-ep') ?>">Metin2 EP</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/valorant-vp') ?>">Valorant VP</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/mobile-legends-elmas') ?>">MLBB Elmas</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/roblox-robux') ?>">Roblox Robux</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/pubg-mobile-uc') ?>">PUBG Mobile UC</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/wartune-ultra-yildiz-parasi') ?>">Wartune Ultra Yıldız Parası</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Sözleşmeler</h4>
        <ul>
          <li><a href="<?= base_url($this->uri->segment(1) . '/privacy-policy') ?>">Gizlilik Politikası</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/refund-policy') ?>">İptal İade Politikası</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/aml-kyc-policy') ?>">AML&KYC Politikası</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/user-agreement') ?>">Kullanıcı Sözleşmesi</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/kvkk-policy') ?>">KVKK Aydınlatma Metni</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/cookie-policy') ?>">Çerez Politikası (K.V.K.K)</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/distance-sales-agreement') ?>">Mesafeli Satış Sözleşmesi</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Kurumsal</h4>
        <ul>
          <li><a href="<?= base_url($this->uri->segment(1) . '/contact') ?>">İletişim</a></li>
          <li><a href="<?= base_url($this->uri->segment(1) . '/about-us') ?>">Hakkımızda</a></li>
        </ul>
        
        <div style="margin-top: 35px;">
          <div class="footer-social-label" style="text-align: left; margin-bottom: 15px;"><?= $this->lang->line('footer_follow_us') ?></div>
          <div class="footer-social" style="justify-content: flex-start;">
            <a href="<?= html_escape(site_setting('social_facebook_url', 'https://facebook.com/playsel0')) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= html_escape($siteName) ?> Facebook">
              <i class="fab fa-facebook"></i>
            </a>
            <a href="<?= html_escape(site_setting('social_instagram_url', 'https://instagram.com/playsel')) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= html_escape($siteName) ?> Instagram">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="<?= html_escape(site_setting('social_tiktok_url', 'https://tiktok.com/@playsel.com')) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= html_escape($siteName) ?> TikTok">
              <i class="fab fa-tiktok"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy;<?= html_escape($footerRights) ?></p>
    </div>
  </div>
</footer>

<style>
.footer-company-info {
  margin: 16px 0 22px;
  padding: 12px 14px;
  background: linear-gradient(145deg, rgba(35, 36, 47, 0.6), rgba(22, 25, 32, 0.6));
  border: 1px solid rgba(255, 179, 0, 0.14);
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  gap: 9px;
  max-width: 420px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
}
.footer-company-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-size: 12.5px;
  line-height: 1.5;
  color: #94a3b8;
}
.footer-company-item i {
  color: #ffb800;
  font-size: 13px;
  margin-top: 3px;
  flex-shrink: 0;
}
.footer-company-item .company-label {
  font-weight: 600;
  color: #e2e8f0;
  margin-right: 4px;
}
.footer-company-item .company-val {
  color: #cbd5e1;
}

.ps-cookie-consent {
    position: fixed;
    bottom: -100px;
    left: 20px;
    max-width: 400px;
    background: #11151d;
    border: 1px solid rgba(255, 255, 255, 0.05);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    border-radius: 12px;
    padding: 20px;
    z-index: 9999;
    color: #fff;
    opacity: 0;
    visibility: hidden;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
.ps-cookie-consent.show {
    bottom: 20px;
    opacity: 1;
    visibility: visible;
}
.ps-cookie-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    font-weight: 600;
    font-size: 16px;
    color: #ffb800; /* accent yellow */
}
.ps-cookie-text {
    font-size: 13px;
    line-height: 1.5;
    color: #a0aec0;
    margin-bottom: 15px;
}
.ps-cookie-text a {
    color: #ffb800;
    text-decoration: underline;
    font-weight: 500;
}
.ps-cookie-buttons {
    display: flex;
    justify-content: flex-end;
}
.ps-cookie-btn {
    background: #ffb800;
    color: #11151d;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, opacity 0.2s;
}
.ps-cookie-btn:hover {
    opacity: 0.9;
    transform: scale(1.02);
}
@media(max-width: 576px) {
    .ps-cookie-consent {
        left: 15px;
        right: 15px;
        max-width: none;
    }
}
</style>

<div class="ps-cookie-consent" id="cookieConsent">
    <div class="ps-cookie-header">
        <i class="fa-solid fa-cookie-bite"></i> Çerez (Cookie) Tercihleri
    </div>
    <div class="ps-cookie-text">
        Sitemizde size daha iyi bir deneyim sunmak ve güvenliğinizi sağlamak için çerezleri kullanıyoruz. Sitemizi kullanmaya devam ederek 
        <a href="<?= base_url($this->uri->segment(1) . '/cookie-policy') ?>">Çerez Politikamızı</a> kabul etmiş sayılırsınız.
    </div>
    <div class="ps-cookie-buttons">
        <button class="ps-cookie-btn" id="btnAcceptCookie">Kabul Et</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var consent = localStorage.getItem('ps_cookie_accepted');
    if(!consent) {
        setTimeout(function(){
            document.getElementById('cookieConsent').classList.add('show');
        }, 1500); // Show after 1.5 seconds for smooth entry
    }
    
    document.getElementById('btnAcceptCookie').addEventListener('click', function(){
        localStorage.setItem('ps_cookie_accepted', '1');
        document.getElementById('cookieConsent').classList.remove('show');
    });
});
</script>