<?php
$providers = $providers ?? [];
$entry = $entry ?? null;
$currencies = $currencies ?? ['TRY','USD','EUR','AED','IDR'];

?>
<div class="card card-flush">
  <div class="card-header">
    <h3 class="card-title"><?= $entry ? 'Hareket Detayı' : 'Yeni Hareket' ?></h3>
  </div>
  <div class="card-body">

    <form>
      <div class="mb-3">
        <label class="form-label">Tedarikçi</label>
        <select class="form-select form-select-solid" disabled>
          <?php foreach ($providers as $p): ?>
            <option value="<?= $p['epin_provider_id'] ?>" <?= $entry && (($entry['provider_balance_provider_id'] ?? $entry['provider_id'] ?? null) == $p['epin_provider_id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['epin_provider_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Para Birimi</label>
          <select class="form-select form-select-solid" disabled>
            <?php foreach ($currencies as $c): ?>
                <option <?= $entry && (($entry['provider_balance_currency'] ?? $entry['currency'] ?? '')==$c) ? 'selected' : '' ?>><?= $c ?></option>
              <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Bakiye</label>
            <input type="text" class="form-control form-control-solid" value="<?= $entry['provider_balance_amount'] ?? $entry['balance_amount'] ?? '' ?>" disabled />
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Güncellendi</label>
            <input type="text" class="form-control form-control-solid" value="<?= $entry['provider_balance_updated_at'] ?? $entry['updated_at'] ?? '' ?>" disabled />
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Not</label>
        <textarea class="form-control form-control-solid" rows="3" disabled><?= htmlspecialchars($entry['provider_balance_note'] ?? $entry['note'] ?? '') ?></textarea>
      </div>

      <?php if (!empty($entry_admin)): ?>
        <div class="mb-3">
          <label class="form-label">İşlemi Yapan Admin</label>
          <input type="text" class="form-control form-control-solid" value="<?= htmlspecialchars(($entry_admin['admin_name'] ?? '') . ' <' . ($entry_admin['admin_email'] ?? '') . '>') ?>" disabled />
        </div>
      <?php endif; ?>


      <div class="d-flex justify-content-end">
        <a href="<?= base_url('reports/providerbalances') ?>" class="btn btn-secondary me-2">Geri</a>
          <button type="button" class="btn btn-primary" disabled>Uygula</button>
      </div>
    </form>
      <hr />
      <div class="d-flex justify-content-end">
        <a href="<?= base_url('reports/providerbalances_create') ?>" class="btn btn-sm btn-primary">Yeni Hareket Ekle</a>
      </div>
  </div>
</div>
