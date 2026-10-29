<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-payments" data-view="payments">
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-card"/></svg></span>
            <div class="hero-copy">
                <h1>Ödemelerim</h1>
                <p>Bakiye yükleme ve sipariş ödeme geçmişini incele.</p>
            </div>
        </header>

        <section class="subnav-shell">
          <div class="tab-list" role="tablist" aria-label="Ödeme durumları" data-filter-tabs="payments">
            <a href="?status=all" style="text-decoration:none;" class="tab-button <?= (empty($active_status) || $active_status == 'all') ? 'active' : '' ?>"><svg class="icon icon-fill"><use href="#i-filter"/></svg>Tümü</a>
            <a href="?status=pending" style="text-decoration:none;" class="tab-button <?= ($active_status == 'pending') ? 'active' : '' ?>"><svg class="icon"><use href="#i-clock"/></svg>Beklemede</a>
            <a href="?status=completed" style="text-decoration:none;" class="tab-button <?= ($active_status == 'completed') ? 'active' : '' ?>"><svg class="icon"><use href="#i-check"/></svg>Tamamlandı</a>
            <a href="?status=cancelled" style="text-decoration:none;" class="tab-button <?= ($active_status == 'cancelled') ? 'active' : '' ?>"><svg class="icon"><use href="#i-x"/></svg>İptal Edilenler</a>
            <a href="?status=rejected" style="text-decoration:none;" class="tab-button <?= ($active_status == 'rejected') ? 'active' : '' ?>"><svg class="icon"><use href="#i-x"/></svg>Sipariş İadesi</a>
          </div>
          <form action="<?= base_url($this->uri->segment(1) . '/profile/payments') ?>" method="GET" class="filter-row">
            <input type="hidden" name="status" value="<?= htmlspecialchars($active_status ?? 'all') ?>">
            <div class="date-control">
                <input type="text" id="flatpickr-date" name="date_range" placeholder="Tarih aralığı seçin" aria-label="Tarih aralığı seçin" value="<?= htmlspecialchars($this->input->get('date_range') ?? '') ?>">
                <span class="calendar"><svg class="icon"><use href="#i-calendar"/></svg></span>
                <button class="filter-submit" type="submit" aria-label="Tarih filtresini uygula"><svg class="icon"><use href="#i-filter"/></svg></button>
            </div>
            <a href="?status=<?= html_escape($active_status) ?>&date=1week" style="text-decoration:none;" class="quick-filter <?= ($this->input->get('date') == '1week') ? 'active' : '' ?>">Son 1 hafta</a>
            <a href="?status=<?= html_escape($active_status) ?>&date=1month" style="text-decoration:none;" class="quick-filter <?= ($this->input->get('date') == '1month') ? 'active' : '' ?>">Son 1 ay</a>
            <a href="?status=<?= html_escape($active_status) ?>&date=3months" style="text-decoration:none;" class="quick-filter <?= ($this->input->get('date') == '3months') ? 'active' : '' ?>">Son 3 ay</a>
            <a href="<?= base_url($this->uri->segment(1) . '/profile/payments') ?>" style="text-decoration:none;" class="icon-button danger" aria-label="Filtreleri temizle"><svg class="icon"><use href="#i-x"/></svg></a>
            <button class="icon-button success" type="button" aria-label="Yenile" onclick="window.location.reload();"><svg class="icon"><use href="#i-refresh"/></svg></button>
          </form>
          
          <?php
          $legacyStatusClass = function ($text) {
              $t = mb_strtolower((string)$text, 'UTF-8');
              if (preg_match('/tamam|başar|onay/u', $t)) { return ['done', 'check']; }
              if (preg_match('/hata|red|ret|iptal|iade|başarısız|başarisiz/u', $t)) { return ['refund', 'x']; }
              return ['payment-pending', 'pending'];
          };
          $mergedPayments = [];
          foreach (($payments ?? []) as $p) {
              $mergedPayments[] = ['kind' => 'current', 'ts' => (int)strtotime($p->user_balance_created_at), 'row' => $p];
          }
          foreach (($order_payments ?? []) as $p) {
              $mergedPayments[] = ['kind' => 'order', 'ts' => (int)strtotime($p->order_created_at), 'row' => $p];
          }
          foreach (($legacy_payments ?? []) as $p) {
              $mergedPayments[] = ['kind' => 'old_payment', 'ts' => $p->payment_date ? (int)strtotime($p->payment_date) : 0, 'row' => $p];
          }
          foreach (($legacy_cancellations ?? []) as $p) {
              $mergedPayments[] = ['kind' => 'old_refund', 'ts' => $p->refund_date ? (int)strtotime($p->refund_date) : 0, 'row' => $p];
          }
          usort($mergedPayments, function ($a, $b) { return $b['ts'] <=> $a['ts']; });
          ?>

          <div class="panel-body">
            <?php if (empty($mergedPayments)): ?>
            <div class="empty-state">
              <span class="empty-icon"><svg class="icon"><use href="#i-wallet"/></svg></span>
              <h3>Ödeme Bulunamadı</h3>
              <p>Seçilen tarih aralığında veya bu durumda herhangi bir ödeme bulunmuyor.</p>
              <a href="<?= base_url($this->uri->segment(1) . '/profile/payments') ?>" class="primary-button" style="text-decoration:none;"><svg class="icon"><use href="#i-filter"/></svg> Filtreleri Temizle</a>
            </div>
            <?php else: ?>
            <div class="table-wrap">
              <table class="data-table" id="payments-table">
                <thead><tr><th>Ödeme no</th><th>Yöntem</th><th>Tarih</th><th>Tutar</th><th>Durum</th></tr></thead>
                <tbody>
                  <?php foreach ($mergedPayments as $item): ?>
                  <?php if ($item['kind'] === 'order'):
                      $orderPayment = $item['row'];
                      $orderPaymentMethods = [2 => 'Cüzdan', 3 => 'Havale / EFT', 4 => 'Kredi Kartı', 5 => 'Mobil Ödeme'];
                      $orderPaymentStatuses = [
                          0 => 'İşleme Alındı',
                          1 => 'Tamamlandı',
                          2 => 'İptal Edildi',
                          3 => 'İşleme Alındı',
                          4 => 'Satıcı Onayladı',
                          5 => 'Satıcı Onaylamadı',
                          9 => 'İşlemde',
                          11 => 'Ödeme Bekleniyor',
                          12 => 'Ödeme Başarısız',
                          13 => 'Kontrol Ediliyor',
                          14 => 'Tedarikçi Bekleniyor',
                      ];
                      $orderStatus = (int)$orderPayment->order_status;
                      $orderStatusClass = $orderStatus === 11 || $orderStatus === 13
                          ? 'payment-pending'
                          : (in_array($orderStatus, [2, 5, 12], true) ? 'refund' : 'done');
                      $orderCurrency = $orderPayment->order_sale_price_currency ?: 'TRY';
                  ?>
                  <tr>
                    <td data-label="Ödeme no"><span class="row-id">Sipariş #<?= (int)$orderPayment->order_id ?></span></td>
                    <td data-label="Yöntem"><span class="method-cell"><span class="method-icon"><svg class="icon"><use href="#i-bag"/></svg></span><span><?= htmlspecialchars($orderPaymentMethods[(int)$orderPayment->order_payment_type] ?? 'Sipariş ödemesi') ?></span></span></td>
                    <td data-label="Tarih" class="subtle"><?= !empty($orderPayment->order_created_at) ? date('d.m.Y · H:i', strtotime($orderPayment->order_created_at)) : '-' ?></td>
                    <td data-label="Tutar" class="money"><?= number_format((float)$orderPayment->order_sale_price, 2, ',', '.') ?> <?= $orderCurrency === 'TRY' ? 'TL' : htmlspecialchars($orderCurrency) ?></td>
                    <td data-label="Durum"><span class="status <?= $orderStatusClass ?>"><?php if ($orderStatusClass === 'payment-pending'): ?><span class="status-mark pending" aria-hidden="true"></span><?php else: ?><span class="status-mark"><svg class="icon"><use href="#i-<?= $orderStatusClass === 'done' ? 'check' : 'x' ?>"/></svg></span><?php endif; ?><?= htmlspecialchars($orderPaymentStatuses[$orderStatus] ?? 'İşleniyor') ?></span></td>
                  </tr>
                  <?php continue; endif; ?>
                  <?php if ($item['kind'] !== 'current'):
                      $lp = $item['row'];
                      $isRefund = $item['kind'] === 'old_refund';
                      $lpText = trim((string)$lp->status_text);
                      $lpStatus = $isRefund ? ['refund', 'x'] : $legacyStatusClass($lpText);
                      $lpDate = $isRefund ? $lp->refund_date : $lp->payment_date;
                      $lpNo = $isRefund ? ($lp->source_payment_id ?: '-') : ($lp->source_payment_id ?: $lp->source_order_id);
                      $lpMethod = $isRefund ? 'Ödeme iadesi' : ($lp->method_text ?: '-');
                  ?>
                  <tr>
                    <td data-label="Ödeme no"><span class="row-id">#<?= htmlspecialchars($lpNo) ?></span></td>
                    <td data-label="Yöntem"><span class="method-cell"><span class="method-icon"><svg class="icon"><use href="#i-card"/></svg></span><span><?= htmlspecialchars($lpMethod) ?></span></span></td>
                    <td data-label="Tarih" class="subtle"><?= $lpDate ? date('d.m.Y · H:i', strtotime($lpDate)) : '-' ?></td>
                    <td data-label="Tutar" class="money"><?= number_format((float)$lp->amount, 2, ',', '.') ?> <?= (!$lp->currency || $lp->currency === 'TRY') ? 'TL' : htmlspecialchars($lp->currency) ?></td>
                    <td data-label="Durum"><span class="status <?= $lpStatus[0] ?>"><?php if ($lpStatus[1] === 'pending'): ?><span class="status-mark pending" aria-hidden="true"></span><?php else: ?><span class="status-mark"><svg class="icon"><use href="#i-<?= $lpStatus[1] ?>"/></svg></span><?php endif; ?><?= htmlspecialchars($isRefund ? ($lpText ?: 'İade Edildi') : ($lpText ?: 'Tamamlandı')) ?></span></td>
                  </tr>
                  <?php continue; endif; $payment = $item['row']; ?>
                  <tr>
                    <td data-label="Ödeme no"><span class="row-id">#<?= htmlspecialchars($payment->user_balance_id) ?></span></td>
                    <td data-label="Yöntem">
                        <span class="method-cell">
                            <?php 
                            if (!empty($payment->bank_name)) {
                                echo '<span class="method-icon"><svg class="icon"><use href="#i-bank"/></svg></span><span>' . htmlspecialchars($payment->bank_name);
                                if (!empty($payment->bank_owner)) echo '<br><small class="subtle">Alıcı: ' . htmlspecialchars($payment->bank_owner) . '</small>';
                                if (!empty($payment->bank_iban)) echo '<br><small class="subtle">' . htmlspecialchars($payment->bank_iban) . '</small>';
                                if (!empty($payment->user_balance_reference_no) && strpos((string)$payment->user_balance_reference_no, 'vomsis_') !== 0) echo '<br><small class="subtle">Ref: ' . htmlspecialchars($payment->user_balance_reference_no) . '</small>';
                                echo '</span>';
                            } elseif (!empty($payment->user_balance_order_id)) {
                                echo '<span class="method-icon"><svg class="icon"><use href="#i-bag"/></svg></span><span>Sipariş İade</span>';
                            } else {
                                echo '<span class="method-icon"><svg class="icon"><use href="#i-card"/></svg></span><span>Banka / Kredi Kartı</span>';
                            }
                            ?>
                        </span>
                    </td>
                    <td data-label="Tarih" class="subtle"><?= date('d.m.Y · H:i', strtotime($payment->user_balance_created_at)) ?></td>
                    <td data-label="Tutar" class="money"><?= number_format($payment->user_balance_amount, 2, ',', '.') ?> TL</td>
                    <td data-label="Durum">
                        <?php if ($payment->user_balance_status == 0): ?>
                            <span class="status payment-pending"><span class="status-mark pending" aria-hidden="true"></span>Beklemede</span>
                        <?php elseif ($payment->user_balance_status == 1): ?>
                            <span class="status done"><span class="status-mark"><svg class="icon"><use href="#i-check"/></svg></span>Tamamlandı</span>
                        <?php elseif ($payment->user_balance_status == 2): ?>
                            <span class="status refund"><span class="status-mark"><svg class="icon"><use href="#i-x"/></svg></span>İptal Edildi</span>
                        <?php elseif ($payment->user_balance_status == 4): ?>
                            <span class="status refund"><span class="status-mark"><svg class="icon"><use href="#i-x"/></svg></span>Sipariş İadesi</span>
                        <?php else: ?>
                            <span class="status payment-pending"><span class="status-mark pending" aria-hidden="true"></span>Bilinmiyor</span>
                        <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php if (!empty($pagination)): ?>
                <div class="profile-pagination-wrapper">
                    <?= $pagination ?>
                </div>
            <?php endif; ?>
            <?php endif; ?>
          </div>
        </section>
      </section>
    </section>
</main>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/tr.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dateInput = document.getElementById('flatpickr-date');

        if (dateInput && typeof flatpickr !== 'undefined') {
            flatpickr(dateInput, {
                mode: "range",
                locale: "tr",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d M Y",
                placeholder: "Tarih Aralığı Seçin",
                disableMobile: "true"
            });
        }
    });
</script>