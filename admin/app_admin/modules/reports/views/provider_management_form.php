<?php
$provider = $provider ?? null;
$currencies = $currencies ?? ['TRY', 'USD', 'EUR', 'AED', 'IDR'];
$isEdit = !empty($provider['epin_provider_id']);
$createdAtValue = '';
if (!empty($provider['epin_provider_created_at'])) {
    $ts = strtotime($provider['epin_provider_created_at']);
    $createdAtValue = $ts ? date('Y-m-d\TH:i', $ts) : '';
}
?>
<div class="card card-flush">
  <div class="card-header">
    <h3 class="card-title"><?= $isEdit ? 'Tedarikçi Düzenle' : 'Yeni Tedarikçi' ?></h3>
  </div>
  <div class="card-body">
    <form method="post" action="<?= $isEdit ? base_url('reports/provider_update/' . (int)$provider['epin_provider_id']) : base_url('reports/provider_store') ?>">
      <div class="row">
        <?php if ($isEdit): ?>
          <div class="col-md-3 mb-3">
            <label class="form-label">epin_provider_id</label>
            <input type="text" class="form-control form-control-solid" value="<?= (int)$provider['epin_provider_id'] ?>" disabled>
          </div>
        <?php endif; ?>
        <div class="col-md-<?= $isEdit ? '5' : '4' ?> mb-3">
          <label class="form-label">Tedarikçi Adı</label>
          <input type="text" name="epin_provider_name" class="form-control form-control-solid" value="<?= htmlspecialchars($provider['epin_provider_name'] ?? '') ?>" required>
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label">Para Birimi</label>
          <select name="epin_provider_currency" class="form-select form-select-solid" required>
            <?php foreach ($currencies as $currency): ?>
              <option value="<?= htmlspecialchars($currency) ?>" <?= (($provider['epin_provider_currency'] ?? '') === $currency) ? 'selected' : '' ?>><?= htmlspecialchars($currency) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="d-flex justify-content-end gap-2">
        <a href="<?= base_url('reports/providerbalances') ?>" class="btn btn-secondary">Listeye Dön</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Güncelle' : 'Kaydet' ?></button>
      </div>
    </form>
  </div>
</div>
