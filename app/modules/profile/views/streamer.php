<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-streamer" data-view="streamer">
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-ticket"></use></svg></span>
            <div class="hero-copy">
                <h1>Yayıncı Başvurusu</h1>
                <p>Yayıncı programımıza katılarak özel avantajlardan faydalanabilirsiniz.</p>
            </div>
        </header>

        <section class="panel">
          <div class="panel-head">
            <h2 class="subheading" style="margin:0"><svg class="icon"><use href="#i-edit"/></svg>Başvuru Formu</h2>
          </div>
          <div class="panel-body">
            
            <?php if (isset($user['user_streamer_status']) && $user['user_streamer_status'] == 1): ?>
                <div class="info-banner" style="margin-bottom: 24px; background: rgba(46, 213, 115, 0.1); color: #2ed573; border-color: rgba(46, 213, 115, 0.2);">
                    <svg class="icon"><use href="#i-check"/></svg> <strong>Tebrikler!</strong> Yayıncı başvurunuz onaylandı ve yayıncı statüsüne geçtiniz.
                </div>
            <?php elseif (isset($user['user_streamer_status']) && $user['user_streamer_status'] == 2): ?>
                <div class="info-banner" style="margin-bottom: 24px; background: rgba(255, 165, 2, 0.1); color: #ffa502; border-color: rgba(255, 165, 2, 0.2);">
                    <svg class="icon"><use href="#i-clock"/></svg> Yayıncı başvurunuz inceleniyor. Lütfen onay sürecini bekleyin.
                </div>
            <?php endif; ?>
            
            <?php if (isset($user['user_streamer_status']) && $user['user_streamer_status'] == 0): ?>
                <div class="info-banner" style="margin-bottom: 24px; background: rgba(255, 71, 87, 0.1); color: #ff4757; border-color: rgba(255, 71, 87, 0.2);">
                    <svg class="icon"><use href="#i-x"/></svg> Önceki yayıncı başvurunuz reddedildi. Bilgilerinizi güncelleyerek tekrar başvurabilirsiniz.
                </div>
            <?php endif; ?>

            <form action="<?= base_url($this->uri->segment(1) . '/profile/streamer') ?>" method="POST" class="form-grid" style="max-width:500px;">
                <div class="field full">
                    <label>Yayıncı Adı (Nick)</label>
                    <input type="text" name="user_streamer_nick" placeholder="Kullandığınız yayıncı adınızı girin" required value="<?= html_escape($user['user_streamer_nick'] ?? '') ?>">
                </div>
                <div class="field full">
                    <label>Yayın URL'si (Twitch, YouTube vb.)</label>
                    <input type="url" name="user_streamer_url" placeholder="Kanalınızın tam bağlantısını (URL) girin" required value="<?= html_escape($user['user_streamer_url'] ?? '') ?>">
                </div>
                
                <?php if (isset($user['user_streamer_status']) && ($user['user_streamer_status'] == 1 || $user['user_streamer_status'] == 2)): ?>
                    <div class="field full">
                        <div class="info-banner" style="background: rgba(52, 152, 219, 0.1); color: #3498db; border-color: rgba(52, 152, 219, 0.2);">
                            <svg class="icon"><use href="#i-info"/></svg> Bilgilerinizi güncellediğinizde yayıncı başvurunuz tekrar onaya düşecektir.
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-actions" style="margin-top:16px;">
                    <button type="submit" class="primary-button" style="width:100%;">
                        <svg class="icon"><use href="#i-send"/></svg> 
                        <?= isset($user['user_streamer_status']) ? 'Bilgileri Güncelle' : 'Başvuruyu Gönder' ?>
                    </button>
                </div>
            </form>

          </div>
        </section>
      </section>
    </section>
</main>
