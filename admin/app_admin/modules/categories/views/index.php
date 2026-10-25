<style>
    #productTable_filter {
        display: block !important;
    }

    #productTable tbody tr td {
        padding: 0.6rem;
        vertical-align: middle;
    }

    .p-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .p-symbol {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, .03);
        flex: 0 0 40px;
    }

    .p-symbol img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        display: block;
    }

    .p-info {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .p-name a {
        color: inherit;
        text-decoration: none;
    }

    .p-name a:hover {
        text-decoration: underline;
    }

    .p-sub {
        margin-top: 4px;
        font-size: 12px;
        color: #6c757d;
    }

    .p-sub .dot {
        margin: 0 6px;
        opacity: .6;
    }

    /* Durum hücresi */
    .status-wrap {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }

    /* İşlemler */
    .actions-col {
        white-space: nowrap;
        width: 1%;
    }

    .actions-wrap {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        align-items: center;
    }
</style>

<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Kategoriler</h3>
            <!-- <div class="card-toolbar">
                <a href="<?= base_url('categories/add') ?>" class="btn btn-sm btn-success">
                    <i class="fa-plus text-white"></i> Kategori Ekle
                </a>
            </div>
        </div> -->
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-success" data-bs-target="#newCategoryModal" data-bs-toggle="modal">
                    <i class="fa-plus text-white"></i> Kategori Ekle
                </button>
            </div>
        </div>
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('error'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <style>
            #categoryTable tbody tr td {
                padding: 0.5rem;
            }
        </style>
        <div class="card-body">
            <div class="row g-3 mb-5" id="categoryFilters">
                <div class="col-md-3">
                    <label class="form-label">Durum</label>
                    <select class="form-select form-select-sm form-select-solid" id="categoryStatusFilter">
                        <option value="">Tüm Durumlar</option>
                        <option value="1">Aktif</option>
                        <option value="0">Pasif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Popülerlik</label>
                    <select class="form-select form-select-sm form-select-solid" id="categoryPopularFilter">
                        <option value="">Tümü</option>
                        <option value="1">Popüler</option>
                        <option value="0">Popüler değil</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tür</label>
                    <select class="form-select form-select-sm form-select-solid" id="categoryTypeFilter">
                        <option value="">Tüm Türler</option>
                        <option value="2">Topup</option>
                        <option value="3">Epin</option>
                        <option value="4">CD Key</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-light w-100" id="resetCategoryFilters">Filtreleri Temizle</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="categoryTable" class="table align-middle table-row-dashed fs-6 gy-4">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th style="width:120px">Kategori ID</th>
                            <th style="width:220px">Kategori Adı</th>
                            <th style="width:280px">Slug</th>
                            <th style="width:180px">Durum</th>
                            <th style="width:180px">Popüler mi?</th>
                            <th style="width:120px">Tür</th>
                            <th class="text-end" style="width:160px">İşlemler</th>
                        </tr>
                    </thead>

                    <tbody class="fw-semibold text-gray-600">
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <span class="text-gray-800 fw-bold">#<?= $category->category_id; ?></span>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">

                                        <div class="p-symbol m-2">
                                            <?php if (!empty($category->category_image)): ?>
                                                <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $category->category_image ?>" alt="" onerror="this.onerror=null;this.src='<?= topup_url('assets/img/no-image.webp') ?>';">
                                            <?php else: ?>
                                                <div class="symbol-label bg-light-info text-info fw-bold">
                                                    <?= mb_strtoupper(mb_substr($category->category_name, 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="d-flex flex-column">
                                            <div class="p-name text-gray-900 fw-bold text-hover-primary">
                                                <a href="<?= base_url('categories/edit/' . $category->category_id) ?>">
                                                    <?= $category->category_title ?: '-' ?>
                                                </a>
                                                </span>
                                            </div>
                                        </div>
                                </td>
                                <td>
                                    <?= $category->category_slug ?>
                                </td>
                                <td>
                                    <?php $isActive = ((int)$category->category_status === 1); ?>
                                    <span class="badge <?= $isActive ? 'badge-light-success' : 'badge-light-danger' ?> fw-semibold js-cat-status-badge">
                                        <?= $isActive ? 'Aktif' : 'Pasif' ?>
                                    </span>

                                    <div class="form-check form-switch form-check-custom form-check-solid ms-2 d-inline-flex align-items-center">
                                        <input class="form-check-input js-category-status-switch"
                                            type="checkbox"
                                            data-category-id="<?= (int)$category->category_id ?>"
                                            <?= $isActive ? 'checked' : '' ?>>
                                    </div>
                                </td>

                                 <td>
                                    <?php $isPopular = ((int)$category->category_is_popular === 1); ?>
                                    <span class="badge <?= $isPopular ? 'badge-light-success' : 'badge-light-danger' ?> fw-semibold js-cat-popular-badge">
                                        <?= $isPopular ? 'Evet' : 'Hayır' ?>
                                    </span>

                                    <div class="form-check form-switch form-check-custom form-check-solid ms-2 d-inline-flex align-items-center">
                                        <input class="form-check-input js-category-popular-switch"
                                            type="checkbox"
                                            data-category-id="<?= (int)$category->category_id ?>"
                                            <?= $isPopular ? 'checked' : '' ?>>
                                    </div>
                                </td>

                                <td>
                                    <?php $type = (int)($category->category_type ?? 0); ?>
                                    <?php
                                    $typeText =
                                        ($type === 2) ? 'Topup' : (($type === 3) ? 'Epin' : (($type === 4) ? 'CD Key' : '-'));
                                    $typeClass =
                                        ($type === 2) ? 'badge-light-primary' : (($type === 3) ? 'badge-light-success' : (($type === 4) ? 'badge-light-warning' : 'badge-light'));
                                    ?>
                                    <span class="badge <?= $typeClass ?> fw-semibold">
                                        <?= $typeText ?>
                                    </span>

                                </td>

                                <td class="text-end actions-col">
                                    <div class="actions-wrap">

                                        <a href="<?= base_url('reports/categoryHourChart/' . $category->category_id) ?>" class="btn btn-sm btn-light-primary" title="Saatlik Satış İstatistikleri">
                                            <i class="ki-duotone ki-chart fs-5">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </a>
                                        <a href="<?= base_url('categories/edit/' . $category->category_id) ?>" class="btn btn-sm btn-light-primary" title="Düzenle">
                                            <i class="ki-duotone ki-pencil fs-5">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </a>

                                        <button type="button"
                                            class="btn btn-sm btn-light-danger js-category-delete"
                                            title="Sil"
                                            data-category-id="<?= (int)$category->category_id ?>"
                                            data-category-title="<?= html_escape($category->category_title) ?>">
                                            <i class="ki-duotone ki-trash fs-5">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                            </i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newCategoryModalLabel">Yeni Kategori Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newCategoryForm" action="<?= base_url('categories/add') ?>" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="newCategoryTitle" class="form-label">Kategori Başlığı</label>
                            <input type="text" class="form-control form-control-solid" id="newCategoryTitle" name="newCategoryTitle" required>
                        </div>

                        <div class="mb-3 col-md-6 d-none">
                            <label for="newCategorySlug" class="form-label">Kategori Slug</label>
                            <input type="text" class="form-control form-control-solid" id="newCategorySlug" name="newCategorySlug">
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="newCategoryPublisher" class="form-label">Yayıncı Firma</label>
                            <input type="text" class="form-control form-control-solid" id="newCategoryPublisher" name="newCategoryPublisher">
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="newCategoryStatus" class="form-label">Durum</label>
                            <select class="form-select form-select-solid" id="newCategoryStatus" name="newCategoryStatus" required>
                                <option value="1">Aktif</option>
                                <option value="0">Aktif değil</option>
                            </select>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="newCategoryTucStatus" class="form-label">
                                TopupCenter Durum
                                <span
                                    data-bs-toggle="tooltip"
                                    title="Kategorinin TopupCenter'da açık olup olmayacağını belirler."
                                    style="cursor: pointer; color: #0d6efd; margin-left: 5px;">
                                    <i class="fa fa-question-circle"></i>
                                </span>
                            </label>
                            <select class="form-select form-select-solid" id="newCategoryTucStatus" name="newCategoryTucStatus" required>
                                <option value="1">Aktif</option>
                                <option value="0">Aktif değil</option>
                            </select>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="formIconFile" class="form-label">Kategori Resmi Yükle</label>
                            <input class="form-control form-control-solid" type="file" id="formThumbnailFile" name="newCategoryImage">
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="formImgFile" class="form-label">Kategori Thumbnail Yükle</label>
                            <input class="form-control form-control-solid" type="file" id="formImgFile" name="newCategoryThumbnail">
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="newCategoryAndroidUrl" class="form-label">Android App Url</label>
                            <input type="text" class="form-control form-control-solid" id="newCategoryAndroidUrl" name="newCategoryAndroidUrl">
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="newCategoryIosUrl" class="form-label">iOS App Url</label>
                            <input type="text" class="form-control form-control-solid" id="newCategoryIosUrl" name="newCategoryIosUrl">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label fw-bold mb-2">Kategori Tipi</label>
                            <select class="form-select form-select-solid" name="category_type">
                                <option value="2">Topup</option>
                                <option value="3">Epin</option>
                                <option value="4">CD Key</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Kaydet</button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#categoryTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            pageLength: 15,
            lengthMenu: [[15, 25, 50, 100, 250, 500], [15, 25, 50, 100, 250, 500]],
            ajax: {
                url: '<?= site_url('categories/ajaxList') ?>',
                type: 'POST',
                data: function(data) {
                    data.category_status = $('#categoryStatusFilter').val();
                    data.category_is_popular = $('#categoryPopularFilter').val();
                    data.category_type = $('#categoryTypeFilter').val();
                },
                error: function() {
                    if (typeof toastr !== 'undefined') toastr.error('Kategoriler yüklenemedi.');
                }
            },
            columns: [{}, {}, {}, {}, {}, {}, {}],
            language: { url: 'https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json' },
            dom: '<"top"f>rt<"bottom"lp><"clear">'
        });
        $('#categoryFilters').on('change', 'select', function() {
            $('#categoryTable').DataTable().ajax.reload(null, true);
        });
        $('#resetCategoryFilters').on('click', function() {
            $('#categoryFilters select').val('');
            $('#categoryTable').DataTable().ajax.reload(null, true);
        });

        $(document).on('change', '.js-category-status-switch', function() {
            const $sw = $(this);
            const categoryId = $sw.data('category-id');
            const newStatus = $sw.is(':checked') ? 1 : 0;

            if ($sw.data('busy')) return;

            const $badge = $sw.closest('td').find('.js-cat-status-badge');

            // UI helper: badge update
            const setBadge = (st) => {
                $badge
                    .removeClass('badge-light-success badge-light-danger')
                    .addClass(st === 1 ? 'badge-light-success' : 'badge-light-danger')
                    .text(st === 1 ? 'Aktif' : 'Pasif');
            };

            const doChange = function(forceVal) {
                $sw.data('busy', true).prop('disabled', true);

                $.ajax({
                    url: '<?= site_url('categories/changeStatus') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        category_id: categoryId,
                        category_status: newStatus,
                        force: forceVal ? 1 : 0
                    },
                    success: function(res) {
                        if (res && res.status === 'success') {
                            if (typeof toastr !== 'undefined') toastr.success(res.message || 'Güncellendi');
                            setBadge(newStatus);
                        } else {
                            if (res && res.code === 'HAS_ACTIVE_PRODUCTS' && newStatus === 0 && typeof Swal !== 'undefined') {
                                const items = (res.data && res.data.items) ? res.data.items : [];
                                const total = (res.data && res.data.total) ? res.data.total : items.length;
                                const shown = (res.data && res.data.shown) ? res.data.shown : items.length;

                                let listHtml = '';
                                if (items.length) {
                                    listHtml += '<div style="text-align:left;max-height:260px;overflow:auto;border:1px solid rgba(0,0,0,.08);border-radius:10px;padding:10px;">';
                                    listHtml += '<ul style="margin:0;padding-left:18px;">';
                                    items.forEach(function(p) {
                                        const name = (p.product_name || '').toString()
                                            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                                            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                                        listHtml += `<li style="margin:6px 0;">
                                                <strong>#${p.product_id}</strong> - ${name}
                                            </li>`;
                                    });
                                    listHtml += '</ul></div>';

                                    if (total > shown) {
                                        listHtml += `<div class="text-muted mt-3" style="font-size:12px;text-align:left;">
                                                +${total - shown} ürün daha var.
                                            </div>`;
                                    }
                                }

                                $sw.prop('checked', true);
                                setBadge(1);

                                Swal.fire({
                                    title: 'Bağlı ürünler var',
                                    html: `
                                <div style="text-align:left;">
                                    <div>${res.message || 'Bu kategoriye bağlı aktif ürünler var.'}</div>
                                    <div class="mt-3"><strong>Aktif Ürünler:</strong></div>
                                    ${listHtml || '<div class="text-muted mt-2">Liste alınamadı.</div>'}
                                    <div class="mt-3">Kategori pasife alınırsa bu ürünler de <b>pasife (0) çekilecek. Devam edilsin mi?</div>
                                </div>
                            `,
                                    icon: 'warning',
                                    width: 650,
                                    showCancelButton: true,
                                    confirmButtonText: 'Evet',
                                    cancelButtonText: 'İptal',
                                    buttonsStyling: false,
                                    customClass: {
                                        confirmButton: 'btn btn-danger',
                                        cancelButton: 'btn btn-light'
                                    }
                                }).then((r) => {
                                    if (r.isConfirmed) {
                                        $sw.prop('checked', false);
                                        setBadge(0);
                                        doChange(true);
                                    } else {}
                                });

                            } else {
                                // Hata -> switch geri al
                                $sw.prop('checked', !newStatus);
                                setBadge(!newStatus ? 0 : 1);
                                if (typeof toastr !== 'undefined') toastr.error((res && res.message) ? res.message : 'Güncellenemedi');
                            }
                        }
                    },
                    error: function(xhr) {

                        $sw.prop('checked', !newStatus);
                        setBadge(!newStatus ? 0 : 1);
                        if (typeof toastr !== 'undefined') toastr.error('Sunucu hatası. Tekrar deneyin.');
                    },
                    complete: function() {
                        $sw.data('busy', false).prop('disabled', false);
                    }
                });
            };

            if (newStatus === 1) {
                doChange(false);
                return;
            }
            doChange(false);
        });

        $(document).on('change', '.js-category-popular-switch', function() {
            const $sw = $(this);
            const categoryId = $sw.data('category-id');
            const newStatus = $sw.is(':checked') ? 1 : 0;

            if ($sw.data('busy')) return;

            const $badge = $sw.closest('td').find('.js-cat-popular-badge');

            const setBadge = (st) => {
                $badge
                    .removeClass('badge-light-success badge-light-danger')
                    .addClass(st === 1 ? 'badge-light-success' : 'badge-light-danger')
                    .text(st === 1 ? 'Evet' : 'Hayır');
            };

            $sw.data('busy', true).prop('disabled', true);

            $.ajax({
                url: '<?= site_url('categories/changePopular') ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    category_id: categoryId,
                    category_is_popular: newStatus
                },
                success: function(res) {
                    if (res && res.status === 'success') {
                        if (typeof toastr !== 'undefined') toastr.success(res.message || 'Güncellendi');
                        setBadge(newStatus);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(res.message || 'Hata');
                        $sw.prop('checked', !newStatus);
                    }
                },
                error: function() {
                    if (typeof toastr !== 'undefined') toastr.error('Bağlantı hatası');
                    $sw.prop('checked', !newStatus);
                },
                complete: function() {
                    $sw.data('busy', false).prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.js-category-delete', function() {
            const $btn = $(this);
            const categoryId = $btn.data('category-id');
            const title = $btn.data('category-title') || ('#' + categoryId);

            if ($btn.data('busy')) return;

            const doDelete = function(force) {
                $btn.data('busy', true).prop('disabled', true);

                $.ajax({
                    url: '<?= site_url('categories/delete/') ?>' + categoryId,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        category_id: categoryId,
                        force: force ? 1 : 0
                    },
                    success: function(res) {
                        if (res && res.status === 'success') {
                            if (typeof toastr !== 'undefined') toastr.success(res.message || 'Güncellendi');

                            const $tr = $btn.closest('tr');
                            const table = $('#categoryTable').DataTable();
                            table.row($tr).remove().draw(false);

                        } else {
                            // bağlı ürün varsa artık "Silinemez" değil, onay iste
                            if (res && res.code === 'HAS_ACTIVE_PRODUCTS' && typeof Swal !== 'undefined') {
                                const items = (res.data && res.data.items) ? res.data.items : [];
                                const total = (res.data && res.data.total) ? res.data.total : items.length;
                                const shown = (res.data && res.data.shown) ? res.data.shown : items.length;

                                let listHtml = '';
                                if (items.length) {
                                    listHtml += '<div style="text-align:left;max-height:260px;overflow:auto;border:1px solid rgba(0,0,0,.08);border-radius:10px;padding:10px;">';
                                    listHtml += '<ul style="margin:0;padding-left:18px;">';
                                    items.forEach(function(p) {
                                        const name = (p.product_name || '').toString()
                                            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                                            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                                        listHtml += `<li style="margin:6px 0;">
                                                <strong>#${p.product_id}</strong> - ${name}
                                            </li>`;
                                    });
                                    listHtml += '</ul>';
                                    listHtml += '</div>';

                                    if (total > shown) {
                                        listHtml += `<div class="text-muted mt-3" style="font-size:12px;text-align:left;">
                                            +${total - shown} ürün daha var.
                                            </div>`;
                                    }
                                }

                                Swal.fire({
                                    title: 'Bağlı Ürünler Var',
                                    html: `
                                <div style="text-align:left;">
                                    <div><b>${title}</b> kategorisine bağlı aktif ürünler var.</div>
                                    <div class="mt-2">Yine de silmek ister misiniz?</div>
                                    <div class="mt-3"><strong>Aktif Ürünler:</strong></div>
                                    ${listHtml || '<div class="text-muted mt-2">Liste alınamadı.</div>'}
                                </div>
                            `,
                                    icon: 'warning',
                                    width: 650,
                                    showCancelButton: true,
                                    confirmButtonText: 'Evet, Sil',
                                    cancelButtonText: 'İptal',
                                    buttonsStyling: false,
                                    customClass: {
                                        confirmButton: 'btn btn-danger',
                                        cancelButton: 'btn btn-light'
                                    }
                                }).then((r) => {
                                    if (r.isConfirmed) {
                                        doDelete(true);
                                    } else {
                                        $btn.prop('disabled', false).data('busy', false);
                                    }
                                });

                            } else {
                                if (typeof toastr !== 'undefined') toastr.error((res && res.message) ? res.message : 'Silinemedi');
                                $btn.prop('disabled', false).data('busy', false);
                            }
                        }
                    },
                    error: function() {
                        if (typeof toastr !== 'undefined') toastr.error('Sunucu hatası. Tekrar deneyin.');
                        $btn.prop('disabled', false).data('busy', false);
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Emin misin?',
                    html: '<b>' + title + '</b> kategorisi silinecek. Bu işlem geri alınamaz.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, Sil',
                    cancelButtonText: 'İptal',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-light'
                    }
                }).then((r) => {
                    if (r.isConfirmed) doDelete(false);
                });
            } else {
                if (confirm(title + ' silinsin mi?')) doDelete(false);
            }
        });
    });
</script>