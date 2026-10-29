<div class="d-flex align-items-center mb-4">

    <div class="w-50">
        Kategori:
        <select name="categoryID" class="form-select categories form-select-solid">
            <option value="">Kategori Seçiniz</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category->category_id ?>" <?= isset($categoryID) && $categoryID == $category->category_id ? 'selected' : '' ?>>
                    <?= $category->category_title ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <!-- <h4 class="text-align-right text-end"><?= $category->category_title ?? '' ?></h4> -->
    &nbsp;&nbsp;
    <!-- <div>
        Ülke:
        <select name="categoryID" class="form-select form-select-sm locales form-select-solid" style="min-width:550px">
            <option value="">Ülke Seçiniz</option>
            <?php foreach ($countries as $country): ?>
                <option value="<?= $country->locale_lang_code ?>" <?= isset($localeID) && $localeID == $country->locale_lang_code ? 'selected' : '' ?>>
                    <?= $country->locale_country ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div> -->

    <div class="ms-auto">
        <button class="btn btn-secondary btn-sm me-2" id="cost-to-inputs-btn">
            <i class="fas fa-sync-alt"></i> Maliyetleri Inputlara Taşı
        </button>
        <a href="<?= base_url('products/productPricesReport' . (!empty($categoryID) ? '/' . (int) $categoryID . '/' . rawurlencode($localeID) : '')) ?>" class="btn btn-info btn-sm me-2">
            Zarar Kontrol Raporu
        </a>
        <button class="btn btn-success btn-sm" id="save-all-prices-btn">
            Tümünü Kaydet
        </button>
    </div>
</div>


<table id="products_table" class="table table-row-bordered table-row-dashed align-middle">
    <thead>
        <tr class="fw-semibold fs-6 text-gray-800">
            <th>Ürün</th>
            <th>Maliyet <?= $currency ?></th>
            <th>Liste Fiyatı <?= $currency ?></th>
            <th>Fiyat Tipi</th>
            <th>Standard <?= $currency ?></th>
            <th>Tedarikçi fiyatı <?= $currency ?></th>
            <th>Playsel <?= $currency ?></th>
            <th>Ödeme Kuruluşu <?= $currency ?></th>
            <th>API Fiyat <?= $currency ?></th>
            <th>API Özel Fiyat <?= $currency ?></th>
            <th>Durum/Stok</th>
            <th>Düzenle/Kaydet</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($products as $product): ?>
            <tr data-product-id="<?= $product->product_id ?>">
                <input type="hidden" name="product_price_country" value="tr">
                <input type="hidden" name="product_price_currency" value="<?= $currency ?>">

                <td>
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50px me-3">
                            <span class="symbol-label" style="background-image:url('<?= getenv('CDN_URL') . $product->product_image ?>')"></span>
                        </div>
                        <div class="fw-bold">
                            <div class="text-muted fs-7"><?= $product->category_title ?></div>
                            <a href="<?= base_url('products/edit/' . $product->product_id) ?>"><?= $product->product_name ?></a>
                            <div class="text-muted fs-7">#<?= $product->product_id ?>
                                <?php if ($product->product_is_bundle == 1): ?>
                                    <span class="badge badge-light-primary fs-8 mt-1">Paket (Bundle)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </td>

                <td data-order="<?= $product->product_price_cost ?>">
                    <input type="text" class="form-control form-control-sm form-control-solid"
                        name="cost"
                        value="<?= $product->product_price_cost ?>">

                    <?php
                    if ($product->product_is_bundle == 1) {
                        // Eğer ürün paketti ise controller'da hesapladığımız dinamik bundle maliyetlerini kullanıyoruz
                        $costCurrency = $product->bundle_cost_currency;
                        $rawCostVal = $product->bundle_total_cost;
                        $displayName = "Paket Toplamı";
                    } else {
                        // Standart tekil ürün maliyet kurgusu
                        $costCurrency = $product->provider_product_product_cost_currency;
                        $rawCostVal = $product->provider_product_product_cost;
                        $displayName = !empty($product->user_name) ? $product->user_name : "Tedarikçi Yok";
                    }

                    $bolen = 1;
                    $providerID = (int) $product->provider_product_provider_id;
                    if (!$product->product_is_bundle && in_array($providerID, [9132, 9156, 9266, 9162, 9302, 9303], true)) {
                        $bolen = 0.805;
                    } elseif (!$product->product_is_bundle && $providerID === 9284) {
                        $bolen = 0.85;
                    }

                    $carpan = ($costCurrency === 'USD') ? 1.0045 : 1;
                    $pCostCalc = currencyCalculator($costCurrency, $rawCostVal) * $carpan;

                    // Eğer döviz hesaplaması boş gelirse ve ürün bundle değilse epin pool maliyetine düş
                    $pCost = !empty($pCostCalc) ? $pCostCalc : ($product->product_is_bundle == 1 ? 0 : $product->epin_pool_cost);

                    $finalCalculatedCost = $pCost * $bolen;
                    $rawFinalCost = number_format($finalCalculatedCost, 2, '.', '');

                    // Zarar kontrolü
                    $isLoss = $finalCalculatedCost > $product->product_price_cost;
                    ?>

                    <small class="cost-price <?= $isLoss ? 'is-loss' : '' ?>" data-raw-cost="<?= $rawFinalCost ?>">
                        <?= $displayName ?> :
                        <?= nFormat($finalCalculatedCost) ?>
                        <?= $currency ?>
                    </small>
                    <br>
                    <small>
                        <?= $isLoss ? '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Zarar</span>' : '<span class="text-success"><i class="fas fa-check"></i> Karlı</span>' ?>
                    </small>
                </td>

                <td>
                    <input type="text" class="form-control form-control-sm form-control-solid"
                        name="list_price"
                        value="<?= $product->product_price_list_price ?>">
                </td>

                <td>
                    <select class="form-select form-select-sm form-select-solid" name="is_multiplier">
                        <option value="0" <?= !$product->product_price_is_multiplier ? 'selected' : '' ?>>Sabit</option>
                        <option value="1" <?= $product->product_price_is_multiplier ? 'selected' : '' ?>>Yüzdeli</option>
                    </select>
                </td>

                <td>
                    <input type="text" class="form-control form-control-sm form-control-solid"
                        name="standart_price"
                        value="<?= $product->product_price_is_multiplier
                                    ? $product->product_price_standart_price_multiplier
                                    : $product->product_price_standart_price ?>">
                    <small class="calculated-price" data-type="standart">
                        <?= nFormat($product->product_price_cost * $product->product_price_standart_price_multiplier) ?>
                        <?= $product->product_price_currency ?>
                    </small>
                </td>

                <td>
                    <input type="text" class="form-control form-control-sm form-control-solid"
                        name="silver_user"
                        value="<?= $product->product_price_is_multiplier
                                    ? $product->product_price_silver_user_multiplier
                                    : $product->product_price_silver_user ?>">
                    <small class="calculated-price" data-type="silver">
                        <?= nFormat($product->product_price_cost * $product->product_price_standart_price_multiplier) ?>
                        <?= $product->product_price_currency ?>
                    </small>
                </td>

                <td>
                    <input type="text" class="form-control form-control-sm form-control-solid"
                        name="playsel"
                        value="<?= $product->product_price_is_multiplier
                                    ? $product->product_price_playsel_multiplier
                                    : $product->product_price_playsel ?>">
                    <small class="calculated-price" data-type="playsel">
                        <?= nFormat($product->product_price_cost * $product->product_price_standart_price_multiplier) ?>
                        <?= $product->product_price_currency ?>
                    </small>
                </td>

                <td>
                    <input type="text" class="form-control form-control-sm form-control-solid"
                        name="payment"
                        value="<?= $product->product_price_is_multiplier
                                    ? $product->product_price_payment_multiplier
                                    : $product->product_price_payment ?>">
                    <small class="calculated-price" data-type="payment">
                        <?= nFormat($product->product_price_cost * $product->product_price_standart_price_multiplier) ?>
                        <?= $product->product_price_currency ?>
                    </small>
                </td>

                <td>
                    <input type="text" class="form-control form-control-sm form-control-solid"
                        name="gold_user"
                        value="<?= $product->product_price_is_multiplier
                                    ? $product->product_price_gold_user_multiplier
                                    : $product->product_price_gold_user ?>">
                    <small class="calculated-price" data-type="gold">
                        <?= nFormat($product->product_price_cost * $product->product_price_standart_price_multiplier) ?>
                        <?= $product->product_price_currency ?>
                    </small>
                </td>

                <td>
                    <input type="text" class="form-control form-control-sm form-control-solid"
                        name="vip_user"
                        value="<?= $product->product_price_is_multiplier
                                    ? $product->product_price_vip_user_multiplier
                                    : $product->product_price_vip_user ?>">
                    <small class="calculated-price" data-type="vip">
                        <?= nFormat($product->product_price_cost * $product->product_price_standart_price_multiplier) ?>
                        <?= $product->product_price_currency ?>
                    </small>
                </td>

                <td>
                    <div class="form-check form-switch form-switch-sm mb-3">
                        <input class="form-check-input product-status-change" type="checkbox" <?= $product->product_status ? 'checked' : '' ?>>
                        <label class="form-check-label">Durum</label>
                    </div>
                    <div class="form-check form-switch form-switch-sm">
                        <input class="form-check-input product-stock-change" type="checkbox" <?= $product->product_api_status ? 'checked' : '' ?>>
                        <label class="form-check-label">Stok</label>
                    </div>
                </td>

                <td>
                    <button class="btn btn-primary btn-sm save-price-btn"
                        data-id="<?= $product->product_id ?>">
                        Kaydet
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<script>
    $(document).ready(function() {
        $('.categories').select2();
        $('.locales').select2();
        // .categories veya .locales change function /categoryID
        $('.categories').on('change', function() {
            const categoryID = $(this).val();
            window.location.href = '/admin/products/productPrices/' + categoryID;
        });
        $('.locales').on('change', function() {
            const localeID = $(this).val();
            const categoryID = $('.categories').val() || '';
            window.location.href = '/admin/products/productPrices/' + categoryID + '/' + localeID;
        });

        $('#products_table').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            stateSave: true,
            ordering: true,
            language: {
                search: "Ara:",
                lengthMenu: "_MENU_ kayıt göster"
            }
        });

        $(document).on('input change', 'input, select', function() {

            const row = $(this).closest('tr');

            const cost = parseFloat(row.find('[name="cost"]').val()) || 0;
            const isMultiplier = row.find('[name="is_multiplier"]').val() == 1;

            if (!isMultiplier) {
                row.find('.calculated-price').text('');
                return;
            }

            const prices = {
                standart: parseFloat(row.find('[name="standart_price"]').val()) || 0,
                silver: parseFloat(row.find('[name="silver_user"]').val()) || 0,
                gold: parseFloat(row.find('[name="gold_user"]').val()) || 0,
                vip: parseFloat(row.find('[name="vip_user"]').val()) || 0,
                playsel: parseFloat(row.find('[name="playsel"]').val()) || 0,
                payment: parseFloat(row.find('[name="payment"]').val()) || 0
            };

            row.find('.calculated-price').each(function() {
                const type = $(this).data('type');
                const result = cost * prices[type];

                $(this).text(result.toFixed(4) + '  <?= $currency ?>');
            });
        });

    });

    // Ürün durumu veya stok durumu değiştiğinde güncelle
    $(document).on('change', '.product-status-change, .product-stock-change',
        function() {
            const row = $(this).closest('tr');
            const productID = row.data('product-id');
            const status = $('.product-status-change', row).is(':checked') ? 1 : 0;
            const stockStatus = $('.product-stock-change', row).is(':checked') ? 1 : 0;

            $.ajax({
                url: '/admin/products/updateProductStatus/' + productID,
                type: 'POST',
                data: {
                    status: status,
                    // Backend alanı product_api_status olarak kaldığı için endpoint sözleşmesi korunur.
                    apiStatus: stockStatus
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    alert('Ürün durumu güncellenirken bir hata oluştu.');
                }
            });
        });

    // save-all-prices-btn click function
    // click edildiğinde tüm satırları dolaş ve her bir satır için save-price-btn click fonksiyonunu tetikle
    $(document).on('click', '#save-all-prices-btn', function() {
        $('#products_table tbody tr').each(function() {
            const row = $(this);
            const button = row.find('.save-price-btn');
            button.click();
        });
    });

    //save-price-btn click function
    $(document).on('click', '.save-price-btn', function() {
        const button = $(this);
        const row = button.closest('tr');
        const productID = button.data('id');

        const data = {
            productID: productID,
            product_price_country: row.find('[name="product_price_country"]').val(),
            product_price_currency: row.find('[name="product_price_currency"]').val(),
            cost: row.find('[name="cost"]').val(),
            list_price: row.find('[name="list_price"]').val(),
            is_multiplier: row.find('[name="is_multiplier"]').val(),
            standart_price: row.find('[name="standart_price"]').val(),
            silver_user: row.find('[name="silver_user"]').val(),
            gold_user: row.find('[name="gold_user"]').val(),
            vip_user: row.find('[name="vip_user"]').val(),
            playsel: row.find('[name="playsel"]').val(),
            payment: row.find('[name="payment"]').val()
        };

        console.log(data);

        // kaydet butonunu success yap ve başına tik koy
        button.removeClass('btn-primary').addClass('btn-success');
        button.html('&#10003;Kaydedildi');

        $.ajax({
            url: '/admin/products/savePrice/' + productID,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                alert('Fiyat kaydedilirken bir hata oluştu.');
            }
        });
    });

    $(document).on('input change', 'input, select', function() {
        const row = $(this).closest('tr');
        calculateRow(row);
    });

    $('#products_table tbody tr').each(function() {
        calculateRow($(this));
    });

    function calculateRow(row) {
        const cost = parseFloat(row.find('[name="cost"]').val()) || 0;
        const isMultiplier = row.find('[name="is_multiplier"]').val() == 1;

        if (!isMultiplier) {
            row.find('.calculated-price').text('');
            return;
        }

        const prices = {
            standart: parseFloat(row.find('[name="standart_price"]').val()) || 0,
            silver: parseFloat(row.find('[name="silver_user"]').val()) || 0,
            gold: parseFloat(row.find('[name="gold_user"]').val()) || 0,
            vip: parseFloat(row.find('[name="vip_user"]').val()) || 0,
            playsel: parseFloat(row.find('[name="playsel"]').val()) || 0,
            payment: parseFloat(row.find('[name="payment"]').val()) || 0
        };

        row.find('.calculated-price').each(function() {
            const type = $(this).data('type');
            const result = cost * prices[type];
            $(this).text(result.toFixed(4) + '  <?= $currency ?>');
        });
    }

    // cost-to-inputs-btn click function
    $(document).on('click', '#cost-to-inputs-btn', function(e) {
        e.preventDefault();

        // Sadece içinde '.is-loss' sınıfı barındıran satırları dön
        $('#products_table tbody tr').each(function() {
            const row = $(this);
            const lossElement = row.find('.cost-price.is-loss');

            // Eğer bu satırda zarar elementini bulduysak işlemi yap
            if (lossElement.length > 0) {
                const rawCost = lossElement.attr('data-raw-cost');

                row.find('input[name="cost"]').val(rawCost);

                if (typeof calculateRow === "function") {
                    calculateRow(row);
                }

                // Görsel geri bildirim (opsiyonel): İşlem yapılan inputu hafifçe parlatabilirsin
                row.find('input[name="cost"]').css('background-color', '#fff3cd').animate({
                    backgroundColor: "#fff"
                }, 1000);
            }
        });
    });
</script>