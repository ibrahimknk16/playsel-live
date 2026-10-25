<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-ref" data-view="ref">
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-link"/></svg></span>
            <div class="hero-copy">
                <h1><?= $this->lang->line('profile_ref_title') ?></h1>
                <p><?= $this->lang->line('profile_ref_page_desc') ?></p>
            </div>
        </header>

        <section class="panel">
          <div class="panel-head">
            <h2 class="subheading" style="margin:0"><svg class="icon"><use href="#i-link"/></svg>Referans Bilgileri</h2>
          </div>
          <div class="panel-body">
            
            <form action="<?= base_url($this->uri->segment(1) . '/profile/updateNickname') ?>" method="POST" class="form-grid" style="max-width:500px;">
              <div class="field full">
                  <label>Referans Nickname</label>
                  <p class="subtle" style="font-size:13px; margin-bottom:12px; margin-top:-4px;"><?= $this->lang->line('profile_ref_nickname_desc') ?></p>
                  <input type="text" name="user_nickname" value="<?= html_escape($user_details->user_nickname ?? '') ?>" placeholder="Sadece harf, rakam ve alt çizgi" required>
              </div>
              <div class="form-actions" style="margin-top:8px;">
                  <button type="submit" class="primary-button"><svg class="icon"><use href="#i-save"/></svg>Kaydet</button>
              </div>
            </form>

            <?php if (!empty($user_details->user_nickname)): ?>
            <div style="margin-top:32px; padding-top:24px; border-top:1px solid rgba(255,255,255,0.05);">
                <h3 class="subheading" style="margin-bottom:16px;"><svg class="icon"><use href="#i-link"/></svg><?= $this->lang->line('profile_ref_link_title') ?></h3>
                <div class="field full" style="max-width:600px;">
                    <div style="display:flex; gap:12px; align-items:center;">
                        <input type="text" readonly id="refLink" value="<?= html_escape(base_url($this->uri->segment(1) . '/register?ref=' . rawurlencode($user_details->user_nickname))) ?>" style="flex:1; background:var(--bg-glass); cursor:text;">
                        <button type="button" class="primary-button" onclick="copyReferralLink()">
                            <svg class="icon"><use href="#i-copy"/></svg> <?= $this->lang->line('profile_ref_copy') ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

          </div>
        </section>
      </section>
    </section>
</main>
<script>
function copyReferralLink() {
    var input = document.getElementById('refLink');
    if (!input) {
        return;
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(input.value).then(function() {
            if (typeof toastr !== 'undefined') {
                toastr.success('Kopyalandı!');
            } else {
                alert('Kopyalandı!');
            }
        }).catch(function() {
            input.focus();
            input.select();
            if (typeof toastr !== 'undefined') {
                toastr.error('Bağlantı kopyalanamadı; seçip kopyalayabilirsiniz.');
            } else {
                alert('Bağlantı kopyalanamadı; seçip kopyalayabilirsiniz.');
            }
        });
        return;
    }

    input.focus();
    input.select();
    var copied = document.execCommand('copy');
    if (typeof toastr !== 'undefined') {
        copied ? toastr.success('Kopyalandı!') : toastr.error('Bağlantı kopyalanamadı; seçip kopyalayabilirsiniz.');
    } else {
        alert(copied ? 'Kopyalandı!' : 'Bağlantı kopyalanamadı; seçip kopyalayabilirsiniz.');
    }
}
</script>
