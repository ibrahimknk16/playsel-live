<style>
#customers-page{--c-panel:#191925;--c-panel2:#1a1a27;--c-inner:#222233;--c-line:#303044;--c-line2:#29293a;--c-text:#f0f0f6;--c-text2:#d8d8e6;--c-muted:#9999af;--c-gold:#ffc21a;--c-input:#1e1e2d;--c-input-line:#343449;font-family:Poppins,Arial,sans-serif;color:var(--c-text2);font-size:11px;min-width:0}
#customers-page *{box-sizing:border-box}
#customers-page .crumb{color:#777b96;font-size:10px;margin:0 0 12px}
#customers-page .crumb b{color:var(--c-text)}
#customers-page .panel{background:var(--c-panel);border:1px solid var(--c-line);border-radius:10px;margin-bottom:14px;overflow:hidden}
#customers-page .panel-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--c-line)}
#customers-page .panel-heading h1{font-size:14px;font-weight:700;margin:0 0 3px;color:var(--c-text);line-height:1.4}
#customers-page .panel-heading p{margin:0;font-size:10px;color:var(--c-muted)}
#customers-page .toolbar{display:flex;gap:8px;align-items:center}
#customers-page .btn{height:33px;padding:0 12px;border-radius:6px;background:#27273a;color:#e8e8f2;border:1px solid #3a3a51;font:600 10px Poppins,Arial,sans-serif;display:inline-flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;cursor:pointer;transition:background .15s,border-color .15s,color .15s}
#customers-page .btn:hover{background:#35354b;color:#fff}
#customers-page .btn svg{width:14px;height:14px;flex:none}
#customers-page .btn-primary{background:var(--c-gold);border-color:var(--c-gold);color:#17131a}
#customers-page .btn-primary:hover{background:#ffca46;color:#17131a}
#customers-page .btn:focus-visible,#customers-page input:focus-visible,#customers-page select:focus-visible{outline:2px solid var(--c-gold);outline-offset:2px}
#customers-page .filter-wrap{padding:14px 18px 16px}
#customers-page .customer-searchbar{display:flex;align-items:center;gap:9px}
#customers-page .customer-search-input{display:flex;align-items:center;gap:9px;height:34px;background:var(--c-input);border:1px solid var(--c-input-line);border-radius:6px;color:#85859c;padding:0 12px;flex:1;min-width:170px;max-width:540px}
#customers-page .customer-search-input:focus-within{border-color:#c68e1a}
#customers-page .customer-search-input input{height:100%;width:100%;background:transparent;border:0;outline:0;color:#f2f2f7;font-size:11px}
#customers-page .customer-search-input input:focus-visible{outline:0}
#customers-page .customer-filter-trigger[aria-expanded=true]{color:var(--c-gold);border-color:#a67713;background:#272218}
#customers-page #filterIndicator:not(:empty){background:var(--c-gold);color:#17131a;border-radius:10px;padding:1px 6px;font-size:9px}
#customers-page .customer-advanced{padding-top:13px}
#customers-page .customer-advanced[hidden]{display:none}
#customers-page .customer-filter-fields{display:flex;gap:10px;align-items:end;flex-wrap:wrap;padding:13px;background:var(--c-panel2);border:1px solid var(--c-line);border-radius:7px}
#customers-page .customer-filter-fields .field{flex:1;min-width:155px;max-width:245px}
#customers-page .customer-filter-fields label{display:block;font-size:10px;color:var(--c-muted);margin-bottom:5px}
#customers-page select,#customers-page .customer-ip-search input{height:33px;width:100%;background:var(--c-inner);border:1px solid var(--c-input-line);border-radius:5px;color:#eee;padding:0 9px;font:inherit}
#customers-page .customer-ip-search{display:flex;gap:6px}
#customers-page .customer-ip-search .btn{height:33px}
#customers-page .subbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 18px;border-bottom:1px solid var(--c-line);color:var(--c-muted);font-size:10px}
#customers-page .subbar strong{color:var(--c-text)}
#customers-page .mobile-tip{display:none}
#customers-page .table-scroll{overflow-x:auto}
#customers-page table.customers{border-collapse:collapse;width:100%;min-width:1400px}
#customers-page .customers th{text-align:left;background:var(--c-panel2);color:#a7a7bb;font-size:10px;font-weight:600;border-bottom:1px solid var(--c-line);padding:13px 12px;white-space:nowrap}
#customers-page .customers th.sort{cursor:pointer;user-select:none}
#customers-page .customers th.sort:hover{color:#ffbf3d}
#customers-page .sortmark{display:inline-block;color:#5c5c75;margin-left:4px}
#customers-page .customers th.active-sort .sortmark{color:var(--c-gold)}
#customers-page .customers td{padding:10px 12px;border-bottom:1px solid var(--c-line2);color:var(--c-text2);vertical-align:middle;height:60px;font-size:10px}
#customers-page .customers tbody tr{transition:background .15s}
#customers-page .customers tbody tr:hover{background:rgba(255,194,26,.035)}
#customers-page .customers tbody tr:last-child td{border-bottom:0}
#customers-page .id{color:var(--c-muted);font-weight:600}
#customers-page .user-cell{display:flex;align-items:center;gap:10px;min-width:0}
#customers-page .avatar{width:30px;height:30px;border-radius:50%;background:#253650;color:#83bdff;display:grid;place-items:center;font-size:11px;font-weight:700;flex:none}
#customers-page .user-cell strong{display:block;font-size:10px;color:var(--c-text);font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:170px}
#customers-page .user-cell strong a{color:inherit;text-decoration:none}
#customers-page .user-cell strong a:hover{color:var(--c-gold)}
#customers-page .user-cell small{font-size:9px;color:#87879f}
#customers-page .contact{display:flex;flex-direction:column;gap:5px;min-width:215px}
#customers-page .contact-line{display:flex;align-items:center;gap:6px;min-height:19px;white-space:nowrap}
#customers-page .contact-line a{color:#c6c6d8;text-decoration:none;overflow:hidden;text-overflow:ellipsis;max-width:200px}
#customers-page .contact-line a:hover{color:var(--c-gold)}
#customers-page .contact-line .none{color:#6d6d84}
#customers-page .verify{display:inline-grid;place-items:center;width:15px;height:15px;border-radius:50%;font-size:9px;font-weight:700;flex:none}
#customers-page .verify.ok{background:#18382d;color:#63d9a6}
#customers-page .verify.no{background:#44341c;color:#ffcf6b}
#customers-page .copy{display:inline-grid;place-items:center;width:23px;height:23px;padding:0;border:1px solid transparent;border-radius:5px;background:transparent;color:#8e90a7;cursor:pointer}
#customers-page .copy:hover,#customers-page .copy:focus-visible{color:var(--c-gold);border-color:#51452d;background:#332b23}
#customers-page .copy svg{width:13px;height:13px}
#customers-page .wa{display:inline-grid;place-items:center;width:23px;height:23px;border-radius:5px;color:#4ade80;border:1px solid transparent;text-decoration:none}
#customers-page .wa:hover,#customers-page .wa:focus-visible{background:#18382d;border-color:#245c45;color:#63d9a6}
#customers-page .wa svg{width:14px;height:14px}
#customers-page .country strong{display:block;color:var(--c-text);font-weight:600}
#customers-page .country small{color:#77788f;font-size:9px}
#customers-page .money{font-weight:600;color:#57d69d;font-variant-numeric:tabular-nums;white-space:nowrap}
#customers-page .money.neg{color:#ff668f}
#customers-page .money-w{font-weight:500;color:var(--c-text2);font-variant-numeric:tabular-nums;white-space:nowrap}
#customers-page .tag{display:inline-block;font-size:9px;font-weight:600;border-radius:4px;padding:4px 7px;white-space:nowrap}
#customers-page .tag.end{background:#1d3249;color:#70b9ff}
#customers-page .tag.dealer{background:#3b3020;color:#ffc368}
#customers-page .tag.active{background:#18382d;color:#63d9a6}
#customers-page .tag.passive{background:#40252e;color:#f18b9b}
#customers-page .user-cell .flags{display:flex;gap:4px;margin-top:3px}
#customers-page .tag.restricted{background:#40252e;color:#f18b9b;padding:2px 6px}
#customers-page .tag.fraud{background:#3b3020;color:#ffc368;padding:2px 6px}
#customers-page .customer-filter-fields input[type=date]{color-scheme:dark}
#customers-page .statusline{display:flex;align-items:center;gap:8px;white-space:nowrap}
#customers-page .switch{position:relative;width:30px;height:17px;display:inline-block;flex:none}
#customers-page .switch input{position:absolute;opacity:0;inset:0;width:100%;height:100%;margin:0;cursor:pointer;z-index:1}
#customers-page .switch span{position:absolute;inset:0;border-radius:20px;background:#49495a;transition:.2s}
#customers-page .switch span:before{content:"";position:absolute;width:13px;height:13px;top:2px;left:2px;border-radius:50%;background:#fff;transition:.2s}
#customers-page .switch input:checked+span{background:#159bf1}
#customers-page .switch input:checked+span:before{transform:translateX(13px)}
#customers-page .switch input:focus-visible+span{outline:2px solid var(--c-gold);outline-offset:2px}
#customers-page .detail-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;height:29px;min-width:78px;padding:0 12px;border:1px solid #404057;border-radius:6px;background:#252538;color:#e6e6f0;font-size:10px;font-weight:600;text-decoration:none;white-space:nowrap;transition:all .16s}
#customers-page .detail-btn:hover{background:#323247;border-color:#6a6070;color:#fff}
#customers-page .detail-btn svg{width:14px;height:14px}
#customers-page td.act,#customers-page th.act{text-align:center}
#customers-page .empty{text-align:center;padding:40px;color:var(--c-muted)}
#customers-page .loading{opacity:.55;pointer-events:none}
#customers-page .foot{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:60px;padding:13px 18px;border-top:1px solid var(--c-line)}
#customers-page .foot-left{display:flex;align-items:center;gap:11px;white-space:nowrap;color:var(--c-muted);font-size:10px}
#customers-page .foot-left select{width:auto;min-width:92px}
#customers-page .pager{display:flex;gap:5px;flex-wrap:wrap}
#customers-page .pager button{min-width:29px;height:29px;border:1px solid #36374c;background:#242538;color:#c6c8dd;border-radius:5px;cursor:pointer;font:600 10px Poppins,Arial,sans-serif}
#customers-page .pager button:hover:not(:disabled){border-color:#8a6921;color:var(--c-gold)}
#customers-page .pager button.on{background:var(--c-gold);border-color:var(--c-gold);color:#17131a}
#customers-page .pager button:disabled{opacity:.35;cursor:default}
@media(max-width:950px){#customers-page .customer-searchbar{flex-wrap:wrap}#customers-page .customer-search-input{max-width:none;flex-basis:100%}}
@media(max-width:700px){#customers-page .panel-heading{flex-wrap:wrap}#customers-page .mobile-tip{display:inline}#customers-page .foot{flex-wrap:wrap}}
</style>

<div id="customers-page">
    <div class="crumb">Müşteri Yönetimi / <b>Müşteri Listesi</b></div>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h1>Müşteri Listesi</h1>
                <p>Kayıtlı müşterileri görüntüleyin, filtreleyin ve hesap durumlarını yönetin.</p>
            </div>
            <div class="toolbar">
                <button type="button" class="btn" id="exportBtn">
                    <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3v12m-4-4 4 4 4-4"/><path d="M4 16v4h16v-4"/></svg>
                    <span>Dışa Aktar</span>
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewUser">
                    <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    <span>Müşteri Ekle</span>
                </button>
            </div>
        </div>

        <div class="filter-wrap">
            <div class="customer-searchbar">
                <div class="customer-search-input">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg>
                    <input type="search" id="fSearch" aria-label="Müşteri ara" placeholder="Ad soyad, müşteri ID, e-posta veya telefon ile ara..." autocomplete="off">
                </div>
                <button type="button" class="btn customer-filter-trigger" id="toggleFilters" aria-expanded="false">
                    <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 7h16M7 12h10M10 17h4"/></svg>
                    <span>Filtreler</span><span id="filterIndicator"></span>
                </button>
                <button type="button" class="btn" id="resetBtn">
                    <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7M3 4v5h5"/></svg>
                    <span>Temizle</span>
                </button>
            </div>

            <div class="customer-advanced" id="advancedFilters" hidden>
                <div class="customer-filter-fields">
                    <div class="field">
                        <label for="fType">Müşteri Tipi</label>
                        <select id="fType">
                            <option value="">Tüm Müşteri Tipleri</option>
                            <option value="1">Son Kullanıcı</option>
                            <option value="2">Dijipin</option>
                            <option value="3">API Kullanıcısı</option>
                            <option value="4">API Özel Kullanıcısı</option>
                            <option value="5">Playsel</option>
                            <option value="6">Ödeme Kuruluşu</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="fStatus">Hesap Durumu</label>
                        <select id="fStatus">
                            <option value="">Tüm Durumlar</option>
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="fBalance">Bakiye Durumu</label>
                        <select id="fBalance">
                            <option value="">Tüm Bakiyeler</option>
                            <option value="positive">Bakiyesi Olanlar</option>
                            <option value="zero">Bakiyesi Olmayanlar</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="fCountry">Kayıt Ülkesi</label>
                        <select id="fCountry" data-countries='<?= html_escape(json_encode(array_values($countries ?? []))) ?>'>
                            <option value="">Tüm Ülkeler</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="fMailVerified">E-posta Onayı</label>
                        <select id="fMailVerified">
                            <option value="">Tümü</option>
                            <option value="yes">Onaylı</option>
                            <option value="no">Onaysız</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="fPhoneVerified">Telefon Onayı</label>
                        <select id="fPhoneVerified">
                            <option value="">Tümü</option>
                            <option value="yes">Onaylı</option>
                            <option value="no">Onaysız</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="fIp">Kayıt IP Adresi</label>
                        <div class="customer-ip-search">
                            <input id="fIp" placeholder="Örn. 85.106.6.10" aria-label="Kayıt IP adresi" autocomplete="off">
                        </div>
                    </div>
                    <div class="field">
                        <label for="fId">Müşteri ID</label>
                        <div class="customer-ip-search"><input id="fId" type="number" min="1" placeholder="Örn. 1024" autocomplete="off"></div>
                    </div>
                    <div class="field">
                        <label for="fName">Ad Soyad</label>
                        <div class="customer-ip-search"><input id="fName" type="text" maxlength="100" placeholder="Ad veya soyad" autocomplete="off"></div>
                    </div>
                    <div class="field">
                        <label for="fDateFrom">Kayıt Tarihi (Başlangıç)</label>
                        <div class="customer-ip-search"><input id="fDateFrom" type="date"></div>
                    </div>
                    <div class="field">
                        <label for="fDateTo">Kayıt Tarihi (Bitiş)</label>
                        <div class="customer-ip-search"><input id="fDateTo" type="date"></div>
                    </div>
                    <div class="field">
                        <label for="fRestricted">Kısıtlama</label>
                        <select id="fRestricted">
                            <option value="">Tümü</option>
                            <option value="yes">Kısıtlı</option>
                            <option value="no">Kısıtlı Değil</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="fFraud">Fraud Belgesi</label>
                        <select id="fFraud">
                            <option value="">Tümü</option>
                            <option value="yes">Belgesi Olanlar</option>
                            <option value="no">Belgesi Olmayanlar</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" id="filterBtn">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 7h16M7 12h10M10 17h4"/></svg>
                        <span>Filtrele</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h1 style="font-size:13px">Tüm Müşteriler</h1>
                <p id="resultSummary">Müşteri kayıtları</p>
            </div>
            <span style="color:#8d8da4;font-size:10px">Başlıklara tıklayarak sıralayabilirsiniz ↕</span>
        </div>
        <div class="subbar">
            <span>Toplam <strong id="totalCount">0</strong> müşteri</span>
            <span class="mobile-tip">Tabloyu yana kaydırarak inceleyebilirsiniz →</span>
            <span id="activeFilterText">Tüm kayıtlar</span>
        </div>
        <div class="table-scroll">
            <table class="customers">
                <thead>
                    <tr>
                        <th class="sort" data-sort="id">ID <span class="sortmark">↕</span></th>
                        <th class="sort" data-sort="name">Müşteri <span class="sortmark">↕</span></th>
                        <th class="sort" data-sort="email">İletişim Bilgileri <span class="sortmark">↕</span></th>
                        <th class="sort" data-sort="country">Kayıt Ülkesi <span class="sortmark">↕</span></th>
                        <th class="sort" data-sort="balance">Bakiye <span class="sortmark">↕</span></th>
                        <th class="sort" data-sort="withdrawable">Çekilebilir Bakiye <span class="sortmark">↕</span></th>
                        <th class="sort" data-sort="type">Müşteri Tipi <span class="sortmark">↕</span></th>
                        <th class="sort" data-sort="active">Durum <span class="sortmark">↕</span></th>
                        <th>Son Giriş</th>
                        <th>Sipariş / Harcama</th>
                        <th class="sort" data-sort="date">Kayıt Tarihi <span class="sortmark">↕</span></th>
                        <th class="act">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="customerRows"></tbody>
            </table>
        </div>
        <div class="foot">
            <div class="foot-left">
                <select id="pageSize" aria-label="Sayfa başına kayıt">
                    <option value="10">10 kayıt</option>
                    <option value="15" selected>15 kayıt</option>
                    <option value="25">25 kayıt</option>
                    <option value="50">50 kayıt</option>
                    <option value="100">100 kayıt</option>
                </select>
                <span id="rangeText">—</span>
            </div>
            <div class="pager" id="pager"></div>
        </div>
    </section>
</div>

<div class="modal fade" id="addNewUser" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold mb-1">Yeni Kullanıcı</h2>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>

            <div class="modal-body pt-6">
                <form class="form w-100" method="POST" id="kt_sign_in_form" action="<?= base_url('admins/addUser') ?>">
                    <input type="hidden" name="admin_action_csrf" value="<?= html_escape($adminActionCsrfToken) ?>">

                    <div class="fv-row mb-5">
                        <label class="form-label fw-semibold">Kullanıcı Adı</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text"><i class="ki-duotone ki-user fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                            <input type="text" name="name" autocomplete="off" class="form-control form-control-solid" required />
                        </div>
                    </div>

                    <div class="fv-row mb-5">
                        <label class="form-label fw-semibold">Email</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text"><i class="ki-duotone ki-sms fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                            <input type="email" name="email" autocomplete="off" class="form-control form-control-solid" required />
                        </div>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="form-label fw-semibold">Şifre</label>
                        <div class="position-relative">
                            <div class="input-group input-group-solid">
                                <span class="input-group-text"><i class="fa-solid fa-key"><span class="path1"></span><span class="path2"></span></i></span>
                                <input id="new_user_password" type="password" name="password" autocomplete="new-password" class="form-control form-control-solid" required />
                                <button type="button" class="btn btn-icon btn-active-light-primary" id="toggleNewUserPass" tabindex="-1">
                                    <i class="ki-duotone ki-eye-slash fs-2" id="iconPassHide"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    <i class="ki-duotone ki-eye fs-2 d-none" id="iconPassShow"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary w-100" id="btnCreateAdmin">
                            <span class="indicator-label">Kaydet</span>
                            <span class="indicator-progress" style="display:none;">Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var LIST_URL = "<?= base_url('users/getUsersList') ?>";
    var EXPORT_URL = "<?= base_url('users/exportUsersCsv') ?>";
    var STATUS_URL = "<?= site_url('users/changeUserStatus') ?>/";
    var ADMIN_ACTION_CSRF = <?= json_encode($adminActionCsrfToken) ?>;
    jQuery.ajaxSetup({ headers: { 'X-Admin-CSRF': ADMIN_ACTION_CSRF } });
    var $ = function (id) { return document.getElementById(id); };
    var state = { page: 1, sort: 'id', dir: 'asc', total: 0, filtered: 0, req: 0 };
    var typeClass = { 'Son Kullanıcı': 'end' };
    var regionNames = (window.Intl && Intl.DisplayNames) ? new Intl.DisplayNames(['tr'], { type: 'region' }) : null;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function money(n) {
        return Number(n).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
    }
    function countryName(code) {
        if (!code) return '—';
        try { return regionNames ? (regionNames.of(String(code).toUpperCase()) || code) : code; } catch (e) { return code; }
    }
    function notify(type, msg) {
        if (typeof toastr !== 'undefined') toastr[type](msg);
    }

    var icons = {
        copy: '<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 0 1 2-2h9"/></svg>',
        mail: '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
        phone: '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>',
        eye: '<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>',
        wa: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.9L2 22l5.25-1.4A9.9 9.9 0 1 0 12.04 2Zm0 18.1a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-3.1.82.83-3.02-.2-.31a8.2 8.2 0 1 1 6.97 3.84Zm4.5-6.1c-.25-.12-1.46-.72-1.68-.8-.23-.08-.4-.12-.56.12-.17.25-.64.8-.78.96-.14.17-.29.19-.53.06a6.7 6.7 0 0 1-3.3-2.88c-.25-.43.25-.4.72-1.33.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.41-.56-.42h-.47c-.17 0-.43.06-.65.31-.23.25-.86.84-.86 2.05s.88 2.38 1 2.55c.12.17 1.73 2.65 4.2 3.72.59.25 1.05.4 1.4.52.59.19 1.13.16 1.55.1.47-.07 1.46-.6 1.66-1.17.2-.58.2-1.07.14-1.17-.06-.1-.23-.17-.48-.29Z"/></svg>'
    };

    function verify(ok) {
        return '<span class="verify ' + (ok ? 'ok' : 'no') + '" title="' + (ok ? 'Onaylı' : 'Onaysız') + '">' + (ok ? '✓' : '!') + '</span>';
    }
    function copyBtn(val, label) {
        return '<button type="button" class="copy" data-copy="' + esc(val) + '" title="' + label + '" aria-label="' + label + '">' + icons.copy + '</button>';
    }

    function rowHtml(c) {
        var phone = c.phone || '';
        var wa = c.whatsapp_url ? '<a class="wa" href="' + esc(c.whatsapp_url) + '" target="_blank" rel="noopener noreferrer" title="WhatsApp ile mesaj gönder" aria-label="WhatsApp ile mesaj gönder">' + icons.wa + '</a>' : '';
        var email = c.email
            ? '<div class="contact-line"><span class="tiny-icon">' + icons.mail + '</span><a href="mailto:' + esc(c.email) + '">' + esc(c.email) + '</a>' + verify(c.email_verified) + copyBtn(c.email, 'E-posta kopyala') + '</div>'
            : '<div class="contact-line"><span class="tiny-icon">' + icons.mail + '</span><span class="none">—</span></div>';
        var tel = phone
            ? '<div class="contact-line"><span class="tiny-icon">' + icons.phone + '</span><a href="tel:' + esc(phone) + '">' + esc(phone) + '</a>' + verify(c.phone_verified) + copyBtn(phone, 'Telefon kopyala') + wa + '</div>'
            : '<div class="contact-line"><span class="tiny-icon">' + icons.phone + '</span><span class="none">—</span></div>';
        var initial = esc(String(c.name || 'M').trim().charAt(0).toLocaleUpperCase('tr') || 'M');
        var cls = typeClass[c.type] || 'dealer';
        var flags = (c.restricted || c.fraud)
            ? '<div class="flags">' + (c.restricted ? '<span class="tag restricted">Kısıtlı</span>' : '') + (c.fraud ? '<span class="tag fraud">Fraud belgesi</span>' : '') + '</div>'
            : '';
        return '<tr>' +
            '<td class="id">#' + c.id + '</td>' +
            '<td><div class="user-cell"><span class="avatar">' + initial + '</span><div><strong><a href="' + esc(c.url) + '">' + esc(c.name) + '</a></strong><small>Playsel müşterisi</small>' + flags + '</div></div></td>' +
            '<td><div class="contact">' + email + tel + '</div></td>' +
            '<td class="country"><strong>' + esc(countryName(c.country)) + '</strong><small>' + esc(c.register_ip || '—') + '</small></td>' +
            '<td><span class="money' + (c.balance < 0 ? ' neg' : '') + '">' + money(c.balance) + '</span></td>' +
            '<td><span class="money-w">' + money(c.withdrawable) + '</span></td>' +
            '<td><span class="tag ' + cls + '">' + esc(c.type) + '</span></td>' +
            '<td><div class="statusline"><span class="tag ' + (c.active ? 'active' : 'passive') + ' js-status-badge">' + (c.active ? 'Aktif' : 'Pasif') + '</span>' +
            '<label class="switch"><input type="checkbox" class="js-user-status-switch" data-user-id="' + c.id + '"' + (c.active ? ' checked' : '') + ' aria-label="Hesap durumunu değiştir"><span></span></label></div></td>' +
            '<td>' + esc(c.last_login || '—') + '</td>' +
            '<td><span class="money-w">' + esc(c.orders) + ' sipariş</span><br><small style="color:#87879f">' + money(c.spend) + '</small></td>' +
            '<td>' + esc(c.created) + '</td>' +
            '<td class="act"><a class="detail-btn" href="' + esc(c.url) + '">' + icons.eye + 'Detay</a></td>' +
            '</tr>';
    }

    function filterPayload() {
        return {
            search: { value: $('fSearch').value.trim() },
            tier: $('fType').value,
            status: $('fStatus').value,
            balance: $('fBalance').value,
            country: $('fCountry').value,
            mail_verified: $('fMailVerified').value,
            phone_verified: $('fPhoneVerified').value,
            ip: $('fIp').value.trim(),
            id: $('fId').value.trim(),
            name: $('fName').value.trim(),
            date_from: $('fDateFrom').value,
            date_to: $('fDateTo').value,
            restricted: $('fRestricted').value,
            fraud: $('fFraud').value,
            sort: state.sort,
            dir: state.dir
        };
    }

    function activeFilterLabels() {
        var out = [];
        [['fType', 'Tip'], ['fStatus', 'Durum'], ['fBalance', 'Bakiye'], ['fCountry', 'Ülke'], ['fMailVerified', 'E-posta'], ['fPhoneVerified', 'Telefon'], ['fRestricted', 'Kısıtlama'], ['fFraud', 'Fraud']].forEach(function (p) {
            var el = $(p[0]);
            if (el.value) out.push(p[1] + ': ' + el.options[el.selectedIndex].text);
        });
        if ($('fIp').value.trim()) out.push('IP: ' + $('fIp').value.trim());
        if ($('fId').value.trim()) out.push('ID: ' + $('fId').value.trim());
        if ($('fName').value.trim()) out.push('Ad: ' + $('fName').value.trim());
        if ($('fDateFrom').value) out.push('Kayıt ≥ ' + $('fDateFrom').value);
        if ($('fDateTo').value) out.push('Kayıt ≤ ' + $('fDateTo').value);
        if ($('fSearch').value.trim()) out.push('Arama: ' + $('fSearch').value.trim());
        return out;
    }

    function renderPager(pages) {
        var el = $('pager'), html = '';
        function b(label, page, dis, on) {
            return '<button type="button" data-page="' + page + '"' + (dis ? ' disabled' : '') + (on ? ' class="on"' : '') + '>' + label + '</button>';
        }
        html += b('‹', state.page - 1, state.page <= 1);
        var start = Math.max(1, state.page - 2), end = Math.min(pages, start + 4);
        start = Math.max(1, end - 4);
        for (var p = start; p <= end; p++) html += b(p, p, false, p === state.page);
        html += b('›', state.page + 1, state.page >= pages);
        el.innerHTML = pages > 0 ? html : '';
    }

    function load() {
        var size = parseInt($('pageSize').value, 10) || 15;
        var req = ++state.req;
        var tbody = $('customerRows');
        tbody.classList.add('loading');
        var payload = filterPayload();
        payload.draw = req;
        payload.start = (state.page - 1) * size;
        payload.length = size;

        jQuery.ajax({ url: LIST_URL, type: 'POST', dataType: 'json', data: payload }).done(function (res) {
            if (req !== state.req) return;
            var rows = (res && res.data) || [];
            state.total = res.recordsTotal || 0;
            state.filtered = res.recordsFiltered || 0;
            tbody.innerHTML = rows.length ? rows.map(rowHtml).join('') : '<tr><td colspan="12" class="empty">Aramanıza uygun müşteri bulunamadı.</td></tr>';
            var pages = Math.max(1, Math.ceil(state.filtered / size));
            if (state.page > pages && state.filtered > 0) { state.page = pages; return load(); }
            $('totalCount').textContent = state.filtered;
            var from = state.filtered ? payload.start + 1 : 0;
            $('rangeText').textContent = from + ' – ' + Math.min(payload.start + rows.length, state.filtered) + ' / ' + state.filtered + ' kayıt';
            $('resultSummary').textContent = state.filtered + ' kayıt listeleniyor';
            var labels = activeFilterLabels();
            $('activeFilterText').textContent = labels.length ? labels.join(' · ') : 'Tüm kayıtlar';
            var count = labels.length - ($('fSearch').value.trim() ? 1 : 0);
            $('filterIndicator').textContent = count > 0 ? count : '';
            renderPager(pages);
        }).fail(function () {
            if (req !== state.req) return;
            tbody.innerHTML = '<tr><td colspan="12" class="empty">Müşteri listesi yüklenemedi. Lütfen tekrar deneyin.</td></tr>';
            notify('error', 'Müşteri listesi yüklenemedi.');
        }).always(function () {
            if (req === state.req) tbody.classList.remove('loading');
        });
    }
    function reload() { state.page = 1; load(); }

    function updateSortMarks() {
        document.querySelectorAll('#customers-page th.sort').forEach(function (th) {
            var on = th.dataset.sort === state.sort;
            th.classList.toggle('active-sort', on);
            th.querySelector('.sortmark').textContent = on ? (state.dir === 'asc' ? '▲' : '▼') : '↕';
        });
    }

    // Ülke seçenekleri
    (function () {
        var sel = $('fCountry'), list = [];
        try { list = JSON.parse(sel.getAttribute('data-countries') || '[]'); } catch (e) {}
        list.forEach(function (code) {
            var o = document.createElement('option');
            o.value = String(code).toUpperCase();
            o.textContent = countryName(code);
            sel.appendChild(o);
        });
    })();

    var searchTimer;
    $('fSearch').addEventListener('input', function () { clearTimeout(searchTimer); searchTimer = setTimeout(reload, 350); });
    $('fSearch').addEventListener('keydown', function (e) { if (e.key === 'Enter') { clearTimeout(searchTimer); reload(); } });
    $('fIp').addEventListener('keydown', function (e) { if (e.key === 'Enter') reload(); });
    ['fId', 'fName'].forEach(function (id) { $(id).addEventListener('keydown', function (e) { if (e.key === 'Enter') reload(); }); });
    $('filterBtn').addEventListener('click', reload);
    ['fType', 'fStatus', 'fBalance', 'fCountry', 'fMailVerified', 'fPhoneVerified', 'fRestricted', 'fFraud', 'fDateFrom', 'fDateTo'].forEach(function (id) { $(id).addEventListener('change', reload); });
    $('pageSize').addEventListener('change', reload);

    $('toggleFilters').addEventListener('click', function () {
        var box = $('advancedFilters'), open = box.hidden;
        box.hidden = !open;
        this.setAttribute('aria-expanded', String(open));
    });
    $('resetBtn').addEventListener('click', function () {
        $('fSearch').value = ''; $('fIp').value = ''; $('fId').value = ''; $('fName').value = ''; $('fDateFrom').value = ''; $('fDateTo').value = '';
        ['fType', 'fStatus', 'fBalance', 'fCountry', 'fMailVerified', 'fPhoneVerified', 'fRestricted', 'fFraud'].forEach(function (id) { $(id).value = ''; });
        state.sort = 'id'; state.dir = 'asc';
        updateSortMarks();
        reload();
    });
    document.querySelectorAll('#customers-page th.sort').forEach(function (th) {
        th.addEventListener('click', function () {
            var key = th.dataset.sort;
            state.dir = state.sort === key && state.dir === 'asc' ? 'desc' : 'asc';
            state.sort = key;
            updateSortMarks();
            reload();
        });
    });
    $('pager').addEventListener('click', function (e) {
        var b = e.target.closest('button[data-page]');
        if (!b || b.disabled) return;
        state.page = parseInt(b.dataset.page, 10);
        load();
    });

    function copyText(val) {
        if (!val) return;
        var done = function () { notify('success', 'Kopyalandı'); };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(val).then(done);
            return;
        }
        var t = document.createElement('input');
        t.style.position = 'absolute'; t.style.left = '-9999px'; t.value = val;
        document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); done(); } catch (err) {}
        document.body.removeChild(t);
    }
    $('customerRows').addEventListener('click', function (e) {
        var b = e.target.closest('[data-copy]');
        if (b) copyText(b.getAttribute('data-copy'));
    });

    // Durum değiştir
    $('customerRows').addEventListener('change', function (e) {
        var sw = e.target.closest('.js-user-status-switch');
        if (!sw) return;
        var newStatus = sw.checked ? 1 : 0;
        sw.disabled = true;
        jQuery.ajax({ url: STATUS_URL + sw.dataset.userId, type: 'POST', dataType: 'json', data: { status: newStatus } }).done(function (res) {
            if (res && res.status === 'success') {
                notify('success', res.message || 'Güncellendi');
                var badge = sw.closest('.statusline').querySelector('.js-status-badge');
                badge.className = 'tag js-status-badge ' + (newStatus ? 'active' : 'passive');
                badge.textContent = newStatus ? 'Aktif' : 'Pasif';
            } else {
                sw.checked = !sw.checked;
                notify('error', (res && res.message) || 'Güncellenemedi');
            }
        }).fail(function () {
            sw.checked = !sw.checked;
            notify('error', 'İşlem sırasında hata oluştu.');
        }).always(function () { sw.disabled = false; });
    });

    // Dışa aktar: mevcut filtrelerin tüm sonucu sunucuda CSV olarak üretilir
    $('exportBtn').addEventListener('click', function () {
        var q = filterPayload();
        q.search = q.search.value;
        window.location.href = EXPORT_URL + '?' + jQuery.param(q);
    });

    // Yeni kullanıcı modalı
    document.addEventListener('click', function (e) {
        var t = e.target.closest('#toggleNewUserPass');
        if (!t) return;
        var inp = $('new_user_password'), isPass = inp.type === 'password';
        inp.type = isPass ? 'text' : 'password';
        $('iconPassHide').classList.toggle('d-none', isPass);
        $('iconPassShow').classList.toggle('d-none', !isPass);
    });
    document.addEventListener('submit', function (e) {
        if (e.target.id !== 'kt_sign_in_form') return;
        var btn = $('btnCreateAdmin');
        btn.disabled = true;
        btn.querySelector('.indicator-label').style.display = 'none';
        btn.querySelector('.indicator-progress').style.display = '';
    });

    updateSortMarks();
    load();
})();
</script>
