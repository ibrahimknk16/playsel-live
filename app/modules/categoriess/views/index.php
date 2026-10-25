<style>
    html, body { overflow-x: hidden; max-width: 100%; }
    .categories-panel{
        margin-top:16px
    }
</style>
<?php $this->load->view('categories-index-style'); ?>
<div class="container mt-5 mb-5 all-categories-page">
    <div class="page-title-section text-center mb-4">
        <h1 class="page-title text-white" style="font-size: 36px; font-weight: 800;"><?= $this->lang->line('all_categories') ?></h1>
        <div class="page-breadcrumbs" style="color: #888; font-size: 14px; font-weight: 600;">
            <a href="<?= base_url() ?>" style="color: #aaa; text-decoration: none;"><?= $this->lang->line('home_page') ?></a>
            <span class="separator" style="margin: 0 8px; opacity: 0.6;"><i class="fa-solid fa-chevron-right"></i></span>
            <span class="current text-white"><?= $this->lang->line('all_categories') ?></span>
        </div>
    </div>

    <section class="categories-panel" id="categories-section">
        <div class="cat-filter-tabs" id="cat-filter-tabs">
            <button class="cat-filter-tab active" data-filter="all" aria-label="Filter All">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                </svg>
                <span>Tümü</span>
            </button>
            <button class="cat-filter-tab" data-filter="topup" aria-label="Filter Topup">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                    <path d="M21 18v1c0 1.1-.9 2-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14c1.1 0 2 .9 2 2v1h-9a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" />
                </svg>
                <span>Top-up</span>
            </button>
            <button class="cat-filter-tab" data-filter="epin" aria-label="Filter E-pin">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
                <span>E-pin</span>
            </button>
            <!-- <button class="cat-filter-tab" data-filter="cdkey" aria-label="Filter CD-Key">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                    <path d="M21 3H3c-1.11 0-2 .89-2 2v12c0 1.1.89 2 2 2h5v2h8v-2h5c1.1 0 1.99-.9 1.99-2L23 5c0-1.11-.9-2-2-2zm0 14H3V5h18v12z" />
                </svg>
                <span>CD-Key</span>
            </button> -->
        </div>

        <div class="categories-grid" id="categories-grid">
            <?php foreach ($categories as $category): ?>
                <?php
                // Determine the data-category attribute based on category_type
                $categoryTypeAttr = 'all';
                if ($category->category_type == 2) {
                    $categoryTypeAttr = 'topup all';
                } elseif ($category->category_type == 3) {
                    $categoryTypeAttr = 'epin all';
                } elseif ($category->category_type == 4) {
                    $categoryTypeAttr = 'cdkey all';
                }

                // Generate the base category URL
                $categoryUrl = topupcenter() . $uri . '/' . $category->category_slug;
                $hasSubCategories = !empty($category->sub_categories);
                ?>

                <a href="<?= $categoryUrl ?>" class="category-card animate-on-scroll" data-category="<?= htmlspecialchars($categoryTypeAttr) ?>">

        <div class="cat-logo-box">
          <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $category->category_thumbnail ?>"
            alt="<?= htmlspecialchars($category->category_title) ?> Logo" class="cat-img" loading="lazy"
            onerror="this.onerror=null;this.src='https://i.ytimg.com/vi/tsx95odmMCw/maxresdefault.jpg';">
          <!-- <div class="cat-go-overlay">
            <span class="cat-go-btn">
              <svg viewBox="0 0 24 24">
                <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" />
              </svg><?= $this->lang->line('index_go_text') ?>
            </span>
          </div> -->

          <!-- <?php if ($hasSubCategories): ?>
            <div class="sub-dropdown-overlay" onclick="event.stopPropagation();">
              <?php foreach ($category->sub_categories as $subCategory): ?>
                <a href="<?= topupcenter() . $uri . '/' . $subCategory->category_slug ?>">
                  <?= htmlspecialchars($subCategory->category_title) ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?> -->

        </div>

        <span class="category-name"><?= htmlspecialchars($category->category_title) ?></span>
      </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
    (function() {
        function initCategoryPage() {
            // Kategori filtreleme
            var filterTabs = document.querySelectorAll('.cat-filter-tab');
            var targets = document.querySelectorAll('#categories-grid .category-card');

            filterTabs.forEach(function(tab) {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Aktif buton stilini değiştir
                    filterTabs.forEach(function(t) { t.classList.remove('active'); });
                    this.classList.add('active');

                    var filterValue = this.getAttribute('data-filter');

                    targets.forEach(function(card) {
                        var cardCats = (card.getAttribute('data-category') || '').split(' ');
                        if (filterValue === 'all' || cardCats.indexOf(filterValue) > -1) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // ===== Scroll animations =====
            if ('IntersectionObserver' in window) {
                var obs = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                            obs.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1
                });
                document.querySelectorAll('.animate-on-scroll').forEach(function(el) {
                    obs.observe(el);
                });
            } else {
                document.querySelectorAll('.animate-on-scroll').forEach(function(el) {
                    el.classList.add('visible');
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCategoryPage);
        } else {
            initCategoryPage();
        }
    })();
</script>