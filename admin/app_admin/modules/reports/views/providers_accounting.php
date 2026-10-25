    <?php
$providers = $providers ?? [];
$providerSummaries = $providerSummaries ?? [];

?>
<div >
<div class="card card-flush">
  <div class="card-header">
    <h3 class="card-title">Tedarikçi Yönetimi</h3>
      <div class="card-toolbar gap-2">
      <a href="<?= base_url('reports/provider_create') ?>" class="btn btn-sm btn-light-primary">Yeni Tedarikçi</a>
      <a href="<?= base_url('reports/providerbalances_create') ?>" class="btn btn-sm btn-primary">Yeni Hareket Ekle</a>
      </div>
  </div>
  <div class="card-body">
    <p class="text-muted">Tedarikçileri, temel bilgilerini ve aktif bakiyelerini bu ekrandan görebilir; detay, ekleme ve düzenleme işlemlerini yapabilirsiniz.</p>

    <table class="table table-striped table-bordered align-middle table-row-dashed">
      <thead>
        <tr>
          <th>#</th>
          <th>Tedarikçi</th>
          <th>Aktif Bakiyeler</th>
          <th>Son Hareket</th>
          <th>İşlem</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($providerSummaries)): ?>
          <tr><td colspan="5" class="text-center">Tedarikçi bulunamadı.</td></tr>
        <?php else: ?>
          <?php foreach ($providerSummaries as $i => $summary): ?>
            <tr>
              <td><?= (int)($summary['provider_id'] ?? 0) ?></td>
              <td>
                <div class="fw-semibold"><?= anchor(base_url('reports/providerbalances_provider/' . (int)($summary['provider_id'] ?? 0)), htmlspecialchars($summary['provider_name'] ?? 'Unknown')) ?></div>
                <div class="text-muted small">Para Birimi: <?= htmlspecialchars($summary['provider_currency'] ?? '-') ?></div>
              </td>
              <td>
                <?php if (empty($summary['currencies'])): ?>
                  <span class="text-muted">Kayıt yok</span>
                <?php else: ?>
                  <?php foreach ($summary['currencies'] as $currencyRow): ?>
                    <?php $net = (float)($currencyRow['net_amount'] ?? 0); ?>
                    <div>
                      <span class="fw-semibold"><?= htmlspecialchars($currencyRow['currency'] ?? '') ?>:</span>
                      <span class="<?= $net < 0 ? 'text-danger' : 'text-success' ?> fw-bold"><?= number_format($net, 2, ',', '.') ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($summary['last_updated'] ?? '-') ?></td>
              <td>
                <a href="<?= base_url('reports/providerbalances_provider/' . (int)($summary['provider_id'] ?? 0)) ?>" class="btn btn-sm btn-success">Detay</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
