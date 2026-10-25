<div class="row">
    <div class="col-12 mb-12">
        <div class="card shadow-sm card-flush">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold">Yayıncı Başvuruları</h3>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table id="streamersTable" class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th>Kullanıcı ID</th>
                                <th>Kullanıcı Adı (Nick)</th>
                                <th>Yayıncı Nick</th>
                                <th>Yayıncı URL</th>
                                <th>E-posta</th>
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
    var table = $('#streamersTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        pageLength: 15,
        lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.2.2/i18n/tr.json"
        },
        ajax: {
            url: "<?= base_url('users/streamersList') ?>",
            type: "POST"
        },
        columns: [
            { data: 'user_id' },
            { 
                data: 'user_nickname',
                render: function(data, type, row) {
                    return (data && data.trim() !== '') ? data : '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'user_streamer_nick',
                render: function(data, type, row) {
                    return (data && data.trim() !== '') ? data : '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'user_streamer_url',
                render: function(data, type, row) {
                    return '<a href="'+data+'" target="_blank" class="text-primary">'+data+'</a>';
                }
            },
            { data: 'user_email' },
            { 
                data: 'user_streamer_status',
                render: function(data, type, row) {
                    if (data == 0) return '<span class="badge badge-light-danger">Reddedildi</span>';
                    if (data == 1) return '<span class="badge badge-light-success">Onaylandı (Aktif)</span>';
                    if (data == 2) return '<span class="badge badge-light-warning">Bekliyor</span>';
                    return '<span class="badge badge-light-secondary">Bilinmiyor</span>';
                }
            },
            { 
                data: null,
                className: 'text-end',
                render: function(data, type, row) {
                    var html = '';
                    if (row.user_streamer_status == 2 || row.user_streamer_status == 0) {
                        html += '<button type="button" class="btn btn-sm btn-light-success js-approve-streamer me-2" data-id="'+row.user_id+'"><i class="fa-solid fa-check"></i> Onayla</button>';
                    }
                    if (row.user_streamer_status == 2 || row.user_streamer_status == 1) {
                        html += '<button type="button" class="btn btn-sm btn-light-danger js-reject-streamer" data-id="'+row.user_id+'"><i class="fa-solid fa-xmark"></i> Reddet</button>';
                    }
                    return html;
                }
            }
        ]
    });

    $(document).on('click', '.js-approve-streamer', function(e) {
        e.preventDefault();
        var userId = $(this).data('id');
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu kullanıcının yayıncı başvurusunu onaylamak istediğinize emin misiniz?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Onayla!',
            cancelButtonText: 'İptal',
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-light"
            }
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post("<?= base_url('users/updateStreamerStatus') ?>", { user_id: userId, status: 1 }, function(res) {
                    var json = JSON.parse(res);
                    if (json.status) {
                        if (typeof toastr !== 'undefined') toastr.success(json.message);
                        table.draw();
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(json.message);
                    }
                });
            }
        });
    });

    $(document).on('click', '.js-reject-streamer', function(e) {
        e.preventDefault();
        var userId = $(this).data('id');
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu kullanıcının yayıncı başvurusunu reddetmek istediğinize emin misiniz?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Reddet!',
            cancelButtonText: 'İptal',
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-light"
            }
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post("<?= base_url('users/updateStreamerStatus') ?>", { user_id: userId, status: 0 }, function(res) {
                    var json = JSON.parse(res);
                    if (json.status) {
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
