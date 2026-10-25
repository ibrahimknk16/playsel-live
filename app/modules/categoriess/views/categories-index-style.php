<style>
  .all-categories-page {
    --cat-gold: #ffb300;
    --cat-orange: #ff7300;
    --cat-border: rgba(255, 179, 0, .25);
    --cat-border-strong: rgba(255, 179, 0, .7);
  }

  .all-categories-page .page-title-section {
    position: relative;
    padding-bottom: 16px;
  }

  .all-categories-page .page-title-section::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 52px;
    height: 3px;
    border-radius: 10px;
    background: var(--cat-gold);
    box-shadow: 0 0 14px var(--cat-gold);
    transform: translateX(-50%);
  }

  .all-categories-page .page-title {
    margin-bottom: 10px;
    letter-spacing: .4px;
    text-transform: uppercase;
  }

  .all-categories-page .page-breadcrumbs a:hover {
    color: var(--cat-gold) !important;
  }

  .all-categories-page .categories-panel {
    padding: 24px;
    border: 1px solid var(--cat-border);
    border-radius: 16px;
    background: linear-gradient(145deg, rgba(35, 36, 47, .98), rgba(17, 21, 29, .98));
    box-shadow: 0 12px 34px rgba(0, 0, 0, .3);
  }

  .all-categories-page .cat-filter-tabs {
    gap: 0;
    border: 1px solid rgba(255, 179, 0, .18);
    border-radius: 10px;
    background: rgba(0, 0, 0, .24);
  }

  .all-categories-page .cat-filter-tab {
    justify-content: center;
    color: rgba(255, 255, 255, .58);
    transition: color .25s ease, background .25s ease, transform .25s ease;
  }

  .all-categories-page .cat-filter-tab:hover {
    color: #fff;
    background: rgba(255, 179, 0, .08);
  }

  .all-categories-page .cat-filter-tab.active {
    background: linear-gradient(45deg, var(--cat-gold), var(--cat-orange));
    color: #171717;
    box-shadow: 0 4px 14px rgba(255, 140, 0, .2);
  }

  .all-categories-page .categories-grid {
    gap: 20px;
  }

  .all-categories-page .category-card {
    position: relative;
    padding: 10px;
    overflow: visible;
    border: 1px solid var(--cat-border);
    border-radius: 14px;
    background: linear-gradient(145deg, rgba(35, 36, 47, .98), rgba(22, 25, 32, .98));
    box-shadow: 0 8px 22px rgba(0, 0, 0, .26);
    transition: transform .35s ease, border-color .35s ease, box-shadow .35s ease, background .35s ease;
  }

  .all-categories-page .category-card:hover {
    border-color: var(--cat-border-strong);
    background: linear-gradient(145deg, rgba(49, 43, 31, .98), rgba(22, 25, 32, .98));
    box-shadow: 0 16px 34px rgba(0, 0, 0, .46), 0 0 18px rgba(255, 140, 0, .12);
    transform: translateY(-7px);
  }

  .all-categories-page .cat-logo-box {
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid rgba(255, 179, 0, .14);
  }

  .all-categories-page .cat-logo-box .cat-img {
    transition: transform .5s ease, filter .5s ease;
  }

  .all-categories-page .category-card:hover .cat-logo-box .cat-img {
    transform: scale(1.08);
    filter: saturate(1.1);
  }

  .all-categories-page .cat-go-overlay {
    border-radius: 10px;
    background: rgba(17, 21, 29, .58);
  }

  .all-categories-page .cat-go-btn {
    width: 78%;
    background: linear-gradient(45deg, var(--cat-gold), var(--cat-orange));
    color: #171717;
    box-shadow: 0 5px 16px rgba(255, 140, 0, .22);
  }

  .all-categories-page .cat-go-btn svg {
    fill: #171717;
  }

  .all-categories-page .category-name {
    min-height: 40px;
    padding: 5px 4px 2px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
  }

  .all-categories-page .sub-dropdown-overlay {
    border-color: var(--cat-border);
    background: rgba(22, 25, 32, .98);
    box-shadow: 0 14px 36px rgba(0, 0, 0, .6), 0 0 16px rgba(255, 140, 0, .1);
  }

  .all-categories-page .sub-dropdown-overlay a:hover {
    background: rgba(255, 179, 0, .1);
    color: var(--cat-gold);
  }

  @media (max-width: 768px) {
    .all-categories-page .categories-panel {
      padding: 16px;
    }

    .all-categories-page .cat-filter-tabs {
      overflow-x: auto;
      justify-content: flex-start;
    }

    .all-categories-page .cat-filter-tab {
      flex: 0 0 auto;
      min-width: 128px;
      padding: 13px 16px;
    }
  }
</style>
