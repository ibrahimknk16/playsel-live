<?php
$provider = $provider ?? null;
$credits = $credits ?? [];
$debits = $debits ?? [];
$netBalances = $netBalances ?? [];
$debitLoadMap = $debitLoadMap ?? [];
?>
<div class="card mb-5 card-flush">
  <div class="card-header">
    <h3 class="card-title">Provider Detay: <?= htmlspecialchars($provider['epin_provider_name'] ?? '-') ?></h3>
    <div class="card-toolbar">
      <a href="<?= base_url('reports/providerbalances_create/' . (int)($provider['epin_provider_id'] ?? 0)) ?>" class="btn btn-sm btn-primary me-2">Yeni Hareket Ekle</a>
      <a href="<?= base_url('reports/providerbalances') ?>" class="btn btn-sm btn-secondary">Listeye Dön</a>
    </div>
  </div>
  <div class="card-body">
    <h5 class="mb-3">Aktif Bakiye Özeti</h5>
    <div class="table-responsive mb-6">
      <table class="table table-sm table-bordered align-middle table-row-dashed">
        <thead>
          <tr>
            <th>Para Birimi</th>
            <th>Net Bakiye</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($netBalances)): ?>
            <tr><td colspan="2" class="text-center text-muted">Kayıt yok</td></tr>
          <?php else: ?>
            <?php foreach ($netBalances as $row): ?>
              <?php $net = (float)($row['net_amount'] ?? 0); ?>
              <tr>
                <td><?= htmlspecialchars($row['currency'] ?? '') ?></td>
                <td class="<?= $net < 0 ? 'text-danger' : 'text-success' ?> fw-bold"><?= number_format($net, 2, ',', '.') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="row">
      <div class="col-12">
        <h5 class="mb-3">Yüklemeler</h5>
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle table-row-dashed">
            <thead>
              <tr>
                <th>#</th>
                <th>Para Birimi</th>
                <th>Tutar</th>
                <th>Not Detayı</th>
                <th>Tarih</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($credits)): ?>
                <tr><td colspan="5" class="text-center text-muted">Yükleme kaydı yok</td></tr>
              <?php else: ?>
                <?php foreach ($credits as $i => $row): ?>
                  <?php $amount = (float)($row['amount'] ?? 0); ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($row['currency'] ?? '') ?></td>
                    <td class="text-success fw-bold"><?= number_format($amount, 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['note'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['updated_at'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="col-12">
        <h5 class="mb-3">Harcamalar</h5>
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle table-row-dashed">
            <thead>
              <tr>
                <th>#</th>
                <th>Para Birimi</th>
                <th>Tutar</th>
                <th>ID</th>
                <th>Yükleme Kodu</th>
                <th>Havuz</th>
                <th>Yükleme Tarihi</th>
                <th>Adet</th>
                <th>Yükleyen</th>
                <th>Tedarikçi</th>
                <th>Yükleme Durumu</th>
                <th>Maliyet</th>
                <th>TRY Kuru</th>
                <th>TRY Maliyet</th>
                <th>Not</th>
                <th>Tarih</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($debits)): ?>
                <tr><td colspan="16" class="text-center text-muted">Harcama kaydı yok</td></tr>
              <?php else: ?>
                <?php foreach ($debits as $i => $row): ?>
                  <?php
                    $amount = abs((float)($row['amount'] ?? 0));
                    $note = (string)($row['note'] ?? '');
                    $linkedLoad = null;
                    if (preg_match('/epin_load_id\s*=\s*(\d+)/i', $note, $matches)) {
                      $linkedLoadId = (int)$matches[1];
                      $linkedLoad = $debitLoadMap[$linkedLoadId] ?? null;
                    }
                  ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($row['currency'] ?? '') ?></td>
                    <td class="text-danger fw-bold"><?= number_format($amount, 2, ',', '.') ?></td>
                    <?php
                      $statusVal = isset($linkedLoad['epin_load_status']) ? (int)$linkedLoad['epin_load_status'] : null;
                      $statusText = $statusVal === 1 ? 'Doğru Yükleme' : ($statusVal === 2 ? 'Hatalı Yükleme' : '-');
                    ?>
                    <td class="fw-semibold"><?= !empty($linkedLoad) ? (int)$linkedLoad['epin_load_id'] : '-' ?></td>
                    <td><?= htmlspecialchars($linkedLoad['epin_load_code'] ?? '-') ?></td>
                    <td><?= !empty($linkedLoad) ? htmlspecialchars($linkedLoad['epin_pool_title'] ?? ('#' . ((int)($linkedLoad['epin_load_pool_id'] ?? 0)))) : '-' ?></td>
                    <td><?= htmlspecialchars($linkedLoad['epin_load_date'] ?? '-') ?></td>
                    <td><?= !empty($linkedLoad) ? (int)($linkedLoad['epin_load_count'] ?? 0) : '-' ?></td>
                    <td><?= !empty($linkedLoad) ? htmlspecialchars($linkedLoad['epin_admin_name'] ?? '-') : '-' ?></td>
                    <td><?= !empty($linkedLoad) ? htmlspecialchars($linkedLoad['epin_provider_name'] ?? '-') : '-' ?></td>
                    <td><?= !empty($linkedLoad) ? ($statusText !== '-' ? $statusText : '-') : '-' ?></td>
                    <td>
                      <?php
                        $pc = $linkedLoad['epin_load_purchase_cost'] ?? null;
                        $pcurr = $linkedLoad['epin_load_purchase_currency'] ?? null;
                        if (!empty($linkedLoad) && is_numeric($pc)) {
                          $costStr = number_format((float)$pc, 2, ',', '.');
                          echo $pcurr ? ($costStr . ' ' . htmlspecialchars($pcurr)) : $costStr;
                        } else {
                          echo htmlspecialchars((string)($linkedLoad['epin_load_purchase_cost'] ?? '-'));
                        }
                      ?>
                    </td>
                    <td>
                      <?= !empty($linkedLoad) && is_numeric($linkedLoad['epin_load_purchase_rate'] ?? null)
                        ? number_format((float)$linkedLoad['epin_load_purchase_rate'], 4, ',', '.')
                        : htmlspecialchars((string)($linkedLoad['epin_load_purchase_rate'] ?? '-'))
                      ?>
                    </td>
                    <td>
                      <?= !empty($linkedLoad) && is_numeric($linkedLoad['epin_load_purchase_try_amount'] ?? null)
                        ? number_format((float)$linkedLoad['epin_load_purchase_try_amount'], 2, ',', '.')
                        : htmlspecialchars((string)($linkedLoad['epin_load_purchase_try_amount'] ?? '-'))
                      ?>
                    </td>
                    <td><?= !empty($linkedLoad) ? '-' : htmlspecialchars($note !== '' ? $note : '-') ?></td>
                    <td><?= htmlspecialchars($row['updated_at'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
