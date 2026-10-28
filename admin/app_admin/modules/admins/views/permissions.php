<div class="row g-6">
    <div class="col-12">
        <div class="card shadow-sm card-flush">
            <div class="card-header">
                <h3 class="card-title">Permission CRUD</h3>
                <div class="card-toolbar">
                    <a href="<?= base_url('admins') ?>" class="btn btn-sm btn-light">Panel Kullanıcılar</a>
                    <a href="<?= base_url('admins/rolePermissions/3') ?>" class="btn btn-sm btn-light-primary ms-2">Rol Yetkileri</a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    Bu ekran permission_categories ve permissions tablolarini yonetir.
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm card-flush">
            <div class="card-header">
                <h4 class="card-title">Kategori Ekle</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= base_url('admins/addCategory') ?>">
                    <input type="hidden" name="admin_action_csrf" value="<?= html_escape($adminActionCsrfToken) ?>">
                    <div class="mb-4">
                        <label class="form-label">Kategori Key</label>
                        <input type="text" name="permission_category_key" class="form-control form-control-solid" placeholder="orders" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Kategori Adi</label>
                        <input type="text" name="permission_category_name" class="form-control form-control-solid" placeholder="Orders" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Durum</label>
                        <select name="permission_category_status" class="form-select form-select-solid">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Kategori Ekle</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm card-flush">
            <div class="card-header">
                <h4 class="card-title">Kategori Listesi</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle table-sm table-row-dashed">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Key</th>
                                <th>Ad</th>
                                <th>Durum</th>
                                <th>Islem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                        <td>
                                            <form id="category-form-<?= (int) $category['permission_category_id'] ?>" method="POST" action="<?= base_url('admins/updateCategory/' . (int) $category['permission_category_id']) ?>"></form>
                                            <input form="category-form-<?= (int) $category['permission_category_id'] ?>" type="hidden" name="admin_action_csrf" value="<?= html_escape($adminActionCsrfToken) ?>">
                                            <?= (int) $category['permission_category_id'] ?>
                                        </td>
                                        <td><input form="category-form-<?= (int) $category['permission_category_id'] ?>" type="text" name="permission_category_key" class="form-control form-control-sm form-control-solid" value="<?= html_escape($category['permission_category_key']) ?>" required></td>
                                        <td><input form="category-form-<?= (int) $category['permission_category_id'] ?>" type="text" name="permission_category_name" class="form-control form-control-sm form-control-solid" value="<?= html_escape($category['permission_category_name']) ?>" required></td>
                                        <td>
                                            <select form="category-form-<?= (int) $category['permission_category_id'] ?>" name="permission_category_status" class="form-select form-select-sm form-select-solid">
                                                <option value="1" <?= (int) $category['permission_category_status'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                                <option value="0" <?= (int) $category['permission_category_status'] === 0 ? 'selected' : '' ?>>Pasif</option>
                                            </select>
                                        </td>
                                        <td class="text-nowrap">
                                            <button form="category-form-<?= (int) $category['permission_category_id'] ?>" type="submit" class="btn btn-sm btn-primary">Guncelle</button>
                                            <button form="category-form-<?= (int) $category['permission_category_id'] ?>" type="submit" formaction="<?= base_url('admins/deleteCategory/' . (int) $category['permission_category_id']) ?>" formmethod="POST" class="btn btn-sm btn-danger" onclick="return confirm('Bu kategoriyi silmek istediginize emin misiniz?')">Sil</button>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm card-flush">
            <div class="card-header">
                <h4 class="card-title">Permission Ekle</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= base_url('admins/addPermission') ?>">
                    <input type="hidden" name="admin_action_csrf" value="<?= html_escape($adminActionCsrfToken) ?>">
                    <div class="row g-4">
                        <div class="col-md-2">
                            <label class="form-label">Kategori</label>
                            <select name="permission_category_id" class="form-select form-select-solid" required>
                                <option value="">Seciniz</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['permission_category_id'] ?>"><?= html_escape($category['permission_category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Module</label>
                            <input type="text" name="permission_module" class="form-control form-control-solid" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Controller</label>
                            <input type="text" name="permission_controller" class="form-control form-control-solid" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Method</label>
                            <input type="text" name="permission_method" class="form-control form-control-solid" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Permission Code</label>
                            <input type="text" name="permission_code" class="form-control form-control-solid" placeholder="orders.orders.index" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gorunen Ad</label>
                            <input type="text" name="permission_name" class="form-control form-control-solid" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aciklama</label>
                            <input type="text" name="permission_description" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Durum</label>
                            <select name="permission_status" class="form-select form-select-solid">
                                <option value="1">Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Permission Ekle</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm card-flush">
            <div class="card-header">
                <h4 class="card-title">Permission Listesi</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle table-sm table-row-dashed">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kategori</th>
                                <th>Code</th>
                                <th>Ad</th>
                                <th>Durum</th>
                                <th>Islem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($permissions as $permission): ?>
                                <tr>
                                        <td>
                                            <form id="permission-form-<?= (int) $permission['permission_id'] ?>" method="POST" action="<?= base_url('admins/updatePermission/' . (int) $permission['permission_id']) ?>"></form>
                                            <input form="permission-form-<?= (int) $permission['permission_id'] ?>" type="hidden" name="admin_action_csrf" value="<?= html_escape($adminActionCsrfToken) ?>">
                                            <?= (int) $permission['permission_id'] ?>
                                        </td>
                                        <td>
                                            <select form="permission-form-<?= (int) $permission['permission_id'] ?>" name="permission_category_id" class="form-select form-select-sm form-select-solid" required>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= (int) $category['permission_category_id'] ?>" <?= (int) $permission['permission_category_id'] === (int) $category['permission_category_id'] ? 'selected' : '' ?>>
                                                        <?= html_escape($category['permission_category_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input form="permission-form-<?= (int) $permission['permission_id'] ?>" type="text" name="permission_code" class="form-control form-control-sm mb-1 form-control-solid" value="<?= html_escape($permission['permission_code']) ?>" required>
                                            <div class="d-flex gap-1">
                                                <input form="permission-form-<?= (int) $permission['permission_id'] ?>" type="text" name="permission_module" class="form-control form-control-sm form-control-solid" value="<?= html_escape($permission['permission_module']) ?>" required>
                                                <input form="permission-form-<?= (int) $permission['permission_id'] ?>" type="text" name="permission_controller" class="form-control form-control-sm form-control-solid" value="<?= html_escape($permission['permission_controller']) ?>" required>
                                                <input form="permission-form-<?= (int) $permission['permission_id'] ?>" type="text" name="permission_method" class="form-control form-control-sm form-control-solid" value="<?= html_escape($permission['permission_method']) ?>" required>
                                            </div>
                                        </td>
                                        <td>
                                            <input form="permission-form-<?= (int) $permission['permission_id'] ?>" type="text" name="permission_name" class="form-control form-control-sm mb-1 form-control-solid" value="<?= html_escape($permission['permission_name']) ?>" required>
                                            <input form="permission-form-<?= (int) $permission['permission_id'] ?>" type="text" name="permission_description" class="form-control form-control-sm form-control-solid" value="<?= html_escape($permission['permission_description']) ?>">
                                        </td>
                                        <td>
                                            <select form="permission-form-<?= (int) $permission['permission_id'] ?>" name="permission_status" class="form-select form-select-sm form-select-solid">
                                                <option value="1" <?= (int) $permission['permission_status'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                                <option value="0" <?= (int) $permission['permission_status'] === 0 ? 'selected' : '' ?>>Pasif</option>
                                            </select>
                                        </td>
                                        <td class="text-nowrap">
                                            <button form="permission-form-<?= (int) $permission['permission_id'] ?>" type="submit" class="btn btn-sm btn-primary">Guncelle</button>
                                            <button form="permission-form-<?= (int) $permission['permission_id'] ?>" type="submit" formaction="<?= base_url('admins/deletePermission/' . (int) $permission['permission_id']) ?>" formmethod="POST" class="btn btn-sm btn-danger" onclick="return confirm('Permission silinsin mi?')">Sil</button>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
