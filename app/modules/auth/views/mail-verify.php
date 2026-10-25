<div class="tf-section-2 pt-60 widget-box-icon">
    <div class="themesflat-container">
        <div class="row">
            <div class="col-md-12">
                <div class="heading-section-1">
                    <h2 class="tf-title pb-16" style="perspective: 400px;">
                        <div style="display: block; text-align: center; position: relative; transform-origin: 460px 27.5px; transform: translate3d(0px, 0px, 0px); opacity: 1;">
                            Email Verification
                        </div>
                    </h2>
                </div>
            </div>
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="widget-login">
                    <form id="loginform" class="comment-form" action="<?= topupcenter() . $this->uri->segment(1) . '/auth/mailVerifySendMail' ?>" method="post">
                        <?php if (!empty($this->input->get('redirect'))): ?>
                            <input type="hidden" name="redirect" value="<?= html_escape($this->input->get('redirect')) ?>">
                        <?php endif; ?>
                        <h3 class="mb-4">Email Verification</h3>
                        <fieldset class="email">
                            <label><?= $this->lang->line('email_label') ?> *</label>
                            <input class="tuc-login-input" type="email" id="login-email" placeholder="<?= $this->lang->line('email_label') ?>" name="email" tabindex="2" aria-required="true" required>
                        </fieldset>
                        <div class="cf-turnstile mb-4 text-center"
                            data-sitekey="0x4AAAAAABiDiK6hOhpaXSis"
                            data-theme="dark"
                            data-action="mail_verify"
                            data-callback="onTurnstileSuccessMailVerify">
                        </div>
                        <?php if (isset($turnstile_error)): ?>
                            <div class="text-danger mt-1"><?= $turnstile_error ?></div>
                        <?php endif; ?>
                        <div class="btn-submit mb-30 d-flex justify-content-center">
                            <button class="tf-button style-1" id="btnMailVerifySubmit" style="width:30%;" type="submit"><?= $this->lang->line('login_button') ?><i class="icon-arrow-up-right2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let turnstileMailVerifyToken = null;
let isMailVerifySubmitting = false;

window.onTurnstileSuccessMailVerify = function(token) {
    turnstileMailVerifyToken = token;
    if (isMailVerifySubmitting) {
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
        var hasToken = (tokenInput && tokenInput.value && tokenInput.value.trim() !== '') || turnstileMailVerifyToken;

        if (!hasToken) {
            e.preventDefault();
            isMailVerifySubmitting = true;

            var submitBtn = document.getElementById('btnMailVerifySubmit') || form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled');
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Doğrulanıyor...';
            }

            setTimeout(function() {
                if (isMailVerifySubmitting && !turnstileMailVerifyToken) {
                    isMailVerifySubmitting = false;
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('disabled');
                        submitBtn.innerHTML = '<?= $this->lang->line('login_button') ?><i class="icon-arrow-up-right2"></i>';
                    }
                }
            }, 7000);
        }
    });
});
</script>