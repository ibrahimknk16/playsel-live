<style id="playsel-showcase-styles">
  /* ========================================================
     PLAYSEL SHOWCASE SECTION (Popüler Oyunlar ve Kampanyalar)
     ======================================================== */
  /* .psm-showcase {
    margin: 24px 0 36px;
    --psm-gap: 14px;
    --align-gap: 16px;
  } */

  #playsel-kategori-kampanya.psm-showcase {
    width: 100% !important;
    max-width: 100% !important;
    margin-top: 14px !important;
    --align-gap: 16px;
  }

  /* Base Promo & Card Styles */
  .psm-cat,
  .psm-promo {
    position: relative;
    display: block;
    min-width: 0;
    overflow: hidden;
    border-radius: 15px;
    background: #181c25;
    border: 1px solid rgba(255, 255, 255, 0.07);
    color: #fff;
    text-decoration: none !important;
    transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
  }

  .psm-promo img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .3s ease;
  }

  .psm-promo:hover {
    border-color: #c79a3e;
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.35);
    transform: translateY(-2px);
  }

  .psm-promo:hover img {
    transform: scale(1.025);
  }

  .psm-promo:focus-visible {
    outline: 2px solid #e6b95e;
    outline-offset: 2px;
  }

  /* Promo Badge & Labels */
  .psm-promo-tag {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
    padding: 6px 10px;
    border-radius: 8px;
    background: #e6ad37;
    color: #171717;
    font: 700 9px/1.2 Poppins, Arial, sans-serif;
    letter-spacing: .7px;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
  }

  .psm-promo-label {
    position: absolute;
    inset: auto 0 0;
    z-index: 2;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 30px 14px 13px;
    background: linear-gradient(transparent, rgba(9, 12, 19, 0.93));
    color: #ffffff;
    font: 600 13px/1.4 Poppins, Arial, sans-serif;
  }

  .psm-promo-label b {
    color: #e6b95e;
    font-size: 20px;
  }

  /* Promo Circular Arrow Button */
  .psm-showcase .psm-promo-label .psm-campaign-arrow {
    display: grid !important;
    place-items: center !important;
    width: 30px !important;
    height: 30px !important;
    min-width: 30px !important;
    flex: 0 0 30px !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 50% !important;
    background: rgba(16, 20, 27, 0.4) !important;
    color: #ffffff !important;
    box-shadow: none !important;
    transform: none !important;
    backdrop-filter: blur(5px);
    transition: background .2s ease, border-color .2s ease, color .2s ease;
  }

  .psm-showcase .psm-campaign-arrow svg {
    display: block;
    width: 17px;
    height: 17px;
  }

  .psm-showcase .psm-promo:hover .psm-campaign-arrow {
    background: #c79a3e !important;
    border-color: #c79a3e !important;
    color: #17191f !important;
  }

  /* Category Cards in Left Grid */
  .psm-aligned .psm-own-cat {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    min-height: 88px;
    padding: 10px;
    text-decoration: none !important;
    background: linear-gradient(145deg, #23242f, #161920);
    border: 1px solid rgba(199, 154, 62, 0.17);
    border-radius: 13px;
    color: #fff;
    transition: border-color .2s ease, background .2s ease, transform .2s ease;
  }

  .psm-aligned .psm-own-visual {
    display: block;
    flex: none;
    width: 64px;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(255, 179, 0, 0.14);
    background: #121722;
  }

  .psm-aligned .psm-own-visual img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  .psm-aligned .psm-own-info {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    gap: 10px;
  }

  .psm-aligned .psm-own-info strong {
    font: 600 11px/1.45 Poppins, Arial, sans-serif;
    color: #f2f3f5;
    overflow-wrap: anywhere;
  }

  .psm-aligned .psm-own-info small {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #8c94a4;
    font: 400 9px/1.4 Poppins, Arial, sans-serif;
  }

  .psm-aligned .psm-own-info small span {
    color: #caa44e;
    font-size: 18px;
  }

  .psm-aligned .psm-own-cat:hover {
    border-color: rgba(199, 154, 62, 0.55);
    background: #252631;
    transform: translateY(-2px);
  }

  .psm-aligned .psm-own-cat:focus-visible {
    outline: 2px solid #d6b56b;
    outline-offset: 3px;
  }

  /* Top Section Grid: 2 Columns Categories (Left 66.6%), 1 Column Featured Campaign (Right 33.3%) */
  .psm-aligned-top {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: var(--align-gap) !important;
    align-items: stretch !important;
  }

  .psm-aligned .psm-own-categories {
    grid-column: span 2 !important;
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    grid-template-rows: repeat(3, minmax(0, 1fr)) !important;
    gap: 12px !important;
    aspect-ratio: auto !important;
    min-width: 0 !important;
  }

  .psm-top-campaign {
    grid-column: span 1 !important;
    min-width: 0 !important;
    min-height: 0 !important;
    position: relative !important;
  }

  .psm-top-campaign>.psm-promo {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
    aspect-ratio: auto !important;
  }

  .psm-top-campaign img {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
  }

  /* Bottom Section Grid: 3 Campaigns (1 Column each) */
  .psm-aligned .psm-bottom {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: var(--align-gap) !important;
    margin-top: var(--align-gap) !important;
    padding: 0 !important;
    overflow: visible !important;
  }

  .psm-aligned .psm-bottom .psm-promo {
    position: relative !important;
    display: block !important;
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    aspect-ratio: 460 / 260 !important;
  }

  .psm-aligned .psm-bottom .psm-promo img {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
  }

  .psm-aligned .psm-promo-label {
    font-size: 12px;
  }

  /* ========================================================
     RESPONSIVE BREAKPOINTS
     ======================================================== */
  @media (min-width: 601px) and (max-width: 1000px) {
    #playsel-kategori-kampanya.psm-showcase {
      margin-top: 12px !important;
      --align-gap: 12px;
    }

    .psm-aligned {
      --align-gap: 12px;
    }

    .psm-aligned .psm-own-cat {
      padding: 7px;
      gap: 7px;
      min-height: 72px;
    }

    .psm-aligned .psm-own-visual {
      width: 45px;
    }

    .psm-aligned .psm-own-info strong {
      font-size: 9px;
    }

    .psm-aligned .psm-own-info small {
      display: none;
    }
  }

  @media (max-width: 600px) {
    #playsel-kategori-kampanya.psm-showcase {
      margin-top: 10px !important;
      --align-gap: 10px;
    }

    #playsel-kategori-kampanya .psm-aligned-top {
      display: block !important;
    }

    #playsel-kategori-kampanya .psm-top-campaign {
      display: none !important;
    }

    #playsel-kategori-kampanya .psm-own-categories {
      grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
      grid-template-rows: none !important;
      gap: 8px !important;
    }

    .psm-aligned .psm-own-cat {
      flex-direction: column;
      align-items: stretch;
      min-height: 0;
      padding: 8px;
      gap: 0;
      border-radius: 11px;
    }

    .psm-aligned .psm-own-visual {
      width: 100%;
      border-radius: 7px;
    }

    .psm-aligned .psm-own-info {
      gap: 5px;
      padding-top: 6px !important;
      padding-bottom: 6px !important;
    }

    .psm-aligned .psm-own-info strong {
      font-size: 9px;
      line-height: 1.45;
      min-height: 27px;
      text-align: center;
    }

    .psm-aligned .psm-own-info small {
      display: none;
    }

    #playsel-kategori-kampanya .psm-bottom {
      position: relative !important;
      display: flex !important;
      grid-template-columns: none !important;
      overflow-x: auto !important;
      overflow-y: hidden !important;
      gap: 10px !important;
      padding: 0 0 2px !important;
      margin-top: 10px !important;
      scroll-snap-type: x mandatory !important;
      scrollbar-width: none !important;
      -webkit-overflow-scrolling: touch;
      scroll-padding-left: 0;
    }

    #playsel-kategori-kampanya .psm-bottom::-webkit-scrollbar {
      display: none !important;
    }

    #playsel-kategori-kampanya .psm-bottom>.psm-promo {
      position: relative !important;
      inset: auto !important;
      display: block !important;
      flex: 0 0 calc((100% - 10px) / 2) !important;
      width: calc((100% - 10px) / 2) !important;
      height: auto !important;
      min-height: 0 !important;
      aspect-ratio: 460 / 310 !important;
      scroll-snap-align: start !important;
      border-radius: 10px !important;
    }

    #playsel-kategori-kampanya .psm-promo-tag {
      font-size: 7px !important;
      top: 8px !important;
      left: 8px !important;
      padding: 3px 5px !important;
    }

    #playsel-kategori-kampanya .psm-promo-label {
      /* left: 9px !important;
      right: 9px !important;
      bottom: 9px !important; */
      font-size: 9px !important;
      line-height: 1.4 !important;
      gap: 5px !important;
      padding: 20px 8px 8px !important;
    }

    #playsel-kategori-kampanya .psm-promo-label .psm-campaign-arrow {
      width: 22px !important;
      height: 22px !important;
      min-width: 22px !important;
      flex-basis: 22px !important;
    }

    #playsel-kategori-kampanya .psm-campaign-arrow svg {
      width: 13px !important;
      height: 13px !important;
    }
  }

  @media (prefers-reduced-motion: reduce) {

    .psm-cat,
    .psm-promo,
    .psm-own-cat,
    .psm-promo img {
      transition: none !important;
    }
  }
</style>

<?php
if (!function_exists('psm_showcase_img')) {
  function psm_showcase_img($img) {
    if (empty($img)) return '';
    if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0 || strpos($img, 'data:') === 0) {
      return $img;
    }
    $cdn = getenv('CDN_URL') ?: base_url();
    if (strpos($img, 'uploads/') === 0 || strpos($img, 'assets-front/') === 0) {
      return rtrim($cdn, '/') . '/' . ltrim($img, '/');
    }
    return rtrim($cdn, '/') . '/uploads/showcases/' . ltrim($img, '/');
  }
}

if (!function_exists('psm_showcase_url')) {
  function psm_showcase_url($url, $uri = '') {
    if (empty($url) || $url === '#') return '#';
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
      return $url;
    }
    $base = function_exists('topupcenter') ? topupcenter() : base_url();
    $prefix = !empty($uri) ? rtrim($uri, '/') . '/' : '';
    return rtrim($base, '/') . '/' . $prefix . ltrim($url, '/');
  }
}
?>

<section class="psm-showcase psm-aligned" id="playsel-kategori-kampanya" aria-label="Popüler oyunlar ve kampanyalar">
  <div class="psm-aligned-top">
    <div class="psm-own-categories">
      <?php if (!empty($showcase_grid)): ?>
        <?php foreach ($showcase_grid as $sc): ?>
          <?php 
            $subDefault = ($lang ?? 'tr') === 'tr' ? 'Ürünleri incele' : 'Explore products';
            $subText = !empty($sc->subtitle) ? $sc->subtitle : $subDefault;
          ?>
          <a class="psm-own-cat" href="<?= htmlspecialchars(psm_showcase_url($sc->url, $uri ?? '')) ?>" aria-label="<?= htmlspecialchars($sc->title) ?>"><span
              class="psm-own-visual"><img src="<?= psm_showcase_img($sc->image) ?>"
                alt="<?= htmlspecialchars($sc->title) ?>" width="90" height="90"></span><span class="psm-own-info"><strong><?= htmlspecialchars($sc->title) ?></strong><small><?= htmlspecialchars(rtrim($subText, ' ›')) ?> <span aria-hidden="true">›</span></small></span></a>
        <?php endforeach; ?>
      <?php else: ?>
        <a class="psm-own-cat" href="https://qq.playsel.com/tr-TR/pubg-mobile" aria-label="PUBG Mobile"><span
            class="psm-own-visual"><img src="<?= getenv('CDN_URL') ?>uploads/categories/thumb_6a3413ad9f04f.webp"
              alt="PUBG Mobile" width="90" height="90"></span><span class="psm-own-info"><strong>PUBG
              Mobile</strong><small>Ürünleri incele <span aria-hidden="true">›</span></small></span></a>


        <a class="psm-own-cat" href="https://qq.playsel.com/tr-TR/wartune-ultra-yildiz-parasi"
          aria-label="Wartune Ultra"><span class="psm-own-visual"><img
              src="<?= getenv('CDN_URL') ?>uploads/categories/thumb_6a4671d21f925.webp" alt="Wartune Ultra" width="90"
              height="90"></span><span class="psm-own-info"><strong>Wartune Ultra</strong><small>Ürünleri incele <span
                aria-hidden="true">›</span></small></span></a>


        <a class="psm-own-cat" href="https://qq.playsel.com/tr-TR/mobile-legends-bang-bang"
          aria-label="Mobile Legends: Bang Bang"><span class="psm-own-visual"><img
              src="<?= getenv('CDN_URL') ?>uploads/categories/thumb_6a34143add06e.webp" alt="Mobile Legends: Bang Bang"
              width="90" height="90"></span><span class="psm-own-info"><strong>Mobile
              Legends: Bang Bang</strong><small>Ürünleri incele <span aria-hidden="true">›</span></small></span></a>


        <a class="psm-own-cat" href="https://qq.playsel.com/tr-TR/whiteout-survival-froststar"
          aria-label="Whiteout Survival"><span class="psm-own-visual"><img
              src="<?= getenv('CDN_URL') ?>uploads/categories/thumb_6a3e5699b4489.webp" alt="Whiteout Survival" width="90"
              height="90"></span><span class="psm-own-info"><strong>Whiteout Survival</strong><small>Ürünleri incele <span
                aria-hidden="true">›</span></small></span></a>

        <a class="psm-own-cat" href="https://qq.playsel.com/tr-TR/roblox-robux" aria-label="Roblox Robux"><span
            class="psm-own-visual"><img src="<?= getenv('CDN_URL') ?>uploads/categories/thumb_6a46741245bea.webp"
              alt="Roblox Robux" width="90" height="90"></span><span class="psm-own-info"><strong>Roblox
              Robux</strong><small>Ürünleri incele <span aria-hidden="true">›</span></small></span></a>


        <a class="psm-own-cat" href="https://qq.playsel.com/tr-TR/wild-rift-vahsi-cekirdek"
          aria-label="Wild Rift Vahşi Çekirdek"><span class="psm-own-visual"><img
              src="<?= getenv('CDN_URL') ?>uploads/categories/thumb_6a34156de6bbc.webp" alt="Wild Rift Vahşi Çekirdek"
              width="90" height="90"></span><span class="psm-own-info"><strong>Wild Rift
              Vahşi Çekirdek</strong><small>Ürünleri incele <span aria-hidden="true">›</span></small></span></a>

        <a class="psm-own-cat" href="https://qq.playsel.com/tr-TR/valorant-vp" aria-label="Valorant VP"><span
            class="psm-own-visual"><img src="<?= getenv('CDN_URL') ?>uploads/categories/thumb_6a341460ab870.webp"
              alt="Valorant VP" width="90" height="90"></span><span class="psm-own-info"><strong>Valorant
              VP</strong><small>Ürünleri incele <span aria-hidden="true">›</span></small></span></a>

        <a class="psm-own-cat" href="https://qq.playsel.com/tr-TR/league-of-legends-rp"
          aria-label="League Of Legends RP"><span class="psm-own-visual"><img
              src="<?= getenv('CDN_URL') ?>uploads/categories/thumb_6a34153171a61.webp"
              alt="League Of Legends RP" width="90" height="90"></span><span class="psm-own-info"><strong>League
              Of Legends RP</strong><small>Ürünleri incele <span aria-hidden="true">›</span></small></span></a>

        <a class="psm-own-cat" href="https://qq.playsel.com/tr-TR/metin2-ep" aria-label="Metin2"><span
            class="psm-own-visual"><img src="<?= getenv('CDN_URL') ?>uploads/categories/thumb_6a467601d01af.webp"
              alt="Metin2" width="90" height="90"></span><span class="psm-own-info"><strong>Metin2</strong><small>Ürünleri
              incele <span aria-hidden="true">›</span></small></span></a>
      <?php endif; ?>
    </div>


    <div class="psm-top-campaign">
      <?php if (!empty($showcase_top)): ?>
        <a class="psm-promo" href="<?= htmlspecialchars(psm_showcase_url($showcase_top->url, $uri ?? '')) ?>"
          aria-label="<?= htmlspecialchars($showcase_top->title) ?>"><img src="<?= psm_showcase_img($showcase_top->image) ?>" alt="<?= htmlspecialchars($showcase_top->title) ?>" loading="eager"><span
            class="psm-promo-tag"><?= htmlspecialchars($showcase_top->badge_text ?: 'KAMPANYA') ?></span><span class="psm-promo-label"><?= htmlspecialchars($showcase_top->title) ?><b
              class="psm-campaign-arrow" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none">
                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg></b></span></a>
      <?php else: ?>
        <a class="psm-promo" href="https://qq.playsel.com/tr-TR/mobile-legends-elmas"
          aria-label="MLBB 10. Yıl ! %10 indirim"><img src="<?= getenv('CDN_URL') ?>uploads/showcases/showcase-mlbb.png" alt="MLBB 10. Yıl ! %10 indirim" loading="eager"><span
            class="psm-promo-tag">KAMPANYA</span><span class="psm-promo-label">MLBB 10. Yıl ! %10 indirim<b
              class="psm-campaign-arrow" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none">
                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg></b></span></a>
      <?php endif; ?>
    </div>
  </div>

  <div class="psm-bottom" aria-label="Kampanyalar">
    <?php if (!empty($showcase_bottom)): ?>
      <?php foreach ($showcase_bottom as $sc): ?>
        <a class="psm-promo" href="<?= htmlspecialchars(psm_showcase_url($sc->url, $uri ?? '')) ?>" aria-label="<?= htmlspecialchars($sc->title) ?>"><img src="<?= psm_showcase_img($sc->image) ?>"
            alt="<?= htmlspecialchars($sc->title) ?>" loading="eager"><span class="psm-promo-tag"><?= htmlspecialchars($sc->badge_text ?: 'KAMPANYA') ?></span><span
            class="psm-promo-label"><?= htmlspecialchars($sc->title) ?><b class="psm-campaign-arrow" aria-hidden="true"><svg
                viewBox="0 0 24 24" fill="none">
                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg></b></span></a>
      <?php endforeach; ?>
    <?php else: ?>
      <a class="psm-promo"
        href="https://qq.playsel.com/tr-TR/pubg-mobile-uc" aria-label="Midnight Hunters Oyunda!"><img src="<?= getenv('CDN_URL') ?>uploads/showcases/showcase-pubg.png"
          alt="Midnight Hunters Oyunda!" loading="eager"><span class="psm-promo-tag">KAMPANYA</span><span
          class="psm-promo-label">Midnight Hunters Oyunda!<b class="psm-campaign-arrow" aria-hidden="true"><svg
              viewBox="0 0 24 24" fill="none">
              <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg></b></span></a>

      <a class="psm-promo" href="https://qq.playsel.com/tr-TR/playstation-store" aria-label="GTA 6 Efsanesine Hazırlan!"><img src="<?= getenv('CDN_URL') ?>uploads/showcases/showcase-ps.png"
          alt="GTA 6 Efsanesine Hazırlan!" loading="eager"><span class="psm-promo-tag">KAMPANYA</span><span
          class="psm-promo-label">GTA 6 Efsanesine Hazırlan!<b class="psm-campaign-arrow" aria-hidden="true"><svg
              viewBox="0 0 24 24" fill="none">
              <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg></b></span></a>

      <a class="psm-promo" href="https://qq.playsel.com/tr-TR/xbox" aria-label="Xbox Mağazasının Hakimi Ol!"><img src="<?= getenv('CDN_URL') ?>uploads/showcases/showcase-xbox.png"
          alt="Xbox Mağazasının Hakimi Ol!" loading="eager"><span class="psm-promo-tag">KAMPANYA</span><span
          class="psm-promo-label">Xbox Mağazasının Hakimi Ol!<b class="psm-campaign-arrow" aria-hidden="true"><svg
              viewBox="0 0 24 24" fill="none">
              <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg></b></span></a>
    <?php endif; ?>
  </div>
</section>

<script id="playsel-mobile-campaigns">
  document.addEventListener('DOMContentLoaded', function () {
    (() => {
      const root = document.querySelector('.psm-aligned'),
        track = root?.querySelector('.psm-bottom'),
        holder = root?.querySelector('.psm-top-campaign'),
        topCard = holder?.querySelector('.psm-promo');
      if (!track || !topCard) return;
      const mobile = matchMedia('(max-width:600px)'),
        reduce = matchMedia('(prefers-reduced-motion: reduce)');
      let timer, visible = false, playing = !reduce.matches;
      function cards() { return [...track.querySelectorAll('.psm-promo')] }
      function index() {
        const a = cards();
        return a.reduce((best, c, i) => Math.abs(c.offsetLeft - track.offsetLeft - track.scrollLeft) < Math.abs(a[best].offsetLeft - track.offsetLeft - track.scrollLeft) ? i : best, 0)
      }
      function stop() { clearTimeout(timer) }
      function schedule() {
        stop();
        if (mobile.matches && visible && playing && !document.hidden)
          timer = setTimeout(() => { go(1); schedule() }, 6000)
      }
      function go(delta) {
        const a = cards(),
          page = index() >= 1 ? 1 : 0,
          i = ((page + delta + 2) % 2) * 2;
        if (a[i]) {
          track.scrollTo({ left: a[i].offsetLeft - track.offsetLeft, behavior: reduce.matches ? 'instant' : 'smooth' });
        }
      }
      function arrange() {
        stop();
        if (mobile.matches) track.prepend(topCard);
        else holder.append(topCard);
        track.scrollLeft = 0;
        schedule()
      }
      function setPlaying(value) {
        playing = value;
        schedule()
      }
      track.addEventListener('pointerdown', () => setPlaying(false), { passive: true });
      track.addEventListener('focusin', () => setPlaying(false));
      let swipeStart = null;
      track.addEventListener('touchstart', event => {
        if (!mobile.matches || event.touches.length !== 1) { swipeStart = null; return }
        const touch = event.touches[0], max = track.scrollWidth - track.clientWidth;
        swipeStart = { x: touch.clientX, y: touch.clientY, atStart: track.scrollLeft <= 4, atEnd: track.scrollLeft >= max - 4 };
      }, { passive: true });
      track.addEventListener('touchend', event => {
        if (!swipeStart || !mobile.matches) return;
        const start = swipeStart;
        swipeStart = null;
        const touch = event.changedTouches[0];
        if (!touch) return;
        const dx = touch.clientX - start.x, dy = touch.clientY - start.y;
        if (Math.abs(dx) < 45 || Math.abs(dx) < Math.abs(dy) * 1.4) return;
        const max = track.scrollWidth - track.clientWidth;
        if (max < 2) return;
        if (start.atEnd && dx < 0) track.scrollTo({ left: 0, behavior: reduce.matches ? 'instant' : 'smooth' });
        else if (start.atStart && dx > 0) track.scrollTo({ left: max, behavior: reduce.matches ? 'instant' : 'smooth' });
      }, { passive: true });
      track.addEventListener('touchcancel', () => { swipeStart = null }, { passive: true });
      document.addEventListener('visibilitychange', schedule);
      mobile.addEventListener('change', arrange);
      reduce.addEventListener('change', () => { if (reduce.matches) setPlaying(false) });
      if (window.IntersectionObserver) {
        new IntersectionObserver(e => { visible = e[0].isIntersecting; schedule() }, { threshold: .3 }).observe(track);
      }
      arrange();
      setPlaying(playing);
    })();
  });
</script>