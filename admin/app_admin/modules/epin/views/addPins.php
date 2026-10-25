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

    .swal2-html-container {
        max-height: 320px !important;
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
        <div class="card shadow-sm border-0 h-100 card-flush">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Yeni Stok Ekle</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <form action="<?= base_url('epin/addKeys') ?>" method="POST" id="addPinForm">
                    <select name="epin_pool_id" id="epin_pool_id" class="form-select form-select-solid mb-5" data-control="select2" data-placeholder="Epin Havuzu Seçin" required>
                        <option>Yüklenecek Havuz Seçin</option>
                        <?php foreach ($pools as $p): ?>
                            <option value="<?= $p->epin_pool_id ?>"><?= $p->epin_pool_title ?></option>
                        <?php endforeach; ?>
                    </select>
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
                        <div class="col-md-2">
                            <!-- Alış maliyeti toplam mı adet mi select olacak toplam selected -->
                            <label class="form-label fw-semibold">Alış Maliyeti Türü</label>
                            <select class="form-select form-select-solid" id="epin_purchase_cost_type" name="epin_purchase_cost_type" required>
                                <option value="total" selected>Toplam</option>
                                <option value="per_unit">Adet Fiyatı</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Alış Maliyeti</label>
                            <input type="text" class="form-control form-control-solid" id="epin_purchase_cost" name="epin_purchase_cost" placeholder="Örn: 1250.50" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Alış Para Birimi</label>
                            <select class="form-select form-select-solid" id="epin_purchase_currency" name="epin_purchase_currency" data-control="select2" required>
                                <option value="TRY" data-rate="1">TRY</option>
                                <option value="USD" data-rate="<?= htmlspecialchars(currencyCalculator222('USD', 1)) ?>">USD</option>
                                <option value="EUR" data-rate="<?= htmlspecialchars(currencyCalculator222('EUR', 1)) ?>">EUR</option>
                                <option value="AED" data-rate="<?= htmlspecialchars(currencyCalculator222('AED', 1)) ?>">AED</option>
                                <option value="IDR" data-rate="<?= htmlspecialchars(currencyCalculator222('IDR', 1)) ?>">IDR</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Kur (TRY karşılığı)
                                <span class="form-text text-muted">
                                    <i class="ki-outline ki-information fs-6" data-bs-toggle="tooltip" title="Seçilen para biriminin güncel TRY karşılığı otomatik olarak gelecektir, dilerseniz düzenleyebilirsiniz. Seçin kur TRY ise değer 1 olmalıdır."></i>
                                </span>
                            </label>
                            <input type="text" class="form-control form-control-solid" id="epin_purchase_rate" name="epin_purchase_rate" value="1" placeholder="Örn: 38.25" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Toplam Maliyet <span class="form-text currency-span text-muted">(TRY)</span></label>
                            <input readonly type="text" class="form-control form-control-solid" id="epin_purchase_total" value="0.00">
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

            // MİMARİ DOKUNUŞ: Seçilen para birimini Toplam Maliyet yanındaki span'a yazdırıyoruz.
            // DOM'da sınıf araması için Vanilla JS (querySelector) jQuery'den her zaman daha hızlıdır.
            const currencySpan = document.querySelector('.currency-span');
            if (currencySpan) {
                currencySpan.textContent = '(' + selectedCurrency + ')';
            }

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

            if (validCount > 0) {
                // ID bazlı DOM erişimlerinde Vanilla JS, jQuery'e göre çok daha hızlıdır.
                // var yerine const/let kullanarak scope yönetimini daha stabil hale getiriyoruz.
                const costElement = document.getElementById('epin_purchase_cost');
                const costTypeElement = document.getElementById('epin_purchase_cost_type');
                const totalElement = document.getElementById('epin_purchase_total');

                const cost = parseFloat(costElement.value) || 0;
                const costType = costTypeElement ? costTypeElement.value : 'per_unit'; // Güvenlik için default değer ataması

                let total = 0;

                // Yorum satırındaki iş kuralının uygulanması
                if (costType === 'per_unit') {
                    total = cost * validCount;
                } else {
                    // per_unit değilse (örn: bulk/total), girilen tutar direkt genel toplamdır
                    total = cost;
                }

                totalElement.value = total.toFixed(2);
            } else {
                document.getElementById('epin_purchase_total').value = '0.00';
            }

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
            var poolName = $('#epin_pool_id option:selected').text();
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
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
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