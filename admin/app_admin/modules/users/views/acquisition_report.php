<div class="d-flex flex-wrap justify-content-between align-items-center mb-6">
	<div>
		<h2 class="fw-bold mb-1">Kullanıcı edinim raporu</h2>
		<div class="text-muted fs-7">Yeni kayıtların ilk bilinen kaynağı; UTM, reklam tıklama kimliği ve yönlendiren alan adına göre raporlanır.</div>
	</div>
	<div class="d-flex gap-2">
		<a href="<?= base_url('users') ?>" class="btn btn-light-primary">Müşteri listesine dön</a>
		<a href="<?= base_url('MARKETING_ACQUISITION_GUIDE.md') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-light">Pazarlama kullanım kılavuzu</a>
	</div>
</div>

<?php if (!empty($acquisitionTableMissing)): ?>
	<div class="alert alert-warning">
		Edinim veritabanı tablosu henüz oluşturulmamış. Raporu kullanabilmek için uygulama kodunu deploy etmeden önce `user_acquisitions` şemasını veritabanına uygulayın.
	</div>
<?php else: ?>
	<div class="card admin-card mb-6">
		<div class="card-body py-5">
			<form method="get" action="<?= base_url('users/acquisitionReport') ?>" class="row g-4 align-items-end">
				<div class="col-sm-4 col-lg-3">
					<label class="form-label fs-7" for="acquisitionDateFrom">Kayıt başlangıcı</label>
					<input type="date" id="acquisitionDateFrom" name="date_from" class="form-control form-control-sm" value="<?= html_escape($acquisitionDateFrom) ?>">
				</div>
				<div class="col-sm-4 col-lg-3">
					<label class="form-label fs-7" for="acquisitionDateTo">Kayıt bitişi</label>
					<input type="date" id="acquisitionDateTo" name="date_to" class="form-control form-control-sm" value="<?= html_escape($acquisitionDateTo) ?>">
				</div>
				<div class="col-sm-4 col-lg-3 d-flex gap-2">
					<button type="submit" class="btn btn-sm btn-primary">Raporu göster</button>
					<a href="<?= base_url('users/acquisitionReport') ?>" class="btn btn-sm btn-light">Tüm zamanlar</a>
				</div>
			</form>
		</div>
	</div>

	<div class="row g-4 mb-6">
		<div class="col-6 col-xl-3"><div class="card admin-card h-100"><div class="card-body"><div class="text-muted fs-7">Yeni kayıt</div><div class="fs-2 fw-bold"><?= number_format($acquisitionTotals['signups'], 0, ',', '.') ?></div></div></div></div>
		<div class="col-6 col-xl-3"><div class="card admin-card h-100"><div class="card-body"><div class="text-muted fs-7">Doğrudan / referrer yok</div><div class="fs-2 fw-bold"><?= number_format($acquisitionTotals['unknown'], 0, ',', '.') ?></div></div></div></div>
		<div class="col-6 col-xl-3"><div class="card admin-card h-100"><div class="card-body"><div class="text-muted fs-7">Ücretli kampanya sinyali</div><div class="fs-2 fw-bold"><?= number_format($acquisitionTotals['paid'], 0, ',', '.') ?></div></div></div></div>
		<div class="col-6 col-xl-3"><div class="card admin-card h-100"><div class="card-body"><div class="text-muted fs-7">Kampanya etiketli kayıt</div><div class="fs-2 fw-bold"><?= number_format($acquisitionTotals['campaigns'], 0, ',', '.') ?></div></div></div></div>
	</div>

	<div class="card admin-card mb-6">
		<div class="card-header"><h3 class="card-title fw-bold">Kaynak ve kanal</h3></div>
		<div class="card-body pt-0">
			<div class="table-responsive">
				<table class="table align-middle table-row-dashed fs-7 gy-3">
					<thead><tr class="text-start text-gray-500 fw-bold text-uppercase"><th>Kaynak</th><th>Kanal</th><th class="text-end">Kayıt</th><th class="text-end">Pay</th></tr></thead>
					<tbody class="fw-semibold">
						<?php if (empty($acquisitionSources)): ?>
							<tr><td colspan="4" class="text-muted">Seçilen tarih aralığında kayıt bulunamadı.</td></tr>
						<?php else: foreach ($acquisitionSources as $row): ?>
							<tr>
								<td><?= html_escape($row->acquisition_source !== '' ? $row->acquisition_source : 'Bilinmiyor') ?></td>
								<td><?= html_escape($row->acquisition_medium !== '' ? $row->acquisition_medium : 'Belirtilmemiş') ?></td>
								<td class="text-end"><?= number_format((int)$row->signups, 0, ',', '.') ?></td>
								<td class="text-end"><?= $acquisitionTotals['signups'] > 0 ? number_format(((int)$row->signups / $acquisitionTotals['signups']) * 100, 1, ',', '.') : '0,0' ?>%</td>
							</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="row g-6">
		<div class="col-xl-6">
			<div class="card admin-card h-100">
				<div class="card-header"><h3 class="card-title fw-bold">Kampanyalar</h3></div>
				<div class="card-body pt-0">
					<div class="table-responsive">
						<table class="table align-middle table-row-dashed fs-7 gy-3">
							<thead><tr class="text-start text-gray-500 fw-bold text-uppercase"><th>Kampanya</th><th>Kaynak / kanal</th><th class="text-end">Kayıt</th></tr></thead>
							<tbody class="fw-semibold">
								<?php if (empty($acquisitionCampaigns)): ?>
									<tr><td colspan="3" class="text-muted">UTM kampanya bilgisi bulunan kayıt yok.</td></tr>
								<?php else: foreach ($acquisitionCampaigns as $row): ?>
									<tr>
										<td><?= html_escape($row->acquisition_campaign) ?></td>
										<td><?= html_escape($row->acquisition_source) ?> / <?= html_escape($row->acquisition_medium !== '' ? $row->acquisition_medium : 'belirtilmemiş') ?></td>
										<td class="text-end"><?= number_format((int)$row->signups, 0, ',', '.') ?></td>
									</tr>
								<?php endforeach; endif; ?>
							</tbody>
						</table>
					</div>
					<?php if (count($acquisitionCampaigns) >= 200): ?><div class="text-muted fs-8">En çok kayıt alan ilk 200 kampanya gösteriliyor.</div><?php endif; ?>
				</div>
			</div>
		</div>
		<div class="col-xl-3">
			<div class="card admin-card h-100">
				<div class="card-header"><h3 class="card-title fw-bold">Kayıt yöntemi</h3></div>
				<div class="card-body pt-0">
					<div class="table-responsive">
						<table class="table align-middle table-row-dashed fs-7 gy-3">
							<thead><tr class="text-start text-gray-500 fw-bold text-uppercase"><th>Yöntem</th><th class="text-end">Kayıt</th></tr></thead>
							<tbody class="fw-semibold">
								<?php if (empty($acquisitionMethods)): ?>
									<tr><td colspan="2" class="text-muted">Seçilen tarih aralığında kayıt bulunamadı.</td></tr>
								<?php else: foreach ($acquisitionMethods as $row): ?>
									<tr>
										<td><?= html_escape($row->acquisition_signup_method !== '' ? $row->acquisition_signup_method : 'Bilinmiyor') ?></td>
										<td class="text-end"><?= number_format((int)$row->signups, 0, ',', '.') ?></td>
									</tr>
								<?php endforeach; endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card admin-card mt-6">
		<div class="card-header"><h3 class="card-title fw-bold">İlk açılan sayfalar</h3></div>
		<div class="card-body pt-0">
			<div class="table-responsive">
				<table class="table align-middle table-row-dashed fs-7 gy-3">
					<thead><tr class="text-start text-gray-500 fw-bold text-uppercase"><th>Sayfa</th><th class="text-end">Kayıt</th></tr></thead>
					<tbody class="fw-semibold">
						<?php if (empty($acquisitionLandingPages)): ?>
							<tr><td colspan="2" class="text-muted">İlk açılan sayfa bilgisi bulunamadı.</td></tr>
						<?php else: foreach ($acquisitionLandingPages as $row): ?>
							<tr>
								<td class="text-break"><?= html_escape($row->acquisition_landing_page !== '' ? $row->acquisition_landing_page : 'Bilinmiyor') ?></td>
								<td class="text-end"><?= number_format((int)$row->signups, 0, ',', '.') ?></td>
							</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>
			<?php if (count($acquisitionLandingPages) >= 200): ?><div class="text-muted fs-8">En çok kayıt alan ilk 200 sayfa gösteriliyor.</div><?php endif; ?>
		</div>
	</div>

	<div class="alert alert-info mt-6">
		<strong>Yorumlama:</strong> Arama motoru ve sosyal ağ referrer’ları, UTM veya tıklama kimliği olmadığında tahmin edilir. Referrer bilgisi tarayıcı/gizlilik ayarlarıyla gizlenebilir; bu kayıtlar doğrudan veya kaynak bilgisi yok olarak görünebilir. Reklam ve organik trafiği kesin ayırmak için kampanya linklerinde UTM kullanın.
	</div>
<?php endif; ?>
