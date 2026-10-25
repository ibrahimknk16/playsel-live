<style>
    /* Gönderdiğin örnekteki şık filtre kutusu stili */
    .filter-shell {
        border-radius: 1.25rem;
        padding: 1.5rem;
        border: 1px dashed rgba(var(--bs-primary-rgb), 0.18);
        background: linear-gradient(180deg, rgba(var(--bs-primary-rgb), 0.05), transparent 55%), var(--bs-card-bg, var(--bs-body-bg));
    }

    .filter-shell .select2-container {
        width: 100% !important;
    }

    .filter-shell .form-control,
    .filter-shell .form-select,
    .filter-shell .select2-selection {
        border-radius: 0.95rem !important;
    }

    .performance-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: rgba(var(--bs-primary-rgb), 0.10);
        color: var(--bs-primary);
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>

<div class="row">
    <div class="col-12 mb-8">
        <div class="card card-flush shadow-sm">
            <div class="card-body">
                <div class="filter-shell">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4 mb-6">
                        <div>
                            <div class="fw-bold fs-4 mb-1">Reseller Filtreleri</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="performance-pill" id="activeFilterCount">0 aktif parametre</span>
                            <button data-bs-toggle="modal" data-bs-target="#staticBackdrop" class="btn btn-sm btn-success ms-3">
                                <i class="fa-plus text-white"></i> Reseller Ekle
                            </button>
                        </div>
                    </div>

                    <form id="filterForm" class="row g-5 align-items-end" onsubmit="return false;">

                        <div class="col-md-6 col-xl-2">
                            <label class="form-label fw-semibold fs-7 text-muted">Reseller Ara</label>
                            <input type="text" id="filterSearch" class="form-control form-control-solid" placeholder="İsim veya ID">
                        </div>

                        <div class="col-md-6 col-xl-2">
                            <label class="form-label fw-semibold fs-7 text-muted">Email</label>
                            <input type="text" id="filterEmail" class="form-control form-control-solid" placeholder="ornek@mail.com">
                        </div>

                        <div class="col-md-6 col-xl-2">
                            <label class="form-label fw-semibold fs-7 text-muted">Kullanıcı Tipi</label>
                            <select id="filterTier" class="form-select form-select-solid" data-control="select2" data-placeholder="Tümü" data-hide-search="true">
                                <option value=""></option>
                                <option value="1">Son Kullanıcı</option>
                                <option value="2">Dijipin</option>
                                <option value="3">API Kullanıcısı</option>
                                <option value="4">API Özel Kullanıcısı</option>
                                <option value="5">Playsel</option>
                                <option value="6">Ödeme Kuruluşu</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-xl-2">
                            <label class="form-label fw-semibold fs-7 text-muted">Bakiye Durumu</label>
                            <select id="filterBalance" class="form-select form-select-solid" data-control="select2" data-placeholder="Tümü" data-hide-search="true">
                                <option value=""></option>
                                <option value="positive">Artı Bakiye (+)</option>
                                <option value="negative">Eksi Bakiye (-)</option>
                                <option value="zero">Sıfır (0)</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-xl-2">
                            <label class="form-label fw-semibold fs-7 text-muted">Durum</label>
                            <select id="filterStatus" class="form-select form-select-solid" data-control="select2" data-placeholder="Tümü" data-hide-search="true">
                                <option value=""></option>
                                <option value="1">Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>

                        <div class="col-2 d-flex flex-wrap justify-content-end gap-3 pt-2">
                            <button type="button" class="btn btn-light" id="resetFiltersBtn">
                                <i class="ki-duotone ki-arrows-circle fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Sıfırla
                            </button>
                            <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                                <i class="ki-duotone ki-filter fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Filtrele
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-12">
        <div class="card shadow-sm card-flush">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="resellersTable" class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-100px">ID</th>
                                <th class="min-w-200px">Reseller</th>
                                <th class="min-w-220px">Email</th>
                                <th class="min-w-200px">API Token</th>
                                <th class="min-w-150px">Kullanıcı Tipi</th>
                                <th class="min-w-150px">Durum</th>
                                <th class="min-w-150px">Bakiye</th>
                                <th class="min-w-140px">Oluşturma</th>
                                <th class="text-end min-w-120px">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="form w-100" method="POST" id="kt_sign_in_form" action="<?= base_url('resellers/add') ?>">
                    <label class="form-label fw-semibold mb-2">Yetkili Ürünler</label>
                    
                    <div class="fv-row mb-5">
                        <label class="form-label fw-semibold">Satıcı Adı</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text">
                                <i class="ki-duotone ki-user fs-3"><span class="path1"></span><span class="path2"></span></i>
                            </span>
                            <input type="text" name="name" autocomplete="off" class="form-control form-control-solid" required />
                        </div>
                    </div>

                    <div class="fv-row mb-5">
                        <label class="form-label fw-semibold">Email</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text">
                                <i class="ki-duotone ki-sms fs-3"><span class="path1"></span><span class="path2"></span></i>
                            </span>
                            <input type="email" name="email" autocomplete="off" class="form-control form-control-solid" required />
                        </div>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="form-label fw-semibold">Şifre</label>
                        <div class="position-relative">
                            <div class="input-group input-group-solid">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-key"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                                <input id="new_user_password" type="password" name="password" autocomplete="new-password" class="form-control form-control-solid" required />
                                <button type="button" class="btn btn-icon btn-active-light-primary" id="toggleNewUserPass" tabindex="-1">
                                    <i class="ki-duotone ki-eye-slash fs-2" id="iconPassHide"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    <i class="ki-duotone ki-eye fs-2 d-none" id="iconPassShow"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </button>
                            </div>
                            <div class="text-muted fs-7 mt-2">Güçlü bir şifre kullan: Harf, rakam ve semboller önerilir.</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100" id="btnCreateAdmin">
                        <span class="indicator-label">Kaydet</span>
                        <span class="indicator-progress" style="display:none;">Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        // Select2 Init
        $('#filterTier, #filterStatus, #filterBalance').select2();
        $('.products').select2({
            dropdownParent: $('#staticBackdrop')
        });

        // DataTables Server-Side Init
        var table = $('#resellersTable').DataTable({
            processing: true,
            serverSide: true,

            // YENİ EKLENEN SIRALAMA AYARLARI
            ordering: true, // Sıralamayı aktif ettik
            order: [
                [0, 'desc']
            ], // Sayfa ilk açıldığında ID'ye göre azalan (En yeni üstte) sıralansın
            columnDefs: [
                // 3. sütun (API Token) ve 8. sütun (İşlemler) sıralanamaz yapıyoruz.
                // Bakiye (6. sütun) tıklandığında sorunsuz çalışacak.
                {
                    targets: [3, 8],
                    orderable: false
                }
            ],

            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
            },
            ajax: {
                url: "<?= base_url('resellers/getResellersList') ?>",
                type: "POST",
                data: function(d) {
                    // Filtreleri gönder
                    d.search.value = $('#filterSearch').val();
                    d.email = $('#filterEmail').val();
                    d.tier = $('#filterTier').val();
                    d.status = $('#filterStatus').val();
                    d.balance_type = $('#filterBalance').val();
                }
            },
            columns: Array.from({
                length: 9
            }, (_, i) => ({
                data: i
            }))
        });

        // Filtreleri Uygula Butonu
        $('#applyFiltersBtn').on('click', function() {
            updateActiveFilterCount();
            table.draw();
        });

        // Filtreleri Sıfırla Butonu
        $('#resetFiltersBtn').on('click', function() {
            $('#filterSearch, #filterEmail').val('');
            $('#filterTier, #filterStatus, #filterBalance').val('').trigger('change');
            updateActiveFilterCount();
            table.draw();
        });

        // Enter'a basınca otomatik arama
        $('#filterSearch, #filterEmail').on('keypress', function(e) {
            if (e.which === 13) {
                updateActiveFilterCount();
                table.draw();
            }
        });

        // Aktif parametre sayısını hesaplayan ufak fonksiyon
        function updateActiveFilterCount() {
            let count = 0;
            if ($('#filterSearch').val()) count++;
            if ($('#filterEmail').val()) count++;
            if ($('#filterTier').val()) count++;
            if ($('#filterStatus').val()) count++;
            if ($('#filterBalance').val()) count++;
            $('#activeFilterCount').text(count + ' aktif parametre');
        }

        // --- MEVCUT JS KODLARIN KOPYALAMA VE STATUS DEĞİŞTİRME ---
        window.copyText = function(val) {
            if (!val) return;
            var tempInput = document.createElement("input");
            tempInput.style.position = "absolute";
            tempInput.style.left = "-9999px";
            tempInput.value = val;
            document.body.appendChild(tempInput);
            tempInput.select();
            tempInput.setSelectionRange(0, 99999);
            try {
                document.execCommand("copy");
                if (typeof toastr !== 'undefined') toastr.success('Kopyalandı: ' + val);
            } catch (err) {
                if (typeof toastr !== 'undefined') toastr.error('Kopyalama başarısız');
            }
            document.body.removeChild(tempInput);
        };

        $(document).on('change', '.js-user-status-switch', function() {
            var $sw = $(this);
            var userId = $sw.data('user-id');
            var newStatus = $sw.is(':checked') ? 1 : 0;

            $sw.prop('disabled', true);

            $.ajax({
                url: "<?= site_url('resellers/changeUserStatus') ?>/" + userId,
                type: "POST",
                dataType: "json",
                data: {
                    status: newStatus
                },
                success: function(res) {
                    if (res && res.status === 'success') {
                        if (typeof toastr !== 'undefined') toastr.success(res.message || 'Güncellendi');
                        var $badge = $sw.closest('.d-flex').find('.js-status-badge');
                        if (newStatus === 1) {
                            $badge.removeClass('badge-light-danger').addClass('badge-light-success').text('Aktif');
                        } else {
                            $badge.removeClass('badge-light-success').addClass('badge-light-danger').text('Pasif');
                        }
                    } else {
                        $sw.prop('checked', !$sw.is(':checked'));
                        if (typeof toastr !== 'undefined') toastr.error((res && res.message) ? res.message : 'Güncellenemedi');
                    }
                },
                error: function(xhr) {
                    $sw.prop('checked', !$sw.is(':checked'));
                    if (typeof toastr !== 'undefined') toastr.error('İşlem sırasında hata oluştu.');
                },
                complete: function() {
                    $sw.prop('disabled', false);
                }
            });
        });

    });
</script>