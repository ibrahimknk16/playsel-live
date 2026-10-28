<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Kullanıcı Detay</h3>
            <div class="card-toolbar">
                <a href="<?= base_url('admins/permissions') ?>" class="btn btn-sm btn-light-primary me-2">Permission CRUD</a>
                <a href="<?= base_url('admins/rolePermissions/' . (int) $user->admin_power) ?>" class="btn btn-sm btn-light-success">Rol Yetkileri</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-8">
                <div class="col-md-6">
                    <label class="form-label">Kullanıcı ID</label>
                    <input type="text" class="form-control form-control-solid" value="<?= (int) $user->admin_id ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kullanıcı Rolü</label>
                    <input type="text" class="form-control form-control-solid" value="<?= !empty($roles[$user->admin_power]) ? $roles[$user->admin_power] : $user->admin_power ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kullanıcı Adı</label>
                    <input type="text" class="form-control form-control-solid" value="<?= html_escape($user->admin_name) ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kullanıcı Email</label>
                    <input type="text" class="form-control form-control-solid" value="<?= html_escape($user->admin_email) ?>" disabled>
                </div>
            </div>

            <hr class="my-8" />

            <form method="POST" action="<?= base_url('admins/updateUserPermissions/' . (int) $user->admin_id) ?>">
                <input type="hidden" name="admin_action_csrf" value="<?= html_escape($adminActionCsrfToken) ?>">
                <input type="hidden" name="admin_id" value="<?= (int) $user->admin_id ?>">

                <div class="alert alert-secondary">
                    <div><strong>Inherit:</strong> Rolde allow var, kullanici override yok.</div>
                    <div><strong>Allow:</strong> Kullaniciya ekstra izin ver.</div>
                    <div><strong>Deny:</strong> Rolde olsa bile kullanicida engelle.</div>
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
                                            <th class="text-center">Inherit</th>
                                            <th class="text-center">Allow</th>
                                            <th class="text-center">Deny</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $permission): ?>
                                            <?php
                                            $permissionId = (int) $permission['permission_id'];
                                            $isRoleAllow = in_array($permissionId, $roleAllowIds);
                                            $isUserAllow = in_array($permissionId, $userAllowIds);
                                            $isUserDeny = in_array($permissionId, $userDenyIds);
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold"><?= html_escape($permission['permission_name']) ?></div>
                                                    <div class="text-muted small"><?= html_escape($permission['permission_code']) ?></div>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($isUserAllow || $isUserDeny): ?>
                                                        <span class="badge badge-light">Override</span>
                                                    <?php elseif ($isRoleAllow): ?>
                                                        <span class="badge badge-success">Role Allow</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-light-danger">No Access</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <input class="form-check-input js-perm-allow"
                                                           type="checkbox"
                                                           data-perm-id="<?= $permissionId ?>"
                                                           name="user_allow_ids[]"
                                                           value="<?= $permissionId ?>"
                                                        <?= $isUserAllow ? 'checked' : '' ?>>
                                                </td>
                                                <td class="text-center">
                                                    <input class="form-check-input js-perm-deny"
                                                           type="checkbox"
                                                           data-perm-id="<?= $permissionId ?>"
                                                           name="user_deny_ids[]"
                                                           value="<?= $permissionId ?>"
                                                        <?= $isUserDeny ? 'checked' : '' ?>>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-success">İzinleri Güncelle</button>
                <a href="<?= base_url('admins') ?>" class="btn btn-light ms-2">Geri Dön</a>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).on('change', '.js-perm-allow', function() {
        if ($(this).is(':checked')) {
            var permID = $(this).data('perm-id');
            $('.js-perm-deny[data-perm-id="' + permID + '"]').prop('checked', false);
        }
    });

    $(document).on('change', '.js-perm-deny', function() {
        if ($(this).is(':checked')) {
            var permID = $(this).data('perm-id');
            $('.js-perm-allow[data-perm-id="' + permID + '"]').prop('checked', false);
        }
    });
</script>