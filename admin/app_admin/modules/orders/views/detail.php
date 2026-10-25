<style>
    hr {
        width: 90%;
        margin: 0 auto !important;
        border: 1px solid #ccc;
        /* görünürlük için */
    }

    .scrollable-voucher {
        max-height: 250px;
        overflow-y: auto;
    }

    .card-header {
        min-height: 50px !important;
    }
</style>

<div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Sipariş Detay #<?= (int)$order->order_id ?>
                </h1>
            </div>

        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <div class="d-flex flex-column gap-7 gap-lg-10">
                <div class="d-flex flex-column flex-xl-row gap-7 gap-lg-10">
                    <div class="card card-flush py-4 flex-row-fluid">
                        <div class="card-body pt-0">
                            <div class="row">
                                <div class="col-1">
                                    <img src="<?= html_escape($order->product_image) ?>" class="w-100" alt="Ürün">
                                </div>
                                <div class="col-11">
                                    <h2><?= html_escape($order->category_title) ?> - <?= html_escape($order->product_name) ?> </h2>
                                    <h3 class="py-2">Durum: <?= $order->order_status_span ?></h3>
                                    <?php if (!empty($order->order_require_data)) : ?>
                                        <?php $keyArray = ['user_id' => 'User ID', 'server_id' => 'Server ID', 'zone_id' => 'Zone ID']; ?>
                                        <?php foreach ($order->order_require_data as $key => $value): ?>
                                            <div class="py-2">
                                                <?php $label = isset($keyArray[$key]) ? $keyArray[$key] : $key; ?>
                                                <?php if (is_array($value) || is_object($value)): ?>
                                                    <span class="text-dark"><?= html_escape(json_encode($value, JSON_UNESCAPED_UNICODE)) ?></span>
                                                <?php else: ?>
                                                    <span class="text-dark"><strong><?= html_escape($label) ?></strong> - <?= html_escape((string)$value) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <div class="row mt-3 mb-2">
                                        <div class="col-4"><strong>Sipariş Oluşturulma Tarihi:</strong></div>
                                        <div class="col-8"><?= html_escape((string)$order->order_created_at) ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Sipariş Teslim Tarihi:</strong></div>
                                        <div class="col-8"><?= html_escape((string)$order->order_sended_at) ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Adet:</strong></div>
                                        <div class="col-2"><?= (int)$order->order_quantity ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Sipariş Notu:</strong></div>
                                        <div class="col-8"><?= html_escape((string)$order->order_data) ?></div>
                                    </div>
                                    <?php
                                    $paymentInfo = is_array($order->order_payment_info ?? null) ? $order->order_payment_info : [];
                                    $cardBin = $paymentInfo['cardBin'] ?? ($paymentInfo['binNumber'] ?? '');
                                    $cardLast4 = $paymentInfo['cardLast4'] ?? ($paymentInfo['lastFourDigits'] ?? '');
                                    ?>
                                    <?php if ($cardBin !== '' || $cardLast4 !== '' || !empty($paymentInfo['cardHolderName'])) : ?>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Kart Bilgileri:</strong></div>
                                            <div class="col-8">
                                                <?= html_escape(($cardBin ?: '******') . ' **** ' . ($cardLast4 ?: '****')) ?><br>
                                                <?= html_escape((string)($paymentInfo['cardHolderName'] ?? '-')) ?><br>
                                                Geçerlilik: <?= html_escape((string)($paymentInfo['cardExpiry'] ?? '-')) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($order->pricing_snapshot)) : ?>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Fiyat Snapshot:</strong></div>
                                            <div class="col-8">
                                                Liste: <?= nFormat((float)($order->pricing_snapshot['list_price'] ?? 0)) ?>
                                                <?= html_escape((string)($order->pricing_snapshot['currency'] ?? '')) ?><br>
                                                Bundle satış: <?= nFormat((float)($order->pricing_snapshot['bundle_sale_price'] ?? 0)) ?>
                                                <?= html_escape((string)($order->pricing_snapshot['currency'] ?? '')) ?><br>
                                                Bundle indirimi: <?= nFormat((float)($order->pricing_snapshot['bundle_discount'] ?? 0)) ?>
                                                <?= html_escape((string)($order->pricing_snapshot['currency'] ?? '')) ?><br>
                                                Kupon indirimi: <?= nFormat((float)($order->pricing_snapshot['coupon_discount'] ?? 0)) ?>
                                                <?= html_escape((string)($order->pricing_snapshot['currency'] ?? '')) ?><br>
                                                Net satış: <?= nFormat((float)($order->pricing_snapshot['final_sale_price'] ?? 0)) ?>
                                                <?= html_escape((string)($order->pricing_snapshot['currency'] ?? '')) ?><br>
                                                Maliyet: <?= nFormat((float)($order->pricing_snapshot['cost'] ?? 0)) ?>
                                                <?= html_escape((string)($order->pricing_snapshot['cost_currency'] ?? '')) ?>
                                                (<?= nFormat((float)($order->pricing_snapshot['cost_tl'] ?? 0)) ?> TL)
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Transaction ID:</strong></div>
                                        <div class="col-8"><?= html_escape((string)$order->order_supplier_order_id) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($order->voucherData)) : ?>
                    <div class="tab-pane fade show active" id="kt_ecommerce_sales_order_summary" role="tab-panel">
                        <div class="d-flex flex-column gap-7 gap-lg-10">
                            <div class="d-flex flex-column flex-xl-row gap-7 gap-lg-10">
                                <div class="card card-flush py-4 flex-row-fluid position-relative">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Epin Kodları</h2>
                                            <button class="btn btn-sm btn-info ms-3" onclick="copyAll()">Tümünü Kopyala</button>

                                        </div>
                                    </div>

                                    <hr>

                                    <div class="card-body pt-5 scrollable-voucher">
                                        <?php
                                        $allVouchers = '';
                                        if (!empty($order->voucherData) && is_array($order->voucherData)) {
                                            $allVouchers = implode("\n", array_map('trim', $order->voucherData));
                                        }
                                        ?>

                                        <textarea id="allVouchers" style="display:none;">
                                            <?= html_escape($allVouchers); ?>
                                        </textarea>

                                        <?php foreach ($order->voucherData as $epin) : ?>
                                            <?php $trimmedPin = trim($epin); ?>
                                            <?= html_escape($trimmedPin) ?>
                                            <i class="fa-solid fa-copy fs-12 copy-value my-2" onClick='copy(<?= json_encode($trimmedPin, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Kopyala"></i>
                                            <br>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <!-- <div class="rounded border d-flex flex-column p-10">
                                        <label for="" class="form-label">Tüm Kodlar</label>
                                        <textarea class="form-control form-control form-control-solid" data-kt-autosize="true"><?= implode(',', array_map('trim', $order->voucherData)) ?></textarea>
                                    </div> -->
                            </div>
                        </div>
                    </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>
</div>

<script>
    function fallbackCopy(text) {
        var tempInput = document.createElement('input');
        tempInput.value = text;
        tempInput.style.position = 'fixed';
        tempInput.style.left = '-9999px';
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.setSelectionRange(0, text.length);
        try {
            document.execCommand('copy');
            toastr.success('Kopyalandı: ' + text);
        } catch (err) {
            toastr.error('Kopyalama başarısız!');
            console.error(err);
        }
        document.body.removeChild(tempInput);
    }

    function copy(val) {
        var text = String(val || '').trim();
        if (!text) {
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                toastr.success('Kopyalandı: ' + text);
            }).catch(function() {
                fallbackCopy(text);
            });
            return;
        }

        fallbackCopy(text);
    }

    function copyAll() {
        const textArea = document.getElementById('allVouchers');
        navigator.clipboard.writeText(textArea.value.trim()).then(function() {
            toastr.success('Tüm kodlar kopyalandı.');
        }, function(err) {
            toastr.error('Kopyalama başarısız!', err);

        });
    }
</script>