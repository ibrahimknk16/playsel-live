<style>
    .ps-subcat-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e5e7eb;
    }

    .ps-subcat-btn {
        padding: 8px 16px;
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .ps-subcat-btn:hover {
        background: #e5e7eb;
        color: #111827;
    }

    .ps-subcat-btn.active {
        background: #5e35b1;
        /* Temanın Ana Rengi */
        color: #ffffff;
        border-color: #5e35b1;
        box-shadow: 0 4px 10px rgba(94, 53, 177, 0.2);
    }

    /* Ürünleri Filtrelerken Gizlemek İçin */
    .ps-package-label.hidden {
        display: none !important;
    }
</style>

<?php
// Gerekli Değişkenlerin Hazırlanması
$isServerRequired = false;
$serverPlaceholder = 'Sunucu Seçin';
$serverRequiredAttr = 'required';

if (!empty($category->category_requires)) {
    foreach ($category->category_requires as $req) {
        if (isset($req->inputName) && $req->inputName == 'server_id') {
            $isServerRequired = true;
            $serverPlaceholder = !empty($req->inputPlaceholder) ? $req->inputPlaceholder : 'Sunucu Seçin';
            $serverRequiredAttr = !empty($req->inputRequired) ? 'required' : '';
            break;
        }
    }
}

?>

<style>
    /* Select2 ve Genel Reset Stilleri */
    .no-animations,
    .no-animations * {
        animation: none !important;
        transition: none !important;
    }

    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }

    .select2-container--default .select2-selection--single {
        background-color: #fff !important;
        border: 2px solid #e0e0e0 !important;
        border-radius: 8px !important;
        height: 46px !important;
        display: flex;
        align-items: center;
        padding: 0 15px;
        outline: none !important;
        transition: border-color 0.3s ease;
    }

    .select2-container--default.select2-container--open .select2-selection--single,
    .select2-container--default .select2-selection--single:focus {
        border-color: #5e35b1 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111 !important;
        font-weight: 400;
        font-size: 14px;
        padding-left: 0 !important;
        line-height: normal;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 15px !important;
    }
</style>

<div class="tf-section pt-40 game-detail-page">
    <div class="themesflat-container">
        <div class="row">

            <!-- Sol Sidebar (Oyun Bilgileri) -->
            <div class="col-lg-4 col-md-12">
                <div class="ps-sidebar">
                    <div class="ps-sidebar-header">
                        <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $category->category_image ?>" alt="<?= $h1_tag ?>">
                        <div>
                            <h2><?= $h1_tag ?></h2>
                            <p><?= $category->category_publisher ?></p>
                            <div class="d-flex mt-2">
                                <p class="mb-0"><i class="fa-solid fa-check" style="color: rgb(255, 212, 59);"></i> <?= $this->lang->line('cd_secure_payment') ?></p>
                                <p class="mb-0 ms-3"><i class="fa-solid fa-bolt-lightning" style="color: rgb(255, 212, 59);"></i> <?= $this->lang->line('cd_fast_delivery') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="ps-store-buttons">
                        <?php if (!empty($category->category_android_app_url)): ?>
                            <a target="_blank" href="<?= $category->category_android_app_url ?>">
                                <img src="<?= getenv('CDN_URL') ?>assets-front/images/google-play.webp" alt="Google Play">
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($category->category_ios_app_url)): ?>
                            <a target="_blank" href="<?= $category->category_ios_app_url ?>">
                                <img src="<?= getenv('CDN_URL') ?>assets-front/images/app-store.webp" alt="App Store">
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="ps-dist-badge"><?= $this->lang->line('cd_official_distributor') ?></div>

                    <div class="ps-sidebar-text">
                        <?php if (!empty($category_description)): ?>
                            <div class="mb-4">
                                <h3><i class="icon-description"></i> <?= $this->lang->line('cd_description') ?></h3>
                                <div class="product-description"><?= $category_description ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($how_to_install)): ?>
                            <div>
                                <h3><i class="icon-description"></i> Nasıl Yüklenir?</h3>
                                <div class="product-description"><?= $how_to_install ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sağ Alan (Adımlar ve Satın Alma) -->
            <div class="col-lg-8 col-md-12">

                <?php if (!empty($categoryProducts)): ?>
                    <!-- ADIM 1: BİLGİLER -->
                    <div class="ps-step-box">
                        <div class="ps-step-header">
                            <h3 class="ps-step-title"><span class="ps-step-num">1</span> Bilgileri Girin</h3>
                        </div>
                        <div class="ps-input-row">
                            <?php if (!empty($category->category_requires)): ?>
                                <?php foreach ($category->category_requires as $req): ?>
                                    <?php
                                    // DB'de sunucu varsa select için atlıyoruz
                                    if (($req->inputName ?? '') === 'server_id' && !empty($category->servers)) continue;
                                    ?>
                                    <div class="ps-input-group">
                                        <label>
                                            <?= htmlspecialchars($req->inputPlaceholder ?? $req->inputName) ?>
                                            <?php if (($req->inputName ?? '') === 'user_id' && !empty($uniqueUserIds)): ?>
                                                <span style="font-size:11px; color:#5e35b1; float:right; cursor:pointer;" data-bs-toggle="modal" data-bs-target="#idModal">
                                                    <i class="fa-solid fa-clock-rotate-left"></i> Son kullanılan ID'ler
                                                </span>
                                            <?php endif; ?>
                                        </label>
                                        <input type="<?= htmlspecialchars($req->inputType ?? 'text') ?>"
                                            name="<?= htmlspecialchars($req->inputName) ?>"
                                            id="<?= htmlspecialchars($req->inputName) ?>"
                                            class="player-field"
                                            placeholder="<?= htmlspecialchars($req->inputPlaceholder ?? '') ?>"
                                            <?= !empty($req->inputMaxLength) ? 'maxlength="' . (int)$req->inputMaxLength . '"' : '' ?>
                                            <?= !empty($req->inputMinLength) ? 'minlength="' . (int)$req->inputMinLength . '"' : '' ?>
                                            <?= !empty($req->inputPattern) ? 'pattern="' . htmlspecialchars($req->inputPattern) . '"' : '' ?>
                                            <?= !empty($req->inputPatternError) ? 'title="' . htmlspecialchars($req->inputPatternError) . '"' : '' ?>
                                            <?= !empty($req->inputRequired) ? 'required' : '' ?>>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($category->servers) && $isServerRequired): ?>
                                <div class="ps-input-group">
                                    <label><?= htmlspecialchars($serverPlaceholder) ?></label>
                                    <select class="select" name="server_id" id="server_id" <?= $serverRequiredAttr ?>>
                                        <option value=""><?= htmlspecialchars($serverPlaceholder) ?></option>
                                        <?php foreach ($category->servers as $server): ?>
                                            <option value="<?= htmlspecialchars($server->value ?? $server->title) ?>">
                                                <?= htmlspecialchars($server->title) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <?php if ($category->category_id == 13): ?>
                                <button type="button" class="ps-btn-verify" id="btn-check-user" style="margin-top: 25px;">ID Doğrula</button>
                            <?php endif; ?>
                        </div>

                        <!-- AJAX Kullanıcı Bilgisi Alanı -->
                        <div id="mlbb-user" class="d-none mt-3 p-3 text-white" style="background:#311b5e; border-radius:8px; font-weight:600;"></div>
                    </div>

                    <!-- ADIM 2: PAKET SEÇİMİ -->
                    <div class="ps-step-box">
                        <div class="ps-step-header">
                            <h3 class="ps-step-title"><span class="ps-step-num">2</span> Dolum Miktarı Seç</h3>
                        </div>

                        <?php
                        $activeSubCatId = !empty($subCategories) ? $subCategories[0]->category_id : $category->category_id;

                        $activeProductCount = 0;
                        foreach ($categoryProducts as $p) {
                            if ($p->product_category_id == $activeSubCatId) {
                                $activeProductCount++;
                            }
                        }
                        ?>

                        <!-- ALT KATEGORİ SEKMELERİ -->
                        <?php if (!empty($subCategories)): ?>
                            <div class="ps-subcat-tabs">
                                <?php foreach ($subCategories as $index => $sub): ?>
                                    <button type="button" class="ps-subcat-btn <?= $index == 0 ? 'active' : '' ?>" data-target-cat="<?= $sub->category_id ?>">
                                        <?= html_escape($sub->category_title) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="ps-tab-content active">
                            <!-- ÜRÜN LİSTESİ -->
                            <div class="ps-grid" id="productGrid" style="<?= $activeProductCount === 0 ? 'display: none;' : '' ?>">
                                <?php
                                $firstVisibleProductFound = false;
                                foreach ($categoryProducts as $key => $product):
                                    $currency = $currentLocale->locale_currency ?? 'TRY';
                                    $displayCurrency = displayCurrency($currency);
                                    $userPrice = number_format($product->user_price, 2, '.', '');
                                    $userPrice = currencyCalculatorTo($currency, $userPrice);
                                    $isInStock = (int)($product->product_api_status ?? 0) === 1 && (float)$userPrice > 0;

                                    $displayStyle = ($product->product_category_id != $activeSubCatId) ? 'display: none;' : '';

                                    $isChecked = '';
                                    if ($product->product_category_id == $activeSubCatId && !$firstVisibleProductFound && $isInStock) {
                                        $isChecked = 'checked';
                                        $firstVisibleProductFound = true;
                                    }
                                ?>
                                    <label class="ps-package-label" data-cat-id="<?= $product->product_category_id ?>" style="<?= $displayStyle ?>">
                                        <input type="radio" name="pkg" value="<?= $userPrice ?>" data-item="<?= $product->product_id ?>" data-name="<?= htmlspecialchars($product->product_name) ?>" <?= $isChecked ?> <?= !$isInStock ? 'disabled' : '' ?>>
                                        <div class="ps-package-card" style="<?= !$isInStock ? 'opacity:0.5;' : '' ?>">
                                            <span class="amount"><?= htmlspecialchars($product->product_name) ?></span>
                                            <img src="<?= $product->product_image ?>" alt="<?= htmlspecialchars($product->product_name) ?>">
                                            <?php if (!$isInStock): ?>
                                                <span class="text-danger" style="font-size:12px; font-weight:bold;">Stokta Yok</span>
                                            <?php else: ?>
                                                <span class="price"><?= $displayCurrency . ' ' . $userPrice ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <!-- BOŞ DURUM KUTUSU (Eğer ilk sekmede ürün varsa PHP baştan gizler) -->
                            <div id="subcat-empty-state" class="text-center" style="<?= $activeProductCount > 0 ? 'display: none;' : 'display: block;' ?> padding: 40px 20px; width: 100%;">
                                <i class="fa-solid fa-box-open" style="font-size: 40px; color: #a7a2b3; margin-bottom: 15px;"></i>
                                <h4 style="color: #1a1a1c; font-weight: 600; margin-bottom: 10px; font-size:16px;">Bu Kategoride Ürün Bulunamadı</h4>
                                <p style="color: #6a627a; font-size: 14px; margin-bottom:0;">Şu anda seçtiğiniz bölgeye/kategoriye ait ürün bulunmuyor.</p>
                            </div>
                        </div>
                    </div>

                    <!-- ADIM 3: ÖDEME -->
                    <div class="ps-step-box">
                        <div class="ps-step-header">
                            <h3 class="ps-step-title"><span class="ps-step-num">3</span> Ödeme Yöntemi Seçin</h3>
                        </div>

                        <div class="ps-payment-box">
                            <div class="ps-payment-left">
                                <div class="ps-payment-icons">
                                    <i class="fa-brands fa-cc-visa" style="color:#1434CB;"></i>
                                    <i class="fa-brands fa-cc-mastercard" style="color:#EB001B;"></i>
                                    <i class="fa-brands fa-cc-amex" style="color:#002663;"></i>
                                </div>
                                <span style="margin: 6px 0 4px 0; display: block;">Güvenli Ödeme Ağı</span>
                                <strong id="step3-price">0.00 <?= displayCurrency($currency) ?></strong>
                            </div>
                            <i class="fa-solid fa-circle-check" style="color:#5e35b1; font-size:28px;"></i>
                        </div>

                        <!-- Görsel Kart Formu (Tasarım Amaçlı) -->
                        <div class="ps-cc-wrapper">
                            <div class="ps-cc-form">
                                <div class="ps-cc-row">
                                    <div class="ps-cc-input-group">
                                        <label>Kart Sahibi</label>
                                        <input type="text" id="cc-name" placeholder="Kart Üzerindeki İsim">
                                    </div>
                                    <div class="ps-cc-input-group">
                                        <label>Kart Numarası</label>
                                        <input type="text" id="cc-number" placeholder="**** **** **** ****" maxlength="19">
                                    </div>
                                </div>
                                <div class="ps-cc-row">
                                    <div class="ps-cc-input-group">
                                        <label>Son Kullanma Tarihi</label>
                                        <div style="display:flex; gap:10px;">
                                            <input type="text" id="cc-month" placeholder="Ay" maxlength="2">
                                            <input type="text" id="cc-year" placeholder="Yıl" maxlength="2">
                                        </div>
                                    </div>
                                    <div class="ps-cc-input-group">
                                        <label>CVV</label>
                                        <input type="password" placeholder="***" maxlength="3">
                                    </div>
                                </div>
                            </div>

                            <div class="ps-virtual-card">
                                <div class="ps-vc-chip"></div>
                                <div class="ps-vc-number" id="vc-number">**** **** **** ****</div>
                                <div class="ps-vc-bottom">
                                    <div>
                                        <span style="font-size:8px; color:#a7a2b3; display:block;">KART SAHİBİ</span>
                                        <div class="ps-vc-name" id="vc-name">AD SOYAD</div>
                                    </div>
                                    <div>
                                        <span style="font-size:8px; color:#a7a2b3; display:block;">SON KULLANMA</span>
                                        <div class="ps-vc-date" id="vc-date">AA/YY</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout Onay Kutusu -->
                    <div class="ps-checkout-box">
                        <div class="ps-fc-left">
                            <span><b id="fc-pkg-name">Seçilen Paket</b> • Kredi/Banka Kartı</span>
                            <strong id="fc-price">0.00 <?= displayCurrency($currency) ?></strong>
                        </div>
                        <div class="ps-fc-right">
                            <div class="ps-qty-selector">
                                <button type="button" id="btn-qty-minus">-</button>
                                <input type="number" id="input-qty" value="1" readonly>
                                <button type="button" id="btn-qty-plus">+</button>
                            </div>
                            <button class="ps-btn-submit checkout-btn-action"><?= $this->lang->line('buy_button') ?></button>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="ps-step-box text-center" style="padding: 60px 20px;">
                        <i class="fa-solid fa-box-open" style="font-size: 60px; color: #a7a2b3; margin-bottom: 20px;"></i>
                        <h3 style="color: #1a1a1c; font-weight: 600; margin-bottom: 10px;">Ürün Bulunamadı</h3>
                        <p style="color: #6a627a; font-size: 15px;">Şu anda bu kategoriye veya bölgeye ait aktif bir ürün bulunmuyor.<br> Lütfen daha sonra tekrar kontrol edin.</p>
                        <a href="<?= base_url() ?>" class="ps-btn-verify" style="display: inline-flex; text-decoration: none; align-items:center">Ana Sayfaya Dön</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php if (!empty($categoryProducts)): ?>
    <!-- Floating Bar (Scroll yapınca aşağıda çıkan bar) -->
    <div class="ps-floating-bar" id="floating-bar">
        <div class="themesflat-container">
            <div class="ps-float-wrapper">
                <div class="ps-float-left">
                    <span><b id="float-pkg-name">Seçilen Paket</b> • Kredi/Banka Kartı</span>
                    <strong id="float-price">0.00 <?= displayCurrency($currency) ?></strong>
                </div>
                <div>
                    <button class="ps-btn-submit checkout-btn-action"><?= $this->lang->line('buy_button') ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Payment iFrame Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered payment-modal" role="document" style="max-width: 600px;">
        <div class="modal-content border-0" style="border-radius:16px;">
            <div class="modal-body p-0 mt-5 payment-modal">
                <iframe class="payment-iframe"
                    src=""
                    style="width: 100%; height: 600px; border: none; overflow: hidden; border-radius:16px;"
                    frameborder="0" allowtransparency="true">
                </iframe>
            </div>
        </div>
    </div>
</div>

<!-- Son Kullanılan ID'ler Modal -->
<?php if (!empty($uniqueUserIds)): ?>
    <div class="modal fade" id="idModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" style="font-weight: 600; font-size:18px;">Son Kullanılan ID'ler</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($uniqueUserIds as $uid): ?>
                            <button type="button" class="btn btn-outline-secondary user-span-btn" style="border-radius: 8px; font-weight: 600;" data-userid="<?= htmlspecialchars($uid) ?>">
                                <?= htmlspecialchars($uid) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Select2 Aktifleştirme
        if ($.fn.select2) {
            $('select.select').select2({
                width: '100%',
                placeholder: "<?= htmlspecialchars($serverPlaceholder) ?>"
            });
        }

        // Son Kullanılan ID Seçimi
        const userSpanBtns = document.querySelectorAll('.user-span-btn');
        userSpanBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-userid');
                const userInput = document.querySelector('input[name="user_id"]');
                if (userInput) {
                    userInput.value = userId;
                    userInput.dispatchEvent(new Event('input')); // MLBB User check için tetikle
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('idModal'));
                modal.hide();
            });
        });

        <?php if (!empty($categoryProducts)): ?>
            let basePrice = 0;
            let currentQty = 1;
            let currencyIcon = "<?= displayCurrency($currency) ?>";

            const radios = document.querySelectorAll('input[type=radio][name="pkg"]');
            const step3Price = document.getElementById('step3-price');
            const bottomPrice = document.getElementById('fc-price');
            const bottomName = document.getElementById('fc-pkg-name');
            const inputQty = document.getElementById('input-qty');
            const floatPrice = document.getElementById('float-price');
            const floatName = document.getElementById('float-pkg-name');

            function updatePrices() {
                let total = (basePrice * currentQty).toFixed(2);
                let priceText = currencyIcon + " " + total;

                if (step3Price) step3Price.innerText = priceText;
                if (bottomPrice) {
                    bottomPrice.innerText = priceText;
                    bottomPrice.style.transform = 'scale(1.1)';
                    setTimeout(() => bottomPrice.style.transform = 'scale(1)', 200);
                }
                if (floatPrice) {
                    floatPrice.innerText = priceText;
                    floatPrice.style.transform = 'scale(1.1)';
                    setTimeout(() => floatPrice.style.transform = 'scale(1)', 200);
                }
            }

            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    basePrice = parseFloat(this.value);
                    let pkgName = this.getAttribute('data-name');
                    if (bottomName) bottomName.innerText = pkgName;
                    if (floatName) floatName.innerText = pkgName;

                    currentQty = 1;
                    if (inputQty) inputQty.value = currentQty;
                    updatePrices();
                });
            });

            // İlk radyo butonunu tetikle
            if (radios.length > 0) {
                let checkedRadio = document.querySelector('input[type=radio][name="pkg"]:checked');
                if (checkedRadio) {
                    checkedRadio.dispatchEvent(new Event('change'));
                }
            }

            // Adet İşlemleri
            const btnPlus = document.getElementById('btn-qty-plus');
            const btnMinus = document.getElementById('btn-qty-minus');
            if (btnPlus) {
                btnPlus.addEventListener('click', function() {
                    if (currentQty < 10) {
                        currentQty++;
                        inputQty.value = currentQty;
                        updatePrices();
                    }
                });
            }
            if (btnMinus) {
                btnMinus.addEventListener('click', function() {
                    if (currentQty > 1) {
                        currentQty--;
                        inputQty.value = currentQty;
                        updatePrices();
                    }
                });
            }

            // Floating Bar Scroll Gözlemleyici
            const floatingBar = document.getElementById('floating-bar');
            const staticCheckoutBox = document.querySelector('.ps-checkout-box');
            if (floatingBar && staticCheckoutBox) {
                const observer = new IntersectionObserver(function(entries) {
                    if (entries[0].isIntersecting) {
                        floatingBar.classList.remove('is-visible');
                    } else {
                        floatingBar.classList.add('is-visible');
                    }
                }, {
                    threshold: 0.1
                });
                observer.observe(staticCheckoutBox);
            }

            // Sanal Kredi Kartı Efektleri (Opsiyonel UI)
            const ccName = document.getElementById('cc-name');
            const ccNumber = document.getElementById('cc-number');
            const ccMonth = document.getElementById('cc-month');
            const ccYear = document.getElementById('cc-year');
            const vcName = document.getElementById('vc-name');
            const vcNumber = document.getElementById('vc-number');
            const vcDate = document.getElementById('vc-date');

            if (ccName) {
                ccName.addEventListener('input', function() {
                    vcName.innerText = this.value.toUpperCase() || 'AD SOYAD';
                });
                ccNumber.addEventListener('input', function(e) {
                    let val = this.value.replace(/\D/g, '').substring(0, 16);
                    let formatted = val.match(/.{1,4}/g)?.join(' ') || '';
                    this.value = formatted;
                    vcNumber.innerText = formatted || '**** **** **** ****';
                });

                function updateCardDate() {
                    let m = ccMonth.value || 'AA';
                    let y = ccYear.value || 'YY';
                    vcDate.innerText = m + '/' + y;
                }
                ccMonth.addEventListener('input', updateCardDate);
                ccYear.addEventListener('input', updateCardDate);
            }

            // ==========================================
            // CHECKOUT - AJAX ÖDEME İŞLEMİ
            // ==========================================
            const checkoutBtns = document.querySelectorAll('.checkout-btn-action');
            checkoutBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    let hasError = false;

                    // Validation (Regex, Required vs)
                    $('.player-field:visible, #server_id:visible').each(function() {
                        this.setCustomValidity('');
                        if (!this.checkValidity()) {
                            if (this.validity.patternMismatch) {
                                this.setCustomValidity(this.getAttribute('title') || 'Geçersiz format girdiniz.');
                            } else if (this.validity.valueMissing) {
                                this.setCustomValidity('Bu alanın doldurulması zorunludur.');
                            } else if (this.validity.tooShort) {
                                this.setCustomValidity('En az ' + this.getAttribute('minlength') + ' karakter girmelisiniz.');
                            } else if (this.validity.tooLong) {
                                this.setCustomValidity('En fazla ' + this.getAttribute('maxlength') + ' karakter girebilirsiniz.');
                            }
                            this.reportValidity();
                            hasError = true;

                            <?php if ($this->agent->is_mobile()): ?>
                                $('html, body').animate({
                                    scrollTop: $(this).offset().top - 150
                                }, 100);
                            <?php endif; ?>

                            return false;
                        }
                    });

                    if (hasError) return;

                    // Seçili paketi al
                    let checkedRadio = document.querySelector('input[name="pkg"]:checked');
                    if (!checkedRadio) {
                        if (typeof toastr !== 'undefined') toastr.error('Lütfen bir ürün seçin.');
                        return;
                    }

                    const $userInput = $('[name="user_id"]');
                    const $serverField = $('[name="server_id"]');

                    // Fiyatı hesaplayıp gönderiyoruz
                    let finalPrice = (parseFloat(checkedRadio.value) * currentQty).toFixed(2);

                    const formData = {
                        uid: $userInput.length ? $userInput.val().trim() : null,
                        sid: $serverField.length ? $serverField.val() : null,
                        pid: checkedRadio.getAttribute('data-item'),
                        p: finalPrice,
                        qty: currentQty
                    };

                    $.ajax({
                        url: '<?= topupcenter() . $uri ?>/payment/checkout',
                        method: 'POST',
                        data: formData,
                        success(response) {
                            if (response.status === 'success') {
                                const modalEl = document.getElementById('paymentModal');
                                const iframe = modalEl.querySelector('.payment-iframe');
                                iframe.src = response.data.paymentUrl || response.data.htmlContent;
                                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                modal.show();
                            } else {
                                if (typeof toastr !== 'undefined') toastr.error(response.message);
                                if (response?.data?.redirectUrl) {
                                    window.location.href = response.data.redirectUrl;
                                }
                            }
                        },
                        error(xhr, status, err) {
                            console.error('AJAX Hatası:', err);
                            if (typeof toastr !== 'undefined') toastr.error('Bir hata oluştu. Lütfen tekrar deneyin.');
                        }
                    });
                });
            });

            // iFrame başarılı dönüş yakalama
            const iframe = document.querySelector('.payment-iframe');
            if (iframe) {
                iframe.addEventListener('load', function() {
                    try {
                        const txt = iframe.contentWindow.document.body.textContent.trim().toLowerCase();
                        if (txt === 'ok') {
                            const modalEl = document.getElementById('paymentModal');
                            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            modal.hide();
                        }
                    } catch {}
                });
            }

        <?php endif; ?>

        // ==========================================
        // MLBB USER CHECK (SADECE CATEGORY 13 İÇİN)
        // ==========================================
        <?php if ($category->category_id == 13): ?>
            const $userInput = document.querySelector('#user_id');
            const $serverField = document.querySelector('#server_id');
            const btnCheckUser = document.querySelector('#btn-check-user');

            function shouldCheckUser() {
                const userId = $userInput ? $userInput.value.trim() : '';
                const serverId = $serverField ? $serverField.value.trim() : '';
                return userId !== '' && /^\d{4}$/.test(serverId);
            }

            function checkUser() {
                const userID = $userInput.value.trim();
                const serverID = $serverField.value;

                if (!userID || !serverID) return;

                $.ajax({
                    url: '<?= topupcenter() . $uri ?>/check-user',
                    method: 'POST',
                    data: {
                        user_id: userID,
                        server_id: serverID
                    },
                    success: function(response) {
                        const mlbbUserDiv = document.getElementById('mlbb-user');
                        if (response && response.name) {
                            const span = document.createElement('span');
                            span.className = 'fs-5';
                            span.appendChild(document.createTextNode(response.name + ' / ' + response.register_country + ' '));
                            const cc = String(response.register_country || '');
                            if (/^[A-Za-z]{2}$/.test(cc)) {
                                const img = document.createElement('img');
                                img.src = 'https://flagsapi.com/' + cc + '/flat/24.png';
                                img.alt = 'Country Flag';
                                span.appendChild(img);
                            }
                            mlbbUserDiv.replaceChildren(span);
                            mlbbUserDiv.classList.remove('d-none');
                        } else {
                            mlbbUserDiv.innerHTML = '';
                            mlbbUserDiv.classList.add('d-none');
                        }
                    }
                });
            }

            if ($userInput) $userInput.addEventListener('input', function() {
                if (shouldCheckUser()) checkUser();
            });
            if ($serverField) {
                $($serverField).on('change', function() {
                    if (shouldCheckUser()) checkUser();
                });
            }
            if (btnCheckUser) {
                btnCheckUser.addEventListener('click', function(e) {
                    e.preventDefault();
                    checkUser();
                });
            }
        <?php endif; ?>
        // --- ALT KATEGORİ SEKMELERİ (FİLTRELEME) ---
        const subcatBtns = document.querySelectorAll('.ps-subcat-btn');
        const productLabels = document.querySelectorAll('.ps-package-label');

        function filterProducts(categoryId) {
            let firstVisibleRadio = null;
            let visibleCount = 0;

            productLabels.forEach(label => {
                // data-cat-id sayı olarak gelebilir, string olarak karşılaştırmayı garantiye alalım
                const itemCatId = label.getAttribute('data-cat-id').toString();
                const radio = label.querySelector('input[type="radio"]');

                if (itemCatId === categoryId.toString()) {
                    label.style.display = 'block'; // veya 'inline-block' temana göre
                    visibleCount++;
                    if (!firstVisibleRadio && !radio.disabled) {
                        firstVisibleRadio = radio;
                    }
                } else {
                    label.style.display = 'none';
                    radio.checked = false;
                }
            });

            const emptyState = document.getElementById('subcat-empty-state');
            const productGrid = document.getElementById('productGrid');

            if (visibleCount === 0) {
                // Hiç ürün yoksa
                if (emptyState) emptyState.style.display = 'block';
                // Grid yapısının CSS'ini bozmamak için grid'i gizleyelim ama flex yapalım ki kutu sığsın
                if (productGrid) productGrid.style.display = 'none';

                // Sağ taraf sepet bilgilerini sıfırla
                basePrice = 0;
                if (bottomName) bottomName.innerText = 'Ürün Yok';
                if (floatName) floatName.innerText = 'Ürün Yok';
                updatePrices();
            } else {
                // Ürün varsa
                if (emptyState) emptyState.style.display = 'none';
                if (productGrid) productGrid.style.display = 'grid'; // Eski haline döndür

                if (firstVisibleRadio) {
                    firstVisibleRadio.checked = true;
                    firstVisibleRadio.dispatchEvent(new Event('change'));
                }
            }
        }

        // Tıklama olayları
        if (subcatBtns.length > 0) {
            const firstActiveBtn = document.querySelector('.ps-subcat-btn.active');
            if (firstActiveBtn) {
                filterProducts(firstActiveBtn.getAttribute('data-target-cat'));
            }

            subcatBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    subcatBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    filterProducts(this.getAttribute('data-target-cat'));
                });
            });
        }
    });
</script>