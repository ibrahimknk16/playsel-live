<div class="card">
    <div class="card-header"><h3 class="card-title">İade Bekleyen Ödemeler</h3></div>
    <div class="card-body">
        <?php if (!empty($refunds_setup_missing)): ?>
            <div class="alert alert-warning">
                <h5 class="mb-2">İade modülü henüz etkinleştirilmemiş</h5>
                <p class="mb-0">
                    Production veritabanında <code>payment_refunds</code> tablosu bulunamadı.
                    Uygulama paketindeki
                    <code>migration/operations_requests.sql</code> dosyasını phpMyAdmin'de
                    doğru veritabanını seçerek bir kez çalıştırın.
                </p>
            </div>
        <?php else: ?>
        <div class="alert alert-info mb-8">Yeni iadeler ödeme detayından başlatılır. Listeden ilgili ödemeye veya müşteriye geri dönebilirsiniz.</div>
        <div class="table-responsive"><table class="table align-middle">
            <thead><tr><th>ID</th><th>Müşteri</th><th>Kaynak ödeme</th><th>Ödeme tutarı</th><th>İade tutarı</th><th>Durum</th><th></th></tr></thead>
            <tbody><?php foreach ($refunds as $refund): ?><tr>
                <td><?= (int)$refund->refund_id ?></td>
                <td><?= html_escape(trim($refund->user_name . ' ' . $refund->user_surname)) ?><br><small><?= html_escape($refund->user_email) ?></small></td>
                <td>
                    <?php if ($refund->refund_balance_id): ?>
                        <a href="<?= base_url('balance/paymentDetail/balance/' . (int)$refund->refund_balance_id) ?>">#<?= (int)$refund->refund_balance_id ?></a>
                    <?php elseif ($refund->refund_order_id): ?>
                        <a href="<?= base_url('balance/paymentDetail/order/' . (int)$refund->refund_order_id) ?>">Sipariş #<?= (int)$refund->refund_order_id ?></a>
                    <?php else: ?>-<?php endif; ?>
                </td>
                <td><?= number_format((float)($refund->payment_amount ?: $refund->order_sale_price), 2, ',', '.') ?> TL</td>
                <td><?= number_format((float)$refund->refund_amount, 2, ',', '.') ?> TL</td>
                <td><?= ['pending' => 'İade bekliyor', 'completed' => 'Tamamlandı'][$refund->refund_status] ?? html_escape($refund->refund_status) ?></td>
                <td><?php if ($refund->refund_status === 'pending'): ?><button class="btn btn-sm btn-success js-complete-refund" data-id="<?= (int)$refund->refund_id ?>">İadeyi tamamla</button><?php endif; ?></td>
            </tr><?php endforeach; ?></tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
<script>
$(function () {
    $('.js-complete-refund').on('click', function () { $.post('<?= base_url('balance/completeRefund') ?>', {refund_id: $(this).data('id')}, function (r) { alert(r.message); if (r.status === 'success') location.reload(); }, 'json'); });
});
</script>
