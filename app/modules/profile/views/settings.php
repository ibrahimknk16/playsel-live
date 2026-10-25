<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-settings" data-view="settings">
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-settings"/></svg></span>
            <div class="hero-copy">
                <h1><?= $this->lang->line('profile_settings') ?? 'Ayarlar' ?></h1>
                <p><?= $this->lang->line('profile_settings_desc') ?? 'Hesap bilgilerinizi güncelleyin ve güvenlik tercihlerinizi yönetin.' ?></p>
            </div>
        </header>

        <section class="subnav-shell">
          <div class="settings-tabs" role="tablist" aria-label="Hesap ayarları">
            <button class="tab-button active" type="button" data-settings-tab="profile"><svg class="icon"><use href="#i-user"/></svg><?= $this->lang->line('profile_settings_tab_1') ?? 'Profil bilgileri' ?></button>
            <button class="tab-button" type="button" data-settings-tab="security"><svg class="icon"><use href="#i-shield"/></svg><?= $this->lang->line('profile_settings_tab_2') ?? 'Güvenlik tercihleri' ?></button>
            <button class="tab-button" type="button" data-settings-tab="password"><svg class="icon"><use href="#i-lock"/></svg><?= $this->lang->line('profile_settings_tab_3') ?? 'Şifre değişikliği' ?></button>
            <button class="tab-button" type="button" data-settings-tab="billing"><svg class="icon"><use href="#i-file"/></svg><?= $this->lang->line('profile_settings_tab_4') ?? 'Fatura bilgilerim' ?></button>
            <button class="tab-button" type="button" data-settings-tab="referral"><svg class="icon"><use href="#i-user"/></svg><?= $this->lang->line('profile_settings_tab_5') ?? 'Referans ayarları' ?></button>
          </div>

          <div class="settings-body">
            <!-- Profil Bilgileri -->
            <section class="settings-panel" data-settings-panel="profile">
              <h2><?= $this->lang->line('profile_settings_tab_1') ?? 'Profil bilgileri' ?></h2>
              <div class="info-banner"><svg class="icon"><use href="#i-info"/></svg><?= $this->lang->line('profile_settings_info_desc') ?? 'Hesabınızı güncel tutmak için kişisel bilgilerinizi buradan düzenleyebilirsiniz.' ?></div>
              
              <?php
                  $firstName = isset($user_details->user_name) ? trim($user_details->user_name) : '';
                  $lastName = isset($user_details->user_surname) ? trim($user_details->user_surname) : '';
                  if ($lastName === '' && $firstName !== '') {
                      $parts = explode(' ', $firstName);
                      if (count($parts) > 1) {
                          $lastName = array_pop($parts);
                          $firstName = implode(' ', $parts);
                      }
                  }
              ?>
              <form action="<?= base_url($this->uri->segment(1) . '/profile/updateProfile') ?>" method="POST">
                  <div class="form-grid">
                      <div class="field">
                          <label for="first-name">İsim *</label>
                          <input id="first-name" name="first_name" value="<?= html_escape($firstName) ?>" required>
                      </div>
                      <div class="field">
                          <label for="last-name">Soy isim *</label>
                          <input id="last-name" name="last_name" value="<?= html_escape($lastName) ?>" required>
                      </div>
                      <div class="field">
                          <label for="identity">T.C. kimlik numarası <span class="subtle">isteğe bağlı</span></label>
                          <input id="identity" name="user_bill_tax_number" inputmode="numeric" value="<?= isset($user_details->user_bill_tax_number) ? html_escape($user_details->user_bill_tax_number) : '' ?>" placeholder="11 haneli T.C. kimlik numaranız" maxlength="11">
                      </div>
                      <div class="field">
                          <label for="birth-date">Doğum tarihi <span class="subtle">isteğe bağlı</span></label>
                          <input id="birth-date" name="user_birthday" type="date" value="<?= isset($user_details->user_birthday) ? html_escape($user_details->user_birthday) : '' ?>">
                      </div>
                  </div>
                  <div class="form-actions">
                      <button class="primary-button" type="submit"><svg class="icon"><use href="#i-check"/></svg><?= $this->lang->line('profile_settings_save') ?? 'Kaydet' ?></button>
                  </div>
              </form>

              <hr class="divider">
              <h2>Nickname</h2>
              <p class="subtle">Nickname değişiklikleri yönetici onayından sonra uygulanır.</p>
              <p><strong><?= html_escape($user_details->user_nickname ?? '') ?></strong></p>
              <?php if (!empty($nickname_request)): ?>
                  <p class="subtle">Bekleyen talep: <strong><?= html_escape($nickname_request->nickname_request_value) ?></strong></p>
              <?php else: ?>
                  <form action="<?= base_url($this->uri->segment(1) . '/profile/requestNicknameChange') ?>" method="POST" class="form-grid">
                      <div class="field">
                          <label for="user-nickname">Yeni nickname</label>
                          <input id="user-nickname" name="user_nickname" minlength="3" maxlength="50" pattern="[a-z][a-z0-9_]{2,49}" required>
                      </div>
                      <div class="form-actions">
                          <button class="primary-button" type="submit">Değişiklik talep et</button>
                      </div>
                  </form>
              <?php endif; ?>
              
              <hr class="divider">
              <h2><?= $this->lang->line('profile_settings_contact') ?? 'İletişim bilgileri' ?></h2>
              <p class="subtle"><?= $this->lang->line('profile_settings_contact_desc') ?? 'İletişim bilgilerinizi buradan güncelleyebilirsiniz.' ?></p>
              
              <div class="contact-grid">
                  <article class="contact-card">
                      <div class="contact-head">
                          <strong><svg class="icon"><use href="#i-phone"/></svg><?= $this->lang->line('profile_settings_phone') ?? 'Telefon' ?></strong>
                          <?php if(isset($user_details->user_phone_is_verified) && $user_details->user_phone_is_verified == 1): ?>
                              <span class="verified">✓ Onaylı</span>
                          <?php else: ?>
                              <span class="verified" style="color:#ffad65;">Onaysız</span>
                          <?php endif; ?>
                      </div>
                      <div class="contact-value" id="phone-display">
                          <span><?= !empty($user_details->user_phone) ? html_escape($user_details->user_phone) : 'Belirtilmedi' ?></span>
                          <button class="icon-button" type="button" onclick="document.getElementById('phone-display').hidden=true;document.getElementById('form_phone').hidden=false;" aria-label="Telefonu düzenle">✎</button>
                      </div>
                      <form id="form_phone" class="contact-value" hidden action="<?= base_url($this->uri->segment(1) . '/profile/updateContactAjax') ?>" method="POST" onsubmit="return submitContactUpdate(event, 'phone')" style="display:flex;flex-direction:column;gap:10px;">
                          <input type="hidden" name="type" value="phone">
                          <div style="display:flex;gap:10px;align-items:center;width:100%;">
                              <input type="tel" id="input_phone" name="user_phone" value="<?= !empty($user_details->user_phone) ? html_escape($user_details->user_phone) : '' ?>" required style="padding:6px 10px;border-radius:6px;border:1px solid #333;background:#1e1c16;color:#fff;width:100%;">
                              <button type="submit" id="btn_phone" class="icon-button" style="color:var(--accent);" aria-label="Kaydet"><svg class="icon"><use href="#i-check"/></svg></button>
                              <button type="button" class="icon-button" onclick="document.getElementById('phone-display').hidden=false;document.getElementById('form_phone').hidden=true;" aria-label="İptal"><svg class="icon"><use href="#i-x"/></svg></button>
                          </div>
                          <?php if(!empty($user_details->user_phone_temp)): ?>
                              <small style="color: #ffad65;">Bekleyen numara değişikliği: <strong><?= html_escape($user_details->user_phone_temp) ?></strong></small>
                          <?php endif; ?>
                      </form>
                      <small class="subtle">SMS ile doğrulama</small>
                  </article>
                  <article class="contact-card">
                      <div class="contact-head">
                          <strong><svg class="icon"><use href="#i-mail"/></svg><?= $this->lang->line('profile_settings_email') ?? 'E-posta adresi' ?></strong>
                          <?php if(isset($user_details->user_email_is_verified) && $user_details->user_email_is_verified == 1): ?>
                              <span class="verified">✓ Onaylı</span>
                          <?php else: ?>
                              <span class="verified" style="color:#ffad65;">Onaysız</span>
                          <?php endif; ?>
                      </div>
                      <div class="contact-value" id="email-display">
                          <span><?= !empty($user_details->user_email) ? html_escape($user_details->user_email) : 'Belirtilmedi' ?></span>
                          <button class="icon-button" type="button" onclick="document.getElementById('email-display').hidden=true;document.getElementById('form_email').hidden=false;" aria-label="E-postayı düzenle">✎</button>
                      </div>
                      <form id="form_email" class="contact-value" hidden action="<?= base_url($this->uri->segment(1) . '/profile/updateContactAjax') ?>" method="POST" onsubmit="return submitContactUpdate(event, 'email')" style="display:flex;flex-direction:column;gap:10px;">
                          <input type="hidden" name="type" value="email">
                          <div style="display:flex;gap:10px;align-items:center;width:100%;">
                              <input type="email" id="input_email" name="user_email" value="<?= !empty($user_details->user_email) ? html_escape($user_details->user_email) : '' ?>" required style="padding:6px 10px;border-radius:6px;border:1px solid #333;background:#1e1c16;color:#fff;width:100%;">
                              <button type="submit" id="btn_email" class="icon-button" style="color:var(--accent);" aria-label="Kaydet"><svg class="icon"><use href="#i-check"/></svg></button>
                              <button type="button" class="icon-button" onclick="document.getElementById('email-display').hidden=false;document.getElementById('form_email').hidden=true;" aria-label="İptal"><svg class="icon"><use href="#i-x"/></svg></button>
                          </div>
                          <?php if(!empty($user_details->user_email_temp)): ?>
                              <small style="color: #ffad65;">Bekleyen e-posta değişikliği: <strong><?= html_escape($user_details->user_email_temp) ?></strong></small>
                          <?php endif; ?>
                      </form>
                      <small class="subtle">E-posta ile doğrulama</small>
                  </article>
              </div>
            </section>

            <!-- Güvenlik Tercihleri -->
            <section class="settings-panel" data-settings-panel="security" hidden>
              <h2>İki aşamalı doğrulama (2FA)</h2>
              <p class="subtle">Hesabınıza giriş yaparken ek güvenlik katmanı oluşturmak için bir doğrulama yöntemi seçin.</p>
              
              <form action="<?= base_url($this->uri->segment(1) . '/profile/updateSecurityRequest') ?>" method="POST" id="form_security" onsubmit="return submitSecurityUpdate(event)">
                  <div class="radio-grid" role="radiogroup" aria-label="İki aşamalı doğrulama tercihi">
                      <label class="radio-choice <?= (!isset($user_details->user_2fa_method) || $user_details->user_2fa_method == 0) ? 'active' : '' ?>">
                          <input type="radio" name="user_2fa_method" value="0" style="display:none;" <?= (!isset($user_details->user_2fa_method) || $user_details->user_2fa_method == 0) ? 'checked' : '' ?>>
                          <svg class="icon"><use href="#i-x"/></svg>Devre dışı
                      </label>
                      <label class="radio-choice <?= (isset($user_details->user_2fa_method) && $user_details->user_2fa_method == 1) ? 'active' : '' ?>">
                          <input type="radio" name="user_2fa_method" value="1" style="display:none;" <?= (isset($user_details->user_2fa_method) && $user_details->user_2fa_method == 1) ? 'checked' : '' ?>>
                          <svg class="icon"><use href="#i-mail"/></svg>E-posta ile doğrulama
                      </label>
                      <label class="radio-choice <?= (isset($user_details->user_2fa_method) && $user_details->user_2fa_method == 2) ? 'active' : '' ?>">
                          <input type="radio" name="user_2fa_method" value="2" style="display:none;" <?= (isset($user_details->user_2fa_method) && $user_details->user_2fa_method == 2) ? 'checked' : '' ?>>
                          <svg class="icon"><use href="#i-phone"/></svg>SMS ile doğrulama
                      </label>
                  </div>
                  <hr class="divider">
                  <button class="primary-button" type="submit"><svg class="icon"><use href="#i-lock"/></svg><?= $this->lang->line('profile_settings_save') ?? 'Kaydet' ?></button>
              </form>
            </section>

            <!-- Şifre Değişikliği -->
            <section class="settings-panel" data-settings-panel="password" hidden>
              <h2><?= $this->lang->line('profile_settings_tab_3') ?? 'Şifre değişikliği' ?></h2>
              <form action="<?= base_url($this->uri->segment(1) . '/profile/changePasswordRequest') ?>" method="POST">
                  <div class="form-grid">
                      <div class="field">
                          <label for="current-password"><?= $this->lang->line('profile_settings_current_password') ?? 'Mevcut şifreniz' ?></label>
                          <input id="current-password" name="current_password" type="password" autocomplete="current-password" placeholder="<?= $this->lang->line('profile_settings_current_password_placeholder') ?? 'Mevcut şifrenizi girin' ?>" required>
                      </div>
                      <div></div>
                      <div class="field">
                          <label for="new-password"><?= $this->lang->line('profile_settings_new_password') ?? 'Yeni şifre' ?></label>
                          <input id="new-password" name="new_password" type="password" autocomplete="new-password" placeholder="<?= $this->lang->line('profile_settings_new_password_placeholder') ?? 'Yeni şifrenizi belirleyin' ?>" required>
                      </div>
                      <div class="field">
                          <label for="confirm-password"><?= $this->lang->line('profile_settings_new_password_confirm') ?? 'Yeni şifre (tekrar)' ?></label>
                          <input id="confirm-password" name="confirm_password" type="password" autocomplete="new-password" placeholder="<?= $this->lang->line('profile_settings_new_password_confirm_placeholder') ?? 'Yeni şifrenizi tekrar girin' ?>" required>
                      </div>
                  </div>
                  <div class="form-actions">
                      <button class="primary-button" type="submit"><svg class="icon"><use href="#i-lock"/></svg><?= $this->lang->line('profile_settings_password_save') ?? 'Şifremi güncelle' ?></button>
                  </div>
              </form>
            </section>

            <!-- Fatura Bilgileri -->
            <section class="settings-panel" data-settings-panel="billing" hidden>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                <div>
                  <h2 style="margin:0;"><?= $this->lang->line('profile_settings_tab_4') ?? 'Fatura Bilgilerim' ?></h2>
                  <p style="margin:4px 0 0; color:#8fa0b5; font-size:12px;"><?= $this->lang->line('profile_settings_billing_desc') ?? 'Satın alımlarınızda kullanılacak fatura adreslerinizi buradan yönetebilirsiniz.' ?></p>
                </div>
                <button type="button" class="primary-button" id="btnAddNewAddress" onclick="openNewAddressForm()" style="min-height:36px; padding:0 14px; font-size:12px;">
                  <svg class="icon"><use href="#i-plus"/></svg> Yeni Adres Ekle
                </button>
              </div>

              <?php
              $addressList = !empty($user_addresses) ? $user_addresses : [];
              // Eğer user_addresses tablosunda henüz kayıt yoksa ama users tablosunda eski fatura verisi varsa geçici olarak ekle
              if (empty($addressList) && (!empty($user_details->user_bill_city) || !empty($user_details->user_bill_address))) {
                  $legacyObj = (object)[
                      'user_addresses_id' => 0,
                      'user_addresses_title' => 'Fatura Adresi',
                      'user_addresses_invoice_type' => !empty($user_details->user_bill_tax_office) ? 2 : 1,
                      'user_addresses_full_name' => $user_details->user_bill_company_name ?? trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? '')),
                      'user_addresses_company_name' => $user_details->user_bill_company_name ?? '',
                      'user_addresses_tax_number' => $user_details->user_bill_tax_number ?? '',
                      'user_addresses_tax_office' => $user_details->user_bill_tax_office ?? '',
                      'user_addresses_is_turkish_citizen' => (strlen(preg_replace('/\D+/', '', (string)($user_details->user_bill_tax_number ?? ''))) === 11 || empty($user_details->user_bill_tax_number)) ? 1 : 0,
                      'user_addresses_is_turkish_company' => (int)($user_details->user_is_turkish_company ?? 1),
                      'user_addresses_is_e_invoice_taxpayer' => (int)($user_details->user_bill_e_invoice ?? 0),
                      'user_addresses_country' => 'Türkiye',
                      'user_addresses_city' => $user_details->user_bill_city ?? '',
                      'user_addresses_district' => $user_details->user_bill_district ?? '',
                      'user_addresses_postal_code' => '',
                      'user_addresses_address' => $user_details->user_bill_address ?? '',
                      'user_addresses_phone' => $user_details->user_phone ?? $user['user_phone'] ?? '',
                      'user_addresses_is_default' => 1
                  ];
                  $addressList = [$legacyObj];
              }

              $hasAddresses = !empty($addressList);
              $defaultAddr = !empty($addressList) ? $addressList[0] : null;

              $invType = (int)($defaultAddr->user_addresses_invoice_type ?? 1);
              $isTcCitizen = (int)($defaultAddr->user_addresses_is_turkish_citizen ?? 1);
              $isTcCompany = (int)($defaultAddr->user_addresses_is_turkish_company ?? 1);
              $isEInvoice = (int)($defaultAddr->user_addresses_is_e_invoice_taxpayer ?? 0);

              $userFullName = trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''));
              $valFullName = !empty($defaultAddr->user_addresses_full_name) ? $defaultAddr->user_addresses_full_name : ($userFullName !== '' ? $userFullName : '');
              $valCompanyName = !empty($defaultAddr->user_addresses_company_name) ? $defaultAddr->user_addresses_company_name : '';
              $valTaxNumber = !empty($defaultAddr->user_addresses_tax_number) ? $defaultAddr->user_addresses_tax_number : '';
              $valTaxOffice = !empty($defaultAddr->user_addresses_tax_office) ? $defaultAddr->user_addresses_tax_office : '';
              $valCity = !empty($defaultAddr->user_addresses_city) ? $defaultAddr->user_addresses_city : '';
              $valDistrict = !empty($defaultAddr->user_addresses_district) ? $defaultAddr->user_addresses_district : '';
              $valCountry = !empty($defaultAddr->user_addresses_country) ? $defaultAddr->user_addresses_country : 'Türkiye';
              $valPostalCode = !empty($defaultAddr->user_addresses_postal_code) ? $defaultAddr->user_addresses_postal_code : '';
              $valAddress = !empty($defaultAddr->user_addresses_address) ? $defaultAddr->user_addresses_address : '';
              $valPhone = !empty($defaultAddr->user_addresses_phone) ? $defaultAddr->user_addresses_phone : ($user_details->user_phone ?? $user['user_phone'] ?? '');
              $valTitle = !empty($defaultAddr->user_addresses_title) ? $defaultAddr->user_addresses_title : 'Fatura Adresi';
              ?>

              <!-- Kayıtlı Adresler Listesi -->
              <?php if ($hasAddresses): ?>
                <div class="saved-addresses-grid" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:14px; margin-bottom:20px;">
                  <?php foreach ($addressList as $addr): ?>
                    <div class="address-card <?= ($addr->user_addresses_is_default == 1) ? 'is-default' : '' ?>" style="border:1px solid <?= ($addr->user_addresses_is_default == 1) ? '#c79a3e' : '#2b3242' ?>; border-radius:12px; background:#121620; padding:15px; display:flex; flex-direction:column; justify-content:space-between; position:relative;">
                      <div>
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                          <strong style="color:#fff; font-size:13px; display:flex; align-items:center; gap:6px;">
                            <svg class="icon" style="width:14px;height:14px;color:var(--accent);"><use href="#i-file"/></svg>
                            <?= html_escape($addr->user_addresses_title ?: 'Fatura Adresi') ?>
                          </strong>
                          <div style="display:flex; gap:5px;">
                            <?php if ($addr->user_addresses_is_default == 1): ?>
                              <span style="font-size:10px; font-weight:700; background:#2a2110; color:#ffd66d; border:1px solid #5f4b1d; padding:2px 7px; border-radius:99px;">Varsayılan</span>
                            <?php endif; ?>
                            <span style="font-size:10px; font-weight:600; background:#181e2d; color:#8fa0b5; border:1px solid #283147; padding:2px 7px; border-radius:99px;">
                              <?= $addr->user_addresses_invoice_type == 2 ? 'Kurumsal' : 'Bireysel' ?>
                            </span>
                          </div>
                        </div>

                        <div style="font-size:13px; color:#edf0f2; font-weight:600; margin-bottom:4px;">
                          <?= html_escape($addr->user_addresses_invoice_type == 2 ? $addr->user_addresses_company_name : $addr->user_addresses_full_name) ?>
                        </div>

                        <?php if (!empty($addr->user_addresses_tax_number)): ?>
                          <div style="font-size:11px; color:#8fa0b5; margin-bottom:4px;">
                            <?= $addr->user_addresses_invoice_type == 2 ? 'VKN: ' : 'TCKN: ' ?>
                            <span style="color:#ccd5df; font-weight:600;"><?= html_escape($addr->user_addresses_tax_number) ?></span>
                            <?php if (!empty($addr->user_addresses_tax_office)): ?>
                              <span style="color:#6f7f91;">(<?= html_escape($addr->user_addresses_tax_office) ?>)</span>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>

                        <div style="font-size:12px; color:#9db0c2; line-height:1.4; margin-bottom:6px; word-break:break-word;">
                          <?= nl2br(html_escape($addr->user_addresses_address)) ?>
                        </div>
                        
                        <div style="font-size:11px; color:#c79a3e; font-weight:600;">
                          <?= html_escape($addr->user_addresses_district . ', ' . $addr->user_addresses_city . (!empty($addr->user_addresses_country) && $addr->user_addresses_country !== 'Türkiye' ? ' / ' . $addr->user_addresses_country : '')) ?>
                        </div>

                        <?php 
                          $addrPhone = !empty($addr->user_addresses_phone) ? $addr->user_addresses_phone : ($user_details->user_phone ?? $user['user_phone'] ?? '');
                        ?>
                        <?php if (!empty($addrPhone)): ?>
                          <div style="font-size:11px; color:#8fa0b5; margin-top:5px; display:flex; align-items:center; gap:5px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;color:#c79a3e;flex-shrink:0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span style="color:#d1dbe5; font-weight:500;"><?= html_escape($addrPhone) ?></span>
                          </div>
                        <?php endif; ?>
                      </div>

                      <div style="display:flex; align-items:center; justify-content:space-between; margin-top:14px; padding-top:10px; border-top:1px solid rgba(255,255,255,0.06); gap:8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                          <button type="button" class="addr-action-btn" onclick="editAddress(<?= (int)$addr->user_addresses_id ?>)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Düzenle</span>
                          </button>
                          <?php if ($addr->user_addresses_is_default != 1 && !empty($addr->user_addresses_id)): ?>
                            <a href="<?= base_url($this->uri->segment(1) . '/profile/setDefaultAddress/' . $addr->user_addresses_id) ?>" class="addr-action-btn is-default-btn">
                              <span>Varsayılan Yap</span>
                            </a>
                          <?php endif; ?>
                        </div>
                        <?php if (!empty($addr->user_addresses_id)): ?>
                          <a href="<?= base_url($this->uri->segment(1) . '/profile/deleteAddress/' . $addr->user_addresses_id) ?>" onclick="return confirm('Bu adresi silmek istediğinize emin misiniz?')" class="addr-delete-btn" title="Adresi Sil">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <!-- Adres Formu (Ekleme / Düzenleme) -->
              <div id="addressFormContainer" style="<?= $hasAddresses ? 'display:none;' : 'display:block;' ?> background:rgba(255,255,255,0.02); border:1px solid #2b3242; border-radius:12px; padding:18px; margin-top:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:10px;">
                  <h3 id="addressFormHeading" style="font-size:14px; font-weight:700; color:#c79a3e; margin:0;">
                    <?= $hasAddresses ? 'Adres Ekle / Düzenle' : 'Fatura Adresi Ekle' ?>
                  </h3>
                  <?php if ($hasAddresses): ?>
                    <button type="button" class="btn-quiet" onclick="cancelAddressForm()" style="font-size:11px; padding:2px 8px; height:24px;">Vazgeç</button>
                  <?php endif; ?>
                </div>

                <form id="billingFormSettings" action="<?= base_url($this->uri->segment(1) . '/profile/updateBilling') ?>" method="POST">
                    <input type="hidden" name="user_addresses_id" id="input_user_addresses_id" value="">

                    <!-- Adres Başlığı & Varsayılan Seçeneği -->
                    <div style="display:grid; grid-template-columns:1fr auto; gap:14px; align-items:center; margin-bottom:14px;">
                        <div class="field" style="margin:0;">
                            <label for="invoice-title">Adres Başlığı *</label>
                            <input id="invoice-title" name="user_addresses_title" value="Fatura Adresi" placeholder="Örn: Ev Adresim, İş / Şirket Adresi" required>
                        </div>
                        <label style="display:flex; align-items:center; gap:8px; font-size:12px; color:#adb8c4; cursor:pointer; margin-top:18px;">
                            <input type="checkbox" name="user_addresses_is_default" id="input_user_addresses_is_default" value="1" checked style="width:16px; height:16px; accent-color:#c79a3e;">
                            Varsayılan fatura adresim yap
                        </label>
                    </div>

                    <!-- 1. Fatura Türü -->
                    <div class="billing-radio-group" role="radiogroup" aria-label="Fatura Türü" style="display:flex; gap:10px; margin-bottom:12px;">
                        <label class="radio-choice <?= $invType === 1 ? 'active' : '' ?>" style="flex:1; min-height:42px; padding:10px 14px; font-size:13px; font-weight:600;">
                            <input type="radio" name="user_addresses_invoice_type" value="1" <?= $invType === 1 ? 'checked' : '' ?> style="display:none;">
                            <svg class="icon"><use href="#i-user"/></svg>
                            Bireysel Fatura
                        </label>
                        <label class="radio-choice <?= $invType === 2 ? 'active' : '' ?>" style="flex:1; min-height:42px; padding:10px 14px; font-size:13px; font-weight:600;">
                            <input type="radio" name="user_addresses_invoice_type" value="2" <?= $invType === 2 ? 'checked' : '' ?> style="display:none;">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/></svg>
                            Kurumsal Fatura
                        </label>
                    </div>

                    <!-- 2. Vatandaşlık Durumu (Bireysel) -->
                    <div id="citizen_status_group" class="billing-radio-group" role="radiogroup" aria-label="Vatandaşlık Durumu" style="display:<?= $invType === 1 ? 'flex' : 'none' ?>; gap:10px; margin-bottom:16px;">
                        <label class="radio-choice <?= $isTcCitizen === 1 ? 'active' : '' ?>" style="flex:1; min-height:38px; padding:8px 12px; font-size:12px;">
                            <input type="radio" name="user_addresses_is_turkish_citizen" value="1" <?= $isTcCitizen === 1 ? 'checked' : '' ?> style="display:none;">
                            <svg class="icon"><use href="#i-check"/></svg>
                            T.C. Vatandaşıyım
                        </label>
                        <label class="radio-choice <?= $isTcCitizen === 0 ? 'active' : '' ?>" style="flex:1; min-height:38px; padding:8px 12px; font-size:12px;">
                            <input type="radio" name="user_addresses_is_turkish_citizen" value="0" <?= $isTcCitizen === 0 ? 'checked' : '' ?> style="display:none;">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            T.C. Vatandaşı Değilim (Yabancı)
                        </label>
                    </div>

                    <!-- 2. Firma Durumu (Kurumsal) -->
                    <div id="company_status_group" class="billing-radio-group" role="radiogroup" aria-label="Firma Durumu" style="display:<?= $invType === 2 ? 'flex' : 'none' ?>; gap:10px; margin-bottom:16px;">
                        <label class="radio-choice <?= $isTcCompany === 1 ? 'active' : '' ?>" style="flex:1; min-height:38px; padding:8px 12px; font-size:12px;">
                            <input type="radio" name="user_addresses_is_turkish_company" value="1" <?= $isTcCompany === 1 ? 'checked' : '' ?> style="display:none;">
                            <svg class="icon"><use href="#i-check"/></svg>
                            Türkiye Firması
                        </label>
                        <label class="radio-choice <?= $isTcCompany === 0 ? 'active' : '' ?>" style="flex:1; min-height:38px; padding:8px 12px; font-size:12px;">
                            <input type="radio" name="user_addresses_is_turkish_company" value="0" <?= $isTcCompany === 0 ? 'checked' : '' ?> style="display:none;">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            Yurtdışı Firması
                        </label>
                    </div>

                    <div class="form-grid">
                        <!-- Ad Soyad (Bireysel) -->
                        <div class="field" id="field-fullname">
                            <label for="invoice-fullname">Ad Soyad *</label>
                            <input id="invoice-fullname" name="user_addresses_full_name" value="<?= html_escape($valFullName) ?>" placeholder="Adınız Soyadınız">
                        </div>

                        <!-- Firma Unvanı (Kurumsal) -->
                        <div class="field" id="field-companyname" style="display:none;">
                            <label for="invoice-companyname">Firma Unvanı *</label>
                            <input id="invoice-companyname" name="user_addresses_company_name" value="<?= html_escape($valCompanyName) ?>" placeholder="Şirket Resmi Unvanı">
                        </div>

                        <!-- Vergi Numarası / TCKN -->
                        <div class="field" id="field-taxnumber">
                            <label id="bill_tax_number_label" for="invoice-taxnumber">T.C. Kimlik Numarası *</label>
                            <input id="invoice-taxnumber" name="user_addresses_tax_number" value="<?= html_escape($valTaxNumber) ?>" placeholder="11 haneli T.C. Kimlik No">
                        </div>

                        <!-- Vergi Dairesi (Kurumsal) -->
                        <div class="field corporate-field" id="field-taxoffice" style="display:none;">
                            <label for="invoice-taxoffice">Vergi Dairesi *</label>
                            <input id="invoice-taxoffice" name="user_addresses_tax_office" value="<?= html_escape($valTaxOffice) ?>" placeholder="Vergi dairesi adı">
                        </div>

                        <!-- E-Fatura (Kurumsal & TR Firması) -->
                        <div class="field corporate-field" id="field-einvoice" style="display:none;">
                            <label for="invoice-einvoice">E-Fatura Durumu</label>
                            <select id="invoice-einvoice" name="user_addresses_is_e_invoice_taxpayer">
                                <option value="0" <?= $isEInvoice === 0 ? 'selected' : '' ?>>E-Fatura mükellefi değilim</option>
                                <option value="1" <?= $isEInvoice === 1 ? 'selected' : '' ?>>E-Fatura mükellefiyim</option>
                            </select>
                        </div>

                        <!-- Türkiye Konum Alanları (İl & İlçe - Select2) -->
                        <div class="field location-tr" id="field-city-tr">
                            <label for="invoice-city"><?= $this->lang->line('profile_settings_city') ?? 'İl' ?> *</label>
                            <select id="invoice-city" name="user_addresses_city">
                                <option value="">İl seçiniz</option>
                            </select>
                        </div>
                        <div class="field location-tr" id="field-district-tr">
                            <label for="invoice-district"><?= $this->lang->line('profile_settings_district') ?? 'İlçe' ?> *</label>
                            <select id="invoice-district" name="user_addresses_district" disabled>
                                <option value="">Önce il seçiniz</option>
                            </select>
                        </div>

                        <!-- Yurtdışı Konum Alanları (Ülke, Şehir, İlçe/Bölge) -->
                        <div class="field location-foreign" id="field-country" style="display:none;">
                            <label for="invoice-country">Ülke *</label>
                            <input id="invoice-country" name="user_addresses_country" value="<?= html_escape($valCountry !== 'Türkiye' ? $valCountry : '') ?>" placeholder="Örn: Almanya, KKTC, ABD">
                        </div>
                        <div class="field location-foreign" id="field-city-foreign" style="display:none;">
                            <label for="invoice-foreign-city">Şehir *</label>
                            <input id="invoice-foreign-city" name="user_addresses_foreign_city" value="<?= html_escape($valCity) ?>" placeholder="Şehir">
                        </div>
                        <div class="field location-foreign" id="field-district-foreign" style="display:none;">
                            <label for="invoice-foreign-district">İlçe / Eyalet / Bölge *</label>
                            <input id="invoice-foreign-district" name="user_addresses_foreign_district" value="<?= html_escape($valDistrict) ?>" placeholder="İlçe / Bölge">
                        </div>

                        <!-- Posta Kodu -->
                        <div class="field" id="field-postal-code">
                            <label for="invoice-postal-code">Posta Kodu <span class="subtle">isteğe bağlı</span></label>
                            <input id="invoice-postal-code" name="user_addresses_postal_code" value="<?= html_escape($valPostalCode) ?>" placeholder="Posta Kodu">
                        </div>

                        <!-- Telefon Numarası -->
                        <div class="field" id="field-address-phone">
                            <label for="invoice-phone">Telefon Numarası *</label>
                            <input id="invoice-phone" type="tel" name="user_addresses_phone" value="<?= html_escape($valPhone) ?>" placeholder="Örn: 05XXXXXXXXX" required>
                        </div>

                        <!-- Açık Adres -->
                        <div class="field full">
                            <label for="invoice-address"><?= $this->lang->line('profile_settings_address') ?? 'Açık adres' ?> *</label>
                            <textarea id="invoice-address" name="user_addresses_address" rows="3" required placeholder="Cadde, sokak, bina ve kapı no..."><?= html_escape($valAddress) ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top:16px; display:flex; gap:10px;">
                        <button class="primary-button" type="submit"><svg class="icon"><use href="#i-check"/></svg>Adresi Kaydet</button>
                        <?php if ($hasAddresses): ?>
                          <button type="button" class="btn-quiet" onclick="cancelAddressForm()" style="min-height:38px; padding:0 14px;">Vazgeç</button>
                        <?php endif; ?>
                    </div>
                </form>
              </div>
            </section>

            <!-- Referans Ayarları -->
            <section class="settings-panel" data-settings-panel="referral" hidden>
              <h2><?= $this->lang->line('profile_settings_tab_5') ?? 'Referans ayarları' ?></h2>
              <div style="margin-bottom:20px; font-size:14px;">
                  <strong><?= $this->lang->line('profile_settings_current_ref') ?? 'Mevcut referansınız:' ?></strong>
                  <?php
                  if (!empty($user_details->user_reference_id)) {
                      $refUser = $this->db->get_where('users', ['user_id' => $user_details->user_reference_id])->row();
                      echo html_escape($refUser->user_nickname ?? $this->lang->line('profile_settings_ref_unknown'));
                  } else {
                      echo $this->lang->line('profile_settings_ref_unknown') ?? 'Yok / Bilinmiyor';
                  }
                  ?>
              </div>
              <form action="<?= base_url($this->uri->segment(1) . '/profile/requestReferenceChange') ?>" method="POST">
                  <div class="field" style="max-width:440px">
                      <label for="referral-code"><?= $this->lang->line('profile_settings_new_ref_code') ?? 'Yeni referans kodu (nickname)' ?></label>
                      <input id="referral-code" name="new_reference_nickname" placeholder="<?= $this->lang->line('profile_settings_new_ref_placeholder') ?? 'Referans kodunu girin' ?>" required>
                  </div>
                  <div class="form-actions">
                      <button class="primary-button" type="submit"><svg class="icon"><use href="#i-send"/></svg>Değişiklik talep et</button>
                  </div>
              </form>
            </section>
          </div>
        </section>
      </section>
    </section>
</main>

<style>
.address-card {
    transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
}
.address-card:hover {
    border-color: rgba(199, 154, 62, 0.5) !important;
}
.address-card.is-default {
    border-color: #c79a3e !important;
    background: linear-gradient(180deg, #181d29 0%, #121620 100%) !important;
    box-shadow: 0 4px 14px rgba(0,0,0,0.25);
}
.addr-action-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    height: 30px !important;
    min-height: 30px !important;
    padding: 0 11px !important;
    border: 1px solid #333c50 !important;
    border-radius: 7px !important;
    background: #171d2a !important;
    color: #e2e8f0 !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    line-height: 1 !important;
    white-space: nowrap !important;
    text-decoration: none !important;
    cursor: pointer !important;
    box-sizing: border-box !important;
    transition: all .18s ease !important;
}
.addr-action-btn:hover {
    background: #22293a !important;
    border-color: #4a5a6e !important;
    color: #fff !important;
}
.addr-action-btn.is-default-btn {
    color: #ffd66d !important;
    border-color: #5c471c !important;
    background: #221c10 !important;
}
.addr-action-btn.is-default-btn:hover {
    background: #332712 !important;
    border-color: #8c6a28 !important;
    color: #ffe08a !important;
}
.addr-delete-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 30px !important;
    height: 30px !important;
    min-width: 30px !important;
    min-height: 30px !important;
    padding: 0 !important;
    border: 1px solid rgba(255, 90, 90, 0.25) !important;
    border-radius: 7px !important;
    background: rgba(255, 70, 70, 0.08) !important;
    color: #ff6b6b !important;
    text-decoration: none !important;
    cursor: pointer !important;
    box-sizing: border-box !important;
    transition: all .18s ease !important;
}
.addr-delete-btn:hover {
    background: rgba(255, 70, 70, 0.2) !important;
    border-color: rgba(255, 90, 90, 0.5) !important;
    color: #ff8585 !important;
}
.addr-action-btn svg,
.addr-delete-btn svg {
    width: 13px !important;
    height: 13px !important;
    flex-shrink: 0 !important;
}
.settings-panel .select2-container {
    width: 100% !important;
}
.settings-panel .select2-container--default .select2-selection--single {
    height: 43px !important;
    background-color: #0e121a !important;
    border: 1px solid #303645 !important;
    border-radius: 8px !important;
    display: flex !important;
    align-items: center !important;
}
.settings-panel .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #edf0f2 !important;
    font-size: 0.75rem !important;
    padding-left: 12px !important;
    line-height: 41px !important;
}
.settings-panel .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 41px !important;
}
.select2-dropdown,
.settings-panel .select2-dropdown {
    background-color: #121620 !important;
    border: 1px solid #303645 !important;
    border-radius: 8px !important;
    z-index: 99999 !important;
}
.select2-results__option,
.settings-panel .select2-results__option {
    color: #ccd5df !important;
    font-size: 0.75rem !important;
    padding: 8px 12px !important;
}
.select2-results__option--highlighted[aria-selected],
.settings-panel .select2-results__option--highlighted[aria-selected] {
    background-color: #c79a3e !important;
    color: #111 !important;
}
.select2-container--default .select2-search--dropdown .select2-search__field,
.settings-panel .select2-container--default .select2-search--dropdown .select2-search__field {
    background-color: #0e121a !important;
    border: 1px solid #303645 !important;
    color: #fff !important;
    border-radius: 6px !important;
    padding: 6px 10px !important;
    font-size: 0.75rem !important;
}
</style>

<script>
    document.addEventListener("click", function(event){
        var settingsTab = event.target.closest("[data-settings-tab]");
        if(settingsTab){
          var key = settingsTab.getAttribute("data-settings-tab");
          document.querySelectorAll("[data-settings-tab]").forEach(function(button){button.classList.toggle("active", button === settingsTab)});
          document.querySelectorAll("[data-settings-panel]").forEach(function(panel){panel.hidden = panel.getAttribute("data-settings-panel") !== key});
          if (key === 'billing') {
              setTimeout(initSelect2, 50);
          }
          return;
        }
        var radio = event.target.closest(".radio-choice");
        if(radio){
            // Sadece bu grubun içindeki diğer butonları düzelt
            var container = radio.closest("[role='radiogroup']") || radio.parentElement;
            container.querySelectorAll(".radio-choice").forEach(function(choice){
                choice.classList.toggle("active", choice === radio);
                var input = choice.querySelector("input[type='radio']");
                if(input) {
                    var wasChecked = input.checked;
                    input.checked = (choice === radio);
                    if (choice === radio && !wasChecked) {
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
            return;
        }
    });

    // Fatura Bilgileri ve Adres Yönetimi
    const citiesData = <?= !empty($cities_json) ? $cities_json : '{}' ?>;
    const userAddressesList = <?= json_encode($addressList) ?>;
    const addressesMap = {};
    if (Array.isArray(userAddressesList)) {
        userAddressesList.forEach(function(a) {
            if (a.user_addresses_id) addressesMap[a.user_addresses_id] = a;
        });
    }

    let savedBillingCity = <?= json_encode($valCity) ?>;
    let savedBillingDistrict = <?= json_encode($valDistrict) ?>;
    let isProgrammaticChange = false;

    function getDistrictsForCity(city) {
        if (!city || typeof citiesData !== 'object') return [];
        if (Array.isArray(citiesData[city])) return citiesData[city];
        const cityUpper = String(city).trim().toLocaleUpperCase('tr');
        for (const k in citiesData) {
            if (k.trim().toLocaleUpperCase('tr') === cityUpper && Array.isArray(citiesData[k])) {
                return citiesData[k];
            }
        }
        return [];
    }

    function initSelect2() {
        if (window.jQuery && typeof $.fn.select2 === 'function') {
            const $city = $('#invoice-city');
            const $district = $('#invoice-district');

            if (!$city.data('select2')) {
                $city.select2({
                    width: '100%',
                    placeholder: 'İl seçiniz'
                });
                $city.off('change.loc select2:select.loc').on('change.loc select2:select.loc', function() {
                    if (isProgrammaticChange) return;
                    savedBillingDistrict = '';
                    populateDistricts('');
                });
            }

            if (!$district.data('select2')) {
                const hasD = $district.find('option').length > 1;
                $district.select2({
                    width: '100%',
                    placeholder: hasD ? 'İlçe seçiniz' : 'Önce il seçiniz'
                });
            }
        }
    }

    function populateDistricts(selectedDistrict) {
        const citySelect = document.getElementById('invoice-city');
        const districtSelect = document.getElementById('invoice-district');
        if (!citySelect || !districtSelect) return;

        const selectedCity = citySelect.value;
        const districts = getDistrictsForCity(selectedCity);
        const targetDist = (selectedDistrict !== undefined && selectedDistrict !== null && selectedDistrict !== '') ? selectedDistrict : savedBillingDistrict;

        districtSelect.innerHTML = '<option value="">' + (districts.length > 0 ? 'İlçe seçiniz' : 'Önce il seçiniz') + '</option>';

        let matchedVal = '';
        if (districts.length > 0) {
            const cleanTarget = String(targetDist || '').trim().toLocaleUpperCase('tr');
            districts.forEach(function(distName) {
                const opt = document.createElement('option');
                opt.value = distName;
                opt.textContent = distName;
                if (cleanTarget && cleanTarget === String(distName).trim().toLocaleUpperCase('tr')) {
                    opt.selected = true;
                    matchedVal = distName;
                }
                districtSelect.appendChild(opt);
            });
            districtSelect.disabled = false;
        } else {
            districtSelect.disabled = true;
        }

        districtSelect.value = matchedVal;

        if (window.jQuery) {
            const $district = $(districtSelect);
            $district.prop('disabled', districts.length === 0);

            if (typeof $.fn.select2 === 'function') {
                if ($district.data('select2')) {
                    $district.select2('destroy');
                }
                $district.select2({
                    width: '100%',
                    placeholder: districts.length > 0 ? 'İlçe seçiniz' : 'Önce il seçiniz'
                });
                if (matchedVal) {
                    $district.val(matchedVal).trigger('change');
                } else {
                    $district.val('').trigger('change');
                }
            }
        }
    }

    function syncBillingState() {
        const invTypeInput = document.querySelector('input[name="user_addresses_invoice_type"]:checked');
        const isCorp = invTypeInput && invTypeInput.value === '2';

        const tcCitizenInput = document.querySelector('input[name="user_addresses_is_turkish_citizen"]:checked');
        const isTcCitizen = tcCitizenInput ? tcCitizenInput.value === '1' : true;

        const tcCompanyInput = document.querySelector('input[name="user_addresses_is_turkish_company"]:checked');
        const isTcCompany = tcCompanyInput ? tcCompanyInput.value === '1' : true;

        const citizenGroup = document.getElementById('citizen_status_group');
        const companyGroup = document.getElementById('company_status_group');

        const fieldFullname = document.getElementById('field-fullname');
        const fieldCompanyname = document.getElementById('field-companyname');
        const fieldTaxoffice = document.getElementById('field-taxoffice');
        const fieldEinvoice = document.getElementById('field-einvoice');
        const taxNumberLabel = document.getElementById('bill_tax_number_label');
        const taxNumberInput = document.getElementById('invoice-taxnumber');
        const fullnameInput = document.getElementById('invoice-fullname');
        const companynameInput = document.getElementById('invoice-companyname');
        const taxofficeInput = document.getElementById('invoice-taxoffice');

        const trLocationFields = document.querySelectorAll('.location-tr');
        const foreignLocationFields = document.querySelectorAll('.location-foreign');
        const citySelect = document.getElementById('invoice-city');
        const districtSelect = document.getElementById('invoice-district');
        const foreignCountryInput = document.getElementById('invoice-country');
        const foreignCityInput = document.getElementById('invoice-foreign-city');
        const foreignDistrictInput = document.getElementById('invoice-foreign-district');

        const isTurkey = isCorp ? isTcCompany : isTcCitizen;

        if (isCorp) {
            // Kurumsal
            if (citizenGroup) citizenGroup.style.display = 'none';
            if (companyGroup) companyGroup.style.display = 'flex';

            if (fieldFullname) fieldFullname.style.display = 'none';
            if (fieldCompanyname) fieldCompanyname.style.display = 'grid';
            if (fieldTaxoffice) fieldTaxoffice.style.display = 'grid';

            if (fullnameInput) fullnameInput.required = false;
            if (companynameInput) companynameInput.required = true;
            if (taxofficeInput) taxofficeInput.required = isTcCompany;

            if (isTcCompany) {
                if (fieldEinvoice) fieldEinvoice.style.display = 'grid';
                if (taxNumberLabel) taxNumberLabel.textContent = 'Vergi Numarası *';
                if (taxNumberInput) {
                    taxNumberInput.placeholder = '10 haneli Vergi Numarası';
                    taxNumberInput.maxLength = 10;
                    taxNumberInput.inputMode = 'numeric';
                    taxNumberInput.required = true;
                }
            } else {
                if (fieldEinvoice) fieldEinvoice.style.display = 'none';
                if (taxNumberLabel) taxNumberLabel.textContent = 'Vergi / Kayıt Numarası (Tax ID)';
                if (taxNumberInput) {
                    taxNumberInput.placeholder = 'Vergi veya Kayıt Numarası';
                    taxNumberInput.removeAttribute('maxLength');
                    taxNumberInput.inputMode = 'text';
                    taxNumberInput.required = false;
                }
            }
        } else {
            // Bireysel
            if (citizenGroup) citizenGroup.style.display = 'flex';
            if (companyGroup) companyGroup.style.display = 'none';

            if (fieldFullname) fieldFullname.style.display = 'grid';
            if (fieldCompanyname) fieldCompanyname.style.display = 'none';
            if (fieldTaxoffice) fieldTaxoffice.style.display = 'none';
            if (fieldEinvoice) fieldEinvoice.style.display = 'none';

            if (fullnameInput) fullnameInput.required = true;
            if (companynameInput) companynameInput.required = false;
            if (taxofficeInput) taxofficeInput.required = false;

            if (isTcCitizen) {
                if (taxNumberLabel) taxNumberLabel.textContent = 'T.C. Kimlik Numarası *';
                if (taxNumberInput) {
                    taxNumberInput.placeholder = '11 haneli T.C. Kimlik Numarası';
                    taxNumberInput.maxLength = 11;
                    taxNumberInput.inputMode = 'numeric';
                    taxNumberInput.required = true;
                }
            } else {
                if (taxNumberLabel) taxNumberLabel.textContent = 'Pasaport / Yabancı Kimlik No';
                if (taxNumberInput) {
                    taxNumberInput.placeholder = 'Pasaport veya Kimlik No';
                    taxNumberInput.removeAttribute('maxLength');
                    taxNumberInput.inputMode = 'text';
                    taxNumberInput.required = false;
                }
            }
        }

        // Lokasyon toggle
        if (isTurkey) {
            trLocationFields.forEach(function(el) { el.style.display = 'grid'; });
            foreignLocationFields.forEach(function(el) { el.style.display = 'none'; });
            if (citySelect) {
                citySelect.required = true;
                citySelect.disabled = false;
            }
            if (districtSelect) {
                districtSelect.required = true;
                districtSelect.disabled = !Boolean(citySelect && citySelect.value);
            }
            if (foreignCountryInput) foreignCountryInput.required = false;
            if (foreignCityInput) foreignCityInput.required = false;
            if (foreignDistrictInput) foreignDistrictInput.required = false;
        } else {
            trLocationFields.forEach(function(el) { el.style.display = 'none'; });
            foreignLocationFields.forEach(function(el) { el.style.display = 'grid'; });
            if (citySelect) {
                citySelect.required = false;
                citySelect.disabled = true;
            }
            if (districtSelect) {
                districtSelect.required = false;
                districtSelect.disabled = true;
            }
            if (foreignCountryInput) foreignCountryInput.required = true;
            if (foreignCityInput) foreignCityInput.required = true;
            if (foreignDistrictInput) foreignDistrictInput.required = true;
        }

        if (window.jQuery) {
            $('#invoice-city').prop('disabled', !isTurkey);
            $('#invoice-district').prop('disabled', !isTurkey || !citySelect || !citySelect.value);
            if (typeof $.fn.select2 === 'function') {
                if (!$('#invoice-city').data('select2')) {
                    $('#invoice-city').select2({ width: '100%', placeholder: 'İl seçiniz' });
                }
                if (!$('#invoice-district').data('select2')) {
                    const hasD = $('#invoice-district').find('option').length > 1;
                    $('#invoice-district').select2({ width: '100%', placeholder: hasD ? 'İlçe seçiniz' : 'Önce il seçiniz' });
                }
            }
        }
    }

    function initBillingSettings() {
        const citySelect = document.getElementById('invoice-city');
        if (!citySelect) return;

        // İlleri doldur
        citySelect.innerHTML = '<option value="">İl seçiniz</option>';
        const cityList = Object.keys(citiesData);
        cityList.sort(function(a, b) { return a.localeCompare(b, 'tr'); });
        
        cityList.forEach(function(cityName) {
            const opt = document.createElement('option');
            opt.value = cityName;
            opt.textContent = cityName;
            if (savedBillingCity && savedBillingCity.toLocaleUpperCase('tr') === cityName.toLocaleUpperCase('tr')) {
                opt.selected = true;
            }
            citySelect.appendChild(opt);
        });

        if (window.jQuery) {
            $('#invoice-city').off('change.loc select2:select.loc').on('change.loc select2:select.loc', function() {
                if (isProgrammaticChange) return;
                savedBillingDistrict = '';
                populateDistricts('');
            });
        }
        citySelect.onchange = function() {
            if (isProgrammaticChange) return;
            savedBillingDistrict = '';
            populateDistricts('');
        };

        if (citySelect.value) {
            populateDistricts(savedBillingDistrict);
        } else {
            populateDistricts('');
        }

        document.querySelectorAll('input[name="user_addresses_invoice_type"], input[name="user_addresses_is_turkish_citizen"], input[name="user_addresses_is_turkish_company"]').forEach(function(r) {
            r.addEventListener('change', syncBillingState);
        });

        initSelect2();
        syncBillingState();
    }

    function openNewAddressForm() {
        const formContainer = document.getElementById('addressFormContainer');
        const form = document.getElementById('billingFormSettings');
        if (!formContainer || !form) return;

        isProgrammaticChange = true;

        document.getElementById('input_user_addresses_id').value = '';
        document.getElementById('addressFormHeading').textContent = 'Yeni Fatura Adresi Ekle';
        document.getElementById('invoice-title').value = 'Fatura Adresi';
        document.getElementById('input_user_addresses_is_default').checked = (userAddressesList.length === 0);

        // Bireysel ve TC seç
        setRadioValue('user_addresses_invoice_type', '1');
        setRadioValue('user_addresses_is_turkish_citizen', '1');
        setRadioValue('user_addresses_is_turkish_company', '1');

        document.getElementById('invoice-fullname').value = <?= json_encode($valFullName) ?>;
        document.getElementById('invoice-companyname').value = '';
        document.getElementById('invoice-taxnumber').value = '';
        document.getElementById('invoice-taxoffice').value = '';
        document.getElementById('invoice-address').value = '';
        document.getElementById('invoice-postal-code').value = '';
        document.getElementById('invoice-phone').value = <?= json_encode($valPhone) ?>;
        document.getElementById('invoice-country').value = '';
        document.getElementById('invoice-foreign-city').value = '';
        document.getElementById('invoice-foreign-district').value = '';

        savedBillingCity = '';
        savedBillingDistrict = '';

        formContainer.style.display = 'block';
        syncBillingState();

        const citySelect = document.getElementById('invoice-city');
        if (citySelect) {
            citySelect.value = '';
            if (window.jQuery && typeof $.fn.select2 === 'function') {
                $(citySelect).val('').trigger('change');
            }
            populateDistricts('');
        }

        setTimeout(function() {
            initSelect2();
            isProgrammaticChange = false;
        }, 50);
        formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function editAddress(id) {
        const addr = addressesMap[id];
        if (!addr) return;

        const formContainer = document.getElementById('addressFormContainer');
        if (!formContainer) return;

        isProgrammaticChange = true;

        document.getElementById('input_user_addresses_id').value = addr.user_addresses_id || '';
        document.getElementById('addressFormHeading').textContent = 'Adresi Düzenle: ' + (addr.user_addresses_title || 'Fatura Adresi');
        document.getElementById('invoice-title').value = addr.user_addresses_title || 'Fatura Adresi';
        document.getElementById('input_user_addresses_is_default').checked = (addr.user_addresses_is_default == 1);

        setRadioValue('user_addresses_invoice_type', String(addr.user_addresses_invoice_type || 1));
        setRadioValue('user_addresses_is_turkish_citizen', String(addr.user_addresses_is_turkish_citizen !== undefined ? addr.user_addresses_is_turkish_citizen : 1));
        setRadioValue('user_addresses_is_turkish_company', String(addr.user_addresses_is_turkish_company !== undefined ? addr.user_addresses_is_turkish_company : 1));

        document.getElementById('invoice-fullname').value = addr.user_addresses_full_name || '';
        document.getElementById('invoice-companyname').value = addr.user_addresses_company_name || '';
        document.getElementById('invoice-taxnumber').value = addr.user_addresses_tax_number || '';
        document.getElementById('invoice-taxoffice').value = addr.user_addresses_tax_office || '';
        document.getElementById('invoice-einvoice').value = addr.user_addresses_is_e_invoice_taxpayer || 0;
        document.getElementById('invoice-address').value = addr.user_addresses_address || '';
        document.getElementById('invoice-postal-code').value = addr.user_addresses_postal_code || '';
        document.getElementById('invoice-phone').value = addr.user_addresses_phone || <?= json_encode($valPhone) ?>;
        document.getElementById('invoice-country').value = addr.user_addresses_country || '';
        document.getElementById('invoice-foreign-city').value = addr.user_addresses_city || '';
        document.getElementById('invoice-foreign-district').value = addr.user_addresses_district || '';

        savedBillingCity = addr.user_addresses_city || '';
        savedBillingDistrict = addr.user_addresses_district || '';

        formContainer.style.display = 'block';
        syncBillingState();

        const citySelect = document.getElementById('invoice-city');
        if (citySelect) {
            let matchedCity = '';
            for (let i = 0; i < citySelect.options.length; i++) {
                if (citySelect.options[i].value && citySelect.options[i].value.toLocaleUpperCase('tr') === savedBillingCity.toLocaleUpperCase('tr')) {
                    matchedCity = citySelect.options[i].value;
                    break;
                }
            }
            citySelect.value = matchedCity;
            if (window.jQuery && typeof $.fn.select2 === 'function') {
                $(citySelect).val(matchedCity).trigger('change');
            }
            populateDistricts(savedBillingDistrict);
        }

        setTimeout(function() {
            initSelect2();
            isProgrammaticChange = false;
        }, 50);
        formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function cancelAddressForm() {
        const formContainer = document.getElementById('addressFormContainer');
        if (formContainer && userAddressesList.length > 0) {
            formContainer.style.display = 'none';
        }
    }

    function setRadioValue(groupName, val) {
        document.querySelectorAll('input[name="' + groupName + '"]').forEach(function(input) {
            const isMatch = (input.value === String(val));
            input.checked = isMatch;
            const parentLabel = input.closest('.radio-choice');
            if (parentLabel) {
                parentLabel.classList.toggle('active', isMatch);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBillingSettings);
    } else {
        initBillingSettings();
    }

    <?php if($this->session->flashdata('active_tab') == 'billing' || $this->uri->segment(3) == 'billing'): ?>
    var billingTab = document.querySelector('[data-settings-tab="billing"]');
    if (billingTab) {
        billingTab.click();
        setTimeout(initSelect2, 100);
    }
    <?php elseif($this->uri->segment(3) == 'change-password'): ?>
    var passTab = document.querySelector('[data-settings-tab="password"]');
    if (passTab) passTab.click();
    <?php elseif($this->uri->segment(3) == 'referral'): ?>
    var refTab = document.querySelector('[data-settings-tab="referral"]');
    if (refTab) refTab.click();
    <?php endif; ?>
</script>

<dialog id="verifyModal" style="padding: 0; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; max-width: 450px; width: 90%; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div style="border-bottom: 1px solid rgba(255,255,255,0.1); padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="color:#c79a3e; font-size:18px; margin: 0;">Doğrulama Kodu</h3>
        <button type="button" style="background: transparent; border: none; color: #fff; font-size: 24px; cursor: pointer;" onclick="document.getElementById('verifyModal').close(); window.location.reload()">&times;</button>
    </div>
    <div style="padding: 24px;">
        <p id="otp_modal_desc" style="color:#aaaaaa; font-size:14px; margin-bottom:20px;">Lütfen yeni bilginize gönderilen 6 haneli doğrulama kodunu giriniz.</p>
        <form id="verifyOtpForm" onsubmit="return verifyOTP(event)">
            <input type="hidden" id="otp_type" name="otp_type" value="">
            <div class="field">
                <label>Doğrulama Kodu</label>
                <input type="text" name="otp_code" required maxlength="6" pattern="\d{6}" placeholder="123456" autocomplete="off" style="text-align:center; font-size:24px; letter-spacing:10px; background-color:rgba(0,0,0,0.3); color:#fff; border:1px solid #333; border-radius:6px; padding:10px; width:100%;">
            </div>
            <button type="submit" id="btn_verify_otp" class="primary-button" style="width:100%; margin-top:15px; justify-content:center;">Doğrula</button>
        </form>
    </div>
</dialog>

<style>
.iti { width: 100%; }
.iti__country-list { background-color: #1a1a1a !important; border: 1px solid rgba(255, 255, 255, 0.08); z-index: 99 !important; }
.iti__country-name, .iti__dial-code { color: #fff; }
dialog::backdrop { background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); }
</style>

<script>
    function submitContactUpdate(e, type) {
        e.preventDefault();
        var form = document.getElementById('form_' + type);
        var formData = new FormData(form);
        
        if (type === 'phone' && window.itiPhoneSettings) {
            if (!window.itiPhoneSettings.isValidNumber()) {
                if (typeof toastr !== 'undefined') toastr.error('Lütfen geçerli bir telefon numarası giriniz.');
                return false;
            }
            formData.set('user_phone', window.itiPhoneSettings.getNumber());
        }
        
        var btn = document.getElementById('btn_' + type);
        var iconHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="icon"><use href="#i-refresh"/></svg>';

        fetch(form.action, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = iconHTML;
            
            if (data.status === 'success') {
                if (typeof toastr !== 'undefined') toastr.success(data.message);
                if (data.requires_verification) {
                    document.getElementById('otp_type').value = type;
                    document.getElementById('otp_modal_desc').innerText = 'Lütfen yeni bilginize gönderilen 6 haneli doğrulama kodunu giriniz.';
                    document.getElementById('verifyModal').showModal();
                } else {
                    setTimeout(() => { window.location.reload(); }, 1000);
                }
            } else {
                if (typeof toastr !== 'undefined') toastr.error(data.message);
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = iconHTML;
            if (typeof toastr !== 'undefined') toastr.error('Bir hata oluştu.');
        });
        return false;
    }

    function verifyOTP(e) {
        e.preventDefault();
        var form = document.getElementById('verifyOtpForm');
        var formData = new FormData(form);
        var btn = document.getElementById('btn_verify_otp');
        btn.disabled = true;
        btn.innerText = 'Doğrulanıyor...';

        fetch('<?= base_url($this->uri->segment(1) . "/profile/verifyContactOTP") ?>', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerText = 'Doğrula';
            
            if (data.status === 'success') {
                if (typeof toastr !== 'undefined') toastr.success(data.message);
                setTimeout(() => { window.location.reload(); }, 1000);
            } else {
                if (typeof toastr !== 'undefined') toastr.error(data.message);
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerText = 'Doğrula';
            if (typeof toastr !== 'undefined') toastr.error('Bir hata oluştu.');
        });
        return false;
    }

    function submitSecurityUpdate(e) {
        e.preventDefault();
        var form = document.getElementById('form_security');
        var formData = new FormData(form);
        var btn = form.querySelector('button[type="submit"]');
        
        var originalBtnContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerText = 'Lütfen Bekleyin...';

        fetch(form.action, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalBtnContent;
            
            if (data.status === 'success') {
                if (data.requires_verification) {
                    if (typeof toastr !== 'undefined') toastr.info(data.message);
                    document.getElementById('otp_type').value = data.otp_type;
                    document.getElementById('otp_modal_desc').innerText = 'Lütfen değişikliği onaylamak için mevcut sistemde kayıtlı email veya telefonunuza gelen onay kodunu giriniz.';
                    document.getElementById('verifyModal').showModal();
                } else {
                    if (typeof toastr !== 'undefined') toastr.success(data.message);
                    setTimeout(() => { window.location.reload(); }, 1000);
                }
            } else {
                if (typeof toastr !== 'undefined') toastr.error(data.message);
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = originalBtnContent;
            if (typeof toastr !== 'undefined') toastr.error('Bir hata oluştu.');
        });
        return false;
    }

    document.addEventListener("DOMContentLoaded", function() {
        const phoneInput = document.querySelector("#input_phone");
        if (phoneInput) {
            window.itiPhoneSettings = window.intlTelInput(phoneInput, {
                initialCountry: "tr",
                preferredCountries: ["tr", "us", "gb", "de"],
                separateDialCode: true,
                dropdownContainer: document.body,
                strictMode: true,
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/23.0.4/js/utils.js"
            });
        }
    });
</script>