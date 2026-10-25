<?php defined('BASEPATH') or exit('No direct script access allowed');
$report = $paymentReport;
$stats = $report['stats'];
$money = static function ($value) {
	return number_format((float)$value, 2, ',', '.') . ' TL';
};
$pageUrl = static function ($page) use ($report) {
	$params = ['start' => $report['start'], 'end' => $report['end'], 'type' => $report['type'], 'query' => $report['query'], 'page' => $page];
	return base_url('reports/paymentReports?' . http_build_query(array_filter($params, static function ($v) {
		return $v !== '' && $v !== null;
	})));
};
$tagClass = ['payment' => 'ok', 'admin_add' => 'ok', 'order' => 'info', 'admin_deduct' => 'bad', 'withdraw' => 'wait', 'pending' => 'wait', 'cancelled' => 'bad'];
?>
<style>
.wr{--wr-card:#171723;--wr-surface:#1d1d2b;--wr-hover:#232334;--wr-line:#2d2d40;--wr-text:#f3f3f7;--wr-muted:#9292a9;--wr-gold:#ffb511;color:var(--wr-text);font-size:12px}
.wr *{box-sizing:border-box}
.wr-crumb{color:var(--wr-muted);font-size:11px;margin-bottom:14px}.wr-crumb b{color:var(--wr-text);font-weight:600}
.wr-panel{background:var(--wr-card);border:1px solid var(--wr-line);border-radius:10px;overflow:hidden}
.wr-head{padding:20px;border-bottom:1px solid var(--wr-line)}
.wr-head h1{margin:0;font-size:18px;font-weight:700;color:#fff}
.wr-head p{margin:5px 0 0;color:var(--wr-muted);font-size:11px}
.wr-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding:14px;border-bottom:1px solid var(--wr-line)}
.wr-stat{background:var(--wr-surface);border:1px solid var(--wr-line);border-radius:8px;padding:12px 14px}
.wr-stat small{display:block;color:var(--wr-muted);font-size:10px;margin-bottom:6px}
.wr-stat strong{font-size:18px;color:#fff;font-weight:700}
.wr-stat.in strong{color:#44dba2}.wr-stat.out strong{color:#ff8d91}
.wr-filter{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;padding:14px;border-bottom:1px solid var(--wr-line)}
.wr-filter label{display:block;color:var(--wr-muted);font-size:10px}
.wr-filter input,.wr-filter select{display:block;margin-top:5px;height:34px;background:#1c1c2a;border:1px solid #2b2b3b;border-radius:6px;color:#eee;padding:0 10px;font:inherit;outline:0}
.wr-filter input[name=query]{min-width:245px}
.wr-filter input:focus,.wr-filter select:focus{border-color:#a57c20}
.wr-btn{height:34px;border:1px solid #333348;border-radius:6px;background:#29293c;color:#d4d4e2;padding:0 14px;font:inherit;font-weight:600;display:inline-flex;align-items:center;text-decoration:none;cursor:pointer}
.wr-btn:hover{border-color:#66572e;color:#fff}
.wr-btn.primary{background:var(--wr-gold);border-color:var(--wr-gold);color:#17131a}
.wr-scroll{overflow-x:auto}
.wr-table{width:100%;min-width:1150px;border-collapse:collapse;text-align:left}
.wr-table th{background:#14141f;color:#a9a9bd;font-size:10px;font-weight:600;padding:12px 14px;border-bottom:1px solid var(--wr-line);white-space:nowrap}
.wr-table td{padding:12px 14px;border-bottom:1px dotted #2b2b3d;color:#cfcfdd;white-space:nowrap;vertical-align:middle}
.wr-table tbody tr:hover td{background:var(--wr-hover)}
.wr-table td strong{color:#f1f1f6;font-weight:600}
.wr-table td small{display:block;color:var(--wr-muted);font-size:9px;margin-top:3px}
.wr-table .pos{color:#44dba2}.wr-table .neg{color:#ff8d91}.wr-table .neu{color:var(--wr-muted)}
.wr-link{color:#21aafa;text-decoration:none}.wr-link:hover{text-decoration:underline}
.wr-tag{display:inline-block;padding:4px 8px;border-radius:4px;font-size:10px;font-weight:600}
.wr-tag.ok{background:#173b2e;color:#63e2aa}.wr-tag.bad{background:#491f34;color:#ff668f}.wr-tag.wait{background:#44341c;color:#ffcf6b}.wr-tag.info{background:#1b2d45;color:#6cc3ff}
.wr-empty{text-align:center;color:var(--wr-muted);padding:34px!important}
.wr-foot{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 16px;color:var(--wr-muted);font-size:10px}
.wr-pages{display:flex;gap:6px}
.wr-pages a,.wr-pages span{min-width:29px;height:28px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #36374c;background:#242538;color:#c6c8dd;border-radius:5px;text-decoration:none}
.wr-pages span{opacity:.35}
.wr-note{padding:10px 16px;color:#ffcf6b;font-size:10px;background:#44341c33}
@media(max-width:900px){.wr-stats{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.wr-filter>*,.wr-filter input,.wr-filter select{width:100%}.wr-filter input[name=query]{min-width:0}}
</style>
<div class="wr">
	<div class="wr-crumb">Raporlar / <b>Ödeme Raporları</b></div>
	<section class="wr-panel">
		<div class="wr-head">
			<h1>Ödeme Raporları</h1>
			<p>Müşteri cüzdan hareketleri: ödeme girişleri, sipariş düşümleri ve admin işlemleri, işlem öncesi ve sonrası bakiyeyle listelenir.</p>
		</div>
		<div class="wr-stats">
			<div class="wr-stat"><small>Toplam Hareket</small><strong><?= number_format($stats['count'], 0, ',', '.') ?></strong></div>
			<div class="wr-stat in"><small>Toplam Ödeme Girişi</small><strong><?= $money($stats['incoming']) ?></strong></div>
			<div class="wr-stat out"><small>Toplam Ödeme Çıkışı</small><strong><?= $money($stats['outgoing']) ?></strong></div>
			<div class="wr-stat"><small>İşlem Yapan Müşteri</small><strong><?= number_format($stats['users'], 0, ',', '.') ?></strong></div>
		</div>
		<form class="wr-filter" method="get" action="<?= base_url('reports/paymentReports') ?>">
			<label>Müşteri / İşlem Ara<input type="text" name="query" maxlength="80" value="<?= html_escape($report['query']) ?>" placeholder="Ad, e-posta, işlem veya sipariş no"></label>
			<label>Başlangıç<input type="date" name="start" value="<?= html_escape($report['start']) ?>"></label>
			<label>Bitiş<input type="date" name="end" value="<?= html_escape($report['end']) ?>"></label>
			<label>Hareket Türü
				<select name="type">
					<option value="">Tüm Hareketler</option>
					<?php foreach ($report['typeLabels'] as $key => $label): ?>
						<option value="<?= html_escape($key) ?>" <?= $report['type'] === $key ? 'selected' : '' ?>><?= html_escape($label) ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<button class="wr-btn primary" type="submit">Filtrele</button>
			<a class="wr-btn" href="<?= base_url('reports/paymentReports') ?>">Temizle</a>
		</form>
		<?php if ($report['capped']): ?><div class="wr-note">Seçilen dönemde çok fazla kayıt var; ilk 20.000 hareket gösteriliyor. Tarih aralığını daraltın.</div><?php endif; ?>
		<div class="wr-scroll">
			<table class="wr-table">
				<thead><tr><th>İşlem No / Tarih</th><th>Müşteri</th><th>Hareket Türü</th><th>Ödeme / Sipariş</th><th>Tutar</th><th>Önceki Bakiye</th><th>Sonraki Bakiye</th><th>İşlemi Yapan</th></tr></thead>
				<tbody>
				<?php if (!$report['rows']): ?>
					<tr><td colspan="8" class="wr-empty">Seçilen kriterlerde hareket bulunamadı.</td></tr>
				<?php endif; ?>
				<?php foreach ($report['rows'] as $row): ?>
					<?php $cls = $row['delta'] > 0 ? 'pos' : ($row['delta'] < 0 ? 'neg' : 'neu'); ?>
					<tr>
						<td><strong>#<?= (int)$row['id'] ?></strong><small><?= html_escape(date('d.m.Y H:i:s', strtotime($row['created_at']))) ?></small></td>
						<td><a class="wr-link" href="<?= base_url('users/detail/' . (int)$row['user_id']) ?>"><strong><?= html_escape($row['user_name']) ?></strong></a><small>#<?= (int)$row['user_id'] ?><?= $row['user_email'] !== '' ? ' · ' . html_escape($row['user_email']) : '' ?></small></td>
						<td><span class="wr-tag <?= html_escape($tagClass[$row['type_key']]) ?>"><?= html_escape($row['type']) ?></span></td>
						<td><?php if ($row['order_id'] > 0): ?><a class="wr-link" href="<?= base_url('balance/paymentDetail/order/' . (int)$row['order_id']) ?>"><?= html_escape($row['reference']) ?></a><?php else: ?><a class="wr-link" href="<?= base_url('balance/paymentDetail/balance/' . (int)$row['id']) ?>"><?= html_escape($row['reference']) ?></a><?php endif; ?></td>
						<td class="<?= $cls ?>"><strong><?= $row['delta'] > 0 ? '+' : ($row['delta'] < 0 ? '−' : '') ?><?= $money($row['amount']) ?></strong></td>
						<td><?= $money($row['before']) ?></td>
						<td><?= $money($row['after']) ?></td>
						<td><?= html_escape($row['actor']) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="wr-foot">
			<span><?= number_format($stats['count'], 0, ',', '.') ?> kayıt · Sayfa <?= (int)$report['page'] ?> / <?= (int)$report['pages'] ?></span>
			<div class="wr-pages">
				<?php if ($report['page'] > 1): ?><a href="<?= html_escape($pageUrl($report['page'] - 1)) ?>">‹</a><?php else: ?><span>‹</span><?php endif; ?>
				<?php if ($report['page'] < $report['pages']): ?><a href="<?= html_escape($pageUrl($report['page'] + 1)) ?>">›</a><?php else: ?><span>›</span><?php endif; ?>
			</div>
		</div>
	</section>
</div>
