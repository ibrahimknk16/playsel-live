<?php
$providers = $providers ?? [];
$currencies = $currencies ?? ['TRY','USD','EUR','AED','IDR'];
$user = $user ?? [];
$providerSegmentId = $this->uri->segment(3);
?>
<div class="card card-flush">
  <div class="card-header">
    <h3 class="card-title">Yeni Provider Hareketi</h3>
  </div>
  <div class="card-body">

    <form method="post" action="<?= base_url('reports/providerbalances_store') ?>">
      <div class="row">
        <div class="col-md-4 mb-4">
          <label class="form-label">Tedarikçi (ID)</label>
          <select id="gen_provider_id" name="provider_id" class="form-select form-select-solid" data-control="select2" required <?= !empty($providerSegmentId) ? 'disabled' : '' ?>>
            <?php foreach ($providers as $p): ?>
              <option <?= $providerSegmentId == $p['epin_provider_id'] ? 'selected' : '' ?> data-currency="<?= htmlspecialchars($p['epin_provider_currency'] ?? '') ?>" data-rate="<?= htmlspecialchars(currencyCalculator($p['epin_provider_currency'] ?? '', 1)) ?>" value="<?= $p['epin_provider_id'] ?>"><?= htmlspecialchars($p['epin_provider_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($providerSegmentId)): ?>
            <input type="hidden" name="provider_id" value="<?= htmlspecialchars($providerSegmentId) ?>">
          <?php endif; ?>
        </div>
        <div class="col-md-4 mb-4">
          <label class="form-label">Para Birimi</label>
          <select id="gen_currency" class="form-select form-select-solid" disabled>
            <?php foreach ($currencies as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>" <?= ($c == ($provider['epin_provider_currency'] ?? '')) ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>            
          </select>
          <input type="hidden" id="gen_currency_input" name="currency" value="<?= htmlspecialchars($provider['epin_provider_currency'] ?? ($currencies[0] ?? '')) ?>">
        </div>
        <div class="col-md-4 mb-4">
          <label class="form-label">İşlemi Yapan Admin</label>
          <input type="text" class="form-control form-control-solid" value="<?= htmlspecialchars(($user['admin_name'] ?? '-') . ' #' . ($user['admin_id'] ?? '-')) ?>" disabled />
        </div>
        
        <div class="col-md-4 mb-4">
          <label class="form-label">İşlem Türü</label>
          <select name="movement_type" class="form-select form-select-solid" required>
            <option value="credit">Bakiye Ekle</option>
            <option value="debit">Harcama</option>
          </select>
        </div>
        <div class="col-md-4 mb-4">
          <label class="form-label">Tutar</label>
          <input id="gen_amount" name="amount" type="text" placeholder="Tutar - TRY" class="form-control form-control-solid" value="" inputmode="decimal" autocomplete="off" required />
        </div>
        <div class="col-md-4 mb-4">
          <label class="form-label fw-semibold">Kur (TRY karşılığı)</label>
          <input type="text" class="form-control form-control-solid" id="gen_rate" name="epin_purchase_rate" value="<?= htmlspecialchars(currencyCalculator('USD' ?? '', 1)) ?>" placeholder="Örn: 38.25" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Not</label>
        <input id="gen_note" name="note" type="text" class="form-control form-control-solid" value="" />
      </div>
      <div class="mb-3 d-flex justify-content-end">
        <a href="<?= base_url('reports/providerbalances') ?>" class="btn btn-secondary me-2">Listeye Dön</a>
        <button type="submit" class="btn btn-sm btn-primary">Kaydet</button>
      </div>
    </form>

    <script>
      (function () {
        if (window.jQuery && jQuery.fn.select2) {
          jQuery('#gen_provider_id').select2({ width: '100%' });
        }

        function updateCurrencyForProvider() {
          var opt = jQuery('#gen_provider_id').find('option:selected');
          var cur = opt.data('currency') || jQuery('#gen_currency option:first').val();
          var rate = opt.data('rate');
          if (cur === undefined || cur === null) cur = '';
          if (rate === undefined || rate === null) rate = (cur === 'TRY' ? 1 : '');
          jQuery('#gen_currency').val(cur);
          jQuery('#gen_currency_input').val(cur);
          jQuery('#gen_rate').val(rate);
          jQuery('#gen_amount').attr('placeholder', 'Tutar - ' + cur);
        }

        function normalizeAmountInput(raw) {
          var val = String(raw || '');
          val = val.replace(/,/g, '');
          val = val.replace(/[^0-9.]/g, '');
          var firstDot = val.indexOf('.');
          if (firstDot !== -1) {
            val = val.substring(0, firstDot + 1) + val.substring(firstDot + 1).replace(/\./g, '');
          }
          return val;
        }

        function bindAmountMask() {
          var amount = jQuery('#gen_amount');

          amount.on('keydown', function(e){
            if (e.key === ',') {
              e.preventDefault();
            }
          });

          amount.on('input', function(){
            var normalized = normalizeAmountInput(jQuery(this).val());
            if (jQuery(this).val() !== normalized) {
              jQuery(this).val(normalized);
            }
          });

          amount.on('paste', function(){
            var inputRef = this;
            setTimeout(function(){
              var normalized = normalizeAmountInput(jQuery(inputRef).val());
              jQuery(inputRef).val(normalized);
            }, 0);
          });
        }

        // initialize on load
        jQuery(function(){
          bindAmountMask();
          updateCurrencyForProvider();
          jQuery('#gen_provider_id').on('change', function(){
            updateCurrencyForProvider();
          });
        });
      })();
    </script>
  </div>
</div>
