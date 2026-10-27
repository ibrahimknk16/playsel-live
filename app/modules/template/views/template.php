<!DOCTYPE html>
<?php
$templateLocale = !empty($currentLocale) ? $currentLocale->locale_lang_code . '-' . $currentLocale->locale_code : 'en-US';
$templateDirection = !empty($currentLocale->locale_direction) ? $currentLocale->locale_direction : 'ltr';
$templateSegments = array_values($this->uri->segment_array());
$templatePathSegments = $templateSegments;
if (!empty($templatePathSegments)) {
  array_shift($templatePathSegments);
}
$templatePath = !empty($templatePathSegments) ? '/' . implode('/', $templatePathSegments) : '';
$templateCanonicalPath = parse_url(current_url(), PHP_URL_PATH);
?>
<html lang="<?= html_escape($templateLocale) ?>" dir="<?= html_escape($templateDirection) ?>">
<?php
$version = 1;
$versionedAssets = [
  'assets-front/css/main-front.css',
  'assets-front/css/site-polish.css',
  'assets-front/css/toastr.min.css',
  'assets-front/js/jquery.min.js',
  'assets-front/js/bootstrap.bundle.min.js',
  'assets-front/js/toastr.min.js',
  'assets-front/js/sweetalert2@11.min.js',
  'assets-front/js/main.js',
];
foreach ($versionedAssets as $assetPath) {
  $assetFile = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $assetPath);
  if (is_file($assetFile)) {
    $version = max($version, (int) filemtime($assetFile));
  }
}
?>

<head>
  <!-- Basic Page Needs -->
  <meta charset="utf-8">
  <!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->
  <?php
  $configuredSiteName = trim((string) site_setting('site_name', getenv('SITE_NAME') ?: 'Playsel'));
  $siteNameParts = preg_split('/\s*\|\s*/u', $configuredSiteName, 2);
  $siteName = trim((string) ($siteNameParts[0] ?? ''));
  if ($siteName === '') {
    $siteName = 'Playsel';
  }
  ?>
  <?php $siteDescription = site_setting('site_meta_description', $siteName . ' - En Uygun Fiyatlı E-Pin ve Top-Up Yükleme Merkezi'); ?>
  <?php
  $siteCanonicalUrl = site_setting('site_canonical_url');
  $requestHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
  $isStagingHost = $requestHost === 'qq.playsel.com';
  $siteRobotsMeta = $isStagingHost
    ? 'noindex,nofollow'
    : ($pageRobots ?? site_setting('site_robots_meta', 'index,follow'));
  $googleAnalyticsId = trim((string) site_setting('google_analytics_id'));
  $googleTagManagerId = trim((string) site_setting('google_tag_manager_id'));
  $metaPixelId = trim((string) getenv('META_PIXEL_ID'));
  if ($metaPixelId === '') {
    $metaPixelId = trim((string) site_setting('meta_pixel_id'));
  }
  $googleSiteVerification = trim((string) site_setting('google_site_verification'));
  $bingSiteVerification = trim((string) site_setting('bing_site_verification'));
  $customHeadCode = site_setting('custom_head_code');
  $customBodyStartCode = site_setting('custom_body_start_code');
  $customBodyEndCode = site_setting('custom_body_end_code');
  $customCss = site_setting('custom_css');
  $customJs = site_setting('custom_js');
  ?>
  <?php
  $isHomePage = empty($templatePathSegments);
  $pageTitle = trim(strip_tags((string) ($title ?? '')));
  $titleParts = preg_split('/\s*\|\s*/u', $pageTitle);
  $titleParts = array_map('trim', $titleParts ?: []);
  $brandPartIndex = null;
  foreach ($titleParts as $titlePartIndex => $titlePart) {
    if ($titlePart !== '' && (strcasecmp($titlePart, $siteName) === 0 || strcasecmp($titlePart, 'Playsell') === 0 || strcasecmp($titlePart, 'Playsel') === 0)) {
      $brandPartIndex = $titlePartIndex;
      break;
    }
  }
  if ($brandPartIndex !== null) {
    $titleParts = array_slice($titleParts, 0, $brandPartIndex);
  }
  $pageTitle = trim(implode(' | ', array_filter($titleParts, static function ($part) {
    return $part !== '';
  })));
  $documentTitle = $isHomePage
    ? 'Playsell | E-Pin ve Dijital Hediye Kartları'
    : ($pageTitle !== '' ? $pageTitle . ' | Playsell' : 'Playsell');
  $documentDescription = trim(strip_tags((string) ($description ?? '')));
  if ($documentDescription === '' && $isHomePage) {
    $documentDescription = trim(strip_tags((string) $siteDescription));
  }
  $canonicalBase = trim((string) $siteCanonicalUrl);
  if ($canonicalBase === '') {
    $canonicalBase = base_url();
  }
  $canonicalUrl = rtrim($canonicalBase, '/') . '/' . ltrim((string) $templateCanonicalPath, '/');
  ?>
  <title><?= html_escape($documentTitle) ?></title>
  <?php if ($documentDescription !== ''): ?>
    <meta name="description" content="<?= html_escape($documentDescription) ?>">
  <?php endif; ?>
  <?php $pageKeywords = trim((string) ($keywords ?? site_setting('site_meta_keywords'))); ?>
  <?php if ($pageKeywords !== ''): ?>
    <meta name="keywords" content="<?= html_escape($pageKeywords) ?>"><?php endif; ?>
  <meta name="author" content="<?= html_escape($siteName) ?>">
  <meta name="robots" content="<?= html_escape($siteRobotsMeta) ?>">
  <?php if ($googleSiteVerification !== ''): ?>
    <meta name="google-site-verification" content="<?= html_escape($googleSiteVerification) ?>"><?php endif; ?>
  <?php if ($bingSiteVerification !== ''): ?>
    <meta name="msvalidate.01" content="<?= html_escape($bingSiteVerification) ?>"><?php endif; ?>
  <link rel="canonical" href="<?= html_escape($canonicalUrl) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="google-site-verification" content="VcsosXYa-RX_cD9arX-EYrDVkwYX6kc7upy73vxiGTQ" />
  <meta name="yandex-verification" content="eefaf3c438b3c371" />
  <!-- 1. GOOGLE FONTS ASENKRON YÜKLEME -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
    media="print" onload="this.media='all'">
  <noscript>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
  </noscript>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- 2. CSS DOSYALARINI ASENKRON VE PRELOAD İLE YÜKLEME -->
  <link rel="preload" href="<?= topupcenter() ?>assets-front/css/main-front.css?v=<?= $version ?>" as="style">
  <link rel="stylesheet" href="<?= topupcenter() ?>assets-front/css/main-front.css?v=<?= $version ?>"
    onload="this.media='all'">
  <noscript>
    <link rel="stylesheet" href="<?= topupcenter() ?>assets-front/css/main-front.css?v=<?= $version ?>">
  </noscript>

  <?php if ($isHomePage && isset($sliders) && is_array($sliders)): ?>
    <?php
    $homeHeroImage = topupcenter() . 'assets-front/images/hero_banner.png';
    foreach ($sliders as $homeSliderCategory) {
      if (!empty($homeSliderCategory['items'][0]->slider_image)) {
        $homeHeroImage = rtrim((string) getenv('CDN_URL'), '/') . '/uploads/sliders/' . ltrim($homeSliderCategory['items'][0]->slider_image, '/');
        break;
      }
    }
    ?>
    <link rel="preload" href="<?= html_escape($homeHeroImage) ?>" as="image" fetchpriority="high">
  <?php endif; ?>

  <link rel="preload" href="<?= topupcenter() ?>assets-front/css/toastr.min.css?v=<?= $version ?>" as="style">
  <link rel="stylesheet" href="<?= topupcenter() ?>assets-front/css/toastr.min.css?v=<?= $version ?>"
    onload="this.media='all'">

  <?php if (!$isHomePage): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/23.0.4/css/intlTelInput.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/23.0.4/js/intlTelInput.min.js"></script>
  <?php endif; ?>

  <style>
    <?php
    $primaryColor = site_setting('primary_color', '#c79a3e');
    $secondaryColor = site_setting('secondary_color', '#f98a2c');
    if (preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor) && preg_match('/^#[0-9a-fA-F]{6}$/', $secondaryColor)):
      ?>
      :root {
        --accent:
          <?= $primaryColor ?>
        ;
        --secondary:
          <?= $secondaryColor ?>
        ;
      }

    <?php endif; ?>
    /* Select2 Dark Mode Fixes */
    .select2-container--default .select2-selection--single {
      background-color: rgba(255, 255, 255, 0.02) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      border-radius: var(--radius-sm, 4px) !important;
      height: 48px !important;
      display: flex !important;
      align-items: center !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #fff !important;
      padding-left: 14px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 46px !important;
    }

    .select2-dropdown {
      background-color: #1a1a2e !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
      background-color: rgba(255, 255, 255, 0.1) !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: var(--accent, #c79a3e) !important;
      color: #000 !important;
    }

    .select2-search--dropdown .select2-search__field {
      background-color: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
    }

    .iti {
      width: 100%
    }

    .iti__country-list {
      z-index: 1100
    }
  </style>
  <?php if ($customCss !== ''): ?>
    <style>
      <?= $customCss ?>
    </style><?php endif; ?>
  <?php if (preg_match('/^G-[A-Z0-9]+$/i', $googleAnalyticsId)): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= rawurlencode($googleAnalyticsId) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag() { dataLayer.push(arguments); }
      gtag('js', new Date());
      gtag('config', '<?= html_escape($googleAnalyticsId) ?>');
    </script>
  <?php endif; ?>
  <?php if (preg_match('/^[0-9]+$/', $metaPixelId)): ?>
    <script>
      !function (f, b, e, v, n, t, s) {
        if (f.fbq) return; n = f.fbq = function () {
          n.callMethod ?
            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
        }; if (!f._fbq) f._fbq = n;
        n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0;
        t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s)
      }
        (window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', <?= json_encode($metaPixelId) ?>);
      fbq('track', 'PageView');
    </script>
  <?php endif; ?>
  <?php if (preg_match('/^GTM-[A-Z0-9]+$/i', $googleTagManagerId)): ?>
    <script>(function (w, d, s, l, i) { w[l] = w[l] || []; w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' }); var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f); })(window, document, 'script', 'dataLayer', '<?= html_escape($googleTagManagerId) ?>');</script>
  <?php endif; ?>
  <?= $customHeadCode ?>

  <!-- Favicon and Touch Icons  -->
  <?php $siteFavicon = site_setting('site_favicon_url', topupcenter() . 'assets-front/images/icons/favicon.ico'); ?>
  <link rel="shortcut icon" href="<?= getenv('CDN_URL') ?>uploads/cdn-gallery/general/favicon.ico">
  <link rel="apple-touch-icon-precomposed" href="<?= getenv('CDN_URL') ?>uploads/cdn-gallery/general/favicon.ico">

  <?php
  // Controller'dan özel bir meta gelmediyse, varsayılan (Anasayfa/Statik) değerleri ata
  $og_title = !empty($og_title) ? $og_title : $documentTitle;
  $og_description = !empty($og_description) ? $og_description : ($documentDescription !== '' ? $documentDescription : $siteDescription);
  $og_image = !empty($og_image) ? $og_image : (!empty($category->category_image)
    ? rtrim((string) getenv('CDN_URL'), '/') . '/uploads/categories/' . ltrim($category->category_image, '/')
    : site_setting('site_og_default_image', 'https://cdn1.playsel.com/uploads/cdn-gallery/general/cdn_6ade20ca64ef16.59908290.webp'));
  $og_url = $canonicalUrl;
  $og_type = !empty($meta_type) ? $meta_type : 'website';
  ?>

  <meta property="og:title" content="<?= htmlspecialchars($og_title, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($og_description, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image" content="<?= htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:url" content="<?= html_escape($og_url) ?>">
  <meta property="og:type" content="<?= html_escape($og_type) ?>">
  <meta property="og:site_name" content="<?= html_escape($siteName) ?>">
  <?php if ($og_type === 'article'): ?>
    <?php if (!empty($article_published_time)): ?>
      <meta property="article:published_time" content="<?= html_escape($article_published_time) ?>"><?php endif; ?>
    <?php if (!empty($article_modified_time)): ?>
      <meta property="article:modified_time" content="<?= html_escape($article_modified_time) ?>"><?php endif; ?>
    <?php if (!empty($article_section)): ?>
      <meta property="article:section" content="<?= html_escape($article_section) ?>"><?php endif; ?>
    <?php foreach (preg_split('/\s*,\s*/', (string) ($article_tags ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $articleTag): ?>
      <meta property="article:tag" content="<?= html_escape($articleTag) ?>">
    <?php endforeach; ?>
  <?php endif; ?>
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= html_escape($og_title) ?>">
  <meta name="twitter:description" content="<?= html_escape($og_description) ?>">
  <meta name="twitter:image" content="<?= html_escape($og_image) ?>">
  <?php foreach ($locales as $locale): ?>
    <?php
    $langPrefix = strtolower($locale->locale_lang_code) . '-' . strtoupper($locale->locale_code);
    ?>
    <link rel="alternate" href="<?= html_escape(base_url($langPrefix . $templatePath)) ?>"
      hreflang="<?= html_escape($langPrefix) ?>">
  <?php endforeach; ?>
  <?php if (!empty($locales)): ?>
    <?php $defaultLocale = reset($locales); ?>
    <?php $defaultLocaleSlug = strtolower($defaultLocale->locale_lang_code) . '-' . strtoupper($defaultLocale->locale_code); ?>
    <link rel="alternate" href="<?= html_escape(base_url($defaultLocaleSlug . $templatePath)) ?>" hreflang="x-default">
  <?php endif; ?>

  <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url() ?>assets-front/icon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url() ?>assets-front/icon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url() ?>assets-front/icon/favicon-16x16.png">
  <link rel="manifest" href="<?= base_url() ?>assets-front/icon/site.webmanifest">

</head>


<body class="body">
  <?php if (preg_match('/^GTM-[A-Z0-9]+$/i', $googleTagManagerId)): ?>
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= rawurlencode($googleTagManagerId) ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <?php endif; ?>
  <?= $customBodyStartCode ?>
  <div id="wrapper">
    <div id="page" class="home-1">
      <?= $this->template->widget("header", array('country' => $currentLocale->locale_code ?? 'TR')); ?>
      <?= $this->template->content; ?>
      <?= $this->template->widget("footer", array()); ?>
      <link rel="stylesheet" href="<?= topupcenter() ?>assets-front/css/site-polish.css?v=<?= $version ?>">
    </div>
  </div>


  <?php
  $schemaBaseUrl = rtrim(topupcenter(), '/');
  $schemaPageUrl = current_url();
  $schemaSiteName = site_setting('site_name', getenv('SITE_NAME') ?: 'Playsel');
  $schemaPhone = site_setting('contact_phone');
  $schemaLogo = site_setting('site_logo_url', $schemaBaseUrl . '/assets-front/images/logo/playsel-beyaz.webp');
  $schemaDescription = !empty($description) ? trim(strip_tags((string) $description)) : 'Güvenli ve hızlı dijital oyun ürünleri, e-pin, CD-Key ve hediye kartları.';
  $schemaImage = !empty($category->category_image)
    ? getenv('CDN_URL') . 'uploads/categories/' . $category->category_image
    : $schemaBaseUrl . '/assets-front/images/logo/playsel-beyaz.webp';
  $schemaLocale = !empty($currentLocale->locale_lang_code) ? $currentLocale->locale_lang_code . '-' . $currentLocale->locale_code : 'tr-TR';
  $schemaSegments = array_values($this->uri->segment_array());
  $schemaPathSegments = $schemaSegments;
  if (!empty($schemaPathSegments)) {
    array_shift($schemaPathSegments);
  }
  $schemaBreadcrumbItems = [];
  $schemaBreadcrumbItems[] = [
    '@type' => 'ListItem',
    'position' => 1,
    'name' => 'Ana Sayfa',
    'item' => $schemaBaseUrl . '/' . ($schemaSegments[0] ?? $schemaLocale),
  ];
  $schemaPath = '';
  foreach ($schemaPathSegments as $schemaIndex => $schemaSegment) {
    $schemaPath .= ($schemaPath === '' ? '' : '/') . $schemaSegment;
    $schemaBreadcrumbItems[] = [
      '@type' => 'ListItem',
      'position' => count($schemaBreadcrumbItems) + 1,
      'name' => ucwords(str_replace(['-', '_'], ' ', $schemaSegment)),
      'item' => $schemaBaseUrl . '/' . ($schemaSegments[0] ?? $schemaLocale) . '/' . $schemaPath,
    ];
  }

  $schemaGraph = [
    [
      '@type' => 'Organization',
      '@id' => $schemaBaseUrl . '/#organization',
      'name' => $schemaSiteName,
      'url' => $schemaBaseUrl . '/',
      'logo' => [
        '@type' => 'ImageObject',
        'url' => $schemaLogo,
      ],
      'contactPoint' => [
        [
          '@type' => 'ContactPoint',
          'telephone' => $schemaPhone,
          'contactType' => 'customer service',
          'areaServed' => 'TR',
          'availableLanguage' => ['tr', 'en'],
        ]
      ],
    ],
    [
      '@type' => 'WebSite',
      '@id' => $schemaBaseUrl . '/#website',
      'name' => $schemaSiteName,
      'url' => $schemaBaseUrl . '/',
      'publisher' => ['@id' => $schemaBaseUrl . '/#organization'],
      'inLanguage' => $schemaLocale,
    ],
    [
      '@type' => 'WebPage',
      '@id' => $schemaPageUrl . '#webpage',
      'url' => $schemaPageUrl,
      'name' => trim(strip_tags((string) ($title ?? $schemaSiteName))),
      'description' => $schemaDescription,
      'isPartOf' => ['@id' => $schemaBaseUrl . '/#website'],
      'publisher' => ['@id' => $schemaBaseUrl . '/#organization'],
      'inLanguage' => $schemaLocale,
    ],
    [
      '@type' => 'BreadcrumbList',
      '@id' => $schemaPageUrl . '#breadcrumb',
      'itemListElement' => $schemaBreadcrumbItems,
    ],
  ];

  $schemaSegmentTwo = isset($schemaSegments[1]) ? $schemaSegments[1] : '';
  $schemaIsHome = empty($schemaSegmentTwo);
  $schemaIsNewsDetail = isset($news) && is_object($news);
  $schemaIsCategoryDetail = isset($category) && is_object($category);
  $schemaIsCategoryList = !$schemaIsCategoryDetail && !empty($categories) && in_array($schemaSegmentTwo, ['categories', 'topup', 'voucher'], true);

  if ($schemaIsHome) {
    $schemaGraph[2]['@type'] = 'WebPage';
    $schemaGraph[2]['mainEntity'] = ['@id' => $schemaBaseUrl . '/#featured-categories'];
    $schemaCategoryItems = [];
    foreach (array_slice((array) $categories, 0, 12) as $schemaIndex => $schemaCategory) {
      $schemaCategoryItems[] = [
        '@type' => 'ListItem',
        'position' => $schemaIndex + 1,
        'name' => (string) ($schemaCategory->category_title ?? ''),
        'url' => $schemaBaseUrl . '/' . ($schemaSegments[0] ?? $schemaLocale) . '/' . ($schemaCategory->category_slug ?? ''),
      ];
    }
    if (!empty($schemaCategoryItems)) {
      $schemaGraph[] = [
        '@type' => 'ItemList',
        '@id' => $schemaBaseUrl . '/#featured-categories',
        'name' => 'Popüler kategoriler',
        'numberOfItems' => count($schemaCategoryItems),
        'itemListElement' => $schemaCategoryItems,
      ];
    }
  }

  if ($schemaIsCategoryList) {
    $schemaGraph[2]['@type'] = 'CollectionPage';
    $schemaItems = [];
    foreach ((array) $categories as $schemaIndex => $schemaCategory) {
      $schemaItems[] = [
        '@type' => 'ListItem',
        'position' => $schemaIndex + 1,
        'name' => (string) ($schemaCategory->category_title ?? ''),
        'url' => $schemaBaseUrl . '/' . ($schemaSegments[0] ?? $schemaLocale) . '/' . ($schemaCategory->category_slug ?? ''),
        'item' => [
          '@type' => 'Thing',
          'name' => (string) ($schemaCategory->category_title ?? ''),
        ],
      ];
    }
    $schemaGraph[] = [
      '@type' => 'ItemList',
      '@id' => $schemaPageUrl . '#category-list',
      'name' => trim(strip_tags((string) ($pageTitle ?? 'Kategoriler'))),
      'numberOfItems' => count($schemaItems),
      'itemListElement' => $schemaItems,
    ];
  }

  if ($schemaIsCategoryDetail) {
    $schemaProducts = (array) ($seoProducts ?? []);
    $schemaProductItems = [];
    foreach (array_slice($schemaProducts, 0, 50) as $schemaIndex => $schemaProduct) {
      $schemaProductUrl = $schemaPageUrl . '?p=' . (int) ($schemaProduct->product_id ?? 0);
      $schemaBrand = trim((string) ($category->category_publisher ?? ''));
      if ($schemaBrand === '') {
        $schemaBrand = $schemaSiteName;
      }
      $schemaProductImage = trim((string) ($schemaProduct->product_icon ?? $schemaProduct->product_image ?? ''));
      if ($schemaProductImage !== '' && strpos($schemaProductImage, 'http') !== 0) {
        $schemaProductImage = rtrim((string) getenv('CDN_URL'), '/') . '/' . ltrim($schemaProductImage, '/');
      }
      $schemaProductEntity = [
        '@type' => 'Product',
        'name' => trim((string) ($schemaProduct->product_name ?? '')),
        'url' => $schemaProductUrl,
        'image' => $schemaProductImage !== '' ? $schemaProductImage : $schemaImage,
        'brand' => ['@type' => 'Brand', 'name' => $schemaBrand],
      ];
      if (isset($schemaProduct->user_price) && is_numeric($schemaProduct->user_price)) {
        $schemaProductEntity['offers'] = [
          '@type' => 'Offer',
          'url' => $schemaProductUrl,
          'priceCurrency' => $currentLocale->locale_currency ?? 'TRY',
          'price' => number_format((float) $schemaProduct->user_price, 2, '.', ''),
          'priceValidUntil' => date('Y-12-31'),
          'availability' => 'https://schema.org/InStock',
          'itemCondition' => 'https://schema.org/NewCondition',
        ];
      }
      $schemaProductItems[] = [
        '@type' => 'ListItem',
        'position' => $schemaIndex + 1,
        'item' => $schemaProductEntity,
      ];
    }
    $schemaGraph[2]['@type'] = 'CollectionPage';
    $schemaGraph[2]['mainEntity'] = ['@id' => $schemaPageUrl . '#products'];
    $schemaGraph[] = [
      '@type' => 'ItemList',
      '@id' => $schemaPageUrl . '#products',
      'name' => (string) ($h1_tag ?? $category->category_title ?? 'Ürünler'),
      'numberOfItems' => count($schemaProductItems),
      'itemListElement' => $schemaProductItems,
    ];
    if (!empty($category_faqs)) {
      $schemaFaqItems = [];
      foreach (array_slice((array) $category_faqs, 0, 20) as $schemaFaq) {
        $schemaFaqItems[] = [
          '@type' => 'Question',
          'name' => trim(strip_tags((string) ($schemaFaq->faq_question ?? ''))),
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => trim(strip_tags((string) ($schemaFaq->faq_answer ?? ''))),
          ],
        ];
      }
      if (!empty($schemaFaqItems)) {
        $schemaGraph[] = [
          '@type' => 'FAQPage',
          '@id' => $schemaPageUrl . '#faq',
          'mainEntity' => $schemaFaqItems,
        ];
      }
    }
  }

  if ($schemaIsNewsDetail) {
    $this->load->helper('news_schema');
    $schemaNewsTitle = news_schema_text($news->news_title ?? '');
    $schemaNewsImage = !empty($news->news_image) ? getenv('CDN_URL') . 'uploads/news/' . $news->news_image : $schemaImage;
    $schemaNewsPubTime = !empty($news->news_published_at) ? $news->news_published_at : ($news->news_created_at ?? null);
    $schemaNewsPublished = !empty($schemaNewsPubTime) ? date('c', strtotime($schemaNewsPubTime)) : null;
    $schemaNewsModified = !empty($news->news_updated_at) ? date('c', strtotime($news->news_updated_at)) : $schemaNewsPublished;
    $schemaNewsBody = news_schema_text($news->news_content ?? '');

    $schemaGraph[2]['@type'] = ['WebPage'];
    $schemaGraph[2]['mainEntity'] = ['@id' => $schemaPageUrl . '#article'];
    $schemaGraph[2]['primaryImageOfPage'] = ['@type' => 'ImageObject', 'url' => $schemaNewsImage];

    $schemaArticle = [
      '@type' => 'NewsArticle',
      '@id' => $schemaPageUrl . '#article',
      'mainEntityOfPage' => ['@id' => $schemaPageUrl . '#webpage'],
      'url' => $schemaPageUrl,
      'headline' => mb_substr($schemaNewsTitle, 0, 110),
      'name' => $schemaNewsTitle,
      'description' => $schemaDescription,
      'articleBody' => $schemaNewsBody,
      'wordCount' => str_word_count($schemaNewsBody) ?: count(preg_split('/\s+/u', $schemaNewsBody, -1, PREG_SPLIT_NO_EMPTY)),
      'image' => [$schemaNewsImage],
      'inLanguage' => $schemaLocale,
      'isPartOf' => ['@id' => $schemaBaseUrl . '/#website'],
      'author' => ['@id' => $schemaBaseUrl . '/#organization'],
      'publisher' => ['@id' => $schemaBaseUrl . '/#organization'],
    ];
    if (!empty($news->category_title)) {
      $schemaArticle['articleSection'] = (string) $news->category_title;
    }
    if ($schemaNewsPublished) {
      $schemaArticle['datePublished'] = $schemaNewsPublished;
      $schemaArticle['dateModified'] = $schemaNewsModified;
    }
    $schemaGraph[] = $schemaArticle;
    foreach (news_schema_graph_nodes($news, $schemaPageUrl, $schemaNewsImage, $schemaNewsPublished) as $schemaNewsNode) {
      $schemaGraph[] = $schemaNewsNode;
    }

    $schemaLastBreadcrumb = count($schemaBreadcrumbItems) - 1;
    foreach ($schemaGraph as $schemaGraphKey => $schemaGraphNode) {
      if (($schemaGraphNode['@type'] ?? '') === 'BreadcrumbList') {
        $schemaItemsRef = &$schemaGraph[$schemaGraphKey]['itemListElement'];
        foreach ($schemaItemsRef as $schemaCrumbKey => $schemaCrumb) {
          if (substr($schemaCrumb['item'], -5) === '/news') {
            $schemaItemsRef[$schemaCrumbKey]['name'] = 'Haberler';
          }
        }
        $schemaItemsRef[$schemaLastBreadcrumb]['name'] = $schemaNewsTitle;
        unset($schemaItemsRef);
      }
    }
  }

  if (!empty($newsList) && !$schemaIsNewsDetail) {
    $schemaGraph[2]['@type'] = 'CollectionPage';
    $schemaNewsItems = [];
    foreach (array_slice((array) $newsList, 0, 24) as $schemaIndex => $schemaNews) {
      $schemaNewsListUrl = $schemaBaseUrl . '/' . ($schemaSegments[0] ?? $schemaLocale) . '/news/' . ($schemaNews->news_slug ?? '');
      $schemaNewsEntity = [
        '@type' => 'NewsArticle',
        'headline' => mb_substr(trim(strip_tags((string) ($schemaNews->news_title ?? ''))), 0, 110),
        'url' => $schemaNewsListUrl,
        'mainEntityOfPage' => $schemaNewsListUrl,
        'description' => trim(strip_tags((string) ($schemaNews->news_summary ?? $schemaNews->news_title ?? ''))),
        'author' => ['@id' => $schemaBaseUrl . '/#organization'],
        'publisher' => ['@id' => $schemaBaseUrl . '/#organization'],
      ];
      if (!empty($schemaNews->news_image)) {
        $schemaNewsEntity['image'] = [getenv('CDN_URL') . 'uploads/news/' . $schemaNews->news_image];
      }
      $schemaItemPub = !empty($schemaNews->news_published_at) ? $schemaNews->news_published_at : ($schemaNews->news_created_at ?? null);
      if (!empty($schemaItemPub)) {
        $schemaNewsEntity['datePublished'] = date('c', strtotime($schemaItemPub));
        $schemaNewsEntity['dateModified'] = !empty($schemaNews->news_updated_at) ? date('c', strtotime($schemaNews->news_updated_at)) : $schemaNewsEntity['datePublished'];
      }
      if (!empty($schemaNews->category_title)) {
        $schemaNewsEntity['articleSection'] = (string) $schemaNews->category_title;
      }
      $schemaNewsItems[] = [
        '@type' => 'ListItem',
        'position' => $schemaIndex + 1,
        'url' => $schemaNewsListUrl,
        'item' => $schemaNewsEntity,
      ];
    }
    $schemaGraph[] = [
      '@type' => 'ItemList',
      '@id' => $schemaPageUrl . '#news-list',
      'name' => 'Haberler',
      'numberOfItems' => count($schemaNewsItems),
      'itemListElement' => $schemaNewsItems,
    ];
  }

  echo '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@graph' => $schemaGraph,
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . '</script>';
  ?>

  <!-- Javascript -->
  <!-- <script src="<?= topupcenter() ?>assets-front/js/popper.min.js?v=<?= $version ?>"></script> -->
  <script src="<?= topupcenter() ?>assets-front/js/jquery.min.js?v=<?= $version ?>"></script>
  <script src="<?= topupcenter() ?>assets-front/js/bootstrap.bundle.min.js?v=<?= $version ?>"></script>
  <!-- <script src="<?= topupcenter() ?>assets-front/js/bootstrap.min.js?v=<?= $version ?>"></script> -->
  <script src="<?= topupcenter() ?>assets-front/js/toastr.min.js?v=<?= $version ?>"></script>
  <script src="<?= topupcenter() ?>assets-front/js/sweetalert2@11.min.js?v=<?= $version ?>"></script>

  <script src="<?= topupcenter() ?>assets-front/js/main.js?v=<?= $version ?>"></script>
  <?php if (!$isHomePage): ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <?php endif; ?>

  <script>
    $(document).ready(function () {
      // Mobil cihazlarda sanal klavyenin açılıp kapanarak tıklamayı iptal etmesini engeller
      $(document).on('pointerdown touchstart mousedown', '.password-toggle-icon', function (e) {
        e.preventDefault();
      });

      $(document).on('click', '.password-toggle-icon', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $icon = $(this);
        var $input = $icon.siblings('input');
        if (!$input.length) {
          $input = $icon.closest('.input-icon-wrap').find('input');
        }

        if ($input.attr('type') === 'password') {
          $input.attr('type', 'text');
          $icon.removeClass('fa-regular fa-eye').addClass('fa-solid fa-eye-slash').css('color', '#c79a3e');
        } else {
          $input.attr('type', 'password');
          $icon.removeClass('fa-solid fa-eye-slash').addClass('fa-regular fa-eye').css('color', '#666666');
        }
      });
    });
  </script>
  <?= $customBodyEndCode ?>
  <?php if ($customJs !== ''): ?>
    <script><?= $customJs ?></script><?php endif; ?>
  <?php if (site_setting('cookie_enabled') === '1'): ?>
    <div id="cookie-consent"
      style="position:fixed;left:1rem;right:1rem;bottom:1rem;z-index:9999;display:none;padding:1rem;background:#1a1a2e;color:#fff;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,.3)">
      <?= html_escape(site_setting('cookie_message', 'Bu site, daha iyi bir deneyim sunmak için çerezleri kullanır.')) ?>
      <?php $cookiePolicyUrl = site_setting('cookie_policy_url'); ?>
      <?php if ($cookiePolicyUrl !== ''): ?> <a href="<?= html_escape($cookiePolicyUrl) ?>" target="_blank"
          rel="noopener">Detaylar</a><?php endif; ?>
      <button type="button" id="cookie-consent-accept" class="btn btn-sm btn-primary ms-2">Kabul et</button>
    </div>
    <script>
        (function () {
          var banner = document.getElementById('cookie-consent');
          if (!localStorage.getItem('playsel_cookie_consent')) banner.style.display = 'block';
          document.getElementById('cookie-consent-accept').addEventListener('click', function () {
            localStorage.setItem('playsel_cookie_consent', '1');
            banner.style.display = 'none';
          });
        }());
    </script>
  <?php endif; ?>
  <?php if (!empty($this->session->userdata('user'))): ?>
    <script>
      // Outbox aktarımı: sonuç sayfasında kalmayan müşterilerin teslim edilmiş satışları sonraki ziyarette gönderilir.
      (function () {
        var base = <?= json_encode(topupcenter() . $templateLocale . '/payment/') ?>;
        function send(a) {
          var sent = false;
          if (typeof window.gtag === 'function') {
            window.gtag('event', <?= json_encode(site_setting('analytics_purchase_event_name', 'purchase') ?: 'purchase') ?>, { transaction_id: a.transaction_id, value: a.value, currency: a.currency, items: a.items });
            sent = true;
          }
          if (typeof window.fbq === 'function') {
            window.fbq('track', 'Purchase', {
              value: a.value, currency: a.currency, content_type: 'product',
              content_ids: a.items.map(function (i) { return i.item_id; }),
              contents: a.items.map(function (i) { return { id: i.item_id, quantity: i.quantity, item_price: i.price }; }),
              num_items: a.items.reduce(function (n, i) { return n + i.quantity; }, 0)
            }, { eventID: 'purchase_' + a.transaction_id });
            sent = true;
          }
          if (sent) {
            fetch(base + 'ackAnalytics', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'transaction_id=' + encodeURIComponent(a.transaction_id) }).catch(function () {});
          }
        }
        var polls = 0;
        function check() {
          polls++;
          fetch(base + 'pendingAnalytics', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : { data: [] }; })
            .then(function (p) {
              (p.data || []).forEach(send);
              if (p.waiting && polls < 40) setTimeout(check, 20000);
            })
            .catch(function () {});
        }
        setTimeout(check, 2500);
      }());
    </script>
  <?php endif; ?>
</body>

</html>