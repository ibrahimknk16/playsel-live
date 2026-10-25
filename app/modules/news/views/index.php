<style>
  .news-page {
    --bg:#11151d;
    --panel:#1e222d;
    --panel2:#171b24;
    --line:rgba(255,179,0,.18);
    --text:#f2f2f2;
    --muted:#98a2b3;
    --blue:#ffb300;
    --blue2:#ff7300;
    --pink:#ff7300;
    --radius:14px;
    --content:1240px;
    
    background: var(--bg);
    color: var(--text);
    font-family: 'Poppins', sans-serif;
    line-height: 1.55;
    padding: clamp(24px, 5vw, 58px) 22px 70px;
    min-height: 100vh;
  }
  
  .news-page * {
    box-sizing: border-box;
  }

  .news-page a {
    color: inherit;
    text-decoration: none;
  }

  .news-page button, .news-page input {
    font: inherit;
  }

  .news-page button:focus-visible, .news-page a:focus-visible, .news-page input:focus-visible {
    outline: 3px solid #91a0ff;
    outline-offset: 3px;
  }

  .news-page .container {
    width: min(100%, var(--content));
    margin-inline: auto;
  }

  .news-page h1, .news-page h2, .news-page h3, .news-page p {
    margin-top: 0;
  }

  .news-page h1 {
    font-size: clamp(2rem, 4vw, 3.2rem);
    line-height: 1.08;
    letter-spacing: -.045em;
    margin: 12px 0 10px;
  }

  .news-page .featured {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    display: grid;
    grid-template-columns: 1.03fr .97fr;
    min-height: 360px;
    border: 1px solid #282f3c;
    border-radius: 24px;
    background: #151a24;
    margin-bottom: 36px;
  }

  .news-page .featured::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: -1;
    background: linear-gradient(90deg, #10141cf5 0%, #10141ce8 34%, #10141c62 66%, #10141c08 100%);
  }

  .news-page .featured-media {
    position: absolute;
    inset: 0 0 0 36%;
    z-index: -2;
  }

  .news-page .featured-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
  }

  .news-page .featured-copy {
    align-self: end;
    padding: clamp(24px, 4.2vw, 52px);
    max-width: 720px;
  }

  .news-page .feature-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 999px;
    color: #fff;
    background: #536dff30;
    border: 1px solid #8292ff60;
    font-size: .73rem;
    font-weight: 750;
  }

  .news-page .feature-label i {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--pink);
  }

  .news-page .featured h1, .news-page .featured h2 {
    font-size: clamp(1.75rem, 3.5vw, 2.8rem);
    line-height: 1.12;
    letter-spacing: -.04em;
    margin: 18px 0 12px;
    max-width: 610px;
  }

  .news-page .featured-copy > p {
    color: #c1c7d2;
    max-width: 560px;
    margin-bottom: 21px;
  }

  .news-page .feature-meta, .news-page .meta {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #aab3c1;
    font-size: .78rem;
  }

  .news-page .feature-meta span + span::before {
    content: "";
    display: inline-block;
    width: 3px;
    height: 3px;
    background: #657084;
    border-radius: 50%;
    margin: 0 12px 2px 0;
  }

  .news-page .read-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: white;
    font-weight: 750;
    font-size: .88rem;
  }

  .news-page .read-link b {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: #fff;
    color: #151923;
    transition: transform .2s;
  }

  .news-page .read-link:hover b {
    transform: translateX(4px);
  }

  .news-page .toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--line);
    margin-bottom: 21px;
  }

  .news-page .filters-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    min-width: 0;
    flex: 1 1 auto;
  }

  .news-page .filters {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none;
    width: 100%;
    min-width: 0;
    padding: 4px 2px;
    cursor: grab;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
  }

  .news-page .filters.is-dragging {
    cursor: grabbing;
    scroll-behavior: auto;
    user-select: none;
  }

  .news-page .filters::-webkit-scrollbar {
    display: none;
  }

  .news-page .filters-arrow {
    flex: 0 0 32px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #171d28;
    border: 1px solid #282f3c;
    color: #a6afbe;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .2s;
    z-index: 2;
    padding: 0;
  }

  .news-page .filters-arrow:hover {
    background: #536dff30;
    border-color: #536dff60;
    color: #fff;
    transform: scale(1.05);
  }

  .news-page .filters-arrow-left {
    margin-right: 8px;
  }

  .news-page .filters-arrow-right {
    margin-left: 8px;
  }

  .news-page .filter {
    flex: 0 0 auto;
    border: 1px solid transparent;
    color: #a6afbe;
    background: transparent;
    border-radius: 999px;
    padding: 9px 14px;
    font-size: .82rem;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
  }

  .news-page .filter:hover {
    color: #fff;
    background: #171d28;
  }

  .news-page .filter[aria-pressed="true"] {
    color: #fff;
    border-color: #566cff65;
    background: #536dff22;
  }

  .news-page .search {
    position: relative;
    flex: 0 0 240px;
  }

  .news-page .search svg {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    width: 17px;
    color: #8993a4;
    pointer-events: none;
  }

  .news-page .search input {
    width: 100%;
    height: 42px;
    color: #fff;
    background: #121720;
    border: 1px solid #2a303c;
    border-radius: 12px;
    padding: 0 13px 0 40px;
  }

  .news-page .search input::placeholder {
    color: #798495;
  }

  .news-page .section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 1px 0 18px;
    padding: 0 2px;
  }

  .news-page .section-head h2 {
    position: relative;
    font-size: 1.18rem;
    letter-spacing: -.025em;
    margin: 0;
    padding-left: 14px;
  }

  .news-page .section-head h2::before {
    content: "";
    position: absolute;
    left: 0;
    top: 4px;
    bottom: 4px;
    width: 3px;
    border-radius: 3px;
    background: linear-gradient(180deg, #8290ff, var(--blue));
  }

  .news-page .result-count {
    font-size: .74rem;
    color: #aeb9ff;
    background: #536dff16;
    border: 1px solid #536dff35;
    border-radius: 999px;
    padding: 5px 10px;
  }

  .news-page .grid {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 18px;
  }

  .news-page .card {
    grid-column: span 3;
    min-width: 0;
    overflow: hidden;
    background: linear-gradient(145deg, #151a24, #10141b);
    border: 1px solid #252c39;
    border-radius: var(--radius);
    transition: transform .22s, border-color .22s, background .22s;
    box-shadow: 0 12px 30px #00000020;
    display: flex;
    flex-direction: column;
  }

  .news-page .card:hover {
    transform: translateY(-4px);
    border-color: #5968c7;
    background: #171d29;
    box-shadow: 0 18px 38px #00000040;
  }

  .news-page .card[hidden] {
    display: none;
  }

  .news-page .card-image {
    display: block;
    position: relative;
    overflow: hidden;
    aspect-ratio: 16/9;
    background: #171d28;
  }

  .news-page .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .35s;
  }

  .news-page .card:hover .card-image img {
    transform: scale(1.035);
  }

  .news-page .game-tag {
    position: absolute;
    z-index: 1;
    left: 13px;
    top: 13px;
    background: #0b0e13d9;
    border: 1px solid #ffffff20;
    color: #f4f6fc;
    border-radius: 8px;
    padding: 5px 9px;
    font-size: .68rem;
    font-weight: 750;
    backdrop-filter: blur(8px);
  }

  .news-page .card-body {
    padding: 17px 17px 16px;
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
  }

  .news-page .card-kicker {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #aab3c1;
    font-size: .72rem;
    font-weight: 750;
    margin-bottom: 9px;
  }

  .news-page .card-kicker i {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--pink);
  }

  .news-page .card h3 {
    font-size: 1.02rem;
    line-height: 1.4;
    letter-spacing: -.015em;
    margin: 0 0 9px;
  }

  .news-page .card h3 a:hover {
    color: #aebaff;
  }

  .news-page .card-desc {
    font-size: .82rem;
    color: #929cab;
    line-height: 1.55;
    margin: 0 0 16px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.55em;
  }

  .news-page .card-foot {
    margin-top: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #242a35;
    padding-top: 12px;
    color: #8791a0;
    font-size: .72rem;
  }

  .news-page .card-foot a {
    color: #c3cbdb;
    font-weight: 700;
  }

  .news-page .card-foot a:hover {
    color: #fff;
  }

  .news-page .empty {
    display: none;
    text-align: center;
    padding: 50px 20px;
    border: 1px dashed #343b49;
    border-radius: 16px;
    color: #a6afbe;
  }

  .news-page .empty.is-visible {
    display: block;
  }

  .news-page .load-wrap {
    text-align: center;
    padding-top: 30px;
  }

  .news-page .load-more {
    min-height: 46px;
    padding: 0 21px;
    border: 1px solid #3a4354;
    border-radius: 12px;
    color: #f2f4fa;
    background: #151a23;
    font-weight: 750;
    cursor: pointer;
    transition: .2s;
  }

  .news-page .load-more:hover {
    border-color: #7182ff;
    background: #1b2231;
  }

  .news-page .load-more[hidden] {
    display: none;
  }

  .news-page .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
    white-space: nowrap;
    border: 0;
  }

  /* Pagination */
  .news-page .news-pagination {
    margin-top: 40px;
    display: flex;
    justify-content: center;
    gap: 10px;
  }
  .news-page .news-page-link {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #151a24;
    border: 1px solid #282f3c;
    border-radius: 10px;
    color: #aab3c1;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
    cursor: pointer;
  }
  .news-page .news-page-link:hover, .news-page .news-page-link.active {
    background: #536dff30;
    border-color: #536dff60;
    color: #fff;
    transform: translateY(-2px);
  }

  @media (max-width: 900px) {
    .news-page .grid { grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 15px; }
    .news-page .card { grid-column: span 3; }
    .news-page .featured { min-height: 340px; }
    .news-page .toolbar { align-items: flex-start; flex-direction: column; }
    .news-page .filters-wrapper { width: 100%; }
    .news-page .filters { width: 100%; }
    .news-page .filters-arrow { display: none !important; }
    .news-page .search { flex-basis: auto; width: min(100%, 360px); }
  }

  @media (max-width: 600px) {
    .news-page { padding: 25px 15px 52px; }
    .news-page .featured { display: flex; min-height: 440px; align-items: end; border-radius: 19px; margin-bottom: 28px; }
    .news-page .featured-media { inset: 0; }
    .news-page .featured::after { background: linear-gradient(0deg, #0b0e13 2%, #0b0e13ed 27%, #0b0e1380 65%, #0b0e1330 100%); }
    .news-page .featured-copy { padding: 25px 21px; }
    .news-page .featured h1, .news-page .featured h2 { font-size: 1.7rem; margin: 13px 0 9px; }
    .news-page .featured-copy > p { font-size: .88rem; margin-bottom: 16px; }
    .news-page .toolbar { gap: 13px; }
    .news-page .filters-wrapper { margin-right: -15px; padding-right: 15px; width: calc(100% + 15px); }
    .news-page .filter { padding: 8px 12px; }
    .news-page .grid { grid-template-columns: 1fr; gap: 14px; }
    .news-page .card { grid-column: 1 / -1; display: grid; grid-template-columns: 38% 1fr; min-height: 146px; }
    .news-page .card-image { height: 100%; min-height: 146px; aspect-ratio: auto; }
    .news-page .card-image img { position: absolute; inset: 0; }
    .news-page .game-tag { left: 8px; top: 8px; padding: 4px 6px; font-size: .6rem; }
    .news-page .card-body { padding: 12px 12px 10px; display: flex; flex-direction: column; }
    .news-page .card-kicker { font-size: .65rem; margin-bottom: 5px; }
    .news-page .card h3 { font-size: .88rem; line-height: 1.33; margin-bottom: 6px; }
    .news-page .card-desc { font-size: .74rem; -webkit-line-clamp: 2; margin-bottom: 8px; min-height: 0; }
    .news-page .card-foot { margin-top: auto; padding-top: 8px; font-size: .66rem; }
    .news-page .result-count { display: inline-flex; font-size: .68rem; padding: 4px 8px; }
  }

  @media (max-width: 390px) {
    .news-page .card { grid-template-columns: 34% 1fr; min-height: 125px; }
    .news-page .card-image { min-height: 125px; }
    .news-page .card-desc { display: none; }
    .news-page .featured { min-height: 410px; }
  }

  @media (prefers-reduced-motion: reduce) {
    .news-page *, .news-page *::before, .news-page *::after { scroll-behavior: auto !important; transition: none !important; }
  }
</style>

<div class="news-page">
  <div class="container">
    <?php if (!empty($newsList)): ?>
        <?php 
            // İlk haberi öne çıkan (featured) olarak al
            $featuredNews = $newsList[0]; 
            // Editörün seçiminde olan haber aşağıdaki listede tekrar çıkmasın
            $gridNewsList = array_slice($newsList, 1);
            $featCatTitle = !empty($featuredNews->category_title) ? html_escape($featuredNews->category_title) : 'Genel';
        ?>
        <article class="featured" aria-labelledby="featured-title" data-category="<?= $featCatTitle ?>">
          <div class="featured-media">
            <?php if (!empty($featuredNews->news_image)): ?>
                <img src="<?= getenv('CDN_URL') . 'uploads/news/' . $featuredNews->news_image ?>" alt="<?= html_escape($featuredNews->news_title) ?>" fetchpriority="high" data-image-placeholder="<?= base_url('assets-front/images/placeholder.png') ?>" onerror="this.onerror=null;this.src=this.dataset.imagePlaceholder">
            <?php else: ?>
                <img src="<?= base_url('assets-front/images/placeholder.png') ?>" alt="Görsel Yok">
            <?php endif; ?>
          </div>
          <div class="featured-copy">
            <span class="feature-label"><i></i> Editörün seçimi <?= !empty($featuredNews->category_title) ? '· ' . html_escape($featuredNews->category_title) : '' ?></span>
            <h1 id="featured-title"><?= html_escape($featuredNews->news_title) ?></h1>
            <p>
                <?= !empty($featuredNews->news_summary) ? html_escape($featuredNews->news_summary) : mb_substr(strip_tags($featuredNews->news_content), 0, 160) . '...' ?>
            </p>
            <?php $featDate = !empty($featuredNews->news_published_at) ? $featuredNews->news_published_at : $featuredNews->news_created_at; ?>
            <div class="feature-meta"><span><?= !empty($featuredNews->category_title) ? html_escape($featuredNews->category_title) : 'Haber' ?></span><span><?= date('d.m.Y', strtotime($featDate)) ?></span></div>
            <a class="read-link" href="<?= topupcenter() . $uri . '/news/' . $featuredNews->news_slug ?>" aria-label="Haberi oku">Haberi oku <b aria-hidden="true">→</b></a>
          </div>
        </article>
    <?php else: ?>
        <?php $gridNewsList = []; ?>
    <?php endif; ?>

    <section aria-labelledby="latest-title">
      <div class="toolbar">
        <div class="filters-wrapper">
          <button type="button" class="filters-arrow filters-arrow-left" aria-label="Sola kaydır">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
          <div class="filters" role="group" aria-label="Haberleri kategoriye göre filtrele">
            <button class="filter" type="button" aria-pressed="true" data-filter="all">Tümü</button>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter" type="button" aria-pressed="false" data-filter="<?= html_escape($cat->category_title) ?>"><?= html_escape($cat->category_title) ?></button>
                <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <button type="button" class="filters-arrow filters-arrow-right" aria-label="Sağa kaydır">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
          </button>
        </div>
        <label class="search">
            <span class="sr-only">Haberlerde ara</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
            <input id="news-search" type="search" placeholder="Haberlerde ara..." autocomplete="off">
        </label>
      </div>
      <div class="section-head">
          <h2 id="latest-title">Gündemden seçtiklerimiz</h2>
          <span class="result-count" id="result-count" aria-live="polite"><?= count($gridNewsList ?? []) ?> haber</span>
      </div>

      <?php if (!empty($gridNewsList)): ?>
          <div class="grid" id="news-grid">
            <?php foreach ($gridNewsList as $newsItem): ?>
                <?php $catTitle = !empty($newsItem->category_title) ? html_escape($newsItem->category_title) : 'Genel'; ?>
                <article class="card" data-category="<?= $catTitle ?>" data-title="<?= html_escape($newsItem->news_title) ?>">
                  <a class="card-image" href="<?= topupcenter() . $uri . '/news/' . $newsItem->news_slug ?>">
                       <?php if (!empty($newsItem->news_image)): ?>
                          <img src="<?= getenv('CDN_URL') . 'uploads/news/' . $newsItem->news_image ?>" alt="<?= html_escape($newsItem->news_title) ?>" loading="lazy" data-image-placeholder="<?= base_url('assets-front/images/placeholder.png') ?>" onerror="this.onerror=null;this.src=this.dataset.imagePlaceholder">
                      <?php else: ?>
                          <img src="<?= base_url('assets-front/images/placeholder.png') ?>" alt="Görsel yok">
                      <?php endif; ?>
                      <span class="game-tag"><?= $catTitle ?></span>
                  </a>
                  <div class="card-body">
                      <div class="card-kicker"><i></i> <?= $catTitle ?></div>
                      <h3><a href="<?= topupcenter() . $uri . '/news/' . $newsItem->news_slug ?>"><?= html_escape($newsItem->news_title) ?></a></h3>
                      <p class="card-desc">
                          <?= !empty($newsItem->news_summary) ? html_escape($newsItem->news_summary) : mb_substr(strip_tags($newsItem->news_content), 0, 120) . '...' ?>
                      </p>
                      <div class="card-foot">
                          <?php $itemDate = !empty($newsItem->news_published_at) ? $newsItem->news_published_at : $newsItem->news_created_at; ?>
                          <time datetime="<?= date('Y-m-d', strtotime($itemDate)) ?>"><?= date('d.m.Y', strtotime($itemDate)) ?></time>
                          <a href="<?= topupcenter() . $uri . '/news/' . $newsItem->news_slug ?>">Oku <span aria-hidden="true">↗</span></a>
                      </div>
                  </div>
                </article>
            <?php endforeach; ?>
          </div>
      <?php else: ?>
          <p class="empty is-visible" id="empty-state" role="status">Şu an gösterilecek haber bulunamadı.</p>
      <?php endif; ?>
      
      <p class="empty" id="empty-search-state" role="status">Bu aramayla eşleşen haber bulunamadı. Farklı bir kelime deneyin.</p>

      <!-- Pagination (JS tabanlı dinamik sayfalama) -->
      <div class="news-pagination" id="news-pagination" style="display: none;"></div>

    </section>
  </div>
</div>

<script>
(() => {
  const cards = [...document.querySelectorAll(".card")];
  const featured = document.querySelector(".featured");
  const filters = [...document.querySelectorAll(".filter")];
  const search = document.querySelector("#news-search");
  const count = document.querySelector("#result-count");
  const emptySearch = document.querySelector("#empty-search-state");
  const pagination = document.querySelector("#news-pagination");
  const PAGE_SIZE = 14;
  let currentPage = 1;
  let activeCategory = "all";
  let query = "";

  function render() {
      // 1. Filtre ve aramaya uyan tüm kartları tespit et
      const matching = cards.filter(card => {
          const title = card.dataset.title ? card.dataset.title.toLocaleLowerCase("tr") : "";
          const category = (card.dataset.category || "").trim().toLocaleLowerCase("tr");
          const targetCat = activeCategory.trim().toLocaleLowerCase("tr");
          
          const matchCategory = activeCategory === "all" || category === targetCat;
          const matchQuery = !query || title.includes(query) || card.textContent.toLocaleLowerCase("tr").includes(query);
          
          return matchCategory && matchQuery;
      });

      // 2. Öne çıkan (featured) haber görünürlüğü
      if (featured) {
          const featCategory = (featured.dataset.category || "").trim().toLocaleLowerCase("tr");
          const featTitle = featured.querySelector("h1") ? featured.querySelector("h1").textContent.toLocaleLowerCase("tr") : "";
          const targetCat = activeCategory.trim().toLocaleLowerCase("tr");
          const matchFeatCat = activeCategory === "all" || featCategory === targetCat;
          const matchFeatQuery = !query || featTitle.includes(query) || featured.textContent.toLocaleLowerCase("tr").includes(query);
          featured.hidden = !(matchFeatCat && matchFeatQuery && currentPage === 1);
      }

      // 3. Toplam sayfa sayısı
      const totalPages = Math.ceil(matching.length / PAGE_SIZE) || 1;
      if (currentPage > totalPages) {
          currentPage = 1;
      }

      // 4. Sayfalamaya göre kartları göster / gizle
      const startIndex = (currentPage - 1) * PAGE_SIZE;
      const endIndex = startIndex + PAGE_SIZE;

      cards.forEach(card => {
          const matchIndex = matching.indexOf(card);
          if (matchIndex >= startIndex && matchIndex < endIndex) {
              card.hidden = false;
          } else {
              card.hidden = true;
          }
      });

      // 5. Sayaç ve boş durum
      if (count) count.textContent = matching.length + " haber";
      if (emptySearch) emptySearch.classList.toggle("is-visible", matching.length === 0 && cards.length > 0);

      // 6. Sayfalama butonları
      renderPagination(totalPages);
  }

  function renderPagination(totalPages) {
      if (!pagination) return;

      if (totalPages <= 1) {
          pagination.innerHTML = "";
          pagination.style.display = "none";
          return;
      }

      pagination.style.display = "flex";
      let html = "";
      for (let i = 1; i <= totalPages; i++) {
          html += `<button type="button" class="news-page-link ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
      }
      pagination.innerHTML = html;

      pagination.querySelectorAll(".news-page-link").forEach(btn => {
          btn.addEventListener("click", () => {
              currentPage = parseInt(btn.dataset.page, 10);
              render();
              const latestTitle = document.querySelector("#latest-title");
              if (latestTitle) {
                  latestTitle.scrollIntoView({ behavior: "smooth", block: "start" });
              }
          });
      });
  }

  // Desktop filtre kaydırma (Mouse Wheel + Drag to Scroll + Ok Butonları)
  const filtersContainer = document.querySelector(".filters");
  const arrowLeft = document.querySelector(".filters-arrow-left");
  const arrowRight = document.querySelector(".filters-arrow-right");

  function checkFilterArrows() {
      if (!filtersContainer || !arrowLeft || !arrowRight) return;
      if (window.innerWidth <= 900) {
          arrowLeft.style.display = "none";
          arrowRight.style.display = "none";
          return;
      }
      const maxScroll = filtersContainer.scrollWidth - filtersContainer.clientWidth;
      if (maxScroll <= 4) {
          arrowLeft.style.display = "none";
          arrowRight.style.display = "none";
      } else {
          arrowLeft.style.display = filtersContainer.scrollLeft > 6 ? "inline-flex" : "none";
          arrowRight.style.display = filtersContainer.scrollLeft < maxScroll - 6 ? "inline-flex" : "none";
      }
  }

  if (filtersContainer) {
      filtersContainer.addEventListener("scroll", checkFilterArrows);
      window.addEventListener("resize", checkFilterArrows);
      setTimeout(checkFilterArrows, 150);

      if (arrowLeft) {
          arrowLeft.addEventListener("click", () => {
              filtersContainer.scrollBy({ left: -220, behavior: "smooth" });
          });
      }
      if (arrowRight) {
          arrowRight.addEventListener("click", () => {
              filtersContainer.scrollBy({ left: 220, behavior: "smooth" });
          });
      }

      // 1. Mouse wheel ile yatay kaydırma
      filtersContainer.addEventListener("wheel", (e) => {
          if (e.deltaY !== 0) {
              e.preventDefault();
              filtersContainer.scrollLeft += e.deltaY;
          }
      }, { passive: false });

      // 2. Mouse ile basılı tutup sürükleme (Drag to scroll)
      let isDown = false;
      let startX = 0;
      let scrollStart = 0;
      let hasDragged = false;

      filtersContainer.addEventListener("mousedown", (e) => {
          isDown = true;
          hasDragged = false;
          filtersContainer.classList.add("is-dragging");
          startX = e.pageX - filtersContainer.offsetLeft;
          scrollStart = filtersContainer.scrollLeft;
      });

      window.addEventListener("mouseup", () => {
          if (isDown) {
              isDown = false;
              filtersContainer.classList.remove("is-dragging");
          }
      });

      filtersContainer.addEventListener("mousemove", (e) => {
          if (!isDown) return;
          const x = e.pageX - filtersContainer.offsetLeft;
          const walk = (x - startX) * 1.5;
          if (Math.abs(walk) > 4) {
              hasDragged = true;
          }
          filtersContainer.scrollLeft = scrollStart - walk;
      });

      filters.forEach(btn => btn.addEventListener("click", (e) => {
          if (hasDragged) {
              e.stopImmediatePropagation();
              e.preventDefault();
              hasDragged = false;
              return;
          }
          activeCategory = btn.dataset.filter;
          filters.forEach(item => item.setAttribute("aria-pressed", String(item === btn)));
          btn.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "center" });
          currentPage = 1; // Kategori değiştiğinde 1. sayfaya dön
          render();
      }));
  }

  if (search) {
      search.addEventListener("input", () => {
          query = search.value.trim().toLocaleLowerCase("tr");
          currentPage = 1;
          render();
      });
  }

  render();
})();
</script>
