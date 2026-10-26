<style>
:root{color-scheme:dark;--bg:#11151d;--surface:#1e222d;--surface2:#232632;--surface3:#171b24;--line:rgba(255,179,0,.16);--text:#f2f2f2;--muted:#9ca8b6;--muted2:#768292;--accent:#ffb300;--accent2:#ff7300;--green:#59c978;--radius:14px;--max:1300px}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{margin:0;background:var(--bg);color:var(--text);font-family:'Poppins',sans-serif;line-height:1.5}
dialog#phone-verify-modal{position:fixed;inset:50% auto auto 50%;transform:translate(-50%,-50%);margin:0;z-index:1000;box-shadow:0 24px 80px #00000080}
dialog#phone-verify-modal::backdrop{background:#05080dcc;backdrop-filter:blur(3px)}
dialog#phone-verify-modal form{margin:0}
a{color:inherit;text-decoration:none}
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
button,input,textarea{font:inherit}
#btn-auth{font-weight:600}
button:focus-visible,a:focus-visible,input:focus-visible,textarea:focus-visible{outline:3px solid #ffd676;outline-offset:3px}
.site-header{border-bottom:1px solid #ffffff10;background:#0d1118eF}
.header-inner{width:min(100% - 44px,var(--max));height:76px;margin:auto;display:flex;align-items:center;justify-content:space-between;gap:20px}
.brand img{display:block;width:116px;height:auto;max-height:42px;object-fit:contain}
.header-actions{display:flex;align-items:center;gap:12px}
.secure-label{display:inline-flex;align-items:center;gap:8px;color:#bdc7d4;font-size:.78rem;font-weight:600}
.secure-label svg{width:17px;height:17px;color:var(--green)}
.account-link{display:inline-flex;align-items:center;gap:9px;background:var(--accent);color:#17130a;border-radius:10px;padding:10px 15px;font-size:.83rem;font-weight:700;transition:background .2s}
.account-link:hover{background:#ffc944}
.account-link svg{width:16px;height:16px}
.checkout-main{width:min(100% - 44px,var(--max));margin:0 auto;padding:29px 0 66px}
.checkout-topline{display:flex;align-items:center;justify-content:space-between;gap:20px;margin:0 0 23px}
.checkout-title h1{margin:0;font-size:clamp(1.55rem,2.8vw,2.1rem);line-height:1.15;letter-spacing:-.035em}
.checkout-title p{margin:7px 0 0;color:var(--muted);font-size:.87rem}
.stepper{display:flex;align-items:center;gap:10px;color:var(--muted2);font-size:.73rem;font-weight:600}
.step{display:flex;align-items:center;gap:7px;white-space:nowrap}
.step-number{width:25px;height:25px;display:grid;place-items:center;border:1px solid #364150;border-radius:50%;color:#a3adba;font-size:.7rem}
.step.is-done,.step.is-current{color:#f4f5f8}
.step.is-done .step-number{background:#233a2b;border-color:#3d8052;color:#9ee7b3}
.step.is-current .step-number{background:var(--accent);border-color:var(--accent);color:#19140a}
.step-line{width:30px;height:1px;background:#34404e}
.checkout-grid{display:grid;grid-template-columns:minmax(0,1fr) 350px;grid-template-rows:auto auto 1fr;grid-template-areas:"product aside" "billing aside" "payment aside";align-items:start;gap:20px 22px}
.panel,.side-card{min-width:0;border:1px solid #ffffff0b;border-radius:var(--radius);background:linear-gradient(145deg,#1a222e,#171e28);box-shadow:0 12px 30px #00000016}
.product-panel{grid-area:product;padding:22px 23px 20px}
.billing-panel{grid-area:billing;padding:22px 23px}
.payment-panel{grid-area:payment;padding:22px 23px 23px}
.section-heading{display:flex;align-items:center;gap:11px;margin:0 0 17px;font-size:1.06rem;font-weight:700;letter-spacing:-.015em}
.heading-icon{width:30px;height:30px;display:grid;place-items:center;border:1px solid #ffbb2238;border-radius:9px;background:#ffb81714;color:var(--accent)}
.heading-icon svg{width:16px;height:16px}
.product-row{display:grid;grid-template-columns:62px minmax(0,1fr) auto;align-items:center;gap:15px;padding:13px 14px;border:1px solid #313b49;border-radius:13px;background:#10151c}
.product-art{width:62px;height:62px;display:grid;place-items:center;border:1px solid #ffffff12;border-radius:12px;background:linear-gradient(145deg,#273044,#222a37);color:var(--accent)}
.product-art svg{width:29px;height:29px}
.product-name{font-size:.94rem;font-weight:700}
.product-caption{margin-top:3px;color:var(--muted);font-size:.75rem}
.product-price{font-size:.93rem;font-weight:700;white-space:nowrap}
.discount{display:inline-flex;margin-left:8px;padding:3px 7px;border-radius:999px;background:#ffb817;color:#17130a;font-size:.65rem;font-weight:700}
.panel-helper{margin:12px 0 0;color:var(--muted2);font-size:.73rem}
.address-display{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:16px;border:1px solid #303a48;border-radius:12px;background:#111720}
.address-display[hidden]{display:none}
.address-person{font-size:.88rem;font-weight:700}
.address-lines{margin-top:4px;color:var(--muted);font-size:.77rem;line-height:1.55}
.btn-quiet{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:37px;padding:0 11px;border:1px solid #3d4654;border-radius:9px;background:#18202a;color:#f4f5f7;font-size:.76rem;font-weight:750;white-space:nowrap;cursor:pointer;transition:border-color .2s,background .2s}
.btn-quiet:hover{border-color:#6a788b;background:#202a37}
.btn-quiet svg{width:14px;height:14px;color:var(--accent)}
.address-form{display:none;padding:16px;border:1px solid #303a48;border-radius:12px;background:#111720}
.address-form.is-open{display:block}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}
.field{display:grid;gap:6px;min-width:0}
.field.full{grid-column:1/-1}
.field label{color:#b7c0cc;font-size:.73rem;font-weight:750}
.field input,.field textarea,.field select{width:100%;min-width:0;border:1px solid #303a48;border-radius:9px;background:#0e131a;color:#f5f7fa;padding:11px 12px;font-size:.83rem}
.field input::placeholder,.field textarea::placeholder{color:#697585}
.field select{min-height:43px;color-scheme:dark}
.field select:disabled{opacity:.55;cursor:not-allowed}
.field textarea{min-height:76px;resize:vertical}
.optional-label{color:var(--muted2);font-size:.68rem;font-weight:500}
.invoice-type{display:flex;align-items:center;gap:10px;margin:0 0 2px;padding:0;border:0}
.invoice-type legend{width:100%;margin-bottom:7px;color:#b7c0cc;font-size:.73rem;font-weight:750}
.invoice-type label{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid #303a48;border-radius:9px;background:#111720;color:#c5ced8;font-size:.76rem;font-weight:600;cursor:pointer}
.invoice-type input{width:15px;height:15px;margin:0;accent-color:var(--accent)}
.invoice-type label:has(input:checked){border-color:#ffb8178c;background:#ffb81712;color:#fff}
.address-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:14px}
.payment-tabs{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px;margin:0 0 17px}
.payment-tab{display:flex;align-items:center;justify-content:center;gap:8px;min-height:47px;padding:9px;border:1px solid #303946;border-radius:10px;background:#10151c;color:#9ca7b5;font-size:.8rem;font-weight:750;cursor:pointer;transition:background .18s,border-color .18s,color .18s}
.payment-tab:hover{border-color:#566270;color:#ecf0f5}
.payment-tab[aria-selected="true"]{background:#f2f3f5;border-color:#f2f3f5;color:#151922;box-shadow:0 5px 14px #00000018}
.payment-tab svg{width:17px;height:17px;flex:0 0 auto}
.payment-pane{padding:16px;border:1px solid #303a48;border-radius:13px;background:#111720}
.payment-pane[hidden]{display:none}
.wallet-summary{display:grid;grid-template-columns:1fr auto;align-items:center;gap:16px;padding:15px;border:1px solid #303a48;border-radius:11px;background:#151d27}
.wallet-label{display:block;color:#9ba7b6;font-size:.73rem}
.wallet-value{display:block;margin-top:3px;color:#f3f5f7;font-size:1rem;font-weight:700}
.wallet-balance{padding:9px 11px;border:1px solid #2d5a3b;border-radius:9px;background:#15261c;color:#9de4b0;font-size:.77rem;font-weight:700;white-space:nowrap}
.wallet-balance small{display:block;color:#7eac89;font-size:.64rem;font-weight:650}
.pane-description{margin:13px 0 0;color:#aab4c2;font-size:.79rem}
.bank-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
.bank-option{display:flex;align-items:center;justify-content:center;min-height:54px;padding:9px 11px;border:1px solid #303a48;border-radius:10px;background:#0e141b;color:#cad1da;text-align:center;font-size:.76rem;font-weight:700;cursor:pointer;transition:border-color .18s,background .18s,color .18s}
.bank-option:hover{border-color:#5c6776;color:#fff}
.bank-option[aria-checked="true"]{border-color:var(--accent);background:#ffb8170d;color:#fff;box-shadow:inset 0 0 0 1px #ffb81724}
.bank-option[aria-checked="true"]::before{content:"✓";display:inline-grid;place-items:center;width:16px;height:16px;margin-right:7px;border-radius:50%;background:var(--accent);color:#15130d;font-size:.68rem}
.transfer-reference{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:14px;margin-top:15px;padding:16px;border:1px solid #e9a90e;border-radius:12px;background:linear-gradient(105deg,#ffbd27,#f5ad0b);color:#1b160a}
.reference-title{display:flex;align-items:center;gap:8px;font-weight:700;font-size:.88rem}
.reference-title svg{width:18px;height:18px}
.reference-help{margin:4px 0 0;color:#513d13;font-size:.74rem}
.reference-code{display:flex;align-items:center;gap:7px;margin-top:11px}
.reference-code code{display:block;min-width:145px;padding:9px 11px;border:1px solid #352a12;border-radius:8px;background:#151920;color:#f8fafc;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.9rem;font-weight:700;letter-spacing:.07em}
.copy-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:0 10px;border:1px solid #ffffff45;border-radius:8px;background:#ffffffec;color:#17191e;font-size:.72rem;font-weight:700;cursor:pointer;transition:background .18s}
.copy-button:hover{background:#fff}
.copy-button svg{width:14px;height:14px}
.transfer-steps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin:14px 0}
.transfer-step{display:flex;gap:8px;align-items:flex-start;padding:10px;border:1px solid #2a3441;border-radius:9px;background:#141b24;color:#aeb8c5;font-size:.7rem;line-height:1.4}
.transfer-step b{flex:0 0 auto;color:var(--accent);font-size:.68rem}
.bank-details{display:grid;grid-template-columns:1fr 1fr;gap:0 18px;padding:4px 13px;border:1px solid #303a48;border-radius:11px;background:#0f151d}
.bank-row{display:grid;grid-template-columns:78px minmax(0,1fr) auto;align-items:center;gap:8px;min-height:46px;border-bottom:1px solid #ffffff0a}
.bank-row:nth-last-child(-n+2){border-bottom:0}
.bank-label{color:#8591a1;font-size:.7rem;font-weight:600}
.bank-value{min-width:0;overflow:hidden;color:#edf0f4;font-size:.73rem;font-weight:750;text-overflow:ellipsis;white-space:nowrap}
.bank-row .copy-button{min-height:29px;padding:0 8px;font-size:.64rem}
.bank-row .copy-button svg{width:12px;height:12px}
.bank-demo-note{grid-column:1/-1;margin:10px 0 2px;color:#7f8b9a;font-size:.66rem}
.transfer-warning{display:flex;gap:9px;margin-top:13px;padding:11px 12px;border:1px solid #594522;border-radius:9px;background:#30251370;color:#c8b68d;font-size:.7rem;line-height:1.5}
.transfer-warning svg{flex:0 0 auto;width:15px;height:15px;color:var(--accent)}
.card-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}
.card-fields .field input{height:43px}
.card-brand-note{display:flex;align-items:center;gap:8px;margin:13px 0 0;color:#8e9bab;font-size:.71rem}
.card-brand-note svg{width:16px;height:16px;color:#b6c1d0}
.card-commission-note{grid-column:1/-1;margin:12px 0 0;padding:10px 12px;border:1px solid #7d5a1f;background:#2b2110;border-radius:9px;color:#d8c28a;font-size:.72rem}
.card-commission-note strong{color:#ffd36a}
.mobile-info{display:flex;gap:11px;padding:13px;border:1px solid #303a48;border-radius:10px;background:#151d27;color:#aab5c2;font-size:.76rem}
.mobile-info svg{flex:0 0 auto;width:19px;height:19px;color:var(--accent)}
.mobile-info strong{display:block;margin-bottom:2px;color:#f1f3f6;font-size:.79rem}
.mobile-commission{margin-top:13px;padding:11px 12px;border-radius:9px;background:#0d131a;color:#8f9bab;font-size:.72rem}
.payment-bottom{margin-top:17px}
.payment-total-line{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:13px;padding:13px 14px;border:1px solid #303743;border-radius:10px;background:#10151c;font-size:.82rem;font-weight:700}
.payment-total-line span:last-child{font-size:.9rem}
.terms-label{display:flex;align-items:flex-start;gap:10px;margin:0 0 14px;padding:13px;border:1px solid #303a48;border-radius:10px;background:#141b24;color:#a9b4c2;font-size:.73rem;line-height:1.55;cursor:pointer}
.terms-label input{width:17px;height:17px;flex:0 0 auto;margin:1px 0 0;accent-color:var(--accent)}
.terms-label.is-invalid{border-color:#d86565;background:#341d22}
.terms-warning{margin:-7px 0 13px;padding:9px 11px;border:1px solid #86434b;border-radius:8px;background:#351d22;color:#ffc4c8;font-size:.72rem;line-height:1.45}
[hidden],.terms-warning[hidden],.transfer-gate[hidden],.field[hidden]{display:none!important}
.terms-label a{color:#ffca50;text-decoration:underline;text-underline-offset:2px}
.pay-button{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;min-height:52px;border:0;border-radius:11px;background:linear-gradient(180deg,#ffc62e,#ffb20b);color:#1c1607;font-size:.91rem;font-weight:700;cursor:pointer;box-shadow:0 8px 20px #ffb81720;transition:filter .2s,transform .2s,opacity .2s}
.pay-button:not(:disabled):hover{filter:brightness(1.05);transform:translateY(-1px)}
.pay-button:disabled{background:#353a41;color:#7b8086;box-shadow:none;cursor:not-allowed}
.pay-button svg{width:18px;height:18px}
.payment-message{min-height:1.4em;margin:9px 0 0;color:#aebaff;font-size:.74rem;text-align:center}
.checkout-aside{grid-area:aside;display:grid;gap:15px;position:sticky;top:18px}
.side-card{padding:19px}
.side-heading{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:0 0 13px;font-size:.98rem;font-weight:700}
.side-heading svg{width:16px;height:16px;color:var(--accent)}
.coupon-row{display:flex;gap:7px}
.coupon-row input{width:100%;min-width:0;height:42px;padding:0 11px;border:1px solid #303a48;border-radius:9px;background:#10151c;color:#f3f5f7;font-size:.77rem}
.coupon-row input::placeholder{color:#748090}
.coupon-row button{flex:0 0 auto;padding:0 14px;border:0;border-radius:9px;background:var(--accent);color:#1b1609;font-size:.75rem;font-weight:700;cursor:pointer}
.coupon-row button:hover{background:#ffc943}
.coupon-message{min-height:1em;margin:8px 0 0;color:#aebaff;font-size:.69rem}
.coupon-highlight{margin-top:10px;padding:10px;border:1px solid #5f4b1d;border-radius:9px;background:#2a2110}
.coupon-highlight-top,.coupon-highlight-bottom{display:flex;align-items:center;justify-content:space-between;gap:10px}
.coupon-highlight-code{color:#ffd66d;font-size:.75rem;font-weight:800;letter-spacing:.06em}
.coupon-highlight-bottom{margin-top:5px;color:#bfc8d4;font-size:.68rem}
.coupon-highlight-discount{color:#79db91;font-weight:750}
.coupon-remove{border:0;background:transparent;color:#ffb7a8;font-size:.68rem;font-weight:700;cursor:pointer}
.summary-product{display:flex;justify-content:space-between;gap:14px;padding:12px 0;border-bottom:1px solid #ffffff10}
.summary-product-name{font-size:.78rem;font-weight:750}
.summary-product-detail{margin-top:3px;color:var(--muted2);font-size:.68rem}
.summary-product-price{font-size:.78rem;font-weight:700;white-space:nowrap}
.summary-row{display:flex;justify-content:space-between;gap:14px;padding:10px 0;color:#aeb8c5;font-size:.76rem}
.summary-row strong{color:#eef0f4;font-weight:750}
.summary-total{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-top:5px;padding:14px 0 3px;border-top:1px solid #ffffff12;font-size:.88rem;font-weight:700}
.summary-total strong{font-size:1.12rem}
.summary-caption{margin:10px 0 0;color:#798596;font-size:.67rem;line-height:1.5}
.trust-list{display:grid;gap:10px}
.trust-item{display:flex;align-items:center;gap:9px;color:#a6b1bf;font-size:.73rem}
.trust-item svg{width:16px;height:16px;flex:0 0 auto;color:var(--green)}
.toast{position:fixed;top:24px;right:24px;bottom:auto;z-index:999999;max-width:min(420px,calc(100vw - 32px));padding:14px 18px;border:1px solid rgba(255,255,255,0.08);border-left:4px solid #c79a3e;border-radius:12px;background:rgba(18,22,28,0.96);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);color:#fff;font-size:.84rem;font-weight:500;box-shadow:0 12px 32px rgba(0,0,0,0.65),0 0 0 1px rgba(255,255,255,0.04);opacity:0;transform:translateY(-16px);pointer-events:none;transition:opacity .25s ease,transform .25s ease}
.toast.show{opacity:1;transform:translateY(0);pointer-events:auto}
@media(max-width:768px){.toast{top:16px;left:16px;right:16px;bottom:auto;width:calc(100% - 32px);max-width:calc(100% - 32px);margin:0 auto}}
@media(max-width:1000px){.checkout-grid{grid-template-columns:minmax(0,1fr) 310px;gap:16px}.payment-tabs{gap:7px}.payment-tab{font-size:.73rem}.bank-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:820px){.checkout-topline{align-items:flex-start;flex-direction:column;gap:17px}.checkout-grid{grid-template-columns:1fr;grid-template-areas:"product" "aside" "billing" "payment"}.checkout-aside{position:static;grid-template-columns:1fr 1fr}.summary-card{grid-column:auto}.trust-card{display:none}.checkout-main{padding-top:23px}}
@media(max-width:560px){.header-inner,.checkout-main,.checkout-footer{width:min(100% - 28px,var(--max))}.header-inner{height:65px}.brand img{width:100px}.secure-label{font-size:0}.secure-label svg{width:19px;height:19px}.account-link{padding:9px 11px;font-size:.76rem}.checkout-main{padding-top:20px}.checkout-title p{font-size:.8rem}.stepper{gap:7px;width:100%;justify-content:space-between;font-size:.66rem}.step{gap:5px}.step-number{width:23px;height:23px}.step-line{width:12px}.checkout-grid{gap:13px}.panel,.side-card{border-radius:13px}.product-panel,.billing-panel,.payment-panel{padding:16px}.section-heading{font-size:.98rem;margin-bottom:13px}.product-row{grid-template-columns:52px minmax(0,1fr);gap:11px;padding:11px}.product-art{width:52px;height:52px}.product-price{grid-column:2;justify-self:start;margin-top:-6px}.discount{margin-left:5px}.address-display{align-items:flex-start;padding:13px}.address-display .btn-quiet{min-height:34px;padding:0 9px;font-size:.69rem}.form-grid{gap:10px}.payment-tabs{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.payment-tab{min-height:45px;font-size:.76rem}.payment-pane{padding:12px}.wallet-summary{grid-template-columns:1fr;gap:10px}.wallet-balance{justify-self:start}.bank-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.bank-option{min-height:46px;font-size:.7rem}.transfer-reference{grid-template-columns:1fr;gap:10px;padding:13px}.reference-code code{flex:1}.transfer-steps{grid-template-columns:1fr;gap:6px}.transfer-step{padding:8px}.bank-details{grid-template-columns:1fr;padding-inline:11px}.bank-row{grid-template-columns:86px minmax(0,1fr) auto;min-height:43px}.bank-row:nth-last-child(-n+2){border-bottom:1px solid #ffffff0a}.bank-row:last-of-type{border-bottom:0}.bank-demo-note{margin-bottom:3px}.card-fields{grid-template-columns:1fr;gap:11px}.side-card{padding:15px}.summary-total strong{font-size:1.02rem}.footer-inner{align-items:flex-start;flex-direction:column}.footer-links{gap:12px}}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition:none!important}}
.summary-card{order:-1}
.summary-row[hidden],.payment-pane[hidden],.bank-details[hidden]{display:none}
.summary-total{margin-top:3px;padding-top:13px;border-top:1px solid #ffffff16}
.terms-label{margin-top:16px}
.pay-button{min-height:48px}
.bank-details{display:block;margin-top:12px;padding:0 15px 12px;border:1px solid #35404d;border-radius:12px;background:#0f151d}
.bank-details-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 0;border-bottom:1px solid #ffffff12}
.bank-details-head div span{display:block;color:#8793a2;font-size:.66rem}
.bank-details-head div strong{display:block;margin-top:2px;color:#f0f2f6;font-size:.84rem}
.bank-account-badge{padding:5px 8px;border:1px solid #33533d;border-radius:99px;background:#14251a;color:#a1e2b2;font-size:.65rem;font-weight:750;white-space:nowrap}
.bank-details .bank-row{grid-template-columns:110px minmax(0,1fr) auto;min-height:43px;border-bottom:1px solid #ffffff0c}
.bank-details .bank-row:last-of-type{border-bottom:0}
.bank-details .bank-value{font-size:.75rem}
.bank-details .copy-button{min-height:29px;padding:0 8px;font-size:.65rem}
.bank-details .copy-button:disabled{opacity:.38;cursor:not-allowed}
.bank-transfer-total{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:7px;padding-top:11px;border-top:1px solid #ffffff15;color:#afb9c7;font-size:.75rem;font-weight:750}
.bank-transfer-total strong{color:#fff;font-size:.83rem}
.payment-assurance{display:flex;align-items:center;justify-content:space-between;gap:22px;margin-top:20px;padding:17px 19px;border:1px solid #28323e;border-radius:14px;background:#121821}
.assurance-info{display:flex;align-items:center;gap:11px;min-width:220px}
.assurance-lock{display:grid;place-items:center;width:35px;height:35px;border:1px solid #30533a;border-radius:10px;background:#14251a;color:#80d393}
.assurance-lock svg{width:17px;height:17px}
.assurance-info strong{display:block;color:#e7ebf1;font-size:.77rem}
.assurance-info small{display:block;margin-top:2px;color:#8491a0;font-size:.66rem}
.assurance-logos{display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:10px}
.visa-mark,.mastercard-mark,.troy-mark,.secure-mark{display:inline-flex;align-items:center;justify-content:center;min-height:31px;padding:0 10px;border:1px solid #303a47;border-radius:7px;background:#0e141b;color:#d7dde6}
.visa-mark{color:#e9f0ff;font-size:1rem;font-style:italic;font-weight:950;letter-spacing:-.06em}
.mastercard-mark{position:relative;gap:0;padding-right:8px;padding-left:8px}
.mastercard-mark i{width:14px;height:14px;border-radius:50%;background:#eb433b;opacity:.95}
.mastercard-mark i+i{margin-left:-5px;background:#f4a340;mix-blend-mode:screen}
.mastercard-mark b{margin-left:5px;font-size:.58rem;font-weight:700}
.troy-mark{color:#f4f6fa;font-size:.78rem;font-weight:950;letter-spacing:.05em}
.secure-mark{gap:5px;color:#b8c2d0;font-size:.62rem;font-weight:750}
.secure-mark svg{width:14px;height:14px;color:#83d596}
@media(max-width:1000px){.payment-assurance{align-items:flex-start;flex-direction:column}.assurance-logos{justify-content:flex-start}}
@media(max-width:820px){.checkout-grid{grid-template-areas:"product" "billing" "payment" "aside"}.checkout-aside{grid-template-columns:1fr 1fr}.summary-card{grid-column:auto}.trust-card{display:none}.payment-assurance{margin-top:14px}}
@media(max-width:560px){.checkout-aside{grid-template-columns:1fr}.transfer-guide{grid-template-columns:30px minmax(0,1fr);align-items:start}.guide-actions{grid-column:2;justify-content:flex-end;margin-top:2px}.transfer-reference{padding:14px 10px}.reference-number{min-width:168px;font-size:.96rem}.bank-details{padding-inline:10px}.bank-details .bank-row{grid-template-columns:84px minmax(0,1fr) auto;gap:6px}.bank-details .bank-value{font-size:.67rem}.bank-details .copy-button{font-size:0;padding:0 8px}.bank-details .copy-button svg{width:13px;height:13px}.bank-transfer-total{font-size:.7rem}.payment-assurance{padding:15px}.assurance-logos{gap:7px}.visa-mark,.mastercard-mark,.troy-mark,.secure-mark{min-height:29px;padding-inline:8px}}
.summary-coupon{margin:3px 0 8px;padding:12px;border:1px solid #303a48;border-radius:10px;background:#111720}
.checkout-target { display:flex; flex-wrap:wrap; gap:8px 16px; margin-top:12px; padding:11px 13px; border:1px solid #303a48; border-radius:10px; background:#111720; color:#b7c1cd; font-size:.76rem; }
.checkout-target strong { flex-basis:100%; color:#f1f4f7; font-size:.8rem; }
.checkout-target b { color:#fff; font-weight:700; }
.summary-coupon>label{display:block;margin-bottom:7px;color:#b9c3d0;font-size:.7rem;font-weight:750}
.summary-coupon .coupon-row input{height:38px}
.summary-coupon .coupon-row button{padding-inline:12px}
.summary-coupon .coupon-message{margin-bottom:0}
.transfer-reference{grid-template-columns:minmax(0,1fr) auto;gap:12px;margin-top:14px;padding:12px 14px;border-color:#394451;background:#151d27;color:var(--text);text-align:left}
.transfer-reference .reference-title{color:#f0f3f6;font-size:.78rem;font-weight:700}
.transfer-reference .reference-help{margin:3px 0 0;color:#9ba7b6;font-size:.68rem;line-height:1.4}
.reference-control{justify-content:center;flex-wrap:nowrap;gap:8px}
.reference-number{min-width:0;padding:8px 11px;border-color:#343f4d;border-radius:8px;background:#0d131a;font-size:.84rem;letter-spacing:.07em}
.reference-copy{min-height:35px;padding:0 10px}
.transfer-help-note{margin:10px 2px 0;color:#aeb9c6;font-size:.7rem;line-height:1.5}
.transfer-stage{position:relative;isolation:isolate}
.transfer-content{transition:filter .25s,opacity .25s}
.transfer-content.is-locked{filter:blur(7px);opacity:.45;pointer-events:none;user-select:none}
.transfer-gate{position:absolute;inset:0;z-index:2;display:grid;place-items:center;padding:18px;border-radius:12px;background:#090d14a6}
.transfer-gate-card{width:min(100%,510px);padding:24px;border:1px solid #4c5664;border-radius:15px;background:linear-gradient(145deg,#202a37f5,#151c26f5);box-shadow:0 18px 55px #0009;text-align:center}
.transfer-gate-icon{display:grid;place-items:center;width:31px;height:31px;margin:0 auto 10px;border:1px solid #ffb81766;border-radius:50%;background:#ffb81718;color:#ffca4d;font-weight:700}
.transfer-gate-card h3{margin:0;color:#f6f7fa;font-size:1rem;letter-spacing:-.02em}
.transfer-gate-card p{max-width:420px;margin:8px auto 16px;color:#b3bdca;font-size:.78rem;line-height:1.6}
.transfer-gate-card .guide-next{display:flex;align-items:center;justify-content:center;gap:8px;min-height:40px;margin:auto;padding:0 16px;border:0;border-radius:9px;background:var(--accent);color:#17130a;font-size:.76rem;font-weight:700;cursor:pointer}
.transfer-gate-card .guide-next:hover{background:#ffc83f}
.amex-mark,.discover-mark,.jcb-mark,.unionpay-mark{display:inline-flex;align-items:center;justify-content:center;min-height:31px;padding:0 10px;border:1px solid #303a47;border-radius:7px;background:#0e141b;color:#e5eaf2;font-size:.68rem;font-weight:700;letter-spacing:.02em}
.amex-mark{background:#102d56;color:#fff;letter-spacing:.04em}
.discover-mark{color:#ffdfbd}
.jcb-mark{background:#fafafa;color:#16408b;font-style:italic}
.unionpay-mark{color:#f3f4f6}
@media(max-width:560px){.transfer-reference{grid-template-columns:1fr;gap:9px;padding:11px}.reference-control{justify-content:flex-start;flex-wrap:wrap}.reference-number{font-size:.82rem}.visa-mark,.mastercard-mark,.troy-mark,.amex-mark,.discover-mark,.jcb-mark,.unionpay-mark,.secure-mark{min-height:29px;padding-inline:8px}}
.checkout-addr-card:hover{border-color:rgba(199,154,62,0.6)!important}
.checkout-addr-card.is-selected{border-color:#c79a3e!important;background:linear-gradient(180deg,#182029 0%,#121820 100%)!important;box-shadow:0 4px 14px rgba(0,0,0,0.25)}
.select2-dropdown{background-color:#121920!important;border:1px solid #303b45!important;border-radius:8px!important;z-index:99999!important}
.select2-results__option{color:#ccd5df!important;font-size:0.75rem!important;padding:8px 12px!important}
.select2-results__option--highlighted[aria-selected]{background-color:#c79a3e!important;color:#111!important}
.select2-container--default .select2-search--dropdown .select2-search__field{background-color:#0e141a!important;border:1px solid #303b45!important;color:#fff!important;border-radius:6px!important;padding:6px 10px!important;font-size:0.75rem!important}
.select2-container--default .select2-selection--single{height:42px!important;background-color:#10151c!important;border:1px solid #303a48!important;border-radius:9px!important;display:flex!important;align-items:center!important}
.select2-container--default .select2-selection--single .select2-selection__rendered{color:#f3f5f7!important;font-size:0.77rem!important;padding-left:11px!important;line-height:40px!important}
.select2-container--default .select2-selection--single .select2-selection__arrow{height:40px!important}
.btn-primary-action{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:7px!important;min-height:36px!important;height:36px!important;padding:0 16px!important;border:0!important;border-radius:9px!important;background:linear-gradient(180deg,#ffc62e,#ffb20b)!important;color:#1c1607!important;font-size:.78rem!important;font-weight:700!important;white-space:nowrap!important;cursor:pointer!important;transition:filter .18s,transform .18s!important}
.btn-primary-action:hover{filter:brightness(1.07)!important;transform:translateY(-1px)!important}
.btn-primary-action svg{width:14px!important;height:14px!important;flex-shrink:0!important}
</style>

<?php
$displayCurrency = displayCurrency($currentLocale->locale_currency ?? 'TRY');
$appliedCoupon = $this->session->userdata('applied_coupon');
?>
<main class="checkout-main">
  <div class="checkout-topline">
    <div class="checkout-title"><h1>Ödeme</h1><p>Siparişini gözden geçir, sana uygun ödeme yöntemini seç.</p></div>
    <nav class="stepper" aria-label="Sipariş adımları">
      <span class="step is-done"><span class="step-number" aria-hidden="true">✓</span> Sepet</span><span class="step-line" aria-hidden="true"></span>
      <span class="step is-done"><span class="step-number" aria-hidden="true">✓</span> Bilgiler</span><span class="step-line" aria-hidden="true"></span>
      <span class="step is-current" aria-current="step"><span class="step-number">3</span> Ödeme</span>
    </nav>
  </div>

  <div class="checkout-grid">
    <section class="panel product-panel" aria-labelledby="product-heading">
      <h2 class="section-heading" id="product-heading"><span class="heading-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 7h12l1 14H5L6 7Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg></span> Siparişindeki ürün</h2>
      <div class="product-row">
        <div class="product-art" aria-hidden="true">
            <?php if(!empty($cartData['product_image'])): ?>
                <img src="<?= html_escape(getenv('CDN_URL') . $cartData['product_image']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
            <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 12v8H4v-8M2 7h20v5H2z"/><path d="M12 20V7m0 0H8.8a2.3 2.3 0 1 1 2.2-3c.7 1.2 1 3 1 3Zm0 0h3.2a2.3 2.3 0 1 0-2.2-3c-.7 1.2-1 3-1 3Z"/></svg>
            <?php endif; ?>
        </div>
        <div>
            <div class="product-name"><?= html_escape($cartData['product_name'] ?? '') ?></div>
            <div class="product-caption">Dijital teslimat · <?= (int)($cartData['quantity'] ?? 1) ?> adet</div>
        </div>
        <div class="product-price"><?= nFormat($cartPrice) ?> <?= $displayCurrency ?></div>
      </div>
      <p class="panel-helper">Ürün ve miktar, sepetindeki seçime göre güncellenir.</p>
    </section>

    <aside class="checkout-aside" aria-label="Sipariş toplamı ve kupon">
      <section class="side-card summary-card" aria-labelledby="summary-heading">
        <h2 class="side-heading" id="summary-heading"><span>Sipariş özeti</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></h2>
        <div class="summary-coupon">
          <label for="coupon-code">Kupon kodun var mı?</label>
          <form class="coupon-row" id="coupon-form" onsubmit="event.preventDefault(); applyCoupon();">
            <input id="coupon-code" type="text" name="coupon_code" placeholder="Kupon kodunu gir" autocomplete="off" value="<?= html_escape($appliedCoupon['coupon_code'] ?? '') ?>">
            <button type="submit">Uygula</button>
          </form>
          <p class="coupon-message" id="coupon-message" aria-live="polite"></p>
          <?php if (!empty($appliedCoupon)): ?>
            <div class="coupon-highlight" id="applied-coupon">
              <div class="coupon-highlight-top">
                <span class="coupon-highlight-code"><?= html_escape(strtoupper($appliedCoupon['coupon_code'] ?? '')) ?></span>
                <button class="coupon-remove" id="remove-coupon" type="button">Kaldır</button>
              </div>
              <div class="coupon-highlight-bottom">
                <span class="coupon-highlight-discount">
                  <?= ($appliedCoupon['coupon_type'] ?? null) == 1 ? '%' . (int)($appliedCoupon['coupon_value'] ?? 0) . ' indirim' : nFormat($appliedCoupon['discount'] ?? 0) . ' indirim' ?>
                </span>
                <span>Kupon başarıyla uygulandı</span>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="summary-product"><div><div class="summary-product-name"><?= html_escape($cartData['product_name'] ?? '') ?></div><div class="summary-product-detail"><?= (int)($cartData['quantity'] ?? 1) ?> adet</div></div><span class="summary-product-price" id="summary-product-price"><?= nFormat($cartPrice) ?> <?= $displayCurrency ?></span></div>
        <div class="summary-row"><span>Sipariş toplamı</span><strong id="subtotal"><?= nFormat($cartPrice) ?> <?= $displayCurrency ?></strong></div>
        <?php if ((float)$couponDiscount > 0): ?>
          <div class="summary-row" id="summary-coupon-line"><span>Kupon indirimi</span><strong style="color:#79db91">-<?= nFormat($couponDiscount) ?> <?= $displayCurrency ?></strong></div>
        <?php endif; ?>
        
        <div class="summary-row"><span id="remaining-label">Ödeme yöntemiyle</span><strong id="remaining-value"><?= nFormat($netTotal) ?> <?= $displayCurrency ?></strong></div>
        <div class="summary-row" id="fee-row" hidden><span id="fee-label">Kredi kartı komisyonu (%<?= nFormat($creditCardCommissionRate) ?>)</span><strong id="fee-value">0,00 <?= $displayCurrency ?></strong></div>
        <div class="summary-total"><span>Ödenecek tutar</span><strong id="summary-total"><?= nFormat($netTotal) ?> <?= $displayCurrency ?></strong></div>
        <label class="terms-label" id="terms-label"><input id="terms-check" type="checkbox" aria-describedby="terms-warning"><span><a href="<?= base_url($uri . '/distance-sales-agreement') ?>" target="_blank" rel="noopener noreferrer">Mesafeli Satış Sözleşmesi</a> ve <a href="<?= base_url($uri . '/preliminary-information-form') ?>" target="_blank" rel="noopener noreferrer">Ön Bilgilendirme Formu</a>'nu okudum, onaylıyorum.</span></label>
        <p class="terms-warning" id="terms-warning" role="alert" hidden>Lütfen ödeme işlemine devam etmeden önce sözleşmeleri onayla.</p>
        <button class="pay-button" id="pay-button" type="button" onclick="submitPayment()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span id="pay-button-label">Ödemeyi yap</span></button>
        <p class="payment-message" id="payment-message" aria-live="polite"></p>
      </section>
    </aside>

    <section class="panel billing-panel" aria-labelledby="billing-heading">
      <?php
      $userFullName = trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''));
      $billName = !empty($user_details->user_bill_company_name) ? $user_details->user_bill_company_name : ($userFullName !== '' ? $userFullName : ($user['user_name'] ?? 'Belirtilmemiş'));
      $billAddress = !empty($user_details->user_bill_address) ? $user_details->user_bill_address : 'Adres belirtilmemiş';
      
      $addressList = !empty($user_addresses) ? $user_addresses : [];
      if (empty($addressList) && !empty($user_details) && (!empty($user_details->user_bill_address) || !empty($user_details->user_bill_city))) {
          $savedTaxNumber = preg_replace('/\D+/', '', (string)($user_details->user_bill_tax_number ?? ''));
          $legacyIsForeign = !empty($savedTaxNumber) && strlen($savedTaxNumber) !== 11;
          $legacyObj = (object)[
              'user_addresses_id' => 0,
              'user_addresses_title' => 'Fatura Adresi',
              'user_addresses_invoice_type' => !empty($user_details->user_bill_tax_office) ? 2 : 1,
              'user_addresses_is_turkish_citizen' => empty($legacyIsForeign) ? 1 : 0,
              'user_addresses_is_turkish_company' => (int)($user_details->user_is_turkish_company ?? 1),
              'user_addresses_full_name' => $billName,
              'user_addresses_company_name' => $user_details->user_bill_company_name ?? '',
              'user_addresses_tax_office' => $user_details->user_bill_tax_office ?? '',
              'user_addresses_tax_number' => $user_details->user_bill_tax_number ?? '',
              'user_addresses_is_e_invoice_taxpayer' => (int)($user_details->user_bill_e_invoice ?? 0),
              'user_addresses_country' => 'Türkiye',
              'user_addresses_city' => $user_details->user_bill_city ?? '',
              'user_addresses_district' => $user_details->user_bill_district ?? '',
              'user_addresses_postal_code' => '',
              'user_addresses_address' => $user_details->user_bill_address ?? '',
              'user_addresses_phone' => $user_details->user_phone ?? $userSession['user_phone'] ?? '',
              'user_addresses_is_default' => 1
          ];
          $addressList = [$legacyObj];
      }
      $hasAddresses = !empty($addressList);
      $selectedAddressId = 0;
      $selectedAddr = null;
      foreach ($addressList as $a) {
          if (!empty($a->user_addresses_is_default)) {
              $selectedAddressId = (int)$a->user_addresses_id;
              $selectedAddr = $a;
              break;
          }
      }
      if (!$selectedAddressId && $hasAddresses) {
          $selectedAddressId = (int)$addressList[0]->user_addresses_id;
          $selectedAddr = $addressList[0];
      }
      $valPhone = !empty($selectedAddr->user_addresses_phone) ? $selectedAddr->user_addresses_phone : ($user_details->user_phone ?? $userSession['user_phone'] ?? '');
      ?>

      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
        <h2 class="section-heading" id="billing-heading" style="margin:0;"><span class="heading-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span> Fatura bilgileri</h2>
        <?php if ($hasAddresses): ?>
          <button type="button" class="btn-quiet" id="btn-add-new-address" onclick="checkoutOpenNewAddress()" style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; padding:6px 12px; height:32px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Yeni Adres Ekle
          </button>
        <?php endif; ?>
      </div>

      <!-- Kayıtlı Adresler Listesi -->
      <div id="checkout-address-list" style="<?= $hasAddresses ? 'display:block;' : 'display:none;' ?>">
        <div class="saved-addresses-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:12px; margin-bottom:14px;">
          <?php foreach ($addressList as $addr): 
            $isSelected = ((int)$addr->user_addresses_id === $selectedAddressId) || (!$selectedAddressId && $addr === $addressList[0]);
          ?>
            <div class="checkout-addr-card <?= $isSelected ? 'is-selected' : '' ?>" id="addr-card-<?= (int)$addr->user_addresses_id ?>" onclick="checkoutSelectAddress(<?= (int)$addr->user_addresses_id ?>)" style="cursor:pointer; border:1px solid <?= $isSelected ? '#c79a3e' : '#303a48' ?>; border-radius:12px; background:<?= $isSelected ? 'linear-gradient(180deg, #182029 0%, #121820 100%)' : '#111720' ?>; padding:14px; display:flex; flex-direction:column; justify-content:space-between; transition:all .18s ease; position:relative;">
              <div>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                  <strong style="color:#fff; font-size:13px; display:flex; align-items:center; gap:8px;">
                    <span class="addr-radio-indicator" style="display:inline-block; width:14px; height:14px; border-radius:50%; border:2px solid <?= $isSelected ? '#c79a3e' : '#556577' ?>; background:<?= $isSelected ? '#c79a3e' : 'transparent' ?>; box-sizing:border-box;"></span>
                    <?= html_escape($addr->user_addresses_title ?: 'Fatura Adresi') ?>
                  </strong>
                  <div style="display:flex; gap:5px;">
                    <span class="badge-selected" style="<?= $isSelected ? 'display:inline-block;' : 'display:none;' ?> font-size:10px; font-weight:700; background:#2a2110; color:#ffd66d; border:1px solid #5f4b1d; padding:2px 7px; border-radius:99px;">Seçili</span>
                    <span style="font-size:10px; font-weight:600; background:#18222d; color:#8fa0b5; border:1px solid #283747; padding:2px 7px; border-radius:99px;">
                      <?= $addr->user_addresses_invoice_type == 2 ? 'Kurumsal' : 'Bireysel' ?>
                    </span>
                  </div>
                </div>

                <div style="font-size:13px; color:#edf0f2; font-weight:600; margin-bottom:4px;">
                  <?= html_escape($addr->user_addresses_invoice_type == 2 ? $addr->user_addresses_company_name : $addr->user_addresses_full_name) ?>
                </div>

                <?php if (!empty($addr->user_addresses_tax_number)): ?>
                  <div style="font-size:11px; color:#8fa0b5; margin-bottom:4px;">
                    <?= $addr->user_addresses_invoice_type == 2 ? 'VKN: ' : 'TCKN: ' ?>
                    <span style="color:#ccd5df; font-weight:600;"><?= html_escape($addr->user_addresses_tax_number) ?></span>
                    <?php if (!empty($addr->user_addresses_tax_office)): ?>
                      <span style="color:#6f7f91;">(<?= html_escape($addr->user_addresses_tax_office) ?>)</span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <div style="font-size:12px; color:#9db0c2; line-height:1.4; margin-bottom:6px; word-break:break-word;">
                  <?= nl2br(html_escape($addr->user_addresses_address)) ?>
                </div>
                
                <div style="font-size:11px; color:#c79a3e; font-weight:600;">
                  <?= html_escape($addr->user_addresses_district . ', ' . $addr->user_addresses_city . (!empty($addr->user_addresses_country) && $addr->user_addresses_country !== 'Türkiye' ? ' / ' . $addr->user_addresses_country : '')) ?>
                </div>

                <?php 
                  $addrPhone = !empty($addr->user_addresses_phone) ? $addr->user_addresses_phone : ($user_details->user_phone ?? $userSession['user_phone'] ?? '');
                ?>
                <?php if (!empty($addrPhone)): ?>
                  <div style="font-size:11px; color:#8fa0b5; margin-top:5px; display:flex; align-items:center; gap:5px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;color:#c79a3e;flex-shrink:0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span style="color:#d1dbe5; font-weight:500;"><?= html_escape($addrPhone) ?></span>
                  </div>
                <?php endif; ?>
              </div>

              <div style="display:flex; align-items:center; justify-content:space-between; margin-top:12px; padding-top:8px; border-top:1px solid rgba(255,255,255,0.06);">
                <button type="button" class="btn-quiet addr-select-btn" onclick="event.stopPropagation(); checkoutSelectAddress(<?= (int)$addr->user_addresses_id ?>)" style="font-size:11px; padding:3px 10px; height:26px; font-weight:600; color:<?= $isSelected ? '#ffd66d' : 'inherit' ?>;">
                  <?= $isSelected ? 'Seçildi' : 'Bu Adresi Seç' ?>
                </button>
                <button type="button" class="btn-quiet" onclick="event.stopPropagation(); checkoutEditAddress(<?= (int)$addr->user_addresses_id ?>)" style="display:inline-flex; align-items:center; gap:4px; font-size:11px; padding:3px 8px; height:26px;">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Düzenle
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Adres Ekleme / Düzenleme Formu -->
      <div id="checkout-form-container" style="<?= $hasAddresses ? 'display:none;' : 'display:block;' ?>">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid rgba(255,255,255,0.08);">
          <h3 id="checkout-form-heading" style="font-size:13px; font-weight:700; color:#c79a3e; margin:0;">
            <?= $hasAddresses ? 'Fatura Adresini Düzenle' : 'Fatura Adresi Ekle' ?>
          </h3>
          <?php if ($hasAddresses): ?>
            <button type="button" class="btn-quiet" onclick="checkoutCancelForm()" style="font-size:11px; padding:3px 8px; height:24px;">Vazgeç</button>
          <?php endif; ?>
        </div>

        <form class="address-form is-open" id="billing-form" action="<?= topupcenter() . $uri . '/payment/updateBillingCheckout' ?>" style="padding:0; border:none; background:transparent;">
          <input type="hidden" name="user_addresses_id" id="checkout-input-address-id" value="">
          
          <div style="display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; margin-bottom:12px;">
            <div class="field" style="margin:0;">
              <label for="bill-address-title" style="font-size:12px;">Adres Başlığı *</label>
              <input id="bill-address-title" name="user_addresses_title" value="Fatura Adresi" placeholder="Örn: Ev Adresim, İş Adresi" required style="height:38px;">
            </div>
            <label style="display:flex; align-items:center; gap:6px; font-size:11px; color:#adb8c4; cursor:pointer; margin-top:16px;">
              <input type="checkbox" name="user_addresses_is_default" id="checkout-input-is-default" value="1" checked style="width:14px; height:14px; accent-color:#c79a3e;">
              Varsayılan adresim yap
            </label>
          </div>

          <fieldset class="invoice-type" style="margin-bottom:12px;">
            <legend>Fatura türü</legend>
            <label><input type="radio" name="invoice_type" value="individual" checked> Bireysel</label>
            <label><input type="radio" name="invoice_type" value="corporate"> Kurumsal</label>
          </fieldset>
          <fieldset class="invoice-type invoice-status citizen-status" style="margin-bottom:12px;">
            <legend id="invoice-status-label">T.C. vatandaşlık durumu</legend>
            <label><input type="radio" name="is_turkish_citizen" value="1" checked> T.C. vatandaşıyım</label>
            <label><input type="radio" name="is_turkish_citizen" value="0"> T.C. vatandaşı değilim</label>
          </fieldset>
          <fieldset class="invoice-type corporate-status" hidden style="margin-bottom:12px;">
            <legend>T.C. firma durumu</legend>
            <label><input type="radio" name="is_turkish_company" value="1" checked> T.C. firmasıyım</label>
            <label><input type="radio" name="is_turkish_company" value="0"> T.C. firması değilim</label>
          </fieldset>
          <div class="form-grid">
            <div class="field"><label id="bill-name-label" for="bill-name">Ad soyad *</label><input id="bill-name" name="user_bill_company_name" required value="<?= html_escape($billName) ?>"></div>
            <div class="field"><label id="bill-tax-number-label" for="bill-tax-number">T.C. kimlik numarası *</label><input id="bill-tax-number" name="user_bill_tax_number" inputmode="numeric" value="<?= html_escape($user_details->user_bill_tax_number ?? '') ?>"></div>
            <div class="field"><label for="bill-phone">Telefon numarası *</label><input id="bill-phone" type="tel" name="user_addresses_phone" required value="<?= html_escape($valPhone) ?>" placeholder="Örn: 05XXXXXXXXX"></div>
            <div class="field corporate-billing-field" style="display:none;"><label for="bill-tax-office">Vergi dairesi *</label><input id="bill-tax-office" name="user_bill_tax_office" value="<?= html_escape($user_details->user_bill_tax_office ?? '') ?>"></div>
            <div class="field corporate-billing-field turkish-company-only" style="display:none;"><label for="bill-e-invoice">E-fatura</label><select id="bill-e-invoice" name="user_bill_e_invoice"><option value="0">E-fatura mükellefi değilim</option><option value="1">E-fatura mükellefiyim</option></select></div>
            <div class="field turkish-location-field"><label for="bill-city">Şehir *</label><select id="bill-city" name="user_bill_city" required><option value="">Şehir seçiniz</option></select></div>
            <div class="field turkish-location-field"><label for="bill-district">İlçe *</label><select id="bill-district" name="user_bill_district" required disabled><option value="">Önce şehir seçiniz</option></select></div>
            <div class="field foreign-location-field" hidden><label for="bill-country">Ülke *</label><input id="bill-country" type="text" name="user_addresses_country" placeholder="Ülke"></div>
            <div class="field foreign-location-field" hidden><label for="bill-foreign-city">Şehir *</label><input id="bill-foreign-city" type="text" name="user_addresses_foreign_city" placeholder="Şehir"></div>
            <div class="field foreign-location-field" hidden><label for="bill-foreign-district">İlçe / Bölge *</label><input id="bill-foreign-district" type="text" name="user_addresses_foreign_district" placeholder="İlçe / Bölge"></div>
            <div class="field full"><label for="bill-address">Adres *</label><textarea id="bill-address" name="user_bill_address" required rows="3"><?= html_escape($billAddress) ?></textarea></div>
          </div>
          <div class="address-actions" style="margin-top:16px; display:flex; justify-content:flex-end; align-items:center; gap:10px;">
            <button class="btn-quiet" id="cancel-address" type="button" onclick="checkoutCancelForm()" style="height:36px; min-height:36px; padding:0 14px; font-size:.78rem;">Vazgeç</button>
            <button class="btn-primary-action" type="submit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
              Adresi Kaydet
            </button>
          </div>
        </form>
      </div>
    </section>

    <section class="panel payment-panel" aria-labelledby="payment-heading">
      <h2 class="section-heading" id="payment-heading"><span class="heading-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span> Ödeme yöntemi</h2>
      
      <div class="payment-tabs" role="tablist" aria-label="Ödeme yöntemini seç">
        <button class="payment-tab" id="tab-card" role="tab" type="button" aria-selected="true" aria-controls="pane-card" data-method="card"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg> Kredi kartı</button>
        <button class="payment-tab" id="tab-transfer" role="tab" type="button" aria-selected="false" aria-controls="pane-transfer" data-method="transfer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m3 21 18-9L3 3v7l12 2-12 2v7Z"/></svg> Havale / EFT</button>
        <button class="payment-tab" id="tab-mobile" role="tab" type="button" aria-selected="false" aria-controls="pane-mobile" data-method="mobile"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/></svg> Mobil ödeme</button>
        <button class="payment-tab" id="tab-wallet" role="tab" type="button" aria-selected="false" aria-controls="pane-wallet" data-method="wallet"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 8h18M16 14h2"/></svg> Cüzdan</button>
      </div>
      <div class="payment-pane" id="pane-wallet" role="tabpanel" aria-labelledby="tab-wallet" hidden>
        <div class="wallet-summary"><div><span class="wallet-label">Cüzdan bakiyenden kullanılacak</span><strong class="wallet-value" id="wallet-method-amount"><?= nFormat(min($userBalance, $netTotal)) ?> <?= $displayCurrency ?></strong></div><span class="wallet-balance"><small>Kullanılabilir bakiye</small><span id="wallet-method-balance"><?= nFormat($userBalance) ?> <?= $displayCurrency ?></span></span></div>
        <p class="pane-description" id="wallet-pane-note">
            <?php if($userBalance >= $netTotal): ?>
                Ödeme, Playsel cüzdan bakiyenden tamamlanır.
            <?php else: ?>
                Cüzdan bakiyeniz siparişin tamamını karşılamıyor; lütfen farklı bir ödeme yöntemi seçin veya bakiye yükleyin.
                <a href="<?= topupcenter() . $uri . '/profile/wallet' ?>" style="color:var(--accent); display:block; margin-top:5px;">Bakiye Yükle</a>
            <?php endif; ?>
        </p>
      </div>

      <div class="payment-pane" id="pane-transfer" role="tabpanel" aria-labelledby="tab-transfer" hidden>
        <div class="transfer-stage">
          <div class="transfer-content is-locked" id="transfer-content" aria-hidden="true" inert>
        <div class="bank-grid" role="radiogroup" aria-label="Havale yapılacak bankayı seç">
            <?php foreach ($banks as $key => $bank): ?>
                <button type="button" class="bank-option" role="radio" aria-checked="<?= $key === 0 ? 'true' : 'false' ?>" 
                    data-bank-id="<?= (int)$bank->bank_id ?>"
                    data-bank="<?= html_escape($bank->bank_name) ?>"
                        data-iban="<?= html_escape($bank->bank_iban) ?>"
                        data-owner="<?= html_escape($bank->bank_owner) ?>"
                        data-account="<?= html_escape($bank->bank_account_number) ?>"
                        onclick="selectBank(this)">
                    <?= html_escape($bank->bank_name) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <section class="transfer-reference" aria-labelledby="reference-heading">
          <div class="reference-copy-block">
            <div class="reference-title" id="reference-heading">Havale referans numarası</div>
            <p class="reference-help">Açıklama alanına ekleyerek ödemenin siparişinle eşleşmesini sağla.</p>
          </div>
          <div class="reference-control">
            <code id="transfer-reference" class="reference-number"><?= createRefCode(6) ?></code>
            <button class="copy-button reference-copy" id="copy-reference" type="button" data-copy-target="transfer-reference" aria-label="Havale referans numarasını kopyala"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="8" y="8" width="13" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg> Kopyala</button>
          </div>
        </section>

        <section class="bank-details" id="bank-details" aria-label="Seçili banka hesap bilgileri">
          <div class="bank-details-head"><div><span>Havale yapılacak hesap</span><strong id="selected-bank"><?= !empty($banks) ? html_escape($banks[0]->bank_name) : '-' ?></strong></div><span class="bank-account-badge">Alıcı: Playsel</span></div>
          <div class="bank-row"><span class="bank-label">Hesap sahibi</span><strong class="bank-value" id="account-holder"><?= !empty($banks) ? html_escape($banks[0]->bank_owner) : '-' ?></strong><button class="copy-button" type="button" data-copy-value="<?= !empty($banks) ? html_escape($banks[0]->bank_owner) : '-' ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="8" y="8" width="13" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg> Kopyala</button></div>
          <div class="bank-row"><span class="bank-label">IBAN</span><strong class="bank-value" id="bank-iban"><?= !empty($banks) ? html_escape($banks[0]->bank_iban) : '-' ?></strong><button class="copy-button" type="button" data-copy-value="<?= !empty($banks) ? html_escape($banks[0]->bank_iban) : '-' ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="8" y="8" width="13" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg> Kopyala</button></div>
          <div class="bank-transfer-total"><span>Gönderilecek tutar</span><strong id="transfer-amount"><?= nFormat($netTotal) ?> <?= $displayCurrency ?></strong></div>
        </section>
        <p class="transfer-help-note">Gönderdiğin tutar sipariş toplamıyla aynı olmalı. Havale açıklamasında yalnızca bu siparişin referans numarasını kullan.</p>
          </div>
          <div class="transfer-gate" id="transfer-gate">
            <div class="transfer-gate-card">
              <span class="transfer-gate-icon" aria-hidden="true">i</span>
              <h3 id="transfer-gate-title">Havale öncesi küçük bir hatırlatma</h3>
              <p>Ödeme açıklamasına bu siparişe özel referans numaranı ekle. Bu, havalenin doğru siparişle eşleşmesini kolaylaştırır; ödeme ulaştığında siparişin hızlıca onaylanır.</p>
              <button class="guide-next" id="transfer-acknowledge" type="button">Tamam, devam et <span aria-hidden="true">→</span></button>
            </div>
            <?php if (!empty($cartData['user_id']) || !empty($cartData['server_id'])): ?>
                <div class="checkout-target" aria-label="Teslimat bilgileri">
                    <strong>Teslimat bilgileri</strong>
                    <?php if (!empty($cartData['user_id'])): ?><span>Oyuncu ID: <b><?= html_escape($cartData['user_id']) ?></b></span><?php endif; ?>
                    <?php if (!empty($cartData['server_id'])): ?><span>Server ID: <b><?= html_escape($cartData['server_id']) ?></b></span><?php endif; ?>
                </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <div class="payment-pane active" id="pane-card" role="tabpanel" aria-labelledby="tab-card">
        <div class="card-fields">
          <div class="field full"><label for="card-name">Kart üzerindeki isim</label><input id="card-name" type="text" autocomplete="cc-name" placeholder="Ad soyad"></div>
          <div class="field full"><label for="card-number">Kart numarası</label><input id="card-number" inputmode="numeric" autocomplete="cc-number" placeholder="•••• •••• •••• ••••" maxlength="23"></div>
          <div class="field"><label for="card-expiry">Son kullanma tarihi</label><input id="card-expiry" inputmode="numeric" autocomplete="cc-exp" placeholder="AA / YY" maxlength="7"></div>
          <div class="field"><label for="card-cvc">Güvenlik kodu</label><input id="card-cvc" inputmode="numeric" autocomplete="cc-csc" placeholder="CVC / CVV" maxlength="4"></div>
        </div>
        <div class="card-brand-note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/></svg> Kart bilgilerin ödeme sağlayıcısına güvenli şekilde aktarılır.</div>
      </div>

      <div class="payment-pane" id="pane-mobile" role="tabpanel" aria-labelledby="tab-mobile" hidden>
        <div class="mobile-info"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/></svg><div><strong>Telefonuna gelen adımları takip et</strong>Mobil ödeme, operatörün onay ekranı üzerinden tamamlanır.</div></div>
        <div class="field" style="margin-top:14px"><label for="mobile-number">Telefon numarası</label><input id="mobile-number" type="tel" inputmode="tel" autocomplete="tel" readonly value="<?= html_escape(normalizePhoneE164((string)($user_details->user_phone ?? ''))) ?>" placeholder="Hesabınızda onaylı telefon yok"></div>
        <div class="mobile-commission">Mobil ödemelerde, indirim sonrası tutara %<?= nFormat($mobileCommissionRate) ?> komisyon eklenir. Toplam: <strong><?= nFormat($mobileTotal) ?> <?= $displayCurrency ?></strong></div>
      </div>

    </section>
  </div>
  
  <section class="payment-assurance" aria-label="Ödeme yöntemleri ve güvenli ödeme bilgisi">
    <div class="assurance-info"><span class="assurance-lock"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 11V7a7 7 0 0 1 14 0v4"/><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M12 15v3"/></svg></span><span><strong>Güvenli ödeme</strong><small>Ödeme yöntemi bilgileri seçtiğin sağlayıcı üzerinden işlenir.</small></span></div>
    <div class="assurance-logos" aria-label="Kart ağları: Visa, Mastercard, TROY, American Express, Discover, JCB ve UnionPay">
      <span class="visa-mark" aria-label="Visa">VISA</span>
      <span class="mastercard-mark" aria-label="Mastercard"><i></i><i></i><b>mastercard</b></span>
      <span class="troy-mark" aria-label="TROY">TROY</span>
      <span class="amex-mark" aria-label="American Express">AMEX</span>
      <span class="secure-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 22 1.5-.7C18.5 19 21 15.5 21 11V5l-9-3-9 3v6c0 4.5 2.5 8 7.5 10.3L12 22Z"/><path d="m9 12 2 2 4-4"/></svg> Korumalı ödeme</span>
    </div>
  </section>
</main>
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<dialog id="phone-verify-modal" style="padding:0;background:#151c26;color:#f5f7fa;border:1px solid #394451;border-radius:14px;max-width:420px;width:calc(100% - 32px)">
  <form id="phone-verify-form" method="dialog" style="padding:22px">
    <h2 style="margin:0 0 8px;font-size:1.1rem">Telefon doğrulama</h2>
    <p style="color:#aeb8c5;font-size:.8rem">Ödemeye devam etmek için telefon numaranızı doğrulayın.</p>
    <div class="field"><label for="checkout-phone">Telefon numarası</label><input id="checkout-phone" type="tel" autocomplete="tel" required></div>
    <div class="field" id="checkout-otp-field" hidden><label for="checkout-otp">6 haneli kod</label><input id="checkout-otp" inputmode="numeric" maxlength="6"></div>
    <p id="phone-verify-message" class="coupon-message" aria-live="polite"></p>
    <div class="address-actions"><button class="btn-quiet" type="button" id="phone-verify-cancel">Vazgeç</button><button class="btn-quiet" type="submit" id="phone-verify-submit">Kod gönder</button></div>
  </form>
</dialog>
<script>
if (typeof window.fbq === 'function') {
  window.fbq('track', 'InitiateCheckout', { value: <?= (float)$netTotal ?>, currency: 'TRY' });
}
(() => {
  function initializeCheckout($) {
    const baseTotal = <?= (float)$netTotal ?>;
      const creditCardTotal = <?= (float)$creditCardTotal ?>;
      const creditCardFee = <?= (float)$creditCardFee ?>;
      const mobileTotal = <?= (float)$mobileTotal ?>;
      const mobileFee = <?= (float)$mobileFee ?>;
    const walletBalance = <?= (float)$userBalance ?>;
  
  const tabs = [...document.querySelectorAll(".payment-tab")];
  const panes = [...document.querySelectorAll(".payment-pane")];
  const banks = [...document.querySelectorAll(".bank-option")];
  const terms = document.querySelector("#terms-check");
  const termsWarning = document.querySelector("#terms-warning");
  const termsLabel = document.querySelector("#terms-label");
  const payButton = document.querySelector("#pay-button");
  const transferGate = document.querySelector("#transfer-gate");
  const transferContent = document.querySelector("#transfer-content");
  const cardName = document.querySelector("#card-name");
  const cardNumber = document.querySelector("#card-number");
  const cardExpiry = document.querySelector("#card-expiry");
  const cardCvc = document.querySelector("#card-cvc");
  const mobileNumber = document.querySelector("#mobile-number");
  const billingForm = document.querySelector("#billing-form");
  const phoneModal = document.querySelector("#phone-verify-modal");
  const phoneForm = document.querySelector("#phone-verify-form");
  const phoneInput = document.querySelector("#checkout-phone");
  const phoneMessage = document.querySelector("#phone-verify-message");
  
  let activeMethod = "card";
  let transferAcknowledged = (function() {
    try {
      return localStorage.getItem('playsel_transfer_gate_dismissed') === '1' ||
             localStorage.getItem('playsel_checkout_transfer_dismissed') === '1';
    } catch(e) {
      return false;
    }
  })();
  if (transferAcknowledged) {
    if (transferGate) transferGate.hidden = true;
    if (transferContent) {
      transferContent.classList.remove("is-locked");
      transferContent.removeAttribute("aria-hidden");
      transferContent.removeAttribute("inert");
    }
  }
  let toastTimer;
  let phoneVerified = <?= !empty($user_details->user_phone_is_verified) ? 'true' : 'false' ?>;
  let phoneOtpSent = false;
  const citiesData = <?= !empty($cities_json) ? $cities_json : '{}' ?>;
  const addressesList = <?= json_encode($addressList ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const addressesMap = {};
  if (Array.isArray(addressesList)) {
    addressesList.forEach(function(a) {
      if (a && a.user_addresses_id !== undefined && a.user_addresses_id !== null) {
        addressesMap[a.user_addresses_id] = a;
      }
    });
  }
  let isProgrammaticChange = false;

  const invoiceTypeInputs = [...document.querySelectorAll('input[name="invoice_type"]')];
  const corporateBillingFields = [...document.querySelectorAll(".corporate-billing-field")];
  const turkishCompanyOnlyFields = [...document.querySelectorAll(".turkish-company-only")];
  const citizenStatusFields = [...document.querySelectorAll(".citizen-status")];
  const corporateStatusFields = [...document.querySelectorAll(".corporate-status")];
  const citizenStatusInputs = [...document.querySelectorAll('input[name="is_turkish_citizen"]')];
  const corporateStatusInputs = [...document.querySelectorAll('input[name="is_turkish_company"]')];
  const turkishLocationFields = [...document.querySelectorAll(".turkish-location-field")];
  const foreignLocationFields = [...document.querySelectorAll(".foreign-location-field")];
  const cityInput = document.querySelector("#bill-city");
  const districtInput = document.querySelector("#bill-district");
  const foreignCityInput = document.querySelector("#bill-foreign-city");
  const foreignDistrictInput = document.querySelector("#bill-foreign-district");
  const savedCity = <?= json_encode($user_details->user_bill_city ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const savedDistrict = <?= json_encode($user_details->user_bill_district ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  function getDistrictsForCity(city) {
    if (!city || typeof citiesData !== 'object') return [];
    if (Array.isArray(citiesData[city])) return citiesData[city];
    const cityUpper = String(city).trim().toLocaleUpperCase('tr');
    for (const k in citiesData) {
      if (k.trim().toLocaleUpperCase('tr') === cityUpper && Array.isArray(citiesData[k])) {
        return citiesData[k];
      }
    }
    return [];
  }

  function updateDistricts(selectedDistrict) {
    const city = cityInput.value;
    const districts = getDistrictsForCity(city);
    const targetDist = (selectedDistrict !== undefined && selectedDistrict !== null && selectedDistrict !== '') ? selectedDistrict : '';

    districtInput.innerHTML = '<option value="">' + (districts.length > 0 ? 'İlçe seçiniz' : 'Önce şehir seçiniz') + '</option>';

    let matchedVal = '';
    if (districts.length > 0) {
      const cleanTarget = String(targetDist || '').trim().toLocaleUpperCase('tr');
      districts.forEach(function(district) {
        const option = document.createElement("option");
        option.value = district;
        option.textContent = district;
        if (cleanTarget && cleanTarget === String(district).trim().toLocaleUpperCase('tr')) {
          option.selected = true;
          matchedVal = district;
        }
        districtInput.appendChild(option);
      });
      districtInput.disabled = false;
    } else {
      districtInput.disabled = true;
    }

    districtInput.value = matchedVal;

    if (window.jQuery && typeof $.fn.select2 === "function") {
      const $dist = $(districtInput);
      $dist.prop('disabled', districts.length === 0);
      if ($dist.data('select2')) {
        $dist.select2('destroy');
      }
      $dist.select2({
        width: "100%",
        dropdownParent: $("#billing-form"),
        placeholder: districts.length > 0 ? "İlçe seçiniz" : "Önce şehir seçiniz"
      });
      if (matchedVal) {
        $dist.val(matchedVal).trigger('change');
      } else {
        $dist.val('').trigger('change');
      }
    }
  }

  function updateBillingFields() {
    const isCorporate = document.querySelector('input[name="invoice_type"]:checked') && document.querySelector('input[name="invoice_type"]:checked').value === "corporate";
    corporateBillingFields.forEach(function(field) { field.style.display = isCorporate ? 'grid' : 'none'; });
    citizenStatusFields.forEach(function(field) { field.hidden = isCorporate; });
    corporateStatusFields.forEach(function(field) { field.hidden = !isCorporate; });
    const selectedCitizenStatus = document.querySelector('input[name="is_turkish_citizen"]:checked');
    const selectedCompanyStatus = document.querySelector('input[name="is_turkish_company"]:checked');
    const isTurkishCitizen = selectedCitizenStatus && selectedCitizenStatus.value === "1";
    const isTurkishCompany = selectedCompanyStatus && selectedCompanyStatus.value === "1";
    const usesTurkishLocations = !isCorporate ? isTurkishCitizen : isTurkishCompany;
    const isTurkishCompanyMode = isCorporate && isTurkishCompany;

    turkishLocationFields.forEach(function(field) { field.style.display = usesTurkishLocations ? 'grid' : 'none'; });
    foreignLocationFields.forEach(function(field) { field.hidden = usesTurkishLocations; field.style.display = usesTurkishLocations ? 'none' : 'grid'; });
    turkishCompanyOnlyFields.forEach(function(field) { field.style.display = isTurkishCompanyMode ? 'grid' : 'none'; });

    cityInput.required = usesTurkishLocations;
    districtInput.required = usesTurkishLocations;
    cityInput.disabled = !usesTurkishLocations;
    districtInput.disabled = !usesTurkishLocations || !cityInput.value;

    foreignCityInput.required = !usesTurkishLocations;
    foreignDistrictInput.required = !usesTurkishLocations;
    foreignCityInput.disabled = usesTurkishLocations;
    foreignDistrictInput.disabled = usesTurkishLocations;

    const foreignCountryInput = document.querySelector("#bill-country");
    if (foreignCountryInput) {
      foreignCountryInput.required = !usesTurkishLocations;
      foreignCountryInput.disabled = usesTurkishLocations;
    }

    const taxOffice = document.querySelector("#bill-tax-office");
    const taxNumber = document.querySelector("#bill-tax-number");
    const eInvoice = document.querySelector("#bill-e-invoice");

    if (taxOffice) {
      taxOffice.disabled = !isCorporate;
      taxOffice.required = isCorporate && isTurkishCompany;
    }
    if (taxNumber) {
      taxNumber.disabled = false;
      taxNumber.required = isCorporate ? isTurkishCompany : isTurkishCitizen;
      taxNumber.maxLength = !isCorporate && isTurkishCitizen ? 11 : (isCorporate && isTurkishCompany ? 10 : 32);
      taxNumber.inputMode = usesTurkishLocations ? "numeric" : "text";
      taxNumber.placeholder = !isCorporate
        ? (isTurkishCitizen ? "11 haneli T.C. Kimlik Numarası" : "Pasaport veya Kimlik No")
        : (isTurkishCompany ? "10 haneli Vergi Numarası" : "Vergi veya Kayıt Numarası");
    }
    if (eInvoice) {
      eInvoice.disabled = !isTurkishCompanyMode;
    }

    const nameLabel = document.querySelector("#bill-name-label");
    if (nameLabel) nameLabel.textContent = isCorporate ? "Firma unvanı *" : "Ad soyad *";

    const taxLabel = document.querySelector("#bill-tax-number-label");
    if (taxLabel) {
      taxLabel.textContent = isCorporate
        ? (isTurkishCompany ? "Vergi numarası *" : "Vergi / Kayıt Numarası")
        : (isTurkishCitizen ? "T.C. kimlik numarası *" : "Yabancı kimlik / vergi numarası");
    }

    if (window.jQuery) {
      $(cityInput).prop('disabled', !usesTurkishLocations);
      $(districtInput).prop('disabled', !usesTurkishLocations || !cityInput.value);
    }
  }

  function initializeLocationSelects() {
    cityInput.innerHTML = '<option value="">Şehir seçiniz</option>';
    const cityList = Object.keys(citiesData);
    cityList.sort(function(a, b) { return a.localeCompare(b, 'tr'); });
    cityList.forEach(function(city) {
      const option = document.createElement("option");
      option.value = city;
      option.textContent = city;
      if (savedCity && savedCity.toLocaleUpperCase('tr') === city.toLocaleUpperCase('tr')) {
        option.selected = true;
      }
      cityInput.appendChild(option);
    });

    if (window.jQuery && typeof $.fn.select2 === "function") {
      const $city = $(cityInput);
      if (!$city.data('select2')) {
        $city.select2({
          width: "100%",
          dropdownParent: $("#billing-form"),
          placeholder: "Şehir seçiniz"
        });
        $city.off('change.loc select2:select.loc').on('change.loc select2:select.loc', function() {
          if (isProgrammaticChange) return;
          updateDistricts('');
        });
      }
      const $dist = $(districtInput);
      if (!$dist.data('select2')) {
        $dist.select2({
          width: "100%",
          dropdownParent: $("#billing-form"),
          placeholder: "Önce şehir seçiniz"
        });
      }
    }

    cityInput.onchange = function() {
      if (isProgrammaticChange) return;
      updateDistricts('');
    };

    if (cityInput.value) {
      updateDistricts(savedDistrict);
    } else {
      updateDistricts('');
    }
  }

  invoiceTypeInputs.forEach(function(input) { input.addEventListener("change", updateBillingFields); });
  citizenStatusInputs.forEach(function(input) { input.addEventListener("change", updateBillingFields); });
  corporateStatusInputs.forEach(function(input) { input.addEventListener("change", updateBillingFields); });

  window.checkoutSelectAddress = function(id) {
    if (id === undefined || id === null) return;
    const $ = window.jQuery || window.$;
    if (!$) return;
    $.ajax({
      url: '<?= topupcenter() . $uri . "/payment/selectAddressCheckout" ?>',
      type: 'POST',
      data: {
        address_id: id,
        csrf_token: '<?= $this->security->get_csrf_hash() ?>'
      },
      dataType: 'json'
    }).done(function(res) {
      if (res.status === 'success') {
        document.querySelectorAll('.checkout-addr-card').forEach(function(card) {
          card.classList.remove('is-selected');
          card.style.borderColor = '#303a48';
          card.style.background = '#111720';
          const indicator = card.querySelector('.addr-radio-indicator');
          if (indicator) {
            indicator.style.borderColor = '#556577';
            indicator.style.background = 'transparent';
          }
          const badge = card.querySelector('.badge-selected');
          if (badge) badge.style.display = 'none';
          const selectBtn = card.querySelector('.addr-select-btn');
          if (selectBtn) {
            selectBtn.textContent = 'Bu Adresi Seç';
            selectBtn.style.color = 'inherit';
          }
        });

        const selectedCard = document.getElementById('addr-card-' + id);
        if (selectedCard) {
          selectedCard.classList.add('is-selected');
          selectedCard.style.borderColor = '#ffb300';
          selectedCard.style.background = 'linear-gradient(180deg, #182029 0%, #121820 100%)';
          const indicator = selectedCard.querySelector('.addr-radio-indicator');
          if (indicator) {
            indicator.style.borderColor = '#ffb300';
            indicator.style.background = '#ffb300';
          }
          const badge = selectedCard.querySelector('.badge-selected');
          if (badge) badge.style.display = 'inline-block';
          const selectBtn = selectedCard.querySelector('.addr-select-btn');
          if (selectBtn) {
            selectBtn.textContent = 'Seçildi';
            selectBtn.style.color = '#ffd66d';
          }
        }
        if (typeof showToast === 'function') showToast('Fatura adresi seçildi.');
        else if (typeof toastr !== 'undefined') toastr.info('Fatura adresi seçildi.');
      } else {
        if (typeof showToast === 'function') showToast(res.message || 'Adres seçilemedi.');
        else if (typeof toastr !== 'undefined') toastr.error(res.message || 'Adres seçilemedi.');
      }
    }).fail(function(xhr) {
      const msg = (xhr.responseJSON || {}).message || 'Adres seçilemedi.';
      if (typeof showToast === 'function') showToast(msg);
      else if (typeof toastr !== 'undefined') toastr.error(msg);
    });
  };

  window.checkoutEditAddress = function(id) {
    if (id === undefined || id === null) return;
    const addr = addressesMap[id];
    if (!addr) return;

    const formContainer = document.getElementById('checkout-form-container');
    const form = document.getElementById('billing-form');
    if (!formContainer || !form) return;

    isProgrammaticChange = true;

    document.getElementById('checkout-input-address-id').value = addr.user_addresses_id || '';
    document.getElementById('checkout-form-heading').textContent = 'Fatura Adresini Düzenle: ' + (addr.user_addresses_title || 'Fatura Adresi');
    document.getElementById('bill-address-title').value = addr.user_addresses_title || 'Fatura Adresi';
    document.getElementById('checkout-input-is-default').checked = (addr.user_addresses_is_default == 1);

    const isCorp = (addr.user_addresses_invoice_type == 2);
    const invRadio = document.querySelector('input[name="invoice_type"][value="' + (isCorp ? 'corporate' : 'individual') + '"]');
    if (invRadio) invRadio.checked = true;

    const citVal = String(addr.user_addresses_is_turkish_citizen !== undefined ? addr.user_addresses_is_turkish_citizen : 1);
    const citRadio = document.querySelector('input[name="is_turkish_citizen"][value="' + citVal + '"]');
    if (citRadio) citRadio.checked = true;

    const compVal = String(addr.user_addresses_is_turkish_company !== undefined ? addr.user_addresses_is_turkish_company : 1);
    const compRadio = document.querySelector('input[name="is_turkish_company"][value="' + compVal + '"]');
    if (compRadio) compRadio.checked = true;

    document.getElementById('bill-name').value = (isCorp ? addr.user_addresses_company_name : addr.user_addresses_full_name) || '';
    document.getElementById('bill-tax-number').value = addr.user_addresses_tax_number || '';
    document.getElementById('bill-tax-office').value = addr.user_addresses_tax_office || '';
    document.getElementById('bill-e-invoice').value = addr.user_addresses_is_e_invoice_taxpayer || 0;
    document.getElementById('bill-address').value = addr.user_addresses_address || '';
    const billPhone = document.getElementById('bill-phone');
    if (billPhone) {
      billPhone.value = addr.user_addresses_phone || <?= json_encode($valPhone, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    }

    const billCountry = document.getElementById('bill-country');
    if (billCountry) billCountry.value = addr.user_addresses_country || '';
    const billForeignCity = document.getElementById('bill-foreign-city');
    if (billForeignCity) billForeignCity.value = addr.user_addresses_city || '';
    const billForeignDistrict = document.getElementById('bill-foreign-district');
    if (billForeignDistrict) billForeignDistrict.value = addr.user_addresses_district || '';

    updateBillingFields();

    const usesTr = !isCorp ? (citVal === '1') : (compVal === '1');
    if (usesTr) {
      let matchedCity = '';
      const savedCityVal = addr.user_addresses_city || '';
      for (let i = 0; i < cityInput.options.length; i++) {
        if (cityInput.options[i].value && cityInput.options[i].value.toLocaleUpperCase('tr') === savedCityVal.toLocaleUpperCase('tr')) {
          matchedCity = cityInput.options[i].value;
          break;
        }
      }
      cityInput.value = matchedCity;
      if (window.jQuery && typeof $.fn.select2 === "function") {
        $(cityInput).val(matchedCity).trigger('change');
      }
      updateDistricts(addr.user_addresses_district || '');
    }

    formContainer.style.display = 'block';
    setTimeout(function() {
      isProgrammaticChange = false;
    }, 50);

    formContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  window.checkoutOpenNewAddress = function() {
    const formContainer = document.getElementById('checkout-form-container');
    const form = document.getElementById('billing-form');
    if (!formContainer || !form) return;

    isProgrammaticChange = true;

    document.getElementById('checkout-input-address-id').value = '';
    document.getElementById('checkout-form-heading').textContent = 'Yeni Fatura Adresi Ekle';
    document.getElementById('bill-address-title').value = 'Fatura Adresi';
    document.getElementById('checkout-input-is-default').checked = (addressesList.length === 0);

    const invRadio = document.querySelector('input[name="invoice_type"][value="individual"]');
    if (invRadio) invRadio.checked = true;

    const citRadio = document.querySelector('input[name="is_turkish_citizen"][value="1"]');
    if (citRadio) citRadio.checked = true;

    const compRadio = document.querySelector('input[name="is_turkish_company"][value="1"]');
    if (compRadio) compRadio.checked = true;

    const userFullName = <?= json_encode($billName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    document.getElementById('bill-name').value = userFullName || '';
    document.getElementById('bill-tax-number').value = '';
    document.getElementById('bill-tax-office').value = '';
    document.getElementById('bill-e-invoice').value = '0';
    document.getElementById('bill-address').value = '';
    const billPhoneNew = document.getElementById('bill-phone');
    if (billPhoneNew) {
      billPhoneNew.value = <?= json_encode($valPhone, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    }

    const billCountry = document.getElementById('bill-country');
    if (billCountry) billCountry.value = '';
    const billForeignCity = document.getElementById('bill-foreign-city');
    if (billForeignCity) billForeignCity.value = '';
    const billForeignDistrict = document.getElementById('bill-foreign-district');
    if (billForeignDistrict) billForeignDistrict.value = '';

    updateBillingFields();

    cityInput.value = '';
    if (window.jQuery && typeof $.fn.select2 === "function") {
      $(cityInput).val('').trigger('change');
    }
    updateDistricts('');

    formContainer.style.display = 'block';
    setTimeout(function() {
      isProgrammaticChange = false;
    }, 50);

    formContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  window.checkoutCancelForm = function() {
    const formContainer = document.getElementById('checkout-form-container');
    if (formContainer && addressesList.length > 0) {
      formContainer.style.display = 'none';
    }
  };

  if (billingForm) {
    billingForm.addEventListener("submit", function(event) {
      event.preventDefault();
      $.ajax({
        url: billingForm.action,
        type: "POST",
        data: $(billingForm).serialize(),
        dataType: "json"
      })
      .done(function(res) {
        if (res.status === "success") {
          window.location.reload();
        } else {
          showToast(res.message || "Fatura bilgileri kaydedilemedi.");
        }
      })
      .fail(function(xhr) {
        showToast((xhr.responseJSON || {}).message || "Fatura bilgileri kaydedilemedi.");
      });
    });
  }

  initializeLocationSelects();
  updateBillingFields();

  const phoneIti = window.intlTelInput ? window.intlTelInput(phoneInput, {
    initialCountry: "tr",
    preferredCountries: ["tr", "us", "gb", "de"],
    separateDialCode: true,
    strictMode: true,
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/23.0.4/js/utils.js"
  }) : null;

  window.applyCoupon = function() {
    const code = document.querySelector("#coupon-code").value.trim();
    const message = document.querySelector("#coupon-message");
    if (!code) { message.textContent = "Kupon kodu boş olamaz."; return; }
    $.ajax({
      url: '<?= topupcenter() . $uri . "/payment/useCoupon" ?>', type: "POST", dataType: "json",
      data: {coupon_code: code, csrf_token: '<?= $this->security->get_csrf_hash() ?>'}
    }).done(function(res) {
      message.textContent = res.message || "";
      if (res.status === "success" && res.redirect_url) window.location.href = res.redirect_url;
    }).fail(function(xhr) {
      message.textContent = (xhr.responseJSON || {}).message || "Kupon uygulanamadı.";
    });
  };

  const removeCouponButton = document.querySelector("#remove-coupon");
  if (removeCouponButton) {
    removeCouponButton.addEventListener("click", function() {
      $.ajax({
        url: '<?= topupcenter() . $uri . "/payment/removeCoupon" ?>',
        type: "POST",
        data: {csrf_token: '<?= $this->security->get_csrf_hash() ?>'},
        dataType: "json"
      }).done(function(res) {
        if (res.status === "success" && res.redirect_url) window.location.href = res.redirect_url;
        else showToast(res.message || "Kupon kaldırılamadı.");
      }).fail(function(xhr) {
        showToast((xhr.responseJSON || {}).message || "Kupon kaldırılamadı.");
      });
    });
  }

  document.querySelector("#phone-verify-cancel").addEventListener("click", function() { phoneModal.close(); });
  phoneForm.addEventListener("submit", function(event) {
    event.preventDefault();
    const submit = document.querySelector("#phone-verify-submit");
    submit.disabled = true;
    const endpoint = phoneOtpSent ? "verifyCheckoutPhoneOtp" : "requestCheckoutPhoneOtp";
    const data = phoneOtpSent
      ? {otp_code: document.querySelector("#checkout-otp").value, csrf_token: '<?= $this->security->get_csrf_hash() ?>'}
      : {phone_number: phoneIti ? phoneIti.getNumber() : phoneInput.value, csrf_token: '<?= $this->security->get_csrf_hash() ?>'};
    $.ajax({url: '<?= topupcenter() . $uri . "/payment/" ?>' + endpoint, type: "POST", data: data, dataType: "json"})
      .done(function(res) {
        phoneMessage.textContent = res.message || "";
        if (res.status === "success") {
          if (!phoneOtpSent) {
            phoneOtpSent = true;
            document.querySelector("#checkout-otp-field").hidden = false;
            submit.textContent = "Doğrula";
            document.querySelector("#checkout-otp").focus();
          } else {
            phoneVerified = true;
            mobileNumber.value = phoneIti ? phoneIti.getNumber() : phoneInput.value;
            phoneModal.close();
            submitPayment();
          }
        }
      }).fail(function(xhr) { phoneMessage.textContent = (xhr.responseJSON || {}).message || "Telefon doğrulaması başarısız."; })
      .always(function() { submit.disabled = false; });
  });

  cardNumber.addEventListener("input", () => {
    const digits = cardNumber.value.replace(/\D/g, "").slice(0, 19);
    cardNumber.value = digits.replace(/(.{4})/g, "$1 ").trim();
  });
  cardExpiry.addEventListener("input", () => {
    const digits = cardExpiry.value.replace(/\D/g, "").slice(0, 4);
    cardExpiry.value = digits.length > 2 ? digits.slice(0, 2) + " / " + digits.slice(2) : digits;
  });
  cardCvc.addEventListener("input", () => {
    cardCvc.value = cardCvc.value.replace(/\D/g, "").slice(0, 4);
  });

  function chooseMethod(method){
    activeMethod=method;
    tabs.forEach(tab=>{
      const selected=tab.dataset.method===method;
      tab.setAttribute("aria-selected", String(selected));
      if(selected) tab.classList.add("active"); else tab.classList.remove("active");
    });
    panes.forEach(pane=>{
      pane.hidden=pane.id!=="pane-"+method;
      if (!pane.hidden) pane.classList.add("active");
      else pane.classList.remove("active");
    });

    const feeRow = document.querySelector("#fee-row");
    const feeValue = document.querySelector("#fee-value");
    const feeLabel = document.querySelector("#fee-label");
    const summaryTotal = document.querySelector("#summary-total");
    const formatAmount = value => Number(value).toLocaleString("tr-TR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }) + " <?= $displayCurrency ?>";
    if (method === "card") {
      feeRow.hidden = false;
      feeLabel.textContent = "Kredi kartı komisyonu (%<?= nFormat($creditCardCommissionRate) ?>)";
      feeValue.textContent = formatAmount(creditCardFee);
      summaryTotal.textContent = formatAmount(creditCardTotal);
    } else if (method === "mobile") {
      feeRow.hidden = false;
      feeLabel.textContent = "Mobil ödeme komisyonu (%<?= nFormat($mobileCommissionRate) ?>)";
      feeValue.textContent = formatAmount(mobileFee);
      summaryTotal.textContent = formatAmount(mobileTotal);
    } else {
      feeRow.hidden = true;
      summaryTotal.textContent = formatAmount(baseTotal);
    }
    
    if(method==="transfer"&&!transferAcknowledged){
      transferGate.hidden=false;
      transferContent.classList.add("is-locked");
      transferContent.setAttribute("aria-hidden","true");
      transferContent.setAttribute("inert","");
    } else if(method==="transfer"){
      transferGate.hidden=true;
      transferContent.classList.remove("is-locked");
      transferContent.removeAttribute("aria-hidden");
      transferContent.removeAttribute("inert");
    }
  }
  
  tabs.forEach(tab=>{
    tab.addEventListener("click", ()=>chooseMethod(tab.dataset.method));
  });

  window.selectBank = function(btn) {
    banks.forEach(option=>option.setAttribute("aria-checked", "false"));
    btn.setAttribute("aria-checked", "true");
    document.querySelector("#selected-bank").textContent = btn.dataset.bank;
    document.querySelector("#account-holder").textContent = btn.dataset.owner;
    document.querySelector("#bank-iban").textContent = btn.dataset.iban;
    document.querySelector("#bank-account-no").textContent = btn.dataset.account;
    
    document.querySelector("#account-holder").nextElementSibling.dataset.copyValue = btn.dataset.owner;
    document.querySelector("#bank-iban").nextElementSibling.dataset.copyValue = btn.dataset.iban;
    document.querySelector("#bank-account-no").nextElementSibling.dataset.copyValue = btn.dataset.account;
  };

  terms.addEventListener("change",()=>{
    if(terms.checked){
        termsWarning.hidden=true;
        termsLabel.classList.remove("is-invalid");
        terms.removeAttribute("aria-invalid");
    }
  });

  document.querySelector("#transfer-acknowledge").addEventListener("click",()=>{
    transferAcknowledged=true;
    try {
      localStorage.setItem('playsel_transfer_gate_dismissed', '1');
      localStorage.setItem('playsel_checkout_transfer_dismissed', '1');
    } catch(e) {}
    transferGate.hidden=true;
    transferContent.classList.remove("is-locked");
    transferContent.removeAttribute("aria-hidden");
    transferContent.removeAttribute("inert");
  });

  document.querySelectorAll("[data-copy-target],[data-copy-value]").forEach(button=>button.addEventListener("click",async()=>{
    const value = button.dataset.copyValue || document.getElementById(button.dataset.copyTarget).textContent.trim();
    try{
        await navigator.clipboard.writeText(value);
        showToast("Kopyalandı: "+value);
    } catch(e) {
      const field = document.createElement("textarea");field.value=value;field.style.position="fixed";field.style.opacity="0";document.body.append(field);field.select();
      const copied=document.execCommand("copy");field.remove();showToast(copied?"Kopyalandı: "+value:"Kopyalama bu tarayıcıda kullanılamıyor.");
    }
  }));

  let paymentInFlight = false;
  window.submitPayment = function() {
    if (paymentInFlight) {
      return;
    }
    if (!phoneVerified) {
      phoneModal.showModal();
      return;
    }
    if(!terms.checked){
      termsWarning.hidden=false;
      termsLabel.classList.add("is-invalid");
      terms.setAttribute("aria-invalid","true");
      terms.focus();
      terms.scrollIntoView({behavior:"smooth",block:"center"});
      return;
    }
    
    if(activeMethod === "wallet" && walletBalance < baseTotal) {
        showToast("Cüzdan bakiyeniz yetersiz!");
        return;
    }
    
    const $button = $('#pay-button');
    let apiMethod = activeMethod === 'card' ? 'credit' : activeMethod;
    
    let paymentPayload = {
        payment_method: apiMethod,
        csrf_token: '<?= $this->security->get_csrf_hash() ?>'
    };

    if (apiMethod === 'transfer') {
        paymentPayload.bank_name = document.querySelector("#selected-bank").textContent;
        const selectedBank = document.querySelector(".bank-option[aria-checked='true']");
        paymentPayload.eft_bank_id = selectedBank ? selectedBank.dataset.bankId : '';
        paymentPayload.eft_ref_code = document.querySelector("#transfer-reference").textContent.trim();
    }
    if (apiMethod === 'credit') {
        paymentPayload.cc_name = cardName.value.trim();
        paymentPayload.cc_number = cardNumber.value.replace(/\D/g, '');
        paymentPayload.cc_expire = cardExpiry.value.replace(/\s/g, '');
        paymentPayload.cc_cvv = cardCvc.value.trim();
        if (!paymentPayload.cc_name || paymentPayload.cc_number.length < 12 || !/^\d{2}\/\d{2}$/.test(paymentPayload.cc_expire) || !/^\d{3,4}$/.test(paymentPayload.cc_cvv)) {
            showToast("Lütfen kredi kartı bilgilerini eksiksiz ve doğru girin.");
            return;
        }
    }
    if (apiMethod === 'mobile') {
        paymentPayload.phone_number = mobileNumber.value.trim();
    }

    $.ajax({
        url: '<?= topupcenter() . $uri . "/payment/finalize" ?>',
        type: 'POST',
        data: paymentPayload,
        dataType: 'json',
        beforeSend: function() {
            paymentInFlight = true;
            $button.prop('disabled', true).find('#pay-button-label').text('İşleminiz Yapılıyor...');
        },
        success: function(res) {
            if (res.status === 'success') {
                if (res.redirect_url) {
                    window.location.href = res.redirect_url;
                } else {
                    if (typeof toastr !== 'undefined') toastr.success(res.message || 'Siparişiniz başarıyla alındı.');
                    else showToast(res.message || 'Siparişiniz başarıyla alındı.');
                }
            } else if (res.status === '3d_redirect') {
                if (res.redirect_url) {
                    window.top.location.replace(res.redirect_url);
                } else {
                    document.open();
                    document.write(res.html_content || '');
                    document.close();
                }
            } else {
                if (typeof toastr !== 'undefined') toastr.error(res.message || 'Ödeme sırasında bir hata oluştu.');
                else showToast(res.message || 'Ödeme sırasında bir hata oluştu.');
                paymentInFlight = false;
                $button.prop('disabled', false).find('#pay-button-label').text('Ödemeyi yap');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON || {};
            const message = response.message || 'Sistem hatası oluştu, lütfen tekrar deneyin.';
            if (typeof toastr !== 'undefined') toastr.error(message);
            else showToast(message);
            // Çift gönderim (409/429) durumunda buton kısa süre daha kilitli kalır
            const cooldown = (xhr.status === 409 || xhr.status === 429) ? 5000 : 0;
            setTimeout(function() {
                paymentInFlight = false;
                $button.prop('disabled', false).find('#pay-button-label').text('Ödemeyi yap');
            }, cooldown);
        }
    });
  };

  function showToast(message, type = 'info'){
    if (typeof toastr !== 'undefined') {
      if (type === 'error') toastr.error(message);
      else if (type === 'success') toastr.success(message);
      else toastr.info(message);
      return;
    }
    const toast=document.querySelector("#toast");
    if(!toast) return;
    toast.textContent=message;
    toast.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer=setTimeout(()=>toast.classList.remove("show"),2800);
  }
  window.showToast = showToast;

    // Initialize
    chooseMethod("card");
  }

  if (window.jQuery) {
    window.jQuery(initializeCheckout);
  } else {
    document.addEventListener("DOMContentLoaded", function() {
      if (window.jQuery) window.jQuery(initializeCheckout);
    });
  }
})();
</script>