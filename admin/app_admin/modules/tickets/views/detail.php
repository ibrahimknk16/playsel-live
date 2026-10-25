<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm card-flush">
            <div class="card-header">
                <h3 class="card-title">Talep #<?= $ticket->tickets_id ?> - <?= html_escape($ticket->tickets_subject) ?></h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary me-3 fs-7">Konu: <?= html_escape($ticket->tickets_topic) ?></span>
                    <span class="badge badge-light-dark me-3 fs-7">Kullanıcı: <?= html_escape($ticket->user_name) ?> (<?= html_escape($ticket->user_email) ?>)</span>
                    
                    <?php if ($ticket->tickets_status == 0): ?>
                        <span class="badge badge-light-warning me-3 fs-7">Kullanıcı Cevabı</span>
                    <?php elseif ($ticket->tickets_status == 1): ?>
                        <span class="badge badge-light-success me-3 fs-7">Yanıtlandı</span>
                    <?php elseif ($ticket->tickets_status == 2): ?>
                        <span class="badge badge-light-secondary me-3 fs-7">Kapatıldı</span>
                    <?php endif; ?>

                    <?php if ($ticket->tickets_status != 2): ?>
                        <button type="button" class="btn btn-sm btn-light-danger btn-update-status" data-id="<?= $ticket->tickets_id ?>" data-status="2">
                            <i class="fa-solid fa-lock"></i> Talebi Kapat
                        </button>
                    <?php endif; ?>
                    <?php if (!empty($ticket->tickets_order_id) && $ticket->tickets_status != 2): ?>
                        <button type="button" class="btn btn-sm btn-danger btn-cancel-refund" data-ticket-id="<?= (int)$ticket->tickets_id ?>">
                            <i class="fa-solid fa-rotate-left"></i> Siparişi İptal Et ve İade Et
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">
                <div class="ticket-chat-container" style="background-color: #f5f8fa; border: 1px solid #e4e6ef; border-radius: 8px; padding: 20px; display: flex; flex-direction: column; gap: 15px; max-height: 600px; overflow-y: auto; margin-bottom: 20px;">
                    <?php foreach ($messages as $msg): ?>
                        <?php if ($msg->ticket_messages_user_id > 0): ?>
                            <!-- User Message (Left for Admin) -->
                            <div style="align-self: flex-start; background-color: #fff; padding: 15px; border-radius: 0 8px 8px 8px; border: 1px solid #e4e6ef; max-width: 75%; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <div style="font-size: 12px; color: #7e8299; margin-bottom: 5px;">Kullanıcı - <?= date('d.m.Y H:i', strtotime($msg->ticket_messages_created_at)) ?></div>
                                <div style="white-space: pre-wrap; color: #3f4254; font-weight: 500;"><?= html_escape($msg->ticket_messages_message) ?></div>
                                <?php if (!empty($msg->ticket_messages_attachment) && preg_match('#^data:image/(png|jpeg|jpg|webp|gif);base64,#i', $msg->ticket_messages_attachment)): ?>
                                    <div style="margin-top: 10px;">
                                        <img src="<?= html_escape($msg->ticket_messages_attachment) ?>" style="max-width: 100%; border-radius: 4px; cursor: pointer; border: 1px solid #e4e6ef;" onclick="window.open(this.src, '_blank')">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <!-- Admin Message (Right for Admin) -->
                            <div style="align-self: flex-end; background-color: #e8fff3; padding: 15px; border-radius: 8px 8px 0 8px; border: 1px solid #50cd89; max-width: 75%; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <div style="font-size: 12px; color: #50cd89; margin-bottom: 5px; text-align: right;">Yetkili (Siz) - <?= date('d.m.Y H:i', strtotime($msg->ticket_messages_created_at)) ?></div>
                                <div style="white-space: pre-wrap; color: #3f4254; font-weight: 500;"><?= html_escape($msg->ticket_messages_message) ?></div>
                                <?php if (!empty($msg->ticket_messages_attachment) && preg_match('#^data:image/(png|jpeg|jpg|webp|gif);base64,#i', $msg->ticket_messages_attachment)): ?>
                                    <div style="margin-top: 10px;">
                                        <img src="<?= html_escape($msg->ticket_messages_attachment) ?>" style="max-width: 100%; border-radius: 4px; cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($ticket->tickets_status != 2): ?>
                    <form id="adminReplyForm">
                        <input type="hidden" name="ticket_id" value="<?= $ticket->tickets_id ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cevap Yazın</label>
                            <textarea class="form-control form-control-solid" name="message" rows="4" placeholder="Kullanıcıya iletilecek mesajınızı buraya yazın..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Görsel / Dosya</label>
                            <input type="file" id="adminReplyAttachmentFile" class="form-control form-control-solid">
                            <input type="hidden" name="attachment" id="adminReplyAttachment">
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary" id="btnAdminReply">
                                <i class="fa-solid fa-paper-plane"></i> Yanıtla
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-secondary text-center">
                        Bu destek talebi kapatılmıştır. Kapatılan taleplere cevap yazılamaz.
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll chat to bottom
    const chatContainer = document.querySelector('.ticket-chat-container');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Submit form via AJAX
    const adminReplyForm = document.getElementById('adminReplyForm');
    if(adminReplyForm) {
        const attachmentFile = document.getElementById('adminReplyAttachmentFile');
        const attachmentField = document.getElementById('adminReplyAttachment');
        if (attachmentFile) {
            attachmentFile.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) {
                    attachmentField.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function() { attachmentField.value = reader.result; };
                reader.readAsDataURL(file);
            });
        }
        adminReplyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnAdminReply');
            btn.disabled = true;
            btn.innerText = 'Gönderiliyor...';

            const formData = new FormData(adminReplyForm);

            $.ajax({
                url: '<?= base_url('tickets/reply/') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Başarılı!', response.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Hata!', response.message, 'error');
                        btn.disabled = false;
                        btn.innerText = 'Yanıtla';
                    }
                },
                error: function() {
                    Swal.fire('Hata!', 'Bir sunucu hatası oluştu.', 'error');
                    btn.disabled = false;
                    btn.innerText = 'Yanıtla';
                }
            });
        });
    }

    // Close ticket
    const statusButtons = document.querySelectorAll('.btn-update-status');
    statusButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-id');
            const status = this.getAttribute('data-status');
            
            Swal.fire({
                title: 'Talebi kapatmak istediğinize emin misiniz?',
                text: 'Kapatılan taleplere tekrar cevap yazılamaz.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f1416c',
                cancelButtonColor: '#a1a5b7',
                confirmButtonText: 'Evet, kapat!',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('tickets/updateStatus') ?>',
                        type: 'POST',
                        data: {
                            ticket_id: ticketId,
                            status: status
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Başarılı!', response.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Hata!', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Hata!', 'Bir sunucu hatası oluştu.', 'error');
                        }
                    });
                }
            });
        });
    });

    const refundButton = document.querySelector('.btn-cancel-refund');
    if (refundButton) {
        refundButton.addEventListener('click', function() {
            Swal.fire({
                title: 'İptal sebebi',
                html: `
                    <select id="cancelReasonId" class="form-select mb-3 form-select-solid">
                        <option value="">Manuel sebep yazacağım</option>
                        <?php foreach ($cancellation_reasons as $reason): ?>
                            <option value="<?= (int)$reason->reason_id ?>"><?= html_escape($reason->reason_text) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea id="cancelManualReason" class="form-control form-control-solid" placeholder="Manuel iptal sebebi (seçim yapılmadıysa zorunlu)"></textarea>
                `,
                showCancelButton: true,
                confirmButtonText: 'İptal et ve iade et',
                cancelButtonText: 'Vazgeç',
                confirmButtonColor: '#f1416c',
                preConfirm: () => {
                    const reasonId = document.getElementById('cancelReasonId').value;
                    const manualReason = document.getElementById('cancelManualReason').value.trim();
                    if (!reasonId && !manualReason) {
                        Swal.showValidationMessage('İptal sebebi zorunludur.');
                        return false;
                    }
                    return { reasonId: reasonId, manualReason: manualReason };
                }
            }).then(result => {
                if (!result.isConfirmed) return;
                $.getJSON('<?= base_url('tickets/getRefundItems') ?>', {ticket_id: refundButton.dataset.ticketId}, function(itemsResponse) {
                    if (itemsResponse.status !== 'success') {
                        Swal.fire('Hata', itemsResponse.message, 'error');
                        return;
                    }
                    const available = itemsResponse.items.filter(item => !item.refunded);
                    if (!available.length) {
                        Swal.fire('Bilgi', 'İade edilebilir kalem kalmadı.', 'info');
                        return;
                    }
                    const html = available.map(item =>
                        `<label class="d-block text-start mb-2"><input type="checkbox" class="refund-item me-2" value="${item.key}" data-amount="${item.amount}">${item.label} - ${Number(item.amount).toFixed(2)} TL</label>`
                    ).join('');
                    Swal.fire({
                        title: 'İade edilecek kalemleri seçin',
                        html: html,
                        showCancelButton: true,
                        confirmButtonText: 'Seçilenleri iade et',
                        preConfirm: () => {
                            const selected = [...document.querySelectorAll('.refund-item:checked')].map(input => input.value);
                            if (!selected.length) Swal.showValidationMessage('En az bir kalem seçin.');
                            return selected;
                        }
                    }).then(selection => {
                        if (!selection.isConfirmed) return;
                        $.post('<?= base_url('tickets/refundSelectedItems') ?>', {
                            ticket_id: refundButton.dataset.ticketId,
                            item_keys: selection.value,
                            reason_id: result.value.reasonId,
                            manual_reason: result.value.manualReason
                        }, function(response) {
                            Swal.fire(response.status === 'success' ? 'Başarılı' : 'Hata', response.message, response.status === 'success' ? 'success' : 'error')
                                .then(() => { if (response.status === 'success') window.location.reload(); });
                        }, 'json');
                    });
                }, 'json');
            });
        });
    }
});
</script>
