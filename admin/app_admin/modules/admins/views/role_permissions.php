<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Rol Yetkileri</h3>
            <div class="card-toolbar">
                <a href="<?= base_url('admins/permissions') ?>" class="btn btn-sm btn-light-primary me-2">Permission CRUD</a>
                <a href="<?= base_url('admins') ?>" class="btn btn-sm btn-light">Panel Kullanıcılar</a>
            </div>
        </div>

        <div class="card-body">
            <form method="GET" action="<?= base_url('admins/rolePermissions') ?>" class="mb-8">
                <div class="row g-4 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Rol</label>
                        <select class="form-select form-select-solid" name="role" onchange="window.location='<?= base_url('admins/rolePermissions') ?>/'+this.value;">
                            <?php foreach ($roles as $power => $roleName): ?>
                                <option value="<?= (int) $power ?>" <?= (int) $selectedRole === (int) $power ? 'selected' : '' ?>>
                                    <?= html_escape($roleName) ?> (<?= (int) $power ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>

            <form method="POST" action="<?= base_url('admins/updateRolePermissions/' . (int) $selectedRole) ?>">
                <input type="hidden" name="admin_power" value="<?= (int) $selectedRole ?>">

                <div class="alert alert-secondary">
                    <div><strong>Allow:</strong> Rol izinli.</div>
                    <div><strong>Deny:</strong> Rolde engelli.</div>
                    <div><strong>Bos:</strong> Erişim yok (inherit yoksa default deny).</div>
                </div>

                <?php foreach ($categories as $category): ?>
                    <?php $categoryId = (int) $category['permission_category_id']; ?>
                    <?php $rows = !empty($permissionsByCategory[$categoryId]) ? $permissionsByCategory[$categoryId] : []; ?>
                    <?php if (empty($rows)) {
                        continue;
                    } ?>

                    <div class="card border mb-6 card-flush">
                        <div class="card-header">
                            <h4 class="card-title mb-0"><?= html_escape($category['permission_category_name']) ?></h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-row-bordered table-sm align-middle mb-0 table-row-dashed">
                                    <thead>
                                        <tr>
                                            <th>Permission</th>
                                            <th class="text-center">Allow</th>
                                            <th class="text-center">Deny</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $permission): ?>
                                            <?php
                                            $permissionId = (int) $permission['permission_id'];
                                            $isAllow = in_array($permissionId, $roleAllowIds);
                                            $isDeny = in_array($permissionId, $roleDenyIds);
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold"><?= html_escape($permission['permission_name']) ?></div>
                                                    <div class="text-muted small"><?= html_escape($permission['permission_code']) ?></div>
                                                </td>
                                                <td class="text-center">
                                                    <input class="form-check-input js-role-allow"
                                                           type="checkbox"
                                                           data-perm-id="<?= $permissionId ?>"
                                                           name="role_allow_ids[]"
                                                           value="<?= $permissionId ?>"
                                                        <?= $isAllow ? 'checked' : '' ?>>
                                                </td>
                                                <td class="text-center">
                                                    <input class="form-check-input js-role-deny"
                                                           type="checkbox"
                                                           data-perm-id="<?= $permissionId ?>"
                                                           name="role_deny_ids[]"
                                                           value="<?= $permissionId ?>"
                                                        <?= $isDeny ? 'checked' : '' ?>>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-success">Rol Yetkilerini Kaydet</button>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).on('change', '.js-role-allow', function() {
        if ($(this).is(':checked')) {
            var permID = $(this).data('perm-id');
            $('.js-role-deny[data-perm-id="' + permID + '"]').prop('checked', false);
        }
    });

    $(document).on('change', '.js-role-deny', function() {
        if ($(this).is(':checked')) {
            var permID = $(this).data('perm-id');
            $('.js-role-allow[data-perm-id="' + permID + '"]').prop('checked', false);
        }
    });
</script>
