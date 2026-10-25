<div class="d-flex align-items-center mb-4">
    <div class="ms-auto">
        <button class="btn btn-secondary btn-sm me-2" id="cost-to-inputs-btn">
            <i class="fas fa-sync-alt"></i> Maliyetleri Inputlara Taşı
        </button>
        <a href="<?= base_url('products/productPrices') ?>" class="btn btn-info btn-sm me-2">
            Ürün Fiyatları
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
            <?php
            $bolen = 1;
            if (in_array($product->provider_product_provider_id, [9132, 9156, 9162, 9266])): ?>
                <?php $bolen = 0.805 ?>
            <?php endif; ?>
            <?php $carpan = ($product->provider_product_product_cost_currency === 'USD') ? 1.0045 : 1;?>
            <?php $pCost = currencyCalculator($product->provider_product_product_cost_currency, $product->provider_product_product_cost) * $carpan ?>
            <?php if ($pCost < $product->product_price_cost || ($product->product_status == 0 || $product->product_api_status == 0) || $product->product_category_id == 36 || $product->product_category_id == 33 || $product->product_category_id == 131 || $product->product_category_id == 35) continue; ?>

            <tr data-product-id="<?= $product->product_id ?>">
                <!-- <input type="hidden" name="product_price_country" value="<?= $product->product_price_country ?>"> -->
                <input type="hidden" name="product_price_country" value="tr">
                <input type="hidden" name="product_price_currency" value="<?= $currency ?>">
                <!-- ÜRÜN -->
                <td>
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50px me-3">
                            <span class="symbol-label" style="background-image:url('<?= $product->product_image ?>')"></span>
                        </div>
                        <div class="fw-bold">
                            <div class="text-muted fs-7"><?= $product->category_title ?></div>
                            <a href="<?= base_url('products/edit/' . $product->product_id) ?>"><?= $product->product_name ?></a>
                            <div class="text-muted fs-7">#<?= $product->product_id ?></div>
                        </div>
                    </div>
                </td>

                <!-- MALİYET -->
                <td data-order="<?= $product->product_price_cost ?>">
                    <input type="text" class="form-control form-control-sm form-control-solid"
                        name="cost"
                        value="<?= $product->product_price_cost ?>">

                    <?php
                    $bolen = 1;
                    if (in_array($product->provider_product_provider_id, [9132, 9156, 9162, 9266])):
                        $bolen = 0.805;
                    endif;

                    $carpan = ($product->provider_product_product_cost_currency === 'USD') ? 1.0045 : 1;
                    $pCostCalc = currencyCalculator($product->provider_product_product_cost_currency, $product->provider_product_product_cost) * $carpan;
                    $pCost = !empty($pCostCalc) ? $pCostCalc : $product->epin_pool_cost;

                    $finalCalculatedCost = $pCost * $bolen;
                    $rawFinalCost = number_format($finalCalculatedCost, 2, '.', '');

                    // Zarar kontrolü
                    $isLoss = $finalCalculatedCost > $product->product_price_cost;
                    ?>

                    <!-- Zarar varsa 'is-loss' class'ını ekliyoruz -->
                    <small class="cost-price <?= $isLoss ? 'is-loss' : '' ?>" data-raw-cost="<?= $rawFinalCost ?>">
                        <?= $product->user_name ?> :
                        <?= nFormat($finalCalculatedCost) ?>
                        <?= $currency ?>
                    </small>
                    <br>
                    <small>
                        <?= $isLoss ? '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Zarar</span>' : '<span class="text-success"><i class="fas fa-check"></i> Karlı</span>' ?>
                    </small>
                </td>

                <!-- LİSTE -->
                <td>
                    <input type="text" class="form-control form-control-sm form-control-solid"
                        name="list_price"
                        value="<?= $product->product_price_list_price ?>">
                </td>

                <!-- FİYAT TİPİ -->
                <td>
                    <select class="form-select form-select-sm form-select-solid" name="is_multiplier">
                        <option value="0" <?= !$product->product_price_is_multiplier ? 'selected' : '' ?>>Sabit</option>
                        <option value="1" <?= $product->product_price_is_multiplier ? 'selected' : '' ?>>Yüzdeli</option>
                    </select>
                </td>

                <!-- STANDART -->
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

                <!-- SILVER -->
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

                <!-- PLAYSEL -->
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

                <!-- PAYMENT -->
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

                <!-- GOLD -->
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

                <!-- VIP -->
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

                <!-- STATUS -->
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

                <!-- KAYDET -->
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