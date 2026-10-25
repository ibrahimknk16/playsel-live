<div class="referral-report-page">
<div class="d-flex flex-wrap justify-content-between align-items-center report-page-heading mb-6">
	<div>
		<h2 class="fw-bold mb-1">Referans raporu</h2>
		<div class="text-muted fs-7">Yeni sistem verileri tarih filtresine tabidir. Legacy (eski sistem) toplamları ayrı ve tüm zamanları kapsar. Kâr = satış − maliyet (platform kârı).</div>
	</div>
	<a id="rrExport" href="#" class="btn btn-light-success"><i class="ki-outline ki-file-down fs-3"></i> CSV dışa aktar</a>
</div>

<div class="card admin-card report-filter-card mb-6">
	<div class="card-body py-5">
		<div class="row g-4 align-items-end">
			<div class="col-md-2"><label class="form-label fs-7">Başlangıç</label><input type="date" id="rrFrom" class="form-control form-control-sm"></div>
			<div class="col-md-2"><label class="form-label fs-7">Bitiş</label><input type="date" id="rrTo" class="form-control form-control-sm"></div>
			<div class="col-md-3"><label class="form-label fs-7">Referans ara</label><input type="text" id="rrSearch" maxlength="100" class="form-control form-control-sm" placeholder="Ad, e-posta, kullanıcı adı"></div>
			<div class="col-md-2"><label class="form-label fs-7">Min. davet edilen</label><input type="number" id="rrMin" min="0" class="form-control form-control-sm" value="0"></div>
			<div class="col-md-1"><label class="form-label fs-7">Komisyon %</label><input type="number" id="rrRate" min="0" max="100" step="0.1" class="form-control form-control-sm" value="0" title="Yalnızca ekranda tahmini hesap; kaydedilmez"></div>
			<div class="col-md-2 d-flex gap-2 align-items-center">
				<div class="form-check form-check-sm"><input class="form-check-input" type="checkbox" id="rrActive"><label class="form-check-label fs-7" for="rrActive">Yalnız aktif</label></div>
			</div>
			<div class="col-12 d-flex gap-2">
				<button id="rrApply" class="btn btn-sm btn-primary">Uygula</button>
				<button id="rrReset" class="btn btn-sm btn-light">Sıfırla</button>
			</div>
		</div>
	</div>
</div>

<div class="row g-4 mb-6 report-kpi-grid" id="rrKpis">
	<div class="col-6 col-lg-3"><div class="rr-kpi"><small>Referans veren</small><strong data-k="referrers">-</strong><span>Davet edilen müşterisi olan</span></div></div>
	<div class="col-6 col-lg-3"><div class="rr-kpi"><small>Davet edilen müşteri</small><strong data-k="referred">-</strong><span data-k="active_customers">-</span></div></div>
	<div class="col-6 col-lg-3"><div class="rr-kpi"><small>Tamamlanan sipariş</small><strong data-k="orders">-</strong><span>Durumu "tamamlandı" olanlar</span></div></div>
	<div class="col-6 col-lg-3"><div class="rr-kpi"><small>Ciro</small><strong data-k="revenue">-</strong><span>Satış tutarı (TL)</span></div></div>
	<div class="col-6 col-lg-3"><div class="rr-kpi"><small>Maliyet</small><strong data-k="cost">-</strong><span data-k="missing_cost">-</span></div></div>
	<div class="col-6 col-lg-3"><div class="rr-kpi"><small>Platform kârı</small><strong data-k="profit">-</strong><span data-k="margin">-</span></div></div>
	<div class="col-6 col-lg-3"><div class="rr-kpi"><small>Bakiye yükleme / harcama</small><strong data-k="deposits">-</strong><span data-k="spend">-</span></div></div>
	<div class="col-6 col-lg-3"><div class="rr-kpi"><small>Tahmini komisyon</small><strong data-k="commission">-</strong><span>Kâr × oran (yalnızca görüntü)</span></div></div>
	<div class="col-6"><div class="rr-kpi legacy"><small>Legacy siparişler (eski sistem)</small><strong data-k="legacy_orders">-</strong><span>Tüm zamanlar, yalnız TL</span></div></div>
	<div class="col-6"><div class="rr-kpi legacy"><small>Legacy ödemeler (eski sistem)</small><strong data-k="legacy_payments">-</strong><span>Tüm zamanlar, yalnız TL</span></div></div>
</div>

<div class="card admin-card mb-6">
	<div class="card-header"><h3 class="card-title fw-bold">Aylık ciro ve kâr (yeni sistem)</h3></div>
	<div class="card-body"><div style="position:relative;height:300px;"><canvas id="rrChart"></canvas></div></div>
</div>

<div class="card admin-card">
	<div class="card-header"><h3 class="card-title fw-bold">Referans sıralaması</h3></div>
	<div class="card-body pt-0">
		<div class="table-responsive">
			<table id="rrTable" class="table align-middle table-row-dashed fs-7 gy-3 w-100">
				<thead>
					<tr class="text-start text-gray-500 fw-bold text-uppercase gs-0">
						<th>Referans</th><th>Davet</th><th>Aktif</th><th>Sipariş</th><th>Ciro</th><th>Maliyet</th>
						<th>Kâr</th><th>Marj</th><th>Yükleme</th><th>Harcama</th><th>Legacy sipariş</th><th>Legacy ödeme</th>
						<th>Tah. komisyon</th><th></th>
					</tr>
				</thead>
				<tbody class="fw-semibold text-gray-700"></tbody>
			</table>
		</div>
		</div>
	</div>
</div>

<script src="<?= base_url('assets-front/js/chart.min.js') ?>"></script>
<script>
$(function () {
	var chart = null;
	var table = null;
	var money = function (v) { return Number(v || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ₺'; };
	var num = function (v) { return Number(v || 0).toLocaleString('tr-TR'); };
	var rate = function () { return Math.min(100, Math.max(0, parseFloat($('#rrRate').val()) || 0)); };
	var filters = function () {
		return {
			date_from: $('#rrFrom').val(), date_to: $('#rrTo').val(), search_text: $('#rrSearch').val(),
			min_referred: $('#rrMin').val() || 0, only_active: $('#rrActive').is(':checked') ? 1 : 0
		};
	};

	function loadSummary() {
		$.post("<?= base_url('users/referralReportSummary') ?>", filters(), function (res) {
			if (!res || res.status !== 'success') { return; }
			var t = res.totals, k = function (n) { return $('#rrKpis [data-k="' + n + '"]'); };
			k('referrers').text(num(t.referrers)); k('referred').text(num(t.referred));
			k('active_customers').text(num(t.active_customers) + ' aktif alışveriş yapan');
			k('orders').text(num(t.orders)); k('revenue').text(money(t.revenue)); k('cost').text(money(t.cost));
			k('missing_cost').text(t.missing_cost > 0 ? num(t.missing_cost) + ' siparişte maliyet yok (kâra dahil değil)' : 'Tüm siparişlerde maliyet var');
			k('profit').text(money(t.profit)); k('margin').text(t.margin === null ? 'Marj: -' : 'Marj: %' + t.margin);
			k('deposits').text(money(t.deposits)); k('spend').text('Harcama: ' + money(t.spend));
			k('commission').text(money(t.profit * rate() / 100));
			k('legacy_orders').text(num(t.legacy_orders) + ' adet / ' + money(t.legacy_order_amount));
			k('legacy_payments').text(num(t.legacy_payments) + ' adet / ' + money(t.legacy_payment_amount));
			var labels = res.trend.map(function (r) { return r.month; });
			var cfg = {
				type: 'bar',
				data: {labels: labels, datasets: [
					{label: 'Ciro', data: res.trend.map(function (r) { return r.revenue; }), backgroundColor: '#3e97ff'},
					{label: 'Kâr', data: res.trend.map(function (r) { return r.profit; }), backgroundColor: '#50cd89'}
				]},
				options: {responsive: true, maintainAspectRatio: false}
			};
			if (chart) { chart.destroy(); }
			chart = new Chart(document.getElementById('rrChart'), cfg);
		}, 'json');
	}

	table = $('#rrTable').DataTable({
		processing: true, serverSide: true, searching: false, pageLength: 25, order: [[6, 'desc']],
		lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
		language: {url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"},
		ajax: {url: "<?= base_url('users/referralReportList') ?>", type: 'POST', data: function (d) { $.extend(d, filters()); }},
		columns: [
			{data: 'referred', render: function (d, t, r) { return r.referrer; }},
			{data: 'referred'}, {data: 'active'}, {data: 'orders'}, {data: 'revenue'}, {data: 'cost'},
			{data: 'profit'}, {data: 'margin'}, {data: 'deposits'}, {data: 'spend'},
			{data: 'legacy_orders'}, {data: 'legacy_payments'},
			{data: 'profit_raw', orderable: false, render: function (d) { return money(d * rate() / 100); }},
			{data: 'actions', orderable: false, className: 'text-end'}
		]
	});

	function refresh() { table.ajax.reload(); loadSummary(); }
	$('#rrApply').on('click', refresh);
	$('#rrSearch').on('keydown', function (e) { if (e.key === 'Enter') { refresh(); } });
	$('#rrReset').on('click', function () {
		$('#rrFrom, #rrTo, #rrSearch').val(''); $('#rrMin').val(0); $('#rrRate').val(0); $('#rrActive').prop('checked', false); refresh();
	});
	$('#rrRate').on('change', function () { table.draw(false); loadSummary(); });
	$('#rrExport').on('click', function (e) {
		e.preventDefault();
		window.location = "<?= base_url('users/referralReportExport') ?>?" + $.param(filters());
	});
	loadSummary();
});
</script>
