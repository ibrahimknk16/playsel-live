<div class="container" style="padding-bottom: 0;">
    <div class="widget-bg-line">
        <div class="wraper">
            <div class="bg-grid-line y bottom">
                <div class="bg-line"></div>
            </div>
        </div>
    </div>
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12 pages-titled2">
                <div class="content">
                    <div class="swiper tucslider">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide"><img src="https://i.imgur.com/vtISNer.jpeg" alt=""></div>
                            <div class="swiper-slide"><img src="https://i.imgur.com/vtISNer.jpeg" alt=""></div>
                            <div class="swiper-slide"><img src="https://i.imgur.com/vtISNer.jpeg" alt=""></div>
                            <div class="swiper-slide"><img src="https://i.imgur.com/vtISNer.jpeg" alt=""></div>
                            <div class="swiper-slide"><img src="https://i.imgur.com/vtISNer.jpeg" alt=""></div>
                            <div class="swiper-slide"><img src="https://i.imgur.com/vtISNer.jpeg" alt=""></div>
                            <div class="swiper-slide"><img src="https://i.imgur.com/vtISNer.jpeg" alt=""></div>
                            <div class="swiper-slide"><img src="https://i.imgur.com/vtISNer.jpeg" alt=""></div>
                        </div>
                        <div class="tuc-pagination gap-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="seller ">
    <div class="container">
        <div class="row">
            <div class="col-md-12 tucontentheader" style="padding:0;">
                <div class="heading-section-d2">
                    <h2 class="tf-title " style="perspective: 400px;">
                        <div style="display: block; text-align: start; position: relative; transform-origin: 156.258px 22px; transform: translate3d(0px, 0px, 0px); opacity: 1;"><?= $this->lang->line('best_sellers') ?></div>
                    </h2>
                    <a href="<?= topupcenter() . $this->uri->segment(1) . '/topup' ?>" class=""><?= $this->lang->line('see_all') ?> <i class="icon-arrow-right2"></i></a>
                </div>
            </div>
            <div class="col-md-12 p-0">
                <div class="row tucproductcenterd2">
                    <?php if (!empty($categories)) : ?>
                        <?php foreach ($categories as $category) : ?>
                            <div class="tf-category-d5 col-5 col-sm-4 col-md-3 col-lg-2">
                                <a href="<?= topupcenter() . $this->uri->segment(1) . '/' .  $category->category_slug ?>">
                                    <div class="card-media-d5 position-relative overflow-hidden">                                    
                                        <div class="card-media-d5-bg position-relative overflow-hidden">
                                            <div class="blur-layer" style="background-image: url('<?= topupcenter() . 'uploads/categories/' . $category->category_thumbnail ?>');"></div>
                                            <div class="card-header-content-d5 d-flex position-relative align-items-center">
                                                <img src="<?= topupcenter() . 'uploads/categories/' . $category->category_thumbnail ?>" alt="" class="thumbnail-img">
                                                <div class="card-header-d5">
                                                    <h6><?= $category->category_title ?></h6>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row tucbestseller">
    <div class="tf-section seller">
        <div class="container" style="padding:0px;">
            <?php
            // Kategorileri ikiye böl
            $chunkedCategories = array_chunk($categories, ceil(count($categories) / 2));
            $leftCategories = $chunkedCategories[0];
            $rightCategories = $chunkedCategories[1];
            ?>
                <div class="row d-flex justify-content-between">
                    <div class="col-lg-6 col-md-6 col-12 tucpopularcat">
                        <div class="tucpopularcatBg">
                            <div class="heading-section-d2 pb-30" style="padding:20px;">
                                <h2 class="tf-title">
                                    <div style="display: block; text-align: start; position: relative; transform-origin: 156.258px 22px; transform: translate3d(0px, 0px, 0px); opacity: 1;"><?= $this->lang->line('best_sellers') ?></div>
                                </h2>
                                <a href="<?= topupcenter() . $this->uri->segment(1) . '/topup' ?>" class=""><?= $this->lang->line('see_all') ?> <i class="icon-arrow-right2"></i></a>
                            </div>
                        <div class="row tucpopularcenter">
                            <?php if (!empty($categories)) : ?>
                                <?php foreach ($leftCategories as $category) : ?>
                                <div class="tf-popular-d2 col-5 col-sm-4 col-md-3 col-lg-3">
                                    <a href="<?= topupcenter() . $this->uri->segment(1) . '/' .  $category->category_slug ?>">
                                        <div class="popular-card-media-d2 d-flex align-items-center">
                                            <img src="<?= topupcenter() . 'uploads/categories/' . $category->category_thumbnail ?>" alt="">
                                            <div class="card-header-d2">
                                                <h6><?= $category->category_title ?></h6>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-12 tucpopularcat">
                        <div class="tucpopularcatBg">
                            <div class="heading-section-d2 pb-30" style="padding:20px;">
                                <h2 class="tf-title " style="perspective: 400px;">
                                    <div style="display: block; text-align: start; position: relative; transform-origin: 156.258px 22px; transform: translate3d(0px, 0px, 0px); opacity: 1;"><?= $this->lang->line('categories') ?></div>
                                </h2>
                                <a href="<?= topupcenter() . $this->uri->segment(1) . '/topup' ?>" class=""><?= $this->lang->line('see_all') ?> <i class="icon-arrow-right2"></i></a>
                            </div>
                        <div class="row tucpopularcenter">
                            <?php if (!empty($categories)) : ?>
                                <?php foreach ($rightCategories as $category) : ?>
                                <div class="tf-popular-d2 col-5 col-sm-4 col-md-3 col-lg-3">
                                    <a href="<?= topupcenter() . $this->uri->segment(1) . '/' .  $category->category_slug ?>">
                                        <div class="popular-card-media-d2 d-flex align-items-center">
                                            <img src="<?= topupcenter() . 'uploads/categories/' . $category->category_thumbnail ?>" alt="">
                                            <div class="card-header-d2">
                                                <h6><?= $category->category_title ?></h6>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tf-section seller">
    <div class="themesflat-container">
        <div class="row">
            <div class="col-md-12">
                <div class="heading-section">
                    <h2 class="tf-title pb-30"><?= $this->lang->line('best_sellers') ?>
                        <span class="dropdown" id="select-day">
                            <span class="btn-selector tf-color">
                                <span>1 day</span>
                            </span>
                            <ul class="show">
                                <li><span data-filter="day" onclick="filterTopSeller(this)">Gün</span></li>
                                <li><span data-filter="week" onclick="filterTopSeller(this)">Hafta</span></li>
                                <li><span data-filter="month" onclick="filterTopSeller(this)">Ay</span></li>
                            </ul>
                        </span>
                    </h2>
                </div>
            </div>

            <div class="col-md-12">
                <div class="swiper-container seller seller-slider2" data-swiper='{
                                "loop":false,
                                "slidesPerView": 2,
                                "observer": true,
                                "grabCursor": true,
                                "observeParents": true,
                                "spaceBetween": 30,
                                "navigation": {
                                    "clickable": true,
                                    "nextEl": ".seller-next",
                                    "prevEl": ".seller-prev"
                                },
                                "breakpoints": {
                                    "500": {
                                        "slidesPerView": 3
                                    },
                                    "640": {
                                        "slidesPerView": 4
                                    },
                                    "768": {
                                        "slidesPerView": 5
                                    },
                                    "1070": {
                                        "slidesPerView": 6
                                    }
                                }
                            }'>
                    <div class="swiper-wrapper">

                        <div class="swiper-slide">
                            <div class="tf-author-box text-center">
                                <div class="top-product-img">
                                    <img src="assets-front/images/item-background/aoem.png" alt="" class="avatar">
                                    <div class="number">1</div>
                                </div>
                                <div class="author-infor">
                                    <h5><a href="game.html">AoM 99 Doruk Parası</a></h5>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="tf-author-box text-center">
                                <div class="top-product-img">
                                    <img src="assets-front/images/item-background/mlbb.webp" alt=""
                                        class="avatar">
                                    <div class="number">2</div>
                                </div>
                                <div class="author-infor">
                                    <h5><a href="game.html">Mobile Legends 100 Elmas</a></h5>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="tf-author-box text-center">
                                <div class="top-product-img">
                                    <img src="assets-front/images/item-background/pubg.webp" alt=""
                                        class="avatar">
                                    <div class="number">3</div>
                                </div>
                                <div class="author-infor">
                                    <h5><a href="game.html">Pubg Mobile 100 UC</a></h5>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="tf-author-box text-center">
                                <div class="top-product-img">
                                    <img src="assets-front/images/item-background/aoem.png" alt="" class="avatar">
                                    <div class="number">4</div>
                                </div>
                                <div class="author-infor">
                                    <h5><a href="game.html">AoM 99 Doruk Parası</a></h5>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="tf-author-box text-center">
                                <div class="top-product-img">
                                    <img src="assets-front/images/item-background/mlbb.webp" alt=""
                                        class="avatar">
                                    <div class="number">5</div>
                                </div>
                                <div class="author-infor">
                                    <h5><a href="game.html">Mobile Legends 100 Elmas</a></h5>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="tf-author-box text-center">
                                <div class="top-product-img">
                                    <img src="assets-front/images/item-background/pubg.webp" alt=""
                                        class="avatar">
                                    <div class="number">6</div>
                                </div>
                                <div class="author-infor">
                                    <h5><a href="game.html">Pubg Mobile 100 UC</a></h5>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="swiper-button-next seller-next over active"></div>
                <div class="swiper-button-prev seller-prev over "></div>
            </div>

        </div>
    </div>
</div>

<div class="tf-section create-sell">
    <div class="themesflat-container">
        <div class="row">
            <div class="col-md-12">
                <div class="heading-section justify-content-center">
                    <h2 class="tf-title pb-30">
                        <?= $this->lang->line('why_topup_center') ?>
                    </h2>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="tf-box-icon relative text-center">
                    <div class="image">
                        <i class="fa-solid fa-rocket"></i>
                    </div>
                    <h4 class="heading">Hızlı Teslimat</h4>
                    <p class="content"> 7 gün 24 saat alış ve satış işlemleriniz anında gerçekleştirilir.
                    </p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="tf-box-icon relative text-center type-1">
                    <div class="image">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h4 class="heading">Güvenli Alışveriş</h4>
                    <p class="content"> 3D ve SSL güvencesiyle dilediğiniz ödeme yöntemini
                        kullanabilirsiniz. </p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="tf-box-icon relative text-center">
                    <div class="image">
                        <i class="fa-regular fa-thumbs-up"></i>
                    </div>
                    <h4 class="heading">Müşteri Memnuniyeti</h4>
                    <p class="content"> Müşterilerimize kaliteli hizmet vermenin mutluluğunu yaşıyoruz. </p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="tf-box-icon relative text-center">
                    <div class="image">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                    <h4 class="heading">Uygun Fiyat</h4>
                    <p class="content"> İndirimli ve en uygun fiyatlarla alışverişin keyfini sizde çıkarın.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tf-section action">
    <div class="themesflat-container">
        <div class="row">
            <div class="col-md-12">
                <div class="action__body" style="background-color:#e0b257">
                    <div class="tf-tsparticles">
                        <div id="tsparticles1" data-color="#000" data-line="#000"></div>
                    </div>
                    <h2><?= $this->lang->line('popular_games_desc') ?></h2>
                    <div class="flat-button flex">
                        <a href="<?= topupcenter() . $this->uri->segment(1) . '/topup' ?>" class="tf-button style-2 h50 w190 mr-10"><?= $this->lang->line('explore') ?><i
                                class="icon-arrow-up-right2"></i></a>
                    </div>
                    <div class="bg-home7">
                        <?php
                        $chunks = array_chunk($categories, ceil(count($categories) / 3));
                        foreach ($chunks as $index => $chunk) { ?>
                            <div class="swiper-container autoslider3reverse" data-swiper='{
                                "loop": true,
                                "slidesPerView": "auto",
                                "spaceBetween": 14,
                                "direction": "vertical",
                                "speed": 10000,
                                "observer": true,
                                "observeParents": true,
                                "autoplay": {
                                    "delay": 0,
                                    "disableOnInteraction": false
                                }
                            }'>
                                <div class="swiper-wrapper">
                                    <?php foreach ($chunk as $category): ?>
                                        <div class="swiper-slide">
                                            <img src="<?= topupcenter() . 'uploads/categories/' . $category->category_thumbnail ?>" alt="">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>