<style>
    /* Modern Tablo Hover */
    table.dataTable tbody tr:hover {
        background-color: var(--bs-light-primary) !important;
        transition: background-color 0.2s ease;
    }

    /* Epin Ürün Kartları */
    .epin-product-badge {
        background-color: var(--bs-gray-100);
        border: 1px solid var(--bs-gray-200);
        border-radius: 8px;
        padding: 4px 10px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--bs-gray-700);
        transition: all 0.2s ease;
    }

    .epin-product-badge:hover {
        border-color: var(--bs-primary);
        color: var(--bs-primary);
        background-color: var(--bs-light-primary);
    }

    .epin-product-badge img {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        object-fit: cover;
    }
</style>

<div class="row">
    <div class="card shadow-sm border-0 mb-12 card-flush">
        <div class="card-header border-0 pt-6">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1 text-gray-900">Stok Havuzu Yönetimi</span>
                <span class="text-muted fw-semibold fs-7">Sistemdeki epin havuzları ve detaylı stok durumları</span>
            </h3>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light-success border border-success border-dashed rounded py-2 px-4 me-3">
                        <span class="fs-6 fw-bold text-gray-700">Toplam Maliyet:</span>
                        <span class="fs-5 fw-bolder text-success ms-2 stock-cost">
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </span>
                    </div>
                    <button class="btn btn-sm btn-primary epin-pool-add fw-bold">
                        <i class="ki-outline ki-plus fs-2"></i> Havuz Ekle
                    </button>
                    <a class="btn btn-sm btn-success fw-bold" href="<?= base_url('epin/addPins') ?>">
                        <i class="ki-outline ki-plus fs-2"></i> Stok Ekle
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body py-4">

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="w-100">
                        <label class="form-label fw-semibold text-gray-700 fs-7">Kategori Filtresi</label>
                        <select class="form-select form-select-sm form-select-solid select-2" id="categoryFilter" data-placeholder="Tüm Kategoriler" data-allow-clear="true">
                            <option value=""></option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->category_id ?>"><?= $category->category_title ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="w-100">
                        <label class="form-label fw-semibold text-gray-700 fs-7">Ürün Filtresi</label>
                        <select class="form-select form-select-sm form-select-solid select-2" id="productFilter" data-placeholder="Tüm Ürünler" data-allow-clear="true">
                            <option value=""></option>
                            <?php foreach ($epin_pools as $product): ?>
                                <option value="<?= $product->epin_pool_id ?>"><?= $product->epin_pool_title ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="epinPoolTable" class="table align-middle table-row-dashed table-hover fs-6 gy-5 w-100">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-50px text-center">ID</th>
                            <th class="min-w-150px">Havuz Adı</th>
                            <th class="min-w-300px">Bağlı Ürünler</th>
                            <th class="text-center min-w-100px">Mevcut Stok</th>
                            <th class="text-end min-w-100px">Birim Maliyet</th>
                            <th class="text-end min-w-125px">Toplam Maliyet</th>
                            <th class="text-center min-w-125px">Ort. Satış/Gün</th>
                            <th class="text-end min-w-100px">İşlemler</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="epinPoolAddModal" tabindex="-1" aria-labelledby="epinPoolAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="epinPoolAddModalLabel">Epin Kategorisi Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" id="epinPoolAddModalContent">
                <form action="<?= base_url('epin/addPoolItem') ?>" method="post">
                    <input type="hidden" name="epin_pool_id" value="">

                    <h4 class="mt-5">Epin Havuz Adı</h4>
                    <input type="text" name="epin_pool_title" class="form-control form-control-solid mb-2" placeholder="Örn: Valorant VP Havuzu">

                    <h4 class="mt-5">Epin Kategorisi</h4>
                    <div class="fv-row mb-8">
                        <select class="form-select form-select-solid select-3" name="epin_pool_category_id" data-placeholder="Kategori Seçiniz">
                            <option value=""></option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->category_id ?>"><?= $category->category_title ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h4 class="mt-5">Bağlı Ürünler</h4>
                    <div class="fv-row mb-8">
                        <select class="products form-select form-select-solid" name="epin_pool_products[]" multiple="multiple" data-placeholder="Ürün Seçiniz (Çoklu)">
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product->product_id ?>" data-image="<?= $product->product_image ?>">
                                    <?= $product->product_name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-muted fs-7 mt-2">Bu havuza ait olan oyun/epin ürünlerini seçiniz.</p>

                        <h4 class="mt-5">Birim Maliyeti</h4>
                        <input type="text" name="epin_pool_cost" class="form-control form-control-solid mb-2 pool-cost" placeholder="Örn: 15.50">
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Select2 Inits
        $('.select-2, .select-3').select2();

        // Modal tetiklendiğinde Select2'yi modal'a bağlayarak başlat
        $('#epinPoolAddModal').on('shown.bs.modal', function() {
            $('.select-3, .products').select2({
                dropdownParent: $('#epinPoolAddModal') // <--- Kritik dokunuş burası
            });
        });

        // Ürün Select2 Custom Template
        $('.products').select2({
            templateResult: function(option) {
                if (!option.id) return option.text;
                const imgSrc = $(option.element).data('image');
                if (!imgSrc) return option.text;
                return $(`<div class="d-flex align-items-center"><img src="${imgSrc}" class="me-2 rounded" width="24" height="24" style="object-fit:cover;"><span>${option.text}</span></div>`);
            },
            templateSelection: function(option) {
                if (!option.id) return option.text;
                const imgSrc = $(option.element).data('image');
                if (!imgSrc) return option.text;
                return $(`<div class="d-flex align-items-center"><img src="${imgSrc}" class="me-2 rounded" width="18" height="18" style="object-fit:cover;"><span>${option.text}</span></div>`);
            }
        });

        // Metronic 8 DataTables Konfigürasyonu
        var tbl = $('#epinPoolTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            searching: false, // AJAX server-side arama yazılmadığı için kapattık, istersen eklersin
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                url: "//cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json",
                processing: `
                    <div class="d-flex flex-column align-items-center justify-content-center py-10 bg-body rounded shadow-sm">
                        <div class="spinner-border text-primary border-4" style="width: 3rem; height: 3rem;" role="status"></div>
                        <div class="text-muted fw-semibold mt-3 fs-6">Kayıtlar getiriliyor...</div>
                    </div>`
            },
            dom: "<'table-responsive'tr><'row align-items-center mt-5'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
            ajax: {
                url: "<?= site_url('epin/epinPoolAjax') ?>",
                type: "POST",
                data: function(d) {
                    d.categoryID = $('#categoryFilter').val();
                    d.productID = $('#productFilter').val();
                }
            }
        });

        // AJAX isteği bittiğinde Toplam Maliyeti (Global) güncelle
        tbl.on('xhr', function() {
            var json = tbl.ajax.json();
            if (json && json.globalTotalCost) {
                $('.stock-cost').text(json.globalTotalCost);
            }
        });

        // Kategori Filtresi Değişince Tabloyu Yenile (Sayfa yenilenmez!)
        $('#categoryFilter').change(function() {
            tbl.ajax.reload();
        });

        // Ürün Filtresi Değişince Tabloyu Yenile (Sayfa yenilenmez!)
        $('#productFilter').change(function() {
            tbl.ajax.reload();
        });

        // Yeni Havuz Ekle Butonu
        $('.epin-pool-add').click(function(e) {
            e.preventDefault(); // Sayfa üste kaymasın
            $('#epinPoolAddModalLabel').text('Epin Kategorisi Ekle');
            $('#epinPoolAddModalContent form').attr('action', '<?= base_url('epin/addPoolItem') ?>');
            $('#epinPoolAddModalContent input[name="epin_pool_id"]').val('');
            $('#epinPoolAddModalContent input[name="epin_pool_title"]').val('');
            $('#epinPoolAddModalContent input[name="epin_pool_cost"]').val('');
            $('#epinPoolAddModalContent .select-3').val('').trigger('change');
            $('#epinPoolAddModalContent .products').val(null).trigger('change');
            $('#epinPoolAddModal').modal('show');
        });

        // Düzenle Butonu (Delegation ile)
        $('#epinPoolTable tbody').on('click', '.edit-pool', function(e) {
            e.preventDefault();

            // Verileri DOM yerine temiz data attribute'lardan alıyoruz
            var poolId = $(this).data('pool-id');
            var poolTitle = $(this).data('pool-title');
            var poolCost = $(this).data('pool-cost');
            var categoryId = $(this).data('category-id');
            var productIds = $(this).data('products'); // Array olarak gelir

            $('#epinPoolAddModalLabel').text('Epin Havuzunu Düzenle');
            $('#epinPoolAddModalContent form').attr('action', '<?= base_url('epin/updatePoolItem') ?>/' + poolId);

            $('#epinPoolAddModalContent input[name="epin_pool_id"]').val(poolId);
            $('#epinPoolAddModalContent input[name="epin_pool_title"]').val(poolTitle);
            $('#epinPoolAddModalContent input[name="epin_pool_cost"]').val(poolCost);

            $('#epinPoolAddModalContent .select-3').val(categoryId).trigger('change');
            $('#epinPoolAddModalContent .products').val(productIds).trigger('change');

            $('#epinPoolAddModal').modal('show');
        });

        // Maliyet virgül düzeltme
        $('.pool-cost').on('input', function() {
            $(this).val($(this).val().replace(',', '.'));
        });
    });
</script>