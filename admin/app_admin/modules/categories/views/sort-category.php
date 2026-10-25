<div >
<div class="d-flex align-items-center mb-4">
    <div class="w-50">
        Kategori:
        <select name="parentCategoryID" class="form-select parent-categories form-select-solid" id="parentCategorySelect">
            <option value="">Ana Kategoriler (Sıralama)</option>
            <?php foreach ($mainCategories as $category): ?>
                <option value="<?= $category->category_id ?>"><?= $category->category_title ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
        <span class="badge badge-light-primary fs-7" id="sortModeLabel">
            <i class="fas fa-arrows-alt me-1"></i> Ana Kategori Sıralaması
        </span>
        <button class="btn btn-info btn-sm" id="resetSortBtn" style="display:none;">
            <i class="fas fa-undo me-1"></i> Ana Kategorilere Dön
        </button>
    </div>
</div>

<div class="card card-flush">
    <div class="card-header border-0 pt-6 pb-4">
        <div class="card-title">
            <h4 class="mb-0" id="sortTitle">
                <i class="fas fa-grip-vertical text-muted me-2"></i>
                Ana Kategori Sıralaması
            </h4>
        </div>
        <div class="card-toolbar">
            <span class="text-muted fs-7">
                <i class="fas fa-info-circle me-1"></i> Sıralamayı değiştirmek için öğeleri sürükleyip bırakın
            </span>
        </div>
    </div>
    <div class="card-body pt-0">
        <div id="sortableList" class="sortable-container">
            <?php foreach ($mainCategories as $index => $category): ?>
                <div class="sortable-item" data-id="<?= $category->category_id ?>">
                    <div class="d-flex align-items-center">
                        <div class="drag-handle me-3">
                            <i class="fas fa-grip-vertical text-gray-400 fs-3"></i>
                        </div>
                        <div class="sort-order-badge me-3">
                            <span class="badge badge-circle badge-light-primary fw-bold fs-7"><?= $index + 1 ?></span>
                        </div>
                        <div class="symbol symbol-45px me-3">
                            <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $category->category_image ?>"
                                 alt="<?= $category->category_title ?>"
                                 onerror="this.onerror=null;this.src='<?= topup_url('assets/img/no-image.webp') ?>';"
                                 class="rounded">
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800 fs-6"><?= $category->category_title ?></span>
                            <span class="text-muted fs-7">#<?= $category->category_id ?>
                                <?php if ($category->category_status == 1): ?>
                                    <span class="badge badge-light-success fs-8 ms-1">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-light-danger fs-8 ms-1">Pasif</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="ms-auto">
                            <span class="text-muted fs-8">Sıra: <strong class="sort-value"><?= $category->category_sort_id ?? 0 ?></strong></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- AJAX ile yüklenen alt kategoriler buraya gelecek -->
        <div id="subSortableList" class="sortable-container" style="display:none;"></div>

        <!-- Boş durum -->
        <div id="emptyState" style="display:none;" class="text-center py-10">
            <i class="fas fa-folder-open text-gray-300 fs-3x mb-3 d-block"></i>
            <p class="text-muted fs-6">Bu kategoriye ait alt kategori bulunamadı.</p>
        </div>

        <!-- Yükleniyor durumu -->
        <div id="loadingState" style="display:none;" class="text-center py-10">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
            <p class="text-muted fs-6 mt-3">Alt kategoriler yükleniyor...</p>
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
    let currentMode = 'main'; // 'main' veya 'sub'
    let currentParentId = null;
    let sortableInstance = null;
    let subSortableInstance = null;

    // Select2 init
    $('#parentCategorySelect').select2({
        placeholder: 'Ana Kategoriler (Sıralama)',
        allowClear: true
    });

    // Ana kategoriler için Sortable init
    initMainSortable();

    function initMainSortable() {
        if (sortableInstance) sortableInstance.destroy();

        sortableInstance = new Sortable(document.getElementById('sortableList'), {
            animation: 200,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
            onEnd: function(evt) {
                updateBadgeNumbers('#sortableList');
                saveSortOrder('#sortableList');
            }
        });
    }

    function initSubSortable() {
        if (subSortableInstance) subSortableInstance.destroy();

        subSortableInstance = new Sortable(document.getElementById('subSortableList'), {
            animation: 200,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
            onEnd: function(evt) {
                updateBadgeNumbers('#subSortableList');
                saveSortOrder('#subSortableList');
            }
        });
    }

    // Badge numaralarını güncelle
    function updateBadgeNumbers(containerSelector) {
        $(containerSelector).find('.sortable-item').each(function(index) {
            $(this).find('.sort-order-badge .badge').text(index + 1);
            $(this).find('.sort-value').text(index + 1);
        });
    }

    // Sıralamayı kaydet (AJAX)
    function saveSortOrder(containerSelector) {
        const sortData = [];
        $(containerSelector).find('.sortable-item').each(function(index) {
            sortData.push({
                category_id: $(this).data('id'),
                sort_order: index + 1
            });
        });

        $.ajax({
            url: '<?= base_url('categories/updateCategorySort') ?>',
            type: 'POST',
            data: { sort_data: sortData },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                    // Kayıt animasyonu
                    $(containerSelector).find('.sortable-item').each(function() {
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

    // Alt kategori HTML'i oluştur
    function buildSubCategoryHTML(categories) {
        let html = '';
        categories.forEach(function(cat, index) {
            const statusBadge = cat.category_status == 1
                ? '<span class="badge badge-light-success fs-8 ms-1">Aktif</span>'
                : '<span class="badge badge-light-danger fs-8 ms-1">Pasif</span>';

            const imgSrc = cat.category_image
                ? '<?= getenv('CDN_URL') ?>uploads/categories/' + cat.category_image
                : '<?= topup_url('assets/img/no-image.webp') ?>';

            html += `
                <div class="sortable-item" data-id="${cat.category_id}">
                    <div class="d-flex align-items-center">
                        <div class="drag-handle me-3">
                            <i class="fas fa-grip-vertical text-gray-400 fs-3"></i>
                        </div>
                        <div class="sort-order-badge me-3">
                            <span class="badge badge-circle badge-light-primary fw-bold fs-7">${index + 1}</span>
                        </div>
                        <div class="symbol symbol-45px me-3">
                            <img src="${imgSrc}"
                                 alt="${cat.category_title}"
                                 onerror="this.onerror=null;this.src='<?= topup_url('assets/img/no-image.webp') ?>';"
                                 class="rounded">
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800 fs-6">${cat.category_title}</span>
                            <span class="text-muted fs-7">#${cat.category_id} ${statusBadge}</span>
                        </div>
                        <div class="ms-auto">
                            <span class="text-muted fs-8">Sıra: <strong class="sort-value">${cat.category_sort_id ?? 0}</strong></span>
                        </div>
                    </div>
                </div>
            `;
        });
        return html;
    }

    // Ana kategori seçildiğinde alt kategorileri yükle
    $('#parentCategorySelect').on('change', function() {
        const parentId = $(this).val();

        if (!parentId) {
            // Ana kategorilere dön
            showMainCategories();
            return;
        }

        currentParentId = parentId;
        currentMode = 'sub';
        const selectedText = $(this).find('option:selected').text();

        // UI güncelle
        $('#sortTitle').html('<i class="fas fa-grip-vertical text-muted me-2"></i> ' + selectedText + ' - Alt Kategori Sıralaması');
        $('#sortModeLabel').html('<i class="fas fa-sitemap me-1"></i> Alt Kategori Sıralaması');
        $('#resetSortBtn').show();

        // Yükleniyor
        $('#sortableList').hide();
        $('#subSortableList').hide();
        $('#emptyState').hide();
        $('#loadingState').show();

        // AJAX ile alt kategorileri çek
        $.ajax({
            url: '<?= base_url('categories/getSubCategories') ?>',
            type: 'POST',
            data: { parent_id: parentId },
            dataType: 'json',
            success: function(response) {
                $('#loadingState').hide();

                if (response.status === 'success' && response.data.length > 0) {
                    const html = buildSubCategoryHTML(response.data);
                    $('#subSortableList').html(html).show();
                    initSubSortable();
                } else {
                    $('#emptyState').show();
                }
            },
            error: function() {
                $('#loadingState').hide();
                toastr.error('Alt kategoriler yüklenirken bir hata oluştu.');
            }
        });
    });

    // Ana kategorilere dön butonu
    $('#resetSortBtn').on('click', function() {
        $('#parentCategorySelect').val('').trigger('change');
    });

    function showMainCategories() {
        currentMode = 'main';
        currentParentId = null;

        $('#sortTitle').html('<i class="fas fa-grip-vertical text-muted me-2"></i> Ana Kategori Sıralaması');
        $('#sortModeLabel').html('<i class="fas fa-arrows-alt me-1"></i> Ana Kategori Sıralaması');
        $('#resetSortBtn').hide();
        $('#loadingState').hide();
        $('#emptyState').hide();
        $('#subSortableList').hide().empty();
        $('#sortableList').show();
    }
});
</script>
