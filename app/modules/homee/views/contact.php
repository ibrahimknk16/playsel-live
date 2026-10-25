<!-- FontAwesome 6 (Fixes missing icons) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* ================= BASE & RESET ================= */
    :root {
        --panel-bg: rgba(24, 24, 27, 0.6);
        --panel-hover: rgba(255, 255, 255, 0.03);
        --border-color: rgba(255, 255, 255, 0.08);
        --accent: #ffb300;
        --accent-glow: rgba(255, 179, 0, 0.15);
        --text-main: #f2f2f2;
        --text-muted: #94a3b8;
        --law-color: #ffb300;
    }

    .contact-page-wrapper * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    
    .contact-page-wrapper {
        color: var(--text-main);
        min-height: 80vh;
        background-image: 
            radial-gradient(circle at 20% 0%, rgba(255, 179, 0, 0.05), transparent 40%),
            radial-gradient(circle at 80% 100%, rgba(255, 115, 0, 0.05), transparent 40%);
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 40px 20px;
    }

    /* Override local FA subset conflicts */
    .contact-page-wrapper i.fa-solid { font-family: "Font Awesome 6 Free" !important; font-weight: 900 !important; }
    .contact-page-wrapper i.fa-regular { font-family: "Font Awesome 6 Free" !important; font-weight: 400 !important; }
    .contact-page-wrapper i.fa-brands { font-family: "Font Awesome 6 Brands" !important; }

    /* ================= CONTAINER ================= */
        .legal-container {
            width: 100%;
            max-width: 1100px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* --- Header --- */
        .legal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            background: var(--panel-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
        }
        .brand img { height: 40px; }
        .page-title-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-main);
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 500;
            font-size: 15px;
            border: 1px solid var(--border-color);
        }
        .page-title-badge i { color: var(--accent); }
        .btn-exit {
            color: var(--text-muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: 0.3s;
            background: rgba(255,255,255,0.03);
            padding: 10px 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .btn-exit:hover { color: #fff; background: rgba(255,255,255,0.08); }

        /* --- Grid Layout --- */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        /* --- Section Shared --- */
        .section-card {
            background: var(--panel-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            gap: 25px;
            position: relative;
            overflow: hidden;
        }
        .section-title {
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 5px;
        }
        .section-title.corp i { color: var(--accent); }
        .section-title.legal i { color: var(--law-color); }

        /* --- Kurumsal Bilgiler Listesi --- */
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: 0.3s;
        }
        .info-item:hover {
            background: var(--panel-hover);
            border-color: var(--accent-glow);
            transform: translateX(4px);
        }
        .info-icon {
            width: 42px;
            height: 42px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            color: var(--accent);
            flex-shrink: 0;
        }
        .info-content {
            flex: 1;
            min-width: 0;
        }
        .info-content h5 {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
            margin: 0 0 4px 0;
        }
        .info-content p {
            font-size: 15px;
            color: #fff;
            font-weight: 500;
            line-height: 1.4;
            margin: 0;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        .info-content p + p {
            margin-top: 3px;
            font-size: 13px;
            color: var(--text-muted);
        }
        
        /* Özel Kopya Butonu Efekti */
        .copy-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-muted);
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-left: auto;
            transition: all 0.2s ease;
        }
        .copy-btn:hover {
            color: var(--accent);
            border-color: var(--accent);
            background: var(--accent-glow);
            transform: scale(1.05);
        }

        /* --- Hukuki Danışmanlık Alanı --- */
        .legal-intro {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.7;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        .lawyer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .lawyer-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: linear-gradient(145deg, rgba(255,255,255,0.02) 0%, rgba(255, 179, 0, 0.05) 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: 0.3s;
        }
        .lawyer-card:hover {
            border-color: rgba(255, 179, 0, 0.4);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .lawyer-avatar {
            width: 45px;
            height: 45px;
            background: rgba(255, 179, 0, 0.1);
            border: 1px solid rgba(255, 179, 0, 0.2);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            color: var(--law-color);
        }
        .lawyer-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }
        .lawyer-info span {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Hukuki Arkaplan Deseni */
        .section-card.legal::after {
            content: '\f24e'; /* fa-scale-balanced */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            bottom: -20px;
            right: -20px;
            font-size: 200px;
            color: rgba(255, 179, 0, 0.03);
            pointer-events: none;
        }

        /* Notification (Unified Top Toast) */
        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            bottom: auto;
            left: auto;
            background: rgba(18, 22, 28, 0.96);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-left: 4px solid #10b981;
            color: #fff;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.04);
            opacity: 0;
            transform: translateY(-16px);
            transition: opacity 0.25s ease, transform 0.25s ease;
            pointer-events: none;
            z-index: 999999;
        }
        .toast.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        @media (max-width: 768px) {
            .toast {
                top: 16px;
                left: 16px;
                right: 16px;
                bottom: auto;
                width: calc(100% - 32px);
                max-width: calc(100vw - 32px);
                margin: 0 auto;
            }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .content-grid { grid-template-columns: 1fr; }
            .legal-header { flex-direction: column; gap: 15px; text-align: center; }
            .contact-page-wrapper { padding: 20px 10px; }
        }
        @media (max-width: 576px) {
            .lawyer-grid { grid-template-columns: 1fr; }
            .section-card { padding: 22px 14px; border-radius: 16px; }
            .info-item {
                display: flex;
                flex-direction: row;
                align-items: center;
                gap: 12px;
                padding: 12px;
            }
            .info-icon {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }
            .info-content h5 {
                font-size: 11.5px;
            }
            .info-content p {
                font-size: 13.5px;
            }
            .info-content p + p {
                font-size: 12px;
            }
            .copy-btn {
                width: 32px;
                height: 32px;
                font-size: 13px;
            }
        }
    </style>
<div class="contact-page-wrapper">
    <?php
    $companyName = site_setting('company_name', 'Playsel Elektronik Ticaret Limited Şirketi');
    $companyTaxNumber = site_setting('company_tax_number', '7300761424');
    $companyAddress = site_setting('contact_address', "Adalet Mh., Anadolu Cd. No: 41/1\nİç Kapı No: 1402 My Plaza\nBayraklı / İzmir");
    ?>

    <div class="legal-container">

        <div class="content-grid">
            
            <!-- SOL: KURUMSAL BİLGİLER -->
            <div class="section-card">
                <h2 class="section-title corp"><i class="fa-solid fa-building"></i> Kurumsal Bilgiler</h2>
                
                <div class="info-list">
                    
                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-signature"></i></div>
                        <div class="info-content">
                            <h5>Ticari Ünvan</h5>
                            <p id="copy-unvan"><?= html_escape($companyName) ?></p>
                            <p id="copy-vkn">VKN: <?= html_escape($companyTaxNumber) ?></p>
                        </div>
                        <button class="copy-btn" onclick="copyText('copy-unvan')" title="Kopyala"><i class="fa-regular fa-copy"></i></button>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                        <div class="info-content">
                            <h5>Adres</h5>
                            <p id="copy-adres"><?= nl2br(html_escape($companyAddress)) ?></p>
                        </div>
                        <button class="copy-btn" onclick="copyText('copy-adres')" title="Kopyala"><i class="fa-regular fa-copy"></i></button>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div class="info-content">
                            <h5>E-Posta Adresi</h5>
                            <p id="copy-mail"><?= html_escape(site_setting('contact_email', 'info@playsel.com')) ?></p>
                        </div>
                        <button class="copy-btn" onclick="copyText('copy-mail')" title="Kopyala"><i class="fa-regular fa-copy"></i></button>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-at"></i></div>
                        <div class="info-content">
                            <h5>KEP Adresi (Kayıtlı Elektronik Posta)</h5>
                            <p id="copy-kep">playsel@hs04.kep.tr</p>
                        </div>
                        <button class="copy-btn" onclick="copyText('copy-kep')" title="Kopyala"><i class="fa-regular fa-copy"></i></button>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-barcode"></i></div>
                        <div class="info-content">
                            <h5>UETS (Ulusal Elektronik Tebligat)</h5>
                            <p id="copy-uets">25929-96257-58393</p>
                        </div>
                        <button class="copy-btn" onclick="copyText('copy-uets')" title="Kopyala"><i class="fa-regular fa-copy"></i></button>
                    </div>

                </div>
            </div>

            <!-- SAĞ: HUKUKİ DANIŞMANLIK -->
            <div class="section-card legal">
                <h2 class="section-title legal"><i class="fa-solid fa-scale-balanced"></i> Hukuki Danışmanlık ve Temsil</h2>
                
                <p class="legal-intro">
                    <strong>Playsel Elektronik Ticaret Limited Şirketi</strong>'ne ait tüm hukuki süreçler, fikri mülkiyet haklarının korunması, marka denetimleri ve resmi yazışmalar; aşağıda bilgileri yer alan kurum içi hukuk departmanımız ve anlaşmalı resmi avukatlarımız tarafından titizlikle yürütülmektedir.
                </p>

                <h3 style="font-size: 15px; color:#fff; font-weight:600; margin-top: 10px; margin-bottom: 5px;">Hukuk Departmanı Kadromuz</h3>
                
                <div class="lawyer-grid">
                    
                    <div class="lawyer-card">
                        <div class="lawyer-avatar"><i class="fa-solid fa-user-tie"></i></div>
                        <div class="lawyer-info">
                            <h4>Av. Deniz KARTAL</h4>
                            <span>Kurum Avukatı</span>
                        </div>
                    </div>

                    <div class="lawyer-card">
                        <div class="lawyer-avatar"><i class="fa-solid fa-user-tie"></i></div>
                        <div class="lawyer-info">
                            <h4>Av. İpek YILDIZ</h4>
                            <span>Kurum Avukatı</span>
                        </div>
                    </div>

                    <div class="lawyer-card">
                        <div class="lawyer-avatar"><i class="fa-solid fa-user-tie"></i></div>
                        <div class="lawyer-info">
                            <h4>Av. Erdem ŞAHİN</h4>
                            <span>Kurum Avukatı</span>
                        </div>
                    </div>

                    <div class="lawyer-card">
                        <div class="lawyer-avatar"><i class="fa-solid fa-user-tie"></i></div>
                        <div class="lawyer-info">
                            <h4>Av. İlyas Efekan KILIÇ</h4>
                            <span>Kurum Avukatı</span>
                        </div>
                    </div>

                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: rgba(255, 179, 0, 0.08); border-left: 3px solid var(--law-color); border-radius: 8px;">
                    <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5;">
                        <i class="fa-solid fa-circle-info" style="color:var(--law-color); margin-right:5px;"></i> Her türlü resmi ve hukuki bildiriminiz için KEP (Kayıtlı Elektronik Posta) adresimizi veya UETS numaramızı kullanmanız önemle rica olunur.
                    </p>
                </div>

            </div>

        </div>
    </div>

    <!-- Kopyalama Bildirimi (Toast) -->
    <div class="toast" id="toastMsg">
        <i class="fa-solid fa-check-circle" style="color:#10b981;"></i> Bilgi panoya kopyalandı!
    </div>

    <script>
        function copyText(elementId) {
            let textToCopy = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(textToCopy).then(() => {
                if (typeof window.showToast === 'function') {
                    window.showToast('Bilgi panoya kopyalandı!', 'success');
                    return;
                }
                if (typeof toastr !== 'undefined') {
                    toastr.success('Bilgi panoya kopyalandı!');
                    return;
                }
                const toast = document.getElementById('toastMsg');
                if (!toast) return;
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 2800);
            }).catch(err => {
                console.error('Kopyalama başarısız!', err);
            });
        }
    </script>
</div>
