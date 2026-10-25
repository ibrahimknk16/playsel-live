<style>
    .custom-quill {
        height: 170px;
    }

    .ql-toolbar.ql-snow {
        border-radius: 10px 10px 0 0;
    }

    .ql-container.ql-snow {
        border-radius: 0 0 10px 10px;
    }

    .image-input .image-input-wrapper {
        background-size: contain !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
    }

    /* Dinamik Form Kartları */
    .req-fields-title {
        font-weight: 700;
        font-size: 14px;
        color: #1f2937;
    }

    .req-field-card {
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .req-field-card .card-header {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(0, 0, 0, .05);
    }

    .req-field-card .card-body {
        padding: 16px;
    }

    .req-field-remove {
        width: 34px;
        height: 34px;
    }

    /* ===================================================
   CKEDITOR 5 - METRONIC 8 DARK MODE + WHITE EDITABLE
   =================================================== */

    /* Toolbar ve Genel Arka Plan (Dark) */
    .ck.ck-toolbar {
        background: #1b1b29 !important;
        border-color: #323248 !important;
        border-bottom: 1px solid #323248 !important;
        border-radius: 0.475rem 0.475rem 0 0 !important;
    }

    /* Toolbar İkonları ve Metinleri */
    .ck-reset_all,
    .ck-reset_all * {
        color: #cdcdde !important;
    }

    /* Toolbar Buton Hover & Aktiflik Durumları */
    .ck.ck-button {
        color: #cdcdde !important;
        cursor: pointer !important;
    }

    .ck.ck-button:hover,
    .ck.ck-button.ck-on {
        background: #2b2b40 !important;
        color: #ffffff !important;
    }

    /* Dropdown Menüler & Popoverlar (Dark) */
    .ck.ck-dropdown__panel,
    .ck.ck-list,
    .ck.ck-responsive-form {
        background: #1e1e2d !important;
        border-color: #323248 !important;
    }

    .ck.ck-list__item .ck-button:hover:not(.ck-disabled) {
        background: #2b2b40 !important;
    }

    /* Input Alanları (Link ekleme popup vs.) */
    .ck.ck-input-text {
        background-color: #151521 !important;
        border-color: #323248 !important;
        color: #ffffff !important;
    }

    /* ===================================================
   EDITABLE (TEXTAREA) ALANI - BEYAZ ARKA PLAN
   =================================================== */
    .ck.ck-editor__main>.ck-editor__editable {
        background-color: #ffffff !important;
        color: #212529 !important;
        /* Koyu metin rengi */
        border-color: #323248 !important;
        min-height: 250px !important;
        border-radius: 0 0 0.475rem 0.475rem !important;
    }

    /* Textarea Odaklandığında (Focus) Border */
    .ck.ck-editor__main>.ck-editor__editable:focus {
        border-color: #009ef7 !important;
        /* Metronic Primary Blue */
        box-shadow: 0 0 0 0.25rem rgba(0, 158, 247, 0.25) !important;
    }

    /* Textarea İçindeki Yazı Rengini ve Linkleri Sıfırlama */
    .ck.ck-editor__main>.ck-editor__editable * {
        color: initial;
    }

    /* Powered by CKEditor Logosunu Gizleme */
    .ck.ck-powered-by-balloon {
        display: none !important;
    }

    /* Source Editing (HTML Modu) Textarea (Dark Kalsın) */
    .ck.ck-source-editing-area textarea {
        background-color: #0f0f18 !important;
        color: #50cd89 !important;
        /* HTML kodları için yeşil ton */
        font-family: monospace !important;
    }
</style>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">

        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        <?= html_escape($category->category_title) ?> • Kategori Detay
                    </h1>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="<?= site_url('categories') ?>" class="btn btn-light">
                        <i class="ki-duotone ki-arrow-left fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Geri
                    </a>
                    <button type="submit" form="kt_ecommerce_edit_category_form" class="btn btn-primary" id="btnCategorySaveTop">
                        <span class="indicator-label"><i class="ki-duotone ki-check fs-5 me-1"></i> Kaydet</span>
                        <span class="indicator-progress" style="display:none;">Kaydediliyor... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </div>
        </div>

        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-5 px-5 px-lg-15" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#tab_general" aria-selected="true" role="tab">Genel Ayarlar</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#tab_subcategories" aria-selected="false" role="tab">Alt Kategoriler</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#tab_form_settings" aria-selected="false" role="tab">Form Ayarları</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#tab_seo_settings" aria-selected="false" role="tab">SEO Ayarları</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#tab_fraud_settings" aria-selected="false" role="tab">Fraud Ayarları</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#tab_content_faq" aria-selected="false" role="tab">SSS ve İçerik Ayarları</a>
            </li>
        </ul>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success d-flex align-items-center p-5 mb-6">
                        <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                        <span class="fw-bold"><?= $this->session->flashdata('success'); ?></span>
                        <button type="button" class="btn btn-icon btn-sm btn-active-light ms-auto" data-bs-dismiss="alert"><i class="ki-duotone ki-cross fs-2"></i></button>
                    </div>
                <?php endif; ?>

                <form id="kt_ecommerce_edit_category_form" enctype="multipart/form-data" class="form d-flex flex-column flex-lg-row fv-plugins-bootstrap5 fv-plugins-framework" method="post" action="<?= site_url('categories/update') ?>">
                    <input type="hidden" name="category_id" value="<?= (int)$category->category_id ?>">

                    <div id="leftSidebar" class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">

                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Ana Resim</h2>
                                </div>
                            </div>
                            <div class="card-body text-center pt-0">
                                <?php $imageUrl = getenv('CDN_URL') . 'uploads/categories/' . $category->category_image; ?>
                                <div class="image-input image-input-outline mb-3" data-kt-image-input="true" style="background-image: url('<?= html_escape($imageUrl) ?>')">
                                    <div class="image-input-wrapper w-150 h-150px" style="background-image: url('<?= html_escape($imageUrl) ?>')"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Değiştir">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="category_image" accept=".png, .jpg, .jpeg, .webp">
                                        <input type="hidden" name="avatar_remove">
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="İptal">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="text-muted fs-7">Öneri: 1200x630px</div>
                            </div>
                        </div>

                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Thumbnail</h2>
                                </div>
                            </div>
                            <div class="card-body text-center pt-0">
                                <?php $thumbUrl = getenv('CDN_URL') . 'uploads/categories/' . $category->category_thumbnail; ?>
                                <div class="image-input image-input-outline mb-3" data-kt-image-input="true" style="background-image: url('<?= html_escape($thumbUrl) ?>')">
                                    <div class="image-input-wrapper w-150px h-150px" style="background-image: url('<?= html_escape($thumbUrl) ?>')"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Değiştir">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="category_thumbnail" accept=".png, .jpg, .jpeg, .webp">
                                        <input type="hidden" name="avatar_remove">
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="İptal">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="text-muted fs-7">Öneri: 100x100px. Sadece *.png, *.jpg, *.jpeg, *.webp</div>
                            </div>
                        </div>



                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Görünürlük</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0 d-flex flex-column gap-5">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold">Durum</div>
                                        <div class="text-muted fs-7">Sitede görünsün mü?</div>
                                    </div>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" id="swCategoryStatus" <?= ((int)$category->category_status === 1) ? 'checked' : '' ?>>
                                        <input type="hidden" name="category_status" id="category_status" value="<?= ((int)$category->category_status === 1) ? 1 : 0 ?>">
                                    </div>
                                </div>
                                <div class="separator separator-dashed"></div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold">TopupCenter Durum</div>
                                        <div class="text-muted fs-7">Açık/Kapalı</div>
                                    </div>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" id="swTopupStatus" <?= ((int)$category->category_topupcenter_status === 1) ? 'checked' : '' ?>>
                                        <input type="hidden" name="category_topupcenter_status" id="category_topupcenter_status" value="<?= ((int)$category->category_topupcenter_status === 1) ? 1 : 0 ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">

                        <div class="tab-content">
                            <div class="tab-pane fade" id="tab_general" role="tab-panel">
                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Kategori Bilgileri</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="row g-6">
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold">Kategori Adı</label>
                                                <input type="text" name="category_title" class="form-control form-control-solid" value="<?= html_escape($category->category_title) ?>">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold">Slug</label>
                                                <input type="text" name="category_slug" class="form-control form-control-solid" value="<?= html_escape($category->category_slug) ?>">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold">iOS App Url</label>
                                                <input type="text" name="category_ios_app_url" class="form-control form-control-solid" value="<?= html_escape($category->category_ios_app_url) ?>">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold">Android App Url</label>
                                                <input type="text" name="category_android_app_url" class="form-control form-control-solid" value="<?= html_escape($category->category_android_app_url) ?>">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold">Yayıncı Firma</label>
                                                <input type="text" name="category_publisher" class="form-control form-control-solid" value="<?= html_escape($category->category_publisher) ?>">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-bold mb-2">Aktif Olan Ülkeler</label>
                                                <select class="form-select form-select-solid locales" name="locales[]" multiple="multiple">
                                                    <?php foreach ($locales as $locale): ?>
                                                        <option value="<?= html_escape($locale->locale_code) ?>" <?= in_array($locale->locale_code, $selectedCountries) ? 'selected' : '' ?>>
                                                            <?= html_escape($locale->locale_country) ?> (<?= html_escape($locale->locale_code) ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-bold mb-2">Kategori Tipi</label>
                                                <select class="form-select form-select-solid" name="category_type">
                                                    <option value="2" <?= $category->category_type === '2' ? 'selected' : '' ?>>Topup</option>
                                                    <option value="3" <?= $category->category_type === '3' ? 'selected' : '' ?>>Epin</option>
                                                    <option value="4" <?= $category->category_type === '4' ? 'selected' : '' ?>>CD Key</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab_subcategories" role="tab-panel">
                                <div class="card card-flush py-4">
                                    <div class="card-header align-items-center">
                                        <div class="card-title">
                                            <h2>Bağlı Alt Kategoriler</h2>
                                        </div>
                                        <div class="card-toolbar">
                                            <button type="button" class="btn btn-sm btn-light-info me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_link_subcategory">
                                                <i class="ki-duotone ki-link fs-4 me-1"></i> Mevcut Kategori Bağla
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_subcategory">
                                                <i class="ki-duotone ki-plus fs-4 me-1"></i> Yeni Alt Kategori
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                                <thead>
                                                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                                        <th>Görsel</th>
                                                        <th>Kategori Adı</th>
                                                        <th>Slug</th>
                                                        <th>Durum</th>
                                                        <th class="text-end">İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="fw-semibold text-gray-600">
                                                    <?php if (!empty($subCategories)): ?>
                                                        <?php foreach ($subCategories as $sub): ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="symbol symbol-50px">
                                                                        <?php if (!empty($sub->category_image)): ?>
                                                                            <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $sub->category_image ?>" alt="">
                                                                        <?php else: ?>
                                                                            <span class="symbol-label bg-light-primary text-primary fs-4"><?= mb_substr($sub->category_title, 0, 1) ?></span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </td>
                                                                <td class="text-gray-800 fw-bolder"><?= html_escape($sub->category_title) ?></td>
                                                                <td><?= html_escape($sub->category_slug) ?></td>
                                                                <td>
                                                                    <?php if ($sub->category_status == 1): ?>
                                                                        <span class="badge badge-light-success">Aktif</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-light-danger">Pasif</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-end">
                                                                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm edit-subcat-btn me-1"
                                                                        data-id="<?= $sub->category_id ?>"
                                                                        data-title="<?= html_escape($sub->category_title) ?>"
                                                                        data-status="<?= $sub->category_status ?>"
                                                                        title="Düzenle">
                                                                        <i class="fa-solid fa-pen fs-5"></i>
                                                                    </button>

                                                                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm unlink-subcat-btn"
                                                                        data-id="<?= $sub->category_id ?>"
                                                                        data-title="<?= html_escape($sub->category_title) ?>"
                                                                        title="Bağlantıyı Kopar (Ana Kategori Yap)">
                                                                        <i class="fa-solid fa-link-slash fs-5"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-10">Henüz alt kategori eklenmemiş.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab_form_settings" role="tab-panel">
                                <div class="card card-flush py-4">
                                    <div class="card-header align-items-center">
                                        <div class="card-title">
                                            <h2>Satış Formu İstekleri</h2>
                                        </div>
                                        <div class="card-toolbar">
                                            <button type="button" class="btn btn-sm btn-light-primary" id="btnAddRequiredField">
                                                <i class="ki-duotone ki-plus fs-4 me-1"></i> Yeni Alan Ekle
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="text-muted fs-7 mb-6">Müşteri sipariş verirken hangi bilgilerin isteneceği ve bunların kurallarını buradan yönetebilirsiniz.</div>

                                        <div id="requiredFieldsWrap" class="req-grid">

                                            <?php
                                            $catReqs = !empty($category->category_requires) ? json_decode($category->category_requires, true) : [];
                                            $reqIndex = 0;
                                            if (!empty($catReqs)):
                                                foreach ($catReqs as $req):
                                                    $reqIndex++;
                                            ?>
                                                    <div class="card req-field-card card-flush">
                                                        <div class="card-header d-flex justify-content-between align-items-center bg-light-secondary">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="req-fields-title">Form Alanı</div>
                                                                <span class="badge badge-light-primary js-badge"><?= html_escape($req['inputName']) ?></span>
                                                            </div>
                                                            <button type="button" class="btn btn-icon btn-sm btn-light-danger req-field-remove js-remove">X</button>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row g-5">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Key (inputName)*</label>
                                                                    <input type="text" name="category_requires[<?= $reqIndex ?>][inputName]" class="form-control form-control-solid js-key" value="<?= html_escape($req['inputName']) ?>" readonly>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Başlık (Placeholder)</label>
                                                                    <input type="text" name="category_requires[<?= $reqIndex ?>][inputPlaceholder]" class="form-control form-control-solid" value="<?= html_escape($req['inputPlaceholder'] ?? '') ?>">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Veri Tipi</label>
                                                                    <select name="category_requires[<?= $reqIndex ?>][inputType]" class="form-select form-select-solid">
                                                                        <option value="text" <?= ($req['inputType'] == 'text') ? 'selected' : '' ?>>Metin (text)</option>
                                                                        <option value="number" <?= ($req['inputType'] == 'number') ? 'selected' : '' ?>>Sayı (number)</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Min Uzunluk</label>
                                                                    <input type="number" name="category_requires[<?= $reqIndex ?>][inputMinLength]" class="form-control form-control-solid" value="<?= html_escape($req['inputMinLength'] ?? '') ?>">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Max Uzunluk</label>
                                                                    <input type="number" name="category_requires[<?= $reqIndex ?>][inputMaxLength]" class="form-control form-control-solid" value="<?= html_escape($req['inputMaxLength'] ?? '') ?>">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Kurallar (Regex)</label>
                                                                    <div class="input-group">
                                                                        <input type="text" name="category_requires[<?= $reqIndex ?>][inputPattern]" class="form-control form-control-solid js-pat" placeholder="Serbest Giriş (Kural Yok)" value="<?= html_escape($req['inputPattern'] ?? '') ?>" readonly>
                                                                        <button type="button" class="btn btn-light-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            Şablon Seç
                                                                        </button>
                                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                                            <li><a class="dropdown-item js-regex-preset" href="javascript:;" data-regex="^[0-9]+$" data-err="Sadece rakam girebilirsiniz.">Sadece Rakam (ID)</a></li>
                                                                            <li><a class="dropdown-item js-regex-preset" href="javascript:;" data-regex="^[a-zA-ZğüşıöçĞÜŞİÖÇ\s]+$" data-err="Sadece harf girebilirsiniz.">Sadece Harf (İsim)</a></li>
                                                                            <li><a class="dropdown-item js-regex-preset" href="javascript:;" data-regex="^[a-zA-Z0-9]+$" data-err="Boşluk ve özel karakter kullanılamaz.">Harf ve Rakam (Boşluksuz)</a></li>
                                                                            <li><a class="dropdown-item js-regex-preset" href="javascript:;" data-regex="^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$" data-err="Geçerli bir e-posta adresi giriniz.">E-Posta Formatı</a></li>
                                                                            <li>
                                                                                <hr class="dropdown-divider">
                                                                            </li>
                                                                            <li><a class="dropdown-item js-regex-preset" href="javascript:;" data-regex="" data-err="">Temizle (Serbest Giriş)</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Kural İhlali Hata Mesajı</label>
                                                                    <input type="text" name="category_requires[<?= $reqIndex ?>][inputPatternError]" class="form-control form-control-solid js-err" placeholder="Serbest Giriş" value="<?= html_escape($req['inputPatternError'] ?? '') ?>" readonly>
                                                                </div>
                                                                <div class="col-12 mt-4">
                                                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                                                        <input class="form-check-input" type="checkbox" name="category_requires[<?= $reqIndex ?>][inputRequired]" value="1" <?= (!empty($req['inputRequired']) && $req['inputRequired']) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label fw-semibold">Bu alanın doldurulması zorunlu olsun mu?</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                            <?php
                                                endforeach;
                                            endif;
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sunucu Yönetimi Kartı (Sadece server_id form kuralı eklendiğinde görünür) -->
                                <div class="card card-flush py-4 mt-7" id="serverManagementCard" style="display: none;">
                                    <div class="card-header align-items-center bg-light-primary">
                                        <div class="card-title">
                                            <h2 class="text-primary mb-0">Sunucu Listesi (Toplu Yönetim)</h2>
                                        </div>
                                        <div class="card-toolbar">
                                            <span class="badge badge-primary total-server-count fs-6">0 Sunucu</span>
                                        </div>
                                    </div>
                                    <div class="card-body pt-5">
                                        <div class="alert alert-dismissible bg-light-info border border-info border-dashed d-flex flex-column flex-sm-row w-100 p-5 mb-5">
                                            <i class="ki-duotone ki-information fs-2hx text-info me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            <div class="d-flex flex-column pe-0 pb-2">
                                                <h5 class="mb-1">Nasıl Eklenir?</h5>
                                                <span>Her satıra bir sunucu gelecek şekilde <code>Sunucu Adı : Sunucu ID</code> formatında yazın. Araya <strong>iki nokta üst üste ( : )</strong> koymayı unutmayın. Kopyala-yapıştır ile 500+ sunucuyu tek seferde ekleyebilirsiniz.</span>
                                            </div>
                                        </div>

                                        <?php
                                        $bulkText = "";
                                        if (!empty($serverList)) {
                                            foreach ($serverList as $s) {
                                                // Ekrana alt alta: Asia Server : 101 şeklinde bas
                                                $bulkText .= html_escape($s['title']) . " : " . html_escape($s['value']) . "\n";
                                            }
                                        }
                                        ?>
                                        <textarea name="servers_bulk" id="servers_bulk" class="form-control form-control-solid" rows="15" placeholder="Örn:&#10;Asia Server : 101&#10;Europe Server : 102" style="font-family: monospace; font-size: 14px; line-height: 1.6; white-space: pre; border: 1px solid #e4e6ef;"><?= $bulkText ?></textarea>
                                    </div>
                                </div>

                            </div>
                            <!-- CKEditor 5 CSS ve UMD Bundle (Lisans Zorunluluğu Olmayan Açık Kaynak Dağıtım) -->
                            <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css">
                            <script src="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.umd.js"></script>
                            <script src="https://cdn.ckeditor.com/ckeditor5/43.3.1/translations/tr.js"></script>

                            <style>
                                /* Editör Yükseklik ve Z-Index Düzeltmeleri */
                                .ck-editor__editable_inline {
                                    min-height: 250px !important;
                                }

                                .ck-body-wrapper {
                                    z-index: 99999 !important;
                                }
                            </style>
                            <div class="tab-pane fade" id="tab_seo_settings" role="tab-panel">
                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>SEO ve Dil Ayarları</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-6" role="tablist">
                                            <?php foreach ($langs as $index => $locale): ?>
                                                <li class="nav-item">
                                                    <a class="nav-link text-active-primary pb-3 <?= $index === 0 ? 'active' : '' ?>" data-bs-toggle="tab" href="#seo_lang_<?= html_escape($locale) ?>"><?= tr_strtoupper($locale) ?></a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>

                                        <div class="tab-content">
                                            <?php foreach ($langs as $index => $locale):
                                                $code = $locale;
                                                $langData = isset($categoryLangData[$code]) ? $categoryLangData[$code] : [];
                                            ?>
                                                <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="seo_lang_<?= html_escape($locale) ?>">
                                                    <div class="row g-6">
                                                        <div class="col-12 col-lg-6">
                                                            <div class="mb-4">
                                                                <label class="form-label fw-semibold">Sayfa Başlığı</label>
                                                                <input type="text" class="form-control form-control-solid" name="category_lang[<?= html_escape($code) ?>][meta_title]" value="<?= htmlspecialchars($langData['meta_title'] ?? '', ENT_QUOTES) ?>">
                                                            </div>
                                                            <div class="mb-4">
                                                                <label class="form-label fw-semibold">Meta Description</label>
                                                                <input type="text" class="form-control form-control-solid" name="category_lang[<?= html_escape($code) ?>][meta_desc]" value="<?= htmlspecialchars($langData['meta_desc'] ?? '', ENT_QUOTES) ?>">
                                                            </div>
                                                            <div class="mb-4">
                                                                <label class="form-label fw-semibold">Meta Keywords</label>
                                                                <input type="text" name="category_lang[<?= html_escape($code) ?>][meta_keywords]" class="form-control form-control-solid" value="<?= htmlspecialchars($langData['meta_keywords'] ?? '', ENT_QUOTES) ?>">
                                                            </div>
                                                            <div class="mb-4">
                                                                <label class="form-label fw-semibold">H1 Etiketi</label>
                                                                <input type="text" name="category_lang[<?= html_escape($code) ?>][h1_tag]" class="form-control form-control-solid" value="<?= htmlspecialchars($langData['h1_tag'] ?? '', ENT_QUOTES) ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-lg-6">
                                                            <!-- Kategori Açıklaması -->
                                                            <div class="mb-6">
                                                                <label class="form-label fw-semibold">Kategori Açıklaması</label>
                                                                <div class="py-2" data-bs-theme="light">
                                                                    <textarea name="category_lang[<?= html_escape($code) ?>][description]"
                                                                        id="editor_description_<?= html_escape($code) ?>"
                                                                        class="custom-ckeditor"><?= $langData['description'] ?? '' ?></textarea>
                                                                </div>
                                                            </div>

                                                            <!-- Kategori Nasıl Yüklenir? -->
                                                            <div class="mb-0">
                                                                <label class="form-label fw-semibold">Kategori Nasıl Yüklenir?</label>
                                                                <div class="py-2" data-bs-theme="light">
                                                                    <textarea name="category_lang[<?= html_escape($code) ?>][how_to_install]"
                                                                        id="editor_how_to_install_<?= html_escape($code) ?>"
                                                                        class="custom-ckeditor"><?= $langData['how_to_install'] ?? '' ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    // 1. CKEDITOR Global Nesnesinden Gerekli Modülleri Çekiyoruz
                                    const {
                                        ClassicEditor,
                                        Essentials,
                                        Autoformat,
                                        Bold,
                                        Italic,
                                        Underline,
                                        Strikethrough,
                                        Code,
                                        Subscript,
                                        Superscript,
                                        RemoveFormat,
                                        Font,
                                        Paragraph,
                                        Heading,
                                        Alignment,
                                        Indent,
                                        IndentBlock,
                                        List,
                                        TodoList,
                                        Link,
                                        Image,
                                        ImageToolbar,
                                        ImageCaption,
                                        ImageStyle,
                                        ImageResize,
                                        LinkImage,
                                        Table,
                                        TableToolbar,
                                        TableProperties,
                                        TableCellProperties,
                                        BlockQuote,
                                        CodeBlock,
                                        HorizontalLine,
                                        MediaEmbed,
                                        SourceEditing,
                                        GeneralHtmlSupport,
                                        Undo
                                    } = CKEDITOR;

                                    // 2. Sayfadaki Tüm .custom-ckeditor Textarea'larını Başlatalım
                                    document.querySelectorAll('.custom-ckeditor').forEach(function(textarea) {
                                        ClassicEditor
                                            .create(textarea, {
                                                language: 'tr', // Türkçe Dil Desteği

                                                plugins: [
                                                    Essentials, Autoformat, Bold, Italic, Underline, Strikethrough, Code, Subscript, Superscript, RemoveFormat,
                                                    Font, Paragraph, Heading, Alignment, Indent, IndentBlock, List, TodoList,
                                                    Link, Image, ImageToolbar, ImageCaption, ImageStyle, ImageResize, LinkImage,
                                                    Table, TableToolbar, TableProperties, TableCellProperties,
                                                    BlockQuote, CodeBlock, HorizontalLine, MediaEmbed, SourceEditing, GeneralHtmlSupport, Undo
                                                ],

                                                toolbar: {
                                                    items: [
                                                        'sourceEditing', '|',
                                                        'heading', '|',
                                                        'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                                                        'bold', 'italic', 'underline', 'strikethrough', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                                                        'alignment', 'outdent', 'indent', '|',
                                                        'bulletedList', 'numberedList', 'todoList', '|',
                                                        'link', 'insertTable', 'codeBlock', 'blockQuote', 'horizontalLine', 'mediaEmbed', '|',
                                                        'undo', 'redo'
                                                    ],
                                                    shouldNotGroupWhenFull: true
                                                },

                                                // Ham HTML ve Özel Class/Style Koruma
                                                htmlSupport: {
                                                    allow: [{
                                                        name: /.*/,
                                                        attributes: true,
                                                        classes: true,
                                                        styles: true
                                                    }]
                                                },

                                                heading: {
                                                    options: [{
                                                            model: 'paragraph',
                                                            title: 'Paragraf',
                                                            class: 'ck-heading_paragraph'
                                                        },
                                                        {
                                                            model: 'heading1',
                                                            view: 'h1',
                                                            title: 'Başlık 1',
                                                            class: 'ck-heading_heading1'
                                                        },
                                                        {
                                                            model: 'heading2',
                                                            view: 'h2',
                                                            title: 'Başlık 2',
                                                            class: 'ck-heading_heading2'
                                                        },
                                                        {
                                                            model: 'heading3',
                                                            view: 'h3',
                                                            title: 'Başlık 3',
                                                            class: 'ck-heading_heading3'
                                                        }
                                                    ]
                                                },

                                                image: {
                                                    toolbar: [
                                                        'imageTextAlternative', 'toggleImageCaption', '|',
                                                        'imageStyle:inline', 'imageStyle:block', 'imageStyle:side', '|',
                                                        'linkImage'
                                                    ]
                                                },

                                                table: {
                                                    contentToolbar: [
                                                        'tableColumn', 'tableRow', 'mergeTableCells',
                                                        'tableProperties', 'tableCellProperties'
                                                    ]
                                                }
                                            })
                                            .then(editor => {
                                                // Form POST edildiğinde veya AJAX atıldığında Textarea'yı Anlık Güncelle
                                                editor.model.document.on('change:data', () => {
                                                    editor.updateSourceElement();
                                                });
                                            })
                                            .catch(error => {
                                                console.error('CKEditor başlatılamadı:', error);
                                            });
                                    });
                                });
                            </script>

                            <div class="tab-pane fade" id="tab_fraud_settings" role="tab-panel">
                                <div class="card card-flush py-4">
                                    <div class="card-header align-items-center">
                                        <div class="card-title">
                                            <h2>Fraud Ayarları</h2>
                                        </div>
                                        <div class="card-toolbar">
                                            <button type="button" class="btn btn-sm btn-light-primary" id="btnAddRules">
                                                <i class="ki-duotone ki-plus fs-4 me-1"></i> Yeni Kural Ekle
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                                <thead>
                                                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                                        <th class="w-50px">Seç</th>
                                                        <th>Kural Adı</th>
                                                        <th>Tekil İşlem Limiti</th>
                                                        <th>Günlük Toplam Limit</th>
                                                        <th>Günlük Adet</th>
                                                        <th>Durum</th>
                                                        <th class="text-end">İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="fw-semibold text-gray-600">
                                                    <?php if (!empty($fraudRules)): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="form-check form-check-custom form-check-solid">
                                                                    <input class="form-check-input" type="radio" name="category_fraud_rule_id" value="0" <?= empty($category->category_fraud_rule_id) ? 'checked' : '' ?> />
                                                                </div>
                                                            </td>
                                                            <td colspan="6" class="text-muted fst-italic">Kural Yok (Devre Dışı)</td>
                                                        </tr>

                                                        <?php foreach ($fraudRules as $rule): ?>
                                                            <tr class="hover-elevate-up">
                                                                <td>
                                                                    <div class="form-check form-check-custom form-check-solid">
                                                                        <input class="form-check-input" type="radio" name="category_fraud_rule_id" value="<?= $rule->fraud_rule_id ?>" <?= ($category->category_fraud_rule_id == $rule->fraud_rule_id) ? 'checked' : '' ?> />
                                                                    </div>
                                                                </td>
                                                                <td class="text-gray-800 fw-bolder"><?= html_escape($rule->fraud_rule_name) ?></td>
                                                                <td><?= number_format($rule->fraud_rule_single_amount_limit, 2, ',', '.') ?> TL</td>
                                                                <td><?= number_format($rule->fraud_rule_daily_total_limit, 2, ',', '.') ?> TL</td>
                                                                <td><?= $rule->fraud_rule_daily_order_limit ?> Adet</td>
                                                                <td>
                                                                    <?php if ($rule->fraud_rule_status == 1): ?>
                                                                        <span class="badge badge-light-success">Aktif</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-light-danger">Pasif</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-end">
                                                                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm edit-fraud-btn"
                                                                        data-id="<?= $rule->fraud_rule_id ?>"
                                                                        data-name="<?= html_escape($rule->fraud_rule_name) ?>"
                                                                        data-single="<?= $rule->fraud_rule_single_amount_limit ?>"
                                                                        data-daily-total="<?= $rule->fraud_rule_daily_total_limit ?>"
                                                                        data-daily-limit="<?= $rule->fraud_rule_daily_order_limit ?>"
                                                                        data-status="<?= $rule->fraud_rule_status ?>">
                                                                        <i class="fa-solid fa-pen fs-5"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted py-10">Kayıtlı Fraud Kuralı Bulunmamaktadır.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END: Fraud Settings Tab -->

                            <!-- BEGIN: SSS ve İçerik Tab -->
                            <div class="tab-pane fade" id="tab_content_faq" role="tab-panel">
                                <div class="card card-flush py-4 mb-10">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Kategori Genel İçeriği (Content)</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <label class="form-label">Kategori Açıklaması / İçeriği</label>
                                        <textarea name="category_content" class="form-control form-control-solid" rows="5"><?= html_escape(isset($category->category_content) ? $category->category_content : '') ?></textarea>
                                        <div class="text-muted fs-7 mt-2">Bu alan SEO ve kullanıcı bilgilendirmesi için kategori detayında (genelde en alt kısımda) görünecektir. HTML kullanabilirsiniz.</div>
                                    </div>
                                </div>

                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Sıkça Sorulan Sorular (SSS)</h2>
                                        </div>
                                        <div class="card-toolbar">
                                            <button type="button" class="btn btn-sm btn-light-primary" id="add_faq_btn">
                                                <i class="ki-duotone ki-plus fs-2"></i> Yeni Soru Ekle
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0" id="faqs_container">
                                        <?php if (!empty($category_faqs)): ?>
                                            <?php foreach ($category_faqs as $index => $faq): ?>
                                                <div class="faq-item-card border border-dashed border-gray-300 rounded p-5 mb-5 position-relative">
                                                    <input type="hidden" name="faqs[<?= $index ?>][faq_id]" value="<?= $faq->faq_id ?>">
                                                    <button type="button" class="btn btn-icon btn-sm btn-light-danger position-absolute top-0 end-0 m-3 remove-faq-btn" title="Sil">
                                                        <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                    </button>
                                                    <div class="row">
                                                        <div class="col-md-9 mb-3">
                                                            <label class="form-label required">Soru</label>
                                                            <input type="text" name="faqs[<?= $index ?>][faq_question]" class="form-control mb-2 form-control-solid" value="<?= html_escape($faq->faq_question) ?>" required>
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label class="form-label">Sıra</label>
                                                            <input type="number" name="faqs[<?= $index ?>][faq_order]" class="form-control mb-2 form-control-solid" value="<?= $faq->faq_order ?>">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12 mb-3">
                                                            <label class="form-label required">Cevap</label>
                                                            <textarea name="faqs[<?= $index ?>][faq_answer]" class="form-control form-control-solid" rows="3" required><?= html_escape($faq->faq_answer) ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                                <input class="form-check-input" type="checkbox" name="faqs[<?= $index ?>][faq_status]" value="1" <?= $faq->faq_status == 1 ? 'checked' : '' ?> />
                                                                <label class="form-check-label">
                                                                    Aktif (Sitede görünsün)
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Boş şablon -->
                                    <template id="faq_template">
                                        <div class="faq-item-card border border-dashed border-gray-300 rounded p-5 mb-5 position-relative">
                                            <input type="hidden" name="faqs[{index}][faq_id]" value="0">
                                            <button type="button" class="btn btn-icon btn-sm btn-light-danger position-absolute top-0 end-0 m-3 remove-faq-btn" title="Sil">
                                                <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                            </button>
                                            <div class="row">
                                                <div class="col-md-9 mb-3">
                                                    <label class="form-label required">Soru</label>
                                                    <input type="text" name="faqs[{index}][faq_question]" class="form-control mb-2 form-control-solid" required>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Sıra</label>
                                                    <input type="number" name="faqs[{index}][faq_order]" class="form-control mb-2 form-control-solid" value="0">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label class="form-label required">Cevap</label>
                                                    <textarea name="faqs[{index}][faq_answer]" class="form-control form-control-solid" rows="3" required></textarea>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox" name="faqs[{index}][faq_status]" value="1" checked />
                                                        <label class="form-check-label">
                                                            Aktif (Sitede görünsün)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <a href="<?= site_url('categories') ?>" class="btn btn-light">Vazgeç</a>
                            <button type="submit" class="btn btn-primary" id="btnCategorySaveBottom">
                                <span class="indicator-label"><i class="ki-duotone ki-check fs-5 me-1"></i> Kaydet</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<template id="tplReqField">
    <div class="card req-field-card card-flush">
        <div class="card-header d-flex justify-content-between align-items-center bg-light-secondary">
            <div class="d-flex align-items-center gap-3">
                <div class="req-fields-title">Yeni Form Alanı</div>
                <span class="badge badge-light-primary js-badge">inputName</span>
            </div>
            <button type="button" class="btn btn-icon btn-sm btn-light-danger req-field-remove js-remove">X</button>
        </div>
        <div class="card-body">
            <div class="row g-5">
                <div class="col-md-6">
                    <label class="form-label">Key (inputName)*</label>
                    <select class="form-select form-select-solid js-key" required></select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Başlık (Placeholder)</label>
                    <input type="text" class="form-control form-control-solid js-ph">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Veri Tipi</label>
                    <select class="form-select form-select-solid js-type">
                        <option value="text">Metin (text)</option>
                        <option value="number">Sayı (number)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Min Uzunluk</label>
                    <input type="number" class="form-control form-control-solid js-min">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Max Uzunluk</label>
                    <input type="number" class="form-control form-control-solid js-max">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kurallar (Regex)</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-solid js-pat" placeholder="Serbest Giriş (Kural Yok)">
                        <button type="button" class="btn btn-light-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Şablon Seç
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item js-regex-preset" href="javascript:;" data-regex="^[0-9]+$" data-err="Sadece rakam girebilirsiniz.">Sadece Rakam (ID)</a></li>
                            <li><a class="dropdown-item js-regex-preset" href="javascript:;" data-regex="^[a-zA-ZğüşıöçĞÜŞİÖÇ\s]+$" data-err="Sadece harf girebilirsiniz.">Sadece Harf (İsim)</a></li>
                            <li><a class="dropdown-item js-regex-preset" href="javascript:;" data-regex="^[a-zA-Z0-9]+$" data-err="Boşluk ve özel karakter kullanılamaz.">Harf ve Rakam (Boşluksuz)</a></li>
                            <li><a class="dropdown-item js-regex-preset" href="javascript:;" data-regex="^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$" data-err="Geçerli bir e-posta adresi giriniz.">E-Posta Formatı</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item js-regex-preset" href="javascript:;" data-regex="" data-err="">Temizle (Serbest Giriş)</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Regex Hata Mesajı</label>
                    <input type="text" class="form-control form-control-solid js-err" placeholder="Serbest Giriş">
                </div>
                <div class="col-12 mt-4">
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input js-req" type="checkbox" value="1" checked>
                        <label class="form-check-label fw-semibold">Bu alanın doldurulması zorunlu olsun mu?</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<div class="modal fade" tabindex="-1" id="kt_modal_edit_fraud">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Fraud Kuralı Düzenle</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark fs-2"></i>
                </div>
            </div>

            <form id="form_edit_fraud" method="POST" action="<?= base_url('categories/updateFraudRule') ?>">
                <div class="modal-body">
                    <input type="hidden" name="fraud_rule_id" id="modal_fraud_id">

                    <div class="mb-5">
                        <label class="required form-label">Kural Adı</label>
                        <input type="text" class="form-control form-control-solid" name="fraud_rule_name" id="modal_fraud_name" required>
                    </div>

                    <div class="row g-5 mb-5">
                        <div class="col-md-6">
                            <label class="required form-label">Tekil İşlem Limiti (TL)</label>
                            <input type="number" step="0.01" class="form-control form-control-solid" name="fraud_rule_single_amount_limit" id="modal_fraud_single" required>
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label">Günlük Toplam Limit (TL)</label>
                            <input type="number" step="0.01" class="form-control form-control-solid" name="fraud_rule_daily_total_limit" id="modal_fraud_daily_total" required>
                        </div>
                    </div>

                    <div class="row g-5 mb-5">
                        <div class="col-md-6">
                            <label class="required form-label">Günlük Adet Limiti</label>
                            <input type="number" class="form-control form-control-solid" name="fraud_rule_daily_order_limit" id="modal_fraud_daily_limit" required>
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label">Durum</label>
                            <select class="form-select form-select-solid" name="fraud_rule_status" id="modal_fraud_status">
                                <option value="1">Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Yeni Fraud Kuralı Ekleme Modalı -->
<div class="modal fade" tabindex="-1" id="kt_modal_add_fraud">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Yeni Fraud Kuralı Ekle</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark fs-2"></i>
                </div>
            </div>

            <form id="form_add_fraud" method="POST" action="<?= site_url('categories/addFraudRule') ?>">
                <div class="modal-body">

                    <div class="mb-5">
                        <label class="required form-label">Kural Adı</label>
                        <input type="text" class="form-control form-control-solid" name="fraud_rule_name" id="add_fraud_name" placeholder="Örn: Günlük Max 5000 TL" required>
                    </div>

                    <div class="row g-5 mb-5">
                        <div class="col-md-6">
                            <label class="required form-label">Tekil İşlem Limiti (TL)</label>
                            <input type="number" step="0.01" class="form-control form-control-solid" name="fraud_rule_single_amount_limit" id="add_fraud_single" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label">Günlük Toplam Limit (TL)</label>
                            <input type="number" step="0.01" class="form-control form-control-solid" name="fraud_rule_daily_total_limit" id="add_fraud_daily_total" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="row g-5 mb-5">
                        <div class="col-md-6">
                            <label class="required form-label">Günlük Adet Limiti</label>
                            <input type="number" class="form-control form-control-solid" name="fraud_rule_daily_order_limit" id="add_fraud_daily_limit" placeholder="Örn: 5" required>
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label">Durum</label>
                            <select class="form-select form-select-solid" name="fraud_rule_status" id="add_fraud_status" data-control="select2" data-hide-search="true" data-dropdown-parent="#kt_modal_add_fraud">
                                <option value="1" selected>Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Kuralı Ekle</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- YENİ ALT KATEGORİ EKLE MODALI -->
<div class="modal fade" tabindex="-1" id="kt_modal_add_subcategory">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Yeni Alt Kategori Ekle</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark fs-2"></i>
                </div>
            </div>
            <form action="<?= site_url('categories/addSubCategory') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Bağlı Olduğu Ana Kategori ID'si -->
                    <input type="hidden" name="category_parent_category_id" value="<?= $category->category_id ?>">
                    <!-- Ana Kategori Tipi (Miras alması için) -->
                    <input type="hidden" name="category_type" value="<?= $category->category_type ?>">

                    <div class="mb-5">
                        <label class="required form-label">Alt Kategori Adı</label>
                        <input type="text" class="form-control form-control-solid" name="sub_category_title" placeholder="Örn: PUBG Mobile UC Türkiye" required>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Kategori Görseli (Opsiyonel)</label>
                        <input type="file" class="form-control form-control-solid" name="sub_category_image" accept=".png, .jpg, .jpeg, .webp">
                    </div>

                    <div class="mb-5">
                        <label class="required form-label">Durum</label>
                        <select class="form-select form-select-solid" name="sub_category_status">
                            <option value="1" selected>Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ALT KATEGORİ DÜZENLE MODALI -->
<div class="modal fade" tabindex="-1" id="kt_modal_edit_subcategory">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Alt Kategori Düzenle</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark fs-2"></i>
                </div>
            </div>
            <form action="<?= site_url('categories/updateSubCategory') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="sub_category_id" id="modal_subcat_id">
                    <input type="hidden" name="category_parent_category_id" value="<?= $category->category_id ?>">

                    <div class="mb-5">
                        <label class="required form-label">Alt Kategori Adı</label>
                        <input type="text" class="form-control form-control-solid" name="sub_category_title" id="modal_subcat_title" required>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Kategori Görseli (Değiştirmek isterseniz seçin)</label>
                        <input type="file" class="form-control form-control-solid" name="sub_category_image" accept=".png, .jpg, .jpeg, .webp">
                    </div>

                    <div class="mb-5">
                        <label class="required form-label">Durum</label>
                        <select class="form-select form-select-solid" name="sub_category_status" id="modal_subcat_status">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" id="kt_modal_link_subcategory">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Mevcut Ana Kategoriyi Bağla</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark fs-2"></i>
                </div>
            </div>
            <form action="<?= site_url('categories/linkSubCategory') ?>" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="category_parent_category_id" value="<?= $category->category_id ?>">

                    <div class="alert alert-info d-flex align-items-center p-4 mb-5">
                        <i class="ki-duotone ki-information fs-2hx text-info me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-column">
                            <span>Seçeceğiniz ana kategori, bu kategoriye alt kategori olarak bağlanacaktır.</span>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="required form-label">Bağlanacak Kategoriyi Seçin</label>
                        <select class="form-select form-select-solid js-link-category-select" name="linked_category_id" required>
                            <option value="">Kategori Seçiniz...</option>
                            <?php if (!empty($availableMainCategories)): ?>
                                <?php foreach ($availableMainCategories as $mainCat): ?>
                                    <option value="<?= $mainCat->category_id ?>"><?= html_escape($mainCat->category_title) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-info">Kategoriyi Bağla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="form_unlink_subcategory" action="<?= site_url('categories/unlinkSubCategory') ?>" method="POST" style="display: none;">
    <input type="hidden" name="sub_category_id" id="unlink_subcat_id">
    <input type="hidden" name="category_parent_category_id" value="<?= $category->category_id ?>">
</form>

<script>
    (function() {
        var hash = window.location.hash || '#tab_general';
        var validTabs = ['#tab_general', '#tab_subcategories', '#tab_form_settings', '#tab_seo_settings', '#tab_fraud_settings', '#tab_content_faq'];
        if (!validTabs.includes(hash)) hash = '#tab_general';

        var targetLink = document.querySelector('a[href="' + hash + '"]');
        var targetPane = document.querySelector(hash);
        var leftSidebar = document.getElementById('leftSidebar');

        if (targetLink && targetPane) {
            targetLink.classList.add('active');
            targetPane.classList.add('show', 'active');
        }
        if (leftSidebar) {
            if (hash === '#tab_general') {
                leftSidebar.classList.remove('d-none');
            } else {
                leftSidebar.classList.add('d-none');
            }
        }
    })();

    document.addEventListener("DOMContentLoaded", function() {

        var hash = window.location.hash || '#tab_general';
        var hashTab = document.querySelector('a[data-bs-toggle="tab"][href="' + hash + '"]');
        if (hashTab) {
            new bootstrap.Tab(hashTab).show();
        }

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            var currentHash = e.target.hash;

            if (currentHash === '#tab_general') {
                $('#leftSidebar').removeClass('d-none');
            } else {
                $('#leftSidebar').addClass('d-none');
            }

            if (history.replaceState) {
                history.replaceState(null, null, currentHash);
            } else {
                window.location.hash = currentHash;
            }
        });

        $('form').on('submit', function() {
            const activeTab = $('.nav-link.active').attr('href');
            if (activeTab) {
                this.action = this.action.split('#')[0] + activeTab;
            }
        });

        if (window.jQuery && $.fn.select2) {
            $('.locales').select2({
                placeholder: "Ülke seç...",
                width: '100%'
            });
        }

        const swCategoryStatus = document.getElementById('swCategoryStatus');
        const inCategoryStatus = document.getElementById('category_status');
        if (swCategoryStatus && inCategoryStatus) {
            swCategoryStatus.addEventListener('change', () => inCategoryStatus.value = swCategoryStatus.checked ? 1 : 0);
        }

        const swTopupStatus = document.getElementById('swTopupStatus');
        const inTopupStatus = document.getElementById('category_topupcenter_status');
        if (swTopupStatus && inTopupStatus) {
            swTopupStatus.addEventListener('change', () => inTopupStatus.value = swTopupStatus.checked ? 1 : 0);
        }




        const allowedKeys = [{
                value: 'user_id',
                label: 'Oyuncu ID (user_id)'
            },
            {
                value: 'server_id',
                label: 'Sunucu ID (server_id)'
            }
        ];

        function checkAddButtonState() {
            let usedKeys = [];
            $('.js-key').each(function() {
                if ($(this).val()) usedKeys.push($(this).val());
            });

            if (usedKeys.length >= allowedKeys.length) {
                $('#btnAddRequiredField').prop('disabled', true).addClass('disabled');
            } else {
                $('#btnAddRequiredField').prop('disabled', false).removeClass('disabled');
            }
        }

        function toggleServerCard() {
            let hasServer = false;
            $('.js-key').each(function() {
                if ($(this).val() === 'server_id') {
                    hasServer = true;
                }
            });

            if (hasServer) {
                $('#serverManagementCard').slideDown();
            } else {
                $('#serverManagementCard').slideUp();
            }
        }

        checkAddButtonState();
        toggleServerCard();

        $('#servers_bulk').on('input', function() {
            let lines = $(this).val().split('\n').filter(line => line.trim() !== '' && line.includes(':'));
            $('.total-server-count').text(lines.length + ' Sunucu');
        });
        $('#servers_bulk').trigger('input');

        let reqIndex = <?= isset($reqIndex) ? $reqIndex : 0 ?>;

        $('#btnAddRequiredField').on('click', function() {
            let usedKeys = [];
            $('.js-key').each(function() {
                if ($(this).val()) usedKeys.push($(this).val());
            });

            const availableKeys = allowedKeys.filter(k => !usedKeys.includes(k.value));

            if (availableKeys.length === 0) return;

            reqIndex++;
            const tpl = document.getElementById('tplReqField');
            const $node = $(tpl.content.firstElementChild.cloneNode(true));

            const $keySelect = $node.find('.js-key');
            $keySelect.attr('name', `category_requires[${reqIndex}][inputName]`);

            availableKeys.forEach(k => {
                $keySelect.append(new Option(k.label, k.value));
            });

            $node.find('.js-badge').text($keySelect.val());
            $keySelect.on('change', function() {
                $node.find('.js-badge').text($(this).val());
                toggleServerCard();
            });

            $node.find('.js-ph').attr('name', `category_requires[${reqIndex}][inputPlaceholder]`);
            $node.find('.js-type').attr('name', `category_requires[${reqIndex}][inputType]`);
            $node.find('.js-min').attr('name', `category_requires[${reqIndex}][inputMinLength]`);
            $node.find('.js-max').attr('name', `category_requires[${reqIndex}][inputMaxLength]`);
            $node.find('.js-pat').attr('name', `category_requires[${reqIndex}][inputPattern]`);
            $node.find('.js-err').attr('name', `category_requires[${reqIndex}][inputPatternError]`);
            $node.find('.js-req').attr('name', `category_requires[${reqIndex}][inputRequired]`);

            $node.find('.js-remove').on('click', function() {
                $node.slideUp(300, function() {
                    $(this).remove();
                    checkAddButtonState();
                    toggleServerCard();
                });
            });

            $('#requiredFieldsWrap').append($node.hide().slideDown(300, function() {
                checkAddButtonState();
            }));
        });

        $(document).on('click', '.req-field-remove', function() {
            $(this).closest('.req-field-card').slideUp(300, function() {
                $(this).remove();
                checkAddButtonState();
                toggleServerCard();
            });
        });

        $(document).on('click', '.js-regex-preset', function(e) {
            e.preventDefault();

            const regexVal = $(this).data('regex');
            const errVal = $(this).data('err');

            const $card = $(this).closest('.row');

            $card.find('.js-pat').val(regexVal);
            $card.find('.js-err').val(errVal);
        });

        $('#btnAddRules').on('click', function() {
            $('#form_add_fraud')[0].reset();
            $('#add_fraud_status').val('1').trigger('change');

            const addModal = new bootstrap.Modal(document.getElementById('kt_modal_add_fraud'));
            addModal.show();
        });

        $('.edit-fraud-btn').on('click', function() {
            const btn = $(this);

            const id = btn.data('id');
            const name = btn.data('name');
            const singleLimit = btn.data('single');
            const dailyTotal = btn.data('daily-total');
            const dailyLimit = btn.data('daily-limit');
            const status = btn.data('status');

            $('#modal_fraud_id').val(id);
            $('#modal_fraud_name').val(name);
            $('#modal_fraud_single').val(singleLimit);
            $('#modal_fraud_daily_total').val(dailyTotal);
            $('#modal_fraud_daily_limit').val(dailyLimit);
            $('#modal_fraud_status').val(status);

            $('#modal_fraud_status').trigger('change');

            const editModal = new bootstrap.Modal(document.getElementById('kt_modal_edit_fraud'));
            editModal.show();
        });

        $('.edit-subcat-btn').on('click', function() {
            const btn = $(this);

            const id = btn.data('id');
            const title = btn.data('title');
            const status = btn.data('status');

            $('#modal_subcat_id').val(id);
            $('#modal_subcat_title').val(title);
            $('#modal_subcat_status').val(status);

            // Modalı aç
            const editModal = new bootstrap.Modal(document.getElementById('kt_modal_edit_subcategory'));
            editModal.show();
        });

        if (window.jQuery && $.fn.select2) {
            $('.js-link-category-select').select2({
                dropdownParent: $('#kt_modal_link_subcategory'),
                placeholder: "Bağlanacak kategoriyi arayın..."
            });
        }

        // Bağlantıyı Kopar (Unlink) Butonu İşlemi
        $('.unlink-subcat-btn').on('click', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');

            Swal.fire({
                title: 'Emin misiniz?',
                text: `"${title}" adlı kategorinin alt kategori bağlantısı koparılacak ve tek başına bir Ana Kategori olacaktır. İşleme devam edilsin mi?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f1416c',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Evet, Bağlantıyı Kopar',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#unlink_subcat_id').val(id);
                    $('#form_unlink_subcategory').submit();
                }
            });
        });
    $(document).ready(function() {
        // SSS Ekleme İşlemi
        let faqIndex = <?= !empty($category_faqs) ? count($category_faqs) : 0 ?>;
        $('#add_faq_btn').click(function() {
            let template = $('#faq_template').html();
            template = template.replace(/{index}/g, faqIndex);
            $('#faqs_container').append(template);
            faqIndex++;
        });

        // SSS Silme İşlemi
        $(document).on('click', '.remove-faq-btn', function() {
            $(this).closest('.faq-item-card').remove();
        });
    });
    });
</script>

<script src="assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js"></script>