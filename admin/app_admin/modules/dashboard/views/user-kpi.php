<?php
$kpis = $userKpis ?? [];
$acquisitionKpis = $userAcquisitionKpis ?? ['available' => false];
$acquisitionTotals = $acquisitionKpis['totals'] ?? [];
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
	.acquisition-kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.75rem; margin-bottom:1rem; }
	.acquisition-kpi { min-width:0; padding:.8rem; border:1px solid rgba(148,163,184,.13); border-radius:12px; background:rgba(255,255,255,.025); }
	.acquisition-kpi small { display:block; color:#94a3b8; font-size:.68rem; font-weight:700; text-transform:uppercase; }
	.acquisition-kpi strong { display:block; margin-top:.3rem; color:#f8fafc; font-size:1.2rem; }
	.acquisition-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.8rem; }
	.acquisition-table-wrap { min-width:0; overflow-x:auto; }
	.acquisition-table td:first-child { overflow-wrap:anywhere; }
	.user-kpi-card, .user-kpi-panel { background:rgba(255,255,255,.02); border:1px solid rgba(148,163,184,.16); border-radius:16px; padding:1.1rem; }
	.user-kpi-card small { color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; font-size:.72rem; font-weight:700; }
	.user-kpi-card strong { display:block; margin-top:.5rem; color:#f8fafc; font-size:1.55rem; }
	.user-kpi-card span { display:block; margin-top:.35rem; color:#64748b; font-size:.8rem; }
	.user-kpi-panel h2 { margin:0 0 .25rem; color:#f8fafc; font-size:1.15rem; font-weight:800; }
	.user-kpi-panel > p { margin:0 0 1rem; color:#94a3b8; font-size:.88rem; }
	.period-leaderboard { margin-bottom:1rem; }
	.period-leaderboard-header { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; }
	.period-leaderboard-header h2 { margin:0; }
	.period-window { flex:0 0 auto; padding:.38rem .7rem; border:1px solid rgba(125,211,252,.2); border-radius:999px; background:rgba(56,189,248,.08); color:#7dd3fc; font-size:.75rem; font-weight:700; }
	.leaderboard-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
	.leaderboard-card { min-width:0; padding:1rem; border:1px solid rgba(148,163,184,.13); border-radius:14px; background:linear-gradient(145deg,rgba(255,255,255,.035),rgba(255,255,255,.012)); }
	.leaderboard-card-header { display:flex; justify-content:space-between; align-items:flex-start; gap:.75rem; margin-bottom:.8rem; }
	.leaderboard-card-header h3 { margin:0; color:#f8fafc; font-size:1rem; font-weight:750; }
	.leaderboard-card-header p { margin:.25rem 0 0; color:#94a3b8; font-size:.78rem; }
	.leaderboard-icon { display:flex; width:2.2rem; height:2.2rem; flex:0 0 auto; align-items:center; justify-content:center; border-radius:10px; background:rgba(56,189,248,.12); color:#7dd3fc; font-size:.78rem; font-weight:800; }
	.leaderboard-card--amount .leaderboard-icon { background:rgba(52,211,153,.12); color:#6ee7b7; }
	.period-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.8rem; }
	.period-card { border:1px solid rgba(148,163,184,.13); border-radius:13px; padding:.9rem; }
	.period-card h3 { margin:0; color:#7dd3fc; font-size:1rem; }
	.period-card em { color:#94a3b8; font-size:.78rem; font-style:normal; }
	.period-metrics { display:grid; grid-template-columns:repeat(2,1fr); gap:.55rem; margin-top:.8rem; }
	.period-metrics div { background:rgba(255,255,255,.025); border-radius:9px; padding:.55rem; }
	.period-metrics small { display:block; color:#94a3b8; font-size:.7rem; }
	.period-metrics b { display:block; margin-top:.2rem; color:#f8fafc; font-size:1rem; }
	.kpi-table { width:100%; border-collapse:collapse; font-size:.84rem; }
	.kpi-table th { color:#64748b; text-align:left; font-weight:700; font-size:.67rem; text-transform:uppercase; letter-spacing:.05em; }
	.kpi-table th, .kpi-table td { padding:.68rem .45rem; border-bottom:1px solid rgba(148,163,184,.09); }
	.kpi-table tbody tr:last-child td { border-bottom:0; }
	.kpi-table td { color:#dbe4f0; }
	.kpi-table td:last-child, .kpi-table th:last-child { text-align:right; }
	.kpi-table tbody tr:hover { background:rgba(255,255,255,.025); }
	.leader-rank { display:inline-flex; width:1.65rem; height:1.65rem; align-items:center; justify-content:center; border:1px solid rgba(148,163,184,.16); border-radius:50%; color:#94a3b8; font-size:.72rem; font-weight:800; }
	.leader-rank--top { border-color:rgba(251,191,36,.28); background:rgba(251,191,36,.1); color:#fcd34d; }
	.leader-name { display:block; color:#f1f5f9; font-weight:650; }
	.leader-email { display:block; margin-top:.16rem; color:#64748b; font-size:.72rem; overflow-wrap:anywhere; }
	.leader-metric { color:#e2e8f0; font-variant-numeric:tabular-nums; font-weight:700; white-space:nowrap; }
	.leaderboard-empty { padding:1.25rem .5rem; color:#94a3b8; font-size:.84rem; text-align:center; }
	.kpi-trend { display:grid; grid-template-columns:repeat(15,minmax(24px,1fr)); align-items:end; gap:.35rem; min-height:150px; padding-top:1rem; }
	.kpi-bar-wrap { height:130px; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; gap:.35rem; }
	.kpi-bar { width:100%; min-height:2px; border-radius:5px 5px 0 0; background:linear-gradient(180deg,#38bdf8,#6366f1); }
	.kpi-bar-label { color:#64748b; font-size:.64rem; writing-mode:vertical-rl; transform:rotate(180deg); }
	.user-kpi-chart-wrap { position:relative; min-height:320px; background:rgba(255,255,255,.01); border:1px solid rgba(148,163,184,.12); border-radius:14px; padding:.85rem; }
	@media (max-width:1100px) { .user-kpi-grid, .acquisition-kpi-grid { grid-template-columns:repeat(2,1fr); } .acquisition-grid { grid-template-columns:1fr; } }
	@media (max-width:800px) { .leaderboard-grid { grid-template-columns:1fr; } }
	@media (max-width:700px) { .user-kpi-header { display:block; } .user-kpi-grid, .period-grid { grid-template-columns:1fr; } .period-leaderboard-header { align-items:flex-start; flex-direction:column; gap:.55rem; } .kpi-table th, .kpi-table td { padding:.6rem .3rem; } }
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

	<section class="user-kpi-panel" style="margin-bottom:1rem;">
		<div class="period-leaderboard-header">
			<div>
				<h2>Kullanıcı edinimi</h2>
				<p style="margin:.25rem 0 0;">Edinim tablosuna kaydedilmiş tüm yeni müşteri kayıtları.</p>
			</div>
			<a href="<?= base_url('users/acquisitionReport') ?>" class="period-window">Detaylı edinim raporu →</a>
		</div>
		<?php if (empty($acquisitionKpis['available'])): ?>
			<div class="leaderboard-empty">Edinim veritabanı tablosu henüz oluşturulmamış. `user_acquisitions` tablosu kurulduğunda bu bölüm raporları gösterecek.</div>
		<?php else: ?>
			<div class="acquisition-kpi-grid">
				<div class="acquisition-kpi"><small>Takip edilen kayıt</small><strong><?= $number($acquisitionTotals['signups'] ?? 0) ?></strong></div>
				<div class="acquisition-kpi"><small>Doğrudan / kaynak belirsiz</small><strong><?= $number($acquisitionTotals['unknown'] ?? 0) ?></strong></div>
				<div class="acquisition-kpi"><small>Ücretli kanal sinyali</small><strong><?= $number($acquisitionTotals['paid'] ?? 0) ?></strong></div>
				<div class="acquisition-kpi"><small>Kampanya etiketli</small><strong><?= $number($acquisitionTotals['campaigns'] ?? 0) ?></strong></div>
			</div>
			<div class="acquisition-grid">
				<div class="acquisition-table-wrap">
					<h3 class="leaderboard-card-header" style="color:#f8fafc;font-size:.95rem;">Kaynak / kanal</h3>
					<table class="kpi-table">
						<thead><tr><th>Kaynak</th><th>Kanal</th><th>Kayıt</th></tr></thead>
						<tbody>
							<?php if (empty($acquisitionKpis['sources'])): ?>
								<tr><td colspan="3" class="leaderboard-empty">Henüz edinim kaydı yok.</td></tr>
							<?php else: foreach ($acquisitionKpis['sources'] as $source): ?>
								<tr>
									<td><?= $escape($source['acquisition_source'] !== '' ? $source['acquisition_source'] : 'Bilinmiyor') ?></td>
									<td><?= $escape($source['acquisition_medium'] !== '' ? $source['acquisition_medium'] : '—') ?></td>
									<td class="leader-metric"><?= $number($source['signups']) ?></td>
								</tr>
							<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
				<div class="acquisition-table-wrap">
					<h3 class="leaderboard-card-header" style="color:#f8fafc;font-size:.95rem;">Kampanyalar</h3>
					<table class="kpi-table">
						<thead><tr><th>Kampanya</th><th>Kaynak</th><th>Kayıt</th></tr></thead>
						<tbody>
							<?php if (empty($acquisitionKpis['campaigns'])): ?>
								<tr><td colspan="3" class="leaderboard-empty">UTM kampanyası bulunamadı.</td></tr>
							<?php else: foreach ($acquisitionKpis['campaigns'] as $campaign): ?>
								<tr>
									<td><?= $escape($campaign['acquisition_campaign']) ?></td>
									<td><?= $escape($campaign['acquisition_source']) ?> / <?= $escape($campaign['acquisition_medium'] !== '' ? $campaign['acquisition_medium'] : '—') ?></td>
									<td class="leader-metric"><?= $number($campaign['signups']) ?></td>
								</tr>
							<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
				<div class="acquisition-table-wrap">
					<h3 class="leaderboard-card-header" style="color:#f8fafc;font-size:.95rem;">İlk açılan sayfalar</h3>
					<table class="kpi-table">
						<thead><tr><th>Sayfa</th><th>Kayıt</th></tr></thead>
						<tbody>
							<?php if (empty($acquisitionKpis['landing_pages'])): ?>
								<tr><td colspan="2" class="leaderboard-empty">Sayfa kaydı bulunamadı.</td></tr>
							<?php else: foreach ($acquisitionKpis['landing_pages'] as $landingPage): ?>
								<tr>
									<td><?= $escape($landingPage['acquisition_landing_page'] !== '' ? $landingPage['acquisition_landing_page'] : 'Bilinmiyor') ?></td>
									<td class="leader-metric"><?= $number($landingPage['signups']) ?></td>
								</tr>
							<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
			<p style="margin:1rem 0 0;color:#64748b;font-size:.75rem;">Gösterilen tablolar tüm zamanları kapsar ve en yüksek kayıt sayısına sahip ilk 10 satırla sınırlıdır.</p>
		<?php endif; ?>
	</section>

	<section class="user-kpi-panel" style="margin-bottom:1rem;">
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

	<?php foreach ($periodLabels as $key => $label):
		$period = $periodsData[$key] ?? [];
		$leaderboards = [
			['title' => 'En çok sipariş veren', 'subtitle' => 'Tamamlanan sipariş adedine göre', 'icon' => '#', 'users' => $period['top_orders'] ?? [], 'metric' => 'orders', 'format' => 'number', 'class' => 'orders'],
			['title' => 'En yüksek harcama', 'subtitle' => 'Toplam sipariş tutarına göre', 'icon' => '₺', 'users' => $period['top_amount'] ?? [], 'metric' => 'amount', 'format' => 'money', 'class' => 'amount'],
		];
	?>
		<section class="user-kpi-panel period-leaderboard">
			<div class="period-leaderboard-header">
				<h2><?= $label ?> liderleri</h2>
				<span class="period-window"><?= $escape($periods[$key]['subtitle'] ?? '') ?></span>
			</div>
			<div class="leaderboard-grid">
				<?php foreach ($leaderboards as $leaderboard): ?>
					<div class="leaderboard-card leaderboard-card--<?= $leaderboard['class'] ?>">
						<div class="leaderboard-card-header">
							<div>
								<h3><?= $leaderboard['title'] ?></h3>
								<p><?= $leaderboard['subtitle'] ?></p>
							</div>
							<span class="leaderboard-icon" aria-hidden="true"><?= $leaderboard['icon'] ?></span>
						</div>
						<?php if (!empty($leaderboard['users'])): ?>
							<table class="kpi-table">
								<thead><tr><th scope="col">Sıra</th><th scope="col">Müşteri</th><th scope="col"><?= $leaderboard['format'] === 'money' ? 'Tutar' : 'Sipariş' ?></th></tr></thead>
								<tbody>
									<?php foreach ($leaderboard['users'] as $index => $user):
										$userName = trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''));
										$userName = $userName !== '' ? $userName : ('Kullanıcı #' . (int)($user['user_id'] ?? 0));
									?>
										<tr>
											<td><span class="leader-rank<?= $index < 3 ? ' leader-rank--top' : '' ?>"><?= $index + 1 ?></span></td>
											<td><span class="leader-name"><?= $escape($userName) ?></span><span class="leader-email"><?= $escape($user['user_email'] ?? '') ?></span></td>
											<td class="leader-metric"><?= $leaderboard['format'] === 'money' ? $money($user[$leaderboard['metric']] ?? 0) : $number($user[$leaderboard['metric']] ?? 0) ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php else: ?>
							<div class="leaderboard-empty">Bu dönemde tamamlanan sipariş bulunmuyor.</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
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
		function renderRegistrationChart(canvasId, labels, values, color, borderColor, maxBarThickness) {
			var canvas = document.getElementById(canvasId);
			if (!canvas || typeof Chart === 'undefined') {
				return;
			}

			new Chart(canvas.getContext('2d'), {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [{
						label: 'Yeni kayıt',
						data: values,
						backgroundColor: color,
						borderColor: borderColor,
						borderWidth: 1,
						borderRadius: 8,
						maxBarThickness: maxBarThickness
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

		function renderRegistrationCharts() {
			renderRegistrationChart(
				'monthlyRegistrationsChart',
				<?= $monthlyTrendLabelsJson ?: '[]' ?>,
				<?= $monthlyTrendValuesJson ?: '[]' ?>,
				'#38bdf8',
				'#7dd3fc',
				42
			);
			renderRegistrationChart(
				'dailyRegistrationsChart',
				<?= $registrationTrendLabelsJson ?: '[]' ?>,
				<?= $registrationTrendValuesJson ?: '[]' ?>,
				'#818cf8',
				'#a5b4fc',
				64
			);
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', renderRegistrationCharts);
		} else {
			renderRegistrationCharts();
		}
	}());
</script>
