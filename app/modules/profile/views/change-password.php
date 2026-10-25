<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
        <section class="view" id="view-settings">
            <header class="page-hero">
                <span class="hero-icon"><svg class="icon"><use href="#i-lock"/></svg></span>
                <div class="hero-copy">
                    <h1>Şifre Değişikliği</h1>
                    <p>Hesap güvenliğiniz için şifrenizi periyodik olarak değiştirin.</p>
                </div>
            </header>
            <section class="subnav-shell">
                <div class="settings-body">
                    <section class="settings-panel">
                        <h2>Şifre değişikliği</h2>
                        <form action="<?= base_url($this->uri->segment(1) . '/profile/change-password') ?>" method="POST">
                            <div class="form-grid">
                                <div class="field">
                                    <label for="current-password">Mevcut Şifreniz</label>
                                    <input id="current-password" type="password" name="current_password" autocomplete="current-password" placeholder="Mevcut şifrenizi girin" required>
                                </div>
                                <div></div>
                                <div class="field">
                                    <label for="new-password">Yeni Şifre</label>
                                    <input id="new-password" type="password" name="new_password" autocomplete="new-password" placeholder="Yeni şifrenizi belirleyin" required>
                                </div>
                                <div class="field">
                                    <label for="confirm-password">Yeni Şifre (tekrar)</label>
                                    <input id="confirm-password" type="password" name="confirm_password" autocomplete="new-password" placeholder="Yeni şifrenizi tekrar girin" required>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button class="primary-button" type="submit"><svg class="icon"><use href="#i-lock"/></svg>Şifremi güncelle</button>
                            </div>
                        </form>
                    </section>
                </div>
            </section>
        </section>
    </section>
</main>