<?php defined('BASEPATH') or exit('No direct script access allowed');
$money = static function ($value) {
	return number_format((float)$value, 2, ',', '.') . ' TL';
};
$num = static function ($value) {
	return number_format((int)$value, 0, ',', '.');
};
$icons = [
	'all' => '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
	'card' => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M3 10h18M7 15h4"/>',
	'bank' => '<path d="m3 9 9-6 9 6M3 10h18M6 10v9m6-9v9m6-9v9M3 21h18"/>',
	'mobile' => '<rect x="6" y="2" width="12" height="20" rx="3"/><path d="M10 18h4"/>',
	'check' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
	'fail' => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6m0-6-6 6"/>',
	'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
	'money' => '<rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 9v6m12-6v6"/>',
];
$svg = static function ($name, $size = 20) use ($icons) {
	return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$name] . '</svg>';
};
$methodTabs = [
	'all' => ['Tüm Ödemeler', 'Kart · Havale · Mobil'],
	'card' => ['Kart', 'Sipariş ve bakiye yükleme'],
	'bank' => ['Havale', 'Banka transferleri'],
	'mobile' => ['Mobil Ödeme', 'Operatör faturası'],
];
$tabUrl = static function ($method) use ($filters) {
	$params = ['start' => $filters['start'], 'end' => $filters['end'], 'status' => $filters['status'], 'query' => $filters['query']];
	if ($method !== 'all') {
		$params['method'] = $method;
	}
	return base_url('reports/paymentMethods?' . http_build_query(array_filter($params, static function ($v) {
		return $v !== '' && $v !== null;
	})));
};
$totalDone = $summary['success'] + $summary['failed'];
$successPct = $totalDone > 0 ? ($summary['success'] / $totalDone) * 100 : 0;
$donutPct = $summary['count'] > 0 ? [
	'success' => $summary['success'] / $summary['count'] * 100,
	'failed' => $summary['failed'] / $summary['count'] * 100,
] : ['success' => 0, 'failed' => 0];
$maxDaily = 1;
foreach ($daily as $d) {
	$maxDaily = max($maxDaily, $d['success'], $d['failed']);
}
$kpis = [
	['Toplam Ödeme İşlemi', $num($summary['count']), 'all', ''],
	['Başarılı Tutar (Komisyon Hariç)', $money($summary['success_amount']), 'money', 'pr-ok'],
	['Başarılı İşlem Komisyonu', $money($summary['success_commission']), 'money', 'pr-ok'],
	['Komisyon Dahil Tahsilat', $money($summary['success_gross_amount']), 'money', 'pr-ok'],
	['Başarılı İşlem Sayısı', $num($summary['success']), 'check', 'pr-ok'],
	['Bekleyen İşlem', $num($summary['pending']) . ' · ' . $money($summary['pending_amount']), 'clock', 'pr-wait'],
	['Başarısız İşlem Sayısı', $num($summary['failed']), 'fail', 'pr-bad'],
	['Başarısız Ödeme Tutarı', $money($summary['failed_amount']), 'fail', 'pr-bad'],
];
?>

<style id="playsel-payment-methods-theme">
	.pm-report {
		--pm-card: #171723;
		--pm-surface: #1d1d2b;
		--pm-hover: #232334;
		--pm-line: #2d2d40;
		--pm-text: #f3f3f7;
		--pm-muted: #9292a9;
		--pm-gold: #ffb511;
		color: var(--pm-text);
	}

	.pm-report .pm-crumb {
		color: var(--pm-muted);
		font-size: 11px;
		margin-bottom: 14px;
	}

	.pm-report .pm-crumb b {
		color: var(--pm-text);
		font-weight: 600;
	}

	.pm-report .pm-panel {
		background: var(--pm-card);
		border: 1px solid #202031;
		border-radius: 12px;
		overflow: hidden;
	}

	.pm-report .pm-head {
		padding: 16px 20px;
		border-bottom: 1px solid #242435;
	}

	.pm-report .pm-head h1 {
		font-size: 16px;
		margin: 0 0 4px;
		color: var(--pm-text);
	}

	.pm-report .pm-head p {
		margin: 0;
		color: var(--pm-muted);
		font-size: 11px;
	}

	.pm-report .pm-methods {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 12px;
		padding: 18px 18px 14px;
	}

	.pm-report .pm-method {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 16px 14px;
		background: var(--pm-surface);
		border: 1px solid var(--pm-line);
		border-radius: 10px;
		color: inherit;
		text-decoration: none;
		transition: border-color .15s, background .15s;
	}

	.pm-report .pm-method:hover {
		background: var(--pm-hover);
		border-color: #6b5420;
		color: inherit;
	}

	.pm-report .pm-method.selected {
		background: linear-gradient(120deg, rgba(255, 181, 17, .12), var(--pm-surface));
		border-color: #a57c20;
	}

	.pm-report .pm-method-icon {
		width: 42px;
		height: 42px;
		display: grid;
		place-items: center;
		flex-shrink: 0;
		border-radius: 10px;
		background: #252537;
		border: 1px solid #34344a;
		color: var(--pm-muted);
	}

	.pm-report .pm-method.selected .pm-method-icon {
		color: var(--pm-gold);
		background: rgba(255, 181, 17, .12);
		border-color: #6b5420;
	}

	.pm-report .pm-method strong {
		display: block;
		font-size: 12px;
		color: var(--pm-text);
	}

	.pm-report .pm-method small {
		display: block;
		margin-top: 4px;
		color: var(--pm-muted);
		font-size: 10px;
	}

	.pm-report .pm-method b {
		margin-left: auto;
		color: var(--pm-muted);
		font-size: 13px;
	}

	.pm-report .pm-method.selected b {
		color: var(--pm-gold);
	}

	.pm-report .pm-kpis {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: 11px;
		padding: 0 18px 18px;
	}

	.pm-report .pm-kpi {
		padding: 15px;
		background: var(--pm-surface);
		border: 1px solid var(--pm-line);
		border-radius: 10px;
	}

	.pm-report .pm-kpi-label {
		display: flex;
		align-items: center;
		gap: 8px;
		color: var(--pm-muted);
		font-size: 10px;
	}

	.pm-report .pm-kpi strong {
		display: block;
		margin-top: 10px;
		font-size: 20px;
		letter-spacing: -.4px;
		overflow-wrap: anywhere;
	}

	.pm-report .pr-ok strong {
		color: #49d19a;
	}

	.pm-report .pr-wait strong {
		color: #ffcf6b;
	}

	.pm-report .pr-bad strong {
		color: #fa507b;
	}

	.pm-report .pm-filter {
		display: flex;
		align-items: flex-end;
		flex-wrap: wrap;
		gap: 10px;
		padding: 15px 18px;
		background: #151521;
		border-top: 1px solid #242435;
		border-bottom: 1px solid #242435;
	}

	.pm-report .pm-filter label {
		display: flex;
		flex-direction: column;
		gap: 5px;
		margin: 0;
		color: var(--pm-muted);
		font-size: 10px;
	}

	.pm-report .pm-filter input,
	.pm-report .pm-filter select {
		min-width: 145px;
		height: 35px;
		padding: 0 10px;
		background: #1c1c2a;
		border: 1px solid #2b2b3b;
		border-radius: 5px;
		color: #d7d7e2;
		color-scheme: dark;
		outline: 0;
	}

	.pm-report .pm-filter input:focus,
	.pm-report .pm-filter select:focus {
		border-color: #a57c20;
	}

	.pm-report .pm-btn {
		height: 35px;
		padding: 0 16px;
		border-radius: 5px;
		border: 1px solid #333348;
		background: #29293c;
		color: #bdbdd0;
		font-weight: 600;
		text-decoration: none;
		display: inline-flex;
		align-items: center;
		cursor: pointer;
	}

	.pm-report .pm-btn.primary {
		background: var(--pm-gold);
		border-color: var(--pm-gold);
		color: #17131a;
		font-weight: 700;
	}

	.pm-report .pm-banks {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
		gap: 11px;
		padding: 16px 18px 0;
	}

	.pm-report .pm-bank {
		padding: 14px;
		background: var(--pm-surface);
		border: 1px solid var(--pm-line);
		border-radius: 10px;
	}

	.pm-report .pm-bank strong {
		display: block;
		font-size: 12px;
	}

	.pm-report .pm-bank span {
		display: block;
		margin: 4px 0 8px;
		color: var(--pm-muted);
		font-size: 10px;
	}

	.pm-report .pm-bank b {
		font-size: 17px;
		color: #49d19a;
	}

	.pm-report .pm-charts {
		display: grid;
		grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
		gap: 14px;
		padding: 18px;
	}

	.pm-report .pm-chart {
		min-height: 290px;
		padding: 18px;
		background: linear-gradient(145deg, rgba(255, 181, 17, .05), transparent 55%), var(--pm-surface);
		border: 1px solid var(--pm-line);
		border-radius: 10px;
		min-width: 0;
	}

	.pm-report .pm-chart h3 {
		display: flex;
		align-items: center;
		gap: 9px;
		margin: 0 0 12px;
		font-size: 12px;
		color: var(--pm-text);
	}

	.pm-report .pm-legend {
		display: flex;
		justify-content: flex-end;
		gap: 13px;
		margin-bottom: 8px;
		color: var(--pm-muted);
		font-size: 9px;
	}

	.pm-report .pm-legend i,
	.pm-report .pm-status-numbers i {
		display: inline-block;
		width: 8px;
		height: 8px;
		border-radius: 50%;
		margin-right: 5px;
	}

	.pm-report .pm-bars {
		display: flex;
		align-items: flex-end;
		gap: 10px;
		height: 205px;
		padding: 18px 4px 0;
		overflow-x: auto;
		background: repeating-linear-gradient(to top, transparent 0, transparent 39px, rgba(255, 255, 255, .05) 40px, transparent 41px);
	}

	.pm-report .pm-bar-day {
		flex: 1 0 34px;
		display: flex;
		flex-direction: column;
		align-items: center;
		height: 100%;
		justify-content: flex-end;
	}

	.pm-report .pm-bar-pair {
		display: flex;
		align-items: flex-end;
		gap: 4px;
		height: 150px;
	}

	.pm-report .pm-bar {
		width: 14px;
		min-height: 2px;
		border-radius: 4px 4px 0 0;
	}

	.pm-report .pm-bar.ok {
		background: linear-gradient(#ffc84d, #b78620);
	}

	.pm-report .pm-bar.bad {
		background: linear-gradient(#fa507b, #8f2a44);
	}

	.pm-report .pm-bar-day small {
		padding-top: 8px;
		color: var(--pm-muted);
		font-size: 9px;
		white-space: nowrap;
	}

	.pm-report .pm-status {
		display: flex;
		align-items: center;
		justify-content: center;
		flex-wrap: wrap;
		gap: 25px;
		min-height: 225px;
	}

	.pm-report .pm-donut {
		width: 155px;
		height: 155px;
		padding: 13px;
		border-radius: 50%;
	}

	.pm-report .pm-donut>div {
		width: 100%;
		height: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		background: var(--pm-card);
	}

	.pm-report .pm-donut strong {
		font-size: 26px;
	}

	.pm-report .pm-donut small,
	.pm-report .pm-status-numbers small {
		color: var(--pm-muted);
		font-size: 10px;
	}

	.pm-report .pm-status-numbers {
		min-width: 130px;
	}

	.pm-report .pm-status-numbers p {
		display: flex;
		align-items: center;
		margin: 0 0 12px;
		padding-bottom: 12px;
		border-bottom: 1px solid var(--pm-line);
		color: #c6c6d3;
	}

	.pm-report .pm-status-numbers p strong {
		margin-left: auto;
		padding-left: 14px;
		font-size: 18px;
		color: var(--pm-text);
	}

	.pm-report .pm-empty {
		padding: 40px 10px;
		text-align: center;
		color: var(--pm-muted);
	}

	.pm-report .pm-note {
		margin: 0;
		padding: 0 18px 18px;
		color: #7f7f98;
		font-size: 10px;
		line-height: 1.8;
	}

	@media (max-width: 1150px) {
		.pm-report .pm-methods {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		.pm-report .pm-charts {
			grid-template-columns: 1fr;
		}
	}

	@media (max-width: 650px) {

		.pm-report .pm-methods,
		.pm-report .pm-kpis {
			grid-template-columns: 1fr;
			padding-left: 12px;
			padding-right: 12px;
		}

		.pm-report .pm-filter label,
		.pm-report .pm-filter input,
		.pm-report .pm-filter select {
			width: 100%;
		}
	}
</style>

<div class="pm-report">
	<div class="pm-crumb">Raporlar / <b>Ödeme Yöntemleri Raporu</b></div>
	<div class="pm-panel">
		<div class="pm-head">
			<h1>Ödeme Yöntemleri Raporu</h1>
			<p>Kart, havale ve mobil ödeme işlemleri · <?= html_escape(date('d.m.Y', strtotime($filters['start']))) ?> - <?= html_escape(date('d.m.Y', strtotime($filters['end']))) ?></p>
		</div>

		<div class="pm-methods">
			<?php foreach ($methodTabs as $key => $tab): ?>
				<a class="pm-method <?= $filters['method'] === $key ? 'selected' : '' ?>" href="<?= html_escape($tabUrl($key)) ?>">
					<span class="pm-method-icon"><?= $svg($key, 22) ?></span>
					<span><strong><?= html_escape($tab[0]) ?></strong><small><?= html_escape($tab[1]) ?> · <?= $num($methodCounts[$key]) ?> işlem</small></span>
					<b>›</b>
				</a>
			<?php endforeach; ?>
		</div>

		<div class="pm-kpis">
			<?php foreach ($kpis as $kpi): ?>
				<div class="pm-kpi <?= $kpi[3] ?>">
					<span class="pm-kpi-label"><?= $svg($kpi[2], 16) ?><?= html_escape($kpi[0]) ?></span>
					<strong><?= html_escape($kpi[1]) ?></strong>
				</div>
			<?php endforeach; ?>
		</div>

		<form class="pm-filter" method="get" action="<?= base_url('reports/paymentMethods') ?>">
			<?php if ($filters['method'] !== 'all'): ?><input type="hidden" name="method" value="<?= html_escape($filters['method']) ?>"><?php endif; ?>
			<label>Başlangıç<input type="date" name="start" value="<?= html_escape($filters['start']) ?>"></label>
			<label>Bitiş<input type="date" name="end" value="<?= html_escape($filters['end']) ?>"></label>
			<label>Durum
				<select name="status">
					<option value="">Tüm Durumlar</option>
					<option value="success" <?= $filters['status'] === 'success' ? 'selected' : '' ?>>Başarılı</option>
					<option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Bekleyen</option>
					<option value="failed" <?= $filters['status'] === 'failed' ? 'selected' : '' ?>>Başarısız</option>
				</select>
			</label>
			<?php if (in_array($filters['method'], ['all', 'bank'], true)): ?>
				<label>Havale Bankası
					<select name="bank">
						<option value="">Tüm Bankalar</option>
						<?php foreach ($banks as $bankName): ?>
							<option value="<?= html_escape($bankName) ?>" <?= $filters['bank'] === $bankName ? 'selected' : '' ?>><?= html_escape($bankName) ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			<?php endif; ?>
			<label>İşlem / Müşteri<input type="text" name="query" maxlength="100" placeholder="İşlem no veya e-posta" value="<?= html_escape($filters['query']) ?>"></label>
			<button class="pm-btn primary" type="submit">Filtrele</button>
			<a class="pm-btn" href="<?= base_url('reports/paymentMethods') ?>">Temizle</a>
		</form>

		<?php if ($filters['method'] === 'bank' && !empty($bankSummary)): ?>
			<div class="pm-banks">
				<?php foreach ($bankSummary as $name => $bankRow): ?>
					<div class="pm-bank"><strong><?= html_escape($name) ?></strong><span><?= $num($bankRow['count']) ?> başarılı işlem</span><b><?= $money($bankRow['amount']) ?></b></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="pm-charts">
			<div class="pm-chart">
				<h3>Günlük Ödeme Tutarları</h3>
				<div class="pm-legend"><span><i style="background:#ffb511"></i>Başarılı (komisyon dahil)</span><span><i style="background:#fa507b"></i>Başarısız (komisyon hariç)</span></div>
				<?php if (empty($daily)): ?>
					<p class="pm-empty">Bu dönemde ödeme bulunamadı.</p>
				<?php else: ?>
					<div class="pm-bars">
						<?php foreach ($daily as $day => $amounts): ?>
							<div class="pm-bar-day">
								<div class="pm-bar-pair">
									<div class="pm-bar ok" style="height:<?= round($amounts['success'] / $maxDaily * 140, 1) ?>px" title="<?= html_escape(date('d.m.Y', strtotime($day)) . ' · Komisyon dahil başarılı: ' . $money($amounts['success'])) ?>"></div>
									<div class="pm-bar bad" style="height:<?= round($amounts['failed'] / $maxDaily * 140, 1) ?>px" title="<?= html_escape(date('d.m.Y', strtotime($day)) . ' · Başarısız (komisyon hariç): ' . $money($amounts['failed'])) ?>"></div>
								</div>
								<small><?= html_escape(date('d.m', strtotime($day))) ?></small>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="pm-chart">
				<h3>İşlem Başarı Dağılımı</h3>
				<div class="pm-status">
					<div class="pm-donut" style="background:conic-gradient(#40c99b 0% <?= round($donutPct['success'], 2) ?>%,#ed7683 <?= round($donutPct['success'], 2) ?>% <?= round($donutPct['success'] + $donutPct['failed'], 2) ?>%,#ffcf6b <?= round($donutPct['success'] + $donutPct['failed'], 2) ?>% 100%)">
						<div><strong>%<?= number_format($successPct, 0, ',', '.') ?></strong><small>Başarı oranı</small></div>
					</div>
					<div class="pm-status-numbers">
						<p><i style="background:#40c99b"></i>Başarılı <strong><?= $num($summary['success']) ?></strong></p>
						<p><i style="background:#ed7683"></i>Başarısız <strong><?= $num($summary['failed']) ?></strong></p>
						<p><i style="background:#ffcf6b"></i>Bekleyen <strong><?= $num($summary['pending']) ?></strong></p>
						<small><?= $num($summary['count']) ?> toplam işlem</small>
					</div>
				</div>
			</div>
		</div>

		<p class="pm-note">Başarılı tutar komisyon hariç ana tutardır; başarılı işlemlerin komisyonu ve komisyon dahil tahsilatı, kart için %2,7 ve mobil ödeme için %60 oranı uygulanarak hesaplanır. Bekleyen ve başarısız tutarlar komisyonsuz ana tutardır ve tahsilat toplamına dahil değildir. Havalede komisyon eklenmez. Oranlar ödeme akışındaki güncel oranlara göre hesaplanır; kesinleşmiş banka/sağlayıcı mutabakatı yerine geçmez. Başarı oranı yalnızca sonuçlanmış (başarılı + başarısız) işlemlere göre hesaplanır. Cüzdan bakiyesiyle yapılan siparişler ve iade/harcama hareketleri bu rapora dahil değildir. İşlem no ön eki: S = sipariş ödemesi, B = bakiye yükleme.</p>
	</div>
</div>
