<?php
$cdnBaseUrl = rtrim((string) $cdn_url, '/') . '/';
?>
<style>
    .cdn-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; }
    .cdn-gallery-card { background: var(--admin-bg-surface); border: 1px solid var(--admin-border-subtle); border-radius: 12px; overflow: hidden; }
    .cdn-gallery-preview { height: 170px; background: #0f101a; display: flex; align-items: center; justify-content: center; }
    .cdn-gallery-preview img { width: 100%; height: 100%; object-fit: contain; }
    .cdn-gallery-card-body { padding: 1rem; }
    .cdn-url-input { font-size: .75rem; }
</style>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="card admin-card mb-6">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <h2 class="card-title mb-1">CDN Galerisi</h2>
            <span class="text-muted">Statik görsellerinizi yükleyin, yönetin ve doğrudan CDN URL’sini kullanın.</span>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cdnUploadModal">
            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Görsel Yükle
        </button>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-3 mb-5">
            <input type="search" id="cdnSearch" class="form-control w-300px" placeholder="Görsel ara...">
            <select id="cdnFolder" class="form-select w-200px">
                <option value="">Tüm klasörler</option>
                <?php $folders = array_unique(array_map(function ($item) { return $item->folder; }, $items)); sort($folders); ?>
                <?php foreach ($folders as $folder): ?>
                    <option value="<?= htmlspecialchars($folder, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($folder, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="cdnGalleryGrid" class="cdn-gallery-grid">
            <?php foreach ($items as $item): ?>
                <?php $url = $cdnBaseUrl . ltrim($item->object_key, '/'); ?>
                <article class="cdn-gallery-card" data-search="<?= htmlspecialchars(strtolower($item->title . ' ' . $item->file_name . ' ' . $item->folder), ENT_QUOTES, 'UTF-8') ?>" data-folder="<?= htmlspecialchars($item->folder, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="cdn-gallery-preview">
                        <img src="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item->alt_text ?: $item->title, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                    </div>
                    <div class="cdn-gallery-card-body">
                        <div class="fw-bold text-white text-truncate" title="<?= htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-muted fs-8 mb-3"><?= htmlspecialchars($item->folder, ENT_QUOTES, 'UTF-8') ?> · <?= (int) $item->width ?>×<?= (int) $item->height ?></div>
                        <div class="input-group input-group-sm mb-3">
                            <input class="form-control cdn-url-input" value="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" readonly>
                            <button class="btn btn-light-primary copy-cdn-url" type="button" title="URL’yi kopyala"><i class="fa-regular fa-copy"></i></button>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-sm btn-light-primary edit-cdn" data-id="<?= (int) $item->id ?>" data-title="<?= htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') ?>" data-alt="<?= htmlspecialchars($item->alt_text, ENT_QUOTES, 'UTF-8') ?>">Düzenle</button>
                            <button class="btn btn-sm btn-light-danger delete-cdn" data-id="<?= (int) $item->id ?>">Sil</button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (empty($items)): ?>
            <div class="text-center text-muted py-15">Henüz CDN galerisine görsel yüklenmedi.</div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="cdnUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="<?= base_url('cdn/save') ?>" method="post" enctype="multipart/form-data">
            <div class="modal-header"><h3 class="modal-title">CDN’e Görsel Yükle</h3><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label required">Görsel</label>
                <input class="form-control mb-4" type="file" name="cdn_file" accept="image/jpeg,image/png,image/webp" required>
                <label class="form-label">Başlık</label>
                <input class="form-control mb-4" type="text" name="title" maxlength="150" placeholder="Örn. Ana logo">
                <label class="form-label">Klasör</label>
                <input class="form-control mb-4" type="text" name="folder" value="general" maxlength="50" pattern="[A-Za-z0-9_-]+">
                <label class="form-label">Alt metin</label>
                <input class="form-control" type="text" name="alt_text" maxlength="255" placeholder="Erişilebilirlik için açıklama">
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button><button class="btn btn-primary">Yükle</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="cdnEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="<?= base_url('cdn/update') ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" id="cdnEditId">
            <div class="modal-header"><h3 class="modal-title">Görseli Düzenle</h3><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label required">Başlık</label><input class="form-control mb-4" type="text" name="title" id="cdnEditTitle" maxlength="150" required>
                <label class="form-label">Alt metin</label><input class="form-control mb-4" type="text" name="alt_text" id="cdnEditAlt" maxlength="255">
                <label class="form-label">Yeni görsel (isteğe bağlı)</label><input class="form-control" type="file" name="cdn_file" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">Yeni görsel yüklenirse mevcut dosyanın üzerine geçer.</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button><button class="btn btn-primary">Kaydet</button></div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cards = Array.from(document.querySelectorAll('.cdn-gallery-card'));
    const filter = function () {
        const query = document.getElementById('cdnSearch').value.toLowerCase().trim();
        const folder = document.getElementById('cdnFolder').value;
        cards.forEach(function (card) {
            card.classList.toggle('d-none', (query && !card.dataset.search.includes(query)) || (folder && card.dataset.folder !== folder));
        });
    };
    document.getElementById('cdnSearch').addEventListener('input', filter);
    document.getElementById('cdnFolder').addEventListener('change', filter);
    document.querySelectorAll('.copy-cdn-url').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = button.closest('.input-group').querySelector('input');
            navigator.clipboard.writeText(input.value).then(function () {
                button.innerHTML = '<i class="fa-solid fa-check"></i>';
                setTimeout(function () { button.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 1200);
            });
        });
    });
    document.querySelectorAll('.edit-cdn').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('cdnEditId').value = button.dataset.id;
            document.getElementById('cdnEditTitle').value = button.dataset.title;
            document.getElementById('cdnEditAlt').value = button.dataset.alt;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('cdnEditModal')).show();
        });
    });
    document.querySelectorAll('.delete-cdn').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!window.confirm('Bu görsel CDN’den ve galeriden silinsin mi?')) return;
            fetch('<?= base_url('cdn/delete/') ?>' + button.dataset.id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (response) { return response.json(); })
                .then(function (data) { if (data.status === 'success') { button.closest('.cdn-gallery-card').remove(); } else { alert(data.message); } })
                .catch(function () { alert('Silme işlemi sırasında bir hata oluştu.'); });
        });
    });
});
</script>
