<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <div class="card-toolbar">

            </div>
        </div>

        <div class="card-body">
            <table id="kt_datatable_dom_positioning" class="table table-striped table-row-bordered gy-5 gs-7 border rounded align-middle table-row-dashed">
                <thead>
                    <tr class="fw-bold fs-6 text-gray-800 px-7">
                        <th>Site URL</th>
                        <th>Title</th>
                        <th>URL</th>
                        <th>Eklenme Tarihi</th>
                        <th>Bitiş Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($links as $link): ?>
                        <tr>
                            <td><?= $link->site_url ?></td>
                            <td><?= $link->link_title ?></td>
                            <td><?= $link->link_url ?></td>
                            <td><?= date('d.m.Y', strtotime($link->link_created_at)) ?></td>
                            <td><?= date('d.m.Y', strtotime($link->link_end_date)) < date('d.m.Y') ? 'Bitti' : date('d.m.Y', strtotime($link->link_end_date)) ?></td>
                            <td>
                                <?php if (date('d.m.Y', strtotime($link->link_end_date)) > date('d.m.Y')): ?>
                                    <a href="<?= base_url('sayfalar/deleteLink?id=' . $link->link_id) ?>" class="btn btn-danger btn-sm">Durdur</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>