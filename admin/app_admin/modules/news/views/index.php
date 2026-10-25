<script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>

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
                <input type="text" data-kt-news-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Haber Ara..." />
            </div>
        </div>
        <div class="card-toolbar">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newNewsModal">
                <i class="ki-duotone ki-plus fs-2"></i> Yeni Haber Ekle
            </button>
        </div>
    </div>

    <div class="card-body pt-0">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_news_table">
            <thead>
                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-100px text-center">Görsel</th>
                    <th class="min-w-175px">Başlık / Özet</th>
                    <th class="min-w-100px text-center">Kategori</th>
                    <th class="min-w-100px text-center">Tarih</th>
                    <th class="min-w-70px text-center">Görüntülenme</th>
                    <th class="min-w-70px text-center">Durum</th>
                    <th class="text-end min-w-100px">İşlemler</th>
                </tr>
            </thead>

            <tbody class="fw-semibold text-gray-600">
                <?php foreach ($newsList as $news): ?>
                    <tr id="news_row_<?= $news->news_id ?>">
                        
                        <td class="text-center">
                            <div class="symbol w-100px h-60px rounded overflow-hidden shadow-sm">
                                <?php if (!empty($news->news_image)): ?>
                                    <img src="<?= getenv('CDN_URL') ?>uploads/news/<?= $news->news_image ?>" class="w-100 h-100" style="object-fit: cover;" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg'">
                                <?php else: ?>
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg" class="w-100 h-100" style="object-fit: cover;">
                                <?php endif; ?>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex flex-column text-start">
                                <a href="javascript:void(0);" onclick="newsDetail('<?= $news->news_id ?>')" class="text-gray-800 text-hover-primary mb-1 fs-6 fw-bold">
                                    <?= html_escape($news->news_title) ?>
                                </a>
                                <span class="text-muted fs-7">
                                    <?= html_escape(mb_substr($news->news_summary, 0, 50)) ?>...
                                </span>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="badge badge-light-primary fw-bold px-3 py-1"><?= !empty($news->category_title) ? $news->category_title : 'Genel' ?></span>
                        </td>
                        
                        <td class="text-center">
                            <?php 
                            $pubTime = !empty($news->news_published_at) ? $news->news_published_at : $news->news_created_at;
                            $isFuture = (!empty($news->news_published_at) && strtotime($news->news_published_at) > time());
                            ?>
                            <div class="d-flex flex-column align-items-center">
                                <span class="text-dark fw-bold fs-7"><?= date('d.m.Y H:i', strtotime($pubTime)) ?></span>
                                <?php if ($isFuture && $news->news_status == 1): ?>
                                    <span class="badge badge-light-warning fs-8 py-0 px-2 mt-1">İleri Tarihli</span>
                                <?php else: ?>
                                    <span class="text-muted fs-8">Yayın Tarihi</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td class="text-center">
                            <span class="badge badge-light-info fw-bold px-3 py-1"><?= number_format($news->news_views) ?></span>
                        </td>

                        <td class="text-center">
                            <?php 
                            $isScheduled = ($news->news_status == 1 && !empty($news->news_published_at) && strtotime($news->news_published_at) > time());
                            ?>
                            <?php if ($isScheduled): ?>
                                <div class="mb-1">
                                    <span class="badge badge-light-warning fw-bold px-2 py-1 fs-8" title="Planlanan yayın: <?= date('d.m.Y H:i', strtotime($news->news_published_at)) ?>">
                                        <i class="ki-duotone ki-time fs-8 text-warning me-1"><span class="path1"></span><span class="path2"></span></i>
                                        Zamanlandı (<?= date('d.m.Y H:i', strtotime($news->news_published_at)) ?>)
                                    </span>
                                </div>
                            <?php endif; ?>
                            <select class="form-select form-select-sm form-select-solid" onchange="changeStatus('<?= $news->news_id ?>', this.value)">
                                <option value="1" <?= $news->news_status == 1 ? 'selected' : '' ?>><?= $isScheduled ? 'Zamanlandı' : 'Yayında' ?></option>
                                <option value="2" <?= $news->news_status == 2 ? 'selected' : '' ?>>Taslak</option>
                                <option value="0" <?= $news->news_status == 0 ? 'selected' : '' ?>>Pasif</option>
                            </select>
                        </td>

                        <td class="text-end">
                            <button class="btn btn-icon btn-color-gray-500 btn-active-color-primary btn-sm me-1" onclick="newsDetail('<?= $news->news_id ?>')" data-bs-toggle="tooltip" title="Düzenle">
                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                            <button class="btn btn-icon btn-color-gray-500 btn-active-color-danger btn-sm" onclick="deleteNews('<?= $news->news_id ?>')" data-bs-toggle="tooltip" title="Sil">
                                <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Yeni Haber Modal -->
<div class="modal fade" id="newNewsModal" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <form id="newNewsForm" class="form" action="<?= base_url('news/add') ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">Yeni Haber Ekle</h1>
                        <div class="text-muted fw-semibold fs-5">Lütfen gerekli tüm alanları doldurun.</div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Başlık</label>
                            <input type="text" class="form-control form-control-solid" name="news_title" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Kategori</label>
                            <select name="news_category_id" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#newNewsModal" data-placeholder="Kategori Seç">
                                <option value="">Genel</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category->category_id ?>"><?= $category->category_title ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-12 fv-row">
                            <label class="fs-6 fw-semibold mb-2">SEO Slug (Boş bırakılırsa başlıktan otomatik oluşturulur)</label>
                            <input type="text" class="form-control form-control-solid" name="news_slug" />
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-12 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Kısa Özet</label>
                            <textarea class="form-control form-control-solid" name="news_summary" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">SEO Başlığı</label>
                            <input type="text" class="form-control form-control-solid" name="news_seo_title" maxlength="255" />
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">SEO Açıklaması</label>
                            <textarea class="form-control form-control-solid" name="news_seo_description" rows="2" maxlength="320"></textarea>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">SEO Anahtar Kelimeleri</label>
                            <input type="text" class="form-control form-control-solid" name="news_seo_keywords" placeholder="pubg, uc, rehber" />
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-12 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Ana Görsel (Kapak Fotoğrafı)</label>
                            <input type="file" class="form-control form-control-solid" name="news_image" accept=".png, .jpg, .jpeg, .webp" required />
                        </div>
                    </div>
                    
                    <div class="row g-9 mb-8">
                        <div class="col-md-12 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">İçerik</label>
                            <div class="py-2" data-bs-theme="light">
                                <textarea class="form-control form-control-solid editor-content" name="news_content" id="news_content_add" rows="10"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Durum</label>
                            <select name="news_status" class="form-select form-select-solid">
                                <option value="1">Yayında</option>
                                <option value="2">Taslak</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Manşet (Öne Çıkar)</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="news_is_featured" value="1" />
                            </div>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Etiketler (Virgülle ayırın)</label>
                            <input type="text" class="form-control form-control-solid" name="news_tags" placeholder="örn: oyun, pubg, indirim" />
                        </div>
                    </div>

                    <!-- Yayınlanma Zamanı / Planlama -->
                    <div class="card bg-light-secondary border border-dashed border-gray-300 p-5 mb-8 rounded">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h5 class="fs-6 fw-bold mb-1 text-gray-800 d-flex align-items-center gap-2">
                                    <i class="ki-duotone ki-calendar-tick fs-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                                    Yayınlanma Zamanı (Zamanlama)
                                </h5>
                                <span class="text-muted fs-7">Haberin sitede ne zaman yayınlanacağını belirleyin.</span>
                            </div>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-6 mb-3">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="publish_timing" value="now" id="timing_now_add" checked onchange="toggleScheduleOptions('add', this.value)" />
                                <label class="form-check-label fw-semibold text-gray-800" for="timing_now_add">
                                    Hemen Yayınla (Şimdi)
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="publish_timing" value="schedule" id="timing_schedule_add" onchange="toggleScheduleOptions('add', this.value)" />
                                <label class="form-check-label fw-semibold text-gray-800" for="timing_schedule_add">
                                    İleri Tarihe Zamanla
                                </label>
                            </div>
                        </div>

                        <div id="schedule_fields_add" class="row g-4 pt-3 border-top border-gray-200" style="display: none;">
                            <div class="col-md-6">
                                <label class="fs-7 fw-bold text-gray-700 mb-1">Yayın Tarihi</label>
                                <input type="date" class="form-control form-control-solid" name="publish_date" id="add_publish_date" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" />
                            </div>
                            <div class="col-md-6">
                                <label class="fs-7 fw-bold text-gray-700 mb-1">Yayın Saati (Saat Başı)</label>
                                <div class="dropdown">
                                    <?php 
                                    $nextHour = ((int)date('H') + 1) % 24;
                                    $nextHourStr = str_pad($nextHour, 2, '0', STR_PAD_LEFT);
                                    ?>
                                    <button class="form-select form-select-solid text-start" type="button" id="add_hour_btn" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="add_hour_label"><?= $nextHourStr ?>:00</span>
                                    </button>
                                    <ul class="dropdown-menu w-100 shadow-sm py-2" id="add_hour_list" aria-labelledby="add_hour_btn" style="max-height: 200px; overflow-y: auto;">
                                        <?php for ($h = 0; $h < 24; $h++): 
                                            $hStr = str_pad($h, 2, '0', STR_PAD_LEFT);
                                        ?>
                                            <li><a class="dropdown-item py-2 px-4 fs-7 <?= ($h === $nextHour) ? 'active' : '' ?>" href="javascript:void(0);" onclick="selectPublishHour('add', '<?= $hStr ?>:00:00', '<?= $hStr ?>:00', this)"><?= $hStr ?>:00</a></li>
                                        <?php endfor; ?>
                                    </ul>
                                    <input type="hidden" name="publish_hour" id="add_publish_hour" value="<?= $nextHourStr ?>:00:00" />
                                </div>
                                <span class="text-muted fs-8 mt-1 d-block">Zamanı geldiğinde haber otomatik olarak sitede görünür hale gelir.</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-10">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Kaydet</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Düzenle Modal -->
<div class="modal fade" id="editNewsModal" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <form id="editNewsForm" class="form" action="<?= base_url('news/update') ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="news_id" id="edit_news_id" />
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">Haberi Düzenle</h1>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Başlık</label>
                            <input type="text" class="form-control form-control-solid" name="news_title" id="edit_news_title" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Kategori</label>
                            <select name="news_category_id" id="edit_news_category_id" class="form-select form-select-solid">
                                <option value="">Genel</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category->category_id ?>"><?= $category->category_title ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-12 fv-row">
                            <label class="fs-6 fw-semibold mb-2">SEO Slug</label>
                            <input type="text" class="form-control form-control-solid" name="news_slug" id="edit_news_slug" />
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-12 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Kısa Özet</label>
                            <textarea class="form-control form-control-solid" name="news_summary" id="edit_news_summary" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">SEO Başlığı</label>
                            <input type="text" class="form-control form-control-solid" name="news_seo_title" id="edit_news_seo_title" maxlength="255" />
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">SEO Açıklaması</label>
                            <textarea class="form-control form-control-solid" name="news_seo_description" id="edit_news_seo_description" rows="2" maxlength="320"></textarea>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">SEO Anahtar Kelimeleri</label>
                            <input type="text" class="form-control form-control-solid" name="news_seo_keywords" id="edit_news_seo_keywords" />
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-12 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Ana Görsel (Değiştirmek için seçin)</label>
                            <input type="file" class="form-control form-control-solid" name="news_image_edit" accept=".png, .jpg, .jpeg, .webp" />
                            <div class="mt-2 text-muted" id="current_image_hint"></div>
                        </div>
                    </div>
                    
                    <div class="row g-9 mb-8">
                        <div class="col-md-12 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">İçerik</label>
                            <div class="py-2" data-bs-theme="light">
                                <textarea class="form-control form-control-solid editor-content" name="news_content" id="edit_news_content" rows="10"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Durum</label>
                            <select name="news_status" id="edit_news_status" class="form-select form-select-solid">
                                <option value="1">Yayında</option>
                                <option value="2">Taslak</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Manşet (Öne Çıkar)</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="news_is_featured" id="edit_news_is_featured" value="1" />
                            </div>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Etiketler</label>
                            <input type="text" class="form-control form-control-solid" name="news_tags" id="edit_news_tags" />
                        </div>
                    </div>

                    <!-- Yayınlanma Zamanı / Planlama (Düzenle) -->
                    <div class="card bg-light-secondary border border-dashed border-gray-300 p-5 mb-8 rounded">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h5 class="fs-6 fw-bold mb-1 text-gray-800 d-flex align-items-center gap-2">
                                    <i class="ki-duotone ki-calendar-tick fs-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                                    Yayınlanma Zamanı (Zamanlama)
                                </h5>
                                <span class="text-muted fs-7">Haberin sitede ne zaman yayınlanacağını belirleyin.</span>
                            </div>
                            <span id="edit_schedule_status_badge"></span>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-6 mb-3">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="publish_timing" value="now" id="timing_now_edit" checked onchange="toggleScheduleOptions('edit', this.value)" />
                                <label class="form-check-label fw-semibold text-gray-800" for="timing_now_edit">
                                    Hemen / Şimdi Yayında
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="publish_timing" value="schedule" id="timing_schedule_edit" onchange="toggleScheduleOptions('edit', this.value)" />
                                <label class="form-check-label fw-semibold text-gray-800" for="timing_schedule_edit">
                                    Belirli Tarih ve Saatte (Zamanla)
                                </label>
                            </div>
                        </div>

                        <div id="schedule_fields_edit" class="row g-4 pt-3 border-top border-gray-200" style="display: none;">
                            <div class="col-md-6">
                                <label class="fs-7 fw-bold text-gray-700 mb-1">Yayın Tarihi</label>
                                <input type="date" class="form-control form-control-solid" name="publish_date" id="edit_publish_date" value="<?= date('Y-m-d') ?>" />
                            </div>
                            <div class="col-md-6">
                                <label class="fs-7 fw-bold text-gray-700 mb-1">Yayın Saati (Saat Başı)</label>
                                <div class="dropdown">
                                    <button class="form-select form-select-solid text-start" type="button" id="edit_hour_btn" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="edit_hour_label">00:00</span>
                                    </button>
                                    <ul class="dropdown-menu w-100 shadow-sm py-2" id="edit_hour_list" aria-labelledby="edit_hour_btn" style="max-height: 200px; overflow-y: auto;">
                                        <?php for ($h = 0; $h < 24; $h++): 
                                            $hStr = str_pad($h, 2, '0', STR_PAD_LEFT);
                                        ?>
                                            <li><a class="dropdown-item py-2 px-4 fs-7 <?= ($h === 0) ? 'active' : '' ?>" href="javascript:void(0);" onclick="selectPublishHour('edit', '<?= $hStr ?>:00:00', '<?= $hStr ?>:00', this)"><?= $hStr ?>:00</a></li>
                                        <?php endfor; ?>
                                    </ul>
                                    <input type="hidden" name="publish_hour" id="edit_publish_hour" value="00:00:00" />
                                </div>
                                <span class="text-muted fs-8 mt-1 d-block">Belirtilen tarih ve saat geldiğinde haber otomatik olarak yayına alınır.</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-10">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Güncelle</span>
                        </button>
                    </div>
                </form>
            </div>
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
        color: #000 !important;
    }

    .ck-body-wrapper {
        z-index: 99999 !important;
    }
</style>

<script>
    var editors = {};
    document.addEventListener("DOMContentLoaded", function() {
        const {
            ClassicEditor, Essentials, Autoformat, Bold, Italic, Underline, Strikethrough, Code, Subscript, Superscript, RemoveFormat,
            Font, Paragraph, Heading, Alignment, Indent, IndentBlock, List, TodoList, Link, Image, ImageToolbar, ImageCaption, ImageStyle, ImageResize, LinkImage,
            Table, TableToolbar, TableProperties, TableCellProperties, BlockQuote, CodeBlock, HorizontalLine, MediaEmbed, SourceEditing, GeneralHtmlSupport, Undo
        } = CKEDITOR;

        document.querySelectorAll('.editor-content').forEach(function(textarea) {
            ClassicEditor
                .create(textarea, {
                    language: 'tr',
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
                    htmlSupport: {
                        allow: [{
                            name: /.*/,
                            attributes: true,
                            classes: true,
                            styles: true
                        }]
                    },
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraf', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Başlık 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Başlık 2', class: 'ck-heading_heading2' },
                            { model: 'heading3', view: 'h3', title: 'Başlık 3', class: 'ck-heading_heading3' }
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
                    editors[textarea.id] = editor;
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

<script>
    var newsTable;

    $(document).ready(function() {
        newsTable = $('#kt_news_table').DataTable({
            "info": false,
            'order': [],
            'pageLength': 10,
        });

        document.querySelector('[data-kt-news-table-filter="search"]').addEventListener('keyup', function(e) {
            newsTable.search(e.target.value).draw();
        });
    });

    function selectPublishHour(modalType, value, label, el) {
        $('#' + modalType + '_publish_hour').val(value);
        $('#' + modalType + '_hour_label').text(label);
        if (el) {
            $(el).closest('.dropdown-menu').find('.dropdown-item').removeClass('active');
            $(el).addClass('active');
        }
    }

    function toggleScheduleOptions(modalType, value) {
        if (value === 'schedule') {
            $('#schedule_fields_' + modalType).slideDown(200);
        } else {
            $('#schedule_fields_' + modalType).slideUp(200);
        }
    }

    function changeStatus(id, status) {
        $.ajax({
            url: '<?= base_url('news/changeStatus') ?>',
            type: 'POST',
            data: { id: id, status: status },
            success: function(response) {
                var res = JSON.parse(response);
                if (res.success) {
                    toastr.success('Durum başarıyla güncellendi.');
                } else {
                    toastr.error('Durum güncellenirken bir hata oluştu.');
                }
            },
            error: function() {
                toastr.error('Sunucu ile iletişim kurulamadı.');
            }
        });
    }

    function newsDetail(id) {
        $.ajax({
            url: '<?= base_url('news/newsDetail/') ?>' + id,
            type: 'GET',
            success: function(response) {
                var news = typeof response === 'string' ? JSON.parse(response) : response;

                if(news.error) {
                    Swal.fire('Hata!', news.error, 'error');
                    return;
                }

                $('#edit_news_id').val(news.news_id);
                $('#edit_news_title').val(news.news_title);
                $('#edit_news_slug').val(news.news_slug);
                $('#edit_news_category_id').val(news.news_category_id);
                $('#edit_news_summary').val(news.news_summary);
                $('#edit_news_seo_title').val(news.news_seo_title);
                $('#edit_news_seo_description').val(news.news_seo_description);
                $('#edit_news_seo_keywords').val(news.news_seo_keywords);
                $('#edit_news_tags').val(news.news_tags);
                $('#edit_news_status').val(news.news_status);
                
                if(news.news_is_featured == 1) {
                    $('#edit_news_is_featured').prop('checked', true);
                } else {
                    $('#edit_news_is_featured').prop('checked', false);
                }

                if (editors['edit_news_content']) {
                    editors['edit_news_content'].setData(news.news_content);
                } else {
                    $('#edit_news_content').val(news.news_content);
                }
                
                if (news.news_image) {
                    $('#current_image_hint').html('<a href="<?= getenv('CDN_URL') ?>uploads/news/' + news.news_image + '" target="_blank">Mevcut Görseli Görüntüle</a>');
                } else {
                    $('#current_image_hint').html('');
                }

                // Yayınlanma Zamanı (Planlama) Alanlarını Doldur
                var pubAt = news.news_published_at || news.news_created_at;
                if (pubAt) {
                    var parts = pubAt.split(' ');
                    var datePart = parts[0] || '';
                    var timePart = parts[1] || '00:00:00';
                    var hourPart = timePart.substring(0, 2) + ':00:00';
                    var hourLabel = timePart.substring(0, 2) + ':00';

                    $('#edit_publish_date').val(datePart);
                    $('#edit_publish_hour').val(hourPart);
                    $('#edit_hour_label').text(hourLabel);
                    $('#edit_hour_list .dropdown-item').removeClass('active');
                    $('#edit_hour_list .dropdown-item').filter(function() {
                        return $(this).text().trim() === hourLabel;
                    }).addClass('active');

                    var pubDateObj = new Date(pubAt.replace(/-/g, '/'));
                    var nowObj = new Date();

                    if (pubDateObj > nowObj && news.news_status == 1) {
                        $('#timing_schedule_edit').prop('checked', true);
                        $('#schedule_fields_edit').show();
                        $('#edit_schedule_status_badge').html('<span class="badge badge-light-warning fw-bold">Zamanlanmış: ' + datePart + ' ' + timePart.substring(0, 5) + '</span>');
                    } else {
                        $('#timing_now_edit').prop('checked', true);
                        $('#schedule_fields_edit').hide();
                        if (news.news_status == 1) {
                            $('#edit_schedule_status_badge').html('<span class="badge badge-light-success fw-bold">Yayında (' + datePart + ' ' + timePart.substring(0, 5) + ')</span>');
                        } else {
                            $('#edit_schedule_status_badge').html('');
                        }
                    }
                } else {
                    $('#timing_now_edit').prop('checked', true);
                    $('#schedule_fields_edit').hide();
                    $('#edit_schedule_status_badge').html('');
                }

                $('#editNewsModal').modal('show');
            },
            error: function() {
                Swal.fire('Hata!', 'Haber bilgileri alınamadı.', 'error');
            }
        });
    }

    function deleteNews(id) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu haberi kalıcı olarak silmek istediğinize emin misiniz?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('news/deleteNews') ?>',
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        var res = typeof response === 'string' ? JSON.parse(response) : response;
                        if (res.success) {
                            Swal.fire('Silindi!', 'Haber başarıyla silindi.', 'success');
                            newsTable.row($('#news_row_' + id)).remove().draw();
                        } else {
                            Swal.fire('Hata!', 'Silme işlemi başarısız.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Hata!', 'Sunucu ile iletişim kurulamadı.', 'error');
                    }
                });
            }
        });
    }
</script>
