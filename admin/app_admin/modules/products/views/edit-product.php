<style>
    .image-input .image-input-wrapper {
        background-size: contain !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
    }

    /* Form Ayarlari kartlari */
    .req-fields-title {
        font-weight: 700;
        font-size: 14px;
        color: #1f2937;
    }

    .req-field-card {
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 12px;
    }

    .req-field-card .card-header {
        padding: 14px 16px;
    }

    .req-field-card .card-body {
        padding: 16px;
    }

    .req-field-remove {
        width: 34px;
        height: 34px;
    }

    .req-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    @media (max-width: 992px) {
        .req-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">

        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        <?= html_escape($product->product_name) ?> Ürün Ayarları
                    </h1>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-5" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link text-active-primary pb-4 active"
                    data-bs-toggle="tab"
                    href="#tab_general"
                    aria-selected="true"
                    role="tab">
                    Genel Ayarlar
                </a>
            </li>

            <li class="nav-item" role="presentation">
                <a class="nav-link text-active-primary pb-4"
                    data-bs-toggle="tab"
                    href="#tab_form_settings"
                    aria-selected="false"
                    role="tab">
                    Form Ayarları
                </a>
            </li>

            <li class="nav-item" role="presentation">
                <a class="nav-link text-active-primary pb-4"
                    data-bs-toggle="tab"
                    href="#tab_api_settings"
                    aria-selected="false"
                    role="tab">
                    Tedarik API/Bundle Ayarları
                </a>
            </li>
        </ul>

        <div id="kt_app_content" class="app-content flex-column-fluid">
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

            <div id="kt_app_content_container" class="app-container">
                <form id="kt_ecommerce_add_product_form"
                    method="post"
                    enctype="multipart/form-data"
                    class="form d-flex flex-column flex-lg-row fv-plugins-bootstrap5 fv-plugins-framework"
                    action="<?= site_url('products/update') ?>">

                    <input type="hidden" name="product_id" id="product_id" value="<?= (int)$product->product_id ?>">

                    <!-- ============ LEFT SIDEBAR ============ -->
                    <div id="leftSidebar" class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">

                        <!-- Product Image -->
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Ürün Resmi</h2>
                                </div>
                            </div>
                            <div class="card-body text-center pt-0">
                                <style>
                                    .image-input-placeholder {
                                        background-image: url('<?= getenv('CDN_URL') . $product->product_image ?>');
                                    }

                                    [data-bs-theme="dark"] .image-input-placeholder {
                                        background-image: url('<?= getenv('CDN_URL') . $product->product_image ?>');
                                    }
                                </style>

                                <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-150px h-150px"></div>

                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Değiştir">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="product_image" accept=".png, .jpg, .jpeg, .webp">
                                        <input type="hidden" name="avatar_remove">
                                    </label>

                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="İptal">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>

                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Kaldır">
                                        <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    </span>
                                </div>

                                <div class="text-muted fs-7">Only *.png, *.jpg, *webp and *.jpeg image files are accepted</div>
                            </div>
                        </div>

                        <!-- Product Icon -->
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Ürün İkonu</h2>
                                </div>
                            </div>
                            <div class="card-body text-center pt-0">
                                <style>
                                    .icon-input-placeholder {
                                        background-image: url('<?= getenv('CDN_URL') . $product->product_icon ?>');
                                    }

                                    [data-bs-theme="dark"] .icon-input-placeholder {
                                        background-image: url('<?= getenv('CDN_URL') . $product->product_icon ?>');
                                    }
                                </style>

                                <div class="image-input image-input-empty image-input-outline icon-input-placeholder mb-3" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-150px h-150px"></div>

                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Değiştir">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="product_icon" accept=".png, .jpg, .jpeg, .webp">
                                        <input type="hidden" name="avatar_remove">
                                    </label>

                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="İptal">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>

                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Kaldır">
                                        <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    </span>
                                </div>

                                <div class="text-muted fs-7">Only *.png, *.jpg, *webp and *.jpeg image files are accepted</div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Status</h2>
                                </div>
                                <div class="card-toolbar">
                                    <div class="rounded-circle <?= $product->product_status =="1" ? "bg-success" : "bg-danger" ?> w-15px h-15px"></div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <select class="form-select mb-2 form-select-solid" name="product_status" data-control="select2" data-hide-search="true">
                                    <option value="1" <?= $product->product_status == "1" ? "selected" : "" ?>>Aktif</option>
                                    <option value="0" <?= $product->product_status == "0" ? "selected" : "" ?>>Pasif</option>
                                    <option value="0" <?= $product->product_status == "255" ? "selected" : "" ?>>Sil</option>
                                </select>
                                <div class="text-muted fs-7">Set the product status.</div>
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Product Details</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="mb-2">
                                    <div class="text-muted fs-7">Kategori</div>
                                    <div class="fw-bold"><?= html_escape($product->category_title) ?></div>
                                </div>

                                <?php if (!empty($product->user_name)): ?>
                                    <div class="mb-4">
                                        <div class="text-muted fs-7">Bağlı Supplier</div>
                                        <div class="fw-bold"><?= html_escape($product->user_name) ?></div>
                                    </div>
                                <?php endif; ?>

                                <a href="<?= base_url() ?>categories/edit/<?= (int)$product->category_id ?>"
                                    target="_blank"
                                    class="btn btn-light w-100">
                                    Kategoriye Git
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ============ RIGHT CONTENT ============ -->
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">



                        <div class="tab-content">

                            <!-- =========================================
                                 TAB: GENEL AYARLAR
                            ========================================== -->
                            <div class="tab-pane fade show active" id="tab_general" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">

                                    <!-- General -->
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>Genel</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="row g-5">
                                                <div class="col-md-6"> <label class="form-label">Ürün Adı</label>
                                                    <div class="mb-10 fv-row">
                                                        <input type="text"
                                                            name="product_name"
                                                            class="form-control form-control-solid"
                                                            placeholder="Product name"
                                                            value="<?= html_escape($product->product_name) ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Ürün Tipi</label>
                                                    <select class="form-select form-select-solid" name="product_type" data-control="select2" data-placeholder="Ürün Tipi">
                                                        <option value="2" <?= $product->product_type == "2" ? "selected" : "" ?>>Top-Up</option>
                                                        <option value="3" <?= $product->product_type == "3" ? "selected" : "" ?>>E-Pin</option>

                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Ana Kategori</label>
                                                    <select class="form-select form-select-solid" name="product_category_id" id="mainCategorySelect" data-control="select2" data-placeholder="Ana Kategori seç">
                                                        <?php foreach ($categories as $c): ?>
                                                            <option value="<?= (int)$c->category_id ?>" <?= ($product->product_category_id == $c->category_id) ? 'selected' : '' ?>>
                                                                <?= html_escape($c->category_title) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Alt Kategori (Opsiyonel)</label>
                                                    <select class="form-select form-select-solid" name="product_sub_category_id" id="subCategorySelect" data-control="select2" data-placeholder="Alt kategori seç">
                                                        <option value="0">Alt Kategori Yok (Temizle)</option>
                                                        <?php if (!empty($subCategories)): ?>
                                                            <?php foreach ($subCategories as $sub): ?>
                                                                <option value="<?= $sub->category_id ?>" <?= ($product->product_sub_category_id == $sub->category_id) ? 'selected' : '' ?>>
                                                                    <?= html_escape($sub->category_title) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Ürün Bundle mı?</label>
                                                    <select class="form-select form-select-solid" name="product_is_bundle" data-control="select2" data-placeholder="Ürün Bundle mı?">
                                                        <option <?php echo ($product->product_is_bundle == 0) ? 'selected' : ''; ?> value="0">Hayır</option>
                                                        <option <?php echo ($product->product_is_bundle == 1) ? 'selected' : ''; ?> value="1">Evet</option>
                                                    </select>
                                                </div>

                                            </div>
                                        </div>

                                    </div>

                                    <!-- <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>Ürün Fiyatları</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="mb-10 fv-row fv-plugins-icon-container">
                                                <div class="row">
                                                    <div class="mb-4 col-3">
                                                        <label class="form-label">Maliyet</label>
                                                        <input type="text" class="form-control form-control-solid" name="product_cost" value="<?= $product->provider_product_product_cost ?>">
                                                    </div>
                                                    <div class="mb-4 col-3">
                                                        <label class="form-label">Para Birimi</label>
                                                        <input type="text" class="form-control form-control-solid" value=<?= $product->provider_product_product_cost_currency ?> readonly>
                                                    </div>
                                                    <input type="hidden" class="form-control tl-cost form-control-solid" value="<?= nFormat(currencyCalculator($product->provider_product_product_cost_currency, $product->provider_product_product_cost) * 1.015) ?>" readonly>
                                                </div>
                                                <div class="row">
                                                    <span class="fs-4 border">Maliyet: <?= nFormat(currencyCalculator($product->provider_product_product_cost_currency, $product->provider_product_product_cost) * 1.02) ?> TL</span>
                                                </div>
                                                <div class="row" id="product-prices-form">
                                                    <input type="hidden" name="product_price_product_id" value="<?= $product->product_id ?>">
                                                    <input type="hidden" name="product_price_currency" value="TRY">
                                                    <input type="hidden" name="product_price_country" value="tr">
                                                    <div class="mb-4 col-3">
                                                        <label class="form-label">Standart Kullanıcı Fiyatı</label>
                                                        <input type="text" class="form-control form-control-solid" name="product_price_standart_price" value="<?= $product->product_price_standart_price ?>">
                                                        Kar: %<span class="oran text-success"></span>
                                                    </div>
                                                    <div class="mb-4 col-3">
                                                        <label class="form-label">Silver Kullanıcı Fiyatı</label>
                                                        <input type="text" class="form-control form-control-solid" name="product_price_silver_user" value="<?= $product->product_price_silver_user ?>">
                                                        Kar: % <span class="oran text-success"></span>
                                                    </div>
                                                    <div class="mb-4 col-3">
                                                        <label class="form-label">Gold Kullanıcı Fiyatı</label>
                                                        <input type="text" class="form-control form-control-solid" name="product_price_gold_user" value="<?= $product->product_price_gold_user ?>">
                                                        Kar: % <span class="oran text-success"></span>
                                                    </div>
                                                    <div class="mb-4 col-3">
                                                        <label class="form-label">VIP Kullanıcı Fiyatı</label>
                                                        <input type="text" class="form-control form-control-solid" name="product_price_vip_user" value="<?= $product->product_price_vip_user ?>">
                                                        Kar: % <span class="oran text-success"></span>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary w-100 mt-4 update-prices">Fiyatları Güncelle</button>
                                                </div>
                                            </div>
                                            <script>
                                                $(document).ready(function() {
                                                    const $tlCostInput = $('.tl-cost');
                                                    const tlCost = parseFloat($tlCostInput.val().replace(',', '.'));
                                                    const priceFields = [{
                                                            name: "product_price_standart_price"
                                                        },
                                                        {
                                                            name: "product_price_silver_user"
                                                        },
                                                        {
                                                            name: "product_price_gold_user"
                                                        },
                                                        {
                                                            name: "product_price_vip_user"
                                                        }
                                                    ];

                                                    function updateOranlar() {
                                                        priceFields.forEach((field, index) => {
                                                            const $input = $('input[name="' + field.name + '"]');
                                                            const $oranSpan = $input.closest('.mb-4').find('.oran');

                                                            let value = parseFloat($input.val().replace(',', '.'));
                                                            if (tlCost && !isNaN(value)) {
                                                                const oran = ((value - tlCost) / tlCost) * 100;
                                                                $oranSpan.text(oran.toFixed(2));
                                                            } else {
                                                                $oranSpan.text('');
                                                            }
                                                        });
                                                    }
                                                    updateOranlar();
                                                    priceFields.forEach((field) => {
                                                        $('input[name="' + field.name + '"]').on('input change', function() {
                                                            updateOranlar();
                                                        });
                                                    });

                                                    $('.update-prices').on('click', function(e) {
                                                        e.preventDefault();
                                                        const formData = $('#product-prices-form :input').serialize();
                                                        $.ajax({
                                                            url: '<?= base_url('products/updateProductPrices') ?>',
                                                            type: 'POST',
                                                            data: formData,
                                                            success: function(response) {
                                                                Swal.fire({
                                                                    text: "Fiyatlar başarıyla güncellendi.",
                                                                    icon: "success",
                                                                    buttonsStyling: false,
                                                                    confirmButtonText: "Tamam",
                                                                    customClass: {
                                                                        confirmButton: "btn btn-primary"
                                                                    }
                                                                });
                                                            },
                                                            error: function(xhr, status, error) {
                                                                Swal.fire({
                                                                    text: "Fiyatlar güncellenirken bir hata oluştu.",
                                                                    icon: "error",
                                                                    buttonsStyling: false,
                                                                    confirmButtonText: "Tamam",
                                                                    customClass: {
                                                                        confirmButton: "btn btn-primary"
                                                                    }
                                                                });
                                                            }
                                                        });
                                                    });
                                                });
                                            </script>
                                        </div>
                                    </div> -->


                                    <!-- Aktif Olan Ülkeler -->
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>Aktif Olan Ülkeler</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="fv-row mt-4 mb-2">
                                                <select class="form-select form-select-solid locales" name="locales[]" multiple="multiple">
                                                    <?php foreach ($locales as $locale): ?>
                                                        <option value="<?= html_escape($locale->locale_code) ?>"
                                                            <?= in_array($locale->locale_code, $selectedCountries) ? 'selected' : '' ?>>
                                                            <?= html_escape($locale->locale_country) ?> (<?= html_escape($locale->locale_code) ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="text-muted fs-7">Birden fazla ülke seçebilirsin.</div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- =========================================
                                 TAB: FORM AYARLARI
                            ========================================== -->
                            <div class="tab-pane fade" id="tab_form_settings" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">

                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>Form Ayarları</h2>
                                            </div>
                                            <div class="card-toolbar">
                                                <!-- <button type="button" class="btn btn-sm btn-light-primary" id="btnAddRequiredField">
                                                    <i class="ki-duotone ki-plus fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                    Alan Ekle
                                                </button> -->
                                            </div>
                                        </div>

                                        <div class="card-body pt-0">

                                            <div id="requiredFieldsWrap" class="req-grid">

                                                <!-- USER ID CARD -->
                                                <div class="card req-field-card card-flush">
                                                    <div class="card-header border-0">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="req-fields-title">Gerekli Alan</div>
                                                            <span class="badge badge-light">user_id</span>
                                                        </div>

                                                        <div class="ms-auto d-flex align-items-center gap-3">
                                                            <div class="form-check form-switch form-check-custom form-check-solid m-0">
                                                                <input class="form-check-input"
                                                                    type="checkbox"
                                                                    name="requires[]"
                                                                    value="user_id"
                                                                    <?= ((int)$product->product_is_require_user_id === 1) ? 'checked' : '' ?>>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card-body">
                                                        <div class="mb-4">
                                                            <label class="form-label fw-semibold">Başlık</label>
                                                            <input type="text"
                                                                class="form-control form-control-solid"
                                                                name="product_form_fields[user_id][title]"
                                                                value="Lütfen oyuncu ID'nizi yazınız.">
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label fw-semibold">Tanımlayıcı</label>
                                                            <input type="text"
                                                                class="form-control form-control-solid"
                                                                name="product_form_fields[user_id][key]"
                                                                value="user_id">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- SERVER ID CARD -->
                                                <div class="card req-field-card card-flush">
                                                    <div class="card-header border-0">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="req-fields-title">Gerekli Alan</div>
                                                            <span class="badge badge-light">server_id</span>
                                                        </div>

                                                        <div class="ms-auto d-flex align-items-center gap-3">
                                                            <div class="form-check form-switch form-check-custom form-check-solid m-0">
                                                                <input class="form-check-input"
                                                                    type="checkbox"
                                                                    name="requires[]"
                                                                    value="server_id"
                                                                    <?= ((int)$product->product_is_require_server_id === 1) ? 'checked' : '' ?>>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card-body">
                                                        <div class="mb-4">
                                                            <label class="form-label fw-semibold">Başlık</label>
                                                            <input type="text"
                                                                class="form-control form-control-solid"
                                                                name="product_form_fields[server_id][title]"
                                                                value="Sunucu ID Yazınız.">
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label fw-semibold">Tanımlayıcı</label>
                                                            <input type="text"
                                                                class="form-control form-control-solid"
                                                                name="product_form_fields[server_id][key]"
                                                                value="server_id">
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>

                                            <!-- Template for adding extra fields (UI only) -->
                                            <template id="tplReqField">
                                                <div class="card req-field-card card-flush">
                                                    <div class="card-header border-0">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="req-fields-title">Gerekli Alan</div>
                                                            <span class="badge badge-light js-badge">custom</span>
                                                        </div>

                                                        <div class="ms-auto d-flex align-items-center gap-2">
                                                            <div class="form-check form-switch form-check-custom form-check-solid m-0">
                                                                <input class="form-check-input js-enabled" type="checkbox" checked>
                                                            </div>
                                                            <button type="button" class="btn btn-icon btn-sm btn-light-danger req-field-remove js-remove" title="Sil">
                                                                <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="card-body">
                                                        <div class="mb-4">
                                                            <label class="form-label fw-semibold">Başlık</label>
                                                            <input type="text" class="form-control form-control-solid js-title" placeholder="Başlık...">
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label fw-semibold">Tanımlayıcı</label>
                                                            <input type="text" class="form-control form-control-solid js-key" placeholder="ör: zone_id">
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

                            <div class="tab-pane fade" id="tab_api_settings" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <style>
                                        .cursor-move {
                                            cursor: move;
                                        }
                                    </style>


                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>API Bağlantısı</h2>
                                            </div>
                                        </div>

                                        <?php if ($product->product_is_bundle == 1): ?>
                                            <div class="card-body pt-0">
                                                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6 mb-7">
                                                    <i class="ki-duotone ki-element-11 fs-2tx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                                    <div class="d-flex flex-stack flex-grow-1">
                                                        <div class="fw-semibold">
                                                            <h4 class="text-gray-900 fw-bold">Bundle Ürün İçerik Yönetimi</h4>
                                                            <div class="fs-6 text-gray-700">Bu ürün bir paket (bundle) üründür. Lütfen aşağıdan pakete dahil olan alt ürünleri ekleyin. Her satır 1 adet ürünü temsil eder.</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div id="kt_bundle_products_repeater">
                                                    <div class="form-group">
                                                        <div id="bundle_items_container" class="d-flex flex-column gap-3">
                                                            <?php
                                                            $bundle_items = !empty($product->product_bundle_items) ? json_decode($product->product_bundle_items, true) : [];
                                                            if (!empty($bundle_items) && is_array($bundle_items)):
                                                                foreach ($bundle_items as $index => $item):
                                                            ?>
                                                                    <div class="form-group d-flex align-items-center gap-5 bundle-row">
                                                                        <div class="w-100 max-w-lg">
                                                                            <select class="form-select form-select-solid bundle-product-select" name="bundle_items[]" data-placeholder="Alt Ürün Seçiniz...">
                                                                                <option></option>
                                                                                <?php foreach ($allProducts as $p): ?>
                                                                                    <option value="<?php echo $p->product_id; ?>" <?php echo ($p->product_id == $item) ? 'selected' : ''; ?>>
                                                                                        [ID: <?php echo $p->product_id; ?>] <?php echo htmlspecialchars($p->product_name); ?>
                                                                                    </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <button type="button" class="btn btn-icon btn-light-danger remove-bundle-row">
                                                                            <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                                        </button>
                                                                    </div>
                                                                <?php
                                                                endforeach;
                                                            else:
                                                                ?>
                                                                <div class="form-group d-flex align-items-center gap-5 bundle-row">
                                                                    <div class="w-100 max-w-lg">
                                                                        <select class="form-select form-select-solid bundle-product-select" name="bundle_items[]" data-placeholder="Alt Ürün Seçiniz...">
                                                                            <option></option>
                                                                            <?php foreach ($allProducts as $p): ?>
                                                                                <option value="<?php echo $p->product_id; ?>">[ID: <?php echo $p->product_id; ?>] <?php echo htmlspecialchars($p->product_name); ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    <button type="button" class="btn btn-icon btn-light-danger remove-bundle-row">
                                                                        <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                                    </button>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="form-group mt-5">
                                                        <button type="button" id="add_bundle_row" class="btn btn-light-primary btn-sm">
                                                            <i class="ki-duotone ki-plus fs-3"></i> Ürün Ekle
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <script>
                                                document.addEventListener("DOMContentLoaded", function() {
                                                    function initSelect2(element) {
                                                        $(element).select2({
                                                            placeholder: $(element).data('placeholder'),
                                                            allowClear: true,
                                                            dropdownParent: $(element).parent()
                                                        });
                                                    }

                                                    // İlk yükleme
                                                    $('.bundle-product-select').each(function() {
                                                        initSelect2(this);
                                                    });

                                                    // Yeni satır ekleme
                                                    $('#add_bundle_row').on('click', function(e) {
                                                        e.preventDefault();

                                                        let $container = $('#bundle_items_container');
                                                        let $newRow = $('<div class="form-group d-flex align-items-center gap-5 bundle-row"></div>');

                                                        let selectHtml = `<div class="w-100 max-w-lg">
                    <select class="form-select form-select-solid bundle-product-select" name="bundle_items[]" data-placeholder="Alt Ürün Seçiniz...">
                        <option></option>
                        <?php foreach ($allProducts as $p): ?>
                            <option value="<?php echo $p->product_id; ?>">[ID: <?php echo $p->product_id; ?>] <?php echo htmlspecialchars($p->product_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>`;

                                                        let buttonHtml = `<button type="button" class="btn btn-icon btn-light-danger remove-bundle-row">
                    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                </button>`;

                                                        $newRow.append(selectHtml).append(buttonHtml);
                                                        $container.append($newRow);

                                                        initSelect2($newRow.find('.bundle-product-select'));
                                                    });

                                                    // Satır silme
                                                    $(document).on('click', '.remove-bundle-row', function(e) {
                                                        e.preventDefault();
                                                        if ($('.bundle-row').length > 1) {
                                                            $(this).closest('.bundle-row').find('.bundle-product-select').select2('destroy');
                                                            $(this).closest('.bundle-row').remove();
                                                        } else {
                                                            $('.bundle-product-select').val(null).trigger('change');
                                                        }
                                                    });
                                                });
                                            </script>
                                        <?php endif; ?>

                                        <?php if ($product->product_is_bundle != 1): ?>
                                            <div class="card-body pt-0">
                                                <h4>Bağlı Ürünler/Tedarikçiler</h4>
                                                <?php if (empty($provider_products)): ?>
                                                    <div class="text-muted">Bu ürüne bağlı tedarikçi ürünü bulunmamaktadır.</div>
                                                <?php else: ?>
                                                    <div class="card bg-dark card-flush">
                                                        <div class="card-body p-0">
                                                            <div id="sortableProviders">
                                                                <?php foreach ($provider_products as $pp): ?>
                                                                    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom border-gray-700 draggable-item cursor-move"
                                                                        data-id="<?= (int)$pp->provider_product_id ?>">

                                                                        <div class="d-flex flex-column">
                                                                            <span class="fw-semibold text-gray-200">
                                                                                <?= html_escape($pp->provider_product_product_name) ?>
                                                                            </span>
                                                                            <span class="text-muted fs-8">
                                                                                <?= html_escape($pp->user_name) ?> ·
                                                                                <?= html_escape($pp->provider_product_category_name) ?>
                                                                            </span>
                                                                            <span class="badge badge-light-primary">
                                                                                #<?= html_escape($pp->provider_product_product_id) ?>
                                                                            </span>
                                                                        </div>

                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    var el = document.getElementById('sortableProviders');

                                                    new Sortable(el, {
                                                        animation: 150,
                                                        onEnd: function() {
                                                            let order = [];
                                                            el.querySelectorAll('[data-id]').forEach(function(item, index) {
                                                                order.push({
                                                                    id: item.dataset.id,
                                                                    sort: index + 1
                                                                });
                                                            });

                                                            fetch('<?= base_url("products/updateProviderOrder/" . $product->product_id) ?>', {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/json'
                                                                    },
                                                                    body: JSON.stringify(order)
                                                                })
                                                                .then(response => response.json())
                                                                .then(data => {
                                                                    if (data.status === true) {
                                                                        toastr.success("Tedarikçi sıralaması başarıyla güncellendi.");
                                                                    } else {
                                                                        toastr.error("Tedarikçi sıralaması güncellenirken bir hata oluştu.");
                                                                    }
                                                                });
                                                        }
                                                    });
                                                });
                                            </script>


                                            <div class="card-body pt-0">
                                                <?php $providers = [1 => 'Dijipin API']; ?>

                                                <div class="mb-4">
                                                    <div class="text-muted fs-7">Bağlı Ürün</div>
                                                    <div class="fw-bold fs-6">
                                                        <?= !empty($provider_product->provider_product_provider_id) ? html_escape($providers[$provider_product->provider_product_provider_id] . ' - ' . $provider_product->provider_product_category_name . ' - ' . $provider_product->provider_product_product_name . ' #' . $provider_product->provider_product_product_id) . '<a href="' . base_url("products/changeProvider/null/{$product->product_id}") . '" class="btn btn-sm btn-danger m-2">Kaldır</a>' : 'Bağlı Tedarikçi Yok' ?>
                                                    </div>
                                                </div>

                                                <label class="form-label">Tedarikçi Ürün Ara</label>
                                                <input type="text" class="form-control form-control-solid" name="provider_product_search" id="provider_product_search">

                                                <div class="search-results m-5"></div>

                                                <button type="button" class="btn btn-sm btn-light-primary mt-2 change-provider">
                                                    <i class="ki-duotone ki-refresh fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                    Tedarikçi Değiştir
                                                </button>

                                                <script>
                                                    $(document).ready(function() {
                                                        $('#provider_product_search').on('input', function() {
                                                            var q = $(this).val();
                                                            var ajaxUrl = '<?= base_url("products/getProviderProducts") ?>';

                                                            $.ajax({
                                                                url: ajaxUrl,
                                                                method: 'POST',
                                                                dataType: 'json',
                                                                data: {
                                                                    q: q
                                                                },
                                                                success: function(response) {
                                                                    $('.search-results').empty();

                                                                    if (response.status === "success") {
                                                                        if (!response.data || response.data.length === 0) {
                                                                            $('.search-results').append('<div class="text-muted">Sonuç yok.</div>');
                                                                            return;
                                                                        }

                                                                        response.data.forEach(function(item) {
                                                                            $('.search-results').append(
                                                                                '<a class="d-block mt-2 text-hover-primary" href="<?= base_url() . 'products/changeProvider/' ?>' +
                                                                                item.provider_product_id + '/<?= (int)$this->uri->segment(3) ?>">' +
                                                                                item.user_name + ' - ' + item.provider_product_category_name +
                                                                                ' - ' + item.provider_product_product_name + ' # ' + item.provider_product_product_id +
                                                                                '</a>'
                                                                            );
                                                                        });
                                                                    } else {
                                                                        $('.search-results').append('<div class="text-danger">' + (response.message || 'Hata') + '</div>');
                                                                    }
                                                                }
                                                            });
                                                        });
                                                    });
                                                </script>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <!-- Footer Buttons -->
                        <div class="d-flex justify-content-end">
                            <a href="javascript:history.back()" class="btn btn-light me-5">Geri</a>
                            <button type="submit" id="kt_ecommerce_add_product_submit" class="btn btn-primary">
                                <span class="indicator-label">Save Changes</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.locales').select2();

        function updateLeftSidebarVisibility() {
            var activeTab = $('.nav-link.active').attr('href');
            if (activeTab === '#tab_general') {
                $('#leftSidebar').removeClass('d-none');
            } else {
                $('#leftSidebar').addClass('d-none');
            }
        }

        updateLeftSidebarVisibility();

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
            updateLeftSidebarVisibility();
        });

        let reqIndex = 0;

        $('#btnAddRequiredField').on('click', function() {
            reqIndex++;
            const tpl = document.getElementById('tplReqField');
            const node = tpl.content.firstElementChild.cloneNode(true);

            const $node = $(node);

            // product_form_fields_extra[<i>][enabled|title|key]
            $node.find('.js-enabled')
                .attr('name', `product_form_fields_extra[${reqIndex}][enabled]`)
                .val('1')
                .on('change', function() {
                    $(this).val($(this).is(':checked') ? '1' : '0');
                });

            $node.find('.js-title')
                .attr('name', `product_form_fields_extra[${reqIndex}][title]`);

            $node.find('.js-key')
                .attr('name', `product_form_fields_extra[${reqIndex}][key]`)
                .on('input', function() {
                    const v = ($(this).val() || '').trim();
                    $node.find('.js-badge').text(v || 'custom');
                });

            $node.find('.js-remove').on('click', function() {
                $node.remove();
            });

            $('#requiredFieldsWrap').append($node);
        });

        $('#mainCategorySelect').on('change', function() {
            var parentId = $(this).val();
            var $subSelect = $('#subCategorySelect');

            $subSelect.empty().append(new Option("Alt Kategori Yok (Temizle)", "0"));

            if (parentId) {
                $.ajax({
                    url: '<?= base_url("products/getSubCategories") ?>',
                    method: 'POST',
                    data: {
                        parent_id: parentId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.data.length > 0) {
                            response.data.forEach(function(item) {
                                var option = new Option(item.category_title, item.category_id);
                                $subSelect.append(option);
                            });
                        }
                        $subSelect.trigger('change');
                    },
                    error: function() {
                        console.error('Alt kategoriler yüklenirken hata oluştu.');
                    }
                });
            }
        });
    });
</script>