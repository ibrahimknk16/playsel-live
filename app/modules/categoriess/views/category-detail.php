<?php
// pre($category);
// pre($categorySeo);
// pre($subCategories);
// pre($groupedProducts);
// die;
// Gerekli Değişkenlerin Hazırlanması
$isServerRequired = false;
$serverPlaceholder = 'Sunucu Seçin';
$serverRequiredAttr = 'required';

if (!empty($category->category_requires)) {
  foreach ($category->category_requires as $req) {
    if (isset($req->inputName) && $req->inputName == 'server_id') {
      $isServerRequired = true;
      $serverPlaceholder = !empty($req->inputPlaceholder) ? $req->inputPlaceholder : 'Sunucu Seçin';
      $serverRequiredAttr = !empty($req->inputRequired) ? 'required' : '';
      break;
    }
  }
}

if (!function_exists('format_category_seo_tables')) {
  function format_category_seo_tables($html)
  {
    if (empty($html) || stripos($html, '<table') === false) {
      return $html;
    }
    // 1. Unwrap figure.table if present
    $html = preg_replace_callback('/<figure\b[^>]*class=["\'][^"\']*table[^"\']*["\'][^>]*>\s*(<table\b.*?<\/table>)\s*<\/figure>/is', function ($m) {
      return '<div class="seo-table-responsive">' . $m[1] . '</div>';
    }, $html);

    // 2. Wrap and ensure proper thead
    $html = preg_replace_callback('/(<div\s+class=["\'][^"\']*seo-table-responsive[^"\']*["\'][^>]*>)?\s*(<table\b.*?<\/table>)\s*(<\/div>)?/is', function ($m) {
      $tableHtml = $m[2];
      if (stripos($tableHtml, '<th') === false) {
        if (preg_match('/<tr\b[^>]*>(.*?)<\/tr>/is', $tableHtml, $trMatch)) {
          $firstTr = $trMatch[0];
          $headerTr = preg_replace('/<(\/?)td\b/i', '<$1th', $firstTr);
          $pos = strpos($tableHtml, $firstTr);
          if ($pos !== false) {
            $tableHtml = substr_replace($tableHtml, '', $pos, strlen($firstTr));
          }
          $tableHtml = preg_replace('/(<table\b[^>]*>)/i', '$1<thead>' . $headerTr . '</thead>', $tableHtml, 1);
        }
      } else {
        $tableHtml = preg_replace_callback('/<thead\b[^>]*>(.*?)<\/thead>/is', function ($theadMatch) {
          return preg_replace('/<(\/?)td\b/i', '<$1th', $theadMatch[0]);
        }, $tableHtml);
      }
      return '<div class="seo-table-responsive">' . $tableHtml . '</div>';
    }, $html);

    return $html;
  }
}
?>
<style>
  input[type=number]::-webkit-outer-spin-button,
  input[type=number]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  input[type=number] {
    -moz-appearance: textfield;
  }

  /* ====== CATEGORY DETAIL PAGE ENHANCEMENTS ====== */

  /* --- Hero Product Info Card --- */
  .category-detail-page .product-info-card.cd-enhanced {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 200, 0, 0.12);
    border-radius: 20px;
    padding: 24px 28px;
    background: linear-gradient(145deg, rgba(23, 26, 35, 0.98), rgba(15, 18, 25, 0.98));
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.35);
  }

  .category-detail-page .product-info-card.cd-enhanced::before {
    content: '';
    position: absolute;
    top: -50px;
    left: -50px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(255, 179, 0, 0.08) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
  }

  .category-detail-page .product-info-card.cd-enhanced::after {
    content: '';
    position: absolute;
    top: -30px;
    right: -30px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255, 140, 0, 0.05) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
  }

  .category-detail-page .product-info-card.cd-enhanced .product-info-top {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: auto 1fr;
    grid-template-rows: auto auto auto;
    grid-template-areas:
      "icon title"
      "icon desc"
      "icon meta";
    column-gap: 22px;
    row-gap: 8px;
    align-items: start;
  }

  .category-detail-page .product-info-card.cd-enhanced .product-info-icon {
    grid-area: icon;
    width: 96px;
    height: 96px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
    border: 1px solid rgba(255, 179, 0, 0.25);
    background: linear-gradient(135deg, #ff9e1f 0%, #ff6b00 100%);
    align-self: start;
    flex-shrink: 0;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .category-detail-page .product-info-card.cd-enhanced .product-info-icon:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.55), 0 0 20px rgba(255, 160, 0, 0.2);
  }

  .category-detail-page .product-info-card.cd-enhanced .product-info-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: 19px;
  }

  .category-detail-page .product-info-card.cd-enhanced .product-info-title-row {
    grid-area: title;
    display: flex;
    /* align-items: center; */
    margin: 0;
  }

  .category-detail-page .product-info-card.cd-enhanced .product-info-title {
    font-size: 24px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: -0.3px;
    margin: 0;
    line-height: 1.25;
  }

  .category-detail-page .product-info-card.cd-enhanced .product-info-desc {
    grid-area: desc;
    font-size: 14px;
    font-weight: 400;
    color: #9ca3af;
    line-height: 1.5;
    margin: 2px 0 6px 0;
    max-width: 950px;
  }

  .category-detail-page .product-info-card.cd-enhanced .product-info-meta-group {
    grid-area: meta;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 16px;
    margin-top: 4px;
  }

  .category-detail-page .product-info-card.cd-enhanced .meta {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    padding: 0;
  }

  .category-detail-page .product-info-card.cd-enhanced .meta .meta-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 16px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    color: #d4d4d8;
    white-space: nowrap;
    transition: all 0.25s ease;
  }

  .category-detail-page .product-info-card.cd-enhanced .meta .meta-item:hover {
    background: rgba(255, 255, 255, 0.07);
    border-color: rgba(255, 200, 0, 0.25);
    color: #ffffff;
  }

  .category-detail-page .product-info-card.cd-enhanced .store-badges {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
    flex-shrink: 0;
  }

  .category-detail-page .product-info-card.cd-enhanced .store-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease, opacity 0.2s ease;
  }

  .category-detail-page .product-info-card.cd-enhanced .store-badge:hover {
    transform: translateY(-2px);
    opacity: 0.95;
  }

  .category-detail-page .product-info-card.cd-enhanced .store-icon {
    height: 38px;
    width: auto;
    max-width: 135px;
    object-fit: contain;
    display: block;
    border-radius: 6px;
  }

  /* --- Step Indicator --- */
  .cd-step-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
  }

  .cd-step-number {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--accent) 0%, #FF9500 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: 600;
    color: var(--dark);
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(255, 200, 0, 0.25);
  }

  .cd-step-header .section-label {
    margin-bottom: 0;
    font-size: 16px;
    font-weight: 600;
  }

  /* --- Enhanced Sub Category Cards --- */
  .sub-category-section.cd-enhanced {
    border: 1px solid rgba(255, 255, 255, 0.05);
    background: var(--bg-secondary);
  }

  .sub-category-section.cd-enhanced .sub-cat-card {
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    padding: 14px 18px;
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .sub-category-section.cd-enhanced .sub-cat-card:hover {
    border-color: rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.04);
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  }

  .sub-category-section.cd-enhanced .sub-cat-card.active {
    border-color: var(--yellow);
    box-shadow: 0 0 0 1px rgba(255, 200, 0, 0.15), 0 4px 20px rgba(255, 200, 0, 0.1);
  }

  .sub-category-section.cd-enhanced .sub-cat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  }

  .sub-category-section.cd-enhanced .sub-cat-name {
    font-weight: 600;
    font-size: 13px;
  }

  .sub-cat-name-desktop {
    display: inline;
  }

  .sub-cat-name-mobile {
    display: none;
  }

  /* --- Enhanced ID Input Section --- */
  .category-detail-page .id-input-section.cd-enhanced {
    border: 1px solid rgba(255, 179, 0, 0.12);
    border-radius: 20px;
    padding: 24px 28px;
    background: linear-gradient(145deg, rgba(23, 26, 35, 0.98), rgba(15, 18, 25, 0.98));
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.26);
  }

  .category-detail-page .id-input-header {
    margin-bottom: 16px;
  }

  .category-detail-page .id-input-fields {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .category-detail-page .id-input-fields.has-server-id {
    grid-template-columns: 1.5fr 1fr !important;
  }

  .category-detail-page .id-input-group {
    position: relative;
    display: flex;
    flex-direction: column;
  }

  .category-detail-page .id-input-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
  }

  .category-detail-page .id-input-label {
    margin-bottom: 0;
    font-size: 13.5px;
    font-weight: 500;
    color: #9ca3af;
    line-height: 1.2;
  }

  .category-detail-page .last-used-ids-trigger {
    font-size: 11.5px;
    color: #8f96a3;
    cursor: pointer;
    transition: color 0.2s ease;
  }

  .category-detail-page .last-used-ids-trigger:hover {
    color: var(--cd-gold);
  }

  .category-detail-page .id-input-section.cd-enhanced .id-field {
    width: 100%;
    height: 52px;
    padding: 0 16px;
    border-radius: 12px !important;
    font-size: 14.5px;
    color: #ffffff !important;
    background: #10131a !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    box-shadow: none;
    transition: all 0.25s ease;
  }

  .category-detail-page .id-input-section.cd-enhanced .id-field::placeholder {
    color: #52525b !important;
    font-size: 14px;
    font-weight: 400;
  }

  .category-detail-page .id-input-section.cd-enhanced .id-field:focus {
    border-color: #ffb300 !important;
    background: #121620 !important;
    box-shadow: 0 0 0 1px rgba(255, 179, 0, 0.25), 0 0 16px rgba(255, 179, 0, 0.12) !important;
    outline: none;
  }

  .category-detail-page .id-input-hint {
    margin: 10px 0 0 0;
    font-size: 12.5px;
    color: #71717a;
    line-height: 1.4;
  }

  /* --- Enhanced Product Selection --- */
  .product-selection-section.cd-enhanced {
    border: 1px solid rgba(255, 255, 255, 0.05);
  }

  .product-selection-section.cd-enhanced .product-item {
    border-radius: 12px;
    padding: 16px 18px;
    border: 1px solid rgba(255, 255, 255, 0.06);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
  }

  .product-selection-section.cd-enhanced .product-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 200, 0, 0.04) 50%, transparent 100%);
    transition: left 0.5s ease;
    pointer-events: none;
    z-index: 0;
  }

  .product-selection-section.cd-enhanced .product-item:hover::before {
    left: 100%;
  }

  .product-selection-section.cd-enhanced .product-item:hover {
    border-color: rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.03);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
  }

  .product-selection-section.cd-enhanced .product-item.selected {
    border-color: var(--yellow);
    box-shadow: 0 0 0 1px rgba(255, 200, 0, 0.12), 0 4px 24px rgba(255, 200, 0, 0.08);
    transform: translateY(-2px);
  }

  .product-selection-section.cd-enhanced .product-item.selected .product-item-icon {
    box-shadow: 0 0 12px rgba(255, 200, 0, 0.2);
  }

  .product-selection-section.cd-enhanced .product-item-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    transition: box-shadow 0.3s ease;
  }

  .product-selection-section.cd-enhanced .product-item-name {
    font-weight: 600;
  }

  .product-selection-section.cd-enhanced .product-discount-badge {
    border-radius: 8px;
    padding: 3px 10px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px rgba(255, 200, 0, 0.3);
  }

  /* --- Selected check indicator --- */
  /* .product-selection-section.cd-enhanced .product-item.selected::after {
    content: '\2713';
    position: absolute;
    top: 10px;
    right: 12px;
    width: 22px;
    height: 22px;
    background: var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    color: var(--dark);
    animation: cdCheckPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  } */

  @keyframes cdCheckPop {
    0% {
      transform: scale(0);
    }

    100% {
      transform: scale(1);
    }
  }


  /* ====== FEATURED PASSES (ÖNE ÇIKAN PAKETLER) ====== */
  .ps-featured-passes {
    margin: 6px 0 22px;
  }

  .ps-pass-heading {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 13px;
    color: #f2f2f2;
    font-size: 14px;
    font-weight: 600;
  }

  .ps-pass-heading:before {
    content: "";
    width: 3px;
    height: 15px;
    border-radius: 3px;
    background: #ffb300;
  }

  .ps-pass-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
  }

  .ps-pass-grid.count-1 {
    grid-template-columns: minmax(280px, 460px);
  }

  .ps-pass-grid.count-2 {
    grid-template-columns: repeat(2, 1fr);
  }

  .ps-pass-grid.count-3 {
    grid-template-columns: repeat(3, 1fr);
  }

  .ps-big-pass {
    position: relative;
    overflow: hidden;
    min-height: 174px;
    width: 100%;
    text-align: left;
    cursor: pointer;
    border: 1px solid #343849;
    border-radius: 12px;
    background: #1b1f29;
    color: #f2f2f2;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    font: inherit;
    isolation: isolate;
    transition: border-color .2s, background .2s, transform .2s, box-shadow .2s;
  }

  .ps-big-pass:hover {
    border-color: #646b78;
    transform: translateY(-2px);
  }

  .ps-big-pass:focus-visible {
    outline: 2px solid #fff1c5 !important;
    outline-offset: 4px !important;
  }

  /* Staggered Playsel wave on featured packages */
  .ps-featured-passes .ps-big-pass::before {
    content: "";
    position: absolute;
    inset: -100%;
    width: 300%;
    height: 300%;
    border-radius: 0;
    pointer-events: none;
    z-index: 0;
    opacity: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 184, 23, .02) 36%, rgba(255, 184, 23, .09) 41%, rgba(255, 190, 35, .27) 45%, rgba(255, 145, 0, .2) 49%, rgba(255, 145, 0, .14) 54%, rgba(255, 145, 0, .08) 61%, rgba(255, 145, 0, .03) 69%, transparent 79%);
    transform: translate(60%, -60%);
    animation: playsel-elite-wave 18s linear infinite;
  }

  .ps-featured-passes .ps-pass-top,
  .ps-featured-passes .ps-pass-pricebar {
    position: relative;
    z-index: 1;
  }

  @keyframes playsel-elite-wave {
    0% {
      transform: translate(60%, -60%);
      opacity: 0;
    }

    2% {
      opacity: 1;
    }

    35% {
      transform: translate(-60%, 60%);
      opacity: 1;
    }

    39%,
    100% {
      transform: translate(-60%, 60%);
      opacity: 0;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .ps-featured-passes .ps-big-pass::before {
      animation: none;
      opacity: 0;
    }
  }

  /* Tone styles and sequential animation delay (0s, 6s, 12s) */
  .ps-featured-passes .ps-big-pass[data-featured-tone="1"] {
    background: radial-gradient(ellipse at 95% 0%, #3587bf25, transparent 65%), linear-gradient(125deg, #203342, #1c252f 70%) !important;
    border-color: #52748a !important;
  }

  .ps-featured-passes .ps-big-pass[data-featured-tone="2"] {
    background: radial-gradient(ellipse at 95% 0%, #39a79820, transparent 65%), linear-gradient(125deg, #203a37, #1c2b2b 70%) !important;
    border-color: #4c8377 !important;
  }

  .ps-featured-passes .ps-big-pass[data-featured-tone="3"] {
    background: radial-gradient(ellipse at 95% 0%, #9a58f02b, transparent 65%), linear-gradient(125deg, #30233e, #201d2d 65%, #1d202b) !important;
    border-color: #a77ae0 !important;
  }

  .ps-featured-passes .ps-big-pass[data-featured-tone="1"]::before {
    animation-delay: 0s;
  }

  .ps-featured-passes .ps-big-pass[data-featured-tone="2"]::before {
    animation-delay: 6s;
  }

  .ps-featured-passes .ps-big-pass[data-featured-tone="3"]::before {
    animation-delay: 12s;
  }

  /* Editorial highlight for recommendation package */
  .ps-featured-passes .ps-big-pass.ps-highlight-package {
    background: radial-gradient(ellipse at 95% 0%, #9a58f02b, transparent 65%), linear-gradient(125deg, #30233e, #201d2d 65%, #1d202b) !important;
    border-color: #a77ae0 !important;
    box-shadow: 0 0 0 1px #ba8cff12, inset 0 1px 0 #e0bdff10, 0 5px 22px #9448db18 !important;
  }

  .ps-featured-passes .ps-highlight-package .ps-pass-copy strong {
    color: #f6efff !important;
  }

  .ps-featured-passes .ps-highlight-package .ps-pass-copy small {
    color: #c5b8d6 !important;
  }

  .ps-featured-passes .ps-big-pass.ps-highlight-package:hover {
    border-color: #d4aaff !important;
    box-shadow: 0 0 0 1px #cda7ff20, 0 5px 24px #9448db25 !important;
  }

  /* High-contrast recommendation badge */
  .ps-package-recommendation {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    width: fit-content;
    color: #241800 !important;
    background: linear-gradient(110deg, #ffdc70, #ffb719 65%, #ff9b00) !important;
    border: 1px solid #ffe29a !important;
    border-radius: 6px !important;
    padding: 4px 8px !important;
    margin-bottom: 7px !important;
    font: 700 11px/1.3 "Segoe UI", Arial, sans-serif !important;
    box-shadow: 0 3px 12px #ffb71926 !important;
    letter-spacing: 0 !important;
    white-space: normal;
    max-width: 100%;
    box-sizing: border-box;
  }

  /* Top content */
  .ps-pass-top {
    display: flex;
    flex-direction: row;
    gap: 14px;
    align-items: center;
    min-height: 70px;
    padding-right: 30px !important;
  }

  .ps-pass-image {
    width: 64px;
    height: 64px;
    object-fit: contain;
    flex-shrink: 0;
    filter: drop-shadow(0 5px 9px rgba(0, 0, 0, 0.35));
    transition: filter .2s;
  }

  .ps-pass-copy {
    flex: 1;
    min-width: 0;
  }

  .ps-pass-copy strong {
    font-size: 12px;
    font-weight: 700;
    display: block;
    line-height: 1.35;
    margin: 0 0 4px;
    color: #f4f5fa !important;
    font-family: Arial,"Segoe UI",sans-serif !important;
  }

  .ps-pass-copy small {
    font-size: 11px;
    line-height: 1.45;
    color: #aeb4c4 !important;
    display: block;
    font-family: 'Poppins', sans-serif !important;
  }

  /* Price bar */
  .ps-pass-pricebar {
    border-top: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    margin-top: 14px;
    padding-bottom: 14px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    width: 100%;
    box-sizing: border-box;
  }

  .ps-pass-price-group {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
  }

  .ps-pass-price-row {
    display: flex;
    align-items: baseline;
    gap: 8px;
  }

  .ps-pass-price {
    font-size: 16px !important;
    letter-spacing: -.5px;
    color: #ffb300;
    font-weight: 700;
    line-height: 1.2;
  }

  .ps-pass-old {
    font-size: 8px;
    text-decoration: line-through;
    color: #838b9d;
  }

  .ps-pass-discount {
    font-size: 10px;
    background: rgba(53, 164, 119, 0.12) !important;
    color: #79d2ad !important;
    border: 1px solid rgba(103, 200, 160, 0.25) !important;
    border-radius: 4px !important;
    padding: 2px 7px !important;
    font-weight: 600 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1.2 !important;
  }

  /* Selection state */
  .ps-featured-passes .ps-big-pass[aria-pressed="true"] {
    border-color: #ffd05b !important;
    box-shadow: inset 0 0 0 1px #ffd05b, 0 0 0 2px rgba(255, 192, 43, 0.15), 0 5px 22px rgba(255, 174, 0, 0.12) !important;
  }

  .ps-featured-passes .ps-big-pass[aria-pressed="true"] .ps-pass-copy strong {
    color: #ffca56 !important;
  }

  .ps-featured-passes .ps-big-pass[aria-pressed="true"] .ps-pass-image {
    filter: drop-shadow(0 3px 9px rgba(255, 192, 43, 0.35));
  }

  .ps-big-pass[disabled] {
    opacity: .45;
    cursor: not-allowed;
  }

  /* Lightning flash emblem on featured cards */
  .ps-featured-passes .ps-big-pass .ps-featured-flash {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 3;
    margin: 0;
    width: 26px;
    height: 26px;
    border-radius: 8px;
    pointer-events: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 183, 25, 0.4);
    background: linear-gradient(135deg, rgba(255, 191, 37, 0.17), rgba(255, 136, 0, 0.07));
    color: #ffc34d;
    box-shadow: inset 0 1px 0 rgba(255, 228, 165, 0.1), 0 2px 8px rgba(255, 157, 0, 0.08);
    transition: background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
  }

  .ps-featured-passes .ps-big-pass .ps-featured-flash svg {
    width: 15px;
    height: 15px;
    fill: currentColor;
    filter: drop-shadow(0 0 3px rgba(255, 154, 0, 0.25));
  }

  .ps-featured-passes .ps-big-pass[aria-pressed="true"] .ps-featured-flash {
    background: linear-gradient(135deg, #ffdb71, #ffb719) !important;
    border-color: #ffe6a6 !important;
    color: #2b1d00 !important;
    box-shadow: 0 0 0 3px rgba(255, 189, 36, 0.15), 0 3px 12px rgba(255, 183, 25, 0.2) !important;
  }

  .ps-featured-passes .ps-big-pass[aria-pressed="true"] .ps-featured-flash svg {
    filter: none !important;
  }

  /* Products grid layout (Desktop 3 Columns) */
  #products-grid.products-grid {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 10px !important;
    align-items: stretch !important;
  }

  #products-grid .product-item {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 11px 14px !important;
    border-radius: 10px !important;
    background: #191c25 !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    position: relative !important;
    min-height: 82px !important;
    cursor: pointer;
    transition: all 0.25s ease;
    box-sizing: border-box;
  }

  #products-grid .product-item:hover {
    border-color: rgba(255, 255, 255, 0.15) !important;
    background: #1d212c !important;
    transform: translateY(-2px);
  }

  #products-grid .product-item.selected {
    border-color: #ffd05b !important;
    box-shadow: 0 0 0 1px #ffd05b, 0 4px 20px rgba(255, 200, 0, 0.1) !important;
  }

  #products-grid .product-item.product-out-of-stock {
    opacity: .55;
    cursor: not-allowed;
  }

  /* Left/Main product content (Icon + Names) */
  #products-grid .product-item-main {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
    padding-right: 10px;
    box-sizing: border-box;
  }

  #products-grid .product-item-icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }

  #products-grid .product-item-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  #products-grid .product-item-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
    text-align: left;
  }

  #products-grid .product-item-name {
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #f4f5fa !important;
    line-height: 1.35 !important;
    margin: 0 !important;
    display: block;
    transition: color 0.2s ease;
    font-family: "Segoe UI",Arial,sans-serif !important;
  }

  #products-grid .product-item.selected .product-item-name {
    color: #ffd05b !important;
  }

  #products-grid .product-item-short-desc {
    font-size: 11px;
    color: #9299a9;
    line-height: 1.4;
    white-space: pre-line;
    display: block;
  }

  /* Divider hidden by default on desktop */
  #products-grid .product-item-divider {
    display: none !important;
  }

  /* Right/Side area on desktop (Row 1: Discount + Flash, Row 2: Price + Old Price, Row 3: Stepper) */
  #products-grid .product-item-side {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-end !important;
    justify-content: center !important;
    gap: 2px !important;
    flex-shrink: 0 !important;
    padding-right: 24px !important;
  }

  #products-grid .product-item-side-info {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-end !important;
    gap: 3px !important;
  }

  #products-grid .product-item-side-top {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: flex-end !important;
  }

  #products-grid .product-item-badge-wrap {
    display: flex;
    align-items: center;
  }

  #products-grid .product-discount-badge {
    position: static !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    background: rgba(53, 164, 119, 0.12) !important;
    color: #79d2ad !important;
    border: 1px solid rgba(103, 200, 160, 0.25) !important;
    border-radius: 4px !important;
    padding: 2px 5px !important;
    font-size: 9.5px !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    letter-spacing: 0 !important;
    box-shadow: none !important;
    white-space: nowrap !important;
    margin: 0 !important;
    flex-shrink: 0 !important;
  }

  #products-grid .ps-product-flash {
    position: absolute !important;
    top: 10px !important;
    right: 12px !important;
    z-index: 2 !important;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    box-sizing: border-box;
    pointer-events: none;
    border: 1px solid rgba(255, 183, 25, 0.35);
    border-radius: 5px;
    background: rgba(255, 174, 0, 0.06);
    color: #eeb84d;
    transition: background .18s ease, color .18s ease, box-shadow .18s ease;
    flex-shrink: 0;
  }

  #products-grid .ps-product-flash svg {
    width: 12px;
    height: 12px;
    fill: currentColor;
  }

  #products-grid .product-item.selected .ps-product-flash {
    background: #ffca56;
    border-color: #ffe6a6;
    color: #2b1d00;
    box-shadow: 0 0 0 2px rgba(255, 189, 36, 0.12);
  }

  #products-grid .product-item.product-out-of-stock .ps-product-flash {
    color: #78818e;
    border-color: rgba(98, 106, 117, 0.25);
    background: rgba(255, 255, 255, 0.02);
  }

  #products-grid .product-item-prices {
    display: flex !important;
    flex-direction: row !important;
    align-items: baseline !important;
    justify-content: flex-end !important;
    gap: 6px !important;
    margin: 0 !important;
    flex-shrink: 0 !important;
  }

  #products-grid .product-item-price {
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #ffb300 !important;
    line-height: 1.2 !important;
    letter-spacing: -.3px !important;
    margin: 0 !important;
    order: 1 !important;
    white-space: nowrap !important;
  }

  #products-grid .product-item-old-price {
    font-size: 9.5px !important;
    color: #7d8596 !important;
    text-decoration: line-through !important;
    line-height: 1.2 !important;
    margin: 0 !important;
    order: 2 !important;
    white-space: nowrap !important;
  }

  /* Quantity Stepper (Masaüstü: Seçilince flex akışında fiyatın hemen altına açılır) */
  #products-grid .product-item-qty-wrap {
    display: none !important;
    width: auto !important;
    justify-content: flex-end !important;
    align-items: center !important;
    margin-top: 4px !important;
    box-sizing: border-box !important;
  }

  #products-grid .product-item[data-epin="1"].selected .product-item-qty-wrap {
    display: flex !important;
  }

  #products-grid .cd-qty {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 25px !important;
    width: 88px !important;
    background: #1c212d !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 6px !important;
    overflow: hidden !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25) !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  #products-grid .cd-qty-btn {
    width: 26px !important;
    height: 100% !important;
    background: transparent !important;
    border: none !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    padding: 0 !important;
    margin: 0 !important;
    line-height: 1 !important;
    transition: background 0.15s ease, color 0.15s ease !important;
    user-select: none !important;
  }

  #products-grid .cd-qty-minus {
    border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
    color: #8b93a5 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
  }

  #products-grid .cd-qty-minus:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.06) !important;
    color: #ffffff !important;
  }

  #products-grid .cd-qty-minus:disabled {
    opacity: 0.3 !important;
    cursor: not-allowed !important;
  }

  #products-grid .cd-qty-val {
    flex: 1 !important;
    height: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    min-width: 28px !important;
    text-align: center !important;
    user-select: none !important;
  }

  #products-grid .cd-qty-plus {
    border-left: 1px solid rgba(255, 255, 255, 0.08) !important;
    color: #ffb300 !important;
    font-size: 13px !important;
    font-weight: 700 !important;
  }

  #products-grid .cd-qty-plus:hover:not(:disabled) {
    background: rgba(255, 179, 0, 0.12) !important;
    color: #ffd05b !important;
  }

  #products-grid .cd-qty-plus:disabled {
    opacity: 0.3 !important;
    cursor: not-allowed !important;
  }

  #products-grid .cd-qty-plus:hover:not(:disabled) {
    background: rgba(255, 179, 0, 0.12) !important;
    color: #ffd05b !important;
  }

  #products-grid .cd-qty-plus:disabled {
    opacity: 0.3 !important;
    cursor: not-allowed !important;
  }

  /* Divider before Tüm Ürünler */
  .ps-pass-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 600;
    color: #8f96a7;
    margin: 26px 0 16px;
  }

  .ps-pass-divider:after {
    content: "";
    height: 1px;
    flex: 1;
    background: rgba(255, 255, 255, 0.08);
  }

  /* Mobile responsive styles (Görsel 1, 2, 3 ve 4) */
  @media (max-width: 850px) {

    /* --- Öne Çıkan Paketler Mobil Düzeni (Görsel 1, 2, 3) --- */
    .ps-pass-grid.count-1 {
      grid-template-columns: 1fr !important;
      max-width: 100% !important;
    }

    .ps-pass-grid.count-2 {
      grid-template-columns: repeat(2, 1fr) !important;
      gap: 10px !important;
    }

    .ps-pass-grid.count-3 {
      grid-template-columns: repeat(2, 1fr) !important;
      gap: 10px !important;
    }

    .ps-pass-grid.count-3> :nth-child(3) {
      grid-column: 1 / -1 !important;
    }

    /* Görsel 1 & 2: Üst satırdaki 2 kart dikey düzen */
    .ps-pass-grid.count-2 .ps-pass-top,
    .ps-pass-grid.count-3> :nth-child(1) .ps-pass-top,
    .ps-pass-grid.count-3> :nth-child(2) .ps-pass-top {
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: 12px !important;
    }

    .ps-pass-grid.count-2 .ps-pass-image,
    .ps-pass-grid.count-3> :nth-child(1) .ps-pass-image,
    .ps-pass-grid.count-3> :nth-child(2) .ps-pass-image {
      width: 60px !important;
      height: 60px !important;
    }

    /* Görsel 1 (3. kart) & Görsel 3 (1 kart): Tam genişliktekiler yatay düzen */
    .ps-pass-grid.count-1 .ps-pass-top,
    .ps-pass-grid.count-3> :nth-child(3) .ps-pass-top {
      flex-direction: row !important;
      align-items: center !important;
      gap: 14px !important;
    }

    .ps-pass-grid.count-1 .ps-pass-image,
    .ps-pass-grid.count-3> :nth-child(3) .ps-pass-image {
      width: 64px !important;
      height: 64px !important;
    }

    /* --- Tüm Ürünler Mobil Düzeni (Görsel 2) --- */
    #products-grid.products-grid {
      grid-template-columns: repeat(2, 1fr) !important;
      gap: 12px !important;
      align-items: stretch !important;
    }

    #products-grid .product-item {
      flex-direction: column !important;
      flex-wrap: nowrap !important;
      align-items: stretch !important;
      justify-content: flex-start !important;
      padding: 16px 16px 14px !important;
      min-height: 165px !important;
      position: relative !important;
    }

    #products-grid .product-item-main {
      align-items: flex-start !important;
      padding-right: 32px !important;
      gap: 10px !important;
    }

    #products-grid .product-item-icon {
      width: 48px !important;
      height: 48px !important;
    }

    #products-grid .product-item-side {
      display: flex !important;
      flex-direction: column !important;
      width: 100% !important;
      margin-top: auto !important;
      padding-top: 14px !important;
      gap: 0 !important;
    }

    #products-grid .product-item-side-info {
      display: flex !important;
      flex-direction: row !important;
      align-items: flex-end !important;
      justify-content: space-between !important;
      width: 100% !important;
      margin-bottom: 12px !important;
    }

    #products-grid .product-item-side-top {
      display: flex !important;
      align-items: center !important;
      flex-shrink: 0 !important;
    }

    #products-grid .product-item-badge-wrap {
      display: flex !important;
      align-items: center !important;
      flex-shrink: 0 !important;
    }

    #products-grid .ps-product-flash {
      position: absolute !important;
      top: 14px !important;
      right: 14px !important;
      z-index: 3 !important;
    }

    #products-grid .product-item-prices {
      flex-direction: column !important;
      align-items: flex-end !important;
      gap: 2px !important;
      margin-left: auto !important;
      flex-shrink: 0 !important;
    }

    #products-grid .product-item-old-price {
      order: 1 !important;
      white-space: nowrap !important;
    }

    #products-grid .product-item-price {
      order: 2 !important;
      font-size: 16px !important;
      white-space: nowrap !important;
    }

    #products-grid .product-item-divider {
      display: block !important;
      width: 100% !important;
      height: 1px !important;
      background: rgba(255, 255, 255, 0.08) !important;
      margin: 0 !important;
    }

    #products-grid .product-item-side {
      padding-right: 0 !important;
    }

    #products-grid .product-item-qty-wrap {
      display: flex !important;
      visibility: hidden !important;
      opacity: 0 !important;
      pointer-events: none !important;
      width: 100% !important;
      height: 25px !important;
      min-height: 25px !important;
      justify-content: center !important;
      align-items: center !important;
      margin-top: 10px !important;
      margin-bottom: 2px !important;
      box-sizing: border-box !important;
      transition: opacity 0.2s ease, visibility 0.2s ease !important;
    }

    #products-grid .product-item.selected .product-item-qty-wrap,
    #products-grid .product-item[data-epin="1"].selected .product-item-qty-wrap {
      display: flex !important;
      visibility: visible !important;
      opacity: 1 !important;
      pointer-events: auto !important;
    }
  }

  @media (max-width: 480px) {
    #products-grid.products-grid {
      gap: 8px !important;
    }

    #products-grid .product-item {
      padding: 14px 12px !important;
    }

    #products-grid .product-item-price {
      font-size: 12px !important;
    }

    #products-grid .product-discount-badge {
      padding: 2px 5px !important;
      font-size: 10.5px !important;
    }

    .ps-pass-grid.count-2,
    .ps-pass-grid.count-3 {
      gap: 8px !important;
    }
  }

  /* --- Static Payment Section --- */
  .cd-payment-card-static {
    border: 1px solid rgba(255, 200, 0, 0.1);
    border-radius: 16px;
    padding: 28px 24px;
    position: relative;
    overflow: hidden;
  }

  .cd-payment-card-static::before {
    content: '';
    position: absolute;
    top: -30px;
    right: -30px;
    width: 120px;
    height: 120px;
    background: radial-gradient(circle, rgba(255, 200, 0, 0.06) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .cd-payment-card-static .cd-payment-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 22px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    margin-bottom: 18px;
    flex-direction: row;
  }

  .cd-payment-card-static .cd-payment-total .total-label {
    font-size: 15px;
  }

  .cd-payment-card-static .cd-payment-total .total-value {
    font-size: 22px;
  }

  .cd-payment-card-static .cd-btn-pay {
    width: 100%;
    padding: 16px;
    font-size: 16px;
  }

  .cd-login-card-static {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 16px;
    padding: 40px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
  }

  .cd-login-card-static h3 {
    font-size: 18px;
    margin: 0;
  }

  .cd-login-card-static p {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
    display: block;
    margin: 0;
  }

  .cd-login-card-static .cd-btn-login {
    width: 100%;
    max-width: 200px;
    padding: 14px;
    font-size: 15px;
  }

  /* --- Fixed Payment Section --- */
  .payment-section-fixed {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease;
  }

  .cd-payment-card {
    border: none;
    border-top: 1px solid rgba(255, 200, 0, 0.2);
    border-radius: 0;
    padding: 16px 5%;
    position: relative;
    overflow: hidden;
    background: rgba(17, 21, 29, 0.98);
    backdrop-filter: blur(15px);
    box-shadow: 0 -5px 30px rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: space-around;
    align-items: center;
  }

  .cd-payment-card::before {
    content: '';
    position: absolute;
    top: -30px;
    right: -30px;
    width: 120px;
    height: 120px;
    background: radial-gradient(circle, rgba(255, 200, 0, 0.06) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .cd-payment-total {
    margin-bottom: 0;
    padding: 0;
    background: transparent;
    border: none;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }

  .cd-payment-total .total-label {
    font-size: 13px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.7);
  }

  .cd-payment-total .total-value {
    font-size: 20px;
    font-weight: 600;
    color: var(--white);
    letter-spacing: -0.3px;
  }

  .cd-btn-pay {
    width: auto;
    padding: 12px 30px;
    background: linear-gradient(135deg, var(--accent) 0%, #FF9500 100%);
    border: none;
    border-radius: 12px;
    color: var(--dark);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    letter-spacing: 0.3px;
  }

  .cd-btn-pay::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.2) 50%, transparent 100%);
    transition: left 0.6s ease;
  }

  .cd-btn-pay:hover::before {
    left: 100%;
  }

  .cd-btn-pay:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(255, 200, 0, 0.35);
  }

  /* --- Login Required Card --- */
  .cd-login-card {
    border: none;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 0;
    padding: 12px 5%;
    background: rgba(17, 21, 29, 0.98);
    backdrop-filter: blur(15px);
    box-shadow: 0 -5px 30px rgba(0, 0, 0, 0.8);
    display: flex;
    flex-direction: row;
    justify-content: space-evenly;
    align-items: center;
    gap: 15px;
  }

  .cd-login-card .cd-login-icon {
    display: none;
    /* Hide icon on horizontal bar to save space */
  }

  .cd-login-card h3 {
    margin: 0;
    font-size: 15px;
  }

  .cd-login-card p {
    margin: 0;
    font-size: 12px;
    text-align: left;
    display: none;
    /* Hide text on mobile, maybe show on desktop via media query */
  }

  @media (min-width: 768px) {
    .cd-login-card p {
      display: block;
    }
  }

  .cd-login-card .cd-btn-login {
    width: auto;
    padding: 10px 25px;
    margin: 0;
  }

  .cd-login-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: rgba(255, 200, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 4px;
  }

  .cd-login-card h3 {
    color: var(--white);
    font-size: 18px;
    font-weight: 600;
    margin: 0;
  }

  .cd-login-card p {
    color: var(--gray);
    font-size: 13px;
    font-weight: 500;
    margin: 0;
  }

  .cd-btn-login {
    display: block;
    width: 100%;
    max-width: 280px;
    padding: 14px;
    background: linear-gradient(135deg, var(--accent) 0%, #FF9500 100%);
    border-radius: 12px;
    color: var(--dark);
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
    margin-top: 4px;
  }

  .cd-btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(255, 200, 0, 0.35);
  }

  /* --- Enhanced Category Detail & How-to Sections --- */
  .category-detail-section.cd-enhanced,
  .how-to-section.cd-enhanced {
    border: 1px solid rgba(255, 255, 255, 0.05);
    position: relative;
    overflow: hidden;
  }

  .category-detail-section.cd-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(180deg, var(--accent) 0%, transparent 100%);
    border-radius: 0 2px 2px 0;
  }

  .how-to-section.cd-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(180deg, #FF9500 0%, transparent 100%);
    border-radius: 0 2px 2px 0;
  }

  .category-detail-section.cd-enhanced .section-label-lg {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .category-detail-section.cd-enhanced .section-label-lg::before {
    content: '\1F4CB';
    font-size: 20px;
  }

  .how-to-section.cd-enhanced .detail-subtitle {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .how-to-section.cd-enhanced .detail-subtitle::before {
    content: '\1F4D6';
    font-size: 18px;
  }

  /* --- SEO Style Box (Read More / Devamını Gör) --- */
  .cd-seo-box {
    background-color: var(--panel-bg, #1e1e24);
    padding: 30px;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
    margin-bottom: 25px;
    border: 1px solid rgba(255, 255, 255, 0.05);
  }

  .cd-seo-box .seo-content-wrapper {
    position: relative;
    max-height: 145px;
    overflow: hidden;
    transition: max-height 0.6s ease;
  }

  .cd-seo-box .seo-content-wrapper.expanded {
    max-height: 2500px;
  }

  .cd-seo-box .seo-blur-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 75px;
    background: linear-gradient(to bottom, rgba(30, 30, 36, 0) 0%, rgba(30, 30, 36, 0.8) 60%, rgba(30, 30, 36, 1) 100%);
    backdrop-filter: blur(3px);
    -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 1) 60%);
    pointer-events: none;
    transition: opacity 0.3s ease;
  }

  .cd-seo-box .seo-content-wrapper.expanded .seo-blur-overlay {
    opacity: 0;
  }

  .cd-seo-box .read-more-btn {
    display: block;
    margin: 15px auto 0;
    padding: 10px 30px;
    background-color: transparent;
    color: #ffb300;
    border: 1px solid #ffb300;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .cd-seo-box .read-more-btn:hover {
    background-color: #ffb300;
    color: #1e1e24;
    box-shadow: 0 4px 15px rgba(255, 179, 0, 0.2);
  }

  /* --- SEO Responsive Tablolar (Anasayfa seo_faq.php ile Birebir Aynı) --- */
  .seo-table-responsive,
  .cd-seo-box figure.table,
  .seo-box figure.table {
    width: 100% !important;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    margin: 18px 0 26px 0 !important;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(255, 255, 255, 0.02) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    display: block;
  }

  .seo-table-responsive table,
  .cd-seo-box figure.table table,
  .seo-box figure.table table,
  .cd-seo-box table,
  .seo-box table,
  .detail-text table {
    width: 100% !important;
    min-width: 520px;
    border-collapse: collapse !important;
    text-align: left;
    margin: 0 !important;
    border: none !important;
  }

  .seo-table-responsive thead th,
  .seo-table-responsive tr:first-child th,
  .seo-table-responsive th,
  .cd-seo-box table thead th,
  .cd-seo-box table tr:first-child th,
  .cd-seo-box table th,
  .seo-box table thead th,
  .seo-box table tr:first-child th,
  .seo-box table th,
  .detail-text table thead th,
  .detail-text table tr:first-child th,
  .detail-text table th {
    background: linear-gradient(90deg, rgba(255, 179, 0, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%) !important;
    color: #ffb300 !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
    padding: 13px 18px !important;
    border: none !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    white-space: nowrap;
  }

  .seo-table-responsive tbody td,
  .seo-table-responsive td,
  .cd-seo-box table tbody td,
  .cd-seo-box table td,
  .seo-box table tbody td,
  .seo-box table td,
  .detail-text table tbody td,
  .detail-text table td {
    padding: 13px 18px !important;
    font-size: 14px !important;
    color: #d1d5db !important;
    border: none !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    transition: background-color 0.2s ease, color 0.2s ease;
  }

  .seo-table-responsive tbody tr:last-child td,
  .seo-table-responsive tr:last-child td,
  .cd-seo-box table tbody tr:last-child td,
  .cd-seo-box table tr:last-child td,
  .seo-box table tbody tr:last-child td,
  .seo-box table tr:last-child td,
  .detail-text table tbody tr:last-child td,
  .detail-text table tr:last-child td {
    border-bottom: none !important;
  }

  .seo-table-responsive tbody tr:nth-child(even),
  .seo-table-responsive tr:nth-child(even),
  .cd-seo-box table tbody tr:nth-child(even),
  .cd-seo-box table tr:nth-child(even),
  .seo-box table tbody tr:nth-child(even),
  .seo-box table tr:nth-child(even),
  .detail-text table tbody tr:nth-child(even),
  .detail-text table tr:nth-child(even) {
    background-color: rgba(255, 255, 255, 0.015) !important;
  }

  .seo-table-responsive tbody tr:hover td,
  .seo-table-responsive tr:hover td,
  .cd-seo-box table tbody tr:hover td,
  .cd-seo-box table tr:hover td,
  .seo-box table tbody tr:hover td,
  .seo-box table tr:hover td,
  .detail-text table tbody tr:hover td,
  .detail-text table tr:hover td {
    background-color: rgba(255, 179, 0, 0.04) !important;
    color: #ffffff !important;
  }

  .seo-table-responsive tbody td:first-child,
  .seo-table-responsive td:first-child,
  .cd-seo-box table tbody td:first-child,
  .cd-seo-box table td:first-child,
  .seo-box table tbody td:first-child,
  .seo-box table td:first-child,
  .detail-text table tbody td:first-child,
  .detail-text table td:first-child {
    color: #ffffff !important;
    font-weight: 500 !important;
  }

  @media (max-width: 768px) {

    .seo-table-responsive thead th,
    .seo-table-responsive tr:first-child th,
    .seo-table-responsive th,
    .seo-table-responsive tbody td,
    .seo-table-responsive td,
    .cd-seo-box table th,
    .cd-seo-box table td,
    .seo-box table th,
    .seo-box table td,
    .detail-text table th,
    .detail-text table td {
      padding: 10px 14px !important;
      font-size: 13px !important;
    }
  }

  /* --- Section fade-in animation on load --- */
  .cd-fade-in {
    opacity: 0;
    transform: translateY(16px);
    animation: cdFadeIn 0.5s ease forwards;
  }

  .cd-fade-in:nth-child(1) {
    animation-delay: 0s;
  }

  .cd-fade-in:nth-child(2) {
    animation-delay: 0.08s;
  }

  .cd-fade-in:nth-child(3) {
    animation-delay: 0.16s;
  }

  .cd-fade-in:nth-child(4) {
    animation-delay: 0.24s;
  }

  .cd-fade-in:nth-child(5) {
    animation-delay: 0.32s;
  }

  .cd-fade-in:nth-child(6) {
    animation-delay: 0.40s;
  }

  .cd-fade-in:nth-child(7) {
    animation-delay: 0.48s;
  }

  @keyframes cdFadeIn {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* --- Responsive --- */
  @media (max-width: 768px) {
    .category-detail-page .product-info-card.cd-enhanced {
      padding: 16px;
      border-radius: 18px;
    }

    .category-detail-page .product-info-card.cd-enhanced .product-info-top {
      grid-template-columns: auto 1fr;
      grid-template-rows: auto auto auto;
      grid-template-areas:
        "icon title"
        "desc desc"
        "meta meta";
      column-gap: 14px;
      row-gap: 14px;
      align-items: center;
    }

    .category-detail-page .product-info-card.cd-enhanced .product-info-icon {
      width: 58px;
      height: 58px;
      border-radius: 14px;
      align-self: center;
    }

    .category-detail-page .product-info-card.cd-enhanced .product-info-icon img {
      border-radius: 13px;
    }

    .category-detail-page .product-info-card.cd-enhanced .product-info-title-row {
      align-self: center;
    }

    .category-detail-page .product-info-card.cd-enhanced .product-info-title {
      font-size: 19px;
      font-weight: 700;
      line-height: 1.25;
    }

    .category-detail-page .product-info-card.cd-enhanced .product-info-desc {
      font-size: 13.5px;
      line-height: 1.45;
      color: #9ca3af;
      margin: 0;
    }

    .category-detail-page .product-info-card.cd-enhanced .product-info-meta-group {
      flex-direction: column;
      width: 100%;
      gap: 14px;
      margin-top: 0;
    }

    .category-detail-page .product-info-card.cd-enhanced .meta {
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
      gap: 6px;
      width: 100%;
    }

    .category-detail-page .product-info-card.cd-enhanced .meta .meta-item {
      flex: 1 1 0;
      min-width: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      padding: 8px 2px;
      border-radius: 10px;
      font-size: 11px;
      font-weight: 500;
      white-space: nowrap;
      text-align: center;
    }

    .category-detail-page .product-info-card.cd-enhanced .store-badges {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
      margin-left: 0;
      margin-top: 0;
    }

    .category-detail-page .product-info-card.cd-enhanced .store-icon {
      height: 36px;
      width: auto;
      max-width: 130px;
    }

    /* Sub Category Cards Mobile Layout */
    .sub-cat-name-desktop {
      display: none !important;
    }

    .sub-cat-name-mobile {
      display: inline !important;
    }

    .category-detail-page .sub-category-section.cd-enhanced {
      padding: 16px;
      border-radius: 18px;
    }

    .category-detail-page .sub-category-section.cd-enhanced .cd-step-header {
      margin-bottom: 12px;
    }

    .category-detail-page .sub-category-cards {
      display: flex !important;
      flex-direction: row !important;
      flex-wrap: nowrap !important;
      gap: 8px !important;
      width: 100% !important;
      overflow-x: auto;
      scrollbar-width: none;
      -webkit-overflow-scrolling: touch;
    }

    .category-detail-page .sub-category-cards::-webkit-scrollbar {
      display: none;
    }

    .category-detail-page .sub-category-section.cd-enhanced .sub-cat-card {
      flex: 1 1 0;
      min-width: 0;
      display: inline-flex !important;
      flex-direction: row !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 7px !important;
      padding: 10px 6px !important;
      border-radius: 12px !important;
      background: rgba(255, 255, 255, 0.03) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      box-shadow: none !important;
      transform: none !important;
    }

    .category-detail-page .sub-category-section.cd-enhanced .sub-cat-card.active {
      border: 1.5px solid #ffb300 !important;
      background: rgba(255, 179, 0, 0.06) !important;
      box-shadow: 0 0 16px rgba(255, 179, 0, 0.15) !important;
    }

    .category-detail-page .sub-category-section.cd-enhanced .sub-cat-icon {
      width: 26px !important;
      height: 26px !important;
      border-radius: 6px !important;
      flex-shrink: 0 !important;
      box-shadow: none !important;
      border: none !important;
      background: transparent !important;
    }

    .category-detail-page .sub-category-section.cd-enhanced .sub-cat-icon img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 6px;
    }

    .category-detail-page .sub-category-section.cd-enhanced .sub-cat-text {
      display: flex;
      align-items: center;
      min-width: 0;
    }

    .category-detail-page .sub-category-section.cd-enhanced .sub-cat-name {
      font-size: 10px !important;
      font-weight: 600 !important;
      color: #ffffff !important;
      white-space: nowrap !important;
      overflow: hidden;
      text-overflow: ellipsis;
      line-height: 1 !important;
    }

    /* ID Input Section Mobile Layout */
    .category-detail-page .id-input-section.cd-enhanced {
      padding: 16px;
      border-radius: 18px;
    }

    .category-detail-page .id-input-header {
      margin-bottom: 12px;
    }

    .category-detail-page .id-input-fields.has-server-id {
      display: grid !important;
      grid-template-columns: 1.5fr 1fr !important;
      gap: 10px !important;
    }

    .category-detail-page .id-input-label {
      font-size: 13px;
      margin-bottom: 6px;
    }

    .category-detail-page .id-input-section.cd-enhanced .id-field {
      height: 48px;
      padding: 0 14px;
      font-size: 14px;
      border-radius: 12px !important;
    }

    .category-detail-page .id-input-section.cd-enhanced .id-field::placeholder {
      font-size: 13.5px;
    }

    .category-detail-page .id-input-hint {
      font-size: 11.5px;
      margin-top: 8px;
    }

    .cd-step-number {
      width: 30px;
      height: 30px;
      font-size: 13px;
      border-radius: 8px;
    }

    .cd-payment-total .total-value {
      font-size: 18px;
    }
  }

  /* --- MOBİL YÜZEN ÖDEME BARI (FLOATING ACTION BAR) --- */
  .floating-action-bar {
    position: fixed;
    bottom: calc(16px + env(safe-area-inset-bottom, 0px));
    left: 50%;
    width: calc(100% - 32px);
    max-width: 600px;
    background: rgba(17, 23, 32, 0.88);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 184, 23, 0.28);
    border-radius: 20px;
    box-shadow: 0 24px 40px rgba(0, 0, 0, 0.8), inset 0 1px 0 rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px 10px 20px;
    z-index: 9001;
    box-sizing: border-box;
    opacity: 0;
    transform: translate(-50%, 120%);
    pointer-events: none;
    transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.3s ease;
  }

  .floating-action-bar.is-visible {
    opacity: 1;
    transform: translate(-50%, 0);
    pointer-events: auto;
  }

  .floating-action-bar .fab-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    text-align: left;
  }

  .floating-action-bar .fab-label {
    font-size: 0.75rem;
    color: #9ba7b6;
    font-weight: 600;
  }

  .floating-action-bar .fab-price {
    font-size: 1.15rem;
    color: #fff;
    font-weight: 850;
    letter-spacing: -0.02em;
  }

  .floating-action-bar .fab-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(110deg, #ffc52e, #ff9d05);
    color: #17130a;
    border: none;
    border-radius: 14px;
    padding: 0 18px;
    min-height: 46px;
    font-size: 0.85rem;
    font-weight: 850;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(255, 157, 5, 0.25);
    transition: transform 0.2s ease, filter 0.2s ease;
    text-decoration: none;
    line-height: 1;
  }

  .floating-action-bar .fab-btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.08);
  }

  .floating-action-bar .fab-btn svg {
    width: 18px;
    height: 18px;
  }

  @media (min-width: 851px) {
    .floating-action-bar {
      display: none !important;
    }
  }

  @media (max-width: 850px) {
    .payment-section-fixed {
      display: none !important;
      
    }
      .ps-pass-image {
    margin-top: -8px !important;
    margin-left: -8px !important;
  }
  }

  /* --- MLBB Dynamic Verification Card & Flow States --- */
  .mlbb-account-card {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-top: 16px;
    padding: 14px 18px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: cdFadeIn 0.35s ease;
  }

  .mlbb-account-card.state-checking {
    background: rgba(255, 183, 25, 0.08);
    border-color: rgba(255, 183, 25, 0.35);
  }

  .mlbb-account-card.state-checking .mlbb-account-icon {
    color: #ffb719;
    font-size: 22px;
  }

  .mlbb-account-card.state-checking .mlbb-account-title {
    color: #ffc233;
    font-weight: 600;
    font-size: 14.5px;
  }

  .mlbb-account-card.state-checking .mlbb-account-desc {
    color: #aeb9cd;
    font-size: 12px;
    margin-top: 2px;
  }

  .mlbb-account-card.state-ready {
    background: linear-gradient(135deg, rgba(80, 205, 137, 0.12) 0%, rgba(80, 205, 137, 0.05) 100%);
    border-color: rgba(80, 205, 137, 0.45);
    box-shadow: 0 4px 20px rgba(80, 205, 137, 0.15);
  }

  .mlbb-account-card.state-ready .mlbb-account-icon {
    color: #50cd89;
    font-size: 24px;
  }

  .mlbb-account-card.state-ready .mlbb-account-title {
    color: #50cd89;
    font-weight: 700;
    font-size: 15.5px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .mlbb-account-card.state-ready .mlbb-account-desc {
    color: #d1d5db;
    font-size: 12.5px;
    margin-top: 3px;
  }

  .mlbb-account-card.state-error {
    background: linear-gradient(135deg, rgba(241, 65, 108, 0.12) 0%, rgba(241, 65, 108, 0.05) 100%);
    border-color: rgba(241, 65, 108, 0.45);
    box-shadow: 0 4px 20px rgba(241, 65, 108, 0.12);
  }

  .mlbb-account-card.state-error .mlbb-account-icon {
    color: #f1416c;
    font-size: 22px;
  }

  .mlbb-account-card.state-error .mlbb-account-title {
    color: #f1416c;
    font-weight: 600;
    font-size: 14.5px;
  }

  .mlbb-account-card.state-error .mlbb-account-desc {
    color: #ff9fb2;
    font-size: 12px;
    margin-top: 2px;
  }

  .mlbb-account-body {
    flex: 1;
    min-width: 0;
  }

  .mlbb-account-recheck {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px;
    color: #d1d5db;
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.25s ease;
    flex-shrink: 0;
  }

  .mlbb-account-recheck:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    transform: rotate(60deg);
  }

  /* Attention pulse when clicking button without entering/verifying ID */
  .flow-field-attention {
    outline: 2px solid #ff9500 !important;
    outline-offset: 3px !important;
    box-shadow: 0 0 20px rgba(255, 149, 0, 0.6) !important;
    border-color: #ff9500 !important;
    animation: mlbbFieldPulse 1.4s ease-in-out infinite;
  }

  @keyframes mlbbFieldPulse {

    0%,
    100% {
      outline-color: #ff9500;
      box-shadow: 0 0 16px rgba(255, 149, 0, 0.5);
    }

    50% {
      outline-color: #ff5c16;
      box-shadow: 0 0 24px rgba(255, 92, 22, 0.7);
    }
  }

  .id-field[aria-invalid="true"] {
    border-color: #f1416c !important;
    box-shadow: 0 0 10px rgba(241, 65, 108, 0.25) !important;
  }

  .id-field.is-verified {
    border-color: #50cd89 !important;
  }

  /* Button status styles for MLBB */
  .btn-submit-order-class.is-need-id {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    color: #111 !important;
    box-shadow: 0 4px 18px rgba(245, 158, 11, 0.3) !important;
  }

  .btn-submit-order-class.is-need-id:hover {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
    box-shadow: 0 8px 26px rgba(245, 158, 11, 0.45) !important;
  }

  .btn-submit-order-class.is-error {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: #fff !important;
    box-shadow: 0 4px 18px rgba(239, 68, 68, 0.3) !important;
  }
</style>
<?php $this->load->view('category-detail-style'); ?>
<main class="main-content category-detail-page">
  <div class="container">
    <div class="product-page-layout">
      <div class="product-left-col">
        <section class="product-info-card cd-enhanced cd-fade-in" id="product-info">
          <div class="product-info-top">
            <div class="product-info-icon">
              <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $category->category_image ?>"
                alt="<?= htmlspecialchars($category->category_title) ?>">
            </div>
            <div class="product-info-title-row">
              <h1 class="product-info-title">
                <?= htmlspecialchars(!empty($h1_tag) ? $h1_tag : $category->category_title) ?>
              </h1>
            </div>
            <p class="product-info-desc" id="product-desc-text"><?= html_escape($description) ?></p>

            <div class="product-info-meta-group">
              <div class="meta">
                <div class="meta-item view">
                  <i class="fa-solid fa-bolt" style="color: #FFD43B;"></i><span>Hızlı Teslimat</span>
                </div>
                <div class="meta-item rating">
                  <i class="fa-solid fa-comments" style="color: #B197FC;"></i><span>7/24 Destek</span>
                </div>
                <div class="meta-item favorites">
                  <i class="fa-solid fa-shield" style="color: #6fdb9a;"></i><span>Güvenli Ödeme</span>
                </div>
              </div>

              <?php
              $androidUrl = trim((string) ($category->category_android_app_url ?? ''));
              $iosUrl = trim((string) ($category->category_ios_app_url ?? ''));
              $hasAndroid = !empty($androidUrl) && $androidUrl !== '0';
              $hasIos = !empty($iosUrl) && $iosUrl !== '0';
              ?>
              <?php if ($hasAndroid || $hasIos): ?>
                <div class="store-badges">
                  <?php if ($hasAndroid): ?>
                    <?php $androidHref = (!preg_match('~^https?://~i', $androidUrl)) ? 'https://' . $androidUrl : $androidUrl; ?>
                    <a href="<?= html_escape($androidHref) ?>" target="_blank" rel="noopener noreferrer"
                      class="store-badge">
                      <img src="<?= getenv('CDN_URL') ?>uploads/cdn-gallery/general/googleplay.webp" alt="Google Play"
                        class="store-icon">
                    </a>
                  <?php endif; ?>
                  <?php if ($hasIos): ?>
                    <?php $iosHref = (!preg_match('~^https?://~i', $iosUrl)) ? 'https://' . $iosUrl : $iosUrl; ?>
                    <a href="<?= html_escape($iosHref) ?>" target="_blank" rel="noopener noreferrer" class="store-badge">
                      <img src="<?= getenv('CDN_URL') ?>uploads/cdn-gallery/general/appstore.webp" alt="App Store"
                        class="store-icon">
                    </a>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </section>

        <?php if (!empty($subCategories)): ?>
          <section class="sub-category-section cd-enhanced cd-fade-in" id="sub-category-section">
            <div class="cd-step-header">
              <div class="cd-step-number">1</div>
              <h2 class="section-label">Alt Kategori Seçin</h2>
            </div>
            <div class="sub-category-cards">

              <?php foreach ($subCategories as $subCat): ?>
                <?php
                $subTitle = $subCat->category_title;
                $mobileTitle = $subTitle;

                if ((int) ($category->category_id ?? 0) === 13) {
                  if (stripos($subCat->category_slug, 'ilk-yukleme') !== false || stripos($subTitle, 'İlk Yükleme') !== false || stripos($subTitle, 'Ilk Yukleme') !== false) {
                    $mobileTitle = 'İlk Yükleme';
                  } elseif (stripos($subCat->category_slug, 'global') !== false || stripos($subTitle, 'Global') !== false) {
                    $mobileTitle = 'Global';
                  } elseif (stripos($subCat->category_slug, 'elmas') !== false || stripos($subTitle, 'Elmas') !== false) {
                    $mobileTitle = 'Elmas';
                  } else {
                    $mobileTitle = trim(preg_replace('/^(Mobile\s+Legends(:?\s*Bang\s*Bang)?|MLBB)\s*[-:]?\s*/iu', '', $subTitle));
                  }
                }
                ?>
                <a href="<?= html_escape(topupcenter() . $uri . '/' . $subCat->category_slug) ?>"
                  class="sub-cat-card <?= ($activeSubCatId == $subCat->category_id) ? 'active' : '' ?>">
                  <div class="sub-cat-icon">
                    <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $subCat->category_image ?>"
                      alt="<?= htmlspecialchars($subCat->category_title) ?>">
                  </div>
                  <div class="sub-cat-text">
                    <span class="sub-cat-name sub-cat-name-desktop"><?= htmlspecialchars($subTitle) ?></span>
                    <span class="sub-cat-name sub-cat-name-mobile"><?= htmlspecialchars($mobileTitle) ?></span>
                  </div>
                </a>
              <?php endforeach; ?>

            </div>
          </section>
        <?php endif; ?>

        <?php if (!empty($category->category_requires)): ?>
          <?php
          $hasServerReq = false;
          foreach ($category->category_requires as $r) {
            if (($r->inputName ?? '') === 'server_id') {
              $hasServerReq = true;
              break;
            }
          }
          ?>
          <section class="id-input-section cd-enhanced cd-fade-in" id="id-input-section">
            <div class="id-input-header">
              <div class="cd-step-header" style="margin-bottom:0; flex-grow: 1;">
                <div class="cd-step-number"><?= !empty($subCategories) ? '2' : '1' ?></div>
                <h2 class="section-label">Bilgilerinizi Girin</h2>
              </div>
            </div>
            <div class="id-input-fields <?= $hasServerReq ? 'has-server-id' : '' ?>">
              <?php foreach ($category->category_requires as $req): ?>
                <?php if (($req->inputName ?? '') === 'server_id' && !empty($category->servers))
                  continue; ?>
                <?php
                $reqName = $req->inputName ?? '';
                $fieldLabel = ($reqName === 'user_id') ? 'Oyuncu ID' : (($reqName === 'server_id') ? 'Sunucu ID' : ($req->inputPlaceholder ?? $reqName));
                $fieldPlaceholder = $req->inputPlaceholder ?? '';
                ?>
                <div class="id-input-group <?= $reqName ? 'id-group-' . htmlspecialchars($reqName) : '' ?>">
                  <div class="id-input-label-row">
                    <label for="<?= htmlspecialchars($req->inputName) ?>" class="id-input-label">
                      <?= htmlspecialchars($fieldLabel) ?>
                    </label>
                    <?php if (($req->inputName ?? '') === 'user_id' && !empty($uniqueUserIds)): ?>
                      <span class="last-used-ids-trigger" data-bs-toggle="modal" data-bs-target="#idModal">
                        <i class="fa-solid fa-clock-rotate-left"></i> Son kullanılan ID'ler
                      </span>
                    <?php endif; ?>
                  </div>
                  <?php
                  $isNumber = ($req->inputType ?? '') === 'number';
                  $inputRealType = $isNumber ? 'text' : htmlspecialchars($req->inputType ?? 'text');
                  $inputMode = $isNumber ? 'inputmode="numeric"' : '';
                  $pattern = !empty($req->inputPattern) ? $req->inputPattern : ($isNumber ? '[0-9]*' : '');
                  ?>
                  <input type="<?= $inputRealType ?>" name="<?= htmlspecialchars($req->inputName) ?>"
                    id="<?= htmlspecialchars($req->inputName) ?>" class="id-field player-field"
                    placeholder="<?= htmlspecialchars($fieldPlaceholder) ?>" <?= $inputMode ?>
                    <?= !empty($req->inputMaxLength) ? 'maxlength="' . (int) $req->inputMaxLength . '"' : '' ?>
                    <?= !empty($req->inputMinLength) ? 'minlength="' . (int) $req->inputMinLength . '"' : '' ?>
                    <?= !empty($pattern) ? 'pattern="' . htmlspecialchars($pattern) . '"' : '' ?>
                    <?= !empty($req->inputPatternError) ? 'title="' . htmlspecialchars($req->inputPatternError) . '"' : ($isNumber ? 'title="Sadece rakam girebilirsiniz."' : '') ?>     <?= !empty($req->inputRequired) ? 'required' : '' ?>>
                </div>
              <?php endforeach; ?>

              <?php if (!empty($category->servers) && $isServerRequired): ?>
                <div class="id-input-group id-group-server_id">
                  <div class="id-input-label-row">
                    <label for="server_id" class="id-input-label">
                      Sunucu ID
                    </label>
                  </div>
                  <select class="id-field select player-field" name="server_id" id="server_id" <?= $serverRequiredAttr ?>>
                    <option value=""><?= htmlspecialchars($serverPlaceholder) ?></option>
                    <?php foreach ($category->servers as $server): ?>
                      <option value="<?= htmlspecialchars($server->title) ?>">
                        <?= htmlspecialchars($server->title) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
            </div>
            <?php if ($category->category_id == 13): ?>
              <div id="mlbb-account-card" class="mlbb-account-card" style="display: none;" role="status" aria-live="polite">
                <div class="mlbb-account-icon" id="mlbb-account-icon">
                  <i class="fa-solid fa-circle-notch fa-spin"></i>
                </div>
                <div class="mlbb-account-body">
                  <div class="mlbb-account-title" id="mlbb-account-title">Hesap kontrol ediliyor...</div>
                  <div class="mlbb-account-desc" id="mlbb-account-desc">Lütfen bekleyin</div>
                </div>
              </div>
            <?php else: ?>
              <div id="mlbb-user" class="d-none mt-3 p-3 text-white"
                style="background:#311b5e; border-radius:8px; font-weight:600; text-align:center;"></div>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <section class="product-selection-section cd-enhanced cd-fade-in" id="product-selection-section">
          <div class="cd-step-header">
            <div class="cd-step-number">
              <?php
              $stepNum = 1;
              if (!empty($subCategories))
                $stepNum++;
              if (!empty($category->category_requires))
                $stepNum++;
              echo $stepNum;
              ?>
            </div>
            <h2 class="section-label">Ürün Seçin</h2>
          </div>
          <?php
          $displayProducts = [];
          // If subcategories exist, filter by the active one.
          if (!empty($subCategories)) {
            if (isset($groupedProducts[$activeSubCatId])) {
              $displayProducts = $groupedProducts[$activeSubCatId];
            }
          } else {
            // Fallback for parent categories that have no subcategories
            if (isset($groupedProducts[0])) {
              $displayProducts = $groupedProducts[0];
            } elseif (isset($groupedProducts[$activeSubCatId])) {
              $displayProducts = $groupedProducts[$activeSubCatId];
            }
          }
          $displayCurrency = displayCurrency($currentLocale->locale_currency ?? 'TRY');
          $featuredProducts = [];
          $featuredProductIds = [];
          if (!empty($displayProducts)) {
            foreach ($displayProducts as $p) {
              if (!empty($p->product_is_featured) && (int) $p->product_is_featured === 1) {
                $featuredProducts[] = $p;
                $featuredProductIds[] = (int) $p->product_id;
              }
            }
            if (count($featuredProducts) > 3) {
              $featuredProducts = array_slice($featuredProducts, 0, 3);
              $featuredProductIds = array_slice($featuredProductIds, 0, 3);
            }
          }
          $firstAvailableProductId = 0;
          if (!empty($displayProducts)) {
            foreach ($displayProducts as $availableProduct) {
              if ((int) ($availableProduct->product_api_status ?? 0) === 1 && (float) $availableProduct->user_price > 0) {
                $firstAvailableProductId = (int) $availableProduct->product_id;
                break;
              }
            }
          }
          ?>

          <?php if (!empty($featuredProducts)): ?>
            <section class="ps-featured-passes" aria-label="Öne Çıkan Paketler">
              <div class="ps-pass-heading">Öne Çıkan Paketler</div>
              <div class="ps-pass-grid count-<?= count($featuredProducts) ?>">
                <?php foreach ($featuredProducts as $fIdx => $fProduct): ?>
                  <?php
                  $fIsInStock = (int) ($fProduct->product_api_status ?? 0) === 1 && (float) $fProduct->user_price > 0;
                  $fIsSelected = (($this->input->get('p') !== null && $this->input->get('p') == $fProduct->product_id) || ($this->input->get('p') === null && (int) $fProduct->product_id === $firstAvailableProductId));
                  $fDiscountBadge = '';
                  if (!empty($fProduct->list_price) && $fProduct->list_price > $fProduct->user_price) {
                    $fDiscount = round((($fProduct->list_price - $fProduct->user_price) / $fProduct->list_price) * 100);
                    if ($fDiscount > 0) {
                      $fDiscountBadge = '<span class="ps-pass-discount">%' . $fDiscount . ' indirim</span>';
                    }
                  }
                  $toneNum = ($fIdx % 3) + 1;
                  $hasBadge = !empty($fProduct->product_badge);
                  ?>
                  <button type="button" class="ps-big-pass <?= $hasBadge ? 'ps-highlight-package' : '' ?>"
                    data-pass-product="<?= htmlspecialchars($fProduct->product_id) ?>" data-featured-tone="<?= $toneNum ?>"
                    aria-pressed="<?= $fIsSelected ? 'true' : 'false' ?>" <?= !$fIsInStock ? 'disabled' : '' ?>>

                    <span class="ps-pass-top">
                      <img class="ps-pass-image" src="<?= getenv('CDN_URL') . $fProduct->product_icon ?>"
                        alt="<?= htmlspecialchars($fProduct->product_name) ?>" loading="lazy">
                      <span class="ps-pass-copy">
                        <?php if ($hasBadge): ?>
                          <span class="ps-package-recommendation"><?= htmlspecialchars($fProduct->product_badge) ?></span>
                        <?php endif; ?>
                        <strong><?= htmlspecialchars($fProduct->product_name) ?></strong>
                        <?php if (!empty($fProduct->product_short_desc)): ?>
                          <small><?= htmlspecialchars($fProduct->product_short_desc) ?></small>
                        <?php endif; ?>
                      </span>
                    </span>

                    <span class="ps-pass-pricebar">
                      <span class="ps-pass-price-group">
                        <?= $fDiscountBadge ?>
                        <span class="ps-pass-price-row">
                          <span
                            class="ps-pass-price"><?= !$fIsInStock ? 'Stokta Yok' : number_format($fProduct->user_price, 2, ',', '.') . ' ' . $displayCurrency ?></span>
                          <?php if (!empty($fProduct->list_price) && $fProduct->list_price > $fProduct->user_price): ?>
                            <span class="ps-pass-old"><?= number_format($fProduct->list_price, 2, ',', '.') ?>
                              <?= $displayCurrency ?></span>
                          <?php endif; ?>
                        </span>
                      </span>
                    </span>

                    <span class="ps-featured-flash" aria-hidden="true">
                      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M13.6 2.8 5.8 13h5.4l-.8 8.2L18.2 11h-5.4l.8-8.2Z"></path>
                      </svg>
                    </span>
                  </button>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <div class="ps-pass-divider">Tüm ürünler</div>

          <div class="products-grid" id="products-grid">

            <?php if (!empty($displayProducts)): ?>
              <?php foreach ($displayProducts as $key => $product): ?>
                <?php
                $isFeaturedItem = in_array((int) $product->product_id, $featuredProductIds, true);
                $isInStock = (int) ($product->product_api_status ?? 0) === 1 && (float) $product->user_price > 0;
                $discountBadge = '';
                if (!empty($product->list_price) && $product->list_price > $product->user_price) {
                  $discountPercentage = round((($product->list_price - $product->user_price) / $product->list_price) * 100);
                  if ($discountPercentage > 0) {
                    $discountBadge = '<span class="product-discount-badge">%' . $discountPercentage . ' indirim</span>';
                  }
                }
                $isEpinProduct = ((int) ($product->product_type ?? 0) === 3 && empty($product->product_is_bundle));
                $isSelected = (!$isInStock ? false : (($this->input->get('p') !== null && $this->input->get('p') == $product->product_id) || ($this->input->get('p') === null && (int) $product->product_id === $firstAvailableProductId)));
                ?>

                <div role="button" tabindex="0" aria-label="<?= html_escape($product->product_name) ?>"
                  class="product-item <?= !$isInStock ? 'product-out-of-stock' : ($isSelected ? 'selected' : '') ?>"
                  <?= $isFeaturedItem ? 'style="display: none !important;"' : '' ?>
                  data-product="<?= htmlspecialchars($product->product_id) ?>"
                  data-price="<?= htmlspecialchars($product->user_price) ?>" data-epin="<?= $isEpinProduct ? '1' : '0' ?>"
                  data-qty="1" data-name="<?= htmlspecialchars($product->product_name) ?>">

                  <div class="product-item-main">
                    <div class="product-item-icon">
                      <img src="<?= getenv('CDN_URL') . $product->product_icon ?>"
                        alt="<?= htmlspecialchars($product->product_name) ?>" loading="lazy">
                    </div>

                    <div class="product-item-info">
                      <span class="product-item-name"><?= htmlspecialchars($product->product_name) ?></span>
                      <?php if (!empty($product->product_short_desc)): ?>
                        <span
                          class="product-item-short-desc"><?= nl2br(htmlspecialchars($product->product_short_desc)) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="product-item-side">
                    <div class="product-item-side-info">
                      <div class="product-item-side-top">
                        <div class="product-item-badge-wrap">
                          <?= $discountBadge ?>
                        </div>
                        <span class="ps-product-flash" aria-hidden="true">
                          <svg viewBox="0 0 24 24" focusable="false">
                            <path d="M13.6 2.8 5.8 13h5.4l-.8 8.2L18.2 11h-5.4l.8-8.2Z"></path>
                          </svg>
                        </span>
                      </div>

                      <div class="product-item-prices">
                        <span class="product-item-price" <?= $isInStock ? ' itemprop="price" content="' . htmlspecialchars(number_format((float) $product->user_price, 2, '.', '')) . '"' : '' ?>><?= !$isInStock ? 'Stokta Yok' : number_format($product->user_price, 2, ',', '.') . ' ' . $displayCurrency ?></span>
                        <?php if (!empty($product->list_price) && $product->list_price > $product->user_price): ?>
                          <span class="product-item-old-price"><?= number_format($product->list_price, 2, ',', '.') ?>
                            <?= $displayCurrency ?></span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="product-item-divider"></div>

                    <div class="product-item-qty-wrap">
                      <?php if ($isEpinProduct): ?>
                        <div class="cd-qty" role="group" aria-label="Adet Seçimi">
                          <button type="button" class="cd-qty-btn cd-qty-minus" aria-label="Azalt" disabled>-</button>
                          <span class="cd-qty-val">1</span>
                          <button type="button" class="cd-qty-btn cd-qty-plus" aria-label="Arttır">+</button>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div style="width: 100%; padding: 20px; color: var(--gray);">Bu kategoriye ait ürün bulunamadı.</div>
            <?php endif; ?>

            <?php if (!empty($user['user_id'])): ?>
              <section class="payment-section-static" style="grid-column: 1 / -1; width: 100%; margin-top: 20px;">
                <div class="cd-payment-card-static">
                  <div class="cd-payment-total">
                    <span class="total-label">Toplam Tutar</span>
                    <span class="total-value total-transfer-text">0,00 <?= $displayCurrency ?></span>
                  </div>
                  <button
                    class="cd-btn-pay btn-submit-order-class<?= ($category->category_id == 13) ? ' is-need-id' : '' ?>">
                    <?php if ($category->category_id == 13): ?>
                      <i class="fa-solid fa-id-card" style="margin-right: 6px;"></i>ID Bilgilerini Tanımlayın
                    <?php else: ?>
                      <i class="fa-solid fa-lock" style="margin-right: 6px;"></i>Güvenli Ödemeye Geç
                    <?php endif; ?>
                  </button>
                </div>
              </section>
            <?php else: ?>
              <section class="payment-section-static" style="grid-column: 1 / -1; width: 100%; margin-top: 20px;">
                <div class="cd-login-card-static">
                  <div class="cd-login-icon">
                    <i class="fa-solid fa-right-to-bracket" style="color: var(--accent); font-size: 32px;"></i>
                  </div>
                  <h3>Giriş Gerekli</h3>
                  <p>Sipariş verebilmek için lütfen hesabınıza giriş yapın</p>
                  <a href="<?= topupcenter() ?>login?redirect=<?= $this->uri->segment(2) ?>" class="cd-btn-login">Giriş
                    Yapın</a>
                </div>
              </section>
            <?php endif; ?>

          </div>
        </section>

        <?php if (!empty($category_description)): ?>
          <?php
          $categoryDescriptionHtml = preg_replace(
            ['/<h1\b([^>]*)>/i', '/<\/h1>/i'],
            ['<h2$1>', '</h2>'],
            (string) $category_description
          );
          $categoryDescriptionHtml = preg_replace('/<h2\b[^>]*>\s*<\/h2>/i', '', (string) $categoryDescriptionHtml);
          $categoryDescriptionHtml = news_content_clean_html($categoryDescriptionHtml);
          $categoryDescriptionHtml = safe_rich_html($categoryDescriptionHtml);
          $categoryDescriptionHtml = format_category_seo_tables($categoryDescriptionHtml);
          ?>
          <section class="category-detail-section cd-seo-box cd-enhanced cd-fade-in" id="category-detail-section">
            <div class="seo-content-wrapper" id="cd-seo-content">
              <div class="detail-text"><?= $categoryDescriptionHtml ?></div>
              <div class="seo-blur-overlay"></div>
            </div>
            <button class="read-more-btn" id="cd-read-more-btn">Devamını Gör</button>
          </section>
        <?php endif; ?>

        <!-- <section class="how-to-section cd-enhanced cd-fade-in" id="how-to-section">
          <p class="detail-text"><?= ($how_to_install) ?></p>
        </section> -->

        <!-- MOBİL YÜZEN ÖDEME BARI (FLOATING ACTION BAR) -->
        <div class="floating-action-bar" id="paymentBar">
          <div class="fab-info">
            <span class="fab-label">Toplam Tutar</span>
            <strong class="fab-price total-transfer-text">0,00 <?= $displayCurrency ?></strong>
          </div>
          <?php if (!empty($user['user_id'])): ?>
            <button class="fab-btn btn-submit-order-class<?= ($category->category_id == 13) ? ' is-need-id' : '' ?>"
              type="button">
              <?php if ($category->category_id == 13): ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"
                  style="width: 18px; height: 18px; flex-shrink: 0;">
                  <rect x="3" y="4" width="18" height="16" rx="3" />
                  <circle cx="9" cy="10" r="2" />
                  <path d="M15 8h2" />
                  <path d="M15 12h2" />
                  <path d="M7 16h10" />
                </svg>
                ID Bilgilerini Tanımlayın
              <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                  <path d="M5 11V7a7 7 0 0 1 14 0v4" />
                  <rect x="3" y="11" width="18" height="11" rx="2" />
                  <path d="M12 15v3" />
                </svg>
                Güvenli Ödemeye Geç
              <?php endif; ?>
            </button>
          <?php else: ?>
            <a href="<?= topupcenter() ?>login?redirect=<?= $this->uri->segment(2) ?>" class="fab-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                <polyline points="10 17 15 12 10 7" />
                <line x1="15" y1="12" x2="3" y2="12" />
              </svg>
              Giriş Yapın
            </a>
          <?php endif; ?>
        </div>

        <?php if (!empty($user['user_id'])): ?>
          <section class="payment-section-fixed" id="payment-section-fixed">
            <div class="cd-payment-card">
              <div class="cd-payment-total">
                <span class="total-label">Toplam Tutar</span>
                <span class="total-value total-transfer-text">0,00 <?= $displayCurrency ?></span>
              </div>
              <button class="cd-btn-pay btn-submit-order-class<?= ($category->category_id == 13) ? ' is-need-id' : '' ?>">
                <?php if ($category->category_id == 13): ?>
                  <i class="fa-solid fa-id-card" style="margin-right: 6px;"></i>ID Bilgilerini Tanımlayın
                <?php else: ?>
                  <i class="fa-solid fa-lock" style="margin-right: 6px;"></i>Güvenli Ödemeye Geç
                <?php endif; ?>
              </button>
            </div>
          </section>
        <?php else: ?>
          <section class="payment-section-fixed" id="payment-section-fixed">
            <div class="cd-login-card">
              <div class="cd-login-icon">
                <i class="fa-solid fa-right-to-bracket" style="color: var(--accent);"></i>
              </div>
              <h3>Giriş Gerekli</h3>
              <p>Sipariş verebilmek için lütfen hesabınıza giriş yapın</p>
              <a href="<?= topupcenter() ?>login?redirect=<?= $this->uri->segment(2) ?>" class="cd-btn-login">Giriş
                Yapın</a>
            </div>
          </section>
        <?php endif; ?>

      </div>
    </div>

    <?php if (!empty($reviews)): ?>
      <section class="reviews-section product-reviews-section" id="product-reviews-section">
        <div class="section-header">
          <h2 class="section-title">Yorumlar</h2>
        </div>
        <div class="reviews-marquee-wrapper">
          <div class="reviews-marquee-track">
            <?php foreach ($reviews as $rev): ?>
              <?php
              $reviewerName = !empty($rev->user_nickname) ? $rev->user_nickname : (!empty($rev->user_name) ? $rev->user_name : 'Kullanıcı');
              if (mb_strlen($reviewerName) > 4) {
                $reviewerName = mb_substr($reviewerName, 0, 2) . '***' . mb_substr($reviewerName, -2);
              }
              ?>
              <div class="review-card">
                <div class="review-header">
                  <div class="review-avatar">
                    <div class="review-avatar-text"
                      style="width: 40px; height: 40px; border-radius: 50%; background-color: var(--accent); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
                      <?= html_escape(strtoupper(mb_substr($reviewerName, 0, 1))) ?>
                    </div>
                  </div>
                  <div class="review-user-info">
                    <div class="review-username"><?= html_escape($reviewerName) ?></div>
                    <div class="review-product"><?= html_escape(!empty($rev->product_name) ? $rev->product_name : $title) ?>
                    </div>
                  </div>
                  <div class="review-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <?php if ($i <= (int) $rev->review_rating): ?>
                        <svg viewBox="0 0 24 24">
                          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z" />
                        </svg>
                      <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z" />
                        </svg>
                      <?php endif; ?>
                    <?php endfor; ?>
                  </div>
                </div>
                <p class="review-text"><?= nl2br(html_escape($rev->review_comment)) ?></p>
                <div class="review-tags">
                  <span class="review-tag">Hızlı Teslimat</span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <?php if (!empty($category->category_content) || !empty($category_faqs)): ?>
      <style>
        .seo-box {
          background-color: #232732;
          padding: 30px;
          border-radius: 12px;
          border: none;
          box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
          margin-bottom: 25px;
          margin-top: 40px;
          color: #d1d5db;
        }

        .seo-box h2,
        .seo-box h3 {
          color: #ffffff;
          font-size: 20px;
          margin-bottom: 15px;
        }

        .seo-box p {
          line-height: 1.6;
          margin-bottom: 15px;
          font-size: 15px;
        }

        .faq-accordion {
          display: flex;
          gap: 15px;
          align-items: flex-start;
          margin-bottom: 35px;
        }

        .faq-col {
          display: flex;
          flex-direction: column;
          gap: 15px;
          flex: 1;
          min-width: 0;
        }

        @media (max-width: 768px) {
          .faq-accordion {
            flex-direction: column;
          }
        }

        .accordion-item {
          background-color: #232732;
          border-radius: 8px;
          overflow: hidden;
          transition: all 0.3s ease;
          border: none;
          box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .accordion-title {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 18px 24px;
          cursor: pointer;
          user-select: none;
        }

        .accordion-title:hover {
          background-color: #2a2e3a;
        }

        .accordion-title .h6 {
          font-size: 15px;
          font-weight: 600;
          color: #ffffff;
          margin: 0;
          flex: 1;
          padding-right: 15px;
          line-height: 1.4;
        }

        .accordion-icon {
          width: 20px;
          height: 20px;
          color: #ffb300;
          flex-shrink: 0;
        }

        .accordion-content {
          max-height: 0;
          overflow: hidden;
          transition: max-height 0.3s ease-out;
          background-color: #1e222d;
        }

        .content-inner {
          padding: 0 24px 20px 24px;
          margin-top: 10px;
          padding-top: 15px;
          font-size: 14px;
          line-height: 1.6;
          border-top: 1px solid rgba(255, 255, 255, 0.05);
          color: #d1d5db;
        }

        .accordion-item.active {
          box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .accordion-item.active .plus-icon {
          display: none;
        }

        .accordion-item.active .minus-icon {
          display: block !important;
        }
      </style>

      <?php if (!empty($category->category_content)): ?>
        <div class="seo-box">
          <?php
          $seoHtml = preg_replace('/<h2\b[^>]*>\s*<\/h2>/i', '', (string) $category->category_content);
          $seoHtml = format_category_seo_tables(safe_rich_html($seoHtml));
          ?>
          <?= $seoHtml ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($category_faqs)): ?>
        <div class="faq-accordion">
          <?php
          $half = ceil(count($category_faqs) / 2);
          $faqs_col1 = array_slice($category_faqs, 0, $half);
          $faqs_col2 = array_slice($category_faqs, $half);
          ?>
          <div class="faq-col">
            <?php foreach ($faqs_col1 as $faq): ?>
              <div class="accordion-item">
                <div class="accordion-title">
                  <span class="h6"><?= html_escape($faq->faq_question) ?></span>
                  <svg class="accordion-icon plus-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>
                  <svg class="accordion-icon minus-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                  </svg>
                </div>
                <div class="accordion-content">
                  <div class="content-inner">
                    <p><?= nl2br(html_escape($faq->faq_answer)) ?></p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if (!empty($faqs_col2)): ?>
            <div class="faq-col">
              <?php foreach ($faqs_col2 as $faq): ?>
                <div class="accordion-item">
                  <div class="accordion-title">
                    <span class="h6"><?= html_escape($faq->faq_question) ?></span>
                    <svg class="accordion-icon plus-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <svg class="accordion-icon minus-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" style="display:none;">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                    </svg>
                  </div>
                  <div class="accordion-content">
                    <div class="content-inner">
                      <p><?= nl2br(html_escape($faq->faq_answer)) ?></p>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const accordionItems = document.querySelectorAll('.accordion-item');
    accordionItems.forEach(item => {
      const title = item.querySelector('.accordion-title');
      const content = item.querySelector('.accordion-content');

      if (title && content) {
        title.addEventListener('click', () => {
          const isActive = item.classList.contains('active');

          accordionItems.forEach(otherItem => {
            otherItem.classList.remove('active');
            const otherContent = otherItem.querySelector('.accordion-content');
            if (otherContent) otherContent.style.maxHeight = null;
          });

          if (!isActive) {
            item.classList.add('active');
            content.style.maxHeight = content.scrollHeight + "px";
          }
        });
      }
    });
  });
</script>

<?php if (!empty($uniqueUserIds)): ?>
  <div class="modal fade" id="idModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:16px;">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title" style="font-weight: 600; font-size:18px;">Son Kullanılan ID'ler</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-3">
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($uniqueUserIds as $uid): ?>
              <button type="button" class="btn btn-outline-secondary user-span-btn"
                style="border-radius: 8px; font-weight: 600;" data-userid="<?= htmlspecialchars($uid) ?>">
                <?= htmlspecialchars($uid) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<script>
  (function () {
    'use strict';

    const $userInput = document.querySelector('#user_id');
    const $serverField = document.querySelector('#server_id');
    const $totalTransfers = document.querySelectorAll('.total-transfer-text');
    const $btnSubmitOrders = document.querySelectorAll('.btn-submit-order-class');

    // Numeric input alanlarında sadece rakam girilmesini sağla
    document.querySelectorAll('#id-input-section .id-field[inputmode="numeric"]').forEach(input => {
      input.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
      });
    });

    // Intersection Observer for static payment section & bar visibility
    const staticSection = document.querySelector('.payment-section-static');
    const fixedSection = document.getElementById('payment-section-fixed');
    const paymentBar = document.getElementById('paymentBar');
    const bottomNav = document.getElementById('bottomNav');
    let isStaticVisible = false;
    let lastScrollY = window.scrollY || 0;
    let scrollDirection = 'up';

    function getSelectedProduct() {
      return document.querySelector('.product-item.selected:not(.product-out-of-stock)');
    }

    function updateBarsVisibility() {
      const selected = getSelectedProduct();
      const hasSelectedProduct = !!selected;
      const currentScrollY = window.scrollY || 0;
      const isAtTop = currentScrollY < 120; // Sayfanın en üstündeyken mobil alt menü kalır

      // Masaüstü sabit ödeme barı (payment-section-fixed)
      if (fixedSection) {
        if (isStaticVisible || !hasSelectedProduct) {
          fixedSection.style.transform = 'translateY(100%)';
          fixedSection.style.opacity = '0';
          fixedSection.style.pointerEvents = 'none';
        } else {
          fixedSection.style.transform = 'translateY(0)';
          fixedSection.style.opacity = '1';
          fixedSection.style.pointerEvents = 'auto';
        }
      }

      // Mobil yüzen ödeme barı ve bottomNav kontrolü
      if (paymentBar) {
        // Aşağı kaydırırken ve sayfa tepesinde değilken ödeme barı açılır, alt menü gizlenir
        // Yukarı kaydırırken veya sayfa tepesindeyken alt menü geri gelir
        const shouldShowPaymentBar = hasSelectedProduct && !isStaticVisible && !isAtTop && (scrollDirection === 'down');

        if (shouldShowPaymentBar) {
          paymentBar.classList.add('is-visible');
          if (bottomNav) bottomNav.classList.add('is-hidden');
        } else {
          paymentBar.classList.remove('is-visible');
          if (bottomNav) bottomNav.classList.remove('is-hidden');
        }
      }
    }

    // Scroll Yönü Dinleyicisi (Aşağı = Ödeme Barı, Yukarı / En Üst = Mobil Alt Menü)
    let isTicking = false;
    window.addEventListener('scroll', function () {
      if (!isTicking) {
        window.requestAnimationFrame(function () {
          const currentScrollY = window.scrollY || 0;
          const diff = currentScrollY - lastScrollY;

          if (Math.abs(diff) > 6) {
            scrollDirection = (diff > 0) ? 'down' : 'up';
            lastScrollY = currentScrollY;
            updateBarsVisibility();
          } else if (currentScrollY < 120 && scrollDirection !== 'up') {
            scrollDirection = 'up';
            updateBarsVisibility();
          }
          isTicking = false;
        });
        isTicking = true;
      }
    }, { passive: true });

    if (staticSection) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          isStaticVisible = entry.isIntersecting;
          updateBarsVisibility();
        });
      }, { rootMargin: '0px 0px -20px 0px' });
      observer.observe(staticSection);
    }

    // Price Formatter Helper
    function formatPrice(num) {
      return num.toFixed(2).replace('.', ',') + ' <?= $displayCurrency ?>';
    }

    // Update Bottom Price Cards
    const IS_EPIN = <?= ((int) ($category->category_type ?? 0) === 3) ? 'true' : 'false' ?>;
    const MAX_QTY = 10;

    // Quantity Controls Setup
    document.querySelectorAll('.product-item[data-epin="1"]:not(.product-out-of-stock)').forEach(item => {
      if (!item.dataset.qty) item.dataset.qty = '1';
      const qtyWrap = item.querySelector('.product-item-qty-wrap');
      if (!qtyWrap) return;

      qtyWrap.addEventListener('click', e => e.stopPropagation());
      qtyWrap.addEventListener('keydown', e => e.stopPropagation());

      const minusBtn = qtyWrap.querySelector('.cd-qty-minus');
      const plusBtn = qtyWrap.querySelector('.cd-qty-plus');

      if (minusBtn) {
        minusBtn.addEventListener('click', e => {
          e.preventDefault();
          e.stopPropagation();
          setQty(item, (parseInt(item.dataset.qty) || 1) - 1);
        });
      }

      if (plusBtn) {
        plusBtn.addEventListener('click', e => {
          e.preventDefault();
          e.stopPropagation();
          setQty(item, (parseInt(item.dataset.qty) || 1) + 1);
        });
      }
    });

    function setQty(item, n) {
      const q = Math.min(MAX_QTY, Math.max(1, n));
      item.dataset.qty = q;
      const valEl = item.querySelector('.cd-qty-val');
      if (valEl) valEl.textContent = q;
      const minusBtn = item.querySelector('.cd-qty-minus');
      if (minusBtn) minusBtn.disabled = q <= 1;
      const plusBtn = item.querySelector('.cd-qty-plus');
      if (plusBtn) plusBtn.disabled = q >= MAX_QTY;
      if (!item.classList.contains('selected')) {
        document.querySelectorAll('.product-item').forEach(p => p.classList.remove('selected'));
        item.classList.add('selected');
        syncFeaturedPasses();
      }
      updateTotalPrice();
      updateBarsVisibility();
    }

    function selectedQty() {
      const s = document.querySelector('.product-item.selected');
      return s ? (parseInt(s.dataset.qty) || 1) : 1;
    }

    function updateTotalPrice() {
      const selected = document.querySelector('.product-item.selected');
      const unit = selected ? parseFloat(selected.dataset.price) : 0;
      const text = selected ? formatPrice(unit * selectedQty()) : '0,00 <?= $displayCurrency ?>';
      $totalTransfers.forEach(el => el.textContent = text);
    }

    function syncFeaturedPasses() {
      const selected = document.querySelector('.product-item.selected');
      const selectedId = selected ? selected.dataset.product : null;
      document.querySelectorAll('.ps-big-pass').forEach(btn => {
        const isMatched = btn.dataset.passProduct === selectedId;
        btn.setAttribute('aria-pressed', isMatched ? 'true' : 'false');
      });
    }

    // Product item selection
    document.querySelectorAll('.product-item').forEach(item => {
      item.addEventListener('click', function () {
        if (this.classList.contains('product-out-of-stock')) return;
        document.querySelectorAll('.product-item').forEach(p => p.classList.remove('selected'));
        this.classList.add('selected');
        updateTotalPrice();
        if ((window.scrollY || 0) >= 120 && !isStaticVisible) {
          scrollDirection = 'down';
        }
        updateBarsVisibility();
        syncFeaturedPasses();
      });
    });

    document.querySelectorAll('.ps-big-pass').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (this.disabled) return;
        const prodId = this.dataset.passProduct;
        const target = document.querySelector(`.product-item[data-product="${prodId}"]`);
        if (target) {
          target.click();
        }
      });
    });
    syncFeaturedPasses();

    // Modal ID Selector logic
    document.querySelectorAll('.user-span-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        if ($userInput) {
          $userInput.value = this.getAttribute('data-userid');
          $userInput.dispatchEvent(new Event('input'));
        }
        const modal = bootstrap.Modal.getInstance(document.getElementById('idModal'));
        if (modal) modal.hide();
      });
    });

    // MLBB User Check Logic
    const isMlbb = <?= ($category->category_id == 13) ? 'true' : 'false' ?>;
    let mlbbPhase = 'empty'; // 'empty' | 'checking' | 'ready' | 'error'
    let lastVerifiedKey = '';
    let verifiedAccountData = null;
    let mlbbCheckTimer = null;
    let mlbbCheckRevision = 0;

    const idInputSection = document.getElementById('id-input-section');
    const mlbbAccountCard = document.getElementById('mlbb-account-card');
    const mlbbAccountIcon = document.getElementById('mlbb-account-icon');
    const mlbbAccountTitle = document.getElementById('mlbb-account-title');
    const mlbbAccountDesc = document.getElementById('mlbb-account-desc');

    function escapeHtmlText(value) {
      return String(value == null ? '' : value).replace(/[&<>"'`]/g, function (ch) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '`': '&#96;' }[ch];
      });
    }

    function focusMlbbInputs() {
      if (!isMlbb) return;
      const target = (!$userInput || !$userInput.value.trim()) ? $userInput :
        ((!$serverField || !$serverField.value.trim()) ? $serverField : $userInput);
      if (idInputSection) {
        idInputSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      if (target) {
        setTimeout(() => {
          target.focus({ preventScroll: true });
          target.classList.add('flow-field-attention');
          setTimeout(() => target.classList.remove('flow-field-attention'), 1600);
        }, 250);
      }
    }

    function updateMlbbButtonStates(phase, customText) {
      if (!isMlbb) return;
      $btnSubmitOrders.forEach(btn => {
        btn.classList.remove('is-need-id', 'is-error');
        const isFab = btn.classList.contains('fab-btn');

        if (phase === 'checking') {
          btn.disabled = true;
          btn.setAttribute('aria-busy', 'true');
          const icon = '<i class="fa-solid fa-circle-notch fa-spin" style="margin-right: 6px;"></i>';
          btn.innerHTML = icon + (customText || 'Hesap Kontrol Ediliyor...');
        } else if (phase === 'ready') {
          btn.disabled = false;
          btn.removeAttribute('aria-busy');
          const icon = isFab
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="width: 18px; height: 18px; flex-shrink: 0;"><path d="M5 11V7a7 7 0 0 1 14 0v4"/><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M12 15v3"/></svg>'
            : '<i class="fa-solid fa-lock" style="margin-right: 6px;"></i>';
          btn.innerHTML = icon + 'Güvenli Ödemeye Geç';
        } else if (phase === 'error') {
          btn.disabled = false;
          btn.removeAttribute('aria-busy');
          btn.classList.add('is-error');
          const icon = '<i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i>';
          btn.innerHTML = icon + (customText || 'ID Bilgilerini Düzeltin');
        } else {
          // 'empty'
          btn.disabled = false;
          btn.removeAttribute('aria-busy');
          btn.classList.add('is-need-id');
          const icon = isFab
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="width: 18px; height: 18px; flex-shrink: 0;"><rect x="3" y="4" width="18" height="16" rx="3"/><circle cx="9" cy="10" r="2"/><path d="M15 8h2"/><path d="M15 12h2"/><path d="M7 16h10"/></svg>'
            : '<i class="fa-solid fa-id-card" style="margin-right: 6px;"></i>';
          btn.innerHTML = icon + 'ID Bilgilerini Tanımlayın';
        }
      });
    }

    function setMlbbCardState(state, title, desc) {
      if (!mlbbAccountCard) return;
      mlbbAccountCard.classList.remove('state-checking', 'state-ready', 'state-error');

      if (!state) {
        mlbbAccountCard.style.display = 'none';
        return;
      }

      mlbbAccountCard.style.display = 'flex';
      mlbbAccountCard.classList.add('state-' + state);

      if (state === 'checking') {
        if (mlbbAccountIcon) mlbbAccountIcon.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        if (mlbbAccountTitle) mlbbAccountTitle.textContent = title || 'Hesap kontrol ediliyor...';
        if (mlbbAccountDesc) mlbbAccountDesc.textContent = desc || 'Moonton sunucularından oyuncu bilgisi alınıyor...';
      } else if (state === 'ready') {
        if (mlbbAccountIcon) mlbbAccountIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
        if (mlbbAccountTitle) mlbbAccountTitle.innerHTML = escapeHtmlText(title || 'Doğrulanmış Hesap') + ' <span class="badge" style="background:#50cd89; color:#0b1d13; font-size:11px; padding:3px 7px; border-radius:4px; font-weight:700;">ONAYLANDI</span>';
        if (mlbbAccountDesc) mlbbAccountDesc.innerHTML = desc || '';
      } else if (state === 'error') {
        if (mlbbAccountIcon) mlbbAccountIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
        if (mlbbAccountTitle) mlbbAccountTitle.textContent = title || 'Hesap Bulunamadı';
        if (mlbbAccountDesc) mlbbAccountDesc.textContent = desc || 'Oyuncu ID ve Sunucu ID bilgilerinizi kontrol edin.';
      }
    }

    function isMlbbInputsValid() {
      const u = $userInput ? $userInput.value.trim() : '';
      const s = $serverField ? $serverField.value.trim() : '';
      return u.length >= 4 && /^\d+$/.test(u) && s.length >= 4 && /^\d+$/.test(s);
    }

    function checkMlbbUser(force = false) {
      if (!isMlbb) return;
      const u = $userInput ? $userInput.value.trim() : '';
      const s = $serverField ? $serverField.value.trim() : '';
      const currentKey = u + '::' + s;

      if (!u || !s) {
        mlbbPhase = 'empty';
        lastVerifiedKey = '';
        verifiedAccountData = null;
        setMlbbCardState(null);
        if ($userInput) $userInput.removeAttribute('aria-invalid');
        if ($serverField) $serverField.removeAttribute('aria-invalid');
        updateMlbbButtonStates('empty');
        return;
      }

      // Eğer zaten bu bilgilerle doğrulanmışsa ve zorlanmamışsa tekrar sorma
      if (!force && mlbbPhase === 'ready' && lastVerifiedKey === currentKey) {
        return;
      }

      if (!force && !isMlbbInputsValid()) {
        return;
      }

      const currentRevision = ++mlbbCheckRevision;
      mlbbPhase = 'checking';
      setMlbbCardState('checking');
      updateMlbbButtonStates('checking');

      $.ajax({
        url: '<?= topupcenter() . $uri ?>/check-user',
        method: 'POST',
        data: {
          user_id: u,
          server_id: s,
          csrf_token: '<?= html_escape($this->security->get_csrf_hash()) ?>'
        },
        dataType: 'json',
        success: function (response) {
          if (currentRevision !== mlbbCheckRevision) return;
          if (response && response.status === 'success' && response.name) {
            mlbbPhase = 'ready';
            lastVerifiedKey = currentKey;
            verifiedAccountData = response;
            if ($userInput) {
              $userInput.setAttribute('aria-invalid', 'false');
              $userInput.classList.add('is-verified');
            }
            if ($serverField) {
              $serverField.setAttribute('aria-invalid', 'false');
              $serverField.classList.add('is-verified');
            }
            const rawCountry = String(response.register_country || (response.data && response.data.register_country) || '').toLowerCase();
            const country = /^[a-z]{2}$/.test(rawCountry) ? rawCountry : '';
            const flagHtml = country
              ? `<img src="https://flagcdn.com/20x15/${country}.png" alt="${country.toUpperCase()}" style="width: 17px; height: 12px; border-radius: 2px; vertical-align: -1px; margin-right: 5px; display: inline-block; box-shadow: 0 1px 3px rgba(0,0,0,0.4);" />`
              : '';
            const desc = `${flagHtml}ID: ${escapeHtmlText(u)} · Sunucu: (${escapeHtmlText(s)}) · Oyuncu Adı Onaylandı`;
            setMlbbCardState('ready', response.name, desc);
            updateMlbbButtonStates('ready');
          } else {
            mlbbPhase = 'error';
            lastVerifiedKey = '';
            verifiedAccountData = null;
            if ($userInput) {
              $userInput.setAttribute('aria-invalid', 'true');
              $userInput.classList.remove('is-verified');
            }
            if ($serverField) {
              $serverField.setAttribute('aria-invalid', 'true');
              $serverField.classList.remove('is-verified');
            }
            const errMsg = (response && response.message) ? response.message : 'Hesap bulunamadı veya ID/Sunucu bilgileri hatalı.';
            setMlbbCardState('error', 'Hesap Bulunamadı', errMsg);
            updateMlbbButtonStates('error');
          }
        },
        error: function (xhr) {
          if (currentRevision !== mlbbCheckRevision) return;
          mlbbPhase = 'error';
          lastVerifiedKey = '';
          verifiedAccountData = null;
          if ($userInput) {
            $userInput.setAttribute('aria-invalid', 'true');
            $userInput.classList.remove('is-verified');
          }
          if ($serverField) {
            $serverField.setAttribute('aria-invalid', 'true');
            $serverField.classList.remove('is-verified');
          }
          const errMsg = (xhr.responseJSON && xhr.responseJSON.message)
            ? xhr.responseJSON.message
            : 'Sorgulama başarısız oldu. Lütfen bilgilerinizi kontrol edin.';
          setMlbbCardState('error', 'Hesap Doğrulanamadı', errMsg);
          updateMlbbButtonStates('error');
        }
      });
    }

    if (isMlbb) {
      const triggerDebouncedCheck = () => {
        const u = $userInput ? $userInput.value.trim() : '';
        const s = $serverField ? $serverField.value.trim() : '';
        const currentKey = u + '::' + s;

        // Bilgi değişmediyse ve zaten onaylıysa tekrar sorgulama yapma
        if (mlbbPhase === 'ready' && lastVerifiedKey === currentKey) {
          return;
        }

        clearTimeout(mlbbCheckTimer);

        // Değer değiştiğinde eski onayı sıfırla
        if (lastVerifiedKey && lastVerifiedKey !== currentKey) {
          mlbbPhase = 'empty';
          lastVerifiedKey = '';
          if ($userInput) $userInput.classList.remove('is-verified');
          if ($serverField) $serverField.classList.remove('is-verified');
          updateMlbbButtonStates('empty');
          setMlbbCardState(null);
        }

        if (isMlbbInputsValid()) {
          mlbbCheckTimer = setTimeout(() => {
            checkMlbbUser(false);
          }, 450);
        } else {
          mlbbPhase = 'empty';
          lastVerifiedKey = '';
          updateMlbbButtonStates('empty');
          setMlbbCardState(null);
        }
      };

      if ($userInput) $userInput.addEventListener('input', triggerDebouncedCheck);
      if ($serverField) $serverField.addEventListener('input', triggerDebouncedCheck);

      // Check on initial load if pre-filled
      if (isMlbbInputsValid()) {
        checkMlbbUser(false);
      }
    }

    // Form Validation and Order Trigger
    if ($btnSubmitOrders.length > 0) {
      $btnSubmitOrders.forEach(btn => {
        btn.addEventListener('click', function (e) {
          if (btn.disabled) return;

          // MLBB özel kontrol: kullanıcı henüz doğrulanmamışsa ödemeye izin verme, ID alanına odaklan
          if (isMlbb && mlbbPhase !== 'ready') {
            e.preventDefault();
            focusMlbbInputs();
            if (isMlbbInputsValid() && mlbbPhase !== 'checking') {
              checkMlbbUser(true);
            }
            return;
          }

          // Eğer input alanları dom üzerinde varsa tarayıcının yerel validasyon metodlarını tetikliyoruz
          const idInputs = document.querySelectorAll('#id-input-section .id-field');
          for (let i = 0; i < idInputs.length; i++) {
            const field = idInputs[i];
            if (!field.checkValidity()) {
              field.focus();
              field.reportValidity();
              return;
            }
          }

          // Seçili ürün kontrolü
          const selectedProduct = document.querySelector('.product-item.selected');
          if (!selectedProduct) {
            alert('Lütfen bir ürün seçin.');
            return;
          }
          if (parseFloat(selectedProduct.dataset.price || '0') <= 0 || selectedProduct.classList.contains('product-out-of-stock')) {
            alert('Bu ürün şu anda stokta bulunmuyor.');
            return;
          }

          // start ajax request to start payment
          const originalButtonText = btn.innerHTML;
          $btnSubmitOrders.forEach(orderButton => {
            orderButton.disabled = true;
            orderButton.setAttribute('aria-busy', 'true');
          });
          $.ajax({
            url: '<?= topupcenter() . $uri ?>/payment/startPayment',
            method: 'POST',
            data: {
              uid: $userInput ? $userInput.value : null,
              pid: selectedProduct.dataset.product,
              qty: selectedProduct.dataset.epin === '1' ? (parseInt(selectedProduct.dataset.qty) || 1) : 1,
              sid: $serverField ? $serverField.value : null,
              csrf_token: '<?= html_escape($this->security->get_csrf_hash()) ?>'
            },
            success: function (response) {
              if (response.status === 'success') {
                if (response.redirect_url) {
                  window.location.href = response.redirect_url;
                } else {
                  alert('Ödeme sayfası açılamadı. Lütfen tekrar deneyin.');
                }
              } else {
                alert(response.message || 'Ödeme başlatılamadı. Lütfen tekrar deneyin.');
              }
            },
            error: function (xhr, status, error) {
              alert(xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Ödeme başlatılamadı. Lütfen tekrar deneyin.');
            },
            complete: function () {
              $btnSubmitOrders.forEach(orderButton => {
                orderButton.disabled = false;
                orderButton.removeAttribute('aria-busy');
                orderButton.innerHTML = originalButtonText;
              });
            }
          });
        });
        btn.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            btn.click();
          }
        });
      });
    }

    // Devamını Gör Logic for Category Description
    const cdReadMoreBtn = document.getElementById('cd-read-more-btn');
    const cdSeoContent = document.getElementById('cd-seo-content');

    if (cdReadMoreBtn && cdSeoContent) {
      cdReadMoreBtn.addEventListener('click', function () {
        cdSeoContent.classList.toggle('expanded');
        if (cdSeoContent.classList.contains('expanded')) {
          this.innerHTML = 'Daha Az Göster';
        } else {
          this.innerHTML = 'Devamını Gör';
        }
      });
    }

    // İlk açılışta fiyatı ve bar durumunu güncelle
    updateTotalPrice();
    updateBarsVisibility();
  })();
</script>