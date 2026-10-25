<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-balance" data-view="balance">
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-history"/></svg></span>
            <div class="hero-copy">
                <h1>Bakiye hareketleri</h1>
                <p>Cüzdanındaki tüm finansal işlemleri, yüklemeleri ve harcamaları takip et.</p>
            </div>
        </header>

        <section class="subnav-shell">
          <div class="tab-list" role="tablist" aria-label="Bakiye hareket türleri" data-filter-tabs="balance">
            <a href="?type=all&filter=<?= htmlspecialchars($active_filter) ?>" style="text-decoration:none;" class="tab-button <?= ($active_type == 'all') ? 'active' : '' ?>"><svg class="icon"><use href="#i-list"/></svg>Tümü</a>
            <a href="?type=in&filter=<?= htmlspecialchars($active_filter) ?>" style="text-decoration:none;" class="tab-button <?= ($active_type == 'in') ? 'active' : '' ?>"><svg class="icon"><use href="#i-arrow"/></svg>Gelen transfer</a>
            <a href="?type=out&filter=<?= htmlspecialchars($active_filter) ?>" style="text-decoration:none;" class="tab-button <?= ($active_type == 'out') ? 'active' : '' ?>"><svg class="icon" style="transform:rotate(180deg)"><use href="#i-arrow"/></svg>Giden transfer</a>
          </div>
          
          <form action="<?= base_url($this->uri->segment(1) . '/profile/transactions') ?>" method="GET" class="filter-row" style="margin-bottom:16px;">
            <input type="hidden" name="type" value="<?= htmlspecialchars($active_type) ?>">
            <div class="search-control">
                <svg class="icon" aria-hidden="true"><use href="#i-search"/></svg>
                <input type="text" id="trxSearch" placeholder="İşlem no veya açıklama ara..." aria-label="Tabloda ara">
            </div>
            <a href="?type=<?= htmlspecialchars($active_type) ?>&filter=all" style="text-decoration:none;" class="quick-filter <?= ($active_filter == 'all') ? 'active' : '' ?>">Tüm zamanlar</a>
            <a href="?type=<?= htmlspecialchars($active_type) ?>&filter=week" style="text-decoration:none;" class="quick-filter <?= ($active_filter == 'week') ? 'active' : '' ?>">Son 1 hafta</a>
            <a href="?type=<?= htmlspecialchars($active_type) ?>&filter=month" style="text-decoration:none;" class="quick-filter <?= ($active_filter == 'month') ? 'active' : '' ?>">Son 1 ay</a>
            <a href="?type=<?= htmlspecialchars($active_type) ?>&filter=year" style="text-decoration:none;" class="quick-filter <?= ($active_filter == 'year') ? 'active' : '' ?>">Son 1 yıl</a>
          </form>

          <div class="panel-body">
            <?php if (empty($transactions)): ?>
            <div class="empty-state">
              <span class="empty-icon"><svg class="icon"><use href="#i-history"/></svg></span>
              <h3>İşlem Bulunamadı</h3>
              <p>Bu kriterlere uygun bakiye hareketi bulunmuyor.</p>
            </div>
            <?php else: ?>
            <div class="table-wrap">
              <table class="data-table" id="trxTable">
                <thead><tr><th>İşlem detayı</th><th>Tarih</th><th>Yöntem</th><th style="text-align:right;">Tutar</th><th>Durum</th></tr></thead>
                <tbody>
                  <?php foreach ($transactions as $trx): 
                      $is_in = ($trx->user_balance_status != 3); 
                      $type_icon = $is_in ? '#i-arrow' : '#i-arrow';
                      $icon_transform = $is_in ? '' : 'transform:rotate(180deg)';
                      $type_title = $trx->display_note ?? ($trx->user_balance_note ?: ($is_in ? 'Bakiye Yükleme' : 'Harcama'));
                      
                      $status_class = '';
                      $status_text = '';
                      $status_icon = '';
                      switch($trx->user_balance_status) {
                          case 0: $status_class = 'payment-pending'; $status_text = 'Beklemede'; $status_icon = 'pending'; break;
                          case 1: $status_class = 'done'; $status_text = 'Tamamlandı'; $status_icon = 'check'; break;
                          case 2: $status_class = 'refund'; $status_text = 'İptal Edildi'; $status_icon = 'x'; break;
                          case 4: $status_class = 'refund'; $status_text = 'Reddedildi'; $status_icon = 'x'; break;
                          default: $status_class = 'payment-pending'; $status_text = 'İnceleniyor'; $status_icon = 'pending'; break;
                      }
                      
                      $method = $trx->display_method ?? ($trx->bank_name ?: 'Bakiye İşlemi');
                  ?>
                  <tr>
                    <td data-label="İşlem detayı">
                        <div class="product-cell">
                            <span class="product-thumb"><svg class="icon" style="<?= $icon_transform ?>"><use href="<?= $type_icon ?>"/></svg></span>
                            <span style="display:flex; flex-direction:column; min-width:0; flex:1;">
                                <strong class="product-name"><?= htmlspecialchars($type_title) ?></strong>
                                <span class="subtle" style="font-size:12px;">#TRX-<?= htmlspecialchars($trx->user_balance_id) ?></span>
                                <?php if (!empty($trx->display_reference)): ?>
                                    <span class="subtle" style="font-size:11px;"><?= htmlspecialchars($trx->display_reference) ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </td>
                    <td data-label="Tarih" class="subtle"><?= date('d.m.Y · H:i', strtotime($trx->user_balance_created_at)) ?></td>
                    <td data-label="Yöntem"><?= htmlspecialchars($method) ?>
                        <?php if (!empty($trx->bank_name) && !empty($trx->user_balance_bank_id) && !empty($trx->user_balance_reference_no) && strpos((string)$trx->user_balance_reference_no, 'vomsis_') !== 0): ?>
                            <br><small class="subtle">Ref: <?= htmlspecialchars($trx->user_balance_reference_no) ?></small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Tutar" class="<?= $is_in ? 'amount-positive' : 'amount-negative' ?>" style="text-align:right;">
                        <?= $is_in ? '+' : '-' ?> <?= number_format($trx->user_balance_amount, 2, ',', '.') ?> ₺
                    </td>
                    <td data-label="Durum">
                        <span class="status <?= $status_class ?>">
                            <?php if($status_icon == 'pending'): ?>
                                <span class="status-mark pending" aria-hidden="true"></span>
                            <?php else: ?>
                                <span class="status-mark"><svg class="icon"><use href="#i-<?= $status_icon ?>"/></svg></span>
                            <?php endif; ?>
                            <?= $status_text ?>
                        </span>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php if (!empty($pagination)): ?>
                <div class="profile-pagination-wrapper">
                    <?= $pagination ?>
                </div>
            <?php endif; ?>
            <?php endif; ?>
          </div>
        </section>
      </section>
    </section>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("trxSearch");
        const table = document.getElementById("trxTable");
        
        if (searchInput && table) {
            const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

            searchInput.addEventListener("keyup", function() {
                let filter = searchInput.value.toLowerCase();
                for (let i = 0; i < rows.length; i++) {
                    let rowText = rows[i].textContent || rows[i].innerText;
                    if (rowText.toLowerCase().indexOf(filter) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            });
        }
    });
</script>