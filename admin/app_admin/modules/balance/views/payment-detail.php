<?php defined('BASEPATH') or exit('No direct script access allowed'); $paymentUserId = (int)($payment->user_balance_user_id ?? $payment->order_reseller_id ?? 0); ?>
<div class="card shadow-sm card-flush">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="text-muted fs-7 text-uppercase">Ödeme Detayı</div>
            <h2 class="card-title mb-0">#<?= html_escape((string)$payment->payment_transaction_id) ?></h2>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge badge-light-primary fs-6"><?= html_escape((string)$payment->payment_source) ?></span>
            <?php if (!empty($payment->payment_refundable)): ?>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#refundModal">İade başlat</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h3 class="modal-title">Ödeme iadesi başlat</h3><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form id="refundForm">
                    <div class="modal-body">
                        <p class="text-muted">Bu iade, <?= html_escape((string)$payment->payment_source) ?> #<?= (int)$payment->payment_transaction_id ?> için oluşturulacak.</p>
                        <label class="form-label required">İade tutarı</label>
                        <input name="amount" type="number" min="0.01" max="<?= (float)$payment->payment_amount ?>" step="0.01" class="form-control mb-4" required>
                        <label class="form-label">Neden</label>
                        <textarea name="reason" class="form-control" rows="3"></textarea>
                        <input type="hidden" name="source" value="<?= html_escape($payment_source_key) ?>">
                        <input type="hidden" name="payment_id" value="<?= (int)$payment_source_id ?>">
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button><button class="btn btn-danger">İadeyi beklemeye al</button></div>
                </form>
            </div>
        </div>
    </div>
    <script>
    $(function () {
        $('#refundForm').on('submit', function (e) {
            e.preventDefault();
            $.post('<?= base_url('balance/startRefund') ?>', $(this).serialize(), function (r) {
                alert(r.message);
                if (r.status === 'success') window.location.href = '<?= base_url('balance/refunds') ?>';
            }, 'json');
        });
    });
    </script>
    <div class="card-body">
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="card card-flush bg-light-primary h-100">
                    <div class="card-body">
                        <h4 class="mb-5">İşlem Bilgileri</h4>
                        <dl class="row mb-0">
                            <dt class="col-sm-5">İşlem No</dt><dd class="col-sm-7">#<?= html_escape((string)$payment->payment_transaction_id) ?></dd>
                            <dt class="col-sm-5">Sipariş No</dt><dd class="col-sm-7"><?= $payment->payment_order_id ? '#' . (int)$payment->payment_order_id : '-' ?></dd>
                            <dt class="col-sm-5">Tutar</dt><dd class="col-sm-7 fw-bold"><?= number_format((float)$payment->payment_amount, 2, ',', '.') ?> TL</dd>
                            <dt class="col-sm-5">Ödeme Tarihi</dt><dd class="col-sm-7"><?= html_escape((string)$payment->payment_created_at) ?></dd>
                            <dt class="col-sm-5">Durum</dt><dd class="col-sm-7"><?= html_escape((string)$payment->payment_status) ?></dd>
                            <dt class="col-sm-5">Referans</dt><dd class="col-sm-7"><?= html_escape((string)$payment->payment_reference) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card card-flush bg-light h-100">
                    <div class="card-body">
                        <h4 class="mb-5">Müşteri ve Güvenlik</h4>
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Müşteri</dt><dd class="col-sm-7"><a href="<?= base_url('users/detail/' . $paymentUserId) ?>"><?= html_escape((string)$payment->user_name) ?></a></dd>
                            <dt class="col-sm-5">E-posta</dt><dd class="col-sm-7"><?= html_escape((string)($payment->user_email ?? '-')) ?></dd>
                            <dt class="col-sm-5">IP Adresi</dt><dd class="col-sm-7"><?= html_escape((string)$payment->payment_ip) ?></dd>
                            <dt class="col-sm-5">Kart</dt><dd class="col-sm-7"><?= html_escape((string)$payment->payment_card) ?></dd>
                            <dt class="col-sm-5">Kart Sahibi</dt><dd class="col-sm-7"><?= html_escape((string)$payment->payment_cardholder) ?></dd>
                            <dt class="col-sm-5">Geçerlilik</dt><dd class="col-sm-7"><?= html_escape((string)($payment->payment_card_expiry ?? '-')) ?></dd>
                            <dt class="col-sm-5">Gönderen</dt><dd class="col-sm-7"><?= html_escape((string)$payment->payment_sender) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card card-flush bg-light-warning">
                    <div class="card-body">
                        <h4 class="mb-3">Not / Açıklama</h4>
                        <p class="mb-0"><?= nl2br(html_escape((string)$payment->payment_note)) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>