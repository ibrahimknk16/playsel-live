<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-order-detail" data-view="order-detail">
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

        $paymentTypeStr = '-';
        if (!empty($order->order_payment_type) && isset($paymentArray[$lang][$order->order_payment_type])) {
            $paymentTypeStr = $paymentArray[$lang][$order->order_payment_type]['text'];
        } elseif ($order->order_type == 3) {
            $paymentTypeStr = 'Cüzdan (Bakiye)';
        }

        if (($order->bundle_count ?? 1) > 1 && empty($order->is_quantity_group)) {
            $productTypeStr = 'Paket';
        } elseif (!empty($order->order_voucher)) {
            $productTypeStr = 'E-pin';
        } elseif (!empty($order->order_require_data)) {
            $productTypeStr = 'Top-up';
        } else {
            $productTypeStr = 'Dijital Ürün';
        }
        ?>

        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-bag"/></svg></span>
            <div class="hero-copy">
                <h1>Sipariş #<?= $order->order_id ?></h1>
                <p>Sipariş ve teslimat bilgilerini görüntüleyin.</p>
            </div>
            <div class="hero-action">
                <a href="<?= base_url($this->uri->segment(1) . '/profile/orders') ?>" class="secondary-button" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                    <svg class="icon" style="transform: rotate(180deg); width:14px; height:14px;"><use href="#i-arrow"/></svg>
                    Siparişlere dön
                </a>
            </div>
        </header>

        <div class="panel">
            <div class="order-detail-header-bar" style="display:flex; justify-content:space-between; align-items:center; padding-bottom:14px; border-bottom:1px solid var(--line-soft); margin-bottom:16px;">
                <div style="font-size:13px; color:#8f9ba6;">
                    Sipariş numarası <strong style="color:var(--accent); font-weight:700;">#<?= $order->order_id ?></strong>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <?php if ((int)$order->order_status === 1): ?>
                        <?php if (!empty($has_reviewed)): ?>
                            <button type="button" class="detail-button" style="min-height:30px;font-size:12px;padding:0 12px;display:inline-flex;align-items:center;gap:5px;opacity:0.6;cursor:not-allowed;" disabled>
                                <svg class="icon" style="width:13px;height:13px;"><use href="#i-star"/></svg> Değerlendirildi
                            </button>
                        <?php else: ?>
                            <button type="button" class="primary-button review-btn" data-id="<?= $order->order_id ?>" style="min-height:30px;font-size:12px;padding:0 12px;display:inline-flex;align-items:center;gap:5px;cursor:pointer;">
                                <svg class="icon" style="width:13px;height:13px;"><use href="#i-star"/></svg> Siparişi Değerlendir
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <span class="status <?= $s['class'] ?>">
                        <?php if($s['icon'] == 'pending' || $s['icon'] == 'processing'): ?>
                            <span class="status-mark <?= $s['icon'] ?>" aria-hidden="true"></span>
                        <?php else: ?>
                            <span class="status-mark"><svg class="icon"><use href="#i-<?= $s['icon'] ?>"/></svg></span>
                        <?php endif; ?>
                        <?= $s['text'] ?>
                    </span>
                </div>
            </div>

            <div class="order-detail-summary">
                <div class="order-detail-thumb">
                    <?php if(!empty($order->product_icon)): ?>
                        <img src="<?= getenv('CDN_URL') . $order->product_icon ?>" alt="<?= html_escape($order->product_name) ?>">
                    <?php else: ?>
                        <svg class="icon"><use href="#i-bag"/></svg>
                    <?php endif; ?>
                </div>
                <div>
                    <strong style="display:block; color:#f5f6f7; font-size:15px; font-weight:750;">Ürün bilgileri</strong>
                    <span style="display:block; margin-top:4px; color:#8f9ba6; font-size:13px;">
                        <?= html_escape($order->product_name) ?> · <?= !empty($order->is_quantity_group) ? (int)$order->bundle_count . ' adet' : (($order->bundle_count ?? 1) > 1 ? (int)$order->bundle_count . ' Parça' : (int)$order->order_quantity . ' adet') ?>
                    </span>
                </div>
            </div>

            <div class="detail-meta-grid">
                <div class="detail-meta">
                    <span>Toplam tutar</span>
                    <strong><?= number_format($order->order_sale_price, 2, ',', '.') ?> <?= $order->order_sale_price_currency === 'TRY' ? '₺' : html_escape($order->order_sale_price_currency) ?></strong>
                </div>
                <div class="detail-meta">
                    <span>Sipariş tarihi</span>
                    <strong><?= date('d.m.Y · H:i', strtotime($order->order_created_at)) ?></strong>
                </div>
                <div class="detail-meta">
                    <span>Sipariş IP adresi</span>
                    <strong>
                        <?= !empty($order->order_user_ip) ? html_escape($order->order_user_ip) : '—' ?>
                        <?php if (!empty($order->order_user_ip)): ?>
                            <button type="button" id="lookupOrderIpButton" class="secondary-button"
                                data-order-id="<?= (int)$order->order_id ?>"
                                style="min-height:26px; margin-top:6px; padding:0 9px; font-size:11px;">
                                IP konumunu sorgula
                            </button>
                        <?php endif; ?>
                    </strong>
                </div>
                <div class="detail-meta">
                    <span>Ödeme yöntemi</span>
                    <strong><?= $paymentTypeStr ?></strong>
                </div>
                <?php if ((int)$order->order_payment_type === 3): ?>
                    <?php if (!empty($order->bank_name)): ?>
                    <div class="detail-meta"><span>Banka</span><strong><?= html_escape($order->bank_name) ?></strong></div>
                    <?php endif; ?>
                    <?php if (!empty($order->bank_owner)): ?>
                    <div class="detail-meta"><span>Alıcı</span><strong><?= html_escape($order->bank_owner) ?></strong></div>
                    <?php endif; ?>
                    <?php if (!empty($order->bank_iban)): ?>
                    <div class="detail-meta"><span>IBAN</span><strong><?= html_escape($order->bank_iban) ?></strong></div>
                    <?php endif; ?>
                    <?php if (!empty($order->order_eft_id)): ?>
                    <div class="detail-meta"><span>Havale referansı</span><strong><?= html_escape($order->order_eft_id) ?></strong></div>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="detail-meta">
                    <span>Ürün türü</span>
                    <strong><?= $productTypeStr ?></strong>
                </div>
            </div>

            <?php if (($order->bundle_count ?? 1) > 1 && !empty($order->bundle_parts)): ?>
            <div class="delivery-panel" style="margin-top:16px;">
                <h3 class="delivery-heading"><?= !empty($order->is_quantity_group) ? 'Sipariş Kalemleri' : 'Paket Parçaları' ?></h3>
                <?php $partLabels = [0 => 'Beklemede', 1 => 'Teslim Edildi', 2 => 'İptal Edildi', 3 => 'İşleniyor', 4 => 'İşleniyor', 5 => 'İptal Edildi', 9 => 'İşleniyor', 11 => 'Ödeme Bekleniyor', 12 => 'Ödeme Başarısız', 13 => 'İşleniyor', 14 => 'İşleniyor', 15 => 'Yükleme Bekliyor']; ?>
                <div class="delivery-rows">
                    <?php $partIndex = 0; foreach ($order->bundle_parts as $part): $partIndex++; ?>
                        <?php
                        $partStatus = (int)$part->order_status;
                        $partCancelled = in_array($partStatus, [2, 5, 12], true);
                        $partData = json_decode((string)$part->order_data, true);
                        $partReason = is_array($partData) ? trim((string)($partData['cancellation_reason'] ?? '')) : '';
                        $partRefunded = (float)($part->refunded_amount ?? 0);
                        if ($partStatus === 1) {
                            $partNote = 'Bu parça başarıyla teslim edildi.';
                            $partColor = '#4cd18a';
                        } elseif ($partCancelled) {
                            $partNote = 'Bu parça iptal edildi' . ($partReason !== '' ? ' (' . $partReason . ')' : '') . '. '
                                . ($partRefunded > 0
                                    ? number_format($partRefunded, 2, ',', '.') . ' ₺ tutar bakiyenize iade edildi.'
                                    : ($partStatus === 12 ? 'Ödeme alınmadığı için iade yapılmaz.' : 'İade işleme alınıyor; kısa süre içinde bakiyenize yansıyacak.'));
                            $partColor = '#f06b63';
                        } elseif (in_array($partStatus, [0, 11], true)) {
                            $partNote = 'Bu parça için ödeme onayı bekleniyor.';
                            $partColor = '#d2a445';
                        } else {
                            $partNote = 'Bu parça işleniyor; tamamlandığında durum güncellenecek.';
                            $partColor = '#d2a445';
                        }
                        ?>
                        <div class="delivery-row" style="flex-wrap:wrap;">
                            <span class="delivery-label"><?= html_escape($part->product_name) ?><?= !empty($order->is_quantity_group) ? ' #' . (int)($partIndex ?? 0) : '' ?> · <span style="color:<?= $partColor ?>;"><?= html_escape($partLabels[$partStatus] ?? 'İşleniyor') ?></span></span>
                            <strong class="delivery-value"><?= number_format((float)$part->order_sale_price, 2, ',', '.') ?> <?= $part->order_sale_price_currency === 'TRY' ? '₺' : html_escape($part->order_sale_price_currency) ?></strong>
                            <span style="flex-basis:100%;font-size:11px;color:<?= $partColor ?>;margin-top:4px;"><?= html_escape($partNote) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($order->order_require_data) && is_array($order->order_require_data)): ?>
            <div class="delivery-panel" style="margin-top:16px;">
                <div class="delivery-rows">
                    <?php foreach($order->order_require_data as $key => $val): ?>
                        <?php
                        $normalizedKey = strtolower(trim(str_replace(['_', '-', ' '], '', $key)));
                        if (in_array($normalizedKey, ['userid', 'playerid', 'oyuncuid', 'user', 'player', 'oyuncu'])) {
                            $label = 'Oyuncu ID';
                        } elseif (in_array($normalizedKey, ['serverid', 'sunucuid', 'sw', 'swid', 'server', 'sunucu'])) {
                            $label = 'Sunucu ID';
                        } else {
                            $label = ucwords(str_replace(['_', '-'], ' ', $key));
                        }
                        ?>
                        <div class="delivery-row">
                            <span class="delivery-label"><?= html_escape($label) ?></span>
                            <strong class="delivery-value"><?= html_escape($val) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (empty($order->order_voucher)): ?>
                <div class="order-info-banner" style="margin-top:14px; display:flex; align-items:center; gap:8px; padding:12px 14px; border:1px solid rgba(210,145,13,0.2); border-radius:9px; background:rgba(210,145,13,0.04); color:#d2a445; font-size:12px;">
                    <span style="font-size:14px; color:var(--accent); flex:none;">ⓘ</span>
                    <span><?php
                        $topupStatus = (int)$order->order_status;
                        if ($topupStatus === 1) {
                            echo 'Top-up işlemi tamamlandı; yükleme bilgileri sipariş kaydınızda yer alır.';
                        } elseif ($topupStatus === 100) {
                            echo 'Paketin bazı parçaları iptal edildi; iptal edilen parçaların durumu ve iadesi "Paket Parçaları" bölümünde gösterilir.';
                        } elseif ($topupStatus === 102) {
                            echo 'Paketin bazı parçaları yüklendi, kalan parçalar işleniyor.';
                        } elseif (in_array($topupStatus, [2, 5, 12], true)) {
                            echo 'Bu sipariş iptal edilmiştir. İade durumunu Bakiye Hareketlerim sayfasından takip edebilirsiniz.';
                        } else {
                            echo 'Siparişiniz işleniyor; yükleme tamamlandığında durum güncellenecektir.';
                        }
                    ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($order->order_voucher)): ?>
            <div class="delivery-panel" style="margin-top:16px;">
                <h3 class="delivery-heading">E-pin Teslimatı</h3>
                <div class="delivery-rows">
                    <?php 
                    $vouchers = json_decode($order->order_voucher, true);
                    if(is_array($vouchers)):
                        $v_idx = 1;
                        foreach($vouchers as $key => $vouch): 
                            $displayVoucher = is_array($vouch) ? json_encode($vouch, JSON_UNESCAPED_UNICODE) : $vouch;
                            if (!is_numeric($key)) {
                                $displayVoucher = $key . ': ' . $displayVoucher;
                            }
                            $vLabel = count($vouchers) > 1 ? 'E-pin Kodu ' . $v_idx : 'E-pin Kodu';
                    ?>
                        <div class="delivery-row">
                            <span class="delivery-label"><?= $vLabel ?></span>
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; min-width:0;">
                                <strong class="delivery-value code" id="voucher-code-<?= $v_idx ?>"><?= html_escape($displayVoucher) ?></strong>
                                <button type="button" class="icon-button" onclick="copyToClipboard('voucher-code-<?= $v_idx ?>', true)" aria-label="Kopyala" title="Kodu Kopyala" style="width:30px; height:30px; min-height:30px; padding:0; flex:none; border-radius:6px;">
                                    <svg class="icon" style="width:14px; height:14px;"><use href="#i-copy"/></svg>
                                </button>
                            </div>
                        </div>
                    <?php 
                        $v_idx++;
                        endforeach; 
                    else: 
                    ?>
                        <div class="delivery-row">
                            <span class="delivery-label">E-pin Kodu</span>
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; min-width:0;">
                                <strong class="delivery-value code" id="voucher-code-1"><?= html_escape($order->order_voucher) ?></strong>
                                <button type="button" class="icon-button" onclick="copyToClipboard('voucher-code-1', true)" aria-label="Kopyala" title="Kodu Kopyala" style="width:30px; height:30px; min-height:30px; padding:0; flex:none; border-radius:6px;">
                                    <svg class="icon" style="width:14px; height:14px;"><use href="#i-copy"/></svg>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="order-info-banner" style="margin-top:14px; display:flex; align-items:center; gap:8px; padding:12px 14px; border:1px solid rgba(210,145,13,0.2); border-radius:9px; background:rgba(210,145,13,0.04); color:#d2a445; font-size:12px;">
                    <span style="font-size:14px; color:var(--accent); flex:none;">ⓘ</span>
                    <span>E-pin kodları siparişinize özel olarak teslim edildi. Güvenle kullanabilirsiniz.</span>
                </div>
            </div>
            <?php endif; ?>

            <?php if(empty($order->order_voucher) && empty($order->order_require_data)): ?>
            <div class="delivery-panel" style="margin-top:16px;">
                <div class="delivery-rows">
                    <div class="delivery-row">
                        <span class="delivery-label">Teslimat Durumu</span>
                        <strong class="delivery-value">
                            <?php
                            if ((int)$order->order_status === 1) {
                                echo 'Teslimat tamamlandı';
                            } elseif ((int)$order->order_status === 100) {
                                echo 'Bazı parçalar teslim edildi, bazıları iptal edildi';
                            } elseif ((int)$order->order_status === 102) {
                                echo 'Bazı parçalar teslim edildi, kalanlar işleniyor';
                            } elseif (in_array((int)$order->order_status, [2, 5, 12])) {
                                echo 'İptal edildi';
                            } elseif (in_array((int)$order->order_status, [0, 11])) {
                                echo 'Ödeme bekleniyor';
                            } else {
                                echo 'İşleniyor / Teslimat bekleniyor';
                            }
                            ?>
                        </strong>
                    </div>
                </div>
                <div class="order-info-banner" style="margin-top:14px; display:flex; align-items:center; gap:8px; padding:12px 14px; border:1px solid rgba(210,145,13,0.2); border-radius:9px; background:rgba(210,145,13,0.04); color:#d2a445; font-size:12px;">
                    <span style="font-size:14px; color:var(--accent); flex:none;">ⓘ</span>
                    <span>
                        <?php
                        if (in_array((int)$order->order_status, [0, 11])) {
                            echo 'Ödemenizin tamamlanması bekleniyor. Tamamlandığında siparişiniz işleme alınacaktır.';
                        } elseif (in_array((int)$order->order_status, [3, 4, 9, 14, 15])) {
                            echo 'Siparişiniz işleniyor. Teslimat tamamlandığında bilgileriniz burada görüntülenir.';
                        } elseif ((int)$order->order_status === 1) {
                            echo 'Siparişiniz başarıyla tamamlandı.';
                        } elseif ((int)$order->order_status === 100) {
                            echo 'Paketin bazı parçaları iptal edildi. İptal edilen parçaların tutarı bakiyenize iade edilir; her parçanın durumunu yukarıdaki listeden görebilirsiniz. İade hareketlerini Bakiye Hareketlerim sayfasından takip edebilirsiniz.';
                        } elseif ((int)$order->order_status === 102) {
                            echo 'Paketin bazı parçaları teslim edildi, kalan parçalar işleniyor. Tamamlanan her parçanın durumu yukarıdaki listede güncellenir.';
                        } else {
                            echo 'Bu sipariş iptal edilmiştir. İade durumunu hesap hareketlerinizden takip edebilirsiniz.';
                        }
                        ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ((int)$order->order_status === 2 || !empty($order->cancellation_reason)): ?>
            <div class="delivery-panel" style="border-color:rgba(240,107,99,0.25); background:rgba(240,107,99,0.05); margin-top:16px;">
                <h3 class="delivery-heading" style="color:#f58a83; margin-bottom:6px;">İptal Bilgisi</h3>
                <p style="margin:0; color:#f1f3f4; font-size:0.72rem; line-height:1.5;"><?= html_escape($order->cancellation_reason ?: 'Siparişiniz iptal edilmiştir.') ?></p>
            </div>
            <?php endif; ?>

            <div class="order-support-cta" style="margin-top:16px;">
                <div class="order-support-copy">
                    <div class="order-support-icon"><svg class="icon"><use href="#i-headset"/></svg></div>
                    <div>
                        <strong>Bu siparişle ilgili desteğe mi ihtiyacın var?</strong>
                        <small>Sipariş bilgileri talebine otomatik eklensin.</small>
                    </div>
                </div>
                <button class="primary-button" type="button" onclick="showOrderSupportModal(<?= (int)$order->order_id ?>)" style="background:var(--accent); color:#12151d; font-weight:750; border:none; display:inline-flex; align-items:center; gap:8px;">
                    <svg class="icon" style="width:15px; height:15px;"><use href="#i-headset"/></svg>
                    Destek talebi oluştur
                </button>
            </div>

            <div class="order-documents" style="margin-top:18px;">
                <div style="color:#8f9ba6; font-size:12px; font-weight:600; margin-bottom:8px;">Sipariş belgeleri</div>
                <div class="order-document-list" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                    <a href="<?= base_url($this->uri->segment(1) . '/profile/orderContract/' . $order->order_id . '/distance-sales-agreement') ?>" target="_blank" class="secondary-button" style="text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <svg class="icon" style="width:14px; height:14px;"><use href="#i-file"/></svg> Mesafeli satış sözleşmesi
                    </a>
                    <a href="<?= base_url($this->uri->segment(1) . '/profile/orderContract/' . $order->order_id . '/refund-policy') ?>" target="_blank" class="secondary-button" style="text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <svg class="icon" style="width:14px; height:14px;"><use href="#i-file"/></svg> İptal ve iade koşulları
                    </a>
                </div>
            </div>
        </div>
      </section>
    </section>
</main>

<div id="orderIpLookupModal" role="dialog" aria-modal="true" aria-labelledby="orderIpLookupTitle" hidden
    style="position:fixed; inset:0; z-index:10000; align-items:center; justify-content:center; padding:16px; background:rgba(0,0,0,.68);">
    <div style="width:min(100%,420px); padding:20px; border:1px solid rgba(255,255,255,.12); border-radius:12px; background:#1f232c; color:#f1f3f4; box-shadow:0 12px 40px rgba(0,0,0,.45);">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px;">
            <h2 id="orderIpLookupTitle" style="margin:0; font-size:16px;">IP konum bilgisi</h2>
            <button type="button" id="closeOrderIpLookup" class="secondary-button" aria-label="Kapat" style="min-height:28px; padding:0 9px;">Kapat</button>
        </div>
        <p style="margin:0 0 14px; color:#aeb6c2; font-size:12px; line-height:1.5;">
            Sorgulama için sipariş IP adresi HTTPS üzerinden ipwho.is servisine gönderilir. Konum bilgisi yaklaşık olabilir.
        </p>
        <div id="orderIpLookupStatus" role="status" style="font-size:13px;">Sorgulanıyor…</div>
        <dl id="orderIpLookupData" hidden style="display:grid; grid-template-columns:110px 1fr; gap:8px 12px; margin:14px 0 0; font-size:12px;"></dl>
    </div>
</div>

<script>
    (function () {
        const lookupButton = document.getElementById('lookupOrderIpButton');
        const modal = document.getElementById('orderIpLookupModal');
        if (!lookupButton || !modal) return;

        const closeButton = document.getElementById('closeOrderIpLookup');
        const status = document.getElementById('orderIpLookupStatus');
        const resultList = document.getElementById('orderIpLookupData');
        const lookupUrl = <?= json_encode(base_url($this->uri->segment(1) . '/profile/lookupOrderIp')) ?>;

        function closeModal() {
            modal.hidden = true;
            modal.style.display = 'none';
        }

        function showResult(data) {
            const fields = [
                ['IP', data.ip],
                ['Tür', data.type],
                ['Ülke', data.country],
                ['Bölge', data.region],
                ['Şehir', data.city],
                ['Posta kodu', data.postal],
                ['ISP', data.isp],
                ['Organizasyon', data.organization],
                ['Saat dilimi', data.timezone],
                ['Koordinat', data.latitude != null && data.longitude != null ? data.latitude + ', ' + data.longitude : '']
            ];

            resultList.replaceChildren();
            fields.forEach(function (field) {
                if (!field[1]) return;
                const term = document.createElement('dt');
                const value = document.createElement('dd');
                term.textContent = field[0];
                value.textContent = field[1];
                term.style.color = '#aeb6c2';
                value.style.margin = '0';
                value.style.overflowWrap = 'anywhere';
                resultList.append(term, value);
            });
            resultList.hidden = false;
            status.hidden = true;
        }

        lookupButton.addEventListener('click', function () {
            modal.hidden = false;
            modal.style.display = 'flex';
            status.hidden = false;
            status.textContent = 'IP konumu sorgulanıyor…';
            resultList.hidden = true;
            resultList.replaceChildren();
            lookupButton.disabled = true;

            fetch(lookupUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({ order_id: lookupButton.dataset.orderId })
            })
                .then(function (response) {
                    return response.json().catch(function () {
                        throw new Error('IP sorgusu sırasında bir hata oluştu.');
                    }).then(function (data) {
                        if (!response.ok || data.status !== 'success') {
                            throw new Error(data.message || 'IP konum bilgisi alınamadı.');
                        }
                        return data;
                    });
                })
                .then(function (response) {
                    showResult(response.data || {});
                })
                .catch(function (error) {
                    status.textContent = error instanceof TypeError
                        ? 'IP sorgu servisine şu anda ulaşılamıyor.'
                        : error.message || 'IP sorgusu sırasında bir hata oluştu.';
                })
                .finally(function () {
                    lookupButton.disabled = false;
                });
        });

        closeButton.addEventListener('click', closeModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) closeModal();
        });
    })();

    function copyToClipboard(elementId, isDiv = false) {
        let textToCopy = "";
        const el = document.getElementById(elementId);

        if (!el) return;

        if (!isDiv) {
            textToCopy = el.value;
        } else {
            textToCopy = el.innerText.trim();
        }

        navigator.clipboard.writeText(textToCopy).then(() => {
            if (typeof toastr !== 'undefined') {
                toastr.success('Kod kopyalandı: ' + textToCopy);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Kod kopyalandı',
                    text: textToCopy,
                    showConfirmButton: false,
                    timer: 2000,
                    background: '#161a24',
                    color: '#eef0f2'
                });
            } else {
                alert('Kod kopyalandı: ' + textToCopy);
            }
        });
    }

    function showOrderSupportModal(orderId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Sipariş Destek Talebi',
                html: `
                    <div class="text-start mb-4" style="text-align: left;">
                        <p style="color:#8f9ba6; font-size:13px; margin-bottom:15px;">Sipariş <b>#${orderId}</b> ile ilgili yaşadığınız sorunu veya talebinizi aşağıya yazabilirsiniz.</p>
                        <textarea id="swal-ticket-msg" class="form-control" rows="4" placeholder="Mesajınızı buraya yazın..." style="background:#12161f; color:#f1f3f4; border:1px solid #303644; border-radius:8px; padding:12px; width:100%; font-family:inherit; resize:vertical; font-size:13px;"></textarea>
                    </div>
                `,
                background: '#161a24',
                color: '#eef0f2',
                showCancelButton: true,
                confirmButtonText: 'Gönder',
                cancelButtonText: 'İptal',
                confirmButtonColor: '#d2910d',
                cancelButtonColor: '#262c3a',
                preConfirm: () => {
                    const msg = document.getElementById('swal-ticket-msg').value;
                    if (!msg || msg.trim() === '') {
                        Swal.showValidationMessage('Lütfen mesajınızı yazın.');
                        return false;
                    }
                    return msg;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const msg = result.value;
                    
                    const formData = new FormData();
                    formData.append('topic', 'Sipariş İtirazı / Sorunu');
                    formData.append('subject', 'Sipariş İtirazı'); 
                    formData.append('order_id', orderId);
                    formData.append('message', msg);
                    
                    fetch('<?= base_url($this->uri->segment(1) . "/profile/createTicket") ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Başarılı',
                                text: 'Destek talebiniz başarıyla oluşturuldu.',
                                background: '#161a24',
                                color: '#eef0f2',
                                confirmButtonColor: '#d2910d'
                            }).then(() => {
                                window.location.href = '<?= base_url($this->uri->segment(1) . "/profile/tickets") ?>';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Hata',
                                text: data.message || 'Bir hata oluştu.',
                                background: '#161a24',
                                color: '#eef0f2',
                                confirmButtonColor: '#d2910d'
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata',
                            text: 'Sistemsel bir hata oluştu.',
                            background: '#161a24',
                            color: '#eef0f2',
                            confirmButtonColor: '#d2910d'
                        });
                    });
                }
            });
        } else {
            alert('Destek penceresi yüklenemedi.');
        }
    }

    // Review Modal Logic
    document.addEventListener("DOMContentLoaded", function() {
        const reviewModal = document.getElementById('reviewModal');
        const closeReviewBtn = document.querySelector('.close-review-modal');
        const reviewOrderIdInput = document.getElementById('review_order_id');

        document.querySelectorAll('.review-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                reviewOrderIdInput.value = this.getAttribute('data-id');
                reviewModal.style.display = 'flex';
            });
        });

        if (closeReviewBtn) {
            closeReviewBtn.addEventListener('click', function() {
                reviewModal.style.display = 'none';
                document.getElementById('reviewForm').reset();
            });
        }

        window.addEventListener('click', function(e) {
            if (e.target == reviewModal) {
                reviewModal.style.display = 'none';
                document.getElementById('reviewForm').reset();
            }
        });

        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const rating = document.querySelector('input[name="rating"]:checked');
                if (!rating) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Eksik Bilgi',
                            text: 'Lütfen bir yıldız seçin.',
                            confirmButtonColor: '#ff4757',
                            background: '#1f1f2e',
                            color: '#fff'
                        });
                    } else {
                        alert('Lütfen bir yıldız seçin.');
                    }
                    return;
                }

                const formData = new FormData(this);
                fetch('<?= base_url($this->uri->segment(1) . '/profile/submit_review') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        reviewModal.style.display = 'none';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Başarılı!',
                                text: data.message,
                                confirmButtonColor: '#ff4757',
                                background: '#1f1f2e',
                                color: '#fff'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            alert(data.message);
                            location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Hata!',
                                text: data.message,
                                confirmButtonColor: '#ff4757',
                                background: '#1f1f2e',
                                color: '#fff'
                            });
                        } else {
                            alert(data.message);
                        }
                    }
                })
                .catch(() => {
                    alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                });
            });
        }
    });
</script>

<!-- Review Modal -->
<div id="reviewModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(3px);">
    <div class="modal-content" style="background:var(--bg-secondary, #1f1f2e); padding:24px; border-radius:12px; width:90%; max-width:400px; position:relative; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <h3 style="color:#fff; margin-top:0; font-size:18px;">Siparişi Değerlendir</h3>
        <span class="close-review-modal" style="position:absolute; top:15px; right:20px; color:#fff; cursor:pointer; font-size:24px; line-height:1;">&times;</span>
        
        <form id="reviewForm">
            <input type="hidden" name="order_id" id="review_order_id">
            
            <div style="margin:20px 0; text-align:center;">
                <p style="color:#e0e0e0; font-size:14px; margin-bottom:10px;">Lütfen puan verin:</p>
                <div class="star-rating" style="display:flex; justify-content:center; gap:10px; flex-direction:row-reverse;">
                    <input type="radio" id="star5" name="rating" value="5" style="display:none;" />
                    <label for="star5" title="5 Yıldız" style="cursor:pointer; color:#555; font-size:32px; transition: color 0.2s;"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" id="star4" name="rating" value="4" style="display:none;" />
                    <label for="star4" title="4 Yıldız" style="cursor:pointer; color:#555; font-size:32px; transition: color 0.2s;"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" id="star3" name="rating" value="3" style="display:none;" />
                    <label for="star3" title="3 Yıldız" style="cursor:pointer; color:#555; font-size:32px; transition: color 0.2s;"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" id="star2" name="rating" value="2" style="display:none;" />
                    <label for="star2" title="2 Yıldız" style="cursor:pointer; color:#555; font-size:32px; transition: color 0.2s;"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" id="star1" name="rating" value="1" style="display:none;" />
                    <label for="star1" title="1 Yıldız" style="cursor:pointer; color:#555; font-size:32px; transition: color 0.2s;"><i class="fa-solid fa-star"></i></label>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <p style="color:#e0e0e0; font-size:14px; margin-bottom:10px;">Yorumunuz (İsteğe bağlı):</p>
                <textarea name="comment" rows="3" style="width:100%; padding:12px; border-radius:8px; background:var(--bg-primary, #151515); border:1px solid rgba(255,255,255,0.1); color:#fff; resize:vertical; font-family:inherit; font-size:14px; outline:none;" placeholder="Deneyiminizi paylaşın..."></textarea>
            </div>

            <button type="submit" class="primary-button" style="width:100%; display:flex; align-items:center; justify-content:center;">Gönder</button>
        </form>
    </div>
</div>

<style>
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input[type="radio"]:checked ~ label {
    color: #ffc800 !important;
}
</style>
