<div >
<div class="card card-flush">
    <div class="card-header"><h3 class="card-title">Nickname Değişiklik Talepleri</h3></div>
    <div class="card-body">
        <table class="table align-middle table-row-dashed">
            <thead><tr><th>Kullanıcı</th><th>Mevcut</th><th>Yeni nickname</th><th>Durum</th><th>İşlem</th></tr></thead>
            <tbody>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= html_escape(trim($request->user_name . ' ' . $request->user_surname)) ?><br><small><?= html_escape($request->user_email) ?></small></td>
                    <td><?= html_escape($request->user_nickname) ?></td>
                    <td><?= html_escape($request->nickname_request_value) ?></td>
                    <td><?= (int)$request->nickname_request_status === 2 ? 'Bekliyor' : ((int)$request->nickname_request_status === 1 ? 'Onaylandı' : 'Reddedildi') ?></td>
                    <td>
                        <?php if ((int)$request->nickname_request_status === 2): ?>
                            <form method="post" action="<?= base_url('users/approveNicknameRequest') ?>" style="display:inline">
                                <input type="hidden" name="request_id" value="<?= (int)$request->nickname_request_id ?>">
                                <button class="btn btn-sm btn-success">Onayla</button>
                            </form>
                            <form method="post" action="<?= base_url('users/rejectNicknameRequest') ?>" style="display:inline">
                                <input type="hidden" name="request_id" value="<?= (int)$request->nickname_request_id ?>">
                                <button class="btn btn-sm btn-danger">Reddet</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>
</div>
