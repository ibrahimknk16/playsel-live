<style>
    body {
        background: #0f172a;
    }

    .card {
        border: none;
    }

    .price {
        font-variant-numeric: tabular-nums;
    }

    .nav-tabs,
    .nav-pills {
        flex-wrap: wrap;
    }

    .nav-tabs .nav-link,
    .nav-pills .nav-link {
        margin-bottom: .5rem;
    }
</style>
</head>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-12">
            <div class="card shadow-lg card-flush">
                <div class="card-body p-0">

                    <!-- Başlık -->
                    <div class="p-4 border-bottom bg-secondary rounded-top">
                        <h1 class="h4 mb-1">Ürün Fiyatları</h1>
                        <p class="mb-0 text-dark">Üstten kategori seç; içeride site sekmeleri arasında gez.</p>
                    </div>

                    <!-- ANA KATEGORİ SEKMELERİ -->
                    <ul class="nav nav-pills bg-dark-subtle m-3" id="categoryTabs" role="tablist">
                        <li class="nav-item"><button class="btn btn-outline-danger me-2 active" id="mlbb-tab"
                                data-bs-toggle="tab" data-bs-target="#mlbb" type="button" role="tab">Mobile
                                Legends</button></li>
                        <li class="nav-item"><button class="btn btn-outline-danger" id="pubgm-tab"
                                data-bs-toggle="tab" data-bs-target="#pubgm" type="button" role="tab">Pubg
                                Mobile</button></li>
                    </ul>

                    <div class="tab-content" id="categoryTabsContent">
                        <!-- ===================== KATEGORİ 1 ===================== -->
                        <div class="tab-pane fade show active" id="mlbb" role="tabpanel" aria-labelledby="mlbb-tab">
                            <!-- Şirket sekmeleri -->
                            <ul class="nav nav-tabs px-3 pt-3 bg-body-tertiary" id="mlbb-companyTabs"
                                role="tablist">
                                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
                                        data-bs-target="#mlbb-X" type="button">GameSatış</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#mlbb-Y" type="button">HesapComTR</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#mlbb-Z" type="button">VatanGame</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#mlbb-A" type="button">ByNoGame</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#mlbb-B" type="button">PerDigital</button></li>
                            </ul>

                            <div class="tab-content bg-body-tertiary" id="mlbb-companyTabsContent">
                                <!-- mlbb - X -->
                                <div class="tab-pane fade show active" id="mlbb-X">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- mlbb - Y -->
                                <div class="tab-pane fade" id="mlbb-Y">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">355,00</td>
                                                    <td class="text-end price">30,50</td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- mlbb - Z -->
                                <div class="tab-pane fade" id="mlbb-Z">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">340,00</td>
                                                    <td class="text-end price">29,00</td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- mlbb - A -->
                                <div class="tab-pane fade" id="mlbb-A">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody">
                                                <tr>

                                                </tr>

                                                </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- mlbb - B -->
                                <div class="tab-pane fade" id="mlbb-B">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- mlbb - C -->
                                <div class="tab-pane fade" id="mlbb-C">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">365,00</td>
                                                    <td class="text-end price">31,20</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- mlbb - D -->
                                <div class="tab-pane fade" id="mlbb-D">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">380,00</td>
                                                    <td class="text-end price">32,00</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- mlbb - E -->
                                <div class="tab-pane fade" id="mlbb-E">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">390,00</td>
                                                    <td class="text-end price">32,50</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- mlbb - F -->
                                <div class="tab-pane fade" id="mlbb-F">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">395,00</td>
                                                    <td class="text-end price">33,00</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===================== KATEGORİ 2 ===================== -->
                        <div class="tab-pane fade" id="pubgm" role="tabpanel" aria-labelledby="pubgm-tab">
                            <!-- Şirket sekmeleri -->
                            <ul class="nav nav-tabs px-3 pt-3 bg-body-tertiary" id="pubgm-companyTabs"
                                role="tablist">
                                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
                                        data-bs-target="#pubgm-X" type="button">GameSatış</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#pubgm-Y" type="button">HesapComTR</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#pubgm-Z" type="button">VatanGame</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#pubgm-A" type="button">ByNoGame</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#pubgm-B" type="button">PerDigital</button></li>
                            </ul>

                            <div class="tab-content bg-body-tertiary" id="pubgm-companyTabsContent">
                                <!-- pubgm - X -->
                                <div class="tab-pane fade show active" id="pubgm-X">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">315,00</td>
                                                    <td class="text-end price">26,00</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- pubgm - Y -->
                                <div class="tab-pane fade" id="pubgm-Y">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">320,00</td>
                                                    <td class="text-end price">26,50</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- pubgm - Z -->
                                <div class="tab-pane fade" id="pubgm-Z">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">310,00</td>
                                                    <td class="text-end price">25,80</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- pubgm - A -->
                                <div class="tab-pane fade" id="pubgm-A">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">325,00</td>
                                                    <td class="text-end price">26,80</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- pubgm - B -->
                                <div class="tab-pane fade" id="pubgm-B">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">330,00</td>
                                                    <td class="text-end price">27,10</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- pubgm - C -->
                                <div class="tab-pane fade" id="pubgm-C">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">335,00</td>
                                                    <td class="text-end price">27,30</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- pubgm - D -->
                                <div class="tab-pane fade" id="pubgm-D">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">340,00</td>
                                                    <td class="text-end price">27,60</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- pubgm - E -->
                                <div class="tab-pane fade" id="pubgm-E">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">345,00</td>
                                                    <td class="text-end price">27,90</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- pubgm - F -->
                                <div class="tab-pane fade" id="pubgm-F">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0 table-row-dashed">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ürün</th>
                                                    <th class="text-end">TR Fiyatı (₺)</th>
                                                    <th class="text-end">Global (₺)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Alpha Kablosuz Mouse</td>
                                                    <td class="text-end price">350,00</td>
                                                    <td class="text-end price">28,20</td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-secondary text-white-50 rounded-bottom">
                        <small>//</small>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>



<script>
    (() => {
        const ENDPOINT = 'https://www.emirkarakavak.info/getPrices';

        // Güvenli yazdırma
        const esc = s => String(s ?? '').replace(/[&<>"']/g, m =>
            ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[m]);

        // Etiket eşleştirme için normalize (TR locale + aksan silme + boşluk sadeleştirme)
        const norm = s => String(s ?? '')
            .toLocaleLowerCase('tr')
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ')
            .trim();

        // Bir kategori içindeki "site sekmesi label -> pane selector" haritasını çıkar
        function buildLabelToPaneMapForCategory(catId) {
            const catPane = document.getElementById(catId);
            if (!catPane) return {};
            const map = {};
            // Bu kategorinin içindeki tab bar'daki butonları tara
            catPane.querySelectorAll('.nav-tabs [data-bs-toggle="tab"]').forEach(btn => {
                const label = norm(btn.textContent);
                const target = btn.getAttribute('data-bs-target'); // ör: "#mlbb-X"
                if (label && target) map[label] = target;
            });
            return map;
        }

        function setAllBodies(msgHtml) {
            document.querySelectorAll('.tab-pane tbody').forEach(tb => {
                tb.innerHTML = `<tr><td colspan="3" class="text-center text-muted">${msgHtml}</td></tr>`;
            });
        }

        function renderRowsToPane(paneSelector, rows, siteId) {
            const pane = document.querySelector(paneSelector);
            if (!pane) return;
            const tbody = pane.querySelector('tbody');
            if (!tbody) return;

            if (!rows || !rows.length) {
                tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">Kayıt yok</td></tr>`;
                return;
            }

            const html = rows.map(r => `
      <tr class="product-row" data-name="${esc(r.name)}" data-site="${esc(siteId)}">
        <td>${esc(r.name)}</td>
        <td class="text-end price">${esc(r.tr ?? '-')}</td>
        <td class="text-end price">${esc(r.global ?? '-')}</td>
      </tr>
    `).join('');
            tbody.innerHTML = html;
        }

        async function loadData() {
            setAllBodies('Yükleniyor…');

            let data;
            try {
                const res = await fetch(ENDPOINT, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                data = await res.json();
            } catch (err) {
                console.error(err);
                setAllBodies('<span class="text-danger">Veri alınamadı</span>');
                return;
            }

            const categories = data.categories || [];
            for (const cat of categories) {
                const labelToPane = buildLabelToPaneMapForCategory(cat.id);
                if (!Object.keys(labelToPane).length) continue;

                for (const site of (cat.sites || [])) {
                    const key = norm(site.label);
                    const paneSel = labelToPane[key];
                    if (!paneSel) {
                        console.warn(`[UI eşleşme yok] kategori=${cat.id} siteLabel='${site.label}'`);
                        continue;
                    }
                    renderRowsToPane(paneSel, site.rows || [], site.id);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', loadData);
    })();
</script>