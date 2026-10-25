<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Reseller Detay</h3>
            <div class="card-toolbar">
            </div>
        </div>

        <div class="card-body">
            <h4 class="mt-5">Slider Bilgileri</h4>
            <table id="productTable" class="table table-striped align-middle table-row-dashed">
                <thead>
                    <tr>
                        <th>Slider ID</th>
                        <th>Slider Başlık</th>
                        <th>Slider Link</th>
                        <th>Slider Dil</th>
                        <th>Slider Content</th>
                        <th>Slider Aktiflik</th>
                        <th>Slider Oluşturulma Tarihi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                            <td><?= $slider->slider_id ?></td>
                            <td><?= $slider->slider_title ?></td>
                            <td><?= $slider->slider_link ?></td>
                            <td><?= $slider->slider_language ?></td>
                            <td><?= $slider->slider_content ?></td>
                            <td><?= $slider->slider_is_active == 1 ? 'Aktif' : 'Pasif' ?></td>
                            <td><?= date('d-m-Y', strtotime($slider->slider_created_at)) ?></td>
                    </tr>
                </tbody>
            </table>



        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(document).ready(function() {
            $('.products').select2();
        });
    });
</script>