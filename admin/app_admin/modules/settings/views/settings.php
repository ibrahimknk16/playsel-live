<form method="post" action="<?= base_url('settings/save') ?>">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
        <div>
            <h2 class="mb-1">Site Ayarları</h2>
            <p class="text-muted mb-0">Marka, SEO, iletişim, duyuru ve bakım ayarlarını buradan yönetin.</p>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-2"></i> Ayarları Kaydet
        </button>
    </div>

    <?php foreach ($settings as $section): ?>
        <div class="card mb-6 card-flush">
            <div class="card-header">
                <h3 class="card-title"><?= html_escape($section['label']) ?></h3>
            </div>
            <div class="card-body">
                <div class="row g-6">
                    <?php foreach ($section['fields'] as $key => $field): ?>
                        <?php $value = $values[$key] ?? ''; ?>
                        <?php if ($field['type'] === 'checkbox'): ?>
                            <div class="col-12">
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="<?= $key ?>" value="1"
                                        id="<?= $key ?>" <?= $value === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="<?= $key ?>">
                                        <?= html_escape($field['label']) ?>
                                    </label>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-<?= in_array($field['type'], ['textarea', 'code'], true) ? '12' : '6' ?>">
                                <label class="form-label" for="<?= $key ?>"><?= html_escape($field['label']) ?></label>
                                <?php if ($field['type'] === 'textarea' || $field['type'] === 'code'): ?>
                                    <?php if ($field['type'] === 'code'): ?><div class="alert alert-warning py-2 fs-7">Bu alan HTML/JavaScript çalıştırabilir. Yalnızca güvendiğiniz servis kodlarını ekleyin.</div><?php endif; ?>
                                    <?php if (!empty($field['help'])): ?><div class="text-muted fs-7 mb-2"><?= html_escape($field['help']) ?></div><?php endif; ?>
                                    <textarea class="form-control font-monospace form-control-solid" rows="<?= $field['type'] === 'code' ? 8 : 3 ?>" name="<?= $key ?>" id="<?= $key ?>"><?= html_escape($value) ?></textarea>
                                <?php else: ?>
                                    <input class="form-control form-control-solid" type="<?= $field['type'] ?>" name="<?= $key ?>"
                                        id="<?= $key ?>" value="<?= html_escape($value) ?>">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</form>
