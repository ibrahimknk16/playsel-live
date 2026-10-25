<style>
    #productTable_filter {
        display: block !important;
    }
</style>

<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Ürünler</h3>
            <div class="card-toolbar">
            </div>
        </div>

        <div class="card-body">
            <table id="productTable" class="table table-striped align-middle table-row-dashed">
                <thead>
                    <tr>
                        <!--<th>Görsel</th>-->
                        <th>Admin ID</th>
                        <th>Ürün Adı</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th>Ürün Maliyeti</th>
                        <th>TL Maliyet</th>
                        <th>USD Maliyet</th>
                        <th>Düzenle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <!--<td><img src="<?= $product->product_image ?>" width="50"></td>-->
                            <td class="p-0 m-0"><?= $product->product_id ?></td>
                            <td class="p-0 m-0"><?= $product->product_name ?></td>
                            <td class="p-0 m-0"><?= $product->category_title ?></td>
                            <?php
                            $index = array_search($product->seller_products_seller_id, array_column($suppliers, 'user_id'));
                            $sp = ($index !== false && isset($suppliers[$index])) ? $suppliers[$index]->user_name : null;
                            ?>
                            <td class="p-0 m-0"><badge class="badge badge-primary"><?= $sp ?? 'Supplier Yok' ?></badge></td>
                            <td class="p-0 m-0"><badge class="badge badge-success"><?= $product->product_cost ?> <?= $product->product_cost_currency ?></badge></td>
                            <td class="p-0 m-0"><badge class="badge badge-info"><?= $costTL ?> ₺</badge></td>
                            <td class="p-0 m-0"><badge class="badge badge-info"><?= currencyCalculatorTo('USD', $costTL) ?> $</badge></td>
                            <td class="p-0 m-0"><button class="btn btn-light btn-xs btn-rounded m-2 p-2" onclick="productDetail('<?= $product->product_id ?>')"><i class="fa-solid fa-magnifying-glass"></i> Düzenle</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productDetailModalLabel">Ürün Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" id="productDetailContent">
                <p class="text-center text-muted">Yükleniyor...</p>
            </div>
        </div>
    </div>
</div>


<script>
    function productDetail(productID) {
        $.ajax({
            url: '<?= site_url('products/productsDetail/') ?>' + productID,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response) {
                    let spesificData = response.product_is_require_spesific ? JSON.parse(response.product_is_require_spesific) : {};
                    let requiredFields = '';
                    let keys = Object.keys(spesificData || {});
                    let defaultKeys = ["user_id", "server_id", "zone_id"];

                    for (let i = 0; i < 3; i++) {
                        let key = defaultKeys[i] || keys[i] || `field_${i+1}`;
                        let value = spesificData[key] !== undefined ? spesificData[key] : '';
                        requiredFields += `
                        <div class="mb-3 col-4">
                        <input type="text" class="form-control mt-2 form-control-solid" name="requires[${key}]" value="${value}" placeholder="${key}">
                        </div>
                    `;
                    }

                    let checkboxes = `
                    <div class="mb-3 col-4">
                                <input type="checkbox" class="form-check-input" id="require_user_id" name="product_is_require_user_id" value="${response.product_is_require_user_id}" ${response.product_is_require_user_id == 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="require_user_id"> Kullanıcı ID Gerekli</label>
                             </div>
                             <div class="mb-3 col-4">
                                <input type="checkbox" class="form-check-input" id="require_server_id" name="product_is_require_server_id" value="${response.product_is_require_server_id}" ${response.product_is_require_server_id == 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="require_server_id"> Sunucu ID Gerekli</label>
                              </div>
                             <div class="mb-3 col-4">
                                <input type="checkbox" class="form-check-input" id="require_zone_id" name="product_is_require_zone_id" value="${response.product_is_require_zone_id}" ${response.product_is_require_zone_id == 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="require_zone_id"> Bölge ID Gerekli</label>
                                </div>
                    `;

                    let modalContent = `
                    <form id="productEditForm">
                        <div class="text-center mb-3">
                            <img src="${response.product_image}" class="img-fluid rounded" style="max-height: 200px;" alt="${response.product_name}">
                        </div>
                        <div class="row">
                            <div class="mb-3 col-4">
                                <label class="form-label">Ürün ID</label>
                                <input type="text" class="form-control form-control-solid" name="product_id" value="${response.product_id}" readonly>
                            </div>
                            <div class="mb-3 col-4">
                                <label class="form-label">Ürün Adı</label>
                                <input type="text" class="form-control form-control-solid" name="product_name" value="${response.product_name}">
                            </div>
                            <div class="mb-3 col-4">
                                <label class="form-label">Kategori</label>
                                <input type="text" class="form-control form-control-solid" value="${response.category_title}" readonly>
                            </div>
                            <div class="mb-3 col-4">
                                <label class="form-label">Maliyet</label>
                                <input type="text" class="form-control form-control-solid" name="product_cost" value="${response.product_cost || 0}">
                            </div>
                            <div class="mb-3 col-4">
                                <label class="form-label">Para Birimi</label>
                                <input type="text" class="form-control form-control-solid" value="${response.product_cost_currency}" readonly>
                            </div>
                            <div class="mb-3 col-4">
                                <label class="form-label">Supplier Product ID</label>
                                <input type="text" class="form-control form-control-solid" name="product_supplier_code" value="${response.product_supplier_code || ''}">
                            </div>
                            <div class="mb-3 col-4">
                                <label class="form-label">Supplier Variant ID</label>
                                <input type="text" class="form-control form-control-solid" name="product_supplier_variant_code" value="${response.product_supplier_variant_code || ''}">
                            </div>
                            <div class="mb-3 col-4">
                                <label class="form-label">Durum</label>
                                <select class="form-control form-control-solid" name="product_status">
                                    <option value="1" ${response.product_status == 1 ? 'selected' : ''}>Aktif</option>
                                    <option value="0" ${response.product_status == 0 ? 'selected' : ''}>Pasif</option>
                                </select>
                            </div>
                            <div class="mb-3 col-12">
                                <label class="form-label">Required Özel Bilgiler</label>
                                <div class="row">
                                    ${requiredFields}
                                </div>
                            </div>
                            <div class="mb-3 col-12">
                                <label class="form-label">Gereklilikler</label>
                                <div class="row">
                                    ${checkboxes}
                                </div>
                            </div>
                            </div>
                            </form>
                            <button class="btn btn-success btn-rounded w-100 update-product">Kaydet</button>
                `;

                    $('#productDetailContent').html(modalContent);
                    $('#productDetailModal').modal('show'); // Modalı aç
                } else {
                    $('#productDetailContent').html('<p class="text-danger">Ürün bilgileri alınamadı.</p>');
                    $('#productDetailModal').modal('show');
                }
            },
            error: function() {
                $('#productDetailContent').html('<p class="text-danger">Ürün bilgileri yüklenirken hata oluştu.</p>');
                $('#productDetailModal').modal('show');
            }
        });
    }


    $(document).ready(function() {

        $('#productTable').DataTable({
            paging: true,
            ordering: false,
            searching: true,
            pageLength: 15,
            lengthMenu: [
                [15, 25, 50, 100, 250, 500],
                [15, 25, 50, 100, 250, 500]
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
            },
            dom: '<"top"f>rt<"bottom"lp><"clear">'
        });



        $(document).on('click', '.update-product', function(e) {
            e.preventDefault();

            var formData = $('#productEditForm').serialize();
            var productID = $('#productEditForm input[name="product_id"]').val();
            var url = '<?= site_url('products/updateProduct/') ?>' + productID;
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        Swal.fire({
                            text: response.message,
                            icon: response.status
                        });
                        //$('#productDetailModal').modal('hide'); // Modalı kapat
                    } else {
                        Swal.fire({
                            text: response.message,
                            icon: response.status
                        });
                    }
                },
                error: function() {
                    alert("Bir hata oluştu, tekrar deneyin!");
                }
            });
        });
    });
</script>