<?php
$userSession = $this->session->userdata('user');
?>

<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-dashboard" data-view="dashboard">
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-grid"/></svg></span>
            <div class="hero-copy">
                <h1><?= $this->lang->line('control_panel') ?? 'Kontrol Paneli' ?></h1>
                <p><?= $this->lang->line('control_panel_desc') ?? 'Hesap özetinizi ve son işlemlerinizi görüntüleyin.' ?></p>
            </div>
        </header>
        
        <div class="dashboard-tiles" aria-label="Hesap kısayolları">
          <div class="dashboard-primary" aria-label="Finans ve işlem menüleri">
              <a class="dashboard-tile wallet-tile" href="<?= base_url($this->uri->segment(1) . '/profile/wallet') ?>" style="text-decoration:none;">
                  <span class="dashboard-tile-head"><svg class="icon"><use href="#i-wallet"/></svg><?= $this->lang->line('wallet') ?? 'Cüzdanım' ?></span>
                  <span class="dashboard-tile-arrow" aria-hidden="true"><svg class="icon"><use href="#i-chevron"/></svg></span>
                  <strong class="dashboard-tile-value"><?= nFormat($userBalance['total_balance'] ?? 0) ?> ₺</strong>
                  <span class="dashboard-tile-note">Cüzdan bakiyeni görüntüle</span>
              </a>
              <a class="dashboard-tile dashboard-action-tile" href="<?= base_url($this->uri->segment(1) . '/profile/payments') ?>" style="text-decoration:none;">
                  <span class="dashboard-tile-head"><svg class="icon"><use href="#i-card"/></svg><?= $this->lang->line('payments') ?? 'Ödemeler' ?></span>
                  <span class="dashboard-tile-arrow" aria-hidden="true"><svg class="icon"><use href="#i-chevron"/></svg></span>
                  <strong class="dashboard-tile-action">Ödeme geçmişini görüntüle</strong>
                  <span class="dashboard-tile-note">Ödeme kayıtları ve durumlar</span>
              </a>
              <a class="dashboard-tile dashboard-action-tile orders-tile" href="<?= base_url($this->uri->segment(1) . '/profile/orders') ?>" style="text-decoration:none;">
                  <span class="dashboard-tile-head"><svg class="icon"><use href="#i-bag"/></svg><?= $this->lang->line('orders') ?? 'Siparişlerim' ?></span>
                  <span class="dashboard-tile-arrow" aria-hidden="true"><svg class="icon"><use href="#i-chevron"/></svg></span>
                  <strong class="dashboard-tile-action">Sipariş geçmişini görüntüle</strong>
                  <span class="dashboard-tile-note">Sipariş detayları ve teslimatlar</span>
              </a>
          </div>
          <div class="dashboard-secondary" aria-label="Diğer hesap menüleri">
              <a class="dashboard-tile compact" href="<?= base_url($this->uri->segment(1) . '/profile/settings') ?>" style="text-decoration:none;">
                  <span class="dashboard-tile-head"><svg class="icon"><use href="#i-settings"/></svg><?= $this->lang->line('settings') ?? 'Ayarlar' ?></span>
                  <span class="dashboard-tile-arrow" aria-hidden="true"><svg class="icon"><use href="#i-chevron"/></svg></span>
                  <span class="dashboard-tile-note">Profil ve güvenlik tercihleri</span>
              </a>
              <a class="dashboard-tile compact" href="<?= base_url($this->uri->segment(1) . '/profile/tickets') ?>" style="text-decoration:none;">
                  <span class="dashboard-tile-head"><svg class="icon"><use href="#i-headset"/></svg>Destek talepleri</span>
                  <span class="dashboard-tile-arrow" aria-hidden="true"><svg class="icon"><use href="#i-chevron"/></svg></span>
                  <span class="dashboard-tile-note">Taleplerini görüntüle</span>
              </a>
              <a class="dashboard-tile compact streamer-tile" href="<?= base_url($this->uri->segment(1) . '/profile/streamer') ?>" style="text-decoration:none;">
                  <span class="dashboard-tile-head"><svg class="icon"><use href="#i-ticket"/></svg><?= $this->lang->line('streamer_app') ?? 'Yayıncı Başvurusu' ?></span>
                  <span class="dashboard-tile-arrow" aria-hidden="true"><svg class="icon"><use href="#i-chevron"/></svg></span>
                  <span class="dashboard-tile-note">Başvuru yap veya durumunu takip et</span>
              </a>
          </div>
        </div>

        <section class="panel">
          <div class="panel-head ledger-panel-head">
              <div class="ledger-heading">
                  <span class="ledger-heading-icon"><svg class="icon"><use href="#i-bag"/></svg></span>
                  <div class="ledger-heading-copy">
                      <small>Hesap aktivitesi</small>
                      <h2><?= $this->lang->line('last_orders') ?? 'Sipariş geçmişi' ?></h2>
                      <p>En son alışverişlerin ve teslimat durumları.</p>
                  </div>
              </div>
              <a class="ledger-action" href="<?= base_url($this->uri->segment(1) . '/profile/orders') ?>" style="text-decoration:none;">Tüm siparişler <svg class="icon"><use href="#i-arrow"/></svg></a>
          </div>
          
          <?php if (!empty($recent_orders)): ?>
          <div class="table-wrap">
              <table class="data-table compact-table" id="dashboard-orders-table">
                  <thead>
                      <tr>
                          <th><?= $this->lang->line('table_product') ?? 'Ürün' ?></th>
                          <th><?= $this->lang->line('table_date') ?? 'Tarih' ?></th>
                          <th><?= $this->lang->line('table_amount') ?? 'Tutar' ?></th>
                          <th><?= $this->lang->line('table_status') ?? 'Durum' ?></th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach (array_slice($recent_orders, 0, 5) as $order): ?>
                          <tr>
                              <td data-label="Ürün">
                                  <span class="product-cell">
                                      <span class="product-thumb">
                                          <?php if (!empty($order->product_icon)): ?>
                                              <img class="order-art" src="<?= getenv('CDN_URL') . $order->product_icon ?>" alt="<?= html_escape($order->product_name ?? '') ?>" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
                                          <?php else: ?>
                                              <svg class="icon"><use href="#i-bag"/></svg>
                                          <?php endif; ?>
                                      </span>
                                      <span class="product-name"><?= html_escape($order->product_name ?? '-') ?></span>
                                  </span>
                              </td>
                              <td data-label="Tarih" class="subtle"><?= date('d.m.Y · H:i', strtotime($order->order_created_at)) ?></td>
                              <td data-label="Tutar" class="money"><?= $order->order_sale_price ?? '0.00' ?> ₺</td>
                              <td data-label="Durum">
                                  <?php $dashStatus = (int)$order->order_status; ?>
                                  <?php if ($dashStatus === 1): ?>
                                      <span class="status done"><span class="status-mark"><svg class="icon"><use href="#i-check"/></svg></span><?= $this->lang->line('status_completed') ?? 'Tamamlandı' ?></span>
                                  <?php elseif (in_array($dashStatus, [2, 5, 12], true)): ?>
                                      <span class="status refund"><span class="status-mark"><svg class="icon"><use href="#i-x"/></svg></span><?= $this->lang->line('status_cancelled') ?? 'İptal Edildi' ?></span>
                                  <?php elseif ($dashStatus === 100): ?>
                                      <span class="status refund"><span class="status-mark"><svg class="icon"><use href="#i-x"/></svg></span>Kısmen İptal Edildi</span>
                                  <?php elseif ($dashStatus === 102): ?>
                                      <span class="status processing"><span class="status-mark processing" aria-hidden="true"></span>Kısmen Teslim Edildi</span>
                                  <?php elseif (in_array($dashStatus, [0, 11], true)): ?>
                                      <span class="status payment-pending"><span class="status-mark pending" aria-hidden="true"></span>Beklemede</span>
                                  <?php else: ?>
                                      <span class="status processing"><span class="status-mark processing" aria-hidden="true"></span><?= $this->lang->line('status_processing') ?? 'İşlemde' ?></span>
                                  <?php endif; ?>
                              </td>
                          </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
          </div>
          <?php else: ?>
          <div class="empty-state">
              <span class="empty-icon"><svg class="icon"><use href="#i-bag"/></svg></span>
              <h3><?= $this->lang->line('no_orders') ?? 'Sipariş Bulunamadı' ?></h3>
              <p><?= $this->lang->line('no_orders_desc') ?? 'Henüz hiç sipariş vermemişsiniz.' ?></p>
          </div>
          <?php endif; ?>
        </section>

        <section class="panel">
          <div class="panel-head ledger-panel-head">
              <div class="ledger-heading">
                  <span class="ledger-heading-icon"><svg class="icon"><use href="#i-cash"/></svg></span>
                  <div class="ledger-heading-copy">
                      <small>Hesap aktivitesi</small>
                      <h2><?= $this->lang->line('balance_history') ?? 'Bakiye geçmişi' ?></h2>
                      <p>Son ödeme ve bakiye hareketlerin.</p>
                  </div>
              </div>
              <a class="ledger-action" href="<?= base_url($this->uri->segment(1) . '/profile/transactions') ?>" style="text-decoration:none;">Tüm hareketler <svg class="icon"><use href="#i-arrow"/></svg></a>
          </div>
          
          <?php if (!empty($recent_transactions)): ?>
          <div class="table-wrap">
              <table class="data-table compact-table" id="dashboard-payments-table">
                  <thead>
                      <tr>
                          <th><?= $this->lang->line('table_process') ?? 'Açıklama' ?></th>
                          <th>Tarih</th>
                          <th>Tutar</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach (array_slice($recent_transactions, 0, 5) as $trx): ?>
                          <tr>
                              <td data-label="Açıklama">
                                  <span class="method-cell">
                                      <span class="method-icon">
                                          <?php if (!empty($trx->bank_name)): ?>
                                              <svg class="icon"><use href="#i-bank"/></svg>
                                          <?php elseif (!empty($trx->user_balance_order_id)): ?>
                                              <svg class="icon"><use href="#i-bag"/></svg>
                                          <?php else: ?>
                                              <svg class="icon"><use href="#i-card"/></svg>
                                          <?php endif; ?>
                                      </span>
                                      <span class="product-name"><?= htmlspecialchars($trx->description ?? '-') ?></span>
                                  </span>
                              </td>
                              <td data-label="Tarih" class="subtle"><?= $trx->created_at ?? '-' ?></td>
                              <td data-label="Tutar" class="<?= ($trx->type ?? '') == 'in' ? 'amount-positive' : 'amount-negative' ?>">
                                  <?= ($trx->type ?? '') == 'in' ? '+' : '-' ?> <?= $trx->amount ?? '0.00' ?> ₺
                              </td>
                          </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
          </div>
          <?php else: ?>
          <div class="empty-state">
              <span class="empty-icon"><svg class="icon"><use href="#i-history"/></svg></span>
              <h3><?= $this->lang->line('no_balance_history') ?? 'Bakiye Hareketi Yok' ?></h3>
              <p><?= $this->lang->line('no_balance_history_desc') ?? 'Henüz hesabınızda bir hareket bulunmuyor.' ?></p>
          </div>
          <?php endif; ?>
        </section>
      </section>
    </section>
</main>