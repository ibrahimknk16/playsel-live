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

    /* Countdown Timer */
    .countdown-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 25px;
    }
    .countdown-circle {
        position: relative;
        width: 80px;
        height: 80px;
    }
    .countdown-circle svg {
        transform: rotate(-90deg);
        width: 80px;
        height: 80px;
    }
    .countdown-circle circle {
        fill: none;
        stroke-width: 6;
        stroke-linecap: round;
    }
    .countdown-circle .bg {
        stroke: rgba(255, 255, 255, 0.05);
    }
    .countdown-circle .progress {
        stroke: #c79a3e;
        stroke-dasharray: 220; 
        stroke-dashoffset: 0;
        transition: stroke-dashoffset 1s linear;
    }
    .countdown-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 16px;
        font-weight: 600;
        color: #ffffff;
    }
</style>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<div class="tf-section login-page pt-60 pb-60">
    <div class="themesflat-container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">

                <div class="login-wrapper">
                    <div class="login-header">
                        <h2><?= $this->lang->line('verify_2fa_title') ?></h2>
                        <p>
                            <?php if (isset($two_fa_method) && $two_fa_method == 2): ?>
                                <?= $this->lang->line('verify_2fa_desc_sms') ?>
                            <?php else: ?>
                                <?= $this->lang->line('verify_2fa_desc_email') ?>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="countdown-wrapper">
                        <div class="countdown-circle">
                            <svg>
                                <circle class="bg" cx="40" cy="40" r="35"></circle>
                                <circle class="progress" cx="40" cy="40" r="35"></circle>
                            </svg>
                            <div class="countdown-text" id="timerText">03:00</div>
                        </div>
                    </div>

                    <form action="<?= topupcenter() . $uri . '/auth/verify2faRequest' ?>" method="POST" id="verify2faForm">
                        <div class="form-group-custom">
                            <label for="otp_code"><?= $this->lang->line('verify_2fa_code_label') ?></label>
                            <div class="input-icon-wrap">
                                <input type="text" id="otp_code" name="otp_code" class="form-control-custom" placeholder="123456" required autocomplete="off" maxlength="6" pattern="\d{6}">
                                <i class="fa-solid fa-key"></i>
                            </div>
                        </div>

                        <button type="submit" class="btn-login-submit mt-4">
                            <span><?= $this->lang->line('verify_2fa_verify_button') ?></span>
                            <i class="fa-solid fa-check"></i>
                        </button>

                        <a href="<?= topupcenter() . $uri . '/auth/resend_2fa' ?>" id="resendBtn" class="btn-login-submit mt-2" style="display:none; background: #333; color: #fff; margin-top:12px;">
                            <span><?= $this->lang->line('verify_2fa_resend') ?></span>
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>

                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Sessiondan ne kadar zaman kaldigini almak istersek php ile 180 uzerinden hesaplayabiliriz
    // Simdilik gorsel amacli sayfaya girildiginde 3 dakikadan baslatildi
    let timeLeft = 180; 
    const timerText = document.getElementById('timerText');
    const progressCircle = document.querySelector('.countdown-circle .progress');
    const circumference = 2 * Math.PI * 35; // ~219.9
    
    progressCircle.style.strokeDasharray = circumference;

    const interval = setInterval(() => {
        timeLeft--;
        if (timeLeft < 0) {
            clearInterval(interval);
            timerText.innerText = "00:00";
            progressCircle.style.stroke = "#ff3100"; // kirmizi (expired)
            document.getElementById('resendBtn').style.display = 'flex';
            return;
        }

        let m = Math.floor(timeLeft / 60);
        let s = timeLeft % 60;
        timerText.innerText = (m < 10 ? "0"+m : m) + ":" + (s < 10 ? "0"+s : s);

        const offset = circumference - (timeLeft / 180) * circumference;
        progressCircle.style.strokeDashoffset = offset;
    }, 1000);
});
</script>