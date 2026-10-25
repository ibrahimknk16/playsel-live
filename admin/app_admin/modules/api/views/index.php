<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Api Yönetimi</h3>
            <div class="card-toolbar">
                <!--
                <div class="d-flex flex-wrap">
                    <button class="btn btn-light btn-xs btn-rounded m-2 p-2" style="height:32px;" id="stop-cron">Sistemi Durdur</button>
                    <button class="btn btn-light btn-xs btn-rounded m-2 p-2" style="height:32px;" id="start-cron">Sistemi Aktif Et</button>
                </div>
                -->
            </div>
        </div>
        <style>
            .form-check-input-sm {
                width: 30px !important;
                height: 16px !important;
            }
            .accordion-header {
                font-size: 12px !important;
                padding: 0.5rem 1rem;
            }
            .accordion-button{
                font-size: 12px !important;
                padding: 0.5rem 1rem;
            }
            .accordion-body{
                font-size: 12px !important;
                padding: 0.1rem 1rem;
            }
        </style>
        <div class="card-body p-2 m-0">
            <div class="accordion" id="kt_accordion_1">
                <?php foreach ($categories as $key => $category): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="kt_accordion_<?= $key + 1 ?>_header_<?= $key + 1 ?>">
                            <button class="accordion-button fs-4 fw-semibold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#kt_accordion_<?= $key + 1 ?>_body_<?= $key + 1 ?>" aria-expanded="true" aria-controls="kt_accordion_<?= $key + 1 ?>_body_<?= $key + 1 ?>">
                                <?= $category->category_title ?>
                            </button>
                        </h2>
                        <div id="kt_accordion_<?= $key + 1 ?>_body_<?= $key + 1 ?>" class="accordion-collapse collapse" aria-labelledby="kt_accordion_<?= $key + 1 ?>_header_<?= $key + 1 ?>" data-bs-parent="#kt_accordion_<?= $key + 1 ?>">
                            <div class="accordion-body">
                                <span class="m-1">
                                    <div class="form-check form-switch d-flex align-items-center">
                                        <input class="form-check-input form-check-input-sm category-switch" type="checkbox" data-cid="<?= $category->category_id ?>" role="switch" id="flexSwitchCheckDefault<?= $key + 1 ?>" <?= $category->category_api_status ? 'checked' : '' ?>>
                                        <label  for="flexSwitchCheckDefault<?= $key + 1 ?>"><span class="fs-24 m-2 fw-700"> Tüm <?= $category->category_title ?> Kategorisi</span></label>
                                    </div>
                                </span>
                                <?php foreach ($category->products as $product): ?>
                                    <span class="m-1">
                                        <div class="form-check form-switch d-flex align-items-center">
                                            <input class="form-check-input form-check-input-sm product-switch" type="checkbox" data-pid="<?= $product->product_id ?>" data-checkbox-cid="<?= $category->category_id ?>" role="switch" id="flexSwitchCheckDefault<?= $key + 1 ?>" <?= $product->product_api_status ? 'checked' : '' ?>>
                                            <label  for="flexSwitchCheckDefault<?= $key + 1 ?>"><span class="fs-24 m-2 fw-700"><?= $product->product_name ?></span></label>
                                        </div>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>

<script>
    //category-switch change get data-cid and change all product-switch data-checkbox-cid
    var productStatusChangeurl = '<?= base_url('api/changeProductStatus') ?>';
    var categoryStatusChangeurl = '<?= base_url('api/changeCategoryStatus') ?>';

    $(document).on('change', '.category-switch', function() {
        var cid = $(this).data('cid');
        var checked = $(this).prop('checked');
        var productSwitch = $('.product-switch');
        productSwitch.each(function() {
            if ($(this).data('checkbox-cid') == cid) {
                $(this).prop('checked', checked);
            }
        });
        // productSwitch change function  get by data-cid same category products data-pids
        var productPids =
            productSwitch.each(function() {
                if ($(this).data('checkbox-cid') == cid) {
                    var pid = $(this).data('pid');
                    var checked = $(this).prop('checked');
                    ajaxCall(productStatusChangeurl, {
                        pid: pid,
                        status: checked ? 1 : 0
                    });
                }
            });
        ajaxCall(categoryStatusChangeurl, {
            cid: cid,
            status: checked ? 1 : 0
        });
    });

    //product-switch change get data-pid and data-checkbox-cid
    $(document).on('change', '.product-switch', function() {
        var pid = $(this).data('pid');
        var cid = $(this).data('checkbox-cid');
        var checked = $(this).prop('checked');
        var status = checked ? 1 : 0;
        if (checked) {
            ajaxCall(categoryStatusChangeurl, {
                cid: cid,
                status: 1
            });
        }
        ajaxCall(productStatusChangeurl, {
            pid: pid,
            status: status
        });
    });

    function ajaxCall(url, data) {
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function(response) {
                console.log(response);
            }
        });
    }
    // stop-cron click function consoloe log durduruldu
    $('#stop-cron').click(function() {
        $.ajax({
            url: '<?= base_url('api/stopCron') ?>',
            type: 'POST',
            success: function(response) {
                console.log(response);
            }
        });
        $.ajax({
            url: '<?= base_url('api/stopCron') ?>',
            type: 'POST',
            success: function(response) {
                console.log(response);
            }
        });
    });
    // start-cron click function consoloe log başlatıldı
    $('#start-cron').click(function() {
        $.ajax({
            url: '<?= base_url('api/startCron') ?>',
            type: 'POST',
            success: function(response) {
                console.log(response);
            }
        });
    });
</script>