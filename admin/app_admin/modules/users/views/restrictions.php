<div class="card admin-card admin-restrictions-page">
    <div class="card-header">
        <div>
            <h3 class="card-title mb-1">Müşteri kısıtlamaları</h3>
            <div class="text-muted fs-7">Bir müşterinin site üzerindeki işlemlerini geçici olarak durdurun.</div>
        </div>
    </div>
    <div class="card-body">
        <form id="restrictionForm" class="restriction-form row g-4 mb-8">
            <input type="hidden" name="admin_action_csrf" value="<?= html_escape($adminActionCsrfToken) ?>">
            <div class="col-md-3">
                <label class="form-label">Müşteri</label>
                <select name="user_id" id="userSelect" class="form-select" required>
                    <option value="">Müşteri aramak için yazın...</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Süre (dakika)</label>
                <input type="number" name="duration_minutes" min="0" class="form-control" placeholder="0 = sınırsız">
            </div>
            <div class="col-md-3">
                <label class="form-label">Müşteriye gösterilecek neden</label>
                <textarea name="customer_reason" class="form-control" required></textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Dahili neden</label>
                <textarea name="admin_reason" class="form-control" required></textarea>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-danger w-100">Uygula</button>
            </div>
        </form>
        <div class="restriction-list-heading d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Kayıtlı kısıtlamalar</h4>
                <div class="text-muted fs-7">Aktif kayıtlar listenin üstünde gösterilir.</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle restriction-table">
                <thead><tr><th>Müşteri</th><th>Müşteri nedeni</th><th>Dahili neden</th><th>Bitiş</th><th>Durum</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($restrictions as $restriction): ?>
                    <?php $active = (int)$restriction->restriction_status === 1 && (empty($restriction->restriction_expires_at) || strtotime($restriction->restriction_expires_at) > time()); ?>
                    <tr>
                        <td><?= html_escape(trim($restriction->user_name . ' ' . $restriction->user_surname)) ?><br><small><?= html_escape($restriction->user_email) ?></small></td>
                        <td><?= nl2br(html_escape($restriction->restriction_customer_reason)) ?></td>
                        <td><?= nl2br(html_escape($restriction->restriction_admin_reason)) ?></td>
                        <td><?= $restriction->restriction_expires_at ? html_escape($restriction->restriction_expires_at) : 'Sınırsız' ?></td>
                        <td><span class="badge badge-light-<?= $active ? 'danger' : 'secondary' ?>"><?= $active ? 'Aktif' : 'Sona erdi' ?></span></td>
                        <td><?php if ($active): ?><button class="btn btn-sm btn-light-danger js-remove-restriction" data-id="<?= (int)$restriction->restriction_id ?>">Kaldır</button><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
$(function () {
    $('#userSelect').select2({
        placeholder: 'Müşteri ara (ID, İsim, E-posta veya Telefon)',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '<?= base_url('users/searchUsersAjax') ?>',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.results || []
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        language: {
            inputTooShort: function () {
                return 'Aramak için en az 2 karakter girin...';
            },
            searching: function () {
                return 'Aranıyor...';
            },
            noResults: function () {
                return 'Kullanıcı bulunamadı.';
            }
        }
    });
    $.ajaxSetup({ headers: { 'X-Admin-CSRF': <?= json_encode($adminActionCsrfToken) ?> } });

    $('#restrictionForm').on('submit', function (event) {
        event.preventDefault();
        $.post('<?= base_url('users/createRestriction') ?>', $(this).serialize(), function (response) {
            if (response.status === 'success') location.reload();
            else alert(response.message || 'İşlem başarısız.');
        }, 'json');
    });

    $('.js-remove-restriction').on('click', function () {
        if (!confirm('Bu kısıtlama kaldırılsın mı?')) return;
        $.post('<?= base_url('users/removeRestriction') ?>', {restriction_id: $(this).data('id'), admin_action_csrf: <?= json_encode($adminActionCsrfToken) ?>}, function (response) {
            if (response.status === 'success') location.reload();
            else alert(response.message || 'İşlem başarısız.');
        }, 'json');
    });
});
</script>
