<div class="row">
    <div class="col-12 mb-12">
        <div class="card shadow-sm card-flush">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold">Referans Değişiklik Talepleri</h3>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table id="requestsTable" class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th>Talep ID</th>
                                <th>Kullanıcı Adı</th>
                                <th>Mevcut Referans</th>
                                <th>İstenen Referans</th>
                                <th>Tarih</th>
                                <th>Durum</th>
                                <th class="text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#requestsTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        pageLength: 15,
        lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
        },
        ajax: {
            url: "<?= base_url('users/getReferenceRequestsList') ?>",
            type: "POST"
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6, className: 'text-end' }
        ]
    });

    $(document).on('click', '.js-approve-request', function(e) {
        e.preventDefault();
        var requestId = $(this).data('id');
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu referans değişikliğini onaylamak istediğinize emin misiniz?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Onayla!',
            cancelButtonText: 'İptal',
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-light"
            }
        }).then(function (result) {
            if (result.value) {
                $.post("<?= base_url('users/approveReferenceRequest') ?>", { request_id: requestId }, function(res) {
                    var json = JSON.parse(res);
                    if (json.status === 'success') {
                        if (typeof toastr !== 'undefined') toastr.success(json.message);
                        table.draw();
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(json.message);
                    }
                });
            }
        });
    });

    $(document).on('click', '.js-reject-request', function(e) {
        e.preventDefault();
        var requestId = $(this).data('id');
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu referans değişikliğini reddetmek istediğinize emin misiniz?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Reddet!',
            cancelButtonText: 'İptal',
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-light"
            }
        }).then(function (result) {
            if (result.value) {
                $.post("<?= base_url('users/rejectReferenceRequest') ?>", { request_id: requestId }, function(res) {
                    var json = JSON.parse(res);
                    if (json.status === 'success') {
                        if (typeof toastr !== 'undefined') toastr.success(json.message);
                        table.draw();
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(json.message);
                    }
                });
            }
        });
    });
});
</script>
