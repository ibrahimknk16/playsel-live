<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title"><?= $site['site_url'] ?> Eklenen Haberler</h3>
            <div class="card-toolbar">
                <a href="<?= base_url('sayfalar/addNews?site=' . $site['site_url']) ?>" type="button" class="btn btn-sm btn-success">
                    <i class="fa-plus text-white"></i> Haber Ekle
                </a>
            </div>
        </div>

        <div class="card-body">
            <table id="kt_datatable_dom_positioning" class="table table-striped table-row-bordered gy-5 gs-7 border rounded align-middle table-row-dashed">
                <thead>
                    <tr class="fw-bold fs-6 text-gray-800 px-7">
                        <th>Görsel</th>
                        <th>Kategori</th>
                        <th>Title</th>
                        <th>Eklenme Tarihi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news as $new): ?>
                        <tr>
                            <td><img src="<?= $new->image ?>" alt="<?=$new->image ?>" width="100"></td>
                            <td><?= $new->category ?></td>
                            <td><?= $new->title ?></td>
                            <td><?= date('d.m.Y', strtotime($new->created_at)) ?></td> 
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>