<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      
      <!-- Destek Talepleri Listesi -->
      <section class="view" id="view-support" data-view="support">
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-headset"/></svg></span>
            <div class="hero-copy">
                <h1>Destek talepleri</h1>
                <p>Geçmiş destek bildirimlerini incele veya yeni bir talep oluştur.</p>
            </div>
        </header>

        <section class="panel">
          <div class="panel-head ticket-panel-head">
            <h2 class="subheading" style="margin:0"><svg class="icon"><use href="#i-list"/></svg><span>Taleplerim</span></h2>
            <button class="primary-button" type="button" aria-label="Yeni talep oluştur" onclick="document.getElementById('view-support').hidden=true; document.getElementById('view-support-new').hidden=false;">
                <svg class="icon"><use href="#i-plus"/></svg> Yeni talep oluştur
            </button>
          </div>

          <div class="panel-body">
            <?php if (empty($tickets)): ?>
            <div class="empty-state">
              <span class="empty-icon"><svg class="icon"><use href="#i-headset"/></svg></span>
              <h3>Talep Bulunamadı</h3>
              <p>Henüz oluşturulmuş bir destek talebiniz bulunmuyor.</p>
            </div>
            <?php else: ?>
            <div class="table-wrap">
              <table class="data-table" id="tickets-table">
                <thead><tr><th>Destek Talebi</th><th>Son Güncelleme</th><th>Durum</th><th style="text-align:right;">İşlem</th></tr></thead>
                <tbody>
                  <?php foreach ($tickets as $ticket): ?>
                  <tr>
                    <td data-label="Destek Talebi">
                        <div class="product-cell">
                            <span class="product-thumb"><svg class="icon"><use href="#i-ticket"/></svg></span>
                            <span style="display:flex; flex-direction:column; min-width:0;">
                                <strong class="product-name"><?= html_escape($ticket->tickets_subject) ?></strong>
                                <span class="subtle">#<?= $ticket->tickets_id ?> • <?= html_escape($ticket->tickets_topic) ?></span>
                            </span>
                        </div>
                    </td>
                    <td data-label="Son Güncelleme" class="subtle"><?= date('d.m.Y · H:i', strtotime($ticket->tickets_updated_at)) ?></td>
                    <td data-label="Durum">
                        <?php if ($ticket->tickets_status == 0): ?>
                            <span class="status payment-pending"><span class="status-mark pending" aria-hidden="true"></span>Cevap Bekliyor</span>
                        <?php elseif ($ticket->tickets_status == 1): ?>
                            <span class="status done"><span class="status-mark"><svg class="icon"><use href="#i-check"/></svg></span>Yanıtlandı</span>
                        <?php elseif ($ticket->tickets_status == 2): ?>
                            <span class="status refund"><span class="status-mark"><svg class="icon"><use href="#i-x"/></svg></span>Kapatıldı</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="İşlem" style="text-align:right;">
                        <a href="<?= base_url($this->uri->segment(1) . '/profile/ticketDetail/' . $ticket->tickets_id) ?>" class="detail-button" style="text-decoration:none;"><svg class="icon"><use href="#i-eye"/></svg>Görüntüle</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php endif; ?>
          </div>
        </section>
      </section>

      <!-- Yeni Talep Oluştur -->
      <section class="view" id="view-support-new" data-view="support-new" hidden>
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-plus"/></svg></span>
            <div class="hero-copy">
                <h1>Yeni destek talebi</h1>
                <p>Ekibimize iletmek istediğin konuyu detaylarıyla açıkla.</p>
            </div>
        </header>

        <section class="panel">
            <div class="panel-head" style="margin-bottom:16px;">
                <button class="detail-button" type="button" onclick="document.getElementById('view-support-new').hidden=true; document.getElementById('view-support').hidden=false;" style="padding-left:0; border:none; background:none;">
                    <svg class="icon" style="transform:rotate(180deg);"><use href="#i-arrow"/></svg> Geri Dön
                </button>
            </div>
            
            <div class="panel-body">
                <form id="newTicketForm" class="form-grid" style="max-width:600px;">
                    <div class="field full">
                        <label for="ticketTopic">Konu</label>
                        <select id="ticketTopic" name="topic" required>
                            <option value="">Seçiniz...</option>
                            <option value="Ödeme Bildirimi">Ödeme Bildirimi</option>
                            <option value="Sipariş İtirazı / Sorunu">Sipariş İtirazı / Sorunu</option>
                            <option value="Teknik Destek">Teknik Destek</option>
                            <option value="Diğer">Diğer</option>
                        </select>
                    </div>
                    <div class="field full">
                        <label for="ticketSubject">Başlık</label>
                        <input type="text" id="ticketSubject" name="subject" required>
                    </div>
                    <div class="field full">
                        <label for="ticketMessage">Mesajınız</label>
                        <textarea id="ticketMessage" name="message" rows="5" required></textarea>
                    </div>
                    <div class="field full">
                        <label for="ticketImage">Ekran Görüntüsü (Opsiyonel)</label>
                        <div class="reply-file-wrap" style="width:100%;">
                            <label for="ticketImage" class="reply-file-label" style="width:100%; justify-content:center;">
                                <svg class="icon"><use href="#i-plus"/></svg>
                                <span id="ticketImageName">Ekran görüntüsü seç</span>
                            </label>
                            <input type="file" id="ticketImage" accept="image/*" class="sr-only">
                            <input type="hidden" id="ticketAttachmentBase64" name="attachment">
                        </div>
                    </div>
                    <div class="form-actions" style="margin-top:20px; width:100%;">
                        <button type="submit" id="btnSubmitTicket" class="primary-button" style="width:100%;"><svg class="icon"><use href="#i-send"/></svg> Talebi Gönder</button>
                    </div>
                </form>
            </div>
        </section>
      </section>

    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Convert image to base64
    const ticketImage = document.getElementById('ticketImage');
    const ticketAttachmentBase64 = document.getElementById('ticketAttachmentBase64');
    const ticketImageName = document.getElementById('ticketImageName');
    const ticketImageLabel = ticketImage ? ticketImage.closest('.reply-file-wrap')?.querySelector('.reply-file-label') : null;

    if(ticketImage) {
        ticketImage.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (ticketImageName) ticketImageName.textContent = file.name;
                if (ticketImageLabel) ticketImageLabel.classList.add('has-file');
                const reader = new FileReader();
                reader.onloadend = function() {
                    ticketAttachmentBase64.value = reader.result;
                };
                reader.readAsDataURL(file);
            } else {
                if (ticketImageName) ticketImageName.textContent = 'Ekran görüntüsü seç';
                if (ticketImageLabel) ticketImageLabel.classList.remove('has-file');
                ticketAttachmentBase64.value = '';
            }
        });
    }

    // Submit form via AJAX
    const newTicketForm = document.getElementById('newTicketForm');
    if(newTicketForm) {
        newTicketForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitTicket');
            btn.disabled = true;
            btn.innerHTML = '<svg class="icon"><use href="#i-clock"/></svg> Gönderiliyor...';

            const formData = new FormData(newTicketForm);

            fetch('<?= base_url($this->uri->segment(1) . '/profile/createTicket') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı',
                            text: data.message,
                            confirmButtonColor: '#ff4757',
                            background: '#1f1f2e',
                            color: '#fff'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        alert(data.message);
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata',
                            text: data.message,
                            confirmButtonColor: '#ff4757',
                            background: '#1f1f2e',
                            color: '#fff'
                        });
                    } else {
                        alert(data.message);
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="icon"><use href="#i-send"/></svg> Talebi Gönder';
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata',
                        text: 'Bir sorun oluştu. Lütfen tekrar deneyin.',
                        confirmButtonColor: '#ff4757',
                        background: '#1f1f2e',
                        color: '#fff'
                    });
                } else {
                    alert('Bir sorun oluştu. Lütfen tekrar deneyin.');
                }
                btn.disabled = false;
                btn.innerHTML = '<svg class="icon"><use href="#i-send"/></svg> Talebi Gönder';
            });
        });
    }
});
</script>
