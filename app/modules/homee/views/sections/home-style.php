<style>
    .homee-home {
        --home-surface: #161920;
        --home-surface-deep: #11151d;
        --home-border: rgba(255, 179, 0, 0.24);
        --home-border-strong: rgba(255, 179, 0, 0.68);
        --home-gold: #ffb300;
        --home-orange: #ff7300;
        --home-muted: #8f96a3;
    }

    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&display=swap');

    .font-gaming {
        font-family: 'Rajdhani', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* Header alt kenarından başlayan slider & ambient arka plan */
    #navbar {
        margin-bottom: 0 !important;
    }

    main.main-content.homee-home {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    main.main-content.homee-home > .container {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    .homee-home .hero-section {
        position: relative !important;
        isolation: isolate;
        margin-top: 0 !important;
        margin-bottom: 28px !important;
        padding-top: 24px !important;
        padding-bottom: 0 !important;
        overflow: visible !important;
        background: transparent !important;
    }

    .homee-home .hero-section::before {
        content: "";
        position: absolute;
        z-index: -2;
        top: -50px;
        bottom: -24px;
        left: 50%;
        width: 100vw;
        transform: translateX(-50%);
        background: #11151d;
        pointer-events: none;
        -webkit-mask-image: linear-gradient(180deg, transparent 0%, black 40px, black 100%);
        mask-image: linear-gradient(180deg, transparent 0%, black 40px, black 100%);
    }

    .homee-home .hero-section::after {
        content: "";
        position: absolute;
        z-index: -1;
        top: -50px;
        bottom: -24px;
        left: 50%;
        width: 100vw;
        transform: translateX(-50%);
        background: linear-gradient(180deg, #0b0f14 0%, rgba(17, 21, 29, 0.6) 18%, transparent 40%),
                    linear-gradient(0deg, #11151d 0%, transparent 45%),
                    linear-gradient(90deg, #11151d 0%, rgba(17,21,29,.48) 20%, rgba(17,21,29,.48) 80%, #11151d 100%),
                    var(--ps-hero-bg) center/cover no-repeat;
        filter: saturate(0.75);
        opacity: 0.38;
        pointer-events: none;
        transition: opacity 0.4s ease;
        -webkit-mask-image: linear-gradient(180deg, transparent 0%, black 45px, black calc(100% - 25px), transparent 100%);
        mask-image: linear-gradient(180deg, transparent 0%, black 45px, black calc(100% - 25px), transparent 100%);
    }

    /* MASAÜSTÜ & MOBİL SLIDER KARTI */
    .homee-home .hero-card-wrapper {
        position: relative;
        z-index: 10;
        margin-top: 0 !important;
        width: 100%;
        max-width: 1360px;
        margin-left: auto;
        margin-right: auto;
        transition: all 0.3s ease;
    }

    .homee-home .hero-main-card {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        background: #0f0a17;
        border: 1px solid rgba(199, 154, 62, 0.55);
        box-shadow: 0 0 50px -5px rgba(255, 92, 22, 0.35), 0 30px 70px -15px rgba(0,0,0,0.95);
        transition: box-shadow 0.4s ease, border-color 0.4s ease;
    }

    /* Sol ve Sağ Sabit Turuncu Neon Çubuklar */
    .homee-home .hero-neon-bar {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 5px;
        height: 90px;
        background: #ff5c16;
        z-index: 35;
        box-shadow: 0 0 16px #ff5c16;
        pointer-events: none;
    }
    .homee-home .hero-neon-bar-left {
        left: 0;
        border-radius: 0 4px 4px 0;
    }
    .homee-home .hero-neon-bar-right {
        right: 0;
        border-radius: 4px 0 0 4px;
    }

    /* SLIDER GÖRSEL ALANI */
    .homee-home .hero-slider-area {
        position: relative;
        width: 100%;
        aspect-ratio: 21 / 8;
        min-height: 380px;
        max-height: 500px;
        background: #0c0714;
        overflow: hidden;
        user-select: none;
    }

    .homee-home .hero-slide-link {
        display: block;
        width: 100%;
        height: 100%;
        position: relative;
        cursor: pointer;
        text-decoration: none;
    }

    .homee-home .hero-slide-media {
        width: 100%;
        height: 100%;
        position: absolute;
        inset: 0;
        overflow: hidden;
    }

    .homee-home .hero-slide-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Sinematik Ken Burns Efekti (6.5 Saniye Senkron) */
    .homee-home .ken-burns-anim {
        /* animation: heroKenBurns 6.5s ease-out forwards; */
        transform-origin: center center;
    }
    @keyframes heroKenBurns {
        0% { transform: scale(1.0); }
        100% { transform: scale(1.045); }
    }

    /* Sağ Alt Köşe Metin Arka Plan Karartması */
    .homee-home .hero-text-shadow-mask {
        position: absolute;
        inset: 0;
        z-index: 12;
        pointer-events: none;
        background: radial-gradient(
            ellipse 90% 90% at 100% 100%,
            rgba(5, 3, 10, 0.98) 0%,
            rgba(5, 3, 10, 0.85) 45%,
            rgba(5, 3, 10, 0.25) 80%,
            transparent 100%
        );
    }

    .homee-home .hero-ember-canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 15;
        opacity: 0.65;
    }

    .homee-home .hero-text-overlay {
        position: absolute;
        right: 48px;
        bottom: 60px;
        max-width: 580px;
        z-index: 20;
        pointer-events: none;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        text-align: right;
        transition: opacity 0.3s ease;
    }

    .homee-home .hero-category-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #ff5c16;
        background: rgba(255, 92, 22, 0.12);
        border: 1px solid rgba(255, 92, 22, 0.35);
        padding: 4px 12px;
        border-radius: 9999px;
        margin-bottom: 8px;
        box-shadow: 0 0 10px rgba(255, 92, 22, 0.2);
    }

    .homee-home .hero-slide-title {
        font-size: clamp(24px, 3.2vw, 46px);
        font-weight: 900;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        line-height: 1.1;
        margin: 0;
        padding: 0;
        text-shadow: 0 4px 18px rgba(0,0,0,0.95);
        font-family: 'Rajdhani', 'Inter', sans-serif;
    }

    .homee-home .hero-slide-subtitle {
        font-size: clamp(12px, 1.2vw, 15px);
        color: rgba(229, 231, 235, 0.92);
        font-weight: 500;
        line-height: 1.5;
        margin-top: 8px;
        margin-bottom: 0;
        text-shadow: 0 2px 8px rgba(0,0,0,0.95);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .homee-home .hero-arrow-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(8px);
        opacity: 0;
        transition: opacity 0.25s ease, background 0.2s ease, transform 0.2s ease;
        z-index: 30;
        cursor: pointer;
    }
    .homee-home .hero-slider-area:hover .hero-arrow-btn {
        opacity: 1;
    }
    .homee-home .hero-arrow-btn:hover {
        background: #ff5c16;
        color: #ffffff;
        transform: translateY(-50%) scale(1.08);
        border-color: #ff5c16;
    }
    .homee-home .hero-arrow-btn svg {
        width: 20px;
        height: 20px;
    }
    .homee-home .hero-arrow-prev { left: 20px; }
    .homee-home .hero-arrow-next { right: 20px; }

    /* DİNAMİK NOKTA / ÇİZGİ SLAYT KONTROL GÖSTERGESİ (2. Görsel Birebir Tasarım) */
    .homee-home .hero-pagination-pill {
        position: absolute;
        bottom: 16px;
        right: 28px;
        z-index: 35;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(10, 8, 16, 0.88);
        backdrop-filter: blur(10px);
        padding: 5px 12px;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.7);
        user-select: none;
    }

    .homee-home .hero-pill-arrow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        padding: 0;
        margin: 0;
        color: #94a3b8;
        cursor: pointer;
        transition: color 0.2s ease, transform 0.2s ease;
        line-height: 1;
    }
    .homee-home .hero-pill-arrow:hover {
        color: #ffffff;
        transform: scale(1.15);
    }
    .homee-home .hero-pill-arrow svg {
        width: 14px;
        height: 14px;
        stroke: currentColor;
    }

    .homee-home .hero-pagination-dots {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .homee-home .hero-dot-btn {
        padding: 0;
        border: 0;
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    .homee-home .hero-dot-indicator {
        display: block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #64748b;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .homee-home .hero-dot-indicator.active {
        width: 24px;
        height: 8px;
        border-radius: 9999px;
        background: #ff5c16;
        box-shadow: 0 0 10px #ff5c16, 0 0 4px #ff5c16;
    }

    /* ENTEGRE EDİLMİŞ DİNAMİK İLERLEME BARI */
    .homee-home .hero-progress-track {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: rgba(255, 255, 255, 0.12);
        z-index: 35;
        pointer-events: none;
        overflow: hidden;
    }

    .homee-home .hero-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #ff5c16, #fbbf24, #ff5c16);
        box-shadow: 0 0 12px #ff5c16;
        will-change: width;
    }

    /* MASAÜSTÜ TAB MENÜSÜ */
    .homee-home .hero-tabs-nav {
        width: 100%;
        background: #0c0714;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        position: relative;
        overflow: hidden;
    }

    .homee-home .hero-tabs-fade {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 30px;
        pointer-events: none;
        z-index: 20;
    }
    .homee-home .hero-tabs-fade-left {
        left: 0;
        background: linear-gradient(90deg, #0c0714, transparent);
    }
    .homee-home .hero-tabs-fade-right {
        right: 0;
        background: linear-gradient(270deg, #0c0714, transparent);
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .no-scrollbar {
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }

    .homee-home .hero-tabs-list {
        display: flex;
        align-items: stretch;
        overflow-x: auto;
        scroll-behavior: smooth;
        position: relative;
        z-index: 10;
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }

    .homee-home .hero-tabs-list::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .homee-home .hero-tab-btn {
        flex: 1 1 auto;
        min-width: max-content;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: transparent;
        border: 0;
        border-right: 1px solid rgba(255, 255, 255, 0.05);
        color: #9ca3af;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .homee-home .hero-tab-btn:hover {
        background: rgba(255, 255, 255, 0.04);
        color: #f3f4f6;
    }
    .homee-home .hero-tab-btn.active {
        background: #1c112b;
        color: #ff5c16;
    }

    .homee-home .hero-tab-icon {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .homee-home .hero-tab-icon img,
    .homee-home .hero-tab-icon svg {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: grayscale(100%);
        opacity: 0.55;
        transition: filter 0.25s ease, opacity 0.25s ease, transform 0.25s ease;
    }
    .homee-home .hero-tab-btn:hover .hero-tab-icon img,
    .homee-home .hero-tab-btn:hover .hero-tab-icon svg {
        opacity: 0.85;
    }
    .homee-home .hero-tab-btn.active .hero-tab-icon img,
    .homee-home .hero-tab-btn.active .hero-tab-icon svg {
        filter: grayscale(0%);
        opacity: 1;
        transform: scale(1.06);
    }

    .homee-home .hero-tab-name {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .homee-home .hero-tab-btn.active .hero-tab-name {
        font-weight: 800;
        color: #ff5c16;
    }

    /* ============================================================== */
    /* RESPONSIVE GÖRÜNÜMLER (TABLET & MOBİL)                        */
    /* ============================================================== */
    @media (max-width: 1024px) {
        .homee-home .hero-slider-area {
            aspect-ratio: 16 / 7;
            min-height: 320px;
        }
        .homee-home .hero-text-overlay {
            right: 28px;
            bottom: 50px;
            max-width: 460px;
        }
        .homee-home .hero-tab-btn {
            padding: 12px 16px;
            min-width: max-content;
            flex: 1 0 auto;
        }
    }

    @media (max-width: 767px) {
        .homee-home .hero-section {
            margin-top: 0 !important;
            margin-bottom: 20px !important;
            padding-top: 14px !important;
        }
        .homee-home .hero-card-wrapper {
            padding: 0 4px;
        }
        .homee-home .hero-main-card {
            border-radius: 18px;
            box-shadow: 0 0 30px -5px rgba(255, 92, 22, 0.3), 0 20px 40px -10px rgba(0,0,0,0.9);
        }
        .homee-home .hero-neon-bar {
            height: 60px;
            width: 4px;
        }
        .homee-home .hero-slider-area {
            aspect-ratio: 16 / 11;
            min-height: 250px;
            touch-action: pan-y;
        }
        .homee-home .hero-text-shadow-mask {
            background: linear-gradient(
                0deg,
                rgba(5, 3, 10, 0.82) 0%,
                rgba(5, 3, 10, 0.45) 24%,
                rgba(5, 3, 10, 0.06) 38%,
                transparent 50%
            );
        }
        .homee-home .hero-text-overlay {
            left: 16px;
            right: 90px;
            bottom: 12px;
            max-width: none;
            align-items: flex-start;
            text-align: left;
        }
        .homee-home .hero-category-badge {
            font-size: 9px;
            padding: 2px 8px;
            margin-bottom: 4px;
        }
        .homee-home .hero-slide-title {
            font-size: 17px;
            text-align: left;
        }
        .homee-home .hero-slide-subtitle {
            font-size: 10px;
            margin-top: 3px;
            text-align: left;
            -webkit-line-clamp: 2;
        }
        .homee-home .hero-arrow-btn {
            display: none;
        }
        .homee-home .hero-pagination-pill {
            right: 12px;
            bottom: 10px;
            padding: 4px 10px;
            gap: 4px;
        }
        .homee-home .hero-dot-indicator {
            width: 5px;
            height: 5px;
        }
        .homee-home .hero-dot-indicator.active {
            width: 16px;
            height: 5px;
        }

        /* MOBİL BULLET / TAB MENÜSÜ AYARLARI */
        .homee-home .hero-tabs-nav {
            background: #090510;
        }
        .homee-home .hero-tabs-fade {
            width: 22px;
        }
        .homee-home .hero-tabs-fade-left {
            background: linear-gradient(90deg, #090510, transparent);
        }
        .homee-home .hero-tabs-fade-right {
            background: linear-gradient(270deg, #090510, transparent);
        }
        .homee-home .hero-tabs-list {
            padding: 0 4px;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x proximity;
            overscroll-behavior-x: contain;
        }
        .homee-home .hero-tab-btn {
            flex: 1 0 auto;
            min-width: max-content;
            padding: 11px 18px;
            gap: 8px;
            scroll-snap-align: center;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            touch-action: pan-x;
        }
        .homee-home .hero-tab-btn:last-child {
            border-right: none;
        }
        .homee-home .hero-tab-name {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.025em;
            white-space: nowrap;
        }
        .homee-home .hero-tab-icon {
            width: 19px;
            height: 19px;
            border-radius: 4px;
        }
        .homee-home .hero-tab-icon img,
        .homee-home .hero-tab-icon svg {
            border-radius: 3px;
            opacity: 0.75;
            filter: grayscale(40%);
        }
        .homee-home .hero-tab-btn.active {
            background: #170e24;
            color: #ff5c16;
            box-shadow: inset 0 -2.5px 0 0 #ff5c16;
        }
        .homee-home .hero-tab-btn.active .hero-tab-icon img,
        .homee-home .hero-tab-btn.active .hero-tab-icon svg {
            opacity: 1;
            filter: grayscale(0%);
            transform: scale(1.08);
        }
        .homee-home .hero-tab-btn.active .hero-tab-name {
            color: #ff7a18;
            font-weight: 800;
        }
    }

    @media (max-width: 480px) {
        .homee-home .hero-tab-btn {
            padding: 10px 14px;
            gap: 7px;
        }
        .homee-home .hero-tab-name {
            font-size: 11px;
        }
        .homee-home .hero-tab-icon {
            width: 17px;
            height: 17px;
        }
    }

    .homee-home .deals-grid .deal-card {
        border: 1px solid transparent;
        border-radius: 14px;
        padding: 14px;
        background: linear-gradient(145deg, rgba(35, 36, 47, .98), rgba(22, 25, 32, .98));
        box-shadow: 0 8px 22px rgba(0, 0, 0, .28);
    }

    .homee-home .deals-grid .deal-card:hover {
        border-color: rgba(255, 179, 0, .22);
        background: linear-gradient(145deg, rgba(49, 43, 31, .98), rgba(22, 25, 32, .98));
        box-shadow: 0 14px 30px rgba(0, 0, 0, .44), 0 0 16px rgba(255, 140, 0, .1);
    }

    .homee-home .deals-grid .deal-img {
        border: 1px solid rgba(255, 179, 0, .2);
        border-radius: 9px;
    }

    .homee-home .deals-grid .deal-divider {
        background: rgba(255, 179, 0, .24);
        opacity: 1;
    }

    .homee-home .deals-grid .deal-badge {
        background: linear-gradient(45deg, var(--home-gold), var(--home-orange));
        color: #171717;
        font-weight: 800;
    }

    .homee-home > .container > .deals-section,
    .homee-home > .container > .categories-panel,
    .homee-home > .container > .giftcards-section,
    .homee-home > .container > .news-section,
    .homee-home > .container > .publishers-section,
    .homee-home > .container > .reviews-section,
    .homee-home > .container > .trust-section {
        position: relative;
        /* margin-top: 42px; */
    }

    .homee-home .deals-header,
    .homee-home .section-header,
    .homee-home .ps-news-header {
        position: relative;
        padding: 0 0 14px;
        margin-bottom: 24px;
        background: transparent;
        border-radius: 0;
    }

    .homee-home .deals-header::after,
    .homee-home .section-header::after,
    .homee-home .ps-news-header::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 48px;
        height: 3px;
        border-radius: 10px;
        background: var(--home-gold);
        box-shadow: 0 0 12px var(--home-gold);
    }

    .homee-home .deals-header h2,
    .homee-home .section-title,
    .homee-home .ps-news-heading {
        margin: 0;
        color: #fff;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .homee-home .countdown-wrapper {
        border: 1px solid rgba(255, 179, 0, .2);
        border-radius: 8px;
        background: rgba(0, 0, 0, .24);
    }

    .homee-home .deal-card,
    .homee-home .category-card,
    .homee-home .gc-card,
    .homee-home .news-card,
    .homee-home .publisher-card,
    .homee-home .review-card,
    .homee-home .trust-badge {
        border: 1px solid transparent;
        background: linear-gradient(145deg, rgba(35, 36, 47, .98), rgba(22, 25, 32, .98));
        box-shadow: 0 8px 24px rgba(0, 0, 0, .28);
    }

    .homee-home .deal-card,
    .homee-home .category-card,
    .homee-home .gc-card,
    .homee-home .news-card,
    .homee-home .publisher-card,
    .homee-home .review-card {
        transition: transform .35s ease, border-color .35s ease, box-shadow .35s ease, background .35s ease;
    }

    .homee-home .deal-card:hover,
    .homee-home .category-card:hover,
    .homee-home .gc-card:hover,
    .homee-home .news-card:hover,
    .homee-home .publisher-card:hover,
    .homee-home .review-card:hover {
        border-color: rgba(255, 179, 0, .18);
        background: linear-gradient(145deg, rgba(43, 39, 31, .98), rgba(22, 25, 32, .98));
        box-shadow: 0 16px 36px rgba(0, 0, 0, .48), 0 0 18px rgba(255, 140, 0, .12);
        transform: translateY(-6px);
    }

    .homee-home .deal-card:hover .deal-img,
    .homee-home .category-card:hover .cat-logo-box,
    .homee-home .gc-card:hover img {
        filter: drop-shadow(0 0 10px rgba(255, 179, 0, .2));
    }

    .homee-home .category-card .category-name {
        position: relative;
        display: inline-block;
        align-self: center;
        width: fit-content;
        max-width: 100%;
        text-align: center;
        padding-bottom: 4px;
    }

    .homee-home .category-card .category-name::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 100%;
        height: 2px;
        background: var(--home-gold, #ffb300);
        box-shadow: 0 0 8px rgba(255, 179, 0, 0.5);
        border-radius: 2px;
        transform: translateX(-50%) scaleX(0);
        transform-origin: center;
        transition: transform 0.25s ease, opacity 0.25s ease;
        opacity: 0;
    }

    .homee-home .category-card:hover .category-name::after {
        transform: translateX(-50%) scaleX(1);
        opacity: 1;
    }

    .homee-home .categories-panel {
        padding: 24px;
        border: 0;
        border-radius: 16px;
        background: linear-gradient(145deg, rgba(35, 36, 47, .98), rgba(17, 21, 29, .98));
        box-shadow: 0 14px 34px rgba(0, 0, 0, .24);
    }

    .homee-home .cat-filter-tabs {
        border: 1px solid rgba(255, 179, 0, .16);
        background: rgba(0, 0, 0, .22);
    }

    .homee-home .cat-filter-tab.active {
        background: linear-gradient(45deg, var(--home-gold), var(--home-orange));
        color: #171717;
    }

    .homee-home .giftcards-section .container {
        padding: 0;
    }

    .homee-home .gc-card {
        padding: 22px 12px;
        border-radius: 14px;
    }

    .homee-home .gc-card:hover {
        transform: translateY(-6px) scale(1.01);
    }

    .homee-home .news-card,
    .homee-home .publisher-card {
        border-radius: 14px;
    }

    .homee-home .news-img,
    .homee-home .pub-thumb {
        border-bottom: 1px solid rgba(255, 179, 0, .12);
    }

    .homee-home .news-tag,
    .homee-home .pub-support-btn {
        background: linear-gradient(45deg, var(--home-gold), var(--home-orange));
        color: #171717;
    }

    .homee-home .news-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .homee-home .news-card {
        display: flex;
        min-width: 0;
        flex-direction: column;
        border: 1px solid #2a3441;
        border-radius: 16px;
        background: linear-gradient(145deg, #151b25, #10151c);
        box-shadow: none;
        text-decoration: none;
        transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
    }

    .homee-home .news-card:hover {
        border-color: #584820;
        background: linear-gradient(145deg, #151b25, #10151c);
        box-shadow: 0 15px 35px rgba(0, 0, 0, .4);
        transform: translateY(-5px);
    }

    .homee-home .news-image-wrapper {
        position: relative;
        overflow: hidden;
        aspect-ratio: 16 / 9;
        background: #1a222c;
    }

    .homee-home .news-image-wrapper::after {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 50%, rgba(11, 15, 19, .8) 100%);
        content: "";
        pointer-events: none;
    }

    .homee-home .news-image-wrapper img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .4s ease;
    }

    .homee-home .news-card:hover .news-image-wrapper img {
        transform: scale(1.05);
    }

    .homee-home .game-badge {
        position: absolute;
        z-index: 2;
        top: 12px;
        left: 12px;
        padding: 6px 10px;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 8px;
        background: rgba(11, 15, 19, .75);
        color: #fff;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        backdrop-filter: blur(8px);
    }

    .homee-home .news-card .news-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 20px;
    }

    .homee-home .news-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        color: #8b97a6;
        font-size: .75rem;
        font-weight: 600;
    }

    .homee-home .news-meta svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        color: #ffb817;
    }

    .homee-home .news-meta .news-tag {
        padding: 3px 8px;
        border: 1px solid rgba(255, 184, 23, .2);
        border-radius: 999px;
        background: rgba(255, 184, 23, .1);
        color: #ffcf68;
        font-size: .65rem;
    }

    .homee-home .news-card .news-title {
        margin: 0 0 10px;
        color: #f3f5f7;
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.35;
        letter-spacing: -.02em;
        -webkit-line-clamp: 2;
    }

    .homee-home .news-excerpt {
        display: -webkit-box;
        margin: 0;
        overflow: hidden;
        color: #929ca8;
        font-size: .85rem;
        line-height: 1.5;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .homee-home .reviews-section {
        padding-bottom: 0;
    }

    .homee-home .review-card {
        border-radius: 14px;
    }

    .homee-home .trust-section {
        padding: 24px 0 8px;
        border-top: none;    
    }

    .homee-home .trust-badge {
        border-radius: 14px;
        padding: 22px 16px;
        transition: transform .35s ease, border-color .35s ease, box-shadow .35s ease;
    }

    .homee-home .trust-badge:hover {
        border-color: var(--home-border-strong);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .35), 0 0 16px rgba(255, 140, 0, .1);
        transform: translateY(-5px);
    }

    .homee-home .trust-icon {
        border: 1px solid rgba(255, 179, 0, .22);
        background: rgba(255, 179, 0, .08);
    }

    .homee-home .trust-title {
        color: #fff;
    }

    @media (max-width: 768px) {
        .homee-home .news-grid {
            grid-template-columns: 1fr !important;
            gap: 16px;
        }

        .homee-home .news-card {
            flex-direction: row;
            align-items: center;
            height: 120px;
            padding: 12px;
        }

        .homee-home .news-image-wrapper {
            width: 120px;
            height: 100%;
            flex-shrink: 0;
            aspect-ratio: auto;
            border-radius: 10px;
        }

        .homee-home .news-image-wrapper::after,
        .homee-home .game-badge,
        .homee-home .news-excerpt {
            display: none;
        }

        .homee-home .news-card .news-body {
            justify-content: center;
            padding: 0 0 0 14px;
        }

        .homee-home .news-meta {
            margin-bottom: 6px;
            font-size: .65rem;
        }

        .homee-home .news-card .news-title {
            margin-bottom: 0;
            font-size: .9rem;
            -webkit-line-clamp: 3;
        }

        .homee-home > .container > .deals-section,
        .homee-home > .container > .categories-panel,
        .homee-home > .container > .giftcards-section,
        .homee-home > .container > .news-section,
        .homee-home > .container > .publishers-section,
        .homee-home > .container > .reviews-section,
        .homee-home > .container > .trust-section {
            margin-top: 30px;
        }

        .homee-home .deals-header,
        .homee-home .section-header {
            align-items: flex-start;
            gap: 12px;
        }

        .homee-home .deals-header h2,
        .homee-home .section-title {
            font-size: 19px;
        }

        .homee-home .categories-panel {
            padding: 16px;
        }

        .homee-home .hero-section {
            margin-bottom: 16px;
        }

        .homee-home .game-tabs {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
            padding: 5px 8px !important;
            background: rgba(22, 25, 32, 0.98) !important;
            border: 1px solid rgba(255, 179, 0, 0.15) !important;
            border-bottom: none !important;
            border-radius: 12px 12px 0 0 !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            scrollbar-width: none !important;
            white-space: nowrap !important;
        }

        .homee-home .game-tabs::-webkit-scrollbar {
            display: none !important;
        }

        .homee-home .game-tab {
            flex: 0 0 auto !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            min-height: 38px !important;
            padding: 4px 10px !important;
            border-radius: 7px !important;
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            opacity: 0.7 !important;
        }

        .homee-home .game-tab:hover {
            opacity: 0.9 !important;
            background: rgba(255, 255, 255, 0.06) !important;
        }

        .homee-home .game-tab.active {
            background: linear-gradient(135deg, rgba(255, 179, 0, 0.2), rgba(255, 115, 0, 0.08)) !important;
            border-color: rgba(255, 179, 0, 0.45) !important;
            opacity: 1 !important;
            box-shadow: 0 2px 8px rgba(255, 179, 0, 0.15) !important;
        }

        .homee-home .game-tab .tab-icon {
            width: 18px !important;
            height: 18px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
        }

        .homee-home .game-tab .tab-icon img {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
        }

        .homee-home .game-tab .tab-name {
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #fff !important;
            white-space: nowrap !important;
        }

        .homee-home .game-tab.active .tab-name {
            color: var(--home-gold) !important;
        }

        .homee-home .hero-banner {
            width: 100% !important;
            height: auto !important;
            min-height: unset !important;
            max-height: unset !important;
            aspect-ratio: 2.2 / 1 !important;
            border-radius: 0 0 12px 12px !important;
            border: 1px solid rgba(255, 179, 0, 0.15) !important;
            border-top: none !important;
            overflow: hidden !important;
            position: relative !important;
            background: #0b0f14 !important;
        }

        .homee-home .hero-slide {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0b0f14;
        }

        .homee-home .hero-slide img {
            width: 100%;
            height: 100%;
            object-fit: contain !important;
            object-position: center;
            display: block;
        }

        .homee-home .hero-overlay-left,
        .homee-home .hero-overlay-right {
            display: none !important;
        }

        .homee-home .hero-text-right {
            display: block !important;
            top: 10px !important;
            right: 10px !important;
            max-width: 54% !important;
            padding: 5px 9px !important;
            border-right: 2px solid var(--home-gold) !important;
            border-radius: 6px 0 0 6px !important;
            background: rgba(11, 15, 20, 0.72) !important;
            backdrop-filter: blur(6px);
            z-index: 4;
        }

        .homee-home .hero-text-right .hero-game-name {
            font-size: 10px !important;
            font-weight: 700;
            line-height: 1.2;
            color: var(--home-gold);
            text-transform: uppercase;
        }

        .homee-home .hero-text-right .hero-title {
            font-size: 12px !important;
            line-height: 1.25 !important;
            margin-top: 2px !important;
            color: #fff;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .homee-home .hero-cta {
            display: none !important;
        }

        .homee-home .hero-nav {
            position: absolute !important;
            bottom: 8px !important;
            left: 50% !important;
            right: auto !important;
            transform: translateX(-50%) !important;
            display: flex !important;
            align-items: center !important;
            gap: 0 !important;
            padding: 4px 8px !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 20px !important;
            background: rgba(11, 15, 20, 0.65) !important;
            backdrop-filter: blur(8px) !important;
            z-index: 4;
        }

        .homee-home .hero-nav-btn {
            display: none !important;
        }

        .homee-home .hero-dots {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
            padding: 0 !important;
        }

        .homee-home .hero-dot {
            width: 6px !important;
            height: 6px !important;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.4) !important;
            transition: all 0.25s ease !important;
        }

        .homee-home .hero-dot.active {
            width: 15px !important;
            border-radius: 6px !important;
            background: var(--home-gold) !important;
            box-shadow: 0 0 8px rgba(255, 179, 0, 0.6) !important;
        }
    }

    @media (max-width: 480px) {
        .homee-home .game-tabs {
            border-radius: 12px 12px 0 0 !important;
            padding: 4px 6px !important;
            gap: 4px !important;
        }

        .homee-home .game-tab {
            min-height: 38px !important;
            padding: 3px 8px !important;
            gap: 5px !important;
        }

        .homee-home .hero-banner {
            aspect-ratio: 2.2 / 1 !important;
            border-radius: 0 0 12px 12px !important;
        }

        .homee-home .hero-text-right {
            max-width: 58% !important;
        }

        .homee-home .hero-text-right .hero-title {
            font-size: 11px !important;
        }
    }
</style>
