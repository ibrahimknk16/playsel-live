<?php 
$userID = $this->uri->segment(3);
$fraudCdnBaseUrl = rtrim((string) getenv('CDN_URL'), '/') . '/';
$refUserName = 'Yok';
if (!empty($user->user_reference_id)) {
    $refUser = $this->db->get_where('users', ['user_id' => $user->user_reference_id])->row();
    if ($refUser) {
        $refUserName = $refUser->user_nickname ?? $refUser->user_name;
    }
}
?>

<style>
    .user-detail-page {
        --c-panel: #191925;
        --c-panel2: #1a1a27;
        --c-inner: #222233;
        --c-line: #303044;
        --c-line2: #29293a;
        --c-text: #f0f0f6;
        --c-text2: #d8d8e6;
        --c-muted: #9999af;
        --c-gold: #ffc21a;
        font-family: Poppins, Arial, sans-serif;
        color: var(--c-text2);
        font-size: 11px;
    }
    .user-detail-page .cd-breadcrumb { display: flex; gap: 10px; align-items: center; color: #777b96; font-size: 11px; margin: 0 0 14px; }
    .user-detail-page .cd-breadcrumb a { color: var(--c-gold); text-decoration: none; }
    .user-detail-page .cd-breadcrumb b { color: var(--c-text); font-weight: 600; }
    .user-detail-page .cd-profile, .user-detail-page .cd-panel { background: var(--c-panel); border: 1px solid var(--c-line); border-radius: 10px; overflow: hidden; }
    .user-detail-page .cd-profile { margin-bottom: 14px; }
    .user-detail-page .cd-profile-top { display: grid; grid-template-columns: 62px minmax(180px, 1fr) minmax(0, 560px); gap: 16px; align-items: center; padding: 18px 20px 14px; }
    .user-detail-page .cd-avatar { width: 62px; height: 62px; display: grid; place-items: center; background: #253650; border: 1px solid #3e536d; border-radius: 12px; color: #83bdff; font-size: 24px; font-weight: 700; }
    .user-detail-page .cd-name { font-size: 17px; font-weight: 700; color: #fff; line-height: 1.3; overflow-wrap: anywhere; }
    .user-detail-page .cd-meta { display: flex; flex-wrap: wrap; gap: 4px 14px; margin-top: 6px; font-size: 10px; color: var(--c-muted); }
    .user-detail-page .cd-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 9px; }
    .user-detail-page .cd-chip { display: inline-flex; align-items: center; padding: 4px 9px; border-radius: 5px; font-size: 10px; font-weight: 600; background: #1d3249; color: #70b9ff; }
    .user-detail-page .cd-chip.success { background: #18382d; color: #63d9a6; }
    .user-detail-page .cd-chip.danger { background: #40252e; color: #f18b9b; }
    .user-detail-page .cd-chip.warn { background: #44341c; color: #ffcf6b; }
    .user-detail-page .cd-header-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .user-detail-page .cd-header-kpi { background: var(--c-inner); border: 1px solid var(--c-line); border-radius: 7px; padding: 10px 14px; display: flex; flex-direction: column; gap: 3px; min-width: 0; }
    .user-detail-page .cd-header-kpi small { color: #b5b5cd; font-size: 10px; }
    .user-detail-page .cd-header-kpi strong { font-size: 20px; color: #f7f7ff; line-height: 1.3; }
    .user-detail-page .cd-header-kpi span { font-size: 9px; color: #8f91ad; }
    .user-detail-page .cd-actions { grid-column: 1 / -1; display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; padding-top: 12px; border-top: 1px solid var(--c-line); }
    .user-detail-page .cd-button { display: inline-flex; align-items: center; gap: 7px; min-height: 34px; padding: 0 13px; border: 1px solid #3a3a51; border-radius: 6px; background: #27273a; color: #e8e8f2; font: 600 10px Poppins, Arial, sans-serif; white-space: nowrap; text-decoration: none; cursor: pointer; transition: background .15s, border-color .15s; }
    .user-detail-page .cd-button:hover { background: #35354b; color: #fff; }
    .user-detail-page .cd-button.primary { background: var(--c-gold); border-color: var(--c-gold); color: #17131a; }
    .user-detail-page .cd-button.primary:hover { background: #ffca46; }
    .user-detail-page .cd-button.danger { color: #ff8da8; border-color: #5a2a3d; background: #2b1d29; }
    .user-detail-page .cd-button svg { width: 14px; height: 14px; flex: none; }
    .user-detail-page .cd-tabs { display: flex; flex-wrap: nowrap; gap: 3px; padding: 0 16px; overflow-x: auto; border-top: 1px solid var(--c-line); margin: 0; list-style: none; scrollbar-width: thin; }
    .user-detail-page .cd-tabs .nav-item { flex: none; }
    .user-detail-page .cd-tabs .nav-link { display: flex; align-items: center; gap: 7px; border: 0; border-bottom: 2px solid transparent; border-radius: 0; background: transparent; padding: 14px 11px 12px; white-space: nowrap; font: 600 10.5px Poppins, Arial, sans-serif; color: #9a9db6; }
    .user-detail-page .cd-tabs .nav-link:hover { color: #f5f5fa; }
    .user-detail-page .cd-tabs .nav-link.active { color: var(--c-gold); border-bottom-color: var(--c-gold); background: transparent; }
    .user-detail-page .cd-tabs .nav-link svg { width: 15px; height: 15px; }
    .user-detail-page .cd-overview-layout { display: grid; grid-template-columns: minmax(250px, 300px) minmax(0, 1fr); gap: 14px; align-items: start; }
    .user-detail-page .cd-overview-left, .user-detail-page .cd-overview-right { display: grid; gap: 14px; min-width: 0; }
    .user-detail-page .cd-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 13px 16px; border-bottom: 1px solid var(--c-line); min-height: 46px; }
    .user-detail-page .cd-panel-head h3 { margin: 0; font-size: 12px; font-weight: 700; color: var(--c-text); }
    .user-detail-page .cd-panel-head p { margin: 3px 0 0; font-size: 10px; color: var(--c-muted); }
    .user-detail-page .cd-info-stack { padding: 4px 16px 10px; }
    .user-detail-page .cd-vrow { display: grid; grid-template-columns: minmax(120px, 46%) minmax(0, 1fr); gap: 10px; align-items: center; min-height: 40px; padding: 9px 0; border-bottom: 1px solid #2a2a3c; }
    .user-detail-page .cd-vrow:last-child { border-bottom: 0; }
    .user-detail-page .cd-vrow > span { display: flex; align-items: center; gap: 8px; color: #a9abc1; font-size: 10px; }
    .user-detail-page .cd-vrow > span svg { width: 13px; height: 13px; color: var(--c-gold); flex: none; }
    .user-detail-page .cd-vrow strong { text-align: right; color: var(--c-text); font-size: 10.5px; font-weight: 600; overflow-wrap: anywhere; line-height: 1.45; }
    .user-detail-page .cd-vrow strong a { color: inherit; text-decoration: none; }
    .user-detail-page .cd-vrow strong a:hover { color: var(--c-gold); }
    .user-detail-page .cd-vmark { display: inline-grid; place-items: center; width: 14px; height: 14px; margin-left: 5px; border-radius: 50%; font-size: 9px; font-weight: 700; vertical-align: middle; }
    .user-detail-page .cd-vmark.ok { background: #18382d; color: #63d9a6; }
    .user-detail-page .cd-vmark.no { background: #44341c; color: #ffcf6b; }
    .user-detail-page .cd-balance-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding: 16px; }
    .user-detail-page .cd-balance-main { background: var(--c-inner); border: 1px solid #35354c; border-radius: 7px; padding: 16px; display: flex; flex-direction: column; gap: 6px; }
    .user-detail-page .cd-balance-main small { font-size: 10px; color: #a5a5b9; }
    .user-detail-page .cd-balance-main strong { font-size: 21px; font-weight: 700; color: #41d99b; }
    .user-detail-page .cd-balance-main.alt strong { color: #f5bc4d; }
    .user-detail-page .cd-balance-main span { font-size: 9px; color: #85859e; }
    .user-detail-page .cd-balance-mini { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; padding: 0 16px 16px; }
    .user-detail-page .cd-balance-mini > div { background: var(--c-panel2); border: 1px solid var(--c-line2); border-radius: 6px; padding: 12px; display: flex; flex-direction: column; gap: 6px; }
    .user-detail-page .cd-balance-mini small { font-size: 9px; color: #9999ad; }
    .user-detail-page .cd-balance-mini b { font-size: 13px; color: #e7e7f3; }
    .user-detail-page .cd-balance-mini b.up { color: #70b9ff; }
    .user-detail-page .cd-balance-mini b.down { color: #ff668f; }
    .user-detail-page .cd-balance-mini .cd-icon-btn { align-self: flex-start; }
    .user-detail-page .cd-icon-btn { display: inline-grid; place-items: center; width: 26px; height: 26px; border: 1px solid #404057; border-radius: 6px; background: #252538; color: #b2b2ca; cursor: pointer; }
    .user-detail-page .cd-icon-btn:hover { color: var(--c-gold); border-color: #8a6921; }
    .user-detail-page .cd-icon-btn svg { width: 13px; height: 13px; }
    .user-detail-page .cd-table-wrap { overflow-x: auto; }
    .user-detail-page .cd-table-wrap table { width: 100%; min-width: 0; table-layout: auto; border-collapse: collapse; margin: 0 !important; }
    .user-detail-page .cd-table-wrap table th:first-child, .user-detail-page .cd-table-wrap table td:first-child { padding-left: 16px !important; }
    .user-detail-page .cd-table-wrap table th:last-child, .user-detail-page .cd-table-wrap table td:last-child { padding-right: 16px !important; }
    .user-detail-page .cd-table-wrap td { word-break: break-word; }
    .user-detail-page .cd-table-wrap td .fs-7 { font-size: 9px !important; color: var(--c-muted) !important; }
    .user-detail-page .cd-vrow > span { white-space: nowrap; }
    .user-detail-page .cd-table-wrap th { padding: 12px 14px; background: var(--c-panel2); color: #a7a7bb; font-size: 10px; font-weight: 600; border-bottom: 1px solid var(--c-line); text-transform: none; white-space: nowrap; }
    .user-detail-page .cd-table-wrap td { padding: 11px 14px; border-bottom: 1px solid var(--c-line2); color: var(--c-text2); font-size: 10px; vertical-align: middle; }
    .user-detail-page .cd-table-wrap tbody tr:hover { background: rgba(255, 194, 26, .035); }
    .user-detail-page .cd-table-wrap tbody tr:last-child td { border-bottom: 0; }
    .user-detail-page .cd-content { margin: 0; }
    .user-detail-page .tab-content > .tab-pane > .card { background: var(--c-panel); border: 1px solid var(--c-line); border-radius: 10px; }
    .user-detail-page .tab-content > .tab-pane > .card .card-header { border-bottom: 1px solid var(--c-line) !important; min-height: 46px; padding: 12px 16px; }
    .user-detail-page .tab-content > .tab-pane > .card .card-title h2,
    .user-detail-page .tab-content > .tab-pane > .card .card-title h3 { font-size: 12px; font-weight: 700; color: var(--c-text); margin: 0; }
    .user-detail-page .tab-content .table > :not(caption) > * > * { border-color: var(--c-line2); }
    .user-detail-page .tab-content .table thead th { background: var(--c-panel2); color: #a7a7bb !important; font-size: 10px; text-transform: none; }
    .user-detail-page .tab-content .table td { font-size: 10px; }
    .user-detail-page .btn-light-primary { background: #1d3249; border-color: #2a4b65; color: #80c7ff; }
    .user-detail-page .btn-light-success { background: #18382d; border-color: #285844; color: #63d9a6; }
    .user-detail-page .btn-light-danger { background: #40252e; border-color: #703047; color: #ff8da8; }
    .user-detail-page .badge-light-success { background: #18382d; color: #63d9a6; }
    .user-detail-page .badge-light-danger { background: #40252e; color: #f18b9b; }
    .user-detail-page .badge-light-warning { background: #44341c; color: #ffcf6b; }
    .user-detail-page .badge-light-primary, .user-detail-page .badge-light-info { background: #1d3249; color: #70b9ff; }
    /* Diğer sekmeler: ortak panel / form / tablo stili */
    .user-detail-page .tab-pane { font-size: 10.5px; color: var(--c-text2); }
    .user-detail-page .tab-pane .card { background: var(--c-panel); border: 1px solid var(--c-line); border-radius: 10px; box-shadow: none; margin-bottom: 14px !important; overflow: hidden; }
    .user-detail-page .tab-pane .card > .card-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 46px; padding: 12px 16px !important; border-bottom: 1px solid var(--c-line) !important; background: transparent; }
    .user-detail-page .tab-pane .card > .card-header.pt-6, .user-detail-page .tab-pane .card.pt-4 { padding-top: 0 !important; }
    .user-detail-page .tab-pane .card-title { margin: 0 !important; }
    .user-detail-page .tab-pane .card-title h2, .user-detail-page .tab-pane .card-title h3, .user-detail-page .tab-pane .card-header h2, .user-detail-page .tab-pane .card-header h3 { font-size: 12px !important; font-weight: 700; color: var(--c-text); margin: 0; }
    .user-detail-page .tab-pane .card-body { padding: 14px 16px !important; }
    .user-detail-page .tab-pane .fs-1, .user-detail-page .tab-pane .fs-2, .user-detail-page .tab-pane .fs-3, .user-detail-page .tab-pane .fs-4, .user-detail-page .tab-pane .fs-5, .user-detail-page .tab-pane .fs-6 { font-size: 11px !important; }
    .user-detail-page .tab-pane .fs-7 { font-size: 10px !important; }
    .user-detail-page .tab-pane .text-gray-800, .user-detail-page .tab-pane .text-gray-900 { color: var(--c-text) !important; }
    .user-detail-page .tab-pane .text-gray-700, .user-detail-page .tab-pane .text-gray-600 { color: var(--c-text2) !important; }
    .user-detail-page .tab-pane .text-gray-500, .user-detail-page .tab-pane .text-gray-400, .user-detail-page .tab-pane .text-muted { color: var(--c-muted) !important; }
    .user-detail-page .tab-pane .notice { display: flex; align-items: center; gap: 10px; padding: 11px 14px !important; margin-bottom: 14px !important; border: 1px solid #2a4b65 !important; border-radius: 7px; background: #172233 !important; }
    .user-detail-page .tab-pane .notice i { font-size: 16px !important; margin: 0 !important; color: #70b9ff !important; }
    .user-detail-page .tab-pane .notice .fs-6 { color: #b7d8f5 !important; }
    .user-detail-page .tab-pane .card-body > .row.mb-7 { display: grid; grid-template-columns: minmax(150px, 220px) minmax(0, 1fr); gap: 10px; align-items: center; margin: 0 !important; padding: 10px 0; border-bottom: 1px solid #2a2a3c; }
    .user-detail-page .tab-pane .card-body > .row.mb-7 > * { width: auto; max-width: none; padding: 0; }
    .user-detail-page .tab-pane .card-body > .row.mb-7:last-child { border-bottom: 0; }
    .user-detail-page .tab-pane .card-body > .row.mb-7 label { font-size: 10px; color: #a9abc1 !important; margin: 0; }
    .user-detail-page .tab-pane .card-body > .row.mb-7 .fw-bold { font-size: 10.5px !important; font-weight: 600; color: var(--c-text) !important; }
    .user-detail-page .tab-pane .form-label, .user-detail-page .tab-pane label { font-size: 10px; color: var(--c-muted); font-weight: 500; }
    .user-detail-page .tab-pane .form-control, .user-detail-page .tab-pane .form-select, .user-detail-page .tab-pane .select2-container--bootstrap5 .select2-selection { min-height: 33px; height: 33px; padding: 0 10px; font-size: 10.5px; background: var(--c-inner) !important; border: 1px solid #343449 !important; border-radius: 6px !important; color: #eee !important; }
    .user-detail-page .tab-pane textarea.form-control { height: auto; padding: 8px 10px; }
    .user-detail-page .tab-pane .form-control:focus, .user-detail-page .tab-pane .form-select:focus { border-color: #c68e1a !important; box-shadow: none; }
    .user-detail-page .tab-pane .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; height: 33px; padding: 0 12px; border-radius: 6px; font: 600 10px Poppins, Arial, sans-serif; border: 1px solid #3a3a51; background: #27273a; color: #e8e8f2; }
    .user-detail-page .tab-pane .btn.btn-icon { width: 30px; height: 30px; padding: 0; }
    .user-detail-page .tab-pane .btn:hover { background: #35354b; color: #fff; }
    .user-detail-page .tab-pane .btn-primary { background: var(--c-gold); border-color: var(--c-gold); color: #17131a; }
    .user-detail-page .tab-pane .btn-primary:hover { background: #ffca46; color: #17131a; }
    .user-detail-page .tab-pane .btn i { font-size: 13px; margin: 0 !important; }
    .user-detail-page .tab-pane .table-responsive, .user-detail-page .tab-pane .dataTables_wrapper { overflow-x: auto; }
    .user-detail-page .tab-pane table.table, .user-detail-page .tab-pane table.dataTable { width: 100% !important; margin: 0 !important; border-collapse: collapse; }
    .user-detail-page .tab-pane table thead th { padding: 12px 14px !important; background: var(--c-panel2) !important; color: #a7a7bb !important; font-size: 10px !important; font-weight: 600; text-transform: none !important; border-bottom: 1px solid var(--c-line) !important; white-space: nowrap; }
    .user-detail-page .tab-pane table tbody td { padding: 11px 14px !important; border-bottom: 1px solid var(--c-line2) !important; color: var(--c-text2); font-size: 10px !important; vertical-align: middle; }
    .user-detail-page .tab-pane table tbody tr:hover { background: rgba(255, 194, 26, .035); }
    .user-detail-page .tab-pane table tbody tr:last-child td { border-bottom: 0 !important; }
    .user-detail-page .tab-pane table code { color: #70b9ff; background: transparent; font-size: 10px; }
    .user-detail-page .tab-pane .badge { padding: 4px 8px; border-radius: 4px; font-size: 9px; font-weight: 600; }
    .user-detail-page .tab-pane .dataTables_info, .user-detail-page .tab-pane .dataTables_length label { font-size: 10px; color: var(--c-muted); }
    .user-detail-page .tab-pane .pagination { gap: 5px; margin: 0; }
    .user-detail-page .tab-pane .page-link { min-width: 29px; height: 29px; display: grid; place-items: center; padding: 0 8px; border: 1px solid #36374c; background: #242538; color: #c6c8dd; border-radius: 5px; font-size: 10px; }
    .user-detail-page .tab-pane .page-item.active .page-link { background: var(--c-gold); border-color: var(--c-gold); color: #17131a; }
    .user-detail-page .tab-pane .page-item.disabled .page-link { opacity: .35; }
    .user-detail-page .tab-pane .separator { border-color: var(--c-line); margin: 14px 0 !important; }
    .user-detail-page .tab-pane .card-body h2, .user-detail-page .tab-pane .card-body h3, .user-detail-page .tab-pane .card-body h4, .user-detail-page .tab-pane .card-body h5 { font-size: 12px !important; font-weight: 700; color: var(--c-text); margin: 0 0 10px !important; }
    .user-detail-page .tab-pane .card-body .separator.my-8 { margin: 18px -16px 14px !important; border-top: 1px solid var(--c-line); }
    .user-detail-page .tab-pane table td .btn, .user-detail-page .tab-pane table td .badge { white-space: nowrap; }
    .user-detail-page .tab-pane table td .btn { height: 29px; font-size: 10px; }
    .user-detail-page .tab-pane .select2-container { min-width: 130px; }
    .user-detail-page .tab-pane .select2-container .select2-selection { display: flex; align-items: center; }
    .user-detail-page .tab-pane .select2-selection__rendered { font-size: 10.5px !important; color: #eee !important; padding-left: 0 !important; }
    .user-detail-page .tab-pane input[type="date"] { color-scheme: dark; }
    .user-detail-page .tab-pane .row.g-3, .user-detail-page .tab-pane .row.g-4, .user-detail-page .tab-pane .row.g-5 { --bs-gutter-x: 10px; --bs-gutter-y: 10px; }
    .user-detail-page .tab-pane .border-dashed { border-color: var(--c-line) !important; }
    /* Ödeme Geçmişi */
    .user-detail-page .cd-pay-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
    .user-detail-page .cd-pay-kpis > div { display: flex; flex-direction: column; gap: 4px; padding: 12px 14px; background: var(--c-panel); border: 1px solid var(--c-line); border-radius: 8px; min-width: 0; }
    .user-detail-page .cd-pay-kpis small { font-size: 10px; color: var(--c-muted); }
    .user-detail-page .cd-pay-kpis strong { font-size: 17px; color: var(--c-text); line-height: 1.3; }
    .user-detail-page .cd-pay-kpis strong.up { color: #70b9ff; }
    .user-detail-page .cd-pay-kpis strong.down { color: #ff668f; }
    .user-detail-page .cd-pay-kpis span { font-size: 9px; color: #8f91ad; }
    .user-detail-page .cd-subtabs { display: flex; flex-wrap: nowrap; gap: 6px; padding: 12px 16px; margin: 0; list-style: none; border-bottom: 1px solid var(--c-line); overflow-x: auto; }
    .user-detail-page .cd-subtabs .nav-link { padding: 7px 13px; border-radius: 6px; border: 1px solid transparent; background: transparent; color: #9a9db6; font: 600 10.5px Poppins, Arial, sans-serif; white-space: nowrap; }
    .user-detail-page .cd-subtabs .nav-link:hover { color: #f5f5fa; }
    .user-detail-page .cd-subtabs .nav-link.active { background: #272218; border-color: #a67713; color: var(--c-gold); }
    .user-detail-page .cd-pay-toolbar { display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-bottom: 1px solid var(--c-line); }
    .user-detail-page .cd-pay-search { flex: 1; max-width: 460px; }
    .user-detail-page .cd-pay-toolbar .btn[aria-expanded="true"], .user-detail-page .cd-pay-toolbar .btn[aria-pressed="true"] { color: var(--c-gold); border-color: #a67713; background: #272218; }
    .user-detail-page .cd-filter-box { padding: 12px 16px; background: var(--c-panel2); border-bottom: 1px solid var(--c-line); }
    .user-detail-page .cd-filter-box[hidden] { display: none; }
    .user-detail-page .cd-filter-box .form-control, .user-detail-page .cd-filter-box .form-select { min-width: 130px; }
    .user-detail-page .cd-pay-table td { vertical-align: top; }
    .user-detail-page .cd-pay-table .cd-sub { display: block; margin-top: 3px; font-size: 9px; color: var(--c-muted); }
    .user-detail-page .cd-pay-table .cd-muted { color: #6d6d84; }
    .user-detail-page .cd-pay-table .cd-money { font-weight: 700; color: var(--c-text); white-space: nowrap; font-variant-numeric: tabular-nums; }
    .user-detail-page .cd-pay-table .cd-ref { font-family: Consolas, monospace; font-size: 9.5px; color: #b5b5ca; word-break: break-all; max-width: 220px; }
    .user-detail-page .cd-pay-table .cd-note-cell { max-width: 200px; color: var(--c-muted); }
    .user-detail-page .cd-pay-table .cd-actions-cell { white-space: nowrap; }
    .user-detail-page .cd-pay-table .cd-actions-cell .btn + .btn { margin-left: 6px; }
    .user-detail-page .cd-pay-table td .badge, .user-detail-page .cd-pay-table td .btn { white-space: nowrap; }
    .user-detail-page #cd_pay_legacy .dataTables_wrapper, .user-detail-page #cd_pay_moves .dataTables_wrapper { padding: 0; }
    .user-detail-page #cd_pay_legacy .row, .user-detail-page #cd_pay_moves .row { margin: 0; padding: 10px 16px; align-items: center; }
    @media (max-width: 900px) { .user-detail-page .cd-pay-kpis { grid-template-columns: 1fr 1fr; } }
    .user-detail-page .cd-empty-state { padding: 22px 16px; text-align: center; color: var(--c-muted); font-size: 10.5px; }
    .user-detail-page .cd-note-body { padding: 14px 16px; font-size: 11px; line-height: 1.7; color: var(--c-text2); white-space: normal; overflow-wrap: anywhere; max-height: 260px; overflow-y: auto; }
    .user-detail-page .cd-icon-btn.danger:hover { color: #ff668f; border-color: #703047; }
    .cd-ref-chart { display: flex; align-items: flex-end; gap: 14px; height: 150px; padding: 18px 20px 12px; }
    .cd-ref-bar { flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: 4px; position: relative; }
    .cd-ref-bar i { display: block; width: 100%; max-width: 46px; min-height: 3px; background: linear-gradient(180deg, #ffc21a, #a67713); border-radius: 5px 5px 2px 2px; }
    .cd-ref-bar em { font-style: normal; font-size: 9px; color: #9999af; }
    .cd-ref-val { font-size: 10px; font-weight: 700; color: #f3f5f6; }
    @media (max-width: 1100px) {
        .user-detail-page .cd-profile-top { grid-template-columns: 62px minmax(0, 1fr); }
        .user-detail-page .cd-header-kpis { grid-column: 1 / -1; }
        .user-detail-page .cd-overview-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .user-detail-page .cd-header-kpis, .user-detail-page .cd-balance-grid, .user-detail-page .cd-balance-mini { grid-template-columns: 1fr 1fr; }
        .user-detail-page .cd-actions { justify-content: flex-start; }
    }
</style>

<div class="row user-detail-page">
    <div class="col-12">

        <?php
        // type/tier
        $userTypeText = 'Bilinmiyor';
        if ((int)$user->user_type === 2) $userTypeText = 'Supplier';
        if ((int)$user->user_type === 3) $userTypeText = 'Reseller';
        if ((int)$user->user_type === 4) $userTypeText = 'Bireysel';

        $identityMissing = false;
        $identityFields = ['user_phone', 'user_phone_is_verified', 'user_email', 'user_email_is_verified', 'user_status'];
        foreach ($identityFields as $f) {
            if (isset($user->$f)) {
                if (in_array($f, ['user_phone_is_verified', 'user_email_is_verified'], true)) {
                    if ((int)$user->$f !== 1) $identityMissing = true;
                } else {
                    if (empty($user->$f)) $identityMissing = true;
                }
            }
        }

        $orderCount = isset($summaryStats['orderCount']) ? (int)$summaryStats['orderCount'] : 0;
        $paidCount  = isset($summaryStats['paidCount']) ? (int)$summaryStats['paidCount'] : 0;
        $noteCount  = isset($summaryStats['noteCount']) ? (int)$summaryStats['noteCount'] : 0;

        $fullName = trim(($user->user_name ?? '') . ' ' . ($user->user_surname ?? '')) ?: ($user->user_name ?: 'Müşteri');
        $avatarInitial = mb_strtoupper(mb_substr($fullName, 0, 1, 'UTF-8'), 'UTF-8');
        $tierNames = [1 => 'Son Kullanıcı', 2 => 'Dijipin', 3 => 'API Kullanıcısı', 4 => 'API Kullanıcısı Özel', 5 => 'Playsel', 6 => 'Ödeme Kuruluşu'];
        $tierText = $tierNames[(int)$user->user_tier] ?? 'Bilinmiyor';
        $isActiveUser = ((int)$user->user_status === 1);
        $cdIcon = function ($name) {
            $paths = [
                'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
                'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
                'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
                'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
                'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
                'bank' => '<path d="M3 10 12 4l9 6M5 10v8M10 10v8M14 10v8M19 10v8M3 20h18"/>',
                'wallet' => '<rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 9h18M16 14h2"/>',
                'order' => '<circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M2 3h2l3 13h13l2-9H5"/>',
                'check' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
                'note' => '<path d="M5 4h14a1 1 0 0 1 1 1v11l-4 4H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"/><path d="M8 9h8M8 13h5"/>',
                'file' => '<path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5M10 13h6M10 17h6"/>',
                'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 5"/>',
                'activity' => '<path d="M3 12h4l3-8 4 16 3-8h4"/>',
                'code' => '<path d="m8 8-5 4 5 4M16 8l5 4-5 4M14 5l-4 14"/>',
                'shield' => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/>',
                'plus' => '<path d="M12 5v14M5 12h14"/>',
                'minus' => '<path d="M5 12h14"/>',
                'edit' => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>',
                'card' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/>',
            ];
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['user']) . '</svg>';
        };
        ?>

        <div class="cd-breadcrumb">
            <a href="<?= base_url('users') ?>">← Müşteri Listesi</a>
            <span>/</span>
            <b>Müşteri Detayı</b>
        </div>

        <div class="cd-profile">
            <div class="cd-profile-top">
                <div class="cd-avatar"><?= html_escape($avatarInitial) ?></div>

                <div class="cd-identity">
                    <div class="cd-name"><?= html_escape($fullName) ?></div>
                    <div class="cd-meta">
                        <span>Müşteri ID: <b>#<?= (int)$user->user_id ?></b></span>
                        <span>Kayıt: <b><?= html_escape($user->user_created_at) ?></b></span>
                        <?php if (!empty($user->user_nickname)): ?><span>Takma Ad: <b><?= html_escape($user->user_nickname) ?></b></span><?php endif; ?>
                        <span>Referans: <b><?= html_escape($refUserName) ?></b></span>
                        <?php if (!empty($user->user_last_login_ip)): ?><span>Son Giriş IP: <b><?= html_escape($user->user_last_login_ip) ?></b></span><?php endif; ?>
                    </div>
                    <div class="cd-chips">
                        <span class="cd-chip"><?= html_escape($tierText) ?></span>
                        <span class="cd-chip <?= $isActiveUser ? 'success' : 'danger' ?>"><?= $isActiveUser ? 'Aktif' : 'Pasif' ?></span>
                        <?php if (!empty($activeRestriction)): ?>
                            <span class="cd-chip danger">Hesap Kısıtlı</span>
                        <?php endif; ?>
                        <?php if ($identityMissing): ?>
                            <span class="cd-chip warn">Kimlik Verisi Eksik</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="cd-header-kpis">
                    <div class="cd-header-kpi">
                        <small>Toplam Sipariş</small>
                        <strong><?= $orderCount ?></strong>
                        <span>Oluşturulan sipariş</span>
                    </div>
                    <div class="cd-header-kpi">
                        <small>Başarılı Ödeme</small>
                        <strong><?= $paidCount ?></strong>
                        <span>Onaylanan işlem</span>
                    </div>
                    <div class="cd-header-kpi">
                        <small>Personel Notu</small>
                        <strong><?= $noteCount ?></strong>
                        <span>Yönetici kayıtları</span>
                    </div>
                </div>

                <div class="cd-actions">
                    <?php if (!empty($canImpersonate)): ?>
                        <form method="post" target="_blank" action="<?= base_url('users/impersonate/' . (int)$user->user_id) ?>" onsubmit="return confirm('Bu kullanıcı hesabına parola ve iki aşamalı doğrulama adımları olmadan geçilecek. Devam edilsin mi?')">
                            <input type="hidden" name="impersonation_csrf" value="<?= html_escape($impersonationCsrfToken) ?>">
                            <button type="submit" class="cd-button"><?= $cdIcon('user') ?> Bu Kullanıcı Olarak Oturum Aç</button>
                        </form>
                    <?php endif; ?>
                    <button type="button" class="cd-button" onclick="showAddBalanceModal(<?= (int)$user->user_id ?>)"><?= $cdIcon('plus') ?> Bakiye Ekle</button>
                    <button type="button" class="cd-button danger" onclick="showRemoveBalanceModal(<?= (int)$user->user_id ?>)"><?= $cdIcon('minus') ?> Bakiye Çıkar</button>
                    <button type="button" class="cd-button" data-bs-toggle="modal" data-bs-target="#cdResetPasswordModal"><?= $cdIcon('shield') ?> Şifre Sıfırla</button>
                    <?php if (empty($activeRestriction)): ?>
                        <button type="button" class="cd-button danger" id="cdRestrictBtn"><?= $cdIcon('shield') ?> Hesabı Kısıtla</button>
                    <?php endif; ?>
                    <button type="button" class="cd-button" data-bs-toggle="modal" data-bs-target="#kt_modal_user_notes" id="btnAddUserNote"><?= $cdIcon('note') ?> Personel Notu</button>
                    <button type="button" class="cd-button primary" data-bs-toggle="modal" data-bs-target="#kt_modal_update_details"><?= $cdIcon('edit') ?> Düzenle</button>
                </div>

                <?php if (!empty($activeRestriction)): ?>
                    <div class="cd-restrict-banner" role="alert">
                        <div>
                            <strong>Bu hesap kısıtlı</strong>
                            <span>
                                Bitiş: <?= !empty($activeRestriction->restriction_expires_at) ? html_escape($activeRestriction->restriction_expires_at) : 'Süresiz' ?>
                                · Müşteriye gösterilen: “<?= html_escape(mb_strimwidth((string)$activeRestriction->restriction_customer_reason, 0, 140, '…', 'UTF-8')) ?>”
                            </span>
                            <small>Yönetici notu: <?= html_escape(mb_strimwidth((string)$activeRestriction->restriction_admin_reason, 0, 200, '…', 'UTF-8')) ?></small>
                        </div>
                        <button type="button" class="cd-button" id="cdUnrestrictBtn" data-id="<?= (int)$activeRestriction->restriction_id ?>">Kısıtlamayı Kaldır</button>
                    </div>
                <?php endif; ?>
            </div>

            <ul class="nav cd-tabs" role="tablist">
                <li class="nav-item" role="presentation"><a class="nav-link active" data-bs-toggle="tab" href="#kt_user_view_account_summary" role="tab"><?= $cdIcon('user') ?> Hesap Özeti</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" href="#kt_user_movements" role="tab"><?= $cdIcon('wallet') ?> Hesap Hareketleri</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" href="#kt_user_billing_info" role="tab"><?= $cdIcon('bank') ?> Fatura / IBAN Bilgileri</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" href="#kt_user_invoices" role="tab"><?= $cdIcon('file') ?> Geçmiş Faturalar</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" href="#kt_user_view_orders" role="tab"><?= $cdIcon('order') ?> Siparişler</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" href="#kt_user_transactions" role="tab"><?= $cdIcon('wallet') ?> Ödeme Geçmişi</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" href="#kt_user_event_logs" role="tab"><?= $cdIcon('activity') ?> Kullanıcı Hareketleri</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" href="#kt_user_referrals" role="tab"><?= $cdIcon('users') ?> Referans Oldukları</a></li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#kt_user_fraud_documents" role="tab"><?= $cdIcon('shield') ?> Fraud Belgeleri
                        <?php if (!empty($fraudDocuments)): ?>
                            <span class="badge badge-light-warning"><?= count($fraudDocuments) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="modal fade" id="cdResetPasswordModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-500px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="fw-bold mb-0">Şifre Sıfırla</h3>
                        <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal" aria-label="Kapat">✕</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted fs-7">Sistem güvenli ve rastgele bir şifre oluşturur. Gönderim yöntemi seçmeden işlem yapılamaz.</p>
                        <div class="d-flex gap-6 mb-5">
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="resetPasswordEmail">
                                <span class="form-check-label">E-posta gönder</span>
                            </label>
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="resetPasswordSms">
                                <span class="form-check-label">SMS gönder</span>
                            </label>
                        </div>
                        <button type="button" class="btn btn-light-danger" id="resetPasswordBtn">Rastgele Şifre Oluştur / Sıfırla</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content">

            <div class="tab-pane fade" id="kt_user_fraud_documents" role="tabpanel">
                <style>
                    #kt_user_fraud_documents,
                    #kt_user_fraud_documents .card,
                    #kt_user_fraud_documents .card-body {
                        min-width: 0;
                        max-width: 100%;
                    }

                    #kt_user_fraud_documents .card-header {
                        flex-wrap: wrap;
                        gap: .75rem;
                    }

                    #kt_user_fraud_documents .card-header .card-toolbar {
                        margin-left: auto;
                        white-space: normal;
                    }

                    #kt_user_fraud_documents .fraud-document-form-row {
                        display: grid !important;
                        grid-template-columns: minmax(150px, 1fr) minmax(220px, 1.6fr) minmax(150px, 1.2fr) minmax(130px, auto);
                        gap: 14px;
                        align-items: start;
                        margin: 0;
                    }

                    #kt_user_fraud_documents .fraud-document-form-row > [class*="col-"] {
                        width: auto;
                        max-width: none;
                        margin: 0;
                        padding: 0;
                    }

                    #kt_user_fraud_documents .form-label { display: block; margin: 0 0 6px; line-height: 15px; }
                    #kt_user_fraud_documents .form-label.required::after { content: " *"; color: #ff668f; }
                    #kt_user_fraud_documents .form-control,
                    #kt_user_fraud_documents .form-select { height: 38px; padding: 0 12px; font-size: 11px; line-height: 38px; background: #222233; border: 1px solid #343449; border-radius: 6px; color: #eee; box-shadow: none; }
                    #kt_user_fraud_documents input[type="file"].form-control { padding: 0 8px 0 0; line-height: 36px; color: #9999af; }
                    #kt_user_fraud_documents input[type="file"]::file-selector-button { height: 36px; margin: 0 10px 0 0; padding: 0 14px; border: 0; border-right: 1px solid #343449; background: #2a2a3c; color: #e8e8f2; font: 600 10px Poppins, Arial, sans-serif; cursor: pointer; }
                    #kt_user_fraud_documents .form-control:focus,
                    #kt_user_fraud_documents .form-select:focus { border-color: #c68e1a; box-shadow: none; }
                    #kt_user_fraud_documents .form-text { margin-top: 6px; font-size: 9.5px; color: #8f91ad; }
                    #kt_user_fraud_documents .fraud-submit-col .btn { height: 38px; width: 100%; white-space: nowrap; }
                    @media (max-width: 1100px) {
                        #kt_user_fraud_documents .fraud-document-form-row { grid-template-columns: 1fr 1fr; }
                    }
                    @media (max-width: 640px) {
                        #kt_user_fraud_documents .fraud-document-form-row { grid-template-columns: 1fr; }
                    }
                </style>
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success"><?= html_escape($this->session->flashdata('success')) ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')) ?></div>
                <?php endif; ?>

                <div class="card card-flush mb-6">
                    <div class="card-header">
                        <h3 class="card-title">Yeni fraud belgesi ekle</h3>
                        <div class="card-toolbar text-muted fs-7">Belgeler yalnızca yetkili admin endpoint'i üzerinden görüntülenir.</div>
                    </div>
                    <div class="card-body">
                        <form class="fraud-document-form" action="<?= base_url('users/uploadFraudDocument') ?>" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="user_id" value="<?= (int) $user->user_id ?>">
                            <div class="row g-5 fraud-document-form-row">
                                <div class="col-12 col-xl-3">
                                    <label class="form-label required">Belge türü</label>
                                    <select name="document_type" class="form-select" required>
                                        <option value="">Seçiniz</option>
                                        <option value="identity_front">Kimlik ön yüzü</option>
                                        <option value="identity_back">Kimlik arka yüzü</option>
                                        <option value="selfie">Selfie / yüz doğrulama</option>
                                        <option value="address_proof">Adres kanıtı</option>
                                        <option value="payment_proof">Ödeme kanıtı</option>
                                        <option value="other">Diğer</option>
                                    </select>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="form-label required">Dosya</label>
                                    <input type="file" name="fraud_document" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                                    <div class="form-text">JPG, PNG veya WebP; en fazla 10 MB.</div>
                                </div>
                                <div class="col-12 col-xl-3">
                                    <label class="form-label">Admin notu</label>
                                    <input type="text" name="admin_note" class="form-control" maxlength="1000">
                                </div>
                                <div class="col-12 col-xl-2 fraud-submit-col">
                                    <label class="form-label" aria-hidden="true">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">Belgeyi kaydet</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card-flush">
                    <div class="card-header">
                        <h3 class="card-title">Kayıtlı belgeler</h3>
                    </div>
                    <div class="card-body pt-0">
                        <?php if (empty($fraudDocuments)): ?>
                            <div class="text-muted py-6">Bu kullanıcı için kayıtlı fraud belgesi bulunmuyor.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed gy-4">
                                    <thead>
                                        <tr class="text-gray-400 fw-bold fs-7 text-uppercase">
                                            <th>Tür</th>
                                            <th>Dosya</th>
                                            <th>Not</th>
                                            <th>Yükleyen</th>
                                            <th>Tarih</th>
                                            <th class="text-end">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $fraudDocumentTypes = [
                                            'identity_front' => 'Kimlik ön yüzü',
                                            'identity_back' => 'Kimlik arka yüzü',
                                            'selfie' => 'Selfie / yüz doğrulama',
                                            'address_proof' => 'Adres kanıtı',
                                            'payment_proof' => 'Ödeme kanıtı',
                                            'other' => 'Diğer',
                                        ];
                                        ?>
                                        <?php foreach ($fraudDocuments as $document): ?>
                                            <tr>
                                                <td><span class="badge badge-light-primary"><?= html_escape($fraudDocumentTypes[$document->document_type] ?? $document->document_type) ?></span></td>
                                                <td>
                                                    <div class="fw-semibold"><?= html_escape($document->original_file_name) ?></div>
                                                    <div class="text-muted fs-8"><?= number_format(((int) $document->file_size) / 1024, 1, ',', '.') ?> KB · <?= html_escape($document->mime_type) ?></div>
                                                </td>
                                                <td><?= html_escape($document->admin_note ?? '') ?: '<span class="text-muted">-</span>' ?></td>
                                                <td><?= html_escape($document->admin_name ?? 'Bilinmiyor') ?></td>
                                                <td><?= html_escape($document->uploaded_at) ?></td>
                                                <td class="text-end">
                                                    <a href="<?= html_escape($fraudCdnBaseUrl . ltrim($document->object_key, '/')) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-light-primary">
                                                        Görüntüle
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- =========================================
                 HESAP ÖZETİ
            ========================================== -->
            <div class="tab-pane fade show active" id="kt_user_view_account_summary" role="tabpanel">
                <div class="cd-overview-layout">

                    <aside class="cd-overview-left">
                        <section class="cd-panel">
                            <div class="cd-panel-head"><h3>Üye Bilgileri</h3></div>
                            <div class="cd-info-stack">
                                <div class="cd-vrow"><span><?= $cdIcon('user') ?> Müşteri ID</span><strong>#<?= (int)$user->user_id ?></strong></div>
                                <div class="cd-vrow"><span><?= $cdIcon('user') ?> Ad Soyad</span><strong><?= html_escape($fullName) ?></strong></div>
                                <div class="cd-vrow"><span><?= $cdIcon('mail') ?> E-posta</span>
                                    <strong>
                                        <?php if (!empty($user->user_email)): ?>
                                            <a href="mailto:<?= html_escape($user->user_email) ?>"><?= html_escape($user->user_email) ?></a>
                                            <span class="cd-vmark <?= !empty($user->user_email_is_verified) ? 'ok' : 'no' ?>" title="<?= !empty($user->user_email_is_verified) ? 'Onaylı' : 'Onaysız' ?>"><?= !empty($user->user_email_is_verified) ? '✓' : '!' ?></span>
                                        <?php else: ?>—<?php endif; ?>
                                    </strong>
                                </div>
                                <div class="cd-vrow"><span><?= $cdIcon('phone') ?> Telefon</span>
                                    <strong>
                                        <?php if (!empty($user->user_phone)): ?>
                                            <a href="tel:+<?= html_escape(ltrim($user->user_phone, '+')) ?>">+<?= html_escape(ltrim($user->user_phone, '+')) ?></a>
                                            <span class="cd-vmark <?= !empty($user->user_phone_is_verified) ? 'ok' : 'no' ?>" title="<?= !empty($user->user_phone_is_verified) ? 'Onaylı' : 'Onaysız' ?>"><?= !empty($user->user_phone_is_verified) ? '✓' : '!' ?></span>
                                        <?php else: ?>Kayıtlı değil<?php endif; ?>
                                    </strong>
                                </div>
                                <div class="cd-vrow"><span><?= $cdIcon('calendar') ?> Kayıt Tarihi</span><strong><?= html_escape($user->user_created_at) ?></strong></div>
                                <div class="cd-vrow"><span><?= $cdIcon('globe') ?> Kayıt Olduğu Ülke</span><strong><?= html_escape($user->user_country ?: '—') ?></strong></div>
                                <div class="cd-vrow"><span><?= $cdIcon('globe') ?> Dil</span><strong><?= html_escape(strtoupper((string)$user->user_lang) ?: '—') ?></strong></div>
                                <div class="cd-vrow"><span><?= $cdIcon('globe') ?> Kayıt IP Adresi</span><strong><?= html_escape($registerIp ?? '—') ?></strong></div>
                                <div class="cd-vrow"><span><?= $cdIcon('bank') ?> Havale Ref. Kodu</span><strong><?= html_escape($user->user_bank_referance_number ?: '—') ?></strong></div>
                                <div class="cd-vrow"><span><?= $cdIcon('user') ?> Müşteri Tipi</span><strong><?= html_escape($tierText) ?></strong></div>
                                <?php if (!empty($user->user_nickname)): ?>
                                    <div class="cd-vrow"><span><?= $cdIcon('user') ?> Takma Ad</span><strong><?= html_escape($user->user_nickname) ?></strong></div>
                                <?php endif; ?>
                                <div class="cd-vrow"><span><?= $cdIcon('calendar') ?> Doğum Tarihi</span><strong><?= !empty($user->user_birthday) && $user->user_birthday !== '0000-00-00' ? html_escape(date('d.m.Y', strtotime($user->user_birthday))) : '—' ?></strong></div>
                                <div class="cd-vrow"><span><?= $cdIcon('globe') ?> Son Giriş IP</span><strong><?= html_escape($user->user_last_login_ip ?: '—') ?></strong></div>
                                <?php
                                $taxNo = preg_replace('/\s+/', '', (string)($user->user_bill_tax_number ?? ''));
                                $taxMasked = $taxNo === '' ? '—' : ((mb_strlen($taxNo) >= 6 ? mb_substr($taxNo, 0, 3) . str_repeat('*', mb_strlen($taxNo) - 5) . mb_substr($taxNo, -2) : str_repeat('*', mb_strlen($taxNo))));
                                ?>
                                <div class="cd-vrow"><span><?= $cdIcon('file') ?> Vergi / Kimlik No</span><strong><?= html_escape($taxMasked) ?></strong></div>
                            </div>
                        </section>

                        <?php if (!empty($user->user_email_temp) || !empty($user->user_phone_temp)): ?>
                            <section class="cd-panel">
                                <div class="cd-panel-head"><div><h3>Bekleyen Değişiklikler</h3><p>Onay bekleyen iletişim bilgisi güncellemeleri</p></div></div>
                                <div class="cd-info-stack">
                                    <?php if (!empty($user->user_email_temp)): ?>
                                        <div class="cd-vrow"><span><?= $cdIcon('mail') ?> Yeni E-posta</span><strong><?= html_escape($user->user_email_temp) ?></strong></div>
                                    <?php endif; ?>
                                    <?php if (!empty($user->user_phone_temp)): ?>
                                        <div class="cd-vrow"><span><?= $cdIcon('phone') ?> Yeni Telefon</span><strong><?= html_escape($user->user_phone_temp) ?></strong></div>
                                    <?php endif; ?>
                                </div>
                            </section>
                        <?php endif; ?>
                    </aside>

                    <div class="cd-overview-right">

                        <section class="cd-panel">
                            <div class="cd-panel-head">
                                <div>
                                    <h3>Hesap Bakiyeleri</h3>
                                    <p>Müşterinin güncel cüzdan bilgileri</p>
                                </div>
                            </div>
                            <div class="cd-balance-grid">
                                <div class="cd-balance-main">
                                    <small>Çekilebilir Bakiye</small>
                                    <strong><?= number_format($userBalance2['withdrawable_balance'], 2, ',', '.') ?> TL</strong>
                                    <span>Çekim talebine uygun bakiye</span>
                                </div>
                                <div class="cd-balance-main alt">
                                    <small>Harcanabilir Bakiye</small>
                                    <strong><?= number_format($userBalance2['spendable_balance'], 2, ',', '.') ?> TL</strong>
                                    <span>Alışverişlerde kullanılabilir</span>
                                </div>
                            </div>
                            <div class="cd-balance-mini">
                                <div>
                                    <small>Güncel Toplam Bakiye</small>
                                    <b><?= number_format($userBalance2['total_balance'], 2, ',', '.') ?> TL</b>
                                </div>
                                <div>
                                    <small title="Kullanıcının yaptığı tüm bakiye yüklemelerinin toplamı">Toplam Yükleme</small>
                                    <b class="up"><?= number_format($userBalance['balanceTotal'], 2, ',', '.') ?> TL</b>
                                </div>
                                <div>
                                    <small title="Kullanıcının yaptığı tüm harcamaların toplamı">Toplam Harcama</small>
                                    <b class="down"><?= number_format($userBalance['spent'], 2, ',', '.') ?> TL</b>
                                </div>
                                <div>
                                    <small>Kredi Limiti</small>
                                    <b><?= number_format((float)$user->user_credit_limit, 2, ',', '.') ?> TL</b>
                                    <button type="button" class="cd-icon-btn" data-bs-toggle="modal" data-bs-target="#kt_modal_update_details" title="Kredi limitini düzenle" aria-label="Kredi limitini düzenle"><?= $cdIcon('edit') ?></button>
                                </div>
                            </div>
                        </section>

                        <section class="cd-panel">
                            <div class="cd-panel-head">
                                <div>
                                    <h3>Banka Hesap Bilgileri</h3>
                                    <p>Müşteriye kayıtlı banka hesapları</p>
                                </div>
                            </div>
                            <?php if (empty($bank_accounts)): ?>
                                <div class="cd-empty-state">Kayıtlı banka hesabı bulunmuyor.</div>
                            <?php else: ?>
                                <div class="cd-table-wrap">
                                    <table class="table align-middle cd-pay-table">
                                        <thead><tr><th>Banka</th><th>IBAN</th><th>Eklenme Tarihi</th><th class="text-end">İşlem</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($bank_accounts as $acc): ?>
                                                <tr>
                                                    <td><?= html_escape($acc->bank_name) ?: '—' ?></td>
                                                    <td class="cd-ref"><?= html_escape(trim(chunk_split(preg_replace('/\s+/', '', (string)$acc->iban), 4, ' '))) ?></td>
                                                    <td><?= html_escape($acc->created_at) ?></td>
                                                    <td class="text-end"><button type="button" class="cd-icon-btn danger" onclick="deleteBankAcc(<?= (int)$acc->id ?>)" title="IBAN sil" aria-label="IBAN sil"><?= $cdIcon('minus') ?></button></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </section>

                        <section class="cd-panel">
                            <div class="cd-panel-head">
                                <div>
                                    <h3>Personel Notları</h3>
                                    <p>Yalnızca yöneticilerin görebildiği notlar</p>
                                </div>
                                <button type="button" class="cd-button" data-bs-toggle="modal" data-bs-target="#kt_modal_user_notes"><?= $cdIcon('plus') ?> Not Ekle</button>
                            </div>
                            <?php if (trim((string)$user->user_notes) === ''): ?>
                                <div class="cd-empty-state">Henüz personel notu eklenmemiş.</div>
                            <?php else: ?>
                                <div class="cd-note-body"><?= nl2br(html_escape($user->user_notes)) ?></div>
                            <?php endif; ?>
                        </section>

                        <section class="cd-panel">
                            <div class="cd-panel-head">
                                <div>
                                    <h3>Son Ödeme Hareketleri</h3>
                                    <p>Müşterinin ödeme, legacy ödeme ve iade kayıtları</p>
                                </div>
                            </div>

                            <div class="cd-table-wrap">
                                    <table id="paymentTable" class="table align-middle">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th>#</th>
                                                <th>Tarih</th>
                                                <th>Yöntem / Kart / Amaç</th>
                                                <th>Tutar</th>
                                                <th>Referans No</th>
                                                <th>Durum</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-700">
                                            <?php if (!empty($lastPayments)): ?>
                                                <?php foreach ($lastPayments as $index => $row): ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td><?= html_escape($row->user_balance_payment_date) ?></td>
                                                        <td>
                                                            <span class="badge badge-light-<?= html_escape($row->bankColor) ?>">
                                                                <?= html_escape($row->bankText) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= nFormat($row->user_balance_amount) ?> ₺</td>
                                                        <td><?= html_escape($row->user_balance_reference_no) ?></td>
                                                        <td>
                                                            <span class="badge badge-light-<?= html_escape($row->statusColor) ?>">
                                                                <?= html_escape($row->statusText) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <?php foreach ($legacy_payments as $legacyPayment): ?>
                                                <tr>
                                                    <td><span class="badge badge-light-warning">Legacy</span></td>
                                                    <td><?= html_escape($legacyPayment->payment_date ?: '-') ?></td>
                                                    <td>
                                                        <span class="badge badge-light-info"><?= html_escape($legacyPayment->method_text ?: 'Eski ödeme') ?></span>
                                                        <?php if (!empty($legacyPayment->masked_card)): ?>
                                                            <div class="text-gray-600 fs-7 mt-1">Kart: <?= html_escape($legacyPayment->masked_card) ?></div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($legacyPayment->purpose_text)): ?>
                                                            <div class="text-gray-600 fs-7 mt-1">Amaç: <?= html_escape($legacyPayment->purpose_text) ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= nFormat($legacyPayment->amount) ?> <?= html_escape($legacyPayment->currency ?: 'TRY') ?>
                                                        <?php if (isset($legacyPayment->net_amount) && $legacyPayment->net_amount !== ''): ?>
                                                            <div class="text-gray-600 fs-7 mt-1">Net: <?= nFormat($legacyPayment->net_amount) ?> <?= html_escape($legacyPayment->currency ?: 'TRY') ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= html_escape($legacyPayment->source_payment_id ?: '-') ?>
                                                        <?php if (!empty($legacyPayment->source_order_id)): ?>
                                                            <div class="text-gray-600 fs-7 mt-1">Sipariş: <?= html_escape($legacyPayment->source_order_id) ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><span class="badge badge-light-warning"><?= html_escape($legacyPayment->status_text ?: 'Legacy') ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php foreach ($legacy_cancellations as $legacyCancellation): ?>
                                                <tr>
                                                    <td><span class="badge badge-light-warning">Legacy</span></td>
                                                    <td><?= html_escape($legacyCancellation->refund_date ?: '-') ?></td>
                                                    <td><span class="badge badge-light-danger">Eski iade</span></td>
                                                    <td><?= nFormat($legacyCancellation->amount) ?> <?= html_escape($legacyCancellation->currency ?: 'TRY') ?></td>
                                                    <td><?= html_escape($legacyCancellation->legacy_cancellation_key ?: '-') ?></td>
                                                    <td><span class="badge badge-light-danger"><?= html_escape($legacyCancellation->status_text ?: 'İade') ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($lastPayments) && empty($legacy_payments) && empty($legacy_cancellations)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-gray-500 py-6">Ödeme kaydı bulunamadı.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                            </div>
                        </section>

                    </div>
                </div>
            </div>

            <!-- =========================================
                 FATURA BİLGİLERİ
            ========================================== -->
            <div class="tab-pane fade" id="kt_user_billing_info" role="tabpanel">
                <div class="card pt-4 mb-6 shadow-sm card-flush">
                    <div class="card-header border-0">
                        <div class="card-title">
                            <h2>Kayıtlı Fatura Adresleri</h2>
                            <span class="badge badge-light-primary ms-3 fs-7"><?= count($user_addresses) ?> Adres</span>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-primary" onclick="openNewAddressModal()">
                                <i class="ki-duotone ki-plus fs-3 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Yeni Adres Ekle
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0 pb-5">

                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-6 p-5">
                            <i class="ki-duotone ki-information fs-2tx text-primary me-4">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <div class="fs-6 text-gray-700">
                                        Kullanıcının sistemde kayıtlı olan tüm fatura adresleridir. Öncelikli olan varsayılan adres sipariş faturalandırmalarında kullanılır.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($user_addresses)): ?>
                            <div class="row g-5">
                                <?php foreach ($user_addresses as $addr): ?>
                                    <div class="col-xl-6 col-md-12">
                                        <div class="card h-100 border <?= ($addr->user_addresses_is_default == 1) ? 'border-primary' : 'border-gray-300' ?>" style="<?= ($addr->user_addresses_is_default == 1) ? 'background-color: rgba(63, 66, 84, 0.02);' : '' ?>">
                                            <div class="card-body p-5 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="fs-5 fw-bold text-gray-900"><?= html_escape($addr->user_addresses_title ?: 'Fatura Adresi') ?></span>
                                                            <span class="text-muted fs-8">#<?= $addr->user_addresses_id ?></span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <?php if ($addr->user_addresses_invoice_type == 2): ?>
                                                                <span class="badge badge-light-primary fw-bold fs-8">Kurumsal</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-light-info fw-bold fs-8">Bireysel</span>
                                                            <?php endif; ?>

                                                            <?php if ($addr->user_addresses_is_default == 1): ?>
                                                                <span class="badge badge-success fw-bold fs-8"><i class="fa fa-check text-white me-1 fs-9"></i> Varsayılan</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <div class="fw-bold fs-6 text-gray-800">
                                                            <?= html_escape($addr->user_addresses_invoice_type == 2 ? $addr->user_addresses_company_name : $addr->user_addresses_full_name) ?>
                                                        </div>
                                                        <?php if (!empty($addr->user_addresses_tax_number)): ?>
                                                            <div class="text-muted fs-7 mt-1">
                                                                <span class="fw-semibold text-gray-700"><?= $addr->user_addresses_invoice_type == 2 ? 'VKN:' : 'TCKN:' ?></span> <?= html_escape($addr->user_addresses_tax_number) ?>
                                                                <?php if (!empty($addr->user_addresses_tax_office)): ?>
                                                                    <span class="ms-1">(<?= html_escape($addr->user_addresses_tax_office) ?> Vergi Dairesi)</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if ($addr->user_addresses_invoice_type == 2 && (int)$addr->user_addresses_is_e_invoice_taxpayer === 1): ?>
                                                            <div class="mt-1">
                                                                <span class="badge badge-light-warning fs-9">E-Fatura Mükellefi</span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="separator separator-dashed my-3"></div>

                                                    <div class="text-gray-700 fs-7 mb-2">
                                                        <i class="ki-duotone ki-geolocation fs-6 text-muted me-1"><span class="path1"></span><span class="path2"></span></i>
                                                        <strong><?= html_escape($addr->user_addresses_district) ?> / <?= html_escape($addr->user_addresses_city) ?></strong>
                                                        <?= (!empty($addr->user_addresses_country) && $addr->user_addresses_country !== 'Türkiye') ? ' - ' . html_escape($addr->user_addresses_country) : '' ?>
                                                        <?= !empty($addr->user_addresses_postal_code) ? ' (' . html_escape($addr->user_addresses_postal_code) . ')' : '' ?>
                                                    </div>

                                                    <div class="text-gray-600 fs-7 bg-light rounded p-3 mb-4" style="min-height: 45px;">
                                                        <?= nl2br(html_escape($addr->user_addresses_address)) ?>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center pt-2">
                                                    <div>
                                                        <?php if ($addr->user_addresses_is_default != 1): ?>
                                                            <button type="button" class="btn btn-sm btn-light-success py-1 px-3 fs-7" onclick="setDefaultAddress(<?= (int)$addr->user_addresses_id ?>)">
                                                                <i class="ki-duotone ki-check fs-6 me-1"><span class="path1"></span><span class="path2"></span></i> Varsayılan Yap
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-light-primary py-1 px-3 fs-7" onclick="editUserAddress(<?= htmlspecialchars(json_encode($addr), ENT_QUOTES, 'UTF-8') ?>)">
                                                            <i class="ki-duotone ki-pencil fs-6 me-1"><span class="path1"></span><span class="path2"></span></i> Düzenle
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-light-danger py-1 px-3 fs-7" onclick="deleteUserAddress(<?= (int)$addr->user_addresses_id ?>)">
                                                            <i class="ki-duotone ki-trash fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> Sil
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="ki-duotone ki-address-book fs-4x text-muted mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="text-gray-600 fs-6 fw-semibold mb-3">Bu kullanıcıya ait kayıtlı fatura adresi bulunmuyor.</div>
                                <?php if (!empty($user->user_bill_company_name) || !empty($user->user_bill_address)): ?>
                                    <div class="alert alert-light-warning d-inline-block text-start p-4 mb-4" style="max-width: 500px;">
                                        <div class="fw-bold mb-1 text-warning">Eski kullanıcı profilinde tekil fatura bilgisi mevcut:</div>
                                        <div class="fs-7 text-gray-700"><strong>Ünvan:</strong> <?= html_escape($user->user_bill_company_name) ?></div>
                                        <div class="fs-7 text-gray-700"><strong>Konum:</strong> <?= html_escape($user->user_bill_district . ' / ' . $user->user_bill_city) ?></div>
                                        <div class="fs-7 text-gray-700"><strong>Adres:</strong> <?= html_escape($user->user_bill_address) ?></div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-light-warning me-2" onclick="openLegacyAddressModal()">
                                            <i class="ki-duotone ki-copy fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Bu Bilgileri Adres Olarak Kaydet
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="openNewAddressModal()">
                                            <i class="ki-duotone ki-plus fs-5 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Yeni Adres Ekle
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="openNewAddressModal()">
                                        <i class="ki-duotone ki-plus fs-5 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Yeni Adres Ekle
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- IBAN BİLGİLERİ -->
                <div class="card pt-4 mb-6 shadow-sm card-flush">
                    <div class="card-header border-0">
                        <div class="card-title">
                            <h2>Kayıtlı Banka Hesapları (IBAN)</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0 pb-5">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-125px">Banka Adı</th>
                                        <th class="min-w-200px">IBAN</th>
                                        <th class="min-w-125px">Eklenme Tarihi</th>
                                        <th class="text-end min-w-70px">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    <?php if (!empty($bank_accounts)): ?>
                                        <?php foreach ($bank_accounts as $acc): ?>
                                            <tr>
                                                <td><?= html_escape($acc->bank_name) ?></td>
                                                <td style="font-family: monospace; letter-spacing: 1px;"><?= html_escape($acc->iban) ?></td>
                                                <td><?= date('d.m.Y H:i', strtotime($acc->created_at)) ?></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-light-danger" onclick="deleteBankAcc(<?= $acc->id ?>)">
                                                        <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> Sil
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Kullanıcının kayıtlı banka hesabı bulunmuyor.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>



            <!-- =========================================
                 SİPARİŞLER
            ========================================== -->
            <div class="tab-pane fade" id="kt_user_view_orders" role="tabpanel">
                <div class="cd-pay-kpis cd-ord-kpis">
                    <div><small>Toplam Sipariş Sayısı</small><strong id="ordStatTotal">—</strong><span>Yeni ve legacy siparişler</span></div>
                    <div><small>Başarılı Sipariş</small><strong class="up" id="ordStatOk">—</strong><span>Teslim edilen / tamamlanan</span></div>
                    <div><small>Başarısız Sipariş</small><strong class="down" id="ordStatFailed">—</strong><span>İptal, red ve başarısız</span></div>
                    <div><small>Toplam Sipariş Tutarı</small><strong id="ordStatAmount">—</strong><span>Yalnızca başarılı siparişler</span></div>
                </div>

                <div class="cd-panel">
                    <div class="cd-panel-head">
                        <div>
                            <h3>Siparişler</h3>
                            <p>Müşterinin oluşturduğu sipariş kayıtları</p>
                        </div>
                    </div>
                    <div class="cd-pay-toolbar">
                        <div class="cd-pay-search"><input id="searchOrderID" type="search" inputmode="numeric" class="form-control form-control-sm form-control-solid" placeholder="Sipariş numarası ile ara..." autocomplete="off"></div>
                        <button type="button" class="btn btn-sm cd-filter-toggle" data-target="#cdOrdFilters" aria-expanded="false">Filtreler <span id="ordFilterCount" class="cd-filter-count"></span></button>
                        <button type="button" class="btn btn-sm" id="ordResetBtn">Temizle</button>
                    </div>
                    <div class="cd-filter-box" id="cdOrdFilters" hidden>
                        <div class="d-flex flex-wrap gap-3 align-items-end">
                            <div class="cd-ord-field">
                                <label for="startDate">Başlangıç Tarihi</label>
                                <input id="startDate" class="form-control form-control-sm form-control-solid" type="date" max="<?= date('Y-m-d') ?>" style="color-scheme: dark;">
                            </div>
                            <div class="cd-ord-field">
                                <label for="endDate">Bitiş Tarihi</label>
                                <input id="endDate" class="form-control form-control-sm form-control-solid" type="date" max="<?= date('Y-m-d') ?>" style="color-scheme: dark;">
                            </div>
                            <div class="cd-ord-field">
                                <label for="searchStatusGroup">Durum</label>
                                <select id="searchStatusGroup" class="form-select form-select-sm form-select-solid">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="success">Başarılı</option>
                                    <option value="failed">Başarısız</option>
                                    <option value="pending">Bekleyen / İşlemde</option>
                                </select>
                            </div>
                            <div class="cd-ord-field wide">
                                <label for="searchCategory">Kategori</label>
                                <select id="searchCategory" class="form-select form-select-sm form-select-solid">
                                    <option value="">Tüm Kategoriler</option>
                                    <?php foreach ($categories as $category) : ?>
                                        <option value="<?= (int)$category->category_id ?>"><?= html_escape($category->category_title) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="filterBtn">Filtrele</button>
                        </div>
                    </div>
                    <div class="cd-table-wrap">
                        <table id="orderTable2" class="table align-middle cd-pay-table w-100">
                            <thead>
                                <tr>
                                    <th>Sipariş No</th>
                                    <th>Ürün</th>
                                    <th>Sipariş Bilgisi</th>
                                    <th>Satış Tutarı</th>
                                    <th>Maliyet / Ödeme</th>
                                    <th>Tarih</th>
                                    <th>Durum</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- =========================================
                 HESAP HAREKETLERİ
            ========================================== -->
            <div class="tab-pane fade" id="kt_user_movements" role="tabpanel">
                <?php
                $mvIn = (float)($movementSums->inflow ?? 0);
                $mvOut = (float)($movementSums->outflow ?? 0);
                $mvKinds = [
                    0 => ['Bekleyen İşlem', 'wait', ''],
                    1 => ['Bakiye Girişi', 'in', '+'],
                    2 => ['İptal Edildi', 'cancel', ''],
                    3 => ['Harcama / Düşüm', 'out', '−'],
                    4 => ['Reddedildi / İade', 'cancel', ''],
                ];
                ?>
                <div class="cd-pay-kpis">
                    <div><small>Toplam Giriş</small><strong class="up"><?= number_format($mvIn, 2, ',', '.') ?> TL</strong><span>Onaylı bakiye girişleri</span></div>
                    <div><small>Toplam Çıkış</small><strong class="down"><?= number_format($mvOut, 2, ',', '.') ?> TL</strong><span>Harcama ve düşümler</span></div>
                    <div><small>Güncel Net</small><strong><?= number_format((float)$userBalance2['total_balance'], 2, ',', '.') ?> TL</strong><span>Çekilebilir + harcanabilir</span></div>
                    <div><small>Hareket Kaydı</small><strong><?= (int)($movementSums->cnt ?? 0) ?></strong><span>Son <?= count($movements) ?> kayıt listeleniyor</span></div>
                </div>

                <div class="cd-panel">
                    <div class="cd-panel-head">
                        <div>
                            <h3>Hesap Hareketleri</h3>
                            <p>Bakiye yükleme, harcama, iptal ve iade hareketleri</p>
                        </div>
                    </div>
                    <div class="cd-pay-toolbar">
                        <div class="cd-pay-search"><input id="cdMvSearch" class="form-control form-control-sm form-control-solid" placeholder="Not, hareket no veya işlem yapan ara..." autocomplete="off"></div>
                        <select id="cdMvKind" class="form-select form-select-sm form-select-solid" style="width:190px" aria-label="Hareket türü">
                            <option value="">Tüm Hareketler</option>
                            <option value="in">Bakiye Girişleri</option>
                            <option value="out">Harcama / Düşümler</option>
                            <option value="wait">Bekleyenler</option>
                            <option value="cancel">İptal / İade</option>
                        </select>
                        <span class="cd-mv-count" id="cdMvCount"></span>
                    </div>
                    <div class="cd-table-wrap">
                        <table class="table align-middle cd-pay-table" id="cdMovementsTable">
                            <thead>
                                <tr>
                                    <th>Hareket No</th>
                                    <th>Tarih</th>
                                    <th>İşlem</th>
                                    <th>Bakiye Türü</th>
                                    <th>Tutar</th>
                                    <th>Not</th>
                                    <th>İşlemi Yapan</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($movements)): ?>
                                    <tr><td colspan="8" class="text-center text-muted py-8">Hesap hareketi bulunamadı.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($movements as $mv): ?>
                                        <?php
                                        $st = (int)$mv->user_balance_status;
                                        $kind = $mvKinds[$st] ?? ['Bilinmiyor', 'cancel', ''];
                                        $mvNote = json_decode((string)$mv->user_balance_note, true);
                                        // Not alanı kart bilgisi içerebilen JSON olabilir; yalnızca mesaj gösterilir
                                        $mvNoteText = is_array($mvNote) ? (string)($mvNote['message'] ?? '') : (string)$mv->user_balance_note;
                                        $mvDate = $mv->user_balance_payment_date ?: $mv->user_balance_created_at;
                                        ?>
                                        <tr data-kind="<?= $kind[1] ?>" data-search="<?= html_escape(mb_strtolower($mv->user_balance_id . ' ' . $mvNoteText . ' ' . $mv->admin_name . ' ' . $mv->user_balance_reference_no, 'UTF-8')) ?>">
                                            <td class="cd-ref">#<?= (int)$mv->user_balance_id ?></td>
                                            <td><?= html_escape($mvDate) ?></td>
                                            <td>
                                                <span class="cd-mv-pill <?= $kind[1] ?>"><?= $kind[0] ?></span>
                                                <?php if (!empty($mv->user_balance_order_id)): ?><small class="cd-sub">Sipariş #<?= (int)$mv->user_balance_order_id ?></small><?php endif; ?>
                                            </td>
                                            <td><?= (int)$mv->user_balance_type === 1 ? 'Çekilebilir' : 'Harcanabilir' ?></td>
                                            <td class="cd-money <?= $kind[1] === 'out' ? 'neg' : ($kind[1] === 'in' ? 'pos' : '') ?>"><?= $kind[2] ?><?= number_format((float)$mv->user_balance_amount, 2, ',', '.') ?> ₺</td>
                                            <td class="cd-note-cell"><?= html_escape(mb_strimwidth($mvNoteText, 0, 90, '…', 'UTF-8')) ?: '—' ?></td>
                                            <td><?= html_escape($mv->admin_name) ?: '—' ?></td>
                                            <td class="text-end"><button type="button" class="cd-table-btn" data-cd-movement="<?= (int)$mv->user_balance_id ?>">Hareket Detayı</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr id="cdMvEmpty" hidden><td colspan="8" class="text-center text-muted py-8">Aramanıza uygun hareket bulunamadı.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- =========================================
                 ÖDEME GEÇMİŞİ
            ========================================== -->
            <div class="tab-pane fade" id="kt_user_transactions" role="tabpanel">
                <?php
                $lfo = $legacyFilterOptions ?? ['payments' => [], 'movements' => []];
                $payOkCount = 0;
                foreach (($lastPayments ?? []) as $p) { if ((int)$p->user_balance_status === 1) $payOkCount++; }
                ?>
                <div class="cd-pay-kpis">
                    <div><small>Toplam Yükleme</small><strong class="up"><?= number_format((float)$userBalance['balanceTotal'], 2, ',', '.') ?> TL</strong></div>
                    <div><small>Toplam Harcama</small><strong class="down"><?= number_format((float)$userBalance['spent'], 2, ',', '.') ?> TL</strong></div>
                    <div><small>Güncel Ödeme Kaydı</small><strong><?= count($lastPayments ?? []) ?></strong><span><?= $payOkCount ?> onaylı</span></div>
                    <div><small>Legacy Ödeme Kaydı</small><strong><?= count($legacy_payments ?? []) ?></strong><span>Eski sistem</span></div>
                </div>

                <div class="cd-panel">
                    <ul class="nav cd-subtabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#cd_pay_current" role="tab">Güncel Ödemeler</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#cd_pay_spending" role="tab">Güncel Harcamalar (<?= count($spendingMovements ?? []) ?>)</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#cd_pay_pending_orders" role="tab">Ödeme Bekleniyor (<?= count($pendingPaymentOrders ?? []) ?>)</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#cd_pay_legacy" role="tab">Legacy Ödemeler</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#cd_pay_moves" role="tab">Legacy Bakiye Hareketleri</a></li>
                    </ul>

                    <div class="tab-content">
                    <div class="tab-pane fade show active" id="cd_pay_current" role="tabpanel">
                        <div class="cd-table-wrap">
                            <table id="transactionTable" class="table align-middle cd-pay-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tarih</th>
                                        <th>Yöntem</th>
                                        <th>Tutar</th>
                                        <th>Referans No</th>
                                        <th>İşlemi Yapan</th>
                                        <th>Not</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($lastPayments)): ?>
                                        <?php foreach ($lastPayments as $index => $row): ?>
                                            <tr>
                                                <td class="cd-muted"><?= $index + 1 ?></td>
                                                <td>
                                                    <?= html_escape($row->user_balance_created_at) ?>
                                                    <?php if (!empty($row->user_balance_change_date)): ?><small class="cd-sub">İşlem: <?= html_escape($row->user_balance_change_date) ?></small><?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (trim((string)$row->bankText) !== ''): ?>
                                                        <span class="badge badge-light-<?= html_escape($row->bankColor) ?>"><?= html_escape($row->bankText) ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-light-warning">Manuel (Yönetici)</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="cd-money"><?= number_format($row->user_balance_amount, 2, ',', '.') ?> ₺</td>
                                                <td class="cd-ref"><?= html_escape($row->user_balance_reference_no) ?: '—' ?></td>
                                                <td>
                                                    <?= html_escape(trim(($row->create_admin_name ?? '') . ' ' . ($row->create_admin_email ?? ''))) ?: '—' ?>
                                                    <?php if (!empty($row->approve_admin_name)): ?><small class="cd-sub">Kredi onayı: <?= html_escape($row->approve_admin_name) ?><?= !empty($row->user_balance_credit_approve_date) ? ' · ' . html_escape($row->user_balance_credit_approve_date) : '' ?></small><?php endif; ?>
                                                </td>
                                                <td class="cd-note-cell"><?= html_escape($row->user_balance_note) ?: '—' ?></td>
                                                <td>
                                                    <span class="badge badge-light-<?= html_escape($row->statusColor) ?>"><?= html_escape($row->statusText) ?></span>
                                                </td>
                                                <td class="text-end cd-actions-cell">
                                                    <button type="button" class="cd-table-btn" data-cd-payment="<?= (int)$row->user_balance_id ?>">Ödeme Detayı</button>
                                                    <?php if ($row->user_balance_bank_id == 4 && $row->user_balance_status != 0): ?>
                                                        <a href="<?= base_url('users/approve_credit/' . (int)$row->user_balance_id) ?>" class="btn btn-sm btn-success approve-credit-btn" data-id="<?= (int)$row->user_balance_id ?>">Krediyi Onayla</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-8">Hareket bulunamadı.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="cd_pay_spending" role="tabpanel">
                        <div class="cd-table-wrap">
                            <table class="table align-middle cd-pay-table">
                                <thead>
                                    <tr>
                                        <th>Harcama No</th>
                                        <th>Tarih</th>
                                        <th>Sipariş No</th>
                                        <th>Bakiye Türü</th>
                                        <th>Tutar</th>
                                        <th>Açıklama</th>
                                        <th>İşlemi Yapan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($spendingMovements)): ?>
                                        <?php foreach ($spendingMovements as $spending): ?>
                                            <?php
                                            $spendingNote = json_decode((string)$spending->user_balance_note, true);
                                            $spendingNoteText = is_array($spendingNote) ? (string)($spendingNote['message'] ?? '') : (string)$spending->user_balance_note;
                                            $spendingDate = $spending->user_balance_payment_date ?: $spending->user_balance_created_at;
                                            ?>
                                            <tr>
                                                <td class="cd-ref">#<?= (int)$spending->user_balance_id ?></td>
                                                <td><?= html_escape($spendingDate) ?></td>
                                                <td><?= !empty($spending->user_balance_order_id) ? '#' . (int)$spending->user_balance_order_id : '—' ?></td>
                                                <td><?= (int)$spending->user_balance_type === 1 ? 'Çekilebilir' : 'Harcanabilir' ?></td>
                                                <td class="cd-money neg">−<?= number_format((float)$spending->user_balance_amount, 2, ',', '.') ?> TL</td>
                                                <td class="cd-note-cell"><?= html_escape($spendingNoteText) ?: '—' ?></td>
                                                <td><?= html_escape($spending->admin_name ?? '') ?: '—' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted py-8">Harcama kaydı bulunamadı.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($spendingMovements ?? []) >= 300): ?>
                            <div class="text-muted fs-7 px-4 pb-4">En son 300 harcama kaydı gösteriliyor.</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="cd_pay_pending_orders" role="tabpanel">
                        <div class="cd-table-wrap">
                            <table class="table align-middle cd-pay-table">
                                <thead>
                                    <tr>
                                        <th>Sipariş No</th>
                                        <th>Tarih</th>
                                        <th>Ürün</th>
                                        <th>Ödeme Yöntemi</th>
                                        <th>Tutar</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pendingPaymentOrders)): ?>
                                        <?php foreach ($pendingPaymentOrders as $pendingOrder): ?>
                                            <tr>
                                                <td class="cd-ref">#<?= (int)$pendingOrder->order_id ?></td>
                                                <td><?= html_escape($pendingOrder->order_created_at) ?></td>
                                                <td><?= html_escape($pendingOrder->product_name ?: 'Ürün') ?> <span class="text-muted">x<?= (int)$pendingOrder->order_quantity ?></span></td>
                                                <td><?= html_escape([2 => 'Cüzdan', 3 => 'Havale / EFT', 4 => 'Kredi Kartı', 5 => 'Mobil Ödeme'][(int)$pendingOrder->order_payment_type] ?? 'Bilinmiyor') ?></td>
                                                <td class="cd-money"><?= number_format((float)$pendingOrder->order_sale_price, 2, ',', '.') ?> <?= html_escape($pendingOrder->order_sale_price_currency ?: 'TL') ?></td>
                                                <td><span class="cd-mv-pill wait">Ödeme Bekleniyor</span></td>
                                                <td class="text-end"><a class="cd-table-btn" href="<?= base_url('orders/orderInvoice/' . (int)$pendingOrder->order_id) ?>" target="_blank" rel="noopener">Sipariş Detayı</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted py-8">Ödeme bekleyen sipariş bulunamadı.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($pendingPaymentOrders ?? []) >= 300): ?>
                            <div class="text-muted fs-7 px-4 pb-4">En son 300 ödeme bekleyen sipariş gösteriliyor.</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="cd_pay_legacy" role="tabpanel">
                        <div class="cd-pay-toolbar">
                            <div class="cd-pay-search"><input id="lpQ" class="form-control form-control-sm form-control-solid" placeholder="Ödeme/sipariş no, kart, amaç ara..."></div>
                            <button type="button" class="btn btn-sm cd-filter-toggle" data-target="#cdLpFilters" aria-expanded="false">Filtreler</button>
                            <button type="button" class="btn btn-sm cd-cols-toggle" data-table="lp" aria-pressed="false">Tüm Sütunlar</button>
                        </div>
                        <div class="cd-filter-box" id="cdLpFilters" hidden>
                            <div class="d-flex flex-wrap gap-3 align-items-end">
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Yöntem</label>
                                <select id="lpMethod" class="form-select form-select-sm form-select-solid" style="min-width:150px;">
                                    <option value="">Tümü</option>
                                    <?php foreach ($lfo['payments']['methods'] ?? [] as $v): ?><option value="<?= html_escape($v) ?>"><?= html_escape($v) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Durum</label>
                                <select id="lpStatus" class="form-select form-select-sm form-select-solid" style="min-width:150px;">
                                    <option value="">Tümü</option>
                                    <?php foreach ($lfo['payments']['statuses'] ?? [] as $v): ?><option value="<?= html_escape($v) ?>"><?= html_escape($v) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Amaç</label>
                                <select id="lpPurpose" class="form-select form-select-sm form-select-solid" style="min-width:150px;">
                                    <option value="">Tümü</option>
                                    <?php foreach ($lfo['payments']['purposes'] ?? [] as $v): ?><option value="<?= html_escape($v) ?>"><?= html_escape($v) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Para Birimi</label>
                                <select id="lpCurrency" class="form-select form-select-sm form-select-solid" style="min-width:110px;">
                                    <option value="">Tümü</option>
                                    <?php foreach ($lfo['payments']['currencies'] ?? [] as $v): ?><option value="<?= html_escape($v) ?>"><?= html_escape($v) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Maskeli Kart</label>
                                <input id="lpCard" class="form-control form-control-sm form-control-solid" style="min-width:150px;" placeholder="örn: 4543 veya 1234">
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Tarih (başlangıç)</label>
                                <input id="lpDateFrom" type="date" class="form-control form-control-sm form-control-solid">
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Tarih (bitiş)</label>
                                <input id="lpDateTo" type="date" class="form-control form-control-sm form-control-solid">
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Min tutar</label>
                                <input id="lpAmountMin" type="number" step="0.01" class="form-control form-control-sm form-control-solid" style="width:110px;">
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Maks tutar</label>
                                <input id="lpAmountMax" type="number" step="0.01" class="form-control form-control-sm form-control-solid" style="width:110px;">
                            </div>
                            <button type="button" class="btn btn-sm btn-light-primary" id="lpFilterBtn">Ara</button>
                            <button type="button" class="btn btn-sm btn-light" id="lpResetBtn">Sıfırla</button>
                            </div>
                        </div>
                        <div class="cd-table-wrap">
                            <table id="legacyPaymentsTable" class="table align-middle cd-pay-table w-100">
                                <thead>
                                    <tr>
                                        <th>Ödeme No</th>
                                        <th>Sipariş No</th>
                                        <th>Kaynak Müşteri</th>
                                        <th>Tarih</th>
                                        <th>Yöntem</th>
                                        <th>Maskeli Kart</th>
                                        <th>Kart Sahibi</th>
                                        <th>IP</th>
                                        <th>Amaç</th>
                                        <th>Tutar</th>
                                        <th>Net Tutar</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="cd_pay_moves" role="tabpanel">
                        <div class="cd-pay-toolbar">
                            <div class="cd-pay-search"><input id="lmQ" class="form-control form-control-sm form-control-solid" placeholder="Ürün, açıklama, ödeme no ara..."></div>
                            <button type="button" class="btn btn-sm cd-filter-toggle" data-target="#cdLmFilters" aria-expanded="false">Filtreler</button>
                            <button type="button" class="btn btn-sm cd-cols-toggle" data-table="lm" aria-pressed="false">Tüm Sütunlar</button>
                        </div>
                        <div class="cd-filter-box" id="cdLmFilters" hidden>
                            <div class="d-flex flex-wrap gap-3 align-items-end">
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">İşlem Türü</label>
                                <select id="lmType" class="form-select form-select-sm form-select-solid" style="min-width:160px;">
                                    <option value="">Tümü</option>
                                    <?php foreach ($lfo['movements']['types'] ?? [] as $v): ?><option value="<?= html_escape($v) ?>"><?= html_escape($v) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Bakiye Türü</label>
                                <select id="lmBalanceType" class="form-select form-select-sm form-select-solid" style="min-width:160px;">
                                    <option value="">Tümü</option>
                                    <?php foreach ($lfo['movements']['balance_types'] ?? [] as $v): ?><option value="<?= html_escape($v) ?>"><?= html_escape($v) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Yön</label>
                                <select id="lmDirection" class="form-select form-select-sm form-select-solid" style="min-width:120px;">
                                    <option value="">Tümü</option>
                                    <option value="in">Giriş (+)</option>
                                    <option value="out">Çıkış (-)</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Para Birimi</label>
                                <select id="lmCurrency" class="form-select form-select-sm form-select-solid" style="min-width:110px;">
                                    <option value="">Tümü</option>
                                    <?php foreach ($lfo['movements']['currencies'] ?? [] as $v): ?><option value="<?= html_escape($v) ?>"><?= html_escape($v) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Aktarım</label>
                                <select id="lmLinked" class="form-select form-select-sm form-select-solid" style="min-width:140px;">
                                    <option value="">Tümü</option>
                                    <option value="yes">Bakiye kaydına aktarılmış</option>
                                    <option value="no">Aktarılmamış</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Tarih (başlangıç)</label>
                                <input id="lmDateFrom" type="date" class="form-control form-control-sm form-control-solid">
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Tarih (bitiş)</label>
                                <input id="lmDateTo" type="date" class="form-control form-control-sm form-control-solid">
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Min tutar</label>
                                <input id="lmAmountMin" type="number" step="0.01" class="form-control form-control-sm form-control-solid" style="width:110px;">
                            </div>
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Maks tutar</label>
                                <input id="lmAmountMax" type="number" step="0.01" class="form-control form-control-sm form-control-solid" style="width:110px;">
                            </div>
                            <button type="button" class="btn btn-sm btn-light-primary" id="lmFilterBtn">Ara</button>
                            <button type="button" class="btn btn-sm btn-light" id="lmResetBtn">Sıfırla</button>
                            </div>
                        </div>
                        <div class="cd-table-wrap">
                            <table id="legacyMovementsTable" class="table align-middle cd-pay-table w-100">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>İşlem Türü</th>
                                        <th>Bakiye Türü</th>
                                        <th>Tutar</th>
                                        <th>Ürün</th>
                                        <th>Açıklama</th>
                                        <th>Müşteri Açıklaması</th>
                                        <th>Sipariş Satır No</th>
                                        <th>Ödeme No</th>
                                        <th>İşlemi Yapan</th>
                                        <th>Aktarılan Bakiye Kaydı</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <!-- =========================================
                 KULLANICI HAREKETLERİ
            ========================================== -->

            <div class="tab-pane fade" id="kt_user_event_logs" role="tabpanel">
                <div class="card pt-4 mb-6 card-flush">
                    <div class="card-header border-0">
                        <div class="card-title">
                            <h2>Kullanıcı Hareketleri</h2>
                        </div>
                    </div>

                    <div class="card-body pt-0">

                        <div class="d-flex flex-wrap gap-3 align-items-end mb-6">
                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Event</label>
                                <select id="logEvent" class="form-select form-select-sm form-select-solid" style="min-width:160px;">
                                    <option value="">Tümü</option>
                                    <option value="REGISTER">REGISTER</option>
                                    <option value="LOGIN">LOGIN (Login)</option>
                                    <option value="LOGOUT">LOGOUT (Logout)</option>
                                    <option value="VIEW">VIEW (Page View)</option>
                                    <option value="PASS-CHANGE">PASS-CHANGE (Şifre Değiştirme)</option>
                                    <option value="FORGOT-PASS">FORGOT-PASS (Şifremi Unuttum)</option>
                                    <option value="FAILED-LOGIN">FAILED-LOGIN (Başarısız Giriş)</option>
                                    <option value="ORDER">ORDER (Sipariş Oluşturma)</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label fs-7 text-muted mb-1">IP</label>
                                <input id="logIP" class="form-control form-control-sm form-control-solid" style="min-width:180px;" placeholder="örn: 1.2.3.4">
                            </div>

                            <div>
                                <label class="form-label fs-7 text-muted mb-1">Sayfa</label>
                                <input id="logPage" class="form-control form-control-sm form-control-solid" style="min-width:220px;" placeholder="örn: /account">
                            </div>

                            <button class="btn btn-sm btn-light-primary" id="logFilterBtn">Ara</button>
                            <button class="btn btn-sm btn-light" id="logResetBtn">Sıfırla</button>
                        </div>

                        <div class="table-responsive">
                            <table id="userLogTable" class="table align-middle table-row-dashed fs-6 gy-5 w-100">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>#</th>
                                        <th>Tarih</th>
                                        <th>Event</th>
                                        <th>Sayfa</th>
                                        <th>Ülke</th>
                                        <th>IP</th>
                                        <th>User Agent</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="kt_user_invoices" role="tabpanel">
                <div class="card pt-4 mb-6 shadow-sm card-flush">
                    <div class="card-header border-0">
                        <div class="card-title">
                            <h2>Kesilmiş Faturalar</h2>
                        </div>
                        <div class="card-toolbar">
                            <span class="badge badge-light-success fs-6"><?= isset($invoices) ? count($invoices) : 0 ?> Fatura</span>
                        </div>
                    </div>

                    <div class="card-body pt-0 pb-5">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="invoicesTable">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-125px">Fatura No / Tarih</th>
                                        <th class="text-center">SiparişNo/Tarih Aralığı</th>
                                        <th class="text-center">İşlem Hacmi</th>
                                        <th class="text-end">Tutar (Satış)</th>
                                        <th class="text-end min-w-100px">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($invoices)): ?>
                                        <?php foreach ($invoices as $inv): ?>
                                            <tr>
                                                <td>
                                                    <span class="text-gray-800 fw-bold d-block fs-6"><?= html_escape($inv['order_invoice_number']) ?></span>
                                                    <span class="text-muted fw-semibold d-block fs-7"><?= html_escape($inv['order_invoice_date']) ?></span>
                                                </td>

                                                <td class="text-center">
                                                    <span class="badge badge-light-info fs-8 mb-1" title="Sipariş ID Aralığı">
                                                        #<?= $inv['first_order_id'] ?> - #<?= $inv['last_order_id'] ?>
                                                    </span>
                                                    <span class="text-muted fw-semibold d-block fs-8" title="Sipariş Tarihi Aralığı">
                                                        <?= date('d.m.Y', strtotime($inv['first_order_date'])) ?> - <?= date('d.m.Y', strtotime($inv['last_order_date'])) ?>
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <span class="text-gray-800 fw-bold"><?= (int)$inv['total_orders'] ?></span>
                                                    <span class="text-muted fs-7"> Adet</span>
                                                </td>

                                                <td class="text-end">
                                                    <span class="text-gray-800 fw-bold d-block fs-6"><?= number_format($inv['total_sale_price'], 2, ',', '.') ?> ₺</span>
                                                    <span class="text-muted fw-semibold d-block fs-8" title="Maliyet">Mal: <?= number_format($inv['total_cost'], 2, ',', '.') ?> ₺</span>
                                                </td>

                                                <td class="text-end">
                                                    <a href="<?= base_url('users/invoiceDetail/') ?><?= html_escape($inv['order_invoice_uuid']) ?>"
                                                        target="_blank"
                                                        class="btn btn-sm btn-light-primary btn-active-primary view-invoice-btn"
                                                        data-invoice-uuid="<?= html_escape($inv['order_invoice_uuid']) ?>"
                                                        data-invoice-no="<?= html_escape($inv['order_invoice_number']) ?>">
                                                        <i class="ki-duotone ki-document fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                        Gör
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-8">Bu kullanıcıya ait kesilmiş fatura bulunamadı.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =========================================
                 REFERANSLAR TABLOSU
            ========================================== -->
            <div class="tab-pane fade" id="kt_user_referrals" role="tabpanel">
                <?php
                $refTotal = count($referredUsers);
                $refMonthKey = date('Y-m');
                $refThisMonth = 0;
                $refMonths = [];
                for ($i = 5; $i >= 0; $i--) { $refMonths[date('Y-m', strtotime("first day of -$i month"))] = 0; }
                foreach ($referredUsers as $ru) {
                    $mk = date('Y-m', strtotime($ru->user_created_at));
                    if ($mk === $refMonthKey) $refThisMonth++;
                    if (isset($refMonths[$mk])) $refMonths[$mk]++;
                }
                $refMax = max(1, max($refMonths));
                $refLast = $refTotal ? date('d.m.Y', strtotime($referredUsers[0]->user_created_at)) : '—';
                ?>
                <?php $refLink = !empty($user->user_nickname) ? rtrim((string)topupcenter(), '/') . '/register?ref=' . rawurlencode($user->user_nickname) : ''; ?>
                <div class="cd-panel mb-5">
                    <div class="cd-panel-head"><div><h3>Referans Linki</h3><p>Bu müşterinin kayıt davet linki</p></div></div>
                    <div class="p-5">
                        <?php if ($refLink && !empty($user->user_ref_permission)): ?>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cd_ref_link" value="<?= html_escape($refLink) ?>" readonly>
                                <button type="button" class="btn btn-warning" onclick="var i=document.getElementById('cd_ref_link');i.select();navigator.clipboard?navigator.clipboard.writeText(i.value):document.execCommand('copy');this.textContent='Kopyalandı';">Kopyala</button>
                            </div>
                        <?php else: ?>
                            <span class="text-muted"><?= $refLink ? 'Kullanıcıya referans olma izni verilmemiş; link çalışmaz.' : 'Kullanıcının kullanıcı adı (nickname) yok.' ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="cd-pay-kpis">
                    <div><small>Toplam Referans</small><strong><?= $refTotal ?></strong><span>Kayıtlı müşteri</span></div>
                    <div><small>Bu Ay</small><strong class="up"><?= $refThisMonth ?></strong><span>Yeni referans kaydı</span></div>
                    <div><small>Son Kayıt</small><strong><?= $refLast ?></strong><span>En son referans olunan</span></div>
                    <div><small>Referans İzni</small><strong class="<?= !empty($user->user_ref_permission) ? 'up' : 'down' ?>"><?= !empty($user->user_ref_permission) ? 'Verildi' : 'Yok' ?></strong><span>Referans olma yetkisi</span></div>
                </div>

                <div class="cd-panel" style="margin-bottom:14px">
                    <div class="cd-panel-head"><div><h3>Aylık Referans Kayıtları</h3><p>Son 6 ay</p></div></div>
                    <div class="cd-ref-chart">
                        <?php foreach ($refMonths as $mk => $cnt): ?>
                            <div class="cd-ref-bar" title="<?= html_escape($mk) ?>: <?= (int)$cnt ?> kayıt">
                                <span class="cd-ref-val"><?= (int)$cnt ?></span>
                                <i style="height: <?= max(3, (int)round($cnt / $refMax * 100)) ?>%"></i>
                                <em><?= html_escape(date('m.Y', strtotime($mk . '-01'))) ?></em>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="cd-panel">
                    <div class="cd-panel-head">
                        <div><h3>Referans Olunan Kullanıcılar</h3><p>Bu müşterinin referansıyla kayıt olanlar</p></div>
                        <a href="<?= base_url('users/referralReportDetail/' . (int)$user->user_id) ?>" class="cd-button">Ciro / kâr raporunu gör</a>
                    </div>
                    <div class="cd-pay-toolbar">
                        <div class="cd-pay-search"><input id="cdRefSearch" class="form-control form-control-sm form-control-solid" placeholder="Ad, e-posta veya ID ara..." autocomplete="off"></div>
                        <span class="cd-mv-count" id="cdRefCount"></span>
                    </div>
                    <div class="cd-table-wrap">
                        <table class="table align-middle cd-pay-table" id="kt_table_referrals">
                            <thead>
                                <tr><th>ID</th><th>Kullanıcı</th><th>Takma Ad</th><th>E-posta</th><th>Kayıt Tarihi</th><th class="text-end">İşlem</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($referredUsers)): ?>
                                    <?php foreach ($referredUsers as $rUser): ?>
                                        <tr data-search="<?= html_escape(mb_strtolower($rUser->user_id . ' ' . $rUser->user_name . ' ' . $rUser->user_nickname . ' ' . $rUser->user_email, 'UTF-8')) ?>">
                                            <td class="cd-ref">#<?= (int)$rUser->user_id ?></td>
                                            <td><?= html_escape($rUser->user_name) ?></td>
                                            <td><?= html_escape($rUser->user_nickname ?: '—') ?></td>
                                            <td><?= html_escape($rUser->user_email) ?></td>
                                            <td><?= html_escape(date('d.m.Y H:i', strtotime($rUser->user_created_at))) ?></td>
                                            <td class="text-end"><a class="cd-table-btn" style="display:inline-flex;align-items:center;text-decoration:none" href="<?= base_url('users/detail/' . (int)$rUser->user_id) ?>">Müşteri Detayı</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted py-8">Kayıtlı referans bulunamadı.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                    $(document).on('input', '#cdRefSearch', function() {
                        var q = this.value.toLocaleLowerCase('tr'), n = 0, all = $('#kt_table_referrals tbody tr[data-search]');
                        all.each(function() { var ok = !q || this.getAttribute('data-search').indexOf(q) !== -1; this.hidden = !ok; if (ok) n++; });
                        $('#cdRefCount').text(all.length ? n + ' / ' + all.length + ' kayıt' : '');
                    });
                </script>
            </div>
            
        </div>

    </div>
</div>





<!-- ======================
     MODALS
====================== -->

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sipariş Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" id="orderDetailContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Order Status Change Modal -->
<div class="modal fade" id="orderStatusChangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sipariş Durumu Değiştir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>

            <div class="modal-body" id="orderStatusChangeContent">
                <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed mb-6 p-6">
                    <i class="ki-duotone ki-information fs-2tx text-danger me-4">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <div class="fs-6 text-gray-700">
                                Bu alan çok dikkatli kullanılmalıdır. Sipariş durumunu değiştirme hakkında bilginiz yoksa değiştirmeyin.
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="order-id">

                <div class="mb-4">
                    <label class="form-label fw-semibold">Yeni Durum</label>
                    <select name="orderStatus" id="orderStatus" class="form-select form-select-solid">
                        <option value="">Seçiniz</option>
                        <option value="1">Tamamlandı</option>
                        <option value="2">İptal Edildi</option>
                        <option value="4">Satıcı Onayladı</option>
                        <option value="5">Satıcı Onaylamadı</option>
                    </select>
                </div>

                <button class="btn btn-success" id="changeStatusBtn">Değiştir</button>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Order / Legacy Order Detail Popup -->
<style>
    #cdPop, .cd-edit-modal { font-family: Poppins, Arial, sans-serif; }
    #cdPop { position: fixed; inset: 0; z-index: 99999; background: rgba(5, 6, 12, .78); display: flex; align-items: center; justify-content: center; padding: 20px; }
    #cdPop[hidden] { display: none; }
    #cdPop .cd-pop { width: min(760px, 96vw); max-height: 92vh; display: flex; flex-direction: column; background: #191925; border: 1px solid #3c3c51; border-radius: 12px; box-shadow: 0 25px 80px #000a; overflow: hidden; color: #d8d8e6; }
    #cdPop .cd-pop-head { display: flex; align-items: center; justify-content: space-between; padding: 17px 20px; border-bottom: 1px solid #303044; }
    #cdPop .cd-pop-head h3 { margin: 0; color: #fff; font-size: 15px; font-weight: 700; }
    #cdPop .cd-pop-head button { background: none; border: 0; color: #a6a8bc; font-size: 17px; cursor: pointer; }
    #cdPop .cd-pop-head button:hover { color: #ffc21a; }
    #cdPop .cd-pop-body { padding: 16px 20px 6px; overflow-y: auto; min-height: 0; }
    #cdPop .cd-pop-foot { padding: 13px 20px; border-top: 1px solid #303044; display: flex; justify-content: flex-end; }
    #cdPop .cd-pop-btn { height: 33px; padding: 0 16px; border-radius: 6px; border: 1px solid #3a3a51; background: #27273a; color: #e8e8f2; font: 600 10px Poppins, Arial, sans-serif; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; }
    #cdPop .cd-pop-btn:hover { background: #35354b; color: #fff; }
    #cdPop .cd-pop-sub { color: #9494aa; font-size: 11px; margin: 0 0 12px; }
    #cdPop .cd-pop-tabs { display: flex; gap: 6px; border-bottom: 1px solid #303044; margin-bottom: 14px; overflow-x: auto; }
    #cdPop .cd-pop-tabs button { border: 0; border-bottom: 2px solid transparent; background: none; color: #aaaac0; padding: 10px 12px; cursor: pointer; font: 600 10px Poppins, Arial, sans-serif; white-space: nowrap; }
    #cdPop .cd-pop-tabs button.active { color: #ffc21a; border-bottom-color: #ffc21a; }
    #cdPop .cd-pop-sum { display: flex; justify-content: space-between; align-items: center; gap: 14px; padding: 14px 16px; background: #1a1a27; border: 1px solid #303044; border-radius: 9px; margin-bottom: 12px; }
    #cdPop .cd-pop-prod { display: flex; align-items: center; gap: 12px; min-width: 0; }
    #cdPop .cd-pop-thumb { width: 52px; height: 52px; border-radius: 9px; background: #253650; color: #83bdff; display: grid; place-items: center; font-weight: 700; font-size: 18px; flex: none; object-fit: cover; overflow: hidden; }
    #cdPop .cd-pop-prod > div { display: grid; gap: 3px; min-width: 0; }
    #cdPop .cd-pop-prod small, #cdPop .cd-pop-prod span { color: #8f94af; font-size: 10px; }
    #cdPop .cd-pop-prod strong { color: #fff; font-size: 13.5px; overflow-wrap: anywhere; }
    #cdPop .cd-pop-tag { padding: 5px 10px; border-radius: 5px; font-size: 10px; font-weight: 700; white-space: nowrap; background: #44341c; color: #ffcf6b; }
    #cdPop .cd-pop-tag.ok { background: #18382d; color: #63d9a6; }
    #cdPop .cd-pop-tag.bad { background: #40252e; color: #f18b9b; }
    #cdPop .cd-pop-figs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 12px; }
    #cdPop .cd-pop-figs > div { padding: 13px 14px; background: #222233; border: 1px solid #35364d; border-radius: 8px; display: grid; gap: 6px; min-width: 0; }
    #cdPop .cd-pop-figs small { font-size: 10px; color: #9da0b9; }
    #cdPop .cd-pop-figs strong { font-size: 15px; color: #f5f5fb; overflow-wrap: anywhere; }
    #cdPop .cd-pop-sec { border: 1px solid #35354b; border-radius: 8px; margin-bottom: 12px; overflow: hidden; background: #1a1a27; }
    #cdPop .cd-pop-sec > header { padding: 12px 15px; border-bottom: 1px solid #303044; color: #f0f0f8; font-size: 12px; font-weight: 700; }
    #cdPop .cd-pop-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 0 18px; padding: 4px 15px 10px; }
    #cdPop .cd-pop-field { border-bottom: 1px solid #2a2a3c; padding: 10px 0; min-width: 0; }
    #cdPop .cd-pop-field small { display: block; color: #9999af; font-size: 10px; margin-bottom: 4px; }
    #cdPop .cd-pop-field strong { color: #f3f5f6; font-size: 11px; font-weight: 600; overflow-wrap: anywhere; line-height: 1.5; }
    #cdPop .cd-pop-table-wrap { overflow-x: auto; }
    #cdPop table { width: 100%; border-collapse: collapse; }
    #cdPop th { padding: 10px 14px; background: #1a1a27; color: #a7a7bb; font-size: 10px; font-weight: 600; text-align: left; border-bottom: 1px solid #303044; white-space: nowrap; }
    #cdPop td { padding: 10px 14px; border-bottom: 1px solid #29293a; font-size: 10.5px; color: #d8d8e6; }
    #cdPop tr:last-child td { border-bottom: 0; }
    #cdPop .cd-pop-empty, #cdPop .cd-pop-loading { padding: 26px 10px; text-align: center; color: #9999af; font-size: 11px; }
    #cdPop .cd-pop-links { display: flex; flex-wrap: wrap; gap: 8px; padding: 14px 15px; }
    @media (max-width: 640px) { #cdPop .cd-pop-fields, #cdPop .cd-pop-figs { grid-template-columns: 1fr; } #cdPop .cd-pop-sum { flex-direction: column; align-items: flex-start; } }

    #cdPop .cd-pop-success { display: grid; justify-items: center; gap: 12px; padding: 28px 10px 20px; text-align: center; }
    #cdPop .cd-pop-success span { display: grid; place-items: center; width: 54px; height: 54px; border-radius: 50%; background: #18382d; color: #63d9a6; font-size: 26px; }
    #cdPop .cd-pop-success strong { font-size: 14px; color: #fff; }
    #cdPop .cd-pop-form { display: grid; gap: 12px; margin-bottom: 14px; }
    .cd-order-prod { display: flex; align-items: center; gap: 10px; min-width: 200px; }
    .cd-order-prod > div { display: grid; gap: 2px; min-width: 0; }
    .cd-order-prod small { color: #8f94af; font-size: 9px; }
    .cd-order-prod strong { color: #f3f5f6; font-size: 10.5px; font-weight: 600; overflow-wrap: anywhere; }
    .cd-order-prod span { color: #9999af; font-size: 9px; }
    .cd-order-thumb { width: 34px; height: 34px; border-radius: 8px; object-fit: cover; flex: none; }
    .cd-order-thumb.ph { display: grid; place-items: center; background: #253650; color: #83bdff; font-weight: 700; font-size: 13px; }
    .cd-req-line { display: flex; align-items: center; gap: 6px; font-size: 10px; line-height: 1.7; }
    .cd-req-line span { color: #9999af; }
    .cd-req-line b { color: #e8e8f2; font-weight: 600; }
    .cd-copy-btn { border: 0; background: none; color: #8e90a7; cursor: pointer; padding: 0 3px; }
    .cd-copy-btn:hover { color: #ffc21a; }
    .cd-muted { color: #6d6d84; }
    .cd-muted-num { color: #b5b5ca; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .cd-money { font-weight: 700; color: #f3f5f6; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .cd-ref { font-weight: 600; color: #d8d8e6; white-space: nowrap; }
    .cd-legacy-tag { display: inline-block; margin-left: 4px; padding: 2px 6px; border-radius: 4px; background: #44341c; color: #ffcf6b; font-size: 8.5px; font-weight: 700; }
    .cd-ord-field { display: grid; gap: 5px; min-width: 150px; }
    .cd-ord-field.wide { min-width: 220px; }
    .cd-ord-field label { font-size: 10px; color: #9999af; margin: 0; }
    .cd-filter-count:not(:empty) { margin-left: 4px; padding: 1px 6px; border-radius: 10px; background: #ffc21a; color: #17131a; font-size: 9px; }
    .cd-ord-kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    #cdPop .cd-pop-form label { display: grid; gap: 5px; font-size: 10px; color: #a9abc1; font-weight: 500; }
    #cdPop .cd-pop-form input, #cdPop .cd-pop-form select, #cdPop .cd-pop-form textarea { width: 100%; min-height: 36px; padding: 8px 11px; background: #222233; border: 1px solid #343449; border-radius: 6px; color: #eee; font: 11px Poppins, Arial, sans-serif; }
    #cdPop .cd-pop-form textarea { resize: vertical; }
    #cdPop .cd-pop-form input:focus, #cdPop .cd-pop-form select:focus, #cdPop .cd-pop-form textarea:focus { outline: 0; border-color: #c68e1a; }
    #cdPop .cd-pop-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    #cdPop .cd-pop-actions { display: flex; justify-content: flex-end; gap: 8px; padding-top: 4px; }
    #cdPop .cd-pop-btn.primary { background: #ffc21a; border-color: #ffc21a; color: #17131a; }
    #cdPop .cd-pop-btn.primary:hover { background: #ffca46; color: #17131a; }
    #cdPop .cd-pop-btn.danger { background: #40252e; border-color: #703047; color: #ff8da8; }
    #cdPop .cd-pop-btn:disabled { opacity: .45; cursor: not-allowed; }
    #cdPop .cd-pop-preview { padding: 10px 12px; border-radius: 6px; background: #1a1a27; border: 1px solid #303044; color: #d8d8e6; font-size: 11px; }
    #cdPop .cd-pop-preview.warn { border-color: #6b5420; background: #2c2515; color: #ffcf6b; }
    #cdPop .cd-pop-preview.bad { border-color: #703047; background: #2b1d29; color: #ff8da8; }
    #cdPop .cd-pop-note { padding: 10px 12px; border-radius: 6px; background: #172233; border: 1px solid #2a4b65; color: #b7d8f5; font-size: 10.5px; line-height: 1.6; margin-bottom: 12px; }
    #cdPop .cd-pop-note.warn { background: #2c2515; border-color: #6b5420; color: #ffcf6b; }
    #cdPop .cd-pop-form .cd-pop-note { margin: 0; background: #2b1d29; border-color: #703047; color: #ff8da8; }
    #cdPop .cd-pop-note[hidden] { display: none; }
    .cd-restrict-banner { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 16px; border: 1px solid #703047; background: #2b1d29; border-radius: 8px; }
    .cd-restrict-banner > div { display: grid; gap: 4px; min-width: 0; }
    .cd-restrict-banner strong { color: #ff8da8; font-size: 12px; }
    .cd-restrict-banner span, .cd-restrict-banner small { color: #d8b8c4; font-size: 10.5px; overflow-wrap: anywhere; }
    .cd-restrict-banner small { color: #a88a96; }
    .user-detail-page .cd-pay-table .cd-money.pos { color: #63d9a6; }
    .user-detail-page .cd-pay-table .cd-money.neg { color: #ff668f; }
    .cd-mv-pill { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 9px; font-weight: 600; white-space: nowrap; background: #44341c; color: #ffcf6b; }
    .cd-mv-pill.in { background: #18382d; color: #63d9a6; }
    .cd-mv-pill.out { background: #40252e; color: #f18b9b; }
    .cd-mv-pill.cancel { background: #2a2a3c; color: #a9abc1; }
    .cd-mv-count { margin-left: auto; font-size: 10px; color: #9999af; }
    @media (max-width: 640px) { #cdPop .cd-pop-grid { grid-template-columns: 1fr; } .cd-restrict-banner { flex-direction: column; align-items: flex-start; } }

    .cd-edit-modal .modal-content { background: #191925; border: 1px solid #3c3c51; border-radius: 12px; color: #d8d8e6; }
    .cd-edit-modal .modal-header { padding: 17px 22px; border-bottom: 1px solid #303044; display: flex; align-items: center; justify-content: space-between; }
    .cd-edit-modal .modal-header h2 { margin: 0; font-size: 15px; color: #fff; }
    .cd-edit-modal .cd-modal-x { background: none; border: 0; color: #a6a8bc; font-size: 17px; cursor: pointer; }
    .cd-edit-modal .cd-modal-x:hover { color: #ffc21a; }
    .cd-edit-modal .modal-body { padding: 4px 22px 14px; max-height: 70vh; overflow-y: auto; }
    .cd-edit-modal .cd-edit-sub { font-size: 10.5px; color: #ffc21a; margin: 10px 0 8px; }
    .cd-edit-modal .select2-selection__arrow { height: 38px !important; right: 8px !important; }
    .cd-edit-modal .select2-selection__arrow b { border-color: #9999af transparent transparent transparent !important; }
    .cd-edit-modal .cd-edit-sec { display: flex; align-items: center; gap: 8px; margin-top: 14px; padding-bottom: 8px; border-bottom: 1px solid #303044; color: #f0f0f8; font-size: 12px; font-weight: 700; }
    .cd-edit-modal .cd-edit-sec svg { width: 15px; height: 15px; color: #ffc21a; }
    .cd-edit-modal .form-label { font-size: 10px; color: #a9abc1; margin-bottom: 5px; }
    .cd-edit-modal .form-control, .cd-edit-modal .form-select { height: 38px; min-height: 38px; padding: 0 12px; font-size: 11px; line-height: 38px; background: #222233 !important; border: 1px solid #343449 !important; border-radius: 6px !important; color: #eee !important; box-shadow: none; }
    .cd-edit-modal input[type="date"].form-control { line-height: normal; }
    .cd-edit-modal .select2-container { width: 100% !important; }
    .cd-edit-modal .select2-container .select2-selection--single { display: flex !important; align-items: center; height: 38px !important; min-height: 38px !important; padding: 0 34px 0 12px !important; background: #222233 !important; border: 1px solid #343449 !important; border-radius: 6px !important; }
    .cd-edit-modal .select2-container--open .select2-selection--single, .cd-edit-modal .select2-container--focus .select2-selection--single { border-color: #c68e1a !important; }
    .cd-edit-modal .select2-selection__rendered { padding: 0 !important; line-height: 1.2 !important; color: #eee !important; font-size: 11px !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cd-edit-modal .select2-selection__arrow { position: absolute !important; top: 0 !important; right: 10px !important; height: 38px !important; width: 16px !important; display: flex; align-items: center; }
    .cd-edit-modal .select2-selection__arrow b { position: static !important; margin: 0 !important; border-color: #9999af transparent transparent transparent !important; border-width: 5px 4px 0 4px !important; }
    .cd-edit-modal .form-control[readonly] { color: #9999af !important; }
    .cd-edit-modal .form-control:focus { border-color: #c68e1a !important; box-shadow: none; }
    .cd-edit-modal textarea.form-control { height: auto; line-height: 1.6; padding: 10px 12px; }
    .select2-container--open .select2-dropdown { background: #222233; border: 1px solid #3a3a51; border-radius: 6px; color: #eee; }
    .select2-container--open .select2-results__option { font-size: 11px; color: #d8d8e6; padding: 8px 12px; }
    .select2-container--open .select2-results__option--highlighted { background: #35354b !important; color: #ffc21a !important; }
    .select2-container--open .select2-search__field { background: #1a1a27; border: 1px solid #343449; color: #eee; }
    .cd-edit-modal .modal-footer { padding: 13px 22px; border-top: 1px solid #303044; display: flex; justify-content: flex-end; gap: 8px; }
    .cd-edit-modal .modal-footer .btn { height: 34px; display: inline-flex; align-items: center; gap: 7px; padding: 0 14px; border-radius: 6px; font: 600 10px Poppins, Arial, sans-serif; border: 1px solid #3a3a51; background: #27273a; color: #e8e8f2; }
    .cd-edit-modal .modal-footer .btn-primary { background: #ffc21a; border-color: #ffc21a; color: #17131a; }
    .cd-edit-modal .modal-footer .btn svg { width: 13px; height: 13px; }
    .user-detail-page .cd-table-btn, .cd-table-btn { height: 30px; padding: 0 12px; border: 1px solid #414157; border-radius: 6px; background: #272739; color: #e5e5f0; font: 600 10px Poppins, Arial, sans-serif; white-space: nowrap; cursor: pointer; }
    .cd-table-btn:hover { background: #353548; border-color: #77768a; color: #fff; }
</style>

<div id="cdPop" hidden>
    <div class="cd-pop" role="dialog" aria-modal="true" aria-labelledby="cdPopTitle">
        <div class="cd-pop-head">
            <h3 id="cdPopTitle">Sipariş Detayı</h3>
            <button type="button" id="cdPopClose" aria-label="Kapat">✕</button>
        </div>
        <div class="cd-pop-body" id="cdPopBody"></div>
        <div class="cd-pop-foot"><button type="button" class="cd-pop-btn" id="cdPopDismiss">Kapat</button></div>
    </div>
</div>

<script>
(function() {
    var CUSTOMER = <?= json_encode(['name' => $fullName, 'email' => (string)$user->user_email, 'id' => (int)$user->user_id], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var BAL = <?= json_encode(['withdrawable' => (float)$userBalance2['withdrawable_balance'], 'spendable' => (float)$userBalance2['spendable_balance'], 'credit' => (float)$user->user_credit_limit]) ?>;
    var URLS = {
        base: "<?= base_url('users/') ?>",
        restrictCreate: "<?= base_url('users/createRestriction') ?>",
        restrictRemove: "<?= base_url('users/removeRestriction') ?>",
        order: "<?= site_url('orders/orderDetail/') ?>",
        legacy: "<?= site_url('users/legacyOrderDetail/' . (int)$user->user_id) ?>",
        payment: "<?= site_url('users/paymentDetail/' . (int)$user->user_id . '/') ?>",
        legacyPayment: "<?= site_url('users/legacyPaymentDetail/' . (int)$user->user_id) ?>",
        contract: "<?= base_url('orders/orderContract/') ?>",
        invoice: "<?= base_url('orders/orderInvoice/') ?>"
    };
    var pop = document.getElementById('cdPop'), body = document.getElementById('cdPopBody'), title = document.getElementById('cdPopTitle');
    var lastFocus = null;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) { return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]; });
    }
    function val(v) { return (v === null || v === undefined || String(v).trim() === '') ? '—' : String(v); }
    function money(n, cur) {
        var x = Number(n);
        if (!isFinite(x)) return '—';
        return x.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + (cur || 'TL');
    }
    function field(label, value) { return '<div class="cd-pop-field"><small>' + esc(label) + '</small><strong>' + esc(val(value)) + '</strong></div>'; }
    function section(name, fields) { return '<section class="cd-pop-sec"><header>' + esc(name) + '</header><div class="cd-pop-fields">' + fields.map(function(f) { return field(f[0], f[1]); }).join('') + '</div></section>'; }
    function tagClass(text, code) {
        var t = String(text || '').toLocaleLowerCase('tr');
        if (String(code) === '1' || /tamam|teslim|onay|başar/.test(t)) return 'ok';
        if (String(code) === '2' || String(code) === '5' || String(code) === '12' || /iptal|red|iade/.test(t)) return 'bad';
        return '';
    }
    function open(heading, sub) {
        lastFocus = document.activeElement;
        title.textContent = heading;
        body.innerHTML = '<div class="cd-pop-sub">' + esc(sub || '') + '</div><div class="cd-pop-loading">Yükleniyor...</div>';
        pop.hidden = false;
        document.body.style.overflow = 'hidden';
        document.getElementById('cdPopClose').focus();
    }
    function close() {
        pop.hidden = true;
        document.body.style.overflow = '';
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    function fail(msg) { body.innerHTML = '<div class="cd-pop-empty">' + esc(msg) + '</div>'; }
    function tabs(sub, items, firstKey) {
        body.innerHTML = '<div class="cd-pop-sub">' + esc(sub) + '</div><div class="cd-pop-tabs">' +
            items.map(function(i) { return '<button type="button" data-tab="' + i.key + '"' + (i.key === firstKey ? ' class="active"' : '') + '>' + esc(i.label) + '</button>'; }).join('') +
            '</div><div id="cdPopPane"></div>';
        var pane = document.getElementById('cdPopPane');
        function show(key) {
            body.querySelectorAll('.cd-pop-tabs button').forEach(function(b) { b.classList.toggle('active', b.getAttribute('data-tab') === key); });
            var item = items.filter(function(i) { return i.key === key; })[0];
            pane.innerHTML = item.render();
        }
        body.querySelector('.cd-pop-tabs').onclick = function(e) {
            var b = e.target.closest('[data-tab]');
            if (b) show(b.getAttribute('data-tab'));
        };
        show(firstKey);
    }
    function requireText(rd) {
        if (!rd || typeof rd !== 'object') return '—';
        var parts = [];
        Object.keys(rd).forEach(function(k) {
            var label = k === 'user_id' ? 'UserID' : k === 'server_id' ? 'Server' : k === 'zone_id' ? 'Zone' : k;
            parts.push(label + ': ' + rd[k]);
        });
        return parts.length ? parts.join(' · ') : '—';
    }

    function renderOrder(d) {
        var qty = Number(d.order_quantity) || 1;
        var statusText = d.order_status_name || 'Bilinmiyor';
        var img = d.product_image && typeof productImageUrl === 'function' ? productImageUrl(d.product_image) : '';
        var thumb = img ? '<img class="cd-pop-thumb" src="' + esc(img) + '" alt="">' : '<div class="cd-pop-thumb">' + esc(String(d.product_name || 'S').charAt(0).toLocaleUpperCase('tr')) + '</div>';
        var channel = Number(d.order_reseller_id) > 0 ? 'API' + (d.reseller_name ? ' · ' + d.reseller_name : '') : 'Web';
        var costTl = isNaN(parseFloat(String(d.order_cost_tl).replace(/\./g, '').replace(',', '.'))) ? d.order_cost_tl : d.order_cost_tl + ' TL';
        var delivered = d.order_voucher ? 'E-Pin kodu tanımlı' : (String(d.order_status) === '1' ? 'Teslim edildi' : 'Teslim bekleniyor');
        var couponDiscount = Number(d.coupon_discount) > 0 ? money(d.coupon_discount) : '—';
        var oid = Number(d.order_id);

        title.textContent = '#' + oid + ' · Sipariş Detayı';
        tabs(d.product_name || '', [
            {key: 'details', label: 'Sipariş Detayı', render: function() {
                return '<div class="cd-pop-sum"><div class="cd-pop-prod">' + thumb + '<div><small>' + esc(val(d.category_title)) + '</small><strong>' + esc(val(d.product_name)) + '</strong><span>' + qty + ' adet · ' + esc(val(d.order_created_at)) + '</span></div></div><span class="cd-pop-tag ' + tagClass(statusText, d.order_status) + '">' + esc(statusText) + '</span></div>' +
                    '<div class="cd-pop-figs"><div><small>Satış Tutarı</small><strong>' + esc(d.order_sale_price ? money(d.order_sale_price, d.order_sale_price_currency || 'TL') : '—') + '</strong></div><div><small>Maliyet (tedarikçi)</small><strong>' + esc(d.order_cost ? money(d.order_cost, d.order_cost_currency || '') : '—') + '</strong></div><div><small>Maliyet (TL)</small><strong>' + esc(val(costTl)) + '</strong></div></div>' +
                    section('Sipariş Bilgileri', [['Sipariş No', '#' + oid], ['Sipariş Durumu', statusText], ['Sipariş Kanalı', channel], ['Sipariş Tarihi', d.order_created_at], ['Kupon', d.coupon_code], ['Kupon İndirimi', couponDiscount], ['Sipariş IP', d.order_user_ip]]) +
                    section('Ürün ve Teslimat', [['Kategori', d.category_title], ['Ürün', d.product_name], ['Adet', qty], ['Sipariş İçeriği', requireText(d.order_require_data)], ['Teslimat', delivered]]) +
                    section('Müşteri Bilgileri', [['Ad Soyad', CUSTOMER.name], ['E-posta', CUSTOMER.email]]);
            }},
            {key: 'contracts', label: 'Sözleşmeler', render: function() {
                return '<section class="cd-pop-sec"><header>Sipariş Sözleşmeleri</header><div class="cd-pop-links">' +
                    '<a class="cd-pop-btn" target="_blank" rel="noopener" href="' + URLS.contract + oid + '/distance-sales-agreement">Mesafeli Satış Sözleşmesi</a>' +
                    '<a class="cd-pop-btn" target="_blank" rel="noopener" href="' + URLS.contract + oid + '/refund-policy">İade Politikası</a></div></section>';
            }},
            {key: 'invoices', label: 'Faturalar', render: function() {
                return '<section class="cd-pop-sec"><header>Sipariş Faturası</header><div class="cd-pop-links"><a class="cd-pop-btn" target="_blank" rel="noopener" href="' + URLS.invoice + oid + '">Fatura Sayfasını Aç</a></div></section>';
            }},
            {key: 'api', label: 'API Geçmişi', render: function() {
                return section('Tedarik ve API İşlem Geçmişi', [['Kanal', channel], ['Tedarikçi', d.supplier_name], ['Tedarikçi Sipariş No', d.order_supplier_order_id], ['Reseller Sipariş No', d.order_reseller_order_id], ['Tedarikçi Okuma', d.order_readed_at], ['Tedarikçi Dönüş', d.order_answered_at], ['Teslim', d.order_sended_at], ['Son Durum', statusText]]);
            }}
        ], 'details');
    }

    function renderLegacy(r) {
        var o = r.order || {}, rows = r.rows || [], pays = r.payments || [], cancels = r.cancellations || [];
        var cur = o.currency || 'TRY';
        var paid = pays.reduce(function(s, p) { return s + (Number(p.amount) || 0); }, 0);
        var refunded = cancels.reduce(function(s, c) { return s + (Number(c.amount) || 0); }, 0);
        var productName = rows.length ? (rows[0].product || 'Eski ürün') + (rows.length > 1 ? ' +' + (rows.length - 1) : '') : (o.product_summary || 'Eski ürün bilgisi');
        var qty = rows.reduce(function(s, x) { return s + (Number(x.quantity) || 0); }, 0) || 1;

        title.textContent = '#' + val(o.order_no) + ' · Sipariş Detayı';
        tabs(productName + ' · Legacy (eski sistem)', [
            {key: 'details', label: 'Sipariş Detayı', render: function() {
                return '<div class="cd-pop-sum"><div class="cd-pop-prod"><div class="cd-pop-thumb">' + esc(String(productName).charAt(0).toLocaleUpperCase('tr')) + '</div><div><small>Legacy (eski sistem)</small><strong>' + esc(productName) + '</strong><span>' + qty + ' adet · ' + esc(val(o.date)) + '</span></div></div><span class="cd-pop-tag ' + tagClass(o.status) + '">' + esc(val(o.status)) + '</span></div>' +
                    '<div class="cd-pop-figs"><div><small>Sipariş Tutarı</small><strong>' + esc(money(o.total_amount, cur)) + '</strong></div><div><small>Ödenen Tutar</small><strong>' + esc(pays.length ? money(paid, cur) : '—') + '</strong></div><div><small>İade Tutarı</small><strong>' + esc(cancels.length ? money(refunded, cur) : '—') + '</strong></div></div>' +
                    section('Sipariş Bilgileri', [['Sipariş No', '#' + val(o.order_no)], ['Sipariş Durumu', o.status], ['Ödeme Yöntemi', o.payment_method], ['Sipariş Tarihi', o.date], ['Sipariş IP', o.order_ip], ['Kayıt Türü', 'Legacy (salt okunur)']]) +
                    '<section class="cd-pop-sec"><header>Ürün ve Teslimat</header><div class="cd-pop-table-wrap"><table><thead><tr><th>Ürün</th><th>Adet</th><th>Birim</th><th>Toplam</th><th>Durum</th><th>Teslim Tarihi</th></tr></thead><tbody>' +
                    (rows.length ? rows.map(function(x) { return '<tr><td>' + esc(val(x.product)) + '</td><td>' + esc(val(x.quantity)) + '</td><td>' + esc(x.unit_price != null ? money(x.unit_price, x.currency || cur) : '—') + '</td><td>' + esc(x.line_total != null ? money(x.line_total, x.currency || cur) : '—') + '</td><td>' + esc(val(x.status)) + '</td><td>' + esc(val(x.completion_date)) + '</td></tr>'; }).join('') : '<tr><td colspan="6" class="cd-pop-empty">' + esc(val(o.product_summary)) + '</td></tr>') +
                    '</tbody></table></div></section>' +
                    section('Müşteri Bilgileri', [['Ad Soyad', (r.customer && r.customer.name) || CUSTOMER.name], ['E-posta', (r.customer && r.customer.email) || CUSTOMER.email]]);
            }},
            {key: 'payments', label: 'Ödemeler (' + pays.length + ')', render: function() {
                if (!pays.length) return '<div class="cd-pop-empty">Bu siparişe bağlı legacy ödeme kaydı bulunmuyor.</div>';
                return '<section class="cd-pop-sec"><div class="cd-pop-table-wrap"><table><thead><tr><th>Ödeme No</th><th>Tarih</th><th>Yöntem</th><th>Kart</th><th>Tutar</th><th>Net</th><th>Durum</th></tr></thead><tbody>' +
                    pays.map(function(p) { return '<tr><td>' + esc(val(p.payment_no)) + '</td><td>' + esc(val(p.date)) + '</td><td>' + esc(val(p.method)) + '</td><td>' + esc(val(p.masked_card)) + '</td><td>' + esc(money(p.amount, p.currency || cur)) + '</td><td>' + esc(p.net_amount != null ? money(p.net_amount, p.currency || cur) : '—') + '</td><td>' + esc(val(p.status)) + '</td></tr>'; }).join('') +
                    '</tbody></table></div></section>';
            }},
            {key: 'refunds', label: 'İadeler (' + cancels.length + ')', render: function() {
                if (!cancels.length) return '<div class="cd-pop-empty">Bu siparişe bağlı iade/iptal kaydı bulunmuyor.</div>';
                return '<section class="cd-pop-sec"><div class="cd-pop-table-wrap"><table><thead><tr><th>Tarih</th><th>Tutar</th><th>Durum</th><th>Açıklama</th></tr></thead><tbody>' +
                    cancels.map(function(c) { return '<tr><td>' + esc(val(c.date)) + '</td><td>' + esc(money(c.amount, c.currency || cur)) + '</td><td>' + esc(val(c.status)) + '</td><td>' + esc(val(c.description)) + '</td></tr>'; }).join('') +
                    '</tbody></table></div></section>';
            }}
        ], 'details');
    }

    function openOrder(id) {
        open('#' + id + ' · Sipariş Detayı', '');
        $.getJSON(URLS.order + encodeURIComponent(id)).done(function(d) {
            if (!d || d.status === 'error' || !d.order_id) return fail((d && d.message) || 'Sipariş detayı alınamadı.');
            renderOrder(d);
        }).fail(function() { fail('Sipariş detayı alınamadı.'); });
    }
    function openLegacy(key) {
        open('Sipariş Detayı', 'Legacy (eski sistem)');
        $.getJSON(URLS.legacy, {key: key}).done(function(r) {
            if (!r || r.status !== 'success') return fail((r && r.message) || 'Legacy sipariş detayı alınamadı.');
            renderLegacy(r);
        }).fail(function() { fail('Legacy sipariş detayı alınamadı.'); });
    }

    function statusTag(text, code) {
        var cls = tagClass(text, code);
        if (/kısmi/i.test(String(text))) cls = '';
        return '<span class="cd-pop-tag ' + cls + '">' + esc(val(text)) + '</span>';
    }
    function payBody(sub, status, method, figs, sections, links) {
        body.innerHTML = '<div class="cd-pop-sub">' + esc(sub) + '</div>' +
            '<div class="cd-pop-sum"><div class="cd-pop-prod"><div><small>Ödeme Durumu</small>' + status + '</div></div><span style="color:#b8bcd0;font-size:12px">' + esc(val(method)) + '</span></div>' +
            '<div class="cd-pop-figs">' + figs.map(function(f) { return '<div><small>' + esc(f[0]) + '</small><strong' + (f[2] ? ' style="color:' + f[2] + '"' : '') + '>' + esc(f[1]) + '</strong></div>'; }).join('') + '</div>' +
            sections + (links || '');
    }

    function renderPayment(r) {
        var p = r.payment, c = r.customer || {};
        title.textContent = '#' + p.id + (payAsMovement ? ' · Hareket Detayı' : ' · Ödeme Detayı');
        var when = p.payment_date || p.created_at;
        payBody(val(p.method) + ' · ' + val(when), statusTag(p.status, p.status_code), p.method,
            payAsMovement
                ? [['Hareket Tutarı', money(p.amount)], ['Yön', p.status_code === 1 ? 'Giriş (+)' : (p.status_code === 3 ? 'Çıkış (−)' : '—'), p.status_code === 1 ? '#63d9a6' : (p.status_code === 3 ? '#ff668f' : '')], ['İade Edilen', money(p.refunded)]]
                : [['Ödeme Tutarı', money(p.amount)], ['Ödenen Tutar', money(p.paid_amount), '#63d9a6'], ['İade Edilen', money(p.refunded)]],
            section('Ödeme Bilgileri', [['Ödeme No', '#' + p.id], ['Ödeme Yöntemi', p.method], ['Ödeme Sağlayıcı İşlem ID', p.transaction_id || p.reference_no], ['İşlem Tarihi', when], ['Ödeme Durumu', p.status], ['Ödeme Tutarı', money(p.amount)], ['Ödenen Tutar', money(p.paid_amount)], ['Kart', p.masked_card], ['Talep Tarihi', p.created_at], ['Onay/İşlem Tarihi', p.change_date], ['İşlemi Yapan', p.admin], ['Not', p.note]]) +
            section('Müşteri ve İşlem Bilgileri', [['Müşteri', c.name || CUSTOMER.name], ['E-posta', c.email || CUSTOMER.email], ['Telefon', c.phone], ['IP Adresi', p.request_ip]]) +
            section('İade Bilgileri', [['Tamamlanan İade', money(p.refunded)], ['Bekleyen İade', money(p.pending_refund)]]),
            p.refundable ? '<div class="cd-pop-links" style="padding:0 0 12px"><a class="cd-pop-btn" target="_blank" rel="noopener" href="' + esc(p.detail_url) + '">İade İşlemi / Ödeme Sayfası</a></div>' : '');
    }

    function renderLegacyPayment(r) {
        var p = r.payment, c = r.customer || {}, cur = p.currency || 'TRY';
        title.textContent = '#' + val(p.payment_no) + ' · Ödeme Detayı';
        payBody(val(p.method) + ' · ' + val(p.date) + ' · Legacy (eski sistem)', statusTag(p.status), p.method,
            [['Ödeme Tutarı', p.amount != null ? money(p.amount, cur) : '—'], ['Net Tutar', p.net_amount != null ? money(p.net_amount, cur) : '—', '#63d9a6'], ['İade Edilen', money(p.refunded, cur)]],
            section('Ödeme Bilgileri', [['Ödeme No', '#' + val(p.payment_no)], ['Sipariş No', p.order_no], ['Ödeme Yöntemi', p.method], ['İşlem Tarihi', p.date], ['Maskeli Kart', p.masked_card], ['Amaç', p.purpose], ['Ödeme Durumu', p.status], ['Kayıt Türü', 'Legacy (salt okunur)']]) +
            section('Müşteri ve İşlem Bilgileri', [['Müşteri', c.name || CUSTOMER.name], ['E-posta', c.email || CUSTOMER.email], ['Telefon', c.phone], ['IP Adresi', p.ip]]) +
            section('İade Bilgileri', [['Tamamlanan İade', money(p.refunded, cur)]]), '');
    }

    function openPayment(id, asMovement) {
        payAsMovement = !!asMovement;
        open('#' + id + (payAsMovement ? ' · Hareket Detayı' : ' · Ödeme Detayı'), '');
        $.getJSON(URLS.payment + encodeURIComponent(id)).done(function(r) {
            if (!r || r.status !== 'success') return fail((r && r.message) || 'Ödeme detayı alınamadı.');
            renderPayment(r);
        }).fail(function() { fail('Ödeme detayı alınamadı.'); });
    }
    function openLegacyPayment(key) {
        open('Ödeme Detayı', 'Legacy (eski sistem)');
        $.getJSON(URLS.legacyPayment, {key: key}).done(function(r) {
            if (!r || r.status !== 'success') return fail((r && r.message) || 'Legacy ödeme detayı alınamadı.');
            renderLegacyPayment(r);
        }).fail(function() { fail('Legacy ödeme detayı alınamadı.'); });
    }

    var payAsMovement = false;

    function openForm(heading, sub, html) {
        open(heading, sub);
        body.innerHTML = '<div class="cd-pop-sub">' + esc(sub) + '</div>' + html;
    }
    function formMsg(msg) {
        var el = document.getElementById('cdFormMsg');
        if (el) { el.textContent = msg || ''; el.hidden = !msg; }
    }

    function balanceForm(kind) {
        var add = kind === 'add';
        openForm(add ? 'Bakiye Ekle' : 'Bakiye Çıkar', CUSTOMER.name + ' · #' + CUSTOMER.id,
            '<div class="cd-pop-figs"><div><small>Çekilebilir Bakiye</small><strong>' + esc(money(BAL.withdrawable)) + '</strong></div><div><small>Harcanabilir Bakiye</small><strong>' + esc(money(BAL.spendable)) + '</strong></div><div><small>Kredi Limiti</small><strong>' + esc(money(BAL.credit)) + '</strong></div></div>' +
            '<form method="post" id="cdBalForm" class="cd-pop-form" action="' + URLS.base + (add ? 'addBalance/' : 'removeBalance/') + CUSTOMER.id + '">' +
            '<div class="cd-pop-grid"><label>Tutar (₺)<input type="number" name="amount" id="cdBalAmount" min="0.01" step="0.01" required placeholder="Örn: 100,50" autocomplete="off"></label>' +
            '<label>Bakiye Türü<select name="type" id="cdBalType"><option value="2">Harcanabilir Bakiye</option><option value="1">Çekilebilir Bakiye</option></select></label></div>' +
            '<label>Not' + (add ? ' (opsiyonel)' : ' (çıkarma nedeni)') + '<input type="text" name="note" maxlength="200" placeholder="' + (add ? 'Örn: Elden alındı, havale' : 'Örn: Hatalı yükleme düzeltmesi') + '" autocomplete="off"></label>' +
            '<div class="cd-pop-preview" id="cdBalPreview">İşlem sonrası bakiye: —</div>' +
            '<div class="cd-pop-note" id="cdFormMsg" hidden></div>' +
            '<div class="cd-pop-actions"><button type="button" class="cd-pop-btn" data-cd-close>Vazgeç</button><button type="submit" class="cd-pop-btn ' + (add ? 'primary' : 'danger') + '" id="cdBalSubmit">' + (add ? 'Bakiye Ekle' : 'Bakiye Çıkar') + '</button></div></form>');

        var amountEl = document.getElementById('cdBalAmount'), typeEl = document.getElementById('cdBalType'), prev = document.getElementById('cdBalPreview'), submit = document.getElementById('cdBalSubmit');
        function update() {
            var amt = parseFloat(amountEl.value), cur = typeEl.value === '1' ? BAL.withdrawable : BAL.spendable;
            submit.disabled = false; prev.className = 'cd-pop-preview';
            if (!isFinite(amt) || amt <= 0) { prev.textContent = 'İşlem sonrası bakiye: —'; return; }
            var next = add ? cur + amt : cur - amt;
            prev.textContent = 'İşlem sonrası ' + (typeEl.value === '1' ? 'çekilebilir' : 'harcanabilir') + ' bakiye: ' + money(next);
            if (!add && amt > cur + BAL.credit) {
                prev.className = 'cd-pop-preview bad';
                prev.textContent += ' · Tutar bakiye ve kredi limitini aşıyor';
                submit.disabled = true;
            } else if (!add && next < 0) {
                prev.className = 'cd-pop-preview warn';
                prev.textContent += ' · Kredi limiti kullanılacak';
            }
        }
        amountEl.addEventListener('input', update); typeEl.addEventListener('change', update);
        document.getElementById('cdBalForm').addEventListener('submit', function(e) {
            var amt = parseFloat(amountEl.value);
            if (!isFinite(amt) || amt <= 0) { e.preventDefault(); formMsg('Lütfen geçerli bir tutar girin.'); return; }
            if (!add && !confirm(money(amt) + ' tutarında bakiye hesaptan düşülecek. Onaylıyor musunuz?')) { e.preventDefault(); return; }
            submit.disabled = true; submit.textContent = 'İşleniyor...';
        });
        amountEl.focus();
    }

    function restrictForm() {
        openForm('Müşteri Kısıtlama', CUSTOMER.name + ' · #' + CUSTOMER.id,
            '<div class="cd-pop-note warn">Kısıtlama anında uygulanır ve müşterinin açık oturumları sonlandırılır. Müşteri, aşağıdaki açıklamayı görür.</div>' +
            '<form id="cdRestrictForm" class="cd-pop-form">' +
            '<label>Süre<select name="duration_minutes"><option value="60">1 saat</option><option value="1440">24 saat</option><option value="10080">7 gün</option><option value="43200">30 gün</option><option value="0">Süresiz</option></select></label>' +
            '<label>Müşteriye gösterilecek açıklama<textarea name="customer_reason" rows="3" maxlength="500" required placeholder="Müşterinin göreceği mesaj"></textarea></label>' +
            '<label>Yönetici notu (yalnızca personel görür)<textarea name="admin_reason" rows="3" maxlength="1000" required placeholder="Kısıtlama gerekçesi"></textarea></label>' +
            '<div class="cd-pop-note" id="cdFormMsg" hidden></div>' +
            '<div class="cd-pop-actions"><button type="button" class="cd-pop-btn" data-cd-close>Vazgeç</button><button type="submit" class="cd-pop-btn danger" id="cdRestrictSubmit">Hesabı Kısıtla</button></div></form>');
        document.getElementById('cdRestrictForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('cdRestrictSubmit'), data = $(this).serializeArray();
            data.push({name: 'user_id', value: CUSTOMER.id});
            btn.disabled = true; formMsg('');
            $.post(URLS.restrictCreate, data, null, 'json').done(function(r) {
                if (r && r.status === 'success') { window.location.reload(); return; }
                btn.disabled = false; formMsg((r && r.message) || 'Kısıtlama kaydedilemedi.');
            }).fail(function(x) { btn.disabled = false; formMsg((x.responseJSON && x.responseJSON.message) || 'Kısıtlama kaydedilemedi.'); });
        });
    }

    function unrestrictForm(restrictionId) {
        openForm('Kısıtlamayı Kaldır', CUSTOMER.name + ' · #' + CUSTOMER.id,
            '<div class="cd-pop-note warn">Kısıtlama kaldırılınca müşteri hesabına yeniden erişebilir.</div>' +
            '<form id="cdUnrestrictForm" class="cd-pop-form"><label>Kaldırma gerekçesi<textarea name="removal_reason" rows="3" maxlength="500" placeholder="Opsiyonel"></textarea></label>' +
            '<div class="cd-pop-note" id="cdFormMsg" hidden></div>' +
            '<div class="cd-pop-actions"><button type="button" class="cd-pop-btn" data-cd-close>Vazgeç</button><button type="submit" class="cd-pop-btn primary" id="cdUnrestrictSubmit">Kısıtlamayı Kaldır</button></div></form>');
        document.getElementById('cdUnrestrictForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('cdUnrestrictSubmit');
            btn.disabled = true; formMsg('');
            $.post(URLS.restrictRemove, {restriction_id: restrictionId, removal_reason: this.elements.removal_reason.value}, null, 'json').done(function(r) {
                if (r && r.status === 'success') { window.location.reload(); return; }
                btn.disabled = false; formMsg((r && r.message) || 'Kısıtlama kaldırılamadı.');
            }).fail(function(x) { btn.disabled = false; formMsg((x.responseJSON && x.responseJSON.message) || 'Kısıtlama kaldırılamadı.'); });
        });
    }

    window.showAddBalanceModal = function() { balanceForm('add'); };
    <?php if ($this->session->flashdata('balance_ok')): ?>
    $(function() {
        openForm('İşlem Başarılı', CUSTOMER.name + ' · #' + CUSTOMER.id,
            '<div class="cd-pop-success"><span>✓</span><strong>İşleminiz başarıyla onaylanmıştır.</strong></div>' +
            '<div class="cd-pop-actions"><button type="button" class="cd-pop-btn primary" data-cd-close>Tamam</button></div>');
    });
    <?php endif; ?>
    window.showRemoveBalanceModal = function() { balanceForm('remove'); };
    $(document).on('click', '#cdRestrictBtn', restrictForm);
    $(document).on('click', '#cdUnrestrictBtn', function() { unrestrictForm(parseInt(this.getAttribute('data-id'), 10)); });
    $(document).on('click', '[data-cd-close]', close);

    // Hesap hareketleri filtresi
    function filterMovements() {
        var q = ($('#cdMvSearch').val() || '').toLocaleLowerCase('tr'), kind = $('#cdMvKind').val(), shown = 0, rows = $('#cdMovementsTable tbody tr[data-kind]');
        rows.each(function() {
            var ok = (!kind || this.getAttribute('data-kind') === kind) && (!q || (this.getAttribute('data-search') || '').indexOf(q) !== -1);
            this.hidden = !ok; if (ok) shown++;
        });
        $('#cdMvEmpty').prop('hidden', shown !== 0 || rows.length === 0);
        $('#cdMvCount').text(rows.length ? shown + ' / ' + rows.length + ' kayıt' : '');
    }
    $(document).on('input change', '#cdMvSearch, #cdMvKind', filterMovements);
    $(filterMovements);
    $(document).on('click', '[data-cd-movement]', function() { openPayment(parseInt(this.getAttribute('data-cd-movement'), 10), true); });

    $(document).on('click', '[data-cd-payment]', function() { openPayment(parseInt(this.getAttribute('data-cd-payment'), 10)); });
    $(document).on('click', '[data-cd-legacy-payment]', function() { openLegacyPayment(this.getAttribute('data-cd-legacy-payment')); });
    $(document).on('click', '[data-cd-order]', function() { openOrder(parseInt(this.getAttribute('data-cd-order'), 10)); });
    $(document).on('click', '[data-cd-legacy-order]', function() { openLegacy(this.getAttribute('data-cd-legacy-order')); });
    document.getElementById('cdPopClose').addEventListener('click', close);
    document.getElementById('cdPopDismiss').addEventListener('click', close);
    pop.addEventListener('mousedown', function(e) { if (e.target === pop) close(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && !pop.hidden) close(); });
})();
</script>

<!-- Update User Details Modal -->
<div class="modal fade cd-edit-modal" id="kt_modal_update_details" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">

            <div class="modal-header">
                <h2 class="fw-bold">Müşteri Bilgilerini Güncelle</h2>
                <button type="button" class="cd-modal-x" data-bs-dismiss="modal" aria-label="Kapat">✕</button>
            </div>

            <form id="updateUserForm" class="form">
                <div class="modal-body">
                    <div class="cd-edit-sub">#<?= (int)$user->user_id ?> · <?= html_escape($fullName) ?></div>
                    <div class="row g-4">

                        <div class="col-12 cd-edit-sec"><?= $cdIcon('user') ?> Kişisel Bilgiler</div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ad</label>
                            <input type="text" class="form-control form-control-solid" name="user_name" value="<?= html_escape($user->user_name) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Soyad</label>
                            <input type="text" class="form-control form-control-solid" name="user_surname" value="<?= html_escape($user->user_surname ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Doğum Tarihi</label>
                            <input type="date" class="form-control form-control-solid" name="user_birthday" max="<?= date('Y-m-d') ?>" value="<?= (!empty($user->user_birthday) && $user->user_birthday !== '0000-00-00') ? html_escape(date('Y-m-d', strtotime($user->user_birthday))) : '' ?>" style="color-scheme: dark;">
                        </div>

                        <div class="col-12 cd-edit-sec"><?= $cdIcon('mail') ?> Hesap Detayları</div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">E-posta</label>
                            <input type="email" class="form-control form-control-solid" name="user_email" value="<?= html_escape($user->user_email) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefon</label>
                            <input type="text" class="form-control form-control-solid" name="user_phone" value="<?= html_escape($user->user_phone) ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Hesap Türü</label>
                            <select class="form-select form-select-solid" name="user_type" data-control="select2" data-hide-search="true" data-dropdown-parent="#kt_modal_update_details">
                                <option value="2" <?= ((int)$user->user_type === 2) ? 'selected' : '' ?>>Supplier</option>
                                <option value="3" <?= ((int)$user->user_type === 3) ? 'selected' : '' ?>>Reseller</option>
                                <option value="4" <?= ((int)$user->user_type === 4) ? 'selected' : '' ?>>User</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Müşteri Tipi</label>
                            <select class="form-select form-select-solid" name="user_tier" data-control="select2" data-hide-search="true" data-dropdown-parent="#kt_modal_update_details">
                                <option value="1" <?= ((int)$user->user_tier === 1) ? 'selected' : '' ?>>Son Kullanıcı</option>
                                <option value="2" <?= ((int)$user->user_tier === 2) ? 'selected' : '' ?>>Dijipin</option>
                                <option value="5" <?= ((int)$user->user_tier === 5) ? 'selected' : '' ?>>Playsel</option>
                                <option value="6" <?= ((int)$user->user_tier === 6) ? 'selected' : '' ?>>Ödeme Kuruluşu</option>
                                <option value="3" <?= ((int)$user->user_tier === 3) ? 'selected' : '' ?>>API</option>
                                <option value="4" <?= ((int)$user->user_tier === 4) ? 'selected' : '' ?>>API Özel</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Kredi Limiti</label>
                            <input type="text" class="form-control form-control-solid" name="user_credit_limit"
                                value="<?= number_format((float)$user->user_credit_limit, 2, '.', '') ?>"
                                inputmode="decimal" placeholder="0.00">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Dil</label>
                            <select class="form-select form-select-solid" name="user_lang" data-control="select2" data-hide-search="false" data-dropdown-parent="#kt_modal_update_details" data-placeholder="Dil Seçiniz">
                                <option value="">Dil Seçiniz</option>
                                <?php
                                $currentLanguage = !empty($user->user_lang) ? strtolower(trim($user->user_lang)) : 'tr';
                                foreach ($locales as $locale):
                                    $isSelected = ($currentLanguage === strtolower($locale->locale_lang_code)) ? 'selected' : '';
                                ?>
                                    <option value="<?= html_escape($locale->locale_lang_code) ?>" <?= $isSelected ?>>
                                        <?= html_escape($locale->locale_lang) ?> (<?= strtoupper($locale->locale_lang_code) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 cd-edit-sec"><?= $cdIcon('bank') ?> Havale ve Bölge</div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Havale Referans Kodu</label>
                            <input type="text" class="form-control form-control-solid" value="<?= html_escape($user->user_bank_referance_number ?: '—') ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kayıt Olduğu Ülke</label>
                            <select class="form-select form-select-solid" name="user_country" data-control="select2" data-hide-search="false" data-dropdown-parent="#kt_modal_update_details" data-placeholder="Ülke Seçiniz">
                                <option value="">Ülke Seçiniz</option>
                                <?php
                                $currentCountry = !empty($user->user_country) ? strtoupper(trim($user->user_country)) : '';
                                foreach ($locales as $locale):
                                    // Kullanıcının mevcut verisi 'TR' koduyla veya 'Türkiye' ismiyle kayıtlı olabilir, ikisini de kontrol ediyoruz
                                    $isSelected = ($currentCountry === strtoupper($locale->locale_code) || $currentCountry === strtoupper($locale->locale_country)) ? 'selected' : '';
                                ?>
                                    <option value="<?= html_escape($locale->locale_code) ?>" <?= $isSelected ?>>
                                        <?= html_escape($locale->locale_country) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 cd-edit-sec"><?= $cdIcon('shield') ?> Güvenlik ve Doğrulama</div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">E-posta Onayı</label>
                            <select class="form-select form-select-solid" name="user_email_is_verified" data-control="select2" data-hide-search="true" data-dropdown-parent="#kt_modal_update_details">
                                <option value="0" <?= empty($user->user_email_is_verified) ? 'selected' : '' ?>>Onaysız</option>
                                <option value="1" <?= !empty($user->user_email_is_verified) ? 'selected' : '' ?>>Onaylı</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Telefon Onayı</label>
                            <select class="form-select form-select-solid" name="user_phone_is_verified" data-control="select2" data-hide-search="true" data-dropdown-parent="#kt_modal_update_details">
                                <option value="0" <?= empty($user->user_phone_is_verified) ? 'selected' : '' ?>>Onaysız</option>
                                <option value="1" <?= !empty($user->user_phone_is_verified) ? 'selected' : '' ?>>Onaylı</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Hesap Durumu</label>
                            <select class="form-select form-select-solid" name="user_status" data-control="select2" data-hide-search="true" data-dropdown-parent="#kt_modal_update_details">
                                <option value="1" <?= (int)$user->user_status === 1 ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= (int)$user->user_status !== 1 ? 'selected' : '' ?>>Pasif</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Giriş OTP</label>
                            <select class="form-select form-select-solid" name="user_2fa_method" data-control="select2" data-hide-search="true" data-dropdown-parent="#kt_modal_update_details">
                                <option value="0" <?= (int)($user->user_2fa_method ?? 0) === 0 ? 'selected' : '' ?>>Pasif</option>
                                <option value="1" <?= (int)($user->user_2fa_method ?? 0) === 1 ? 'selected' : '' ?>>Aktif · E-posta ile</option>
                                <option value="2" <?= (int)($user->user_2fa_method ?? 0) === 2 ? 'selected' : '' ?>>Aktif · SMS ile</option>
                            </select>
                        </div>

                        <div class="col-12 cd-edit-sec"><?= $cdIcon('users') ?> Referans</div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Referans User ID</label>
                            <input type="text" class="form-control form-control-solid" name="user_reference_id" value="<?= html_escape($user->user_reference_id) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Referans Olma İzni</label>
                            <select class="form-select form-select-solid" name="user_ref_permission" data-control="select2" data-hide-search="true" data-dropdown-parent="#kt_modal_update_details">
                                <option value="0" <?= empty($user->user_ref_permission) ? 'selected' : '' ?>>İzin Yok</option>
                                <option value="1" <?= !empty($user->user_ref_permission) ? 'selected' : '' ?>>İzin Verildi</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary" id="updateUserSubmitBtn">
                        <span class="indicator-label"><?= $cdIcon('check') ?> Değişiklikleri Kaydet</span>
                        <span class="indicator-progress" style="display:none;">
                            Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- =========================
     USER NOTES MODAL
========================= -->
<div class="modal fade cd-edit-modal" id="kt_modal_user_notes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-700px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Personel Notu</h2>
                <button type="button" class="cd-modal-x" data-bs-dismiss="modal" aria-label="Kapat">✕</button>
            </div>

            <div class="modal-body">
                <div class="cd-edit-sub">#<?= (int)$user->user_id ?> · <?= html_escape($fullName) ?> · Yalnızca yöneticiler görebilir</div>
                <label class="form-label fw-semibold" for="userNoteText">Not</label>
                <textarea id="userNoteText" class="form-control form-control-solid" rows="10" name="user_notes" maxlength="5000" placeholder="Müşteriyle ilgili iç notu yazın..."><?= html_escape($user->user_notes) ?></textarea>
                <div id="userNotesList" class="d-flex flex-column gap-4"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" id="btnSaveUserNote"><?= $cdIcon('check') ?> Notu Kaydet</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="kt_modal_user_address" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">

            <div class="modal-header">
                <h2 class="fw-bold" id="userAddressModalTitle">Fatura Adresi Ekle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>

            <form id="userAddressForm" class="form">
                <input type="hidden" name="user_addresses_id" id="addr_id" value="0">
                <div class="modal-body py-8 px-lg-12">
                    <div class="row g-5">

                        <!-- Adres Başlığı -->
                        <div class="col-md-7">
                            <label class="form-label required fw-semibold">Adres Başlığı</label>
                            <input type="text" class="form-control form-control-solid" name="user_addresses_title" id="addr_title" placeholder="Örn: Ev, Ofis, Merkez Fatura" required>
                        </div>

                        <!-- Fatura Türü -->
                        <div class="col-md-5">
                            <label class="form-label required fw-semibold">Fatura Türü</label>
                            <div class="d-flex gap-4 mt-3">
                                <label class="form-check form-check-custom form-check-solid cursor-pointer">
                                    <input class="form-check-input" type="radio" name="user_addresses_invoice_type" id="addr_type_individual" value="1" checked>
                                    <span class="form-check-label fw-semibold text-gray-800">Bireysel</span>
                                </label>
                                <label class="form-check form-check-custom form-check-solid cursor-pointer">
                                    <input class="form-check-input" type="radio" name="user_addresses_invoice_type" id="addr_type_corporate" value="2">
                                    <span class="form-check-label fw-semibold text-gray-800">Kurumsal</span>
                                </label>
                            </div>
                        </div>

                        <!-- BİREYSEL ALANLAR -->
                        <div id="wrapper_individual" class="col-12 p-0">
                            <div class="row g-5 px-3">
                                <div class="col-12">
                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="user_addresses_is_turkish_citizen" id="addr_is_tc_citizen" value="1" checked>
                                        <span class="form-check-label fw-semibold text-gray-700">T.C. Vatandaşı</span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required fw-semibold">Ad Soyad</label>
                                    <input type="text" class="form-control form-control-solid" name="user_addresses_full_name" id="addr_full_name" placeholder="Ad Soyad">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" id="lbl_tckn">TC Kimlik No</label>
                                    <input type="text" class="form-control form-control-solid" name="user_addresses_tax_number" id="addr_tc_no" placeholder="TCKN (11 hane)" maxlength="11">
                                </div>
                            </div>
                        </div>

                        <!-- KURUMSAL ALANLAR -->
                        <div id="wrapper_corporate" class="col-12 p-0 d-none">
                            <div class="row g-5 px-3">
                                <div class="col-12">
                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="user_addresses_is_turkish_company" id="addr_is_turkish_company" value="1" checked>
                                        <span class="form-check-label fw-semibold text-gray-700">Türk Şirketi (Türkiye İçi Fatura)</span>
                                    </label>
                                </div>
                                <div class="col-12">
                                    <label class="form-label required fw-semibold">Firma Ünvanı</label>
                                    <input type="text" class="form-control form-control-solid" name="user_addresses_company_name" id="addr_company_name" placeholder="Firma Resmi Ünvanı">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Vergi Dairesi</label>
                                    <input type="text" class="form-control form-control-solid" name="user_addresses_tax_office" id="addr_tax_office" placeholder="Vergi Dairesi Adı">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required fw-semibold" id="lbl_vkn">Vergi Numarası (VKN)</label>
                                    <input type="text" class="form-control form-control-solid" name="user_addresses_corporate_tax_number" id="addr_vkn" placeholder="Vergi No (10 veya 11 hane)">
                                </div>
                                <div class="col-12" id="wrapper_e_invoice">
                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="user_addresses_is_e_invoice_taxpayer" id="addr_is_e_invoice" value="1">
                                        <span class="form-check-label fw-semibold text-gray-700">E-Fatura Mükellefidir</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12"><div class="separator separator-dashed my-1"></div></div>

                        <!-- Ülke Seçimi -->
                        <div class="col-md-4">
                            <label class="form-label required fw-semibold">Ülke</label>
                            <select class="form-select form-select-solid" name="user_addresses_country" id="addr_country">
                                <option value="Türkiye" selected>Türkiye</option>
                                <option value="Yurtdışı">Yurtdışı</option>
                            </select>
                        </div>

                        <!-- İl / Şehir -->
                        <div class="col-md-4">
                            <label class="form-label required fw-semibold" id="addr_lbl_city">İl</label>
                            <div id="addr_tr_city_wrapper">
                                <select name="user_addresses_city_select" id="addrCitySelect" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#kt_modal_user_address" data-placeholder="İl Seçiniz">
                                    <option></option>
                                    <?php if (!empty($cities)): ?>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?= html_escape($city) ?>"><?= html_escape($city) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div id="addr_foreign_city_wrapper" class="d-none">
                                <input type="text" name="user_addresses_foreign_city" id="addrCityInput" class="form-control form-control-solid" placeholder="Şehir / Eyalet">
                            </div>
                        </div>

                        <!-- İlçe -->
                        <div class="col-md-4">
                            <label class="form-label required fw-semibold" id="addr_lbl_district">İlçe</label>
                            <div id="addr_tr_district_wrapper">
                                <select name="user_addresses_district_select" id="addrDistrictSelect" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#kt_modal_user_address" data-placeholder="İlçe Seçiniz">
                                    <option></option>
                                </select>
                            </div>
                            <div id="addr_foreign_district_wrapper" class="d-none">
                                <input type="text" name="user_addresses_foreign_district" id="addrDistrictInput" class="form-control form-control-solid" placeholder="İlçe / Bölge">
                            </div>
                        </div>

                        <!-- Açık Adres -->
                        <div class="col-md-9">
                            <label class="form-label required fw-semibold">Açık Adres</label>
                            <textarea class="form-control form-control-solid" name="user_addresses_address" id="addr_address" rows="3" placeholder="Mahalle, Cadde, Sokak, No, Daire vb." required></textarea>
                        </div>

                        <!-- Posta Kodu -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Posta Kodu</label>
                            <input type="text" class="form-control form-control-solid" name="user_addresses_postal_code" id="addr_postal_code" placeholder="34000">
                        </div>

                        <!-- Telefon (Opsiyonel) -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefon</label>
                            <input type="text" class="form-control form-control-solid" name="user_addresses_phone" id="addr_phone" placeholder="05XXXXXXXXX" value="<?= html_escape($user->user_phone ?? '') ?>">
                        </div>

                        <!-- Varsayılan Yap -->
                        <div class="col-md-6 d-flex align-items-center mt-9">
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="user_addresses_is_default" id="addr_is_default" value="1">
                                <span class="form-check-label fw-semibold text-gray-800">Varsayılan Fatura Adresi Yap</span>
                            </label>
                        </div>

                    </div>
                </div>

                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveUserAddress">
                        <span class="indicator-label">Kaydet</span>
                        <span class="indicator-progress" style="display:none;">
                            Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    // ===== Helpers =====
    function copy(val) {
        var tempInput = document.createElement("input");
        tempInput.style.position = "absolute";
        tempInput.style.left = "-9999px";
        tempInput.value = val;
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.setSelectionRange(0, 99999);
        try {
            document.execCommand("copy");
        } catch (err) {}
        document.body.removeChild(tempInput);
        if (typeof toastr !== 'undefined') toastr.success('Kopyalandı: ' + val);
    }

    function productImageUrl(image) {
        image = String(image || '').trim();
        if (!image) return '';
        if (/^https?:\/\//i.test(image)) return image;
        return '<?= rtrim((string)getenv('CDN_URL'), '/') ?>/' + image.replace(/^\/+/, '');
    }

    function changeStatus(orderID) {
        $('#order-id').val(orderID);
        $('#orderStatus').val('');
        $('#orderStatusChangeModal').modal('show');
    }

    // ===== Order Detail (tek) =====
    function orderDetail(orderID) {
        $.ajax({
            url: '<?= site_url('orders/orderDetail/') ?>' + orderID,
            type: 'GET',
            success: function(response) {
                var data = response;

                var statusText = "";
                switch (String(data.order_status)) {
                    case "0":
                        statusText = "Yeni Sipariş";
                        break;
                    case "1":
                        statusText = "Tamamlandı";
                        break;
                    case "2":
                        statusText = "İptal Edildi";
                        break;
                    case "3":
                        statusText = "Supplier Okudu";
                        break;
                    case "4":
                        statusText = "Supplier Teslim Etti";
                        break;
                    case "5":
                        statusText = "Supplier İptal Etti";
                        break;
                    default:
                        statusText = "Bilinmiyor";
                        break;
                }

                var modalContent = `
                    <div class="row g-6">
                        <div class="col-md-8">
                            <h5 class="fw-bold mb-4">${data.product_name || '-'}</h5>

                            <div class="d-flex flex-column gap-2">
                                <div><span class="fw-semibold text-gray-600">Kategori:</span> ${data.category_title || '-'}</div>
                                <div><span class="fw-semibold text-gray-600">Sipariş ID:</span> ${data.order_id || '-'}</div>
                                <div><span class="fw-semibold text-gray-600">Ürün ID:</span> ${data.order_product_id || '-'}</div>
                                <div><span class="fw-semibold text-gray-600">Reseller:</span> ${data.reseller_name || '-'}</div>
                                <div><span class="fw-semibold text-gray-600">Supplier:</span> ${data.supplier_name || '-'}</div>
                                <div><span class="fw-semibold text-gray-600">Durum:</span> ${statusText}</div>
                                <div><span class="fw-semibold text-gray-600">Miktar:</span> ${data.order_quantity || '-'}</div>
                                <div><span class="fw-semibold text-gray-600">Maliyet:</span> ${data.order_cost || '-'} ${data.order_cost_currency || ''}</div>
                                <div><span class="fw-semibold text-gray-600">Maliyet TL:</span> ${data.order_cost_tl || '-'}</div>
                                <div><span class="fw-semibold text-gray-600">Oluşturulma:</span> ${data.order_created_at || '-'}</div>
                            </div>
                        </div>

                        <div class="col-md-4 text-center">
                            ${data.product_image ? `<img src="${productImageUrl(data.product_image)}" class="rounded-circle object-fit-cover" style="width:180px;height:180px;" alt="Ürün Görseli">` : ''}
                        </div>

                        <div class="col-12">
                            <div class="separator my-6"></div>
                            <h6 class="fw-bold mb-3">Ekstra Bilgiler</h6>
                            <pre class="bg-light p-4 rounded">${JSON.stringify(data.order_require_data || {}, null, 2)}</pre>
                            <div class="mt-3"><span class="fw-semibold text-gray-600">Sipariş Data:</span> ${data.order_data || '-'}</div>
                            <div class="mt-2"><span class="fw-semibold text-gray-600">Epin:</span> ${data.order_voucher || '-'}</div>
                        </div>
                        <div class="col-12">
                            <div class="separator my-6"></div>
                            <h6 class="fw-bold mb-3">Ödeme Bilgileri</h6>
                            <pre class="bg-light p-4 rounded">${JSON.stringify(data.order_payment_info || {}, null, 2)}</pre>
                        </div>
                    </div>
                `;

                $('#orderDetailContent').html(modalContent);
                $('#orderDetailModal').modal('show');
            },
            error: function() {
                if (typeof toastr !== 'undefined') toastr.error("Sipariş detayları alınamadı.");
            }
        });
    }

    $(document).ready(function() {

        // Select2 init (Metronic)
        if ($.fn.select2) {
            // modal içindeki select2
            $('#kt_modal_update_details [data-control="select2"]').each(function() {
                var parent = $(this).data('dropdown-parent') || '#kt_modal_update_details';
                $(this).select2({
                    dropdownParent: $(parent),
                    minimumResultsForSearch: $(this).data('hide-search') ? Infinity : 0,
                    width: '100%'
                });
            });
        }

        // DataTable
        var table3 = $('#orderTable2').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            pageLength: 20,
            searching: false,
            lengthMenu: [
                [10, 20, 30, 40, 50, 100, 250, 500],
                [10, 20, 30, 40, 50, 100, 250, 500]
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
            },
            ajax: {
                url: "<?= site_url('users/getOrders/' . $userID) ?>",
                type: "POST",
                data: function(d) {
                    d.order_id = $('#searchOrderID').val();
                    d.category = $('#searchCategory').val();
                    d.status_group = $('#searchStatusGroup').val();
                    d.startDate = $('#startDate').val();
                    d.endDate = $('#endDate').val();
                },
                dataSrc: function(json) {
                    if (json && json.stats) {
                        $('#ordStatTotal').text(json.stats.total);
                        $('#ordStatOk').text(json.stats.ok);
                        $('#ordStatFailed').text(json.stats.failed);
                        $('#ordStatAmount').text(json.stats.amount + ' TL');
                    }
                    return (json && json.data) ? json.data : [];
                }
            },
            columns: Array.from({
                length: 8
            }, (_, i) => ({
                data: i
            }))
        });

        function ordFilterCount() {
            var n = 0;
            ['#startDate', '#endDate', '#searchStatusGroup', '#searchCategory'].forEach(function(s) { if ($(s).val()) n++; });
            $('#ordFilterCount').text(n || '');
        }
        function ordReload() { ordFilterCount(); table3.ajax.reload(); }

        $('#filterBtn').click(ordReload);
        $('#searchStatusGroup, #searchCategory, #startDate, #endDate').on('change', ordReload);

        var ordTimer;
        $('#searchOrderID').on('input', function() { clearTimeout(ordTimer); ordTimer = setTimeout(ordReload, 350); });

        $('#ordResetBtn').click(() => {
            $('#searchOrderID, #startDate, #endDate').val('');
            $('#searchStatusGroup, #searchCategory').val('');
            ordReload();
        });

        $(document).on('click', '.cd-copy-btn', function() {
            var v = this.getAttribute('data-copy') || '';
            if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(v);
            if (typeof toastr !== 'undefined') toastr.success('Kopyalandı');
        });

        // Status change
        $('#changeStatusBtn').click(() => {
            var orderStatus = $('#orderStatus').val();
            var orderID = $('#order-id').val();

            if (!orderStatus) {
                if (typeof toastr !== 'undefined') toastr.error('Lütfen geçerli bir durum seçin!');
                return;
            }

            $.ajax({
                url: '<?= site_url('orders/changeStatus/') ?>' + orderID,
                type: 'POST',
                data: {
                    orderStatus: orderStatus
                },
                success: function(response) {
                    if (response && response.status === 'success') {
                        if (typeof toastr !== 'undefined') toastr.success(response.message || 'Güncellendi');
                        $('#orderStatusChangeModal').modal('hide');
                        table3.ajax.reload(null, false);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error((response && response.message) ? response.message : 'İşlem başarısız');
                    }
                },
                error: function() {
                    if (typeof toastr !== 'undefined') toastr.error('Sipariş durumu değiştirilemedi.');
                }
            });
        });

        // Update user modal submit
        $('#updateUserForm').on('submit', function(e) {
            e.preventDefault();

            var $btn = $('#updateUserSubmitBtn');
            $btn.prop('disabled', true);
            $btn.find('.indicator-label').hide();
            $btn.find('.indicator-progress').show();

            $.ajax({
                url: "<?= site_url('users/updateDetails/' . $userID) ?>",
                type: "POST",
                dataType: "json",
                data: $(this).serialize(),
                success: function(res) {
                    if (res && res.status === 'success') {
                        if (typeof toastr !== 'undefined') toastr.success(res.message || 'Kaydedildi');
                        // sayfayı yenileyerek rozet/bilgileri güncel göster
                        setTimeout(function() {
                            window.location.reload();
                        }, 600);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error((res && res.message) ? res.message : 'Kaydedilemedi');
                        $btn.prop('disabled', false);
                        $btn.find('.indicator-label').show();
                        $btn.find('.indicator-progress').hide();
                    }
                },
                error: function(xhr) {
                    if (typeof toastr !== 'undefined') toastr.error('İşlem sırasında hata oluştu.');
                    $btn.prop('disabled', false);
                    $btn.find('.indicator-label').show();
                    $btn.find('.indicator-progress').hide();
                }
            });
        });

        $('#resetPasswordBtn').on('click', function() {
            var sendEmail = $('#resetPasswordEmail').is(':checked');
            var sendSms = $('#resetPasswordSms').is(':checked');
            if (!sendEmail && !sendSms) {
                toastr.error('En az bir bildirim yöntemi seçin.');
                return;
            }
            if (!confirm('Yeni şifre oluşturulup seçilen kanallara gönderilsin mi?')) return;
            var $btn = $(this).prop('disabled', true);
            $.ajax({
                url: "<?= site_url('users/resetPassword/' . (int)$userID) ?>",
                type: 'POST',
                dataType: 'json',
                data: {send_email: sendEmail ? 1 : 0, send_sms: sendSms ? 1 : 0},
                success: function(res) {
                    if (res && res.status === 'success') {
                        toastr.success(res.message);
                        $('#cdResetPasswordModal').modal('hide');
                        $('#resetPasswordEmail, #resetPasswordSms').prop('checked', false);
                    } else {
                        toastr.error((res && res.message) || 'Şifre sıfırlanamadı.');
                    }
                },
                error: function(xhr) {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Şifre sıfırlanamadı.');
                },
                complete: function() { $btn.prop('disabled', false); }
            });
        });

        var logTable = $('#userLogTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            searching: false,
            pageLength: 20,
            lengthMenu: [
                [10, 20, 50, 100, 250],
                [10, 20, 50, 100, 250]
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
            },
            ajax: {
                url: "<?= site_url('users/getUserLogs/' . (int)$userID) ?>",
                type: "POST",
                data: function(d) {
                    d.event = $('#logEvent').val();
                    d.ip = $('#logIP').val();
                    d.page = $('#logPage').val();
                }
            },
            columns: Array.from({
                length: 7
            }, (_, i) => ({
                data: i
            }))
        });

        $('#logFilterBtn').on('click', function() {
            logTable.ajax.reload();
        });

        $('#logResetBtn').on('click', function() {
            $('#logEvent').val('').trigger('change');
            $('#logIP').val('');
            $('#logPage').val('');
            logTable.ajax.reload();
        });

        $('#logIP, #logPage').on('keypress', function(e) {
            if (e.which === 13) logTable.ajax.reload();
        });

        $('a[data-bs-toggle="tab"][href="#kt_user_transactions_logs"]').on('shown.bs.tab', function() {
            logTable.columns.adjust();
        });

        // Legacy ödemeler / bakiye hareketleri: sekme ilk açıldığında yüklenir (server-side)
        var legacyDtLanguage = {
            url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
        };
        var legacyDtLengthMenu = [
            [10, 20, 50, 100, 250],
            [10, 20, 50, 100, 250]
        ];
        var legacyPaymentsTable = null;
        var legacyMovementsTable = null;

        function initLegacyTables() {
            if (!legacyPaymentsTable) {
                legacyPaymentsTable = $('#legacyPaymentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    ordering: true,
                    order: [
                        [3, 'desc']
                    ],
                    pageLength: 20,
                    lengthMenu: legacyDtLengthMenu,
                    language: legacyDtLanguage,
                    ajax: {
                        url: "<?= site_url('users/legacyPaymentsList/' . (int)$userID) ?>",
                        type: "POST",
                        data: function(d) {
                            d.q = $('#lpQ').val();
                            d.method = $('#lpMethod').val();
                            d.status = $('#lpStatus').val();
                            d.purpose = $('#lpPurpose').val();
                            d.currency = $('#lpCurrency').val();
                            d.card = $('#lpCard').val();
                            d.date_from = $('#lpDateFrom').val();
                            d.date_to = $('#lpDateTo').val();
                            d.amount_min = $('#lpAmountMin').val();
                            d.amount_max = $('#lpAmountMax').val();
                        }
                    },
                    columnDefs: [{
                        targets: [2, 6, 7],
                        visible: false
                    }, {
                        targets: [12],
                        orderable: false
                    }],
                    columns: Array.from({
                        length: 13
                    }, (_, i) => ({
                        data: i
                    }))
                });
            }
            if (!legacyMovementsTable) {
                legacyMovementsTable = $('#legacyMovementsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    ordering: true,
                    order: [
                        [0, 'desc']
                    ],
                    pageLength: 20,
                    lengthMenu: legacyDtLengthMenu,
                    language: legacyDtLanguage,
                    ajax: {
                        url: "<?= site_url('users/legacyBalanceMovementsList/' . (int)$userID) ?>",
                        type: "POST",
                        data: function(d) {
                            d.q = $('#lmQ').val();
                            d.type = $('#lmType').val();
                            d.balance_type = $('#lmBalanceType').val();
                            d.direction = $('#lmDirection').val();
                            d.currency = $('#lmCurrency').val();
                            d.linked = $('#lmLinked').val();
                            d.date_from = $('#lmDateFrom').val();
                            d.date_to = $('#lmDateTo').val();
                            d.amount_min = $('#lmAmountMin').val();
                            d.amount_max = $('#lmAmountMax').val();
                        }
                    },
                    columnDefs: [{
                        targets: [6, 7, 9],
                        visible: false
                    }],
                    columns: Array.from({
                        length: 11
                    }, (_, i) => ({
                        data: i
                    }))
                });
            }
        }

        $('a[data-bs-toggle="tab"][href="#kt_user_transactions"]').on('shown.bs.tab', function() {
            initLegacyTables();
            legacyPaymentsTable.columns.adjust();
            legacyMovementsTable.columns.adjust();
        });

        $('a[href="#cd_pay_legacy"], a[href="#cd_pay_moves"]').on('shown.bs.tab', function() {
            initLegacyTables();
            legacyPaymentsTable.columns.adjust();
            legacyMovementsTable.columns.adjust();
        });

        $(document).on('click', '.cd-filter-toggle', function() {
            var box = document.querySelector($(this).data('target'));
            var open = box.hidden;
            box.hidden = !open;
            this.setAttribute('aria-expanded', String(open));
        });

        $(document).on('click', '.cd-cols-toggle', function() {
            var on = this.getAttribute('aria-pressed') !== 'true';
            var isPayments = $(this).data('table') === 'lp';
            var table = isPayments ? legacyPaymentsTable : legacyMovementsTable;
            if (!table) return;
            table.columns(isPayments ? [2, 6, 7] : [6, 7, 9]).visible(on);
            this.setAttribute('aria-pressed', String(on));
            this.textContent = on ? 'Sütunları Daralt' : 'Tüm Sütunlar';
        });

        function bindLegacyFilters(prefix, getTable) {
            $('#' + prefix + 'FilterBtn').on('click', function() {
                getTable().ajax.reload();
            });
            $('#' + prefix + 'ResetBtn').on('click', function() {
                $('[id^="' + prefix + '"]').filter('input, select').val('');
                getTable().page('first').ajax.reload();
            });
            $('[id^="' + prefix + '"]').filter('input').on('keypress', function(e) {
                if (e.which === 13) getTable().ajax.reload();
            });
            $('[id^="' + prefix + '"]').filter('select').on('change', function() {
                getTable().ajax.reload();
            });
        }
        bindLegacyFilters('lp', function() {
            return legacyPaymentsTable;
        });
        bindLegacyFilters('lm', function() {
            return legacyMovementsTable;
        });

        $(document).on('click', '#btnSaveUserNote', function() {
            var note = $('#userNoteText').val() || '';
            note = note.trim();

            if (!note) {
                if (typeof toastr !== 'undefined') toastr.error('Not boş olamaz.');
                return;
            }

            var userName = ($('#edit_user_name').val() || "<?= html_escape($user->user_name) ?>").trim();
            var userEmail = ($('#edit_user_email').val() || "<?= html_escape($user->user_email) ?>").trim();

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: "<?= site_url('users/updateDetails/' . $userID) ?>",
                type: "POST",
                dataType: "json",
                data: {
                    user_name: userName,
                    user_email: userEmail,
                    user_notes: note
                },
                success: function(res) {
                    if (res && res.status === 'success') {
                        if (typeof toastr !== 'undefined') toastr.success(res.message || 'Not kaydedildi');
                        var modalEl = document.getElementById('kt_modal_user_notes');
                        if (modalEl && window.bootstrap) {
                            var m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            m.hide();
                        } else {
                            $('#kt_modal_user_notes').modal('hide');
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 400);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error((res && res.message) ? res.message : 'Kaydedilemedi');
                    }
                },
                error: function(xhr) {
                    console.log('Save user note error', xhr.status, xhr.responseText);
                    if (typeof toastr !== 'undefined') toastr.error('İşlem sırasında hata oluştu.');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // ==========================================
        // KULLANICI FATURA ADRESLERİ YÖNETİMİ
        // ==========================================
        const $addrCountry = $('#addr_country');
        const $addrCitySel = $('#addrCitySelect');
        const $addrDistSel = $('#addrDistrictSelect');
        const $addrCityInp = $('#addrCityInput');
        const $addrDistInp = $('#addrDistrictInput');

        const $addrTrCityWrap = $('#addr_tr_city_wrapper');
        const $addrTrDistWrap = $('#addr_tr_district_wrapper');
        const $addrForCityWrap = $('#addr_foreign_city_wrapper');
        const $addrForDistWrap = $('#addr_foreign_district_wrapper');

        const $addrLblCity = $('#addr_lbl_city');
        const $addrLblDistrict = $('#addr_lbl_district');

        // Bireysel / Kurumsal geçişi
        $('input[name="user_addresses_invoice_type"]').on('change', function() {
            if ($(this).val() === '2') {
                $('#wrapper_individual').addClass('d-none');
                $('#wrapper_corporate').removeClass('d-none');
            } else {
                $('#wrapper_individual').removeClass('d-none');
                $('#wrapper_corporate').addClass('d-none');
            }
        });

        // Ülke geçişi (Türkiye / Yurtdışı)
        function toggleAddrCountry() {
            const isTr = ($addrCountry.val() === 'Türkiye');
            if (isTr) {
                $addrTrCityWrap.removeClass('d-none');
                $addrTrDistWrap.removeClass('d-none');
                $addrForCityWrap.addClass('d-none');
                $addrForDistWrap.addClass('d-none');

                $addrCitySel.prop('disabled', false);
                $addrDistSel.prop('disabled', false);
                $addrCityInp.prop('disabled', true);
                $addrDistInp.prop('disabled', true);

                $addrLblCity.text('İl');
                $addrLblDistrict.text('İlçe');
            } else {
                $addrTrCityWrap.addClass('d-none');
                $addrTrDistWrap.addClass('d-none');
                $addrForCityWrap.removeClass('d-none');
                $addrForDistWrap.removeClass('d-none');

                $addrCityInp.prop('disabled', false);
                $addrDistInp.prop('disabled', false);
                $addrCitySel.prop('disabled', true);
                $addrDistSel.prop('disabled', true);

                $addrLblCity.text('Şehir / Eyalet');
                $addrLblDistrict.text('İlçe / Bölge');
            }
        }
        $addrCountry.on('change', toggleAddrCountry);

        // Select2 başlatma
        $('#kt_modal_user_address [data-control="select2"]').each(function() {
            var parent = $(this).data('dropdown-parent') || '#kt_modal_user_address';
            $(this).select2({
                dropdownParent: $(parent),
                minimumResultsForSearch: $(this).data('hide-search') ? Infinity : 0,
                width: '100%'
            });
        });

        // İlçe yükleme fonksiyonu
        function loadAddrDistricts(cityName, selectedDistrict = '') {
            $addrDistSel.empty().append('<option value="">Yükleniyor...</option>').trigger('change');

            $.ajax({
                url: "<?= base_url('users/getDistricts') ?>",
                type: "POST",
                dataType: "json",
                data: { city: cityName },
                success: function(response) {
                    $addrDistSel.empty().append('<option></option>');

                    if (response.status === 'success' && response.data.length > 0) {
                        response.data.forEach(function(district) {
                            const isSelected = (district === selectedDistrict) ? 'selected' : '';
                            $addrDistSel.append(`<option value="${district}" ${isSelected}>${district}</option>`);
                        });
                    } else {
                        $addrDistSel.append('<option value="">İlçe bulunamadı</option>');
                    }
                    $addrDistSel.trigger('change');
                },
                error: function() {
                    $addrDistSel.empty().append('<option value="">Hata oluştu</option>').trigger('change');
                }
            });
        }

        $addrCitySel.on('change', function() {
            const selectedCity = $(this).val();
            if (selectedCity && $addrCountry.val() === 'Türkiye') {
                loadAddrDistricts(selectedCity);
            } else {
                $addrDistSel.empty().append('<option></option>').trigger('change');
            }
        });

        // Yeni Adres Ekle Modalını Aç
        window.openNewAddressModal = function() {
            $('#userAddressForm')[0].reset();
            $('#addr_id').val('0');
            $('#userAddressModalTitle').text('Yeni Fatura Adresi Ekle');
            $('#addr_type_individual').prop('checked', true).trigger('change');
            $('#addr_is_tc_citizen').prop('checked', true);
            $('#addr_is_turkish_company').prop('checked', true);
            $('#addr_is_e_invoice').prop('checked', false);
            $('#addr_is_default').prop('checked', <?= empty($user_addresses) ? 'true' : 'false' ?>);
            $addrCountry.val('Türkiye').trigger('change');
            $addrCitySel.val('').trigger('change');
            $addrDistSel.empty().append('<option></option>').trigger('change');

            $('#kt_modal_user_address').modal('show');
        };

        // Eski Bilgileri Adres Olarak Kaydet
        window.openLegacyAddressModal = function() {
            openNewAddressModal();
            $('#userAddressModalTitle').text('Eski Fatura Bilgilerinden Adres Oluştur');
            $('#addr_title').val('Ana Fatura Adresi');
            <?php if (!empty($user->user_bill_tax_office)): ?>
                $('#addr_type_corporate').prop('checked', true).trigger('change');
                $('#addr_company_name').val("<?= html_escape($user->user_bill_company_name ?? '') ?>");
                $('#addr_tax_office').val("<?= html_escape($user->user_bill_tax_office ?? '') ?>");
                $('#addr_vkn').val("<?= html_escape($user->user_bill_tax_number ?? '') ?>");
                $('#addr_is_e_invoice').prop('checked', <?= ((int)($user->user_bill_e_invoice ?? 0) === 1) ? 'true' : 'false' ?>);
            <?php else: ?>
                $('#addr_type_individual').prop('checked', true).trigger('change');
                $('#addr_full_name').val("<?= html_escape($user->user_bill_company_name ?? ($user->user_name . ' ' . $user->user_surname)) ?>");
                $('#addr_tc_no').val("<?= html_escape($user->user_bill_tax_number ?? '') ?>");
            <?php endif; ?>
            $('#addr_address').val("<?= html_escape($user->user_bill_address ?? '') ?>");
            $('#addr_is_default').prop('checked', true);

            const legacyCity = "<?= html_escape($user->user_bill_city ?? '') ?>";
            const legacyDistrict = "<?= html_escape($user->user_bill_district ?? '') ?>";
            if (legacyCity) {
                $addrCitySel.val(legacyCity).trigger('change');
                loadAddrDistricts(legacyCity, legacyDistrict);
            }
        };

        // Adres Düzenleme
        window.editUserAddress = function(addr) {
            $('#userAddressForm')[0].reset();
            $('#addr_id').val(addr.user_addresses_id);
            $('#userAddressModalTitle').text('Fatura Adresini Düzenle (#' + addr.user_addresses_id + ')');
            $('#addr_title').val(addr.user_addresses_title || '');

            const isCorp = (parseInt(addr.user_addresses_invoice_type) === 2);
            if (isCorp) {
                $('#addr_type_corporate').prop('checked', true).trigger('change');
                $('#addr_is_turkish_company').prop('checked', parseInt(addr.user_addresses_is_turkish_company) === 1);
                $('#addr_company_name').val(addr.user_addresses_company_name || '');
                $('#addr_tax_office').val(addr.user_addresses_tax_office || '');
                $('#addr_vkn').val(addr.user_addresses_tax_number || '');
                $('#addr_is_e_invoice').prop('checked', parseInt(addr.user_addresses_is_e_invoice_taxpayer) === 1);
            } else {
                $('#addr_type_individual').prop('checked', true).trigger('change');
                $('#addr_is_tc_citizen').prop('checked', parseInt(addr.user_addresses_is_turkish_citizen) === 1);
                $('#addr_full_name').val(addr.user_addresses_full_name || '');
                $('#addr_tc_no').val(addr.user_addresses_tax_number || '');
            }

            const country = addr.user_addresses_country || 'Türkiye';
            $addrCountry.val(country).trigger('change');

            if (country === 'Türkiye') {
                $addrCitySel.val(addr.user_addresses_city || '').trigger('change');
                if (addr.user_addresses_city) {
                    loadAddrDistricts(addr.user_addresses_city, addr.user_addresses_district || '');
                }
            } else {
                $addrCityInp.val(addr.user_addresses_city || '');
                $addrDistInp.val(addr.user_addresses_district || '');
            }

            $('#addr_address').val(addr.user_addresses_address || '');
            $('#addr_postal_code').val(addr.user_addresses_postal_code || '');
            $('#addr_phone').val(addr.user_addresses_phone || '');
            $('#addr_is_default').prop('checked', parseInt(addr.user_addresses_is_default) === 1);

            $('#kt_modal_user_address').modal('show');
        };

        // Adres Kaydetme Form Submit
        $('#userAddressForm').on('submit', function(e) {
            e.preventDefault();

            var $btn = $('#btnSaveUserAddress');
            $btn.prop('disabled', true);
            $btn.find('.indicator-label').hide();
            $btn.find('.indicator-progress').show();

            const isCorp = ($('input[name="user_addresses_invoice_type"]:checked').val() === '2');
            const isTr = ($addrCountry.val() === 'Türkiye');

            const postData = {
                user_addresses_id: $('#addr_id').val(),
                user_addresses_title: $('#addr_title').val(),
                user_addresses_invoice_type: isCorp ? 2 : 1,
                user_addresses_is_turkish_citizen: $('#addr_is_tc_citizen').is(':checked') ? 1 : 0,
                user_addresses_is_turkish_company: $('#addr_is_turkish_company').is(':checked') ? 1 : 0,
                user_addresses_is_e_invoice_taxpayer: $('#addr_is_e_invoice').is(':checked') ? 1 : 0,
                user_addresses_full_name: isCorp ? '' : $('#addr_full_name').val(),
                user_addresses_company_name: isCorp ? $('#addr_company_name').val() : '',
                user_addresses_tax_office: isCorp ? $('#addr_tax_office').val() : '',
                user_addresses_tax_number: isCorp ? $('#addr_vkn').val() : $('#addr_tc_no').val(),
                user_addresses_phone: $('#addr_phone').val(),
                user_addresses_country: $addrCountry.val(),
                user_addresses_city: isTr ? $addrCitySel.val() : $addrCityInp.val(),
                user_addresses_district: isTr ? $addrDistSel.val() : $addrDistInp.val(),
                user_addresses_postal_code: $('#addr_postal_code').val(),
                user_addresses_address: $('#addr_address').val(),
                user_addresses_is_default: $('#addr_is_default').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: "<?= base_url('users/saveUserAddress/' . $userID) ?>",
                type: "POST",
                dataType: "json",
                data: postData,
                success: function(res) {
                    if (res && res.status === 'success') {
                        if (typeof toastr !== 'undefined') toastr.success(res.message || 'Adres kaydedildi');
                        $('#kt_modal_user_address').modal('hide');
                        setTimeout(function() {
                            window.location.reload();
                        }, 500);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error((res && res.message) ? res.message : 'Kaydedilemedi');
                        $btn.prop('disabled', false);
                        $btn.find('.indicator-label').show();
                        $btn.find('.indicator-progress').hide();
                    }
                },
                error: function() {
                    if (typeof toastr !== 'undefined') toastr.error('İşlem sırasında hata oluştu.');
                    $btn.prop('disabled', false);
                    $btn.find('.indicator-label').show();
                    $btn.find('.indicator-progress').hide();
                }
            });
        });

        // Varsayılan Adres Yap
        window.setDefaultAddress = function(addrId) {
            Swal.fire({
                title: 'Varsayılan Yapılsın mı?',
                text: "Bu adres kullanıcının öncelikli fatura adresi olarak ayarlanacaktır.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#50cd89',
                cancelButtonColor: '#7e8299',
                confirmButtonText: 'Evet, Ayarla',
                cancelButtonText: 'İptal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "<?= base_url('users/setDefaultUserAddress/' . $userID . '/') ?>" + addrId,
                        type: "POST",
                        dataType: "json",
                        success: function(res) {
                            if (res && res.status === 'success') {
                                if (typeof toastr !== 'undefined') toastr.success(res.message);
                                setTimeout(function() { window.location.reload(); }, 400);
                            } else {
                                Swal.fire('Hata', (res && res.message) ? res.message : 'İşlem başarısız.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Hata', 'Sunucu hatası oluştu.', 'error');
                        }
                    });
                }
            });
        };

        // Adres Sil
        window.deleteUserAddress = function(addrId) {
            Swal.fire({
                title: 'Adresi Silmek İstiyor musunuz?',
                text: "Bu fatura adresi silinecektir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f1416c',
                cancelButtonColor: '#7e8299',
                confirmButtonText: 'Evet, Sil!',
                cancelButtonText: 'İptal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "<?= base_url('users/deleteUserAddress/' . $userID . '/') ?>" + addrId,
                        type: "POST",
                        dataType: "json",
                        success: function(res) {
                            if (res && res.status === 'success') {
                                if (typeof toastr !== 'undefined') toastr.success(res.message);
                                setTimeout(function() { window.location.reload(); }, 400);
                            } else {
                                Swal.fire('Hata', (res && res.message) ? res.message : 'Silinemedi.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Hata', 'Sunucu hatası oluştu.', 'error');
                        }
                    });
                }
            });
        };
    });

    function legacySwalAddBalance(userId) {
        Swal.fire({
            title: 'Bakiye Ekle',
            html: `
                <div class="mb-5 text-start">
                    <label class="form-label fw-bold">Tutar (₺)</label>
                    <input type="number" id="swal-amount" class="form-control form-control-solid" placeholder="Örn: 100.50" min="0.01" step="0.01">
                </div>
                <div class="mb-5 text-start">
                    <label class="form-label fw-bold">Bakiye Tipi</label>
                    <select id="swal-type" class="form-select form-select-solid">
                        <option value="2">Harcanabilir Bakiye</option>
                        <option value="1">Çekilebilir Bakiye</option>
                    </select>
                </div>
                <div class="mb-0 text-start">
                    <label class="form-label fw-bold">Not (Opsiyonel)</label>
                    <input type="text" id="swal-note" class="form-control form-control-solid" placeholder="Örn: Elden alındı, Havale vs.">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Bakiye Ekle',
            cancelButtonText: 'İptal',
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            },
            preConfirm: () => {
                const amount = document.getElementById('swal-amount').value;
                const type = document.getElementById('swal-type').value;
                const note = document.getElementById('swal-note').value;
                
                if (!amount || parseFloat(amount) <= 0) {
                    Swal.showValidationMessage('Lütfen geçerli bir tutar girin');
                    return false;
                }

                return { amount: amount, type: type, note: note };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url("users/addBalance/") ?>' + userId;
                
                const inpAmount = document.createElement('input');
                inpAmount.type = 'hidden';
                inpAmount.name = 'amount';
                inpAmount.value = result.value.amount;
                form.appendChild(inpAmount);
                
                const inpType = document.createElement('input');
                inpType.type = 'hidden';
                inpType.name = 'type';
                inpType.value = result.value.type;
                form.appendChild(inpType);
                
                const inpNote = document.createElement('input');
                inpNote.type = 'hidden';
                inpNote.name = 'note';
                inpNote.value = result.value.note;
                form.appendChild(inpNote);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function legacySwalRemoveBalance(userId) {
        Swal.fire({
            title: 'Bakiye Çıkar',
            html: `
                <div class="mb-5 text-start">
                    <label class="form-label fw-bold">Tutar (₺)</label>
                    <input type="number" id="remove-amount" class="form-control form-control-solid" placeholder="Örn: 100.50" min="0.01" step="0.01">
                </div>
                <div class="mb-5 text-start">
                    <label class="form-label fw-bold">Bakiye Tipi</label>
                    <select id="remove-type" class="form-select form-select-solid">
                        <option value="2">Harcanabilir Bakiye</option>
                        <option value="1">Çekilebilir Bakiye</option>
                    </select>
                </div>
                <div class="mb-0 text-start">
                    <label class="form-label fw-bold">Not (Opsiyonel)</label>
                    <input type="text" id="remove-note" class="form-control form-control-solid" placeholder="Çıkarma nedeni">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Bakiye Çıkar',
            cancelButtonText: 'İptal',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            preConfirm: function () {
                var amount = document.getElementById('remove-amount').value;
                if (!amount || parseFloat(amount) <= 0) {
                    Swal.showValidationMessage('Lütfen geçerli bir tutar girin.');
                    return false;
                }
                return { amount: amount, type: document.getElementById('remove-type').value, note: document.getElementById('remove-note').value };
            }
        }).then(function (result) {
            if (!result.isConfirmed) return;
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= base_url("users/removeBalance/") ?>' + userId;
            Object.keys(result.value).forEach(function (key) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = result.value[key];
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        });
    }

    function deleteBankAcc(id) {
        Swal.fire({
            title: 'Silmek İstediğinize Emin Misiniz?',
            text: "Bu banka hesabı kalıcı olarak silinecektir!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, Sil!',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url("users/deleteBankAcc/") ?>' + id;
            }
        });
    }
</script>