<section class="deals-section" id="deals-section">
  <div class="deals-header">
    <h2><?= $this->lang->line('index_special_discounts') ?></h2>
    <div class="countdown-wrapper">
      <svg viewBox="0 0 24 24">
        <path d="M6 2v6l4 4-4 4v6h12v-6l-4-4 4-4V2H6zm10 12.5V20H8v-5.5l4-4 4 4zm-4-5L8 5.5V4h8v1.5l-4 4z"
          fill="#FFC800" />
      </svg>
      <span class="countdown-label"><?= $this->lang->line('index_finish_text') ?>:</span>
      <span class="countdown-digit" id="cd-hours">02</span><span class="countdown-sep">:</span>
      <span class="countdown-digit" id="cd-minutes">33</span><span class="countdown-sep">:</span>
      <span class="countdown-digit" id="cd-seconds">48</span>
    </div>
  </div>
  <div class="deals-grid">
    <?php
    function dealPercent($listPrice, $userPrice, $currency = '₺')
    {
      if ($listPrice <= 0)
        return '';

      $discount = (($listPrice - $userPrice) / $listPrice) * 100;

      if ($discount <= 0)
        return '';

      return '<span class="deal-price-old">' . nFormat($listPrice) . ' ' . $currency . '</span>'
        . '<span class="deal-badge">-%' . round($discount) . '</span>';
    }
    ?>

    <?php foreach ($randomProducts as $product): ?>
      <a class="<?= (float)$product->user_price <= 0 ? 'is-out-of-stock' : '' ?>" href="<?= topupcenter() . $uri . '/' . $product->category_slug . '?p=' . $product->product_id ?>">
        <div class="deal-card animate-on-scroll">
          <div class="deal-card-top">
            <div class="deal-img"><img src="<?= getenv('CDN_URL') . $product->product_icon ?>"
                alt="<?= $product->product_name ?>" loading="lazy"></div>
            <div class="deal-info">
              <h3><?= $product->product_name ?>
              </h3>
              <span class="deal-game">
                <?= $product->category_title ?>
              </span>
            </div>
          </div>
          <div class="deal-divider"></div>
          <div class="deal-pricing">
            <div class="deal-prices">
              <span class="deal-price-current">
                <?= (float)$product->user_price <= 0 ? 'Stokta Yok' : nFormat($product->user_price) . ' ' . displayCurrency($product->product_price_currency) ?>
              </span>
              <?= dealPercent($product->list_price, $product->user_price, displayCurrency($product->product_price_currency)) ?>
            </div>
          </div>
        </div>
      </a>
    <?php endforeach; ?>

  </div>
</section>