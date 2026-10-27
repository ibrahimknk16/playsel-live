<?php
$type = $result_type ?? 'order';
$resultId = (int)($result_id ?? 0);
$resultToken = (string)($result_token ?? '');
$isOrder = $type === 'order';
$endpoint = topupcenter() . $uri . '/payment/resultStatus';
$orderUrl = topupcenter() . $uri . '/profile/orders';
$walletUrl = topupcenter() . $uri . '/profile/wallet';
$paymentsUrl = topupcenter() . $uri . '/profile/payments';
?>

<main class="main-content payment-result-page">
    <div class="payment-result-shell">
        <section class="payment-result-card" id="resultCard" data-type="<?= html_escape($type) ?>" data-id="<?= $resultId ?>">
            <div class="payment-stepper" aria-label="Ödeme adımları">
                <div class="payment-step is-complete"><span><i class="fa-solid fa-check"></i></span><strong>Ödeme</strong></div>
                <div class="payment-step-line"></div>
                <div class="payment-step is-active" id="stepProcessing"><span><i class="fa-solid fa-spinner"></i></span><strong>İşleniyor</strong></div>
                <div class="payment-step-line"></div>
                <div class="payment-step" id="stepResult"><span>3</span><strong>Sonuç</strong></div>
            </div>

            <div class="payment-result-icon is-processing" id="resultIcon"><i class="fa-solid fa-spinner"></i></div>
            <p class="payment-result-eyebrow" id="resultEyebrow"><?= $isOrder ? 'GÜVENLİ ÖDEME' : 'BAKİYE YÜKLEME' ?></p>
            <h1 id="resultTitle"><?= $isOrder ? 'Ödeme sonucu kontrol ediliyor' : 'Bakiye yüklemeniz kontrol ediliyor' ?></h1>
            <p class="payment-result-description" id="resultDescription"><?= $isOrder ? 'Ödeme sağlayıcısından gelen sonuç doğrulanıyor. Lütfen bu sayfayı kapatmayın.' : 'Bakiye yükleme ödemeniz doğrulanıyor. Bakiye, ödeme tamamen onaylandıktan sonra hesabınıza yansıtılır.' ?></p>

            <div class="payment-result-summary" id="resultSummary">
                <div class="payment-result-summary-row"><span>İşlem</span><strong id="resultReference">#<?= $resultId ?></strong></div>
                <div class="payment-result-summary-row"><span>Durum</span><strong id="resultStatusText">Kontrol ediliyor</strong></div>
                <div class="payment-result-summary-row"><span>Tutar</span><strong id="resultAmount">-</strong></div>
                <div class="payment-result-summary-row" id="resultPaymentMethod" hidden><span>Ödeme yöntemi</span><strong id="resultPaymentMethodText">-</strong></div>
                <div class="payment-result-summary-row" id="resultBankName" hidden><span>Banka</span><strong id="resultBankNameText">-</strong></div>
                <div class="payment-result-summary-row" id="resultBankOwner" hidden><span>Alıcı</span><strong id="resultBankOwnerText">-</strong></div>
                <div class="payment-result-summary-row" id="resultBankIban" hidden><span>IBAN</span><strong id="resultBankIbanText">-</strong></div>
                <div class="payment-result-summary-row" id="resultTransferReference" hidden><span>Havale referansı</span><strong id="resultTransferReferenceText">-</strong></div>
                <div class="payment-result-product" id="resultProduct" hidden>
                    <div class="payment-result-product-image"><img id="resultProductImage" alt=""></div>
                    <div><strong id="resultProductName">Dijital Ürün</strong><span>Sipariş özeti</span></div>
                </div>
                <div class="payment-result-parts" id="resultProductParts" hidden></div>
            </div>

            <div class="payment-result-alert" id="resultAlert" hidden></div>

            <div class="payment-result-actions">
                <a class="payment-result-primary" id="primaryAction" href="<?= html_escape($isOrder ? $orderUrl : $walletUrl) ?>"><i class="fa-solid fa-arrow-right"></i><span>Devam Et</span></a>
                <a class="payment-result-secondary" href="<?= html_escape($isOrder ? $orderUrl : $paymentsUrl) ?>"><i class="fa-solid fa-clock-rotate-left"></i><span><?= $isOrder ? 'Siparişlerime Git' : 'Ödeme Geçmişine Git' ?></span></a>
            </div>

            <div class="payment-result-faq" id="resultFaq">
                <h2><?= $isOrder ? 'Sipariş Sonrası Destek' : 'Bakiye Yükleme Desteği' ?></h2>
                <div class="payment-faq-item">
                    <button type="button" class="payment-faq-question" aria-expanded="false">
                        <span><?= $isOrder ? 'Siparişim ne zaman teslim edilir?' : 'Bakiye hesabıma ne zaman yansır?' ?></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="payment-faq-answer">
                        <p><?= $isOrder ? 'Ödeme onaylandıktan sonra dijital ürünler genellikle birkaç dakika içinde hazırlanır. Ürün kodu veya teslimat bilgisi hazır olduğunda sipariş detayında görünür.' : 'Kredi kartı ve mobil ödemeler callback onayından sonra otomatik olarak yansır. Havale işlemleri banka kontrolünden sonra onaylanır.' ?></p>
                    </div>
                </div>
                <div class="payment-faq-item">
                    <button type="button" class="payment-faq-question" aria-expanded="false">
                        <span><?= $isOrder ? 'İşlem beklemede kalırsa ne yapmalıyım?' : 'Ödeme yaptım fakat bakiye görünmüyor, ne yapmalıyım?' ?></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="payment-faq-answer">
                        <p>Sayfayı yenilemeden önce birkaç dakika bekleyin. İşlem geçmişinizden durumu kontrol edebilir, devam eden sorunlarda destek ekibimize sipariş veya işlem numaranızla ulaşabilirsiniz.</p>
                    </div>
                </div>
                <div class="payment-faq-item">
                    <button type="button" class="payment-faq-question" aria-expanded="false">
                        <span>Faturama veya işlem detaylarıma nereden ulaşabilirim?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="payment-faq-answer">
                        <p><?= $isOrder ? 'Sipariş detayınızda ürün, ödeme yöntemi ve teslimat bilgilerini görebilirsiniz. Faturanıza profilinizdeki ilgili işlem alanından ulaşabilirsiniz.' : 'Bakiye hareketlerinizi Cüzdanım ve Ödemelerim sayfalarından takip edebilirsiniz.' ?></p>
                    </div>
                </div>
            </div>

            <p class="payment-result-security"><i class="fa-solid fa-shield-halved"></i> Bilgileriniz güvenli bağlantı üzerinden işlenir.</p>
        </section>
    </div>
</main>

<style>
    .payment-result-page { padding: 48px 20px 72px; }
    .payment-result-shell { width: 100%; max-width: 620px; margin: 0 auto; }
    .payment-result-card { position: relative; overflow: hidden; padding: 36px; border: 1px solid rgba(255,255,255,.1); border-radius: 16px; background: linear-gradient(145deg, #1e2430, #171e2c); box-shadow: 0 18px 55px rgba(0,0,0,.28); text-align: center; }
    .payment-result-card:before { content: ''; position: absolute; inset: 0 0 auto; height: 3px; background: var(--accent, #fab316); }
    .payment-stepper { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 38px; }
    .payment-step { display: flex; align-items: center; gap: 7px; color: rgba(255,255,255,.46); font-size: 12px; white-space: nowrap; }
    .payment-step span { display: grid; place-items: center; width: 28px; height: 28px; border: 1px solid rgba(255,255,255,.16); border-radius: 50%; font-size: 12px; }
    .payment-step.is-complete { color: rgba(255,255,255,.46); }
    .payment-step.is-complete span { border-color: rgba(255,255,255,.16); }
    .payment-step.is-active { color: var(--accent, #fab316); }
    .payment-step.is-active span { border-color: var(--accent, #fab316); }
    .payment-step.is-active i { animation: payment-spin 1.4s linear infinite; }
    .payment-step.is-result { color: #39c985; }
    .payment-step.is-result span { border-color: #39c985; background: #39c985; color: #111; }
    .payment-step-line { width: 32px; height: 1px; background: rgba(255,255,255,.15); }
    .payment-result-icon { display: grid; place-items: center; width: 78px; height: 78px; margin: 0 auto 20px; border-radius: 50%; font-size: 30px; color: var(--accent, #fab316); background: rgba(250,179,22,.12); box-shadow: 0 0 32px rgba(250,179,22,.12); }
    .payment-result-icon.is-success { color: #39c985; background: rgba(57,201,133,.12); box-shadow: 0 0 32px rgba(57,201,133,.14); }
    .payment-result-icon.is-error { color: #f06b63; background: rgba(240,107,99,.12); box-shadow: 0 0 32px rgba(240,107,99,.12); }
    .payment-result-icon.is-processing i { animation: payment-spin 1.1s linear infinite; }
    .payment-result-eyebrow { margin: 0 0 8px; color: var(--accent, #fab316); font-size: 11px; font-weight: 700; letter-spacing: 1.5px; }
    #resultTitle { margin: 0 0 10px; color: #fff; font-size: 28px; line-height: 1.2; }
    .payment-result-description { max-width: 470px; margin: 0 auto 26px; color: rgba(255,255,255,.66); font-size: 14px; line-height: 1.65; }
    .payment-result-summary { margin: 0 0 20px; padding: 5px 18px 18px; border: 1px solid rgba(255,255,255,.08); border-radius: 10px; background: rgba(0,0,0,.16); text-align: left; }
    .payment-result-summary-row { display: flex; justify-content: space-between; gap: 20px; padding: 13px 0; border-bottom: 1px solid rgba(255,255,255,.07); color: rgba(255,255,255,.56); font-size: 13px; }
    .payment-result-summary-row:last-of-type { border-bottom: 0; }
    .payment-result-summary-row strong { color: #fff; text-align: right; }
    .payment-result-product { display: flex; align-items: center; gap: 12px; margin-top: 12px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,.07); }
    .payment-result-product-image { display: grid; place-items: center; width: 48px; height: 48px; overflow: hidden; border-radius: 8px; background: rgba(255,255,255,.06); }
    .payment-result-product-image img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .payment-result-product strong, .payment-result-product span { display: block; }
    .payment-result-product strong { color: #fff; font-size: 14px; }
    .payment-result-product span { margin-top: 3px; color: rgba(255,255,255,.48); font-size: 12px; }
    .payment-result-alert { margin: 0 0 20px; padding: 13px 15px; border: 1px solid rgba(240,107,99,.25); border-radius: 8px; background: rgba(240,107,99,.1); color: #ffaaa4; font-size: 13px; line-height: 1.5; text-align: left; }
    .payment-result-actions { display: grid; gap: 10px; }
    .payment-result-actions a { display: flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; transition: transform .2s, background .2s; }
    .payment-result-primary { background: var(--accent, #fab316); color: #141414; }
    .payment-result-primary:hover { background: #e0b55c; transform: translateY(-1px); }
    .payment-result-secondary { border: 1px solid rgba(255,255,255,.12); color: rgba(255,255,255,.78); }
    .payment-result-secondary:hover { background: rgba(255,255,255,.06); }
    .payment-result-faq { margin-top: 30px; text-align: left; }
    .payment-result-faq h2 { margin: 0 0 14px; color: rgba(255,255,255,.72); font-size: 16px; text-align: center; }
    .payment-faq-item { overflow: hidden; margin-bottom: 8px; border: 1px solid rgba(255,255,255,.08); border-radius: 8px; background: rgba(255,255,255,.025); }
    .payment-faq-question { display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; padding: 14px 15px; border: 0; background: transparent; color: rgba(255,255,255,.82); font: inherit; font-size: 13px; text-align: left; cursor: pointer; }
    .payment-faq-question i { color: rgba(255,255,255,.45); font-size: 12px; transition: transform .2s; }
    .payment-faq-answer { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .25s ease; }
    .payment-faq-answer p { overflow: hidden; margin: 0; padding: 0 15px; color: rgba(255,255,255,.55); font-size: 12px; line-height: 1.6; }
    .payment-faq-item.is-open .payment-faq-answer { grid-template-rows: 1fr; }
    .payment-faq-item.is-open .payment-faq-answer p { padding-bottom: 14px; }
    .payment-faq-item.is-open .payment-faq-question i { transform: rotate(180deg); }
    .payment-result-security { margin: 24px 0 0; color: rgba(255,255,255,.38); font-size: 12px; }
    .payment-result-security i { margin-right: 5px; color: #39c985; }
    .payment-result-parts { display: grid; gap: 7px; margin-top: 12px; text-align: left; }
    .payment-result-part { display: flex; justify-content: space-between; gap: 12px; padding: 9px 11px; border: 1px solid rgba(255,255,255,.08); border-radius: 8px; color: rgba(255,255,255,.78); font-size: 12px; }
    .payment-result-part strong { color: #fff; white-space: nowrap; }
    @keyframes payment-spin { to { transform: rotate(360deg); } }
    @media (max-width: 560px) { .payment-result-page { padding: 24px 12px 48px; } .payment-result-card { padding: 28px 18px; } .payment-stepper { gap: 6px; } .payment-step strong { display: none; } .payment-step-line { width: 24px; } #resultTitle { font-size: 24px; } }
</style>

<script>
(function () {
    const card = document.getElementById('resultCard');
    let type = card.dataset.type;
    const id = card.dataset.id;
    const statusUrl = <?= json_encode($endpoint) ?>;
    const resultToken = <?= json_encode($resultToken) ?>;
    const orderUrl = <?= json_encode($orderUrl) ?>;
    const walletUrl = <?= json_encode($walletUrl) ?>;
    const icon = document.getElementById('resultIcon');
    const eyebrow = document.getElementById('resultEyebrow');
    const title = document.getElementById('resultTitle');
    const description = document.getElementById('resultDescription');
    const statusText = document.getElementById('resultStatusText');
    const amount = document.getElementById('resultAmount');
    const paymentMethod = document.getElementById('resultPaymentMethod');
    const paymentMethodText = document.getElementById('resultPaymentMethodText');
    const bankName = document.getElementById('resultBankName');
    const bankNameText = document.getElementById('resultBankNameText');
    const bankOwner = document.getElementById('resultBankOwner');
    const bankOwnerText = document.getElementById('resultBankOwnerText');
    const bankIban = document.getElementById('resultBankIban');
    const bankIbanText = document.getElementById('resultBankIbanText');
    function showTransferInfo(data) {
        const isTransfer = data.payment_method === 'eft';
        bankName.hidden = !isTransfer || !data.bank_name;
        bankNameText.textContent = data.bank_name || '-';
        bankOwner.hidden = !isTransfer || !data.bank_owner;
        bankOwnerText.textContent = data.bank_owner || '-';
        bankIban.hidden = !isTransfer || !data.bank_iban;
        bankIbanText.textContent = data.bank_iban || '-';
        transferReference.hidden = !isTransfer || !data.transfer_reference;
        transferReferenceText.textContent = data.transfer_reference || '-';
    }
    const transferReference = document.getElementById('resultTransferReference');
    const transferReferenceText = document.getElementById('resultTransferReferenceText');
    const alertBox = document.getElementById('resultAlert');
    const primary = document.getElementById('primaryAction');
    const resultStep = document.getElementById('stepResult');
    const processingStep = document.getElementById('stepProcessing');
    const product = document.getElementById('resultProduct');
    const productName = document.getElementById('resultProductName');
    const productImage = document.getElementById('resultProductImage');
    const productParts = document.getElementById('resultProductParts');
    const faqQuestions = document.querySelectorAll('.payment-faq-question');
    let attempts = 0;

    function applyEntity(entity) {
        if (entity !== 'order' && entity !== 'balance') return;

        type = entity;
        card.dataset.type = entity;
        if (entity === 'balance') {
            product.hidden = true;
            product.style.display = 'none';
            document.querySelector('.payment-result-faq h2').textContent = 'Bakiye Yükleme Desteği';
            faqQuestions[0].querySelector('span').textContent = 'Bakiye hesabıma ne zaman yansır?';
            faqQuestions[1].querySelector('span').textContent = 'Ödeme yaptım fakat bakiye görünmüyor, ne yapmalıyım?';
            faqQuestions[2].querySelector('span').textContent = 'Faturama veya işlem detaylarıma nereden ulaşabilirim?';
        } else {
            product.hidden = false;
            product.style.display = '';
        }
    }

    faqQuestions.forEach(function (question) {
        question.addEventListener('click', function () {
            const item = question.closest('.payment-faq-item');
            const isOpen = item.classList.toggle('is-open');
            question.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

    });

    function showLookupError(message) {
        icon.className = 'payment-result-icon is-error';
        icon.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i>';
        resultStep.classList.add('is-result');
        processingStep.classList.remove('is-active');
        eyebrow.textContent = 'SONUÇ BEKLENİYOR';
        title.textContent = 'Ödeme sonucu kontrol ediliyor';
        description.textContent = message || 'Ödeme kaydı doğrulanıyor. Lütfen bu sayfayı kapatmayın.';
        statusText.textContent = 'Kontrol ediliyor';
        icon.className = 'payment-result-icon is-processing';
        icon.innerHTML = '<i class="fa-solid fa-spinner"></i>';
        resultStep.classList.remove('is-result');
        processingStep.classList.add('is-active');
        alertBox.textContent = 'Provider yanıtı alınamadı. Sistem otomatik olarak tekrar deniyor.';
        alertBox.hidden = false;
        primary.href = type === 'order' ? orderUrl : walletUrl;
        primary.querySelector('span').textContent = type === 'order' ? 'Siparişlerime Git' : 'Cüzdanıma Git';
    }

    function setState(kind, data) {
        const orderStatus = Number(data.order_status);
        const balanceStatus = Number(data.balance_status);
        const isPending = type === 'order' ? [0, 3, 4, 9, 11, 13, 14, 15].includes(orderStatus) : balanceStatus === 0;
        const isSuccess = type === 'order' ? orderStatus === 1 : balanceStatus === 1;
        const isPaymentApproved = type === 'order' && orderStatus === 0;
        const isCancelled = type === 'order' && [2, 5].includes(orderStatus);
        const isError = type === 'order' ? orderStatus === 12 : balanceStatus === 2;

        icon.className = 'payment-result-icon ' + (isSuccess ? 'is-success' : (isCancelled || isError) ? 'is-error' : 'is-processing');
        icon.innerHTML = '<i class="fa-solid ' + (isSuccess ? 'fa-check' : (isCancelled || isError) ? 'fa-xmark' : 'fa-spinner') + '"></i>';
        resultStep.classList.toggle('is-result', isSuccess || isCancelled || isError);
        processingStep.classList.toggle('is-active', isPending);
        statusText.textContent = isSuccess ? 'Teslim Edildi' : isPaymentApproved ? 'Ödeme Onaylandı - Teslimat Bekleniyor' : isCancelled ? 'Sipariş İptal Edildi' : isError ? 'Başarısız' : 'İşleniyor';
        amount.textContent = data.amount ? data.amount + ' ' + (data.currency === 'TRY' ? 'TL' : (data.currency || 'TL')) : '-';

        if (type === 'order') {
            paymentMethod.hidden = !data.payment_method_label;
            paymentMethodText.textContent = data.payment_method_label || '-';
            showTransferInfo(data);
            product.hidden = false;
            product.style.display = '';
            productName.textContent = data.product_name || 'Dijital Ürün';
            const items = Array.isArray(data.items) ? data.items : [];
            productParts.hidden = items.length <= 1;
            productParts.replaceChildren();
            if (items.length > 1) {
                const heading = document.createElement('strong');
                heading.textContent = Number(data.part_count || items.length) + ' parçalı sipariş';
                heading.style.cssText = 'display:block;margin-bottom:2px;';
                productParts.appendChild(heading);
                items.forEach(function (item) {
                    const part = document.createElement('div');
                    part.className = 'payment-result-part';
                    const name = document.createElement('span');
                    name.textContent = item.name || 'Dijital Ürün';
                    const price = document.createElement('strong');
                    price.textContent = (item.amount || '-') + ' ' + (item.currency === 'TRY' ? 'TL' : (item.currency || 'TL'));
                    part.append(name, price);
                    productParts.appendChild(part);
                });
            }
            if (data.product_icon) {
                productImage.src = <?= json_encode(getenv('CDN_URL')) ?> + data.product_icon;
            } else {
                productImage.hidden = true;
            }
        } else {
            paymentMethod.hidden = !data.payment_method_label;
            paymentMethodText.textContent = data.payment_method_label || 'Bakiye yükleme';
            showTransferInfo(data);
        }

        if (isSuccess) {
            eyebrow.textContent = type === 'order' ? 'SİPARİŞ TAMAMLANDI' : 'BAKİYE YÜKLEME TAMAMLANDI';
            title.textContent = type === 'order' ? (data.has_voucher ? 'Ürününüz Teslim Edildi' : 'Siparişiniz Tamamlandı') : 'Bakiyeniz Yüklendi';
            description.textContent = type === 'order' ? (data.has_voucher ? 'Dijital kodunuz sipariş detayında görüntülenebilir.' : 'Siparişiniz tedarikçi tarafından başarıyla teslim edildi.') : 'Onaylanan tutar harcanabilir bakiyenize eklendi.';
            primary.href = type === 'order' ? orderUrl.replace('/profile/orders', '/profile/order-detail/' + id) : walletUrl;
            primary.querySelector('span').textContent = type === 'order' ? 'Sipariş Detayına Git' : 'Cüzdanıma Git';
            alertBox.hidden = true;
            return false;
        }

        if (isPaymentApproved) {
            eyebrow.textContent = 'ÖDEME ONAYLANDI';
            title.textContent = 'Siparişiniz Hazırlanıyor';
            description.textContent = 'Ödemeniz onaylandı. Siparişiniz tedarikçiye gönderilmek üzere sırada.';
            primary.href = orderUrl.replace('/profile/orders', '/profile/order-detail/' + id);
            primary.querySelector('span').textContent = 'Sipariş Detayına Git';
            alertBox.hidden = true;
            return true;
        }

        if (isCancelled) {
            eyebrow.textContent = 'SİPARİŞ SONUCU';
            title.textContent = 'Sipariş İptal Edildi';
            description.textContent = 'Siparişiniz iptal edildi ve teslimat yapılmayacaktır.';
            alertBox.textContent = data.cancellation_reason || 'İptal nedeni destek ekibi tarafından kaydedilmemiş.';
            alertBox.hidden = false;
            primary.href = orderUrl.replace('/profile/orders', '/profile/order-detail/' + id);
            primary.querySelector('span').textContent = 'Sipariş Detayını Gör';
            return false;
        }

        if (isError) {
            eyebrow.textContent = type === 'order' ? 'ÖDEME SONUCU' : 'BAKİYE YÜKLEME SONUCU';
            title.textContent = type === 'order' ? 'Ödeme Tamamlanamadı' : 'Bakiye Yüklenemedi';
            description.textContent = type === 'order' ? 'Ödeme sağlayıcısı işlemi tamamlayamadı. Sipariş detayından destek alabilir veya farklı bir ödeme yöntemi deneyebilirsiniz.' : 'Ödeme işlemi başarısız oldu. Tutar hesabınıza eklenmedi.';
            alertBox.textContent = type === 'order' ? 'Siparişiniz için herhangi bir teslimat yapılmayacaktır. İşlem hesabınızdan çekildiyse bankanızla veya destek ekibimizle iletişime geçin.' : 'Ödeme hesabınızdan çekildiyse işlem sağlayıcı tarafından otomatik olarak iade edilebilir.';
            alertBox.hidden = false;
            primary.href = type === 'order' ? orderUrl.replace('/profile/orders', '/profile/order-detail/' + id) : walletUrl;
            primary.querySelector('span').textContent = type === 'order' ? 'Sipariş Detayını Gör' : 'Cüzdana Dön';
            return false;
        }

        eyebrow.textContent = 'İŞLEM DEVAM EDİYOR';
        title.textContent = type === 'order' && orderStatus === 11 ? 'Ödeme Sonucu Bekleniyor' : 'Siparişiniz İşleniyor';
        description.textContent = 'Lütfen bekleyin. İşleminiz tamamlandığında bu ekran otomatik olarak güncellenecektir.';
        primary.href = type === 'order' ? orderUrl.replace('/profile/orders', '/profile/order-detail/' + id) : walletUrl;
        primary.querySelector('span').textContent = type === 'order' ? 'Sipariş Detayına Git' : 'Cüzdana Dön';
        return true;
    }

    function loadStatus() {
        fetch(statusUrl + '?token=' + encodeURIComponent(resultToken), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (response) {
                if (!response.ok) {
                    if (response.status >= 500) throw new Error('temporary_failure');
                    throw new Error('lookup_failed');
                }
                return response.json();
            })
            .then(function (payload) {
                if (payload.status !== 'success') {
                    throw new Error(payload.message || 'Sonuç alınamadı.');
                }
                applyEntity(payload.entity);
                const shouldPoll = setState('loaded', payload.data || {});
                attempts += 1;
                if (shouldPoll && attempts < 150) window.setTimeout(loadStatus, 4000);
            })
            .catch(function (error) {
                attempts += 1;
                showLookupError();
                window.setTimeout(loadStatus, attempts < 150 ? 5000 : 15000);
            });
    }

    loadStatus();
})();
</script>
