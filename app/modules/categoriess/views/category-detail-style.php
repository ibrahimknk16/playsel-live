<style>
  .category-detail-page {
    --cd-gold: #ffb300;
    --cd-orange: #ff7300;
    --cd-surface: #161920;
    --cd-surface-deep: #11151d;
    --cd-border: rgba(255, 179, 0, .25);
    --cd-border-strong: rgba(255, 179, 0, .68);
    --cd-muted: #8f96a3;
  }

  .category-detail-page .product-info-card.cd-enhanced,
  .category-detail-page .sub-category-section.cd-enhanced,
  .category-detail-page .id-input-section.cd-enhanced,
  .category-detail-page .product-selection-section.cd-enhanced,
  .category-detail-page .cd-seo-box,
  .category-detail-page .seo-box {
    border: 1px solid var(--cd-border);
    border-radius: 16px;
    background: linear-gradient(145deg, rgba(35, 36, 47, .98), rgba(17, 21, 29, .98));
    box-shadow: 0 10px 30px rgba(0, 0, 0, .26);
  }

  .category-detail-page .product-info-card.cd-enhanced {
    overflow: hidden;
  }

  .category-detail-page .product-info-card.cd-enhanced::before {
    background: radial-gradient(circle, rgba(255, 179, 0, .13) 0%, transparent 70%);
  }

  .category-detail-page .product-info-card.cd-enhanced::after {
    background: radial-gradient(circle, rgba(255, 115, 0, .08) 0%, transparent 70%);
  }

  .category-detail-page .product-info-card.cd-enhanced .product-info-icon {
    border: 1px solid rgba(255, 179, 0, .3);
    box-shadow: 0 10px 30px rgba(0, 0, 0, .42), 0 0 20px rgba(255, 140, 0, .12);
  }

  .category-detail-page .product-info-card.cd-enhanced .meta-item {
    border-color: rgba(255, 179, 0, .2);
    background: rgba(255, 179, 0, .06);
  }

  .category-detail-page .product-info-card.cd-enhanced .meta-item:hover {
    border-color: var(--cd-border-strong);
    background: rgba(255, 179, 0, .12);
    color: #fff;
  }

  .category-detail-page .cd-step-number,
  .category-detail-page .ps-step-num {
    background: linear-gradient(45deg, var(--cd-gold), var(--cd-orange));
    color: #171717;
    box-shadow: 0 5px 18px rgba(255, 140, 0, .25);
  }

  .category-detail-page .sub-category-section.cd-enhanced,
  .category-detail-page .id-input-section.cd-enhanced,
  .category-detail-page .product-selection-section.cd-enhanced {
    padding: 24px;
  }

  .category-detail-page .sub-category-section.cd-enhanced .sub-cat-card,
  .category-detail-page .product-selection-section.cd-enhanced .product-item {
    border-color: rgba(255, 179, 0, .2);
    background: linear-gradient(145deg, rgba(35, 36, 47, .96), rgba(22, 25, 32, .96));
    box-shadow: 0 6px 18px rgba(0, 0, 0, .18);
  }

  .category-detail-page .sub-category-section.cd-enhanced .sub-cat-card:hover,
  .category-detail-page .product-selection-section.cd-enhanced .product-item:hover {
    border-color: var(--cd-border-strong);
    background: linear-gradient(145deg, rgba(49, 43, 31, .98), rgba(22, 25, 32, .98));
    box-shadow: 0 12px 26px rgba(0, 0, 0, .36), 0 0 16px rgba(255, 140, 0, .1);
    transform: translateY(-4px);
  }

  .category-detail-page .sub-category-section.cd-enhanced .sub-cat-card.active,
  .category-detail-page .product-selection-section.cd-enhanced .product-item.selected {
    border-color: var(--cd-gold);
    background: linear-gradient(145deg, rgba(62, 48, 25, .98), rgba(22, 25, 32, .98));
    box-shadow: 0 0 0 1px rgba(255, 179, 0, .18), 0 8px 24px rgba(255, 140, 0, .12);
  }

  .category-detail-page .product-selection-section.cd-enhanced .product-item-icon,
  .category-detail-page .sub-category-section.cd-enhanced .sub-cat-icon {
    border: 1px solid rgba(255, 179, 0, .18);
    box-shadow: 0 4px 14px rgba(0, 0, 0, .3);
  }

  .category-detail-page .product-selection-section.cd-enhanced .product-discount-badge {
    background: linear-gradient(45deg, var(--cd-gold), var(--cd-orange));
    color: #171717;
    border: 0;
  }

  .category-detail-page .id-input-section.cd-enhanced .id-field,
  .category-detail-page .id-input-section.cd-enhanced select {
    border-color: rgba(255, 179, 0, .2);
    background: rgba(0, 0, 0, .22);
    color: #fff;
    outline: none !important;
  }

  .category-detail-page .id-input-section.cd-enhanced .id-field:focus,
  .category-detail-page .id-input-section.cd-enhanced select:focus {
    border-color: var(--cd-gold);
    box-shadow: 0 0 0 3px rgba(255, 179, 0, .1), 0 4px 16px rgba(255, 140, 0, .08);
    outline: none !important;
  }

  .category-detail-page .cd-payment-card-static,
  .category-detail-page .cd-payment-card,
  .category-detail-page .cd-login-card-static,
  .category-detail-page .cd-login-card {
    border-color: var(--cd-border);
    background: linear-gradient(145deg, rgba(35, 36, 47, .98), rgba(17, 21, 29, .98));
  }

  .category-detail-page .cd-payment-card-static .cd-payment-total,
  .category-detail-page .cd-payment-total {
    border-color: rgba(255, 179, 0, .16);
  }

  .category-detail-page .cd-btn-pay,
  .category-detail-page .cd-btn-login,
  .category-detail-page .btn-id-verify,
  .category-detail-page .read-more-btn {
    background: linear-gradient(45deg, var(--cd-gold), var(--cd-orange));
    border-color: transparent;
    color: #171717;
    box-shadow: 0 5px 18px rgba(255, 140, 0, .18);
  }

  .category-detail-page .cd-btn-pay:hover,
  .category-detail-page .cd-btn-login:hover,
  .category-detail-page .btn-id-verify:hover,
  .category-detail-page .read-more-btn:hover {
    background: linear-gradient(45deg, #ffc837, #ff851b);
    box-shadow: 0 10px 26px rgba(255, 140, 0, .3);
  }

  .category-detail-page .category-detail-section.cd-enhanced,
  .category-detail-page .how-to-section.cd-enhanced {
    border-color: var(--cd-border);
    background: linear-gradient(145deg, rgba(35, 36, 47, .98), rgba(17, 21, 29, .98));
  }

  .category-detail-page .accordion-item {
    border: 1px solid rgba(255, 179, 0, .18);
    background: linear-gradient(145deg, rgba(35, 36, 47, .98), rgba(22, 25, 32, .98));
  }

  .category-detail-page .accordion-item:hover,
  .category-detail-page .accordion-item.active {
    border-color: var(--cd-border-strong);
    box-shadow: 0 8px 24px rgba(0, 0, 0, .32), 0 0 14px rgba(255, 140, 0, .08);
  }

  .category-detail-page .accordion-title:hover {
    background: rgba(255, 179, 0, .06);
  }

  .category-detail-page .accordion-content {
    background: rgba(0, 0, 0, .18);
  }

  @media (max-width: 768px) {

    .category-detail-page .sub-category-section.cd-enhanced,
    .category-detail-page .id-input-section.cd-enhanced,
    .category-detail-page .product-selection-section.cd-enhanced {
      padding: 16px;
    }
  }
</style>