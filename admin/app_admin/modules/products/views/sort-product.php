<div >
<div class="d-flex align-items-center mb-4">
    <div class="w-50">
        Kategori:
        <select name="categoryID" class="form-select categories form-select-solid" id="categorySelect">
            <option value="">Kategori Seçiniz</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category->category_id ?>">
                    <?= $category->category_is_main ? '' : '— ' ?><?= $category->category_title ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
        <span class="badge badge-light-primary fs-7" id="sortModeLabel">
            <i class="fas fa-arrows-alt me-1"></i> Ürün Sıralaması
        </span>
    </div>
</div>

<div class="card card-flush">
    <div class="card-header border-0 pt-6 pb-4">
        <div class="card-title">
            <h4 class="mb-0" id="sortTitle">
                <i class="fas fa-grip-vertical text-muted me-2"></i>
                Ürün Sıralaması
            </h4>
        </div>
        <div class="card-toolbar">
            <span class="text-muted fs-7">
                <i class="fas fa-info-circle me-1"></i> Sıralamayı değiştirmek için öğeleri sürükleyip bırakın
            </span>
        </div>
    </div>
    <div class="card-body pt-0">

        <!-- Başlangıç durumu: Kategori seçilmemiş -->
        <div id="initialState" class="text-center py-10">
            <i class="fas fa-hand-pointer text-gray-300 fs-3x mb-3 d-block"></i>
            <p class="text-muted fs-6">Ürünleri sıralamak için yukarıdan bir kategori seçin.</p>
        </div>

        <!-- Ürün listesi -->
        <div id="productSortableList" class="sortable-container" style="display:none;"></div>

        <!-- Boş durum -->
        <div id="emptyState" style="display:none;" class="text-center py-10">
            <i class="fas fa-box-open text-gray-300 fs-3x mb-3 d-block"></i>
            <p class="text-muted fs-6">Bu kategoriye ait ürün bulunamadı.</p>
        </div>

        <!-- Yükleniyor durumu -->
        <div id="loadingState" style="display:none;" class="text-center py-10">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
            <p class="text-muted fs-6 mt-3">Ürünler yükleniyor...</p>
        </div>
    </div>
</div>

<style>
    .sortable-container {
        min-height: 100px;
    }

    .sortable-item {
        background: var(--bs-card-bg, #1e1e2d);
        border: 1px solid var(--bs-border-color, #2d2d3f);
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 8px;
        cursor: grab;
        transition: all 0.2s ease;
        user-select: none;
    }

    .sortable-item:hover {
        border-color: var(--bs-primary, #3699ff);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        background: var(--bs-gray-200, #2b2b40);
    }

    .sortable-item:active {
        cursor: grabbing;
    }

    .sortable-item.sortable-ghost {
        opacity: 0.4;
        background: var(--bs-gray-300, #323248);
        border: 2px dashed var(--bs-primary, #3699ff);
    }

    .sortable-item.sortable-chosen {
        box-shadow: 0 4px 16px rgba(54, 153, 255, 0.25);
        border-color: var(--bs-primary, #3699ff);
        background: var(--bs-gray-200, #2b2b40);
    }

    .sortable-item.sortable-drag {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.35);
        transform: scale(1.02);
    }

    .drag-handle {
        cursor: grab;
        padding: 4px 2px;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    .sort-order-badge .badge-circle {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .symbol img {
        width: 45px;
        height: 45px;
        object-fit: cover;
    }

    .sortable-item.sort-saved {
        border-color: var(--bs-success, #50cd89);
        background: rgba(80, 205, 137, 0.1);
    }

    @keyframes sortSaved {
        0% { background-color: rgba(80, 205, 137, 0.15); }
        100% { background-color: var(--bs-card-bg, #1e1e2d); }
    }

    .sort-flash {
        animation: sortSaved 0.8s ease-out;
    }
</style>

</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    let sortableInstance = null;

    // Select2 init
    $('#categorySelect').select2({
        placeholder: 'Kategori Seçiniz',
        allowClear: true
    });

    // Badge numaralarını güncelle
    function updateBadgeNumbers() {
        $('#productSortableList').find('.sortable-item').each(function(index) {
            $(this).find('.sort-order-badge .badge').text(index + 1);
            $(this).find('.sort-value').text(index + 1);
        });
    }

    // Sortable init
    function initSortable() {
        if (sortableInstance) sortableInstance.destroy();

        sortableInstance = new Sortable(document.getElementById('productSortableList'), {
            animation: 200,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
            onEnd: function(evt) {
                updateBadgeNumbers();
                saveSortOrder();
            }
        });
    }

    // Sıralamayı kaydet (AJAX)
    function saveSortOrder() {
        const sortData = [];
        $('#productSortableList').find('.sortable-item').each(function(index) {
            sortData.push({
                product_id: $(this).data('id'),
                sort_order: index + 1
            });
        });

        $.ajax({
            url: '<?= base_url('products/updateProductSort') ?>',
            type: 'POST',
            data: { sort_data: sortData },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                    $('#productSortableList').find('.sortable-item').each(function() {
                        $(this).addClass('sort-flash');
                        setTimeout(() => {
                            $(this).removeClass('sort-flash');
                        }, 800);
                    });
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Sıralama kaydedilirken bir hata oluştu.');
            }
        });
    }

    // Ürün HTML'i oluştur
    function buildProductHTML(products) {
        let html = '';
        products.forEach(function(product, index) {
            const statusBadge = product.product_status == 1
                ? '<span class="badge badge-light-success fs-8 ms-1">Aktif</span>'
                : '<span class="badge badge-light-danger fs-8 ms-1">Pasif</span>';

            const imgSrc = product.product_image
                ? '<?= getenv('CDN_URL') ?>' + product.product_image
                : '<?= topup_url('assets/img/no-image.webp') ?>';

            html += `
                <div class="sortable-item" data-id="${product.product_id}">
                    <div class="d-flex align-items-center">
                        <div class="drag-handle me-3">
                            <i class="fas fa-grip-vertical text-gray-400 fs-3"></i>
                        </div>
                        <div class="sort-order-badge me-3">
                            <span class="badge badge-circle badge-light-primary fw-bold fs-7">${index + 1}</span>
                        </div>
                        <div class="symbol symbol-45px me-3">
                            <img src="${imgSrc}"
                                 alt="${product.product_name}"
                                 onerror="this.onerror=null;this.src='<?= topup_url('assets/img/no-image.webp') ?>';"
                                 class="rounded">
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800 fs-6">${product.product_name}</span>
                            <span class="text-muted fs-7">#${product.product_id} ${statusBadge}</span>
                        </div>
                        <div class="ms-auto">
                            <span class="text-muted fs-8">Sıra: <strong class="sort-value">${product.product_sort_id ?? 0}</strong></span>
                        </div>
                    </div>
                </div>
            `;
        });
        return html;
    }

    // Kategori seçildiğinde ürünleri yükle
    $('#categorySelect').on('change', function() {
        const categoryId = $(this).val();

        if (!categoryId) {
            $('#productSortableList').hide().empty();
            $('#emptyState').hide();
            $('#loadingState').hide();
            $('#initialState').show();
            return;
        }

        const selectedText = $(this).find('option:selected').text().trim();

        // UI güncelle
        $('#sortTitle').html('<i class="fas fa-grip-vertical text-muted me-2"></i> ' + selectedText + ' - Ürün Sıralaması');
        $('#sortModeLabel').html('<i class="fas fa-boxes me-1"></i> ' + selectedText);

        // Yükleniyor
        $('#initialState').hide();
        $('#productSortableList').hide();
        $('#emptyState').hide();
        $('#loadingState').show();

        // AJAX ile ürünleri çek
        $.ajax({
            url: '<?= base_url('products/getProductsByCategory') ?>',
            type: 'POST',
            data: { category_id: categoryId },
            dataType: 'json',
            success: function(response) {
                $('#loadingState').hide();

                if (response.status === 'success' && response.data.length > 0) {
                    const html = buildProductHTML(response.data);
                    $('#productSortableList').html(html).show();
                    initSortable();
                } else {
                    $('#emptyState').show();
                }
            },
            error: function() {
                $('#loadingState').hide();
                toastr.error('Ürünler yüklenirken bir hata oluştu.');
            }
        });
    });
});
</script>
