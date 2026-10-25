<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-order-detail" data-view="order-detail">
        <?php
        $statusText = trim((string)$order->status_text);
        $statusLower = mb_strtolower($statusText, 'UTF-8');
        if (preg_match('/tamam|teslim|onay/u', $statusLower)) { $ls = ['done', 'check']; }
        elseif (preg_match('/hata|red|ret|iptal|iade|başarısız|başarisiz/u', $statusLower)) { $ls = ['refund', 'x']; }
        else { $ls = ['payment-pending', 'pending']; }
        $orderNo = $order->source_order_no ?: $order->source_order_id;
        $cur = (!$order->currency || $order->currency === 'TRY') ? '₺' : $order->currency;
        ?>
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-bag"/></svg></span>
            <div class="hero-copy">
                <h1>Sipariş #<?= html_escape($orderNo) ?></h1>
                <p>Sipariş bilgilerini görüntüleyin.</p>
            </div>
            <div class="hero-action">
                <a href="<?= base_url($this->uri->segment(1) . '/profile/orders') ?>" class="secondary-button" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                    <svg class="icon" style="transform: rotate(180deg); width:14px; height:14px;"><use href="#i-arrow"/></svg>
                    Siparişlere dön
                </a>
            </div>
        </header>

        <div class="panel">
            <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:14px; border-bottom:1px solid var(--line-soft); margin-bottom:16px;">
                <div style="font-size:13px; color:#8f9ba6;">
                    Sipariş numarası <strong style="color:var(--accent); font-weight:700;">#<?= html_escape($orderNo) ?></strong>
                </div>
                <span class="status <?= $ls[0] ?>">
                    <?php if ($ls[1] === 'pending'): ?>
                        <span class="status-mark pending" aria-hidden="true"></span>
                    <?php else: ?>
                        <span class="status-mark"><svg class="icon"><use href="#i-<?= $ls[1] ?>"/></svg></span>
                    <?php endif; ?>
                    <?= html_escape($statusText ?: 'Tamamlandı') ?>
                </span>
            </div>

            <div class="detail-meta-grid">
                <div class="detail-meta">
                    <span>Toplam tutar</span>
                    <strong><?= number_format((float)$order->total_amount, 2, ',', '.') ?> <?= html_escape($cur) ?></strong>
                </div>
                <div class="detail-meta">
                    <span>Sipariş tarihi</span>
                    <strong><?= $order->order_date ? date('d.m.Y · H:i', strtotime($order->order_date)) : '-' ?></strong>
                </div>
                <div class="detail-meta">
                    <span>Ödeme yöntemi</span>
                    <strong><?= html_escape($order->payment_method ?: '-') ?></strong>
                </div>
            </div>

            <div class="delivery-panel" style="margin-top:16px;">
                <h3 class="delivery-heading">Ürünler</h3>
                <div class="delivery-rows">
                    <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $row): ?>
                            <div class="delivery-row">
                                <span class="delivery-label"><?= html_escape($row->product_name) ?><?= ($row->quantity !== null && (float)$row->quantity > 1) ? ' x' . (float)$row->quantity : '' ?></span>
                                <strong class="delivery-value"><?= $row->line_total !== null ? number_format((float)$row->line_total, 2, ',', '.') . ' ' . html_escape($cur) : '-' ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="delivery-row">
                            <span class="delivery-label"><?= html_escape($order->product_summary ?: 'Ürün') ?></span>
                            <strong class="delivery-value"><?= number_format((float)$order->total_amount, 2, ',', '.') ?> <?= html_escape($cur) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
      </section>
    </section>
</main>
