<div class="card mb-10 card-flush">
    <div class="card-body">
        <h2 class="fs-2 fw-bold text-dark mb-2"><?php echo html_escape($title); ?></h2>
        <p class="text-muted mb-7"><?php echo html_escape($description); ?></p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-7">
                <i class="ki-duotone ki-cross-circle fs-2 text-danger me-2"></i>
                <?php echo html_escape($error); ?>
            </div>
        <?php endif; ?>

        <div class="card border border-dashed border-primary">
            <div class="card-body">
                <h3 class="fs-4 fw-bold text-dark mb-2">Günlük Fatura Adayları</h3>
                <p class="text-muted mb-5">Seçilen Türkiye günündeki tamamlanmış ve faturalanmamış siparişleri alıcı snapshot'ına göre gruplar. Bu önizleme Birfatura'ya belge göndermez.</p>
                <form method="post" id="invoiceCandidateForm" class="row g-5 align-items-end mb-7">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sipariş Günü (Europe/Istanbul)</label>
                        <input type="date" name="automation_date" class="form-control form-control-solid" value="<?php echo html_escape($automation_date); ?>" required>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" name="invoice_action" value="daily_preview" class="btn btn-primary">Adayları Kontrol Et</button>
                    </div>
                </form>

                <?php if (!empty($daily_preview)): ?>
                    <div class="d-flex flex-wrap gap-5 mb-5">
                        <span class="badge badge-light-primary fs-6">Sipariş günü: <?php echo html_escape($daily_preview['target_date']); ?></span>
                        <span class="badge badge-light-dark fs-6">Fatura grubu: <?php echo (int)$daily_preview['group_count']; ?></span>
                        <span class="badge badge-light-success fs-6">Kontrole hazır: <?php echo (int)$daily_preview['ready_count']; ?></span>
                        <span class="badge badge-light-warning fs-6">Bekletilen: <?php echo (int)$daily_preview['held_count']; ?></span>
                    </div>

                    <?php if (empty($daily_preview['groups'])): ?>
                        <div class="alert alert-info mb-0">Bu gün için faturalanmamış, tamamlanmış sipariş bulunamadı.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Kullanıcı</th>
                                        <th>Fatura Alıcısı</th>
                                        <th>Tür</th>
                                        <th>Fatura Tarihi</th>
                                        <th>Siparişler</th>
                                        <th>Durum / Kontrol</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($daily_preview['groups'] as $group): ?>
                                        <tr>
                                            <td><?php echo html_escape($group['user_name'] ?: ('Kullanıcı #' . (int)$group['user_id'])); ?></td>
                                            <td><?php echo html_escape($group['customer_name']); ?></td>
                                            <td><?php echo (int)$group['invoice_type'] === 2 ? 'Kurumsal' : ((int)$group['invoice_type'] === 1 ? 'Bireysel' : 'Belirsiz'); ?></td>
                                            <td><?php echo html_escape($group['invoice_date']); ?></td>
                                            <td>
                                                <?php echo (int)$group['order_count']; ?> sipariş
                                                <small class="d-block text-muted">#<?php echo html_escape(implode(', #', $group['order_ids'])); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($group['ready']): ?>
                                                    <span class="badge badge-light-success">Veri ön kontrolü geçti (gönderim yok)</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-warning">Fatura için bekletildi</span>
                                                    <ul class="mb-0 mt-2 ps-4">
                                                        <?php foreach ($group['hold_reasons'] as $reason): ?>
                                                            <li><?php echo html_escape($reason); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?php if ($group['ready']): ?>
                                                    <button type="button" class="btn btn-sm btn-light-primary js-invoice-preview"
                                                        data-user-id="<?php echo (int)$group['user_id']; ?>"
                                                        data-order-id="<?php echo (int)$group['order_ids'][0]; ?>"
                                                        data-target-date="<?php echo html_escape($daily_preview['target_date']); ?>">
                                                        Fatura Önizle
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light-danger js-invoice-cut mt-2"
                                                        data-user-id="<?php echo (int)$group['user_id']; ?>"
                                                        data-order-id="<?php echo (int)$group['order_ids'][0]; ?>"
                                                        data-target-date="<?php echo html_escape($daily_preview['target_date']); ?>">
                                                        Faturayı Elle Kes
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">Kontrol gerekli</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="separator my-8"></div>
                    <h3 class="fs-4 fw-bold text-dark mb-4">Kesilmiş Faturalar</h3>
                    <?php if (empty($daily_preview['invoices'])): ?>
                        <div class="alert alert-info mb-0">Seçilen sipariş gününde kesilmiş fatura bulunamadı.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Kullanıcı</th>
                                        <th>Fatura No</th>
                                        <th>Fatura Tarihi</th>
                                        <th>Sipariş Sayısı</th>
                                        <th>Fatura</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($daily_preview['invoices'] as $invoice): ?>
                                        <tr>
                                            <td><?php echo html_escape($invoice['user_name'] ?: ('Kullanıcı #' . (int)$invoice['user_id'])); ?></td>
                                            <td><?php echo html_escape($invoice['order_invoice_number']); ?></td>
                                            <td><?php echo html_escape($invoice['order_invoice_date']); ?></td>
                                            <td><?php echo (int)$invoice['order_count']; ?></td>
                                            <td>
                                                <?php if (!empty($invoice['order_invoice_uuid'])): ?>
                                                    <a class="btn btn-sm btn-light-primary"
                                                        href="<?php echo html_escape(base_url('users/invoiceDetail/' . $invoice['order_invoice_uuid'])); ?>"
                                                        target="_blank" rel="noopener noreferrer">Faturayı Gör</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Fatura UUID bulunamadı</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="invoicePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5">Fatura Kesim Önizlemesi</h2>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal" aria-label="Kapat">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <div class="modal-body" id="invoicePreviewContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var CSRF = <?php echo json_encode($adminActionCsrfToken); ?>;
    var PREVIEW_URL = <?php echo json_encode(base_url('fatura/previewInvoiceGroup')); ?>;
    var CUT_URL = <?php echo json_encode(base_url('fatura/manualCutInvoiceGroup')); ?>;

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    }

    function money(value) {
        return Number(value || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
    }

    function notifyInvoice(type, message) {
        if (typeof toastr !== 'undefined' && typeof toastr[type] === 'function') toastr[type](message);
        else window.alert(message);
    }

    function renderPreview(group, targetDate) {
        var recipient = group.recipient || {};
        var items = Array.isArray(group.items) ? group.items : [];
        var html = '<p class="text-muted">Sipariş günü: <strong>' + escapeHtml(targetDate) + '</strong>. Bu ekran Birfatura’ya belge göndermez.</p>' +
            '<div class="card border mb-5"><div class="card-body">' +
            '<h3 class="fs-5 fw-bold">' + escapeHtml(group.customer_name) + '</h3>' +
            '<p class="text-muted">' + (Number(group.invoice_type) === 2 ? 'Kurumsal' : 'Bireysel') + ' · ' +
            (group.is_efatura ? 'E-Fatura' : 'E-Arşiv') + ' · Fatura tarihi: ' + escapeHtml(group.invoice_date) +
            ' · ' + escapeHtml(group.order_count) + ' sipariş</p>' +
            '<div class="mb-4"><strong>Alıcı:</strong> ' + escapeHtml(recipient.name) + ' · ' + escapeHtml(recipient.tax_number) +
            (recipient.tax_office ? ' · ' + escapeHtml(recipient.tax_office) : '') +
            '<br><span class="text-muted">' + escapeHtml(recipient.address) + ', ' + escapeHtml(recipient.district) + ' / ' + escapeHtml(recipient.city) + '</span>' +
            '<br><span class="text-muted">Telefon: ' + escapeHtml(recipient.phone) + ' · E-posta: ' + escapeHtml(recipient.email) + '</span>' +
            '<br><span class="text-muted">Siparişler: #' + escapeHtml((group.order_ids || []).join(', #')) + '</span></div>' +
            '<div class="table-responsive"><table class="table table-sm table-bordered align-middle"><thead><tr>' +
            '<th>Stok Kodu</th><th>Ürün / Kalem</th><th>Adet</th><th>Birim Tutar</th><th>KDV</th><th>KDV Tutarı</th>' +
            '</tr></thead><tbody>';
        items.forEach(function (item) {
            html += '<tr><td>' + escapeHtml(item.stock_code) + '</td><td>' + escapeHtml(item.name) + '</td><td>' +
                escapeHtml(item.qty) + '</td><td>' + money(item.price) + '</td><td>%' +
                escapeHtml(item.tax_rate) + '</td><td>' + money(item.tax_amount) + '</td></tr>';
        });
        html += '</tbody></table></div><div class="text-end"><div>Matrah: <strong>' + money(group.net_total) +
            '</strong></div><div>KDV: <strong>' + money(group.tax_total) + '</strong></div><div>Fatura toplamı: <strong>' +
            money(group.grand_total) + '</strong></div><div>Sipariş satış toplamı: <strong>' +
            money(group.toplam_odenecek) + '</strong></div></div></div></div>';

        document.getElementById('invoicePreviewContent').innerHTML = html;
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('invoicePreviewModal')).show();
        }
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.js-invoice-preview, .js-invoice-cut');
        if (!button) return;

        var data = {
            user_id: button.dataset.userId,
            order_id: button.dataset.orderId,
            target_date: button.dataset.targetDate
        };
        var isCut = button.classList.contains('js-invoice-cut');
        if (isCut && !window.confirm('Seçilen sipariş grubu için Birfatura’ya gerçek fatura gönderilecek. Bu işlem geri alınamaz. Önizlemeyi kontrol ettiğinizden emin misiniz?')) {
            return;
        }

        button.disabled = true;
        jQuery.ajax({
            url: isCut ? CUT_URL : PREVIEW_URL,
            type: 'POST',
            dataType: 'json',
            headers: { 'X-Admin-CSRF': CSRF },
            data: data
        }).done(function (response) {
            if (!response || response.status !== 'success') {
                notifyInvoice('error', (response && response.message) || 'İşlem tamamlanamadı.');
                return;
            }
            if (isCut) {
                notifyInvoice('success', response.message || 'Fatura kesildi.');
                document.getElementById('invoiceCandidateForm').requestSubmit(document.querySelector('#invoiceCandidateForm button[name="invoice_action"]'));
                return;
            }
            renderPreview(response.group, response.target_date);
        }).fail(function (xhr) {
            var response = xhr.responseJSON || {};
            notifyInvoice('error', response.message || 'İşlem sırasında bir hata oluştu.');
        }).always(function () {
            button.disabled = false;
        });
    });
})();
</script>
