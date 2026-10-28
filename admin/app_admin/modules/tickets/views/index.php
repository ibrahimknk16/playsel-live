<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Destek Talepleri</h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-primary" onclick="location.reload()">
                    <i class="fa-solid fa-sync"></i> Yenile
                </button>
                <button type="button" class="btn btn-sm btn-light-danger" data-bs-toggle="modal" data-bs-target="#cancellationReasonsModal">
                    <i class="fa-solid fa-ban"></i> İptal sebepleri
                </button>
            </div>
        </div>

        <div class="modal fade" id="cancellationReasonsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Sipariş iptal sebepleri</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                    </div>
                    <div class="modal-body">
                        <form id="cancellationReasonForm" class="d-flex gap-2 mb-4">
                            <input type="text" name="reason" class="form-control form-control-solid" placeholder="Yeni iptal sebebi" required>
                            <button class="btn btn-primary" type="submit">Ekle</button>
                        </form>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($cancellation_reasons as $reason): ?>
                                <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2">
                                    <span><?= html_escape($reason->reason_text) ?></span>
                                    <span class="badge badge-light-success">Aktif</span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($cancellation_reasons)): ?>
                                <span class="text-muted">Henüz tanımlı iptal sebebi yok.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-5" id="ticketStatusTabs">
                <button type="button" class="btn btn-sm btn-primary" data-status="">Tümü <span class="badge badge-light ms-1"><?= (int)array_sum($status_counts) ?></span></button>
                <button type="button" class="btn btn-sm btn-light-warning" data-status="0">Kullanıcı Cevabı <span class="badge badge-light ms-1"><?= (int)$status_counts[0] ?></span></button>
                <button type="button" class="btn btn-sm btn-light-success" data-status="1">Yanıtlandı <span class="badge badge-light ms-1"><?= (int)$status_counts[1] ?></span></button>
                <button type="button" class="btn btn-sm btn-light-secondary" data-status="2">Kapatıldı <span class="badge badge-light ms-1"><?= (int)$status_counts[2] ?></span></button>
            </div>

            <form id="ticketFilters" class="row g-3 mb-6" autocomplete="off">
                <div class="col-md-4">
                    <label class="form-label fs-8">Ara</label>
                    <input type="text" id="filterSearch" class="form-control form-control-solid form-control-sm" placeholder="Talep no, konu, kullanıcı adı veya e-posta">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-8">Durum</label>
                    <select id="filterStatus" class="form-select form-select-solid form-select-sm">
                        <option value="">Tümü</option>
                        <option value="0">Kullanıcı Cevabı</option>
                        <option value="1">Yanıtlandı</option>
                        <option value="2">Kapatıldı</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-8">Başlangıç</label>
                    <input type="date" id="filterStartDate" class="form-control form-control-solid form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-8">Bitiş</label>
                    <input type="date" id="filterEndDate" class="form-control form-control-solid form-control-sm">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">Filtrele</button>
                    <button type="button" id="resetTicketFilters" class="btn btn-sm btn-light flex-fill">Sıfırla</button>
                </div>
            </form>

            <div class="table-responsive">
                <table id="ticketsTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 w-100">
                    <thead>
                        <tr class="fw-bolder text-muted">
                            <th class="w-25px">ID</th>
                            <th class="min-w-150px">Kullanıcı</th>
                            <th class="min-w-150px">Konu / Başlık</th>
                            <th class="min-w-120px">Son Güncelleme</th>
                            <th class="min-w-100px">Durum</th>
                            <th class="min-w-150px text-end">İşlemler</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ticketsTable = $('#ticketsTable').DataTable({
            serverSide: true,
            ordering: false,
            searching: false,
            deferRender: true,
            pageLength: 20,
            lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json'
            },
            columns: [
                { title: 'ID' },
                { title: 'Kullanıcı' },
                { title: 'Konu / Başlık' },
                { title: 'Son Güncelleme' },
                { title: 'Durum' },
                { title: 'İşlemler', className: 'text-end' }
            ],
            ajax: {
                url: '<?= base_url('tickets/getTickets') ?>',
                type: 'POST',
                data: function(d) {
                    d.search_text = $('#filterSearch').val();
                    d.status = $('#filterStatus').val();
                    d.start_date = $('#filterStartDate').val();
                    d.end_date = $('#filterEndDate').val();
                }
            }
        });

        function setStatusTab(status) {
            $('#ticketStatusTabs button').each(function() {
                const isActive = String($(this).data('status')) === String(status);
                $(this).toggleClass('btn-primary', isActive);
            });
        }

        $('#ticketFilters').on('submit', function(e) {
            e.preventDefault();
            setStatusTab($('#filterStatus').val());
            ticketsTable.ajax.reload();
        });

        $('#ticketStatusTabs button').on('click', function() {
            $('#filterStatus').val(String($(this).data('status')));
            setStatusTab($(this).data('status'));
            ticketsTable.ajax.reload();
        });

        $('#filterStatus').on('change', function() {
            setStatusTab(this.value);
            ticketsTable.ajax.reload();
        });

        let searchTimer;
        $('#filterSearch').on('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() { ticketsTable.ajax.reload(); }, 400);
        });

        $('#resetTicketFilters').on('click', function() {
            $('#ticketFilters')[0].reset();
            setStatusTab('');
            ticketsTable.ajax.reload();
        });

        $('#cancellationReasonForm').on('submit', function(e) {
            e.preventDefault();
            $.post('<?= base_url('tickets/createCancellationReason') ?>', $(this).serialize(), function(response) {
                Swal.fire(response.status === 'success' ? 'Başarılı' : 'Hata', response.message, response.status === 'success' ? 'success' : 'error')
                    .then(() => { if (response.status === 'success') location.reload(); });
            }, 'json');
        });

        $('#ticketsTable').on('click', '.btn-update-status', function() {
            const ticketId = this.getAttribute('data-id');
            const status = this.getAttribute('data-status');

            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu destek talebini kapatmak istediğinize emin misiniz? Kapatılan taleplere tekrar cevap yazılamaz.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f1416c',
                cancelButtonColor: '#a1a5b7',
                confirmButtonText: 'Evet, kapat!',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('tickets/updateStatus') ?>',
                        type: 'POST',
                        data: {
                            ticket_id: ticketId,
                            status: status
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Başarılı!', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Hata!', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Hata!', 'Bir sunucu hatası oluştu.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
