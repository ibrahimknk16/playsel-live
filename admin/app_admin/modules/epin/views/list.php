<style>
    /* Tablo satır hover efekti */
    table.dataTable tbody tr:hover {
        background-color: var(--bs-light-primary) !important;
        transition: background-color 0.2s ease;
    }

    /* Kopyalama ikonu efekti */
    .copy-value {
        cursor: pointer;
        transition: color 0.2s ease;
    }
    .copy-value:hover {
        color: var(--bs-primary) !important;
    }

    /* Filtre kutusu inputları */
    .filter-wrapper .form-control-solid:focus {
        background-color: var(--bs-gray-100) !important;
        border-color: transparent !important;
        box-shadow: none !important;
    }
</style>

<div class="card shadow-sm border-0 mb-12 card-flush">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title align-items-start flex-column">
            <div class="d-flex align-items-center">
                <a href="javascript:history.back()" class="btn btn-icon btn-light btn-sm me-3">
                    <i class="ki-outline ki-arrow-left fs-3"></i>
                </a>
                <span class="card-label fw-bold fs-3 text-gray-900">Stok Kodları Detay Listesi</span>
            </div>
            <span class="text-muted fw-semibold fs-7 mt-2">Bu yüklemeye ait sisteme kaydedilen tüm pinler</span>
        </h3>
    </div>

    <div class="card-body py-4">
        
        <div class="bg-light rounded-3 p-6 mb-8 border border-dashed border-gray-300 filter-wrapper">
            <div class="row g-5 align-items-end">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold text-gray-700 fs-7">Sipariş No (Yükleme ID)</label>
                    <input type="text" id="searchOrderID" class="form-control form-control-sm form-control-solid" placeholder="Örn: 1045">
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <label class="form-label fw-semibold text-gray-700 fs-7">Epin Kodu</label>
                    <input type="text" id="epin" class="form-control form-control-sm form-control-solid" placeholder="Örn: ABCD-1234...">
                </div>
                
                <div class="col-lg-5 col-md-4 col-sm-12 d-flex justify-content-end gap-2">
                    <button class="btn btn-sm btn-light-primary fw-bold" id="resetBtn" data-bs-toggle="tooltip" title="Filtreleri Sıfırla">
                        <i class="ki-outline ki-arrows-circle fs-3"></i> Sıfırla
                    </button>
                    <button class="btn btn-sm btn-primary fw-bold" id="filterBtn">
                        <i class="ki-outline ki-filter fs-3"></i> Ara
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="epins" class="table align-middle table-row-dashed table-hover fs-6 gy-5 w-100">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-50px">#</th>
                        <th>Yükleme ID</th>
                        <th class="min-w-150px">Stok Adı</th>
                        <th class="min-w-200px">Epin Kodu</th>
                        <th>Birim Maliyet</th>
                        <th>Durum</th>
                        <th>Müşteri</th>
                        <th>Tedarikçi</th>
                        <th>Yükleme Tarihi</th>
                        <th>Satış Tarihi</th>
                        <th>Sipariş No</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    </tbody>
            </table>
        </div>

    </div>
</div>


<script>
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
            console.log("Kopyalandı: " + val);
            toastr.success('Kod başarıyla kopyalandı.');
        } catch (err) {
            console.error("Kopyalama başarısız!", err);
            toastr.error('Kopyalama işlemi başarısız!');
        }
        document.body.removeChild(tempInput);
    }

    var table3;
    
    function cancelPin(epinId) {
        if (!confirm('Bu pini iptal etmek ve stoktan düşmek istediğinize emin misiniz?')) return;

        $.ajax({
            url: '<?= site_url('epin/cancelPin') ?>',
            method: 'POST',
            data: { epin_id: epinId },
            success: function(response) {
                if (response.success || response.status == 'success') {
                    toastr.success('Pin başarıyla iptal edildi.');
                    table3.ajax.reload(null, false); // Sayfalama sıfırlanmadan tabloyu yenile
                } else {
                    toastr.error('Pin iptal edilemedi: ' + (response.message || 'Bilinmeyen hata'));
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Sistemsel bir hata oluştu: ' + error);
            }
        });
    }

    $(document).ready(function() {
        // DataTable Başlatma
        table3 = $('#epins').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            pageLength: 50,
            searching: false,
            searchable: false,
            lengthMenu: [
                [10, 20, 30, 50, 100, 250, 500],
                [10, 20, 30, 50, 100, 250, 500]
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
            },
            ajax: {
                url: "<?= site_url('epin/listPins/' . $this->uri->segment(3)) ?>",
                type: "POST",
                data: function(d) {
                    d.order_id = $('#searchOrderID').val();
                    d.epin = $('#epin').val();
                },
                dataSrc: function(json) {
                    if (!json.data || json.data.length === 0) {
                        return [];
                    }
                    return json.data;
                },
                error: function(xhr, error, thrown) {
                    console.error("Error fetching data: ", error);
                    return [];
                }
            },
            columns: Array.from({
                length: 12
            }, (_, i) => ({
                data: i
            }))
        });

        $('#filterBtn').click(() => table3.ajax.reload());

        $('select').select2({
            width: '200px'
        });



    });
</script>