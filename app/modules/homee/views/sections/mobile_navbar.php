<style>
    :root {
        --nav-bg: rgba(13, 17, 24, 0.85);
        --nav-border: rgba(255, 255, 255, 0.05);
        --text-muted: #748192;
        --text-active: #ffb817;
        --gold-gradient: linear-gradient(110deg, #ffc52e, #ff9d05);
    }

    /* === Global mobile overflow fix & body padding === */
    html,
    body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
    }

    @media (max-width: 850px) {
        body {
            padding-bottom: calc(76px + env(safe-area-inset-bottom, 0px)) !important;
        }
    }

    /* --- MOBİL ALT MENÜ --- */
    .mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 64px;
        background: var(--nav-bg);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-top: 1px solid var(--nav-border);
        display: flex;
        justify-content: space-around;
        align-items: flex-end;
        padding-bottom: env(safe-area-inset-bottom, 0px);
        z-index: 9000;
        box-sizing: border-box;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease;
    }

    /* Alt Menü Gizlendiğinde Aşağı Kayar */
    .mobile-bottom-nav.is-hidden {
        transform: translateY(120%) !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    .mobile-bottom-nav .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        flex: 1;
        height: 100%;
        padding-bottom: 8px;
        color: var(--text-muted);
        text-decoration: none;
        -webkit-tap-highlight-color: transparent;
        position: relative;
        cursor: pointer;
        min-width: 0;
    }

    .mobile-bottom-nav .icon-box {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: absolute;
        top: 4px;
        color: #748192;
    }

    .mobile-bottom-nav .icon-box svg {
        width: 22px;
        height: 22px;
        stroke-width: 1.8;
        fill: none;
        stroke: currentColor;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .mobile-bottom-nav .nav-text {
        font-size: 0.65rem;
        font-weight: 650;
        transition: color 0.3s ease;
        white-space: nowrap;
    }

    /* Aktif Menü Durumu */
    .mobile-bottom-nav .nav-item.active .icon-box {
        background: var(--gold-gradient);
        color: #17130a;
        transform: translateY(-10px);
        box-shadow: 0 6px 16px rgba(255, 157, 5, 0.35), 0 0 0 5px #0b0f13;
    }

    .mobile-bottom-nav .nav-item.active .nav-text {
        color: var(--text-active);
        font-weight: 800;
    }

    @media (min-width: 851px) {
        .mobile-bottom-nav {
            display: none !important;
        }
    }

    /* === Region Modal Mobile Fix === */
    .region-modal-content {
        width: 95% !important;
        max-width: 100vw !important;
        min-height: auto !important;
        max-height: 90vh !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
    }

    .region-modal-body {
        padding: 10px 16px 20px 16px !important;
        overflow-x: hidden !important;
        max-height: 50vh !important;
    }

    .region-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 8px !important;
    }

    .region-modal-header {
        padding: 14px 16px !important;
    }

    .region-modal-tabs {
        padding: 0 16px !important;
    }

    .region-card {
        padding: 10px 10px !important;
        gap: 8px !important;
    }

    .region-card span {
        font-size: 12px !important;
    }

    .region-modal-footer {
        padding: 14px !important;
    }

    .region-confirm-btn {
        width: 100% !important;
    }

    /* === Mobilde JivoChat Yüzen Baloncuğunu Gizle (Sadece Sohbet Açıkken Göster) === */
    @media (max-width: 850px) {

        body:not(.jivo-chat-open) jdiv,
        body:not(.jivo-chat-open) #jvlabelWrap {
            display: none !important;
        }
    }
</style>

<nav class="mobile-bottom-nav" id="bottomNav">
    <?php
    $currentUrl = current_url();
    $isCategories = (strpos($currentUrl, '/categories') !== false);
    $isWallet = (strpos($currentUrl, '/wallet') !== false);
    $isProfile = (strpos($currentUrl, '/profile') !== false || strpos($currentUrl, '/login') !== false) && !$isWallet;
    $isHome = (!$isCategories && !$isProfile && !$isWallet);
    $walletUrl = $this->session->userdata('user') ? topupcenter() . ($uri ?? '') . '/profile/wallet' : topupcenter() . ($uri ?? '') . '/login';
    $loginUrl = $this->session->userdata('user') ? topupcenter() . ($uri ?? '') . '/profile' : topupcenter() . ($uri ?? '') . '/login';
    ?>
    <a href="<?= topupcenter() ?>" class="nav-item <?= $isHome ? 'active' : '' ?>">
        <div class="icon-box">
            <svg viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
        </div>
        <span class="nav-text"><?= $this->lang->line('home_page') ?: 'Anasayfa' ?></span>
    </a>

    <a href="<?= topupcenter() . ($uri ?? '') . '/categories' ?>" class="nav-item <?= $isCategories ? 'active' : '' ?>">
        <div class="icon-box">
            <svg viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                <rect x="3" y="14" width="7" height="7" height="7" rx="1.5"></rect>
            </svg>
        </div>
        <span class="nav-text"><?= $this->lang->line('categories') ?: 'Kategoriler' ?></span>
    </a>

    <a href="<?= $walletUrl ?>" class="nav-item <?= $isWallet ? 'active' : '' ?>">
        <div class="icon-box">
            <svg viewBox="0 0 24 24">
                <rect x="3" y="5" width="18" height="15" rx="2"></rect>
                <path d="M3 8h18M16 14h2"></path>
            </svg>
        </div>
        <span class="nav-text"><?= $this->lang->line('wallet') ?: 'Cüzdan' ?></span>
    </a>

    <a href="javascript:void(0);" onclick="openMobileJivoChat();" class="nav-item" id="mobileSupportBtn">
        <div class="icon-box">
            <svg viewBox="0 0 24 24">
                <path d="M4 13v-2a8 8 0 0 1 16 0v2"></path>
                <rect x="3" y="13" width="4" height="6" rx="1.5"></rect>
                <rect x="17" y="13" width="4" height="6" rx="1.5"></rect>
                <path d="M19 19v1a2 2 0 0 1-2 2h-4"></path>
            </svg>
        </div>
        <span class="nav-text">Destek</span>
    </a>

    <a href="<?= $loginUrl ?>" class="nav-item <?= $isProfile ? 'active' : '' ?>">
        <div class="icon-box">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="4"></circle>
                <path d="M4 20c0-4 4-7 8-7s8 3 8 7"></path>
            </svg>
        </div>
        <span
            class="nav-text"><?= !empty($this->session->userdata('user')) ? ($this->lang->line('my_accounts') ?: 'Hesabım') : ($this->lang->line('login_register') ?: 'Giriş Yap') ?></span>
    </a>
</nav>

<script>
    function openMobileJivoChat() {
        document.body.classList.add('jivo-chat-open');
        if (typeof jivo_api !== 'undefined') {
            jivo_api.open();
        }
    }
    window.jivo_onOpen = function () {
        document.body.classList.add('jivo-chat-open');
    };
    window.jivo_onClose = function () {
        document.body.classList.remove('jivo-chat-open');
    };
</script>