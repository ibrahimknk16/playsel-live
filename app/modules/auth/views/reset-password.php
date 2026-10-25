<style>
    /* =========================================
       RESET PASSWORD PAGE STYLES
    ========================================= */
    .login-section {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 200px);
    }

    .login-wrapper {
        background-color: var(--bg-secondary);
        border: 1px solid rgba(255, 255, 255, 0.03);
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        max-width: 450px;
        width: 100%;
        margin: 0 auto;
        margin-top:28px;
    }

    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .login-header h2 {
        color: #ffffff;
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .login-header p {
        color: #aaaaaa;
        font-size: 14px;
        margin: 0;
    }

    /* Input Grupları */
    .form-group-custom {
        margin-bottom: 20px;
        position: relative;
    }

    .form-group-custom label {
        display: block;
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .input-icon-wrap {
        position: relative;
        display: block;
    }

    .input-icon-wrap i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #666666;
        font-size: 16px;
        transition: color 0.3s ease;
        pointer-events: none;
        z-index: 2;
    }

    .input-icon-wrap i.password-toggle-icon {
        left: auto;
        right: 2px;
        top: 50%;
        transform: translateY(-50%);
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 5;
        pointer-events: auto !important;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
        cursor: pointer;
    }

    .input-icon-wrap i.password-toggle-icon:hover {
        color: #c79a3e;
    }

    .form-control-custom {
        width: 100%;
        background-color: #0d0d0d;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 12px 45px !important;
        color: #ffffff;
        font-size: 14px;
        font-weight: 500;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    /* iOS Safari otomatik zoom/scale engelleme (font-size >= 16px) */
    @media (max-width: 768px) {
        .form-control-custom {
            font-size: 16px !important;
        }
    }

    .form-control-custom:focus {
        outline: none;
        border-color: #c79a3e;
        box-shadow: 0 0 0 3px rgba(199, 154, 62, 0.15);
    }

    .form-control-custom:focus+i {
        color: #c79a3e;
    }

    /* Submit Butonu */
    .btn-login-submit {
        width: 100%;
        background: linear-gradient(135deg, #c79a3e 0%, #f98a2c 100%);
        border: none;
        border-radius: 10px;
        padding: 14px;
        color: #ffffff;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-login-submit:hover {
        box-shadow: 0 5px 15px rgba(249, 138, 44, 0.4);
        transform: translateY(-2px);
    }

    .btn-login-submit:active {
        transform: translateY(0);
    }
</style>

<div class="tf-section login-page pt-60 pb-60">
    <div class="themesflat-container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">

                <div class="login-wrapper">
                    <div class="login-header">
                        <h2><?= $this->lang->line('reset_password_title') ?></h2>
                        <p><?= $this->lang->line('reset_password_description') ?></p>
                    </div>

                    <form action="<?= topupcenter() . $this->uri->                    segment(1) . '/auth/resetPassword?token=' . rawurlencode((string)$token) ?>" method="POST" id="resetform">
                                            <input type="hidden" name="token" value="<?= html_escape($token) ?>">
                        
                        <div class="form-group-custom">
                            <label for="reset-pass"><?= $this->lang->line('password_label') ?> *</label>
                            <div class="input-icon-wrap">
                                <input type="password" id="reset-pass" name="password" class="form-control-custom" placeholder="<?= $this->lang->line('min_character') ?>" required>
                                <i class="fa-solid fa-lock"></i>
                                <i class="fa-regular fa-eye password-toggle-icon" id="toggle-password"></i>
                            </div>
                            <div class="text-danger mt-1" style="font-size:12px;" id="error-pass1"></div>
                        </div>

                        <div class="form-group-custom">
                            <label for="reset-pass2"><?= $this->lang->line('passconfirm_label') ?> *</label>
                            <div class="input-icon-wrap">
                                <input type="password" id="reset-pass2" name="confirm_password" class="form-control-custom" placeholder="<?= $this->lang->line('passconfirm_label') ?>" required>
                                <i class="fa-solid fa-lock"></i>
                                <i class="fa-regular fa-eye password-toggle-icon" id="toggle-password2"></i>
                            </div>
                            <div class="text-danger mt-1" style="font-size:12px;" id="error-pass2"></div>
                        </div>

                        <button type="submit" class="btn-login-submit mt-4">
                            <span><?= $this->lang->line('save_button') ?></span>
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('resetform').addEventListener('submit', function(e) {
        const pass = document.getElementById('reset-pass').value;
        const confirmPass = document.getElementById('reset-pass2').value;

        let valid = true;

        if (!isValidPassword(pass)) {
            showError('error-pass1', 'Şifre en az 8 karakter, büyük/küçük harf, sayı ve özel karakter içermelidir.');
            valid = false;
        } else {
            showError('error-pass1', '');
        }

        if (!passwordsMatch(pass, confirmPass)) {
            showError('error-pass2', 'Şifreler eşleşmiyor.');
            valid = false;
        } else {
            showError('error-pass2', '');
        }

        if (!valid) e.preventDefault();
    });

    function isValidPassword(password) {
        const passwordRegex = /^(?=.{8,})(?=.*[A-ZÇĞİÖŞÜ])(?=.*[a-zçğıöşü])(?=.*\d)(?=.*[^A-Za-zçğıöşüÇĞİÖŞÜ\d]).+$/u;
        return passwordRegex.test(password);
    }

    function passwordsMatch(pass1, pass2) {
        return pass1 === pass2;
    }

    function showError(inputId, message) {
        const errorEl = document.getElementById(inputId);
        errorEl.innerText = message;
    }
</script>
