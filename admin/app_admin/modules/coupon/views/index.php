<style>
    /* Modern Tablo Hover */
    table.dataTable tbody tr:hover {
        background-color: var(--bs-light-primary) !important;
        transition: background-color 0.2s ease;
    }

    /* Kategori ve Seçim Rozetleri */
    .coupon-category-badge {
        background-color: var(--bs-gray-100);
        border: 1px solid var(--bs-gray-200);
        border-radius: 6px;
        padding: 2px 8px;
        display: inline-flex;
        align-items: center;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--bs-gray-700);
    }
</style>

<div class="row">
    <div class="card shadow-sm border-0 mb-12 card-flush">
        <div class="card-header border-0 pt-6">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1 text-gray-900">Kupon Kodu Yönetimi</span>
                <span class="text-muted fw-semibold fs-7">Sistemdeki aktif, pasif kuponlar ve indirim kuralları</span>
            </h3>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-primary cp-add-trigger fw-bold">
                        <i class="ki-outline ki-plus fs-2"></i> Yeni Kupon Oluştur
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                    <span class="fw-semibold"><?= $this->session->flashdata('success') ?></span>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                    <span class="fw-semibold"><?= $this->session->flashdata('error') ?></span>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table id="mainCouponTable" class="table align-middle table-row-dashed table-hover fs-6 gy-5 w-100">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-50px text-center">ID</th>
                            <th class="min-w-100px">Kupon Kodu</th>
                            <th class="min-w-100px">Tür</th>
                            <th class="min-w-100px">İndirim Değeri</th>
                            <th class="min-w-125px">Min. Sepet / Max. İndirim</th>
                            <th class="min-w-125px">Limitler (Kullanıcı / Genel)</th>
                            <th class="min-w-150px">Geçerlilik Tarihi</th>
                            <th class="text-center min-w-80px">Durum</th>
                            <th class="text-end min-w-100px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($coupons)): foreach ($coupons as $c): ?>
                            <tr>
                                <td class="text-center"><?= $c->coupon_id ?></td>
                                <td><span class="badge badge-light-dark fw-bolder fs-6 text-uppercase px-3 py-2 border border-dashed border-gray-400"><?= $c->coupon_code ?></span>
                                </td>
                                <td><?= $c->coupon_type == 1 ? '<span class="badge badge-light-info fw-bold">Yüzdesel (%)</span>' : '<span class="badge badge-light-success fw-bold">Sabit Tutar (TL)</span>' ?>
                                </td>
                                <td><span class="fw-bold text-gray-800"><?= $c->coupon_type == 1 ? '%' . (int)$c->coupon_value : number_format($c->coupon_value, 2, ',', '.') . ' TL' ?></span>
                                </td>
                                <td><div class="d-flex flex-column text-start">
                                        <span class="text-gray-800 fs-7 fw-semibold">Min: <span class="fw-bolder"><?= number_format($c->coupon_min_order_total, 2, ',', '.') ?> TL</span></span>
                                        <?php if ($c->coupon_type == 1): ?>
                                            <span class="text-muted fs-7">Max İndirim: <span class="fw-bold text-gray-700"><?= $c->coupon_max_discount > 0 ? number_format($c->coupon_max_discount, 2, ',', '.') . ' TL' : 'Sınırsız' ?></span></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><div class="d-flex flex-column text-start">
                                        <span class="text-gray-800 fs-7">Kullanıcı: <span class="fw-bold text-primary"><?= $c->coupon_user_limit ?></span></span>
                                        <span class="text-muted fs-7">Genel Havuz: <span class="fw-bold text-danger"><?= $c->coupon_total_limit > 0 ? $c->coupon_total_limit : 'Sınırsız' ?></span></span>
                                    </div>
                                </td>
                                <td><div class="d-flex flex-column text-start">
                                        <span class="text-gray-600 fs-7 fw-semibold">Bşl: <?= date('d.m.Y H:i', strtotime($c->coupon_start_date)) ?></span>
                                        <span class="text-danger fs-7 fw-semibold">Bts: <?= date('d.m.Y H:i', strtotime($c->coupon_end_date)) ?></span>
                                    </div>
                                </td>
                                <td class="text-center"><div class="form-check form-switch form-check-custom form-check-solid justify-content-center">
                                        <input class="form-check-input h-20px w-30px cp-status-toggle" type="checkbox" id="status_<?= $c->coupon_id ?>" data-id="<?= $c->coupon_id ?>" <?= $c->coupon_status == 1 ? 'checked' : '' ?> value="1" />
                                    </div>
                                </td>
                                <td class="text-end"><button class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1 cp-edit-trigger" 
                                            data-id="<?= $c->coupon_id ?>"
                                            data-code="<?= $c->coupon_code ?>"
                                            data-type="<?= $c->coupon_type ?>"
                                            data-value="<?= $c->coupon_value ?>"
                                            data-min-total="<?= $c->coupon_min_order_total ?>"
                                            data-max-discount="<?= $c->coupon_max_discount ?>"
                                            data-max-qty="<?= $c->coupon_max_product_qty ?>"
                                            data-user-limit="<?= $c->coupon_user_limit ?>"
                                            data-total-limit="<?= $c->coupon_total_limit ?>"
                                            data-categories="<?= $c->coupon_categories ?>"
                                            data-start="<?= date('Y-m-d\TH:i', strtotime($c->coupon_start_date)) ?>"
                                            data-end="<?= date('Y-m-d\TH:i', strtotime($c->coupon_end_date)) ?>"
                                            data-status="<?= $c->coupon_status ?>"
                                            title="Düzenle">
                                        <i class="ki-outline ki-pencil fs-3"></i>
                                    </button>
                                    <a href="<?= base_url('coupon/delete/' . $c->coupon_id) ?>" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" onclick="return confirm('Bu kupon kodunu silmek istediğinize emin misiniz?')" title="Sil">
                                        <i class="ki-outline ki-trash fs-3"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <form id="couponForm" action="<?= base_url('coupon/add') ?>" method="post" class="form">
                    
                    <div class="mb-13 text-center">
                        <h1 class="mb-3" id="couponModalLabel">Yeni Kupon Oluştur</h1>
                        <div class="text-muted fw-semibold fs-5">Kupon kural ve limitlerini eksikosiz tanımlayınız.</div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2 justify-content-between">
                                <span class="required">Kupon Kodu</span>
                            </label>
                            <input type="text" name="coupon_code" class="form-control form-control-solid text-uppercase fw-bold" placeholder="Örn: INDIRIM20" required autocomplete="off" />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                <span>Kupon İlk Durumu</span>
                            </label>
                            <div class="form-check form-switch form-check-custom form-check-solid pt-2">
                                <input class="form-check-input h-25px w-45px" type="checkbox" name="coupon_status" id="form_coupon_status" checked value="1" />
                                <label class="form-check-label ms-3 text-muted" for="form_coupon_status">Oluşturulduğunda Aktif Olsun</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">İndirim Türü</label>
                            <select class="form-select form-select-solid select-modal" name="coupon_type" required>
                                <option value="1">Yüzdesel (%)</option>
                                <option value="2">Sabit Tutar (TL)</option>
                            </select>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">İndirim Değeri</label>
                            <input type="text" name="coupon_value" class="form-control form-control-solid decimal-input" placeholder="Örn: 10 veya 50.00" required />
                        </div>
                    </div>

                    <div class="separator separator-dashed my-6"></div>
                    <h5 class="text-gray-900 fw-bold mb-6 fs-5">Kural ve Sepet Limitleri</h5>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Minimum Sepet Tutarı (TL)</label>
                            <input type="text" name="coupon_min_order_total" class="form-control form-control-solid decimal-input" value="0.00" />
                        </div>
                        <div class="col-md-6 fv-row" id="maxDiscountContainer">
                            <label class="fs-6 fw-semibold mb-2">Maksimum İndirim Sınırı (TL)</label>
                            <input type="text" name="coupon_max_discount" class="form-control form-control-solid decimal-input" value="0.00" />
                            <p class="text-muted fs-7 mt-1">Yüzdesel indirimlerde sepetin alacağı max indirim limitidir. (0 = Sınırsız)</p>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Max. Geçerli Ürün Adedi</label>
                            <input type="number" name="coupon_max_product_qty" class="form-control form-control-solid" value="0" min="0" />
                            <p class="text-muted fs-7 mt-1">0 ise sepetteki tüm adetlere uygulanır.</p>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Kullanıcı Başı Limit</label>
                            <input type="number" name="coupon_user_limit" class="form-control form-control-solid" value="1" min="1" required />
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Genel Havuz Limiti</label>
                            <input type="number" name="coupon_total_limit" class="form-control form-control-solid" value="0" min="0" />
                            <p class="text-muted fs-7 mt-1">Toplam kaç kez kullanılabilir. (0 = Sınırsız)</p>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Başlangıç Tarihi</label>
                            <input type="datetime-local" name="coupon_start_date" class="form-control form-control-solid" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Bitiş Tarihi</label>
                            <input type="datetime-local" name="coupon_end_date" class="form-control form-control-solid" required />
                        </div>
                    </div>

                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2">Geçerli Olacağı Kategoriler</label>
                        <select class="form-select form-select-solid select-categories" name="coupon_categories[]" multiple="multiple" data-placeholder="Tüm Kategorilerde Geçerli">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->category_id ?>"><?= $cat->category_title ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-muted fs-7 mt-1">Seçim yapmazsanız kupon tüm kategorilerdeki ürünlerde aktif sayılır.</p>
                    </div>

                    <div class="text-end pt-5">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kuponu Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // DataTable Başlatma
        var tbl = $('#mainCouponTable').DataTable({
            ordering: false,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                url: "//cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
            },
            dom: "<'table-responsive'tr><'row align-items-center mt-5'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>"
        });

        // Modal içindeki Select2 bileşenlerini modal'a bağlama
        $('#couponModal').on('shown.bs.modal', function() {
            $('.select-modal, .select-categories').select2({
                dropdownParent: $('#couponModal')
            });
        });

        // Yeni Kupon Oluştur Butonu (Syntax Error Düzeltildi)
        $('.cp-add-trigger').click(function(e) {
            e.preventDefault();
            $('#couponModalLabel').text('Yeni Kupon Oluştur');
            $('#couponForm').attr('action', '<?= base_url('coupon/add') ?>');
            
            $('#couponForm')[0].reset();
            // Syntax hatası veren yer düzeltildi: Köşeli parantez eklendi.
            $('input[name="coupon_code"]').val('').prop('readonly', false);
            
            $('input[name="coupon_min_order_total"]').val('0.00');
            $('input[name="coupon_max_discount"]').val('0.00');
            $('input[name="coupon_max_product_qty"]').val('0');
            $('input[name="coupon_user_limit"]').val('1');
            $('input[name="coupon_total_limit"]').val('0');
            $('#form_coupon_status').prop('checked', true);
            
            $('.select-modal').val('1').trigger('change');
            $('.select-categories').val(null).trigger('change');
            $('#maxDiscountContainer').slideDown();

            let now = new Date();
            let future = new Date();
            future.setDate(now.getDate() + 30);
            
            $('input[name="coupon_start_date"]').val(now.toISOString().slice(0, 16));
            $('input[name="coupon_end_date"]').val(future.toISOString().slice(0, 16));

            $('#couponModal').modal('show');
        });

        // Düzenle Buton Tıklaması
        $('#mainCouponTable tbody').on('click', '.cp-edit-trigger', function(e) {
            e.preventDefault();
            
            var id = $(this).data('id');
            var code = $(this).data('code');
            var type = $(this).data('type');
            var value = $(this).data('value');
            var minTotal = $(this).data('min-total');
            var maxDiscount = $(this).data('max-discount');
            var maxQty = $(this).data('max-qty');
            var userLimit = $(this).data('user-limit');
            var totalLimit = $(this).data('total-limit');
            var categories = $(this).data('categories');
            var start = $(this).data('start');
            var end = $(this).data('end');
            var status = $(this).data('status');

            $('#couponModalLabel').text('Kupon Kodunu Düzenle: ' + code);
            $('#couponForm').attr('action', '<?= base_url('coupon/edit') ?>/' + id);

            $('input[name="coupon_code"]').val(code);
            $('.select-modal').val(type).trigger('change');
            $('input[name="coupon_value"]').val(value);
            $('input[name="coupon_min_order_total"]').val(minTotal);
            $('input[name="coupon_max_discount"]').val(maxDiscount);
            $('input[name="coupon_max_product_qty"]').val(maxQty);
            $('input[name="coupon_user_limit"]').val(userLimit);
            $('input[name="coupon_total_limit"]').val(totalLimit);
            $('input[name="coupon_start_date"]').val(start);
            $('input[name="coupon_end_date"]').val(end);
            
            $('#form_coupon_status').prop('checked', status == 1);

            if (categories) {
                var catArray = categories.toString().split(',');
                $('.select-categories').val(catArray).trigger('change');
            } else {
                $('.select-categories').val(null).trigger('change');
            }

            if (type == 2) {
                $('#maxDiscountContainer').hide();
            } else {
                $('#maxDiscountContainer').show();
            }

            $('#couponModal').modal('show');
        });

        // İndirim türü değişimi
        $('.select-modal').change(function() {
            if ($(this).val() == '2') {
                $('#maxDiscountContainer').slideUp();
            } else {
                $('#maxDiscountContainer').slideDown();
            }
        });

        // Ajax ile Anlık Statü Yönetimi
        $('#mainCouponTable tbody').on('change', '.cp-status-toggle', function() {
            var id = $(this).data('id');
            var status = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: '<?= base_url("coupon/toggle_status") ?>',
                type: 'POST',
                dataType: 'JSON',
                data: { id: id, status: status },
                success: function(response) {
                    if (!response.status) {
                        alert('Durum güncellenirken sistemsel bir hata oluştu.');
                    }
                },
                error: function() {
                    alert('Sunucuyla bağlantı kurulamadı.');
                }
            });
        });

        // Virgül Nokta Değişimi
        $('.decimal-input').on('input', function() {
            $(this).val($(this).val().replace(',', '.'));
        });
    });
</script>