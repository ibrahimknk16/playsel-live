<div class="card mb-10 card-flush">
    <div class="card-body">

        <h2 class="fs-2 fw-bold text-dark mb-7">Toplu Fatura Kesim</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success mb-7"><?php echo html_escape($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-7"><?php echo html_escape($error); ?></div>
        <?php endif; ?>

        <form method="post" class="row g-5 align-items-end mb-10">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Reseller</label>
                <select name="reseller_id" class="form-select form-select-solid" required>
                    <option value="">Reseller seçiniz</option>
                    <?php foreach ($resellers as $reseller): ?>
                        <option value="<?php echo (int)$reseller['user_id']; ?>" <?php echo ((int)$filters['reseller_id'] === (int)$reseller['user_id']) ? 'selected' : ''; ?>>
                            <?php echo html_escape($reseller['user_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        

            <div class="col-md-3">
                <label class="form-label fw-semibold">Başlangıç Tarihi</label>
                <input type="date" name="start_date" class="form-control form-control-solid" value="<?php echo html_escape($filters['start_date']); ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Bitiş Tarihi</label>
                <input type="date" name="end_date" class="form-control form-control-solid" value="<?php echo html_escape($filters['end_date']); ?>" required>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Fatura Tarihi</label>
                <input type="date" name="invoice_date" class="form-control form-control-solid" value="<?php echo html_escape($filters['invoice_date'] ?? date('Y-m-d')); ?>" required>
            </div>

            <div class="col-md-2 d-grid">
                <button type="submit" name="invoice_action" value="preview" class="btn btn-primary">
                    Fatura Önizle
                </button>
            </div>
        </form>

        <?php if (!empty($preview)): ?>
            <div class="separator my-8"></div>

            <div class="row g-5 mb-7">
                <div class="col-md-3">
                    <div class="border rounded p-4 h-100">
                        <div class="text-muted fs-7">Sipariş Adedi</div>
                        <div class="fs-3 fw-bold"><?php echo (int)$preview['total_order_count']; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-4 h-100">
                        <div class="text-muted fs-7">Fatura Kalem Sayısı</div>
                        <div class="fs-3 fw-bold"><?php echo (int)$preview['total_item_count']; ?></div>
                    </div>
                </div>
            </div>

            <div class="mb-7 text-gray-700 fs-6">
                <p class="mb-2"><strong>Reseller:</strong> <?php echo html_escape($preview['user']->user_name ?? '-'); ?></p>
                <p class="mb-2"><strong>Tarih Aralığı:</strong> <?php echo html_escape($preview['date_start']); ?> - <?php echo html_escape($preview['date_end']); ?></p>
                <p class="mb-2"><strong>Fatura Tarihi:</strong> <?php echo html_escape($preview['invoice_date'] ?? '-'); ?></p>
                <p class="mb-2"><strong>Sipariş No:</strong> <?php echo html_escape($preview['order_no']); ?></p>
                <p class="mb-0"><strong>Fatura Tipi:</strong> <?php echo !empty($preview['invoice_data']['is_foreign']) ? 'Yurtdışı (İSTİSNA)' : 'Yurtiçi (SATIŞ)'; ?></p>
            </div>

            <div class="table-responsive mb-7">
                <table class="table table-bordered table-row-bordered gy-3 gs-3 align-middle table-row-dashed">
                    <thead class="bg-light fw-bold text-center">
                        <tr class="text-gray-700">
                            <th class="text-start">Stok Kodu</th>
                            <th class="text-start">Kalem Adı</th>
                            <th>Adet</th>
                            <th>Birim Fiyat</th>
                            <th>KDV %</th>
                            <th>KDV Tutarı</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview['items'] as $item): ?>
                            <tr>
                                <td><?php echo html_escape($item['stock_code']); ?></td>
                                <td><?php echo html_escape($item['name']); ?></td>
                                <td class="text-center"><?php echo (int)$item['qty']; ?></td>
                                <td class="text-end"><?php echo number_format((float)$item['price'], 4, ',', '.'); ?></td>
                                <td class="text-center"><?php echo (int)$item['tax_rate']; ?></td>
                                <td class="text-end"><?php echo number_format((float)$item['tax_amount'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row g-5 mb-7">
                <div class="col-md-4 offset-md-4">
                    <div class="border rounded p-4 h-100">
                        <div class="text-muted fs-7">KDV Hariç Toplam</div>
                        <div class="fs-3 fw-bold"><?php echo number_format((float)$preview['net_total'], 2, ',', '.'); ?> TL</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-4 h-100">
                        <div class="text-muted fs-7">Genel Toplam</div>
                        <div class="fs-3 fw-bold"><?php echo number_format((float)$preview['grand_total'], 2, ',', '.'); ?> TL</div>
                    </div>
                </div>
            </div>

            <form method="post" class="d-flex gap-3">
                <input type="hidden" name="reseller_id" value="<?php echo (int)$filters['reseller_id']; ?>">
                <input type="hidden" name="start_date" value="<?php echo html_escape($filters['start_date']); ?>">
                <input type="hidden" name="end_date" value="<?php echo html_escape($filters['end_date']); ?>">
                <input type="hidden" name="invoice_date" value="<?php echo html_escape($filters['invoice_date'] ?? date('Y-m-d')); ?>">
                <input type="hidden" name="confirm_cut" value="yes">

                <button type="submit" name="invoice_action" value="cut" class="btn btn-danger"
                    onclick="return confirm('Bu tarih aralığındaki seçili siparişler için fatura kesilecek. Devam edilsin mi?');">
                    Faturayı Kes
                </button>

                <button type="submit" name="invoice_action" value="preview" class="btn btn-light-primary">
                    Önizlemeyi Yenile
                </button>
            </form>
        <?php endif; ?>

    </div>
</div>
