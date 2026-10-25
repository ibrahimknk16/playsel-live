<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-withdrawals" data-view="withdrawals">
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-withdraw"/></svg></span>
            <div class="hero-copy">
                <h1>Nakit Çekimlerim</h1>
                <p>Para çekme işlemlerinizi yönetin ve banka hesaplarınızı kontrol edin.</p>
            </div>
        </header>

        <section class="subnav-shell">
          <div class="tab-list" role="tablist" aria-label="Nakit çekim işlemleri" id="withdrawTabs">
            <button class="tab-button active" type="button" data-withdraw-tab="history" aria-selected="true"><svg class="icon"><use href="#i-list"/></svg>Nakit çekimlerim</button>
            <button class="tab-button" type="button" data-withdraw-tab="request" aria-selected="false"><svg class="icon"><use href="#i-plus"/></svg>Nakit talebi gönder</button>
            <button class="tab-button" type="button" data-withdraw-tab="accounts" aria-selected="false"><svg class="icon"><use href="#i-bank"/></svg>Hesaplarım</button>
          </div>
          
          <!-- Geçmiş Sekmesi -->
          <div class="withdraw-pane" id="pane-history" data-withdraw-panel="history">
              <form action="<?= base_url($this->uri->segment(1) . '/profile/withdrawals') ?>" method="GET" class="filter-row">
                <div class="date-control">
                    <input type="text" id="flatpickr-date" name="date_range" placeholder="Tarih aralığı seçin" aria-label="Tarih aralığı seçin" value="<?= htmlspecialchars($this->input->get('date_range') ?? '') ?>">
                    <span class="calendar"><svg class="icon"><use href="#i-calendar"/></svg></span>
                    <button class="filter-submit" type="submit" aria-label="Tarih filtresini uygula"><svg class="icon"><use href="#i-filter"/></svg></button>
                </div>
                <a href="?date=1week" style="text-decoration:none;" class="quick-filter <?= ($this->input->get('date') == '1week') ? 'active' : '' ?>">Son 1 hafta</a>
                <a href="?date=1month" style="text-decoration:none;" class="quick-filter <?= ($this->input->get('date') == '1month') ? 'active' : '' ?>">Son 1 ay</a>
                <a href="?date=3months" style="text-decoration:none;" class="quick-filter <?= ($this->input->get('date') == '3months') ? 'active' : '' ?>">Son 3 ay</a>
                <a href="<?= base_url($this->uri->segment(1) . '/profile/withdrawals') ?>" style="text-decoration:none;" class="icon-button danger" aria-label="Filtreleri temizle"><svg class="icon"><use href="#i-x"/></svg></a>
                <button class="icon-button success" type="button" aria-label="Yenile" onclick="window.location.reload();"><svg class="icon"><use href="#i-refresh"/></svg></button>
              </form>

              <div class="panel-body">
                  <div class="empty-state">
                      <span class="empty-icon"><svg class="icon"><use href="#i-card"/></svg></span>
                      <h3>Nakit Çekim Talebi Bulunamadı</h3>
                      <p>Seçilen tarih aralığında bakiye hareketi bulunmuyor.</p>
                      <button type="button" class="primary-button" onclick="document.querySelector('[data-withdraw-tab=\'request\']').click();">
                          <svg class="icon"><use href="#i-plus"/></svg> Nakit Talebi Gönder
                      </button>
                  </div>
              </div>
          </div>

          <!-- Talep Oluştur Sekmesi -->
          <div class="withdraw-pane" id="pane-request" data-withdraw-panel="request" hidden>
              <div class="panel-body">
                  <h3 class="subheading"><svg class="icon"><use href="#i-withdraw"/></svg>Nakit Çekim Talebi Oluştur</h3>
                  <form action="<?= base_url($this->uri->segment(1) . '/profile/request_withdrawal') ?>" method="POST" class="form-grid" style="max-width: 500px;">
                      <div class="field full">
                          <label>Çekilecek Tutar (₺)</label>
                          <div class="amount-field">
                              <input type="number" name="amount" placeholder="0.00" required>
                              <span class="currency">₺</span>
                          </div>
                      </div>
                      <div class="field full">
                          <label>Banka Hesabı Seçin</label>
                          <select name="bank_account_id" required>
                              <option value="">Kayıtlı Banka Hesabı Seçin...</option>
                              <?php if (!empty($bank_accounts)): ?>
                                  <?php foreach ($bank_accounts as $account): ?>
                                      <option value="<?= (int)$account->id ?>"><?= htmlspecialchars($account->bank_name, ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($account->iban, ENT_QUOTES, 'UTF-8') ?></option>
                                  <?php endforeach; ?>
                              <?php endif; ?>
                          </select>
                      </div>
                      <div class="form-actions" style="margin-top:16px;">
                          <button type="submit" class="primary-button" style="width:100%;"><svg class="icon"><use href="#i-send"/></svg> Talebi Gönder</button>
                      </div>
                  </form>
              </div>
          </div>

          <!-- Hesaplarım Sekmesi -->
          <div class="withdraw-pane" id="pane-accounts" data-withdraw-panel="accounts" hidden>
              <div class="panel-body">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
                      <h3 class="subheading" style="margin:0;"><svg class="icon"><use href="#i-bank"/></svg>Kayıtlı Banka Hesaplarım</h3>
                      <button type="button" class="primary-button" onclick="openAddAccount();">
                          <svg class="icon"><use href="#i-plus"/></svg> Yeni Ekle
                      </button>
                  </div>

                  <?php if (empty($bank_accounts)): ?>
                      <div class="empty-state">
                          <span class="empty-icon"><svg class="icon"><use href="#i-bank"/></svg></span>
                          <h3>Kayıtlı Hesap Bulunamadı</h3>
                          <p>Para çekebilmek için lütfen kendinize ait bir banka hesabı ekleyin.</p>
                          <button type="button" class="primary-button" onclick="openAddAccount();" style="margin-top:12px;">
                              <svg class="icon"><use href="#i-plus"/></svg> Banka Hesabı Ekle
                          </button>
                      </div>
                  <?php else: ?>
                      <div class="table-wrap">
                          <table class="data-table">
                              <thead>
                                  <tr>
                                      <th>Banka Adı</th>
                                      <th>IBAN</th>
                                      <th>Eklenme Tarihi</th>
                                      <th style="text-align:right;">İşlem</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php foreach ($bank_accounts as $account): ?>
                                      <tr>
                                          <td data-label="Banka Adı"><strong><?= htmlspecialchars($account->bank_name, ENT_QUOTES, 'UTF-8') ?></strong></td>
                                          <td data-label="IBAN"><span style="font-family: monospace; letter-spacing: 1px; color: var(--profile-gold);"><?= htmlspecialchars($account->iban, ENT_QUOTES, 'UTF-8') ?></span></td>
                                          <td data-label="Eklenme Tarihi" class="subtle"><?= date('d.m.Y H:i', strtotime($account->created_at)) ?></td>
                                          <td data-label="İşlem" style="text-align:right;">
                                              <button type="button" class="detail-button" style="min-height:30px; font-size:12px; padding:0 8px;"
                                                      data-action="edit-bank"
                                                      data-id="<?= (int)$account->id ?>"
                                                      data-bank="<?= htmlspecialchars($account->bank_name, ENT_QUOTES, 'UTF-8') ?>"
                                                      data-iban="<?= htmlspecialchars($account->iban, ENT_QUOTES, 'UTF-8') ?>">
                                                  <svg class="icon" style="width:14px;height:14px;"><use href="#i-edit"/></svg>Düzenle
                                              </button>
                                              <a href="<?= base_url($this->uri->segment(1) . '/profile/delete_bank_account/' . (int)$account->id) ?>" class="detail-button" style="color:#ff4757; min-height:30px; font-size:12px; padding:0 8px; text-decoration:none;" onclick="return confirm('Bu banka hesabını silmek istediğinize emin misiniz?');">
                                                  <svg class="icon" style="width:14px;height:14px;"><use href="#i-trash"/></svg>Sil
                                              </a>
                                          </td>
                                      </tr>
                                  <?php endforeach; ?>
                              </tbody>
                          </table>
                      </div>
                  <?php endif; ?>
              </div>
          </div>

          <!-- Hesap Ekle / Düzenle (Gizli Sekme, Yönlendirme ile Açılır) -->
          <div class="withdraw-pane" id="pane-add-account" data-withdraw-panel="add-account" hidden>
              <div class="panel-body">
                  <h3 class="subheading" id="addAccountTitle"><svg class="icon"><use href="#i-bank"/></svg>Yeni Banka Hesabı Ekle</h3>
                  <div class="info-banner" style="margin-bottom:20px;">
                      <svg class="icon"><use href="#i-info"/></svg>Ekleyeceğiniz banka hesabı mutlaka sizin adınıza olmalıdır. Başkasına ait hesaplara ödeme yapılamaz.
                  </div>

                  <form action="<?= base_url($this->uri->segment(1) . '/profile/add_bank_account') ?>" method="POST" class="form-grid" style="max-width: 500px;">
                      <input type="hidden" name="bank_account_id" id="editBankId" value="">
                      <div class="field full">
                          <label>Banka Adı</label>
                          <select name="bank_name" id="editBankName" required>
                              <option value="">Seçiniz...</option>
                              <?php if (!empty($system_banks)): ?>
                                  <?php foreach ($system_banks as $bank): ?>
                                      <option value="<?= htmlspecialchars($bank->bank_name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($bank->bank_name, ENT_QUOTES, 'UTF-8') ?></option>
                                  <?php endforeach; ?>
                              <?php endif; ?>
                          </select>
                      </div>
                      <div class="field full">
                          <label>IBAN Numarası</label>
                          <input type="text" name="iban" id="ibanInput" placeholder="TR00 0000 0000 0000 0000 0000 00" maxlength="32" required autocomplete="off">
                      </div>
                      <div class="form-actions" style="margin-top:16px; display:flex; gap:12px;">
                          <button type="button" class="detail-button" style="flex:1; justify-content:center; min-height:42px;" onclick="switchWithdrawTab('accounts');">Vazgeç</button>
                          <button type="submit" class="primary-button" style="flex:2;"><svg class="icon"><use href="#i-save"/></svg> Hesabı Kaydet</button>
                      </div>
                  </form>
              </div>
          </div>

        </section>
      </section>
    </section>
</main>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/tr.js"></script>

<script>
    // Sekmeler Arası Geçiş
    function switchWithdrawTab(targetId) {
        document.querySelectorAll('#withdrawTabs .tab-button').forEach(b => {
            let isActive = (b.getAttribute('data-withdraw-tab') === targetId);
            b.classList.toggle('active', isActive);
            b.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
        document.querySelectorAll('.withdraw-pane').forEach(p => {
            let isMatch = (p.id === 'pane-' + targetId || p.getAttribute('data-withdraw-panel') === targetId);
            p.hidden = !isMatch;
        });
    }

    // Yeni Hesap Ekleme Formunu Aç ve Temizle
    function openAddAccount() {
        const titleEl = document.getElementById('addAccountTitle');
        if (titleEl) {
            titleEl.innerHTML = '<svg class="icon"><use href="#i-bank"/></svg>Yeni Banka Hesabı Ekle';
        }
        const editId = document.getElementById('editBankId');
        if (editId) editId.value = '';

        const bankSelect = document.getElementById('editBankName');
        if (bankSelect) bankSelect.selectedIndex = 0;

        const ibanInput = document.getElementById('ibanInput');
        if (ibanInput) ibanInput.value = '';

        switchWithdrawTab('add-account');
    }

    // Var Olan Hesabı Düzenleme Formunu Aç
    function editBank(id, bank_name, iban) {
        const titleEl = document.getElementById('addAccountTitle');
        if (titleEl) {
            titleEl.innerHTML = '<svg class="icon"><use href="#i-bank"/></svg>Banka Hesabını Düzenle';
        }
        const editId = document.getElementById('editBankId');
        if (editId) editId.value = id;
        
        let bankSelect = document.getElementById('editBankName');
        if (bankSelect) {
            for (let i = 0; i < bankSelect.options.length; i++) {
                if (bankSelect.options[i].value === bank_name) {
                    bankSelect.selectedIndex = i;
                    break;
                }
            }
        }
        
        let ibanInput = document.getElementById('ibanInput');
        if (ibanInput) {
            let formattedIban = iban.match(/.{1,4}/g);
            ibanInput.value = formattedIban ? formattedIban.join(' ') : iban;
        }

        switchWithdrawTab('add-account');
    }

    // Tab butonlarına tıklama delegasyonu ve Düzenle butonu
    document.addEventListener('click', function(e) {
        let editBtn = e.target.closest('[data-action="edit-bank"]');
        if (editBtn) {
            e.preventDefault();
            let id = editBtn.getAttribute('data-id');
            let bank = editBtn.getAttribute('data-bank');
            let iban = editBtn.getAttribute('data-iban');
            editBank(id, bank, iban);
            return;
        }

        let tab = e.target.closest('[data-withdraw-tab]');
        if (tab) {
            let targetId = tab.getAttribute('data-withdraw-tab');
            if (targetId === 'add-account') {
                openAddAccount();
            } else {
                switchWithdrawTab(targetId);
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // URL hash kontrolü (örn. #tab-accounts, #accounts veya #tab-add-account)
        const rawHash = (window.location.hash || '').replace('#tab-', '').replace('#', '');
        if (rawHash === 'accounts' || rawHash === 'request' || rawHash === 'add-account' || rawHash === 'history') {
            switchWithdrawTab(rawHash);
        }

        // Datepicker
        const dateInput = document.getElementById('flatpickr-date');
        if (dateInput && typeof flatpickr !== 'undefined') {
            flatpickr(dateInput, {
                mode: "range",
                locale: "tr",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d M Y",
                placeholder: "Tarih Aralığı Seçin",
                disableMobile: "true" 
            });
        }

        // IBAN formatter
        const ibanInput = document.getElementById('ibanInput');
        if (ibanInput) {
            ibanInput.addEventListener('input', function(e) {
                let val = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                if (val.length > 0 && !val.startsWith('TR')) {
                    if (val === 'T' || val === 'R') {
                    } else {
                        val = 'TR' + val;
                    }
                }
                if (val.length > 26) {
                    val = val.substring(0, 26);
                }
                let formatted = val.match(/.{1,4}/g);
                this.value = formatted ? formatted.join(' ') : val;
            });
            
            const form = ibanInput.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    let val = ibanInput.value.replace(/[^a-zA-Z0-9]/g, '');
                    if (val.length < 10) {
                        e.preventDefault();
                        if (typeof toastr !== 'undefined') {
                            toastr.warning('Lütfen geçerli bir IBAN veya hesap numarası girin.');
                        } else {
                            alert('Lütfen geçerli bir IBAN veya hesap numarası girin.');
                        }
                        return;
                    }
                    if (val.startsWith('TR') && val.length !== 26) {
                        e.preventDefault();
                        if (typeof toastr !== 'undefined') {
                            toastr.warning('TR ile başlayan IBAN numarası 26 karakter olmalıdır.');
                        } else {
                            alert('TR ile başlayan IBAN numarası 26 karakter olmalıdır.');
                        }
                    }
                });
            }
        }
    });
</script>