<style>
    .category-hour-report .chart-shell {
        position: relative;
        height: 420px;
    }

    .category-hour-report .metric-card {
        border: 1px dashed var(--bs-gray-300);
        border-radius: 0.75rem;
        padding: 1rem;
        background: var(--bs-gray-100);
        height: 100%;
    }

    .category-hour-report .product-stat-item {
        border: 1px solid var(--bs-gray-200);
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
    }

    .category-hour-report .product-hour-card {
        border: 1px solid var(--bs-gray-200);
        border-radius: 0.75rem;
        padding: 0.85rem;
        background: var(--bs-gray-100);
        height: 100%;
    }

    .category-hour-report .product-hour-chart-shell {
        position: relative;
        height: 170px;
    }

    @media (max-width: 768px) {
        .category-hour-report .chart-shell {
            height: 320px;
        }
    }
</style>

<div class="category-hour-report row g-6">
    <div class="col-12">
        <div class="card card-flush shadow-sm">
            <div class="card-header align-items-center">
                <div class="card-title">
                    <h3 class="fw-bold m-0"><?= html_escape($categoryTitle ?? 'Kategori') ?> Saatlik Satış Raporu</h3>
                </div>
            </div>
            <div class="card-body pt-6">
                <form method="post" action="<?= base_url('reports/categoryHourChart/' . (int)$categoryID) ?>" class="row g-4 align-items-end mb-8">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fw-semibold">Başlangıç Tarihi</label>
                        <input type="date" name="startDate" class="form-control form-control-solid" value="<?= html_escape($startDateValue ?? date('Y-m-d')) ?>" max="<?= html_escape($maxDateValue ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fw-semibold">Bitiş Tarihi</label>
                        <input type="date" name="endDate" class="form-control form-control-solid" value="<?= html_escape($endDateValue ?? date('Y-m-d')) ?>" max="<?= html_escape($maxDateValue ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-4 col-lg-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-filter fs-5 me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Filtrele
                        </button>
                    </div>
                    <div class="col-md-4 col-lg-2 d-grid">
                        <a href="<?= base_url('reports/categoryHourChart/' . (int)$categoryID) ?>" class="btn btn-light">Sıfırla</a>
                    </div>
                </form>

                <div class="alert alert-light-info d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-8 py-3">
                    <span class="fw-semibold">Rapor Aralığı: <?= html_escape($reportRangeLabelText ?? '') ?></span>
                    <span class="badge badge-light-primary">24 Saatlik Dağılım</span>
                </div>

                <div class="row g-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card">
                            <div class="text-muted fs-7 mb-1">Toplam Satış</div>
                            <div class="fs-2 fw-bold"><?= html_escape($totalSalesText ?? '0') ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card">
                            <div class="text-muted fs-7 mb-1">En Yoğun Saat</div>
                            <div class="fs-2 fw-bold"><?= html_escape($peakHourText ?? '00:00') ?></div>
                            <div class="text-gray-700 fs-8 mt-1"><?= html_escape($peakSalesText ?? '0') ?> satış</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card">
                            <div class="text-muted fs-7 mb-1">Saatlik Ortalama</div>
                            <div class="fs-2 fw-bold"><?= html_escape($averagePerHourText ?? '0,0') ?></div>
                            <div class="text-gray-700 fs-8 mt-1">24 saat bazında</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card">
                            <div class="text-muted fs-7 mb-1">Aktif Saat Ortalaması</div>
                            <div class="fs-2 fw-bold"><?= html_escape($averageActiveHourText ?? '0,0') ?></div>
                            <div class="text-gray-700 fs-8 mt-1"><?= html_escape($activeHoursText ?? '0') ?> aktif saat</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-flush shadow-sm">
            <div class="card-header align-items-center">
                <div class="card-title">
                    <h4 class="m-0">Saatlik Satış Grafiği</h4>
                </div>
            </div>
            <div class="card-body pt-6">
                <?php if (!($hasSales ?? false)): ?>
                    <div class="alert alert-light-warning mb-6">
                        Seçilen tarih aralığında başarılı sipariş bulunamadı.
                    </div>
                <?php endif; ?>

                <div class="chart-shell">
                    <canvas id="categorySaleChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-flush shadow-sm">
            <div class="card-header align-items-center">
                <div class="card-title">
                    <h4 class="m-0">Ürün - Kategori İstatistikleri</h4>
                </div>
            </div>
            <div class="card-body pt-6">
                <div class="row g-4 mb-8">
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card">
                            <div class="text-muted fs-7 mb-1">Aktif Ürün Sayısı</div>
                            <div class="fs-2 fw-bold"><?= html_escape($totalProductsText ?? '0') ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card">
                            <div class="text-muted fs-7 mb-1">Lider Ürün</div>
                            <div class="fs-6 fw-bold text-truncate" title="<?= html_escape($topProductNameText ?? '-') ?>"><?= html_escape($topProductNameText ?? '-') ?></div>
                            <div class="text-gray-700 fs-8 mt-1"><?= html_escape($topProductSalesText ?? '0') ?> satış</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card">
                            <div class="text-muted fs-7 mb-1">Lider Ürün Payı</div>
                            <div class="fs-2 fw-bold"><?= html_escape($topProductShareText ?? '0,0') ?>%</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card">
                            <div class="text-muted fs-7 mb-1">Top 3 Yoğunlaşma</div>
                            <div class="fs-2 fw-bold"><?= html_escape($top3ShareText ?? '0,0') ?>%</div>
                            <div class="text-gray-700 fs-8 mt-1">İlk 3 ürün satış payı</div>
                        </div>
                    </div>
                </div>

                <?php if (!($hasProductStats ?? false)): ?>
                    <div class="alert alert-light-warning mb-0">
                        Ürün bazlı dağılım hesaplanamadı.
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach (($topProductStatCards ?? []) as $card): ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="product-stat-item h-100">
                                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                                            <span class="badge badge-light-primary">#<?= (int)($card['rank'] ?? 0) ?></span>
                                            <div class="min-w-0">
                                                <div class="fw-semibold text-gray-900 text-truncate" title="<?= html_escape($card['product_name'] ?? '-') ?>"><?= html_escape($card['product_name'] ?? '-') ?></div>
                                                <div class="text-muted fs-8">ID: <?= html_escape($card['product_id_text'] ?? '0') ?></div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-gray-900"><?= html_escape($card['rate_text'] ?? '0,0') ?>%</div>
                                            <div class="text-muted fs-8"><?= html_escape($card['sales_text'] ?? '0') ?> satış</div>
                                        </div>
                                    </div>
                                    <div class="progress h-8px bg-light-primary">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= html_escape($card['rate_width_text'] ?? '0.00') ?>%" aria-valuenow="<?= html_escape($card['rate_width_text'] ?? '0.00') ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-flush shadow-sm">
            <div class="card-header align-items-center">
                <div class="card-title">
                    <h4 class="m-0">Ürün Bazlı Saatlik Raporlar</h4>
                </div>
            </div>
            <div class="card-body pt-6">
                <?php if (!($hasProductHourlyReports ?? false)): ?>
                    <div class="alert alert-light-warning mb-0">
                        Seçilen tarih aralığında ürün bazlı saatlik satış bulunamadı.
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach (($productHourlyChartCards ?? []) as $card): ?>
                            <div class="col-sm-6 col-lg-4 col-xl-4">
                                <div class="product-hour-card">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="min-w-0 me-2">
                                            <div class="fw-semibold text-gray-900 text-truncate" title="<?= html_escape($card['product_name'] ?? '-') ?>">
                                                <?= html_escape($card['product_name'] ?? '-') ?>
                                            </div>
                                            <div class="text-muted fs-8">ID: <?= html_escape($card['product_id_text'] ?? '0') ?></div>
                                        </div>
                                        <span class="badge badge-light-primary"><?= html_escape($card['sales_rate_text'] ?? '0,0') ?>%</span>
                                    </div>

                                    <div class="d-flex justify-content-between text-muted fs-8 mb-3">
                                        <span>Toplam: <span class="text-gray-900 fw-semibold"><?= html_escape($card['total_sales_text'] ?? '0') ?></span></span>
                                        <span>Peak: <span class="text-gray-900 fw-semibold"><?= html_escape($card['peak_hour_text'] ?? '00:00') ?></span></span>
                                    </div>

                                    <div class="product-hour-chart-shell">
                                        <canvas id="<?= html_escape($card['canvas_id'] ?? '') ?>"></canvas>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Chart === 'undefined') {
            return;
        }

        const canvas = document.getElementById('categorySaleChart');
        if (!canvas) {
            return;
        }

        const salesData = <?= $chartDataJson ?? '[]' ?>;
        const labels = <?= $hourLabelsChartJson ?? '[]' ?>;
        const productHourlyCharts = <?= $productHourlyChartsJson ?? '[]' ?>;
        const safeSalesData = Array.isArray(salesData) ? salesData : [];
        const safeLabels = Array.isArray(labels) ? labels : [];
        const safeProductHourlyCharts = Array.isArray(productHourlyCharts) ? productHourlyCharts : [];

        const cssVars = getComputedStyle(document.documentElement);
        const primaryColor = (cssVars.getPropertyValue('--bs-primary') || '#009ef7').trim();
        const successColor = (cssVars.getPropertyValue('--bs-success') || '#50cd89').trim();
        const warningColor = (cssVars.getPropertyValue('--bs-warning') || '#ffc700').trim();
        const gridColor = (cssVars.getPropertyValue('--bs-gray-300') || 'rgba(0,0,0,0.08)').trim();
        const labelColor = (cssVars.getPropertyValue('--bs-gray-700') || '#3f4254').trim();

        const peakValue = safeSalesData.length > 0 ? Math.max(...safeSalesData) : 0;
        const backgroundColors = safeSalesData.map(function(value) {
            if (value === peakValue && peakValue > 0) {
                return warningColor;
            }
            return successColor;
        });

        if (window.categorySaleChartInstance) {
            window.categorySaleChartInstance.destroy();
        }

        const ctx = canvas.getContext('2d');
        window.categorySaleChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: safeLabels,
                datasets: [{
                    label: 'Satış Adedi',
                    data: safeSalesData,
                    backgroundColor: backgroundColors,
                    borderColor: primaryColor,
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: labelColor
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.raw + ' satış';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: labelColor,
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 12
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: labelColor,
                            precision: 0
                        }
                    }
                }
            }
        });

        const productPalette = [
            '#1bc5bd', '#7239ea', '#f64e60', '#0d6efd', '#50cd89',
            '#ffc700', '#20c997', '#fd7e14', '#6610f2', '#198754'
        ];

        if (Array.isArray(window.productHourlyChartInstances)) {
            window.productHourlyChartInstances.forEach(function(instance) {
                if (instance && typeof instance.destroy === 'function') {
                    instance.destroy();
                }
            });
        }
        window.productHourlyChartInstances = [];

        safeProductHourlyCharts.forEach(function(item, idx) {
            const canvasId = item && item.canvas_id ? item.canvas_id : '';
            const productCanvas = canvasId ? document.getElementById(canvasId) : null;
            const productData = Array.isArray(item && item.data) ? item.data : [];
            if (!productCanvas || productData.length === 0) {
                return;
            }

            const color = productPalette[idx % productPalette.length];
            const productCtx = productCanvas.getContext('2d');
            const productChart = new Chart(productCtx, {
                type: 'line',
                data: {
                    labels: safeLabels,
                    datasets: [{
                        label: item.label || 'Ürün',
                        data: productData,
                        borderColor: color,
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 1.5,
                        pointHoverRadius: 3,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.raw + ' satış';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: labelColor,
                                autoSkip: true,
                                maxTicksLimit: 6,
                                maxRotation: 0,
                                minRotation: 0,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: labelColor,
                                precision: 0,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });

            window.productHourlyChartInstances.push(productChart);
        });
    });
</script>