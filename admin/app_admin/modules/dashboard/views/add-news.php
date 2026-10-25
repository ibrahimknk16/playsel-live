<div class="row">
    <div class="card card-flush">
        <div class="card-header">
            <h3 class="card-title">Haber Ekle</h3>
        </div>
        <div class="card-body">
            <form id="newsForm" action="<?= base_url('sayfalar/saveNews') ?>" method="post">
                <div class="form-group">
                    <label for="title">Başlık</label>
                    <input type="text" id="title" name="title" class="form-control mt-3 form-control-solid" placeholder="Başlık giriniz" maxlength="200">
                </div>
                <div class="form-group mt-6">
                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id" class="form-control mt-3 form-control-solid">
                        <option value="0">Seçiniz</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?= $category->title ?>"><?= $category->title ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mt-6">
                    <label for="site_id">Site Seç</label>
                    <select id="site_id" name="site_id" class="form-control mt-3 form-select form-control-solid form-select-solid" data-control="select2">
                        <option value="0">Seçiniz</option>
                        <?php foreach ($sites as $site) : ?>
                            <?php $selected = !empty($_GET['site']) && $_GET['site'] == $site->site_url ? 'selected' : ''; ?>
                            <option <?= $selected ?> value="<?= $site->site_url ?>"><?= $site->site_url ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mt-6">
                    <label for="description">Açıklama</label>
                    <textarea id="description" name="description" class="form-control mt-3 form-control-solid" rows="2"></textarea>
                </div>
                <div class="form-group mt-6">
                    <label for="content">İçerik</label>
                    <textarea id="content" name="content" class="form-control mt-3 form-control-solid" rows="6"></textarea>
                </div>
                <div class="form-group mt-6">
                    <label for="image">Haber Görseli</label>
                    <input type="text" id="image" name="image" class="form-control mt-3 form-control-solid" placeholder="Haber görseli giriniz" maxlength="400">
                </div>
                <div class="form-group mt-6">
                    <label for="source">Kaynak</label>
                    <input type="text" id="source" name="source" class="form-control mt-3 form-control-solid" placeholder="Kaynak giriniz" maxlength="400">
                </div>
                <div class="form-group mt-6">
                    <label for="is_active">Durum</label>
                    <select id="is_active" name="is_active" class="form-control mt-3 form-control-solid">
                        <option value="1">Aktif</option>
                        <option value="0">Pasif</option>
                    </select>
                </div>
                <div class="text-right mt-6">
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        //
    });
</script>