<?php if (!empty($latestNews)): ?>
<section class="ps-news-section" id="news-section" aria-labelledby="news-heading">
  <style>
    .ps-news-section {
      width: 100%;
      padding: 40px 0 20px 0;
      position: relative;
    }

    /* Başlık ve Tümünü Gör Barı */
    .ps-news-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: relative;
      padding: 0 0 14px;
      margin-bottom: 24px;
    }

    .ps-news-heading {
      margin: 0;
      color: #fff;
      font-size: 24px;
      font-weight: 800;
      letter-spacing: .5px;
      text-transform: uppercase;
      line-height: 1.2;
    }

    .ps-news-view-all {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 8px 20px;
      background-color: #ffb300;
      color: #000000;
      font-weight: 800;
      font-size: 13px;
      border-radius: 6px;
      text-decoration: none;
      transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
      box-shadow: 0 4px 14px rgba(255, 179, 0, 0.2);
      line-height: 1.2;
      white-space: nowrap;
    }

    .ps-news-view-all:hover {
      background-color: #fca800;
      color: #000000;
      box-shadow: 0 6px 20px rgba(255, 179, 0, 0.35);
      transform: translateY(-1px);
    }

    /* Yatay Kaydırma Rayı (Slider Track) */
    .ps-news-slider {
      display: flex;
      overflow-x: auto;
      gap: 20px;
      padding-top: 8px;
      padding-bottom: 24px;
      width: 100%;
      position: relative;
      z-index: 10;
      overscroll-behavior-x: contain;
      will-change: scroll-position;
      -webkit-overflow-scrolling: touch;
      touch-action: pan-x pan-y;
      cursor: grab;
    }

    .ps-news-slider:active {
      cursor: grabbing;
    }

    /* Scrollbar Gizleme */
    .ps-news-slider::-webkit-scrollbar {
      display: none;
    }
    .ps-news-slider {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    /* Haber Kartı */
    .ps-news-card {
      position: relative;
      flex-shrink: 0;
      aspect-ratio: 16 / 10;
      scroll-snap-align: start;
      border-radius: 16px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.1);
      background-color: #13111a;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
      display: flex;
      flex-direction: column;
      user-select: none;
      -webkit-user-select: none;
      box-sizing: border-box;
    }

    /* Responsive Kart Genişlikleri (Hedef Tasarım Kuralı) */
    /* Masaüstü: 3 Kart sığar (Aralardaki 2 boşluk 40px) */
    .ps-news-card {
      width: calc((100% - 40px) / 3);
      min-width: calc((100% - 40px) / 3);
      flex: 0 0 calc((100% - 40px) / 3);
    }

    /* Tablet: 2 Kart + taşma */
    @media (max-width: 1024px) {
      .ps-news-slider {
        scroll-snap-type: x mandatory;
      }
      .ps-news-card {
        width: 44%;
        min-width: 44%;
        flex: 0 0 44%;
      }
    }

    /* Mobil: 1 Kart + taşma (%85) */
    @media (max-width: 767px) {
      .ps-news-section {
        padding: 24px 0 16px 0;
      }
      .ps-news-slider {
        gap: 14px;
        padding-bottom: 16px;
      }
      .ps-news-card {
        width: 85vw;
        min-width: 85vw;
        flex: 0 0 85vw;
      }
      .ps-news-heading {
        font-size: 19px;
      }
      .ps-news-view-all {
        padding: 6px 14px;
        font-size: 12px;
      }
    }

    .ps-news-card:hover {
      border-color: rgba(255, 255, 255, 0.3);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
    }

    /* Kartın Tıklanabilir Tam Alanı */
    .ps-news-card-link {
      position: absolute;
      inset: 0;
      z-index: 6;
      border-radius: 16px;
      outline: none;
    }

    .ps-news-card-link:focus-visible {
      outline: 2px solid #ffb300;
    }

    /* Görsel Katmanı */
    .ps-news-img-box {
      position: absolute;
      inset: 0;
      z-index: 1;
      overflow: hidden;
      background: #11151d;
    }

    .ps-news-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.65s cubic-bezier(0.25, 0.1, 0.25, 1);
      will-change: transform;
      -webkit-user-drag: none;
    }

    .ps-news-card:hover .ps-news-img {
      transform: scale(1.04);
    }

    /* Karartma Gradyan Katmanı */
    .ps-news-overlay {
      position: absolute;
      inset: 0;
      z-index: 2;
      background: linear-gradient(to top, #0a0710 0%, rgba(10, 7, 16, 0.82) 55%, rgba(10, 7, 16, 0.15) 100%);
      opacity: 0.95;
      transition: opacity 0.3s ease;
      pointer-events: none;
    }

    .ps-news-card:hover .ps-news-overlay {
      opacity: 1;
    }

    /* İçerik Katmanı */
    .ps-news-content {
      position: relative;
      z-index: 3;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 100%;
      padding: 20px;
      pointer-events: none;
      box-sizing: border-box;
    }

    @media (max-width: 767px) {
      .ps-news-content {
        padding: 16px;
      }
    }

    /* Kategori Rozeti (Oyun Adı) */
    .ps-news-badge {
      align-self: flex-start;
      background: #ffb300;
      color: #000000;
      font-size: 11px;
      font-weight: 800;
      padding: 4px 10px;
      border-radius: 4px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      line-height: 1.2;
    }

    /* Alt Başlık ve Ok Butonu */
    .ps-news-footer {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 14px;
      margin-top: auto;
    }

    .ps-news-title {
      color: #ffffff;
      font-size: 15px;
      font-weight: 700;
      line-height: 1.35;
      margin: 0;
      max-width: 80%;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.8));
      transition: color 0.2s ease;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    @media (min-width: 768px) {
      .ps-news-title {
        font-size: 16px;
      }
    }

    .ps-news-card:hover .ps-news-title {
      color: #ffb300;
    }

    /* Premium Dairesel Ok Butonu */
    .ps-news-arrow-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #2a2a30;
      border: 1px solid rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
      position: relative;
      overflow: hidden;
      pointer-events: none;
      z-index: 5;
      color: #ffffff;
    }

    .ps-news-arrow-btn svg {
      width: 20px;
      height: 20px;
      stroke: #ffffff;
      transition: transform 0.25s ease;
    }

    .ps-news-card:hover .ps-news-arrow-btn {
      background: #3f3f46;
      border-color: rgba(255, 255, 255, 0.5);
      box-shadow: 0 0 15px rgba(255, 255, 255, 0.25);
    }

    .ps-news-card:hover .ps-news-arrow-btn svg {
      transform: translateX(2px);
    }

    /* Drag Önleyici */
    .drag-prevent {
      -webkit-user-drag: none;
      user-select: none;
    }
  </style>

  <!-- Bölüm Başlığı ve "Tümünü Gör" Butonu -->
  <header class="section-header ps-news-header">
    <h2 id="news-heading" class="section-title ps-news-heading"><?= !empty($this->lang->line('index_news')) ? $this->lang->line('index_news') : 'HABERLER' ?></h2>
    
    <a href="<?= topupcenter() . $uri . '/news' ?>" class="btn-view-all ps-news-view-all" title="Tüm Haber ve Kampanyaları Gör">
      <?= !empty($this->lang->line('view_all')) ? $this->lang->line('view_all') : 'Tümünü Gör' ?>
    </a>
  </header>

  <!-- HABERLER YATAY KAYDIRMA ALANI -->
  <div id="newsSlider" class="ps-news-slider">
    <?php foreach ($latestNews as $news): ?>
    <?php
      $tags = !empty($news->news_tags) ? explode(',', $news->news_tags) : [];
      $newsUrl = topupcenter() . $uri . '/news/' . $news->news_slug;
      $imgSrc = !empty($news->news_image) ? getenv('CDN_URL') . 'uploads/news/' . $news->news_image : base_url('assets-front/images/placeholder.png');
      $badgeText = !empty($news->news_category_title) ? $news->news_category_title : (!empty($tags[0]) ? trim($tags[0]) : 'HABER');
    ?>
    <article class="ps-news-card">
      <!-- Tıklanabilir Link Katmanı -->
      <a href="<?= html_escape($newsUrl) ?>" target="_blank" draggable="false" class="ps-news-card-link drag-prevent" aria-label="<?= html_escape($news->news_title) ?>">
        <span class="sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;"><?= html_escape($news->news_title) ?></span>
      </a>

      <!-- Görsel Katmanı -->
      <div class="ps-news-img-box">
        <img src="<?= html_escape($imgSrc) ?>" alt="<?= html_escape($news->news_title) ?>" class="ps-news-img drag-prevent" loading="lazy">
      </div>

      <!-- Koyu Degrade Karartma Katmanı -->
      <div class="ps-news-overlay"></div>

      <!-- İçerik Katmanı -->
      <div class="ps-news-content">
        <!-- Kategori / Oyun Rozeti -->
        <!-- <?php if (!empty($badgeText)): ?>
          <div class="ps-news-badge"><?= html_escape($badgeText) ?></div>
        <?php else: ?>
          <div></div>
        <?php endif; ?> -->

        <!-- Alt Başlık ve Dairesel Ok Butonu -->
        <div class="ps-news-footer">
          <h3 class="ps-news-title" title="<?= html_escape($news->news_title) ?>">
            <?= html_escape($news->news_title) ?>
          </h3>
          <div class="ps-news-arrow-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </div>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <script>
    (function() {
      const slider = document.getElementById('newsSlider');
      if (!slider) return;

      let isDown = false;
      let isDragging = false;
      let startX = 0;
      let scrollLeft = 0;
      const DRAG_THRESHOLD = 8;

      // Masaüstü fare sürükleme (Drag to scroll) motoru
      slider.addEventListener('pointerdown', (e) => {
        // Dokunmatik cihazlarda (mobil / tablet) yerel kaydırmaya dokunma
        if (e.pointerType === 'touch') return;
        if (e.button !== 0) return; // Sadece sol tık

        isDown = true;
        isDragging = false;
        startX = e.pageX;
        scrollLeft = slider.scrollLeft;
      });

      window.addEventListener('pointermove', (e) => {
        if (!isDown || e.pointerType === 'touch') return;
        const deltaX = e.pageX - startX;

        // Yalnızca 8px'ten fazla sürüklendiyse sürükleme olarak algıla
        if (Math.abs(deltaX) > DRAG_THRESHOLD) {
          isDragging = true;
          slider.scrollLeft = scrollLeft - deltaX;
        }
      });

      const stopDrag = (e) => {
        if (!isDown) return;
        if (e && e.pointerType === 'touch') return;
        isDown = false;

        setTimeout(() => {
          isDragging = false;
        }, 40);
      };

      window.addEventListener('pointerup', stopDrag);
      window.addEventListener('pointercancel', stopDrag);

      // Fare tekerleği (wheel) ile yatay kaydırma desteği (özellikle standart fareler için)
      slider.addEventListener('wheel', (e) => {
        // Mac Trackpad / Magic Mouse gibi iki parmakla yatay kaydıran cihazlarda (deltaX)
        // macOS'un kendi yerel, akıcı fizik motorunu engelleme
        if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
          return;
        }

        // Standart fare tekerleği (dikey deltaY) döndürüldüğünde yatay kaydır
        if (Math.abs(e.deltaY) > 0) {
          const maxScroll = slider.scrollWidth - slider.clientWidth;
          const canScroll = (e.deltaY > 0 && slider.scrollLeft < maxScroll - 1) || (e.deltaY < 0 && slider.scrollLeft > 1);
          if (canScroll) {
            e.preventDefault();
            slider.scrollLeft += e.deltaY;
          }
        }
      }, { passive: false });

      // Sürükleme bittiğinde linke yanlışlıkla tıklanmasını engelle
      slider.addEventListener('click', (e) => {
        if (isDragging) {
          e.preventDefault();
          e.stopPropagation();
        }
      }, true);

      // Resimlerin hayalet kopya gibi sürüklenmesini engelle
      slider.querySelectorAll('.drag-prevent').forEach(el => {
        el.addEventListener('dragstart', (e) => e.preventDefault());
      });
    })();
  </script>
</section>
<?php endif; ?>
