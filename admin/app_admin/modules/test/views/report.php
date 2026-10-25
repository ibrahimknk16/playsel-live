<?php if (isset($error)): ?>
    <div class="alert alert-danger fw-bold"><?= $error ?></div>
<?php return;
endif; ?>

<div class="card mb-10 card-flush">
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
                <label class="form-label fw-semibold">Ziraat USD/TRY Rate</label>
                <input type="text" name="exchangeRate"
                    class="form-control form-control-solid"
                    value="<?= htmlspecialchars($get['exchangeRate'] ?? '43.40') ?>">
            </div>
            <?php $providers = [9132 => 'Moonton MLBB', 9156 => 'Moonton MCGG'] ?>

            <div class="col-md-2">
                <!-- select provider  get ->providerID if null 9132 selected-->
                <label class="form-label fw-semibold">Provider</label>
                <select name="providerID" class="form-select form-select-solid">
                    <?php foreach ($providers as $id => $name): ?>
                        <option value="<?= $id ?>" <?= (isset($get['providerID']) && $get['providerID'] == $id) || (!isset($get['providerID']) && $id == 9132) ? 'selected' : '' ?>>
                            <?= $name ?>
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

        <div class="mb-8 text-gray-700 fs-6">
            <p><strong>Report Period:</strong> <?= $reportStart ?> → <?= $reportEnd ?></p>

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
        <a href="<?= base_url('test/exportMoontonReport?' . http_build_query($get)) ?>"
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
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($quantityReport as $productName => $quantity): ?>
                        <tr>
                            <td><?= htmlspecialchars($productName) ?></td>

                            <td class="text-center fw-bold"><?= $quantity ?></td>

                            <td class="text-center">
                                <?= number_format($productCostTotal[$productName], 2, ',', '.') ?> ₺
                            </td>

                            <td class="text-center">
                                <?= number_format($productCostTotal[$productName] * 0.82, 2, ',', '.') ?> ₺
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- TOTAL ROW -->
                    <tr class="fw-bold bg-light">
                        <td class="text-end">TOTAL</td>
                        <td class="text-center"><?= $totalQuantity ?></td>
                        <td class="text-center"><?= number_format($totalCost, 2, ',', '.') ?> ₺</td>
                        <td class="text-center"><?= number_format($totalCost * 0.82, 2, ',', '.') ?> ₺</td>
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

    </div>
</div>