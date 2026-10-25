<?php
$filters = $filters ?? [];
$summary = array_merge([
  'order_count' => 0,
  'total_qty' => 0,
  'total_cost_tl' => 0,
  'total_sale' => 0,
  'profit' => 0,
], $summary ?? []);
$productRows = $productRows ?? [];
$statusMap = $statusMap ?? [];
$resellers = $resellers ?? [];
$suppliers = $suppliers ?? [];
$categories = $categories ?? [];
$reportedProducts = $reportedProducts ?? [];

$formatNumber = static function ($value, int $decimals = 0): string {
  return number_format((float)$value, $decimals, ',', '.');
};

$formatMoney = static function ($value, string $currency = 'TL') use ($formatNumber): string {
  return $formatNumber($value, 2) . ' ' . $currency;
};

$formatPercent = static function ($value): string {
  return number_format((float)$value, 1, ',', '.') . '%';
};

$formatDateLabel = static function ($value): string {
  $timestamp = strtotime((string)$value);
  return $timestamp ? date('d.m.Y', $timestamp) : (string)$value;
};

$capPercent = static function ($value): float {
  return max(0, min(100, (float)$value));
};

$buildLookup = static function (array $items, string $idKey, string $nameKey): array {
  $lookup = [];
  foreach ($items as $item) {
    if (isset($item[$idKey], $item[$nameKey])) {
      $lookup[(int)$item[$idKey]] = (string)$item[$nameKey];
    }
  }
  return $lookup;
};

$sites = [3 => 'Dijipin', 2 => 'Topupcenter'];
$resellerMap = $buildLookup($resellers, 'user_id', 'user_name');
$supplierMap = $buildLookup($suppliers, 'user_id', 'user_name');
$categoryMap = $buildLookup($categories, 'category_id', 'category_title');
$productMap = $buildLookup($reportedProducts, 'order_product_id', 'product_name');

$startDateValue = substr((string)($filters['start'] ?? date('Y-m-d 00:00:00')), 0, 10);
$endDateValue = substr((string)($filters['end'] ?? date('Y-m-d 23:59:59')), 0, 10);
$reportRangeLabel = $formatDateLabel($startDateValue) . ' - ' . $formatDateLabel($endDateValue);
$resellerLabelForExport = !empty($filters['reseller_id'])
  ? ($resellerMap[(int)$filters['reseller_id']] ?? ('#' . (int)$filters['reseller_id']))
  : 'Tüm Bayiler';
$reportExportTitle = $resellerLabelForExport . ' - ' . $reportRangeLabel . ' Raporu';

$selectedStatuses = array_map('intval', $filters['status'] ?? array_keys($statusMap));
if (empty($selectedStatuses) && !empty($statusMap)) {
  $selectedStatuses = array_map('intval', array_keys($statusMap));
}

$selectedStatusNames = [];
foreach ($selectedStatuses as $statusId) {
  if (isset($statusMap[$statusId])) {
    $selectedStatusNames[] = $statusMap[$statusId];
  }
}

$statusPreview = 'Durum seçilmedi';
if (!empty($statusMap) && count($selectedStatuses) === count($statusMap)) {
  $statusPreview = 'Tüm durumlar';
} elseif (!empty($selectedStatusNames)) {
  $statusPreview = implode(', ', array_slice($selectedStatusNames, 0, 3));
  if (count($selectedStatusNames) > 3) {
    $statusPreview .= ' +' . (count($selectedStatusNames) - 3);
  }
}

$successInSelection = in_array(1, $selectedStatuses, true);

$displayRows = [];
$tableTotals = [
  'order_count' => 0,
  'total_qty' => 0,
  'total_cost_tl' => 0.0,
  'total_sale' => 0.0,
  'profit' => 0.0,
];
$categorySummary = [];
$maxOrderCount = 0;

foreach ($productRows as $index => $row) {
  $orderCount = (int)($row['order_count'] ?? 0);
  $totalQty = (int)($row['total_qty'] ?? 0);
  $totalCost = (float)($row['total_cost_tl'] ?? 0);
  $totalSale = (float)($row['total_sale'] ?? 0);
  $profit = (float)($row['profit'] ?? ($totalSale - $totalCost));
  $unitSale = $totalQty > 0 ? $totalSale / $totalQty : 0;
  $unitCost = $totalQty > 0 ? $totalCost / $totalQty : 0;
  $marginRate = $totalSale > 0 ? ($profit / $totalSale) * 100 : 0;
  $categoryTitle = trim((string)($row['category_title'] ?? '')) ?: 'Kategorisiz';

  $displayRows[] = [
    'rank' => $index + 1,
    'order_product_id' => (int)($row['order_product_id'] ?? 0),
    'order_external_product_id' => trim((string)($row['order_external_product_id'] ?? '')),
    'product_name' => trim((string)($row['product_name'] ?? '')) ?: 'İsimsiz ürün',
    'category_title' => $categoryTitle,
    'order_count' => $orderCount,
    'total_qty' => $totalQty,
    'total_cost_tl' => $totalCost,
    'total_sale' => $totalSale,
    'profit' => $profit,
    'unit_sale' => $unitSale,
    'unit_cost' => $unitCost,
    'margin_rate' => $marginRate,
    'order_heat' => 0,
    'sale_share' => 0,
  ];

  $tableTotals['order_count'] += $orderCount;
  $tableTotals['total_qty'] += $totalQty;
  $tableTotals['total_cost_tl'] += $totalCost;
  $tableTotals['total_sale'] += $totalSale;
  $tableTotals['profit'] += $profit;
  $maxOrderCount = max($maxOrderCount, $orderCount);

  if (!isset($categorySummary[$categoryTitle])) {
    $categorySummary[$categoryTitle] = [
      'category_title' => $categoryTitle,
      'order_count' => 0,
      'total_qty' => 0,
      'total_sale' => 0.0,
    ];
  }

  $categorySummary[$categoryTitle]['order_count'] += $orderCount;
  $categorySummary[$categoryTitle]['total_qty'] += $totalQty;
  $categorySummary[$categoryTitle]['total_sale'] += $totalSale;
}

$successfulMarginRate = $tableTotals['total_sale'] > 0 ? ($tableTotals['profit'] / $tableTotals['total_sale']) * 100 : 0;
$successfulCoverageRate = (int)$summary['order_count'] > 0 ? ($tableTotals['order_count'] / (int)$summary['order_count']) * 100 : 0;
$averageOrderValue = (int)$summary['order_count'] > 0 ? (float)$summary['total_sale'] / (int)$summary['order_count'] : 0;
$averageUnitRevenue = (int)$summary['total_qty'] > 0 ? (float)$summary['total_sale'] / (int)$summary['total_qty'] : 0;
$averageUnitCost = (int)$summary['total_qty'] > 0 ? (float)$summary['total_cost_tl'] / (int)$summary['total_qty'] : 0;
$productCount = count($displayRows);
$ordersPerProduct = $productCount > 0 ? $tableTotals['order_count'] / $productCount : 0;
$overallMarginRate = (float)$summary['total_sale'] > 0 ? ((float)$summary['profit'] / (float)$summary['total_sale']) * 100 : 0;

foreach ($displayRows as &$displayRow) {
  $displayRow['order_heat'] = $maxOrderCount > 0 ? ($displayRow['order_count'] / $maxOrderCount) * 100 : 0;
  $displayRow['sale_share'] = $tableTotals['total_sale'] > 0 ? ($displayRow['total_sale'] / $tableTotals['total_sale']) * 100 : 0;
}
unset($displayRow);

$categorySummary = array_values($categorySummary);
usort($categorySummary, static function ($left, $right) {
  return $right['total_sale'] <=> $left['total_sale'];
});

foreach ($categorySummary as &$categoryItem) {
  $categoryItem['sale_share'] = $tableTotals['total_sale'] > 0 ? ($categoryItem['total_sale'] / $tableTotals['total_sale']) * 100 : 0;
}
unset($categoryItem);

$topProducts = array_slice($displayRows, 0, 5);
$topCategories = array_slice($categorySummary, 0, 4);
$bestProduct = $topProducts[0] ?? null;
$dominantCategory = $topCategories[0] ?? null;

$activeFilterBadges = [
  ['label' => 'Dönem', 'value' => $reportRangeLabel],
  ['label' => 'Durum', 'value' => $statusPreview],
];

if (!empty($filters['site_id']) && isset($sites[(int)$filters['site_id']])) {
  $activeFilterBadges[] = ['label' => 'Site', 'value' => $sites[(int)$filters['site_id']]];
}
if (!empty($filters['reseller_id'])) {
  $activeFilterBadges[] = ['label' => 'Satıcı', 'value' => $resellerMap[(int)$filters['reseller_id']] ?? ('#' . (int)$filters['reseller_id'])];
}
if (!empty($filters['supplier_id'])) {
  $activeFilterBadges[] = ['label' => 'Tedarikçi', 'value' => $supplierMap[(int)$filters['supplier_id']] ?? ('#' . (int)$filters['supplier_id'])];
}
if (!empty($filters['category_id'])) {
  $activeFilterBadges[] = ['label' => 'Kategori', 'value' => $categoryMap[(int)$filters['category_id']] ?? ('#' . (int)$filters['category_id'])];
}
if (!empty($filters['product_id'])) {
  $activeFilterBadges[] = ['label' => 'Ürün', 'value' => $productMap[(int)$filters['product_id']] ?? ('#' . (int)$filters['product_id'])];
}

$profitClass = (float)$summary['profit'] >= 0 ? 'text-success' : 'text-danger';
?>

<style>
  .reports-dashboard .dashboard-card {
    border: 1px solid rgba(var(--bs-primary-rgb), 0.08);
    background: var(--bs-card-bg, var(--bs-body-bg));
  }

  .reports-dashboard .dashboard-hero {
    overflow: hidden;
    background:
      radial-gradient(circle at top right, rgba(var(--bs-info-rgb), 0.18), transparent 34%),
      radial-gradient(circle at bottom left, rgba(var(--bs-primary-rgb), 0.14), transparent 28%),
      linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.10), rgba(var(--bs-success-rgb), 0.05)),
      var(--bs-card-bg, var(--bs-body-bg));
    border: 1px solid rgba(var(--bs-primary-rgb), 0.12);
  }

  .reports-dashboard .hero-divider {
    border-left: 1px solid rgba(var(--bs-primary-rgb), 0.10);
  }

  .reports-dashboard .hero-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 0.9rem;
    border-radius: 999px;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.12);
    background: rgba(var(--bs-body-bg-rgb), 0.70);
    backdrop-filter: blur(8px);
  }

  .reports-dashboard .hero-chip-label {
    color: var(--bs-gray-600);
    font-size: 0.75rem;
    letter-spacing: 0.02em;
    text-transform: uppercase;
  }

  .reports-dashboard .hero-chip-value {
    font-weight: 600;
  }

  .reports-dashboard .mini-metric {
    height: 100%;
    padding: 1rem 1.1rem;
    border-radius: 1rem;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.10);
    background: rgba(var(--bs-body-bg-rgb), 0.72);
    backdrop-filter: blur(8px);
  }

  .reports-dashboard .mini-metric-label {
    color: var(--bs-gray-600);
    font-size: 0.8rem;
    margin-bottom: 0.35rem;
  }

  .reports-dashboard .mini-metric-value {
    font-size: 1.15rem;
    font-weight: 700;
    line-height: 1.2;
  }

  .reports-dashboard .filter-shell {
    border-radius: 1.25rem;
    padding: 1.5rem;
    border: 1px dashed rgba(var(--bs-primary-rgb), 0.18);
    background:
      linear-gradient(180deg, rgba(var(--bs-primary-rgb), 0.05), transparent 55%),
      var(--bs-card-bg, var(--bs-body-bg));
  }

  .reports-dashboard .filter-shell .select2-container {
    width: 100% !important;
  }

  /* Select2 Multiple arama inputunun gizlenmesini engelle ve genişlet */
  .select2-container--default .select2-search--inline .select2-search__field {
    width: 100% !important;
    margin-top: 0 !important;
    padding-left: 10px !important;
  }

  /* Eğer dropdown açıldığında arama kutusu kayboluyorsa */
  .select2-search input {
    display: block !important;
  }

  .reports-dashboard .filter-shell .form-control,
  .reports-dashboard .filter-shell .form-select,
  .reports-dashboard .filter-shell .select2-selection {
    border-radius: 0.95rem !important;
  }

  .reports-dashboard .kpi-card {
    position: relative;
    overflow: hidden;
    height: 100%;
    border-radius: 1.25rem;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.08);
    background:
      linear-gradient(160deg, rgba(var(--bs-primary-rgb), 0.08), transparent 48%),
      var(--bs-card-bg, var(--bs-body-bg));
  }

  .reports-dashboard .kpi-card::after {
    content: '';
    position: absolute;
    width: 180px;
    height: 180px;
    right: -75px;
    top: -90px;
    border-radius: 50%;
    background: rgba(var(--bs-primary-rgb), 0.08);
  }

  .reports-dashboard .kpi-card.kpi-info::after {
    background: rgba(var(--bs-info-rgb), 0.08);
  }

  .reports-dashboard .kpi-card.kpi-warning::after {
    background: rgba(var(--bs-warning-rgb), 0.10);
  }

  .reports-dashboard .kpi-card.kpi-success::after {
    background: rgba(var(--bs-success-rgb), 0.10);
  }

  .reports-dashboard .kpi-card.kpi-danger::after {
    background: rgba(var(--bs-danger-rgb), 0.10);
  }

  .reports-dashboard .kpi-icon {
    width: 52px;
    height: 52px;
    border-radius: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--bs-primary-rgb), 0.12);
    color: var(--bs-primary);
    position: relative;
    z-index: 1;
  }

  .reports-dashboard .kpi-info .kpi-icon {
    background: rgba(var(--bs-info-rgb), 0.12);
    color: var(--bs-info);
  }

  .reports-dashboard .kpi-warning .kpi-icon {
    background: rgba(var(--bs-warning-rgb), 0.15);
    color: var(--bs-warning);
  }

  .reports-dashboard .kpi-success .kpi-icon {
    background: rgba(var(--bs-success-rgb), 0.12);
    color: var(--bs-success);
  }

  .reports-dashboard .kpi-danger .kpi-icon {
    background: rgba(var(--bs-danger-rgb), 0.12);
    color: var(--bs-danger);
  }

  .reports-dashboard .insight-card,
  .reports-dashboard .table-card {
    border-radius: 1.25rem;
  }

  .reports-dashboard .insight-list-item {
    padding: 1rem 1.1rem;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.08);
    border-radius: 1rem;
    background: rgba(var(--bs-body-bg-rgb), 0.65);
  }

  .reports-dashboard .insight-list-item+.insight-list-item {
    margin-top: 0.85rem;
  }

  .reports-dashboard .performance-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.75rem;
    border-radius: 999px;
    background: rgba(var(--bs-primary-rgb), 0.10);
    color: var(--bs-primary);
    font-size: 0.8rem;
    font-weight: 600;
  }

  .reports-dashboard .rank-item {
    padding: 1rem 1.1rem;
    border-radius: 1rem;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.08);
    background: rgba(var(--bs-body-bg-rgb), 0.66);
  }

  .reports-dashboard .rank-item+.rank-item {
    margin-top: 0.85rem;
  }

  .reports-dashboard .rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    height: 2rem;
    border-radius: 999px;
    background: rgba(var(--bs-primary-rgb), 0.12);
    color: var(--bs-primary);
    font-weight: 700;
    font-size: 0.85rem;
  }

  .reports-dashboard .progress-soft {
    height: 0.45rem;
    border-radius: 999px;
    background: rgba(var(--bs-primary-rgb), 0.08);
    overflow: hidden;
  }

  .reports-dashboard .progress-soft>span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, var(--bs-primary), var(--bs-info));
  }

  .reports-dashboard .table-toolbar-input {
    min-width: 280px;
  }

  .reports-dashboard .table-toolbar-actions {
    min-height: 38px;
  }

  .reports-dashboard .reports-table {
    margin-bottom: 0 !important;
  }

  .reports-dashboard .reports-table thead th {
    white-space: nowrap;
    font-size: 0.75rem;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--bs-gray-600);
    padding-top: 1rem;
    padding-bottom: 1rem;
  }

  .reports-dashboard .reports-table tbody tr {
    transition: background-color 0.18s ease, transform 0.18s ease;
  }

  .reports-dashboard .reports-table tbody tr:hover {
    background: rgba(var(--bs-primary-rgb), 0.04);
    transform: translateY(-1px);
  }

  .reports-dashboard .product-cell {
    min-width: 280px;
  }

  .reports-dashboard .product-name {
    max-width: 280px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .reports-dashboard .metric-stack {
    min-width: 150px;
  }

  .reports-dashboard .category-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.45rem 0.75rem;
    border-radius: 999px;
    background: rgba(var(--bs-info-rgb), 0.10);
    color: var(--bs-info);
    font-weight: 600;
  }

  .reports-dashboard .table-empty {
    padding: 3rem 1rem;
  }

  .reports-dashboard .table-empty-icon {
    width: 72px;
    height: 72px;
    border-radius: 1.5rem;
    margin: 0 auto 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--bs-warning-rgb), 0.12);
    color: var(--bs-warning);
  }

  .reports-dashboard .dataTables_wrapper .dataTables_info,
  .reports-dashboard .dataTables_wrapper .dataTables_paginate {
    padding-top: 1rem;
  }

  @media (max-width: 1199.98px) {
    .reports-dashboard .hero-divider {
      border-left: 0;
      border-top: 1px solid rgba(var(--bs-primary-rgb), 0.10);
    }
  }

  @media (max-width: 991.98px) {
    .reports-dashboard .table-toolbar-input {
      min-width: 100%;
    }

    .reports-dashboard .filter-shell {
      padding: 1.15rem;
    }
  }
</style>

<div class="reports-dashboard">

  <div class="row g-6 mb-8">
    <div class="col-md-6 col-xl-3">
      <div class="card card-flush shadow-sm kpi-card kpi-info">
        <div class="card-body d-flex flex-column gap-4">
          <div class="d-flex justify-content-between align-items-start">
            <div class="kpi-icon">
              <i class="ki-duotone ki-basket fs-2x">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
              </i>
            </div>
            <span class="badge badge-light-info">Toplam sipariş</span>
          </div>
          <div>
            <div class="text-muted fs-7 text-uppercase mb-2">Sipariş Adedi</div>
            <div class="fs-2hx fw-bold"><?php echo $formatNumber($summary['order_count']); ?></div>
          </div>
          <div class="text-muted fs-4">Seçili filtrelerde oluşan toplam sipariş sayısı</div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card card-flush shadow-sm kpi-card kpi-warning">
        <div class="card-body d-flex flex-column gap-4">
          <div class="d-flex justify-content-between align-items-start">
            <div class="kpi-icon">
              <i class="ki-duotone ki-chart-simple fs-2x">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
              </i>
            </div>
            <span class="badge badge-light-warning">Toplam Adet</span>
          </div>
          <div>
            <div class="text-muted fs-7 text-uppercase mb-2">Toplam Adet</div>
            <div class="fs-2hx fw-bold"><?php echo $formatNumber($summary['total_qty']); ?></div>
          </div>
          <div class="text-muted fs-4">Birim başına ortalama maliyet: <?php echo $formatMoney($averageUnitCost); ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card card-flush shadow-sm kpi-card kpi-success">
        <div class="card-body d-flex flex-column gap-4">
          <div class="d-flex justify-content-between align-items-start">
            <div class="kpi-icon">
              <i class="ki-duotone ki-dollar fs-2x">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
              </i>
            </div>
            <span class="badge badge-light-success">Ciro</span>
          </div>
          <div>
            <div class="text-muted fs-7 text-uppercase mb-2">Toplam Satış</div>
            <div class="fs-2hx fw-bold"><?php echo $formatMoney($summary['total_sale']); ?></div>
          </div>
          <div class="text-muted fs-4">Ortalama sipariş değeri: <?php echo $formatMoney($averageOrderValue); ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card card-flush shadow-sm kpi-card kpi-danger">
        <div class="card-body d-flex flex-column gap-4">
          <div class="d-flex justify-content-between align-items-start">
            <div class="kpi-icon">
              <i class="ki-duotone ki-graph-up fs-2x">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
              </i>
            </div>
            <span class="badge <?php echo (float)$summary['profit'] >= 0 ? 'badge-light-success' : 'badge-light-danger'; ?>">Marj <?php echo $formatPercent($overallMarginRate); ?></span>
          </div>
          <div>
            <div class="text-muted fs-7 text-uppercase mb-2">Net Kâr</div>
            <div class="fs-2hx fw-bold <?php echo $profitClass; ?>"><?php echo $formatMoney($summary['profit']); ?></div>
          </div>
          <div class="text-muted fs-4">Toplam maliyet: <?php echo $formatMoney($summary['total_cost_tl']); ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-flush shadow-sm dashboard-card mb-8">
    <div class="card-body">
      <div class="filter-shell">

        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4 mb-6">
          <div>
            <div class="fw-bold fs-4 mb-1">Filtreler</div>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <span class="performance-pill"><?php echo count($activeFilterBadges); ?> aktif parametre</span>
            <span class="performance-pill"><?php echo html_escape($statusPreview); ?></span>
          </div>
        </div>

        <form method="get" class="row g-5 align-items-end">
          <div class="col-md-6 col-xl-2">
            <label class="form-label fw-semibold fs-7 text-muted">Başlangıç</label>
            <input type="date" name="start_date" class="form-control form-control-solid" value="<?php echo html_escape($startDateValue); ?>">
          </div>

          <div class="col-md-6 col-xl-2">
            <label class="form-label fw-semibold fs-7 text-muted">Bitiş</label>
            <input type="date" name="end_date" class="form-control form-control-solid" value="<?php echo html_escape($endDateValue); ?>">
          </div>

          <div class="col-md-6 col-xl-2">
            <label class="form-label fw-semibold fs-7 text-muted">Site</label>
            <select class="form-select form-select-solid" name="site_id" data-report-select data-placeholder="Tüm siteler">
              <option value=""></option>
              <?php foreach ($sites as $id => $name): ?>
                <option value="<?php echo $id; ?>" <?php echo ((int)($filters['site_id'] ?? 0) === (int)$id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6 col-xl-6">
            <label class="form-label fw-semibold fs-7 text-muted">Durum</label>
            <select name="status[]" class="form-select form-select-solid" multiple data-report-select data-placeholder="Durum seçin">
              <?php foreach ($statusMap as $statusId => $label): ?>
                <option value="<?php echo (int)$statusId; ?>" <?php echo in_array((int)$statusId, $selectedStatuses, true) ? 'selected' : ''; ?>>
                  <?php echo (int)$statusId; ?> - <?php echo html_escape($label); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6 col-xl-3">
            <label class="form-label fw-semibold fs-7 text-muted">Bayiler</label>
            <select class="form-select form-select-solid" name="reseller_id[]" data-report-select data-placeholder="Tüm bayiler" multiple>
              <option value=""></option>
              <?php foreach ($resellers as $reseller): ?>
                <option value="<?php echo (int)$reseller['user_id']; ?>"
                  <?php echo in_array((int)$reseller['user_id'], (array)($filters['reseller_id'] ?? []), true) ? 'selected' : ''; ?>>
                  <?php echo html_escape($reseller['user_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6 col-xl-3">
            <label class="form-label fw-semibold fs-7 text-muted">Tedarikçi</label>
            <select class="form-select form-select-solid" name="supplier_id" data-report-select data-placeholder="Tüm tedarikçiler">
              <option value=""></option>
              <?php foreach ($suppliers as $supplier): ?>
                <option value="<?php echo (int)$supplier['user_id']; ?>" <?php echo ((int)($filters['supplier_id'] ?? 0) === (int)$supplier['user_id']) ? 'selected' : ''; ?>>
                  <?php echo html_escape($supplier['user_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6 col-xl-3">
            <label class="form-label fw-semibold fs-7 text-muted">Kategori</label>
            <select class="form-select form-select-solid" name="category_id" data-report-select data-placeholder="Tüm kategoriler">
              <option value=""></option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo (int)$category['category_id']; ?>" <?php echo ((int)($filters['category_id'] ?? 0) === (int)$category['category_id']) ? 'selected' : ''; ?>>
                  <?php echo html_escape($category['category_title']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6 col-xl-3">
            <label class="form-label fw-semibold fs-7 text-muted">Ürün</label>
            <select class="form-select form-select-solid" name="product_id" data-report-select data-placeholder="Tüm ürünler">
              <option value=""></option>
              <?php foreach ($reportedProducts as $reportedProduct): ?>
                <option value="<?php echo (int)$reportedProduct['order_product_id']; ?>" <?php echo ((int)($filters['product_id'] ?? 0) === (int)$reportedProduct['order_product_id']) ? 'selected' : ''; ?>>
                  <?php echo html_escape($reportedProduct['product_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 d-flex flex-wrap justify-content-end gap-3 pt-2">
            <a class="btn btn-light" href="<?php echo current_url(); ?>">
              <i class="ki-duotone ki-arrows-circle fs-5 me-1">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
              </i>
              Sıfırla
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="ki-duotone ki-filter fs-5 me-1">
                <span class="path1"></span><span class="path2"></span>
              </i>
              Görünümü Uygula
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="card card-flush shadow-sm dashboard-card mb-8 table-card">
    <div class="card-header border-0 pt-6 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-4">
      <div class="card-title flex-column align-items-start">
        <h3 class="fw-bold m-0">Başarılı Sipariş Ürün Tablosu</h3>
        <span class="text-muted fs-7">Detay görünüm ürün bazında sipariş, hacim, maliyet, satış ve marjı birlikte okutur.</span>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge badge-light-primary px-4 py-3"><span id="reports-table-count"><?php echo $formatNumber($productCount); ?></span> görünür kayıt</span>
        <span class="badge badge-light-success px-4 py-3"><?php echo $formatMoney($tableTotals['total_sale']); ?> başarılı ciro</span>
      </div>
    </div>

    <div class="card-body pt-2">
      <?php if (!$successInSelection): ?>
        <div class="alert alert-light-warning d-flex align-items-center mb-6 py-4 px-5">
          <i class="ki-duotone ki-information-5 fs-2hx me-4 text-warning">
            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
          </i>
          <div>
            <div class="fw-semibold">Durum filtresi başarıyı içermediği için tablo boş kalabilir.</div>
            <div class="text-muted fs-7">Tam ürün listesini görmek için durumlar içinde 1 - Başarılı seçimini aktif tut.</div>
          </div>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-4 mb-6">
        <div class="position-relative table-toolbar-input flex-grow-1">
          <i class="ki-duotone ki-magnifier fs-2 text-muted position-absolute top-50 translate-middle-y ms-5">
            <span class="path1"></span><span class="path2"></span>
          </i>
          <input type="text" id="reports-table-search" class="form-control form-control-solid ps-13" placeholder="Ürün adı, kategori veya ID ara">
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3 justify-content-lg-end">
          <div class="w-125px">
            <select id="reports-table-length" class="form-select form-select-solid">
              <option value="10">10 satır</option>
              <option value="25" selected>25 satır</option>
              <option value="50">50 satır</option>
              <option value="100">100 satır</option>
            </select>
          </div>
          <div id="reports-table-actions" class="table-toolbar-actions d-flex flex-wrap gap-2"></div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table align-middle table-row-dashed gy-5 reports-table">
          <thead>
            <tr>
              <th class="w-70px">Sıra</th>
              <th>Ürün</th>
              <th>Kategori</th>
              <th class="text-end">Hacim</th>
              <th class="text-end">Maliyet</th>
              <th class="text-end">Satış</th>
              <th class="text-end">Kârlılık</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($displayRows)): ?>
              <tr>
                <td colspan="7" class="table-empty text-center">
                  <div class="table-empty-icon">
                    <i class="ki-duotone ki-chart-line-star fs-2x">
                      <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                  </div>
                  <div class="fw-semibold mb-1">Gösterilecek ürün kaydı bulunamadı</div>
                  <div class="text-muted fs-7">Filtreleri genişlet veya durum listesine başarılı sipariş durumunu dahil et.</div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($displayRows as $row): ?>
                <?php $rowProfitClass = $row['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>
                <?php $rowExportProduct = $row['product_name'] . '#' . (int)$row['order_product_id']; ?>
                <tr>
                  <td data-order="<?php echo (int)$row['rank']; ?>">
                    <span class="rank-badge"><?php echo (int)$row['rank']; ?></span>
                  </td>

                  <td class="product-cell" data-order="<?php echo (int)$row['order_product_id']; ?>" data-export="<?php echo html_escape($rowExportProduct); ?>">
                    <div class="d-flex flex-column gap-2">
                      <div class="fw-bold product-name" title="<?php echo html_escape($row['product_name']); ?>"><?php echo html_escape($row['product_name']); ?></div>
                      <div class="d-flex flex-wrap gap-2 text-muted fs-8">
                        <span class="badge badge-light">ID: <?php echo (int)$row['order_product_id']; ?></span>
                      </div>
                    </div>
                  </td>

                  <td data-order="<?php echo html_escape($row['category_title']); ?>">
                    <div class="d-flex flex-column align-items-start gap-2">
                      <span class="category-badge"><?php echo html_escape($row['category_title']); ?></span>
                      <span class="text-muted fs-6"><?php echo $formatPercent($row['sale_share']); ?> ciro payı</span>
                    </div>
                  </td>

                  <td class="text-end metric-stack" data-order="<?php echo (int)$row['order_count']; ?>" data-export="<?php echo (int)$row['total_qty']; ?>">
                    <div class="fw-bold fs-4"><?php echo $formatNumber($row['order_count']); ?></div>
                    <div class="text-muted fs-6"><?php echo $formatNumber($row['total_qty']); ?> adet</div>
                  </td>

                  <td class="text-end metric-stack" data-order="<?php echo $row['total_cost_tl']; ?>" data-export="<?php echo number_format((float)$row['total_cost_tl'], 2, '.', ''); ?>">
                    <div class="fw-bold"><?php echo $formatMoney($row['total_cost_tl']); ?></div>
                    <div class="text-muted fs-6">Birim: <?php echo $formatMoney($row['unit_cost']); ?></div>
                  </td>

                  <td class="text-end metric-stack" data-order="<?php echo $row['total_sale']; ?>" data-export="<?php echo number_format((float)$row['total_sale'], 2, '.', ''); ?>">
                    <div class="fw-bold"><?php echo $formatMoney($row['total_sale']); ?></div>
                    <div class="text-muted fs-6">Birim: <?php echo $formatMoney($row['unit_sale']); ?></div>
                  </td>

                  <td class="text-end metric-stack" data-order="<?php echo $row['profit']; ?>" data-export="<?php echo number_format((float)$row['profit'], 2, '.', ''); ?>">
                    <div class="fw-bold <?php echo $rowProfitClass; ?>"><?php echo $formatMoney($row['profit']); ?></div>
                    <div class="text-muted fs-6">Marj: <?php echo $formatPercent($row['margin_rate']); ?></div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
          <?php if (!empty($displayRows)): ?>
            <tfoot>
              <tr class="fw-bold text-end">
                <td></td>
                <td class="text-start">Toplam Sipariş: <?php echo $formatNumber($tableTotals['order_count']); ?></td>
                <td></td>
                <td class="text-end"><?php echo $formatNumber($tableTotals['total_qty']); ?></td>
                <td class="text-end"><?php echo $formatMoney($tableTotals['total_cost_tl']); ?></td>
                <td class="text-end"><?php echo $formatMoney($tableTotals['total_sale']); ?></td>
                <td class="text-end <?php echo $tableTotals['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>"><?php echo $formatMoney($tableTotals['profit']); ?></td>
              </tr>
            </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>

  <div class="card card-flush shadow-sm dashboard-card dashboard-hero mb-8">
    <div class="card-body p-0">
      <div class="row g-0">
        <div class="col-12 col-xl-7">
          <div class="p-8 p-xl-10 h-100 d-flex flex-column justify-content-between gap-6">
            <div>
              <div class="d-flex flex-wrap align-items-center gap-3 mb-5">
                <span class="badge badge-light-primary px-4 py-3">Rapor Merkezi</span>
                <span class="badge badge-light px-4 py-3"><?php echo html_escape($reportRangeLabel); ?></span>
                <span class="badge badge-light-success px-4 py-3"><?php echo $formatNumber($productCount); ?> ürün satırı</span>
              </div>

              <h1 class="fs-2hx fw-bold mb-4">Gelir ve Ürün Performans Görünümü</h1>
            </div>

            <div class="d-flex flex-wrap gap-3">
              <?php foreach ($activeFilterBadges as $badge): ?>
                <span class="hero-chip">
                  <span class="hero-chip-label"><?php echo html_escape($badge['label']); ?></span>
                  <span class="hero-chip-value"><?php echo html_escape($badge['value']); ?></span>
                </span>
              <?php endforeach; ?>
            </div>

            <?php if (!$successInSelection): ?>
              <div class="alert alert-warning d-flex align-items-center mb-0 py-4 px-5">
                <i class="ki-duotone ki-information-5 fs-2hx me-4 text-warning">
                  <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
                <div>
                  <div class="fw-semibold">Ürün tablosu yalnızca başarılı siparişlerden beslenir.</div>
                  <div class="text-muted fs-7">Durum filtresinde 1 - Başarılı seçili değilse aşağıdaki ürün tablosu boş görünebilir.</div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-12 col-xl-5 hero-divider">
          <div class="p-8 p-xl-10 h-100 d-flex flex-column justify-content-between gap-5">
            <div class="row g-4">
              <div class="col-sm-6">
                <div class="mini-metric">
                  <div class="mini-metric-label">Ortalama Sipariş</div>
                  <div class="mini-metric-value"><?php echo $formatMoney($averageOrderValue); ?></div>
                  <div class="text-muted fs-8 mt-2">Seçili toplam satış / sipariş</div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="mini-metric">
                  <div class="mini-metric-label">Birim Gelir</div>
                  <div class="mini-metric-value"><?php echo $formatMoney($averageUnitRevenue); ?></div>
                  <div class="text-muted fs-8 mt-2">Adet başına ortalama satış</div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="mini-metric">
                  <div class="mini-metric-label">Başarılı Sipariş Payı</div>
                  <div class="mini-metric-value"><?php echo $formatPercent($successfulCoverageRate); ?></div>
                  <div class="text-muted fs-8 mt-2">Ürün tablosunun sipariş kapsaması</div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="mini-metric">
                  <div class="mini-metric-label">Ürün Başına Sipariş</div>
                  <div class="mini-metric-value"><?php echo $formatNumber($ordersPerProduct, 1); ?></div>
                  <div class="text-muted fs-8 mt-2">Başarılı ürün yoğunluğu</div>
                </div>
              </div>
            </div>

            <div class="row g-4">
              <div class="col-md-6">
                <div class="insight-list-item h-100">
                  <div class="text-muted fs-8 text-uppercase mb-2">Lider Ürün</div>
                  <div class="fw-bold mb-1 product-name" title="<?php echo html_escape($bestProduct['product_name'] ?? 'Henüz veri yok'); ?>">
                    <?php echo html_escape($bestProduct['product_name'] ?? 'Henüz veri yok'); ?>
                  </div>
                  <div class="text-muted fs-8">
                    <?php echo $bestProduct ? $formatMoney($bestProduct['total_sale']) . ' • ' . $formatPercent($bestProduct['sale_share']) . ' pay' : 'Gösterilecek veri yok'; ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="insight-list-item h-100">
                  <div class="text-muted fs-8 text-uppercase mb-2">Lider Kategori</div>
                  <div class="fw-bold mb-1 product-name" title="<?php echo html_escape($dominantCategory['category_title'] ?? 'Henüz veri yok'); ?>">
                    <?php echo html_escape($dominantCategory['category_title'] ?? 'Henüz veri yok'); ?>
                  </div>
                  <div class="text-muted fs-8">
                    <?php echo $dominantCategory ? $formatMoney($dominantCategory['total_sale']) . ' • ' . $formatPercent($dominantCategory['sale_share']) . ' pay' : 'Gösterilecek veri yok'; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-6 mb-12">
    <div class="col-xl-4">
      <div class="card card-flush shadow-sm dashboard-card insight-card h-100">
        <div class="card-header border-0 pt-6">
          <div class="card-title flex-column align-items-start">
            <h3 class="fw-bold m-0">Operasyon Özeti</h3>
            <span class="text-muted fs-7">Genel görünüm ve başarılı ürün tablosunun birlikte okunması için kısa metrikler</span>
          </div>
        </div>
        <div class="card-body pt-2">
          <div class="insight-list-item">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold">Başarılı sipariş marjı</span>
              <span class="fw-bold <?php echo $tableTotals['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>"><?php echo $formatPercent($successfulMarginRate); ?></span>
            </div>
            <div class="progress-soft">
              <span style="width: <?php echo $capPercent(abs($successfulMarginRate)); ?>%"></span>
            </div>
          </div>

          <div class="insight-list-item">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold">Başarılı ürün cirosu</span>
              <span class="fw-bold"><?php echo $formatMoney($tableTotals['total_sale']); ?></span>
            </div>
            <div class="text-muted fs-7">Tablodaki ürün satırlarının toplam satış değeri</div>
          </div>

          <div class="insight-list-item">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold">Başarılı ürün kârı</span>
              <span class="fw-bold <?php echo $tableTotals['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>"><?php echo $formatMoney($tableTotals['profit']); ?></span>
            </div>
            <div class="text-muted fs-7">Ürün bazlı tabloda görünen kârlılık toplamı</div>
          </div>

          <div class="insight-list-item">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold">Sipariş yoğunluğu</span>
              <span class="fw-bold"><?php echo $formatNumber($ordersPerProduct, 1); ?></span>
            </div>
            <div class="text-muted fs-7">Başarılı sipariş / ürün satırı oranı</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card card-flush shadow-sm dashboard-card insight-card h-100">
        <div class="card-header border-0 pt-6">
          <div class="card-title flex-column align-items-start">
            <h3 class="fw-bold m-0">Top Ürünler</h3>
            <span class="text-muted fs-7">Başarılı ürün satırlarında ciroya göre öne çıkan ilk 5 ürün</span>
          </div>
        </div>
        <div class="card-body pt-2">
          <?php if (empty($topProducts)): ?>
            <div class="table-empty text-center text-muted">
              <div class="table-empty-icon">
                <i class="ki-duotone ki-chart-pie-3 fs-2x">
                  <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
              </div>
              Ürün karşılaştırması için yeterli veri bulunamadı.
            </div>
          <?php else: ?>
            <?php foreach ($topProducts as $topProduct): ?>
              <div class="rank-item">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                  <div class="d-flex align-items-start gap-3 min-w-0">
                    <span class="rank-badge"><?php echo (int)$topProduct['rank']; ?></span>
                    <div class="min-w-0">
                      <div class="fw-bold product-name" title="<?php echo html_escape($topProduct['product_name']); ?>"><?php echo html_escape($topProduct['product_name']); ?></div>
                      <div class="text-muted fs-8">ID: <?php echo (int)$topProduct['order_product_id']; ?><?php echo $topProduct['order_external_product_id'] !== '' ? ' • Ext: ' . html_escape($topProduct['order_external_product_id']) : ''; ?></div>
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="fw-bold"><?php echo $formatMoney($topProduct['total_sale']); ?></div>
                    <div class="text-muted fs-8"><?php echo $formatPercent($topProduct['sale_share']); ?> pay</div>
                  </div>
                </div>
                <div class="d-flex justify-content-between text-muted fs-8 mb-2">
                  <span><?php echo html_escape($topProduct['category_title']); ?></span>
                  <span><?php echo $formatNumber($topProduct['order_count']); ?> sipariş</span>
                </div>
                <div class="progress-soft">
                  <span style="width: <?php echo $capPercent($topProduct['order_heat']); ?>%"></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card card-flush shadow-sm dashboard-card insight-card h-100">
        <div class="card-header border-0 pt-6">
          <div class="card-title flex-column align-items-start">
            <h3 class="fw-bold m-0">Kategori Yoğunluğu</h3>
            <span class="text-muted fs-7">Başarılı ciro içindeki kategori dağılımı</span>
          </div>
        </div>
        <div class="card-body pt-2">
          <?php if (empty($topCategories)): ?>
            <div class="table-empty text-center text-muted">
              <div class="table-empty-icon">
                <i class="ki-duotone ki-element-11 fs-2x">
                  <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                </i>
              </div>
              Kategori kırılımı oluşturulamadı.
            </div>
          <?php else: ?>
            <?php foreach ($topCategories as $topCategory): ?>
              <div class="rank-item">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                  <div class="min-w-0">
                    <div class="fw-bold product-name" title="<?php echo html_escape($topCategory['category_title']); ?>"><?php echo html_escape($topCategory['category_title']); ?></div>
                    <div class="text-muted fs-8"><?php echo $formatNumber($topCategory['order_count']); ?> sipariş • <?php echo $formatNumber($topCategory['total_qty']); ?> adet</div>
                  </div>
                  <div class="text-end">
                    <div class="fw-bold"><?php echo $formatMoney($topCategory['total_sale']); ?></div>
                    <div class="text-muted fs-8"><?php echo $formatPercent($topCategory['sale_share']); ?> pay</div>
                  </div>
                </div>
                <div class="progress-soft">
                  <span style="width: <?php echo $capPercent($topCategory['sale_share']); ?>%"></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $(function() {
    if ($.fn.select2) {
      $('[data-report-select]').each(function() {
        var $select = $(this);
        var isMultiple = $select.prop('multiple');

        $select.select2({
          width: '100%',
          placeholder: $select.data('placeholder') || 'Seçiniz',
          allowClear: true,
          closeOnSelect: !isMultiple,
          // Arama barının her zaman (0 eleman olsa bile) görünmesini zorunlu kılıyoruz
          minimumResultsForSearch: 0,
          // Bootstrap 5 form-select-solid temalarıyla çakışmayı önlemek için select2 container yapısını optimize et
        });
      });
    }

    if (!$.fn.DataTable || !$('.reports-table').length) {
      return;
    }

    var exportColumns = [1, 3, 4, 5, 6];
    var exportTitle = <?php echo json_encode($reportExportTitle, JSON_UNESCAPED_UNICODE); ?>;
    var exportHeaderMap = {
      1: 'Ürün adı#product_id',
      3: 'Toplam Adet',
      4: 'Toplam Maliyet',
      5: 'Toplam Satış F.',
      6: 'Toplam Kâr'
    };

    $.fn.dataTable.ext.errMode = 'throw';
    // ya da alternatif olarak:
    $.fn.dataTable.ext.errMode = 'none'; // Hatayı tamamen sessize alır, alert çıkartmaz.
    
    var normalizeSpaces = function(value) {
      return String(value || '').replace(/\s+/g, ' ').trim();
    };

    var stripHtml = function(value) {
      return normalizeSpaces($('<div>').html(String(value || '')).text());
    };

    var parseIntValue = function(value) {
      var text = stripHtml(value);
      var match = text.match(/-?\d{1,3}(?:\.\d{3})*|-?\d+/);
      if (!match) {
        return 0;
      }
      var normalized = match[0].replace(/\./g, '');
      var parsed = parseInt(normalized, 10);
      return Number.isNaN(parsed) ? 0 : parsed;
    };

    var parseFloatValue = function(value) {
      var text = stripHtml(value).replace(/TL|TRY/gi, ' ');
      var match = text.match(/-?\d{1,3}(?:\.\d{3})*,\d+|-?\d+,\d+|-?\d+(?:\.\d+)?/);
      if (!match) {
        return 0;
      }
      var normalized = match[0].replace(/\./g, '').replace(/,/g, '.');
      var parsed = parseFloat(normalized);
      return Number.isNaN(parsed) ? 0 : parsed;
    };

    var resolveAbsoluteExportColumnIdx = function(columnIdx) {
      if (exportColumns.indexOf(columnIdx) !== -1) {
        return columnIdx;
      }
      if (columnIdx >= 0 && columnIdx < exportColumns.length) {
        return exportColumns[columnIdx];
      }
      return columnIdx;
    };

    var parseQuantityCell = function(cellHtml) {
      return parseIntValue(cellHtml);
    };

    var parseMetricCell = function(cellHtml) {
      return parseFloatValue(cellHtml);
    };

    var parseProductText = function(cellHtml) {
      var wrapper = $('<div>').html(cellHtml || '');
      var productName = normalizeSpaces(wrapper.find('.product-name').first().text());
      var idBadgeText = normalizeSpaces(wrapper.find('.badge').first().text());
      var idMatch = idBadgeText.match(/ID:\s*(\d+)/i);
      var productId = idMatch ? idMatch[1] : '';
      if (!productName) {
        var plainText = stripHtml(cellHtml || '');
        var plainIdMatch = plainText.match(/ID:\s*(\d+)/i);
        if (!productId && plainIdMatch) {
          productId = plainIdMatch[1];
        }
        productName = normalizeSpaces(plainText.replace(/ID:\s*\d+/i, '')) || '-';
      }
      return productId ? (productName + '#' + productId) : productName;
    };

    var exportFormat = {
      header: function(data, columnIdx) {
        var absoluteColumnIdx = resolveAbsoluteExportColumnIdx(columnIdx);
        return exportHeaderMap[absoluteColumnIdx] || normalizeSpaces(data);
      },
      body: function(data, rowIdx, colIdxMaybe, node) {
        var columnIdx = typeof colIdxMaybe === 'number' ? colIdxMaybe : null;
        if (columnIdx === null && node && typeof node.cellIndex === 'number') {
          columnIdx = node.cellIndex;
        }
        var absoluteColumnIdx = resolveAbsoluteExportColumnIdx(columnIdx);
        var nodeExportValue = node ? $(node).attr('data-export') : null;
        if (typeof nodeExportValue !== 'undefined' && nodeExportValue !== null && nodeExportValue !== '') {
          return nodeExportValue;
        }

        if (absoluteColumnIdx === 1) {
          return parseProductText(data);
        }
        if (absoluteColumnIdx === 3) {
          return parseQuantityCell(data);
        }
        if (absoluteColumnIdx === 4 || absoluteColumnIdx === 5 || absoluteColumnIdx === 6) {
          return parseMetricCell(data);
        }
        return stripHtml(data);
      }
    };
    var buttons = [];

    if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.buttons) {
      buttons = [{
          extend: 'excelHtml5',
          text: '<i class="ki-duotone ki-file-up fs-5 me-1"><span class="path1"></span><span class="path2"></span></i>Excel',
          className: 'btn btn-light-success btn-sm',
          exportOptions: {
            columns: exportColumns,
            footer: true,
            format: exportFormat
          },
          title: exportTitle
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="ki-duotone ki-document fs-5 me-1"><span class="path1"></span><span class="path2"></span></i>PDF',
          className: 'btn btn-light-danger btn-sm',
          orientation: 'landscape',
          pageSize: 'A4',
          exportOptions: {
            columns: exportColumns,
            footer: true,
            format: exportFormat
          },
          title: exportTitle,
          customize: function(doc) {
            doc.styles.title.alignment = 'left';
            doc.styles.tableHeader.alignment = 'left';
            doc.styles.tableHeader.fontSize = 10;
            doc.defaultStyle.fontSize = 9;
          }
        },
        {
          extend: 'print',
          text: '<i class="ki-duotone ki-printer fs-5 me-1"><span class="path1"></span><span class="path2"></span></i>Yazdır',
          className: 'btn btn-light-primary btn-sm',
          exportOptions: {
            columns: exportColumns,
            footer: true,
            format: exportFormat
          },
          title: exportTitle
        }
      ];
    }

    var table = $('.reports-table').DataTable({
      order: [
        [3, 'desc']
      ],
      columnDefs: [{
          targets: [0],
          orderable: false
        },
        {
          targets: [3, 4, 5, 6],
          type: 'num'
        }
      ],
      pageLength: 25,
      lengthChange: false,
      stateSave: true,
      autoWidth: false,
      dom: "<'row mb-2'<'col-12'B>>" +
        "<'row'<'col-12'tr>>" +
        "<'row align-items-center mt-4'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
      buttons: buttons,
      language: {
        emptyTable: 'Gösterilecek ürün kaydı bulunamadı',
        zeroRecords: 'Aramaya uyan kayıt bulunamadı',
        info: '_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor',
        infoEmpty: 'Kayıt yok',
        infoFiltered: '(_MAX_ kayıt içinden filtrelendi)',
        paginate: {
          first: 'İlk',
          previous: 'Önceki',
          next: 'Sonraki',
          last: 'Son'
        }
      }
    });

    if (buttons.length && typeof table.buttons === 'function') {
      table.buttons().container().appendTo('#reports-table-actions');
    } else {
      $('#reports-table-actions').remove();
    }

    $('#reports-table-search').on('input', function() {
      table.search(this.value).draw();
    });

    $('#reports-table-length').on('change', function() {
      var length = parseInt(this.value, 10) || 25;
      table.page.len(length).draw();
    });

    $('#reports-table-length').val(String(table.page.len()));

    var syncVisibleCount = function() {
      var info = table.page.info();
      $('#reports-table-count').text(info.recordsDisplay);
    };

    syncVisibleCount();
    table.on('draw', syncVisibleCount);
  });
</script>