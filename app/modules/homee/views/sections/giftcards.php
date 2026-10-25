<section class="giftcards-section" id="giftcards-section">
  <style>
    .giftcards-section {
      overflow: hidden;
      padding: 30px 0;
    }

    .gc-slider-wrapper {
      width: 100%;
      overflow: hidden;
      position: relative;
      /* Kenarlarda yavaşça kaybolma efekti (pürüzsüz geçiş için) */
      -webkit-mask-image: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 1) 5%, rgba(0, 0, 0, 1) 95%, rgba(0, 0, 0, 0) 100%);
      mask-image: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 1) 5%, rgba(0, 0, 0, 1) 95%, rgba(0, 0, 0, 0) 100%);
    }

    .gc-slider-track {
      display: flex;
      width: max-content;
      animation: scrollMarquee 64s linear infinite;
      padding: 10px 0;
    }

    .gc-slider-track:hover {
      animation-play-state: paused;
    }

    .gc-card {
      width: 180px;
      flex-shrink: 0;
      margin-right: 20px;
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 16px;
      padding: 24px 12px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 15px;
      transition: all 0.3s ease;
      cursor: pointer;
      text-decoration: none;
    }

    .gc-card:hover {
      transform: translateY(-4px);
      background: rgba(255, 255, 255, 0.04);
      border-color: rgba(199, 154, 62, 0.4);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .gc-card img {
      width: 80px;
      height: 80px;
      object-fit: contain;
      transition: transform 0.4s ease;
    }

    .gc-card:hover img {
      transform: scale(1.1) rotate(2deg);
    }

    .gc-name {
      color: #f8fafc;
      font-weight: 600;
      font-size: 15px;
      text-align: center;
    }

    @media (max-width: 768px) {
      .gc-card {
        width: 140px;
        margin-right: 15px;
      }

      .gc-card img {
        width: 60px;
        height: 60px;
      }
    }

    /* @keyframes marquee {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); } 
    } */
    @keyframes scrollMarquee {
      0% {
        transform: translateX(0);
      }

      100% {
        transform: translateX(calc(-50% - 10px));
      }
    }
  </style>

  <div class="container">
    <!-- <div class="section-header">
      <h2 class="section-title"><?= $this->lang->line('index_gift_card') ?></h2>
      <a href="#" class="btn-view-all" aria-label="view_all"><?= $this->lang->line('view_all') ?></a>
    </div> -->

    <div class="gc-slider-wrapper">
      <div class="gc-slider-track">
        <?php
        if (!empty($giftCards)):
          // Sonsuz kayan (marquee) animasyonunun kesintisiz dönebilmesi için 2 tekrar yeterlidir (13 x 2 = 26 kart)
          for ($i = 0; $i < 2; $i++):
            foreach ($giftCards as $card):
              ?>
              <a href="<?= topupcenter() . $uri . '/' . $card->category_slug ?>" class="gc-card" target="_blank">
                <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $card->category_thumbnail ?>"
                  alt="<?= html_escape($card->category_title) ?>" loading="lazy"
                  onerror="this.onerror=null;this.src='https://placehold.co/80x80/161920/f8fafc?text=Playsel';">
                <span class="gc-name"><?= html_escape($card->category_title) ?></span>
              </a>
            <?php
            endforeach;
          endfor;
        endif;
        ?>
      </div>
    </div>
  </div>
</section>