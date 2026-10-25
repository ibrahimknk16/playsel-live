<style>
    /* =========================================
       FORGOT PASSWORD PAGE STYLES
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
        font-weight: 600;
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

    .form-control-custom {
        width: 100%;
        background-color: #0d0d0d;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 12px 16px 12px 45px !important;
        color: #ffffff;
        font-size: 14px;
        font-weight: 600;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-control-custom:focus {
        outline: none;
        border-color: #c79a3e;
        box-shadow: 0 0 0 3px rgba(199, 154, 62, 0.15);
    }

    .form-control-custom:focus+i {
        color: #c79a3e;
    }

    .login-meta {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 25px;
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
                        <h2>Şifremi Unuttum</h2>
                        <p>Şifrenizi sıfırlamak için e-posta adresinizi girin.</p>
                    </div>

                    <form action="<?= topupcenter() . $this->uri->segment(1) . '/auth/forgotPassword' ?>" method="POST" id="loginform">
                        <?php if (!empty($this->input->get('redirect'))): ?>
                            <input type="hidden" name="redirect" value="<?= html_escape($this->input->get('redirect')) ?>">
                        <?php endif; ?>
                        
                        <div class="form-group-custom">
                            <label for="email">E-posta Adresi *</label>
                            <div class="input-icon-wrap">
                                <input type="email" id="email" name="email" class="form-control-custom" placeholder="E-posta adresiniz" required autocomplete="email">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                        </div>

                        <div class="mt-4" style="width: 100%;">
                            <div class="cf-turnstile"
                                data-sitekey="0x4AAAAAADLcnbzDAlq8ip49"
                                data-theme="dark"
                                data-size="flexible"
                                data-action="forgot_password"
                                data-callback="onTurnstileSuccessForgot">
                            </div>
                        </div>
                        <?php if (isset($turnstile_error)): ?>
                            <div class="text-danger mt-1"><?= $turnstile_error ?></div>
                        <?php endif; ?>

                        <button type="submit" class="btn-login-submit mt-4" id="btnForgotSubmit">
                            <span>Şifremi Sıfırla</span>
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>

                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
let turnstileForgotToken = null;
let isForgotSubmitting = false;

window.onTurnstileSuccessForgot = function(token) {
    turnstileForgotToken = token;
    if (isForgotSubmitting) {
        var form = document.getElementById('loginform');
        if (form) {
            form.submit();
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('loginform');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        var tokenInput = form.querySelector('[name="cf-turnstile-response"]');
        var hasToken = (tokenInput && tokenInput.value && tokenInput.value.trim() !== '') || turnstileForgotToken;

        if (!hasToken) {
            e.preventDefault();
            isForgotSubmitting = true;

            var submitBtn = document.getElementById('btnForgotSubmit') || form.querySelector('.btn-login-submit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled');
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> <span>Doğrulanıyor...</span>';
            }

            setTimeout(function() {
                if (isForgotSubmitting && !turnstileForgotToken) {
                    isForgotSubmitting = false;
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('disabled');
                        submitBtn.innerHTML = '<span>Şifremi Sıfırla</span> <i class="fa-solid fa-paper-plane"></i>';
                    }
                }
            }, 7000);
        }
    });
});
</script>