<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Ürün Yorumları</h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-primary" onclick="location.reload()">
                    <i class="fa-solid fa-sync"></i> Yenile
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="reviewsTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bolder text-muted">
                            <th class="w-25px">ID</th>
                            <th class="min-w-150px">Kullanıcı</th>
                            <th class="min-w-150px">Ürün</th>
                            <th class="min-w-100px">Puan</th>
                            <th class="min-w-200px">Yorum</th>
                            <th class="min-w-120px">Tarih</th>
                            <th class="min-w-100px">Durum</th>
                            <th class="min-w-150px text-end">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Henüz ürün yorumu bulunmamaktadır.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?= $review->review_id ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bolder"><?= html_escape($review->user_name) ?></span>
                                            <span class="text-muted fw-bold text-muted d-block fs-7"><?= html_escape($review->user_email) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-gray-800 fw-bolder d-block fs-6"><?= html_escape($review->product_name) ?></span>
                                    </td>
                                    <td>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review->review_rating): ?>
                                                    <i class="fa-solid fa-star text-warning"></i>
                                                <?php else: ?>
                                                    <i class="fa-regular fa-star text-muted"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-gray-600 d-block fs-6" style="max-width: 300px; white-space: pre-wrap; word-wrap: break-word;"><?= html_escape($review->review_comment) ?></span>
                                    </td>
                                    <td>
                                        <span class="text-gray-600 fw-bold d-block fs-7"><?= date('d.m.Y H:i', strtotime($review->review_created_at)) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($review->review_status == 0): ?>
                                            <span class="badge badge-light-warning">Bekliyor</span>
                                        <?php elseif ($review->review_status == 1): ?>
                                            <span class="badge badge-light-success">Onaylandı</span>
                                        <?php elseif ($review->review_status == 2): ?>
                                            <span class="badge badge-light-danger">Reddedildi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($review->review_status == 0): ?>
                                            <button class="btn btn-icon btn-light-success btn-sm me-1 btn-update-status" data-id="<?= $review->review_id ?>" data-status="1" title="Onayla">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button class="btn btn-icon btn-light-danger btn-sm me-1 btn-update-status" data-id="<?= $review->review_id ?>" data-status="2" title="Reddet">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-icon btn-light-dark btn-sm btn-update-status" data-id="<?= $review->review_id ?>" data-status="3" title="Sil">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
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

        statusButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = this.getAttribute('data-id');
                const status = this.getAttribute('data-status');
                
                let actionText = '';
                let confirmColor = '';
                
                if (status == 1) {
                    actionText = 'Bu yorumu onaylamak';
                    confirmColor = '#50cd89'; // success
                } else if (status == 2) {
                    actionText = 'Bu yorumu reddetmek';
                    confirmColor = '#f1416c'; // danger
                } else if (status == 3) {
                    actionText = 'Bu yorumu kalıcı olarak silmek';
                    confirmColor = '#181c32'; // dark
                }

                Swal.fire({
                    title: 'Emin misiniz?',
                    text: actionText + ' istediğinize emin misiniz?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#a1a5b7',
                    confirmButtonText: 'Evet, devam et!',
                    cancelButtonText: 'İptal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '<?= base_url('products/updateReviewStatus') ?>',
                            type: 'POST',
                            data: {
                                review_id: reviewId,
                                status: status
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire(
                                        'Başarılı!',
                                        response.message,
                                        'success'
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Hata!',
                                        response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function() {
                                Swal.fire(
                                    'Hata!',
                                    'Bir sunucu hatası oluştu.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    });
</script>
