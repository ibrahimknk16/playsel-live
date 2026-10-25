<?php
$alert = $this->session->flashdata('alert');
if (!empty($alert) && is_array($alert)):
    $alertType = $alert['type'] ?? 'info';
    $toastClass = 'ps-toast-info';
    $toastIcon = 'fa-solid fa-circle-info';
    if ($alertType === 'error' || $alertType === 'danger') {
        $toastClass = 'ps-toast-error';
        $toastIcon = 'fa-solid fa-circle-exclamation';
    } elseif ($alertType === 'warning') {
        $toastClass = 'ps-toast-warning';
        $toastIcon = 'fa-solid fa-triangle-exclamation';
    } elseif ($alertType === 'success') {
        $toastClass = 'ps-toast-success';
        $toastIcon = 'fa-solid fa-circle-check';
    }
?>
    <div class="ps-toast-container">
        <div class="ps-toast <?= $toastClass ?>">
            <div class="ps-toast-icon">
                <i class="<?= $toastIcon ?>"></i>
            </div>
            <div class="ps-toast-message">
                <?= $alert['message']; ?>
            </div>
            <button type="button" class="ps-toast-close" onclick="this.parentElement.remove();" title="Kapat">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<script>
    const toasts = document.querySelectorAll('.ps-toast');
    toasts.forEach(toast => {
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => {
                if (toast.parentElement) toast.parentElement.remove();
            }, 400);
        }, 5000);
    });
</script>
<?php if (!$this->agent->is_mobile() && !$this->session->userdata('user') && site_setting('announcement_enabled') === '1' && site_setting('announcement_text') !== ''): ?>
    <!-- Top Bar -->
    <div class="top-banner" id="top-banner">
        <p>
            <?= html_escape(site_setting('announcement_text')) ?>
        </p>
    </div>
<?php endif; ?>

<nav class="navbar" id="navbar">
    <div class="container">
        <div class="nav-left">
            <div class="header-logo-area">
                <a href="/<?= $uri ?>" rel="home" class="main-logo">
                    <img id="logo_header" src="<?= html_escape(site_setting('site_logo_url', topupcenter() . 'assets-front/images/logo/playsel-beyaz.webp')) ?>"
                        alt="<?= html_escape(site_setting('site_name', getenv('SITE_NAME') ?: 'Playsel') . '-Logo') ?>">
                </a>
            </div>
            <?php if ($this->uri->segment(2) !== 'checkout' && $this->uri->segment(3) !== 'checkout'): ?>
                <button type="button" class="btn-categories" id="btn-categories">
                    <img src="<?= topupcenter() . 'assets-front/images/icons/category-header-icon.svg' ?>" alt="">
                    <?= $this->lang->line('categories') ?>
                </button>

                <!-- Category Dropdown -->
                <div class="categories-dropdown" id="categories-dropdown">
                    <!-- Main Grid View -->
                    <div class="cat-dropdown-main">
                        <div class="cat-dropdown-header">
                            <div class="cat-dropdown-title">
                                <i class="fa-solid fa-fire" style="color: #f2f2f2; margin-right: 8px;"></i> <?= $this->lang->line('popular_categories') ?>
                            </div>
                            <a href="<?= topupcenter() . $uri . '/categories' ?>" class="cat-dropdown-view-all"><?= $this->lang->line('view_all') ?></a>
                        </div>
                        <div class="cat-dropdown-grid">
                            <?php if (!empty($header_categories)): foreach ($header_categories as $cat):
                                    $catUrl = topupcenter() . $uri . '/' . $cat->category_slug;
                                    $hasSub = !empty($cat->sub_categories);
                            ?>
                                    <a href="<?= htmlspecialchars($catUrl) ?>"
                                        class="cat-grid-item"
                                        id="grid-cat-item-<?= $cat->category_id ?>"
                                        <?php if ($hasSub): ?>
                                        onclick="openSubCategoryFlyout(<?= $cat->category_id ?>, event); event.preventDefault();"
                                        <?php endif; ?>>

                                        <?php if (!empty($cat->category_thumbnail)): ?>
                                            <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $cat->category_thumbnail ?>"
                                                alt="<?= htmlspecialchars($cat->category_title) ?>">
                                        <?php else: ?>
                                            <div style="width: 32px; height: 32px; background: #333; border-radius: 4px;"></div>
                                        <?php endif; ?>

                                        <span><?= htmlspecialchars($cat->category_title) ?></span>
                                    </a>
                            <?php endforeach;
                            endif; ?>
                        </div>
                    </div>

                    <!-- Sub Category Flyout -->
                    <div class="cat-dropdown-flyout cat-dropdown-hidden" id="cat-dropdown-flyout">
                        <?php if (!empty($header_categories)): foreach ($header_categories as $cat): if (!empty($cat->sub_categories)): ?>
                                    <div class="cat-sub-group" id="sub-group-<?= $cat->category_id ?>" style="display: none;">
                                        <div class="cat-sub-title"><?= htmlspecialchars($cat->category_title) ?></div>
                                        <div class="cat-sub-list">
                                            <?php foreach ($cat->sub_categories as $sub):
                                                $subUrl = topupcenter() . $uri . '/' . $sub->category_slug;
                                            ?>
                                                <a href="<?= $subUrl ?>" class="cat-sub-item">
                                                    <?php if (!empty($sub->category_thumbnail)): ?>
                                                        <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $sub->category_thumbnail ?>" alt="<?= htmlspecialchars($sub->category_title) ?>">
                                                    <?php else: ?>
                                                        <div style="width: 32px; height: 32px; background: #333; border-radius: 4px;"></div>
                                                    <?php endif; ?>
                                                    <span><?= htmlspecialchars($sub->category_title) ?></span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                        <?php endif;
                            endforeach;
                        endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <!-- End Category Dropdown -->

        </div>
        <?php if ($this->uri->segment(2) !== 'checkout' && $this->uri->segment(3) !== 'checkout'): ?>
            <div class="search-bar" id="search-bar" style="position: relative;">
                <input type="text" aria-label="Arama" placeholder="<?= $this->lang->line('search') ?>" id="search-input" autocomplete="off">
                <img src="<?= topupcenter() . 'assets-front/images/icons/search-icon.svg' ?>" alt="">

                <!-- Search Results Dropdown -->
                <div id="search-results-dropdown" class="search-results-dropdown" style="display: none;">
                    <div class="search-results-header">
                        <span id="search-keyword-display">""</span> ARAMA SONUÇLARI
                    </div>

                    <div class="search-results-section" id="search-categories-section">
                        <div class="search-section-title">KATEGORİLER</div>
                        <div class="search-category-list" id="search-category-list">
                            <!-- Categories will be appended here -->
                        </div>
                    </div>

                    <div class="search-results-section" id="search-products-section">
                        <div class="search-section-title">ÜRÜNLER</div>
                        <div class="search-product-list" id="search-product-list">
                            <!-- Products will be appended here -->
                        </div>
                    </div>

                    <div id="search-loading" style="display: none; padding: 15px; text-align: center; color: #aaa;">
                        Yükleniyor...
                    </div>
                    <div id="search-no-results" style="display: none; padding: 15px; text-align: center; color: #aaa;">
                        Sonuç bulunamadı.
                    </div>
                </div>
            </div>

            <style>
                .search-results-dropdown {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    margin-top: 10px;
                    background-color: #17181c;
                    border: 1px solid #2a2b30;
                    border-radius: 8px;
                    z-index: 1000;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
                    max-height: 450px;
                    overflow-y: auto;
                    color: #fff;
                    padding: 10px 0;
                    text-align: left;
                }

                .search-results-dropdown::-webkit-scrollbar {
                    width: 6px;
                }

                .search-results-dropdown::-webkit-scrollbar-thumb {
                    background: #333;
                    border-radius: 4px;
                }

                .search-results-header {
                    font-size: 12px;
                    color: #888;
                    padding: 10px 20px 5px;
                    text-transform: uppercase;
                    font-weight: 600;
                    letter-spacing: 0.5px;
                }

                .search-results-header span {
                    color: #fff;
                }

                .search-section-title {
                    font-size: 13px;
                    /* color: #fab316; */
                    color: var(--accent);
                    padding: 15px 20px 10px;
                    text-transform: uppercase;
                    font-weight: 700;
                    border-bottom: 1px solid #2a2b30;
                    margin-bottom: 5px;
                }

                .search-category-item {
                    display: flex;
                    align-items: center;
                    padding: 10px 20px;
                    text-decoration: none;
                    color: #e0e0e0;
                    transition: background 0.2s;
                    font-size: 14px;
                    font-weight: 500;
                }

                .search-category-item:hover {
                    background-color: #24252a;
                    color: #fff;
                }

                .search-category-image {
                    width: 32px;
                    height: 32px;
                    border-radius: 6px;
                    margin-right: 15px;
                    object-fit: cover;
                    background-color: #333;
                    flex-shrink: 0;
                }

                .search-product-item {
                    display: flex;
                    align-items: center;
                    padding: 10px 20px;
                    text-decoration: none;
                    color: #e0e0e0;
                    transition: background 0.2s;
                }

                .search-product-image {
                    width: 32px;
                    height: 32px;
                    border-radius: 6px;
                    margin-right: 15px;
                    object-fit: cover;
                    background-color: #333;
                    flex-shrink: 0;
                }

                .search-product-info {
                    display: flex;
                    flex-direction: column;
                }

                .search-product-name {
                    font-size: 14px;
                    font-weight: 500;
                    color: #fff;
                    margin-bottom: 2px;
                }

                .search-product-cat-name {
                    font-size: 11px;
                    color: #999;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .search-product-item:hover {
                    background-color: #24252a;
                    color: #fff;
                }
            </style>
        <?php endif; ?>

        <div class="nav-right">
            <?php if ($this->uri->segment(2) !== 'checkout' && $this->uri->segment(3) !== 'checkout'): ?>
                <button type="button" class="btn-lang" id="openRegionModal"><?= strtoupper($country) ?> / <?= strtoupper($lang) ?></button>
            <?php endif; ?>
            <?php if ($this->session->userdata('user')): ?>
                <a href="<?= topupcenter() . $uri . '/profile'  ?>" class="btn-auth"
                    id="btn-auth">
                    <svg viewBox="0 0 24 24">
                        <path
                            d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                            fill="#f2f2f2" />
                    </svg>
                    <span class="btn-auth-text"><?= $this->lang->line('my_accounts') ?></span>
                </a>
            <?php else: ?>
                <a href="<?= topupcenter() . $uri . '/login'  ?>" class="btn-auth" id="btn-auth" aria-label="Login">
                    <svg viewBox="0 0 24 24">
                        <path
                            d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                            fill="#f2f2f2" />
                    </svg>
                    <span class="btn-auth-text"><?= $this->lang->line('login_register') ?></span>
                </a>
            <?php endif; ?>
        </div>

    </div>
</nav>
<!-- Region Selection Modal -->

<?php
$groupedCountries = [];
foreach ($locales as $localeObj) {
    $code = strtoupper($localeObj->locale_code);
    $countryName = explode(' - ', $localeObj->locale_country)[0];
    if (!isset($groupedCountries[$code])) {
        $groupedCountries[$code] = [
            'code' => $code,
            'name' => $countryName,
            'languages' => []
        ];
    }
    $groupedCountries[$code]['languages'][] = [
        'id' => $localeObj->locale_id,
        'name' => $localeObj->locale_lang,
        'code' => strtoupper($localeObj->locale_lang_code)
    ];
}
$defaultCountryCode = strtoupper($country);
$defaultLangCode = strtolower($lang);
// Mevcut locale bilgisini tab subtitle'ları için bul
$currentCountryName = '';
$currentLangName = '';
foreach ($groupedCountries as $gc) {
    if ($gc['code'] === $defaultCountryCode) {
        $currentCountryName = $gc['name'];
        foreach ($gc['languages'] as $gl) {
            if (strtolower($gl['code']) === $defaultLangCode) {
                $currentLangName = $gl['name'] . ' - ' . strtoupper($gl['code']);
                break;
            }
        }
        break;
    }
}
if (empty($currentCountryName)) $currentCountryName = 'Türkiye';
if (empty($currentLangName)) $currentLangName = 'Türkçe - TR';
?>
<div id="regionModal" class="region-modal">
    <div class="region-modal-overlay"></div>
    <div class="region-modal-content">

        <div class="region-modal-header">
            <img src="<?= topupcenter() . 'assets-front/images/logo/playsel-beyaz.webp' ?>" alt="Logo"
                class="region-modal-logo">
            <button class="region-modal-close" id="closeRegionModal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="region-modal-tabs">
            <button class="region-tab active" data-target="country-pane">
                <div class="tab-title">Ülke/Bölge</div>
                <div class="tab-subtitle"><?= htmlspecialchars($currentCountryName) ?></div>
            </button>
            <button class="region-tab" data-target="language-pane">
                <div class="tab-title">DİL</div>
                <div class="tab-subtitle"><?= htmlspecialchars($currentLangName) ?></div>
            </button>
        </div>

        <div class="region-modal-body">
            <div id="country-pane" class="region-pane active">
                <h5 class="pane-category-title">Ülke/Bölge Seçimi</h5>
                <div class="region-grid">

                    <?php foreach ($groupedCountries as $code => $countryItem): ?>
                        <label class="region-radio-label">
                            <input type="radio" name="country" value="<?= $code ?>"
                                data-name="<?= htmlspecialchars($countryItem['name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-languages="<?= htmlspecialchars(json_encode($countryItem['languages']), ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($code === $defaultCountryCode) ? 'checked' : '' ?>>
                            <div class="region-card">
                                <img src="https://flagcdn.com/w40/<?= strtolower($code) ?>.png"
                                    alt="<?= $countryItem['name'] ?>">
                                <span><?= $countryItem['name'] ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>

                </div>
            </div>

            <div id="language-pane" class="region-pane">
                <h5 class="pane-category-title">Desteklenen Diller</h5>

                <div class="region-grid" id="dynamic-language-grid"></div>

            </div>
        </div>

        <div class="region-modal-footer">
            <button class="tf-button style-1 region-confirm-btn">Onayla</button>
        </div>
    </div>
</div>

<?php if ($this->agent->is_mobile()): ?>
    <?php $this->load->view('homee/sections/mobile_navbar', get_defined_vars()); ?>
<?php endif; ?>

<script>
    function openSubCategoryFlyout(categoryId, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        document.getElementById('cat-dropdown-flyout').classList.remove('cat-dropdown-hidden');

        document.querySelectorAll('.cat-grid-item').forEach(el => el.classList.remove('active'));
        const activeItem = document.getElementById('grid-cat-item-' + categoryId);
        if (activeItem) activeItem.classList.add('active');

        document.querySelectorAll('.cat-sub-group').forEach(el => el.style.display = 'none');
        const activeGroup = document.getElementById('sub-group-' + categoryId);
        if (activeGroup) activeGroup.style.display = 'block';
    }

    function resetCategoryDropdown() {
        const flyout = document.getElementById('cat-dropdown-flyout');
        if (flyout) flyout.classList.add('cat-dropdown-hidden');
        document.querySelectorAll('.cat-sub-group').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.cat-grid-item').forEach(el => el.classList.remove('active'));
    }

    const btnCategories = document.getElementById('btn-categories');
    const categoriesDropdown = document.getElementById('categories-dropdown');

    if (btnCategories) {
        btnCategories.addEventListener('mouseenter', function() {
            resetCategoryDropdown();
        });
    }

    // Create a wrapper or just use mouseleave on the dropdown to reset its internal state
    if (categoriesDropdown) {
        categoriesDropdown.addEventListener('mouseleave', function() {
            resetCategoryDropdown();
        });
    }

    const searchInput = document.getElementById('search-input');
    const searchDropdown = document.getElementById('search-results-dropdown');

    if (searchInput && searchDropdown) {
        const searchKeywordDisplay = document.getElementById('search-keyword-display');
        const catSection = document.getElementById('search-categories-section');
        const prodSection = document.getElementById('search-products-section');
        const catList = document.getElementById('search-category-list');
        const prodList = document.getElementById('search-product-list');
        const searchLoading = document.getElementById('search-loading');
        const searchNoResults = document.getElementById('search-no-results');

        let searchTimeout;
        let searchController = null;
        let searchRequestId = 0;

        function appendTextElement(parent, tagName, className, text) {
            const element = document.createElement(tagName);
            element.className = className;
            element.textContent = text || '';
            parent.appendChild(element);
            return element;
        }

        function renderSearchImage(parent, src, alt, className) {
            const image = document.createElement('img');
            image.src = src || '';
            image.alt = alt || '';
            image.className = className;
            parent.appendChild(image);
        }

        searchInput.addEventListener('input', function() {
            const keyword = this.value.trim().toLocaleLowerCase('tr-TR');

            if (keyword.length > 0) {
                searchDropdown.style.display = 'block';
                searchKeywordDisplay.textContent = `"${this.value}"`;

                // Show loading state
                catSection.style.display = 'none';
                prodSection.style.display = 'none';
                searchNoResults.style.display = 'none';
                searchLoading.style.display = 'block';

                clearTimeout(searchTimeout);
                if (searchController) {
                    searchController.abort();
                    searchController = null;
                }

                searchTimeout = setTimeout(() => {
                    const requestId = ++searchRequestId;
                    searchController = new AbortController();
                    fetch(`<?= topupcenter() ?>test/search?q=${encodeURIComponent(keyword)}`, {
                        signal: searchController.signal
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Arama isteği başarısız.');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (requestId !== searchRequestId) return;
                            searchLoading.style.display = 'none';

                            const categories = data.categories || [];
                            const products = data.products || [];

                            if (categories.length === 0 && products.length === 0) {
                                searchNoResults.style.display = 'block';
                            } else {
                                // Populate categories
                                if (categories.length > 0) {
                                    catList.replaceChildren();
                                    categories.forEach(c => {
                                        const link = document.createElement('a');
                                        link.href = `<?= topupcenter() . $uri . '/' ?>${encodeURIComponent(c.category_slug || '')}`;
                                        link.className = 'search-category-item';
                                        renderSearchImage(link, `<?= getenv('CDN_URL') . 'uploads/categories/' ?>${encodeURIComponent(c.category_image || '')}`, c.category_title, 'search-category-image');
                                        appendTextElement(link, 'span', '', c.category_title);
                                        catList.appendChild(link);
                                    });
                                    catSection.style.display = 'block';
                                }

                                // Populate products
                                if (products.length > 0) {
                                    prodList.replaceChildren();
                                    products.forEach(p => {
                                        const cat = categories.find(c => c.category_id === p.product_category_id);
                                        const catTitle = cat ? cat.category_title : p.category_slug.replace(/-/g, ' ');
                                        const link = document.createElement('a');
                                        link.href = `<?= topupcenter() . $uri . '/' ?>${encodeURIComponent(p.category_slug || '')}`;
                                        link.className = 'search-product-item';
                                        renderSearchImage(link, `<?= getenv('CDN_URL') ?>${p.product_image || ''}`, p.product_name, 'search-product-image');
                                        const info = document.createElement('div');
                                        info.className = 'search-product-info';
                                        appendTextElement(info, 'span', 'search-product-name', p.product_name);
                                        appendTextElement(info, 'span', 'search-product-cat-name', catTitle);
                                        link.appendChild(info);
                                        prodList.appendChild(link);
                                    });
                                    prodSection.style.display = 'block';
                                }
                            }
                        })
                        .catch(error => {
                            if (error.name === 'AbortError') return;
                            console.error('Arama hatası:', error);
                            searchLoading.style.display = 'none';
                            searchNoResults.style.display = 'block';
                        });
                }, 400);

            } else {
                searchDropdown.style.display = 'none';
                clearTimeout(searchTimeout);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.style.display = 'none';
            }
        });
    }
</script>
