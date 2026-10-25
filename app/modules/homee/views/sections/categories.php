<style>
  .categories-panel .category-card .category-name {
    position: relative;
    display: inline-block;
    align-self: center;
    width: fit-content;
    max-width: 100%;
    text-align: center;
    padding-bottom: 4px;
  }

  .categories-panel .category-card .category-name::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 100%;
    height: 2px;
    background: #ffb300;
    box-shadow: 0 0 8px rgba(255, 179, 0, 0.5);
    border-radius: 2px;
    transform: translateX(-50%) scaleX(0);
    transform-origin: center;
    transition: transform 0.25s ease, opacity 0.25s ease;
    opacity: 0;
  }

  .categories-panel .category-card:hover .category-name::after {
    transform: translateX(-50%) scaleX(1);
    opacity: 1;
  }
</style>

<section class="categories-panel mt-5" id="categories-section">

  <!-- <div class="cat-filter-tabs" id="cat-filter-tabs">
    <button class="cat-filter-tab active" data-filter="all" aria-label="Filter All">
      <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
      </svg>
      <span><?= $this->lang->line('popular_categories') ?></span>
    </button>
    <button class="cat-filter-tab" data-filter="topup" aria-label="Filter Topup">
      <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
        <path
          d="M21 18v1c0 1.1-.9 2-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14c1.1 0 2 .9 2 2v1h-9a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" />
      </svg>
      <span><?= $this->lang->line('index_topup') ?></span>
    </button>
    <button class="cat-filter-tab" data-filter="epin" aria-label="Filter E-pin">
      <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
        <path
          d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
      </svg>
      <span><?= $this->lang->line('index_epin') ?></span>
    </button>
    <button class="cat-filter-tab" data-filter="dijital" aria-label="Filter Dijital">
      <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
        <path
          d="M21 3H3c-1.11 0-2 .89-2 2v12c0 1.1.89 2 2 2h5v2h8v-2h5c1.1 0 1.99-.9 1.99-2L23 5c0-1.11-.9-2-2-2zm0 14H3V5h18v12z" />
      </svg>
      <span><?= $this->lang->line('index_tv_platform') ?></span>
    </button>
  </div> -->

  <!-- <div class="section-header">
    <h2 class="section-title" id="dynamic-cat-title"><?= $this->lang->line('popular_categories') ?></h2>
    <a href="<?= topupcenter() . $uri . '/categories' ?>" class="btn-view-all" aria-label="view_all"><?= $this->lang->line('view_all') ?></a>
  </div> -->

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
      $hasSubCategories = !empty($category->sub_categories);
      if ($hasSubCategories) {
        $categoryUrl = topupcenter() . $uri . '/' . $category->sub_categories[0]->category_slug;
      } else {
        $categoryUrl = topupcenter() . $uri . '/' . $category->category_slug;
      }
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