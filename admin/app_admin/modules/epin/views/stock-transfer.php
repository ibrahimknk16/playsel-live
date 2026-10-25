<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div >
<div class="card card-flush">
    <div class="card-header">
        <h4 class="mt-8">Stok Transfer İşlemi</h4>
        <ul>
            <li><strong>Yükleme Kodu:</strong> <?= html_escape($load->epin_load_code) ?></li>
            <li><strong>Mevcut Havuz ID:</strong> <?= (int)$load->epin_load_pool_id ?></li>
            <li><strong>Aktarılabilir Maksimum Stok:</strong> <?= (int)$poolStockCount ?> Adet</li>
        </ul>
    </div>

    <div class="card-body">
        <form action="<?= base_url('epin/stockTransferProcess') ?>" method="POST">

            <input type="hidden" name="load_code" value="<?= html_escape($load->epin_load_code) ?>">

            <div class="form-group">
                <label for="transfer_count">Taşınacak Stok Adedi</label>
                <input type="number"
                    class="form-control form-control-solid"
                    id="transfer_count"
                    name="transfer_count"
                    min="1"
                    max="<?= (int)$poolStockCount ?>"
                    required>
                <small class="form-text text-muted">
                    Bu yüklemeden en fazla <?= (int)$poolStockCount ?> adet pini başka bir havuza taşıyabilirsiniz.
                </small>
            </div>
            </div>

            <div class="form-group mt-3">
                <label for="target_pool_id">Hedef Havuz</label>
                <select class="form-control form-control-solid" id="target_pool_id" name="target_pool_id" data-control="select2" data-placeholder="Sağlayıcı Seçin" required>
                    <option value="">Lütfen aktarılacak havuzu seçin...</option>
                    <?php if (!empty($pools)): ?>
                        <?php foreach ($pools as $pool): ?>
                            <?php
                            // Mevcut havuzu select listesine dahil etmiyoruz.
                            if ($pool->epin_pool_id == $load->epin_load_pool_id) {
                                continue;
                            }
                            ?>
                            <option value="<?= (int)$pool->epin_pool_id ?>">
                                <?= html_escape($pool->epin_pool_title) ?> (ID: <?= (int)$pool->epin_pool_id ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary" onclick="this.disabled=true;this.form.submit();">
                    Seçili Stoğu Aktar
                </button>
            </div>

        </form>
    </div>
</div>