<style>
    /* Tablo satır hover efekti */
    table.dataTable tbody tr:hover {
        background-color: var(--bs-light-primary) !important;
        transition: background-color 0.2s ease;
    }

    /* Filtre kutusu Select2 uyumluluğu */
    .filter-wrapper .select2-container {
        width: 100% !important;
    }

    .bakiye-dashboard .table-empty { padding: 4rem 1rem; }
    .bakiye-dashboard .table-empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 1.5rem;
        margin: 0 auto 1.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--bs-primary-rgb), 0.08);
        color: var(--bs-primary);
    }
</style>

<div class="card shadow-sm border-0 mb-12 bakiye-dashboard card-flush">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1 text-gray-900">Stok Yükleme Raporu</span>
            <span class="text-muted fw-semibold fs-7">Sisteme yüklenen tüm epin kodlarının geçmiş kayıtları</span>
        </h3>
    </div>

    <div class="card-body py-4">

        <div class="bg-light rounded-3 p-6 mb-8 border border-dashed border-gray-300 filter-wrapper">
            <div class="row g-5 align-items-end">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold text-gray-700 fs-7">Yükleyen Admin</label>
                    <select id="filter_admin_id" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="Tümü">
                        <option value="">Tümü</option>
                        <?php foreach ($admins as $admin) : ?>
                            <option value="<?= $admin['admin_id'] ?>"><?= $admin['admin_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold text-gray-700 fs-7">Ürün Havuzu</label>
                    <select id="filter_epin_pool_id" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="Tümü">
                        <option value="">Tümü</option>
                        <?php foreach ($epin_pools as $pool) : ?>
                            <option value="<?= $pool['epin_pool_id'] ?>"><?= $pool['epin_pool_title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold text-gray-700 fs-7">Sağlayıcı</label>
                    <select id="filter_epin_provider_id" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="Tümü">
                        <option value="">Tümü</option>
                        <?php foreach ($providers as $reseller) : ?>
                            <option value="<?= $reseller['epin_provider_id'] ?>"><?= $reseller['epin_provider_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold text-gray-700 fs-7">Başlangıç Tarihi</label>
                    <input type="date" id="filter_startDate" class="form-control form-control-sm form-control-solid" value="<?= $filters['startDate'] ?>" max="<?= date('Y-m-d') ?>">
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold text-gray-700 fs-7">Bitiş Tarihi</label>
                    <input type="date" id="filter_endDate" class="form-control form-control-sm form-control-solid" value="<?= $filters['endDate'] ?>" max="<?= date('Y-m-d') ?>">
                </div>

                <div class="col-lg-2 col-md-12 d-flex justify-content-end gap-2">
                    <button class="btn btn-sm btn-light-primary fw-bold" id="resetFiltersBtn" data-bs-toggle="tooltip" title="Filtreleri Sıfırla">
                        <i class="ki-outline ki-arrows-circle fs-3"></i> Sıfırla
                    </button>
                    <button class="btn btn-sm btn-primary fw-bold" id="applyFiltersBtn">
                        <i class="ki-outline ki-filter fs-3"></i> Ara
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-row-dashed table-hover fs-6 gy-5 w-100" id="epinLoadTable">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-50px">#</th>
                        <th class="min-w-150px">Yükleyen</th>
                        <th class="min-w-200px">Ürün (Havuz) Adı</th>
                        <th class="min-w-150px">Yükleme Kodu</th>
                        <th class="text-center min-w-100px">Adet</th>
                        <th class="min-w-125px">Yükleme Durumu</th>
                        <th class="min-w-150px">Tedarikçi</th>
                        <th class="min-w-175px">Maliyet</th>
                        <th class="min-w-125px">TRY Kuru</th>
                        <th class="min-w-175px">Birim Maliyet TL</th>
                        <th class="min-w-125px">Toplam Maliyet TL</th>
                        <th class="min-w-125px text-end">Yükleme Tarihi</th>
                        <th class="min-w-125px text-end">Düzenle</th>
                    </tr>
                </thead>
                </table>
        </div>
    </div>
</div>

<div class="modal fade" id="epinLoadDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yükleme Detayları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?= base_url('epin/updateEpinLoad') ?>" method="post">
                    <input type="hidden" name="epin_load_id" id="epinLoadIdInput">
                    <input type="hidden" name="epin_load_code" id="epinLoadCodeInput">
                    <input type="text" name="epin_pool_cost" id="epinPoolCostInput" class="form-control form-control-sm mb-4 form-control-solid" placeholder="Havuz Maliyeti">
                    <select name="epin_load_cost_currency" id="epinLoadCostCurrencySelect" class="form-select form-select-sm mb-4 form-select-solid" data-control="select2" data-placeholder="Maliyet Para Birimi">
                        <option value="">Maliyet Para Birimi</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="TRY">TRY</option>
                    </select>
                    <input type="text" name="epin-load-purchase-rate" class="form-control form-control-sm mb-4 form-control-solid" placeholder="Havuz Maliyeti" id="epinLoadPurchaseRateInput">
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Select2 Init
        if ($('select[data-control="select2"]').length > 0) {
            $('select[data-control="select2"]').select2({ width: '100%' });
        }

        // Metronic 8 DataTables Konfigürasyonu
        const dtConfig = {
            processing: true,
            serverSide: true,
            ordering: false, // İstersen sonradan açabilirsin, query'e order column eklemen gerekir
            searching: false,
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                url: "//cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json",
                processing: `
                    <div class="d-flex flex-column align-items-center justify-content-center py-10 bg-body rounded shadow-sm">
                        <div class="spinner-border text-primary border-4" style="width: 3rem; height: 3rem;" role="status"></div>
                        <div class="text-muted fw-semibold mt-3 fs-6">Sunucudan veriler çekiliyor...</div>
                    </div>`,
                emptyTable: `
                    <div class="table-empty text-center">
                        <div class="table-empty-icon bg-light-warning text-warning"><i class="ki-outline ki-magnifier fs-3x"></i></div>
                        <div class="fw-bolder fs-4 text-gray-900 mb-1">Kayıt Bulunamadı</div>
                        <div class="text-muted fs-6">Filtre kriterlerinize uyan epin yükleme kaydı yok.</div>
                    </div>`,
                zeroRecords: `
                    <div class="table-empty text-center">
                        <div class="table-empty-icon bg-light-warning text-warning"><i class="ki-outline ki-magnifier fs-3x"></i></div>
                        <div class="fw-bolder fs-4 text-gray-900 mb-1">Sonuç Bulunamadı</div>
                    </div>`
            },
            dom: "<'table-responsive'tr><'row align-items-center mt-5'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>"
        };

        // DataTable Başlatma
        var epinLoadTable = $('#epinLoadTable').DataTable({
            ...dtConfig,
            ajax: {
                url: "<?= site_url('epin/epinLoadReportAjax') ?>", // Controller Route'unu ayarla
                type: "POST",
                data: function(d) {
                    // Filtre parametrelerini AJAX'a dahil et
                    d.admin_id = $('#filter_admin_id').val();
                    d.epin_pool_id = $('#filter_epin_pool_id').val();
                    d.epin_provider_id = $('#filter_filter_epin_provider_id').val();
                    d.startDate = $('#filter_startDate').val();
                    d.endDate = $('#filter_endDate').val();
                }
            }
        });

        // Filtre Uygula Butonu
        $('#applyFiltersBtn').click(function(e) {
            e.preventDefault();
            epinLoadTable.ajax.reload();
        });

        // Filtre Sıfırla Butonu
        $('#resetFiltersBtn').click(function(e) {
            e.preventDefault();
            $('#filter_admin_id, #filter_epin_pool_id, #filter_epin_provider_id').val('').trigger('change');
            
            // Tarihleri default'a çek (Bugün ve 30 gün öncesi)
            let today = new Date().toISOString().split('T')[0];
            let lastMonth = new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0];
            
            $('#filter_startDate').val(lastMonth);
            $('#filter_endDate').val(today);
            
            epinLoadTable.ajax.reload();
        });

        // Düzenle (Edit) Butonu Dinleme - Server-Side tablolarda Delegation ZORUNLUDUR!
        $('#epinLoadTable tbody').on('click', '.epin-load-edit', function() {
            var $row = $(this).closest('tr');

            // TD içindeki data attributelarından veya classlardan raw veriyi oku
            var loadId = $(this).data('epin-load-id');
            var loadCode = $row.find('span[data-epin-load-code]').data('epin-load-code') || '';
            
            // AJAX PHP'de gizli inputlarla bastığımız değerleri alıyoruz
            var purchaseCost = $row.find('input.epin-load-purchase-cost').val() || '';
            var purchaseCurrency = $row.find('input.epin-load-purchase-currency').val() || '';
            var purchaseRate = $row.find('span[data-epin-load-purchase-rate]').data('epin-load-purchase-rate') || '';

            // Formu Doldur
            $('#epinLoadIdInput').val(loadId);
            $('#epinLoadCodeInput').val(loadCode);
            $('#epinPoolCostInput').val(purchaseCost);
            $('#epinLoadCostCurrencySelect').val(purchaseCurrency).trigger('change');
            $('#epinLoadPurchaseRateInput').val(purchaseRate);

            $('#epinLoadDetailModal').modal('show');
        });
    });
</script>