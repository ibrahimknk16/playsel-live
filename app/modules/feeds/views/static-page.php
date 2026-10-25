<div style="height: 57px; display: none;"></div>
<div class="flat-title-page">
    <div class="themesflat-container">
        <div class="row">
            <div class="col-12">
                <h4>
                    <span><?= !empty($staticPage->static_page_title) ? $staticPage->static_page_title : '' ?></span>
                </h4>

            </div>
        </div>
    </div>
</div>
<div class="tf-section-5 tf-grid-blog">
    <div class="themesflat-container">
        <div class="row flex flex-wrap">
            <div class="col-md-4 col-lg-3">
                <div class="side-bar">

                    <div class="widget widget-categories">
                        <ul>
                            <li>
                                <div class="cate-item">
                                    <a class="<?= $this->uri->segment(2) == 'about-us' ? 'cate-item-active' : '' ?>" href="<?= topupcenter() . $uri . '/about-us' ?>">Hakkımızda</a>
                                </div>
                            </li>
                            <li>
                                <div class="cate-item">
                                    <a class="<?= $this->uri->segment(2) == 'terms-conditions' ? 'cate-item-active' : '' ?>" href="<?= topupcenter() . $uri . '/terms-conditions' ?>">Aydınlatma Metni</a>
                                </div>
                            </li>
                            <li>
                                <div class="cate-item">
                                    <a class="<?= $this->uri->segment(2) == 'cookie-policy' ? 'cate-item-active' : '' ?>" href="<?= topupcenter() . $uri . '/cookie-policy' ?>">Çerez Politikası</a>
                                </div>
                            </li>
                            <li>
                                <div class="cate-item">
                                    <a class="<?= $this->uri->segment(2) == 'explicit-consent-text' ? 'cate-item-active' : '' ?>" href="<?= topupcenter() . $uri . '/explicit-consent-text' ?>">Açık Rıza Metni</a>
                                </div>
                            </li>
                            <li>
                                <div class="cate-item">
                                    <a class="<?= $this->uri->segment(2) == 'privacy-policy' ? 'cate-item-active' : '' ?>" href="<?= topupcenter() . $uri . '/privacy-policy' ?>">Gizlilik Politikası</a>
                                </div>
                            </li>
                            <li>
                                <div class="cate-item">
                                    <a class="<?= $this->uri->segment(2) == 'refund-policy' ? 'cate-item-active' : '' ?>" href="<?= topupcenter() . $uri . '/refund-policy' ?>">Geri Ödeme Politikası</a>
                                </div>
                            </li>
                        </ul>
                    </div>


                </div>
            </div>
            <div class="col-md-8">
                <div class="wrap-inner row">
                    <div class="col-md-6 col-lg-12">
                        <article class="policy-card">
                            <div class="card-title" style="position: sticky !important;">
                                <span class="text-aboutus aggrements-section">
                                    <?= !empty($staticPage->static_page_content) ? $staticPage->static_page_content : $this->lang->line('page_is_unready') ?>
                                </span>
                            </div>

                        </article>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>