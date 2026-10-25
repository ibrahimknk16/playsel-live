<style>
    .copy-value {
        cursor: pointer;
    }
</style>
<div class="card mb-12 card-flush">
    <div class="card-body">

        <!-- Filter Form -->
        <form method="get" class="row g-5 align-items-end mb-10">

            <div class="col-md-2">
                <label class="form-label fw-semibold">Start Date</label>
                <input type="date" name="startDate" class="form-control form-control-solid"
                    value="<?= !empty($get['startDate']) ? htmlspecialchars($get['startDate']) : date('Y-m-d') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">End Date</label>
                <input type="date" name="endDate" class="form-control form-control-solid"
                    value="<?= !empty($get['endDate']) ? htmlspecialchars($get['endDate']) : date('Y-m-d') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">USD Kuru</label>
                <input type="text" name="exchangeRate"
                    class="form-control form-control-solid"
                    value="<?= htmlspecialchars($get['exchangeRate'] ?? currencyCalculator('USD', 1)) ?>">
            </div>
            <?php //$providers = [9132 => 'Mobile Legends',  9156 => 'Magic Chess: Go Go', 9002 => 'Bangjeff', 9054 => 'Pinabi'] ?>
            <?php $providers = [9132 => 'Mobile Legends',  9156 => 'Magic Chess: Go Go'] ?>

            <div class="col-md-2">
                <!-- select provider  get ->providerID if null 9132 selected-->
                <label class="form-label fw-semibold">Provider</label>
                <select name="providerID" class="form-select form-select-solid">
                    <?php foreach ($providers as $id => $name): ?>
                        <option value="<?= $id ?>" <?= ($providerId == $id) ? 'selected' : '' ?>>
                            <?= $name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <!-- select provider  get ->providerID if null 9132 selected-->
                <label class="form-label fw-semibold">Reseller</label>
                <select name="resellerID" class="form-select form-select-solid">
                    <option value="">Tümü</option>
                    <?php foreach ($resellers as $reseller): ?>
                        <option value="<?= $reseller['user_id'] ?>" <?= ($resellerID == $reseller['user_id']) ? 'selected' : '' ?>>
                            <?= $reseller['user_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="la la-search"></i> Generate Report
                </button>
            </div>

        </form>

        <!-- Report Header -->
        <h2 class="fs-2 fw-bold text-dark mb-5">
            <?= $providerName ?> Product Quantity Report
        </h2>

        <?php if (empty($error)): ?>
            <div class="mb-8 text-gray-700 fs-6">
                <p><strong>Report Period:</strong> <?= !empty($reportStart) ? htmlspecialchars($reportStart) : date('Y-m-d') ?> → <?= !empty($reportEnd) ? htmlspecialchars($reportEnd) : date('Y-m-d') ?></p>
                <p>
                    <strong>Total Orders:</strong> <?= count($orders) ?> &nbsp; | &nbsp;
                    <strong>Total Quantity Sold:</strong> <?= $totalQuantity ?> &nbsp; | &nbsp;
                    <strong>Total Cost:</strong> <?= number_format($totalCost, 2, ',', '.') ?> TL
                </p>

                <p>
                    <b>First Order Transaction ID:</b> <?= $firstOrder->transactionID ?>
                    &nbsp; | &nbsp; Date: <?= $firstOrder->orderDatetime ?>
                </p>

                <p>
                    <b>Last Order Transaction ID:</b> <?= $lastOrder->transactionID ?>
                    &nbsp; | &nbsp; Date: <?= $lastOrder->orderDatetime ?>
                </p>
            </div>

            <!-- Export Button -->
            <a href="<?= base_url('reports/exportMoontonReport?' . http_build_query($get)) ?>"
                class="btn btn-success btn-sm mb-5">
                <i class="la la-file-excel"></i> Download Excel
            </a>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-row-bordered gy-4 gs-4 align-middle table-row-dashed">
                    <thead class="bg-light fw-bold text-center">
                        <tr class="text-gray-700">
                            <th class="text-start">Product Name</th>
                            <th>Quantity Sold</th>
                            <th>Total Cost (TL)</th>
                            <th>Discounted Cost (TL) -18%</th>
                            <th>Adet Maliyet (TL) -18%</th>
                            <th>Discounted Cost (TL) -19.5%</th>
                            <th>Adet Maliyet (TL) -19.5%</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($quantityReport as $productName => $quantity): ?>
                            <tr>
                                <td><?= htmlspecialchars($productName) ?></td>

                                <td class="text-center fw-bold"><?= $quantity ?> &nbsp;<i class="fa-solid fa-copy fs-12 copy-value" onClick=copy("<?= $quantity ?>") title="Kopyala"></i></td>

                                <td class="text-center">
                                    <?= number_format($productCostTotal[$productName], 2, ',', '.') ?> ₺
                                </td>

                                <td class="text-center">
                                    <?= number_format($productCostTotal[$productName] * 0.82, 2, ',', '.') ?> ₺
                                </td>
                                <td class="text-center">
                                    <?= number_format($productCostTotal[$productName] * 0.82 / $quantity, 2, ',', '.') ?> ₺
                                </td>
                                <td class="text-center">
                                    <?= number_format($productCostTotal[$productName] * 0.805, 2, ',', '.') ?> ₺
                                </td>
                                <td class="text-center">
                                    <?= number_format($productCostTotal[$productName] * 0.805 / $quantity, 2, ',', '.') ?> ₺
                                </td>

                            </tr>
                        <?php endforeach; ?>

                        <!-- TOTAL ROW -->
                        <tr class="fw-bold bg-light">
                            <td class="text-end">TOTAL</td>
                            <td class="text-center"><?= $totalQuantity ?></td>
                            <td class="text-center"><?= number_format($totalCost, 2, ',', '.') ?> ₺</td>
                            <td class="text-center"><?= number_format($totalCost * 0.82, 2, ',', '.') ?> ₺</td>
                            <td></td>
                            <td class="text-center"><?= number_format($totalCost * 0.805, 2, ',', '.') ?> ₺</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- USD Section -->
            <div class="mt-10">
                <h3 class="fs-3 fw-bold text-dark">
                    Total Payable in USD (fixed rate <?= $exchangeRate ?> Ziraat):
                    <span class="text-primary">
                        $<?= number_format($totalUSD_fixedRate, 4, ',', '.') ?>
                    </span>
                </h3>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    function copy(val) {
        var tempInput = document.createElement("input");
        tempInput.style.position = "absolute";
        tempInput.style.left = "-9999px";
        tempInput.value = val;
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.setSelectionRange(0, 99999);
        try {
            document.execCommand("copy");
            console.log("Kopyalandı: " + val);
        } catch (err) {
            console.error("Kopyalama başarısız!", err);
        }
        document.body.removeChild(tempInput);
        toastr.success('Kopyalandı: ' + val);
    }
</script>