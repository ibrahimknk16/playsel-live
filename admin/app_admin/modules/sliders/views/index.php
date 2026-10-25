<script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>

<style>
    .slide-preview-container {
        position: relative;
        border-radius: 0.475rem;
        overflow: hidden;
    }

    .slide-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.65);
        backdrop-filter: blur(4px);
        color: #fff;
        padding: 1rem 1.5rem;
        border-radius: 0.475rem;
        text-align: center;
        width: 80%;
        max-width: 400px;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    .slide-content h4 {
        color: #ffffff;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .slide-content p {
        font-family: "Azeret Mono", monospace;
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.85rem;
        margin: 0;
    }
</style>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-dismissible bg-light-success border border-success border-dashed d-flex flex-column flex-sm-row w-100 p-5 mb-10">
        <i class="ki-duotone ki-verify fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span></i>
        <div class="d-flex flex-column pe-0 pe-sm-10">
            <h5 class="mb-1">Başarılı</h5>
            <span><?= $this->session->flashdata('success'); ?></span>
        </div>
        <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
            <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
        </button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-dismissible bg-light-danger border border-danger border-dashed d-flex flex-column flex-sm-row w-100 p-5 mb-10">
        <i class="ki-duotone ki-information fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
        <div class="d-flex flex-column pe-0 pe-sm-10">
            <h5 class="mb-1">Hata</h5>
            <span><?= $this->session->flashdata('error'); ?></span>
        </div>
        <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
            <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
        </button>
    </div>
<?php endif; ?>

<div class="card card-flush shadow-sm mb-12">
    <div class="card-header pt-8">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" data-kt-slider-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Slider Ara..." />
            </div>
        </div>
        <div class="card-toolbar d-flex gap-2">
            <button type="button" class="btn btn-light-info btn-sm" data-bs-toggle="modal" data-bs-target="#manageSliderCategoriesModal" onclick="loadCategoriesList()">
                <i class="ki-duotone ki-category fs-2"></i> Kategorileri Yönet
            </button>
            <button type="button" class="btn btn-light-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newSliderCategoryModal">
                <i class="ki-duotone ki-plus-circle fs-2"></i> Kategori Ekle
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newSliderModal">
                <i class="ki-duotone ki-plus fs-2"></i> Yeni Slider Ekle
            </button>
        </div>
    </div>

    <div class="card-body pt-0">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_slider_table">
            <thead>
                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">Başlık / İçerik</th>
                    <th class="text-center min-w-100px">Masaüstü Resim</th>
                    <th class="text-center min-w-100px">Mobil Resim</th>
                    <th class="text-center min-w-80px">Bullet İkon</th>
                    <th class="text-center min-w-60px">Sıra</th>
                    <th class="text-center min-w-70px">Kategori</th>
                    <th class="text-center min-w-70px">Dil</th>
                    <th class="text-center min-w-70px">Durum</th>
                    <th class="text-end min-w-100px">İşlemler</th>
                </tr>
            </thead>

            <tbody class="fw-semibold text-gray-600">
                <?php foreach ($sliders as $slider): ?>
                    <tr id="slider_row_<?= $slider->slider_id ?>">
                        <td>
                            <div class="d-flex flex-column text-start">
                                <a href="javascript:void(0);" onclick="sliderDetail('<?= $slider->slider_id ?>')" data-bs-toggle="tooltip" title="Düzenle">
                                    <?= $slider->slider_title ?>
                                </a>
                                <span class="text-muted fs-7"><?= $slider->slider_content_2 ?></span>
                            </div>
                        </td>

                        <td class="text-center">
                            <div class="symbol w-200px h-65px rounded overflow-hidden shadow-sm mx-auto">
                                <img src="<?= getenv('CDN_URL') ?>uploads/sliders/<?= $slider->slider_image ?>" class="w-100 h-100" style="object-fit: cover;" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg'">
                            </div>
                        </td>

                        <td class="text-center">
                            <?php if (!empty($slider->slider_mobile_image)): ?>
                                <div class="symbol w-100px h-65px rounded overflow-hidden shadow-sm mx-auto">
                                    <img src="<?= getenv('CDN_URL') ?>uploads/sliders/<?= $slider->slider_mobile_image ?>" class="w-100 h-100" style="object-fit: cover;" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg'">
                                </div>
                            <?php else: ?>
                                <span class="badge badge-light fs-8 text-muted">Masaüstü</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-center align-middle">
                            <div class="d-flex align-items-center justify-content-center mx-auto w-60px h-35px">
                                <?php if (!empty($slider->slider_bullet_image)): ?>
                                    <img src="<?= getenv('CDN_URL') ?>uploads/sliders/<?= $slider->slider_bullet_image ?>" class="mw-100 mh-100" style="object-fit: contain;" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg'">
                                <?php else: ?>
                                    <span class="text-muted fs-8">-</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="text-center align-middle">
                            <span class="badge badge-light fw-bold fs-7"><?= $slider->slider_sort_order ?? 0 ?></span>
                        </td>

                        <td class="text-center align-middle">
                            <span class="text-muted fs-7"><?= $slider->category_title ?? 'Kategori Yok' ?></span>
                        </td>

                        <td class="text-center">
                            <span class="badge badge-light-primary fw-bold px-3 py-1"><?= strtoupper($slider->slider_language) ?></span>
                        </td>

                        <td class="text-center">
                            <div class="form-check form-switch form-check-custom form-check-solid justify-content-center">
                                <input class="form-check-input h-20px w-30px cursor-pointer" type="checkbox" value="" id="status_switch_<?= $slider->slider_id ?>" onchange="changeStatus('<?= $slider->slider_id ?>')" <?= $slider->slider_status == 1 ? 'checked' : '' ?> />
                            </div>
                        </td>

                        <td class="text-end">
                            <button class="btn btn-icon btn-color-gray-500 btn-active-color-primary btn-sm me-1" onclick="sliderDetail('<?= $slider->slider_id ?>')" data-bs-toggle="tooltip" title="Düzenle">
                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                            <button class="btn btn-icon btn-color-gray-500 btn-active-color-danger btn-sm" onclick="deleteSlider('<?= $slider->slider_id ?>')" data-bs-toggle="tooltip" title="Sil">
                                <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Yeni Slider Kategori Modal -->
<div class="modal fade" id="newSliderCategoryModal" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <form id="newSliderCategoryForm" class="form" action="<?= base_url('sliders/addCategory') ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-10 text-center">
                        <h1 class="mb-3">Yeni Slider Kategorisi Ekle</h1>
                        <div class="text-muted fw-semibold fs-6">Slider sekmelerinde görünecek oyun veya kampanya kategorisi.</div>
                    </div>

                    <div class="fv-row mb-6">
                        <label class="required fs-6 fw-semibold mb-2">Kategori Adı</label>
                        <input type="text" class="form-control form-control-solid" name="category_name" placeholder="Örn: PUBG Mobile, Valorant, Free Fire" required>
                    </div>

                    <div class="fv-row mb-6">
                        <label class="fs-6 fw-semibold mb-2">Kategori Slug / Linki (Opsiyonel)</label>
                        <input type="text" class="form-control form-control-solid" name="category_slug" placeholder="Boş bırakırsanız otomatik oluşturulur (örn: pubg-mobile)">
                    </div>

                    <div class="fv-row mb-6">
                        <label class="fs-6 fw-semibold mb-2">Bullet İkonu (Sekme Logosu)</label>
                        <input class="form-control form-control-solid" type="file" name="category_bullet_icon" accept=".svg, .png, .jpg, .webp">
                        <div class="text-muted fs-8 mt-1">Önerilen: Küçük SVG veya şeffaf PNG ikon (örn: 32x32px).</div>
                    </div>

                    <div class="row g-6 mb-8">
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Sıralama</label>
                            <input type="number" class="form-control form-control-solid" name="category_sort_order" value="0">
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Dil</label>
                            <select class="form-select form-select-solid" name="category_language">
                                <?php foreach ($locales as $locale): ?>
                                    <option value="<?= $locale->locale_lang_code ?>"><?= $locale->locale_lang ?> - <?= $locale->locale_code ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Durum</label>
                            <select class="form-select form-select-solid" name="category_status">
                                <option value="1">Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kategoriyi Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Slider Kategorilerini Yönet Modal -->
<div class="modal fade" id="manageSliderCategoriesModal" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content rounded">
            <div class="modal-header">
                <h2 class="fw-bold">Slider Kategorileri</h2>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y px-10 py-8">
                <div class="d-flex justify-content-between align-items-center mb-6">
                    <span class="text-muted fs-7">Oluşturulan tüm slider sekmelerini buradan inceleyebilir ve silebilirsiniz.</span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newSliderCategoryModal">
                        <i class="ki-duotone ki-plus fs-3"></i> Yeni Ekle
                    </button>
                </div>
                <div id="sliderCategoriesListContainer">
                    <div class="text-center py-8"><span class="spinner-border text-primary" role="status"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Slider Kategorisi Düzenle Modal -->
<div class="modal fade" id="editSliderCategoryModal" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <form id="editSliderCategoryForm" class="form" action="<?= base_url('sliders/updateCategory') ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="category_id" id="edit_cat_id" value="">

                    <div class="mb-10 text-center">
                        <h1 class="mb-3">Slider Kategorisini Düzenle</h1>
                        <div class="text-muted fw-semibold fs-6">Kategori başlığını, sekme ikonunu ve sıralamasını güncelleyin.</div>
                    </div>

                    <div class="fv-row mb-6">
                        <label for="edit_cat_name" class="required fs-6 fw-semibold mb-2">Kategori Adı</label>
                        <input type="text" class="form-control form-control-solid" name="category_name" id="edit_cat_name" placeholder="Örn: League of Legends" style="pointer-events: auto !important;" required>
                    </div>

                    <div class="fv-row mb-6">
                        <label for="edit_cat_slug" class="fs-6 fw-semibold mb-2">Kategori Slug / Linki (Opsiyonel)</label>
                        <input type="text" class="form-control form-control-solid" name="category_slug" id="edit_cat_slug" placeholder="Boş bırakılırsa otomatik güncellenir" style="pointer-events: auto !important;">
                    </div>

                    <div class="fv-row mb-6">
                        <label class="fs-6 fw-semibold mb-2">Bullet İkonu (Sekme Logosu)</label>
                        <div id="edit_cat_icon_preview" class="mb-3 d-none">
                            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded border border-dashed border-gray-300">
                                <img id="edit_cat_icon_img" src="" class="w-35px h-35px object-fit-contain" alt="Mevcut İkon" onerror="this.style.display='none'">
                                <div>
                                    <div class="fs-7 fw-bold text-gray-800">Mevcut İkon</div>
                                    <div class="fs-8 text-muted">Değiştirmek istemiyorsanız yeni dosya seçmeyin.</div>
                                </div>
                            </div>
                        </div>
                        <input class="form-control form-control-solid" type="file" name="category_bullet_icon" accept=".svg, .png, .jpg, .webp">
                        <div class="text-muted fs-8 mt-1">Önerilen: Küçük SVG veya şeffaf PNG ikon (örn: 32x32px).</div>
                    </div>

                    <div class="row g-6 mb-8">
                        <div class="col-md-4 fv-row">
                            <label for="edit_cat_sort_order" class="fs-6 fw-semibold mb-2">Sıralama</label>
                            <input type="number" class="form-control form-control-solid" name="category_sort_order" id="edit_cat_sort_order" value="0" style="pointer-events: auto !important;">
                        </div>
                        <div class="col-md-4 fv-row">
                            <label for="edit_cat_language" class="required fs-6 fw-semibold mb-2">Dil</label>
                            <select class="form-select form-select-solid" name="category_language" id="edit_cat_language" style="pointer-events: auto !important;">
                                <?php foreach ($locales as $locale): ?>
                                    <option value="<?= $locale->locale_lang_code ?>"><?= $locale->locale_lang ?> - <?= $locale->locale_code ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label for="edit_cat_status" class="required fs-6 fw-semibold mb-2">Durum</label>
                            <select class="form-select form-select-solid" name="category_status" id="edit_cat_status" style="pointer-events: auto !important;">
                                <option value="1">Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveCategoryEdit">Değişiklikleri Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Slider Modal -->
<div class="modal fade" id="newSliderModal" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <form id="newSliderForm" class="form" action="<?= base_url('sliders/add') ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">Yeni Slider Ekle</h1>
                        <div class="text-muted fw-semibold fs-5">Sisteme yeni bir promosyon veya duyuru görseli ekleyin.</div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Slider Başlığı</label>
                            <input type="text" class="form-control form-control-solid" name="newSliderTitle" required>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Slide Linki</label>
                            <input type="url" class="form-control form-control-solid" name="newSliderUrl" placeholder="https://" required>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-3 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Dil Seçimi</label>
                            <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" name="newSliderLanguage" required>
                                <?php foreach ($locales as $locale): ?>
                                    <option value="<?= $locale->locale_lang_code ?>"><?= $locale->locale_lang ?> - <?= $locale->locale_code ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Durum</label>
                            <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" name="newSliderStatus" required>
                                <option value="1">Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Kategori</label>
                            <select class="form-select form-select-solid slider-categories" data-control="select2" data-hide-search="false" name="newSliderCategory" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category->category_id ?>"><?= $category->category_title ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Sıralama</label>
                            <input type="number" class="form-control form-control-solid" name="newSliderSortOrder" value="0">
                        </div>
                    </div>

                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2">Üst İçerik (Opsiyonel)</label>
                        <textarea class="form-control form-control-solid" rows="2" name="newSliderContent1"></textarea>
                    </div>

                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2">Alt İçerik (Opsiyonel)</label>
                        <textarea class="form-control form-control-solid" rows="3" name="newSliderContent2"></textarea>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-4 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Masaüstü Slider Resmi</label>
                            <input class="form-control form-control-solid" type="file" name="file" accept=".png, .jpg, .jpeg, .webp" required>
                            <div class="text-muted fs-7 mt-2">Önerilen boyut: 1920x800px.</div>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Mobil Slider Resmi (Opsiyonel)</label>
                            <input class="form-control form-control-solid" type="file" name="slider_mobile_image" accept=".png, .jpg, .jpeg, .webp">
                            <div class="text-muted fs-7 mt-2">Önerilen: 600x420px. Boşsa masaüstü kullanılır.</div>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Bullet Resmi (Opsiyonel)</label>
                            <input class="form-control form-control-solid" type="file" name="slider_bullet_image" accept=".png, .svg">
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Detay / Düzenleme Modal -->
<div class="modal fade" id="sliderDetailModal" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <form id="sliderEditForm" class="form" enctype="multipart/form-data" action="<?= base_url('sliders/update') ?>" method="POST">
                    <input type="hidden" name="slider_id" id="sliderEditId" value="">
                    
                    <div class="mb-10 text-center">
                        <h1 class="mb-3">Slider Düzenle</h1>
                        <div class="text-muted fw-semibold fs-6">Slider bilgilerini, yönlendirme linkini ve görsellerini güncelleyin.</div>
                    </div>

                    <!-- Görsel Önizleme -->
                    <div class="row g-6 mb-8 text-center" id="sliderEditPreviewRow">
                        <div class="col-md-8">
                            <label class="fs-7 fw-bold text-muted mb-2 d-block">Mevcut Masaüstü Görsel</label>
                            <div class="rounded border p-2 bg-light text-center h-160px d-flex align-items-center justify-content-center overflow-hidden">
                                <img id="sliderEditDesktopImg" src="" class="img-fluid rounded shadow-sm mh-100 mw-100" style="object-fit: cover;" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg'">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="fs-7 fw-bold text-muted mb-2 d-block">Mevcut Mobil Görsel</label>
                            <div class="rounded border p-2 bg-light text-center h-160px d-flex align-items-center justify-content-center overflow-hidden">
                                <img id="sliderEditMobileImg" src="" class="img-fluid rounded shadow-sm mh-100 mw-100" style="object-fit: contain;">
                                <span id="sliderEditMobileNoImg" class="text-muted fs-8 d-none">Mobil görsel yok<br>(Masaüstü kullanılıyor)</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-4 fv-row">
                            <label for="sliderEditTitle" class="required fs-6 fw-semibold mb-2">Slider Adı</label>
                            <input type="text" id="sliderEditTitle" class="form-control form-control-solid" name="sliderTitle" style="pointer-events: auto !important;" required>
                        </div>
                        <div class="col-md-3 fv-row">
                            <label for="sliderEditLanguage" class="required fs-6 fw-semibold mb-2">Dil</label>
                            <select id="sliderEditLanguage" class="form-select form-select-solid" name="sliderLanguage" style="pointer-events: auto !important;">
                                <?php foreach ($locales as $locale): ?>
                                    <option value="<?= $locale->locale_lang_code ?>"><?= $locale->locale_lang ?> - <?= $locale->locale_code ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 fv-row">
                            <label for="sliderEditStatus" class="required fs-6 fw-semibold mb-2">Durum</label>
                            <select id="sliderEditStatus" class="form-select form-select-solid" name="sliderStatus" style="pointer-events: auto !important;">
                                <option value="1">Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                        <div class="col-md-3 fv-row">
                            <label for="sliderEditSortOrder" class="fs-6 fw-semibold mb-2">Sıralama</label>
                            <input type="number" id="sliderEditSortOrder" class="form-control form-control-solid" name="sliderSortOrder" value="0" style="pointer-events: auto !important;">
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label for="sliderEditCategory" class="required fs-6 fw-semibold mb-2">Kategori</label>
                            <select id="sliderEditCategory" class="form-select form-select-solid" name="sliderCategory" style="pointer-events: auto !important;" required>
                                <option value="">Kategori Seçin</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category->category_id ?>"><?= $category->category_title ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label for="sliderEditUrl" class="required fs-6 fw-semibold mb-2">Yönlendirme Linki</label>
                            <input type="text" id="sliderEditUrl" class="form-control form-control-solid" name="sliderUrl" style="pointer-events: auto !important;" required>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label for="sliderEditContent1" class="fs-6 fw-semibold mb-2">Üst İçerik</label>
                            <textarea id="sliderEditContent1" class="form-control form-control-solid" rows="3" name="sliderContent1" style="pointer-events: auto !important;"></textarea>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label for="sliderEditContent2" class="fs-6 fw-semibold mb-2">Alt İçerik</label>
                            <textarea id="sliderEditContent2" class="form-control form-control-solid" rows="3" name="sliderContent2" style="pointer-events: auto !important;"></textarea>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Yeni Masaüstü Resim</label>
                            <input class="form-control form-control-solid" type="file" name="slider_image" accept=".png, .jpg, .jpeg, .webp">
                            <div class="text-muted fs-8 mt-1">Önerilen: 1920x800px</div>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Yeni Mobil Resim</label>
                            <input class="form-control form-control-solid" type="file" name="slider_mobile_image" accept=".png, .jpg, .jpeg, .webp">
                            <div class="text-muted fs-8 mt-1">Önerilen: 600x420px</div>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Yeni Bullet Resmi</label>
                            <input class="form-control form-control-solid" type="file" name="slider_bullet_image" accept=".png, .svg">
                        </div>
                    </div>

                    <div class="text-center pt-10">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    var datatable;
    document.addEventListener("DOMContentLoaded", function() {
        const table = document.getElementById('kt_slider_table');
        if (table) {
            datatable = $(table).DataTable({
                "info": false,
                'order': [],
                'pageLength': 10,
                'language': {
                    'url': '//cdn.datatables.net/plug-ins/2.3.1/i18n/tr.json'
                },
                drawCallback: function() {
                    // Re-initialize tooltips after datatable redraw
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            });

            document.querySelector('[data-kt-slider-table-filter="search"]').addEventListener('keyup', function(e) {
                datatable.search(e.target.value).draw();
            });
        }
    });

    function changeStatus(sliderID) {
        const isChecked = $('#status_switch_' + sliderID).is(':checked');
        const currentStatus = isChecked ? 1 : 0;

        $.ajax({
            url: '<?= site_url('sliders/changeStatus/') ?>' + sliderID,
            type: 'POST',
            data: {
                status: currentStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.status === 'success') {
                    // Başarılı toast bildirimi (Metronic için minimalist tasarım)
                    toastr.success(response.message || "Durum güncellendi.");
                } else {
                    toastr.error(response.message || "Bir hata oluştu.");
                    $('#status_switch_' + sliderID).prop('checked', !isChecked);
                }
            },
            error: function() {
                toastr.error("Sunucu hatası oluştu.");
                $('#status_switch_' + sliderID).prop('checked', !isChecked);
            }
        });
    }

    function sliderDetail(sliderID) {
        // Clear any stuck tooltips from table buttons
        $('.tooltip').remove();

        const modalEl = document.getElementById('sliderDetailModal');
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

        // Reset form
        $('#sliderEditForm')[0].reset();
        $('#sliderEditDesktopImg').attr('src', '');
        $('#sliderEditMobileImg').attr('src', '').show();
        $('#sliderEditMobileNoImg').addClass('d-none');

        $.ajax({
            url: '<?= base_url('sliders/sliderDetail/') ?>' + sliderID,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && !response.error) {
                    $('#sliderEditId').val(response.slider_id);
                    $('#sliderEditTitle').val(response.slider_title || '');
                    $('#sliderEditUrl').val(response.slider_url || '');
                    $('#sliderEditSortOrder').val(response.slider_sort_order || 0);
                    $('#sliderEditLanguage').val(response.slider_language || 'TR');
                    $('#sliderEditStatus').val(response.slider_status !== undefined ? response.slider_status : 1);
                    $('#sliderEditCategory').val(response.slider_category_id || '');
                    $('#sliderEditContent1').val(response.slider_content_1 || '');
                    $('#sliderEditContent2').val(response.slider_content_2 || '');

                    const cdnUrl = '<?= getenv('CDN_URL') ?>';
                    if (response.slider_image) {
                        $('#sliderEditDesktopImg').attr('src', cdnUrl + 'uploads/sliders/' + response.slider_image);
                    }

                    if (response.slider_mobile_image) {
                        $('#sliderEditMobileImg').attr('src', cdnUrl + 'uploads/sliders/' + response.slider_mobile_image).show();
                        $('#sliderEditMobileNoImg').addClass('d-none');
                    } else {
                        $('#sliderEditMobileImg').hide();
                        $('#sliderEditMobileNoImg').removeClass('d-none');
                    }

                    modalInstance.show();
                } else {
                    toastr.error('Slider detayları alınamadı.');
                }
            },
            error: function() {
                toastr.error('Sunucu hatası oluştu.');
            }
        });
    }

    function deleteSlider(id) {
        Swal.fire({
            text: "Bu slider'ı silmek istediğinize emin misiniz?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Evet, sil!",
            cancelButtonText: "Hayır, iptal et",
            customClass: {
                confirmButton: "btn fw-bold btn-danger",
                cancelButton: "btn fw-bold btn-active-light-primary"
            }
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "<?= base_url('sliders/delete/') ?>" + id,
                    type: "POST",
                    dataType: "json",
                    success: function(response) {
                        if (response && response.status === "success") {
                            Swal.fire({
                                text: response.message,
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "Tamam",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary"
                                }
                            }).then(function() {
                                // DataTable satırını DOM'dan animasyonla kaldır ve tabloyu redraw et
                                datatable.row($('#slider_row_' + id)).remove().draw();
                            });
                        } else {
                            Swal.fire({
                                text: response.message || "Silme işlemi başarısız oldu.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Tamam",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary"
                                }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            text: "İşlem sırasında sunucu hatası oluştu.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Tamam",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary"
                            }
                        });
                    }
                });
            }
        });
    }

    function loadCategoriesList() {
        $('#sliderCategoriesListContainer').html('<div class="text-center py-8"><span class="spinner-border text-primary" role="status"></span></div>');
        $.ajax({
            url: '<?= base_url('sliders/getCategoriesList') ?>',
            type: 'GET',
            dataType: 'json',
            success: function(cats) {
                if (!cats || !cats.length) {
                    $('#sliderCategoriesListContainer').html(`
                        <div class="alert alert-info text-center">
                            Henüz oluşturulmuş bir slider kategorisi bulunmuyor.<br>
                            Yukarıdaki <strong>Yeni Ekle</strong> butonundan yeni bir kategori ekleyebilirsiniz.
                        </div>
                    `);
                    return;
                }

                let html = `
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-7 gy-3">
                            <thead>
                                <tr class="text-gray-400 fw-bold text-uppercase gs-0">
                                    <th class="w-50px text-center">İkon</th>
                                    <th>Kategori Adı</th>
                                    <th>Slug / Link</th>
                                    <th class="text-center">Sıra</th>
                                    <th class="text-center">Dil</th>
                                    <th class="text-center">Durum</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                `;

                cats.forEach(function(c) {
                    const iconUrl = c.bullet_icon ? (c.bullet_icon.startsWith('http') ? c.bullet_icon : '<?= getenv('CDN_URL') ?>uploads/sliders/bullets/' + c.bullet_icon) : '';
                    html += `
                        <tr id="cat_row_${c.id}">
                            <td class="text-center">
                                ${iconUrl ? `
                                    <img src="${iconUrl}" class="w-25px h-25px object-fit-contain" onerror="this.style.display='none'">
                                ` : '<span class="text-muted fs-8">-</span>'}
                            </td>
                            <td><strong class="text-gray-800">${c.name}</strong></td>
                            <td><span class="text-muted">${c.slug || '-'}</span></td>
                            <td class="text-center"><span class="badge badge-light">${c.sort_order || 0}</span></td>
                            <td class="text-center"><span class="badge badge-light-primary text-uppercase">${c.language || 'tr'}</span></td>
                            <td class="text-center">
                                ${c.status == 1 ? '<span class="badge badge-light-success">Aktif</span>' : '<span class="badge badge-light-danger">Pasif</span>'}
                            </td>
                            <td class="text-end d-flex">
                                <button type="button" class="btn btn-icon btn-color-gray-500 btn-active-color-primary btn-sm me-1" onclick="openEditCategoryModal(${c.id})" title="Düzenle">
                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                <button type="button" class="btn btn-icon btn-color-gray-500 btn-active-color-danger btn-sm" onclick="deleteCategory(${c.id})" title="Sil">
                                    <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                $('#sliderCategoriesListContainer').html(html);
            },
            error: function() {
                $('#sliderCategoriesListContainer').html('<div class="alert alert-danger">Kategoriler yüklenirken hata oluştu.</div>');
            }
        });
    }

    function openEditCategoryModal(catId) {
        // Temporarily hide manage modal to avoid focus trap / stacking collision
        const manageEl = document.getElementById('manageSliderCategoriesModal');
        const manageModal = bootstrap.Modal.getInstance(manageEl);
        if (manageModal) {
            manageModal.hide();
        }

        $.ajax({
            url: '<?= base_url('sliders/getCategoryDetail/') ?>' + catId,
            type: 'GET',
            dataType: 'json',
            success: function(cat) {
                if (!cat || cat.status === 'error') {
                    toastr.error(cat.message || 'Kategori bilgisi alınamadı.');
                    if (manageModal) manageModal.show();
                    return;
                }

                $('#edit_cat_id').val(cat.id);
                $('#edit_cat_name').val(cat.name || '');
                $('#edit_cat_slug').val(cat.slug || '');
                $('#edit_cat_sort_order').val(cat.sort_order || 0);
                $('#edit_cat_language').val(cat.language || 'tr');
                $('#edit_cat_status').val(cat.status !== undefined ? cat.status : 1);

                if (cat.icon_url) {
                    $('#edit_cat_icon_img').attr('src', cat.icon_url).show();
                    $('#edit_cat_icon_preview').removeClass('d-none');
                } else {
                    $('#edit_cat_icon_preview').addClass('d-none');
                }

                $('#editSliderCategoryForm').find('input[type="file"]').val('');

                // When edit modal closes, automatically re-open manage categories modal with updated list
                $('#editSliderCategoryModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
                    const mModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('manageSliderCategoriesModal'));
                    mModal.show();
                    loadCategoriesList();
                });

                const editModalEl = document.getElementById('editSliderCategoryModal');
                const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
                editModal.show();
            },
            error: function() {
                toastr.error('Kategori detayları yüklenirken sunucu hatası oluştu.');
                if (manageModal) manageModal.show();
            }
        });
    }

    $(document).on('submit', '#editSliderCategoryForm', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        const submitBtn = $('#btnSaveCategoryEdit');

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Kaydediliyor...');

        $.ajax({
            url: $(form).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(resp) {
                submitBtn.prop('disabled', false).text('Değişiklikleri Kaydet');
                if (resp && resp.status === 'success') {
                    toastr.success(resp.message || 'Kategori güncellendi.');
                    const modalEl = document.getElementById('editSliderCategoryModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    // hidden.bs.modal will automatically reopen manageSliderCategoriesModal
                } else {
                    toastr.error(resp.message || 'Güncelleme başarısız oldu.');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).text('Değişiklikleri Kaydet');
                toastr.error('İşlem sırasında bir hata oluştu.');
            }
        });
    });

    function deleteCategory(catId) {
        Swal.fire({
            text: "Bu kategoriyi silmek istediğinize emin misiniz?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Evet, sil!",
            cancelButtonText: "Hayır, iptal et",
            customClass: {
                confirmButton: "btn fw-bold btn-danger",
                cancelButton: "btn fw-bold btn-active-light-primary"
            }
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: '<?= base_url('sliders/deleteCategory/') ?>' + catId,
                    type: 'POST',
                    dataType: 'json',
                    success: function(resp) {
                        if (resp && resp.status === 'success') {
                            toastr.success(resp.message || "Kategori silindi.");
                            $('#cat_row_' + catId).fadeOut(300, function() { $(this).remove(); });
                        } else {
                            toastr.error(resp.message || "Silme başarısız.");
                        }
                    },
                    error: function() {
                        toastr.error("Sunucu hatası oluştu.");
                    }
                });
            }
        });
    }
</script>