<style>
  :root {
    color-scheme: dark;
    --bg: #11151d;
    --surface: #1e222d;
    --surface-2: #232632;
    --surface-3: #272b38;
    --line: #2d3343;
    --line-soft: #242a38;
    --text: #f2f2f2;
    --muted: #a3a9b5;
    --dim: #858585;
    --accent: #ffb300;
    --accent-deep: #ff7300;
    --gold: var(--accent);
    --accent-ink: #11151d;
    --green: #27c997;
    --red: #ff6552;
    --radius: 14px;
    --max: 1440px;
  }

  * {
    box-sizing: border-box
  }

  html {
    scroll-behavior: smooth;
    background: var(--bg)
  }

  body {
    min-width: 320px;
    margin: 0;
    background: var(--bg);
    color: var(--text);
  }

  button,
  input,
  select,
  textarea {
    font: inherit
  }

  button {
    color: inherit
  }

  button:focus-visible,
  a:focus-visible {
    outline: 2px solid #ffdc75;
    outline-offset: 3px
  }

  input:focus-visible,
  select:focus-visible,
  textarea:focus-visible {
    outline: none !important;
    border-color: var(--accent, #fab316) !important;
    box-shadow: 0 0 0 3px rgba(255, 194, 26, 0.12) !important;
  }

  [hidden] {
    display: none !important
  }

  .sr-only,
  .visually-hidden {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
  }

  .icon {
    display: block;
    width: 18px;
    height: 18px;
    flex: 0 0 auto;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round
  }

  .icon-fill {
    fill: currentColor;
    stroke: none
  }

  .app-header {
    position: sticky;
    top: 0;
    z-index: 30;
    border-bottom: 1px solid var(--line-soft);
    background: #0e1118;
    backdrop-filter: blur(16px)
  }

  .header-inner {
    display: grid;
    grid-template-columns: auto auto minmax(180px, 1fr) auto auto;
    align-items: center;
    gap: 16px;
    width: min(calc(100% - 48px), var(--max));
    min-height: 70px;
    margin: auto
  }

  .brand {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #f7f8f9;
    text-decoration: none;
    font-size: 1.16rem;
    font-weight: 650;
    letter-spacing: -.055em;
    white-space: nowrap
  }

  .brand-mark {
    width: 28px;
    height: 31px
  }

  .category-button,
  .header-account,
  .locale-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 42px;
    padding: 0 13px;
    border: 1px solid var(--line);
    border-radius: 10px;
    background: var(--surface-2);
    color: #e7eaec;
    font-size: .76rem;
    font-weight: 760;
    white-space: nowrap;
    cursor: pointer;
    transition: background .16s, border-color .16s, transform .16s
  }

  .category-button:hover,
  .locale-button:hover {
    border-color: #59616a;
    background: #202634
  }

  .category-button .icon {
    color: var(--accent)
  }

  .header-account {
    border-color: transparent;
    background: linear-gradient(110deg, var(--accent), var(--accent-deep));
    color: var(--accent-ink);
    font-weight: 650;
    box-shadow: 0 5px 18px #ffad0019
  }

  .header-account:hover,
  .primary-button:hover {
    filter: brightness(1.04);
    transform: translateY(-1px)
  }

  .header-search {
    position: relative;
    width: 100%;
    max-width: 590px;
    justify-self: center
  }

  .header-search input {
    width: 100%;
    height: 43px;
    padding: 0 44px 0 14px;
    border: 1px solid var(--line);
    border-radius: 10px;
    background: #11151d;
    color: var(--text);
    font-size: .78rem
  }

  .header-search input::placeholder {
    color: #78838d
  }

  .header-search .icon {
    position: absolute;
    top: 13px;
    right: 14px;
    color: var(--accent)
  }

  .locale-button {
    min-width: 72px
  }

  .mobile-search {
    display: none
  }

  .workspace {
    display: grid;
    grid-template-columns: 246px minmax(0, 1fr);
    align-items: start;
    gap: 26px;
    width: min(calc(100% - 48px), var(--max));
    margin: 27px auto 56px
  }

  .sidebar {
    position: sticky;
    top: 92px;
    min-width: 0;
    padding: 9px;
    border: 1px solid var(--line);
    border-radius: 16px;
    background: linear-gradient(155deg, #151923, #11151d);
    box-shadow: 0 15px 38px #0003
  }

  .profile-user-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 11px;
    width: 100%;
    min-height: 72px;
    margin: 0 0 11px;
    padding: 10px;
    overflow: hidden;
    border: 1px solid #303645;
    border-radius: 12px;
    background: #10141c;
    color: inherit;
    text-align: left;
    cursor: pointer;
    transition: border-color .18s, background .18s
  }

  .profile-user-card:before {
    position: absolute;
    z-index: 0;
    top: 0;
    right: 0;
    bottom: 0;
    width: 88px;
    background: radial-gradient(ellipse at 75% 50%, #fab316, transparent 72%);
    content: "";
    opacity: 0;
    pointer-events: none
  }

  .profile-user-card:hover {
    border-color: #46515c;
    background: #131720
  }

  .profile-user-card.menu-hint:before {
    animation: menuHalo 2.55s ease-in-out 2 both
  }

  @keyframes menuHalo {

    0%,
    100% {
      opacity: 0
    }

    50% {
      opacity: .9
    }
  }

  .avatar {
    position: relative;
    z-index: 1;
    display: grid;
    place-items: center;
    flex: none;
    width: 43px;
    height: 43px;
    border: 1px solid #dfaa18;
    border-radius: 50%;
    background: var(--accent);
    color: #18150c;
    font-size: .91rem;
    font-weight: 900
  }

  .user-copy {
    position: relative;
    z-index: 1;
    min-width: 0
  }

  .user-copy strong {
    display: block;
    color: #f3f5f6;
    font-size: .73rem;
    font-weight: 800
  }

  .user-copy small {
    display: block;
    max-width: 143px;
    margin-top: 3px;
    overflow: hidden;
    color: #89949f;
    font-size: .66rem;
    line-height: 1.2;
    text-overflow: ellipsis
  }

  .profile-mobile-arrow {
    position: relative;
    z-index: 1;
    display: none;
    place-items: center;
    width: 34px;
    height: 34px;
    margin-left: auto;
    padding: 8px;
    border: 1px solid #363c4b;
    border-radius: 9px;
    background: #171c29;
    color: #bec7ce;
    transition: transform .2s, border-color .18s, background .18s, color .18s
  }

  .profile-user-card[aria-expanded="true"] .profile-mobile-arrow {
    transform: rotate(180deg);
    border-color: #66552b;
    background: #211f18;
    color: var(--accent)
  }

  .profile-user-card.menu-hint .profile-mobile-arrow {
    animation: menuButtonCue 2.55s ease-in-out 2 both
  }

  @keyframes menuButtonCue {

    0%,
    100% {
      box-shadow: 0 0 0 0 #fab316;
      border-color: #363c4b
    }

    50% {
      box-shadow: 0 0 14px 2px #fab316;
      border-color: #806a2e;
      background: #201e17;
      color: #ffcf3a
    }
  }

  .profile-menus-wrapper {
    display: flex;
    flex-direction: column
  }

  .profile-menu-group,
  .nav-group-title {
    margin: 18px 9px 7px;
    color: #77838e;
    font-size: .59rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase
  }

  .profile-menu,
  .account-nav {
    display: grid;
    gap: 3px
  }

  .nav-link {
    display: flex;
    align-items: center;
    gap: 11px;
    width: 100%;
    min-height: 41px;
    padding: 0 11px;
    border: 1px solid transparent;
    border-radius: 9px;
    background: transparent;
    color: #a2acb6;
    font-size: .74rem;
    font-weight: 670;
    text-align: left;
    cursor: pointer;
    transition: background .16s, border-color .16s, color .16s
  }

  .nav-link .icon {
    width: 16px;
    height: 16px;
    color: #808c97
  }

  .nav-link:hover {
    background: #ffffff07;
    color: #f5f6f7
  }

  .nav-link.active {
    border-color: #4b4129;
    background: #1e1c16;
    color: #fff;
    box-shadow: inset 3px 0 var(--accent)
  }

  .nav-link.active .icon {
    color: var(--accent)
  }

  .nav-link.logout {
    margin-top: 4px;
    color: #ed806e
  }

  .nav-link.logout .icon {
    color: #ed806e
  }

  .content,
  .view {
    display: grid;
    min-width: 0;
    gap: 18px
  }

  .view[hidden],
  .settings-panel[hidden],
  .withdrawal-panel[hidden] {
    display: none !important
  }

  .page-hero {
    display: flex;
    align-items: center;
    gap: 14px;
    min-height: 78px;
    padding: 3px 2px 15px;
    border-bottom: 1px solid var(--line-soft)
  }

  .hero-icon {
    display: grid;
    place-items: center;
    flex: none;
    width: 43px;
    height: 43px;
    border: 1px solid #343b4a;
    border-radius: 12px;
    background: #171c27;
    color: var(--accent)
  }

  .hero-icon .icon {
    width: 20px;
    height: 20px
  }

  .hero-copy {
    min-width: 0
  }

  .hero-copy h1 {
    margin: 0;
    color: #f5f6f7;
    font-size: clamp(1.2rem, 1.8vw, 1.52rem);
    font-weight: 790;
    letter-spacing: -.04em;
    line-height: 1.2
  }

  .hero-copy p {
    margin: 5px 0 0;
    color: #8e9aa5;
    font-size: .74rem
  }

  .hero-action {
    margin-left: auto
  }

  .primary-button,
  .secondary-button,
  .icon-button,
  .filter-button,
  .tab-button,
  .quick-filter,
  .detail-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 40px;
    padding: 0 13px;
    border: 1px solid transparent;
    border-radius: 9px;
    font-size: .72rem;
    font-weight: 780;
    text-decoration: none;
    cursor: pointer;
    transition: background .16s, border-color .16s, color .16s, filter .16s, transform .16s
  }

  .primary-button {
    background: linear-gradient(110deg, var(--accent), var(--accent-deep));
    color: var(--accent-ink);
    font-weight: 650;
    box-shadow: 0 5px 16px #e9910018
  }

  .secondary-button,
  .detail-button {
    border-color: #353b4a;
    background: #151923;
    color: #e2e7ea
  }

  .secondary-button:hover,
  .detail-button:hover {
    border-color: #616d78;
    background: #1b202d
  }

  .icon-button {
    width: 38px;
    height: 38px;
    min-height: 38px;
    padding: 0;
    border-color: var(--line);
    background: #151923;
    color: #aeb8c2
  }

  .icon-button:hover {
    border-color: #5a6570;
    color: var(--accent)
  }

  .icon-button.danger {
    color: #f17b69
  }

  .icon-button.success {
    color: #4ad2aa
  }

  .panel,
  .subnav-shell,
  .conversation {
    min-width: 0;
    border: 1px solid var(--line);
    border-radius: var(--radius);
    background: linear-gradient(145deg, #141822, #11151d);
    box-shadow: 0 10px 30px #0000001c
  }

  .panel {
    padding: 18px
  }

  .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 13px
  }

  .panel-head h2,
  .section-title {
    margin: 0;
    color: #f0f2f4;
    font-size: .91rem;
    font-weight: 770;
    letter-spacing: -.02em
  }

  .panel-head p {
    margin: 4px 0 0;
    color: #929da8;
    font-size: .7rem
  }

  .panel-more {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border: 1px solid var(--line);
    border-radius: 10px;
    background: #151923;
    color: #abb5bf;
    font-size: 1.05rem;
    cursor: pointer
  }

  .panel-more:hover {
    border-color: #66552b;
    color: var(--accent)
  }

  .stat-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 13px
  }

  .stat-card {
    position: relative;
    min-width: 0;
    min-height: 128px;
    padding: 16px;
    border: 1px solid var(--line);
    border-radius: 14px;
    background: #131721
  }

  .stat-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #9aa5af;
    font-size: .69rem;
    font-weight: 680
  }

  .stat-label .icon {
    width: 15px;
    height: 15px;
    color: var(--accent)
  }

  .stat-value {
    display: block;
    margin-top: 13px;
    overflow-wrap: anywhere;
    color: #f4f6f7;
    font-size: clamp(1.3rem, 2vw, 1.7rem);
    font-weight: 650;
    letter-spacing: -.05em
  }

  .stat-foot {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 8px;
    color: #7f8b96;
    font-size: .65rem
  }

  .stat-foot .icon {
    width: 13px;
    height: 13px;
    color: #d6a71f
  }

  .dashboard-tiles {
    display: grid;
    gap: 12px
  }

  .dashboard-primary,
  .dashboard-secondary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px
  }

  .dashboard-tile {
    position: relative;
    display: flex;
    min-width: 0;
    min-height: 130px;
    flex-direction: column;
    align-items: flex-start;
    gap: 9px;
    padding: 15px;
    border: 1px solid #303643;
    border-radius: 13px;
    background: linear-gradient(145deg, #171b25, #12161e);
    color: inherit;
    text-align: left;
    cursor: pointer;
    transition: border-color .18s, transform .18s, background .18s
  }

  .dashboard-tile:hover {
    transform: translateY(-2px);
    border-color: #65552b;
    background: linear-gradient(145deg, #1d212b, #141820)
  }

  .dashboard-tile:focus-visible {
    outline: 2px solid #d7b449;
    outline-offset: 3px
  }

  .dashboard-tile:active {
    transform: translateY(0)
  }

  .dashboard-tile-head {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    min-height: 22px;
    padding-right: 31px;
    color: #aab4bd;
    font-size: .68rem;
    font-weight: 730
  }

  .dashboard-tile-head .icon {
    width: 16px;
    height: 16px;
    color: #d5b34f
  }

  .dashboard-tile-value {
    max-width: 100%;
    overflow: hidden;
    color: #f1f3f4;
    font-size: 1.02rem;
    font-weight: 800;
    text-overflow: ellipsis;
    white-space: nowrap
  }

  .dashboard-tile-note {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: auto;
    color: #87939e;
    font-size: .63rem;
    line-height: 1.4
  }

  .dashboard-tile-note .icon {
    width: 13px;
    height: 13px;
    color: #9ba6b0
  }

  .dashboard-tile-arrow {
    position: absolute;
    top: 12px;
    right: 12px;
    display: grid;
    place-items: center;
    width: 27px;
    height: 27px;
    border: 1px solid #303645;
    border-radius: 8px;
    background: #131721;
    color: #9ba6b0;
    transition: border-color .18s, color .18s, background .18s
  }

  .dashboard-tile-arrow .icon {
    width: 14px;
    height: 14px;
    transform: rotate(-90deg)
  }

  .dashboard-tile:hover .dashboard-tile-arrow {
    border-color: #65552b;
    background: #201e18;
    color: #e2c356
  }

  .dashboard-tile-action {
    display: block;
    max-width: 100%;
    color: #e8ecef;
    font-size: .92rem;
    font-weight: 780;
    line-height: 1.35;
    letter-spacing: -.015em
  }

  .dashboard-tile.compact {
    min-height: 92px
  }

  .dashboard-tile.compact .dashboard-tile-note {
    max-width: calc(100% - 4px)
  }

  .ledger-panel-head {
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 13px;
    border-bottom: 1px solid var(--line-soft)
  }

  .ledger-heading {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0
  }

  .ledger-heading-icon {
    display: grid;
    place-items: center;
    width: 37px;
    height: 37px;
    flex: none;
    border: 1px solid #353b4a;
    border-radius: 10px;
    background: #181d29;
    color: #d5b34f
  }

  .ledger-heading-icon .icon {
    width: 17px;
    height: 17px
  }

  .ledger-heading-copy {
    min-width: 0
  }

  .ledger-heading-copy small {
    display: block;
    margin-bottom: 2px;
    color: #7f8b96;
    font-size: .54rem;
    font-weight: 800;
    letter-spacing: .09em;
    text-transform: uppercase
  }

  .ledger-heading-copy h2 {
    margin: 0;
    color: #eff1f3;
    font-size: .88rem;
    font-weight: 780;
    letter-spacing: -.02em
  }

  .ledger-heading-copy p {
    margin: 3px 0 0;
    color: #85919d;
    font-size: .64rem;
    line-height: 1.4
  }

  .ledger-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex: none;
    padding: 8px 10px;
    border: 1px solid #303645;
    border-radius: 8px;
    background: #141821;
    color: #c8d0d6;
    font-size: .63rem;
    font-weight: 740;
    cursor: pointer
  }

  .ledger-action:hover {
    border-color: #67562a;
    color: #f0d37c
  }

  .ledger-action .icon {
    width: 13px;
    height: 13px
  }

  .two-panels {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px
  }

  .table-wrap {
    min-width: 0;
    overflow-x: auto
  }

  table {
    width: 100%;
    border-collapse: collapse;
    text-align: left
  }

  thead th {
    padding: 10px 11px;
    border-bottom: 1px solid #303643;
    color: #7f8c97;
    font-size: .59rem;
    font-weight: 800;
    letter-spacing: .075em;
    text-transform: uppercase;
    white-space: nowrap
  }

  tbody td {
    padding: 12px 11px;
    border-bottom: 1px solid #262c3a;
    background: transparent;
    color: #d8dde1;
    font-size: .7rem;
    font-weight: 560;
    vertical-align: middle
  }

  tbody tr:last-child td {
    border-bottom: 0
  }

  tbody tr:hover td {
    background: #ffffff03
  }

  .row-id {
    color: #d6b85d;
    font-weight: 790
  }

  .product-cell,
  .order-image,
  .method-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0
  }

  .product-thumb,
  .method-icon {
    display: grid;
    place-items: center;
    flex: none;
    width: 38px;
    height: 38px;
    border: 1px solid #303644;
    border-radius: 10px;
    background: #1b202c;
    color: #b5a06b
  }

  .product-thumb .icon,
  .method-icon .icon {
    width: 18px;
    height: 18px
  }

  .product-thumb .order-art {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: inherit
  }

  .product-name {
    min-width: 0;
    overflow: hidden;
    color: #e8ebed;
    font-weight: 730;
    text-overflow: ellipsis;
    white-space: nowrap
  }

  .subtle {
    color: #84909c;
    font-size: .66rem
  }

  .quantity {
    display: inline-flex;
    padding: 4px 8px;
    border: 1px solid #303645;
    border-radius: 7px;
    background: #181d2a;
    color: #cbd2d7;
    font-size: .65rem;
    white-space: nowrap
  }

  .money {
    color: #f0f2f4;
    font-weight: 780;
    white-space: nowrap
  }

  .status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 24px;
    padding: 0 9px;
    border: 1px solid #5a4920;
    border-radius: 999px;
    background: #282316;
    color: #e6be54;
    font-size: .62rem;
    font-weight: 770;
    white-space: nowrap
  }

  .status.done {
    border-color: #215441;
    background: #14271f;
    color: #49d4a3
  }

  .status.refund {
    border-color: #63332c;
    background: #2a1917;
    color: #ff806e
  }

  .status.neutral {
    border-color: #343a47;
    background: #1c202a;
    color: #acb7c0
  }

  .status.processing {
    border-color: #303c59;
    background: #171d2a;
    color: #77c5ef
  }

  .status.payment-pending {
    border-color: #5a4920;
    background: #282316;
    color: #e6be54
  }

  .status-mark {
    display: inline-grid;
    place-items: center;
    flex: none;
    width: 13px;
    height: 13px;
    margin-right: 5px
  }

  .status-mark .icon {
    width: 13px;
    height: 13px
  }

  /* Bekleme/işlem durumları: dönen spinner yerine sabit nokta (bekleme hissi yaratmasın) */
  .status-mark.pending,
  .status-mark.processing {
    width: 8px;
    height: 8px;
    margin: 0 7px 0 2px;
    border: 0;
    border-radius: 50%;
    background: currentColor;
    animation: none
  }

  .amount-positive {
    color: var(--green);
    font-weight: 780;
    white-space: nowrap
  }

  .amount-negative {
    color: var(--red);
    font-weight: 780;
    white-space: nowrap
  }

  .data-table .product-cell {
    min-width: 0
  }

  .compact-table thead th {
    padding-block: 8px
  }

  .compact-table tbody td {
    padding-block: 10px
  }

  .subnav-shell {
    overflow: hidden
  }

  .profile-pagination-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 22px 0 10px;
    width: 100%;
  }

  .ps-pagination,
  .profile-pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
    align-items: center;
    justify-content: center;
  }

  .ps-pagination li,
  .profile-pagination li {
    margin: 0;
    list-style: none;
  }

  .ps-pagination li a,
  .ps-pagination li span,
  .ps-pagination li strong,
  .profile-pagination li a,
  .profile-pagination li span,
  .profile-pagination li strong {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    border-radius: 8px;
    background: var(--surface-2, #171c27);
    border: 1px solid var(--line, #292f3e);
    color: var(--text, #f3f5f6);
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.16s, border-color 0.16s, color 0.16s, transform 0.16s;
    user-select: none;
  }

  .ps-pagination li a:hover,
  .profile-pagination li a:hover {
    background: var(--surface-3, #1d2331);
    border-color: #59616a;
    color: #fff;
    transform: translateY(-1px);
  }

  .ps-pagination li a.active,
  .ps-pagination li strong,
  .profile-pagination li a.active,
  .profile-pagination li strong,
  .profile-pagination li.active span {
    background: linear-gradient(135deg, var(--accent, #fab316), var(--accent-deep, #ff9e00));
    color: var(--accent-ink, #17140c);
    border-color: var(--accent, #fab316);
    font-weight: 750;
    pointer-events: none;
    box-shadow: 0 2px 8px rgba(255, 194, 26, 0.25);
  }

  .ps-pagination li a[rel="prev"],
  .ps-pagination li a[rel="next"],
  .profile-pagination li a[rel="prev"],
  .profile-pagination li a[rel="next"] {
    font-size: 1rem;
    font-weight: 700;
  }

  .tab-list,
  .settings-tabs,
  .withdrawal-tabs {
    display: flex;
    align-items: stretch;
    gap: 4px;
    min-width: 0;
    overflow-x: auto;
    padding: 7px;
    border-bottom: 1px solid var(--line-soft);
    scrollbar-width: thin;
    scrollbar-color: #383e4d transparent
  }

  .tab-button {
    flex: 0 0 auto;
    min-height: 39px;
    padding: 0 13px;
    border: 1px solid transparent;
    border-radius: 9px;
    background: transparent;
    color: #929da7;
    white-space: nowrap
  }

  .tab-button .icon {
    width: 15px;
    height: 15px
  }

  .tab-button:hover {
    background: #ffffff06;
    color: #edf0f2
  }

  .tab-button.active {
    border-color: #514626;
    background: #211f18;
    color: #f7f4e9
  }

  .tab-button.active .icon {
    color: var(--accent)
  }

  .filter-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px 14px;
    border-bottom: 1px solid var(--line-soft)
  }

  .date-control {
    position: relative;
    display: flex;
    align-items: center;
    max-width: 320px;
    min-width: 0;
    flex: 0 1 280px;
    height: 38px;
  }

  .date-control input {
    width: 100%;
    height: 38px;
    min-height: 38px;
    padding: 0 75px 0 12px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: #0f131b;
    color: var(--text);
    font-size: .69rem;
    transition: border-color .18s ease, box-shadow .18s ease;
  }

  .date-control input:hover {
    border-color: rgba(255, 194, 26, 0.4);
  }

  .date-control input:focus,
  .date-control input:focus-visible {
    border-color: var(--accent, #fab316) !important;
    box-shadow: 0 0 0 3px rgba(255, 194, 26, 0.12) !important;
    outline: none !important;
  }

  .date-control input::placeholder {
    color: #78838e
  }

  .date-control .calendar {
    position: absolute;
    right: 42px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    color: #9ba6b0;
    pointer-events: none;
    z-index: 2;
  }

  .date-control .calendar .icon {
    width: 15px;
    height: 15px
  }

  .filter-submit {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: 0;
    border-radius: 6px;
    background: #222733;
    color: var(--accent);
    cursor: pointer;
    transition: background .18s, color .18s;
    z-index: 2;
  }

  .filter-submit:hover {
    background: #2e3546;
    color: #ffe082;
  }

  .filter-submit .icon {
    width: 15px;
    height: 15px;
  }

  .quick-filter {
    min-height: 38px;
    height: 38px;
    padding: 0 11px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: #121620;
    color: #9aa5af;
    font-size: .65rem;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .quick-filter:hover,
  .quick-filter.active {
    border-color: #544727;
    background: #211f18;
    color: #f2d37a
  }

  .filter-row .icon-button {
    min-height: 38px;
    height: 38px;
    width: 38px;
    flex: 0 0 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .search-control,
  .search-box {
    position: relative;
    display: inline-flex;
    align-items: center;
    flex: 1 1 230px;
    min-width: 190px;
    max-width: 320px;
  }

  .filter-row .search-control,
  .filter-row .search-box {
    margin-left: 0;
  }

  .search-control .icon,
  .search-box .icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 15px;
    height: 15px;
    color: #72808e;
    pointer-events: none;
    transition: color .2s ease;
  }

  .search-control input,
  .search-box input {
    width: 100%;
    height: 36px;
    min-height: 36px;
    padding: 0 12px 0 36px;
    border: 1px solid #292f3e;
    border-radius: 9px;
    background: #11151f;
    color: var(--text, #f3f5f6);
    font-size: .74rem;
    font-weight: 500;
    outline: none;
    transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
  }

  .search-control input::placeholder,
  .search-box input::placeholder {
    color: #657280;
    font-size: .73rem;
  }

  /* İnsancıl hover & focus */
  .search-control input:hover,
  .search-box input:hover {
    border-color: #3e4f60;
    background-color: #141925;
  }

  .search-control input:focus,
  .search-control input:focus-visible,
  .search-box input:focus,
  .search-box input:focus-visible {
    outline: none !important;
    border-color: var(--accent, #fab316) !important;
    background-color: #141a26 !important;
    box-shadow: 0 0 0 3px rgba(255, 194, 26, 0.12) !important;
  }

  .search-control:focus-within .icon,
  .search-box:focus-within .icon {
    color: var(--accent, #fab316);
  }

  .panel-body {
    min-width: 0;
    padding: 0 14px 10px
  }

  .empty-state {
    display: grid;
    justify-items: center;
    align-content: center;
    min-height: 255px;
    padding: 28px 20px;
    border-radius: 12px;
    background: #10141c;
    text-align: center
  }

  .empty-icon {
    display: grid;
    place-items: center;
    width: 53px;
    height: 53px;
    margin-bottom: 12px;
    border: 1px solid #303645;
    border-radius: 15px;
    background: #171c27;
    color: #87939f
  }

  .empty-icon .icon {
    width: 24px;
    height: 24px
  }

  .empty-state h3 {
    margin: 0;
    color: #edf0f2;
    font-size: .9rem;
    font-weight: 780
  }

  .empty-state p {
    max-width: 420px;
    margin: 7px 0 16px;
    color: #8a96a2;
    font-size: .7rem
  }

  .info-banner {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin: 14px 0;
    padding: 12px 13px;
    border: 1px solid #403b2b;
    border-radius: 10px;
    background: #1c1a15;
    color: #d6c28a;
    font-size: .7rem
  }

  .info-banner .icon {
    width: 16px;
    height: 16px;
    margin-top: 1px;
    color: var(--accent)
  }

  .divider {
    height: 1px;
    margin: 18px 0;
    border: 0;
    background: var(--line-soft)
  }

  .form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px
  }

  .field {
    display: grid;
    gap: 6px;
    min-width: 0
  }

  .field.full {
    grid-column: 1/-1
  }

  .field label {
    color: #9ba6b0;
    font-size: .67rem;
    font-weight: 700
  }

  .field label .subtle {
    margin-left: 4px;
    font-size: .6rem
  }

  .field input,
  .field select,
  .field textarea {
    width: 100%;
    min-height: 43px;
    padding: 10px 12px;
    border: 1px solid #303645;
    border-radius: 8px;
    background: #0e121a;
    color: #edf0f2;
    font-size: 16px;
    transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease;
  }

  .field input:hover,
  .field select:hover,
  .field textarea:hover {
    border-color: #445464;
    background-color: #111620;
  }

  .field input:focus,
  .field select:focus,
  .field textarea:focus {
    outline: none !important;
    border-color: var(--accent, #fab316);
    box-shadow: 0 0 0 3px rgba(255, 194, 26, 0.12);
  }

  .field input::placeholder,
  .field textarea::placeholder {
    color: #687581
  }

  .field textarea {
    min-height: 112px;
    resize: vertical
  }

  .field input[type=file] {
    padding: 8px
  }

  .field input[type=radio],
  input[type=radio] {
    width: 16px;
    height: 16px;
    min-height: 0;
    accent-color: var(--accent)
  }

  .field input[type="date"],
  input[type="date"] {
    position: relative;
    color-scheme: dark;
    padding-right: 10px;
  }

  .field input[type="date"]::-webkit-calendar-picker-indicator,
  input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(0.8) brightness(1.2);
    padding: 4px;
    margin-right: 2px;
    border-radius: 4px;
    transition: opacity .15s, background-color .15s;
  }

  .field input[type="date"]::-webkit-calendar-picker-indicator:hover,
  input[type="date"]::-webkit-calendar-picker-indicator:hover {
    background-color: rgba(255, 255, 255, 0.12);
  }

  .form-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 15px
  }

  .order-detail-footer {
    display: grid;
    gap: 12px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--line-soft)
  }

  .order-support-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 13px 14px;
    border: 1px solid #303645;
    border-radius: 11px;
    background: linear-gradient(145deg, #171c27, #131720)
  }

  .order-support-copy {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0
  }

  .order-support-icon {
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    flex: none;
    border: 1px solid #343b4b;
    border-radius: 10px;
    background: #1a1f2c;
    color: #d4b34d
  }

  .order-support-icon .icon {
    width: 17px;
    height: 17px
  }

  .order-support-copy strong {
    display: block;
    color: #edf0f2;
    font-size: .73rem
  }

  .order-support-copy small {
    display: block;
    margin-top: 3px;
    color: #88949e;
    font-size: .62rem
  }

  .order-support-cta .primary-button {
    flex: none;
    min-height: 39px;
    padding-inline: 12px;
    font-size: .66rem
  }

  .order-documents {
    min-width: 0
  }

  .order-documents-heading {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px;
    margin: 0 1px 8px
  }

  .order-documents-heading h3 {
    margin: 0;
    color: #aab4bd;
    font-size: .66rem;
    font-weight: 780
  }

  .order-documents-hint {
    display: none;
    color: #7f8b96;
    font-size: .58rem
  }

  .order-document-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 8px
  }

  .order-document-list .secondary-button {
    min-height: 41px;
    padding: 7px 9px;
    font-size: .63rem;
    line-height: 1.3;
    text-align: center
  }

  .order-document-list .icon {
    width: 15px;
    height: 15px;
    flex: none
  }

  .order-detail-summary {
    display: flex;
    align-items: center;
    gap: 14px
  }

  .order-detail-thumb {
    width: 68px;
    height: 68px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: #1b202c;
    border: 1px solid #303644;
    color: #b5a06b;
    flex: none;
    overflow: hidden
  }

  .order-detail-thumb .icon {
    width: 32px;
    height: 32px
  }

  .order-detail-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: inherit
  }

  .order-detail-product {
    margin: 5px 0 0;
    color: #c6cdd2;
    font-size: .72rem
  }

  .detail-meta-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-top: 18px
  }

  .detail-meta {
    min-width: 0;
    padding: 11px 12px;
    border: 1px solid #292f3e;
    border-radius: 9px;
    background: #131721
  }

  .detail-meta span {
    display: block;
    color: #84909c;
    font-size: .6rem
  }

  .detail-meta strong {
    display: block;
    margin-top: 5px;
    overflow-wrap: anywhere;
    color: #e9edef;
    font-size: .7rem
  }

  .delivery-panel {
    margin-top: 16px;
    padding: 14px;
    border: 1px solid #303643;
    border-radius: 11px;
    background: #12161f
  }

  .delivery-heading {
    margin: 0 0 10px;
    color: #eef0f2;
    font-size: .76rem;
    font-weight: 780
  }

  .delivery-rows {
    display: grid;
    gap: 8px
  }

  .delivery-row {
    display: grid;
    grid-template-columns: minmax(110px, .55fr) minmax(0, 1fr);
    align-items: center;
    gap: 12px;
    padding: 10px 11px;
    border: 1px solid #292f3e;
    border-radius: 8px;
    background: #171b26
  }

  .delivery-label {
    color: #8f9ba6;
    font-size: .65rem
  }

  .delivery-value {
    color: #f1f3f4;
    font-size: .7rem;
    font-weight: 720;
    overflow-wrap: anywhere
  }

  .delivery-value.code {
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    letter-spacing: .04em;
    color: #ead17f
  }

  .order-info-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 14px;
    border: 1px solid rgba(210, 145, 13, 0.22);
    border-radius: 9px;
    background: rgba(210, 145, 13, 0.04);
    color: #ceb578;
    font-size: .67rem;
    line-height: 1.45
  }

  .load-more-wrap {
    display: flex;
    justify-content: center;
    padding: 12px 14px 16px
  }

  .load-more-button {
    min-height: 39px;
    padding: 0 17px;
    border: 1px solid #3b4150;
    border-radius: 9px;
    background: #171b26;
    color: #dce1e4;
    font-size: .68rem;
    font-weight: 720;
    cursor: pointer
  }

  .load-more-button:hover {
    border-color: #65552b;
    color: #f1d77f
  }

  .help-welcome {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border: 1px solid #303643;
    border-radius: 14px;
    background: radial-gradient(circle at 100% 0, #d5a92716, transparent 37%), linear-gradient(145deg, #171c27, #12161f)
  }

  .help-welcome-icon {
    display: grid;
    place-items: center;
    width: 47px;
    height: 47px;
    flex: none;
    border: 1px solid #66562e;
    border-radius: 13px;
    background: #282316;
    color: #e3c45c
  }

  .help-welcome-icon .icon {
    width: 22px;
    height: 22px
  }

  .help-welcome h2 {
    margin: 0;
    color: #f1f3f4;
    font-size: 1rem
  }

  .help-welcome p {
    margin: 4px 0 0;
    color: #929da8;
    font-size: .69rem
  }

  .help-faq-list {
    display: grid;
    gap: 9px
  }

  .help-faq-list details {
    overflow: hidden;
    border: 1px solid #2d3342;
    border-radius: 11px;
    background: #141822
  }

  .help-faq-list details[open] {
    border-color: #65552b;
    background: #171a22
  }

  .help-faq-list summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    min-height: 54px;
    padding: 12px 15px;
    color: #e8ebed;
    font-size: .73rem;
    font-weight: 730;
    cursor: pointer;
    list-style: none
  }

  .help-faq-list summary::-webkit-details-marker {
    display: none
  }

  .help-faq-list summary:after {
    content: "+";
    color: #d5b34f;
    font-size: 1.15rem;
    font-weight: 500
  }

  .help-faq-list details[open] summary:after {
    content: "−"
  }

  .help-faq-list details p {
    margin: 0;
    padding: 0 15px 15px;
    color: #9aa5af;
    font-size: .68rem;
    line-height: 1.65
  }

  .help-contact-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 17px;
    border: 1px solid #303643;
    border-radius: 12px;
    background: #131721
  }

  .help-contact-card strong {
    display: block;
    color: #eef0f2;
    font-size: .75rem
  }

  .help-contact-card p {
    margin: 3px 0 0;
    color: #8d99a4;
    font-size: .65rem
  }

  .summary-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 14px;
    padding: 13px 14px;
    border: 1px solid var(--line);
    border-radius: 9px;
    background: #141822;
    color: #9ba6b0;
    font-size: .7rem
  }

  .summary-strip strong {
    color: #f1f3f4;
    font-size: .85rem
  }

  .contact-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px
  }

  .contact-card {
    display: grid;
    gap: 10px;
    padding: 14px;
    border: 1px solid var(--line);
    border-radius: 12px;
    background: #131721
  }

  .contact-head,
  .contact-value {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px
  }

  .contact-head strong {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .75rem
  }

  .contact-head .icon {
    width: 15px;
    height: 15px;
    color: #aeb8c1
  }

  .contact-value {
    min-height: 42px;
    padding: 0 10px;
    border-radius: 8px;
    background: #0f131b;
    color: #e1e5e8;
    font-size: .72rem
  }

  .verified {
    color: #54d3a9;
    font-size: .62rem;
    font-weight: 760
  }

  .radio-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 14px
  }

  .radio-choice {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 65px;
    padding: 12px;
    border: 1px solid var(--line);
    border-radius: 11px;
    background: #121620;
    color: #a9b3bd;
    text-align: left;
    cursor: pointer
  }

  .radio-choice .icon {
    color: #85919c
  }

  .radio-choice.active {
    border-color: #655328;
    background: #211f18;
    color: #f1d47c
  }

  .radio-choice.active .icon {
    color: var(--accent)
  }

  .settings-body {
    padding: 18px
  }

  .settings-panel {
    min-width: 0
  }

  .settings-panel h2 {
    margin: 0 0 12px;
    color: #edf0f2;
    font-size: .9rem;
    font-weight: 780
  }

  .conversation {
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-height: 260px;
    padding: 20px
  }

  .message {
    max-width: min(76%, 560px);
    padding: 12px 14px;
    border: 1px solid #303645;
    border-radius: 13px;
    background: #181d2a
  }

  .message.mine {
    align-self: flex-end;
    border-color: #514626;
    background: #211f18
  }

  .message-meta {
    color: #8b97a2;
    font-size: .61rem;
    font-weight: 700
  }

  .message p {
    margin: 6px 0 0;
    color: #e4e8eb;
    font-size: .74rem
  }

  .withdrawal-body,
  .withdraw-pane {
    min-width: 0;
  }

  .withdraw-pane[hidden] {
    display: none !important;
  }

  .withdraw-pane:not(#pane-history) .panel-body {
    padding: 20px 18px 18px;
  }

  .withdraw-pane#pane-history .panel-body {
    padding: 16px 14px 14px;
  }

  .withdraw-pane .subheading {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 0 16px;
    color: #f0f2f4;
    font-size: .95rem;
    font-weight: 770;
    letter-spacing: -.02em;
  }

  .withdraw-pane .subheading .icon {
    width: 18px;
    height: 18px;
    color: var(--accent);
  }

  .withdrawal-body {
    padding: 18px
  }

  .withdrawal-body form {
    max-width: 760px
  }

  .bank-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 14px;
    border: 1px solid var(--line);
    border-radius: 11px;
    background: #141822
  }

  .bank-card strong,
  .bank-card small {
    display: block
  }

  .bank-card strong {
    font-size: .77rem
  }

  .bank-card small {
    margin-top: 5px;
    color: #8d98a3;
    font-size: .66rem
  }

  .prototype-note {
    margin: 2px;
    color: #737e89;
    font-size: .61rem
  }

  .toast {
    position: fixed;
    top: 24px;
    right: 24px;
    bottom: auto;
    z-index: 999999;
    max-width: min(420px, calc(100vw - 32px));
    padding: 14px 18px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-left: 4px solid #c79a3e;
    border-radius: 12px;
    background: rgba(18, 22, 28, 0.96);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    color: #ffffff;
    font-size: .84rem;
    font-weight: 500;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.04);
    opacity: 0;
    transform: translateY(-16px);
    pointer-events: none;
    transition: opacity .25s ease, transform .25s ease;
  }

  .toast.show {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
  }

  /* One unified order list across desktop and mobile. */
  #orders-table,
  #orders-table tbody {
    display: block;
    width: 100%
  }

  #orders-table thead {
    display: none
  }

  #orders-table tbody {
    display: grid;
    gap: 8px
  }

  #orders-table tbody tr {
    display: block !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important
  }

  #orders-table tbody tr[hidden] {
    display: none !important
  }

  #orders-table tbody tr>td:not(.order-list-cell) {
    display: none !important
  }

  #orders-table tbody td.order-list-cell {
    display: block !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important
  }

  #orders-table tbody td.order-list-cell:before {
    display: none !important
  }

  .order-list-card {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(130px, .75fr) minmax(180px, auto) 36px;
    align-items: center;
    gap: 13px;
    min-height: 76px;
    padding: 11px 13px;
    border: 1px solid #292f3e;
    border-radius: 12px;
    background: #131721;
    transition: background .16s, border-color .16s
  }

  .order-list-card:hover {
    border-color: #3b4251;
    background: #181d28
  }

  .order-list-top {
    display: grid;
    grid-column: 1;
    grid-template-columns: 44px minmax(0, 1fr);
    align-items: center;
    gap: 11px;
    min-width: 0
  }

  .order-list-thumb {
    width: 44px !important;
    height: 44px !important;
    border-color: #303644 !important;
    border-radius: 10px !important;
    background: #1a1f2a !important;
    color: #b49a5b !important
  }

  .order-list-main {
    display: grid;
    gap: 4px;
    min-width: 0
  }

  .order-list-name {
    overflow: hidden;
    color: #eef0f2;
    font-size: .77rem;
    font-weight: 750;
    line-height: 1.3;
    text-overflow: ellipsis;
    white-space: nowrap
  }

  .order-list-meta {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #8995a0;
    font-size: .63rem;
    line-height: 1.2
  }

  .order-list-id {
    color: #a7b1ba;
    font-weight: 640
  }

  .order-list-meta-separator {
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: #5c6872
  }

  .order-list-quantity {
    color: #8995a0
  }

  .order-list-info {
    display: grid;
    grid-column: 2;
    gap: 4px;
    min-width: 0;
    padding: 0
  }

  .order-list-date {
    color: #8995a0;
    font-size: .65rem;
    white-space: nowrap
  }

  .order-list-amount {
    color: #f2f4f5;
    font-size: .81rem;
    font-weight: 820;
    white-space: nowrap
  }

  .order-list-footer {
    display: flex;
    grid-column: 3;
    align-items: center;
    gap: 8px;
    min-height: 22px;
    padding: 0;
    flex-wrap: wrap;
  }

  .order-list-footer .status {
    min-height: 24px;
    padding: 0 8px;
    font-size: .62rem;
  }

  .order-list-footer .review-btn {
    min-height: 26px;
    padding: 0 10px;
    font-size: 11px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(110deg, #ffc52e, #ff9d05);
    color: #17130a;
    font-weight: 750;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    transition: transform 0.15s ease, filter 0.15s ease;
  }

  .order-list-footer .review-btn:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
  }

  .order-list-footer .review-btn .icon {
    width: 12px;
    height: 12px;
  }

  .order-list-footer .detail-button {
    min-height: 26px;
    padding: 0 10px;
    font-size: 11px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    text-decoration: none;
  }

  .order-list-footer .detail-button[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .order-list-action {
    position: absolute;
    top: 50%;
    right: 13px;
    display: grid;
    place-items: center;
    width: 33px;
    height: 33px;
    transform: translateY(-50%);
    border: 1px solid #303645;
    border-radius: 9px;
    background: #151923;
    color: #9ba6b0;
    cursor: pointer
  }

  .order-list-action .icon {
    width: 15px;
    height: 15px;
    transform: rotate(-90deg)
  }

  .order-list-action:hover {
    border-color: #606b76;
    color: #f4f6f7
  }

  @media(max-width:1120px) {
    .header-inner {
      gap: 11px
    }

    .workspace {
      grid-template-columns: 222px minmax(0, 1fr);
      gap: 18px
    }

    .two-panels {
      grid-template-columns: 1fr
    }

    .panel {
      padding: 16px
    }

    .order-list-card {
      grid-template-columns: minmax(0, 1.45fr) minmax(135px, .82fr) minmax(118px, .67fr) 36px;
      gap: 10px;
      padding-inline: 11px
    }

    .order-list-name {
      font-size: .73rem
    }
  }

  /* ====================================================
     Destek Talepleri & Talep Detay (Chat) Stilleri
     ==================================================== */
  .subheading {
    display: inline-flex !important;
    align-items: center !important;
    gap: 9px !important;
    margin: 0 !important;
    color: #f3f5f6 !important;
    font-size: 0.96rem !important;
    font-weight: 750 !important;
    line-height: 1.25 !important;
  }

  .subheading .icon {
    width: 18px !important;
    height: 18px !important;
    stroke: var(--accent, #fab316) !important;
    flex-shrink: 0 !important;
    display: inline-block !important;
    vertical-align: middle !important;
  }

  .ticket-panel-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
  }

  #tickets-table {
    width: 100%;
  }

  #tickets-table tbody td {
    vertical-align: middle;
  }

  #tickets-table .product-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
  }

  #tickets-table .product-thumb {
    flex-shrink: 0;
  }

  #tickets-table .product-name {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 460px;
    font-size: 0.88rem;
    font-weight: 750;
    color: #f3f5f6;
  }

  .support-chat-panel {
    display: flex;
    flex-direction: column;
    padding: 0 !important;
    overflow: hidden;
    border: 1px solid var(--line);
    border-radius: var(--radius);
    background: #11151e;
  }

  .ticket-detail-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 16px 20px;
    margin-bottom: 0 !important;
    border-bottom: 1px solid var(--line-soft);
    background: #141823;
  }

  .ticket-head-info {
    min-width: 0;
    flex: 1 1 auto;
  }

  .ticket-head-info .subheading {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    font-size: 0.96rem;
    font-weight: 750;
    color: #f3f5f6;
    word-break: break-word;
  }

  .ticket-meta-tags {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    font-size: 0.72rem;
    color: var(--muted);
  }

  .subtle-dot {
    color: var(--dim);
    font-size: 0.65rem;
  }

  .ticket-head-status {
    flex-shrink: 0;
  }

  .chat-thread {
    display: flex;
    flex-direction: column;
    gap: 18px;
    min-height: 260px;
    max-height: 540px;
    overflow-y: auto;
    padding: 22px;
    background: radial-gradient(circle at 50% 0%, #151a27 0%, #0d1017 100%);
    scroll-behavior: smooth;
  }

  .chat-thread::-webkit-scrollbar {
    width: 6px;
  }

  .chat-thread::-webkit-scrollbar-track {
    background: transparent;
  }

  .chat-thread::-webkit-scrollbar-thumb {
    background: #272e3f;
    border-radius: 4px;
  }

  .chat-thread::-webkit-scrollbar-thumb:hover {
    background: #374056;
  }

  .chat-bubble {
    display: flex;
    flex-direction: column;
    max-width: 76%;
    min-width: 140px;
    word-break: break-word;
    animation: chatBubbleFadeIn 0.2s ease-out;
  }

  @keyframes chatBubbleFadeIn {
    from {
      opacity: 0;
      transform: translateY(6px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Müşteri Mesajı -> SAĞDA */
  .chat-bubble.user {
    align-self: flex-end;
    align-items: flex-end;
    margin-left: auto;
  }

  .chat-bubble.user .bubble-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 5px;
  }

  .chat-bubble.user .bubble-author {
    color: var(--accent, #fab316);
    font-size: 0.72rem;
    font-weight: 750;
  }

  .chat-bubble.user .bubble-time {
    color: #74808c;
    font-size: 0.68rem;
  }

  .chat-bubble.user .bubble-body {
    background: linear-gradient(135deg, #1d2434 0%, #141925 100%);
    border: 1px solid rgba(255, 194, 26, 0.28);
    border-radius: 14px 14px 2px 14px;
    padding: 12px 16px;
    color: #f3f5f6;
    font-size: 0.84rem;
    line-height: 1.55;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35);
    text-align: left;
  }

  /* Operatör Mesajı -> SOLDA */
  .chat-bubble.rep {
    align-self: flex-start;
    align-items: flex-start;
    margin-right: auto;
  }

  .chat-bubble.rep .bubble-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 5px;
  }

  .chat-bubble.rep .rep-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #27c997;
    font-size: 0.72rem;
    font-weight: 750;
  }

  .chat-bubble.rep .rep-badge .icon {
    width: 14px;
    height: 14px;
    stroke: #27c997;
  }

  .chat-bubble.rep .bubble-time {
    color: #74808c;
    font-size: 0.68rem;
  }

  .chat-bubble.rep .bubble-body {
    background: #161b27;
    border: 1px solid #283042;
    border-radius: 14px 14px 14px 2px;
    padding: 12px 16px;
    color: #eaedf0;
    font-size: 0.84rem;
    line-height: 1.55;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
    text-align: left;
  }

  .bubble-text {
    white-space: pre-wrap;
    word-break: break-word;
  }

  .bubble-attachment {
    margin-top: 10px;
    padding-top: 4px;
  }

  .bubble-attachment img {
    display: block;
    max-width: 260px;
    max-height: 220px;
    width: auto;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    cursor: pointer;
    transition: transform 0.18s, border-color 0.18s, box-shadow 0.18s;
  }

  .bubble-attachment img:hover {
    transform: scale(1.02);
    border-color: var(--accent, #fab316);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
  }

  /* Reply Box & Upload Label */
  .chat-reply-box {
    padding: 16px 20px;
    border-top: 1px solid var(--line-soft);
    background: #0f121a;
  }

  .chat-reply-box .form-grid {
    gap: 10px;
  }

  .chat-reply-box textarea {
    min-height: 85px;
    background: #090b11;
    border-color: #292f3e;
    border-radius: 10px;
    color: #f3f5f6;
    padding: 12px 14px;
    font-size: 0.82rem;
    resize: vertical;
    transition: border-color 0.16s, box-shadow 0.16s;
  }

  .chat-reply-box textarea:focus {
    border-color: var(--accent, #fab316);
    box-shadow: 0 0 0 2px rgba(255, 194, 26, 0.15);
  }

  .chat-reply-footer {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 12px !important;
    margin-top: 6px !important;
    width: 100% !important;
  }

  .reply-file-wrap {
    display: inline-flex !important;
    align-items: center !important;
    min-width: 0 !important;
    flex-shrink: 1 !important;
  }

  .reply-file-label {
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    min-height: 40px !important;
    height: 40px !important;
    padding: 0 13px !important;
    border: 1px dashed #3a4255 !important;
    border-radius: 9px !important;
    background: #141822 !important;
    color: #9ba6b0 !important;
    font-size: 0.72rem !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: border-color 0.16s, background 0.16s, color 0.16s !important;
    user-select: none !important;
    white-space: nowrap !important;
    box-sizing: border-box !important;
    max-width: 100% !important;
  }

  .reply-file-label:hover {
    border-color: var(--accent, #fab316) !important;
    background: #1a1f2c !important;
    color: #f3f5f6 !important;
  }

  .reply-file-label .icon {
    width: 15px !important;
    height: 15px !important;
    color: var(--accent, #fab316) !important;
    flex-shrink: 0 !important;
  }

  .reply-file-label.has-file {
    border-style: solid !important;
    border-color: #27c997 !important;
    color: #27c997 !important;
  }

  #replyFileName {
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: inline-block;
    vertical-align: middle;
  }

  .chat-reply-footer .primary-button {
    flex-shrink: 0 !important;
    min-height: 40px !important;
    height: 40px !important;
    padding: 0 18px !important;
    border-radius: 9px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    box-sizing: border-box !important;
  }

  @media(max-width:850px) {
    .header-inner {
      width: calc(100% - 32px);
      grid-template-columns: auto minmax(0, 1fr) auto auto;
      min-height: 62px;
      gap: 10px
    }

    .category-button {
      display: none
    }

    .header-inner>.header-search {
      max-width: none
    }

    .locale-button {
      min-width: 58px;
      padding-inline: 9px
    }

    .header-account {
      width: 42px;
      padding: 0;
      font-size: 0
    }

    .header-account .icon {
      width: 17px;
      height: 17px
    }

    .mobile-search {
      display: none
    }

    .workspace {
      grid-template-columns: minmax(0, 1fr);
      gap: 14px;
      width: calc(100% - 32px);
      margin: 17px auto 40px
    }

    .sidebar {
      position: relative;
      top: auto;
      padding: 7px
    }

    .profile-user-card {
      min-height: 62px;
      margin: 0;
      padding: 7px 9px;
      cursor: pointer
    }

    .profile-mobile-arrow {
      display: block
    }

    .profile-menus-wrapper {
      display: none;
      padding: 8px 1px 2px
    }

    .profile-menus-wrapper.show-menus {
      display: flex
    }

    .profile-menu-group {
      margin: 13px 8px 6px
    }

    .nav-link {
      min-height: 44px
    }

    .content,
    .view {
      gap: 15px
    }

    .page-hero {
      min-height: 0;
      padding: 2px 1px 13px
    }

    .hero-icon {
      width: 40px;
      height: 40px
    }

    .hero-action {
      margin-left: auto
    }

    .stat-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px
    }

    .dashboard-primary,
    .dashboard-secondary {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 9px
    }

    .dashboard-tile {
      min-height: 120px;
      padding: 13px
    }

    .dashboard-tile.compact {
      min-height: 88px
    }

    .dashboard-primary .dashboard-tile[data-page="orders"],
    .dashboard-primary .dashboard-tile.orders-tile,
    .dashboard-secondary .dashboard-tile[data-page="help"],
    .dashboard-secondary .dashboard-tile.streamer-tile {
      grid-column: 1/-1
    }

    .stat-card {
      min-height: 110px;
      padding: 14px
    }

    .data-table thead {
      display: none
    }

    .data-table,
    .data-table tbody {
      display: block;
      width: 100%
    }

    .data-table tbody {
      display: grid;
      gap: 8px
    }

    .data-table tbody tr {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0 12px;
      padding: 8px 11px;
      border: 1px solid #2a303e;
      border-radius: 11px;
      background: #131721
    }

    .data-table tbody tr[hidden] {
      display: none !important
    }

    .data-table tbody td {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      min-width: 0;
      padding: 8px 1px;
      border: 0;
      background: transparent;
      font-size: .68rem
    }

    .data-table tbody td:before {
      content: attr(data-label);
      flex: none;
      color: #788590;
      font-size: .56rem;
      font-weight: 780;
      letter-spacing: .04em;
      text-transform: uppercase
    }

    .data-table tbody td:first-child {
      grid-column: 1/-1;
      border-bottom: 1px solid #ffffff0c
    }

    .data-table .product-name {
      overflow-wrap: anywhere
    }

    #dashboard-orders-table tbody tr {
      grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
      gap: 0 12px
    }

    #dashboard-orders-table tbody td:first-child {
      display: block;
      grid-column: 1/-1;
      padding: 8px 1px 10px
    }

    #dashboard-orders-table tbody td:first-child:before {
      display: none
    }

    #dashboard-orders-table .order-image {
      width: 100%;
      justify-content: flex-start
    }

    #dashboard-orders-table .product-name {
      flex: 1;
      white-space: normal;
      text-align: left;
      overflow-wrap: anywhere
    }

    #dashboard-orders-table tbody td:last-child {
      grid-column: 1/-1;
      justify-content: flex-start;
      border-top: 1px solid #ffffff0c
    }

    #dashboard-payments-table tbody tr {
      grid-template-columns: minmax(0, 1fr);
      gap: 0;
      padding: 7px 11px
    }

    #dashboard-payments-table tbody td,
    #dashboard-payments-table tbody td:first-child {
      grid-column: 1;
      min-height: 34px;
      padding: 7px 1px;
      border-bottom: 1px solid #ffffff0c
    }

    #dashboard-payments-table tbody td:last-child {
      border-bottom: 0
    }

    #dashboard-payments-table tbody td:before {
      font-size: .55rem
    }

    /* Tickets Table Mobile Card Layout */
    #tickets-table thead {
      display: none !important;
    }

    #tickets-table,
    #tickets-table tbody {
      display: block !important;
      width: 100% !important;
    }

    #tickets-table tbody {
      display: grid !important;
      gap: 10px !important;
    }

    #tickets-table tbody tr {
      display: flex !important;
      flex-direction: column !important;
      gap: 10px !important;
      padding: 14px !important;
      border: 1px solid #2a303e !important;
      border-radius: 12px !important;
      background: #131721 !important;
    }

    #tickets-table tbody td {
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      gap: 10px !important;
      padding: 0 !important;
      border: 0 !important;
      background: transparent !important;
      width: 100% !important;
      min-width: 0 !important;
    }

    #tickets-table tbody td:first-child {
      display: block !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
      padding-bottom: 10px !important;
      grid-column: auto !important;
    }

    #tickets-table tbody td:first-child:before {
      display: none !important;
    }

    #tickets-table tbody td:first-child .product-cell {
      display: flex !important;
      align-items: center !important;
      gap: 12px !important;
      width: 100% !important;
    }

    #tickets-table tbody td:first-child .product-thumb {
      flex: 0 0 38px !important;
    }

    #tickets-table tbody td:first-child .product-name {
      white-space: normal !important;
      font-size: 0.88rem !important;
      font-weight: 700 !important;
      line-height: 1.35 !important;
      color: #fff !important;
      display: -webkit-box !important;
      -webkit-line-clamp: 2 !important;
      -webkit-box-orient: vertical !important;
      overflow: hidden !important;
      max-width: 100% !important;
    }

    #tickets-table tbody td:not(:first-child):not(:last-child):before {
      content: attr(data-label) !important;
      color: #788590 !important;
      font-size: 0.68rem !important;
      font-weight: 750 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.04em !important;
    }

    #tickets-table tbody td:last-child {
      border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
      padding-top: 10px !important;
      margin-top: 2px !important;
      display: flex !important;
      justify-content: stretch !important;
      grid-column: auto !important;
    }

    #tickets-table tbody td:last-child:before {
      display: none !important;
    }

    #tickets-table tbody td:last-child .detail-button {
      width: 100% !important;
      justify-content: center !important;
      min-height: 38px !important;
    }

    /* Transactions Table Mobile Card Header & Overflow Fix */
    #trxTable tbody td:first-child {
      display: flex !important;
      align-items: center !important;
      gap: 12px !important;
      width: 100% !important;
      padding: 10px 0 !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    }

    #trxTable tbody td:first-child:before {
      display: none !important;
    }

    #trxTable tbody td:first-child .product-cell {
      display: flex !important;
      align-items: center !important;
      gap: 12px !important;
      width: 100% !important;
      min-width: 0 !important;
      flex: 1 !important;
    }

    #trxTable tbody td:first-child .product-thumb {
      flex: 0 0 38px !important;
      width: 38px !important;
      height: 38px !important;
      min-width: 38px !important;
    }

    #trxTable tbody td:first-child .product-cell > span:not(.product-thumb) {
      flex: 1 !important;
      min-width: 0 !important;
      display: flex !important;
      flex-direction: column !important;
      gap: 2px !important;
    }

    #trxTable tbody td:first-child .product-name {
      white-space: normal !important;
      font-size: 0.84rem !important;
      font-weight: 700 !important;
      line-height: 1.35 !important;
      color: #fff !important;
      overflow-wrap: break-word !important;
      word-break: break-word !important;
      max-width: 100% !important;
    }

    .ticket-panel-head {
      display: flex !important;
      flex-direction: row !important;
      justify-content: space-between !important;
      align-items: center !important;
      gap: 10px !important;
    }

    .ticket-panel-head .subheading {
      font-size: 0.92rem !important;
    }

    .ticket-panel-head .primary-button {
      width: auto !important;
    }

    @media (max-width: 480px) {
      .ticket-panel-head {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
      }
      .ticket-panel-head .primary-button {
        width: 100% !important;
        justify-content: center !important;
      }
    }

    /* Ticket Detail Chat on Mobile */
    .ticket-detail-head {
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: 10px !important;
      padding: 14px 16px !important;
    }

    .ticket-head-status {
      align-self: flex-start !important;
    }

    .chat-thread {
      padding: 16px 12px !important;
      gap: 14px !important;
      max-height: 60vh !important;
    }

    .chat-bubble {
      max-width: 90% !important;
    }

    .chat-bubble.user .bubble-body,
    .chat-bubble.rep .bubble-body {
      padding: 10px 13px !important;
      font-size: 0.82rem !important;
    }

    .bubble-attachment img {
      max-width: 100% !important;
    }

    .chat-reply-box {
      padding: 14px !important;
    }

    .chat-reply-footer {
      display: flex !important;
      flex-direction: row !important;
      justify-content: space-between !important;
      align-items: center !important;
      gap: 10px !important;
      width: 100% !important;
    }

    .reply-file-wrap {
      flex: 1 1 auto !important;
      min-width: 0 !important;
    }

    .reply-file-label {
      max-width: 100% !important;
      width: auto !important;
      min-height: 40px !important;
      height: 40px !important;
    }

    #replyFileName {
      max-width: 130px !important;
    }

    .chat-reply-footer .primary-button {
      flex-shrink: 0 !important;
      width: auto !important;
      min-height: 40px !important;
      height: 40px !important;
      padding: 0 16px !important;
    }

    @media (max-width: 380px) {
      .chat-reply-footer {
        gap: 6px !important;
      }
      #replyFileName {
        max-width: 85px !important;
      }
      .reply-file-label {
        padding: 0 9px !important;
        font-size: 0.69rem !important;
      }
      .chat-reply-footer .primary-button {
        padding: 0 12px !important;
      }
    }

    .hero-back-btn {
      width: 100% !important;
      justify-content: center !important;
      margin-left: 0 !important;
      margin-top: 6px !important;
    }

    .table-panel .panel-body {
      padding-inline: 10px
    }

    .filter-row {
      gap: 7px
    }

    .date-control {
      flex: 0 1 200px;
      max-width: 220px;
    }

    .date-control input {
      width: 100%;
    }

    .search-control {
      margin-left: 0;
      flex: 1 1 190px
    }

    .settings-body,
    .withdrawal-body {
      padding: 15px
    }

    .message {
      max-width: 88%
    }

    .order-list-card {
      grid-template-columns: minmax(0, 1fr);
      gap: 9px;
      min-height: 0;
      padding: 14px 3px 13px;
      border: 0;
      border-radius: 0;
      background: transparent
    }

    .order-list-card:hover {
      border-color: transparent;
      background: transparent
    }

    .order-list-top {
      grid-column: 1;
      grid-template-columns: 43px minmax(0, 1fr) 34px;
      gap: 10px
    }

    .order-list-thumb {
      width: 43px !important;
      height: 43px !important
    }

    .order-list-action {
      position: static;
      grid-column: 3;
      grid-row: 1;
      width: 34px;
      height: 34px;
      transform: none
    }

    .order-list-info {
      display: flex;
      grid-column: 1;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding-left: 53px
    }

    .order-list-footer {
      grid-column: 1;
      padding-left: 53px
    }

    #orders-table tbody {
      gap: 0;
      padding-inline: 3px
    }

    #orders-table tbody tr+tr .order-list-cell {
      border-top: 1px solid #272d3b !important
    }

    #view-orders .page-hero,
    #view-withdrawals .page-hero {
      padding: 1px 1px 11px
    }

    #view-orders .subnav-shell,
    #view-withdrawals .subnav-shell {
      border-radius: 13px
    }

    #view-orders .tab-list,
    #view-withdrawals .tab-list {
      flex-wrap: nowrap;
      overflow-x: auto;
      scrollbar-width: thin
    }

    #view-orders .tab-button,
    #view-withdrawals .tab-button {
      min-height: 38px;
      padding-inline: 11px
    }

    #view-orders .filter-row,
    #view-withdrawals .filter-row {
      flex-wrap: nowrap;
      overflow-x: auto;
      scrollbar-width: thin
    }

    #view-orders .date-control,
    #view-withdrawals .date-control {
      flex: 0 0 185px;
      max-width: 185px
    }

    #view-orders .date-control input,
    #view-withdrawals .date-control input {
      width: 100%;
      min-width: 0
    }

    #view-orders .quick-filter,
    #view-withdrawals .quick-filter {
      flex: 0 0 auto
    }
  }

  @media(max-width:620px) {
    .header-inner {
      grid-template-columns: auto 1fr auto;
      grid-template-rows: 56px;
      min-height: 56px;
      padding-bottom: 0
    }

    .brand {
      font-size: 1.02rem
    }

    .brand-mark {
      width: 25px;
      height: 28px
    }

    .header-inner>.header-search {
      display: none
    }

    .header-inner .locale-button {
      justify-self: end;
      min-height: 35px;
      font-size: .62rem
    }

    .header-inner .header-account {
      grid-column: 3;
      grid-row: 1;
      justify-self: end;
      width: 38px;
      min-height: 35px
    }

    .mobile-search {
      display: block;
      width: calc(100% - 32px);
      margin: 0 auto 9px
    }

    .mobile-search .header-search {
      max-width: none
    }

    .mobile-search input {
      height: 39px
    }

    .mobile-search .icon {
      top: 11px
    }

    .workspace {
      width: calc(100% - 24px);
      margin-top: 13px
    }

    .hero-icon {
      width: 38px;
      height: 38px;
      border-radius: 10px
    }

    .page-hero {
      flex-wrap: wrap;
      align-items: flex-start
    }

    .hero-copy {
      flex: 1 1 calc(100% - 52px)
    }

    .page-hero>button:not(.hero-action) {
      margin-left: auto
    }

    .hero-copy h1 {
      font-size: 1.08rem
    }

    .hero-copy p {
      font-size: .67rem;
      line-height: 1.4
    }

    .hero-action .primary-button,
    .hero-action.primary-button {
      min-height: 36px;
      padding-inline: 9px;
      font-size: .61rem;
      white-space: nowrap
    }

    .stat-grid {
      grid-template-columns: 1fr
    }

    .stat-card {
      min-height: 98px;
      padding: 13px 15px
    }

    .stat-value {
      margin-top: 8px;
      font-size: 1.35rem
    }

    .panel {
      padding: 13px;
      border-radius: 13px
    }

    .panel-head h2,
    .section-title {
      font-size: .84rem
    }

    .ledger-panel-head {
      align-items: flex-start;
      gap: 8px;
      padding-bottom: 11px
    }

    .ledger-heading {
      gap: 9px
    }

    .ledger-heading-icon {
      width: 33px;
      height: 33px;
      border-radius: 9px
    }

    .ledger-heading-copy p {
      max-width: 190px;
      font-size: .6rem
    }

    .ledger-action {
      padding: 7px 8px;
      font-size: .58rem
    }

    .help-welcome {
      align-items: flex-start;
      padding: 15px
    }

    .help-contact-card {
      align-items: flex-start;
      flex-direction: column
    }

    .filter-row {
      padding: 10px;
      gap: 6px
    }

    .quick-filter {
      min-height: 34px;
      padding-inline: 9px;
      font-size: .59rem
    }

    .date-control {
      flex: 1 1 100%;
      max-width: 100%
    }

    .date-control input {
      flex: 1;
      width: 0;
      min-width: 0
    }

    .search-control {
      flex-basis: 100%;
      min-width: 0
    }

    .data-table tbody tr {
      grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
      gap: 0 8px;
      padding: 7px 9px
    }

    .data-table tbody td {
      font-size: .64rem
    }

    .data-table tbody td:before {
      font-size: .52rem
    }

    .product-thumb {
      width: 34px;
      height: 34px
    }

    .status {
      min-height: 22px;
      padding-inline: 8px;
      font-size: .57rem
    }

    .detail-meta-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 8px
    }

    .order-support-cta {
      align-items: stretch;
      flex-direction: column;
      gap: 11px;
      padding: 12px
    }

    .order-support-cta .primary-button {
      width: 100%;
      min-height: 43px
    }

    .order-documents-hint {
      display: block
    }

    .order-document-list {
      display: flex;
      gap: 8px;
      overflow-x: auto;
      padding: 1px 1px 7px;
      scroll-snap-type: x mandatory;
      scrollbar-width: thin;
      scrollbar-color: #46515a transparent;
      -webkit-overflow-scrolling: touch
    }

    .order-document-list .secondary-button {
      flex: 0 0 176px;
      min-height: 46px;
      scroll-snap-align: start
    }

    .delivery-row {
      grid-template-columns: minmax(0, 1fr);
      gap: 5px
    }

    .tab-button {
      min-height: 42px;
      padding-inline: 11px;
      font-size: .65rem
    }

    .form-grid,
    .contact-grid {
      grid-template-columns: 1fr
    }

    .field.full {
      grid-column: auto
    }

    .field input,
    .field select {
      min-height: 46px;
      font-size: 16px
    }

    .field textarea {
      font-size: 16px
    }

    .radio-grid {
      grid-template-columns: 1fr;
      gap: 8px
    }

    .radio-choice {
      min-height: 56px
    }

    .form-actions {
      align-items: stretch;
      flex-direction: column
    }

    .form-actions .primary-button,
    .form-actions .secondary-button {
      width: 100%;
      min-height: 44px
    }

    .summary-strip {
      align-items: flex-start;
      padding: 12px;
      font-size: .66rem
    }

    .summary-strip strong {
      font-size: .8rem
    }

    .empty-state {
      min-height: 230px;
      padding: 22px 14px
    }

    .bank-card {
      align-items: flex-start;
      flex-direction: column
    }

    .toast {
      top: 16px;
      left: 16px;
      right: 16px;
      bottom: auto;
      width: calc(100% - 32px);
      max-width: calc(100vw - 32px);
      margin: 0 auto;
    }

    .settings-body,
    .withdrawal-body {
      padding: 13px
    }

    .settings-tabs,
    .withdrawal-tabs {
      padding: 6px
    }

    .order-list-name {
      font-size: .75rem
    }

    .order-list-info,
    .order-list-footer {
      padding-left: 0
    }
  }

  @media(max-width:360px) {
    .workspace {
      width: calc(100% - 20px)
    }

    .mobile-search {
      width: calc(100% - 24px)
    }

    .page-hero {
      gap: 9px
    }

    .hero-copy h1 {
      font-size: 1rem
    }

    .order-list-top {
      grid-template-columns: 40px minmax(0, 1fr) 32px;
      gap: 8px
    }

    .order-list-thumb {
      width: 40px !important;
      height: 40px !important
    }

    .order-list-info,
    .order-list-footer {
      padding-left: 0
    }

    .order-list-card {
      gap: 8px
    }

    .dashboard-primary,
    .dashboard-secondary {
      grid-template-columns: 1fr
    }

    .dashboard-tile,
    .dashboard-tile.compact {
      min-height: 88px
    }

    .detail-meta-grid {
      grid-template-columns: 1fr 1fr
    }
  }

  @media(prefers-reduced-motion:reduce) {

    *,
    *::before,
    *::after {
      scroll-behavior: auto !important;
      animation-duration: .01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: .01ms !important
    }
  }


  /* Integrated wallet view */
  #view-wallet {
    --gold: #ffb300;
    --gold-2: #ff9900;
    --muted: #9ba7b6;
    --muted-2: #748192;
    --max: 100%;
  }

  #view-wallet * {
    box-sizing: border-box
  }

  #view-wallet button,
  #view-wallet input {
    font: inherit
  }

  #view-wallet button {
    color: inherit
  }

  #view-wallet a {
    color: inherit;
    text-decoration: none
  }

  #view-wallet button:focus-visible,
  #view-wallet a:focus-visible {
    outline: 2px solid #ffcf61;
    outline-offset: 2px
  }

  #view-wallet input:focus,
  #view-wallet input:focus-visible {
    outline: none !important;
  }

  #view-wallet .icon {
    width: 18px;
    height: 18px;
    flex: 0 0 auto;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round
  }

  #view-wallet .wallet-shell {
    width: min(100% - 48px, var(--max));
    margin: 28px auto;
    display: grid;
    grid-template-columns: 248px minmax(0, 1fr);
    align-items: start;
    gap: 28px
  }

  #view-wallet .profile-sidebar {
    position: sticky;
    top: 20px;
    padding: 8px;
    border: 1px solid #ffb81738;
    border-radius: 17px;
    background: linear-gradient(145deg, #23272f, #11151d);
    box-shadow: 0 14px 34px #0005
  }

  #view-wallet .profile-user-card {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 11px;
    padding: 12px;
    border: 1px solid #ffb81725;
    border-radius: 12px;
    background: #090b10
  }

  #view-wallet .avatar {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    flex: 0 0 auto;
    border: 2px solid #ffb81775;
    border-radius: 50%;
    background: #ffca05;
    color: #1a1607;
    font-weight: 900
  }

  #view-wallet .user-name {
    font-size: .77rem;
    font-weight: 800
  }

  #view-wallet .user-email {
    color: #858e9b;
    font-size: .69rem;
    overflow-wrap: anywhere
  }

  #view-wallet .profile-mobile-arrow {
    display: none;
    margin-left: auto;
    color: var(--gold)
  }

  #view-wallet .profile-menu-group {
    margin: 18px 7px 8px;
    color: var(--gold);
    font-size: .59rem;
    font-weight: 900;
    letter-spacing: .13em;
    text-transform: uppercase
  }

  #view-wallet .profile-menu {
    display: grid;
    gap: 4px
  }

  #view-wallet .profile-menu a {
    display: flex;
    align-items: center;
    gap: 11px;
    min-height: 41px;
    padding: 0 11px;
    border: 1px solid transparent;
    border-radius: 8px;
    color: #929aa7;
    font-size: .76rem;
    font-weight: 700;
    transition: color .18s, border-color .18s, background .18s
  }

  #view-wallet .profile-menu a .icon {
    width: 15px;
    height: 15px;
    color: #858d99
  }

  #view-wallet .profile-menu a:hover {
    border-color: #ffb8172e;
    background: #ffb8170d;
    color: #fff
  }

  #view-wallet .profile-menu a.active {
    border-color: #ffb81750;
    background: linear-gradient(90deg, #ffb81724, #ff730008);
    color: #fff;
    box-shadow: inset 3px 0 0 var(--gold)
  }

  #view-wallet .profile-menu a.active .icon {
    color: var(--gold)
  }

  #view-wallet .profile-menu a.logout {
    margin-top: 4px;
    color: #ff6548
  }

  #view-wallet .profile-menu a.logout .icon {
    color: #ff6548
  }

  #view-wallet .profile-content {
    min-width: 0;
    display: grid;
    gap: 20px
  }

  #view-wallet .wallet-banner,
  #view-wallet .wallet-topup,
  #view-wallet .wallet-info,
  #view-wallet .balance-card {
    border: 1px solid #ffb81732;
    border-radius: 16px;
    background: linear-gradient(145deg, #20242f, #131721);
    box-shadow: 0 12px 32px #0003
  }

  #view-wallet .wallet-banner {
    position: relative;
    display: flex;
    align-items: center;
    gap: 16px;
    overflow: hidden;
    padding: 23px 25px
  }

  #view-wallet .wallet-banner:after {
    position: absolute;
    top: -84px;
    right: -34px;
    width: 205px;
    height: 205px;
    border-radius: 50%;
    background: radial-gradient(circle, #ffb81725, transparent 70%);
    content: "";
    pointer-events: none
  }

  #view-wallet .wallet-banner-icon {
    display: grid;
    place-items: center;
    width: 49px;
    height: 49px;
    flex: 0 0 auto;
    border: 1px solid #ffb81772;
    border-radius: 12px;
    background: linear-gradient(140deg, var(--gold), var(--gold-2));
    color: #17130a;
    box-shadow: 0 10px 22px #ff9c0026
  }

  #view-wallet .wallet-banner-icon .icon {
    width: 23px;
    height: 23px;
    stroke-width: 2.1
  }

  #view-wallet .wallet-banner h1 {
    margin: 0;
    font-size: 1.42rem;
    line-height: 1.15;
    letter-spacing: -.035em
  }

  #view-wallet .wallet-banner p {
    margin: 5px 0 0;
    color: var(--muted);
    font-size: .8rem
  }

  #view-wallet .wallet-info {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 15px 18px;
    border-color: #ffb8173e;
    background: linear-gradient(105deg, #ffb81717, #ff73000c)
  }

  #view-wallet .info-icon {
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    flex: 0 0 auto;
    border: 1px solid #ffb81742;
    border-radius: 10px;
    background: #ffb8171c;
    color: var(--gold)
  }

  #view-wallet .info-icon .icon {
    width: 18px;
    height: 18px;
    stroke-width: 2.2
  }

  #view-wallet .wallet-info strong {
    display: block;
    color: #ffc84d;
    font-size: .78rem
  }

  #view-wallet .wallet-info p {
    margin: 3px 0 0;
    color: #adb5c0;
    font-size: .73rem;
    line-height: 1.5
  }

  #view-wallet .balance-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px
  }

  #view-wallet .balance-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
    overflow: hidden;
    padding: 18px 17px
  }

  #view-wallet .balance-card:after {
    position: absolute;
    top: -44px;
    right: -35px;
    width: 115px;
    height: 115px;
    border-radius: 50%;
    background: radial-gradient(circle, var(--glow), transparent 72%);
    content: "";
    pointer-events: none
  }

  #view-wallet .balance-card.available {
    --glow: #17c66b2c;
    border-color: #2789436b;
    background: linear-gradient(135deg, #14281e, #111b19)
  }

  #view-wallet .balance-card.withdrawable {
    --glow: #ff8a282b;
    border-color: #99531970;
    background: linear-gradient(135deg, #2a2019, #19191a)
  }

  #view-wallet .balance-card.total {
    --glow: #ffb81728;
    border-color: #75531b7a;
    background: linear-gradient(135deg, #28231a, #1a1920)
  }

  #view-wallet .balance-icon {
    display: grid;
    place-items: center;
    width: 45px;
    height: 45px;
    flex: 0 0 auto;
    border-radius: 12px;
    background: #ffffff0d;
    color: var(--balance-color)
  }

  #view-wallet .available .balance-icon {
    --balance-color: #58d685;
    background: #1d4930
  }

  #view-wallet .withdrawable .balance-icon {
    --balance-color: #ff9b47;
    background: #4a301e
  }

  #view-wallet .total .balance-icon {
    --balance-color: #ffc42e;
    background: #47391d
  }

  #view-wallet .balance-icon .icon {
    width: 22px;
    height: 22px;
    stroke-width: 2.1
  }

  #view-wallet .balance-copy {
    min-width: 0
  }

  #view-wallet .balance-label {
    display: block;
    color: #a4acb8;
    font-size: .69rem;
    font-weight: 700
  }

  #view-wallet .balance-value {
    display: block;
    margin-top: 2px;
    color: #f4f6f8;
    font-size: 1.23rem;
    font-weight: 900;
    letter-spacing: -.04em;
    white-space: nowrap
  }

  #view-wallet .balance-value small {
    color: #a4adba;
    font-size: .79rem;
    font-weight: 750
  }

  #view-wallet .balance-badge {
    position: absolute;
    top: 11px;
    right: 11px;
    padding: 3px 8px;
    border-radius: 999px;
    background: #ffffff10;
    color: #cbd1d9;
    font-size: .57rem;
    font-weight: 650
  }

  #view-wallet .available .balance-badge {
    background: #1b5732;
    color: #71e496
  }

  #view-wallet .withdrawable .balance-badge {
    background: #55331a;
    color: #ffad65
  }

  #view-wallet .total .balance-badge {
    background: #493817;
    color: #ffd34f
  }

  #view-wallet .wallet-topup {
    padding: 21px 22px 23px
  }

  #view-wallet .topup-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 20px
  }

  #view-wallet .topup-title {
    margin: 0;
    font-size: 1.04rem;
    font-weight: 650;
    letter-spacing: -.02em
  }

  #view-wallet .topup-subtitle {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: .73rem
  }

  #view-wallet .stepper {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #8792a1;
    font-size: .66rem;
    font-weight: 750;
    white-space: nowrap
  }

  #view-wallet .step {
    display: flex;
    align-items: center;
    gap: 6px
  }

  #view-wallet .step-num {
    display: grid;
    place-items: center;
    width: 27px;
    height: 27px;
    border: 1px solid #373f51;
    border-radius: 50%;
    color: #9ba6b5;
    font-size: .68rem
  }

  #view-wallet .step.current {
    color: #ffd15b
  }

  #view-wallet .step.current .step-num {
    border-color: var(--gold);
    background: linear-gradient(135deg, var(--gold), var(--gold-2));
    color: #17130a
  }

  #view-wallet .step.done {
    color: #a2e7b4
  }

  #view-wallet .step.done .step-num {
    border-color: #238353;
    background: #14834a;
    color: #fff
  }

  #view-wallet .step-line {
    width: clamp(12px, 4vw, 42px);
    height: 1px;
    background: #394051
  }

  #view-wallet .topup-layout {
    display: grid;
    grid-template-columns: 250px minmax(0, 1fr);
    gap: 22px;
    align-items: start
  }

  #view-wallet .method-label,
  #view-wallet .subheading {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 11px;
    font-size: .78rem;
    font-weight: 650
  }

  #view-wallet .method-label .icon,
  #view-wallet .subheading .icon {
    width: 16px;
    height: 16px;
    color: var(--gold)
  }

  #view-wallet .method-list {
    display: grid;
    gap: 8px
  }

  #view-wallet .method-option {
    display: grid;
    grid-template-columns: 38px minmax(0, 1fr) 17px;
    align-items: center;
    gap: 10px;
    width: 100%;
    min-height: 66px;
    padding: 10px;
    border: 1px solid #303748;
    border-radius: 10px;
    background: #10141e;
    color: #dce1e8;
    text-align: left;
    cursor: pointer;
    transition: border-color .18s, background .18s, transform .18s
  }

  #view-wallet .method-option:hover {
    border-color: #6b592d;
    transform: translateY(-1px)
  }

  #view-wallet .method-option[aria-selected="true"] {
    border-color: #d2910d;
    background: linear-gradient(110deg, #ffb8171b, #ff730009);
    box-shadow: inset 0 0 0 1px #ffb81719
  }

  #view-wallet .method-icon {
    display: grid;
    place-items: center;
    width: 37px;
    height: 37px;
    border: 1px solid #ffffff0d;
    border-radius: 9px;
    background: #ffffff08;
    color: #9aa5b3
  }

  #view-wallet .method-option[aria-selected="true"] .method-icon {
    border-color: #ffb8173a;
    background: #ffb81716;
    color: var(--gold)
  }

  #view-wallet .method-icon .icon {
    width: 19px;
    height: 19px
  }

  #view-wallet .method-name {
    display: block;
    font-size: .76rem;
    font-weight: 650
  }

  #view-wallet .method-desc {
    display: block;
    margin-top: 2px;
    color: #8994a3;
    font-size: .64rem
  }

  #view-wallet .method-check {
    width: 16px;
    height: 16px;
    color: var(--gold);
    opacity: 0
  }

  #view-wallet .method-option[aria-selected="true"] .method-check {
    opacity: 1
  }

  #view-wallet .payment-pane {
    min-width: 0;
    animation: fade-pane .18s ease-out
  }

  #view-wallet .payment-pane[hidden] {
    display: none !important;
  }

  @keyframes fade-pane {
    from {
      opacity: 0;
      transform: translateY(4px)
    }

    to {
      opacity: 1;
      transform: translateY(0)
    }
  }

  #view-wallet .provider-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 14px;
    padding: 10px 12px;
    border: 1px solid #313849;
    border-radius: 10px;
    background: #111620
  }

  #view-wallet .provider-meta {
    display: flex;
    align-items: center;
    gap: 9px
  }

  #view-wallet .provider-logo {
    display: grid;
    place-items: center;
    width: 34px;
    height: 28px;
    border-radius: 6px;
    background: #f6f7f8;
    color: #28334b;
    font-size: .65rem;
    font-weight: 950;
    letter-spacing: -.03em
  }

  #view-wallet .provider-meta strong {
    display: block;
    font-size: .73rem
  }

  #view-wallet .provider-meta small {
    display: block;
    margin-top: 1px;
    color: var(--muted);
    font-size: .61rem
  }

  #view-wallet .provider-secure {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #94dbaa;
    font-size: .62rem;
    font-weight: 750
  }

  #view-wallet .provider-secure .icon {
    width: 14px;
    height: 14px
  }

  #view-wallet .subsection {
    margin-top: 16px
  }

  #view-wallet .subheading {
    margin-bottom: 9px
  }

  #view-wallet .amount-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 7px
  }

  #view-wallet .amount-preset {
    min-height: 43px;
    padding: 6px 5px;
    border: 1px solid #303748;
    border-radius: 9px;
    background: #111620;
    color: #dbe0e7;
    font-size: .7rem;
    font-weight: 650;
    cursor: pointer;
    transition: border-color .15s, background .15s, color .15s
  }

  #view-wallet .amount-preset:hover {
    border-color: #8a641c;
    color: #fff
  }

  #view-wallet .amount-preset[aria-pressed="true"] {
    border-color: var(--gold);
    background: #ffb8171a;
    color: #ffd66f;
    box-shadow: inset 0 0 0 1px #ffb81725
  }

  #view-wallet .amount-field,
  .amount-field {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
  }

  #view-wallet .amount-field input,
  .amount-field input {
    width: 100%;
    height: 48px;
    padding: 0 48px 0 14px;
    border: 1px solid #343b4b;
    border-radius: 10px;
    background: #0e121b;
    color: #fff;
    font-size: 16px;
    font-weight: 800;
    appearance: textfield;
  }

  #view-wallet .amount-field input::-webkit-inner-spin-button,
  #view-wallet .amount-field input::-webkit-outer-spin-button,
  .amount-field input::-webkit-inner-spin-button,
  .amount-field input::-webkit-outer-spin-button {
    margin: 0;
    appearance: none;
  }

  #view-wallet .amount-field input::placeholder,
  .amount-field input::placeholder {
    color: #697584;
    font-weight: 650;
  }

  #view-wallet .amount-field input:focus,
  .amount-field input:focus {
    outline: none !important;
    border-color: #d2910d;
    box-shadow: 0 0 0 2px rgba(210, 145, 13, 0.2);
  }

  #view-wallet .currency,
  .amount-field .currency {
    position: absolute;
    top: 50%;
    right: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-left: 1px solid #303748;
    color: #b8c1ce;
    font-size: .95rem;
    font-weight: 700;
    line-height: 1;
    transform: translateY(-50%);
    pointer-events: none;
  }

  #view-wallet .amount-hint {
    margin: 6px 1px 0;
    color: #778393;
    font-size: .63rem
  }

  #view-wallet .form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 11px
  }

  #view-wallet .field {
    display: grid;
    gap: 5px;
    min-width: 0
  }

  #view-wallet .field.full {
    grid-column: 1/-1
  }

  #view-wallet .field label {
    color: #b5bfcb;
    font-size: .67rem;
    font-weight: 750
  }

  #view-wallet .field input {
    width: 100%;
    height: 42px;
    min-width: 0;
    padding: 0 11px;
    border: 1px solid #303748;
    border-radius: 8px;
    background: #0e121b;
    color: #f3f5f7;
    font-size: 16px;
  }

  #view-wallet .field input:focus {
    outline: none !important;
    border-color: #d2910d;
    box-shadow: 0 0 0 2px rgba(210, 145, 13, 0.2);
  }

  #view-wallet .field input::placeholder {
    color: #697584
  }

  #view-wallet .card-brand-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 7px;
    margin-top: 10px;
    color: #8e9aa9;
    font-size: .63rem
  }

  #view-wallet .card-brand-row span {
    padding: 4px 7px;
    border: 1px solid #303748;
    border-radius: 5px;
    background: #111620;
    color: #dce2e9;
    font-size: .61rem;
    font-weight: 650;
    line-height: 1
  }

  #view-wallet .card-autofill-hint {
    margin: 8px 0 0;
    color: #8390a0;
    font-size: .59rem;
    line-height: 1.45
  }

  #view-wallet .card-details-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.12fr) minmax(235px, .88fr);
    align-items: start;
    gap: 16px
  }

  #view-wallet .card-preview-column {
    grid-column: 2;
    grid-row: 1;
    min-width: 0
  }

  #view-wallet .card-fields {
    grid-column: 1;
    grid-row: 1;
    min-width: 0
  }

  #view-wallet .bank-card-preview {
    position: relative;
    width: 100%;
    aspect-ratio: 1.586/1;
    max-width: 360px;
    margin: 0 auto;
    perspective: 1000px
  }

  #view-wallet .bank-card-inner {
    position: absolute;
    inset: 0;
    transform-style: preserve-3d;
    transition: transform .72s cubic-bezier(.2, .7, .2, 1)
  }

  #view-wallet .bank-card-preview.is-back .bank-card-inner {
    transform: rotateY(180deg)
  }

  #view-wallet .card-face {
    position: absolute;
    inset: 0;
    overflow: hidden;
    border: 1px solid #ffffff20;
    border-radius: 15px;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    box-shadow: 0 16px 36px #0007
  }

  #view-wallet .card-front {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: clamp(15px, 2vw, 21px);
    background: radial-gradient(ellipse at 90% 0%, #f8b41f55, transparent 45%), radial-gradient(ellipse at 0% 100%, #6142a64a, transparent 48%), linear-gradient(135deg, #202532, #11141c 66%, #25201a);
    color: #f5f6f8
  }

  #view-wallet .card-front:after {
    position: absolute;
    right: -28px;
    bottom: -76px;
    width: 190px;
    height: 190px;
    border: 1px solid #ffffff12;
    border-radius: 50%;
    content: "";
    box-shadow: 0 0 0 20px #ffffff05, 0 0 0 42px #ffffff03;
    pointer-events: none
  }

  #view-wallet .card-chip {
    position: relative;
    z-index: 1;
    width: 36px;
    height: 28px;
    border: 1px solid #e3c373aa;
    border-radius: 6px;
    background: linear-gradient(135deg, #f5d987, #b48739);
    box-shadow: inset 0 0 0 1px #6e5325;
    overflow: hidden
  }

  #view-wallet .card-chip:before,
  #view-wallet .card-chip:after {
    position: absolute;
    content: "";
    background: #82632d88
  }

  #view-wallet .card-chip:before {
    left: 50%;
    top: 0;
    bottom: 0;
    width: 1px
  }

  #view-wallet .card-chip:after {
    left: 0;
    right: 0;
    top: 50%;
    height: 1px
  }

  #view-wallet .card-chip span {
    position: absolute;
    inset: 5px 11px;
    border: 1px solid #82632d88;
    border-radius: 3px
  }

  #view-wallet .preview-number {
    position: relative;
    z-index: 1;
    margin-top: auto;
    margin-bottom: 13px;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: clamp(.72rem, 1.15vw, .96rem);
    font-weight: 750;
    letter-spacing: .085em;
    white-space: nowrap
  }

  #view-wallet .card-front-bottom {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 10px
  }

  #view-wallet .card-front-bottom span {
    display: grid;
    gap: 3px;
    min-width: 0
  }

  #view-wallet .card-front-bottom small {
    color: #aeb8c4;
    font-size: .45rem;
    font-weight: 700;
    letter-spacing: .1em
  }

  #view-wallet .card-front-bottom strong {
    overflow: hidden;
    color: #fff;
    font-size: .59rem;
    font-weight: 650;
    letter-spacing: .055em;
    text-overflow: ellipsis;
    white-space: nowrap
  }

  #view-wallet .card-front-bottom .preview-expiry {
    flex: 0 0 auto
  }

  #view-wallet .card-back {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    transform: rotateY(180deg);
    background: linear-gradient(145deg, #252a35, #10131b 68%, #201b17);
    color: #eef1f4
  }

  #view-wallet .magnetic-stripe {
    height: 24%;
    margin-top: 11%;
    background: linear-gradient(90deg, #07090d, #151820 45%, #07090d)
  }

  #view-wallet .card-back-label {
    margin: 13px 17px 5px;
    color: #9da8b6;
    font-size: .45rem;
    font-weight: 750;
    letter-spacing: .09em
  }

  #view-wallet .signature-strip {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    height: 25px;
    margin: 0 17px;
    padding: 0 8px;
    border-radius: 4px;
    background: repeating-linear-gradient(-8deg, #e5e6e3, #e5e6e3 5px, #c9cbc9 5px, #c9cbc9 7px);
    color: #101218
  }

  #view-wallet .signature-strip span {
    display: grid;
    place-items: center;
    min-width: 45px;
    height: 22px;
    border-radius: 3px;
    background: #fafafa;
    color: #181d2a;
    font-family: ui-monospace, monospace;
    font-size: .68rem;
    font-style: italic;
    font-weight: 900;
    letter-spacing: .08em
  }

  #view-wallet .card-back-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: auto 17px 12px;
    color: #aeb7c3;
    font-size: .48rem;
    font-weight: 700;
    letter-spacing: .07em
  }

  #view-wallet .card-brand-row {
    margin-top: 11px
  }

  @media(max-width:1120px) {
    #view-wallet .card-details-layout {
      grid-template-columns: 1fr
    }

    #view-wallet .card-preview-column {
      grid-column: 1;
      grid-row: 1
    }

    #view-wallet .card-fields {
      grid-column: 1;
      grid-row: 2
    }

    #view-wallet .bank-card-preview {
      max-width: 360px
    }
  }

  @media(max-width:900px) {
    #view-wallet .card-details-layout {
      grid-template-columns: minmax(0, 1.12fr) minmax(220px, .88fr)
    }

    #view-wallet .card-preview-column {
      grid-column: 2;
      grid-row: 1
    }

    #view-wallet .card-fields {
      grid-column: 1;
      grid-row: 1
    }

    #view-wallet .bank-card-preview {
      max-width: none
    }
  }

  #view-wallet .payment-summary {
    margin-top: 16px;
    overflow: hidden;
    border: 1px solid #353c4c;
    border-radius: 11px;
    background: #121621
  }

  #view-wallet .summary-head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 11px 13px;
    border-bottom: 1px solid #ffffff10;
    background: #ffffff04;
    font-size: .74rem;
    font-weight: 650
  }

  #view-wallet .summary-head .icon {
    width: 15px;
    height: 15px;
    color: var(--gold)
  }

  #view-wallet .summary-rows {
    padding: 7px 13px
  }

  #view-wallet .summary-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) max-content;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    color: #9ea9b7;
    font-size: .69rem;
    line-height: 1.4
  }

  #view-wallet .summary-row span {
    min-width: 0
  }

  #view-wallet .summary-row strong {
    display: block;
    color: #e8ecf1;
    font-size: .73rem;
    font-variant-numeric: tabular-nums;
    font-weight: 800;
    text-align: right;
    white-space: nowrap
  }

  #view-wallet .summary-row .commission {
    color: #ff8665
  }

  #view-wallet .summary-total {
    display: grid;
    grid-template-columns: minmax(0, 1fr) max-content;
    align-items: center;
    gap: 12px;
    margin: 0 13px;
    padding: 11px 0 13px;
    border-top: 1px solid #ffffff13;
    color: #edf0f4;
    font-size: .78rem;
    font-weight: 650;
    line-height: 1.35
  }

  #view-wallet .summary-total strong {
    color: #ffc12c;
    font-size: 1rem;
    font-variant-numeric: tabular-nums;
    text-align: right;
    white-space: nowrap
  }

  #view-wallet .wallet-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    min-height: 49px;
    margin-top: 13px;
    border: 0;
    border-radius: 10px;
    background: linear-gradient(105deg, #ffc222, #ff9700);
    color: #17130a;
    font-size: .81rem;
    font-weight: 900;
    cursor: pointer;
    box-shadow: 0 9px 22px #ff9a001b;
    transition: filter .18s, transform .18s
  }

  #view-wallet .wallet-submit:hover {
    filter: brightness(1.06);
    transform: translateY(-1px)
  }

  #view-wallet .wallet-submit .icon {
    width: 17px;
    height: 17px;
    stroke-width: 2.2
  }

  #view-wallet .action-note {
    min-height: 1.25em;
    margin: 7px 0 0;
    color: #aab5c3;
    font-size: .67rem;
    text-align: center
  }

  #view-wallet .phone-verified {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 11px 12px;
    border: 1px solid #303748;
    border-radius: 10px;
    background: #111620
  }

  #view-wallet .phone-verified label {
    display: block;
    color: #9da8b6;
    font-size: .64rem;
    font-weight: 700
  }

  #view-wallet .phone-verified strong {
    display: block;
    margin-top: 2px;
    color: #f1f4f7;
    font-size: .8rem;
    letter-spacing: .02em
  }

  #view-wallet .verified-badge {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #57d184;
    font-size: .64rem;
    font-weight: 800
  }

  #view-wallet .verified-badge .icon {
    width: 14px;
    height: 14px
  }

  #view-wallet .bank-picker {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 7px
  }

  #view-wallet .bank-choice {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 41px;
    padding: 7px;
    border: 1px solid #303748;
    border-radius: 8px;
    background: #10141e;
    color: #cbd2dc;
    font-size: .66rem;
    font-weight: 800;
    text-align: center;
    cursor: pointer
  }

  #view-wallet .bank-choice[aria-pressed="true"] {
    border-color: #d2910d;
    background: #ffb81714;
    color: #fff
  }

  #view-wallet .transfer-ref {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 10px;
    margin-top: 13px;
    padding: 11px 12px;
    border: 1px solid #55451f;
    border-radius: 10px;
    background: linear-gradient(110deg, #2a251a, #171b25)
  }

  #view-wallet .transfer-ref strong {
    display: block;
    color: #ffcf59;
    font-size: .73rem
  }

  #view-wallet .transfer-ref p {
    margin: 2px 0 0;
    color: #a9b3c0;
    font-size: .64rem;
    line-height: 1.45
  }

  #view-wallet .ref-control {
    display: flex;
    align-items: center;
    gap: 7px
  }

  #view-wallet .ref-control code {
    padding: 7px 9px;
    border: 1px solid #40434a;
    border-radius: 7px;
    background: #0d1017;
    color: #fff;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: .72rem;
    font-weight: 650;
    letter-spacing: .06em;
    white-space: nowrap
  }

  #view-wallet .copy-btn {
    display: grid;
    place-items: center;
    width: 32px;
    height: 32px;
    border: 1px solid #3a4152;
    border-radius: 7px;
    background: #202736;
    color: #dbe2e9;
    cursor: pointer
  }

  #view-wallet .copy-btn:hover {
    border-color: #ffb817;
    color: #ffcf59
  }

  #view-wallet .copy-btn .icon {
    width: 14px;
    height: 14px
  }

  #view-wallet .transfer-stage {
    position: relative !important;
    min-width: 0;
  }

  #view-wallet .transfer-content {
    transition: filter .2s, opacity .2s;
  }

  #view-wallet .transfer-stage.is-locked .transfer-content {
    filter: blur(5px);
    opacity: .48;
    user-select: none;
  }

  #view-wallet .transfer-stage {
    position: relative;
  }

  #view-wallet .transfer-gate {
    display: none !important;
    position: absolute;
    inset: 0;
    z-index: 10;
    align-items: center;
    justify-content: center;
    padding: 16px;
    border-radius: 12px;
    background: rgba(15, 19, 26, 0.78);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    pointer-events: auto;
  }

  #view-wallet .transfer-stage.is-locked .transfer-gate {
    display: flex !important;
  }

  #view-wallet .transfer-stage:not(.is-locked) .transfer-gate,
  #view-wallet .payment-pane[hidden] .transfer-gate {
    display: none !important;
  }
  #view-wallet .transfer-gate-card {
    display: flex;
    align-items: center;
    gap: 12px;
    width: min(100%, 620px);
    padding: 15px 16px;
    border: 1px solid #514322;
    border-radius: 10px;
    background: #1d1f24;
    box-shadow: 0 12px 30px #0006
  }

  #view-wallet .transfer-gate-icon {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    flex: 0 0 auto;
    border: 1px solid #b17a10;
    border-radius: 50%;
    background: #ffb8171a;
    color: #ffc52d
  }

  #view-wallet .transfer-gate-icon .icon {
    width: 17px;
    height: 17px
  }

  #view-wallet .transfer-gate-copy {
    min-width: 0;
    flex: 1
  }

  #view-wallet .transfer-gate-copy strong {
    display: block;
    color: #f4c653;
    font-size: .76rem
  }

  #view-wallet .transfer-gate-copy p {
    margin: 3px 0 0;
    color: #c0c5cc;
    font-size: .68rem;
    line-height: 1.45
  }

  #view-wallet .transfer-reveal {
    flex: 0 0 auto;
    min-height: 37px;
    padding: 0 13px;
    border: 0;
    border-radius: 8px;
    background: linear-gradient(105deg, #ffc222, #ff9700);
    color: #17130a;
    font-size: .68rem;
    font-weight: 900;
    cursor: pointer
  }

  #view-wallet .transfer-reveal:hover {
    filter: brightness(1.06)
  }


  #view-wallet .amount-grid[data-presets="mobile"] {
    row-gap: 13px
  }

  #view-wallet .popular-preset {
    position: relative
  }

  #view-wallet .popular-preset:after {
    position: absolute;
    top: -9px;
    right: 3px;
    padding: 2px 5px;
    border: 1px solid #8b681d;
    border-radius: 999px;
    background: #302718;
    color: #ffd66f;
    content: "Çok tercih edilen";
    font-size: .47rem;
    font-weight: 650;
    line-height: 1.25;
    white-space: nowrap
  }

  #view-wallet .bank-details {
    margin-top: 12px;
    padding: 0 12px 10px;
    border: 1px solid #303748;
    border-radius: 10px;
    background: #0f131c
  }

  #view-wallet .bank-details-head {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 11px 0;
    border-bottom: 1px solid #ffffff12
  }

  #view-wallet .bank-logo-mini {
    display: grid;
    place-items: center;
    width: 29px;
    height: 29px;
    border: 1px solid #ffb81730;
    border-radius: 8px;
    background: #ffb81712;
    color: #ffd15b;
    font-size: .58rem;
    font-weight: 900
  }

  #view-wallet .bank-details-head strong {
    font-size: .73rem
  }

  #view-wallet .bank-row {
    display: grid;
    grid-template-columns: 100px minmax(0, 1fr) auto;
    align-items: center;
    gap: 8px;
    min-height: 38px;
    border-bottom: 1px solid #ffffff0c
  }

  #view-wallet .bank-row:last-child {
    border-bottom: 0
  }

  #view-wallet .bank-label {
    color: #8491a0;
    font-size: .64rem;
    font-weight: 700
  }

  #view-wallet .bank-value {
    overflow: hidden;
    color: #e4e9ef;
    font-size: .68rem;
    font-weight: 750;
    text-overflow: ellipsis;
    white-space: nowrap
  }

  #view-wallet .bank-demo-note {
    margin: 8px 0 1px;
    color: #758191;
    font-size: .59rem
  }

  #view-wallet .wallet-safe-note {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-top: 12px;
    color: #8995a4;
    font-size: .64rem
  }

  #view-wallet .wallet-safe-note .icon {
    width: 15px;
    height: 15px;
    color: #72cf8d
  }

  #view-wallet .toast {
    position: fixed;
    top: 24px;
    right: 24px;
    bottom: auto;
    z-index: 999999;
    max-width: min(420px, calc(100vw - 32px));
    padding: 14px 18px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-left: 4px solid #c79a3e;
    border-radius: 12px;
    background: rgba(18, 22, 28, 0.96);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    color: #ffffff;
    font-size: .84rem;
    font-weight: 500;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.04);
    opacity: 0;
    transform: translateY(-16px);
    pointer-events: none;
    transition: opacity .25s ease, transform .25s ease;
  }

  #view-wallet .toast.show {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
  }

  @media(max-width:1120px) {
    #view-wallet .wallet-shell {
      width: min(100% - 36px, var(--max));
      grid-template-columns: 225px minmax(0, 1fr);
      gap: 18px
    }

    #view-wallet .topup-layout {
      grid-template-columns: 218px minmax(0, 1fr);
      gap: 16px
    }

    #view-wallet .balance-card {
      padding: 15px 13px
    }

    #view-wallet .balance-value {
      font-size: 1.08rem
    }

    #view-wallet .amount-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr))
    }
  }

  @media(max-width:900px) {
    #view-wallet .wallet-shell {
      grid-template-columns: 1fr;
      margin-top: 18px
    }

    #view-wallet .profile-sidebar {
      position: static
    }

    #view-wallet .profile-user-card {
      cursor: pointer;
      margin-bottom: 0
    }

    #view-wallet .profile-mobile-arrow {
      display: block
    }

    #view-wallet .profile-menus-wrapper {
      display: none;
      flex-direction: column;
      padding: 10px 1px 2px
    }

    #view-wallet .profile-menus-wrapper.show-menus {
      display: flex
    }

    #view-wallet .profile-menu-group {
      margin-top: 13px
    }

    #view-wallet .profile-content {
      gap: 15px
    }

    #view-wallet .topup-layout {
      grid-template-columns: 1fr
    }

    #view-wallet .method-list {
      grid-template-columns: repeat(3, minmax(0, 1fr))
    }

    #view-wallet .method-option {
      grid-template-columns: 32px minmax(0, 1fr) 15px;
      min-height: 58px;
      gap: 7px;
      padding: 8px
    }

    #view-wallet .method-icon {
      width: 32px;
      height: 32px
    }

    #view-wallet .step-line {
      width: clamp(8px, 7vw, 38px)
    }
  }

  @media(max-width:620px) {
    #view-wallet .transfer-gate {
      padding: 9px
    }

    #view-wallet .transfer-gate-card {
      display: grid;
      grid-template-columns: 30px minmax(0, 1fr);
      gap: 9px;
      padding: 12px
    }

    #view-wallet .transfer-gate-icon {
      width: 29px;
      height: 29px
    }

    #view-wallet .transfer-reveal {
      grid-column: 2;
      justify-self: start;
      min-height: 34px
    }

    #view-wallet .transfer-gate-copy p {
      font-size: .62rem
    }
  }

  @media(max-width:620px) {
    #view-wallet .profile-content {
      min-width: 0;
      gap: 12px
    }

    #view-wallet .profile-sidebar {
      padding: 6px
    }

    #view-wallet .wallet-topup {
      min-width: 0;
      padding: 13px;
      border-radius: 14px
    }

    #view-wallet .topup-head {
      align-items: flex-start;
      flex-direction: column;
      margin-bottom: 14px
    }

    #view-wallet .topup-head .stepper {
      width: 100%;
      justify-content: space-between;
      gap: 3px;
      font-size: .55rem
    }

    #view-wallet .step {
      gap: 4px
    }

    #view-wallet .step-line {
      width: clamp(5px, 2vw, 9px)
    }

    #view-wallet .method-list {
      grid-template-columns: 1fr
    }

    #view-wallet .method-option {
      min-height: 50px;
      grid-template-columns: 36px minmax(0, 1fr) 16px;
      padding: 7px 9px
    }

    #view-wallet .method-name {
      font-size: .78rem
    }

    #view-wallet .method-desc {
      font-size: .67rem
    }

    #view-wallet .amount-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
      column-gap: 7px
    }

    #view-wallet .amount-preset {
      min-height: 48px;
      padding: 7px 4px;
      font-size: .74rem
    }

    #view-wallet .amount-field input {
      height: 50px;
      padding-right: 48px;
      font-size: 16px
    }

    #view-wallet .currency {
      right: 9px;
      width: 27px;
      height: 27px
    }

    #view-wallet .card-details-layout {
      grid-template-columns: minmax(0, 1fr);
      gap: 14px
    }

    #view-wallet .card-preview-column,
    #view-wallet .card-fields {
      grid-column: 1;
      grid-row: auto
    }

    #view-wallet .bank-card-preview {
      width: min(100%, 360px);
      max-width: 360px
    }

    #view-wallet .card-front {
      padding: 15px
    }

    #view-wallet .preview-number {
      font-size: clamp(.72rem, 3.4vw, .9rem);
      letter-spacing: .055em
    }

    #view-wallet .card-front-bottom strong {
      font-size: .56rem
    }

    #view-wallet .card-autofill-hint {
      font-size: .68rem
    }

    #view-wallet .form-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 9px
    }

    #view-wallet .field input {
      height: 48px;
      padding: 0 9px;
      font-size: 16px
    }

    #view-wallet .field label {
      font-size: .69rem
    }

    #view-wallet .card-brand-row {
      gap: 5px
    }

    #view-wallet .card-brand-row span {
      font-size: .58rem
    }

    #view-wallet .bank-picker {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 7px
    }

    #view-wallet .bank-choice {
      min-height: 46px;
      padding: 7px 5px;
      font-size: .68rem
    }

    #view-wallet .transfer-ref {
      grid-template-columns: minmax(0, 1fr);
      gap: 8px;
      padding: 11px
    }

    #view-wallet .ref-control {
      justify-content: flex-start
    }

    #view-wallet .bank-row {
      grid-template-columns: 74px minmax(0, 1fr) 30px;
      gap: 6px
    }

    #view-wallet .bank-value {
      font-size: .62rem
    }

    #view-wallet .copy-btn {
      width: 30px;
      height: 30px
    }

    #view-wallet .wallet-submit {
      min-height: 52px;
      padding: 8px 12px;
      font-size: .76rem
    }

    #view-wallet .summary-row {
      font-size: .68rem
    }

    #view-wallet .wallet-safe-note {
      align-items: flex-start
    }

    #view-wallet .wallet-safe-note .icon {
      margin-top: 1px
    }
  }

  @media(max-width:620px) {
    #view-wallet .payment-summary {
      margin-top: 13px;
      border-radius: 10px
    }

    #view-wallet .summary-head {
      padding: 11px 12px
    }

    #view-wallet .summary-rows {
      padding: 6px 12px
    }

    #view-wallet .summary-row {
      grid-template-columns: minmax(0, 1fr) max-content;
      gap: 8px;
      padding: 8px 0;
      font-size: .69rem
    }

    #view-wallet .summary-row strong {
      font-size: .74rem
    }

    #view-wallet .summary-total {
      grid-template-columns: minmax(0, 1fr) max-content;
      gap: 8px;
      margin: 0 12px;
      padding: 11px 0 13px;
      font-size: .77rem
    }

    #view-wallet .summary-total strong {
      font-size: .94rem
    }
  }

  @media(prefers-reduced-motion:reduce) {

    #view-wallet *,
    #view-wallet *::before,
    #view-wallet *::after {
      scroll-behavior: auto !important;
      animation-duration: .01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: .01ms !important
    }
  }

  /* Keep the wallet visually aligned with the account panel and prevent balance cards from collapsing on phones. */
  #view-wallet .wallet-info {
    border-color: #303643;
    background: linear-gradient(145deg, #171c27, #131720)
  }

  #view-wallet .wallet-info strong {
    color: #e7c75e
  }

  #view-wallet .balance-card.available,
  #view-wallet .balance-card.withdrawable,
  #view-wallet .balance-card.total {
    border-color: #303643;
    background: linear-gradient(145deg, #171c27, #12161f)
  }

  #view-wallet .balance-card.available {
    --glow: #17c66b14
  }

  #view-wallet .balance-card.withdrawable {
    --glow: #ff8a2814
  }

  #view-wallet .balance-card.total {
    --glow: #ffb81714
  }

  #view-wallet .wallet-topup {
    border-color: #303643;
    background: linear-gradient(145deg, #171c27, #12161f);
    box-shadow: 0 10px 28px #0002
  }

  @media(max-width:850px) {
    #view-wallet .balance-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr))
    }
  }

  @media(max-width:620px) {
    #view-wallet .wallet-info {
      align-items: flex-start;
      padding: 12px
    }

    #view-wallet .balance-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 9px
    }

    #view-wallet .balance-card {
      display: grid;
      grid-template-columns: 34px minmax(0, 1fr);
      align-items: center;
      gap: 7px;
      min-height: 84px;
      padding: 10px 9px;
      border-radius: 12px
    }

    #view-wallet .balance-icon {
      width: 32px;
      height: 32px;
      border-radius: 9px
    }

    #view-wallet .balance-icon .icon {
      width: 17px;
      height: 17px
    }

    #view-wallet .balance-copy {
      min-width: 0
    }

    #view-wallet .balance-label {
      font-size: .57rem;
      line-height: 1.25;
      overflow-wrap: anywhere
    }

    #view-wallet .balance-value {
      margin-top: 4px;
      font-size: .92rem;
      letter-spacing: -.035em
    }

    #view-wallet .balance-value small {
      font-size: .66rem
    }

    #view-wallet .balance-badge {
      position: static;
      grid-column: 2;
      justify-self: start;
      margin-top: 0;
      padding: 2px 6px;
      font-size: .49rem
    }

    #view-wallet .balance-card.total {
      grid-column: 1/-1;
      grid-template-columns: 34px minmax(0, 1fr) max-content;
      min-height: 68px
    }

    #view-wallet .balance-card.total .balance-badge {
      grid-column: 3;
      grid-row: 1;
      align-self: center
    }

    #view-wallet .balance-card.total .balance-value {
      font-size: 1rem
    }
  }
</style>
<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">
  <symbol id="i-grid" viewBox="0 0 24 24">
    <rect x="4" y="4" width="6" height="6" rx="1" />
    <rect x="14" y="4" width="6" height="6" rx="1" />
    <rect x="4" y="14" width="6" height="6" rx="1" />
    <rect x="14" y="14" width="6" height="6" rx="1" />
  </symbol>
  <symbol id="i-bag" viewBox="0 0 24 24">
    <path d="M5 8h14l1 12H4L5 8Z" />
    <path d="M9 9V6a3 3 0 0 1 6 0v3" />
  </symbol>
  <symbol id="i-history" viewBox="0 0 24 24">
    <path d="M3 12a9 9 0 1 0 2.6-6.4L3 8" />
    <path d="M3 3v5h5M12 7v5l3 2" />
  </symbol>
  <symbol id="i-headset" viewBox="0 0 24 24">
    <path d="M4 13v-2a8 8 0 0 1 16 0v2" />
    <rect x="3" y="12" width="4" height="7" rx="2" />
    <rect x="17" y="12" width="4" height="7" rx="2" />
    <path d="M17 19c-1 2-3 2-5 2" />
  </symbol>
  <symbol id="i-wallet" viewBox="0 0 24 24">
    <rect x="3" y="5" width="18" height="15" rx="2" />
    <path d="M3 8h18M16 14h2" />
  </symbol>
  <symbol id="i-cash" viewBox="0 0 24 24">
    <rect x="3" y="6" width="18" height="13" rx="2" />
    <path d="M7 10h.01M17 15h.01M12 9v7m2-5c0-1-1-2-2-2s-2 1-2 2 1 1 2 2 2 1 2 2-1 2-2 2-2-1-2-2" />
  </symbol>
  <symbol id="i-card" viewBox="0 0 24 24">
    <rect x="3" y="5" width="18" height="14" rx="2" />
    <path d="M3 10h18M7 15h3" />
  </symbol>
  <symbol id="i-settings" viewBox="0 0 24 24">
    <path d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z" />
    <path
      d="m19.4 15 .1.1 1.1 1.8-1.8 3.1-2.1-.5-.2.1-1.1 1.3h-3.6l-.7-1.9-.2-.1-2-.5-1.8 1-3.1-1.8.5-2.1-.1-.2-1.3-1.1v-3.6l1.9-.7.1-.2.5-2-1-1.8L6.4 2.8l2.1.5.2-.1 1.1-1.3h3.6l.7 1.9.2.1 2 .5 1.8-1 3.1 1.8-.5 2.1.1.2 1.3 1.1v3.6l-1.9.7-.1.2Z" />
  </symbol>
  <symbol id="i-exit" viewBox="0 0 24 24">
    <path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5M15 16l4-4-4-4M19 12H9" />
  </symbol>
  <symbol id="i-star" viewBox="0 0 24 24">
    <path d="m12 3 2.7 5.6 6.2.9-4.5 4.4 1.1 6.2-5.5-2.9-5.5 2.9 1.1-6.2-4.5-4.4 6.2-.9L12 3Z" />
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24">
    <rect x="3" y="5" width="18" height="16" rx="2" />
    <path d="M16 3v4M8 3v4M3 10h18" />
  </symbol>
  <symbol id="i-list" viewBox="0 0 24 24">
    <line x1="8" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    <line x1="8" y1="12" x2="21" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    <line x1="8" y1="18" x2="21" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    <circle cx="3.5" cy="6" r="1.25" fill="currentColor" />
    <circle cx="3.5" cy="12" r="1.25" fill="currentColor" />
    <circle cx="3.5" cy="18" r="1.25" fill="currentColor" />
  </symbol>
  <symbol id="i-trash" viewBox="0 0 24 24">
    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" />
  </symbol>
  <symbol id="i-save" viewBox="0 0 24 24">
    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z" />
    <polyline points="17 21 17 13 7 13 7 21" />
    <polyline points="7 3 7 8 15 8" />
  </symbol>
  <symbol id="i-filter" viewBox="0 0 24 24">
    <path d="M4 5h16l-6.2 7.1v5.7l-3.6 1.8v-7.5L4 5Z" />
  </symbol>
  <symbol id="i-search" viewBox="0 0 24 24">
    <circle cx="10.8" cy="10.8" r="6.8" />
    <path d="m16 16 4.5 4.5" />
  </symbol>
  <symbol id="i-refresh" viewBox="0 0 24 24">
    <path d="M20 7v5h-5M4 17v-5h5" />
    <path d="M5.6 9A7 7 0 0 1 18 6l2 2M4 16l2 2a7 7 0 0 0 12.4-3" />
  </symbol>
  <symbol id="i-eye" viewBox="0 0 24 24">
    <path d="M2.5 12s3.3-6 9.5-6 9.5 6 9.5 6-3.3 6-9.5 6-9.5-6-9.5-6Z" />
    <circle cx="12" cy="12" r="2.5" />
  </symbol>
  <symbol id="i-clock" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="9" />
    <path d="M12 7v5l3 2" />
  </symbol>
  <symbol id="i-check" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="9" />
    <path d="m8 12 2.5 2.5L16 9" />
  </symbol>
  <symbol id="i-x" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="9" />
    <path d="m9 9 6 6m0-6-6 6" />
  </symbol>
  <symbol id="i-up" viewBox="0 0 24 24">
    <path d="M12 19V5m-6 6 6-6 6 6" />
  </symbol>
  <symbol id="i-down" viewBox="0 0 24 24">
    <path d="M12 5v14m6-6-6 6-6-6" />
  </symbol>
  <symbol id="i-ticket" viewBox="0 0 24 24">
    <path d="M4 6h16v4a2 2 0 0 0 0 4v4H4v-4a2 2 0 0 0 0-4V6Z" />
    <path d="M13 8v2m0 4v2" />
  </symbol>
  <symbol id="i-plus" viewBox="0 0 24 24">
    <path d="M12 5v14M5 12h14" />
  </symbol>
  <symbol id="i-arrow" viewBox="0 0 24 24">
    <path d="M5 12h14m-6-6 6 6-6 6" />
  </symbol>
  <symbol id="i-chevron" viewBox="0 0 24 24">
    <path d="m6 9 6 6 6-6" />
  </symbol>
  <symbol id="i-info" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="9" />
    <path d="M12 11v5m0-8h.01" />
  </symbol>
  <symbol id="i-lock" viewBox="0 0 24 24">
    <rect x="4" y="10" width="16" height="11" rx="2" />
    <path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3" />
  </symbol>
  <symbol id="i-user" viewBox="0 0 24 24">
    <circle cx="12" cy="8" r="4" />
    <path d="M4 21a8 8 0 0 1 16 0" />
  </symbol>
  <symbol id="i-shield" viewBox="0 0 24 24">
    <path d="m12 3 8 3v5c0 5-3.4 8.3-8 10-4.6-1.7-8-5-8-10V6l8-3Z" />
    <path d="m9 12 2 2 4-4" />
  </symbol>
  <symbol id="i-file" viewBox="0 0 24 24">
    <path d="M6 3h8l5 5v13H6z" />
    <path d="M14 3v6h5M9 14h6m-6 3h6" />
  </symbol>
  <symbol id="i-bank" viewBox="0 0 24 24">
    <path d="m3 9 9-6 9 6M5 10v8m4-8v8m6-8v8m4-8v8M3 21h18M2 18h20" />
  </symbol>
  <symbol id="i-mail" viewBox="0 0 24 24">
    <rect x="3" y="5" width="18" height="14" rx="2" />
    <path d="m4 7 8 6 8-6" />
  </symbol>
  <symbol id="i-phone" viewBox="0 0 24 24">
    <rect x="6" y="2.5" width="12" height="19" rx="2" />
    <path d="M10 18.5h4" />
  </symbol>
  <symbol id="i-attach" viewBox="0 0 24 24">
    <path d="m9 12.5 6.9-6.9a3 3 0 0 1 4.2 4.2L10.2 19.7A5 5 0 0 1 3.1 12.6l9.2-9.2" />
  </symbol>
  <symbol id="i-send" viewBox="0 0 24 24">
    <path d="m22 2-7 20-4-9-9-4 20-7ZM22 2 11 13" />
  </symbol>
  <symbol id="i-wallet-check" viewBox="0 0 24 24">
    <rect x="3" y="5" width="18" height="15" rx="2" />
    <path d="M3 8h18m-8 6 2 2 4-4" />
  </symbol>
  <symbol id="i-coins" viewBox="0 0 24 24">
    <ellipse cx="12" cy="6" rx="8" ry="3" />
    <path
      d="M4 6v4c0 1.7 3.6 3 8 3 3.2 0 5.9-.7 7.2-1.8M4 10v4c0 1.7 3.6 3 8 3 1.1 0 2.1-.1 3-.2M4 14v4c0 1.7 3.6 3 8 3 1.1 0 2.1-.1 3-.2" />
    <path d="M20 13v6m-3-3h6" />
  </symbol>
  <symbol id="i-withdraw" viewBox="0 0 24 24">
    <path d="M4 19h16M6 16l3-4 3 2 5-7" />
    <path d="M14 7h3v3" />
    <circle cx="6" cy="7" r="2" />
  </symbol>
  <symbol id="i-mobile" viewBox="0 0 24 24">
    <rect x="6" y="2" width="12" height="20" rx="2" />
    <path d="M11 18h2" />
  </symbol>
  <symbol id="i-receipt" viewBox="0 0 24 24">
    <path d="M5 3h14v18l-3-2-4 2-4-2-3 2V3Z" />
    <path d="M8 8h8M8 12h8M8 16h4" />
  </symbol>
  <symbol id="i-copy" viewBox="0 0 24 24">
    <rect x="8" y="8" width="13" height="13" rx="2" />
    <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3" />
  </symbol>
  <symbol id="i-bolt" viewBox="0 0 24 24">
    <path d="m13 2-9 12h7l-1 8 10-13h-7l1-7Z" />
  </symbol>
  <symbol id="i-edit" viewBox="0 0 24 24">
    <path d="m14 5 5 5M4 20l4.5-1L19 8.5a2.1 2.1 0 0 0-3-3L5.5 16 4 20Z" />
  </symbol>
</svg>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    "use strict";
    var groups = [
      { title: "Genel", items: [["dashboard", "grid", "Kontrol Paneli"], ["orders", "bag", "Siparişlerim"], ["balance", "history", "Bakiye Hareketleri"], ["support", "headset", "Destek Taleplerim"]] },
      { title: "Finans", items: [["wallet", "wallet", "Cüzdan"], ["payments", "cash", "Ödemeler"], ["withdrawals", "card", "Nakit Çekimler"]] },
      { title: "İş ortaklığı", items: [["publisher", "ticket", "Yayıncı başvurusu"]] },
      { title: "Hesap", items: [["settings", "settings", "Ayarlar"], ["logout", "exit", "Çıkış"]] },
      { title: "Yardım", items: [["help", "headset", "Yardım Merkezi"]] }
    ];
    function icon(name) { return '<svg class="icon" aria-hidden="true"><use href="#i-' + name + '"/></svg>' }
    function makeNav() {
      var html = "";
      groups.forEach(function (group) {
        html += '<div class="nav-group-title">' + group.title + '</div><nav class="account-nav" aria-label="' + group.title + ' menüsü">';
        group.items.forEach(function (item) {
          html += '<button class="nav-link" type="button" data-page="' + item[0] + '">' + icon(item[1]) + '<span>' + item[2] + '</span></button>';
        });
        html += '</nav>';
      });
      return html;
    }
    // document.getElementById("account-nav").innerHTML=makeNav();
    var orderExamples = {
      "129": { type: "epin", codes: [] },
      "122": { type: "epin", codes: ["DEMO-KOD-2001"] },
      "121": { type: "topup", fields: [["Kullanıcı ID", "5555555"]] },
      "120": { type: "topup", fields: [["Oyuncu ID", "5555555"], ["Server ID", "EU-1"]] },
      "118": { type: "epin", codes: ["DEMO-KOD-1001", "DEMO-KOD-1002"] },
      "114": { type: "topup", fields: [["Oyuncu ID", "5555555"]] }
    };
    var orderExpanded = false, activeOrderFilter = "all", paymentExpanded = false, activePaymentFilter = "all";
    function rowOrderId(row) { var cell = row.querySelector('[data-label="Sipariş no"] .row-id'); return cell ? cell.textContent.trim().replace("#", "") : "" }
    function paintOrderStatus(row) {
      return; // Disable mock JS overriding backend status
      var id = rowOrderId(row), cell = row.querySelector('[data-label="Durum"] .status');
      if (!cell) return;
      var state = id === "129" ? "payment-pending" : id === "121" ? "processing" : id === "114" ? "cancelled" : row.getAttribute("data-status") === "done" || id === "122" ? "completed" : "processing";
      if (state === "completed") row.setAttribute("data-status", "done");
      row.setAttribute("data-order-state", state);
      cell.classList.remove("done", "refund", "neutral", "processing", "payment-pending");
      cell.classList.add(state === "completed" ? "done" : state === "cancelled" ? "refund" : state);
      cell.textContent = state === "payment-pending" ? "Ödeme bekliyor" : state === "processing" ? "İşlemde" : state === "completed" ? "Tamamlandı" : "İptal edildi";
      var mark = document.createElement("span"); mark.className = "status-mark" + (state === "payment-pending" ? " pending" : state === "processing" ? " processing" : ""); mark.setAttribute("aria-hidden", "true");
      if (state === "completed" || state === "cancelled") { mark.innerHTML = icon(state === "completed" ? "check" : "x") }
      else mark.setAttribute("aria-label", state === "payment-pending" ? "Ödeme bekliyor" : "Sipariş işleniyor");
      cell.prepend(mark);
    }
    function paintPaymentStatuses() {
      return; // Disable mock JS overriding backend status
      var table = document.getElementById("payments-table"); if (!table) return;
      table.querySelectorAll("tbody tr").forEach(function (row, index) {
        if (index >= 4) row.setAttribute("data-payment-extra", "true");
        var cell = row.querySelector('[data-label="Durum"] .status'); if (!cell) return;
        var oldMark = cell.querySelector(".status-mark"); if (oldMark) oldMark.remove();
        var state = row.getAttribute("data-payment-state") || row.getAttribute("data-status"), label = state === "processing" ? "İşlemde" : state === "waiting" ? "Beklemede" : state === "done" ? "Tamamlandı" : state === "cancelled" ? "İptal edildi" : "Sipariş iadesi";
        cell.classList.remove("done", "refund", "neutral", "processing", "payment-pending");
        if (state === "done") cell.classList.add("done");
        else if (state === "waiting") cell.classList.add("payment-pending");
        else if (state === "processing") cell.classList.add("processing");
        else if (state === "cancelled") cell.classList.add("neutral");
        else if (state === "refund") cell.classList.add("refund");
        var mark = document.createElement("span"); mark.className = "status-mark" + (state === "waiting" ? " pending" : state === "processing" ? " processing" : ""); mark.setAttribute("aria-hidden", "true");
        if (state === "done") mark.innerHTML = icon("check");
        else if (state === "cancelled") mark.innerHTML = icon("x");
        else if (state === "refund") mark.innerHTML = icon("history");
        cell.textContent = label; cell.prepend(mark);
      });
    }
    // function updateOrderDetail(row){
    //   if(!row)return;
    //   var id=rowOrderId(row),example=orderExamples[id]||{type:"topup",fields:[["Kullanıcı ID","—"]]},productCell=row.querySelector('[data-label="Ürün"]'),product=productCell.querySelector(".product-cell"),name=product.querySelector(".product-name").textContent.trim(),qty=row.querySelector('[data-label="Adet"]').textContent.trim(),amount=row.querySelector('[data-label="Tutar"]').textContent.trim(),date=row.querySelector('[data-label="Tarih"]').textContent.trim(),status=row.querySelector('[data-label="Durum"] .status'),thumb=product.querySelector(".product-thumb").cloneNode(true),state=row.getAttribute("data-order-state");
    //   document.getElementById("detail-order-title").textContent="Sipariş #"+id;
    //   document.getElementById("detail-order-number").textContent="#"+id;
    //   document.getElementById("detail-order-product").textContent=name+" · "+qty;
    //   document.getElementById("detail-order-amount").textContent=amount;
    //   document.getElementById("detail-order-date").textContent=date;
    //   document.getElementById("detail-order-payment").textContent="Havale / EFT";
    //   document.getElementById("detail-order-type").textContent=example.type==="epin"?"E-pin":"Top-up";
    //   var image=document.getElementById("detail-order-image");image.innerHTML="";thumb.classList.add("order-detail-thumb");image.appendChild(thumb);
    //   var detailStatus=document.getElementById("detail-order-status");detailStatus.className=status.className;detailStatus.innerHTML=status.innerHTML;
    //   var deliveryTitle=document.getElementById("detail-delivery-title"),rows=document.getElementById("detail-delivery-rows");rows.innerHTML="";
    //   function addDelivery(label,value,isCode){var line=document.createElement("div");line.className="delivery-row";var key=document.createElement("span");key.className="delivery-label";key.textContent=label;var val=document.createElement("strong");val.className="delivery-value"+(isCode?" code":"");val.textContent=value;line.append(key,val);rows.appendChild(line)}
    //   if(example.type==="topup"){
    //     deliveryTitle.textContent="Kullanıcı bilgileri";
    //     (example.fields||[]).forEach(function(field){addDelivery(field[0],field[1],false)});
    //   }else{
    //     deliveryTitle.textContent="E-pin teslimatı";
    //     if(example.codes&&example.codes.length){example.codes.forEach(function(code,index){addDelivery(example.codes.length===1?"E-pin teslim edildi":"E-pin kodu "+(index+1),code,true)})}
    //     else addDelivery("Teslimat durumu",state==="cancelled"?"Sipariş iptal edildi":"Ödeme onayı bekleniyor",false);
    //   }
    //   var message=state==="payment-pending"?"Ödeme bekleniyor. Ödeme onaylandığında siparişiniz işleme alınır.":state==="processing"?"Siparişiniz işleniyor. Teslimat tamamlandığında bilgileriniz burada görüntülenir.":state==="completed"?(example.type==="epin"?"E-pin kodları siparişinize özel olarak teslim edildi.":"Top-up işlemi tamamlandı; yükleme bilgileri sipariş kaydınızda yer alır."):"Sipariş iptal edildi. İade durumunu ödeme geçmişinizden takip edebilirsiniz.";
    //   document.querySelector("#detail-order-note span").textContent=message;
    // }
    function applyOrderVisibility() {
      // Handled by backend PHP
    }
    function applyPaymentVisibility() {
      // Handled by backend PHP
    }
    function buildOrderCards() {
      var table = document.getElementById("orders-table");
      if (!table) return;
      table.querySelectorAll("tbody tr").forEach(function (row, index) {
        paintOrderStatus(row);
        if (row.querySelector(".order-list-cell")) return;
        var cells = row.cells, product = cells[1].querySelector(".product-cell"), name = product.querySelector(".product-name").textContent.trim(), orderId = cells[0].textContent.trim(), quantity = cells[2].textContent.trim(), amount = cells[3].textContent.trim(), date = cells[4].textContent.trim(), status = cells[5].querySelector(".status").cloneNode(true);
        var mobileCell = document.createElement("td"); mobileCell.className = "order-list-cell"; mobileCell.colSpan = cells.length;
        var card = document.createElement("div"); card.className = "order-list-card";
        var top = document.createElement("div"); top.className = "order-list-top";
        var thumb = product.querySelector(".product-thumb").cloneNode(true); thumb.classList.add("order-list-thumb");
        var main = document.createElement("div"); main.className = "order-list-main";
        var title = document.createElement("strong"); title.className = "order-list-name"; title.textContent = name;
        var meta = document.createElement("div"); meta.className = "order-list-meta";
        var id = document.createElement("span"); id.className = "order-list-id"; id.textContent = orderId;
        var separator = document.createElement("span"); separator.className = "order-list-meta-separator"; separator.setAttribute("aria-hidden", "true");
        var count = document.createElement("span"); count.className = "order-list-quantity"; count.textContent = quantity;
        meta.append(id, separator, count); main.append(title, meta);
        var action = document.createElement("button"); action.type = "button"; action.className = "order-list-action"; action.setAttribute("data-order-detail", ""); action.setAttribute("aria-label", orderId + " siparişinin detayını görüntüle"); action.innerHTML = '<svg class="icon" aria-hidden="true"><use href="#i-chevron"/></svg>';
        top.append(thumb, main, action);
        var info = document.createElement("div"); info.className = "order-list-info";
        var orderDate = document.createElement("span"); orderDate.className = "order-list-date"; orderDate.textContent = date;
        var total = document.createElement("strong"); total.className = "order-list-amount"; total.textContent = amount;
        var footer = document.createElement("div"); footer.className = "order-list-footer"; footer.append(status);
        var actionsCell = row.querySelector("td[data-label='İşlem']");
        if (actionsCell) {
          var reviewBtn = actionsCell.querySelector(".review-btn");
          var reviewedBtn = actionsCell.querySelector("button[disabled]");
          var cancelBtn = actionsCell.querySelector(".cancel-order-btn");
          if (reviewBtn) {
            footer.append(reviewBtn.cloneNode(true));
          } else if (reviewedBtn && reviewedBtn.textContent.includes("Değerlendirildi")) {
            footer.append(reviewedBtn.cloneNode(true));
          }
          if (cancelBtn) {
            footer.append(cancelBtn.cloneNode(true));
          }
        }
        card.append(top, info, footer); mobileCell.append(card); row.insertBefore(mobileCell, row.firstChild);
      });
    }
    window.buildOrderCards = buildOrderCards;
    buildOrderCards();
    applyOrderVisibility();
    paintPaymentStatuses();
    applyPaymentVisibility();
    var toast = document.getElementById("toast"), toastTimer = null;
    function notify(message) {
      if (typeof toastr !== "undefined") {
        toastr.success(message);
        return;
      }
      if (!toast) return;
      toast.textContent = message;
      toast.classList.add("show");
      window.clearTimeout(toastTimer);
      toastTimer = window.setTimeout(function () { toast.classList.remove("show") }, 2800);
    }
    function closeMobileMenu() {
      var menu = document.getElementById("account-nav"), toggle = document.getElementById("profile-menu-toggle");
      if (window.matchMedia("(max-width: 850px)").matches) { menu.classList.remove("show-menus"); toggle.setAttribute("aria-expanded", "false") }
    }
    function showPage(page) {
      var views = document.querySelectorAll("[data-view]");
      var found = false;
      views.forEach(function (view) { var active = view.getAttribute("data-view") === page; view.hidden = !active; if (active) found = true });
      if (!found) return;
      document.querySelectorAll(".nav-link").forEach(function (link) {
        var active = link.getAttribute("data-page") === page;
        link.classList.toggle("active", active);
        if (active) link.setAttribute("aria-current", "page"); else link.removeAttribute("aria-current");
      });
      var menu = document.getElementById("account-nav"), toggle = document.getElementById("profile-menu-toggle");
      if (window.matchMedia("(max-width: 850px)").matches) { menu.classList.remove("show-menus"); toggle.setAttribute("aria-expanded", "false") } else { toggle.setAttribute("aria-expanded", "true") }
      window.scrollTo({ top: 0, behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth" });
    }
    document.addEventListener("click", function (event) {
      if (window.matchMedia("(max-width: 850px)").matches && !event.target.closest(".sidebar")) closeMobileMenu();
      var loadMore = event.target.closest("#orders-load-more");
      if (loadMore) { orderExpanded = true; applyOrderVisibility(); return }
      var paymentLoadMore = event.target.closest("#payments-load-more");
      if (paymentLoadMore) { paymentExpanded = true; applyPaymentVisibility(); return }
      var pageButton = event.target.closest("[data-page]");
      if (pageButton) { 
        var href = pageButton.getAttribute("href");
        var next = pageButton.getAttribute("data-page"); 
        if (next === "logout") { 
          event.preventDefault();
          closeMobileMenu(); 
          notify("Çıkış bağlantısı canlı hesap sistemine bağlanacak."); 
          return 
        } 
        if (next === "wallet-link") { 
          event.preventDefault();
          closeMobileMenu(); 
          if (window.location.protocol !== "file:") { 
            window.location.href = "/tr-TR/profile/wallet.html" 
          } else notify("Cüzdan bağlantısı: /tr-TR/profile/wallet.html"); 
          return 
        } 
        var targetView = document.querySelector('[data-view="' + next + '"]');
        if (targetView) {
          event.preventDefault();
          showPage(next);
          return;
        }
        if (href && href !== "#" && !href.startsWith("javascript:")) {
          return;
        }
        event.preventDefault();
        return;
      }
      var toastButton = event.target.closest("[data-toast]");
      if (toastButton) { notify(toastButton.getAttribute("data-toast")); return }
      var settingsTab = event.target.closest("[data-settings-tab]");
      if (settingsTab) {
        var key = settingsTab.getAttribute("data-settings-tab");
        document.querySelectorAll("[data-settings-tab]").forEach(function (button) { button.classList.toggle("active", button === settingsTab) });
        document.querySelectorAll("[data-settings-panel]").forEach(function (panel) { panel.hidden = panel.getAttribute("data-settings-panel") !== key });
        return;
      }
      var withdrawTab = event.target.closest("[data-withdraw-tab]");
      if (withdrawTab) {
        var tab = withdrawTab.getAttribute("data-withdraw-tab");
        document.querySelectorAll("[data-withdraw-tab]").forEach(function (button) { button.classList.toggle("active", button.getAttribute("data-withdraw-tab") === tab) });
        document.querySelectorAll("[data-withdraw-panel]").forEach(function (panel) { panel.hidden = panel.getAttribute("data-withdraw-panel") !== tab });
        var filters = document.querySelector("[data-withdraw-filters]");
        if (filters) {
          filters.hidden = tab !== "requests";
        }
        return;
      }
      var order = event.target.closest("[data-order-detail]");
      if (order) {
        var row = order.closest("tr");
        var detailLink = row.querySelector("td[data-label='İşlem'] a.detail-button");
        if (detailLink) {
          window.location.href = detailLink.href;
        }
        return;
      }
      var tabButton = event.target.closest("[data-filter-tabs] [data-filter]");
      if (tabButton) { filterTable(tabButton); return }
      var quick = event.target.closest(".quick-filter");
      if (quick) { var parent = quick.parentElement; parent.querySelectorAll(".quick-filter").forEach(function (b) { b.classList.toggle("active", b === quick) }); return }
      var clear = event.target.closest("[data-clear-filters]");
      if (clear) { var section = clear.closest(".view"); section.querySelectorAll(".date-control input").forEach(function (input) { input.value = "" }); section.querySelectorAll(".quick-filter").forEach(function (button) { button.classList.remove("active") }); section.querySelectorAll("[data-filter-tabs] [data-filter]").forEach(function (button) { button.classList.toggle("active", button.getAttribute("data-filter") === "all") }); if (section.id === "view-orders") { activeOrderFilter = "all"; applyOrderVisibility() } else if (section.id === "view-payments") { activePaymentFilter = "all"; applyPaymentVisibility() } else section.querySelectorAll("tbody tr").forEach(function (row) { row.hidden = false }); notify("Filtreler temizlendi."); return }
      var radio = event.target.closest(".radio-choice");
      if (radio) { radio.parentElement.querySelectorAll(".radio-choice").forEach(function (choice) { choice.classList.toggle("active", choice === radio); choice.setAttribute("aria-pressed", choice === radio ? "true" : "false") }); return }
    });
    function filterTable(button) {
      var group = button.closest("[data-filter-tabs]").getAttribute("data-filter-tabs"), filter = button.getAttribute("data-filter"), table = document.getElementById(group === "orders" ? "orders-table" : group === "payments" ? "payments-table" : "balance-table");
      button.closest("[data-filter-tabs]").querySelectorAll("[data-filter]").forEach(function (b) { b.classList.toggle("active", b === button) });
      if (!table) return;
      if (group === "orders") { activeOrderFilter = filter; applyOrderVisibility(); return }
      if (group === "payments") { activePaymentFilter = filter; applyPaymentVisibility(); return }
      table.querySelectorAll("tbody tr").forEach(function (row) {
        var rowValue = group === "balance" ? row.getAttribute("data-kind") : row.getAttribute("data-status");
        var match = filter === "all" || rowValue === filter;
        var search = document.getElementById("balance-search");
        if (group === "balance" && search && search.value.trim()) match = match && row.textContent.toLocaleLowerCase("tr").indexOf(search.value.trim().toLocaleLowerCase("tr")) >= 0;
        row.hidden = !match;
      });
    }
    var profileMenuToggle = document.getElementById("profile-menu-toggle");
    if (profileMenuToggle) {
      if (window.matchMedia("(max-width: 850px)").matches && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) { profileMenuToggle.classList.add("menu-hint"); window.setTimeout(function () { profileMenuToggle.classList.remove("menu-hint") }, 5300) }
      profileMenuToggle.addEventListener("click", function () {
        if (!window.matchMedia("(max-width: 850px)").matches) return;
        this.classList.remove("menu-hint");
        var menu = document.getElementById("account-nav");
        if (menu) {
          var open = !menu.classList.contains("show-menus");
          menu.classList.toggle("show-menus", open);
          this.setAttribute("aria-expanded", String(open));
        }
      });
    }
    window.addEventListener("resize", function () {
      var mobile = window.matchMedia("(max-width: 850px)").matches, menu = document.getElementById("account-nav"), toggle = document.getElementById("profile-menu-toggle");
      if (!mobile) { menu.classList.remove("show-menus"); toggle.setAttribute("aria-expanded", "true") }
      else if (!menu.classList.contains("show-menus")) { toggle.setAttribute("aria-expanded", "false") }
    });
    var balanceSearch = document.getElementById("balance-search");
    if (balanceSearch) {
      balanceSearch.addEventListener("input", function () {
        var active = document.querySelector('[data-filter-tabs="balance"] .tab-button.active'), filter = active ? active.getAttribute("data-filter") : "all";
        document.querySelectorAll("#balance-table tbody tr").forEach(function (row) {
          var typeMatch = filter === "all" || row.getAttribute("data-kind") === filter;
          var textMatch = row.textContent.toLocaleLowerCase("tr").indexOf(balanceSearch.value.trim().toLocaleLowerCase("tr")) >= 0;
          row.hidden = !(typeMatch && textMatch);
        });
      });
    }
    document.querySelectorAll(".demo-form").forEach(function (form) {
      form.addEventListener("submit", function (event) {
        if (form.getAttribute("action") && form.getAttribute("action") !== "#") return;
        event.preventDefault();
        if (!form.reportValidity()) return;
        notify(form.getAttribute("data-success") || "İşlem önizleme için kaydedildi.");
        form.reset();
      });
    });
    document.querySelectorAll('input[name="invoice-type"]').forEach(function (input, index) {
      input.value = index === 0 ? "Bireysel Fatura" : "Kurumsal Fatura";
      input.addEventListener("change", function () { document.querySelector(".corporate-field").hidden = this.value !== "Kurumsal Fatura" });
    });
    // showPage("dashboard");
  });
</script>