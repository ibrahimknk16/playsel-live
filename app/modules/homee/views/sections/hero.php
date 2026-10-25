<?php
// Eğer henüz hiçbir slider eklenmemişse boş/kırık kutu basma
if (empty($jsData)) {
    return;
}

// İlk aktif kategoriyi ve ilk slaytı belirle (SSR için)
$firstCatId = array_key_first($jsData);
$firstCat = $jsData[$firstCatId];
$firstSlide = !empty($firstCat['slides']) ? $firstCat['slides'][0] : null;
if (!$firstSlide) {
    return;
}

$initialBgImg = $firstSlide['image'];
$initialMobileImg = $firstSlide['mobile_image'] ?? $initialBgImg;
$initialTitle = $firstSlide['title'] ?? ($firstCat['name'] ?? 'Playsell');
$initialSubtitle = $firstSlide['content_2'] ?? ($firstSlide['content_1'] ?? '');
$initialUrl = $firstSlide['url'] ?? '#';
$initialCatName = $firstCat['name'] ?? '';
?>

<section class="hero-section" id="hero-section" style="--ps-hero-bg: url('<?= html_escape($initialBgImg) ?>');">

  <!-- MASAÜSTÜ & MOBİL SLIDER KARTI (Dışa Taşan Turuncu Neon Akışıyla) -->
  <div class="hero-card-wrapper" id="heroCardWrapper">
    <article class="hero-main-card ambient-neon-glow" id="heroMainCard" role="region" aria-roledescription="carousel" aria-label="Playsell Kampanyaları">

      <!-- Sol ve Sağ Sabit Turuncu Neon Çubuklar -->
      <div class="hero-neon-bar hero-neon-bar-left" aria-hidden="true"></div>
      <div class="hero-neon-bar hero-neon-bar-right" aria-hidden="true"></div>

      <!-- SLIDER GÖRSEL & ETKİLEŞİM ALANI -->
      <div class="hero-slider-area" id="heroSliderArea">
        <a id="heroSlideLink" href="<?= html_escape($initialUrl) ?>" class="hero-slide-link" target="_self" draggable="false" aria-label="Kampanyayı İncele">

          <!-- KEN BURNS DİNAMİK GÖRSEL ALANI -->
          <div class="hero-slide-media" id="heroSlideMedia">
            <picture id="heroSlidePicture">
              <source id="heroSlideSourceMobile" media="(max-width: 767px)" srcset="<?= html_escape($initialMobileImg) ?>">
              <img id="heroSlideImg" class="hero-slide-img ken-burns-anim" src="<?= html_escape($initialBgImg) ?>" alt="<?= html_escape($initialTitle) ?>" fetchpriority="high" decoding="async">
            </picture>
          </div>

          <!-- SAĞ ALT KÖŞE KARARTMA GÖLGESİ (Metin Okunabilirliği İçin) -->
          <div class="hero-text-shadow-mask" aria-hidden="true"></div>

          <!-- KÖZ / KIVILCIM PARÇACIK TUVALİ (Subtle Ambient Embers) -->
          <canvas id="emberCanvas" class="hero-ember-canvas" aria-hidden="true"></canvas>

          <!-- SAĞ ALT KÖŞE METİN ALANI -->
          <div class="hero-text-overlay" id="heroTextOverlay">
            <!-- <span class="hero-category-badge" id="heroCategoryBadge"><?= html_escape($initialCatName) ?></span> -->
            <h2 class="hero-slide-title font-gaming" id="heroSlideTitle"><?= html_escape($initialTitle) ?></h2>
            <p class="hero-slide-subtitle" id="heroSlideSubtitle"><?= html_escape($initialSubtitle) ?></p>
          </div>
        </a>

        <!-- Kart Sağ & Sol Navigasyon Okları (Hover ile belirir) -->
        <button type="button" class="hero-arrow-btn hero-arrow-prev" id="heroSidePrev" aria-label="Önceki Kampanya">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" class="hero-arrow-btn hero-arrow-next" id="heroSideNext" aria-label="Sonraki Kampanya">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>

        <!-- DİNAMİK . - (NOKTA / ÇİZGİ) SLAYT KONTROL GÖSTERGESİ (2. Görsel Birebir Tasarım) -->
        <div class="hero-pagination-pill" role="group" aria-label="Slayt Seçimi">
          <!-- <button type="button" class="hero-pill-arrow hero-pill-prev" id="heroPillPrev" aria-label="Önceki Slayt">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button> -->
          <div id="heroPaginationDots" class="hero-pagination-dots"></div>
          <!-- <button type="button" class="hero-pill-arrow hero-pill-next" id="heroPillNext" aria-label="Sonraki Slayt">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button> -->
        </div>

        <!-- ENTEGRE EDİLMİŞ DİNAMİK SLIDER İLERLEME BARI (Özel akışkan yavaşlama eğrisi ile) -->
        <div class="hero-progress-track" aria-hidden="true">
          <div id="heroProgressBar" class="hero-progress-fill" style="width: 0%;"></div>
        </div>
      </div>

      <!-- TAB MENÜSÜ -->
      <nav class="hero-tabs-nav" aria-label="Oyun Kategorileri">
        <div class="hero-tabs-fade hero-tabs-fade-left" aria-hidden="true"></div>
        <div class="hero-tabs-fade hero-tabs-fade-right" aria-hidden="true"></div>
        <div id="heroTabsList" class="hero-tabs-list no-scrollbar" role="tablist">
          <?php
          $firstTab = true;
          foreach ($jsData as $cId => $catItem):
            $isActive = $firstTab;
            $bulletIcon = $catItem['bulletImg'] ?? '';
          ?>
            <button type="button"
                    class="hero-tab-btn <?= $isActive ? 'active' : '' ?>"
                    id="tab-btn-<?= html_escape($cId) ?>"
                    role="tab"
                    aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                    data-game="<?= html_escape($cId) ?>">
              <?php if (!empty($bulletIcon)): ?>
                <div class="hero-tab-icon">
                  <img src="<?= html_escape($bulletIcon) ?>" alt="<?= html_escape($catItem['name']) ?>" loading="lazy" decoding="async" onerror="this.parentElement.style.display='none'">
                </div>
              <?php endif; ?>
              <span class="hero-tab-name"><?= html_escape($catItem['name']) ?></span>
            </button>
          <?php
            $firstTab = false;
          endforeach;
          ?>
        </div>
      </nav>

    </article>
  </div>
</section>
