<?php
$mergedOrders = [];
foreach (($orders ?? []) as $o) {
    $mergedOrders[] = ['legacy' => false, 'ts' => (int)strtotime($o->order_created_at), 'row' => $o];
}
foreach (($legacy_orders ?? []) as $l) {
    $mergedOrders[] = ['legacy' => true, 'ts' => $l->order_date ? (int)strtotime($l->order_date) : 0, 'row' => $l];
}
usort($mergedOrders, function ($a, $b) { return $b['ts'] <=> $a['ts']; });
?>
<?php if (empty($mergedOrders)): ?>
<div class="empty-state">
  <span class="empty-icon"><svg class="icon"><use href="#i-bag"/></svg></span>
  <h3>Sipariş Bulunamadı</h3>
  <p>Seçilen tarih aralığında veya bu durumda herhangi bir sipariş bulunmuyor.</p>
  <button type="button" class="primary-button clear-filters-btn"><svg class="icon"><use href="#i-filter"/></svg> Filtreleri Temizle</button>
</div>
<?php else: ?>
<div class="table-wrap">
  <table class="data-table" id="orders-table">
    <thead><tr><th>Sipariş no</th><th>Ürün</th><th>Adet</th><th>Tutar</th><th>Tarih</th><th>Durum</th><th>İşlem</th></tr></thead>
    <tbody style="margin-top:12px">
      <?php foreach ($mergedOrders as $item): ?>
      <?php if ($item['legacy']):
          $legacy = $item['row'];
          $legacyRows = array_filter($legacy_order_rows ?? [], function ($row) use ($legacy) {
              return $row->legacy_order_key === $legacy->legacy_order_key;
          });
          $legacyProducts = array_map(function ($row) {
              return $row->product_name . (($row->quantity !== null && (float)$row->quantity > 1) ? ' x' . $row->quantity : '');
          }, $legacyRows);
          $legacyText = trim((string)$legacy->status_text);
          $legacyLower = mb_strtolower($legacyText, 'UTF-8');
          if (preg_match('/tamam|teslim|onay/u', $legacyLower)) { $ls = ['done', 'check']; }
          elseif (preg_match('/hata|red|ret|iptal|iade|başarısız|başarisiz/u', $legacyLower)) { $ls = ['refund', 'x']; }
          else { $ls = ['payment-pending', 'pending']; }
      ?>
      <tr>
        <td data-label="Sipariş no"><span class="row-id">#<?= html_escape($legacy->source_order_no ?: $legacy->source_order_id) ?></span></td>
        <td data-label="Ürün"><span class="product-cell"><span class="product-thumb"><svg class="icon"><use href="#i-bag"/></svg></span><span class="product-name"><?= html_escape(implode(', ', $legacyProducts) ?: ($legacy->product_summary ?: 'Ürün')) ?></span></span></td>
        <td data-label="Adet"><span class="quantity"><?= count($legacyRows) > 1 ? count($legacyRows) . ' parça' : '1 adet' ?></span></td>
        <td data-label="Tutar" class="money"><?= number_format((float)$legacy->total_amount, 2, ',', '.') ?> <?= (!$legacy->currency || $legacy->currency === 'TRY') ? 'TL' : html_escape($legacy->currency) ?></td>
        <td data-label="Tarih" class="subtle"><?= $legacy->order_date ? date('d.m.Y · H:i', strtotime($legacy->order_date)) : '-' ?></td>
        <td data-label="Durum"><span class="status <?= $ls[0] ?>"><?php if ($ls[1] === 'pending'): ?><span class="status-mark pending" aria-hidden="true"></span><?php else: ?><span class="status-mark"><svg class="icon"><use href="#i-<?= $ls[1] ?>"/></svg></span><?php endif; ?><?= html_escape($legacyText ?: 'Tamamlandı') ?></span></td>
        <td data-label="İşlem"><div style="display:flex;gap:5px;align-items:center;"><a href="<?= base_url($this->uri->segment(1) . '/profile/order-detail/' . rawurlencode($legacy->legacy_order_key)) ?>" class="detail-button" style="text-decoration:none;"><svg class="icon"><use href="#i-eye"/></svg>Detay</a></div></td>
      </tr>
      <?php continue; endif; $order = $item['row']; ?>
      <tr>
        <td data-label="Sipariş no"><span class="row-id">#<?= $order->order_id ?></span><?php if (($order->bundle_count ?? 1) > 1 && empty($order->is_quantity_group)): ?><small style="display:block;color:var(--profile-gold);"><?= (int)$order->bundle_count ?> parçalı sipariş</small><?php endif; ?></td>
        <td data-label="Ürün">
            <span class="product-cell">
                <?php if(!empty($order->product_icon)): ?>
                    <span class="product-thumb"><img class="order-art" src="<?= getenv('CDN_URL') . $order->product_icon ?>" alt=""></span>
                <?php else: ?>
                    <span class="product-thumb"><svg class="icon"><use href="#i-bag"/></svg></span>
                <?php endif; ?>
                <span class="product-name"><?= html_escape($order->bundle_product_name ?? $order->product_name) ?><?php if (($order->bundle_count ?? 1) > 1 && empty($order->is_quantity_group)): ?><small style="display:block;color:var(--text-muted);"><?= (int)$order->bundle_count ?> ürün birlikte</small><?php endif; ?></span>
            </span>
        </td>
        <td data-label="Adet"><span class="quantity"><?= ($order->bundle_count ?? 1) > 1 ? (int)$order->bundle_count . (!empty($order->is_quantity_group) ? ' adet' : ' parça') : (int)$order->order_quantity . ' adet' ?></span></td>
        <td data-label="Tutar" class="money"><?= number_format(($order->bundle_count ?? 1) > 1 ? $order->bundle_total : $order->order_sale_price, 2, ',', '.') ?> <?= $order->order_sale_price_currency === 'TRY' ? 'TL' : html_escape($order->order_sale_price_currency) ?></td>
        <td data-label="Tarih" class="subtle"><?= date('d.m.Y · H:i', strtotime($order->order_created_at)) ?></td>
        <td data-label="Durum">
            <?php
            $statusMap = [
                0 => ['class' => 'payment-pending', 'text' => 'Beklemede', 'icon' => 'pending'],
                1 => ['class' => 'done', 'text' => 'Tamamlandı', 'icon' => 'check'],
                2 => ['class' => 'refund', 'text' => 'İptal Edildi', 'icon' => 'x'],
                100 => ['class' => 'processing', 'text' => 'Kısmen İptal Edildi', 'icon' => 'processing'],
                102 => ['class' => 'processing', 'text' => 'Kısmen Teslim Edildi', 'icon' => 'processing'],
                3 => ['class' => 'processing', 'text' => 'İşleme Alındı', 'icon' => 'processing'],
                4 => ['class' => 'processing', 'text' => 'Satıcı Onayladı', 'icon' => 'processing'],
                5 => ['class' => 'refund', 'text' => 'Satıcı Onaylamadı', 'icon' => 'x'],
                9 => ['class' => 'processing', 'text' => 'Cron İşlemi', 'icon' => 'processing'],
                11 => ['class' => 'payment-pending', 'text' => 'Ödeme Bekleniyor', 'icon' => 'pending'],
                12 => ['class' => 'refund', 'text' => 'Ödeme Başarısız', 'icon' => 'x'],
                13 => ['class' => 'processing', 'text' => 'İşleniyor', 'icon' => 'processing'],
                14 => ['class' => 'processing', 'text' => 'Tedarikçi Değiştir', 'icon' => 'processing'],
                15 => ['class' => 'processing', 'text' => 'Yükleme Bekliyor', 'icon' => 'processing'],
            ];
            $s = $statusMap[$order->order_status] ?? ['class' => 'processing', 'text' => 'İşleniyor', 'icon' => 'processing'];
            ?>
            <span class="status <?= $s['class'] ?>">
                <?php if($s['icon'] == 'pending' || $s['icon'] == 'processing'): ?>
                    <span class="status-mark <?= $s['icon'] ?>" aria-hidden="true"></span>
                <?php else: ?>
                    <span class="status-mark"><svg class="icon"><use href="#i-<?= $s['icon'] ?>"/></svg></span>
                <?php endif; ?>
                <?= $s['text'] ?>
            </span>
        </td>
        <td data-label="İşlem">
            <div style="display:flex;gap:5px;align-items:center;">
                <a href="<?= base_url($this->uri->segment(1) . '/profile/order-detail/' . $order->order_id) ?>" class="detail-button" style="text-decoration:none;"><svg class="icon"><use href="#i-eye"/></svg>Detay</a>
                <?php if ($order->order_status == 1): ?>
                    <?php if (isset($reviewed_orders) && in_array($order->order_id, $reviewed_orders)): ?>
                        <button type="button" class="detail-button" style="opacity:0.6; cursor:not-allowed;" disabled><svg class="icon"><use href="#i-star"/></svg> Değerlendirildi</button>
                    <?php else: ?>
                        <button type="button" class="primary-button review-btn" data-id="<?= $order->order_id ?>" style="min-height:30px;font-size:11px;padding:0 10px;display:inline-flex;align-items:center;gap:4px;"><svg class="icon"><use href="#i-star"/></svg> Değerlendir</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
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
