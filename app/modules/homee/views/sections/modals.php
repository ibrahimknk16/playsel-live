  <!-- Coupons Modal -->
  <div class="modal-overlay" id="coupons-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3><?= $this->lang->line('index_suitable_coupons') ?></h3>
        <button type="button" class="modal-close" id="close-coupons-modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="coupons-grid modal-coupons-grid">
          <!-- Coupon 1 -->
          <div class="coupon-card">
            <div class="coupon-top">
              <span class="coupon-code">MERHABA10</span>
              <button class="btn-use"><span><?= $this->lang->line('index_use_text') ?></span></button>
            </div>
            <div class="coupon-bottom">
              <span class="coupon-discount">%10 <?= $this->lang->line('index_discount_text') ?></span>
              <span class="coupon-desc">Yeni Üyelere özel indirim</span>
            </div>
          </div>
          <!-- Coupon 2 -->
          <div class="coupon-card">
            <div class="coupon-top">
              <span class="coupon-code">MLBB15</span>
              <button class="btn-use"><span><?= $this->lang->line('index_use_text') ?></span></button>
            </div>
            <div class="coupon-bottom">
              <span class="coupon-discount">%15 indirim</span>
              <span class="coupon-desc">Mlbb ürünlerinde geçerlidir.</span>
            </div>
          </div>
          <!-- Coupon 3 -->
          <div class="coupon-card">
            <div class="coupon-top">
              <span class="coupon-code">MERHABA10</span>
              <button class="btn-use"><span><?= $this->lang->line('index_use_text') ?></span></button>
            </div>
            <div class="coupon-bottom">
              <span class="coupon-discount">%10 indirim</span>
              <span class="coupon-desc">Yeni Üyelere özel indirim</span>
            </div>
          </div>
          <!-- Coupon 4 -->
          <div class="coupon-card">
            <div class="coupon-top">
              <span class="coupon-code">MERHABA10</span>
              <button class="btn-use"><span><?= $this->lang->line('index_use_text') ?></span></button>
            </div>
            <div class="coupon-bottom">
              <span class="coupon-discount">%10 indirim</span>
              <span class="coupon-desc">Yeni Üyelere özel indirim</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>