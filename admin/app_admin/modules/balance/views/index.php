<?php
// Sayıyı formatlayan yardımcı fonksiyonlar
$formatNumber = static function ($value, int $decimals = 0): string {
    return number_format((float)$value, $decimals, ',', '.');
};
$formatMoney = static function ($value, string $currency = 'TL') use ($formatNumber): string {
    return $formatNumber($value, 2) . ' ' . $currency;
};
?>

<style id="playsel-balance-theme">
    .bakiye-dashboard {
        --playsel-bg: #101019;
        --playsel-card: #171723;
        --playsel-surface: #1d1d2b;
        --playsel-surface-hover: #232334;
        --playsel-line: #2d2d40;
        --playsel-text: #f3f3f7;
        --playsel-muted: #9292a9;
        --playsel-gold: #ffb511;
    }

    .bakiye-dashboard .card,
    .bakiye-dashboard .dashboard-card,
    .bakiye-dashboard .kpi-card {
        background: var(--playsel-card) !important;
        border: 1px solid #202031 !important;
        border-radius: 12px !important;
        box-shadow: none !important;
        color: var(--playsel-text);
    }

    .bakiye-dashboard .kpi-card {
        background: linear-gradient(145deg, rgba(255, 181, 17, .08), transparent 55%), var(--playsel-card) !important;
    }

    .bakiye-dashboard .kpi-card.kpi-success {
        background: linear-gradient(145deg, rgba(73, 209, 154, .08), transparent 55%), var(--playsel-card) !important;
    }

    .bakiye-dashboard .kpi-card.kpi-danger {
        background: linear-gradient(145deg, rgba(250, 80, 123, .08), transparent 55%), var(--playsel-card) !important;
    }

    .bakiye-dashboard .card-header,
    .bakiye-dashboard .card-footer {
        background: transparent !important;
        border-color: #242435 !important;
    }

    .bakiye-dashboard .card-title,
    .bakiye-dashboard .card-label,
    .bakiye-dashboard .text-gray-900,
    .bakiye-dashboard .text-gray-800 {
        color: var(--playsel-text) !important;
    }

    .bakiye-dashboard .text-muted,
    .bakiye-dashboard .form-label {
        color: var(--playsel-muted) !important;
    }

    .bakiye-dashboard .kpi-icon {
        background: rgba(255, 181, 17, .12) !important;
        color: var(--playsel-gold) !important;
    }

    .bakiye-dashboard .kpi-success .kpi-icon {
        background: rgba(73, 209, 154, .12) !important;
        color: #49d19a !important;
    }

    .bakiye-dashboard .kpi-danger .kpi-icon {
        background: rgba(250, 80, 123, .12) !important;
        color: #fa507b !important;
    }

    .bakiye-dashboard .balance-tabs {
        gap: .4rem;
        padding: .4rem;
        background: var(--playsel-card);
        border: 1px solid #252538;
        border-radius: 12px;
    }

    .bakiye-dashboard .balance-tabs .nav-link,
    .bakiye-dashboard .balance-tabs .nav-link:hover {
        min-width: 0;
        padding: .7rem .9rem;
        border: 1px solid transparent !important;
        border-radius: 7px;
        background: transparent !important;
        color: #9494ac !important;
    }

    .bakiye-dashboard .balance-tabs .nav-link.active,
    .bakiye-dashboard .balance-tabs .nav-link.active:hover {
        background: #28283b !important;
        border-left: 2px solid var(--playsel-gold) !important;
        border-color: #28283b !important;
        color: #fff !important;
        box-shadow: none;
    }

    .bakiye-dashboard .filter-shell {
        padding: 1rem;
        background: var(--playsel-surface) !important;
        border: 1px solid var(--playsel-line) !important;
        border-radius: 8px;
    }

    .bakiye-dashboard .form-control,
    .bakiye-dashboard .form-select,
    .bakiye-dashboard .form-control-solid,
    .bakiye-dashboard .form-select-solid {
        min-height: 35px;
        background: #1c1c2a !important;
        border: 1px solid #2b2b3b !important;
        color: #d7d7e2 !important;
        border-radius: 5px;
    }

    .bakiye-dashboard .form-control:focus,
    .bakiye-dashboard .form-select:focus {
        border-color: #a57c20 !important;
        box-shadow: none !important;
    }

    .bakiye-dashboard .table-responsive {
        overflow-x: auto;
    }

    .bakiye-dashboard .table {
        --bs-table-bg: transparent;
        --bs-table-color: #c6c6d3;
        --bs-table-border-color: #2b2b3d;
    }

    .bakiye-dashboard .table thead th {
        color: #c5c5d3 !important;
        border-bottom: 1px dotted #303045 !important;
        white-space: nowrap;
    }

    .bakiye-dashboard .table tbody td {
        color: #c6c6d3 !important;
        border-bottom: 1px dotted #2b2b3d !important;
        vertical-align: middle;
    }

    .bakiye-dashboard .table tbody tr:hover {
        background: #212133 !important;
    }

    .bakiye-dashboard .btn-light,
    .bakiye-dashboard .btn-light-primary,
    .bakiye-dashboard .btn-light-warning,
    .bakiye-dashboard .btn-light-danger,
    .bakiye-dashboard .btn-light-success {
        background: #29293c !important;
        border-color: #333348 !important;
        color: #bdbdd0 !important;
    }

    .bakiye-dashboard .btn-primary {
        background: var(--playsel-gold) !important;
        border-color: var(--playsel-gold) !important;
        color: #17131a !important;
    }

    @media (max-width: 767.98px) {
        .bakiye-dashboard .balance-tabs .nav-link {
            flex: 0 0 auto;
        }
    }

    .bakiye-dashboard .pay-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 18px;
    }

    .bakiye-dashboard .pay-title h1 {
        color: #f3f3f7;
        font-size: 19px;
        margin: 0 0 4px;
    }

    .bakiye-dashboard .pay-title p,
    .bakiye-dashboard .pay-panel-top p {
        color: #9292a9;
        margin: 0;
    }

    .bakiye-dashboard .pay-tabs {
        display: flex;
        gap: 5px;
        border-bottom: 1px solid #2d2d40;
        margin-bottom: 18px;
    }

    .bakiye-dashboard .pay-tab {
        display: inline-block;
        padding: 12px 18px;
        border: 0;
        border-bottom: 2px solid transparent;
        background: transparent;
        color: #9c9cb1;
        font-weight: 600;
        text-decoration: none;
    }

    .bakiye-dashboard .pay-tab.active,
    .bakiye-dashboard .pay-tab:hover {
        color: #ffb511;
        border-bottom-color: #ffb511;
    }

    .bakiye-dashboard .pay-panel {
        overflow: hidden;
        background: #171723 !important;
        border: 1px solid #2d2d40 !important;
        border-radius: 11px !important;
    }

    .bakiye-dashboard .pay-panel-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 18px;
        border-bottom: 1px solid #2d2d40;
    }

    .bakiye-dashboard .pay-panel-top h2 {
        color: #f3f3f7;
        font-size: 14px;
        margin: 0;
    }

    .bakiye-dashboard .pay-filters {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        align-items: end;
        gap: 10px;
        padding: 15px 18px;
        border-bottom: 1px solid #2d2d40;
    }

    .bakiye-dashboard .pay-filters label {
        display: flex;
        flex-direction: column;
        gap: 5px;
        color: #9696aa;
        font-size: 10px;
    }

    .bakiye-dashboard .pay-filters input,
    .bakiye-dashboard .pay-filters select,
    .bakiye-dashboard .pay-btn {
        min-width: 0;
        min-height: 35px;
        background: #20202e !important;
        border: 1px solid #343448 !important;
        border-radius: 6px;
        color: #f1f1f7 !important;
        padding: 9px 11px;
    }

    .bakiye-dashboard .pay-btn {
        cursor: pointer;
        font-weight: 700;
        white-space: nowrap;
    }

    .bakiye-dashboard .pay-btn.secondary {
        color: #d5d5e0 !important;
    }

    .bakiye-dashboard .pay-btn:hover,
    .bakiye-dashboard .pay-filters input:focus,
    .bakiye-dashboard .pay-filters select:focus {
        border-color: #ffb511 !important;
        outline: none;
    }

    .bakiye-dashboard .pay-tablewrap {
        overflow-x: auto;
    }

    .bakiye-dashboard .pay-table {
        width: 100%;
        min-width: 1130px;
        border-collapse: collapse;
        table-layout: auto;
    }

    .bakiye-dashboard .pay-table th {
        padding: 13px;
        color: #9393aa !important;
        font-size: 10px;
        font-weight: 600;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid #303043 !important;
    }

    .bakiye-dashboard .pay-table td {
        padding: 14px 13px;
        color: #d4d4e1 !important;
        white-space: nowrap;
        border-bottom: 1px solid #29293a !important;
    }

    .bakiye-dashboard .pay-table tbody tr:hover {
        background: #202031 !important;
    }

    .bakiye-dashboard .pay-table a {
        color: #f7b621;
        text-decoration: none;
    }

    .bakiye-dashboard .pay-sub {
        display: block;
        margin-top: 3px;
        color: #87879f;
        font-size: 10px;
    }

    .bakiye-dashboard .pay-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 5px;
        background: #28283a;
        color: #c9c9d9;
        font-size: 10px;
        font-weight: 600;
    }

    .bakiye-dashboard .pay-chip.blue {
        background: #1c3043;
        color: #79c8ff;
    }

    .bakiye-dashboard .pay-action {
        display: inline-block;
        padding: 7px 12px;
        border: 1px solid #3b3b50;
        border-radius: 5px;
        background: #29293b;
        color: #dedee9 !important;
    }

    .bakiye-dashboard .pay-action:hover {
        border-color: #ffb511;
        color: #ffb511 !important;
    }

    .bakiye-dashboard .pay-pagination {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        color: #9292aa;
    }

    .bakiye-dashboard { font-size: 11px; }
    .bakiye-dashboard .pay-amount-filter { display: grid; grid-template-columns: 44% 1fr; gap: 6px; }
    .bakiye-dashboard .pay-chip.ok { background: #16382e; color: #58d6a0; }
    .bakiye-dashboard .pay-chip.bad { background: #3b202e; color: #ff6d91; }
    .bakiye-dashboard .pay-chip.wait { background: #3b3119; color: #ffc349; }
    .bakiye-dashboard .pay-link { color: #ffb511 !important; font-weight: 700; }
    .bakiye-dashboard .pay-customer { color: #f0f0f6 !important; font-weight: 600; }
    .bakiye-dashboard .pay-table th:last-child,
    .bakiye-dashboard .pay-table td:last-child { text-align: center; min-width: 105px; }
    .bakiye-dashboard .pay-chevron {
        width: 33px; height: 30px; border: 1px solid #3b3b51; border-radius: 7px;
        background: #252538; color: #ffb511; cursor: pointer; line-height: 1;
    }
    .bakiye-dashboard .pay-chevron i { transition: transform .15s; }
    .bakiye-dashboard .pay-chevron[aria-expanded="true"] i { transform: rotate(180deg); }
    .bakiye-dashboard .pay-table tr.pay-selected,
    .bakiye-dashboard .pay-table tr.pay-selected:hover { background: #252333 !important; }
    .bakiye-dashboard .pay-table tr.pay-expand > td { padding: 0; background: #1a1a29; text-align: left; min-width: 0; }
    .bakiye-dashboard .pay-expandinner {
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
        margin: 10px 16px; padding: 13px 16px; border: 1px solid #3c3541; border-radius: 8px; background: #20202e;
    }
    .bakiye-dashboard .pay-expandinner strong { color: #f0f0f6; }
    .bakiye-dashboard .pay-expandactions { display: flex; gap: 8px; flex-wrap: wrap; }
    .bakiye-dashboard .pay-ghost {
        padding: 7px 12px; border: 1px solid #ffb511; border-radius: 6px; background: transparent;
        color: #ffb511; font-weight: 700; cursor: pointer;
    }
    .bakiye-dashboard .pay-ghost.solid { background: #ffb511; color: #17131a; }
    #paymentDetailModal .modal-content { background: #171723; border: 1px solid #2d2d40 !important; color: #d4d4e1; }
    #paymentDetailModal .classic-block { margin-bottom: 12px; border: 1px solid #2d2d40; border-radius: 9px; overflow: hidden; }
    #paymentDetailModal .classic-head { padding: 10px 14px; background: #1d1d2b; color: #ffb511; font-weight: 700; }
    #paymentDetailModal .classic-pair { display: flex; justify-content: space-between; gap: 12px; padding: 9px 14px; border-top: 1px solid #29293a; }
    #paymentDetailModal .classic-pair span { color: #9696aa; }
    #paymentDetailModal .classic-pair b { color: #f0f0f6; text-align: right; word-break: break-word; }
    #paymentDetailModal .classic-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 700px) { #paymentDetailModal .classic-grid { grid-template-columns: 1fr; } }

    @media (max-width: 1150px) {
        .bakiye-dashboard .pay-filters {
            grid-template-columns: repeat(3, minmax(135px, 1fr));
        }
    }

    @media (max-width: 900px) {
        .bakiye-dashboard .pay-filters {
            grid-template-columns: repeat(2, minmax(135px, 1fr));
        }
    }

    @media (max-width: 600px) {
        .bakiye-dashboard .pay-title {
            align-items: flex-start;
            flex-direction: column;
        }

        .bakiye-dashboard .pay-filters {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="bakiye-dashboard">
    <div class="pay-title">
        <div>
            <h1>Ödemeler</h1>
            <p>Ödeme Yönetimi / Müşteri Ödemeleri</p>
        </div>
        <button type="button" class="pay-btn secondary" id="clearAllPaymentFilters">↻ Filtreleri Sıfırla</button>
    </div>

    <div class="pay-tabs" id="balanceTabs" role="tablist">
        <button class="pay-tab active" id="all-payments-tab" data-bs-toggle="tab" data-bs-target="#all-payments" type="button" role="tab" aria-controls="all-payments" aria-selected="true">▤ &nbsp; Ödemeler</button>
        <button class="pay-tab" id="vomsis-transactions-tab" data-bs-toggle="tab" data-bs-target="#vomsis-transactions" type="button" role="tab" aria-controls="vomsis-transactions" aria-selected="false">▣ &nbsp; Vomsis</button>
        <a class="pay-tab" href="<?= site_url('balance/refunds') ?>">↶ &nbsp; İade Bekleyen İşlemler</a>
    </div>

    <div class="tab-content" id="balanceTabContent">
    <div id="all-payments" class="tab-pane fade show active pay-panel" role="tabpanel" aria-labelledby="all-payments-tab">
        <div class="pay-panel-top">
            <div>
                <h2>Tüm Ödemeler</h2>
                <p>Kart, havale ve bakiye yükleme işlemleri</p>
            </div>
            <span class="pay-chip blue" id="allPaymentCount">0 kayıt</span>
        </div>
        <div class="pay-filters all-payment-filters">
            <label>Ödeme No / Referans
                <input type="search" id="allPaymentSearch" placeholder="Ödeme no, POS ID, referans...">
            </label>
            <label>Müşteri
                <input type="search" id="allPaymentCustomerSearch" placeholder="Ad, e-posta, telefon..." aria-label="Müşteri">
            </label>
            <label>Ödeme Yöntemi
                <select id="allPaymentType"><option value="">Tüm yöntemler</option><option value="2">Cüzdan</option><option value="3">Havale / EFT</option><option value="4">Kredi Kartı</option><option value="5">Mobil</option></select>
            </label>
            <label>Durum
                <select id="allPaymentStatus"><option value="">Tüm durumlar</option><option value="ok">Başarılı</option><option value="wait">Bekleyen</option><option value="bad">Başarısız / İptal</option><option value="refund">İade</option></select>
            </label>
            <label>Tutar Filtresi
                <span class="pay-amount-filter">
                    <select id="allPaymentAmountOp" aria-label="Tutar operatörü"><option value="">Seçiniz</option><option value="gt">Büyüktür</option><option value="lt">Küçüktür</option><option value="eq">Eşittir</option></select>
                    <input type="number" id="allPaymentAmount" min="0" step="0.01" placeholder="0,00" aria-label="Tutar">
                </span>
            </label>
            <label>Başlangıç
                <input type="date" id="allPaymentStartDate">
            </label>
            <label>Bitiş
                <input type="date" id="allPaymentEndDate">
            </label>
            <label>Ödeme Kaynağı
                <select id="allPaymentSource"><option value="">Tüm kaynaklar</option><option value="balance">Bakiye Yükleme</option><option value="order">Sipariş Ödemesi</option></select>
            </label>
            <label>&nbsp;
                <button type="button" class="pay-btn secondary" id="applyAllPaymentFilters">⌕ Ara</button>
            </label>
        </div>
        <div class="pay-tablewrap">
            <table id="allPaymentsTable" class="pay-table">
                <thead><tr><th>Ödeme No</th><th>Müşteri</th><th>Ödeme Yöntemi</th><th>Tutar</th><th>Ödenen</th><th>Referans / POS ID</th><th>Durum</th><th>Oluşturulma</th><th>İşlem</th></tr></thead>
            </table>
        </div>
        <div class="pay-pagination"><span id="payTotal">0 kayıt gösteriliyor</span><span>Aktif kayıtlar · Playsel Admin arayüzü</span></div>
    </div>

    <div id="vomsis-transactions" class="tab-pane fade pay-panel" role="tabpanel" aria-labelledby="vomsis-transactions-tab">
        <div class="pay-panel-top">
            <div>
                <h2>Vomsis · Banka Bildirimleri</h2>
                <p>Şirket banka hesaplarına gelen ödemeler ve eşleşme kayıtları</p>
            </div>
        </div>
        <div class="pay-filters">
            <label>İşlem no / açıklama / banka<input type="search" id="vomsisSearch" placeholder="Arama yapın"></label>
            <label>Eşleşme durumu<select id="vomsisMatch"><option value="">Tümü</option><option value="unmatched">Eşleşmemiş</option><option value="balance">Bakiye ile eşleşen</option><option value="order">Sipariş ile eşleşen</option></select></label>
            <label>Banka<input type="search" id="vomsisBank" placeholder="Banka adı"></label>
            <label>Başlangıç<input type="date" id="vomsisStartDate"></label>
            <label>Bitiş<input type="date" id="vomsisEndDate"></label>
            <label>&nbsp;<button type="button" class="pay-btn secondary" id="clearVomsisFilters">↻ Temizle</button></label>
        </div>
        <div class="pay-tablewrap">
            <table id="vomsisTransactionsTable" class="pay-table">
                <thead><tr><th>İşlem No</th><th>Açıklama</th><th>Banka</th><th>Tutar</th><th>Tarih</th><th>Eşleşme</th><th>İşlem</th></tr></thead>
            </table>
        </div>
    </div>

</div>

<div class="modal fade" id="paymentDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-sm border-0 rounded-4">
            <div class="modal-header border-0">
                <div><div class="text-muted fs-7 text-uppercase">Ödeme Detayı</div><h3 class="modal-title fw-bold" id="paymentDetailTitle">-</h3></div>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal" aria-label="Kapat"><i class="ki-outline ki-cross fs-2"></i></button>
            </div>
            <div class="modal-body pt-0" id="paymentDetailBody"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="orderStatusChangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content shadow-sm border-0 rounded-4">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <div class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal"><i class="ki-outline ki-cross fs-1"></i></div>
            </div>
            <div class="modal-body text-center pt-0 pb-10 px-10">
                <div class="mb-5"><i class="ki-outline ki-information-5 text-primary fs-5x"></i></div>
                <h3 class="fw-bold text-gray-900 mb-2">Bakiye Onayı</h3>
                <p class="text-muted fs-6 mb-6">Müşterinin bakiye yükleme talebini onaylamak veya reddetmek için bir durum seçin.</p>

                <input type="hidden" id="order-id">
                <select id="orderStatus" class="form-select form-select-solid form-select-lg mb-8" data-control="select2" data-hide-search="true" data-placeholder="Lütfen Bir Durum Seçin">
                    <option value=""></option>
                    <option value="1">Onayla (Bakiyeyi Hesaba Yükle)</option>
                    <option value="2">Reddet (Talebi İptal Et)</option>
                </select>

                <div class="d-flex flex-column gap-3">
                    <button type="button" class="btn btn-primary btn-lg w-100" id="changeStatusBtn">
                        <span class="indicator-label">İşlemi Tamamla</span>
                        <span class="indicator-progress">Lütfen Bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                    <button type="button" class="btn btn-light btn-lg w-100" data-bs-dismiss="modal">İptal</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="vomsisMatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content shadow-sm border-0 rounded-4">
            <div class="modal-header border-0"><h3 class="modal-title">Vomsis ödemesini eşleştir</h3><button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal" aria-label="Kapat"><i class="ki-outline ki-cross fs-2"></i></button></div>
            <div class="modal-body">
                <div class="alert alert-light-primary d-flex justify-content-between align-items-start gap-4 flex-wrap"><div><div class="fw-bold" id="vomsisMatchPaymentId">-</div><div id="vomsisMatchDescription" class="text-muted"></div></div><div class="fw-bold fs-4" id="vomsisMatchAmount">-</div></div>
                <input type="hidden" id="vomsisMatchId">
                <div class="d-flex gap-3 align-items-center mb-5 flex-wrap">
                    <input type="email" class="form-control form-control-solid w-100 w-md-350px" id="vomsisMatchEmail" placeholder="Müşteri e-posta adresi ile ara" autocomplete="off">
                    <button type="button" class="btn btn-primary" id="vomsisMatchEmailSearch">E-posta ile ara</button>
                    <button type="button" class="btn btn-light" id="vomsisMatchEmailClear">Temizle</button>
                </div>
                <div class="row g-5">
                    <div class="col-lg-6"><div class="border rounded p-4 h-100"><div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0">Bakiye talepleri</h4><span class="badge badge-light-primary" id="vomsisBalanceCandidateCount">0</span></div><div id="vomsisBalanceCandidates"><div class="text-muted text-center py-6">Adaylar aranıyor...</div></div></div></div>
                    <div class="col-lg-6"><div class="border rounded p-4 h-100"><div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0">Havale siparişleri</h4><span class="badge badge-light-primary" id="vomsisOrderCandidateCount">0</span></div><div id="vomsisOrderCandidates"><div class="text-muted text-center py-6">Adaylar aranıyor...</div></div></div></div>
                </div>
                <div class="border rounded p-4 mt-5">
                    <h4 class="mb-1">Bildirimi olmayan ödeme</h4>
                    <div class="text-muted fs-7 mb-4">Müşteri ödeme bildirimi yapmadıysa bu Vomsis ödemesini doğrudan kullanıcının bakiyesine ekleyin. Aynı tutarlı bekleyen bildirimi olan kullanıcıya doğrudan ekleme yapılamaz; o bildirimle eşleştirin.</div>
                    <div class="d-flex gap-3 align-items-center mb-4 flex-wrap">
                        <input type="text" class="form-control form-control-solid w-100 w-md-350px" id="vomsisCreditSearch" placeholder="E-posta, ad, telefon veya referans no" autocomplete="off">
                        <button type="button" class="btn btn-light-primary" id="vomsisCreditSearchBtn">Müşteri ara</button>
                    </div>
                    <div id="vomsisCreditResults"></div>
                </div>
            </div>
            <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button></div>
        </div>
    </div>
</div>

<script>
    // DataTable içindeki onaylama butonundan çağrılan fonksiyon
    function changeStatus(orderID) {
        $('#order-id').val(orderID);
        $('#orderStatus').val('').trigger('change');
        $('#orderStatusChangeModal').modal('show');
    }

    $(document).ready(function() {

        function escapePaymentHtml(value) {
            return $('<div>').text(value == null || value === '' ? '-' : value).html();
        }

        $(document).on('click', '.payment-detail-link', function(event) {
            event.preventDefault();
            var url = $(this).data('detail-url');
            $('#paymentDetailTitle').text('Yükleniyor...');
            $('#paymentDetailBody').html('<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>');
            $('#paymentDetailModal').modal('show');
            $.getJSON(url + (url.indexOf('?') === -1 ? '?' : '&') + 'format=json')
                .done(function(payment) {
                    var pair = function(label, value) { return '<div class="classic-pair"><span>' + label + '</span><b>' + escapePaymentHtml(value) + '</b></div>'; };
                    var pairIf = function(label, value) { return (value == null || value === '' || value === '-' || value === '0') ? '' : pair(label, value); };
                    $('#paymentDetailTitle').text('#' + (payment.payment_transaction_id || '-'));
                    $('#paymentDetailBody').html(
                        '<div class="classic-grid">' +
                        '<div class="classic-block"><div class="classic-head"><i class="fa-solid fa-receipt me-2"></i>İşlem Bilgileri</div>' +
                        pair('Kaynak', payment.payment_source) +
                        pairIf('Ödeme Yöntemi', payment.payment_method) +
                        pairIf('Sipariş No', payment.payment_order_id ? '#' + payment.payment_order_id : '') +
                        pair('Tutar', Number(payment.payment_amount || 0).toFixed(2) + ' TL') +
                        pair('Durum', payment.payment_status) +
                        pairIf('Referans', payment.payment_reference) +
                        pairIf('Banka', payment.payment_bank) +
                        pairIf('Alıcı', payment.payment_bank_owner) +
                        pairIf('IBAN', payment.payment_iban) +
                        pairIf('Oluşturulma', payment.payment_created_at) +
                        pairIf('Onay Tarihi', payment.payment_approved_at) +
                        pairIf('Onaylayan', payment.payment_approved_by) +
                        pairIf('Vomsis İşlem No', payment.payment_vomsis_id) + '</div>' +
                        '<div class="classic-block"><div class="classic-head"><i class="fa-solid fa-user-shield me-2"></i>Müşteri</div>' +
                        pair('Müşteri', payment.user_name) +
                        pairIf('E-posta', payment.user_email) +
                        pairIf('Telefon', payment.user_phone) +
                        pairIf('IP', payment.payment_ip) +
                        pairIf('Port', payment.payment_port) +
                        pairIf('Onay IP', payment.payment_approval_ip) +
                        pairIf('Onay Port', payment.payment_approval_port) +
                        pairIf('Kart', payment.payment_card) +
                        pairIf('Kart sahibi', payment.payment_cardholder) +
                        pairIf('Geçerlilik', payment.payment_card_expiry) +
                        pairIf('Gönderen', payment.payment_sender) + '</div></div>' +
                        (payment.payment_note && payment.payment_note !== '-' ? '<div class="classic-block"><div class="classic-head">Not</div><div class="classic-pair"><b>' + escapePaymentHtml(payment.payment_note) + '</b></div></div>' : '') +
                        (payment.order_items && payment.order_items.length ?
                            '<div class="classic-block"><div class="classic-head"><i class="fa-solid fa-box me-2"></i>Sipariş Detayları' + (payment.order_bundle_name ? ' · ' + escapePaymentHtml(payment.order_bundle_name) : '') + '</div>' +
                            pair('Sipariş Durumu', payment.order_status_label) +
                            payment.order_items.map(function(item) {
                                var req = Object.keys(item.require || {}).map(function(k) { return k + ': ' + item.require[k]; }).join(' · ');
                                return pair('#' + item.order_id + ' · ' + (item.product || '-') + ' × ' + item.quantity,
                                    Number(item.amount || 0).toFixed(2) + ' TL · ' + item.status + (req ? ' · ' + req : '') + (item.supplier_order_id ? ' · Tedarikçi #' + item.supplier_order_id : ''));
                            }).join('') + '</div>'
                            : '') +
                        (payment.payment_refundable ?
                            '<div class="classic-block"><div class="classic-head">İade</div><form id="paymentRefundForm" class="p-4">' +
                            '<div class="text-muted fs-7 mb-3">Kalan iade edilebilir tutar: ' + Number(payment.payment_refund_remaining || 0).toFixed(2) + ' TL</div>' +
                            '<input type="hidden" name="source" value="' + escapePaymentHtml(payment.payment_source_key) + '"><input type="hidden" name="payment_id" value="' + Number(payment.payment_source_id) + '">' +
                            '<div class="row g-3"><div class="col-md-4"><input name="amount" type="number" step="0.01" min="0.01" max="' + Number(payment.payment_refund_remaining || 0).toFixed(2) + '" value="' + Number(payment.payment_refund_remaining || 0).toFixed(2) + '" class="form-control" required></div>' +
                            '<div class="col-md-5"><input name="reason" class="form-control" placeholder="İade nedeni"></div>' +
                            '<div class="col-md-3"><button class="btn btn-danger w-100">İade Başlat</button></div></div></form></div>'
                            : '')
                    );
                    $('#paymentRefundForm').on('submit', function(e) {
                        e.preventDefault();
                        var form = $(this), button = form.find('button').prop('disabled', true);
                        $.post('<?= site_url('balance/startRefund') ?>', form.serialize(), null, 'json')
                            .done(function(r) { toastr[r.status === 'success' ? 'success' : 'error'](r.message); if (r.status === 'success') { $('#paymentDetailModal').modal('hide'); window.location.href = '<?= site_url('balance/refunds') ?>'; } })
                            .fail(function(xhr) { toastr.error((xhr.responseJSON || {}).message || 'İade başlatılamadı.'); })
                            .always(function() { button.prop('disabled', false); });
                    });
                })
                .fail(function() {
                    $('#paymentDetailTitle').text('Ödeme detayı');
                    $('#paymentDetailBody').html('<div class="alert alert-danger mb-0">Ödeme detayı alınamadı.</div>');
                });
        });

        // --- DATA TABLES PREMIUM ŞABLON (Code review edildi) ---
        const dtConfig = {
            processing: true,
            serverSide: true,
            ordering: false,
            searching: false,
            autoWidth: false,
            language: {
                url: "//cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json",
                // Gelişmiş Skeleton/Spinner Loader (İşleniyor yazısı kalktı)
                processing: `
                    <div class="d-flex flex-column align-items-center justify-content-center py-10 bg-body rounded shadow-sm">
                        <div class="spinner-border text-primary border-4" style="width: 3rem; height: 3rem;" role="status"></div>
                        <div class="text-muted fw-semibold mt-3 fs-6">Sunucudan veriler çekiliyor...</div>
                    </div>`,
                // Boş Durum (Empty State) Gösterimi
                emptyTable: `
                    <div class="table-empty text-center">
                        <div class="fw-bolder fs-4 text-gray-900 mb-1">Kayıt Yok</div>
                        <div class="text-muted fs-6">Gösterilecek kayıt bulunmuyor.</div>
                    </div>`,
                // Arama sonucu bulamama durumu
                zeroRecords: `
                    <div class="table-empty text-center">
                        <div class="fw-bolder fs-4 text-gray-900 mb-1">Sonuç Bulunamadı</div>
                        <div class="text-muted fs-6">Filtre kriterlerinize uyan kayıt yok.</div>
                    </div>`
            }
        };

        var allPaymentsTable = $('#allPaymentsTable').DataTable({
            ...dtConfig,
            pageLength: 20,
            drawCallback: function(settings) {
                var total = settings.json ? Number(settings.json.recordsTotal || 0) : 0;
                $('#allPaymentCount').text(total + ' kayıt');
                $('#payTotal').text(total + ' kayıt, ' + this.api().rows({ page: 'current' }).count() + ' tanesi gösteriliyor');
            },
            ajax: {
                url: "<?= site_url('balance/allPaymentsAjax') ?>",
                type: "POST",
                data: function(d) {
                    d.payment_status = $('#allPaymentStatus').val();
                    d.amount_op = $('#allPaymentAmountOp').val();
                    d.amount = $('#allPaymentAmount').val();
                    d.payment_search = $('#allPaymentSearch').val();
                    d.payment_customer_search = $('#allPaymentCustomerSearch').val();
                    d.payment_source = $('#allPaymentSource').val();
                    d.payment_type = $('#allPaymentType').val();
                    d.startDate = $('#allPaymentStartDate').val();
                    d.endDate = $('#allPaymentEndDate').val();
                }
            }
        });

        var vomsisTransactionsTable = $('#vomsisTransactionsTable').DataTable({
            ...dtConfig,
            pageLength: 20,
            ajax: {
                url: "<?= site_url('balance/vomsisTransactionsAjax') ?>",
                type: "POST",
                data: function(d) {
                    d.vomsis_search = $('#vomsisSearch').val();
                    d.vomsis_match = $('#vomsisMatch').val();
                    d.vomsis_bank = $('#vomsisBank').val();
                    d.startDate = $('#vomsisStartDate').val();
                    d.endDate = $('#vomsisEndDate').val();
                }
            }
        });

        function reloadVomsisTransactions() { vomsisTransactionsTable.ajax.reload(null, false); }
        $('#vomsisSearch, #vomsisBank').on('keydown', function(event) {
            if (event.key === 'Enter') { event.preventDefault(); reloadVomsisTransactions(); }
        });
        $('#vomsisMatch, #vomsisStartDate, #vomsisEndDate').on('change', reloadVomsisTransactions);
        $('#clearVomsisFilters').on('click', function() {
            $('#vomsisSearch, #vomsisBank, #vomsisStartDate, #vomsisEndDate').val('');
            $('#vomsisMatch').val('');
            reloadVomsisTransactions();
        });

        function escapeVomsisText(value) { return $('<div>').text(value == null || value === '' ? '-' : value).html(); }
        function candidateCard(candidate, target) {
            var scoreClass = candidate.score >= 150 ? 'success' : (candidate.score >= 100 ? 'primary' : 'warning');
            var reference = candidate.reference ? '<span class="text-muted">Ref: ' + escapeVomsisText(candidate.reference) + '</span>' : '';
            var bank = candidate.bank ? '<span class="text-muted">' + escapeVomsisText(candidate.bank) + '</span>' : '';
            var badges = (candidate.reference_match ? '<span class="badge badge-light-success me-1">Referans açıklamada</span>' : '') + (candidate.email_match ? '<span class="badge badge-light-success me-1">E-posta eşleşti</span>' : '');
            return '<div class="border rounded p-3 mb-3 d-flex justify-content-between align-items-center gap-3">' +
                '<div class="min-w-0"><div class="fw-bold text-truncate">#' + candidate.id + ' · ' + escapeVomsisText(candidate.user_name) + '</div>' +
                (badges ? '<div class="mb-1">' + badges + '</div>' : '') +
                '<div class="fs-7 text-muted">' + escapeVomsisText(candidate.user_email || 'E-posta yok') + '</div>' +
                '<div class="d-flex gap-3 flex-wrap fs-7">' + reference + bank + '<span class="text-muted">' + escapeVomsisText(candidate.created_at) + '</span></div></div>' +
                '<div class="text-end flex-shrink-0"><div class="fw-bold mb-2">' + Number(candidate.amount || 0).toFixed(2) + ' TL</div><button type="button" class="btn btn-sm btn-light-' + scoreClass + ' js-vomsis-confirm" data-target="' + target + '" data-target-id="' + candidate.id + '">Eşleştir</button></div></div>';
        }
        var vomsisMatchContext = null;
        function requestVomsisCandidates() {
            if (!vomsisMatchContext) return;
            $('#vomsisBalanceCandidates, #vomsisOrderCandidates').html('<div class="text-muted text-center py-6"><span class="spinner-border spinner-border-sm me-2"></span>Adaylar aranıyor...</div>');
            $.post('<?= site_url('balance/vomsisMatchCandidates') ?>', $.extend({}, vomsisMatchContext, { email: $('#vomsisMatchEmail').val().trim() }))
                .done(function(response) {
                    if (response.status !== 'success') {
                        toastr.error(response.message || 'Adaylar alınamadı.');
                        $('#vomsisBalanceCandidates, #vomsisOrderCandidates').html('<div class="text-muted text-center py-6">Adaylar alınamadı.</div>');
                        return;
                    }
                    var balances = response.balances || [], orders = response.orders || [];
                    $('#vomsisBalanceCandidateCount').text(balances.length); $('#vomsisOrderCandidateCount').text(orders.length);
                    $('#vomsisBalanceCandidates').html(balances.length ? balances.map(function(item) { return candidateCard(item, 'balance'); }).join('') : '<div class="text-muted text-center py-6">Uygun bakiye talebi bulunamadı.</div>');
                    $('#vomsisOrderCandidates').html(orders.length ? orders.map(function(item) { return candidateCard(item, 'order'); }).join('') : '<div class="text-muted text-center py-6">Uygun havale siparişi bulunamadı.</div>');
                }).fail(function() { toastr.error('Aday kayıtlar alınamadı.'); });
        }
        function loadVomsisCandidates(button) {
            var paymentId = button.data('payment-id');
            $('#vomsisMatchId').val(paymentId);
            $('#vomsisMatchEmail').val('');
            $('#vomsisMatchPaymentId').text('İşlem #' + paymentId + ' · ' + (button.data('bank') || '-'));
            $('#vomsisMatchDescription').text(button.data('description') || '-');
            $('#vomsisMatchAmount').text(Number(button.data('amount') || 0).toFixed(2) + ' TL');
            vomsisMatchContext = { payment_id: paymentId, description: button.data('description'), amount: button.data('amount'), bank: button.data('bank'), payment_date: button.data('date') };
            $('#vomsisCreditSearch').val(''); $('#vomsisCreditResults').empty();
            requestVomsisCandidates();
            $('#vomsisMatchModal').modal('show');
        }
        $('#vomsisMatchEmailSearch').on('click', requestVomsisCandidates);
        $('#vomsisMatchEmail').on('keydown', function(event) { if (event.key === 'Enter') { event.preventDefault(); requestVomsisCandidates(); } });
        $('#vomsisMatchEmailClear').on('click', function() { $('#vomsisMatchEmail').val(''); requestVomsisCandidates(); });

        // Bildirimsiz Vomsis ödemesini doğrudan kullanıcı bakiyesine ekleme
        function searchVomsisCreditUsers() {
            var term = $('#vomsisCreditSearch').val().trim();
            if (term.length < 3) { toastr.warning('En az 3 karakter girin.'); return; }
            $('#vomsisCreditResults').html('<div class="text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Aranıyor...</div>');
            $.post('<?= site_url('balance/vomsisUserSearch') ?>', { term: term }, null, 'json').done(function(r) {
                if (r.status !== 'success') { $('#vomsisCreditResults').html(''); toastr.error(r.message || 'Arama başarısız.'); return; }
                var users = r.users || [];
                if (!users.length) { $('#vomsisCreditResults').html('<div class="text-muted">Müşteri bulunamadı.</div>'); return; }
                $('#vomsisCreditResults').html(users.map(function(u) {
                    var pending = (u.pending_requests || []).map(function(p) { return '#' + p.id + ' · ' + Number(p.amount).toFixed(2) + ' TL'; }).join(', ');
                    return '<div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center gap-3"><div class="min-w-0">' +
                        '<div class="fw-bold">#' + u.id + ' · ' + escapeVomsisText(u.name) + (u.active ? '' : ' <span class="badge badge-light-danger">Pasif</span>') + '</div>' +
                        '<div class="fs-7 text-muted">' + escapeVomsisText(u.email) + ' · ' + escapeVomsisText(u.phone) + '</div>' +
                        (pending ? '<div class="fs-7 text-warning">Bekleyen bildirim: ' + escapeVomsisText(pending) + '</div>' : '') +
                        '</div><button type="button" class="btn btn-sm btn-light-success js-vomsis-credit" data-user-id="' + u.id + '" data-user-name="' + escapeVomsisText(u.name) + '"' + (u.active ? '' : ' disabled') + '>Bakiyeye ekle</button></div>';
                }).join(''));
            }).fail(function() { $('#vomsisCreditResults').html(''); toastr.error('Arama yapılamadı.'); });
        }
        $('#vomsisCreditSearchBtn').on('click', searchVomsisCreditUsers);
        $('#vomsisCreditSearch').on('keydown', function(event) { if (event.key === 'Enter') { event.preventDefault(); searchVomsisCreditUsers(); } });
        $(document).on('click', '.js-vomsis-credit', function() {
            var button = $(this);
            if (!vomsisMatchContext) return;
            var amountText = Number(vomsisMatchContext.amount || 0).toFixed(2) + ' TL';
            if (!confirm('Vomsis #' + vomsisMatchContext.payment_id + ' (' + amountText + ') ödemesi ' + button.data('user-name') + ' kullanıcısının bakiyesine eklenecek. Bu işlem geri alınamaz. Onaylıyor musunuz?')) return;
            button.prop('disabled', true);
            $.post('<?= site_url('balance/creditVomsisToUser') ?>', { payment_id: vomsisMatchContext.payment_id, payment_date: vomsisMatchContext.payment_date, user_id: button.data('user-id') }, null, 'json')
                .done(function(r) {
                    if (r.status === 'success') { toastr.success(r.message); $('#vomsisMatchModal').modal('hide'); reloadVomsisTransactions(); }
                    else { toastr.error(r.message || 'Bakiye eklenemedi.'); button.prop('disabled', false); }
                })
                .fail(function(xhr) { toastr.error((xhr.responseJSON || {}).message || 'Sunucu ile iletişim kurulamadı.'); button.prop('disabled', false); });
        });

        $(document).on('click', '.vomsis-match-btn', function() { loadVomsisCandidates($(this)); });
        $(document).on('click', '.js-vomsis-confirm', function() {
            var button = $(this);
            button.prop('disabled', true);
            $.post('<?= site_url('balance/matchVomsisPayment') ?>', {
                payment_id: $('#vomsisMatchId').val(),
                payment_date: vomsisMatchContext ? vomsisMatchContext.payment_date : '',
                target: button.data('target'),
                target_id: button.data('target-id')
            }).done(function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                    $('#vomsisMatchModal').modal('hide');
                    reloadVomsisTransactions();
                } else {
                    toastr.error(response.message || 'Eşleştirme yapılamadı.');
                }
            }).fail(function() {
                toastr.error('Sunucu ile iletişim kurulamadı.');
            }).always(function() { button.prop('disabled', false); });
        });

        function reloadAllPayments() {
            allPaymentsTable.ajax.reload(null, false);
        }

        $('#allPaymentSearch, #allPaymentCustomerSearch, #allPaymentAmount').on('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                reloadAllPayments();
            }
        });
        $('#allPaymentSource, #allPaymentType, #allPaymentStatus, #allPaymentAmountOp, #allPaymentStartDate, #allPaymentEndDate').on('change', reloadAllPayments);
        $('#applyAllPaymentFilters').on('click', reloadAllPayments);
        $('#clearAllPaymentFilters').on('click', function() {
            $('#allPaymentSearch, #allPaymentCustomerSearch, #allPaymentAmount').val('');
            $('#allPaymentSource, #allPaymentType, #allPaymentStatus, #allPaymentAmountOp, #allPaymentStartDate, #allPaymentEndDate').val('');
            reloadAllPayments();
        });

        $('#allPaymentsTable tbody').on('click', '.pay-chevron', function() {
            var button = $(this);
            var tr = button.closest('tr');
            var row = allPaymentsTable.row(tr);
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('pay-selected');
                button.attr('aria-expanded', 'false');
                return;
            }
            var id = parseInt(button.data('id'), 10) || 0;
            var detailButton = $('<button type="button" class="pay-ghost payment-detail-link"><i class="fa-solid fa-magnifying-glass me-1"></i>Ödeme Detayı</button>')
                .attr('data-detail-url', button.attr('data-detail-url'));
            var actions = $('<div class="pay-expandactions"></div>').append(detailButton);
            var inner = $('<div class="pay-expandinner"></div>')
                .append($('<strong></strong>').text('#' + id + ' · İşlem seçenekleri'))
                .append(actions);
            row.child($('<tr class="pay-expand"><td colspan="9"></td></tr>').find('td').append(inner).end()).show();
            tr.addClass('pay-selected');
            button.attr('aria-expanded', 'true');
        });
        $(document).on('click', '.pay-approve-btn', function() {
            changeStatus($(this).data('id'));
        });

        $('#balanceTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function(event) {
            var target = $(event.target).data('bs-target');
            if (target === '#all-payments') {
                allPaymentsTable.columns.adjust();
            } else if (target === '#vomsis-transactions') {
                vomsisTransactionsTable.columns.adjust();
            }
        });

        // --- İŞLEM AKSİYONU (Approve/Reject) ---
        $('#changeStatusBtn').click(function() {
            var btn = $(this);
            var orderStatus = $('#orderStatus').val();
            var orderID = $('#order-id').val();

            if (!orderStatus) {
                toastr.warning('Lütfen işlemi tamamlamak için bir onay durumu belirleyin.', 'Eksik Seçim');
                return;
            }

            // Metronic Loading Effect (Çift tık engelleme)
            btn.attr('data-kt-indicator', 'on').prop('disabled', true);

            $.ajax({
                url: '<?= site_url('balance/changeStatus/') ?>' + orderID,
                type: 'POST',
                data: {
                    status: orderStatus
                },
                success: function(response) {
                    if (response.status == 'success') {
                        toastr.success(response.message, 'İşlem Başarılı');
                        $('#orderStatusChangeModal').modal('hide');
                        reloadAllPayments();
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    } else {
                        toastr.error(response.message || 'Bir hata oluştu.', 'Sistem Hatası');
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    }
                },
                error: function() {
                    toastr.error('Sunucu ile iletişim kurulamadı. İnternet bağlantınızı kontrol edin.', 'Ağ Hatası');
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                }
            });
        });
    });
</script>