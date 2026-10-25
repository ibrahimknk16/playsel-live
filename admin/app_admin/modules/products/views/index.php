<?php
$filterOptions = isset($filter_options) && is_array($filter_options) ? $filter_options : [];
$categoryOptions = $filterOptions['categories'] ?? [];
$supplierOptions = $filterOptions['suppliers'] ?? [];
$productTypeOptions = $filterOptions['product_types'] ?? [];
$categoryTypeOptions = $filterOptions['category_types'] ?? [];
$countryOptions = $filterOptions['countries'] ?? [];
$costCurrencyOptions = $filterOptions['cost_currencies'] ?? [];
$priceCurrencyOptions = $filterOptions['price_currencies'] ?? [];
$productStatusOptions = $filterOptions['product_statuses'] ?? [];
$productApiStatusOptions = $filterOptions['product_api_statuses'] ?? [];
$categoryApiStatusOptions = $filterOptions['category_api_statuses'] ?? [];

$currencyOptions = [];
foreach ($costCurrencyOptions as $currency) {
    $currencyOptions[$currency] = $currency;
}
foreach ($priceCurrencyOptions as $currency) {
    $currencyOptions[$currency] = $currency;
}
ksort($currencyOptions);

$productCounts = isset($product_counts) && is_array($product_counts)
    ? $product_counts
    : [
        'total' => isset($products) && is_array($products) ? count($products) : 0,
        'active' => 0,
        'passive' => 0,
    ];
?>

<style>
    #productTable tbody tr td {
        padding: 0.65rem;
        vertical-align: middle;
    }

    .filter-shell {
        background: rgba(var(--bs-dark-rgb), 0.05);
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        padding: 1rem;
    }

    .result-chip-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .p-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .p-symbol {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--bs-dark-rgb), 0.08);
        flex: 0 0 40px;
    }

    .p-symbol img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        display: block;
    }

    .p-info {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .p-name a {
        color: inherit;
        text-decoration: none;
    }

    .p-name a:hover {
        text-decoration: underline;
    }

    .p-sub {
        margin-top: 4px;
        font-size: 12px;
        color: var(--bs-secondary-color);
    }

    .dot {
        margin: 0 6px;
        opacity: .6;
    }

    .status-wrap {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }

    .actions-col {
        white-space: nowrap;
        width: 1%;
    }

    .actions-wrap {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        align-items: center;
    }
</style>

<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Ürünler</h3>

            <div class="card-toolbar">
                <a href="<?= site_url('products/add') ?>" class="btn btn-sm btn-primary">
                    <i class="ki-duotone ki-plus fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                    Ürün Ekle
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="filter-shell mb-6">
                <div class="row g-3 align-items-end" id="productFilters">
                    <div class="col-12 col-lg-4 col-xl-3">
                        <label class="form-label mb-1">Detaylı Arama</label>
                        <input type="text" class="form-control form-control-sm form-control-solid" id="filterKeyword" placeholder="Ürün, kategori, kod, provider...">
                    </div>

                    <div class="col-6 col-lg-4 col-xl-2">
                        <label class="form-label mb-1">Kategori</label>
                        <select class="form-select form-select-sm form-select-solid" id="filterCategory">
                            <option value="">Tüm Kategoriler</option>
                            <?php foreach ($categoryOptions as $category): ?>
                                <option value="<?= (int)$category['id'] ?>">
                                    <?= html_escape($category['title']) ?> #<?= (int)$category['id'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-lg-4 col-xl-2">
                        <label class="form-label mb-1">Ürün Tipi</label>
                        <select class="form-select form-select-sm form-select-solid" id="filterProductType">
                            <option value="">Hepsi</option>
                            <?php foreach ($productTypeOptions as $typeItem): ?>
                                <option value="<?= html_escape($typeItem['value']) ?>">
                                    <?= html_escape($typeItem['label']) ?> (<?= html_escape($typeItem['value']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-lg-4 col-xl-2">
                        <label class="form-label mb-1">Kategori Tipi</label>
                        <select class="form-select form-select-sm form-select-solid" id="filterCategoryType">
                            <option value="">Hepsi</option>
                            <?php foreach ($categoryTypeOptions as $typeItem): ?>
                                <option value="<?= html_escape($typeItem['value']) ?>">
                                    <?= html_escape($typeItem['label']) ?> (<?= html_escape($typeItem['value']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-lg-4 col-xl-2">
                        <label class="form-label mb-1">Tedarikçi</label>
                        <select class="form-select form-select-sm form-select-solid" id="filterSupplier">
                            <option value="">Tüm Tedarikçiler</option>
                            <?php foreach ($supplierOptions as $supplier): ?>
                                <option value="<?= (int)$supplier['id'] ?>">
                                    <?= html_escape($supplier['name']) ?> (#<?= (int)$supplier['id'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-lg-4 col-xl-2">
                        <label class="form-label mb-1">Ürün Durumu</label>
                        <select class="form-select form-select-sm form-select-solid" id="filterStatus">
                            <option value="">Hepsi</option>
                            <?php foreach ($productStatusOptions as $statusValue => $statusLabel): ?>
                                <option value="<?= html_escape($statusValue) ?>"><?= html_escape($statusLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-lg-4 col-xl-2">
                        <label class="form-label mb-1">Ürün Stok Durumu</label>
                        <select class="form-select form-select-sm form-select-solid" id="filterProductApi">
                            <option value="">Hepsi</option>
                            <?php foreach ($productApiStatusOptions as $apiStatusValue => $apiStatusLabel): ?>
                                <option value="<?= html_escape($apiStatusValue) ?>"><?= html_escape($apiStatusLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-lg-4 col-xl-2">
                        <label class="form-label mb-1">Kategori API Durumu</label>
                        <select class="form-select form-select-sm form-select-solid" id="filterCategoryApi">
                            <option value="">Hepsi</option>
                            <?php foreach ($categoryApiStatusOptions as $apiStatusValue => $apiStatusLabel): ?>
                                <option value="<?= html_escape($apiStatusValue) ?>"><?= html_escape($apiStatusLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-lg-4 col-xl-1">
                        <label class="form-label mb-1">Ülke</label>
                        <select class="form-select form-select-sm form-select-solid" id="filterCountry">
                            <option value="">Hepsi</option>
                            <?php foreach ($countryOptions as $country): ?>
                                <option value="<?= html_escape(strtolower($country)) ?>"><?= html_escape($country) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-lg-4 col-xl-1">
                        <label class="form-label mb-1">Para Birimi</label>
                        <select class="form-select form-select-sm form-select-solid" id="filterCurrency">
                            <option value="">Hepsi</option>
                            <?php foreach ($currencyOptions as $currency): ?>
                                <option value="<?= html_escape(strtolower($currency)) ?>"><?= html_escape($currency) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-3 col-lg-2 col-xl-2 d-grid">
                        <button type="button" class="btn btn-sm btn-light" id="resetProductFilters">
                            Filtreleri Temizle
                        </button>
                    </div>
                </div>

                <div class="result-chip-wrap mt-3">
                    <span class="badge badge-light-primary">Toplam: <span id="totalCount"><?= (int)$productCounts['total'] ?></span></span>
                    <span class="badge badge-light-success">Aktif: <span id="activeCount"><?= (int)$productCounts['active'] ?></span></span>
                    <span class="badge badge-light-danger">Pasif: <span id="passiveCount"><?= (int)$productCounts['passive'] ?></span></span>
                    <span class="badge badge-light-warning">Filtre Sonucu: <span id="filteredCount"><?= (int)$productCounts['total'] ?></span></span>
                </div>
            </div>

            <div class="table-responsive">
                <table id="productTable" class="table align-middle table-row-dashed fs-6 gy-4">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-100px">Ürün ID</th>
                            <th class="min-w-320px">Ürün</th>
                            <th class="min-w-240px">Kategori</th>
                            <th class="min-w-240px">Alt Kategori</th>
                            <th class="min-w-220px">Durum</th>
                            <th class="text-end min-w-120px">İşlemler</th>
                        </tr>
                    </thead>

                    <tbody class="fw-semibold text-gray-600">
                        <?php foreach ($products as $product): ?>
                            <?php
                            $isActive = ((int)($product->product_status ?? 0) === 1);
                            $isApiActive = ((int)($product->product_api_status ?? 0) === 1);
                            $categoryTitle = trim((string)($product->category_title ?? '-'));
                            $subCategoryTitle = trim((string)($product->sub_category_title ?? 'Alt Kategori Yok'));
                            $supplierName = trim((string)($product->supplier_name ?? ''));
                            $countries = (!empty($product->search_countries) && is_array($product->search_countries)) ? $product->search_countries : [];
                            $costCurrency = trim((string)($product->search_cost_currency ?? ''));
                            $priceCurrency = trim((string)($product->search_price_currency ?? ''));
                            ?>

                            <tr
                                data-search="<?= html_escape($product->search_blob ?? '') ?>"
                                data-category-id="<?= (int)($product->category_id ?? 0) ?>"
                                data-supplier-id="<?= (int)($product->product_supplier_id ?? 0) ?>"
                                data-status="<?= (int)($product->product_status ?? 0) ?>"
                                data-product-api-status="<?= (int)($product->product_api_status ?? 0) ?>"
                                data-category-api-status="<?= (int)($product->category_api_status ?? 0) ?>"
                                data-product-type-id="<?= (int)($product->product_type ?? 0) ?>"
                                data-category-type-id="<?= (int)($product->category_type ?? 0) ?>"
                                data-country-list="<?= html_escape(implode(',', $countries)) ?>"
                                data-currency-list="<?= html_escape(trim($costCurrency . ',' . $priceCurrency, ',')) ?>">

                                <td>
                                    <span class="text-gray-800 fw-bold">#<?= (int)$product->product_id; ?></span>
                                </td>

                                <td>
                                    <div class="p-row">
                                        <div class="p-symbol">
                                            <?php if (!empty($product->product_image)): ?>
                                                <img src="<?= getenv('CDN_URL') . $product->product_image ?>" alt="">
                                            <?php else: ?>
                                                <div class="symbol-label bg-light-info text-info fw-bold">
                                                    <?= mb_strtoupper(mb_substr((string)$product->product_name, 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="p-info">
                                            <div class="p-name text-gray-900 fw-bold text-hover-primary">
                                                <a href="<?= base_url('products/edit/' . $product->product_id) ?>">
                                                    <?= $product->product_name ?: '-' ?>
                                                </a>
                                                <span class="text-muted fs-7">#<?= (int)$product->product_id ?></span>
                                            </div>

                                            <div class="p-sub">
                                                <?php if ($supplierName !== ''): ?>
                                                    <span class="badge badge-light-info"><?= html_escape($supplierName) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">Tedarikçi yok</span>
                                                <?php endif; ?>

                                                <?php if (!empty($product->provider_product_product_name)): ?>
                                                    <span class="dot">•</span>
                                                    <span><?= html_escape($product->provider_product_product_name) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="text-gray-800 fw-bold">
                                        <?= html_escape($categoryTitle !== '' ? $categoryTitle : '-') ?>
                                        <span class="text-muted">#<?= (int)($product->category_id ?? 0) ?></span>
                                    </div>
                                </td>

                                <td>
                                    <div class="text-gray-800 fw-bold">
                                        <?= html_escape($subCategoryTitle !== '' ? $subCategoryTitle : '-') ?>
                                        <span class="text-muted">#<?= (int)($product->category_id ?? 0) ?></span>
                                    </div>
                                </td>

                                <td>
                                    <div class="status-wrap mb-2">
                                        <span class="badge <?= $isActive ? 'badge-light-success' : 'badge-light-danger' ?> fw-semibold js-product-status-badge">
                                            <?= $isActive ? 'Aktif' : 'Pasif' ?>
                                        </span>

                                        <div class="form-check form-switch form-check-custom form-check-solid m-0">
                                            <input class="form-check-input js-product-status-switch"
                                                type="checkbox"
                                                data-product-id="<?= (int)$product->product_id ?>"
                                                <?= $isActive ? 'checked' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="status-wrap mb-2">
                                        <span class="badge <?= $isApiActive ? 'badge-light-success' : 'badge-light-danger' ?> fw-semibold js-product-api-status-badge">
                                            <?= $isApiActive ? 'API Aktif' : 'API Pasif' ?>
                                        </span>

                                        <div class="form-check form-switch form-check-custom form-check-solid m-0">
                                            <input class="form-check-input js-product-api-status-switch"
                                                type="checkbox"
                                                data-product-id="<?= (int)$product->product_id ?>"
                                                <?= $isApiActive ? 'checked' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="p-sub">
                                        Kategori API: <?= (int)($product->category_api_status ?? 0) === 1 ? 'Aktif' : 'Pasif' ?>
                                    </div>
                                </td>

                                <td class="text-end actions-col">
                                    <div class="actions-wrap">
                                        <a href="<?= base_url('products/edit/' . $product->product_id) ?>" class="btn btn-sm btn-light-primary">
                                            <i class="ki-duotone ki-pencil fs-5 me-1">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            Düzenle
                                        </a>
                                        <a href="<?= base_url('products/delete/' . $product->product_id) ?>" class="btn btn-sm btn-light-danger">
                                            <i class="ki-duotone ki-trash fs-5 me-1">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            Sil
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#productTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            pageLength: 25,
            lengthMenu: [[25, 50, 100, 250, 500], [25, 50, 100, 250, 500]],
            ajax: {
                url: '<?= site_url('products/ajaxList') ?>',
                type: 'POST',
                data: function(data) {
                    data.filterKeyword = $('#filterKeyword').val();
                    data.search.value = $('#filterKeyword').val() || data.search.value;
                    data.filterCategory = $('#filterCategory').val();
                    data.filterProductType = $('#filterProductType').val();
                    data.filterCategoryType = $('#filterCategoryType').val();
                    data.filterSupplier = $('#filterSupplier').val();
                    data.filterStatus = $('#filterStatus').val();
                    data.filterProductApi = $('#filterProductApi').val();
                    data.filterCategoryApi = $('#filterCategoryApi').val();
                    data.filterCountry = $('#filterCountry').val();
                    data.filterCurrency = $('#filterCurrency').val();
                },
                dataSrc: function(response) {
                    $('#totalCount').text(response.recordsTotal || 0);
                    $('#filteredCount').text(response.recordsFiltered || 0);
                    return response.data || [];
                },
                error: function() {
                    if (typeof toastr !== 'undefined') toastr.error('Ürünler yüklenemedi.');
                }
            },
            columns: [{}, {}, {}, {}, {}, {}],
            language: { url: 'https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json' },
            dom: 'rt<"d-flex justify-content-between align-items-center mt-4"lp><"clear">'
        });

        $('#productFilters').on('keyup change', 'input, select', function() {
            clearTimeout(window.productAjaxTimer);
            window.productAjaxTimer = setTimeout(function() {
                $('#productTable').DataTable().ajax.reload(null, true);
            }, 250);
        });
        $('#resetProductFilters').on('click', function() {
            $('#productFilters input').val('');
            $('#productFilters select').val('').trigger('change');
            $('#productTable').DataTable().ajax.reload(null, true);
        });
        return;

        const normalizeText = function(value) {
            return $.trim(String(value || '')).toLowerCase();
        };

        const containsText = function(haystack, needle) {
            if (!needle) {
                return true;
            }
            return normalizeText(haystack).indexOf(needle) !== -1;
        };

        const containsToken = function(listValue, token) {
            if (!token) {
                return true;
            }

            const tokenList = normalizeText(listValue).split(/[\s,|]+/).filter(Boolean);
            return tokenList.indexOf(token) !== -1;
        };

        const initSelect2ForAllSelects = function() {
            if (typeof $.fn.select2 === 'undefined') {
                return;
            }

            $('select').each(function() {
                const $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    return;
                }

                const isDataTableLength = $select.closest('.dataTables_length').length > 0;

                $select.select2({
                    width: isDataTableLength ? '80px' : '100%',
                    minimumResultsForSearch: isDataTableLength ? Infinity : 0,
                    dropdownAutoWidth: true
                });
            });
        };

        const $tableElement = $('#productTable');

        if (window.productAdminFilterFn) {
            const oldFilterIndex = $.fn.dataTable.ext.search.indexOf(window.productAdminFilterFn);
            if (oldFilterIndex !== -1) {
                $.fn.dataTable.ext.search.splice(oldFilterIndex, 1);
            }
        }

        window.productAdminFilterFn = function(settings, data, dataIndex) {
            if (!settings.nTable || settings.nTable.id !== 'productTable') {
                return true;
            }

            const rowMeta = settings.aoData[dataIndex];
            if (!rowMeta || !rowMeta.nTr) {
                return true;
            }

            const $row = $(rowMeta.nTr);

            const keyword = normalizeText($('#filterKeyword').val());
            const categoryId = $('#filterCategory').val();
            const productTypeId = $('#filterProductType').val();
            const categoryTypeId = $('#filterCategoryType').val();
            const supplierId = $('#filterSupplier').val();
            const status = $('#filterStatus').val();
            const productApiStatus = $('#filterProductApi').val();
            const categoryApiStatus = $('#filterCategoryApi').val();
            const country = normalizeText($('#filterCountry').val());
            const currency = normalizeText($('#filterCurrency').val());

            if (keyword && !containsText($row.data('search'), keyword)) {
                return false;
            }
            if (categoryId && String($row.data('category-id')) !== String(categoryId)) {
                return false;
            }
            if (productTypeId && String($row.data('product-type-id')) !== String(productTypeId)) {
                return false;
            }
            if (categoryTypeId && String($row.data('category-type-id')) !== String(categoryTypeId)) {
                return false;
            }
            if (supplierId && String($row.data('supplier-id')) !== String(supplierId)) {
                return false;
            }
            if (status !== '' && String($row.data('status')) !== String(status)) {
                return false;
            }
            if (productApiStatus !== '' && String($row.data('product-api-status')) !== String(productApiStatus)) {
                return false;
            }
            if (categoryApiStatus !== '' && String($row.data('category-api-status')) !== String(categoryApiStatus)) {
                return false;
            }
            if (country && !containsToken($row.data('country-list'), country)) {
                return false;
            }
            if (currency && !containsToken($row.data('currency-list'), currency)) {
                return false;
            }

            return true;
        };

        $.fn.dataTable.ext.search.push(window.productAdminFilterFn);

        const table = $tableElement.DataTable({
            paging: true,
            ordering: false,
            searching: true,
            pageLength: 25,
            lengthMenu: [
                [25, 50, 100, 250, 500],
                [25, 50, 100, 250, 500]
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json'
            },
            dom: 'rt<"d-flex justify-content-between align-items-center mt-4"lp><"clear">'
        });

        initSelect2ForAllSelects();

        const updateFilteredCount = function() {
            $('#filteredCount').text(table.rows({
                filter: 'applied'
            }).count());
        };

        $('#productFilters').on('keyup change', 'input, select', function() {
            table.draw();
        });

        $('#resetProductFilters').on('click', function() {
            const $container = $('#productFilters');
            $container.find('input[type="text"], input[type="search"]').val('');
            $container.find('select').val('').trigger('change');
            table.draw();
        });

        table.on('draw', function() {
            initSelect2ForAllSelects();
            updateFilteredCount();
        });

        table.draw();
    });

    $(document).on('change', '.js-product-status-switch', function() {
        const $sw = $(this);
        const productId = $sw.data('product-id');
        const newStatus = $sw.is(':checked') ? 1 : 0;

        if ($sw.data('busy')) {
            return;
        }
        $sw.data('busy', true).prop('disabled', true);

        $.ajax({
            url: '<?= site_url('products/changeStatus') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                product_id: productId,
                product_status: newStatus
            },
            success: function(res) {
                if (res && res.status === 'success') {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Güncellendi');
                    }

                    const $badge = $sw.closest('td').find('.js-product-status-badge');
                    $badge
                        .removeClass('badge-light-success badge-light-danger')
                        .addClass(newStatus === 1 ? 'badge-light-success' : 'badge-light-danger')
                        .text(newStatus === 1 ? 'Aktif' : 'Pasif');

                    const $row = $sw.closest('tr');
                    $row.attr('data-status', newStatus);
                } else {
                    $sw.prop('checked', !newStatus);
                    if (typeof toastr !== 'undefined') {
                        toastr.error((res && res.message) ? res.message : 'Güncellenemedi');
                    }
                }
            },
            error: function() {
                $sw.prop('checked', !newStatus);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Sunucu hatası. Tekrar deneyin.');
                }
            },
            complete: function() {
                $sw.data('busy', false).prop('disabled', false);
            }
        });
    });

    $(document).on('change', '.js-product-api-status-switch', function() {
        const $sw = $(this);
        const productId = $sw.data('product-id');
        const newApiStatus = $sw.is(':checked') ? 1 : 0;

        if ($sw.data('busy')) {
            return;
        }

        $sw.data('busy', true).prop('disabled', true);

        $.ajax({
            url: '<?= site_url('products/changeApiStatus') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                product_id: productId,
                product_api_status: newApiStatus
            },
            success: function(res) {
                if (res && res.status === 'success') {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Güncellendi');
                    }

                    const $cell = $sw.closest('td');
                    const $badge = $cell.find('.js-product-api-status-badge');
                    $badge
                        .removeClass('badge-light-success badge-light-danger')
                        .addClass(newApiStatus === 1 ? 'badge-light-success' : 'badge-light-danger')
                        .text(newApiStatus === 1 ? 'API Aktif' : 'API Pasif');

                    const $row = $sw.closest('tr');
                    $row.attr('data-product-api-status', newApiStatus);

                    if ($.fn.DataTable.isDataTable('#productTable')) {
                        $('#productTable').DataTable().draw(false);
                    }
                } else {
                    $sw.prop('checked', !newApiStatus);
                    if (typeof toastr !== 'undefined') {
                        toastr.error((res && res.message) ? res.message : 'Güncellenemedi');
                    }
                }
            },
            error: function() {
                $sw.prop('checked', !newApiStatus);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Sunucu hatası. Tekrar deneyin.');
                }
            },
            complete: function() {
                $sw.data('busy', false).prop('disabled', false);
            }
        });
    });
</script>