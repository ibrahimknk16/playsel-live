<?php
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$statusOptions = [
    '' => 'Tümü',
    '0' => 'Yeni Sipariş',
    '1' => 'Teslim Edildi',
    '2' => 'İptal Edildi',
    '3' => 'İşleme Alındı',
    '4' => 'Satıcı Onayladı',
    '5' => 'Satıcı Onaylamadı',
    '9' => 'Cron İşlemi',
    '13' => 'Fraud Kontrolü',
    '14' => 'Tedarikçi Değiştir',
    '15' => 'Yükleme Bekliyor',
];
?>

<style>
    .orders-page .orders-table td {
        padding: 0.4rem 0.35rem;
        vertical-align: middle;
    }

    .orders-page .copy-value {
        cursor: pointer;
    }

    .orders-page .change-status {
        cursor: pointer;
    }

    .orders-page .orders-filters {
        border: 1px solid var(--bs-gray-300);
        border-radius: 0.75rem;
        padding: 0.85rem;
        background: var(--bs-gray-100);
    }

    .orders-page .stock-fail-item,
    .stock-alert-drawer .stock-fail-item {
        border: 1px solid rgba(139, 152, 179, 0.26);
        border-radius: 0.65rem;
        padding: 0.65rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: rgba(27, 33, 57, 0.88);
        color: #dbe4fb;
    }

    .orders-page .stock-fail-media,
    .stock-alert-drawer .stock-fail-media {
        width: 44px;
        height: 44px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bs-light-primary);
        color: var(--bs-primary);
        font-weight: 700;
        flex: 0 0 44px;
    }

    .orders-page .stock-fail-item img,
    .stock-alert-drawer .stock-fail-item img {
        width: 44px;
        height: 44px;
        border-radius: 0.5rem;
        object-fit: cover;
    }

    .orders-page .provider-item {
        border: 1px solid var(--bs-gray-300);
        border-radius: 0.75rem;
        padding: 0.9rem;
        margin-bottom: 0.7rem;
        transition: all 0.15s ease;
    }

    .orders-page .provider-item:hover {
        border-color: var(--bs-primary);
        background: var(--bs-light-primary);
    }

    .orders-page .table-responsive {
        overflow-x: auto;
    }

    .stock-alert-fab {
        position: fixed !important;
        inset: auto 24px 24px auto;
        top: 50% !important;
        left: auto !important;
        right: 24px !important;
        bottom: 24px !important;
        transform: none !important;
        z-index: 1095;
        width: 56px;
        height: 56px;
        padding: 0;
        border-radius: 50% !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.16);
        transition: right 0.2s ease, bottom 0.2s ease, box-shadow 0.2s ease;
    }

    .stock-alert-fab i {
        font-size: 1.15rem;
    }

    .stock-alert-fab.is-critical {
        animation: stockAlertPulse 1s ease-in-out infinite;
    }

    .stock-alert-fab.is-ok {
        box-shadow: 0 0.5rem 1.5rem rgba(80, 205, 137, 0.32);
    }

    .stock-alert-count {
        position: absolute;
        top: -6px;
        right: -6px;
        min-width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        padding: 0 4px;
        border: 2px solid #fff;
        line-height: 1;
    }

    .stock-alert-drawer {
        width: min(430px, calc(100vw - 20px));
    }

    .stock-alert-drawer .stock-fail-title {
        color: #e8eeff;
        text-decoration: none;
        display: inline-block;
        max-width: 100%;
    }

    .stock-alert-drawer .stock-fail-title:hover {
        color: #8ecbff;
    }

    .stock-alert-drawer .stock-fail-subtitle {
        color: #9aa7c4;
    }

    .stock-alert-drawer .stock-fail-metrics {
        color: #ff6f95;
        font-weight: 700;
    }

    .stock-alert-fab.is-drawer-open {
        right: calc(min(430px, calc(100vw - 20px)) + 24px) !important;
    }

    @keyframes stockAlertPulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.16);
        }

        50% {
            transform: scale(1.02);
            box-shadow: 0 0.75rem 2rem rgba(241, 65, 108, 0.45);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.16);
        }
    }

    @media (max-width: 992px) {
        .orders-page .orders-filters {
            padding: 0.75rem;
        }

        .orders-page .orders-filters .form-control,
        .orders-page .orders-filters .form-select {
            min-width: 100%;
        }

        .stock-alert-fab {
            inset: auto 14px 14px auto;
            right: 14px !important;
            bottom: 14px !important;
            width: 52px;
            height: 52px;
        }

        .stock-alert-fab.is-drawer-open {
            right: 14px !important;
        }
    }
</style>

<style id="orders-redesign">
    .orders-page {
        --bs-gutter-x: 0;
        --bs-gutter-y: 12px;
    }

    /* Panels */
    .admin-page-main .orders-page > .col-12 > .card.card-flush:not(.modal-content):not(.modal .card) {
        margin: 0 !important;
        overflow: hidden;
        background: #151520 !important;
        border: 1px solid #202031 !important;
        border-radius: 12px !important;
        box-shadow: none !important;
    }

    .orders-page .card-header {
        min-height: 0;
        padding: 18px 23px !important;
        gap: 12px;
        background: transparent !important;
        border-bottom: 1px solid #242435 !important;
    }

    .orders-page .card-header .card-title {
        margin: 0;
        color: #f3f3f7;
        font-size: 14px;
        font-weight: 600;
    }

    .orders-page .card-body {
        padding: 0 !important;
    }

    .orders-page .card-header .btn,
    .orders-page .card-header .form-select {
        height: 36px;
        padding: 8px 12px;
        background-color: #29293c !important;
        border: 1px solid #333348 !important;
        border-radius: 6px;
        box-shadow: none;
        color: #bdbdd0 !important;
        font-size: 11px;
        font-weight: 400;
    }

    .orders-page .card-header .btn:hover {
        border-color: #66572e !important;
        color: #fff !important;
    }

    .orders-page .card-header .btn i {
        font-size: 11px;
        color: inherit;
    }

    .orders-page .card-header .btn.btn-warning {
        background-color: #44341c !important;
        border-color: #775528 !important;
        color: #ffcf6b !important;
    }

    .orders-page .card-header .btn.btn-success {
        background-color: #173b2e !important;
        border-color: #295d45 !important;
        color: #63e2aa !important;
    }

    .orders-page #refreshInterval {
        width: auto;
        min-width: 72px !important;
        padding-right: 30px;
    }

    /* Filters: compact 6-column grid, same fields and ids */
    .orders-page .orders-filters {
        margin: 0 !important;
        padding: 18px 22px 12px !important;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
    }

    .orders-page .orders-filters > .row {
        --bs-gutter-x: 0;
        --bs-gutter-y: 0;
        margin: 0;
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 10px;
        align-items: end;
    }

    .orders-page .orders-filters > .row > [class*="col-"] {
        width: auto;
        max-width: none;
        margin: 0;
        padding: 0;
        flex: none;
    }

    .orders-page .orders-filters .form-label {
        display: block;
        margin: 0 0 5px;
        color: #a8a8bd;
        font-size: 10px !important;
        font-weight: 400;
    }

    .orders-page .orders-filters .form-control,
    .orders-page .orders-filters .form-select,
    .orders-page .orders-filters .select2-container .select2-selection {
        height: 33px;
        min-height: 33px;
        padding: 0 10px;
        background-color: #1c1c2a !important;
        border: 1px solid #2b2b3b !important;
        border-radius: 5px;
        box-shadow: none !important;
        color: #d7d7e2 !important;
        font-size: 11px;
    }

    .orders-page .orders-filters .form-control:focus,
    .orders-page .orders-filters .form-select:focus,
    .orders-page .orders-filters .select2-container--open .select2-selection,
    .orders-page .orders-filters .select2-container--focus .select2-selection {
        border-color: #a57c20 !important;
    }

    .orders-page .orders-filters input[type="date"] {
        color-scheme: dark;
    }

    .orders-page .orders-filters .select2-container {
        width: 100% !important;
    }

    .orders-page .orders-filters .select2-selection__rendered {
        padding: 0 !important;
        line-height: 31px !important;
        color: #d7d7e2 !important;
        font-size: 11px;
    }

    .orders-page .orders-filters .btn.btn-primary {
        height: 35px;
        padding: 8px 12px;
        background: #0a9de7 !important;
        border: 1px solid #0a9de7 !important;
        border-radius: 6px;
        box-shadow: none;
        font-size: 11px;
        font-weight: 400;
    }

    /* Tables */
    .orders-page .table-responsive {
        padding: 0 20px 12px;
        overflow-x: auto;
    }

    .orders-page .table-responsive .table-responsive {
        padding: 0;
    }

    .orders-page > .col-12 {
        margin-top: 0;
    }

    .orders-page > .col-12 + .col-12 {
        margin-top: 12px;
    }

    /* DataTables footer: length, info, pagination */
    .orders-page .dataTables_wrapper > .row {
        --bs-gutter-x: 0;
        margin: 0;
        padding: 13px 3px 0;
        color: #9292a9;
        font-size: 11px;
    }

    .orders-page .dataTables_wrapper > .row > [class*="col-"] {
        padding: 0;
    }

    .orders-page .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        color: #9292a9;
        font-size: 11px;
        font-weight: 400;
    }

    .orders-page .dataTables_length select {
        width: auto;
        height: 28px;
        min-height: 28px;
        padding: 0 24px 0 8px;
        background-color: #1c1c2a;
        border: 1px solid #2b2b3b;
        border-radius: 5px;
        color: #d7d7e2;
        font-size: 11px;
    }

    .orders-page .dataTables_info {
        padding: 0 0 0 14px;
        color: #9292a9;
        font-size: 11px;
    }

    .orders-page .dataTables_paginate .pagination {
        margin: 0;
        gap: 4px;
    }

    .orders-page .dataTables_paginate .page-link {
        padding: 4px 10px;
        background: transparent;
        border: 0;
        box-shadow: none;
        color: #9292a9;
        font-size: 11px;
    }

    .orders-page .dataTables_paginate .page-item.active .page-link {
        background: transparent;
        color: #ffb511;
        font-weight: 700;
    }

    .orders-page .dataTables_paginate .page-item.disabled .page-link {
        color: #55556b;
    }

    .orders-page table.orders-table {
        --bs-table-bg: transparent;
        --bs-table-accent-bg: transparent;
        --bs-table-striped-bg: transparent;
        --bs-table-hover-bg: transparent;
        --bs-table-color: #c6c6d3;
        width: 100%;
        min-width: 1180px;
        margin: 0;
        border-collapse: collapse;
    }

    .orders-page table.orders-table > thead > tr > th {
        padding: 10px 8px !important;
        background: transparent;
        border-bottom: 1px dotted #303045 !important;
        color: #c5c5d3 !important;
        font-size: 10px;
        font-weight: 600;
        text-align: left;
        text-transform: none;
        white-space: nowrap;
    }

    .orders-page table.orders-table > tbody > tr > td {
        padding: 13px 8px !important;
        background: transparent;
        border-bottom: 1px dotted #2b2b3d !important;
        color: #c6c6d3;
        font-size: 11px;
        vertical-align: middle;
    }

    .orders-page table.orders-table > tbody > tr > td:first-child {
        color: #eee;
        font-weight: 600;
    }

    .orders-page table.orders-table > tbody > tr:hover {
        background: #212133;
    }

    .orders-page table.orders-table > tbody > tr.order-state-waiting {
        background: rgba(232, 183, 78, 0.08);
    }

    .orders-page table.orders-table > tbody > tr.order-state-processing {
        background: rgba(64, 137, 207, 0.106);
    }

    .orders-page table.orders-table > tbody > tr.order-state-delivered {
        background: rgba(37, 153, 111, 0.106);
    }

    .orders-page table.orders-table > tbody > tr.order-state-cancelled {
        background: rgba(110, 119, 143, 0.13);
    }

    .orders-page table.orders-table > tbody > tr.order-state-fraud {
        background: rgba(231, 85, 39, 0.184);
    }

    .orders-page table.orders-table > tbody > tr.order-state-waiting > td:first-child {
        box-shadow: inset 3px 0 0 #e8b74e;
    }

    .orders-page table.orders-table > tbody > tr.order-state-processing > td:first-child {
        box-shadow: inset 3px 0 0 #5799dc;
    }

    .orders-page table.orders-table > tbody > tr.order-state-delivered > td:first-child {
        box-shadow: inset 3px 0 0 #25996f;
    }

    .orders-page table.orders-table > tbody > tr.order-state-cancelled > td:first-child {
        box-shadow: inset 3px 0 0 #6e778f;
    }

    .orders-page table.orders-table > tbody > tr.order-state-fraud > td:first-child {
        box-shadow: inset 3px 0 0 #e75527;
    }

    .orders-page .copy-value {
        margin-right: 4px;
        color: #6b6b85;
        font-size: 10px;
    }

    .orders-page .copy-value:hover {
        color: #ffb511;
    }

    .orders-page table.orders-table a {
        text-decoration: none;
    }

    .orders-page .order-product {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 150px;
    }

    .orders-page .order-category-img {
        flex: 0 0 33px;
        width: 33px;
        height: 33px;
        padding: 2px;
        background: #232333;
        border: 1px solid #363649;
        border-radius: 6px;
        object-fit: contain;
    }

    .orders-page .order-product-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
        line-height: 1.3;
    }

    .orders-page .order-product-cat {
        color: #55c791;
        font-size: 9.2px;
    }

    .orders-page .order-product-info a,
    .orders-page .order-product-info strong {
        color: #c6c6d3;
        font-size: 11px;
        font-weight: 700;
    }

    .orders-page .order-product-qty {
        color: #ffb511;
        font-size: 9px;
    }

    .orders-page .order-flag-fraud {
        display: inline-block;
        margin-top: 4px;
        padding: 2px 6px;
        background: #e57d27;
        border-radius: 4px;
        color: #fff;
        font-size: 8.5px;
        font-weight: 700;
        letter-spacing: 0.4px;
    }

    .orders-page table.orders-table .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border: 1px solid transparent;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.3;
    }

    .orders-page table.orders-table .badge-success {
        background: #173b2e;
        border-color: rgba(99, 226, 170, 0.18);
        color: #63e2aa;
    }

    .orders-page table.orders-table .badge-danger {
        background: #491f34;
        border-color: rgba(255, 102, 143, 0.18);
        color: #ff668f;
    }

    .orders-page table.orders-table .badge-warning {
        background: #44341c;
        border-color: rgba(255, 207, 107, 0.2);
        color: #ffcf6b;
    }

    .orders-page table.orders-table .badge-info,
    .orders-page table.orders-table .badge-primary {
        background: rgba(60, 137, 211, 0.19);
        border-color: rgba(78, 154, 228, 0.24);
        color: #92c9ff;
    }

    .orders-page table.orders-table .badge-secondary,
    .orders-page table.orders-table .badge-dark,
    .orders-page table.orders-table .badge-light {
        background: #2a2a3c;
        color: #b5b5c9;
    }

    .orders-page table.orders-table .order-channel-api {
        background: #5631a5;
        border-color: transparent;
        color: #fff;
    }

    .orders-page table.orders-table > thead > tr > th:last-child,
    .orders-page table.orders-table > tbody > tr > td:last-child {
        width: 1%;
        white-space: nowrap;
    }

    .orders-page table.orders-table > tbody > tr > td:last-child .btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin: 2px 4px 2px 0 !important;
        padding: 6px 9px !important;
        background: #29293d;
        border: 1px solid #35354b;
        border-radius: 5px;
        box-shadow: none;
        color: #b5b5c9;
        font-size: 10px !important;
        font-weight: 500;
    }

    .orders-page table.orders-table > tbody > tr > td:last-child .btn:hover {
        border-color: #8a6921;
        color: #ffb511;
    }

    .orders-page table.orders-table > tbody > tr > td:last-child .btn-danger {
        background: #3b1d2a;
        border-color: #66263c;
        color: #ff668f;
    }

    .orders-page table.orders-table > tbody > tr > td:last-child .btn-success {
        background: #173b2e;
        border-color: #295d45;
        color: #63e2aa;
    }

    @media (max-width: 1250px) {
        .orders-page .orders-filters > .row {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .orders-page .orders-filters > .row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .orders-page .card-header {
            padding: 12px !important;
        }
    }

    /* Filter controls */
    .orders-page .card-body .form-control,
    .orders-page .card-body .form-select,
    .orders-page .card-body .select2-container .select2-selection--single {
        height: 36px !important;
        min-height: 36px;
        font-size: 11px;
        background: #1e1e2d !important;
        border: 1px solid #343449 !important;
        border-radius: 6px !important;
        color: #eee !important;
        box-shadow: none;
    }

    .orders-page .card-body .form-control:focus,
    .orders-page .card-body .form-select:focus {
        border-color: #c68e1a !important;
    }

    .orders-page .card-body label.form-label,
    .orders-page .card-body .form-label {
        font-size: 10px;
        color: #9999af;
        margin-bottom: 5px;
    }

    .orders-page .card-body input[type="date"] {
        color-scheme: dark;
    }

    .orders-page table.orders-table > tbody > tr > td:last-child .btn-success .fa-shield-check::before {
        content: "\f058";
    }

    /* Order detail modal */
    .od-tabs { display: flex; gap: 6px; border-bottom: 1px solid #38384c; margin: 0 0 16px; overflow-x: auto; }
    .od-tabs button { display: inline-flex; align-items: center; gap: 7px; border: 0; border-bottom: 2px solid transparent; background: none; color: #aaaac0; padding: 11px 12px; cursor: pointer; font: 600 10px Poppins, Arial, sans-serif; white-space: nowrap; }
    .od-tabs button:hover { color: #f5f5fa; }
    .od-tabs button.active { color: #ffc21a; border-bottom-color: #ffc21a; }
    .od-pane { display: none; }
    .od-pane.active { display: block; }
    .od-note { margin: 16px 0; padding: 13px 15px; border-radius: 8px; border-left: 3px solid #ffc21a; background: #2c291c; font-size: 11px; }
    .od-note.ok { background: #1b2c26; border-color: #4bc990; }
    .od-note.no { background: #32232a; border-color: #f06780; }
    .od-note strong { display: block; color: #f5f5fa; }
    .od-note p { margin: 5px 0 0; color: #bcbccc; }
    .od-doc { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px 16px; margin: 10px 0; border: 1px solid #343449; border-radius: 8px; background: #1a1a27; color: #e6e6f1; font-size: 12px; text-decoration: none; }
    .od-doc:hover { border-color: #a67713; color: #fff; }
    .od-doc em { font-style: normal; color: #ffc21a; font-size: 10px; font-weight: 600; }
    .od-epin-tile { min-height: 74px; }
    #orderDetailModal .modal-header { align-items: center; }
    #orderDetailModal .btn-close { filter: invert(1) grayscale(1) brightness(1.6); opacity: .7; }

    #orderDetailModal .modal-content {
        background: #191925;
        border: 1px solid #39394c;
        border-radius: 12px;
        box-shadow: 0 24px 90px rgba(0, 0, 0, 0.6);
    }

    #orderDetailModal .modal-header,
    #orderDetailModal .modal-footer {
        padding: 18px 23px;
        border-color: #303044;
    }

    #orderDetailModal .modal-title {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
    }

    #orderDetailModal .modal-footer .btn {
        height: 34px;
        padding: 0 14px;
        background: #29293c;
        border: 1px solid #333348;
        border-radius: 6px;
        color: #bdbdd0;
        font-size: 11px;
        font-weight: 400;
    }

    #orderDetailModal .modal-body {
        padding: 16px 23px 20px;
        color: #d4d4e1;
        font-size: 11px;
    }

    .od-product {
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 70px;
        margin-bottom: 6px;
        padding: 13px;
        background: #252535;
        border: 1px solid #323247;
        border-radius: 9px;
    }

    .od-product img {
        flex: 0 0 45px;
        width: 45px;
        height: 45px;
        border-radius: 8px;
        object-fit: cover;
    }

    .od-product-main {
        flex: 1;
        min-width: 0;
    }

    .od-product-main strong {
        display: block;
        color: #f5f5fa;
        font-size: 14px;
    }

    .od-product-main small {
        display: block;
        margin-top: 5px;
        color: #9a9aad;
        font-size: 11px;
    }

    .od-status {
        padding: 6px 9px;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .od-status.ok {
        background: #123b30;
        color: #60d7a1;
    }

    .od-status.no {
        background: #452331;
        color: #ff7993;
    }

    .od-status.wait {
        background: #493817;
        color: #ffcd5c;
    }

    .od-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 16px 0 8px;
        padding-bottom: 10px;
        border-bottom: 1px solid #333346;
        color: #f5f5f9;
        font-size: 12px;
        font-weight: 600;
    }

    .od-ico {
        display: inline-flex;
        flex: 0 0 22px;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        background: rgba(255, 181, 17, 0.085);
        border: 1px solid rgba(255, 181, 17, 0.2);
        border-radius: 5px;
        color: #ffb511;
        font-size: 10px;
    }

    .od-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 20px;
    }

    .od-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-height: 40px;
        border-bottom: 1px solid #303044;
        font-size: 11px;
    }

    .od-key {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        color: #a4a4b8;
    }

    .od-row strong {
        color: #f3f3f7;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .od-player {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .od-tile {
        position: relative;
        min-height: 66px;
        padding: 12px 13px;
        background: #222233;
        border: 1px solid #36364b;
        border-radius: 9px;
    }

    .od-tile small {
        display: block;
        margin-bottom: 8px;
        color: #a7a7ba;
        font-size: 11px;
    }

    .od-tile strong {
        color: #fff;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .od-copy {
        position: absolute;
        right: 10px;
        bottom: 10px;
        width: 28px;
        height: 28px;
        background: #2b2b3e;
        border: 1px solid #414155;
        border-radius: 6px;
        color: #e8e8f2;
        cursor: pointer;
    }

    .od-copy:hover {
        border-color: #ffb511;
        color: #ffb511;
    }

    .od-raw {
        margin: 8px 0;
        background: #20202e;
        border: 1px solid #36364b;
        border-radius: 8px;
    }

    .od-raw summary {
        padding: 10px 13px;
        color: #c6c6d3;
        cursor: pointer;
    }

    .od-raw pre {
        margin: 0;
        padding: 12px 13px;
        background: #12131c;
        border-top: 1px solid #36364b;
        color: #d8e4f0;
        font-size: 11px;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .od-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 16px;
    }

    @media (max-width: 580px) {
        .od-fields,
        .od-player {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="orders-page row g-6">
    <div class="col-12">
        <div class="card shadow-sm card-flush">
            <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <h4 class="card-title m-0">Bekleyen Siparişler</h4>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <label class="mb-0">
                        <select id="refreshInterval" class="form-select form-select-sm form-select-solid" style="min-width: 120px;">
                            <option value="1000" selected>1 sn</option>
                            <option value="2000">2 sn</option>
                            <option value="5000">5 sn</option>
                            <option value="10000">10 sn</option>
                            <option value="15000">15 sn</option>
                            <option value="30000">30 sn</option>
                        </select>
                    </label>
                    <button class="btn btn-light btn-sm" id="stop-start-btn">
                        <i class="fa-solid fa-stop me-1"></i>Ekranı Dondur
                    </button>
                    <button class="btn btn-light btn-sm" id="refreshrBtn1"><i class="fa-solid fa-arrows-rotate me-1"></i>Filtreleri Sıfırla</button>
                </div>
            </div>
            <div class="card-body">
                <div class="orders-filters mb-4" id="filterPanelOps">
                    <div class="row g-2 align-items-end">
                        <div class="col-2 col-md-2 col-lg-2">
                            <label class="form-label fs-8">Sipariş No</label>
                            <input class="form-control form-control-sm form-control-solid" type="text" id="searchOrderID1">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Reseller Sipariş No</label>
                            <input class="form-control form-control-sm form-control-solid" type="text" id="searchResellerOrderID1">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Ürün</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="searchProductID1">
                                <option value="">Tümü</option>
                                <?php foreach ($products as $product) : ?>
                                    <option value="<?= (int)$product->product_id ?>"><?= html_escape($product->product_name) ?>#<?= (int)$product->product_id ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Kategori</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="searchCategory1">
                                <option value="">Tümü</option>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?= (int)$category->category_id ?>"><?= html_escape($category->category_title) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">UserID/Server</label>
                            <input class="form-control form-control-sm form-control-solid" type="text" id="searchUserIDorServer1">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Durum</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="searchStatus1">
                                <?php foreach ($statusOptions as $statusValue => $statusLabel) : ?>
                                    <option value="<?= html_escape($statusValue) ?>"><?= html_escape($statusLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Başlangıç</label>
                            <input id="startDate1" class="form-control form-control-sm form-control-solid" value="<?= $yesterday ?>" type="date" max="<?= $today ?>">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Bitiş</label>
                            <input id="endDate1" class="form-control form-control-sm form-control-solid" value="<?= $today ?>" type="date" max="<?= $today ?>">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Reseller</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="reseller1">
                                <option value="">Tümü</option>
                                <?php foreach ($resellers as $reseller) : ?>
                                    <option value="<?= (int)$reseller->user_id ?>"><?= html_escape($reseller->user_name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Supplier</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="supplier1">
                                <option value="">Tümü</option>
                                <?php foreach ($suppiers as $suppier) : ?>
                                    <option value="<?= (int)$suppier->user_id ?>"><?= html_escape($suppier->user_name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Site</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="site_id1">
                                <option value="">Tümü</option>
                                <option value="2">Topupcenter</option>
                                <option value="3">Dijipin</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2">
                            <button class="btn btn-primary btn-sm w-100" id="filterBtn1"><i class="fa-solid fa-filter me-1"></i>Ara</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="orderTable" class="orders-table display table table-striped w-100 align-middle table-row-dashed">
                        <thead>
                            <tr>
                                <th>Sipariş No</th>
                                <th>Rs Sipariş No</th>
                                <th>Ürün Adı#ID</th>
                                <th>Sipariş Data</th>
                                <th>Maliyet</th>
                                <th>Satış F.</th>
                                <th>Müşteri</th>
                                <th>Tedarikçi</th>
                                <th>Sip.Kanalı</th>
                                <th>Sip.Tarihi</th>
                                <th>İşlem</th>
                                <th>Durum</th>
                                <th>Detay</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm mb-12 card-flush">
            <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <h4 class="card-title m-0">Tüm Siparişler</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-light btn-sm" id="allBtn"><i class="fa-solid fa-infinity me-1"></i>Tümünü Göster</button>
                    <button class="btn btn-light btn-sm" id="todayBtn"><i class="fa-solid fa-calendar-day me-1"></i>Bugün</button>
                    <button class="btn btn-light btn-sm" id="refreshrBtn"><i class="fa-solid fa-arrows-rotate me-1"></i>Filtreleri Sıfırla</button>
                </div>
            </div>
            <div class="card-body">
                <div class="orders-filters mb-4" id="filterPanelAll">
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Sipariş No</label>
                            <input class="form-control form-control-sm form-control-solid" type="text" id="searchOrderID">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Reseller Sipariş No</label>
                            <input class="form-control form-control-sm form-control-solid" type="text" id="searchResellerOrderID">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Ürün</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="searchProductID">
                                <option value="">Tümü</option>
                                <?php foreach ($products as $product) : ?>
                                    <option value="<?= (int)$product->product_id ?>"><?= html_escape($product->product_name) ?>#<?= (int)$product->product_id ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Kategori</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="searchCategory">
                                <option value="">Tümü</option>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?= (int)$category->category_id ?>"><?= html_escape($category->category_title) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">UserID/Server</label>
                            <input class="form-control form-control-sm form-control-solid" type="text" id="searchUserIDorServer">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Durum</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="searchStatus">
                                <?php foreach ($statusOptions as $statusValue => $statusLabel) : ?>
                                    <option value="<?= html_escape($statusValue) ?>"><?= html_escape($statusLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Başlangıç</label>
                            <input id="startDate" class="form-control form-control-sm form-control-solid" value="<?= $yesterday ?>" type="date" max="<?= $today ?>">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Bitiş</label>
                            <input id="endDate" class="form-control form-control-sm form-control-solid" value="<?= $today ?>" type="date" max="<?= $today ?>">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Reseller</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="reseller">
                                <option value="">Tümü</option>
                                <?php foreach ($resellers as $reseller) : ?>
                                    <option value="<?= (int)$reseller->user_id ?>"><?= html_escape($reseller->user_name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Supplier</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="supplier">
                                <option value="">Tümü</option>
                                <?php foreach ($suppiers as $suppier) : ?>
                                    <option value="<?= (int)$suppier->user_id ?>"><?= html_escape($suppier->user_name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label fs-8">Site</label>
                            <select class="form-select form-select-sm js-filter-select form-select-solid" id="site_id">
                                <option value="">Tümü</option>
                                <option value="2">Topupcenter</option>
                                <option value="3">Dijipin</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2">
                            <button class="btn btn-primary btn-sm w-100" id="filterBtn"><i class="fa-solid fa-filter me-1"></i>Ara</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="orderTable2" class="orders-table display table table-striped w-100 align-middle table-row-dashed">
                        <thead>
                            <tr>
                                <th>Sipariş No</th>
                                <th>Rs Sipariş No</th>
                                <th>Ürün Adı#ID</th>
                                <th>Sipariş Data</th>
                                <th>Maliyet</th>
                                <th>Satış F.</th>
                                <th>Müşteri</th>
                                <th>Tedarikçi</th>
                                <th>Sip.Kanalı</th>
                                <th>Sip.Tarihi</th>
                                <th>İşlem</th>
                                <th>Durum</th>
                                <th>Detay</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<button type="button" id="stockAlertFab" class="btn btn-icon btn-success stock-alert-fab is-ok" data-bs-toggle="offcanvas" data-bs-target="#stockAlarmDrawer" aria-controls="stockAlarmDrawer" aria-label="Epin Alarm" title="Epin Alarm">
    <i id="stockAlertFabIcon" class="fa-solid fa-circle-check"></i>
    <span id="stockAlertCount" class="badge badge-success stock-alert-count d-none">0</span>
    <span class="visually-hidden">Epin Alarm</span>
</button>

<div class="offcanvas offcanvas-end stock-alert-drawer" tabindex="-1" id="stockAlarmDrawer" aria-labelledby="stockAlarmDrawerLabel" data-bs-scroll="true" data-bs-backdrop="false">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title mb-1" id="stockAlarmDrawerLabel">Anlık Epin Stok Alarmı</h5>
            <div class="text-muted fs-8" id="stockAlertSummary">Bekleyen epin siparişleri ve havuz stokları karşılaştırılıyor...</div>
        </div>
        <button type="button" class="btn btn-sm btn-icon btn-light" data-bs-dismiss="offcanvas" aria-label="Kapat">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <div class="offcanvas-body pt-4">
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-light btn-sm" id="refreshStockAlarmBtn"><i class="fa-solid fa-arrows-rotate me-1"></i>Yenile</button>
        </div>
        <div class="row g-2 stok-fails">
            <div class="col-12 text-muted fs-7">Bekleyen epin siparişleri ve havuz stokları karşılaştırılıyor...</div>
        </div>
    </div>
</div>

<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">Sipariş Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>

            <div class="modal-body" id="orderDetailContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<style>
    #fraudModal .modal-content { background: #1d1f2c; border: 1px solid #303a43; border-radius: 12px; color: #f4f4f8; }
    #fraudModal .modal-header { border: 0; padding: 22px 26px 0; align-items: center; }
    #fraudModal .modal-title { font-size: 18px; font-weight: 700; }
    #fraudModal .fraud-x { background: transparent; border: 1px solid #ffc21a; color: #ffc21a; border-radius: 50%; width: 31px; height: 31px; cursor: pointer; font-size: 18px; line-height: 1; }
    #fraudModal .modal-body { padding: 18px 26px 6px; }
    #fraudModal .fraud-tab { color: #ffc21a; border-bottom: 1px solid #38384a; padding: 0 0 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; }
    #fraudModal .fraud-toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 22px; }
    #fraudModal .fraud-toggle-row strong { font-size: 13px; }
    #fraudModal .fraud-toggle-row small { display: block; color: #9699aa; font-size: 10px; margin-top: 6px; }
    #fraudModal .fraud-switch { position: relative; width: 40px; height: 23px; flex: none; }
    #fraudModal .fraud-switch input { position: absolute; inset: 0; opacity: 0; margin: 0; cursor: pointer; z-index: 1; width: 100%; height: 100%; }
    #fraudModal .fraud-switch span { position: absolute; inset: 0; border-radius: 30px; background: #57596b; transition: .2s; }
    #fraudModal .fraud-switch span:before { content: ""; position: absolute; width: 17px; height: 17px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .2s; }
    #fraudModal .fraud-switch input:checked + span { background: #ffc21a; }
    #fraudModal .fraud-switch input:checked + span:before { transform: translateX(17px); }
    #fraudModal .fraud-label { font-size: 13px; font-weight: 600; display: block; margin-bottom: 9px; }
    #fraudModal textarea { width: 100%; resize: vertical; min-height: 95px; background: #202232; border: 1px solid #34374d; border-radius: 7px; padding: 13px; color: #fff; font: 12px Poppins, Arial, sans-serif; }
    #fraudModal textarea:focus { outline: 0; border-color: #c68e1a; }
    #fraudModal .modal-footer { border: 0; padding: 14px 26px 24px; gap: 10px; }
    #fraudModal .modal-footer button { flex: 1; border: 0; border-radius: 7px; padding: 12px; font-weight: 700; cursor: pointer; margin: 0; }
    #fraudModal .fraud-cancel { background: #ffc21a; color: #1b1921; }
    #fraudModal #fraudSubmit { background: #a92d3e; color: #fff; }
    #fraudModal #fraudSubmit.is-approve { background: #145b32; }
    #fraudModal #fraudSubmit:disabled { opacity: .5; cursor: not-allowed; }
</style>
<div class="modal fade" id="fraudModal" tabindex="-1" aria-labelledby="fraudModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:610px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fraudModalTitle">Fraud Durumu</h5>
                <button type="button" class="fraud-x" data-bs-dismiss="modal" aria-label="Kapat">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fraudOrderId">
                <div class="fraud-tab">Sipariş Onay İşlemi</div>
                <div class="fraud-toggle-row">
                    <div>
                        <strong>Sipariş durumunu onayla</strong>
                        <small id="fraudHint">Onaylanırsa sipariş işleme alınabilir hale gelir.</small>
                    </div>
                    <label class="fraud-switch"><input type="checkbox" id="fraudDecision" aria-label="Siparişi onayla" checked><span></span></label>
                </div>
                <label class="fraud-label" for="fraudNote">Açıklama</label>
                <textarea id="fraudNote" maxlength="1000" placeholder="İşleme ilişkin açıklamanızı yazın..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="fraud-cancel" data-bs-dismiss="modal">Vazgeç</button>
                <button type="button" id="fraudSubmit" class="is-approve">Onayla</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="orderCancelModal" tabindex="-1" aria-labelledby="orderCancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderCancelModalLabel">Siparişi iptal et ve iade et</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cancelOrderId">
                <label class="form-label">İptal sebebi</label>
                <select class="form-select mb-3 form-select-solid" id="cancelReasonId">
                    <option value="">Manuel sebep yazacağım</option>
                    <?php foreach ($cancellation_reasons as $reason): ?>
                        <option value="<?= (int)$reason->reason_id ?>"><?= html_escape($reason->reason_text) ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea class="form-control form-control-solid" id="cancelManualReason" rows="3" placeholder="Manuel sebep (seçim yapılmadıysa zorunlu)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="button" class="btn btn-danger" id="confirmOrderCancel">İptal et ve iade et</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="orderStatusChangeModal" tabindex="-1" aria-labelledby="orderStatusChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderStatusChangeModalLabel">Sipariş Durumu Değiştir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" id="orderStatusChangeContent">
                <div class="alert alert-light-danger">
                    Bu alanı yalnızca süreç etkilerini biliyorsanız kullanın.
                </div>
                <p>Eğer ürün satıcıda tamamlanmış ancak panelde bekliyorsa <strong class="text-success">Tamamlandı</strong> seçin.</p>
                <p>Eğer ürün satıcıda iptal olmuşsa <strong class="text-danger">İptal Edildi</strong> seçin.</p>
                <p>Fraud kontrolü başarıyla geçildiğinde <strong class="text-warning">Yeni Sipariş</strong> durumuna çekin.</p>
                <hr>
                <input type="hidden" id="order-id">
                <select name="orderStatus" id="orderStatus" class="form-select w-100 form-select-solid">
                    <option value="">Sipariş Durumu Seç</option>
                    <option value="0">Yeni Sipariş</option>
                    <option value="1">Tamamlandı</option>
                    <option value="2">İptal Edildi</option>
                    <option value="13">Fraud Kontrolü</option>
                    <option value="14">Tedarikçi Değiştir</option>
                </select>
                <button class="btn btn-success mt-3 w-100" id="changeStatusBtn">Durumu Güncelle</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="providerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tedarikçi Seç</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="providerList"></div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        const defaultStartDate = '<?= $yesterday ?>';
        const defaultEndDate = '<?= $today ?>';
        const statusMap = {
            0: 'Yeni Sipariş',
            1: 'Tamamlandı',
            2: 'İptal Edildi',
            3: 'İşleme Alındı',
            4: 'Satıcı Onayladı',
            5: 'Satıcı Onaylamadı',
            9: 'Cron İşlemi',
            13: 'Fraud Kontrolü',
            14: 'Tedarikçi Değiştir',
            15: 'Yükleme Bekliyor'
        };

        let tableOps = null;
        let tableAll = null;
        let currentOrderID = null;
        let tableOpsTimer = null;
        let tableAllTimer = null;
        let stockTimer = null;
        let refreshPausedByUser = false;

        const filtersOps = {
            order_id: '#searchOrderID1',
            reseller_order_id: '#searchResellerOrderID1',
            product_id: '#searchProductID1',
            category: '#searchCategory1',
            status: '#searchStatus1',
            startDate: '#startDate1',
            endDate: '#endDate1',
            reseller: '#reseller1',
            supplier: '#supplier1',
            site_id: '#site_id1',
            searchUserIDorServer: '#searchUserIDorServer1'
        };

        const filtersAll = {
            order_id: '#searchOrderID',
            reseller_order_id: '#searchResellerOrderID',
            product_id: '#searchProductID',
            category: '#searchCategory',
            status: '#searchStatus',
            startDate: '#startDate',
            endDate: '#endDate',
            reseller: '#reseller',
            supplier: '#supplier',
            site_id: '#site_id',
            searchUserIDorServer: '#searchUserIDorServer'
        };

        function escapeHtml(value) {
            const str = String(value ?? '');
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function getRefreshInterval() {
            const selected = parseInt($('#refreshInterval').val(), 10);
            if (Number.isFinite(selected) && selected >= 1000) {
                return selected;
            }
            return 1000;
        }

        function updateRefreshBadge() {
            if (refreshPausedByUser) {
                $('#stop-start-btn').removeClass('btn-light btn-warning').addClass('btn-success').html('<i class="fa-solid fa-play me-1"></i>Devam Et');
                return;
            }

            if (document.hidden) {
                $('#stop-start-btn').removeClass('btn-light btn-success').addClass('btn-warning').html('<i class="fa-solid fa-eye-slash me-1"></i>Sekme Pasif');
                return;
            }

            $('#stop-start-btn').removeClass('btn-success btn-warning').addClass('btn-light').html('<i class="fa-solid fa-stop me-1"></i>Ekranı Dondur');
        }

        function collectFilters(map) {
            const payload = {};
            Object.keys(map).forEach(function(key) {
                payload[key] = $(map[key]).val();
            });
            return payload;
        }

        function reloadTable(table, keepPage, minSpacingMs) {
            if (!table) {
                return;
            }

            const settings = table.settings()[0];
            const state = settings ? settings._ordersTableState : null;
            if (state && state.requestInFlight) {
                return;
            }

            const spacing = Number(minSpacingMs) || 0;
            if (state && spacing > 0) {
                const now = Date.now();
                if (state.lastAutoReloadAt && (now - state.lastAutoReloadAt) < spacing) {
                    return;
                }
                state.lastAutoReloadAt = now;
            }

            const resetPaging = keepPage ? false : true;
            table.ajax.reload(null, resetPaging);
        }

        function reloadTables(keepPage) {
            reloadTable(tableOps, keepPage);
            reloadTable(tableAll, keepPage);
        }

        function startTimers() {
            clearInterval(tableOpsTimer);
            clearInterval(tableAllTimer);
            clearInterval(stockTimer);

            if (refreshPausedByUser || document.hidden) {
                updateRefreshBadge();
                return;
            }

            const interval = getRefreshInterval();
            const autoMinSpacing = Math.max(800, interval - 100);
            const stockInterval = 10000;

            tableOpsTimer = setInterval(function() {
                reloadTable(tableOps, true, autoMinSpacing);
            }, interval);

            tableAllTimer = setInterval(function() {
                reloadTable(tableAll, true, autoMinSpacing);
            }, interval);

            stockTimer = setInterval(function() {
                loadStokFails();
            }, stockInterval);

            updateRefreshBadge();
        }

        function resetFilterPanel(panelSelector, includeDates) {
            const panel = $(panelSelector);
            panel.find('input[type="text"]').val('');
            panel.find('select').val('').trigger('change');
            if (includeDates) {
                panel.find('input[type="date"]').val('');
            } else {
                panel.find('input[type="date"]').each(function() {
                    const id = this.id;
                    if (id === 'startDate' || id === 'startDate1') {
                        $(this).val(defaultStartDate);
                    }
                    if (id === 'endDate' || id === 'endDate1') {
                        $(this).val(defaultEndDate);
                    }
                });
            }
        }

        function initDataTable(tableSelector, ajaxUrl, filterMap, pageLength) {
            return $(tableSelector).DataTable({
                serverSide: true,
                ordering: false,
                searching: false,
                deferRender: true,
                stateSave: false,
                pageLength: pageLength,
                lengthMenu: [
                    [10, 20, 30, 40, 50, 100, 250, 500],
                    [10, 20, 30, 40, 50, 100, 250, 500]
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json'
                },
                createdRow: function(row) {
                    const stateByStatus = {
                        0: 'waiting',
                        1: 'delivered',
                        2: 'cancelled',
                        3: 'processing',
                        4: 'processing',
                        5: 'processing',
                        9: 'processing',
                        13: 'fraud',
                        14: 'waiting',
                        15: 'processing'
                    };
                    const code = $(row).find('[data-order-status]').first().attr('data-order-status');
                    if (code !== undefined && stateByStatus[code]) {
                        $(row).addClass('order-state-' + stateByStatus[code]);
                    }
                },
                ajax: function(d, callback, settings) {
                    const tableState = settings._ordersTableState || (settings._ordersTableState = {
                        requestInFlight: false
                    });

                    tableState.requestInFlight = true;

                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: Object.assign({}, d, collectFilters(filterMap)),
                        success: function(json) {
                            callback({
                                draw: Number(json && json.draw) || Number(d.draw) || 0,
                                recordsTotal: Number(json && json.recordsTotal) || 0,
                                recordsFiltered: Number(json && json.recordsFiltered) || 0,
                                data: Array.isArray(json && json.data) ? json.data : []
                            });
                        },
                        error: function(xhr, textStatus) {
                            if (textStatus !== 'abort') {
                                console.error('DataTable error:', xhr);
                                toastr.error('Sipariş verileri alınamadı.');
                            }

                            callback({
                                draw: Number(d.draw) || 0,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                data: []
                            });
                        },
                        complete: function() {
                            tableState.requestInFlight = false;
                        }
                    });
                },
                columns: Array.from({
                    length: 13
                }, (_, i) => ({
                    data: i
                }))
            });
        }

        function statusText(orderData) {
            if (orderData && orderData.order_status_name) {
                return orderData.order_status_name;
            }
            const numericStatus = Number(orderData && orderData.order_status);
            return statusMap[numericStatus] || 'Bilinmiyor';
        }

        function fallbackCopy(text) {
            const tempInput = document.createElement('input');
            tempInput.value = text;
            tempInput.style.position = 'fixed';
            tempInput.style.left = '-9999px';
            document.body.appendChild(tempInput);
            tempInput.select();
            tempInput.setSelectionRange(0, text.length);
            try {
                document.execCommand('copy');
                toastr.success('Kopyalandı: ' + text);
            } catch (err) {
                toastr.error('Kopyalama başarısız!');
                console.error(err);
            }
            document.body.removeChild(tempInput);
        }

        function buildProviderHtml(data) {
            if (!Array.isArray(data) || data.length === 0) {
                return '<div class="text-muted">Uygun alternatif tedarikçi bulunamadı.</div>';
            }

            return data.map(function(item) {
                const providerId = Number(item.provider_product_id) || 0;
                const providerName = escapeHtml(item.provider_product_product_name || '-');
                const supplierName = escapeHtml(item.user_name || '-');
                const categoryName = escapeHtml(item.provider_product_category_name || '-');
                const cost = escapeHtml(item.provider_product_product_cost || '0');
                const currency = escapeHtml(item.provider_product_product_cost_currency || '');

                return '<div class="provider-item" data-id="' + providerId + '">' +
                    '<div class="fw-bold">' + providerName + '</div>' +
                    '<div class="text-muted small">' + supplierName + ' · ' + categoryName + '</div>' +
                    '<div class="mt-2 badge badge-light-primary">' + cost + ' ' + currency + '</div>' +
                    '</div>';
            }).join('');
        }

        function updateStockAlarmIndicator(alertItems) {
            const count = Number(alertItems) || 0;
            const countText = count > 99 ? '99+' : String(count);
            const fab = $('#stockAlertFab');
            const fabIcon = $('#stockAlertFabIcon');
            const countBadge = $('#stockAlertCount');

            fab.attr('title', count > 0 ? count + ' kritik havuz' : 'Stoklar uygun');

            if (count > 0) {
                countBadge.text(countText).removeClass('d-none badge-success').addClass('badge-danger');
                fab.removeClass('btn-light-success btn-success is-ok').addClass('btn-danger is-critical');
                fabIcon.removeClass('fa-circle-check').addClass('fa-triangle-exclamation');
                $('#stockAlertSummary').text(count + ' kritik havuz tespit edildi.');
                return;
            }

            countBadge.text('0').removeClass('badge-danger').addClass('badge-success d-none');
            fab.removeClass('btn-danger is-critical').addClass('btn-success is-ok');
            fabIcon.removeClass('fa-triangle-exclamation').addClass('fa-circle-check');
            $('#stockAlertSummary').text('Şu anda kritik stok açığı görünmüyor.');
        }

        function loadStokFails() {
            $.ajax({
                url: '<?= site_url('orders/stokFails') ?>',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (!Array.isArray(res) || res.length === 0) {
                        $('.stok-fails').html('<div class="col-12 text-muted fs-7">Bekleyen siparişe göre kritik stok açığı bulunmuyor.</div>');
                        updateStockAlarmIndicator(0);
                        return;
                    }

                    updateStockAlarmIndicator(res.length);

                    const html = res.map(function(item) {
                        const image = escapeHtml(item.product_image || '');
                        const poolTitle = escapeHtml(item.epin_pool_title || item.product_name || '-');
                        const productSubtitle = escapeHtml(item.product_subtitle || '');
                        const poolId = Number(item.epin_pool_id) || 0;
                        const failCount = Number(item.fail_count) || 0;
                        const waitingOrderQuantity = Number(item.waiting_order_quantity) || 0;
                        const availableStock = Number(item.available_stock) || 0;
                        const stockUrl = '<?= base_url('epin/addStock/') ?>' + poolId;
                        const mediaHtml = image
                            ? '<img src="' + image + '" alt="' + poolTitle + '">'
                            : '<div class="stock-fail-media">EP</div>';

                        return '<div class="col-12">' +
                            '<div class="stock-fail-item">' +
                            mediaHtml +
                            '<div class="d-flex flex-column">' +
                            '<a href="' + stockUrl + '" class="stock-fail-title fw-bold fs-7 text-truncate" target="_blank" rel="noopener">' + poolTitle + '</a>' +
                            (productSubtitle ? '<span class="stock-fail-subtitle fs-8 text-truncate" title="' + productSubtitle + '">' + productSubtitle + '</span>' : '') +
                            '<span class="stock-fail-metrics fs-8">Eksik: ' + failCount + ' | Bekleyen: ' + waitingOrderQuantity + ' | Stok: ' + availableStock + '</span>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                    }).join('');

                    $('.stok-fails').html(html);
                    // ana dizin assets klasöründeki new-order-2.mp3 dosyasını çal
                    if (res.length > 0) {
                        const audio = new Audio('<?= topup_url('assets/new-order-2.mp3') ?>'); // new-order-2.mp3
                        audio.play().catch(function(error) {
                            console.error('Ses çalınamadı:', error);
                        });
                    }
                },
                error: function() {
                    $('.stok-fails').html('<div class="col-12 text-danger fs-7">Stok alarm verileri alınamadı.</div>');
                    $('#stockAlertSummary').text('Stok alarm verileri alınamadı.');
                    $('#stockAlertFab').removeClass('btn-success btn-light-success is-ok').addClass('btn-danger is-critical');
                    $('#stockAlertFabIcon').removeClass('fa-circle-check').addClass('fa-triangle-exclamation');
                    $('#stockAlertCount').text('!').removeClass('d-none badge-success').addClass('badge-danger');
                }
            });
        }

        window.changeStatus = function(orderID) {
            $('#order-id').val(orderID);
            $('#orderStatus').val('').trigger('change');
            $('#orderStatusChangeModal').modal('show');
        };

        window.approveFraudOrder = function(orderID) {
            $('#fraudOrderId').val(orderID);
            $('#fraudModalTitle').text('Fraud Durumu · Sipariş #' + orderID);
            $('#fraudDecision').prop('checked', true).trigger('change');
            $('#fraudNote').val('');
            $('#fraudModal').modal('show');
        };

        $(document).on('change', '#fraudDecision', function() {
            var on = this.checked;
            $('#fraudSubmit').text(on ? 'Onayla' : 'Reddet').toggleClass('is-approve', on);
            $('#fraudHint').text(on ? 'Onaylanırsa sipariş işleme alınabilir hale gelir.' : 'Reddetmek için butonu pasifte bırakın; sipariş iptal edilir ve iade yapılır.');
        });

        $('#fraudSubmit').on('click', function() {
            var note = $('#fraudNote').val().trim();
            if (!note) { toastr.error('Açıklama zorunludur.'); return; }
            var btn = $(this).prop('disabled', true);
            $.post('<?= site_url('orders/approveFraud') ?>', {
                order_id: $('#fraudOrderId').val(),
                decision: $('#fraudDecision').is(':checked') ? 'approve' : 'reject',
                note: note
            }, function(response) {
                if (response && response.status === 'success') {
                    toastr.success(response.message);
                    $('#fraudModal').modal('hide');
                    reloadTables(true);
                } else {
                    toastr.error((response && response.message) || 'İşlem başarısız oldu.');
                }
            }, 'json').fail(function(xhr) {
                toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'İşlem başarısız oldu.');
            }).always(function() { btn.prop('disabled', false); });
        });

        window.openOrderCancelModal = function(orderID) {
            $('#cancelOrderId').val(orderID);
            $('#cancelReasonId').val('');
            $('#cancelManualReason').val('');
            $('#orderCancelModal').modal('show');
        };

        $('#confirmOrderCancel').on('click', function() {
            const reasonId = $('#cancelReasonId').val();
            const manualReason = $('#cancelManualReason').val().trim();
            if (!reasonId && !manualReason) {
                toastr.error('İptal sebebi zorunludur.');
                return;
            }
            const button = $(this);
            button.prop('disabled', true);
            $.post('<?= site_url('orders/cancelOrderAndRefund') ?>', {
                order_id: $('#cancelOrderId').val(),
                reason_id: reasonId,
                manual_reason: manualReason
            }, function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                    $('#orderCancelModal').modal('hide');
                    reloadTables(true);
                } else {
                    toastr.error(response.message || 'İptal işlemi başarısız oldu.');
                }
            }, 'json').fail(function(xhr) {
                toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'İptal işlemi başarısız oldu.');
            }).always(function() {
                button.prop('disabled', false);
            });
        });

        window.deliverOrder = function(orderID) {
            currentOrderID = orderID;
            $.post('<?= site_url('orders/orderDeliveryAlternatives/') ?>' + orderID, function(response) {
                if (!response || response.status !== 'success') {
                    toastr.error(response && response.message ? response.message : 'Alternatif tedarikçi bilgisi alınamadı.');
                    return;
                }

                $('#providerList').html(buildProviderHtml(response.data));
                $('#providerModal').modal('show');
            }, 'json').fail(function() {
                toastr.error('Alternatif tedarikçi bilgisi alınamadı.');
            });
        };

        window.orderAdmin = function(orderID, adminID) {
            $.post('<?= site_url('orders/orderAdmin/') ?>' + orderID + '/' + adminID, function(response) {
                if (response && response.status === 'success') {
                    toastr.success(response.message || 'İşlem başarılı.');
                    reloadTables(true);
                    if ($('#orderDetailModal').hasClass('show')) {
                        window.orderDetail(orderID);
                    }
                } else {
                    toastr.error(response && response.message ? response.message : 'İşlem yapılamadı.');
                }
            }, 'json').fail(function() {
                toastr.error('İşlem yapılamadı.');
            });
        };

        window.copy = function(value) {
            const text = String(value ?? '').trim();
            if (!text) {
                return;
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    toastr.success('Kopyalandı: ' + text);
                }).catch(function() {
                    fallbackCopy(text);
                });
                return;
            }

            fallbackCopy(text);
        };

        window.orderDetail = function(orderID) {
            $.ajax({
                url: '<?= site_url('orders/orderDetail/') ?>' + orderID,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (!data || data.status === 'error') {
                        toastr.error(data && data.message ? data.message : 'Sipariş detayı alınamadı.');
                        return;
                    }

                    const statusLabel = escapeHtml(statusText(data));
                    const paymentInfo = data.order_payment_info ? escapeHtml(JSON.stringify(data.order_payment_info, null, 2)) : 'Yok';
                    const requireData = data.order_require_data ? escapeHtml(JSON.stringify(data.order_require_data, null, 2)) : '-';
                    const voucherText = data.order_voucher ? escapeHtml(data.order_voucher) : '-';

                    const odValue = function(value, fallback) {
                        return (value === null || value === undefined || value === '') ? (fallback === undefined ? '-' : fallback) : value;
                    };
                    const odIcon = function(name) {
                        return '<span class="od-ico"><i class="fa-solid fa-' + name + '"></i></span>';
                    };
                    const odRow = function(label, value, icon) {
                        return '<div class="od-row"><span class="od-key">' + odIcon(icon) + escapeHtml(label) + '</span><strong>' + escapeHtml(odValue(value)) + '</strong></div>';
                    };
                    const statusCode = Number(data.order_status);
                    const statusTag = statusCode === 1 ? 'ok' : ([2, 5, 12].indexOf(statusCode) !== -1 ? 'no' : 'wait');
                    const requireKeyLabels = {
                        user_id: 'UserID',
                        server_id: 'Server',
                        zone_id: 'Zone'
                    };
                    const requireObject = (data.order_require_data && typeof data.order_require_data === 'object') ? data.order_require_data : null;
                    const playerTiles = requireObject ? Object.keys(requireObject).map(function(key) {
                        const raw = requireObject[key];
                        const text = (raw !== null && typeof raw === 'object') ? JSON.stringify(raw) : String(odValue(raw));
                        return '<div class="od-tile"><small>' + escapeHtml(requireKeyLabels[key] || key) + '</small><strong>' + escapeHtml(text) + '</strong>' +
                            '<button type="button" class="od-copy" data-copy="' + escapeHtml(text) + '" title="Kopyala"><i class="fa-solid fa-copy"></i></button></div>';
                    }).join('') : '';
                    const imageSrc = escapeHtml((function(p) { p = String(p || '').trim(); return !p || /^https?:\/\//i.test(p) ? p : '<?= rtrim((string)getenv('CDN_URL'), '/') ?>/' + p.replace(/^\/+/, ''); })(data.product_image));
                    const orderId = Number(data.order_id);
                    const hasEpin = !!data.order_voucher;
                    const isEpinProduct = hasEpin || Number(data.product_category_id) === 1;
                    const channel = Number(data.order_site_id) > 0 || data.reseller_name ? 'API' : 'Web';
                    const noteByStatus = {
                        ok: ['Sipariş tamamlandı', isEpinProduct ? 'E-Pin kodu müşteriye teslim edildi.' : 'Yükleme tedarikçi tarafından tamamlandı.'],
                        no: ['Sipariş tamamlanamadı', 'Sipariş iptal edildi veya tedarikçi tarafından reddedildi.'],
                        wait: ['Sipariş işlemde', statusCode === 13 ? 'Fraud kontrolü bekleniyor.' : 'Sipariş henüz tamamlanmadı; tedarikçi yanıtı bekleniyor.']
                    }[statusTag];
                    const copyBtn = function(v) { return '<button type="button" class="od-copy" data-copy="' + escapeHtml(v) + '" title="Kopyala"><i class="fa-solid fa-copy"></i></button>'; };

                    const paneDetail = '<section class="od-product">' +
                        (imageSrc ? '<img src="' + imageSrc + '" alt="">' : '') +
                        '<div class="od-product-main"><strong>' + escapeHtml(odValue(data.product_name)) + '</strong>' +
                        '<small>' + escapeHtml(odValue(data.category_title)) + ' · ' + (isEpinProduct ? 'E-Pin kod teslimatı' : 'UID / Server ID top-up') + ' · ' + escapeHtml(odValue(data.order_quantity)) + ' adet</small></div>' +
                        '<span class="od-status ' + statusTag + '">' + statusLabel + '</span>' + (statusCode === 13 ? '<span class="od-status wait" style="margin-left:6px">FRAUD</span>' : '') + '</section>' +
                        (playerTiles ? '<h4 class="od-heading">' + odIcon('gamepad') + 'Oyuncu Bilgileri</h4><div class="od-player">' + playerTiles + '</div>' : '') +
                        '<h4 class="od-heading">' + odIcon('receipt') + 'Sipariş Bilgileri</h4><div class="od-fields">' +
                        odRow('Sipariş No', '#' + data.order_id, 'hashtag') +
                        odRow('Sipariş Durumu', statusText(data), 'circle-check') +
                        odRow('Sipariş Kanalı', channel, 'globe') +
                        odRow('Sipariş Tarihi', data.order_created_at, 'calendar-days') +
                        odRow('Ürün ID', data.order_product_id, 'box') +
                        odRow('Miktar', data.order_quantity, 'cubes') +
                        odRow('Maliyet', odValue(data.order_cost, '0') + ' ' + odValue(data.order_cost_currency, ''), 'wallet') +
                        odRow('Maliyet TL', /^[\d.,]+$/.test(String(data.order_cost_tl || '')) ? data.order_cost_tl + ' TL' : data.order_cost_tl, 'wallet') +
                        odRow('Kupon', data.coupon_code, 'ticket') +
                        odRow('Kupon İndirimi', Number(data.coupon_discount || 0).toFixed(2) + ' TL', 'tag') +
                        '</div>' +
                        '<div class="od-note ' + statusTag + '"><strong>' + escapeHtml(noteByStatus[0]) + '</strong><p>' + escapeHtml(noteByStatus[1]) + '</p></div>' +
                        '<h4 class="od-heading">' + odIcon('user') + 'Müşteri İletişim Bilgileri</h4><div class="od-fields">' +
                        odRow('Müşteri', data.customer_name || data.reseller_name, 'user') +
                        odRow('E-posta', data.customer_email, 'envelope') +
                        odRow('Telefon', data.customer_phone, 'phone') +
                        odRow('Sipariş IP Adresi', data.order_user_ip, 'globe') +
                        '</div>' +
                        ((data.islemeAl || data.pinTeslim || statusCode !== 1 && statusCode !== 2) ? '<div class="od-actions">' + (statusCode !== 1 && statusCode !== 2 ? '<button onClick="changeStatus(' + Number(orderID) + ')" class="btn btn-success m-3">Durum Değiştir</button>' : '') + (data.islemeAl || '') + (data.pinTeslim || '') + '</div>' : '');

                    const paneContracts = '<h4 class="od-heading">' + odIcon('file-contract') + 'Sözleşmeler</h4>' +
                        '<a class="od-doc" target="_blank" rel="noopener" href="<?= base_url('orders/orderContract/') ?>' + orderId + '/distance-sales-agreement"><span>Mesafeli Satış Sözleşmesi</span><em>Görüntüle</em></a>' +
                        '<a class="od-doc" target="_blank" rel="noopener" href="<?= base_url('orders/orderContract/') ?>' + orderId + '/refund-policy"><span>İade Politikası</span><em>Görüntüle</em></a>';

                    const paneInvoices = '<h4 class="od-heading">' + odIcon('receipt') + 'Faturalar</h4>' +
                        '<a class="od-doc" target="_blank" rel="noopener" href="<?= base_url('orders/orderInvoice/') ?>' + orderId + '"><span>#' + orderId + ' siparişine ait fatura</span><em>Aç</em></a>';

                    const paneApi = '<h4 class="od-heading">' + odIcon('truck-fast') + 'Tedarik ve API İşlem Geçmişi</h4><div class="od-fields">' +
                        odRow('Kanal', channel, 'globe') +
                        odRow('Tedarikçi', data.supplier_name, 'building') +
                        odRow('Tedarikçi Sipariş No', data.order_supplier_order_id, 'hashtag') +
                        odRow('Reseller Sipariş No', data.order_reseller_order_id, 'hashtag') +
                        odRow('Reseller Detay No', data.order_reseller_order_detail_id, 'hashtag') +
                        odRow('Tedarikçi Okuma', data.order_readed_at, 'clock') +
                        odRow('Tedarikçi Dönüş', data.order_answered_at, 'clock') +
                        odRow('Teslim', data.order_sended_at, 'circle-check') +
                        '</div>' +
                        '<details class="od-raw"><summary>Ödeme / kart bilgisi (ham veri)</summary><pre>' + paymentInfo + '</pre></details>' +
                        '<details class="od-raw"><summary>Sipariş verisi (ham)</summary><pre>' + requireData + '</pre></details>';

                    const paneEpin = '<h4 class="od-heading">' + odIcon('key') + 'E-Pin Teslimat Bilgisi</h4>' +
                        '<div class="od-tile od-epin-tile"><small>E-Pin Kodu</small><strong>' + (hasEpin ? voucherText : 'Henüz teslim edilmedi') + '</strong>' + (hasEpin ? copyBtn(data.order_voucher) : '') + '</div>';

                    const modalContent = '<div class="od-tabs" role="tablist">' +
                        '<button type="button" class="active" data-od-tab="detail">' + odIcon('receipt') + ' Sipariş Detayı</button>' +
                        '<button type="button" data-od-tab="contracts">Sözleşmeler</button>' +
                        '<button type="button" data-od-tab="invoices">Faturalar</button>' +
                        '<button type="button" data-od-tab="api">API Geçmişi</button>' +
                        (isEpinProduct ? '<button type="button" data-od-tab="epin">E-Pin</button>' : '') +
                        '</div>' +
                        '<section class="od-pane active" data-od-pane="detail">' + paneDetail + '</section>' +
                        '<section class="od-pane" data-od-pane="contracts">' + paneContracts + '</section>' +
                        '<section class="od-pane" data-od-pane="invoices">' + paneInvoices + '</section>' +
                        '<section class="od-pane" data-od-pane="api">' + paneApi + '</section>' +
                        (isEpinProduct ? '<section class="od-pane" data-od-pane="epin">' + paneEpin + '</section>' : '');

                    $('#orderDetailModalLabel').text('#' + odValue(data.order_id, '') + ' · Sipariş Detayı' + (data.order_created_at ? '  ·  ' + data.order_created_at : ''));
                    $('#orderDetailContent').html(modalContent);
                    $('#orderDetailModal').modal('show');
                },
                error: function() {
                    toastr.error('Sipariş detayı alınamadı.');
                }
            });
        };

        $(document).ready(function() {
            $.fn.dataTable.ext.errMode = 'none';

            $(document).on('click', '.od-copy', function() {
                window.copy($(this).attr('data-copy'));
            });

            $(document).on('click', '#orderDetailContent [data-od-tab]', function() {
                var key = this.getAttribute('data-od-tab');
                $('#orderDetailContent [data-od-tab]').removeClass('active');
                $(this).addClass('active');
                $('#orderDetailContent [data-od-pane]').removeClass('active').filter('[data-od-pane="' + key + '"]').addClass('active');
                $('#orderDetailContent').closest('.modal-body').scrollTop(0);
            });

            $('.js-filter-select').select2({
                width: '100%'
            });

            $('#orderStatus').select2({
                width: '100%'
            });

            tableOps = initDataTable('#orderTable', '<?= site_url('orders/getOrders/2') ?>', filtersOps, 10);
            tableAll = initDataTable('#orderTable2', '<?= site_url('orders/getOrders/3') ?>', filtersAll, 20);

            $('#filterBtn').on('click', function() {
                tableAll.ajax.reload();
            });

            $('#filterBtn1').on('click', function() {
                tableOps.ajax.reload();
            });

            $('#filterPanelAll').on('keypress', 'input', function(e) {
                if (e.which === 13) {
                    tableAll.ajax.reload();
                }
            });

            $('#filterPanelOps').on('keypress', 'input', function(e) {
                if (e.which === 13) {
                    tableOps.ajax.reload();
                }
            });

            $('#refreshrBtn').on('click', function() {
                resetFilterPanel('#filterPanelAll', false);
                tableAll.ajax.reload();
            });

            $('#refreshrBtn1').on('click', function() {
                resetFilterPanel('#filterPanelOps', false);
                tableOps.ajax.reload();
            });

            $('#allBtn').on('click', function() {
                resetFilterPanel('#filterPanelAll', true);
                tableAll.ajax.reload();
            });

            $('#todayBtn').on('click', function() {
                resetFilterPanel('#filterPanelAll', false);
                $('#startDate').val(defaultEndDate);
                $('#endDate').val(defaultEndDate);
                tableAll.ajax.reload();
            });

            $('#changeStatusBtn').on('click', function() {
                const orderStatus = $('#orderStatus').val();
                const orderID = $('#order-id').val();

                if (!orderStatus) {
                    toastr.error('Lütfen geçerli bir durum seçin.');
                    return;
                }

                const button = $(this);
                button.prop('disabled', true).text('Güncelleniyor...');

                $.ajax({
                    url: '<?= site_url('orders/changeStatus/') ?>' + orderID,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        orderStatus: orderStatus
                    },
                    success: function(response) {
                        if (response && response.status === 'success') {
                            toastr.success(response.message || 'Durum güncellendi.');
                            $('#orderStatusChangeModal').modal('hide');
                            reloadTables(true);
                            if ($('#orderDetailModal').hasClass('show')) {
                                window.orderDetail(orderID);
                            }
                        } else {
                            toastr.error(response && response.message ? response.message : 'Durum güncellenemedi.');
                        }
                    },
                    error: function() {
                        toastr.error('Sipariş durumu değiştirilemedi.');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Durumu Güncelle');
                    }
                });
            });

            $(document).on('click', '.provider-item', function() {
                const providerID = Number($(this).data('id')) || 0;
                if (!currentOrderID || !providerID) {
                    toastr.error('Geçersiz seçim.');
                    return;
                }

                if (!window.confirm('Seçili tedarikçi ile sipariş devam ettirilsin mi?')) {
                    return;
                }

                $.ajax({
                    url: '<?= site_url('orders/orderProviderChange') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        orderID: currentOrderID,
                        newProviderProductID: providerID
                    },
                    success: function(res) {
                        if (res && res.status === 'success') {
                            toastr.success(res.message || 'Tedarikçi değiştirildi.');
                            $('#providerModal').modal('hide');
                            reloadTables(true);
                        } else {
                            toastr.error(res && res.message ? res.message : 'Provider değiştirilemedi.');
                        }
                    },
                    error: function() {
                        toastr.error('Provider değiştirilemedi.');
                    }
                });
            });

            $('#stop-start-btn').on('click', function() {
                refreshPausedByUser = !refreshPausedByUser;
                startTimers();
            });

            $('#refreshInterval').on('change', function() {
                startTimers();
            });

            $('#refreshStockAlarmBtn').on('click', function() {
                loadStokFails();
            });

            document.addEventListener('visibilitychange', function() {
                startTimers();
            });

            const stockAlarmDrawer = document.getElementById('stockAlarmDrawer');
            if (stockAlarmDrawer) {
                stockAlarmDrawer.addEventListener('shown.bs.offcanvas', function() {
                    $('#stockAlertFab').addClass('is-drawer-open');
                });

                stockAlarmDrawer.addEventListener('hidden.bs.offcanvas', function() {
                    $('#stockAlertFab').removeClass('is-drawer-open');
                });
            }

            loadStokFails();
            startTimers();
        });
    })(jQuery);
</script>
