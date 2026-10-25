<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .order-invoice-page {
        background: radial-gradient(circle at top right, #eef6ff 0%, #f7f9fc 45%, #f4f5f7 100%);
    }

    .order-invoice-page .receipt-wrapper {
        max-width: 720px;
        margin: 0 auto;
    }

    .order-invoice-page .receipt-paper {
        background: #fff;
        border: 1px solid #dfe3ea;
        border-radius: 1rem;
        box-shadow: 0 0.9rem 2.5rem rgba(24, 39, 75, 0.08);
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .order-invoice-page .receipt-paper::before,
    .order-invoice-page .receipt-paper::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #f3f5f8;
        border: 1px solid #dfe3ea;
        top: calc(50% - 10px);
    }

    .order-invoice-page .receipt-paper::before {
        left: -10px;
    }

    .order-invoice-page .receipt-paper::after {
        right: -10px;
    }

    .order-invoice-page .receipt-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.5rem;
    }

    .order-invoice-page .receipt-brand {
        font-size: 0.76rem;
        letter-spacing: 0.34em;
        font-weight: 700;
        color: #6c7585;
    }

    .order-invoice-page .receipt-title {
        margin: 0.35rem 0 0.4rem;
        font-size: 1.7rem;
        font-weight: 700;
        color: #161c2d;
    }

    .order-invoice-page .receipt-meta {
        font-size: 0.78rem;
        color: #6a7382;
    }

    .order-invoice-page .receipt-status {
        font-size: 0.79rem;
        padding: 0.45rem 0.6rem;
    }

    .order-invoice-page .receipt-timeline {
        margin-top: 0.85rem;
        font-size: 0.8rem;
        color: #596273;
    }

    .order-invoice-page .receipt-divider {
        margin: 1.35rem 0 1.5rem;
        border-top: 1px dashed #cfd5df;
    }

    .order-invoice-page .receipt-product {
        border: 1px solid #e6eaf2;
        border-radius: 0.85rem;
        padding: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        background: linear-gradient(145deg, #fdfefe, #f7f9fc);
    }

    .order-invoice-page .receipt-product-image {
        width: 64px;
        height: 64px;
        border-radius: 0.85rem;
        background: #f0f3f8;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid #d9e0ea;
        flex: 0 0 64px;
    }

    .order-invoice-page .receipt-product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .order-invoice-page .receipt-product-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 600;
        color: #111827;
    }

    .order-invoice-page .receipt-product-subtitle {
        margin-top: 0.25rem;
        font-size: 0.82rem;
        color: #6b7383;
    }

    .order-invoice-page .receipt-section {
        margin-top: 1rem;
    }

    .order-invoice-page .receipt-section-title {
        font-size: 0.82rem;
        letter-spacing: 0.08em;
        color: #687181;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.55rem;
    }

    .order-invoice-page .receipt-kv {
        border: 1px solid #e6eaf2;
        border-radius: 0.8rem;
        overflow: hidden;
    }

    .order-invoice-page .receipt-kv-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 0.6rem 0.85rem;
        font-size: 0.85rem;
        border-bottom: 1px solid #eef1f6;
    }

    .order-invoice-page .receipt-kv-item:last-child {
        border-bottom: 0;
    }

    .order-invoice-page .receipt-kv-item span {
        color: #6e7788;
    }

    .order-invoice-page .receipt-kv-item strong {
        color: #1b2435;
        text-align: right;
    }

    .order-invoice-page .receipt-summary {
        border: 1px solid #d7deea;
        border-radius: 0.95rem;
        background: #f7faff;
        padding: 1rem;
    }

    .order-invoice-page .receipt-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 1rem;
        padding: 0.35rem 0;
        font-size: 0.9rem;
        color: #526079;
    }

    .order-invoice-page .receipt-summary-row strong {
        color: #1a2233;
    }

    .order-invoice-page .receipt-summary-row.total {
        margin-top: 0.45rem;
        border-top: 1px dashed #c9d2e3;
        padding-top: 0.65rem;
        font-size: 1rem;
        font-weight: 700;
    }

    .order-invoice-page .receipt-summary-note {
        margin-top: 0.75rem;
        font-size: 0.75rem;
        color: #73809a;
    }

    .order-invoice-page .receipt-pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
        border: 1px solid #e6eaf2;
        border-radius: 0.8rem;
        background: #f9fbfe;
        padding: 0.8rem;
        font-size: 0.8rem;
        color: #25304a;
    }

    .order-invoice-page .voucher-list {
        border: 1px solid #e4e8f0;
        border-radius: 0.85rem;
        overflow: hidden;
    }

    .order-invoice-page .voucher-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem;
        padding: 0.55rem 0.75rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .order-invoice-page .voucher-item:last-child {
        border-bottom: 0;
    }

    .order-invoice-page .voucher-code {
        font-family: Consolas, "Courier New", monospace;
        font-size: 0.82rem;
        color: #1e2535;
        word-break: break-all;
    }

    .order-invoice-page .receipt-footer {
        margin-top: 1.35rem;
        border-top: 1px dashed #cfd5df;
        padding-top: 0.85rem;
        font-size: 0.76rem;
        color: #697487;
    }

    .order-invoice-page .receipt-actions {
        position: sticky;
        top: 1rem;
        z-index: 5;
    }

    @media (max-width: 992px) {
        .order-invoice-page .receipt-paper {
            padding: 1rem;
            border-radius: 0.8rem;
        }

        .order-invoice-page .receipt-head {
            flex-direction: column;
            align-items: stretch;
        }

        .order-invoice-page .receipt-title {
            font-size: 1.35rem;
        }
    }

    @media print {
        body {
            background: #fff !important;
        }

        .receipt-actions,
        #kt_app_header,
        #kt_app_sidebar,
        #kt_app_toolbar,
        #kt_app_footer {
            display: none !important;
        }

        .order-invoice-page,
        .app-content,
        #kt_app_content_container {
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .order-invoice-page .receipt-paper {
            box-shadow: none;
            border: 1px solid #d2d8e3;
            border-radius: 0;
            padding: 1rem;
        }
    }
</style>

<div class="d-flex flex-column flex-column-fluid order-invoice-page">
    <div id="kt_app_content" class="app-content flex-column-fluid py-8">
        <div id="kt_app_content_container" class="app-container container-sm">
            <div class="receipt-wrapper">
                <div class="receipt-actions d-flex justify-content-end gap-2 mb-4">
                    <button type="button" class="btn btn-light-success btn-sm" id="btn-copy-card">
                        <i class="fa-solid fa-image me-1"></i>Kart Kopyala
                    </button>
                    <button type="button" class="btn btn-light-primary btn-sm" onclick="window.print()">
                        <i class="fa-solid fa-print me-1"></i>Yazdır
                    </button>
                    <a href="<?= base_url('orders') ?>" class="btn btn-light btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i>Sipariş Listesi
                    </a>
                </div>

                <div class="receipt-paper">
                    <div class="receipt-head">
                        <div>
                            <div class="receipt-brand">DIJIPIN</div>
                            <h1 class="receipt-title">Sipariş Dekontu</h1>
                            <div class="receipt-meta">Belge No: DPT-<?= (int)$order->order_id ?></div>
                        </div>
                        <div class="text-lg-end">
                            <span class="<?= html_escape((string)$order->order_status_badge_class) ?> receipt-status"><?= html_escape((string)$order->order_status_label) ?></span>
                            <div class="receipt-timeline">
                                <div><strong>Oluşturma:</strong> <?= html_escape((string)$order->order_created_at_formatted) ?></div>
                                <div><strong>Teslim:</strong> <?= html_escape((string)$order->order_sended_at_formatted) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="receipt-divider"></div>

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="receipt-product">
                                <div class="receipt-product-image">
                                    <?php if (!empty($order->product_image)) : ?>
                                        <img src="<?= html_escape((string)$order->product_image) ?>" alt="Ürün">
                                    <?php else : ?>
                                        <span class="fs-8 text-muted">Ürün</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h2 class="receipt-product-title"><?= html_escape((string)$order->product_name) ?></h2>
                                    <div class="receipt-product-subtitle"><?= html_escape((string)$order->category_title) ?> | <?= (int)$order->order_quantity ?> Adet</div>
                                </div>
                            </div>

                            <div class="receipt-section">
                                <div class="receipt-section-title">Sipariş Referansları</div>
                                <div class="receipt-kv">
                                    <div class="receipt-kv-item"><span>Sipariş ID</span><strong>#<?= (int)$order->order_id ?></strong></div>
                                    <div class="receipt-kv-item"><span>Reseller Referans</span><strong><?= html_escape((string)$order->reseller_order_ref) ?></strong></div>
                                    <div class="receipt-kv-item"><span>Transaction ID</span><strong id="bot-transaction-id"><?= html_escape((string)$order->supplier_order_ref) ?></strong></div>
                                </div>
                            </div>

                            <?php
                                $order_data_normalized   = trim((string)($order->order_data ?? ''));
                                $voucher_text_normalized = trim((string)($order->voucher_text ?? ''));
                                $show_order_note = $order_data_normalized !== '' && $order_data_normalized !== $voucher_text_normalized;
                            ?>
                            <?php if ($show_order_note) : ?>
                                <div class="receipt-section">
                                    <div class="receipt-section-title">Sipariş Notu</div>
                                    <pre class="receipt-pre"><?= html_escape((string)$order->order_data) ?></pre>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($order->require_data_lines)) : ?>
                                <div class="receipt-section">
                                    <div class="receipt-section-title">Sipariş Parametreleri</div>
                                    <div class="receipt-kv" id="bot-params-kv">
                                        <?php foreach ($order->require_data_lines as $line) : ?>
                                            <div class="receipt-kv-item">
                                                <span><?= html_escape((string)$line['label']) ?></span>
                                                <strong><?= html_escape((string)$line['value']) ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- <div class="col-lg-4">
                            <div class="receipt-summary">
                                <div class="receipt-section-title mb-1">Tutar Özeti</div>
                                <div class="receipt-summary-row"><span>Birim Satış</span><strong><?= html_escape((string)$order->sale_price_text) ?></strong></div>
                                <div class="receipt-summary-row total"><span>Toplam Satış</span><strong><?= html_escape((string)$order->total_sale_text) ?></strong></div>
                                <div class="receipt-summary-note">Bu dekont operasyon takibi içindir ve sistem tarafından otomatik üretilir.</div>
                            </div>
                        </div> -->
                    </div>

                    <?php if (!empty($order->voucher_data)) : ?>
                        <div class="receipt-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="receipt-section-title mb-0">Teslim Edilen Epin Kodları</div>
                                <button type="button" class="btn btn-light btn-sm" onclick="copyAllVouchers()">
                                    <i class="fa-solid fa-copy me-1"></i>Tümünü Kopyala
                                </button>
                            </div>

                            <textarea id="voucherPayload" class="d-none"><?= html_escape((string)$order->voucher_text) ?></textarea>

                            <div class="voucher-list">
                                <?php foreach ($order->voucher_data as $voucher) : ?>
                                    <?php $encodedVoucher = json_encode((string)$voucher, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
                                    <div class="voucher-item">
                                        <span class="voucher-code"><?= html_escape((string)$voucher) ?></span>
                                        <button type="button" class="btn btn-icon btn-light btn-sm" onclick='copyVoucher(<?= $encodedVoucher ?>)' title="Kopyala">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($order->payment_info_text)) : ?>
                        <div class="receipt-section">
                            <div class="receipt-section-title">Ödeme Bilgisi</div>
                            <pre class="receipt-pre"><?= html_escape((string)$order->payment_info_text) ?></pre>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($order->api_logs)) : ?>
                        <div class="receipt-section">
                            <div class="receipt-section-title">Ödeme ve Tedarikçi API Logları</div>
                            <div class="accordion" id="orderApiLogs">
                                <?php foreach ($order->api_logs as $logIndex => $apiLog) : ?>
                                    <?php
                                    $apiPayload = json_decode((string)$apiLog->log_message, true);
                                    $apiPayload = is_array($apiPayload) ? $apiPayload : ['raw' => (string)$apiLog->log_message];
                                    $phase = (string)($apiPayload['phase'] ?? 'order.api');
                                    $direction = (string)($apiPayload['direction'] ?? '');
                                    $logTitle = $phase . ($direction !== '' ? ' / ' . $direction : '');
                                    $collapseId = 'orderApiLog' . (int)$logIndex;
                                    ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="<?= html_escape($collapseId) ?>Heading">
                                            <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#<?= html_escape($collapseId) ?>" aria-expanded="false" aria-controls="<?= html_escape($collapseId) ?>">
                                                <span class="me-3"><?= html_escape($logTitle) ?></span>
                                                <small class="text-muted"><?= html_escape((string)$apiLog->log_created_at) ?></small>
                                            </button>
                                        </h2>
                                        <div id="<?= html_escape($collapseId) ?>" class="accordion-collapse collapse" aria-labelledby="<?= html_escape($collapseId) ?>Heading" data-bs-parent="#orderApiLogs">
                                            <div class="accordion-body">
                                                <pre class="receipt-pre"><?= html_escape(json_encode($apiPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="receipt-footer">
                        <div>Dijipin Operasyon Belgesi | Belge Zamanı: <?= date('d.m.Y H:i:s') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        function notify(type, message) {
            if (window.toastr && typeof window.toastr[type] === 'function') {
                window.toastr[type](message);
                return;
            }

            if (type === 'error') {
                window.alert(message);
            }
        }

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
                notify('success', 'Kod kopyalandı.');
            } catch (err) {
                notify('error', 'Kopyalama başarısız.');
                console.error(err);
            }

            document.body.removeChild(tempInput);
        }

        function copyText(text) {
            var normalized = String(text || '').trim();
            if (!normalized) {
                notify('error', 'Kopyalanacak veri bulunamadı.');
                return;
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(normalized).then(function() {
                    notify('success', 'Kod kopyalandı.');
                }).catch(function() {
                    fallbackCopy(normalized);
                });
                return;
            }

            fallbackCopy(normalized);
        }

        window.copyVoucher = function(voucher) {
            copyText(voucher);
        };

        window.copyAllVouchers = function() {
            var payload = document.getElementById('voucherPayload');
            copyText(payload ? payload.value : '');
        };
    })();

    (function () {
        var orderId = <?= (int)$order->order_id ?>;

        function makeRow(label, value) {
            var div = document.createElement('div');
            div.className = 'receipt-kv-item';
            var span = document.createElement('span');
            span.textContent = label;
            var strong = document.createElement('strong');
            strong.textContent = value;
            div.appendChild(span);
            div.appendChild(strong);
            return div;
        }

        fetch('https://botstats.dijipin.com/api2.php?orderID=' + orderId)
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (!json || json.success !== true || !json.data) return;
                var d = json.data;

                // order_no → Transaction ID
                if (d.order_no) {
                    var txEl = document.getElementById('bot-transaction-id');
                    if (txEl) txEl.textContent = d.order_no;
                }

                // player_name & player_region → Sipariş Parametreleri, userID satırından sonra
                var kv = document.getElementById('bot-params-kv');
                if (kv && (d.player_name || d.player_region)) {
                    var items = kv.querySelectorAll('.receipt-kv-item');
                    var insertAfter = null;
                    for (var i = 0; i < items.length; i++) {
                        var spanEl = items[i].querySelector('span');
                        if (spanEl && /userid|user.?id|kullan.c./i.test(spanEl.textContent)) {
                            insertAfter = items[i];
                            break;
                        }
                    }
                    var anchor = insertAfter ? insertAfter.nextSibling : null;
                    if (d.player_name) {
                        var nameRow = makeRow('Oyuncu Adı', d.player_name);
                        anchor ? kv.insertBefore(nameRow, anchor) : kv.appendChild(nameRow);
                        anchor = nameRow.nextSibling;
                    }
                    if (d.player_region) {
                        var regionRow = makeRow('Bölge', d.player_region);
                        anchor ? kv.insertBefore(regionRow, anchor) : kv.appendChild(regionRow);
                    }
                }
            })
            .catch(function (e) { console.error('Bot API hatası:', e); });
    })();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    (function () {
        var btn = document.getElementById('btn-copy-card');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var paper = document.querySelector('.receipt-paper');
            if (!paper) return;

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Kopyalanıyor...';

            html2canvas(paper, { scale: 2, useCORS: true, backgroundColor: '#ffffff' }).then(function (canvas) {
                canvas.toBlob(function (blob) {
                    if (!blob) return;
                    navigator.clipboard.write([
                        new ClipboardItem({ 'image/png': blob })
                    ]).then(function () {
                        if (window.toastr) toastr.success('Kart görüntüsü panoya kopyalandı.');
                    }).catch(function () {
                        var url = URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'siparis-' + paper.closest('.receipt-wrapper').querySelector('.receipt-meta').textContent.trim() + '.png';
                        a.click();
                        URL.revokeObjectURL(url);
                    }).finally(function () {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-image me-1"></i>Kart Kopyala';
                    });
                }, 'image/png');
            }).catch(function (e) {
                console.error(e);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-image me-1"></i>Kart Kopyala';
            });
        });
    })();
</script>
