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

    /* Add sayfası placeholder */
    .image-input-placeholder {
        background-image: url('<?= base_url('assets/media/svg/files/blank-image.svg') ?>');
    }

    [data-bs-theme="dark"] .image-input-placeholder {
        background-image: url('<?= base_url('assets/media/svg/files/blank-image.svg') ?>');
    }
</style>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">

        <!-- TOOLBAR -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        Yeni Ürün Ekle
                    </h1>
                    <div class="text-muted fs-7 mt-1">Ürün bilgilerini doldurup kaydedin.</div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="<?= site_url('products') ?>" class="btn btn-light">
                        <i class="ki-duotone ki-arrow-left fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                        Geri
                    </a>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-n2" role="tablist">
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
        </ul>

        <!-- CONTENT -->
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
                    action="<?= site_url('products/addProduct') ?>">

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
                                <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-150px h-150px">
                                        <img src="https://www.usla.com.tr/Upload/null.png" alt="" class="img-fluid">
                                    </div>

                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Değiştir">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="product_image" accept=".png, .jpg, .jpeg, .webp">
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

                                <div class="text-muted fs-7">Only *.png, *.jpg, *.jpeg, *.webp</div>
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
                                <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-150px h-150px"> <img src="https://www.usla.com.tr/Upload/null.png" alt="" class="img-fluid"></div>

                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Değiştir">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="product_icon" accept=".png, .jpg, .jpeg, .webp">
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

                                <div class="text-muted fs-7">Only *.png, *.jpg, *.jpeg, *.webp</div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Status</h2>
                                </div>
                                <div class="card-toolbar">
                                    <div id="statusDot" class="rounded-circle bg-success w-15px h-15px"></div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <select id="product_status" class="form-select mb-2 form-select-solid" name="product_status" data-control="select2" data-hide-search="true">
                                    <option value="1" selected>Aktif</option>
                                    <option value="0">Pasif</option>
                                </select>
                                <div class="text-muted fs-7">Set the product status.</div>
                            </div>
                        </div>

                        <!-- Product Details (add'de info) -->
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Product Details</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="mb-2">
                                    <div class="text-muted fs-7">Kategori</div>
                                    <div class="fw-bold">Seçilmedi</div>
                                </div>
                                <div class="mb-4">
                                    <div class="text-muted fs-7">Bağlı Supplier</div>
                                    <div class="fw-bold">Yok</div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- ============ RIGHT CONTENT ============ -->
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">

                        <div class="tab-content">

                            <!-- TAB: GENEL AYARLAR -->
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

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Ürün Adı</label>
                                                    <input type="text"
                                                        name="product_name"
                                                        class="form-control form-control-solid"
                                                        placeholder="Ürün Adı"
                                                        required>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Ana Kategori</label>
                                                    <select class="form-select form-select-solid" name="product_category_id" id="mainCategorySelect" data-control="select2" data-placeholder="Ana Kategori seç">
                                                        <option></option>
                                                        <?php foreach ($categories as $c): ?>
                                                            <option value="<?= (int)$c->category_id ?>">
                                                                <?= html_escape($c->category_title) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Alt Kategori (Opsiyonel)</label>
                                                    <select class="form-select form-select-solid" name="product_sub_category_id" id="subCategorySelect" data-control="select2" data-placeholder="Alt kategori seç">
                                                        <option value="0">Alt Kategori Yok (Temizle)</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Ürün Tipi</label>
                                                    <select class="form-select form-select-solid" name="product_type" data-control="select2" data-placeholder="Ürün Tipi">
                                                        <option value="2">Top-Up</option>
                                                        <option value="3">Epin</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Ürün Bundle mı?</label>
                                                    <select class="form-select form-select-solid" name="product_is_bundle" data-control="select2" data-placeholder="Ürün Bundle mı?">
                                                        <option value="0">Hayır</option>
                                                        <option value="1">Evet</option>
                                                    </select>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

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
                                                        <option value="<?= html_escape($locale->locale_code) ?>">
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

                            <!-- TAB: FORM AYARLARI -->
                            <div class="tab-pane fade" id="tab_form_settings" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">

                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>Form Ayarları</h2>
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
                                                                <input class="form-check-input" type="checkbox" name="requires[]" value="user_id">
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
                                                                value="user_id" readonly>
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
                                                                <input class="form-check-input" type="checkbox" name="requires[]" value="server_id">
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
                                                                value="server_id" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- ZONE ID CARD -->
                                                <div class="card req-field-card card-flush">
                                                    <div class="card-header border-0">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="req-fields-title">Gerekli Alan</div>
                                                            <span class="badge badge-light">zone_id</span>
                                                        </div>

                                                        <div class="ms-auto d-flex align-items-center gap-3">
                                                            <div class="form-check form-switch form-check-custom form-check-solid m-0">
                                                                <input class="form-check-input" type="checkbox" name="requires[]" value="zone_id">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card-body">
                                                        <div class="mb-4">
                                                            <label class="form-label fw-semibold">Başlık</label>
                                                            <input type="text"
                                                                class="form-control form-control-solid"
                                                                name="product_form_fields[zone_id][title]"
                                                                value="Zone ID Yazınız.">
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label fw-semibold">Tanımlayıcı</label>
                                                            <input type="text"
                                                                class="form-control form-control-solid"
                                                                name="product_form_fields[zone_id][key]"
                                                                value="zone_id" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <!-- Footer Buttons -->
                        <div class="d-flex justify-content-end">
                            <a href="<?= site_url('products') ?>" class="btn btn-light me-5">Geri</a>
                            <button type="submit" id="kt_ecommerce_add_product_submit" class="btn btn-primary">
                                <span class="indicator-label">Ürün Ekle</span>
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
        // select2
        $('.locales').select2();

        // Sol sidebar sadece Genel Ayarlarda görünsün
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
            if (!tpl) return;

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