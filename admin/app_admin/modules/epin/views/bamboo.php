<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="card card-flush shadow-sm">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bolder fs-3 mb-1">Bamboo Ürün Yönetimi</span>
            <span class="text-muted mt-1 fw-bold fs-7">Kategori ve ürün seçerek anında Bamboo API üzerinden e-pin satın alın.</span>
        </h3>
        <div class="card-toolbar">
            <span class="badge badge-light-success fw-bolder px-4 py-3">API: Aktif</span>
        </div>
    </div>

    <div class="card-body py-4">

        <div class="mb-10">
            <label class="form-label fw-bolder text-dark fs-6">1. Kategori Seçimi</label>
            <div class="d-flex flex-column">
                <select class="form-select form-select-solid select2-lib" id="category_id" data-control="select2" data-placeholder="Kategori arayın veya seçin...">
                    <option></option>
                    <?php if (!empty($categories)) : ?>
                        <?php foreach ($categories as $category) : ?>

                            <option value="<?= $category['id'] ?>" <?= (isset($selected_category_id) && $selected_category_id == $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="text-muted fs-7 mt-2">Bamboo sisteminden çekilen güncel kategoriler.</div>
            </div>
        </div>

        <?php if (!empty($products) && is_array($products)) : ?>

            <div class="separator separator-dashed my-8"></div>

            <div class="mb-10">
                <label class="form-label fw-bolder text-dark fs-6">2. Ürün Seçimi <span class="badge badge-light-success ms-2"><?= count($products) ?> Adet Aktif Ürün Bulundu</span></label>
                <select class="form-select form-select-solid select2-lib" id="product_id" data-control="select2" data-placeholder="Satın alınacak ürünü seçin...">
                    <option></option>
                    <?php foreach ($products as $product) : ?>
                        <?php if (isset($product['id'])): ?>
                            <option value="<?= $product['id']; ?>" data-name="<?= htmlspecialchars($product['name']) ?>" data-price="<?= $product['price'] ?>" data-facevalue="<?= $product['faceValue'] ?>">
                                <?= htmlspecialchars($product['name']); ?> - <?= number_format($product['price'], 4); ?> <?= $currencyCode ?> (Stok: <?= $product['stock'] ?>)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <form action="<?= site_url('epin/buyBamboo') ?>" method="post" id="bamboo_buy_form">

                <div id="epin-pool-section" class="mb-10">
                    <label class="form-label fw-bolder text-dark fs-6">3. E-Pin Havuzu Seçimi</label>
                    <?php if (!empty($epin_pools)) : ?>
                        <select class="form-select form-select-solid select2-lib" id="epin_pool_id" name="epin_pool_id" data-control="select2" data-placeholder="Pinlerin aktarılacağı havuzu seçin..." required>
                            <option></option>
                            <?php foreach ($epin_pools as $pool) : ?>
                                <option value="<?= $pool['epin_pool_id']; ?>" data-stock="<?= $pool['stock'] ?>">
                                    <?= htmlspecialchars($pool['epin_pool_title']); ?> - Stok: <?= $pool['stock'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="text-muted fs-7 mt-2">Satın alınan e-pinler şifrelenerek (AES) seçtiğiniz havuza otomatik olarak eklenecektir.</div>
                    <?php else : ?>
                        <div class="alert alert-dismissible bg-light-warning d-flex flex-column flex-sm-row p-5 mb-0">
                            <span class="svg-icon svg-icon-2hx svg-icon-warning me-4 mb-5 mb-sm-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path opacity="0.3" d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="currentColor"></path>
                                    <path d="M10.5 17H13.5V14H10.5V17ZM10.5 12H13.5V7H10.5V12Z" fill="currentColor"></path>
                                </svg>
                            </span>
                            <div class="d-flex flex-column pe-0 pe-sm-10">
                                <h4 class="fw-bold">Havuz Bulunamadı</h4>
                                <span>Lütfen satın alma işleminden önce sistemde en az bir adet e-pin havuzu oluşturduğunuzdan emin olun.</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="buy-section" style="display:none;">

                    <div class="separator separator-dashed my-8"></div>

                    <div class="bg-light-primary rounded border-primary border border-dashed p-6 mb-10">
                        <div class="row align-items-center">
                            <div class="col-lg-7 mb-7 mb-lg-0">
                                <div class="d-flex align-items-center">
                                    <span class="svg-icon svg-icon-3x svg-icon-primary me-5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M21 10H13V11C13 11.6 12.6 12 12 12C11.4 12 11 11.6 11 11V10H3C2.4 10 2 10.4 2 11V13H22V11C22 10.4 21.6 10 21 10Z" fill="currentColor"></path>
                                            <path opacity="0.3" d="M12 12C11.4 12 11 11.6 11 11V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V11C13 11.6 12.6 12 12 12Z" fill="currentColor"></path>
                                            <path opacity="0.3" d="M21 14H3C2.4 14 2 14.4 2 15V19C2 20.1 2.9 21 4 21H20C21.1 21 22 20.1 22 19V15C22 14.4 21.6 14 21 14ZM12 19C11.4 19 11 18.6 11 18V16C11 15.4 11.4 15 12 15C12.6 15 13 15.4 13 16V18C13 18.6 12.6 19 12 19Z" fill="currentColor"></path>
                                        </svg>
                                    </span>
                                    <div class="d-flex flex-column">
                                        <h4 class="text-gray-900 fw-bolder mb-1" id="display-product-name">Ürün Adı</h4>
                                        <span class="text-gray-600 fw-bold">API Anlık Birim Fiyat: <b id="display-product-price" class="text-success">0.00</b> <?= $currencyCode ?></span>
                                        <span class="text-gray-600 fw-bold">API Anlık Birim Fiyat: <b id="display-try-product-price" class="text-success">0.00</b> TRY</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <input type="hidden" name="catID" value="<?= $selected_category_id ?>">
                                <input type="hidden" name="productID" id="hidden_product_id" value="">

                                <div class="d-flex align-items-center justify-content-lg-end">
                                    <div class="w-100 mw-150px me-3">
                                        <label class="form-label fw-bolder text-dark fs-7 mb-1">Satın Alınacak Adet</label>
                                        <input type="number" name="qty" id="qty" class="form-control form-control-solid fw-bolder text-center" value="1" min="1" required>
                                    </div>
                                    <div class="mt-6">
                                        <button type="submit" class="btn btn-primary" id="btn_buy_submit">
                                            <span class="indicator-label">
                                                <i class="fas fa-shopping-cart fs-4 me-2"></i> Satın Al
                                            </span>
                                            <span class="indicator-progress">
                                                Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="faceValue" id="hidden_face_value" value="">
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<span class="usd-currency d-none"><?= currencyCalculator($currencyCode, 1) ?></span>

<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2-lib').select2({
                width: '100%',
                allowClear: true
            });
        }

        $('#category_id').on('change', function() {
            var cid = $(this).val();
            if (cid) {
                $(this).prop('disabled', true);
                window.location.href = '<?= current_url() ?>?category_id=' + cid;
            }
        });

        $('#product_id').on('change', function() {
            var pid = $(this).val();
            if (pid) {
                var faceVal = $(this).find(':selected').data('facevalue');
                $('#hidden_face_value').val(faceVal);
                var name = $(this).find(':selected').data('name');
                var price = $(this).find(':selected').data('price');

                $('#hidden_product_id').val(pid);
                $('#display-product-name').text(name);
                $('#display-product-price').text(parseFloat(price).toFixed(4));
                var usdToTryRate = parseFloat($('.usd-currency').text());
                var priceInTry = price * usdToTryRate;
                $('#display-try-product-price').text(priceInTry.toFixed(4));

                $('#buy-section').slideDown(300);
            } else {
                $('#buy-section').slideUp(300);
                $('#hidden_product_id').val('');
            }
        });

        $('#bamboo_buy_form').on('submit', function() {
            var submitButton = document.querySelector('#btn_buy_submit');
            submitButton.setAttribute('data-kt-indicator', 'on');
            submitButton.disabled = true;
        });
    });
</script>