<div class="flat-title-page">
    <div class="themesflat-container">
        <div class="row">
            <div class="col-12">
                <h1 class="heading text-center"><?= $pageTitle ?></h1>

            </div>
        </div>
    </div>
</div>
<div class="tf-section-2 discover-item loadmore-12-item">
    <div class="container">
        <div class="container">
            <div id="product-list" class="row">
                <?php if (!empty($categories)) : ?>
                    <?php foreach ($categories as $category) : ?>
                        <div data-wow-delay="0s" class="wow fadeInUp fl-item col-xl-2 col-lg-4 col-md-6 col-sm-6 col-6 animated" style="display: block; visibility: visible; animation-delay: 0s; animation-name: fadeInUp;">
                            <div class="tf-card-box style-1 tf-card-box-cat ">
                                <div class="card-media" style="margin-bottom:2px !important;">
                                    <a href="<?= topupcenter() . $this->uri->segment(1) . '/' . $category->category_slug ?>">
                                        <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $category->category_thumbnail ?>" alt="<?= $category->category_title ?>">
                                    </a>
                                </div>
                                <div class="card-media-header">
                                    <h5 class="name tuc-category-name" style="margin-bottom:0px !important;"><a href="<?= topupcenter() . $this->uri->segment(1) . '/' . $category->category_slug ?>"><?= $category->category_title ?></a></h5>
                                    <?= $category->category_publisher ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-12">
                        <h2 class="text-center">No categories found</h2>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>