<div class="card mb-10 card-flush">
    <div class="card-body">

        <h2 class="fs-2 fw-bold text-dark mb-7">Toplu Fatura Kesim</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success mb-7">
                <i class="ki-duotone ki-check-circle fs-2 text-success me-2"></i>
                <?php echo html_escape($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-7">
                <i class="ki-duotone ki-cross-circle fs-2 text-danger me-2"></i>
                <?php echo html_escape($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="row g-5 align-items-end mb-10">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Reseller</label>
                <select name="reseller_id" class="form-select form-select-solid select-2" required>
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


        <script>
            // Initialize Select2
            $(document).ready(function() {
                $('.select-2').select2();
            });
        </script>

        <?php if (!empty($preview)): ?>
            <div class="separator my-8"></div>

            <div class="notice d-flex rounded border-info border border-dashed p-6 mb-8">
                <i class="ki-duotone ki-information fs-2tx text-info me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <div class="d-flex flex-stack flex-grow-1">
                    <div class="fw-semibold">
                        <h4 class="text-gray-900 fw-bold">Akıllı Gruplama Aktif</h4>
                        <div class="fs-6 text-gray-700">Seçilen tarih aralığındaki siparişler ürün bazlı gruplandırılmış, hizmet bedelleri tek bir satırda toplanarak UBL faturası sadeleştirilmiştir.</div>
                    </div>
                </div>
            </div>

            <div class="row g-5 mb-7">
                <div class="col-md-3">
                    <div class="border rounded p-4 h-100 border-primary border-dashed bg-light-primary">
                        <div class="text-primary fs-7 fw-bold">İşlenen Sipariş Adedi</div>
                        <div class="fs-2 fw-bolder text-primary"><?php echo (int)$preview['total_order_count']; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-4 h-100 border-success border-dashed bg-light-success">
                        <div class="text-success fs-7 fw-bold">Nihai Fatura Kalem Sayısı</div>
                        <div class="fs-2 fw-bolder text-success"><?php echo (int)$preview['total_item_count']; ?></div>
                    </div>
                </div>
            </div>

            <div class="mb-7 text-gray-700 fs-6 bg-light p-5 rounded">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Reseller:</strong> <span class="text-dark fw-bold"><?php echo html_escape($preview['user']->user_name ?? '-'); ?></span></p>
                        <p class="mb-2"><strong>Tarih Aralığı:</strong> <?php echo date('d.m.Y', strtotime($preview['date_start'])); ?> - <?php echo date('d.m.Y', strtotime($preview['date_end'])); ?></p>
                        <p class="mb-0"><strong>Fatura Tarihi:</strong> <?php echo date('d.m.Y', strtotime($preview['invoice_date'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Sipariş Aralığı:</strong> <span class="badge badge-light-dark fw-bold"><?php echo html_escape($preview['order_no']); ?></span></p>
                        <p class="mb-0"><strong>Fatura Tipi:</strong>
                            <?php if (!empty($preview['invoice_data']['is_foreign'])): ?>
                                <span class="badge badge-light-warning fw-bold">Yurtdışı (İSTİSNA - %0 KDV)</span>
                            <?php else: ?>
                                <span class="badge badge-light-info fw-bold">Yurtiçi (SATIŞ)</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <!-- Fatura bilgileri 
                     user_bill_company_name
                     user_bill_city
                     user_bill_district
                     user_bill_address
                     user_bill_tax_office
                     user_bill_tax_number
                     user_bill_e_invoice
                     user_is_turkish_company
                     -->
                    <div class="col-md-12 mt-5">
                        <p class="mb-0"><strong>Fatura Bilgileri:</strong></p>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Firma Adı:</strong> <?php echo html_escape($preview['user']->user_bill_company_name ?? '-'); ?></li>
                            <li><strong>Adres:</strong> <?php echo html_escape($preview['user']->user_bill_address ?? '-'); ?>, <?php echo html_escape($preview['user']->user_bill_district ?? '-'); ?> / <?php echo html_escape($preview['user']->user_bill_city ?? '-'); ?></li>
                            <li><strong>Vergi Dairesi:</strong> <?php echo html_escape($preview['user']->user_bill_tax_office ?? '-'); ?></li>
                            <li><strong>Vergi Numarası:</strong> <?php echo html_escape($preview['user']->user_bill_tax_number ?? '-'); ?></li>
                            <li><strong>E-Fatura Mükellefi:</strong> <?php echo !empty($preview['user']->user_bill_e_invoice) ? 'Evet' : 'Hayır'; ?></li>
                            <li><strong>Türk Şirketi:</strong> <?php echo !empty($preview['user']->user_is_turkish_company) ? 'Evet' : 'Hayır'; ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="table-responsive mb-7">
                <table class="table table-bordered table-row-bordered table-striped gy-4 gs-4 align-middle table-row-dashed">
                    <thead class="bg-dark fw-bold text-white text-center">
                        <tr>
                            <th class="text-start rounded-start">Stok Kodu</th>
                            <th class="text-start">Ürün / Kalem Adı</th>
                            <th class="w-100px">Adet</th>
                            <th class="w-150px">Ağırlıklı Birim Fiyat</th>
                            <th class="w-100px">KDV %</th>
                            <th class="w-150px rounded-end">KDV Tutarı</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview['items'] as $item): ?>
                            <tr>
                                <td class="text-muted fw-semibold"><?php echo html_escape($item['stock_code'] ?? '0-0'); ?></td>
                                <td class="fw-bold text-dark">
                                    <?php echo html_escape($item['name']); ?>
                                    <?php if ($item['name'] === 'Hizmet Bedeli'): ?>
                                        <span class="badge badge-light-success ms-2 fs-8">Kümülatif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center fw-bold fs-5">
                                    <span class="badge badge-secondary fs-6"><?php echo (int)$item['qty']; ?></span>
                                </td>
                                <td class="text-end fw-semibold"><?php echo number_format((float)$item['price'], 4, ',', '.'); ?> ₺</td>
                                <td class="text-center">
                                    <span class="badge badge-light-primary fw-bold">%<?php echo (int)$item['tax_rate']; ?></span>
                                </td>
                                <td class="text-end fw-semibold"><?php echo number_format((float)$item['tax_amount'], 2, ',', '.'); ?> ₺</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row g-5 mb-10 justify-content-end">
                <div class="col-md-4">
                    <div class="border rounded p-5 bg-light-dark border-dark border-dashed text-end">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted fw-bold">KDV Hariç Toplam:</span>
                            <span class="fw-bolder fs-5"><?php echo number_format((float)$preview['net_total'], 2, ',', '.'); ?> ₺</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted fw-bold">Toplam KDV:</span>
                            <span class="fw-bolder fs-5"><?php echo number_format((float)$preview['tax_total'], 2, ',', '.'); ?> ₺</span>
                        </div>
                        <div class="separator border-dark mb-3"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-dark fw-bolder fs-4">Genel Toplam:</span>
                            <span class="text-success fw-bolder fs-2"><?php echo number_format((float)$preview['grand_total'], 2, ',', '.'); ?> ₺</span>
                        </div>
                    </div>
                </div>
            </div>

            <form method="post" class="d-flex gap-3 justify-content-end">
                <input type="hidden" name="reseller_id" value="<?php echo (int)$filters['reseller_id']; ?>">
                <input type="hidden" name="start_date" value="<?php echo html_escape($filters['start_date']); ?>">
                <input type="hidden" name="end_date" value="<?php echo html_escape($filters['end_date']); ?>">
                <input type="hidden" name="invoice_date" value="<?php echo html_escape($filters['invoice_date'] ?? date('Y-m-d')); ?>">
                <input type="hidden" name="confirm_cut" value="yes">

                <button type="submit" name="invoice_action" value="preview" class="btn btn-light-primary px-8">
                    <i class="ki-duotone ki-arrows-circle fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                    Önizlemeyi Yenile
                </button>

                <button type="submit" name="invoice_action" value="cut" class="btn btn-danger fw-bold px-8"
                    onclick="return confirm('Bu tarih aralığındaki <?php echo (int)$preview['total_order_count']; ?> sipariş, <?php echo (int)$preview['total_item_count']; ?> kalem olarak faturaya yansıtılacaktır. Onaylıyor musunuz?');">
                    <i class="ki-duotone ki-send fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                    Faturayı Kes
                </button>
            </form>
        <?php endif; ?>

    </div>
</div>