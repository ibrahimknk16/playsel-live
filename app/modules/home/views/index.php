<div class="full-width-slider-section mt-1 mb-5">
    <div class="swiper hero-slider-container">
        <div class="swiper-wrapper">

            <?php
            $bulletLogos = [];

            if (!empty($sliders)) : ?>
                <?php foreach ($sliders as $slider) :
                    $bulletLogos[] = base_url('uploads/sliders/' . $slider->slider_bullet_image);
                ?>
                    <div class="swiper-slide">
                        <a href="<?= !empty($slider->slider_url) ? topupcenter() . '/' . $slider->slider_url : '#' ?>">
                            <img src="<?= getenv('CDN_URL') . 'uploads/sliders/' . $slider->slider_image ?>"
                                class="img-fluid"
                                alt="<?= $slider->slider_title ?? 'slide' ?>">
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

        <div class="swiper-button-next next-3d over"></div>
        <div class="swiper-button-prev prev-3d over"></div>
        <div class="swiper-pagination"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var bulletLogos = <?= json_encode($bulletLogos); ?>;

        var heroSwiper = new Swiper('.hero-slider-container', {
            loop: true,
            slidesPerView: 1,
            speed: 600,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.next-3d',
                prevEl: '.prev-3d',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                renderBullet: function(index, className) {
                    var logoUrl = bulletLogos[index] ? bulletLogos[index] : '';
                    return '<div class="' + className + '"><img src="' + logoUrl + '" alt="Bullet ' + (index + 1) + '"></div>';
                }
            }
        });
    });
</script>

<div class="tf-section seller">
    <div class="themesflat-container">
        <div class="row">
            <div class="col-md-12">
                <div class="heading-section pb-30 flex justify-space-between align-center">
                    <h2 class="tf-title">Popüler Oyunlar</h2>

                    <a href="games.html" class="view-all-link">Tümünü Gör <i
                            class="icon-arrow-right2"></i></a>
                </div>
            </div>
            <div class="col-md-12">

                <div class="swiper game-category-slider">
                    <div class="swiper-wrapper">

                        <?php foreach ($categories as $category): ?>
                            <div class="swiper-slide">
                                <div class="tf-category game-card text-center">
                                    <a href="<?= topupcenter() . $uri . '/' . $category->category_slug ?>">
                                        <div class="card-image-wrapper">
                                            <div class="bg-layer">
                                                <!-- <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $category->category_image ?>"
                                                    alt="<?= $category->category_title ?> BG" class="bg-image"> -->
                                            </div>
                                            <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $category->category_thumbnail ?>"
                                                alt="<?= $category->category_title ?> Char" class="char-image">
                                        </div>
                                        <h6><?= $category->category_title ?></h6>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tf-section-3 discover-item">
    <div class="themesflat-container">
        <div class="row">
            <div class="col-md-12">
                <div class="heading-section pb-30 flex justify-space-between align-center">
                    <h2 class="tf-title">Fırsatlar</h2>
                    <a href="flash-sales.html" class="view-all-link">Tüm Fırsatlar <i
                            class="icon-arrow-right2"></i></a>
                </div>
            </div>

            <div class="col-md-12">
                <div class="swiper flash-sale-slider">
                    <div class="swiper-wrapper">

                        <div class="swiper-slide">
                            <div class="product-card">
                                <div class="card-media">
                                    <a href="#">
                                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTzYGk7zYBXoS-yZJFjIXh2lMZK-BJ82giNZw&s"
                                            alt="8400 VP">
                                    </a>
                                    <span class="discount-badge">%15 İndirim</span>
                                    <div class="flash-sale-timer">
                                        <span class="js-countdown" data-timer="7500"
                                            data-labels="G,S,D,S"></span>
                                    </div>
                                </div>
                                <div class="flash-info">
                                    <div class="game-tag">Valorant</div>
                                    <h5 class="name"><a href="#">8500 VP (Valorant Points)</a></h5>
                                    <div class="divider"></div>
                                    <div class="flash-meta">
                                        <div class="price-wrapper">
                                            <span class="old-price">1.250,00 ₺</span>
                                            <h6 class="new-price">1.062,50 ₺</h6>
                                        </div>
                                        <a href="#" class="btn-buy-modern">Satın Al</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="product-card">
                                <div class="card-media">
                                    <a href="#">
                                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTiKMxHjv-RZjS5lHfN1N-YtTDSPF3bZHAjWQ&s"
                                            alt="8100 UC">
                                    </a>
                                    <span class="discount-badge">%20 İndirim</span>
                                    <div class="flash-sale-timer">
                                        <span class="js-countdown" data-timer="14400"
                                            data-labels="G,S,D,S"></span>
                                    </div>
                                </div>
                                <div class="flash-info">
                                    <div class="game-tag">PUBG Mobile</div>
                                    <h5 class="name"><a href="#">8100 UC (Unknown Cash)</a></h5>
                                    <div class="divider"></div>
                                    <div class="flash-meta">
                                        <div class="price-wrapper">
                                            <span class="old-price">2.400,00 ₺</span>
                                            <h6 class="new-price">1.920,00 ₺</h6>
                                        </div>
                                        <a href="#" class="btn-buy-modern">Satın Al</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="product-card">
                                <div class="card-media">
                                    <a href="#">
                                        <img src="https://cdn.dijipin.com/Category/c38d28c4-8d6b-48b4-9522-260e916ce508/pubgm-kategorıgorselı.webp"
                                            alt="Steam 10 USD">
                                    </a>
                                    <span class="discount-badge">%10 İndirim</span>
                                    <div class="flash-sale-timer">
                                        <span class="js-countdown" data-timer="25000"
                                            data-labels="G,S,D,S"></span>
                                    </div>
                                </div>
                                <div class="flash-info">
                                    <div class="game-tag">Steam Cüzdan</div>
                                    <h5 class="name"><a href="#">10 USD Steam Cüzdan Kodu</a></h5>
                                    <div class="divider"></div>
                                    <div class="flash-meta">
                                        <div class="price-wrapper">
                                            <span class="old-price">340,00 ₺</span>
                                            <h6 class="new-price">306,00 ₺</h6>
                                        </div>
                                        <a href="#" class="btn-buy-modern">Satın Al</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="product-card">
                                <div class="card-media">
                                    <a href="#">
                                        <img src="https://cdn.dijipin.com/Category/c3b18e25-dea6-4d3b-9d95-408016c4e425/mlbb-kategori.jpg.webp"
                                            alt="LOL RP">
                                    </a>
                                    <span class="discount-badge">%18 İndirim</span>
                                    <div class="flash-sale-timer">
                                        <span class="js-countdown" data-timer="3600"
                                            data-labels="G,S,D,S"></span>
                                    </div>
                                </div>
                                <div class="flash-info">
                                    <div class="game-tag">League of Legends</div>
                                    <h5 class="name"><a href="#">7400 RP (Riot Points)</a></h5>
                                    <div class="divider"></div>
                                    <div class="flash-meta">
                                        <div class="price-wrapper">
                                            <span class="old-price">1.400,00 ₺</span>
                                            <h6 class="new-price">1.148,00 ₺</h6>
                                        </div>
                                        <a href="#" class="btn-buy-modern">Satın Al</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tf-section gift-cards-section">
    <div class="themesflat-container">
        <div class="row">
            <div class="col-md-12">
                <div class="heading-section pb-30 flex justify-space-between align-center">
                    <h2 class="tf-title">Oyun & Hediye Kartları</h2>
                    <a href="explore.html" class="view-all-link">Tümünü İncele <i
                            class="icon-arrow-right2"></i></a>
                </div>
            </div>

            <div class="col-md-12">
                <div class="swiper gc-slider">
                    <div class="swiper-wrapper">

                        <div class="swiper-slide">
                            <a href="#" class="gc-card bg-netflix">
                                <div class="gc-icon-box"><i class="fa-brands fa-neos"></i></div>
                                <h5 class="gc-title">Netflix</h5>
                                <p class="gc-subtitle">Hediye Kartları</p>
                            </a>
                        </div>

                        <div class="swiper-slide">
                            <a href="#" class="gc-card bg-geforce">
                                <div class="gc-icon-box"><i class="fa-solid fa-microchip"></i></div>
                                <h5 class="gc-title">GeForce Now</h5>
                                <p class="gc-subtitle">Hediye Kartları</p>
                            </a>
                        </div>

                        <div class="swiper-slide">
                            <a href="#" class="gc-card bg-apple">
                                <div class="gc-icon-box"><i class="fa-brands fa-apple"></i></div>
                                <h5 class="gc-title">Apple Store</h5>
                                <p class="gc-subtitle">Hediye Kartları</p>
                            </a>
                        </div>

                        <div class="swiper-slide">
                            <a href="#" class="gc-card bg-google">
                                <div class="gc-icon-box"><i class="fa-brands fa-google-play"></i></div>
                                <h5 class="gc-title">Google Play</h5>
                                <p class="gc-subtitle">Hediye Kartları</p>
                            </a>
                        </div>

                        <div class="swiper-slide">
                            <a href="#" class="gc-card bg-amazon">
                                <div class="gc-icon-box"><i class="fa-brands fa-amazon"></i></div>
                                <h5 class="gc-title">Amazon</h5>
                                <p class="gc-subtitle">Hediye Kartları</p>
                            </a>
                        </div>

                        <div class="swiper-slide">
                            <a href="#" class="gc-card bg-steam">
                                <div class="gc-icon-box"><i class="fa-brands fa-steam"></i></div>
                                <h5 class="gc-title">Steam</h5>
                                <p class="gc-subtitle">Cüzdan Kodları</p>
                            </a>
                        </div>

                        <div class="swiper-slide">
                            <a href="#" class="gc-card bg-playstation">
                                <div class="gc-icon-box"><i class="fa-brands fa-playstation"></i></div>
                                <h5 class="gc-title">PlayStation</h5>
                                <p class="gc-subtitle">Hediye Kartları</p>
                            </a>
                        </div>

                        <div class="swiper-slide">
                            <a href="#" class="gc-card bg-xbox">
                                <div class="gc-icon-box"><i class="fa-brands fa-xbox"></i></div>
                                <h5 class="gc-title">Xbox</h5>
                                <p class="gc-subtitle">Hediye Kartları</p>
                            </a>
                        </div>

                        <div class="swiper-slide">
                            <a href="#" class="gc-card bg-riot">
                                <div class="gc-icon-box"><i class="fa-solid fa-fist-raised"></i></div>
                                <h5 class="gc-title">Riot Games</h5>
                                <p class="gc-subtitle">Oyun Kodları</p>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="tf-section top-collections">
    <div class="themesflat-container relative">
        <div class="tf-tsparticles">
            <div id="tsparticles1" data-color="#161616" data-line="#000"></div>
        </div>

        <div class="row relative z-5">
            <div class="col-md-12">
                <div class="heading-section pb-40 text-center">
                    <h2 class="tf-title mw-667 mx-auto capitalization">Neden Playsel? Ayrıcalıklarımız</h2>
                    <p>Playsel Elektronik Ticaret Limited Şirketi, en uygun fiyatlarla oyun, oyun içi
                        ürünler, dijital platformlar ve daha birçok ürünü sizlere sunar.</p>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-30">
                <div class="benefit-box">
                    <div class="icon-wrap">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <h4>Anında Teslimat</h4>
                    <p>Satın aldığın e-pin ve oyun kodları anında hesabında ve mail adresinde!</p>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-30">
                <div class="benefit-box">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h4>Güvenli Ödeme</h4>
                    <p>Tüm işlemleriniz 256-bit SSL ve 3D Secure ile %100 güvence altındadır.</p>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-30">
                <div class="benefit-box">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h4>7/24 Canlı Destek</h4>
                    <p>Deneyimli ekibimiz, her sorunuz ve işleminiz için her an yanınızda.</p>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-30">
                <div class="benefit-box">
                    <div class="icon-wrap">
                        <i class="fa-regular fa-gem"></i>
                    </div>
                    <h4>En İyi Fiyat</h4>
                    <p>Binlerce oyun ve epin paketinde her zaman rekabetçi fiyatlar sunuyoruz.</p>
                </div>
            </div>

        </div>
    </div>
</div>

