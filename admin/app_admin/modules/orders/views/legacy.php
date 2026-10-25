<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row g-6">
    <div class="col-12">
        <div class="card shadow-sm card-flush">
            <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div>
                    <h4 class="card-title mb-1">Legacy Geçmiş</h4>
                    <div class="text-muted fs-7">Eski sistem kayıtları yeni ürün ve sipariş tablolarından bağımsızdır.</div>
                </div>
                <form method="get" action="<?= base_url('orders/legacy') ?>" class="d-flex gap-2">
                    <input class="form-control form-control-sm" name="search" value="<?= html_escape($legacy_search) ?>" placeholder="Sipariş, ödeme, e-posta, ürün">
                    <select class="form-select form-select-sm" name="limit">
                        <?php foreach ([50, 100, 200] as $limit): ?>
                            <option value="<?= $limit ?>" <?= $legacy_limit === $limit ? 'selected' : '' ?>><?= $limit ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary btn-sm" type="submit">Filtrele</button>
                </form>
            </div>
            <div class="card-body">
                <?php if ($legacy_error): ?>
                    <div class="alert alert-warning"><?= html_escape($legacy_error) ?></div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Legacy kayıtlar salt-okunurdur. Eski ürün adları ve kaynak durumları değiştirilmeden gösterilir.
                    </div>

                    <h5 class="mt-6">Eski siparişler (<?= count($legacy_orders) ?>)</h5>
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle">
                            <thead><tr><th>Sipariş</th><th>Kullanıcı</th><th>Eski ürün</th><th>Tutar</th><th>Ödeme</th><th>IP</th><th>Tarih</th><th>Durum</th></tr></thead>
                            <tbody>
                            <?php foreach ($legacy_orders as $order): ?>
                                <?php $rows = array_filter($legacy_order_rows, function ($row) use ($order) { return $row->legacy_order_key === $order->legacy_order_key; }); ?>
                                <tr>
                                    <td><code><?= html_escape($order->source_order_no ?: $order->source_order_id) ?></code><br><small><?= html_escape($order->source_order_id) ?></small></td>
                                    <td><?= html_escape($order->user_email ?: $order->source_email ?: 'Eşleşmemiş kullanıcı') ?><br><small>Kaynak ID: <?= html_escape($order->source_customer_id) ?></small></td>
                                    <td><?php foreach ($rows as $row): ?><div><?= html_escape($row->product_name) ?> <small>x<?= html_escape($row->quantity) ?></small></div><?php endforeach; ?></td>
                                    <td><?= number_format((float)$order->total_amount, 2, ',', '.') ?> <?= html_escape($order->currency ?: 'TRY') ?></td>
                                    <td><?= html_escape($order->payment_method ?: '-') ?></td>
                                    <td><?= html_escape(isset($order->order_ip) && $order->order_ip !== '' ? $order->order_ip : '-') ?></td>
                                    <td><?= html_escape($order->order_date ?: '-') ?></td>
                                    <td><span class="badge badge-light-warning"><?= html_escape($order->status_text ?: 'Legacy') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-8">Eski ödemeler (<?= count($legacy_payments) ?>)</h5>
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle">
                            <thead><tr><th>Ödeme ID</th><th>Sipariş</th><th>Yöntem / Kart</th><th>Kart sahibi</th><th>IP</th><th>Tutar</th><th>Net</th><th>Tarih</th><th>Durum</th></tr></thead>
                            <tbody>
                            <?php foreach ($legacy_payments as $payment): ?>
                                <tr>
                                    <td><code><?= html_escape($payment->source_payment_id ?: '-') ?></code></td>
                                    <td><?= html_escape($payment->source_order_id ?: $payment->source_order_no ?: '-') ?></td>
                                    <td><?= html_escape($payment->method_text ?: '-') ?><br><small><?= html_escape($payment->masked_card ?: '') ?></small><br><small><?= html_escape($payment->purpose_text ?: '') ?></small></td>
                                    <td><?= html_escape(isset($payment->cardholder_name) && $payment->cardholder_name !== '' ? $payment->cardholder_name : '-') ?></td>
                                    <td><?= html_escape(isset($payment->payment_ip) && $payment->payment_ip !== '' ? $payment->payment_ip : '-') ?></td>
                                    <td><?= number_format((float)$payment->amount, 2, ',', '.') ?> <?= html_escape($payment->currency ?: 'TRY') ?></td>
                                    <td><?= number_format((float)$payment->net_amount, 2, ',', '.') ?></td>
                                    <td><?= html_escape($payment->payment_date ?: '-') ?></td>
                                    <td><span class="badge badge-light-info"><?= html_escape($payment->status_text ?: 'Legacy') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-8">Eski iadeler (<?= count($legacy_cancellations) ?>)</h5>
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle">
                            <thead><tr><th>İade ID</th><th>Sipariş</th><th>Tutar</th><th>Tarih</th><th>Açıklama</th><th>Admin</th></tr></thead>
                            <tbody>
                            <?php foreach ($legacy_cancellations as $refund): ?>
                                <tr>
                                    <td><code><?= html_escape($refund->source_refund_id ?: '-') ?></code></td>
                                    <td><?= html_escape($refund->source_order_no ?: $refund->source_order_id ?: '-') ?></td>
                                    <td><?= number_format((float)$refund->amount, 2, ',', '.') ?> <?= html_escape($refund->currency ?: 'TRY') ?></td>
                                    <td><?= html_escape($refund->refund_date ?: '-') ?></td>
                                    <td><?= html_escape($refund->description ?: '-') ?></td>
                                    <td><?= html_escape($refund->admin_text ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
