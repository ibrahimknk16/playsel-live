<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Panel Kullanıcıları</h3>
            <div class="card-toolbar">
                <a href="<?= base_url('admins/permissions') ?>" class="btn btn-sm btn-light-primary me-2">Permission CRUD</a>
                <a href="<?= base_url('admins/rolePermissions/3') ?>" class="btn btn-sm btn-light-success me-2">Rol Yetkileri</a>
                <button data-bs-toggle="modal" data-bs-target="#staticBackdrop" class="btn btn-sm btn-success">
                    <i class="fa-plus text-white"></i> Kullanıcı Ekle
                </button>
            </div>
        </div>

        <div class="card-body">
            <table id="productTable" class="table table-striped align-middle table-row-dashed">
                <thead>
                    <tr>
                        <th>Kullanıcı ID</th>
                        <th>Kullanıcı Adı</th>
                        <th>Kullanıcı Email</th>
                        <th>Kullanıcı Rol</th>
                        <th>Kullanıcı Durum</th>
                        <th>Oluşturma Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <?php $roles = [2 => 'Super Admin', 3 => 'Admin', 4 => 'Operasyon']; ?>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user->admin_id ?></td>
                            <td><?= $user->admin_name ?></td>
                            <td><?= $user->admin_email ?></td>
                            <td><?= $roles[$user->admin_power] ?></td>
                            <td><?= $user->admin_status ?></td>
                            <td><?= date('d-m-Y', strtotime($user->admin_created_at)) ?></td>
                            <td><a href="<?= base_url('admins/detail/' . $user->admin_id) ?>" class="btn btn-sm btn-primary">Detay</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="form w-100" method="POST" novalidate="novalidate" id="kt_sign_in_form" action="<?= base_url('admins/addUser') ?>">
                    <input type="hidden" name="admin_action_csrf" value="<?= html_escape($adminActionCsrfToken) ?>">
                    <div class="fv-row mb-8">
                        <input type="text" placeholder="Kullanıcı Adı" name="name" autocomplete="off" class="form-control bg-transparent form-control-solid" />
                    </div>
                    <div class="fv-row mb-8">
                        <input type="email" placeholder="Email" name="email" autocomplete="off" class="form-control bg-transparent form-control-solid" />
                    </div>
                    <div class="fv-row mb-8">
                        <input type="password" placeholder="Şifre" name="password" autocomplete="off" class="form-control bg-transparent form-control-solid" />
                    </div>

                    <div class="fv-row mb-8">
                        <select name="admin_power" class="form-select form-select-solid" data-control="select2" data-placeholder="Kullanıcı Rolü">
                            <option></option>
                            <?php foreach ($roles as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <span class="indicator-label">
                            Kaydet</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // $(document).ready(function() {
        //     $('.products').select2();
        // });
    });
</script>