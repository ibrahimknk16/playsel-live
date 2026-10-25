<?php
$kpis = $userKpis ?? [];
$totals = $kpis['totals'] ?? [];
$periodsData = $kpis['periods'] ?? [];
$money = static function ($value) {
	return number_format((float)$value, 2, ',', '.') . ' TL';
};
$number = static function ($value) {
	return number_format((int)$value, 0, ',', '.');
};
$escape = static function ($value) {
	return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
};
$periodLabels = ['daily' => 'Günlük', 'weekly' => 'Haftalık', 'monthly' => 'Aylık'];
$periods = $periods ?? [];
$activeCustomerWindowDays = (int)($activeCustomerWindowDays ?? 30);
$monthLabels = [1 => 'Oca', 2 => 'Şub', 3 => 'Mar', 4 => 'Nis', 5 => 'May', 6 => 'Haz', 7 => 'Tem', 8 => 'Ağu', 9 => 'Eyl', 10 => 'Eki', 11 => 'Kas', 12 => 'Ara'];
$monthlyTrendLabels = [];
$monthlyTrendValues = [];
foreach (($kpis['monthly_trend'] ?? []) as $trendRow) {
	$trendDate = strtotime($trendRow['period_key'] . '-01');
	$monthlyTrendLabels[] = ($monthLabels[(int)date('n', $trendDate)] ?? date('m', $trendDate)) . ' ' . date('Y', $trendDate);
	$monthlyTrendValues[] = (int)($trendRow['registrations'] ?? 0);
}
$monthlyTrendLabelsJson = json_encode($monthlyTrendLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$monthlyTrendValuesJson = json_encode($monthlyTrendValues, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$registrationTrendLabels = [];
$registrationTrendValues = [];
foreach (($kpis['registration_trend'] ?? []) as $trendRow) {
	$registrationTrendDate = strtotime($trendRow['period_key']);
	$registrationTrendLabels[] = date('d.m', $registrationTrendDate);
	$registrationTrendValues[] = (int)($trendRow['registrations'] ?? 0);
}
$registrationTrendLabelsJson = json_encode($registrationTrendLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$registrationTrendValuesJson = json_encode($registrationTrendValues, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>

<style>
	.user-kpi-shell { color: #e5e7eb; font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif; }
	.user-kpi-header { display:flex; justify-content:space-between; gap:1rem; align-items:flex-end; margin-bottom:1.2rem; }
	.user-kpi-header h1 { margin:0; color:#f8fafc; font-size:2rem; font-weight:800; }
	.user-kpi-header p { margin:.45rem 0 0; color:#94a3b8; }
	.user-kpi-refresh { color:#bae6fd; font-size:.85rem; white-space:nowrap; }
	.user-kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.9rem; margin-bottom:1rem; }
	.user-kpi-card, .user-kpi-panel { background:rgba(255,255,255,.02); border:1px solid rgba(148,163,184,.16); border-radius:16px; padding:1.1rem; }
	.user-kpi-card small { color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; font-size:.72rem; font-weight:700; }
	.user-kpi-card strong { display:block; margin-top:.5rem; color:#f8fafc; font-size:1.55rem; }
	.user-kpi-card span { display:block; margin-top:.35rem; color:#64748b; font-size:.8rem; }
	.user-kpi-layout { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(280px,.75fr); gap:1rem; margin-bottom:1rem; }
	.user-kpi-panel h2 { margin:0 0 .25rem; color:#f8fafc; font-size:1.15rem; font-weight:800; }
	.user-kpi-panel > p { margin:0 0 1rem; color:#94a3b8; font-size:.88rem; }
	.period-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.8rem; }
	.period-card { border:1px solid rgba(148,163,184,.13); border-radius:13px; padding:.9rem; }
	.period-card h3 { margin:0; color:#7dd3fc; font-size:1rem; }
	.period-card em { color:#94a3b8; font-size:.78rem; font-style:normal; }
	.period-metrics { display:grid; grid-template-columns:repeat(2,1fr); gap:.55rem; margin-top:.8rem; }
	.period-metrics div { background:rgba(255,255,255,.025); border-radius:9px; padding:.55rem; }
	.period-metrics small { display:block; color:#94a3b8; font-size:.7rem; }
	.period-metrics b { display:block; margin-top:.2rem; color:#f8fafc; font-size:1rem; }
	.kpi-table { width:100%; border-collapse:collapse; font-size:.84rem; }
	.kpi-table th { color:#94a3b8; text-align:left; font-weight:700; font-size:.72rem; text-transform:uppercase; }
	.kpi-table th, .kpi-table td { padding:.58rem .45rem; border-bottom:1px solid rgba(148,163,184,.1); }
	.kpi-table td { color:#dbe4f0; }
	.kpi-table td:last-child, .kpi-table th:last-child { text-align:right; }
	.user-rank { display:inline-flex; width:1.5rem; height:1.5rem; align-items:center; justify-content:center; border-radius:50%; background:rgba(56,189,248,.13); color:#7dd3fc; font-size:.72rem; font-weight:800; }
	.kpi-trend { display:grid; grid-template-columns:repeat(15,minmax(24px,1fr)); align-items:end; gap:.35rem; min-height:150px; padding-top:1rem; }
	.kpi-bar-wrap { height:130px; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; gap:.35rem; }
	.kpi-bar { width:100%; min-height:2px; border-radius:5px 5px 0 0; background:linear-gradient(180deg,#38bdf8,#6366f1); }
	.kpi-bar-label { color:#64748b; font-size:.64rem; writing-mode:vertical-rl; transform:rotate(180deg); }
	.user-kpi-chart-wrap { position:relative; min-height:320px; background:rgba(255,255,255,.01); border:1px solid rgba(148,163,184,.12); border-radius:14px; padding:.85rem; }
	@media (max-width:1100px) { .user-kpi-grid { grid-template-columns:repeat(2,1fr); } .user-kpi-layout { grid-template-columns:1fr; } }
	@media (max-width:700px) { .user-kpi-header { display:block; } .user-kpi-grid, .period-grid { grid-template-columns:1fr; } }
</style>

<div class="user-kpi-shell">
	<div class="user-kpi-header">
		<div>
			<h1>Kullanıcı KPI Merkezi</h1>
			<p>Kullanıcı kayıt, aktivite, alışveriş ve bakiye göstergelerini tek ekranda izleyin.</p>
		</div>
		<div class="user-kpi-refresh">Veri zamanı: <?= $escape(date('d.m.Y H:i')) ?></div>
	</div>

	<div class="user-kpi-grid">
		<div class="user-kpi-card"><small>Toplam kullanıcı</small><strong><?= $number($totals['total_users'] ?? 0) ?></strong></div>
		<div class="user-kpi-card"><small>Toplam kullanıcı bakiyesi</small><strong><?= $money($totals['total_balance'] ?? 0) ?></strong><span>Onaylı bakiye hareketleri</span></div>
		<div class="user-kpi-card"><small>Aktiflik oranı</small><strong><?= number_format(($totals['total_users'] ?? 0) > 0 ? (($totals['active_users'] ?? 0) / $totals['total_users']) * 100 : 0, 2, ',', '.') ?>%</strong><span>Son <?= $activeCustomerWindowDays ?> günde son tamamlanan siparişi olan müşteriler</span></div>
		<div class="user-kpi-card"><small>Bu ay yeni kayıt</small><strong><?= $number($periodsData['monthly']['registrations'] ?? 0) ?></strong><span>Ay başından bugüne</span></div>
	</div>

	<div class="user-kpi-layout">
		<section class="user-kpi-panel">
			<h2>Dönem karşılaştırması</h2>
			<p>Tamamlanan alışverişler ve yeni kullanıcı kayıtları.</p>
			<div class="period-grid">
				<?php foreach ($periodLabels as $key => $label):
					$period = $periodsData[$key] ?? [];
				?>
					<div class="period-card">
						<h3><?= $label ?></h3><em><?= $escape($periods[$key]['subtitle'] ?? '') ?></em>
						<div class="period-metrics">
							<div><small>Yeni kayıt</small><b><?= $number($period['registrations'] ?? 0) ?></b></div>
							<div><small>Aktif alışverişçi</small><b><?= $number($period['active_users'] ?? 0) ?></b></div>
							<div><small>Sipariş</small><b><?= $number($period['orders'] ?? 0) ?></b></div>
							<div><small>Toplam tutar</small><b><?= $money($period['amount'] ?? 0) ?></b></div>
						</div>
						<div style="margin-top:.65rem;color:#94a3b8;font-size:.78rem;">Ortalama sepet: <b style="color:#f8fafc;"><?= $money($period['average_order'] ?? 0) ?></b></div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	</div>

	<?php foreach ($periodLabels as $key => $label): $period = $periodsData[$key] ?? []; ?>
		<section class="user-kpi-panel" style="margin-bottom:1rem;">
			<h2><?= $label ?> en aktif kullanıcılar</h2>
			<p>Sipariş adedine ve toplam alışveriş tutarına göre ilk 10 kullanıcı.</p>
			<div class="user-kpi-layout" style="margin:0;">
				<div>
					<table class="kpi-table"><thead><tr><th>#</th><th>Kullanıcı</th><th>Sipariş</th></tr></thead><tbody>
						<?php foreach (($period['top_orders'] ?? []) as $index => $user): ?>
							<tr><td><span class="user-rank"><?= $index + 1 ?></span></td><td><?= $escape(trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''))) ?><br><small style="color:#64748b;"><?= $escape($user['user_email'] ?? '') ?></small></td><td><?= $number($user['orders'] ?? 0) ?></td></tr>
						<?php endforeach; ?>
						<?php if (empty($period['top_orders'])): ?><tr><td colspan="3">Bu dönemde tamamlanan alışveriş yok.</td></tr><?php endif; ?>
					</tbody></table>
				</div>
				<div>
					<table class="kpi-table"><thead><tr><th>#</th><th>Kullanıcı</th><th>Tutar</th></tr></thead><tbody>
						<?php foreach (($period['top_amount'] ?? []) as $index => $user): ?>
							<tr><td><span class="user-rank"><?= $index + 1 ?></span></td><td><?= $escape(trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''))) ?><br><small style="color:#64748b;"><?= $escape($user['user_email'] ?? '') ?></small></td><td><?= $money($user['amount'] ?? 0) ?></td></tr>
						<?php endforeach; ?>
						<?php if (empty($period['top_amount'])): ?><tr><td colspan="3">Bu dönemde tamamlanan alışveriş yok.</td></tr><?php endif; ?>
					</tbody></table>
				</div>
			</div>
		</section>
	<?php endforeach; ?>

	<section class="user-kpi-panel">
		<h2>Aktif kullanıcı trendi</h2>
		<p>Son <?= $activeCustomerWindowDays ?> günde tamamlanan siparişi olan benzersiz bireysel müşteriler.</p>
		<?php
		$dailyTrend = $kpis['daily_trend'] ?? [];
		$dailyMap = [];
		$maxDaily = 1;
		foreach ($dailyTrend as $row) { $dailyMap[$row['period_key']] = $row; $maxDaily = max($maxDaily, (int)$row['active_users']); }
		?>
		<div class="kpi-trend">
			<?php for ($i = 29; $i >= 0; $i--):
				$day = date('Y-m-d', strtotime('-' . $i . ' days'));
				$row = $dailyMap[$day] ?? ['active_users' => 0];
			?>
				<div class="kpi-bar-wrap" title="<?= $escape($day) ?>: <?= $number($row['active_users']) ?> aktif kullanıcı">
					<div class="kpi-bar" style="height:<?= max(2, ((int)$row['active_users'] / $maxDaily) * 100) ?>%;"></div>
					<span class="kpi-bar-label"><?= $escape(date('d.m', strtotime($day))) ?></span>
				</div>
			<?php endfor; ?>
		</div>
	</section>
	<section class="user-kpi-panel" style="margin-top:1rem;">
		<h2>Aylık kayıt trendi</h2>
		<p>Son 6 ayda platforma katılan kullanıcı sayısı.</p>
		<div class="user-kpi-chart-wrap">
			<canvas id="monthlyRegistrationsChart"></canvas>
		</div>
	</section>
	<section class="user-kpi-panel" style="margin-top:1rem;">
		<h2>Son 30 gün kayıt trendi</h2>
		<p>Son 30 takvim gününde kayıt olan tüm kullanıcıların günlük dağılımı.</p>
		<div class="user-kpi-chart-wrap">
			<canvas id="dailyRegistrationsChart"></canvas>
		</div>
	</section>
</div>

<script src="<?= base_url('assets-front/js/chart.min.js') ?>"></script>
<script>
	(function () {
		function renderMonthlyRegistrationsChart() {
			var canvas = document.getElementById('monthlyRegistrationsChart');
			if (!canvas || typeof Chart === 'undefined') {
				return;
			}

			function renderDailyRegistrationsChart() {
				var canvas = document.getElementById('dailyRegistrationsChart');
				if (!canvas || typeof Chart === 'undefined') {
					return;
				}

				new Chart(canvas.getContext('2d'), {
					type: 'bar',
					data: {
						labels: <?= $registrationTrendLabelsJson ?: '[]' ?>,
						datasets: [{
							label: 'Yeni kayıt',
							data: <?= $registrationTrendValuesJson ?: '[]' ?>,
							backgroundColor: '#818cf8',
							borderColor: '#a5b4fc',
							borderWidth: 1,
							borderRadius: 8,
							maxBarThickness: 64
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							legend: { display: false },
							tooltip: {
								callbacks: {
									label: function (context) {
										return ' ' + new Intl.NumberFormat('tr-TR').format(context.parsed.y) + ' kullanıcı';
									}
								}
							}
						},
						scales: {
							x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
							y: {
								beginAtZero: true,
								ticks: {
									color: '#94a3b8',
									precision: 0,
									callback: function (value) {
										return new Intl.NumberFormat('tr-TR').format(value);
									}
								},
								grid: { color: 'rgba(148,163,184,.12)' }
							}
						}
					}
				});
			}

			new Chart(canvas.getContext('2d'), {
				type: 'bar',
				data: {
					labels: <?= $monthlyTrendLabelsJson ?: '[]' ?>,
					datasets: [{
						label: 'Yeni kayıt',
						data: <?= $monthlyTrendValuesJson ?: '[]' ?>,
						backgroundColor: '#38bdf8',
						borderColor: '#7dd3fc',
						borderWidth: 1,
						borderRadius: 8,
						maxBarThickness: 42
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: false },
						tooltip: {
							callbacks: {
								label: function (context) {
									return ' ' + new Intl.NumberFormat('tr-TR').format(context.parsed.y) + ' kullanıcı';
								}
							}
						}
					},
					scales: {
						x: {
							grid: { display: false },
							ticks: { color: '#94a3b8' }
						},
						y: {
							beginAtZero: true,
							ticks: {
								color: '#94a3b8',
								precision: 0,
								callback: function (value) {
									return new Intl.NumberFormat('tr-TR').format(value);
								}
							},
							grid: { color: 'rgba(148,163,184,.12)' }
						}
					}
				}
			});
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', renderMonthlyRegistrationsChart);
			document.addEventListener('DOMContentLoaded', renderDailyRegistrationsChart);
		} else {
			renderMonthlyRegistrationsChart();
			renderDailyRegistrationsChart();
		}
	}());
</script>
