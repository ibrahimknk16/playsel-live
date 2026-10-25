<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Destek Talepleri</h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-primary" onclick="location.reload()">
                    <i class="fa-solid fa-sync"></i> Yenile
                </button>
                <button type="button" class="btn btn-sm btn-light-danger" data-bs-toggle="modal" data-bs-target="#cancellationReasonsModal">
                    <i class="fa-solid fa-ban"></i> İptal sebepleri
                </button>
            </div>
        </div>

        <div class="modal fade" id="cancellationReasonsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Sipariş iptal sebepleri</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                    </div>
                    <div class="modal-body">
                        <form id="cancellationReasonForm" class="d-flex gap-2 mb-4">
                            <input type="text" name="reason" class="form-control form-control-solid" placeholder="Yeni iptal sebebi" required>
                            <button class="btn btn-primary" type="submit">Ekle</button>
                        </form>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($cancellation_reasons as $reason): ?>
                                <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2">
                                    <span><?= html_escape($reason->reason_text) ?></span>
                                    <span class="badge badge-light-success">Aktif</span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($cancellation_reasons)): ?>
                                <span class="text-muted">Henüz tanımlı iptal sebebi yok.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="ticketsTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bolder text-muted">
                            <th class="w-25px">ID</th>
                            <th class="min-w-150px">Kullanıcı</th>
                            <th class="min-w-150px">Konu / Başlık</th>
                            <th class="min-w-120px">Son Güncelleme</th>
                            <th class="min-w-100px">Durum</th>
                            <th class="min-w-150px text-end">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Henüz destek talebi bulunmamaktadır.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?= $ticket->tickets_id ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bolder"><?= html_escape($ticket->user_name) ?></span>
                                            <span class="text-muted fw-bold text-muted d-block fs-7"><?= html_escape($ticket->user_email) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-gray-800 fw-bolder d-block fs-6"><?= html_escape($ticket->tickets_subject) ?></span>
                                        <span class="text-muted fw-bold d-block fs-7"><?= html_escape($ticket->tickets_topic) ?></span>
                                    </td>
                                    <td>
                                        <span class="text-gray-600 fw-bold d-block fs-7"><?= date('d.m.Y H:i', strtotime($ticket->tickets_updated_at)) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($ticket->tickets_status == 0): ?>
                                            <span class="badge badge-light-warning">Kullanıcı Cevabı</span>
                                        <?php elseif ($ticket->tickets_status == 1): ?>
                                            <span class="badge badge-light-success">Yanıtlandı</span>
                                        <?php elseif ($ticket->tickets_status == 2): ?>
                                            <span class="badge badge-light-secondary">Kapatıldı</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url('tickets/detail/' . $ticket->tickets_id) ?>" class="btn btn-icon btn-light-primary btn-sm me-1" title="Görüntüle/Cevapla">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <?php if ($ticket->tickets_status != 2): ?>
                                            <button class="btn btn-icon btn-light-danger btn-sm btn-update-status" data-id="<?= $ticket->tickets_id ?>" data-status="2" title="Talebi Kapat">
                                                <i class="fa-solid fa-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const statusButtons = document.querySelectorAll('.btn-update-status');

        $('#cancellationReasonForm').on('submit', function(e) {
            e.preventDefault();
            $.post('<?= base_url('tickets/createCancellationReason') ?>', $(this).serialize(), function(response) {
                Swal.fire(response.status === 'success' ? 'Başarılı' : 'Hata', response.message, response.status === 'success' ? 'success' : 'error')
                    .then(() => { if (response.status === 'success') location.reload(); });
            }, 'json');
        });

        statusButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                const status = this.getAttribute('data-status');
                
                Swal.fire({
                    title: 'Emin misiniz?',
                    text: 'Bu destek talebini kapatmak istediğinize emin misiniz? Kapatılan taleplere tekrar cevap yazılamaz.',
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
                                        location.reload();
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
    });
</script>
