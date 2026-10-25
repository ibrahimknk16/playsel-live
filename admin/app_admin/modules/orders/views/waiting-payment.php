<div >
<div class="card card-flush">
<div class="card-body">
<div class="table-responsive">
<table id="ordersTable" class="display table align-middle table-row-dashed">
    <thead>
        <tr>
            <th>Sipariş No</th>
            <th>Ürün</th>
            <th>Kategori</th>
            <th>Müşteri</th>
            <th>Tutar</th>
            <th>Oluşturulma Tarihi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= htmlspecialchars($o->order_id,        ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($o->product_name,    ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($o->category_title,  ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($o->reseller_name,   ENT_QUOTES) ?></td>
                <td><?= !empty($o->order_sale_price) ? htmlspecialchars($o->order_sale_price, ENT_QUOTES)  . ' ' . htmlspecialchars($o->order_sale_price_currency, ENT_QUOTES) : '0' ?></td>
                <td><?= htmlspecialchars($o->order_created_at, ENT_QUOTES) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
</div>
</div>
</div>