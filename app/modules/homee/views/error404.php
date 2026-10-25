<style>
    .error-404-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 70vh;
        text-align: center;
        padding: 40px 20px;
        background-color: transparent;
        color: #e0e0e0;
        font-family: 'Inter', 'Roboto', sans-serif;
    }
    
    .error-404-glitch {
        font-size: 150px;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 20px;
        color: #fff;
        text-shadow: 
            0 0 10px rgba(250, 179, 22, 0.5), 
            0 0 20px rgba(250, 179, 22, 0.3), 
            0 0 30px rgba(250, 179, 22, 0.2);
        letter-spacing: 5px;
        position: relative;
    }

    .error-404-glitch span.tf-color {
        color: #fab316;
    }

    .error-404-title {
        font-size: 32px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #ffffff;
    }

    .error-404-subtitle {
        font-size: 16px;
        max-width: 500px;
        margin: 0 auto 40px auto;
        color: #a0a0a0;
        line-height: 1.6;
    }

    .error-404-btn-wrapper {
        margin-top: 20px;
    }

    /* Geliştirilmiş Buton Stili (Eğer tema class'ları yetersiz kalırsa) */
    .error-404-wrapper .tf-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 40px;
        height: 50px;
        background-color: #fab316;
        color: #111;
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .error-404-wrapper .tf-button:hover {
        background-color: #ffc233;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(250, 179, 22, 0.2);
    }

    .error-404-wrapper .tf-button i {
        margin-left: 10px;
        font-size: 16px;
    }

    /* Animasyon */
    @keyframes float404 {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
    
    .error-404-glitch {
        animation: float404 4s ease-in-out infinite;
    }

    /* Popular Categories CSS */
    .pop-cat-section {
        width: 100%;
        max-width: 1200px;
        margin: 80px auto 0 auto;
        padding: 0 20px;
        text-align: left;
    }
    .pop-cat-title {
        font-size: 22px;
        font-weight: 600;
        color: #fff;
        margin-bottom: 25px;
    }
    .pop-cat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
    .pop-cat-card {
        background-color: #0b0e14;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: transform 0.3s ease;
    }
    .pop-cat-card:hover {
        transform: translateY(-5px);
        border-color: rgba(255, 255, 255, 0.1);
    }
    .pop-cat-header {
        height: 100px;
        background-size: cover;
        background-position: center;
        position: relative;
        display: flex;
        align-items: center;
        padding: 0 20px;
    }
    .pop-cat-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(to right, rgba(0,0,0,0.9), rgba(0,0,0,0.2));
    }
    .pop-cat-header-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #fff;
        font-weight: 600;
        font-size: 16px;
    }
    .pop-cat-header-content img {
        width: 30px;
        height: 30px;
        object-fit: contain;
    }
    .pop-cat-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        background: #111419;
    }
    .pop-cat-subtitle {
        font-size: 11px;
        color: #8c8c8c;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 15px;
        letter-spacing: 1px;
    }
    .pop-cat-list {
        list-style: none;
        padding: 0;
        margin: 0;
        flex-grow: 1;
    }
    .pop-cat-list li {
        margin-bottom: 12px;
    }
    .pop-cat-list a {
        color: #e0e0e0;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.2s;
    }
    .pop-cat-list a:hover {
        color: #fff;
    }
    .pop-cat-footer {
        padding-top: 15px;
        margin-top: 15px;
    }
    .pop-cat-all {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #090b0e;
        padding: 12px 15px;
        border-radius: 8px;
        color: #fab316;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: background 0.2s;
    }
    .pop-cat-all:hover {
        background: #121620;
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .pop-cat-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .error-404-glitch { font-size: 100px; }
        .error-404-title { font-size: 24px; }
        .error-404-subtitle { font-size: 14px; }
    }
    @media (max-width: 600px) {
        .pop-cat-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="section-404-page relative error-404-wrapper">
    <div class="error-404-glitch">
        4<span class="tf-color">0</span>4
    </div>
    <h2 class="error-404-title"><?= $this->lang->line('error_404') ?></h2>
    <p class="error-404-subtitle">
        Aradığınız sayfa silinmiş, adı değiştirilmiş veya geçici olarak ulaşılamıyor olabilir. 
    </p>

    <div class="error-404-btn-wrapper">
        <a href="<?= topupcenter() . $uri ?>" class="tf-button w320 style-1 h50">
            <?= $this->lang->line('home_page') ?> <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <?php if (!empty($popular_categories)): ?>
    <div class="pop-cat-section">
        <h3 class="pop-cat-title">Popüler Kategoriler</h3>
        <div class="pop-cat-grid">
            <?php foreach($popular_categories as $cat): ?>
                <div class="pop-cat-card">
                    <div class="pop-cat-header" style="background-image: url('<?= getenv('CDN_URL') . 'uploads/categories/' . $cat->category_image ?>');">
                        <div class="pop-cat-header-content">
                            <?php if(!empty($cat->category_thumbnail)): ?>
                                <img src="<?= getenv('CDN_URL') . 'uploads/categories/' . $cat->category_thumbnail ?>" alt="<?= $cat->category_title ?>">
                            <?php endif; ?>
                            <span><?= $cat->category_title ?></span>
                        </div>
                    </div>
                    <div class="pop-cat-body">
                        <div class="pop-cat-subtitle">ALT KATEGORİLER</div>
                        <ul class="pop-cat-list">
                            <?php if(!empty($cat->sub_categories)): ?>
                                <?php foreach(array_slice($cat->sub_categories, 0, 5) as $subCat): ?>
                                    <li><a href="<?= topupcenter() . $uri . '/' . $subCat->category_slug ?>"><?= $subCat->category_title ?></a></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><a href="<?= topupcenter() . $uri . '/' . $cat->category_slug ?>">Tüm Ürünleri Gör</a></li>
                            <?php endif; ?>
                        </ul>
                        <div class="pop-cat-footer">
                            <a href="<?= topupcenter() . $uri . '/' . $cat->category_slug ?>" class="pop-cat-all">
                                Tüm Kategoriler <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>