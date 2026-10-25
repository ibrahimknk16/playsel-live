<style>
    /* =========================================
       REGISTER PAGE STYLES
    ========================================= */
    .register-section {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 200px);
        /* Header/Footer payı göz önüne alınarak */
    }

    .register-wrapper {
        background-color: var(--bg-secondary);
        border: 1px solid rgba(255, 255, 255, 0.03);
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        max-width: 500px;
        width: 100%;
        margin: 0 auto;
        margin-top:28px;
    }

    .register-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .register-header h2 {
        color: #ffffff;
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .register-header p {
        color: #aaaaaa;
        font-size: 14px;
        margin: 0;
    }

    .register-header p a {
        color: #f98a2c;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .register-header p a:hover {
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
        /* Temanın esnek yapılarını sıfırlamak için */
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
        /* Kullanıcı ikona tıklasa bile input'un odaklanmasını sağlar */
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
        /* Sol boşluk 45px ikon için, sağ boşluk 45px göz ikonu için */
        padding: 12px 45px 12px 45px;
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

    /* Checkbox & Kvkk */
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
        line-height: 1.5;
        margin: 0;
        cursor: pointer;
        user-select: none;
    }

    .form-check-custom label a {
        color: #ffffff;
        text-decoration: underline;
        transition: color 0.3s ease;
    }

    .form-check-custom label a:hover {
        color: #f98a2c;
    }

    /* Submit Butonu */
    .btn-register-submit {
        width: 100%;
        background: linear-gradient(135deg, #c79a3e 0%, #f98a2c 100%);
        border: none;
        border-radius: 10px;
        padding: 14px;
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-register-submit:hover {
        box-shadow: 0 5px 15px rgba(249, 138, 44, 0.4);
        transform: translateY(-2px);
    }

    .btn-register-submit:active {
        transform: translateY(0);
    }

    .btn-register-submit:disabled,
    .btn-register-submit.disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
        pointer-events: none;
    }

    /* intl-tel-input dark theme fix */
    .iti {
        width: 100%;
    }

    .iti__country-list {
        background-color: #161616;
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #ffffff;
        z-index: 999 !important;
    }

    .iti__country-list .iti__country.iti__highlight {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .iti__country-list .iti__country:hover {
        background-color: #c79a3e;
        color: #000;
    }

    .iti__country-name, .iti__dial-code, .iti__selected-dial-code {
        color: #ffffff;
    }

    /* Gerekirse dropdown içindeki ikonlar vs için */
    .iti__search-input {
        background-color: #0d0d0d;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
</style>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<div class="tf-section register-page pt-60 pb-60">
    <div class="themesflat-container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">

                <div class="register-wrapper">
                    <div class="register-header">
                        <h2><?= $this->lang->line('view_register_title') ?></h2>
                        <p><?= $this->lang->line('view_register_description') ?> <a href="<?= topupcenter() . $uri . '/login' ?>"><?= $this->lang->line('view_register_text') ?></a></p>
                        <?php if (!empty($referral_user)): ?>
                            <p style="margin-top:12px;color:#c79a3e;">
                                <?= ($lang ?? '') === 'tr' ? 'Davet eden:' : 'Referred by:' ?>
                                <strong>@<?= html_escape($referral_user->user_nickname) ?></strong>
                            </p>
                        <?php endif; ?>
                    </div>

                    <a href="<?= base_url('auth/googleLogin') . '?locale=' . rawurlencode($uri) . '&redirect=' . rawurlencode('/' . $uri) ?>" class="btn-register-submit" style="display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none;background:#fff;color:#222;margin-bottom:18px;">
                        <span>Google ile kayıt ol</span>
                    </a>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;color:#888;font-size:13px;">
                        <span style="height:1px;background:rgba(255,255,255,.15);flex:1;"></span>
                        <span>veya</span>
                        <span style="height:1px;background:rgba(255,255,255,.15);flex:1;"></span>
                    </div>

                    <form action="<?= base_url('auth/registerRequest') ?>" method="POST" id="registerForm">

                        <!-- İsim Soyisim -->
                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <div class="form-group-custom">
                                    <label for="name"><?= $this->lang->line('view_register_name') ?: 'İsim' ?></label>
                                    <div class="input-icon-wrap">
                                        <input type="text" id="name" name="name" class="form-control-custom" placeholder="Ahmet" required autocomplete="off" pattern="[a-zA-ZğüşıöçĞÜŞİÖÇ\s]{2,}" title="Lütfen sadece harf giriniz">
                                        <i class="fa-regular fa-user"></i>
                                    </div>
                                    <div class="text-danger mt-1 error-msg" id="error-name" style="display:none; font-size: 13px;"></div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-group-custom">
                                    <label for="surname"><?= $this->lang->line('view_register_surname') ?: 'Soyisim' ?></label>
                                    <div class="input-icon-wrap">
                                        <input type="text" id="surname" name="surname" class="form-control-custom" placeholder="Yılmaz" required autocomplete="off" pattern="[a-zA-ZğüşıöçĞÜŞİÖÇ\s]{2,}" title="Lütfen sadece harf giriniz">
                                        <i class="fa-regular fa-user"></i>
                                    </div>
                                    <div class="text-danger mt-1 error-msg" id="error-surname" style="display:none; font-size: 13px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- E-posta -->
                        <div class="form-group-custom">
                            <label for="email"><?= $this->lang->line('view_login_email') ?></label>
                            <div class="input-icon-wrap">
                                <input type="email" id="email" name="email" class="form-control-custom" placeholder="name@example.com" required autocomplete="off">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <div class="text-danger mt-1 error-msg" id="error-email" style="display:none; font-size: 13px;"></div>
                        </div>

                        <!-- Telefon Numarası -->
                        <div class="form-group-custom" style="z-index: 99;">
                            <label for="phone"><?= $this->lang->line('view_register_tel') ?></label>
                            <div class="input-icon-wrap" id="phone-wrap">
                                <input type="tel" id="phone" name="phone" class="form-control-custom" required autocomplete="off">
                            </div>
                            <div class="text-danger mt-1 error-msg" id="error-phone" style="display:none; font-size: 13px;"></div>
                        </div>

                        <!-- Şifre -->
                        <div class="form-group-custom">
                            <label for="password"><?= $this->lang->line('view_login_password') ?></label>
                            <div class="input-icon-wrap">
                                <input type="password" id="password" name="password" class="form-control-custom" placeholder="••••••••" required autocomplete="off">
                                <i class="fa-solid fa-lock"></i> <i class="fa-regular fa-eye password-toggle-icon"></i>
                            </div>
                            <div class="text-danger mt-1 error-msg" id="error-password" style="display:none; font-size: 13px;"></div>
                        </div>

                        <!-- Şifre Tekrar -->
                        <div class="form-group-custom">
                            <label for="confirm_password"><?= $this->lang->line('view_login_password_again') ?></label>
                            <div class="input-icon-wrap">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control-custom" placeholder="••••••••" required autocomplete="off">
                                <i class="fa-solid fa-lock"></i> <i class="fa-regular fa-eye password-toggle-icon"></i>
                            </div>
                            <div class="text-danger mt-1 error-msg" id="error-confirm-password" style="display:none; font-size: 13px;"></div>
                        </div>

                        <!-- KVKK & Şartlar -->
                        <div class="form-check-custom">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">
                                <?=
                                str_replace(
                                    ['{terms_url}', '{kvkk_url}'],
                                    [site_url($uri . '/user-agreement'), site_url($uri . '/cookie-policy')],
                                    $this->lang->line('register_terms')
                                );
                                ?>
                            </label>
                        </div>

                        <div class="mt-4" style="width: 100%;">
                            <div class="cf-turnstile"
                                data-sitekey="0x4AAAAAADLcnbzDAlq8ip49"
                                data-theme="dark"
                                data-size="flexible"
                                data-action="register"
                                data-callback="onTurnstileSuccessRegister">
                            </div>
                        </div>
                        <?php if (isset($turnstile_error)): ?>
                            <div class="text-danger mt-1"><?= $turnstile_error ?></div>
                        <?php endif; ?>


                        <!-- Gönder Butonu -->
                        <button type="submit" class="btn-register-submit mt-4">
                            <span><?= $this->lang->line('view_register_enter') ?></span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>

                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    let turnstileRegisterToken = null;
    let isRegisterSubmitting = false;

    window.onTurnstileSuccessRegister = function(token) {
        turnstileRegisterToken = token;
        if (isRegisterSubmitting) {
            const form = document.getElementById('registerForm');
            if (form) {
                form.submit();
            }
        }
    };

    document.addEventListener("DOMContentLoaded", function() {
        const phoneInput = document.querySelector("#phone");
        const iti = window.intlTelInput(phoneInput, {
            initialCountry: "tr",
            preferredCountries: ["tr", "us", "gb", "de"],
            separateDialCode: true,
            strictMode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/23.0.4/js/utils.js"
        });

        // İsim ve Soyisim sadece harf kısıtlaması (Türkçe ve Latin karakterler)
        const nameInput = document.getElementById('name');
        const surnameInput = document.getElementById('surname');
        const letterOnlyRegex = /[^a-zA-ZğüşıöçĞÜŞİÖÇ\s]/g;

        [nameInput, surnameInput].forEach(function(input) {
            if (!input) return;
            input.addEventListener('input', function() {
                this.value = this.value.replace(letterOnlyRegex, '');
            });
            input.addEventListener('paste', function() {
                setTimeout(() => {
                    this.value = this.value.replace(letterOnlyRegex, '');
                }, 0);
            });
        });

        // Form doğrulama ve uluslararası format gönderimi
        const registerForm = document.getElementById('registerForm');
        registerForm.addEventListener('submit', function(e) {
            let valid = true;

            // Hataları temizle
            document.querySelectorAll('.error-msg').forEach(function(el) {
                el.style.display = 'none';
                el.textContent = '';
            });

            // İsim Doğrulama
            const nameVal = nameInput ? nameInput.value.trim() : '';
            const nameRegex = /^[a-zA-ZğüşıöçĞÜŞİÖÇ\s]{2,}$/;
            if (!nameRegex.test(nameVal)) {
                const errName = document.getElementById('error-name');
                if (errName) {
                    errName.textContent = 'İsim sadece harflerden oluşmalı ve en az 2 karakter olmalıdır.';
                    errName.style.display = 'block';
                }
                valid = false;
            }

            // Soyisim Doğrulama
            const surnameVal = surnameInput ? surnameInput.value.trim() : '';
            if (!nameRegex.test(surnameVal)) {
                const errSurname = document.getElementById('error-surname');
                if (errSurname) {
                    errSurname.textContent = 'Soyisim sadece harflerden oluşmalı ve en az 2 karakter olmalıdır.';
                    errSurname.style.display = 'block';
                }
                valid = false;
            }

            // Telefon Doğrulama
            if (!iti.isValidNumber()) {
                const errPhone = document.getElementById('error-phone');
                if (errPhone) {
                    errPhone.textContent = 'Lütfen geçerli bir telefon numarası giriniz.';
                    errPhone.style.display = 'block';
                }
                valid = false;
            } else {
                phoneInput.value = iti.getNumber();
            }

            // E-posta Doğrulama
            const emailInput = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput)) {
                const errEmail = document.getElementById('error-email');
                if (errEmail) {
                    errEmail.textContent = 'Lütfen geçerli bir e-posta adresi giriniz.';
                    errEmail.style.display = 'block';
                }
                valid = false;
            }

            // Şifre Doğrulama (En az 1 büyük harf, 1 sayı, 1 özel sembol)
            const passwordInput = document.getElementById('password').value;
            const passRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/;
            if (!passRegex.test(passwordInput)) {
                const errPass = document.getElementById('error-password');
                if (errPass) {
                    errPass.textContent = 'Şifreniz en az 1 büyük harf, 1 rakam ve 1 özel sembol içermelidir.';
                    errPass.style.display = 'block';
                }
                valid = false;
            }

            // Şifre Eşleşme Kontrolü
            const confirmPasswordInput = document.getElementById('confirm_password').value;
            if (passwordInput !== confirmPasswordInput) {
                const errConfirm = document.getElementById('error-confirm-password');
                if (errConfirm) {
                    errConfirm.textContent = 'Şifreler eşleşmiyor. Lütfen aynı şifreyi giriniz.';
                    errConfirm.style.display = 'block';
                }
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
                return;
            }

            // Turnstile Doğrulama Kontrolü
            const tokenInput = registerForm.querySelector('[name="cf-turnstile-response"]');
            const hasToken = (tokenInput && tokenInput.value && tokenInput.value.trim() !== '') || turnstileRegisterToken;

            if (!hasToken) {
                e.preventDefault();
                isRegisterSubmitting = true;

                const submitBtn = registerForm.querySelector('.btn-register-submit');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('disabled');
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> <span>Doğrulanıyor...</span>';
                }

                setTimeout(function() {
                    if (isRegisterSubmitting && !turnstileRegisterToken) {
                        isRegisterSubmitting = false;
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('disabled');
                            submitBtn.innerHTML = '<span><?= $this->lang->line('view_register_enter') ?></span> <i class="fa-solid fa-arrow-right"></i>';
                        }
                    }
                }, 7000);
            }
        });
    });
</script>