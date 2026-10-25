<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-support-detail" data-view="support-detail">
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-headset"/></svg></span>
            <div class="hero-copy">
                <h1>Talep detayları</h1>
                <p>Geçmiş ve mevcut mesajlaşmaları bu sayfadan takip edebilirsin.</p>
            </div>
            <a href="<?= base_url($this->uri->segment(1) . '/profile/tickets') ?>" class="detail-button hero-back-btn" style="text-decoration:none;"><svg class="icon" style="transform:rotate(180deg);"><use href="#i-arrow"/></svg> Geri Dön</a>
        </header>

        <section class="panel support-chat-panel">
          <div class="panel-head ticket-detail-head">
            <div class="ticket-head-info">
              <h2 class="subheading"><svg class="icon"><use href="#i-ticket"/></svg><?= html_escape($ticket->tickets_subject) ?></h2>
              <div class="ticket-meta-tags">
                <span class="subtle">#<?= $ticket->tickets_id ?></span>
                <span class="subtle-dot">•</span>
                <span class="subtle"><?= html_escape($ticket->tickets_topic) ?></span>
              </div>
            </div>
            <div class="ticket-head-status">
              <?php if ($ticket->tickets_status == 0): ?>
                  <span class="status payment-pending"><span class="status-mark pending" aria-hidden="true"></span>Cevap Bekliyor</span>
              <?php elseif ($ticket->tickets_status == 1): ?>
                  <span class="status done"><span class="status-mark"><svg class="icon"><use href="#i-check"/></svg></span>Yanıtlandı</span>
              <?php elseif ($ticket->tickets_status == 2): ?>
                  <span class="status refund"><span class="status-mark"><svg class="icon"><use href="#i-x"/></svg></span>Kapatıldı</span>
              <?php endif; ?>
            </div>
          </div>
          
          <div class="panel-body chat-thread">
            <?php foreach ($messages as $msg): ?>
                <?php if ($msg->ticket_messages_user_id > 0): ?>
                    <!-- Müşteri Mesajı (SAĞDA) -->
                    <article class="chat-bubble user">
                      <div class="bubble-meta">
                        <span class="bubble-author">Siz</span>
                        <time class="bubble-time"><?= date('d.m.Y H:i', strtotime($msg->ticket_messages_created_at)) ?></time>
                      </div>
                      <div class="bubble-body">
                          <div class="bubble-text"><?= nl2br(html_escape($msg->ticket_messages_message)) ?></div>
                          <?php if (!empty($msg->ticket_messages_attachment) && preg_match('#^data:image/(png|jpeg|jpg|webp|gif);base64,#i', $msg->ticket_messages_attachment)): ?>
                              <div class="bubble-attachment">
                                  <img src="<?= html_escape($msg->ticket_messages_attachment) ?>" alt="Ekran görüntüsü" onclick="window.open(this.src, '_blank')">
                              </div>
                          <?php endif; ?>
                      </div>
                    </article>
                <?php else: ?>
                    <!-- Operatör Mesajı (SOLDA) -->
                    <article class="chat-bubble rep">
                      <div class="bubble-meta">
                        <span class="rep-badge"><svg class="icon"><use href="#i-headset"/></svg>Müşteri Hizmetleri</span>
                        <time class="bubble-time"><?= date('d.m.Y H:i', strtotime($msg->ticket_messages_created_at)) ?></time>
                      </div>
                      <div class="bubble-body">
                          <div class="bubble-text"><?= nl2br(html_escape($msg->ticket_messages_message)) ?></div>
                          <?php if (!empty($msg->ticket_messages_attachment) && preg_match('#^data:image/(png|jpeg|jpg|webp|gif);base64,#i', $msg->ticket_messages_attachment)): ?>
                              <div class="bubble-attachment">
                                  <img src="<?= html_escape($msg->ticket_messages_attachment) ?>" alt="Ekran görüntüsü" onclick="window.open(this.src, '_blank')">
                              </div>
                          <?php endif; ?>
                      </div>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
          </div>

          <?php if ($ticket->tickets_status != 2): ?>
          <div class="chat-reply-box">
            <form id="replyTicketForm" class="form-grid">
              <input type="hidden" name="ticket_id" value="<?= $ticket->tickets_id ?>">
              <div class="field full">
                <label for="replyMessage" class="sr-only">Cevap yaz</label>
                <textarea id="replyMessage" name="message" rows="3" placeholder="Yanıtınızı buraya yazın..." required></textarea>
              </div>
              <div class="field full chat-reply-footer">
                  <div class="reply-file-wrap">
                      <label for="replyImage" class="reply-file-label">
                          <svg class="icon"><use href="#i-plus"/></svg>
                          <span id="replyFileName">Ekran görüntüsü ekle</span>
                      </label>
                      <input type="file" id="replyImage" accept="image/*" class="sr-only">
                      <input type="hidden" id="replyAttachmentBase64" name="attachment">
                  </div>
                  <button type="submit" id="btnReplyTicket" class="primary-button"><svg class="icon"><use href="#i-send"/></svg><span>Gönder</span></button>
              </div>
            </form>
          </div>
          <?php else: ?>
          <div class="chat-reply-box" style="text-align:center;">
              <div class="empty-state" style="padding:20px 0;">
                  <span class="empty-icon" style="margin-bottom:12px;"><svg class="icon"><use href="#i-lock"/></svg></span>
                  <h4 style="margin-bottom:8px;">Talep Kapatıldı</h4>
                  <p>Bu destek talebi sonlandırılmıştır. Yeni bir sorununuz varsa lütfen yeni talep oluşturun.</p>
              </div>
          </div>
          <?php endif; ?>
        </section>
      </section>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll chat to bottom
    const chatContainer = document.querySelector('.chat-thread');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Convert image to base64
    const replyImage = document.getElementById('replyImage');
    const replyAttachmentBase64 = document.getElementById('replyAttachmentBase64');
    const replyFileName = document.getElementById('replyFileName');
    const replyFileLabel = replyImage ? replyImage.closest('.reply-file-wrap')?.querySelector('.reply-file-label') : null;

    if(replyImage) {
        replyImage.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (replyFileName) replyFileName.textContent = file.name;
                if (replyFileLabel) replyFileLabel.classList.add('has-file');
                const reader = new FileReader();
                reader.onloadend = function() {
                    replyAttachmentBase64.value = reader.result;
                };
                reader.readAsDataURL(file);
            } else {
                if (replyFileName) replyFileName.textContent = 'Ekran görüntüsü ekle';
                if (replyFileLabel) replyFileLabel.classList.remove('has-file');
                replyAttachmentBase64.value = '';
            }
        });
    }

    // Submit form via AJAX
    const replyTicketForm = document.getElementById('replyTicketForm');
    if(replyTicketForm) {
        replyTicketForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnReplyTicket');
            btn.disabled = true;
            btn.innerHTML = '<svg class="icon"><use href="#i-clock"/></svg>Gönderiliyor...';

            const formData = new FormData(replyTicketForm);

            fetch('<?= base_url($this->uri->segment(1) . '/profile/replyTicket') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message || 'Cevabınız gönderildi.');
                    } else {
                        alert(data.message || 'Cevabınız gönderildi.');
                    }
                    btn.innerHTML = '<svg class="icon"><use href="#i-check"/></svg><span>Gönderildi</span>';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Bir hata oluştu.');
                    } else {
                        alert(data.message || 'Bir hata oluştu.');
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="icon"><use href="#i-send"/></svg><span>Gönder</span>';
                }
            })
            .catch(err => {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Bir sorun oluştu. Lütfen tekrar deneyin.');
                } else {
                    alert('Bir sorun oluştu. Lütfen tekrar deneyin.');
                }
                btn.disabled = false;
                btn.innerHTML = '<svg class="icon"><use href="#i-send"/></svg><span>Gönder</span>';
            });
        });
    }
});
</script>
