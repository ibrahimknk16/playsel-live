<style>
    /* Pin Giriş Editörü Görünümü */
    .pin-editor-container {
        position: relative;
        background: #1e1e2d;
        border-radius: 8px;
        padding: 10px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .pin-editor-container textarea {
        background: transparent !important;
        border: none !important;
        color: #00ff00 !important;
        /* Hacker yeşili/açık yeşil pin rengi */
        font-family: 'Courier New', Courier, monospace;
        resize: vertical;
        box-shadow: none !important;
    }

    .pin-editor-container textarea:focus {
        outline: none;
    }
    .swal2-html-container{
        max-height: 320px!important;
    }
</style>

<?php
function stockColors($stock)
{
    if ($stock <= 0) {
        return 'text-danger';
    } elseif ($stock <= 25) {
        return 'text-warning';
    } else {
        return 'text-success';
    }
}
?>
<div class="row g-7">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h3 class="fw-bold text-gray-900 mb-0">
                <a href="javascript:history.back()" class="btn btn-icon btn-light btn-sm me-3"><i class="ki-outline ki-arrow-left fs-3"></i></a>
                <?= $pool->epin_pool_title ?> - Stok Yönetimi
            </h3>
        </div>

        <div class="row g-5 mt-2">
            <div class="col-xl-3 col-sm-6">
                <div class="card bg-light-info border border-info border-dashed shadow-sm card-flush">
                    <div class="card-body p-5 d-flex align-items-center">
                        <i class="ki-outline ki-cloud-add fs-3x text-info me-4"></i>
                        <div>
                            <div class="fs-2 fw-bolder text-gray-800 counted" data-kt-countup="true" data-kt-countup-value="<?= $totalInstalled ?>"><?= $totalInstalled ?></div>
                            <div class="fw-semibold text-gray-600">Toplam Yüklenen</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card bg-light-primary border border-primary border-dashed shadow-sm card-flush">
                    <div class="card-body p-5 d-flex align-items-center">
                        <i class="ki-outline ki-dollar fs-3x text-primary me-4"></i>
                        <div>
                            <div class="fs-2 fw-bolder text-gray-800 counted" data-kt-countup="true" data-kt-countup-value="<?= $totalSelled ?>"><?= $totalSelled ?></div>
                            <div class="fw-semibold text-gray-600">Başarılı Satış</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card bg-light-danger border border-danger border-dashed shadow-sm card-flush">
                    <div class="card-body p-5 d-flex align-items-center">
                        <i class="ki-outline ki-cross-circle fs-3x text-danger me-4"></i>
                        <div>
                            <div class="fs-2 fw-bolder text-gray-800 counted" data-kt-countup="true" data-kt-countup-value="<?= $totalCancelled ?>"><?= $totalCancelled ?></div>
                            <div class="fw-semibold text-gray-600">İptal / Hatalı</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card bg-light-success border border-success border-dashed shadow-sm card-flush">
                    <div class="card-body p-5 d-flex align-items-center">
                        <i class="ki-outline ki-check-square fs-3x text-success me-4"></i>
                        <div>
                            <div class="fs-2 fw-bolder text-gray-800 counted" data-kt-countup="true" data-kt-countup-value="<?= $pool->epinsCount ?>"><?= $pool->epinsCount ?></div>
                            <div class="fw-semibold text-gray-600">Mevcut Canlı Stok</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card shadow-sm border-0 h-100 card-flush">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Geçmiş Yüklemeler</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Bu havuza yapılan tüm pin yükleme işlemleri</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-hover fs-7 gy-3 w-100" id="epinPoolTable">
                        <thead>
                            <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                <th>Kod</th>
                                <th>Tarih</th>
                                <th class="text-center">Yüklenen</th>
                                <th class="text-center text-primary">Satılan</th>
                                <th class="text-center text-success">Stokta</th>
                                <th class="text-center text-danger">İptal</th>
                                <th>Sağlayıcı / Admin</th>
                                <th class="text-center">Durum</th>
                                <th class="text-end">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            <?php foreach ($pool->epinLoads as $epin): ?>
                                <tr>
                                    <td><span class="badge badge-light fw-bold"><?= $epin->epin_load_code ?></span></td>
                                    <td><?= date('d.m.Y H:i', strtotime($epin->epin_load_date)) ?></td>
                                    <td class="text-center fw-bold text-gray-800"><?= $epin->epin_load_count ?></td>
                                    <td class="text-center fw-bold text-primary"><?= $epin->satilan ?></td>
                                    <td class="text-center fw-bold text-success"><?= $epin->stokta ?></td>
                                    <td class="text-center fw-bold text-danger"><?= $epin->iptal ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bold"><?= $epin->epin_provider_name ?></span>
                                            <span class="text-muted fs-8"><?= $epin->admin_name ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($epin->epin_load_status == 1): ?>
                                            <span class="badge badge-light-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-light-danger">Geri Alındı</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url('epin/stockTransfer/' . $epin->epin_load_code) ?>" class="btn btn-icon btn-light-dark btn-sm" data-bs-toggle="tooltip" title="Transfer Et"><i class="ki-outline ki-rocket fs-5"></i></a>
                                        <a href="<?= base_url('epin/list/' . $epin->epin_load_code) ?>" class="btn btn-icon btn-light-info btn-sm" data-bs-toggle="tooltip" title="Pinleri Gör"><i class="ki-outline ki-eye fs-5"></i></a>
                                        <a href="<?= base_url('epin/downloadLoad/' . $epin->epin_load_code) ?>" class="btn btn-icon btn-light-primary btn-sm" data-bs-toggle="tooltip" title="İndir"><i class="ki-outline ki-file-down fs-5"></i></a>

                                        <?php if ($epin->epin_load_status == 2): ?>
                                            <a href="<?= base_url('epin/returnLoad/' . $epin->epin_load_id) ?>" class="btn btn-icon btn-light-warning btn-sm" data-bs-toggle="tooltip" title="Geri Almayı İptal Et" onclick="return confirm('Bu işlemi geri almak istediğinize emin misiniz?')"><i class="ki-outline ki-arrows-circle fs-5"></i></a>
                                        <?php else: ?>
                                            <a href="<?= base_url('epin/deleteLoad/' . $epin->epin_load_id) ?>" class="btn btn-icon btn-light-danger btn-sm" data-bs-toggle="tooltip" title="Yüklemeyi İptal Et" onclick="return confirm('Bu yüklemeyi iptal edip stoktan silmek istediğinize emin misiniz?')"><i class="ki-outline ki-trash fs-5"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card shadow-sm border-0 h-100 card-flush">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Yeni Stok Ekle</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Her satıra 1 kod gelecek şekilde yapıştırın.</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <form action="<?= base_url('epin/addKeys') ?>" method="POST" id="addPinForm">
                    <input type="hidden" name="epin_pool_id" value="<?= $pool->epin_pool_id ?>">
                    <input type="hidden" name="epin_load_admin_id" value="<?= $this->session->userdata('admin_id') ?>">

                    <div class="mb-5">
                        <label class="form-label fw-semibold">Epin Sağlayıcı (Satın Alınan Yer)</label>
                        <select class="form-select form-select-solid" id="epin_load_provider_id" name="epin_load_provider_id" data-control="select2" data-placeholder="Sağlayıcı Seçin" required>
                            <option></option>
                            <?php foreach ($epin_providers as $provider): ?>
                                <option data-provider-currency="<?= $provider->epin_provider_currency ?>" value="<?= $provider->epin_provider_id ?>"><?= $provider->epin_provider_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Alış Maliyeti (Toplam)</label>
                            <input type="text" class="form-control form-control-solid" id="epin_purchase_cost" name="epin_purchase_cost" placeholder="Örn: 1250.50" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Alış Para Birimi</label>
                            <select class="form-select form-select-solid" id="epin_purchase_currency" name="epin_purchase_currency" data-control="select2" required>
                                <option value="TRY" data-rate="1">TRY</option>
                                <option value="USD" data-rate="<?= htmlspecialchars(currencyCalculator222('USD', 1)) ?>">USD</option>
                                <option value="EUR" data-rate="<?= htmlspecialchars(currencyCalculator222('EUR', 1)) ?>">EUR</option>
                                <option value="AED" data-rate="<?= htmlspecialchars(currencyCalculator222('AED', 1)) ?>">AED</option>
                                <option value="IDR" data-rate="<?= htmlspecialchars(currencyCalculator222('IDR', 1)) ?>">IDR</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kur (TRY karşılığı)
                                <span class="form-text text-muted">
                                    <i class="ki-outline ki-information fs-6" data-bs-toggle="tooltip" title="Seçilen para biriminin güncel TRY karşılığı otomatik olarak gelecektir, dilerseniz düzenleyebilirsiniz. Seçin kur TRY ise değer 1 olmalıdır."></i>
                                </span>
                            </label>
                            <input type="text" class="form-control form-control-solid" id="epin_purchase_rate" name="epin_purchase_rate" value="1" placeholder="Örn: 38.25" required>
                            <!-- alttaki span info butonu olsun ve popover olarak görünsün -->

                        </div>
                    </div>

                    <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <label class="form-label fw-semibold mb-0">Epin Kodları</label>
                            <span class="badge badge-light-success fw-bold fs-7" data-bs-toggle="tooltip" title="Boş satırlar sayılmaz">
                                Eklenecek: <span id="pinCounter" class="ms-1 fs-6">0</span>
                            </span>
                        </div>
                        <div class="pin-editor-container">
                            <textarea id="pinTextarea" name="pins" class="form-control form-control-solid" rows="15" placeholder="ABCD-1234-EFGH-5678&#10;XYZ1-9876-WUV2-5432" required></textarea>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary w-100 fw-bold" id="submitPinBtn">
                        <span class="indicator-label"><i class="ki-outline ki-plus-square fs-3"></i> Kodları Veritabanına Yükle</span>
                        <span class="indicator-progress">Kodlar Yükleniyor, Lütfen Bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="epinPoolAddModal" tabindex="-1" aria-labelledby="epinPoolAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="epinPoolAddModalLabel">Epin Kategorisi Ekle</h5><br>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" id="epinPoolAddModalContent">
                <form action="<?= base_url('epin/addPoolItem') ?>" method="post">
                    <input type="hidden" name="epin_pool_id" value="">
                    <h4 class="mt-5">Epin Havuz Adı</h4>
                    <input type="text" name="epin_pool_title" class="form-control mb-2 form-control-solid" placeholder="Havuz Adı">
                    <h4 class="mt-5">Bağlı Ürünler</h4>
                    <div class="fv-row mb-8">
                        <select class="products border rounded" name="epin_pool_products[]" multiple="multiple">
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product->product_id ?>" data-image="<?= $product->product_image ?>">
                                    <?= $product->product_name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-danger">Birden fazla ürüne aitse çoklu seçim yapınız.</p>
                        <h4 class="mt-5">Ürün Maliyeti</h4>
                        <input type="text" name="epin_pool_cost" class="forzzm-control mb-2 pool-cost" placeholder="Maliyet">
                    </div>
                    <button type="submit" class="btn btn-success">
                        <span class="indicator-label">Ekle</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateLineNumbers() {
        const textarea = document.getElementById("textarea");
        const lineNumbers = document.getElementById("lineNumbers");

        const lines = textarea.value.split("\n").length;
        lineNumbers.innerHTML = "";
        for (let i = 1; i <= lines; i++) {
            const div = document.createElement("div");
            div.textContent = i;
            lineNumbers.appendChild(div);
        }
    }

    function syncScroll() {
        const textarea = document.getElementById("textarea");
        const lineNumbers = document.getElementById("lineNumbers");
        lineNumbers.scrollTop = textarea.scrollTop;
    }
    $(document).ready(function() {

        function normalizeNumericDotInput(raw) {
            var val = String(raw || '');
            val = val.replace(/,/g, '');
            val = val.replace(/[^0-9.]/g, '');
            var firstDot = val.indexOf('.');
            if (firstDot !== -1) {
                val = val.substring(0, firstDot + 1) + val.substring(firstDot + 1).replace(/\./g, '');
            }
            return val;
        }

        function bindNumericMask(selector) {
            var field = $(selector);
            if (!field.length) return;

            field.on('keydown', function(e) {
                if (e.key === ',') {
                    e.preventDefault();
                }
            });

            field.on('input', function() {
                var normalized = normalizeNumericDotInput($(this).val());
                if ($(this).val() !== normalized) {
                    $(this).val(normalized);
                }
            });

            field.on('paste', function() {
                var inputRef = this;
                setTimeout(function() {
                    var normalized = normalizeNumericDotInput($(inputRef).val());
                    $(inputRef).val(normalized);
                }, 0);
            });
        }

        function updatePurchaseRateFromCurrency() {
            var currencySelect = $('#epin_purchase_currency');
            if (!currencySelect.length) return;

            var selectedOption = currencySelect.find('option:selected');
            var selectedCurrency = selectedOption.val();
            var mappedRate = selectedOption.data('rate');

            if (selectedCurrency === 'TRY') {
                $('#epin_purchase_rate').val('1');
                return;
            }

            if (mappedRate === undefined || mappedRate === null || mappedRate === '') {
                mappedRate = '<?= htmlspecialchars(currencyCalculator('USD', 1)) ?>';
            }

            $('#epin_purchase_rate').val(mappedRate);
        }

        function syncPurchaseCurrencyWithProvider() {
            var providerSelect = $('#epin_load_provider_id');
            var currencySelect = $('#epin_purchase_currency');
            if (!providerSelect.length || !currencySelect.length) return;

            var selectedProvider = providerSelect.find('option:selected');
            var providerCurrency = selectedProvider.data('provider-currency');
            if (!providerCurrency) return;

            var hasTarget = currencySelect.find('option[value="' + providerCurrency + '"]').length > 0;
            if (!hasTarget) return;

            currencySelect.val(providerCurrency).trigger('change');
        }

        $('.products').select2({
            templateResult: formatProductOption,
            templateSelection: formatProductSelection
        });

        bindNumericMask('#epin_purchase_rate');
        bindNumericMask('#epin_purchase_cost');

        updatePurchaseRateFromCurrency();
        syncPurchaseCurrencyWithProvider();

        $('#epin_load_provider_id').on('change', function() {
            syncPurchaseCurrencyWithProvider();
        });

        $('#epin_purchase_currency').on('change', function() {
            updatePurchaseRateFromCurrency();
        });

        function formatProductOption(option) {
            if (!option.id) return option.text;
            const imgSrc = $(option.element).data('image');
            if (!imgSrc) return option.text;
            return $(`
                <div class="d-flex align-items-center">
                    <img src="${imgSrc}" class="me-2" width="24" height="24" style="object-fit: cover; border-radius: 4px;">
                    <span>${option.text}</span>
                </div>
            `);
        }

        function formatProductSelection(option) {
            if (!option.id) return option.text;
            const imgSrc = $(option.element).data('image');
            if (!imgSrc) return option.text;
            return $(`
            <div class="d-flex align-items-center">
                <img src="${imgSrc}" width="20" height="20" style="object-fit: cover; border-radius: 4px; margin-right: 6px;">
                <span>${option.text}</span>
            </div>
        `);
        }

        $('.edit-pool').click(function() {
            var poolPid = $(this).data('pool-pid');
            var cost = $(this).closest('tr').find('td:nth-child(4) .text-success').data('cost');
            var productIds = [];
            var row = $(this).closest('tr');
            var poolProducts = row.find('td:nth-child(2) .epin-product');
            poolProducts.each(function() {
                productIds.push($(this).data('pid'));
            });
            var poolTitle = row.find('td:first').text().trim();

            // Modal işlemleri
            $('#epinPoolAddModal').modal('show');
            $('#epinPoolAddModalLabel').text('Epin Havuzunu Düzenle');
            $('#epinPoolAddModalContent form').attr('action', '<?= base_url('epin/updatePoolItem') ?>/' + poolPid);
            $('#epinPoolAddModalContent input[name="epin_pool_id"]').val(poolPid);
            $('#epinPoolAddModalContent input[name="epin_pool_title"]').val(poolTitle);
            $('#epinPoolAddModalContent input[name="epin_pool_cost"]').val(cost);
            $('#epinPoolAddModalContent .products').val(productIds).trigger('change');
        });

        $('.epin-pool-add').click(function() {
            $('#epinPoolAddModalLabel').text('Epin Kategorisi Ekle');
            $('#epinPoolAddModalContent form').attr('action', '<?= base_url('epin/addPoolItem') ?>');
            $('#epinPoolAddModalContent input[name="epin_pool_id"]').val('');
            $('#epinPoolAddModalContent input[name="epin_pool_title"]').val('');
            $('#epinPoolAddModalContent .products').val(null).trigger('change');
            $('#epinPoolAddModalContent input[name="epin_pool_cost"]').val('');
        });

        $('#epinPoolTable').DataTable({
            paging: true,
            ordering: false,
            searching: true,
            pageLength: 10,
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
            },
            dom: '<"top"f>rt<"bottom"lp><"clear">'
        });

        // .pool-cost change function replace , to .
        $('.pool-cost').on('input', function() {
            var value = $(this).val();
            value = value.replace(',', '.');
            $(this).val(value);
        });

        $('#pinTextarea').on('input', function() {
            var lines = $(this).val().split('\n');
            var validCount = 0;

            lines.forEach(function(line) {
                // Sadece boş olmayan (trimlenmiş) satırları say
                if (line.trim() !== '') {
                    validCount++;
                }
            });

            // Sayacı güncelle
            $('#pinCounter').text(validCount);

            // Renk efekti (0 ise gri, 0'dan büyükse yeşil)
            if (validCount > 0) {
                $('#pinCounter').parent().removeClass('badge-light-secondary text-muted').addClass('badge-light-success');
            } else {
                $('#pinCounter').parent().removeClass('badge-light-success').addClass('badge-light-secondary text-muted');
            }
        });

        $('#submitPinBtn').on('click', function(e) {
            e.preventDefault();
            var form = document.getElementById('addPinForm');

            // 1. Önce formdaki gerekli (required) alanlar dolmuş mu diye HTML5 ile kontrol et
            if (!form.checkValidity()) {
                form.reportValidity(); // Hata baloncuğunu çıkartır
                return;
            }

            // 2. Formdan özet verilerini topla
            var poolName = "<?= html_escape($pool->epin_pool_title) ?>";
            var providerName = $('#epin_load_provider_id option:selected').text();
            var cost = $('#epin_purchase_cost').val();
            var currency = $('#epin_purchase_currency').val();
            var pinCount = $('#pinCounter').text();

            // 3. Hiç pin girilmemişse uyar ve durdur
            if (parseInt(pinCount) === 0) {
                Swal.fire({
                    text: "Lütfen en az 1 adet geçerli pin kodu girin.",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Tamam",
                    customClass: { confirmButton: "btn btn-primary" }
                });
                return;
            }

            // 4. Onay penceresinde gösterilecek HTML özeti ve 5 saniyelik uyarı alanı
            var summaryHtml = `
                <div class="text-start mt-4 bg-light p-5 rounded">
                    <div class="mb-3 fs-5"><strong>Havuz/Oyun:</strong> <span class="text-gray-800">${poolName}</span></div>
                    <div class="mb-3 fs-5"><strong>Sağlayıcı:</strong> <span class="text-gray-800">${providerName}</span></div>
                    <div class="mb-3 fs-5"><strong>Toplam Maliyet:</strong> <span class="text-gray-800">${cost} ${currency}</span></div>
                    <div class="mb-1 fs-4"><strong>Yüklenecek Pin:</strong> <span class="text-success fw-bolder">${pinCount} Adet</span></div>
                </div>
                <div class="mt-5 text-danger fw-bold fs-6" id="check-warning">
                    <i class="ki-outline ki-timer fs-2 text-danger align-middle me-1"></i>
                    Lütfen verileri kontrol edin. Onay butonu <span id="swal-timer" class="fw-bolder fs-4 mx-1">5</span> saniye içinde aktif olacaktır.
                </div>
            `;

            // 5. SweetAlert ile onayı sor ve geri sayımı başlat
            Swal.fire({
                title: "Stok Yükleme Onayı",
                html: summaryHtml,
                icon: "question",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Evet, Onaylıyorum",
                cancelButtonText: "Vazgeç",
                customClass: {
                    confirmButton: "btn btn-success px-8",
                    cancelButton: "btn btn-light-danger px-8"
                },
                didOpen: () => {
                    const confirmBtn = Swal.getConfirmButton();
                    confirmBtn.disabled = true; // Sadece onay butonunu kilitler (Vazgeç çalışır)
                    
                    let timeLeft = 5;
                    let timerInterval = setInterval(() => {
                        timeLeft--;
                        const timerSpan = document.getElementById('swal-timer');
                        if (timerSpan) timerSpan.textContent = timeLeft;
                        
                        // Süre bittiğinde
                        if (timeLeft <= 0) {
                            clearInterval(timerInterval);
                            confirmBtn.disabled = false; // Butonu aktif et
                            
                            // Uyarı yazısını yeşile ve olumlu bir mesaja çevir
                            const warningDiv = document.getElementById('check-warning');
                            if (warningDiv) {
                                warningDiv.className = "mt-5 text-success fw-semibold fs-6";
                                warningDiv.innerHTML = '<i class="ki-outline ki-check-circle fs-2 text-success align-middle me-1"></i> Kontrol süresi doldu, işlemi onaylayabilirsiniz.';
                            }
                        }
                    }, 1000);
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Kullanıcı evet derse butonu loading moduna al ve formu gönder
                    $('#submitPinBtn').attr('data-kt-indicator', 'on').prop('disabled', true);
                    form.submit();
                }
            });
        });

    });
</script>