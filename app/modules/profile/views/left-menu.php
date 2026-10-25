<?php $this->load->view('profile/profile-style'); ?>

<?php
$fullName = trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''));
if ($fullName === '') {
    $fullName = 'Kullanıcı';
}
?>
<aside class="sidebar" aria-label="Hesap menüsü">
    <button class="profile-user-card" id="profile-menu-toggle" type="button" aria-label="Hesap menüsünü aç veya kapat" aria-expanded="false" aria-controls="account-nav">
        <span class="avatar"><?= mb_substr($fullName, 0, 2) ?></span>
        <span class="user-copy">
            <strong><?= html_escape($fullName) ?></strong>
            <small><?= html_escape($user['user_email'] ?? '') ?></small>
        </span>
        <svg class="icon profile-mobile-arrow" aria-hidden="true"><use href="#i-chevron"/></svg>
    </button>
    <div class="profile-menus-wrapper" id="account-nav">
        <div class="nav-group-title"><?= $this->lang->line('general') ?? 'Genel' ?></div>
        <nav class="account-nav" aria-label="Genel menüsü">
            <a href="<?= base_url($this->uri->segment(1) . '/profile') ?>" class="nav-link <?= ($this->uri->segment(3) == '' || $this->uri->segment(3) == 'index') ? 'active' : '' ?>">
                <svg class="icon" aria-hidden="true"><use href="#i-grid"/></svg><span><?= $this->lang->line('control_panel') ?? 'Kontrol Paneli' ?></span>
            </a>
            <a href="<?= base_url($this->uri->segment(1) . '/profile/orders') ?>" class="nav-link <?= ($this->uri->segment(3) == 'orders' || $this->uri->segment(3) == 'order-detail') ? 'active' : '' ?>">
                <svg class="icon" aria-hidden="true"><use href="#i-bag"/></svg><span><?= $this->lang->line('orders') ?? 'Siparişlerim' ?></span>
            </a>
            <a href="<?= base_url($this->uri->segment(1) . '/profile/transactions') ?>" class="nav-link <?= ($this->uri->segment(3) == 'transactions') ? 'active' : '' ?>">
                <svg class="icon" aria-hidden="true"><use href="#i-history"/></svg><span><?= $this->lang->line('balance_history') ?? 'Bakiye Hareketleri' ?></span>
            </a>
            <a href="<?= base_url($this->uri->segment(1) . '/profile/tickets') ?>" class="nav-link <?= ($this->uri->segment(3) == 'tickets' || $this->uri->segment(3) == 'ticket-detail') ? 'active' : '' ?>">
                <svg class="icon" aria-hidden="true"><use href="#i-headset"/></svg><span>Destek Taleplerim</span>
            </a>
        </nav>

        <div class="nav-group-title"><?= $this->lang->line('finance') ?? 'Finans' ?></div>
        <nav class="account-nav" aria-label="Finans menüsü">
            <a href="<?= base_url($this->uri->segment(1) . '/profile/wallet') ?>" class="nav-link <?= ($this->uri->segment(3) == 'wallet') ? 'active' : '' ?>">
                <svg class="icon" aria-hidden="true"><use href="#i-wallet"/></svg><span><?= $this->lang->line('wallet') ?? 'Cüzdan' ?></span>
            </a>
            <a href="<?= base_url($this->uri->segment(1) . '/profile/payments') ?>" class="nav-link <?= ($this->uri->segment(3) == 'payments') ? 'active' : '' ?>">
                <svg class="icon" aria-hidden="true"><use href="#i-cash"/></svg><span><?= $this->lang->line('payments') ?? 'Ödemeler' ?></span>
            </a>
            <a href="<?= base_url($this->uri->segment(1) . '/profile/withdrawals') ?>" class="nav-link <?= ($this->uri->segment(3) == 'withdrawals') ? 'active' : '' ?>">
                <svg class="icon" aria-hidden="true"><use href="#i-card"/></svg><span><?= $this->lang->line('withdrawals') ?? 'Nakit Çekimler' ?></span>
            </a>
        </nav>

        <div class="nav-group-title">İş Ortaklığı</div>
        <nav class="account-nav" aria-label="İş Ortaklığı menüsü">
            <a href="<?= base_url($this->uri->segment(1) . '/profile/streamer') ?>" class="nav-link <?= ($this->uri->segment(3) == 'streamer') ? 'active' : '' ?>">
                <svg class="icon" aria-hidden="true"><use href="#i-ticket"/></svg><span>Yayıncı Başvurusu</span>
            </a>
        </nav>

        <div class="nav-group-title"><?= $this->lang->line('account') ?? 'Hesap' ?></div>
        <nav class="account-nav" aria-label="Hesap menüsü">
            <a href="<?= base_url($this->uri->segment(1) . '/profile/settings') ?>" class="nav-link <?= ($this->uri->segment(3) == 'settings' || $this->uri->segment(3) == 'change-password' || $this->uri->segment(3) == 'referral') ? 'active' : '' ?>">
                <svg class="icon" aria-hidden="true"><use href="#i-settings"/></svg><span><?= $this->lang->line('settings') ?? 'Ayarlar' ?></span>
            </a>
            <a href="<?= base_url($this->uri->segment(1) . '/auth/logout') ?>" class="nav-link logout">
                <svg class="icon" aria-hidden="true"><use href="#i-exit"/></svg><span><?= $this->lang->line('logout') ?? 'Çıkış' ?></span>
            </a>
        </nav>
    </div>
</aside>