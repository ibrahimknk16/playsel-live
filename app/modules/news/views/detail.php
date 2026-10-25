<style>
  .article-page {
    --article-bg: #11151d;
    --article-panel: #1e222d;
    --article-panel-2: #171b24;
    --article-line: rgba(255,179,0,.18);
    --article-text: #f2f2f2;
    --article-muted: #9ba6b6;
    --article-blue: #ffb300;
    --article-blue-soft: #ffd166;
    --article-pink: #ff7300;
    position: relative;
    isolation: isolate;
    min-height: 100vh;
    padding: 30px 22px 78px;
    color: var(--article-text);
    background: var(--article-bg);
    font-family: 'Poppins', sans-serif;
    line-height: 1.6;
  }
  .article-page, .article-page * { box-sizing: border-box; }
  .article-page a { color: inherit; text-decoration: none; }
  .article-page button, .article-page a { -webkit-tap-highlight-color: transparent; }
  .article-page a:focus-visible, .article-page button:focus-visible, .article-page summary:focus-visible { outline: 3px solid #a8b3ff; outline-offset: 3px; }
  
  .article-progress { position: fixed; z-index: 20; top: 0; left: 0; width: 100%; height: 3px; background: #ffffff0b; }
  .article-progress span { display: block; width: 100%; height: 100%; background: linear-gradient(90deg, var(--article-blue), #9d67ff, var(--article-pink)); transform: scaleX(0); transform-origin: left center; }
  
  .article-container { width: min(100%, 1180px); margin: 0 auto; }
  
  .article-breadcrumb { display: inline-flex; align-items: center; gap: 9px; padding: 9px 13px; margin: 2px 0 22px; color: #bdc6d6; border: 1px solid var(--article-line); border-radius: 999px; background: #11151d8c; font-size: .82rem; font-weight: 700; transition: background .2s, border-color .2s; }
  .article-breadcrumb:hover { background: #171d28; border-color: #4a568e; }
  .article-breadcrumb svg { width: 16px; height: 16px; color: #96a4ff; }
  
  .article-feature { display: flex; flex-direction: column; gap: 24px; max-width: 1180px; margin: 0 auto 42px; }
  .article-header { width: min(100%, 930px); margin: 0 auto; }
  .article-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 10px 15px; margin-bottom: 14px; color: var(--article-muted); font-size: .78rem; }
  .article-category { display: inline-flex; align-items: center; gap: 7px; border: 1px solid #536dff55; background: #536dff1b; color: #c4ccff; border-radius: 999px; padding: 6px 10px; font-size: .72rem; font-weight: 800; }
  .article-category i { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: var(--article-pink); }
  .article-meta .meta-dot { width: 3px; height: 3px; border-radius: 50%; background: #667184; }
  
  .article-title { margin: 0; color: #f7f8fc; font-size: clamp(2rem, 3.4vw, 2.85rem); line-height: 1.12; letter-spacing: -.045em; font-weight: 820; text-wrap: balance; }
  .article-deck { max-width: 780px; margin: 17px 0 0; color: #aeb8c8; font-size: clamp(.98rem, 1.5vw, 1.12rem); line-height: 1.7; }
  
  .article-author { display: flex; align-items: center; gap: 10px; margin-top: 19px; color: #aab4c4; font-size: .8rem; }
  .author-mark { width: 32px; height: 32px; display: grid; place-items: center; border: 1px solid #7785ff70; border-radius: 50%; background: linear-gradient(145deg, #5069ff3c, #ff3c861c); color: #d8ddff; font-size: .8rem; font-weight: 850; }
  .author-name { color: #f2f4f9; font-weight: 750; }
  .author-role { color: #8792a2; }
  
  .article-cover { display: flex; justify-content: center; overflow: hidden; width: 100%; margin: 0; border-radius: 22px; background: transparent; }
  .article-cover img { display: block; width: auto; max-width: 100%; height: auto; max-height: 680px; object-fit: contain; border: 1px solid #ffffff16; border-radius: 22px; box-shadow: 0 22px 55px #00000038; }
  
  .article-layout { display: grid; grid-template-columns: minmax(0, 800px) minmax(245px, 290px); align-items: start; justify-content: center; gap: 48px; }
  
  .article-content { min-width: 0; color: #c0c8d4; font-size: 1.03rem; line-height: 1.85; }
  .article-content > p:first-child { margin-top: 0; font-size: 1.08rem; color: #e0e5ed; }
  .article-content p { margin: 0 0 22px; }
  .article-content h2, .article-content h3 { color: #f5f7fb; line-height: 1.3; letter-spacing: -.025em; text-wrap: pretty; }
  .article-content h2 { position: relative; margin: 54px 0 20px; padding-top: 4px; font-size: clamp(1.4rem, 2.3vw, 1.8rem); scroll-margin-top: 24px; }
  .article-content h2::before { content: ""; position: absolute; left: -18px; top: 7px; bottom: 4px; width: 3px; border-radius: 3px; background: linear-gradient(180deg, #8190ff, var(--article-blue)); }
  .article-content h3 { margin: 34px 0 14px; font-size: 1.22rem; scroll-margin-top: 24px; }
  .article-content strong { color: #f4f6fb; font-weight: 760; }
  .article-content a { color: #aebaff; text-decoration: underline; text-decoration-color: #7182ff70; text-underline-offset: 3px; }
  .article-content a:hover { color: #fff; text-decoration-color: #fff; }
  .article-content ul, .article-content ol { margin: 0 0 24px; padding-left: 1.4rem; }
  .article-content li { margin: 8px 0; padding-left: 4px; }
  .article-content li::marker { color: #8997ff; }
  .article-content blockquote { margin: 30px 0; padding: 22px 24px; border-left: 3px solid var(--article-blue); border-radius: 0 14px 14px 0; background: linear-gradient(90deg, #536dff18, #ffffff04); color: #e4e8f2; }
  .article-content img { display: block; max-width: 100%; height: auto; margin: 28px auto; border: 1px solid #ffffff14; border-radius: 14px; }
  .article-content table { width: 100%; max-width: 100%; margin: 28px 0; border: 1px solid var(--article-line); border-radius: 12px; border-collapse: collapse; table-layout: fixed; }
  .article-content th, .article-content td { padding: 12px 15px; border: 1px solid var(--article-line); text-align: left; overflow-wrap: anywhere; word-break: break-word; }
  .article-content th { background: #171e2a; color: #fff; }
  
  .article-aside { position: sticky; top: 25px; display: grid; gap: 14px; }
  .toc-card, .aside-cta { overflow: hidden; border: 1px solid var(--article-line); border-radius: 16px; background: linear-gradient(145deg, #141a24, #10141b); }
  .toc-card { padding: 16px 16px 13px; }
  .toc-card summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; list-style: none; color: #f4f6fb; font-size: .91rem; font-weight: 800; cursor: pointer; }
  .toc-card summary::-webkit-details-marker { display: none; }
  .toc-card summary::after { content: "⌄"; color: #9daaff; font-size: 1.2rem; line-height: 1; }
  .toc-card[open] summary::after { content: "⌃"; }
  .toc-list { max-height: min(60vh, 520px); overflow: auto; margin: 12px -4px 0; padding: 0 4px 2px; list-style: none; scrollbar-color: #414b63 transparent; scrollbar-width: thin; }
  .toc-list li + li { margin-top: 3px; }
  .toc-list a { display: block; padding: 7px 9px; border-radius: 8px; color: #9ca7b8; font-size: .76rem; line-height: 1.45; transition: background .18s, color .18s; }
  .toc-list a:hover, .toc-list a[aria-current="location"] { background: #536dff1b; color: #dbe0ff; }
  .toc-list .toc-sub { padding-left: 18px; }
  
  .aside-cta { position: relative; padding: 18px; }
  .aside-cta::before { content: ""; position: absolute; width: 120px; height: 120px; right: -38px; top: -54px; border-radius: 50%; background: #536dff30; filter: blur(20px); }
  .cta-eyebrow { position: relative; color: #aab6ff; font-size: .68rem; font-weight: 850; letter-spacing: .1em; text-transform: uppercase; }
  .aside-cta h2 { position: relative; margin: 8px 0 6px; color: #f4f6fb; font-size: 1rem; line-height: 1.35; }
  .aside-cta p { position: relative; margin: 0 0 14px; color: #97a2b2; font-size: .77rem; line-height: 1.5; }
  .cta-link { position: relative; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1px solid #6678ff5c; border-radius: 10px; background: #536dff20; color: #eef0ff; font-size: .78rem; font-weight: 750; transition: background .2s; }
  .cta-link:hover { background: #536dff40; }
  
  .article-tools { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 37px 0 0; padding-top: 19px; border-top: 1px solid var(--article-line); }
  .article-tools-label { color: #8c97a8; font-size: .76rem; }
  .share-button { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 13px; border: 1px solid #313a49; border-radius: 10px; background: #151a23; color: #e6e9f0; font-size: .78rem; font-weight: 750; cursor: pointer; transition: border-color .2s, background .2s; }
  .share-button:hover { border-color: #7182ff; background: #1b2231; }
  .share-button svg { width: 16px; height: 16px; color: #aab6ff; }
  .share-status { min-height: 1.2em; margin: 9px 0 0; color: #aebaff; font-size: .75rem; text-align: right; }
  
  .related-section { position: relative; margin-top: 68px; padding: 34px 0 0; border-top: 1px solid var(--article-line); }
  .related-heading { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 21px; }
  .related-intro { max-width: 660px; }
  .related-label { display: block; margin-bottom: 8px; color: #aebaff; font-size: .68rem; font-weight: 850; letter-spacing: .13em; text-transform: uppercase; }
  .related-heading h2 { margin: 0; color: #f4f6fb; font-size: clamp(1.35rem, 2.3vw, 1.75rem); letter-spacing: -.035em; }
  .related-heading p { margin: 7px 0 0; color: #929dad; font-size: .85rem; }
  .related-all { display: inline-flex; align-items: center; gap: 9px; flex: 0 0 auto; padding: 10px 13px; border: 1px solid #313a49; border-radius: 10px; color: #dfe4ee; font-size: .76rem; font-weight: 750; transition: background .2s, border-color .2s; }
  .related-all:hover { background: #171d28; border-color: #6575de; }
  
  .related-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
  .related-card { display: flex; min-width: 0; overflow: hidden; flex-direction: column; border: 1px solid #252c39; border-radius: 17px; background: linear-gradient(145deg, #151b26, #10141b); box-shadow: 0 14px 34px #00000024; transition: transform .22s, border-color .22s, box-shadow .22s; }
  .related-card:hover { transform: translateY(-4px); border-color: #5364bf; box-shadow: 0 18px 38px #00000040; }
  .related-image { position: relative; display: block; aspect-ratio: 2/1; overflow: hidden; background: #171d28; }
  .related-image::after { content: ""; position: absolute; inset: 45% 0 0; background: linear-gradient(0deg, #0b0e1390, transparent); }
  .related-image img { display: block; width: 100%; height: 100%; object-fit: cover; transition: transform .35s; }
  .related-card:hover .related-image img { transform: scale(1.04); }
  .related-body { display: flex; flex: 1; flex-direction: column; padding: 16px 18px 15px; }
  .related-kicker { display: flex; align-items: center; gap: 7px; margin-bottom: 8px; color: #aab4c4; font-size: .68rem; font-weight: 750; }
  .related-kicker i { width: 6px; height: 6px; border-radius: 50%; background: var(--article-pink); }
  .related-body h3 { margin: 0; color: #edf0f7; font-size: 1.02rem; line-height: 1.4; letter-spacing: -.015em; }
  .related-body p { margin: 8px 0 16px; color: #929dad; font-size: .79rem; line-height: 1.55; }
  .related-read { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: auto; padding-top: 11px; border-top: 1px solid #ffffff12; color: #c3cbdb; font-size: .74rem; font-weight: 750; }
  .related-card:hover h3, .related-card:hover .related-read { color: #bcc6ff; }
  
  @media (max-width: 980px) { .article-layout { grid-template-columns: minmax(0, 1fr) 250px; gap: 28px; } }
  @media (max-width: 760px) {
    .article-page { padding: 20px 16px 56px; }
    .article-feature { gap: 20px; margin-bottom: 32px; }
    .article-cover { order: 0; border-radius: 17px; }
    .article-cover img { max-height: 580px; border-radius: 17px; }
    .article-header { order: 1; }
    .article-title { font-size: clamp(1.9rem, 5vw, 2.55rem); }
    .article-layout { grid-template-columns: 1fr; gap: 22px; }
    .article-aside { position: static; grid-row: 1; }
    .toc-list { max-height: 340px; }
    .aside-cta { display: none; }
    .article-content { font-size: .98rem; }
    .article-content h2 { margin-top: 42px; padding-left: 13px; }
    .article-content h2::before { left: 0; }
    .article-author { margin-top: 15px; }
    .related-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .article-tools { margin-top: 29px; }
  }
  @media (max-width: 480px) {
    .article-page { padding-inline: 13px; }
    .article-breadcrumb { margin-bottom: 19px; }
    .article-title { font-size: 1.9rem; line-height: 1.15; }
    .article-deck { font-size: .92rem; margin-top: 12px; }
    .article-meta { gap: 8px 10px; }
    .article-meta .meta-dot { display: none; }
    .article-cover img { max-height: 480px; }
    .article-content { font-size: .94rem; line-height: 1.78; }
    .article-content > p:first-child { font-size: 1rem; }
    .article-content h3 { font-size: 1.08rem; }
    .article-content blockquote { padding: 17px; }
    .related-grid { grid-template-columns: 1fr; gap: 13px; }
    .related-image { aspect-ratio: 2.1/1; }
    .related-body { padding: 13px 14px; }
    .related-body h3 { font-size: .91rem; }
    .related-heading { align-items: flex-start; }
    .related-heading h2 { font-size: 1.18rem; }
    .related-heading p { font-size: .79rem; }
    .related-all { padding: 9px 10px; font-size: .69rem; }
    .related-body p { margin-bottom: 12px; }
    .share-button { padding-inline: 10px; }
  }
  @media (prefers-reduced-motion: reduce) { .article-page *, .article-page *::before, .article-page *::after { scroll-behavior: auto !important; transition: none !important; } }
</style>
<?php
  $monthsTr = [
    '01' => 'Ocak', '02' => 'Şubat', '03' => 'Mart', '04' => 'Nisan',
    '05' => 'Mayıs', '06' => 'Haziran', '07' => 'Temmuz', '08' => 'Ağustos',
    '09' => 'Eylül', '10' => 'Ekim', '11' => 'Kasım', '12' => 'Aralık'
  ];
  $pubDate = !empty($news->news_published_at) ? $news->news_published_at : $news->news_created_at;
  $ts = !empty($pubDate) ? strtotime($pubDate) : time();
  $formattedDate = date('d', $ts) . ' ' . ($monthsTr[date('m', $ts)] ?? date('M', $ts)) . ' ' . date('Y', $ts);
?>
<div class="article-page">
  <div class="article-progress" aria-hidden="true"><span id="article-progress-fill"></span></div>
  <div class="article-container">
    <a class="article-breadcrumb" href="<?= topupcenter() . $uri . '/news' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/><path d="M20 12H9"/></svg>
      Haberlere dön
    </a>

    <section class="article-feature" aria-label="Haber özeti ve kapak görseli">
    <?php if (!empty($news->news_image)): ?>
    <figure class="article-cover">
      <img src="<?= getenv('CDN_URL') . 'uploads/news/' . $news->news_image ?>" alt="<?= html_escape($news->news_title) ?>" fetchpriority="high" onerror="this.onerror=null;this.src='<?= base_url('assets-front/images/placeholder.png') ?>'">
    </figure>
    <?php endif; ?>
    <header class="article-header">
      <div class="article-meta">
        <span class="article-category"><i></i> <?= !empty($news->category_title) ? html_escape($news->category_title) : 'Haber' ?></span>
        <span class="meta-dot" aria-hidden="true"></span>
        <time datetime="<?= $formattedDate ?>"><?= $formattedDate ?></time>
        <span class="meta-dot" aria-hidden="true"></span>
        <span id="reading-time">Okuma süresi hesaplanıyor…</span>
      </div>
      <h1 class="article-title"><?= html_escape($news->news_title) ?></h1>
      <?php if (!empty($news->news_summary)): ?>
      <p class="article-deck"><?= html_escape($news->news_summary) ?></p>
      <?php endif; ?>
      <div class="article-author">
          <span class="author-mark" aria-hidden="true">P</span>
          <span class="author-name">Playsel</span>
          <span class="author-role">· <?= number_format($news->news_views) ?> Görüntülenme</span>
      </div>
    </header>
    </section>

    <div class="article-layout">
      <article class="article-content" id="article-content">
        <?= news_content_clean_html($news->news_content) ?>
        <div class="article-tools">
          <span class="article-tools-label">Bu rehber işine yaradı mı?</span>
          <button class="share-button" id="share-article" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.7 6.8-4.4M8.6 13.3l6.8 4.4"/></svg>
            Paylaş
          </button>
        </div>
        <p class="share-status" id="share-status" aria-live="polite"></p>
      </article>

      <aside class="article-aside">
        <details class="toc-card" id="toc-details" open>
          <summary>Bu yazıda</summary>
          <nav aria-label="Makale içindekiler">
            <ol class="toc-list" id="toc-list"></ol>
          </nav>
        </details>
        <section class="aside-cta" aria-labelledby="aside-cta-title">
          <span class="cta-eyebrow"><?= !empty($news->category_title) ? html_escape($news->category_title) : 'Genel' ?></span>
          <h2 id="aside-cta-title">Oyuna kaldığın yerden devam et</h2>
          <p>Tüm oyunlar için en uygun e-pin ve bakiye seçeneklerini keşfet.</p>
          <?php
            $categoryUrl = !empty($news->category_slug) ? topupcenter() . $uri . '/' . $news->category_slug : topupcenter() . $uri;
            $categoryName = trim((string)($news->category_title ?? ''));
            $categoryType = strtolower(trim((string)($news->category_type ?? '')));
            $ctaLabel = $categoryName !== '' ? $categoryName : 'Ürünleri';
            if (strpos($categoryType, 'topup') !== false || strpos($categoryType, 'yük') !== false) {
              $ctaLabel .= ' Yükle';
            } elseif (strpos($categoryType, 'voucher') !== false || strpos($categoryType, 'kart') !== false) {
              $ctaLabel .= ' Satın Al';
            } else {
              $ctaLabel .= ' Ürünleri';
            }
          ?>
          <a class="cta-link" href="<?= html_escape($categoryUrl) ?>"><?= html_escape($ctaLabel) ?> <span aria-hidden="true">↗</span></a>
        </section>
      </aside>
    </div>

    <?php if (!empty($relatedNews)): ?>
    <section class="related-section" aria-labelledby="related-title">
      <div class="related-heading">
        <div class="related-intro">
            <span class="related-label">İlginizi Çekebilir</span>
            <p>Sizin için seçtiğimiz diğer içeriklere de göz atın.</p>
        </div>
        <a class="related-all" href="<?= topupcenter() . $uri . '/news' ?>">Tüm haberler <span aria-hidden="true">→</span></a>
      </div>
      <div class="related-grid">
        <?php foreach ($relatedNews as $related): ?>
            <?php $relatedCat = !empty($related->category_title) ? html_escape($related->category_title) : 'Genel'; ?>
            <a class="related-card" href="<?= topupcenter() . $uri . '/news/' . $related->news_slug ?>">
              <span class="related-image">
                  <?php if (!empty($related->news_image)): ?>
                      <img src="<?= getenv('CDN_URL') . 'uploads/news/' . $related->news_image ?>" alt="<?= html_escape($related->news_title) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= base_url('assets-front/images/placeholder.png') ?>'">
                  <?php else: ?>
                      <img src="<?= base_url('assets-front/images/placeholder.png') ?>" alt="Görsel yok" loading="lazy">
                  <?php endif; ?>
              </span>
              <span class="related-body">
                  <span class="related-kicker"><i></i> <?= $relatedCat ?></span>
                  <h3><?= html_escape($related->news_title) ?></h3>
                  <p><?= !empty($related->news_summary) ? html_escape($related->news_summary) : mb_substr(strip_tags($related->news_content), 0, 80) . '...' ?></p>
                  <span class="related-read">Haberi oku <span aria-hidden="true">↗</span></span>
              </span>
            </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  </div>
</div>

<script>
(() => {
  const content = document.querySelector("#article-content");
  const list = document.querySelector("#toc-list");
  const headings = [...content.querySelectorAll("h2")];
  const toc = document.querySelector("#toc-details");
  const text = content.innerText || content.textContent || "";
  const wordCount = text.trim().split(/\s+/).filter(Boolean).length;
  const minutes = Math.max(1, Math.ceil(wordCount / 210));
  
  const readingTimeEl = document.querySelector("#reading-time");
  if(readingTimeEl) readingTimeEl.textContent = minutes + " dk okuma";

  headings.forEach((heading, index) => {
    const id = "bolum-" + (index + 1);
    heading.id = id;
    const item = document.createElement("li");
    const link = document.createElement("a");
    link.href = "#" + id;
    link.textContent = heading.textContent.trim();
    item.append(link);
    if(list) list.append(item);
  });
  content.querySelectorAll("h3").forEach((heading, index) => { heading.id = "alt-bolum-" + (index + 1); });

  if (window.matchMedia("(max-width: 760px)").matches) {
      if(toc) toc.open = false;
  }

  const observer = new IntersectionObserver(entries => {
    const visible = entries.filter(entry => entry.isIntersecting).sort((a,b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
    if (!visible) return;
    if(list) list.querySelectorAll("a").forEach(link => link.removeAttribute("aria-current"));
    const active = list ? list.querySelector('a[href="#' + visible.target.id + '"]') : null;
    if (active) active.setAttribute("aria-current", "location");
  }, { rootMargin: "-12% 0px -72% 0px" });
  
  headings.forEach(heading => observer.observe(heading));

  const progress = document.querySelector("#article-progress-fill");
  function updateProgress(){
    const max = document.documentElement.scrollHeight - window.innerHeight;
    if(progress) progress.style.transform = "scaleX(" + (max > 0 ? Math.min(1, window.scrollY / max) : 0) + ")";
  }
  window.addEventListener("scroll", updateProgress, {passive:true});
  window.addEventListener("resize", updateProgress);
  updateProgress();

  const shareButton = document.querySelector("#share-article");
  const shareStatus = document.querySelector("#share-status");
  if(shareButton) {
      shareButton.addEventListener("click", async () => {
        const payload = {title: document.title, text: document.querySelector(".article-title").textContent, url: window.location.href};
        try {
          if (navigator.share) await navigator.share(payload);
          else { await navigator.clipboard.writeText(payload.url); if(shareStatus) shareStatus.textContent = "Bağlantı kopyalandı."; }
        } catch (error) {
          if (error.name !== "AbortError" && shareStatus) shareStatus.textContent = "Bağlantı paylaşılamadı.";
        }
      });
  }
})();
</script>
