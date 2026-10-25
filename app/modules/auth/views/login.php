<style>
    /* =========================================
       LOGIN PAGE STYLES
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

    .login-header p a {
        color: #f98a2c;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .login-header p a:hover {
        color: #c79a3e;
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

    .input-icon-wrap .password-toggle-icon {
        position: absolute;
        right: 2px;
        left: auto;
        top: 50%;
        transform: translateY(-50%);
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666666;
        font-size: 16px;
        cursor: pointer;
        z-index: 5;
        pointer-events: auto !important;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
        transition: color 0.3s ease;
    }

    .input-icon-wrap .password-toggle-icon:hover {
        color: #c79a3e;
    }

    .form-control-custom {
        width: 100%;
        background-color: #0d0d0d;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 12px 45px 12px 45px !important;
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

    /* Beni Hatırla & Şifremi Unuttum */
    .login-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .form-check-custom {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 25px;
        cursor: pointer;
    }

    .form-check-custom input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        position: relative;
        top: 2px;
        flex-shrink: 0;
        accent-color: #c79a3e;
    }

    .form-check-custom label {
        color: #aaaaaa;
        font-size: 13px;
        margin: 0;
        cursor: pointer;
        user-select: none;
    }

    .forgot-password-link {
        color: #aaaaaa;
        font-size: 13px;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .forgot-password-link:hover {
        color: #f98a2c;
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

    .btn-login-submit:disabled,
    .btn-login-submit.disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
        pointer-events: none;
    }
</style>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<div class="tf-section login-page pt-60 pb-60">
    <div class="themesflat-container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">

                <div class="login-wrapper">
                    <div class="login-header">
                        <h2><?= $this->lang->line('view_login_title') ?></h2>
                        <p><?= $this->lang->line('view_login_description') ?> <a href="<?= topupcenter() . $uri . '/register' ?>">
                            <?= $this->lang->line('view_login_text') ?>
                        </a></p>
                    </div>

                    <form action="<?= base_url('auth/loginRequest') ?>" method="POST" id="loginForm">
                        <input type="hidden" name="redirect" value="<?= !empty($this->input->get('redirect')) ? htmlspecialchars($this->input->get('redirect')) : '' ?>">
                        <a href="<?= base_url('auth/googleLogin') . '?locale=' . rawurlencode($uri) . (!empty($this->input->get('redirect')) ? '&redirect=' . rawurlencode($this->input->get('redirect')) : '') ?>" class="btn-login-submit" style="text-decoration:none;background:#fff;color:#222;margin-bottom:18px;">
                            <span>Google ile giriş yap</span>
                        </a>
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;color:#888;font-size:13px;">
                            <span style="height:1px;background:rgba(255,255,255,.15);flex:1;"></span>
                            <span>veya</span>
                            <span style="height:1px;background:rgba(255,255,255,.15);flex:1;"></span>
                        </div>
                        <div class="form-group-custom">
                            <label for="email"><?= $this->lang->line('view_login_email') ?></label>
                            <div class="input-icon-wrap">
                                <input type="email" id="email" name="email" class="form-control-custom" placeholder="name@example.com" required autocomplete="email">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                        </div>

                        <div class="form-group-custom">
                            <label for="password"><?= $this->lang->line('view_login_password') ?></label>
                            <div class="input-icon-wrap">
                                <input type="password" id="password" name="password" class="form-control-custom" placeholder="••••••••" required autocomplete="current-password">
                                <i class="fa-solid fa-lock"></i> <i class="fa-regular fa-eye password-toggle-icon"></i>
                            </div>
                        </div>

                        <div class="login-meta">
                            <div class="form-check-custom">
                                <input type="checkbox" id="remember_me" name="remember_me">
                                <label for="remember_me"><?= $this->lang->line('view_login_remind_me') ?></label>
                            </div>
                            <a href="<?= topupcenter() . $uri . '/forgot-password' ?>" class="forgot-password-link"><?= $this->lang->line('view_login_forgot_password') ?></a>
                        </div>

                        <div class="mt-4" style="width: 100%;">
                            <div class="cf-turnstile"
                                data-sitekey="0x4AAAAAADLcnbzDAlq8ip49"
                                data-theme="dark"
                                data-size="flexible"
                                data-action="login"
                                data-callback="onTurnstileSuccessLogin">
                            </div>
                        </div>
                        <?php if (isset($turnstile_error)): ?>
                            <div class="text-danger mt-1"><?= $turnstile_error ?></div>
                        <?php endif; ?>

                        <button type="submit" class="btn-login-submit mt-4" id="btnLoginSubmit">
                            <span><?= $this->lang->line('view_login_enter') ?></span>
                            <i class="fa-solid fa-right-to-bracket"></i>
                        </button>

                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
let turnstileLoginToken = null;
let isLoginSubmitting = false;

window.onTurnstileSuccessLogin = function(token) {
    turnstileLoginToken = token;
    if (isLoginSubmitting) {
        var form = document.getElementById('loginForm');
        if (form) {
            form.submit();
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        var tokenInput = form.querySelector('[name="cf-turnstile-response"]');
        var hasToken = (tokenInput && tokenInput.value && tokenInput.value.trim() !== '') || turnstileLoginToken;

        if (!hasToken) {
            e.preventDefault();
            isLoginSubmitting = true;

            var submitBtn = document.getElementById('btnLoginSubmit') || form.querySelector('.btn-login-submit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled');
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> <span>Doğrulanıyor...</span>';
            }

            setTimeout(function() {
                if (isLoginSubmitting && !turnstileLoginToken) {
                    isLoginSubmitting = false;
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('disabled');
                        submitBtn.innerHTML = '<span><?= $this->lang->line('view_login_enter') ?></span> <i class="fa-solid fa-right-to-bracket"></i>';
                    }
                }
            }, 7000);
        }
    });
});
</script>

<?php if ($this->session->userdata('temp_terms_user_id')): ?>
<style>
.custom-terms-overlay {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0, 0, 0, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999999;
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease-out;
}
.custom-terms-box {
    background-color: #1a1a1a;
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.8);
    animation: popIn 0.3s ease-out;
    display: flex;
    flex-direction: column;
}
.custom-terms-header {
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding: 20px 30px;
}
.custom-terms-title {
    color: #c79a3e;
    font-size: 20px;
    font-weight: 800;
    margin: 0;
}
.custom-terms-body {
    padding: 30px;
    color: #aaaaaa;
}
.custom-terms-footer {
    border-top: 1px solid rgba(255,255,255,0.05);
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    gap: 15px;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes popIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>

<div class="custom-terms-overlay" id="termsModalOverlay">
  <div class="custom-terms-box">
      <div class="custom-terms-header">
        <h5 class="custom-terms-title">Kullanıcı Sözleşmesi Onayı</h5>
      </div>
      <div class="custom-terms-body">
        <p style="font-size: 15px; margin-bottom: 15px;">Sisteme giriş yapabilmek için Kullanıcı Sözleşmesini onaylamanız gerekmektedir.</p>
        <p style="margin-bottom: 25px;"><a href="<?= topupcenter() . $uri . '/user-agreement' ?>" target="_blank" style="color: #f98a2c; text-decoration: underline; font-weight: 600;">Sözleşme detaylarını okumak için tıklayınız</a></p>
        
        <div class="form-check-custom" style="display: flex; align-items: center; gap: 10px; margin-bottom: 0;">
            <input type="checkbox" id="modalTermsCheck" style="width: 20px; height: 20px; cursor: pointer; accent-color: #c79a3e; margin:0;">
            <label for="modalTermsCheck" style="font-weight: 600; cursor: pointer; user-select: none; color: #ffffff; font-size: 15px; margin: 0;">
                Okudum, Onaylıyorum
            </label>
        </div>
      </div>
      <div class="custom-terms-footer">
        <a href="<?= topupcenter() . $uri . '/auth/logout' ?>" class="btn" style="color: #ff4d4d; border: 1px solid #ff4d4d; padding: 10px 20px; border-radius: 8px; flex: 1; text-align: center;">Vazgeç (Çıkış)</a>
        <a href="<?= topupcenter() . $uri . '/auth/acceptUserAgreement' ?>" class="btn-login-submit disabled" id="btnModalAcceptTerms" style="width: auto; flex: 2; padding: 10px 30px; margin: 0; opacity: 0.5; pointer-events: none; text-align: center;">Sözleşmeyi Kabul Et</a>
      </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var agreeCheck = document.getElementById('modalTermsCheck');
    var btnAccept = document.getElementById('btnModalAcceptTerms');
    if (agreeCheck && btnAccept) {
        agreeCheck.addEventListener('change', function() {
            if (this.checked) {
                btnAccept.classList.remove('disabled');
                btnAccept.style.opacity = '1';
                btnAccept.style.pointerEvents = 'auto';
            } else {
                btnAccept.classList.add('disabled');
                btnAccept.style.opacity = '0.5';
                btnAccept.style.pointerEvents = 'none';
            }
        });
    }
});
</script>
<?php endif; ?>