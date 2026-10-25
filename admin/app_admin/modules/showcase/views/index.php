<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<style>
  /* Admin Showcase Canvas Styling */
  .sc-admin-wrapper {
    background: #0f1218;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 24px;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  }

  .sc-admin-top {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    align-items: stretch;
  }

  /* Left 3x3 Grid */
  .sc-admin-grid {
    grid-column: span 2;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    grid-template-rows: repeat(3, minmax(0, 1fr));
    gap: 12px;
  }

  /* Game Card */
  .sc-card-game {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 86px;
    padding: 10px 12px;
    background: linear-gradient(145deg, #20222c, #15181f);
    border: 1px solid rgba(199, 154, 62, 0.22);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.25s ease;
    user-select: none;
  }

  .sc-card-game:hover {
    border-color: rgba(199, 154, 62, 0.85);
    background: #252834;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
  }

  .sc-card-game.is-passive {
    opacity: 0.55;
    border-style: dashed;
    border-color: rgba(255, 255, 255, 0.2);
  }

  .sc-card-visual {
    width: 60px;
    height: 60px;
    flex-shrink: 0;
    border-radius: 8px;
    overflow: hidden;
    background: #11141b;
    border: 1px solid rgba(255, 179, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .sc-card-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .sc-card-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .sc-card-info strong {
    font-size: 11px;
    font-weight: 600;
    color: #f1f3f5;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .sc-card-info small {
    font-size: 9px;
    color: #8c94a4;
  }

  /* Card Action Badges */
  .sc-card-actions {
    position: absolute;
    top: 6px;
    right: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s ease;
  }

  .sc-card-game:hover .sc-card-actions,
  .sc-admin-top-campaign:hover .sc-card-actions,
  .sc-card-promo:hover .sc-card-actions {
    opacity: 1;
  }

  .sc-badge-btn {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    font-size: 10px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.15s ease;
  }

  .sc-badge-btn:hover {
    background: #c79a3e;
    color: #000;
    border-color: #c79a3e;
  }

  .sc-drag-handle {
    cursor: grab;
    color: rgba(255, 255, 255, 0.4);
    margin-right: 2px;
  }
  .sc-drag-handle:active {
    cursor: grabbing;
  }

  /* Top Right Featured Campaign */
  .sc-admin-top-campaign {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    background: #181c25;
    border: 1px solid rgba(255, 255, 255, 0.1);
    min-height: 280px;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    transition: all 0.25s ease;
  }

  .sc-admin-top-campaign:hover {
    border-color: #c79a3e;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
  }

  .sc-campaign-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .sc-promo-tag {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
    padding: 5px 9px;
    border-radius: 6px;
    background: #e6ad37;
    color: #171717;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.5px;
  }

  .sc-promo-label {
    position: absolute;
    inset: auto 0 0;
    z-index: 2;
    padding: 24px 14px 12px;
    background: linear-gradient(transparent, rgba(10, 13, 19, 0.95));
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  /* Bottom 3 Campaigns */
  .sc-admin-bottom {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-top: 16px;
  }

  .sc-card-promo {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    background: #181c25;
    border: 1px solid rgba(255, 255, 255, 0.1);
    aspect-ratio: 460 / 250;
    cursor: pointer;
    transition: all 0.25s ease;
    user-select: none;
  }

  .sc-card-promo:hover {
    border-color: #c79a3e;
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.4);
  }

  .sc-card-promo.is-passive {
    opacity: 0.5;
    border-style: dashed;
  }

  /* Empty / Add Slot Card */
  .sc-empty-slot {
    border: 2px dashed rgba(255, 255, 255, 0.15) !important;
    background: rgba(255, 255, 255, 0.02) !important;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    color: #8c94a4;
    transition: all 0.2s ease;
    cursor: pointer;
    border-radius: 12px;
    min-height: 86px;
  }

  .sc-empty-slot:hover {
    border-color: #c79a3e !important;
    color: #c79a3e !important;
    background: rgba(199, 154, 62, 0.05) !important;
  }

  .sc-empty-slot i {
    font-size: 18px;
  }

  .sc-empty-slot span {
    font-size: 11px;
    font-weight: 600;
  }

  /* Sortable Ghost / Drag States */
  .sortable-ghost {
    opacity: 0.35;
    border: 2px dashed #c79a3e !important;
  }
  .sortable-chosen {
    background: #2b2e3c !important;
  }

  /* Language Tabs Styling in Modal - High Specificity */
  #langTabs {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    background: rgba(15, 18, 24, 0.7) !important;
    padding: 6px !important;
    border-radius: 10px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
  }

  #langTabs .nav-item {
    margin: 0 !important;
  }

  #langTabs .nav-link {
    background: #1c202a !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #989fb0 !important;
    border-radius: 8px !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    cursor: pointer !important;
    padding: 8px 14px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    text-decoration: none !important;
    font-size: 13px !important;
    font-weight: 600 !important;
  }

  #langTabs .nav-link:hover {
    background: #252a37 !important;
    color: #ffffff !important;
    border-color: rgba(199, 154, 62, 0.4) !important;
    transform: translateY(-1px);
  }

  #langTabs .nav-link .badge {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    padding: 3px 6px !important;
    border-radius: 4px !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    transition: all 0.2s ease !important;
  }

  /* ACTIVE STATE - Super Visible Premium Gold */
  #langTabs .nav-link.active,
  .nav-pills#langTabs .nav-link.active {
    background: #c79a3e !important;
    background: linear-gradient(135deg, #c79a3e 0%, #e0ab48 100%) !important;
    border: 1px solid #f3cb6b !important;
    color: #0d1017 !important;
    font-weight: 700 !important;
    box-shadow: 0 4px 14px rgba(199, 154, 62, 0.5) !important;
    transform: translateY(-1px);
  }

  #langTabs .nav-link.active .badge,
  .nav-pills#langTabs .nav-link.active .badge {
    background: #0d1017 !important;
    color: #c79a3e !important;
    border-color: #0d1017 !important;
  }

  /* Helper Image URL Resolver */
  <?php
  function getShowcaseImgUrl($img) {
      if (empty($img)) return base_url('assets-front/images/hero_banner.png');
      if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0 || strpos($img, 'data:') === 0) {
          return $img;
      }
      $cdn = getenv('CDN_URL') ?: base_url();
      if (strpos($img, 'uploads/') === 0 || strpos($img, 'assets-front/') === 0) {
          return rtrim($cdn, '/') . '/' . ltrim($img, '/');
      }
      return rtrim($cdn, '/') . '/uploads/showcases/' . ltrim($img, '/');
  }
  ?>
</style>

<!-- Toolbar / Breadcrumb -->
<div class="card card-flush shadow-sm mb-6">
  <div class="card-header pt-6">
    <div class="card-title d-flex align-items-center gap-3">
      <div class="symbol symbol-45px symbol-circle bg-light-warning">
        <i class="ki-duotone ki-element-11 fs-2x text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
      </div>
      <div>
        <h2 class="fw-bold fs-3 mb-0">Vitrin Yönetimi (Showcase Visual Editor)</h2>
        <span class="text-muted fs-7">Ana sayfadaki popüler 3x3 oyun ızgarasını ve kampanya afişlerini doğrudan düzenleyin.</span>
      </div>
    </div>
    <div class="card-toolbar d-flex align-items-center gap-2">
      <!-- Canlı Önizleme Dili -->
      <div class="d-flex align-items-center gap-1 bg-light px-2 py-1 rounded">
        <i class="ki-duotone ki-translate fs-4 text-primary"><span class="path1"></span><span class="path2"></span></i>
        <select class="form-select form-select-transparent form-select-sm w-140px border-0 fs-8 py-1" onchange="changePreviewLanguage(this.value)">
          <option value="default">Önizleme: Varsayılan (TR)</option>
          <?php if (!empty($locales)): ?>
            <?php foreach ($locales as $code => $loc): ?>
              <option value="<?= htmlspecialchars($code) ?>">
                Önizleme: <?= htmlspecialchars($loc->locale_lang) ?> (<?= strtoupper($code) ?>)
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
      <button type="button" class="btn btn-light-success btn-sm" onclick="openNewModal('grid')">
        <i class="ki-duotone ki-plus fs-2"></i> + Yeni Oyun Ekle
      </button>
      <button type="button" class="btn btn-light-warning btn-sm" onclick="openNewModal('campaign_bottom')">
        <i class="ki-duotone ki-plus fs-2"></i> + Kampanya Ekle
      </button>
      <a href="<?= topupcenter() ?>" target="_blank" class="btn btn-light-primary btn-sm">
        <i class="ki-duotone ki-external-drive fs-2"><span class="path1"></span><span class="path2"></span></i> Sitede Gör
      </a>
    </div>
  </div>
</div>

<?php if (!$table_exists): ?>
  <h1>HENÜZ AKTİF DEĞİL KULLANMAYIN. AMA ÇOK İYİ OLDU</h1>
  <!-- Tablo Henüz Oluşturulmadı Bildirimi -->
  <div class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex flex-column p-6 mb-8">
    <div class="d-flex align-items-center mb-3">
      <i class="ki-duotone ki-information-5 fs-2hx text-warning me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
      <div>
        <h4 class="mb-0 text-dark fw-bold">Veritabanı Tablosu Henüz Oluşturulmadı!</h4>
      </div>
    </div>
    <div class="bg-dark p-4 rounded-3 text-white fs-8 position-relative mt-2" style="font-family: monospace;">
      <button type="button" class="btn btn-sm btn-light-primary position-absolute top-0 end-0 m-2" onclick="copySql()">Kodu Kopyala</button>
      <pre id="sqlSnippet" class="m-0 text-warning" style="white-space: pre-wrap;">CREATE TABLE IF NOT EXISTS `showcase_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('grid', 'campaign_top', 'campaign_bottom') NOT NULL DEFAULT 'grid',
  `title` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `badge_text` VARCHAR(50) DEFAULT 'KAMPANYA',
  `translations` JSON DEFAULT NULL,
  `image` VARCHAR(500) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `category_id` INT(11) UNSIGNED DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type_status_order` (`type`, `status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
</pre>
    </div>
  </div>
<?php endif; ?>

<!-- Bilgilendirme İpucu -->
<div class="d-flex align-items-center justify-content-between mb-4 px-2">
  <div class="d-flex align-items-center gap-2 text-muted fs-7">
    <i class="ki-duotone ki-mouse fs-3 text-primary"><span class="path1"></span><span class="path2"></span></i>
    <span>Kartların üzerine <strong>tıklayarak düzenleyebilir</strong>, sürükleyip bırakarak <strong>sıralamasını değiştirebilirsiniz</strong>.</span>
  </div>
  <div id="saveOrderBadge" class="badge badge-light-success fs-8 py-2 px-3 d-none">
    <i class="fa-solid fa-check me-1"></i> Sıralama Kaydedildi
  </div>
</div>

<!-- ========================================================
     GÖRSEL VİTRİN KANVASI (Aynen Sitedeki Grid Düzeni)
     ======================================================== -->
<div class="sc-admin-wrapper mb-10">

  <!-- Üst Kısım (Sol 3x3 Grid + Sağ 1 Büyük Banner) -->
  <div class="sc-admin-top">
    
    <!-- SOL: 3x3 Oyun Izgarası (Maksimum 9 Adet) -->
    <div class="sc-admin-grid" id="sortableGrid">
      <?php if (!empty($grid_items)): ?>
        <?php foreach ($grid_items as $item): ?>
          <?php 
            $itemTrans = !empty($item->translations) ? (is_string($item->translations) ? json_decode($item->translations, true) : (array)$item->translations) : [];
          ?>
          <div class="sc-card-game <?= $item->status == 0 ? 'is-passive' : '' ?>" 
               data-id="<?= $item->id ?>"
               data-title="<?= htmlspecialchars($item->title) ?>"
               data-subtitle="<?= htmlspecialchars($item->subtitle ?: 'Ürünleri incele ›') ?>"
               data-trans='<?= htmlspecialchars(json_encode($itemTrans, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>'
               onclick="editItem(<?= $item->id ?>)">
            <div class="sc-card-actions">
              <span class="sc-badge-btn" title="Aktif/Pasif Yap" onclick="toggleItemStatus(event, <?= $item->id ?>)"><i class="sc-status-icon fa-solid <?= $item->status == 1 ? 'fa-eye text-success' : 'fa-eye-slash text-muted' ?>"></i></span>
              <span class="sc-badge-btn sc-drag-handle" title="Sırala" onclick="event.stopPropagation()"><i class="fa-solid fa-grip-vertical"></i></span>
              <span class="sc-badge-btn" title="Sil" onclick="deleteItem(event, <?= $item->id ?>)"><i class="fa-solid fa-trash text-danger"></i></span>
            </div>
            <div class="sc-card-visual">
              <img src="<?= getShowcaseImgUrl($item->image) ?>" alt="<?= htmlspecialchars($item->title) ?>">
            </div>
            <div class="sc-card-info">
              <strong class="sc-card-title"><?= htmlspecialchars($item->title) ?></strong>
              <small class="sc-card-subtitle"><?= htmlspecialchars($item->subtitle ?: 'Ürünleri incele ›') ?></small>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- 9'dan az kart varsa yeni kart ekleme butonu slotu -->
      <?php 
      $gridCount = !empty($grid_items) ? count($grid_items) : 0;
      if ($gridCount < 9): 
      ?>
        <div class="sc-empty-slot" onclick="openNewModal('grid')">
          <i class="fa-solid fa-plus-circle"></i>
          <span>Oyun Ekle (<?= $gridCount ?>/9)</span>
          <small class="text-muted fs-8">Sol 9'lu</small>
        </div>
      <?php endif; ?>
    </div>

    <!-- SAĞ: Büyük Öne Çıkan Kampanya Afişi (1 Adet) -->
    <?php if ($campaign_top): ?>
      <?php 
        $topTrans = !empty($campaign_top->translations) ? (is_string($campaign_top->translations) ? json_decode($campaign_top->translations, true) : (array)$campaign_top->translations) : [];
      ?>
      <div class="sc-admin-top-campaign <?= $campaign_top->status == 0 ? 'is-passive' : '' ?>" 
           data-id="<?= $campaign_top->id ?>"
           data-title="<?= htmlspecialchars($campaign_top->title) ?>"
           data-badge="<?= htmlspecialchars($campaign_top->badge_text ?: 'KAMPANYA') ?>"
           data-trans='<?= htmlspecialchars(json_encode($topTrans, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>'
           onclick="editItem(<?= $campaign_top->id ?>)">
        <div class="sc-card-actions">
          <span class="sc-badge-btn" title="Aktif/Pasif Yap" onclick="toggleItemStatus(event, <?= $campaign_top->id ?>)"><i class="sc-status-icon fa-solid <?= $campaign_top->status == 1 ? 'fa-eye text-success' : 'fa-eye-slash text-muted' ?>"></i></span>
          <span class="sc-badge-btn" title="Sil" onclick="deleteItem(event, <?= $campaign_top->id ?>)"><i class="fa-solid fa-trash text-danger"></i></span>
        </div>
        <img class="sc-campaign-img" src="<?= getShowcaseImgUrl($campaign_top->image) ?>" alt="<?= htmlspecialchars($campaign_top->title) ?>">
        <span class="sc-promo-tag"><?= htmlspecialchars($campaign_top->badge_text ?: 'KAMPANYA') ?></span>
        <div class="sc-promo-label">
          <span class="sc-card-title"><?= htmlspecialchars($campaign_top->title) ?></span>
          <span class="btn btn-icon btn-sm btn-dark rounded-circle" style="width:28px;height:28px;"><i class="fa-solid fa-arrow-right fs-9"></i></span>
        </div>
      </div>
    <?php else: ?>
      <div class="sc-admin-top-campaign sc-empty-slot" onclick="openNewModal('campaign_top')">
        <i class="fa-solid fa-image fs-1 mb-2"></i>
        <span>+ Öne Çıkan Kampanya Ekle</span>
        <small class="text-muted fs-8">Sağ üstteki büyük dikey afiş</small>
      </div>
    <?php endif; ?>

  </div>

  <!-- Alt Kısım: 3 Yatay Kampanya Kartı -->
  <div class="sc-admin-bottom" id="sortableBottom">
    <?php if (!empty($campaign_bottom)): ?>
      <?php foreach ($campaign_bottom as $item): ?>
        <?php 
          $bTrans = !empty($item->translations) ? (is_string($item->translations) ? json_decode($item->translations, true) : (array)$item->translations) : [];
        ?>
        <div class="sc-card-promo <?= $item->status == 0 ? 'is-passive' : '' ?>" 
             data-id="<?= $item->id ?>"
             data-title="<?= htmlspecialchars($item->title) ?>"
             data-badge="<?= htmlspecialchars($item->badge_text ?: 'KAMPANYA') ?>"
             data-trans='<?= htmlspecialchars(json_encode($bTrans, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>'
             onclick="editItem(<?= $item->id ?>)">
          <div class="sc-card-actions">
            <span class="sc-badge-btn" title="Aktif/Pasif Yap" onclick="toggleItemStatus(event, <?= $item->id ?>)"><i class="sc-status-icon fa-solid <?= $item->status == 1 ? 'fa-eye text-success' : 'fa-eye-slash text-muted' ?>"></i></span>
            <span class="sc-badge-btn sc-drag-handle" title="Sırala" onclick="event.stopPropagation()"><i class="fa-solid fa-grip-vertical"></i></span>
            <span class="sc-badge-btn" title="Sil" onclick="deleteItem(event, <?= $item->id ?>)"><i class="fa-solid fa-trash text-danger"></i></span>
          </div>
          <img class="sc-campaign-img" src="<?= getShowcaseImgUrl($item->image) ?>" alt="<?= htmlspecialchars($item->title) ?>">
          <span class="sc-promo-tag"><?= htmlspecialchars($item->badge_text ?: 'KAMPANYA') ?></span>
          <div class="sc-promo-label">
            <span class="sc-card-title"><?= htmlspecialchars($item->title) ?></span>
            <span class="btn btn-icon btn-sm btn-dark rounded-circle" style="width:26px;height:26px;"><i class="fa-solid fa-arrow-right fs-9"></i></span>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- 3'ten az kampanya varsa ekleme slotu -->
    <?php 
    $bottomCount = !empty($campaign_bottom) ? count($campaign_bottom) : 0;
    if ($bottomCount < 3): 
    ?>
      <div class="sc-card-promo sc-empty-slot" onclick="openNewModal('campaign_bottom')">
        <i class="fa-solid fa-plus-circle fs-2"></i>
        <span>+ Kampanya Ekle (<?= $bottomCount ?>/3)</span>
        <small class="text-muted fs-8">Alt 3'lü</small>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- ========================================================
     DÜZENLEME / EKLEME MODALI (Interactive Edit Modal)
     ======================================================== -->
<div class="modal fade" id="showcaseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered mw-650px">
    <div class="modal-content border-0 shadow-lg">
      
      <div class="modal-header pb-0 border-0 justify-content-between">
        <h2 class="fw-bold fs-4 modal-title" id="modalTitle">Vitrin Öğesini Düzenle</h2>
        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
          <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
        </div>
      </div>

      <form id="showcaseForm" enctype="multipart/form-data">
        <div class="modal-body py-6 px-lg-10">
          <input type="hidden" name="id" id="itemId" value="">
          <input type="hidden" name="sort_order" id="itemSortOrder" value="0">

          <!-- Öğe Türü (Tip Seçimi) -->
          <div class="fv-row mb-6">
            <label class="required fs-6 fw-semibold mb-2">Vitrin Konumu</label>
            <div class="row g-4">
              <div class="col-4">
                <input class="btn-check" type="radio" name="type" value="grid" id="type_grid" checked onchange="toggleTypeFields()">
                <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-4 d-flex flex-column align-items-center w-100" for="type_grid">
                  <i class="fa-solid fa-table-cells fs-2x mb-2"></i>
                  <span class="fs-7 fw-bold">Sol 3x3 Oyun</span>
                </label>
              </div>
              <div class="col-4">
                <input class="btn-check" type="radio" name="type" value="campaign_top" id="type_campaign_top" onchange="toggleTypeFields()">
                <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-4 d-flex flex-column align-items-center w-100" for="type_campaign_top">
                  <i class="fa-solid fa-rectangle-ad fs-2x mb-2"></i>
                  <span class="fs-7 fw-bold">Sağ Üst Kampanya</span>
                </label>
              </div>
              <div class="col-4">
                <input class="btn-check" type="radio" name="type" value="campaign_bottom" id="type_campaign_bottom" onchange="toggleTypeFields()">
                <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-4 d-flex flex-column align-items-center w-100" for="type_campaign_bottom">
                  <i class="fa-solid fa-images fs-2x mb-2"></i>
                  <span class="fs-7 fw-bold">Alt Kampanya</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Kategori Hızlı Doldurma -->
          <?php if (!empty($categories)): ?>
            <div class="fv-row mb-6">
              <label class="fs-6 fw-semibold mb-2">
                <span>Hızlı Kategori Seçimi (Opsiyonel)</span>
                <i class="fas fa-question-circle ms-1 fs-7" title="Seçtiğiniz kategorinin başlığı, görseli ve URL'si otomatik doldurulur."></i>
              </label>
              <select class="form-select form-select-solid" id="categorySelect" name="category_id" onchange="applyCategory(this)">
                <option value="">-- Kategoriden Seçin veya Elle Girin --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat->category_id ?>"
                          data-title="<?= htmlspecialchars($cat->category_title) ?>"
                          data-slug="<?= $cat->category_slug ?>"
                          data-img="<?= $cat->category_thumbnail ?: $cat->category_image ?>">
                    <?= htmlspecialchars($cat->category_title) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>

          <!-- Çoklu Dil Sekmeleri (Metinler) -->
          <div class="card card-bordered bg-light-dark p-4 mb-6 rounded-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <label class="fs-6 fw-bold m-0">Metinler ve Çoklu Dil Çevirileri</label>
              <span class="text-muted fs-8">Görsel ve URL ortaktır, yazıları her dile göre özelleştirebilirsiniz.</span>
            </div>

            <!-- Dil Tab Butonları -->
            <ul class="nav nav-pills mb-3 gap-2" id="langTabs" role="tablist">
              <?php $i = 0; foreach ($locales as $code => $loc): ?>
                <li class="nav-item" role="presentation">
                  <button type="button" 
                     class="nav-link py-2 px-3 fs-7 fw-bold <?= $i === 0 ? 'active' : '' ?>" 
                     data-bs-toggle="pill" 
                     data-bs-target="#trans_tab_<?= $code ?>" 
                     role="tab"
                     aria-controls="trans_tab_<?= $code ?>"
                     aria-selected="<?= $i === 0 ? 'true' : 'false' ?>">
                    <span class="badge me-1 fs-9 text-uppercase"><?= $code ?></span>
                    <?= htmlspecialchars($loc->locale_lang) ?>
                  </button>
                </li>
              <?php $i++; endforeach; ?>
            </ul>

            <!-- Dil Tab İçerikleri -->
            <div class="tab-content" id="langTabContents">
              <?php $i = 0; foreach ($locales as $code => $loc): ?>
                <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="trans_tab_<?= $code ?>" role="tabpanel">
                  <div class="row g-4">
                    <div class="col-12">
                      <label class="<?= $i === 0 ? 'required' : '' ?> fs-7 fw-semibold mb-1">Başlık (<?= strtoupper($code) ?>)</label>
                      <input type="text" class="form-control form-control-solid" name="trans[<?= $code ?>][title]" id="itemTitle_<?= $code ?>" placeholder="Örn: Valorant VP" <?= $i === 0 ? 'required' : '' ?>>
                    </div>
                    <div class="col-md-6 field-subtitle">
                      <label class="fs-7 fw-semibold mb-1">Alt Metin / Slogan (<?= strtoupper($code) ?>)</label>
                      <input type="text" class="form-control form-control-solid" name="trans[<?= $code ?>][subtitle]" id="itemSubtitle_<?= $code ?>" placeholder="Örn: Ürünleri incele">
                    </div>
                    <div class="col-md-6 field-badge" style="display:none;">
                      <label class="fs-7 fw-semibold mb-1">Rozet Metni (<?= strtoupper($code) ?>)</label>
                      <input type="text" class="form-control form-control-solid" name="trans[<?= $code ?>][badge_text]" id="itemBadge_<?= $code ?>" placeholder="Örn: KAMPANYA">
                    </div>
                  </div>
                </div>
              <?php $i++; endforeach; ?>
            </div>
          </div>

          <!-- Hedef Link / URL (Ortak) -->
          <div class="row g-4 mb-6">
            <div class="col-12">
              <label class="required fs-6 fw-semibold mb-2">Hedef Link / URL (Tüm diller için ortak)</label>
              <input type="text" class="form-control form-control-solid" name="url" id="itemUrl" placeholder="Örn: valorant-vp" required>
            </div>
          </div>

          <!-- Görsel Seçimi -->
          <div class="fv-row mb-6">
            <label class="required fs-6 fw-semibold mb-2">Görsel (İkon veya Afiş)</label>
            <div class="d-flex align-items-center gap-4">
              <!-- Sabit Boyutlu Önizleme Kutusu -->
              <div id="previewBox" style="width: 110px; height: 75px; min-width: 110px; max-width: 110px; min-height: 75px; max-height: 75px; border-radius: 10px; overflow: hidden; background: #15181f; border: 2px dashed rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; position: relative;">
                <img id="imgPreview" src="<?= base_url('assets-front/images/hero_banner.png') ?>" style="width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: cover; display: block;">
              </div>
              <div class="flex-grow-1">
                <input type="file" class="form-control form-control-solid mb-2" name="image_file" id="itemFile" accept="image/*" onchange="previewFile(this)">
                <input type="text" class="form-control form-control-solid fs-8" name="image_url" id="itemImageUrl" placeholder="veya doğrudan görsel URL'si girin" oninput="previewUrl(this.value)">
              </div>
            </div>
          </div>

          <!-- Durum Switch -->
          <div class="d-flex align-items-center justify-content-between p-4 bg-light rounded-3">
            <div>
              <span class="fw-bold fs-6 text-dark d-block">Yayın Durumu</span>
              <span class="text-muted fs-7">Pasif yapılırsa vitrinde gösterilmez.</span>
            </div>
            <div class="form-check form-switch form-check-custom form-check-solid">
              <input type="hidden" name="status" value="0">
              <input class="form-check-input" type="checkbox" name="status" id="itemStatus" value="1" checked>
            </div>
          </div>

        </div>

        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light-danger me-auto d-none" id="btnDeleteModal" onclick="deleteFromModal()">
            <i class="fa-solid fa-trash me-1"></i> Sil
          </button>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
          <button type="submit" class="btn btn-primary" id="btnSave">
            <i class="fa-solid fa-check me-1"></i> Kaydet
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- ========================================================
     JAVASCRIPT MANTIĞI & AJAX İŞLEMLERİ
     ======================================================== -->
<script>
  const CDN_URL = "<?= rtrim(getenv('CDN_URL') ?: base_url(), '/') ?>/";
  const BASE_URL = "<?= rtrim(base_url(), '/') ?>/";

  // 1. SortableJS Drag & Drop Başlatma
  document.addEventListener('DOMContentLoaded', function() {
    // Sol Grid Sıralama
    const gridEl = document.getElementById('sortableGrid');
    if (gridEl) {
      new Sortable(gridEl, {
        animation: 180,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        draggable: '.sc-card-game',
        handle: '.sc-card-game',
        onEnd: function() {
          saveOrder(gridEl);
        }
      });
    }

    // Alt Kampanyalar Sıralama
    const bottomEl = document.getElementById('sortableBottom');
    if (bottomEl) {
      new Sortable(bottomEl, {
        animation: 180,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        draggable: '.sc-card-promo',
        handle: '.sc-card-promo',
        onEnd: function() {
          saveOrder(bottomEl);
        }
      });
    }
  });

  // Sıralamayı Kaydet
  function saveOrder(container) {
    const ids = [];
    container.querySelectorAll('[data-id]').forEach(el => {
      const id = el.getAttribute('data-id');
      if (id) ids.push(id);
    });

    if (ids.length === 0) return;

    $.ajax({
      url: '<?= site_url("showcase/update_order") ?>',
      type: 'POST',
      data: { order: ids },
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          showOrderBadge();
          toastr.success(res.message || "Sıralama güncellendi.");
        } else {
          toastr.error(res.message || "Sıralama güncellenemedi.");
        }
      },
      error: function() {
        toastr.error("Sunucu bağlantı hatası.");
      }
    });
  }

  function showOrderBadge() {
    const badge = $('#saveOrderBadge');
    badge.removeClass('d-none');
    setTimeout(() => badge.addClass('d-none'), 2500);
  }

  // 2. Modal Açma (Yeni Ekleme Modu)
  function openNewModal(type = 'grid') {
    $('#showcaseForm')[0].reset();
    $('#itemId').val('');
    $('#modalTitle').text('Yeni Vitrin Öğesi Ekle');
    $('#btnDeleteModal').addClass('d-none');
    $('#itemStatus').prop('checked', true);
    $('#imgPreview').attr('src', BASE_URL + 'assets-front/images/hero_banner.png');

    // İlk dil sekmesini aktif yap
    $('#langTabs .nav-link').removeClass('active').attr('aria-selected', 'false');
    $('#langTabs .nav-link').first().addClass('active').attr('aria-selected', 'true');
    $('#langTabContents .tab-pane').removeClass('show active');
    $('#langTabContents .tab-pane').first().addClass('show active');

    // Tipe göre radio seçimi
    $(`input[name="type"][value="${type}"]`).prop('checked', true);
    toggleTypeFields();

    const modal = new bootstrap.Modal(document.getElementById('showcaseModal'));
    modal.show();
  }

  // 3. Modal Açma (Düzenleme Modu)
  function editItem(id) {
    $.ajax({
      url: '<?= site_url("showcase/detail/") ?>' + id,
      type: 'GET',
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success' && res.data) {
          const item = res.data;
          $('#showcaseForm')[0].reset();
          $('#itemId').val(item.id);
          $('#itemSortOrder').val(item.sort_order);
          $('#modalTitle').text('Vitrin Öğesini Düzenle (#' + item.id + ')');
          $('#btnDeleteModal').removeClass('d-none');

          $(`input[name="type"][value="${item.type}"]`).prop('checked', true);
          $('#itemUrl').val(item.url);
          $('#itemStatus').prop('checked', parseInt(item.status) === 1);
          $('#categorySelect').val(item.category_id || '');

          // Çoklu Dil (Translations) Doldurma
          let trans = item.translations || {};
          if (typeof trans === 'string') {
            try { trans = JSON.parse(trans); } catch(e) { trans = {}; }
          }

          <?php foreach ($locales as $code => $loc): ?>
            var c = '<?= $code ?>';
            var t = trans[c] || {};
            $('#itemTitle_' + c).val(t.title || (c === 'tr' ? item.title : ''));
            $('#itemSubtitle_' + c).val(t.subtitle !== undefined ? t.subtitle : (c === 'tr' ? (item.subtitle || '') : ''));
            $('#itemBadge_' + c).val(t.badge_text !== undefined ? t.badge_text : (c === 'tr' ? (item.badge_text || 'KAMPANYA') : ''));
          <?php endforeach; ?>

          // İlk dil sekmesini aktif yap
          $('#langTabs .nav-link').removeClass('active').attr('aria-selected', 'false');
          $('#langTabs .nav-link').first().addClass('active').attr('aria-selected', 'true');
          $('#langTabContents .tab-pane').removeClass('show active');
          $('#langTabContents .tab-pane').first().addClass('show active');

          // Görsel Önizleme
          $('#itemImageUrl').val(item.image);
          previewUrl(item.image);

          toggleTypeFields();

          const modal = new bootstrap.Modal(document.getElementById('showcaseModal'));
          modal.show();
        } else {
          toastr.error(res.message || "Öğe bilgisi yüklenemedi.");
        }
      },
      error: function() {
        toastr.error("Sunucu ile bağlantı kurulamadı.");
      }
    });
  }

  // 4. Tip Alanına Göre Dinamik Alan Gizle/Göster
  function toggleTypeFields() {
    const selectedType = $('input[name="type"]:checked').val();
    if (selectedType === 'grid') {
      $('.field-subtitle').show();
      $('.field-badge').hide();
    } else {
      $('.field-subtitle').hide();
      $('.field-badge').show();
    }
  }

  // 5. Hızlı Kategori Seçimiyle Alanları Doldur
  function applyCategory(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;

    const title = opt.getAttribute('data-title');
    const slug = opt.getAttribute('data-slug');
    const img = opt.getAttribute('data-img');

    // İlk dil (varsayılan Türkçe) başlığını doldur
    const firstTitle = $('input[name^="trans"][name$="[title]"]').first();
    if (title && !firstTitle.val()) {
      firstTitle.val(title);
    }
    if (slug && !$('#itemUrl').val()) {
      $('#itemUrl').val(slug);
    }
    if (img && !$('#itemImageUrl').val()) {
      $('#itemImageUrl').val(img);
      previewUrl(img);
    }
  }

  // Canlı Önizleme Dilini Değiştir
  function changePreviewLanguage(lang) {
    $('[data-trans]').each(function() {
      const card = $(this);
      const defaultTitle = card.attr('data-title');
      const defaultSubtitle = card.attr('data-subtitle');
      const defaultBadge = card.attr('data-badge');
      let trans = card.attr('data-trans');
      if (typeof trans === 'string') {
        try { trans = JSON.parse(trans); } catch(e) { trans = {}; }
      }
      trans = trans || {};

      let title = defaultTitle;
      let subtitle = defaultSubtitle;
      let badge = defaultBadge;

      if (lang !== 'default' && trans[lang]) {
        if (trans[lang].title) title = trans[lang].title;
        if (trans[lang].subtitle !== undefined && trans[lang].subtitle !== '') subtitle = trans[lang].subtitle;
        if (trans[lang].badge_text !== undefined && trans[lang].badge_text !== '') badge = trans[lang].badge_text;
      }

      card.find('.sc-card-title').text(title);
      if (subtitle !== undefined) {
        card.find('.sc-card-subtitle').text(subtitle);
      }
      if (badge !== undefined) {
        card.find('.sc-promo-tag').text(badge);
      }
    });
  }

  // 6. Görsel Önizleme
  function previewFile(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        $('#imgPreview').attr('src', e.target.result);
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  function previewUrl(url) {
    if (!url) {
      $('#imgPreview').attr('src', BASE_URL + 'assets-front/images/hero_banner.png');
      return;
    }
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('data:')) {
      $('#imgPreview').attr('src', url);
    } else if (url.startsWith('uploads/') || url.startsWith('assets-front/')) {
      $('#imgPreview').attr('src', CDN_URL + url);
    } else {
      $('#imgPreview').attr('src', CDN_URL + 'uploads/showcases/' + url);
    }
  }

  // 7. Form Kaydetme (AJAX)
  $('#showcaseForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btnSave');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...');

    const formData = new FormData(this);
    formData.set('status', $('#itemStatus').is(':checked') ? 1 : 0);

    $.ajax({
      url: '<?= site_url("showcase/save") ?>',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Kaydet');
        if (res.status === 'success') {
          toastr.success(res.message);
          $('#showcaseModal').modal('hide');
          setTimeout(() => location.reload(), 600);
        } else {
          toastr.error(res.message || "Kaydetme başarısız.");
        }
      },
      error: function(xhr) {
        btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Kaydet');
        toastr.error(xhr.responseJSON?.message || "Sunucu hatası oluştu.");
      }
    });
  });

  // 8. Silme İşlemleri
  function deleteItem(event, id) {
    event.stopPropagation();
    Swal.fire({
      title: 'Emin misiniz?',
      text: "Bu vitrin öğesi kalıcı olarak silinecektir!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Evet, Sil!',
      cancelButtonText: 'İptal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '<?= site_url("showcase/delete/") ?>' + id,
          type: 'POST',
          dataType: 'json',
          success: function(res) {
            if (res.status === 'success') {
              toastr.success(res.message);
              $(`[data-id="${id}"]`).fadeOut(300, function() { $(this).remove(); });
            } else {
              toastr.error(res.message);
            }
          },
          error: function() {
            toastr.error("Silinirken bir hata oluştu.");
          }
        });
      }
    });
  }

  function deleteFromModal() {
    const id = $('#itemId').val();
    if (!id) return;
    $('#showcaseModal').modal('hide');
    deleteItem(new Event('click'), id);
  }

  function copySql() {
    const text = document.getElementById('sqlSnippet').innerText;
    navigator.clipboard.writeText(text).then(() => {
      toastr.success("SQL kodu panoya kopyalandı!");
    });
  }

  function toggleItemStatus(event, id) {
    event.stopPropagation();
    $.ajax({
      url: '<?= site_url("showcase/toggle_status/") ?>' + id,
      type: 'POST',
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          toastr.success(res.message);
          const card = $(`[data-id="${id}"]`);
          const icon = card.find('.sc-status-icon');
          if (res.new_status === 1) {
            card.removeClass('is-passive');
            icon.removeClass('fa-eye-slash text-muted').addClass('fa-eye text-success');
          } else {
            card.addClass('is-passive');
            icon.removeClass('fa-eye text-success').addClass('fa-eye-slash text-muted');
          }
        } else {
          toastr.error(res.message || "Durum güncellenemedi.");
        }
      },
      error: function() {
        toastr.error("Sunucu ile bağlantı kurulamadı.");
      }
    });
  }

  // 9. Çoklu Dil Sekmesi Tıklama / Değiştirme
  $(document).on('click', '#langTabs .nav-link', function(e) {
    e.preventDefault();
    $('#langTabs .nav-link').removeClass('active').attr('aria-selected', 'false');
    $(this).addClass('active').attr('aria-selected', 'true');
    const target = $(this).attr('data-bs-target');
    if (target) {
      $('#langTabContents .tab-pane').removeClass('show active');
      $(target).addClass('show active');
    }
  });
</script>
