<style>
    .playsell-coupons-section {
        --coupon-page: #11151d;
        --coupon-text: #f1f4f6;
        --coupon-muted: #98a5ae;
        --coupon-accent: #dfbd70;
        --coupon-accent-soft: rgba(167, 128, 57, 0.14);
        background-color: var(--coupon-page);
        color: var(--coupon-text);
        padding: 24px 0;
        box-sizing: border-box;
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .playsell-coupons-container {
        width: 100%;
        margin: 0 auto;
        box-sizing: border-box;
    }

    .playsell-coupons-section .coupon-grid {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 13px !important;
        align-items: stretch !important;
        width: 100% !important;
    }

    .playsell-coupons-section .coupon-card {
        --coupon-accent: #dfbd70;
        position: relative !important;
        isolation: isolate !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        min-width: 0 !important;
        min-height: 286px !important;
        overflow: hidden !important;
        padding: 8px 12px 9px !important;
        border: 1px solid #354149 !important;
        border-radius: 14px !important;
        background: radial-gradient(ellipse at 100% 0%, rgba(167, 128, 57, 0.14), transparent 49%), linear-gradient(150deg, #192127, #12181d 72%) !important;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2), inset 0 1px rgba(255, 255, 255, 0.04) !important;
        transition: border-color .25s ease, transform .25s ease, box-shadow .25s ease !important;
        box-sizing: border-box !important;
    }

    .playsell-coupons-section .coupon-card:hover {
        transform: translateY(-3px) !important;
        border-color: #ff9800 !important;
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.45), 0 0 20px rgba(255, 140, 0, 0.25), inset 0 1px rgba(255, 255, 255, 0.08) !important;
    }

    .playsell-coupons-section .coupon-card:hover .ticket-divider::before,
    .playsell-coupons-section .coupon-card:hover .ticket-divider::after {
        border-color: #ff9800 !important;
    }

    .playsell-coupons-section .offer-row {
        position: relative !important;
        z-index: 1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 8px !important;
        padding: 2px 0 5px !important;
        width: 100% !important;
    }

    .playsell-coupons-section .offer-value {
        min-width: 0 !important;
        display: flex !important;
        align-items: baseline !important;
        gap: 6px !important;
        color: var(--coupon-accent) !important;
    }

    .playsell-coupons-section .offer-value strong {
        font-size: clamp(1.6rem, 2.4vw, 2rem) !important;
        font-weight: 950 !important;
        letter-spacing: -.075em !important;
        line-height: 1 !important;
        text-shadow: 0 2px 18px rgba(0, 0, 0, 0.25) !important;
        white-space: nowrap !important;
    }

    .playsell-coupons-section .offer-value span {
        font-size: .68rem !important;
        font-weight: 800 !important;
        letter-spacing: .01em !important;
    }

    .playsell-coupons-section .offer-mark {
        display: grid !important;
        place-items: center !important;
        width: 31px !important;
        height: 31px !important;
        flex: 0 0 auto !important;
        border: 1px solid #46515a !important;
        border-radius: 9px !important;
        background: #182127 !important;
        color: var(--coupon-accent) !important;
    }

    .playsell-coupons-section .offer-mark svg {
        width: 18px !important;
        height: 18px !important;
        display: block !important;
    }

    .playsell-coupons-section .coupon-card h2 {
        position: relative !important;
        z-index: 1 !important;
        margin: 3px 0 3px !important;
        font-size: .9rem !important;
        font-weight: 850 !important;
        letter-spacing: -.025em !important;
        line-height: 1.3 !important;
        color: #f1f4f6 !important;
    }

    .playsell-coupons-section .coupon-description {
        position: relative !important;
        z-index: 1 !important;
        min-height: 29px !important;
        margin: 0 !important;
        color: var(--coupon-muted) !important;
        font-size: .64rem !important;
        line-height: 1.4 !important;
    }

    /* Bilet Kesik Çizgisi */
    .playsell-coupons-section .ticket-divider {
        position: relative !important;
        display: block !important;
        align-self: stretch !important;
        width: calc(100% + 26px) !important;
        margin: 8px -13px 7px !important;
        height: 0 !important;
        min-height: 0 !important;
        border: none !important;
        border-top: 1.5px dashed #b9c3c9 !important;
        box-sizing: border-box !important;
        z-index: 2 !important;
    }

    .playsell-coupons-section .ticket-divider::before,
    .playsell-coupons-section .ticket-divider::after {
        position: absolute !important;
        top: -9px !important;
        width: 17px !important;
        height: 17px !important;
        border: 1px solid #74818a !important;
        border-radius: 50% !important;
        background: var(--coupon-page, #11151d) !important;
        content: "" !important;
        z-index: 3 !important;
        box-sizing: border-box !important;
        transition: border-color .25s ease !important;
    }

    .playsell-coupons-section .ticket-divider::before {
        left: -9px !important;
        right: auto !important;
    }

    .playsell-coupons-section .ticket-divider::after {
        right: -9px !important;
        left: auto !important;
    }

    .playsell-coupons-section .scope-heading {
        position: relative !important;
        z-index: 1 !important;
        margin: 0 0 4px !important;
        color: #9aa6af !important;
        font-size: .55rem !important;
        font-weight: 850 !important;
        letter-spacing: .12em !important;
        text-transform: uppercase !important;
    }

    .playsell-coupons-section .scope-list {
        position: relative !important;
        z-index: 1 !important;
        display: flex !important;
        align-content: flex-start !important;
        gap: 4px !important;
        flex-wrap: wrap !important;
        overflow: hidden !important;
    }

    .playsell-coupons-section .scope-chip[hidden] {
        display: none !important;
    }

    .playsell-coupons-section .scope-chip {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        max-width: 100% !important;
        min-height: 21px !important;
        padding: 2px 6px 2px 4px !important;
        border: 1px solid #3a464e !important;
        border-radius: 7px !important;
        background: #11191f !important;
        color: #c0c8ce !important;
        font-size: .53rem !important;
        font-weight: 780 !important;
        white-space: nowrap !important;
        box-sizing: border-box !important;
    }

    .playsell-coupons-section .game-mark {
        display: grid !important;
        place-items: center !important;
        width: 15px !important;
        height: 15px !important;
        flex: 0 0 auto !important;
        border: 1px solid #655a3b !important;
        border-radius: 4px !important;
        background: #211f19 !important;
        color: var(--coupon-accent) !important;
        font-size: .5rem !important;
        font-weight: 950 !important;
        line-height: 1 !important;
    }

    .playsell-coupons-section .game-mark svg {
        width: 10px !important;
        height: 10px !important;
        display: block !important;
    }

    .playsell-coupons-section .scope-more {
        display: inline-grid !important;
        place-items: center !important;
        min-width: 25px !important;
        height: 21px !important;
        padding: 0 6px !important;
        border: 1px solid #65717a !important;
        border-radius: 7px !important;
        background: #1a232a !important;
        color: #e2e7e9 !important;
        font-size: .72rem !important;
        font-weight: 900 !important;
        line-height: 1 !important;
        cursor: pointer !important;
        transition: border-color .2s, color .2s, background .2s !important;
    }

    .playsell-coupons-section .scope-more:hover {
        border-color: #b8c2c9 !important;
        background: #202a31 !important;
        color: #fff !important;
    }

    /* Kupon Kodu Alanı */
    .playsell-coupons-section .coupon-code {
        position: relative !important;
        z-index: 1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 7px !important;
        min-height: 36px !important;
        margin-top: auto !important;
        width: 100% !important;
        align-self: stretch !important;
        padding: 0 6px 0 9px !important;
        border: 1px dashed #756641 !important;
        border-radius: 8px !important;
        background: #10161b !important;
        box-sizing: border-box !important;
    }

    .playsell-coupons-section .coupon-code::before {
        position: absolute !important;
        top: 0 !important;
        right: 40px !important;
        bottom: 0 !important;
        width: 0 !important;
        border: none !important;
        border-left: 1px dashed #756641 !important;
        content: "" !important;
        display: block !important;
    }

    .playsell-coupons-section .coupon-code > div {
        position: relative !important;
        z-index: 1 !important;
        display: flex !important;
        align-items: center !important;
        min-width: 0 !important;
    }

    .playsell-coupons-section .coupon-code code {
        color: var(--coupon-accent) !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace !important;
        font-size: .7rem !important;
        font-weight: 850 !important;
        letter-spacing: .035em !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
    }

    .playsell-coupons-section .copy-button {
        display: grid !important;
        place-items: center !important;
        width: 28px !important;
        height: 28px !important;
        flex: 0 0 auto !important;
        border: 1px solid #3a464e !important;
        border-radius: 6px !important;
        background: #182127 !important;
        color: #c8d0d6 !important;
        cursor: pointer !important;
        transition: border-color .2s, color .2s, background .2s !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }

    .playsell-coupons-section .copy-button:hover {
        border-color: #8d7947 !important;
        background: #20282d !important;
        color: var(--coupon-accent) !important;
    }

    .playsell-coupons-section .copy-button svg {
        width: 13px !important;
        height: 13px !important;
        display: block !important;
    }

    /* Modal Diyalog */
    .category-dialog {
        width: min(420px, calc(100% - 28px)) !important;
        max-height: min(78vh, 560px) !important;
        overflow: auto !important;
        padding: 0 !important;
        border: 1px solid #46535d !important;
        border-radius: 16px !important;
        background: #151d23 !important;
        color: var(--coupon-text) !important;
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.7) !important;
    }

    .category-dialog::backdrop {
        background: rgba(5, 8, 11, 0.8) !important;
        backdrop-filter: blur(4px) !important;
    }

    .dialog-content {
        padding: 18px !important;
    }

    .dialog-head {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        gap: 14px !important;
        margin-bottom: 15px !important;
    }

    .dialog-head h2 {
        margin: 0 !important;
        font-size: 1rem !important;
        line-height: 1.3 !important;
        color: #f1f4f6 !important;
    }

    .dialog-head p {
        margin: 4px 0 0 !important;
        color: var(--coupon-muted) !important;
        font-size: .75rem !important;
    }

    .dialog-close {
        display: grid !important;
        place-items: center !important;
        width: 32px !important;
        height: 32px !important;
        flex: 0 0 auto !important;
        border: 1px solid #3a464e !important;
        border-radius: 8px !important;
        background: #1b252c !important;
        color: #dce2e5 !important;
        cursor: pointer !important;
    }

    .dialog-close svg {
        width: 15px !important;
        height: 15px !important;
        display: block !important;
    }

    .category-dialog-list {
        --coupon-accent: var(--coupon-accent);
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 7px !important;
    }

    .category-dialog-list .scope-chip {
        min-height: 28px !important;
        padding: 4px 8px 4px 5px !important;
        font-size: .68rem !important;
    }

    /* Toast Bildirim */
    .coupon-toast {
        position: fixed !important;
        top: 24px !important;
        right: 24px !important;
        bottom: auto !important;
        left: auto !important;
        background: rgba(18, 22, 28, 0.96) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-left: 4px solid #c79a3e !important;
        padding: 14px 18px !important;
        border-radius: 12px !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.04) !important;
        opacity: 0 !important;
        transform: translateY(-16px) !important;
        pointer-events: none !important;
        transition: opacity 0.25s ease, transform 0.25s ease !important;
        z-index: 999999 !important;
    }

    .coupon-toast.is-visible {
        opacity: 1 !important;
        transform: translateY(0) !important;
        pointer-events: auto !important;
    }

    @media (max-width: 768px) {
        .coupon-toast {
            top: 16px !important;
            left: 16px !important;
            right: 16px !important;
            bottom: auto !important;
            width: calc(100% - 32px) !important;
            max-width: calc(100vw - 32px) !important;
            margin: 0 auto !important;
        }
    }

    @media (max-width: 980px) {
        .playsell-coupons-section .coupon-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }
        .playsell-coupons-section .coupon-card {
            min-height: 278px !important;
        }
    }

    @media (max-width: 640px) {
        .playsell-coupons-section .coupon-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 8px !important;
        }
        .playsell-coupons-section .coupon-card {
            min-height: 266px !important;
            padding: 7px 8px 8px !important;
            border-radius: 12px !important;
        }
        .playsell-coupons-section .offer-row {
            padding: 1px 0 4px !important;
        }
        .playsell-coupons-section .offer-value {
            gap: 4px !important;
        }
        .playsell-coupons-section .offer-value strong {
            font-size: clamp(1.25rem, 6vw, 1.65rem) !important;
        }
        .playsell-coupons-section .offer-value span {
            font-size: .56rem !important;
        }
        .playsell-coupons-section .offer-mark {
            width: 28px !important;
            height: 28px !important;
            border-radius: 8px !important;
        }
        .playsell-coupons-section .offer-mark svg {
            width: 15px !important;
            height: 15px !important;
        }
        .playsell-coupons-section .coupon-card h2 {
            margin: 2px 0 3px !important;
            font-size: .75rem !important;
            line-height: 1.25 !important;
        }
        .playsell-coupons-section .coupon-description {
            min-height: 28px !important;
            font-size: .55rem !important;
            line-height: 1.34 !important;
        }
        .playsell-coupons-section .ticket-divider {
            width: calc(100% + 18px) !important;
            margin: 7px -9px 6px !important;
        }
        .playsell-coupons-section .scope-heading {
            margin-bottom: 4px !important;
            font-size: .47rem !important;
            letter-spacing: .09em !important;
        }
        .playsell-coupons-section .scope-list {
            gap: 3px !important;
        }
        .playsell-coupons-section .scope-chip {
            gap: 3px !important;
            min-height: 19px !important;
            padding: 2px 4px 2px 3px !important;
            font-size: .46rem !important;
        }
        .playsell-coupons-section .game-mark {
            width: 14px !important;
            height: 14px !important;
            font-size: .43rem !important;
        }
        .playsell-coupons-section .game-mark svg {
            width: 9px !important;
            height: 9px !important;
        }
        .playsell-coupons-section .scope-more {
            min-width: 23px !important;
            height: 19px !important;
            padding: 0 5px !important;
            font-size: .67rem !important;
        }
        .playsell-coupons-section .coupon-code {
            min-height: 32px !important;
            padding: 0 4px 0 6px !important;
            border-radius: 7px !important;
        }
        .playsell-coupons-section .coupon-code::before {
            right: 35px !important;
        }
        .playsell-coupons-section .coupon-code code {
            font-size: clamp(.52rem, 2.8vw, .64rem) !important;
            letter-spacing: 0 !important;
        }
        .playsell-coupons-section .copy-button {
            width: 24px !important;
            height: 24px !important;
            border-radius: 5px !important;
        }
        .playsell-coupons-section .copy-button svg {
            width: 11px !important;
            height: 11px !important;
        }
    }

    @media (max-width: 370px) {
        .playsell-coupons-section .coupon-grid {
            gap: 7px !important;
        }
        .playsell-coupons-section .coupon-card {
            min-height: 256px !important;
            padding: 7px !important;
        }
        .playsell-coupons-section .coupon-card h2 {
            font-size: .7rem !important;
        }
        .playsell-coupons-section .coupon-description {
            font-size: .51rem !important;
        }
        .playsell-coupons-section .scope-chip {
            font-size: .43rem !important;
        }
        .playsell-coupons-section .coupon-code code {
            font-size: .5rem !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .playsell-coupons-section .coupon-card,
        .playsell-coupons-section .copy-button,
        .playsell-coupons-section .scope-more,
        .coupon-toast {
            transition: none !important;
        }
    }
</style>

<section class="playsell-coupons-section">
    <div class="playsell-coupons-container">
        <div class="coupon-grid" aria-label="Kuponlar">
            
            <!-- 1. KART -->
            <article class="coupon-card" data-coupon-id="welcome">
                <div class="offer-row">
                    <div class="offer-value">
                        <strong>%10</strong>
                        <span>indirim</span>
                    </div>
                    <div class="offer-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path d="M4 6h16v3a2 2 0 0 0 0 4v3H4v-3a2 2 0 0 0 0-4V6Z"/>
                            <path d="M12 8v1m0 3v1" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <h2>Hoş Geldin Hediyesi</h2>
                <p class="coupon-description">Yeni üyelere özel, tüm alışverişlerinde anında geçerli %10 indirim.</p>
                <div class="ticket-divider" aria-hidden="true"></div>
                <div class="scope-heading">Geçerli kategoriler</div>
                <div class="scope-list">
                    <span class="scope-chip"><span class="game-mark" aria-hidden="true">✦</span>Tüm oyunlar</span>
                    <span class="scope-chip"><span class="game-mark" aria-hidden="true">₺</span>Cüzdan kodları</span>
                    <span class="scope-chip"><span class="game-mark" aria-hidden="true">E</span>E-pin</span>
                </div>
                <div class="coupon-code">
                    <div><code>MERHABA10</code></div>
                    <button class="copy-button" type="button" data-copy="MERHABA10" aria-label="MERHABA10 kupon kodunu kopyala">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="8" y="8" width="12" height="13" rx="2"/>
                            <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>
                        </svg>
                    </button>
                </div>
            </article>

            <!-- 2. KART -->
            <article class="coupon-card" data-coupon-id="pubg">
                <div class="offer-row">
                    <div class="offer-value">
                        <strong>%15</strong>
                        <span>indirim</span>
                    </div>
                    <div class="offer-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path d="M4 6h16v3a2 2 0 0 0 0 4v3H4v-3a2 2 0 0 0 0-4V6Z"/>
                            <path d="M12 8v1m0 3v1" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <h2>PUBG UC Avantajı</h2>
                <p class="coupon-description">Royale Pass ve UC yüklemelerinde ekstra %15 sepet indirimi.</p>
                <div class="ticket-divider" aria-hidden="true"></div>
                <div class="scope-heading">Geçerli kategoriler</div>
                <div class="scope-list">
                    <span class="scope-chip"><span class="game-mark game-mark--pubg" aria-hidden="true">P</span>PUBG Mobile</span>
                    <span class="scope-chip"><span class="game-mark game-mark--pubg" aria-hidden="true">UC</span>UC yükleme</span>
                    <span class="scope-chip"><span class="game-mark" aria-hidden="true">RP</span>Royale Pass</span>
                </div>
                <div class="coupon-code">
                    <div><code>PUBG15</code></div>
                    <button class="copy-button" type="button" data-copy="PUBG15" aria-label="PUBG15 kupon kodunu kopyala">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="8" y="8" width="12" height="13" rx="2"/>
                            <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>
                        </svg>
                    </button>
                </div>
            </article>

            <!-- 3. KART -->
            <article class="coupon-card" data-coupon-id="diamonds">
                <div class="offer-row">
                    <div class="offer-value">
                        <strong>50₺</strong>
                        <span>indirim</span>
                    </div>
                    <div class="offer-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path d="M4 6h16v3a2 2 0 0 0 0 4v3H4v-3a2 2 0 0 0 0-4V6Z"/>
                            <path d="M12 8v1m0 3v1" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <h2>Elmas Festivali</h2>
                <p class="coupon-description">Mobile Legends ve Free Fire yüklemelerinde anında 50₺ indirim.</p>
                <div class="ticket-divider" aria-hidden="true"></div>
                <div class="scope-heading">Geçerli kategoriler</div>
                <div class="scope-list">
                    <span class="scope-chip"><span class="game-mark game-mark--mlbb" aria-hidden="true">ML</span>Mobile Legends</span>
                    <span class="scope-chip"><span class="game-mark game-mark--freefire" aria-hidden="true">FF</span>Free Fire</span>
                    <span class="scope-chip"><span class="game-mark" aria-hidden="true">◆</span>Elmas yükleme</span>
                </div>
                <div class="coupon-code">
                    <div><code>ELMAS50</code></div>
                    <button class="copy-button" type="button" data-copy="ELMAS50" aria-label="ELMAS50 kupon kodunu kopyala">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="8" y="8" width="12" height="13" rx="2"/>
                            <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>
                        </svg>
                    </button>
                </div>
            </article>

            <!-- 4. KART -->
            <article class="coupon-card" data-coupon-id="weekend">
                <div class="offer-row">
                    <div class="offer-value">
                        <strong>%5</strong>
                        <span>indirim</span>
                    </div>
                    <div class="offer-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path d="M4 6h16v3a2 2 0 0 0 0 4v3H4v-3a2 2 0 0 0 0-4V6Z"/>
                            <path d="M12 8v1m0 3v1" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <h2>Hafta Sonu Kampanyası</h2>
                <p class="coupon-description">Steam, Valorant ve seçili oyun cüzdan kodlarında geçerli indirim.</p>
                <div class="ticket-divider" aria-hidden="true"></div>
                <div class="scope-heading">Geçerli kategoriler</div>
                <div class="scope-list">
                    <span class="scope-chip">
                        <span class="game-mark game-mark--steam" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <circle cx="8" cy="8" r="5"/>
                                <circle cx="17.5" cy="17.5" r="3"/>
                                <path d="m11.5 11.5 4.1 4.1" stroke-linecap="round"/>
                            </svg>
                        </span>Steam
                    </span>
                    <span class="scope-chip">
                        <span class="game-mark game-mark--riot" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="m5 3 6-1 8 3 2 5-3 1 2 3-3 1-1 6-4-1-2-5-3 2-2-3 3-4-4-2 2-3-2-2Zm5 5-2 3 3 1-1 3 3-2 2 2v-4l3-1-3-2-1-3-2 3Z"/>
                            </svg>
                        </span>Riot Games
                    </span>
                    <span class="scope-chip">
                        <span class="game-mark game-mark--valorant" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 3h5l4 8 4-8h5L12 21 3 3Z"/>
                            </svg>
                        </span>VALORANT
                    </span>
                    <span class="scope-chip">
                        <span class="game-mark game-mark--tft" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round">
                                <path d="m12 2 9 5v10l-9 5-9-5V7l9-5Z"/>
                                <path d="m7 9 5-3 5 3m-10 2 5 3 5-3m-5-3v10"/>
                            </svg>
                        </span>Teamfight Tactics
                    </span>
                </div>
                <div class="coupon-code">
                    <div><code>HAFTASONU5</code></div>
                    <button class="copy-button" type="button" data-copy="HAFTASONU5" aria-label="HAFTASONU5 kupon kodunu kopyala">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="8" y="8" width="12" height="13" rx="2"/>
                            <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>
                        </svg>
                    </button>
                </div>
            </article>

        </div>
    </div>
</section>

<!-- Diğer Kategoriler Modal Penceresi -->
<dialog class="category-dialog" id="category-dialog" aria-labelledby="category-dialog-title">
    <div class="dialog-content">
        <div class="dialog-head">
            <div>
                <h2 id="category-dialog-title">Diğer kategoriler</h2>
                <p class="dialog-coupon-name"></p>
            </div>
            <button class="dialog-close" type="button" aria-label="Pencereyi kapat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>
        <div class="category-dialog-list"></div>
    </div>
</dialog>

<!-- Kopyalandı Toast -->
<div class="coupon-toast" id="coupon-toast" role="status" aria-live="polite"></div>

<script>
    (function () {
        const toast = document.getElementById('coupon-toast');
        let toastTimer;

        function showToast(message) {
            if (typeof window.showToast === 'function') {
                window.showToast(message, 'success');
                return;
            }
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
                return;
            }
            if (!toast) return;
            toast.textContent = message;
            toast.classList.add('is-visible');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 2800);
        }

        const categoryDialog = document.querySelector('.category-dialog');
        if (categoryDialog) {
            const dialogList = categoryDialog.querySelector('.category-dialog-list');
            const dialogCouponName = categoryDialog.querySelector('.dialog-coupon-name');
            const closeBtn = categoryDialog.querySelector('.dialog-close');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', () => categoryDialog.close());
            }
            categoryDialog.addEventListener('click', event => {
                if (event.target === categoryDialog) categoryDialog.close();
            });

            const categoryLists = [...document.querySelectorAll('.playsell-coupons-section .scope-list')];
            const overflowLists = [];

            function topRows(elements) {
                return [...new Set(elements.filter(el => !el.hidden).map(el => Math.round(el.getBoundingClientRect().top)))].sort((a, b) => a - b);
            }

            function layoutCategories() {
                overflowLists.forEach(({ list, chips, more }) => {
                    chips.forEach(chip => chip.hidden = false);
                    more.remove();
                });
                overflowLists.length = 0;

                categoryLists.forEach((list, index) => {
                    const chips = [...list.querySelectorAll('.scope-chip')];
                    if (!chips.length) return;
                    list.id = 'coupon-categories-' + (index + 1);
                    const rows = topRows(chips);
                    if (rows.length <= 3) return;

                    const card = list.closest('.coupon-card');
                    const more = document.createElement('button');
                    more.className = 'scope-more';
                    more.type = 'button';
                    more.textContent = '…';
                    more.setAttribute('aria-label', 'Diğer kategorileri göster');
                    more.title = 'Diğer kategorileri göster';
                    more.setAttribute('aria-controls', 'category-dialog');
                    list.append(more);

                    const thirdRow = rows[2];
                    chips.filter(chip => Math.round(chip.getBoundingClientRect().top) > thirdRow).forEach(chip => chip.hidden = true);
                    while (Math.round(more.getBoundingClientRect().top) > thirdRow) {
                        const thirdRowChips = chips.filter(chip => !chip.hidden && Math.round(chip.getBoundingClientRect().top) === thirdRow);
                        if (!thirdRowChips.length) break;
                        thirdRowChips[thirdRowChips.length - 1].hidden = true;
                    }

                    const hiddenChips = () => chips.filter(chip => chip.hidden);
                    more.addEventListener('click', () => {
                        const overflow = hiddenChips();
                        if (dialogCouponName) dialogCouponName.textContent = card.querySelector('h2').textContent;
                        if (dialogList) dialogList.replaceChildren(...overflow.map(chip => {
                            const clone = chip.cloneNode(true);
                            clone.hidden = false;
                            return clone;
                        }));
                        categoryDialog.showModal();
                    });
                    overflowLists.push({ list, chips, more });
                });
            }

            let layoutTimer;
            const scheduleCategoryLayout = () => {
                clearTimeout(layoutTimer);
                layoutTimer = setTimeout(() => requestAnimationFrame(layoutCategories), 80);
            };

            requestAnimationFrame(layoutCategories);
            window.addEventListener('resize', scheduleCategoryLayout, { passive: true });
        }

        async function copyCode(code) {
            try {
                await navigator.clipboard.writeText(code);
                showToast(code + ' panoya kopyalandı.');
            } catch (err) {
                const field = document.createElement('textarea');
                field.value = code;
                field.style.position = 'fixed';
                field.style.opacity = '0';
                document.body.appendChild(field);
                field.select();
                let copied = false;
                try {
                    copied = document.execCommand('copy');
                } catch (e) {}
                field.remove();
                showToast(copied ? code + ' panoya kopyalandı.' : 'Kupon kodu: ' + code);
            }
        }

        document.querySelectorAll('.playsell-coupons-section [data-copy]').forEach(button => {
            button.addEventListener('click', () => copyCode(button.dataset.copy));
        });
    })();
</script>