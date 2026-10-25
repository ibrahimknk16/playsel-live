<div >
<div class="card card-flush">
<div class="card-body">
<div class="mb-3">
    <strong>Toplam Bakiye:</strong>
    <span id="totalBalance"></span>
</div>

<table id="accountsTable" class="table table-bordered table-striped w-100 align-middle table-row-dashed">
    <thead>
        <tr>
            <th>Banka</th>
            <th>Şube</th>
            <th>Para Birimi</th>
            <th>Hesap No</th>
            <th>IBAN</th>
            <th>Bakiye</th>
            <th>Durum</th>
            <th>Oluşturulma</th>
        </tr>
    </thead>
</table>
</div>
</div>
</div>

<script>
    $(document).ready(function() {

        var table = $('#accountsTable').DataTable({
            ajax: {
                url: "<?= base_url('test/vomsisAccounts'); ?>",
                dataSrc: function(json) {
                    $('#totalBalance').text(json.total_balance + ' TL');
                    return json.data;
                }
            },
            order: [
                [0, 'asc']
            ], // bankaya göre sırala
            pageLength: 25,
            columns: [{
                    data: 'bank'
                },
                {
                    data: 'branch'
                },
                {
                    data: 'currency'
                },
                {
                    data: 'account'
                },
                {
                    data: 'iban'
                },
                {
                    data: 'balance'
                },
                {
                    data: 'status'
                },
                {
                    data: 'created'
                }
            ],
            rowGroup: {
                dataSrc: 'bank'
            }
        });

    });
</script>