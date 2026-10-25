<div class="row">
    <div class="card card-flush">
        <div class="card-header">
            <h3 class="card-title">Link Ekle</h3>
        </div>
        <div class="card-body">
            <form id="newsForm" action="<?= base_url('sayfalar/addSiteLinkAction') ?>" method="post">
                <div class="form-group">
                    <label for="link_type">Site Tipi</label>
                    <select id="link_type" id="link_type" name="link_type" class="form-control mt-3 form-control-solid">
                        <option value="haber">Haber</option>
                        <option value="blog">Blog</option>
                        <option value="eticaret">E-Ticaret</option>
                    </select>
                </div>
                <div class="form-group mt-6">
                    <label for="link_content">Linkler</label>
                    <textarea id="link_content" name="link_content" class="form-control mt-3 form-control-solid" rows="20"><?= $links[0]->link_content ?></textarea>
                </div>
                <div class="text-right mt-6">
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
    <div class="d-none">
        <?php foreach ($links as $link) : ?>
            <span class="<?= $link->link_type ?>"><?= $link->link_content ?></span>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // jquery #link_type change function to change the textarea value
        $('#link_type').change(function() {
            var linkType = $(this).val();
            $('#link_content').val($('span.' + linkType).html());
        });
    });
</script>