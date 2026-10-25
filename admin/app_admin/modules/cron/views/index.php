<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Title</h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-success">
                    <i class="fa-plus text-white"></i> Button
                </button>
            </div>
        </div>

        <div class="card-body">
            <table id="kt_datatable_dom_positioning" class="table table-striped table-row-bordered gy-5 gs-7 border rounded align-middle table-row-dashed">
                <thead>
                    <tr class="fw-bold fs-6 text-gray-800 px-7">
                        <th>ID</th>
                        <th>URL</th>
                        <th>Toplam Haber</th>
                        <th>Haber Ekle</th>
                        <th>Haberleri Gör</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sites as $site): ?>
                        <tr>
                            <td><?= $site->site_id ?></td>
                            <td><?= $site->site_url ?></td>
                            <td><?= $site->total_news ?></td>
                            <td><a href="<?= base_url('sayfalar/addNews?site=' . $site->site_url) ?>" class="btn btn-success btn-sm">Haber Ekle</a>
                            <td><a href="<?= base_url('sayfalar/siteNews?site=' . $site->site_id) ?>" class="btn btn-info btn-sm">Haberleri Gör</a>
                            </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>