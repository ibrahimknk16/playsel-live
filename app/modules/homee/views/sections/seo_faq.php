<style>
    .playsell-widget-area {
        background-color: #11151d;
        padding: 40px 15px;
        color: #d1d5db;
    }

    .playsell-container {
        max-width: 1200px;
        margin: 0 auto;
        overflow-x: hidden;
    }

    /* =========================================
       1. SEO ALANI
       ========================================= */
    .seo-box {
        background-color: var(--panel-bg, #1e1e24);
        padding: 30px;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.05);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        margin-bottom: 25px;
    }

    .seo-content-wrapper {
        position: relative;
        max-height: 145px;
        overflow: hidden;
        transition: max-height 0.6s ease;
    }

    .seo-content-wrapper.expanded {
        max-height: 4000px;
    }

    .seo-blur-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 75px;
        background: linear-gradient(to bottom, rgba(30, 30, 36, 0) 0%, rgba(30, 30, 36, 0.8) 60%, rgba(30, 30, 36, 1) 100%);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 1) 60%);
        mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 1) 60%);
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .seo-content-wrapper.expanded .seo-blur-overlay {
        opacity: 0;
    }

    .seo-box h3 {
        color: #ffffff;
        font-size: 19px;
        font-weight: 700;
        margin-top: 30px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        position: relative;
        padding-left: 14px;
        letter-spacing: -0.2px;
    }

    .seo-box h3:first-child {
        margin-top: 0;
    }

    .seo-box h3::before {
        content: '';
        position: absolute;
        left: 0;
        top: 3px;
        bottom: 3px;
        width: 4px;
        background: #ffb300;
        border-radius: 2px;
        box-shadow: 0 0 10px rgba(255, 179, 0, 0.4);
    }

    .seo-box p {
        line-height: 1.7;
        margin-bottom: 16px;
        font-size: 14.5px;
        color: #9ca3af;
    }

    .seo-box p:first-of-type {
        margin-top: 8px;
    }

    .seo-box ul {
        list-style-type: disc;
        padding-left: 20px;
        margin-bottom: 20px;
    }

    .seo-box ul li {
        margin-bottom: 8px;
        font-size: 14.5px;
        color: #9ca3af;
    }

    /* SEO Responsive Tablolar */
    .seo-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 18px 0 26px 0;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.02);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .seo-table-responsive table {
        width: 100%;
        min-width: 520px;
        border-collapse: collapse;
        text-align: left;
    }

    .seo-table-responsive thead th,
    .seo-table-responsive tr:first-child th {
        background: linear-gradient(90deg, rgba(255, 179, 0, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
        color: #ffb300;
        font-size: 12.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 13px 18px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        white-space: nowrap;
    }

    .seo-table-responsive tbody td {
        padding: 13px 18px;
        font-size: 14px;
        color: #d1d5db;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .seo-table-responsive tbody tr:last-child td {
        border-bottom: none;
    }

    .seo-table-responsive tbody tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.015);
    }

    .seo-table-responsive tbody tr:hover td {
        background-color: rgba(255, 179, 0, 0.04);
        color: #ffffff;
    }

    .seo-table-responsive tbody td:first-child {
        color: #ffffff;
        font-weight: 500;
    }

    .seo-table-responsive tbody td:last-child {
        color: #4ade80;
        font-weight: 500;
    }

    .read-more-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin: 20px auto 0;
        padding: 10px 32px;
        background-color: rgba(255, 179, 0, 0.08);
        color: #ffb300;
        border: 1px solid rgba(255, 179, 0, 0.35);
        border-radius: 8px;
        cursor: pointer;
        font-size: 14.5px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .read-more-btn:hover {
        background-color: #ffb300;
        color: #11151d;
        border-color: #ffb300;
        box-shadow: 0 4px 18px rgba(255, 179, 0, 0.25);
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .seo-box {
            padding: 22px 16px;
        }

        .seo-box h3 {
            font-size: 17px;
            margin-top: 24px;
        }

        .seo-table-responsive thead th,
        .seo-table-responsive tr:first-child th,
        .seo-table-responsive tbody td {
            padding: 10px 14px;
            font-size: 13px;
        }
    }

    /* =========================================
       2. S.S.S. ALANI
       ========================================= */
    .faq-accordion {
        display: flex;
        gap: 15px;
        align-items: flex-start;
        margin-bottom: 35px;
    }

    .faq-col {
        display: flex;
        flex-direction: column;
        gap: 15px;
        flex: 1;
        min-width: 0;
    }

    @media (max-width: 768px) {
        .faq-accordion {
            flex-direction: column;
        }
    }

    .accordion-item {
        background-color: #232732;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
        /* Çerçeve silindi, derinlik (gölge) eklendi */
        border: none;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .accordion-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 24px;
        cursor: pointer;
        user-select: none;
    }

    .accordion-title:hover {
        background-color: #2a2e3a;
    }

    .accordion-title .h6 {
        font-size: 15px;
        font-weight: 600;
        color: #ffffff;
        margin: 0;
        flex: 1;
        padding-right: 15px;
        line-height: 1.4;
    }

    .accordion-icon {
        width: 20px;
        height: 20px;
        color: #ffb300;
        flex-shrink: 0;
    }

    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
        background-color: #1e222d;
    }

    .content-inner {
        padding: 0 24px 20px 24px;
        margin-top: 10px;
        padding-top: 15px;
        font-size: 14px;
        line-height: 1.6;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .accordion-item.active {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    .accordion-item.active .plus-icon {
        display: none;
    }

    .accordion-item.active .minus-icon {
        display: block !important;
    }

    /* =========================================
       3. HAREKETLİ PARTNERLER VE ÖDEME ALANI
       ========================================= */
    .trust-partners-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 992px) {
        .trust-partners-section {
            grid-template-columns: 1fr;
        }
    }

    .trust-box {
        background-color: #161920;
        border-radius: 12px;
        padding: 25px;
        overflow: hidden;
        /* Çerçeve silindi, güçlü derinlik eklendi */
        border: none;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
    }

    .trust-box h4 {
        color: #ffb300;
        font-size: 18px;
        margin-top: 0;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pulse-icon {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .trust-box p {
        color: #9ca3af;
        font-size: 13px;
        margin-top: 0;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    /* LOGO SİSTEMİ (MOUSE İLE SAĞA SOLA KAYDIRMA) */
    .marquee-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        -webkit-mask-image: linear-gradient(to right, transparent, black 4%, black 96%, transparent);
        mask-image: linear-gradient(to right, transparent, black 4%, black 96%, transparent);
    }

    .marquee-track {
        display: flex;
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        gap: 12px;
        cursor: grab;
        user-select: none;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 4px;
        animation: none !important;
    }

    .marquee-track::-webkit-scrollbar {
        display: none;
    }

    .marquee-track.is-dragging {
        cursor: grabbing;
    }

    .marquee-group {
        display: flex;
        gap: 12px;
        flex-shrink: 0;
    }

    /* Kutu Görünümleri (Kenarlıksız, Hafif Gölgeli) */
    .payment-item {
        background-color: #1e222d;
        border-radius: 6px;
        height: 40px;
        width: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: bold;
        color: #ffffff;
        padding: 5px;
        text-align: center;
        flex-shrink: 0;
        border: none;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        transition: transform 0.2s ease;
    }

    .distributor-item {
        background-color: #1e222d;
        border-radius: 6px;
        height: 40px;
        width: 130px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
        color: #ffffff;
        padding: 5px;
        text-align: center;
        flex-shrink: 0;
        border: none;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease;
    }

    .payment-item:hover,
    .distributor-item:hover {
        transform: scale(1.05);
        cursor: pointer;
    }

    .payment-item img,
    .distributor-item img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
</style>

<section class="playsell-widget-area">
    <div class="playsell-container">

        <!-- 1. SEO KUTUSU -->
        <div class="seo-box">
            <div class="seo-content-wrapper" id="seo-content">
                <h1>Playsell | E-Pin, Top-UP ve Hediye Kartları</h1>
                <p>Playsell, dijital eğlence dünyasında oyuncuların ihtiyaç duyduğu e-pin, oyun içi bakiye ve hediye
                    kartlarına en avantajlı fiyatlarla ulaşmasını sağlayan resmi alışveriş platformudur.
                    15 yıllık sektör deneyimimiz ve 7 yıllık kurumsal geçmişimizle, bugüne kadar 1 milyondan fazla
                    siparişi başarıyla teslim etmenin gururunu yaşıyoruz. Güçlü altyapımız sayesinde haftanın 7 günü 24
                    saat kesintisiz hizmet veriyor; oyuncuları binlerce dijital ürünle saniyeler içinde buluşturuyoruz.
                    Gelişmiş 3D Secure ödeme yöntemleriyle işlemlerinizi tamamen güvenli bir ortamda
                    tamamlayabilirsiniz. Platformumuzdaki tüm ürünler doğrudan resmi yayıncılardan temin edilir; bu
                    sayede %100 orijinal ve üretici garantili ürünlere anında sahip olursunuz.
                </p>
                <h3>Anında Teslimat Top-Up Ürünleri</h3>
                <p>Top-Up (Doğrudan Yükleme) sistemi, satın aldığınız oyun parasının hiçbir şifre gerektirmeden sadece
                    Oyuncu ID'niz ile hesabınıza aktarılmasıdır.
                    Oyun içi mağazada yeni bir kostüm veya sezon bileti gördüğünüzde saniyeler içinde bakiye yüklemek
                    hayat kurtarır. Playsell üzerinden yapacağınız Top-Up alışverişlerinde hesap güvenliğiniz maksimum
                    seviyede kalır. Herhangi bir e-pin kodu kopyalamanıza veya oyun içinde bozdurmaya çalışmanıza gerek
                    kalmaz. Ödemeniz onaylandığı an sistem oyun sunucularıyla haberleşir ve bakiyeniz anında yansır.
                </p>
                <div class="seo-table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Oyun / Platform</th>
                                <th>Ürün Birimi</th>
                                <th>Teslimat Yöntemi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>PUBG Mobile</td>
                                <td>Unknown Cash (UC)</td>
                                <td>Ortalama 3 Dakika</td>
                            </tr>
                            <tr>
                                <td>Mobile Legends: Bang Bang</td>
                                <td>MLBB Elmas</td>
                                <td>Anında (Sadece Oyuncu ID)</td>
                            </tr>
                            <tr>
                                <td>Marvel Rivals</td>
                                <td>Latis</td>
                                <td>Ortalama 2 Dakika</td>
                            </tr>
                            <tr>
                                <td>Age Of Empires Mobile</td>
                                <td>İmparatorluk Parası</td>
                                <td>Ortalama 5 Dakika</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>Güvenli Dijital Kodlar: E-Pin Ürünleri</h3>
                <p>E-Pin (Elektronik Pin) ürünleri, özel dijital kodlar halinde satın alım sonrası anında ekranınıza
                    yansıtılan bakiye sistemleridir.
                    Satın aldığınız kodlar sipariş ekranında anında görünürken, aynı zamanda sisteme kayıtlı e-posta
                    adresinize ve telefonunuza SMS olarak iletilir. Bu kodları oyunun veya platformun kendi resmi
                    mağazasına girerek saniyeler içinde aktif edebilirsiniz. Ayrıca bu dijital kodları arkadaşlarınıza
                    göndererek onlara oyun içi sürprizler yapabilirsiniz.
                </p>

                <div class="seo-table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Oyun / Platform</th>
                                <th>Ürün Birimi</th>
                                <th>Teslimat Yöntemi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Valorant</td>
                                <td>Valorant Points (VP)</td>
                                <td>Anında (E-Pin Kodu)</td>
                            </tr>
                            <tr>
                                <td>League of Legends</td>
                                <td>Riot Points (RP)</td>
                                <td>Anında (E-Pin Kodu)</td>
                            </tr>
                            <tr>
                                <td>Roblox</td>
                                <td>Robux</td>
                                <td>Anında (E-Pin Kodu)</td>
                            </tr>
                            <tr>
                                <td>Wartune Ultra</td>
                                <td>Yıldız Parası</td>
                                <td>Anında (E-Pin Kodu)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>Dijital Hediye Kartları</h3>

                <p>Oyun içi ürünlerin yanı sıra günlük eğlence ve alışveriş deneyiminizi kolaylaştıracak hediye kartları
                    da Playsell vitrininde yer alır.
                    Bu kartlar, kredi kartı bilgilerinizi her platforma girmek istemediğiniz durumlarda maksimum dijital
                    güvenlik sağlar. İstediğiniz tutardaki hediye kartını saniyeler içinde satın alıp kendi hesabınıza
                    yükleyebilirsiniz. Ayrıca sevdiklerinize tek tıkla dijital bir armağan göndermenin en pratik
                    yoludur.
                    Konsol kütüphanenize yeni bir oyun eklemek veya dijital mağaza bakiyenizi doldurmak istiyorsanız,
                    Playsell güvencesiyle bütçenize en uygun kodu hemen seçebilirsiniz. Eğlenceye hız kesmeden devam
                    etmek için aşağıdaki hediye kartlarını güvenle kullanabilirsiniz.
                </p>

                <div class="seo-table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Platform</th>
                                <th>Ürün Birimi</th>
                                <th>Teslimat Yöntemi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Steam</td>
                                <td>Steam Cüzdan Kodu</td>
                                <td>Anında (E-Pin Kodu)</td>
                            </tr>
                            <tr>
                                <td>Xbox</td>
                                <td>Xbox Hediye Kartı</td>
                                <td>Anında (E-Pin Kodu)</td>
                            </tr>
                            <tr>
                                <td>Google Play</td>
                                <td>Google Play Bakiye Kodu</td>
                                <td>Anında (E-Pin Kodu)</td>
                            </tr>
                            <tr>
                                <td>Apple (iTunes)</td>
                                <td>iTunes Hediye Kartı</td>
                                <td>Anında (E-Pin Kodu)</td>
                            </tr>
                            <tr>
                                <td>PlayStation</td>
                                <td>PSN Bakiye Kodu</td>
                                <td>Anında (E-Pin Kodu)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>Güvenli ve Çeşitli Ödeme Altyapısı</h3>
                <p>Playsell olarak alışverişlerinizi sorunsuz ve güvenle tamamlayabilmeniz için lisanslı ödeme
                    kuruluşlarıyla çalışıyoruz. Tüm işlemleriniz 3D Secure altyapısıyla maksimum seviyede korunmaktadır.
                    Kredi kartı, ön ödemeli kartlar ve mobil ödeme işlemlerinizde bakiyeniz saniyeler içinde hesabınıza
                    yansırken; Havale, EFT ve FAST işlemleriniz ortalama 3 dakika içinde sistem tarafından onaylanarak
                    kullanıma hazır hale gelir.
                </p>

                <div class="seo-table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Ödeme Kategorisi</th>
                                <th>Geçerli Yöntemler ve Kartlar</th>
                                <th>İşlem Süresi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Kredi ve Banka Kartları</td>
                                <td>Tüm Kredi ve Banka Kartları (Visa, Mastercard, Troy, Amex vb. tüm altyapılar)</td>
                                <td>Anında</td>
                            </tr>
                            <tr>
                                <td>Ön Ödemeli Kart</td>
                                <td>Tüm Ön Ödemeli Kartlar (Papara, İninal, Tosla vb.)</td>
                                <td>Anında</td>
                            </tr>
                            <tr>
                                <td>Banka Transferi</td>
                                <td>Havale, EFT ve FAST</td>
                                <td>Ortalama 3 Dakika</td>
                            </tr>
                            <tr>
                                <td>Mobil Ödeme</td>
                                <td>Sadece Vodafone Pay</td>
                                <td>Anında</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="seo-blur-overlay"></div>
            </div>
            <button class="read-more-btn" id="read-more-btn">Devamını Gör</button>
        </div>

        <!-- 2. SSS KUTULARI (8 SORU) -->
        <div class="faq-accordion" id="faq-accordion">
            <div class="faq-col">
                <div class="accordion-item">
                    <div class="accordion-title"><span class="h6">Yetkili Satıcı ve Orijinal Kod Garantisi
                            Nedir?</span><svg class="accordion-icon plus-icon" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg><svg class="accordion-icon minus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                        </svg></div>
                    <div class="accordion-content">
                        <div class="content-inner">
                            <p>Playsell üzerinde satışa sunulan tüm epin kodları, oyun içi bakiyeler ve dijital kartlar
                                doğrudan resmi yayıncılar, yetkili distribütörler ve lisanslı iş ortakları üzerinden
                                temin edilir. Platformdaki tüm ürünler %100 orijinal, üretici garantili ve adınıza
                                faturalıdır.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-title"><span class="h6">Satın Aldığım Kodlar Nasıl Teslim Edilir?</span><svg
                            class="accordion-icon plus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg><svg class="accordion-icon minus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                        </svg></div>
                    <div class="accordion-content">
                        <div class="content-inner">
                            <p>Epin ve dijital hediye kartları, ödemeniz onaylandığı anda otomatik teslimat altyapımız
                                tarafından devreye alınır. Satın aldığınız kod hem sipariş başarı ekranında anında
                                görüntülenir hem de e-posta adresinize ve telefonunuza SMS olarak iletilir.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-title"><span class="h6">Top-Up (ID Yükleme) Nasıl Teslim Edilir?</span><svg
                            class="accordion-icon plus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg><svg class="accordion-icon minus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                        </svg></div>
                    <div class="accordion-content">
                        <div class="content-inner">
                            <p>Bazı mobil oyunlarda doğrudan hesaba aktarım (Top-Up) yöntemi kullanılır. Satın alma
                                ekranında ilgili oyunun Oyuncu ID'sini girmeniz yeterlidir. Bakiye, harici bir koda
                                ihtiyaç duyulmadan doğrudan oyun hesabınıza tanımlanır.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-title"><span class="h6">Hangi Ödeme Yöntemlerini Kullanabilirim?</span><svg
                            class="accordion-icon plus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg><svg class="accordion-icon minus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                        </svg></div>
                    <div class="accordion-content">
                        <div class="content-inner">
                            <p>Playsell; güvenli, hızlı ve BDDK lisanslı geniş bir ödeme ağı sunar: Turkcell, Vodafone
                                mobil ödeme; Papara, Paycell, İninal; Lidio ve Elekse aracılığıyla Visa, Mastercard ve
                                Troy kartları ile 3D Secure güvencesiyle 7/24 ödeme yapabilirsiniz.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="faq-col">
                <div class="accordion-item">
                    <div class="accordion-title"><span class="h6">Alışveriş için üye olmak zorunlu mu?</span><svg
                            class="accordion-icon plus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg><svg class="accordion-icon minus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                        </svg></div>
                    <div class="accordion-content">
                        <div class="content-inner">
                            <p>Evet, platformumuzda işlem güvenliği gereği üyeliksiz alışveriş yapılamamaktadır. Satın
                                aldığınız dijital kodlara sonradan kolayca ulaşabilmek ve güvenli işlem yapmak için
                                ücretsiz üyelik oluşturmanız gerekmektedir.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-title"><span class="h6">Satın Alımlarda Fatura Kesiliyor mu?</span><svg
                            class="accordion-icon plus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg><svg class="accordion-icon minus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                        </svg></div>
                    <div class="accordion-content">
                        <div class="content-inner">
                            <p>Evet. Playsell, vergi levhası ve resmi şirket tüzel kişiliğiyle faaliyet gösteren yasal
                                bir platformdur. Yapılan her alışveriş için yasal mevzuata uygun olarak e-arşiv fatura
                                düzenlenir.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-title"><span class="h6">İptal ve İade Durumları Nasıl İşler?</span><svg
                            class="accordion-icon plus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg><svg class="accordion-icon minus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                        </svg></div>
                    <div class="accordion-content">
                        <div class="content-inner">
                            <p>Dijital kodlar "anında ifa edilen mallar" sınıfındadır. Görüntülenmiş veya hesaba
                                aktarılmış ürünlerde cayma hakkı yoktur. Ancak kodun hatalı çıkması gibi teknik arıza
                                durumlarında log kontrolleri sağlanıp anında iade/değişim yapılır.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-title"><span class="h6">Canlı destek hangi saatlerde aktif?</span><svg
                            class="accordion-icon plus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg><svg class="accordion-icon minus-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                        </svg></div>
                    <div class="accordion-content">
                        <div class="content-inner">
                            <p>Playsell müşteri hizmetleri 7 gün 24 saat kesintisiz hizmet vermektedir. Siparişleriniz
                                ve tüm sorularınız için dilediğiniz an destek ekibimize bağlanabilirsiniz.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. HAREKETLİ PARTNERLER VE ÖDEME ALANI -->
        <div class="trust-partners-section">
            <!-- Sol Kutu -->
            <div class="trust-box">
                <h4>
                    <svg class="pulse-icon" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z" />
                    </svg>
                    Lisanslı Ödeme Yöntemleri
                </h4>
                <p>Alışverişlerinizde maksimum güvenlik sağlamak adına tüm süreçler, TCMB ve BDDK lisansına sahip
                    yetkili ödeme kuruluşlarının üst düzey güvenlik standartlarıyla korunur ve tamamlanır.</p>
                <div class="marquee-container">
                    <div class="marquee-track track-left">
                        <div class="marquee-group">
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/elekse.png' ?>" alt="elekse"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/lidio.png' ?>" alt="lidio"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/papara.png' ?>" alt="papara"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/param.png' ?>" alt="param"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/paycell.png' ?>" alt="paycell"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/payinall.png' ?>" alt="payinall"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/paytr.png' ?>" alt="paytr"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/turkcell.png' ?>" alt="turkcell"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/vpay.png' ?>" alt="vpay"></div>
                        </div>
                        <div class="marquee-group">
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/elekse.png' ?>" alt="elekse"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/lidio.png' ?>" alt="lidio"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/papara.png' ?>" alt="papara"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/param.png' ?>" alt="param"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/paycell.png' ?>" alt="paycell"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/payinall.png' ?>" alt="payinall"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/paytr.png' ?>" alt="paytr"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/turkcell.png' ?>" alt="turkcell"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/vpay.png' ?>" alt="vpay"></div>
                        </div>
                    </div>
                    <div class="marquee-track track-right">
                        <div class="marquee-group">
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/paytr.png' ?>" alt="paytr"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/papara.png' ?>" alt="papara"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/vpay.png' ?>" alt="vpay"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/param.png' ?>" alt="param"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/paycell.png' ?>" alt="paycell"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/lidio.png' ?>" alt="lidio"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/elekse.png' ?>" alt="elekse"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/payinall.png' ?>" alt="payinall"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/turkcell.png' ?>" alt="turkcell"></div>
                        </div>
                        <div class="marquee-group">
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/paytr.png' ?>" alt="paytr"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/papara.png' ?>" alt="papara"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/vpay.png' ?>" alt="vpay"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/param.png' ?>" alt="param"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/paycell.png' ?>" alt="paycell"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/lidio.png' ?>" alt="lidio"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/elekse.png' ?>" alt="elekse"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/payinall.png' ?>" alt="payinall"></div>
                            <div class="payment-item"><img src="<?= getenv('CDN_URL') . 'uploads/payments/turkcell.png' ?>" alt="turkcell"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sağ Kutu -->
            <div class="trust-box">
                <h4>
                    <svg class="pulse-icon" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z" />
                    </svg>
                    Güvenilir Kaynak, %100 Orijinal Ürün
                </h4>
                <p>Oyun deneyiminizi kesintisiz kılmak adına tüm dijital ürünler, küresel ekosistemin güvenilir ve
                    doğrulanmış tedarik altyapıları üzerinden maksimum güvenlik ve anında teslimat ile sunulur.</p>
                <div class="marquee-container">
                    <div class="marquee-track track-left">
                        <div class="marquee-group">
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/capcom.png' ?>" alt="capcom"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/crytek.png' ?>" alt="crytek"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/curve.png' ?>" alt="curve"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/gameforge.png' ?>" alt="gameforge">
                            </div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/garena.png' ?>" alt="garena"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/gearbox.png' ?>" alt="gearbox"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/gplay.png' ?>" alt="gplay"></div>
                            <div class="distributor-item"><img src="<?= getenv('CDN_URL') . 'uploads/games/igg.png' ?>"
                                    alt="igg"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/konami.png' ?>" alt="konami"></div>
                        </div>
                        <div class="marquee-group">
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/capcom.png' ?>" alt="capcom"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/crytek.png' ?>" alt="crytek"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/curve.png' ?>" alt="curve"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/gameforge.png' ?>" alt="gameforge">
                            </div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/garena.png' ?>" alt="garena"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/gearbox.png' ?>" alt="gearbox"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/gplay.png' ?>" alt="gplay"></div>
                            <div class="distributor-item"><img src="<?= getenv('CDN_URL') . 'uploads/games/igg.png' ?>"
                                    alt="igg"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/konami.png' ?>" alt="konami"></div>
                        </div>
                    </div>
                    <div class="marquee-track track-right">
                        <div class="marquee-group">
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/lokumgames.png' ?>" alt="lokumgames">
                            </div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/netease.png' ?>" alt="netease"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/nvidia.png' ?>" alt="nvidia"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/playstatıon.png' ?>"
                                    alt="playstation  "></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/riotgames.png' ?>" alt="riotgames">
                            </div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/tencent.png' ?>" alt="tencent"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/ubisoft.png' ?>" alt="ubisoft"></div>
                            <div class="distributor-item"><img src="<?= getenv('CDN_URL') . 'uploads/games/xbox.png' ?>"
                                    alt="xbox"></div>
                        </div>
                        <div class="marquee-group">
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/lokumgames.png' ?>" alt="lokumgames">
                            </div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/netease.png' ?>" alt="netease"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/nvidia.png' ?>" alt="nvidia"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/playstatıon.png' ?>"
                                    alt="playstation  "></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/riotgames.png' ?>" alt="riotgames">
                            </div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/tencent.png' ?>" alt="tencent"></div>
                            <div class="distributor-item"><img
                                    src="<?= getenv('CDN_URL') . 'uploads/games/ubisoft.png' ?>" alt="ubisoft"></div>
                            <div class="distributor-item"><img src="<?= getenv('CDN_URL') . 'uploads/games/xbox.png' ?>"
                                    alt="xbox"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // SEO Metni Aç/Kapa
        const readMoreBtn = document.getElementById('read-more-btn');
        const seoContent = document.getElementById('seo-content');

        if (readMoreBtn && seoContent) {
            readMoreBtn.addEventListener('click', function () {
                if (seoContent.classList.contains('expanded')) {
                    seoContent.classList.remove('expanded');
                    readMoreBtn.textContent = 'Devamını Gör';
                    const seoBox = document.querySelector('.seo-box');
                    if (seoBox) {
                        seoBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } else {
                    seoContent.classList.add('expanded');
                    readMoreBtn.textContent = 'Daralt';
                }
            });
        }

        // SSS Akordiyon 
        const accordionItems = document.querySelectorAll('.accordion-item');
        accordionItems.forEach(item => {
            const title = item.querySelector('.accordion-title');
            if (title) {
                title.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');
                    accordionItems.forEach(otherItem => {
                        otherItem.classList.remove('active');
                        otherItem.querySelector('.accordion-content').style.maxHeight = null;
                    });
                    if (!isActive) {
                        item.classList.add('active');
                        const content = item.querySelector('.accordion-content');
                        content.style.maxHeight = content.scrollHeight + "px";
                    }
                });
            }
        });
    });
</script>