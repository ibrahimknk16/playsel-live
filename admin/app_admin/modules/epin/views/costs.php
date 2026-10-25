<div class="row">
    <div class="card shadow-sm mb-12 card-flush">
        <div class="card-header">
            <h3 class="card-title">Ürünler</h3>
            <div class="card-toolbar">
            </div>
        </div>

        <div class="card-body">
            <table id="productTable" class="table table-striped align-middle table-row-dashed">
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Ürün Birimi</th>
                        <th>Ürün Maliyeti</th>
                        <th>Ürün Maliyeti TL</th>
                        <th>Ürün Maliyeti USD</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <?php $costTL = currencyCalculator($product->product_cost_currency, $product->product_cost); ?>
                        <tr>
                            <td><?= $product->product_name ?></td>
                            <td><?= $product->product_cost_currency ?></td>
                            <td><?= $product->product_cost ?></td>
                            <td><?= $costTL * 1.08 ?></td>
                            <td><?= currencyCalculatorTo('USD', $costTL) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>