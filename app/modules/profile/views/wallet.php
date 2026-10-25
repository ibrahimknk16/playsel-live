<?php
$userSession = $this->session->userdata('user');
?>
<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
        <section class="view" id="view-wallet" data-view="wallet">
            <div class="profile-content">
                <header class="page-hero">
                    <span class="hero-icon"><svg class="icon" aria-hidden="true"><use href="#i-wallet"/></svg></span>
                    <div class="hero-copy">
                        <h1>Cüzdanım</h1>
                        <p>Cüzdan bakiyeni görüntüle ve hesabına güvenle bakiye yükle.</p>
                    </div>
                </header>

                <section class="wallet-info" aria-label="Cüzdan hakkında önemli bilgi">
                    <span class="info-icon"><svg class="icon" aria-hidden="true"><use href="#i-info"/></svg></span>
                    <div>
                        <strong>Önemli bilgi</strong>
                        <p>Çekilebilir bakiye ile alışveriş yapamazsın. Bu tutarı harcamak için önce kullanılabilir bakiyeye dönüştürmen gerekir.</p>
                    </div>
                </section>

                <section class="balance-grid" aria-label="Cüzdan bakiyeleri">
                    <article class="balance-card available">
                        <span class="balance-icon"><svg class="icon" aria-hidden="true"><use href="#i-coins"/></svg></span>
                        <span class="balance-copy">
                            <span class="balance-label">Harcanabilir bakiye</span>
                            <strong class="balance-value"><?= nFormat($userBalance['spendable_balance'] ?? 0) ?> <small>₺</small></strong>
                        </span>
                        <span class="balance-badge">Kullanılabilir</span>
                    </article>
                    <article class="balance-card withdrawable">
                        <span class="balance-icon"><svg class="icon" aria-hidden="true"><use href="#i-withdraw"/></svg></span>
                        <span class="balance-copy">
                            <span class="balance-label">Çekilebilir bakiye</span>
                            <strong class="balance-value"><?= nFormat($userBalance['withdrawable_balance'] ?? 0) ?> <small>₺</small></strong>
                        </span>
                        <span class="balance-badge">Çekilebilir</span>
                    </article>
                    <article class="balance-card total">
                        <span class="balance-icon"><svg class="icon" aria-hidden="true"><use href="#i-wallet"/></svg></span>
                        <span class="balance-copy">
                            <span class="balance-label">Toplam bakiye</span>
                            <strong class="balance-value"><?= nFormat($userBalance['total_balance'] ?? 0) ?> <small>₺</small></strong>
                        </span>
                        <span class="balance-badge">Toplam</span>
                    </article>
                </section>

                <section class="wallet-topup" aria-labelledby="topup-heading">
                    <div class="topup-head">
                        <div>
                            <h2 class="topup-title" id="topup-heading">Cüzdanına bakiye yükle</h2>
                            <p class="topup-subtitle">Ödeme yöntemini ve yüklemek istediğin tutarı seç.</p>
                        </div>
                        <div class="stepper" aria-label="Bakiye yükleme adımları">
                            <span class="step done" id="step-method"><span class="step-num">1</span>Yöntem seç</span>
                            <span class="step-line"></span>
                            <span class="step current" id="step-amount"><span class="step-num">2</span>Tutar belirle</span>
                            <span class="step-line"></span>
                            <span class="step" id="step-pay"><span class="step-num">3</span>Ödeme yap</span>
                        </div>
                    </div>

                    <div class="topup-layout">
                        <section aria-labelledby="method-heading">
                            <h3 class="method-label" id="method-heading"><svg class="icon"><use href="#i-card"/></svg>Ödeme yöntemi</h3>
                            <div class="method-list" role="tablist" aria-label="Bakiye yükleme yöntemi">
                                <button class="method-option" id="method-card" type="button" role="tab" aria-selected="true" aria-controls="pane-card" data-method="card">
                                    <span class="method-icon"><svg class="icon"><use href="#i-card"/></svg></span>
                                    <span><span class="method-name">Kredi kartı</span><span class="method-desc">Güvenli ve hızlı</span></span>
                                    <svg class="icon method-check"><use href="#i-check"/></svg>
                                </button>
                                <button class="method-option" id="method-transfer" type="button" role="tab" aria-selected="false" aria-controls="pane-transfer" data-method="transfer">
                                    <span class="method-icon"><svg class="icon"><use href="#i-bank"/></svg></span>
                                    <span><span class="method-name">Banka havalesi</span><span class="method-desc">Komisyonsuz</span></span>
                                    <svg class="icon method-check"><use href="#i-check"/></svg>
                                </button>
                                <button class="method-option" id="method-mobile" type="button" role="tab" aria-selected="false" aria-controls="pane-mobile" data-method="mobile">
                                    <span class="method-icon"><svg class="icon"><use href="#i-mobile"/></svg></span>
                                    <span><span class="method-name">Mobil ödeme</span><span class="method-desc">Telefonla hızlı</span></span>
                                    <svg class="icon method-check"><use href="#i-check"/></svg>
                                </button>
                            </div>
                        </section>

                        <div class="payment-panes">
                            <!-- Kredi Kartı -->
                            <section class="payment-pane" id="pane-card" role="tabpanel" aria-labelledby="method-card" style="display: block;">
                                <!-- <div class="provider-row">
                                    <span class="provider-meta">
                                        <span class="provider-logo">PayTR</span>
                                        <span><strong>Kartla güvenli ödeme</strong><small>Ödeme sağlayıcısı</small></span>
                                    </span>
                                    <span class="provider-secure"><svg class="icon"><use href="#i-lock"/></svg>Güvenli</span>
                                </div> -->
                                <div class="subsection">
                                    <h3 class="subheading"><svg class="icon"><use href="#i-edit"/></svg>Yüklenecek tutar</h3>
                                    <div class="amount-field">
                                        <label class="sr-only" for="amount-card">Kredi kartıyla yüklenecek tutar</label>
                                        <input id="amount-card" type="number" step="1" inputmode="decimal" placeholder="Tutar gir" data-amount-input="card">
                                        <span class="currency">₺</span>
                                    </div>
                                </div>
                                <div class="subsection">
                                    <h3 class="subheading"><svg class="icon"><use href="#i-lock"/></svg>Kart bilgileri</h3>
                                    <div class="card-details-layout">
                                        <div class="card-preview-column">
                                            <div class="bank-card-preview" id="bank-card-preview" aria-hidden="true">
                                                <div class="bank-card-inner">
                                                    <div class="card-face card-front">
                                                        <div class="card-chip"><span></span></div>
                                                        <div class="preview-number" id="preview-card-number">•••• •••• •••• ••••</div>
                                                        <div class="card-front-bottom">
                                                            <span><small>KART SAHİBİ</small><strong id="preview-card-holder">AD SOYAD</strong></span>
                                                            <span class="preview-expiry"><small>SON KULLANMA</small><strong id="preview-card-expiry">AA / YY</strong></span>
                                                        </div>
                                                    </div>
                                                    <div class="card-face card-back">
                                                        <div class="magnetic-stripe"></div>
                                                        <small class="card-back-label">İMZA</small>
                                                        <div class="signature-strip"><span id="preview-card-cvc">•••</span></div>
                                                        <div class="card-back-bottom"><span>GÜVENLİ ÖDEME</span><span>••••</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-fields">
                                            <form class="form-grid" id="wallet-card-form" autocomplete="on">
                                                <div class="field full">
                                                    <label for="card-holder">Kart üzerindeki isim</label>
                                                    <input id="card-holder" name="cc-name" type="text" autocomplete="cc-name" autocapitalize="words" placeholder="Ad soyad">
                                                </div>
                                                <div class="field full">
                                                    <label for="card-number">Kart numarası</label>
                                                    <input id="card-number" name="cc-number" type="text" inputmode="numeric" autocomplete="cc-number" autocapitalize="off" spellcheck="false" placeholder="•••• •••• •••• ••••" maxlength="23">
                                                </div>
                                                <div class="field">
                                                    <label for="card-expiry">Son kullanma tarihi</label>
                                                    <input id="card-expiry" name="cc-exp" type="text" inputmode="numeric" autocomplete="cc-exp" placeholder="AA / YY" maxlength="7">
                                                </div>
                                                <div class="field">
                                                    <label for="card-cvc">Güvenlik kodu</label>
                                                    <input id="card-cvc" name="cc-csc" type="password" inputmode="numeric" autocomplete="cc-csc" placeholder="CVC / CVV" maxlength="4">
                                                </div>
                                            </form>
                                            <p class="card-autofill-hint">Kart bilgileriniz güvenli ödeme sağlayıcısına şifreli olarak iletilir.</p>
                                            <div class="card-brand-row" aria-label="Örnek global kart ağları">
                                                <span>VISA</span><span>Mastercard</span><span>TROY</span><span>AMEX</span><span>Discover</span><span>UnionPay</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="payment-summary">
                                    <div class="summary-head"><svg class="icon"><use href="#i-receipt"/></svg>Ödeme özeti</div>
                                    <div class="summary-rows">
                                        <div class="summary-row"><span>Yüklenecek tutar</span><strong data-summary-amount="card">0,00 ₺</strong></div>
                                        <div class="summary-row"><span>İşlem ücreti (%2,7)</span><strong class="commission" data-summary-fee="card">0,00 ₺</strong></div>
                                    </div>
                                    <div class="summary-total"><span>Toplam ödenecek</span><strong data-summary-total="card">0,00 ₺</strong></div>
                                </div>
                                <button class="wallet-submit" type="button" data-submit-method="card">
                                    <svg class="icon"><use href="#i-lock"/></svg>Güvenli ödemeye geç<svg class="icon"><use href="#i-arrow"/></svg>
                                </button>
                                <p class="action-note" data-action-note="card" aria-live="polite"></p>
                            </section>

                            <!-- Havale -->
                            <?php
                            $bankMap = [];
                            if (!empty($banks)) {
                                foreach ($banks as $b) {
                                    $bId = (int)$b->bank_id;
                                    $bankMap[$bId] = [
                                        'id' => $bId,
                                        'name' => strip_tags($b->bank_name ?? ''),
                                        'owner' => strip_tags($b->bank_owner ?? ''),
                                        'iban' => strip_tags($b->bank_iban ?? ''),
                                        'account' => strip_tags($b->bank_account_number ?? ''),
                                    ];
                                }
                            }
                            $firstBank = !empty($bankMap) ? reset($bankMap) : null;
                            ?>
                            <section class="payment-pane" id="pane-transfer" role="tabpanel" aria-labelledby="method-transfer" hidden style="display: none;">
                                <div class="transfer-stage" id="transfer-stage" style="position: relative;">
                                    <div class="transfer-content" id="transfer-content">
                                        <h3 class="subheading"><svg class="icon"><use href="#i-bank"/></svg>Banka hesabı seç</h3>
                                        <div class="bank-picker" role="group" aria-label="Havale yapılacak banka">
                                            <?php if (!empty($bankMap)): ?>
                                                <?php foreach ($bankMap as $bId => $b): ?>
                                                    <button class="bank-choice" type="button" aria-pressed="<?= $bId === ($firstBank['id'] ?? 0) ? 'true' : 'false' ?>" data-bank-id="<?= $bId ?>"><?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?></button>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <button class="bank-choice" type="button" aria-pressed="true" disabled>Banka bulunamadı</button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="subsection">
                                            <h3 class="subheading"><svg class="icon"><use href="#i-edit"/></svg>Yüklenecek tutar</h3>
                                            <div class="amount-field">
                                                <label class="sr-only" for="amount-transfer">Havale ile yüklenecek tutar</label>
                                                <input id="amount-transfer" type="number" step="1" inputmode="decimal" placeholder="Tutar gir" data-amount-input="transfer">
                                                <span class="currency">₺</span>
                                            </div>
                                        </div>
                                        <div class="transfer-ref">
                                            <div>
                                                <strong>Havale referans numarası</strong>
                                                <p>Bu kodu havale açıklamasına ekle; ödemen otomatik eşleşsin ve bakiyen daha hızlı onaylansın.</p>
                                            </div>
                                            <span class="ref-control">
                                                <code id="wallet-reference"><?= htmlspecialchars($userSession['user_bank_referance_number'] ?? 'REF-XXXX') ?></code>
                                                <button class="copy-btn" type="button" data-copy="wallet-reference" aria-label="Havale referans numarasını kopyala"><svg class="icon"><use href="#i-copy"/></svg></button>
                                            </span>
                                        </div>
                                        <div class="bank-details">
                                            <div class="bank-details-head">
                                                <span class="bank-logo-mini" aria-hidden="true" id="mini-bank-logo"><?= !empty($firstBank) ? mb_substr($firstBank['name'], 0, 2) : 'XX' ?></span>
                                                <strong id="selected-bank"><?= !empty($firstBank) ? htmlspecialchars($firstBank['name'], ENT_QUOTES, 'UTF-8') : 'Banka' ?></strong>
                                            </div>
                                            <div class="bank-row">
                                                <span class="bank-label">Hesap sahibi</span>
                                                <strong class="bank-value" id="selected-bank-owner"><?= !empty($firstBank) ? htmlspecialchars($firstBank['owner'], ENT_QUOTES, 'UTF-8') : '-' ?></strong>
                                                <button class="copy-btn" type="button" data-copy-value="<?= !empty($firstBank) ? htmlspecialchars($firstBank['owner'], ENT_QUOTES, 'UTF-8') : '' ?>" aria-label="Hesap sahibini kopyala"><svg class="icon"><use href="#i-copy"/></svg></button>
                                            </div>
                                            <div class="bank-row">
                                                <span class="bank-label">IBAN</span>
                                                <strong class="bank-value" id="selected-bank-iban"><?= !empty($firstBank) ? htmlspecialchars($firstBank['iban'], ENT_QUOTES, 'UTF-8') : '-' ?></strong>
                                                <button class="copy-btn" type="button" data-copy-value="<?= !empty($firstBank) ? htmlspecialchars($firstBank['iban'], ENT_QUOTES, 'UTF-8') : '' ?>" aria-label="IBAN kopyala"><svg class="icon"><use href="#i-copy"/></svg></button>
                                            </div>
                                            <div class="bank-row">
                                                <span class="bank-label">Hesap no</span>
                                                <strong class="bank-value" id="selected-bank-account"><?= !empty($firstBank) ? htmlspecialchars($firstBank['account'], ENT_QUOTES, 'UTF-8') : '-' ?></strong>
                                                <button class="copy-btn" type="button" data-copy-value="<?= !empty($firstBank) ? htmlspecialchars($firstBank['account'], ENT_QUOTES, 'UTF-8') : '' ?>" aria-label="Hesap numarası kopyala"><svg class="icon"><use href="#i-copy"/></svg></button>
                                            </div>
                                        </div>
                                        <div class="payment-summary">
                                            <div class="summary-head"><svg class="icon"><use href="#i-receipt"/></svg>Yükleme özeti</div>
                                            <div class="summary-rows">
                                                <div class="summary-row"><span>Yüklenecek tutar</span><strong data-summary-amount="transfer">0,00 ₺</strong></div>
                                                <div class="summary-row"><span>İşlem ücreti</span><strong class="commission" data-summary-fee="transfer">0,00 ₺</strong></div>
                                            </div>
                                            <div class="summary-total"><span>Toplam gönderilecek</span><strong data-summary-total="transfer">0,00 ₺</strong></div>
                                        </div>
                                        <button class="wallet-submit" type="button" data-submit-method="transfer"><svg class="icon"><use href="#i-bank"/></svg>Havaleyi yaptım, ödemeyi bildir<svg class="icon"><use href="#i-arrow"/></svg></button>
                                        <p class="action-note" data-action-note="transfer" aria-live="polite"></p>
                                    </div>
                                    <div class="transfer-gate" role="group" aria-label="Havale ön bilgilendirmesi">
                                        <div class="transfer-gate-card">
                                            <span class="transfer-gate-icon"><svg class="icon" aria-hidden="true"><use href="#i-info"/></svg></span>
                                            <div class="transfer-gate-copy">
                                                <strong>Havale açıklamasına referans numaranı ekle</strong>
                                                <p>Ödeme bildiriminin otomatik eşleşip bakiyenin daha hızlı onaylanması için açıklama alanına referans kodunu yaz.</p>
                                            </div>
                                            <button class="transfer-reveal" id="transfer-reveal" type="button" onclick="dismissTransferGate()">Tamam, devam et <span aria-hidden="true">→</span></button>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Mobil Ödeme -->
                            <section class="payment-pane" id="pane-mobile" role="tabpanel" aria-labelledby="method-mobile" hidden style="display: none;">
                                <h3 class="subheading"><svg class="icon"><use href="#i-mobile"/></svg>Mobil ödeme bilgileri</h3>
                                <div class="phone-verified">
                                    <div>
                                        <label>Onaylı telefon numarası</label>
                                        <strong><?= htmlspecialchars($userSession['user_phone'] ?? 'Eklenmemiş') ?></strong>
                                    </div>
                                    <?php if(isset($user_details->user_phone_is_verified) &&$user_details->user_phone_is_verified == 1): ?>
                                        <span class="verified-badge"><svg class="icon"><use href="#i-check"/></svg>Onaylı</span>
                                    <?php else: ?>
                                        <span class="verified-badge" style="color:#ffad65"><svg class="icon"><use href="#i-info"/></svg>Onaysız</span>
                                    <?php endif; ?>
                                </div>
                                <div class="subsection">
                                    <h3 class="subheading"><svg class="icon"><use href="#i-bolt"/></svg>Hızlı tutar seçimi</h3>
                                    <div class="amount-grid" data-presets="mobile">
                                        <button class="amount-preset" type="button" data-amount="50" aria-pressed="false">50 ₺</button>
                                        <button class="amount-preset" type="button" data-amount="100" aria-pressed="false">100 ₺</button>
                                        <button class="amount-preset" type="button" data-amount="250" aria-pressed="false">250 ₺</button>
                                        <button class="amount-preset" type="button" data-amount="500" aria-pressed="false">500 ₺</button>
                                        <button class="amount-preset popular-preset" type="button" data-amount="750" aria-pressed="false" aria-label="750 TL, çok tercih edilen">750 ₺</button>
                                        <button class="amount-preset" type="button" data-amount="1000" aria-pressed="false">1.000 ₺</button>
                                    </div>
                                </div>
                                <div class="subsection">
                                    <h3 class="subheading"><svg class="icon"><use href="#i-edit"/></svg>Yüklenecek tutar</h3>
                                    <div class="amount-field">
                                        <label class="sr-only" for="amount-mobile">Mobil ödemeyle yüklenecek tutar</label>
                                        <input id="amount-mobile" type="number" step="1" inputmode="decimal" placeholder="Tutar gir" data-amount-input="mobile">
                                        <span class="currency">₺</span>
                                    </div>
                                </div>
                                <p class="amount-hint">Mobil ödeme sağlayıcısının %60 işlem ücreti toplam tutara eklenir.</p>
                                <div class="payment-summary">
                                    <div class="summary-head"><svg class="icon"><use href="#i-receipt"/></svg>Ödeme özeti</div>
                                    <div class="summary-rows">
                                        <div class="summary-row"><span>Yüklenecek tutar</span><strong data-summary-amount="mobile">0,00 ₺</strong></div>
                                        <div class="summary-row"><span>İşlem ücreti (%60)</span><strong class="commission" data-summary-fee="mobile">0,00 ₺</strong></div>
                                    </div>
                                    <div class="summary-total"><span>Toplam ödenecek</span><strong data-summary-total="mobile">0,00 ₺</strong></div>
                                </div>
                                <?php if(isset($user_details->user_phone_is_verified) &&$user_details->user_phone_is_verified == 1): ?>
                                    <button class="wallet-submit" type="button" data-submit-method="mobile">
                                        <svg class="icon"><use href="#i-mobile"/></svg>Mobil ödemeyi başlat<svg class="icon"><use href="#i-arrow"/></svg>
                                    </button>
                                <?php else: ?>
                                    <button class="wallet-submit" type="button" disabled style="opacity:0.5;cursor:not-allowed">
                                        <svg class="icon"><use href="#i-mobile"/></svg>Telefon numaranızı onaylayın<svg class="icon"><use href="#i-arrow"/></svg>
                                    </button>
                                <?php endif; ?>
                                <p class="action-note" data-action-note="mobile" aria-live="polite"></p>
                            </section>
                        </div>
                    </div>
                    <div class="wallet-safe-note"><svg class="icon"><use href="#i-lock"/></svg><span>Ödemeler güvenli ödeme kuruluşu üzerinden gerçekleşir. Kart bilgileriniz Playsel’de kayıtlı tutulmaz.</span></div>
                </section>
            </div>
        </section>
    </section>
</main>
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
window.transferGateDismissed = (function() {
    try {
        return localStorage.getItem('playsel_transfer_gate_dismissed') === '1' ||
               localStorage.getItem('playsel_wallet_transfer_dismissed') === '1';
    } catch(e) {
        return false;
    }
})();

window.dismissTransferGate = function(e) {
    if(e && e.preventDefault) e.preventDefault();
    window.transferGateDismissed = true;
    try {
        localStorage.setItem('playsel_transfer_gate_dismissed', '1');
        localStorage.setItem('playsel_wallet_transfer_dismissed', '1');
    } catch(err) {}
    const stage = document.querySelector("#transfer-stage");
    const content = document.querySelector("#transfer-content");
    const gate = document.querySelector(".transfer-gate");
    if(gate) gate.style.setProperty("display", "none", "important");
    if(stage) stage.classList.remove("is-locked");
    if(content) {
        content.inert = false;
        content.style.filter = "none";
        content.style.opacity = "1";
    }
    const bankChoice = document.querySelector(".bank-choice[aria-pressed='true']") || document.querySelector(".bank-choice");
    if(bankChoice) bankChoice.focus();
};

document.addEventListener("DOMContentLoaded", function() {
    if (window.transferGateDismissed) {
        const stage = document.querySelector("#transfer-stage");
        const content = document.querySelector("#transfer-content");
        const gate = document.querySelector(".transfer-gate");
        if(gate) gate.style.setProperty("display", "none", "important");
        if(stage) stage.classList.remove("is-locked");
        if(content) {
            content.inert = false;
            content.style.filter = "none";
            content.style.opacity = "1";
        }
    }
});

(function() {
function initWallet() {
    const fees = { card: 2.7, transfer: 0, mobile: 60 };
    let activeMethod = "card";
    let timer;
    const money = value => new Intl.NumberFormat("tr-TR", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + " ₺";
    const amountInput = method => document.querySelector(`[data-amount-input="${method}"]`);
    
    const cardPreview = document.querySelector("#bank-card-preview");
    const updateCardPreview = () => {
        const holder = document.querySelector("#card-holder").value.trim();
        const digits = document.querySelector("#card-number").value.replace(/\D/g, "").slice(0, 19);
        const expiry = document.querySelector("#card-expiry").value.trim();
        const cvcLength = document.querySelector("#card-cvc").value.length;
        document.querySelector("#preview-card-holder").textContent = holder ? holder.toLocaleUpperCase("tr-TR") : "AD SOYAD";
        document.querySelector("#preview-card-number").textContent = digits ? digits.replace(/(.{4})/g, "$1 ").trim() : "•••• •••• •••• ••••";
        document.querySelector("#preview-card-expiry").textContent = expiry || "AA / YY";
        document.querySelector("#preview-card-cvc").textContent = cvcLength ? "•".repeat(Math.min(cvcLength, 4)) : "•••";
    };
    
    ["card-holder", "card-number", "card-expiry", "card-cvc"].forEach(id => {
        ["input", "change"].forEach(type => {
            const el = document.querySelector(`#${id}`);
            if(el) el.addEventListener(type, updateCardPreview);
        });
    });
    
    const cvcEl = document.querySelector("#card-cvc");
    if(cvcEl){
        cvcEl.addEventListener("focus", () => cardPreview.classList.add("is-back"));
        cvcEl.addEventListener("blur", () => cardPreview.classList.remove("is-back"));
    }
    
    const cardForm = document.querySelector("#wallet-card-form");
    if(cardForm) cardForm.addEventListener("submit", event => event.preventDefault());

    function updateStep(){
        const amount = Number(amountInput(activeMethod).value) || 0;
        const chosen = amount > 0;
        document.querySelector("#step-method").classList.add("done");
        document.querySelector("#step-amount").classList.toggle("current", !chosen);
        document.querySelector("#step-pay").classList.toggle("current", chosen);
    }
    
    function updateSummary(method){
        const amount = Math.max(0, Number(amountInput(method).value) || 0);
        const fee = amount * fees[method] / 100;
        document.querySelector(`[data-summary-amount="${method}"]`).textContent = money(amount);
        document.querySelector(`[data-summary-fee="${method}"]`).textContent = money(fee);
        document.querySelector(`[data-summary-total="${method}"]`).textContent = money(amount + fee);
        document.querySelectorAll(`[data-presets="${method}"] .amount-preset`).forEach(button => button.setAttribute("aria-pressed", String(Number(button.dataset.amount) === amount)));
        updateStep();
    }
    
    function chooseMethod(method){
        activeMethod = method;
        document.querySelectorAll(".method-option").forEach(button => {
            const selected = button.dataset.method === method;
            button.setAttribute("aria-selected", String(selected));
            button.tabIndex = selected ? 0 : -1;
        });
        document.querySelectorAll(".payment-pane").forEach(pane => {
            const isMatch = pane.id === "pane-" + method;
            pane.hidden = !isMatch;
            pane.style.display = isMatch ? "block" : "none";
        });
        
        const transferLocked = method === "transfer" && !window.transferGateDismissed;
        const transferStage = document.querySelector("#transfer-stage");
        const transferContent = document.querySelector("#transfer-content");
        if(transferStage) transferStage.classList.toggle("is-locked", transferLocked);
        if(transferContent) transferContent.inert = transferLocked;
        
        updateSummary(method);
    }
    
    const transferReveal = document.querySelector("#transfer-reveal");
    if(transferReveal){
        transferReveal.addEventListener("click", window.dismissTransferGate);
    }
    
    document.querySelectorAll(".method-option").forEach((button, index, buttons) => {
        button.addEventListener("click", () => chooseMethod(button.dataset.method));
        button.addEventListener("keydown", event => {
            if(!["ArrowRight", "ArrowLeft", "Home", "End"].includes(event.key)) return;
            event.preventDefault(); 
            let next = index;
            if(event.key === "ArrowRight") next = (index + 1) % buttons.length;
            if(event.key === "ArrowLeft") next = (index - 1 + buttons.length) % buttons.length;
            if(event.key === "Home") next = 0; 
            if(event.key === "End") next = buttons.length - 1;
            buttons[next].focus(); 
            chooseMethod(buttons[next].dataset.method);
        });
    });
    
    document.querySelectorAll("[data-presets] .amount-preset").forEach(button => {
        button.addEventListener("click", () => {
            const method = button.closest("[data-presets]").dataset.presets;
            amountInput(method).value = button.dataset.amount;
            updateSummary(method);
        });
    });
    
    document.querySelectorAll("[data-amount-input]").forEach(input => {
        input.addEventListener("input", () => updateSummary(input.dataset.amountInput));
    });
    
    const bankDataMap = <?= !empty($bankMap) ? json_encode($bankMap, JSON_UNESCAPED_UNICODE) : '{}' ?>;
    
    document.querySelectorAll(".bank-choice").forEach(button => {
        button.addEventListener("click", () => {
            document.querySelectorAll(".bank-choice").forEach(item => item.setAttribute("aria-pressed", String(item === button)));
            
            const bankId = button.dataset.bankId;
            const b = bankDataMap[bankId] || {};
            
            const selectedBankEl = document.querySelector("#selected-bank");
            const selectedBankOwnerEl = document.querySelector("#selected-bank-owner");
            const selectedBankIbanEl = document.querySelector("#selected-bank-iban");
            const selectedBankAccountEl = document.querySelector("#selected-bank-account");
            const bankLogoMiniEl = document.querySelector("#mini-bank-logo");

            if(selectedBankEl) selectedBankEl.textContent = b.name || "-";
            if(selectedBankOwnerEl) selectedBankOwnerEl.textContent = b.owner || "-";
            if(selectedBankIbanEl) selectedBankIbanEl.textContent = b.iban || "-";
            if(selectedBankAccountEl) selectedBankAccountEl.textContent = b.account || "-";
            if(bankLogoMiniEl) bankLogoMiniEl.textContent = (b.name || "XX").slice(0, 2).toUpperCase();
            
            document.querySelectorAll("[data-copy-value]").forEach(btn => {
                if(btn.getAttribute("aria-label") === "Hesap sahibini kopyala") btn.dataset.copyValue = b.owner || "";
                if(btn.getAttribute("aria-label") === "IBAN kopyala") btn.dataset.copyValue = b.iban || "";
                if(btn.getAttribute("aria-label") === "Hesap numarası kopyala") btn.dataset.copyValue = b.account || "";
            });
        });
    });
    
    function toast(message, type = 'info'){
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
            return;
        }
        if (typeof toastr !== 'undefined') {
            if (type === 'error') toastr.error(message);
            else if (type === 'success') toastr.success(message);
            else toastr.info(message);
            return;
        }
        const element = document.querySelector("#toast");
        if(element){
            element.textContent = message; 
            element.classList.add("show");
            clearTimeout(timer);
            timer = setTimeout(() => element.classList.remove("show"), 2800);
        } else {
            alert(message);
        }
    }
    
    document.querySelectorAll("[data-copy], [data-copy-value]").forEach(button => {
        button.addEventListener("click", async () => {
            const copyElement = document.getElementById(button.dataset.copy);
            const value = button.dataset.copyValue || (copyElement ? copyElement.textContent.trim() : "");
            try { 
                await navigator.clipboard.writeText(value); 
                toast("Kopyalandı."); 
            } catch { 
                toast("Kopyalama bu tarayıcıda kullanılamıyor."); 
            }
        });
    });
    
    document.querySelectorAll("[data-submit-method]").forEach(button => {
        button.addEventListener("click", () => {
            const method = button.dataset.submitMethod; 
            const amount = Number(amountInput(method).value) || 0;
            const actionNoteEl = document.querySelector(`[data-action-note="${method}"]`);
            
            if(amount <= 0){
                if(actionNoteEl) actionNoteEl.textContent = "Lütfen yüklemek istediğin tutarı gir.";
                const inputField = amountInput(method);
                if(inputField) inputField.focus();
                return;
            }
            if(actionNoteEl) actionNoteEl.textContent = "";
            
            const payload = {
                method: method === "card" ? "cc" : (method === "transfer" ? "eft" : method),
                amount: amount.toFixed(2),
                csrf_token: "<?= $this->security->get_csrf_hash() ?>"
            };
            
            if(method === "card"){
                payload.cc_name = document.querySelector("#card-holder").value.trim();
                payload.cc_number = document.querySelector("#card-number").value.replace(/\D/g, "");
                payload.cc_expiry = document.querySelector("#card-expiry").value.replace(/\s/g, "");
                payload.cc_cvv = document.querySelector("#card-cvc").value.trim();
                if(!payload.cc_name || payload.cc_number.length !== 16 || !/^\d{2}\/\d{2}$/.test(payload.cc_expiry) || !/^\d{3,4}$/.test(payload.cc_cvv)){
                    if(actionNoteEl) actionNoteEl.textContent = "Kart bilgilerini eksiksiz ve doğru gir.";
                    return;
                }
            } else if(method === "transfer"){
                const bank = document.querySelector(".bank-choice[aria-pressed='true']");
                payload.bank_id = bank ? bank.dataset.bankId : "";
                if(!payload.bank_id){
                    if(actionNoteEl) actionNoteEl.textContent = "Banka bilgisi gereklidir.";
                    return;
                }
            } else {
                payload.phone_number = "<?= htmlspecialchars($userSession['user_phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>";
            }
            
            const buttonEl = document.querySelector(`[data-submit-method="${method}"]`);
            if(buttonEl) buttonEl.disabled = true;
            
            fetch("<?= topupcenter() . $this->uri->segment(1) ?>/payment/addBalance", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
                body: new URLSearchParams(payload)
            }).then(async response => {
                const result = await response.json();
                if(!response.ok || result.status === "error") throw new Error(result.message || "İşlem başlatılamadı.");
                if(result.status === "3d_redirect"){
                    if(result.redirect_url) window.location.href = result.redirect_url;
                    else { document.open(); document.write(result.html_content || ""); document.close(); }
                    return;
                }
                if(result.redirect_url) window.location.href = result.redirect_url;
                else toast(result.message || "İşlem başarıyla oluşturuldu.");
            }).catch(error => {
                if(actionNoteEl) actionNoteEl.textContent = error.message;
                if(buttonEl) buttonEl.disabled = false;
            });
        });
    });
    
    const cardNumberInput = document.querySelector("#card-number");
    const cardExpiryInput = document.querySelector("#card-expiry");
    const cardCvcInput = document.querySelector("#card-cvc");
    if(cardNumberInput) cardNumberInput.addEventListener("input", () => { const d = cardNumberInput.value.replace(/\D/g, "").slice(0, 19); cardNumberInput.value = d.replace(/(.{4})/g, "$1 ").trim(); });
    if(cardExpiryInput) cardExpiryInput.addEventListener("input", () => { const d = cardExpiryInput.value.replace(/\D/g, "").slice(0, 4); cardExpiryInput.value = d.length > 2 ? d.slice(0, 2) + " / " + d.slice(2) : d; });
    if(cardCvcInput) cardCvcInput.addEventListener("input", () => { cardCvcInput.value = cardCvcInput.value.replace(/\D/g, "").slice(0, 4); });
    
    chooseMethod("card");
    ["card", "transfer", "mobile"].forEach(updateSummary);
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initWallet);
} else {
    initWallet();
}
})();
</script>