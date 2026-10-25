<style>
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
                            <div class="fw-bold fs-4 mb-1">Supplier Filtreleri</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="performance-pill" id="activeFilterCount">0 aktif parametre</span>
                            <button data-bs-toggle="modal" data-bs-target="#staticBackdrop" class="btn btn-sm btn-success ms-3">
                                <i class="fa-plus text-white"></i> Supplier Ekle
                            </button>
                        </div>
                    </div>

                    <form id="filterForm" class="row g-5 align-items-end" onsubmit="return false;">

                        <div class="col-md-6 col-xl-3">
                            <label class="form-label fw-semibold fs-7 text-muted">Supplier Ara</label>
                            <input type="text" id="filterSearch" class="form-control form-control-solid" placeholder="İsim veya ID">
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <label class="form-label fw-semibold fs-7 text-muted">Email</label>
                            <input type="text" id="filterEmail" class="form-control form-control-solid" placeholder="ornek@mail.com">
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <label class="form-label fw-semibold fs-7 text-muted">Bakiye Durumu</label>
                            <select id="filterBalance" class="form-select form-select-solid" data-control="select2" data-placeholder="Tümü" data-hide-search="true">
                                <option value=""></option>
                                <option value="positive">Artı Bakiye (+)</option>
                                <option value="negative">Eksi Bakiye (-)</option>
                                <option value="zero">Sıfır (0)</option>
                            </select>
                        </div>

                        <div class="col-3 d-flex flex-wrap justify-content-end gap-3 pt-2">
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

    <div class="col-12">
        <div class="card shadow-sm mb-12 card-flush">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="suppliersTable" class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-120px">Supplier ID</th>
                                <th class="min-w-200px">Supplier</th>
                                <th class="min-w-220px">Email</th>
                                <th class="min-w-150px">API Token</th>
                                <th class="min-w-160px">Bakiyemiz</th>
                                <th class="min-w-160px">Oluşturma</th>
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
        $('#filterBalance').select2();
        $('.products').select2({
            dropdownParent: $('#staticBackdrop')
        });

        // DataTables Server-Side Init
        var table = $('#suppliersTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
            },
            ajax: {
                url: "<?= base_url('sellers/getSellersList') ?>",
                type: "POST",
                data: function(d) {
                    d.search.value = $('#filterSearch').val();
                    d.email = $('#filterEmail').val();
                    d.balance_type = $('#filterBalance').val();
                }
            },
            columns: Array.from({
                length: 7
            }, (_, i) => ({
                data: i
            })) // 7 Sütun
        });

        // Filtreleri Uygula Butonu
        $('#applyFiltersBtn').on('click', function() {
            updateActiveFilterCount();
            table.draw();
        });

        // Filtreleri Sıfırla Butonu
        $('#resetFiltersBtn').on('click', function() {
            $('#filterSearch, #filterEmail').val('');
            $('#filterBalance').val('').trigger('change');
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

        function updateActiveFilterCount() {
            let count = 0;
            if ($('#filterSearch').val()) count++;
            if ($('#filterEmail').val()) count++;
            if ($('#filterBalance').val()) count++;
            $('#activeFilterCount').text(count + ' aktif parametre');
        }

        // Kopyalama Scripti
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
                if (typeof toastr !== 'undefined') toastr.success('Kopyalandı');
            } catch (err) {
                if (typeof toastr !== 'undefined') toastr.error('Kopyalama başarısız');
            }
            document.body.removeChild(tempInput);
        };

    });
</script>