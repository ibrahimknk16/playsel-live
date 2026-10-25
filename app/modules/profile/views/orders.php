<main class="workspace">
    <?php $this->load->view('profile/left-menu'); ?>

    <section class="content" aria-live="polite">
      <section class="view" id="view-orders" data-view="orders">
        <header class="page-hero">
            <span class="hero-icon"><svg class="icon"><use href="#i-bag"/></svg></span>
            <div class="hero-copy">
                <h1>Siparişlerim</h1>
                <p>Sipariş geçmişinizi görüntüleyin ve takip edin.</p>
            </div>
        </header>
        <section class="subnav-shell">
          <div class="tab-list" role="tablist" aria-label="Sipariş durumları" data-filter-tabs="orders">
            <a href="?status=all" style="text-decoration:none;" class="tab-button <?= ($active_status == 'all') ? 'active' : '' ?>"><svg class="icon icon-fill"><use href="#i-filter"/></svg>Tümü</a>
            <a href="?status=pending" style="text-decoration:none;" class="tab-button <?= ($active_status == 'pending') ? 'active' : '' ?>"><svg class="icon"><use href="#i-clock"/></svg>Beklemede</a>
            <a href="?status=completed" style="text-decoration:none;" class="tab-button <?= ($active_status == 'completed') ? 'active' : '' ?>"><svg class="icon"><use href="#i-check"/></svg>Tamamlandı</a>
            <a href="?status=cancelled" style="text-decoration:none;" class="tab-button <?= ($active_status == 'cancelled') ? 'active' : '' ?>"><svg class="icon"><use href="#i-x"/></svg>İptal edildi</a>
          </div>
          <form action="<?= base_url($this->uri->segment(1) . '/profile/orders') ?>" method="GET" class="filter-row">
            <input type="hidden" name="status" value="<?= html_escape($active_status) ?>">
            <div class="date-control">
                <input type="text" id="flatpickr-date" name="date_range" placeholder="Tarih aralığı seçin" aria-label="Tarih aralığı seçin" value="<?= html_escape($this->input->get('date_range') ?? '') ?>">
                <span class="calendar"><svg class="icon"><use href="#i-calendar"/></svg></span>
                <button class="filter-submit" type="submit" aria-label="Tarih filtresini uygula"><svg class="icon"><use href="#i-filter"/></svg></button>
            </div>
            <a href="?status=<?= html_escape($active_status) ?>&date=1week" style="text-decoration:none;" class="quick-filter <?= ($this->input->get('date') == '1week') ? 'active' : '' ?>">Son 1 hafta</a>
            <a href="?status=<?= html_escape($active_status) ?>&date=1month" style="text-decoration:none;" class="quick-filter <?= ($this->input->get('date') == '1month') ? 'active' : '' ?>">Son 1 ay</a>
            <a href="?status=<?= html_escape($active_status) ?>&date=3months" style="text-decoration:none;" class="quick-filter <?= ($this->input->get('date') == '3months') ? 'active' : '' ?>">Son 3 ay</a>
            <a href="<?= base_url($this->uri->segment(1) . '/profile/orders') ?>" style="text-decoration:none;" class="icon-button danger" aria-label="Filtreleri temizle"><svg class="icon"><use href="#i-x"/></svg></a>
            <button class="icon-button success" type="button" aria-label="Yenile" onclick="window.location.reload();"><svg class="icon"><use href="#i-refresh"/></svg></button>
          </form>
          <div class="panel-body" id="orders-list-container" style="position:relative;">
            <?php $this->load->view('profile/orders_list_partial'); ?>
          </div>
        </section>
      </section>
    </section>

    <!-- Review Modal -->
    <div id="reviewModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(3px);">
        <div class="modal-content" style="background:var(--bg-secondary, #1f1f2e); padding:24px; border-radius:12px; width:90%; max-width:400px; position:relative; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <h3 style="color:#fff; margin-top:0; font-size:18px;">Siparişi Değerlendir</h3>
            <span class="close-review-modal" style="position:absolute; top:15px; right:20px; color:#fff; cursor:pointer; font-size:24px; line-height:1;">&times;</span>
            
            <form id="reviewForm">
                <input type="hidden" name="order_id" id="review_order_id">
                
                <div style="margin:20px 0; text-align:center;">
                    <p style="color:#e0e0e0; font-size:14px; margin-bottom:10px;">Lütfen puan verin:</p>
                    <div class="star-rating" style="display:flex; justify-content:center; gap:10px; flex-direction:row-reverse;">
                        <input type="radio" id="star5" name="rating" value="5" style="display:none;" />
                        <label for="star5" title="5 Yıldız" style="cursor:pointer; color:#555; font-size:32px; transition: color 0.2s;"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star4" name="rating" value="4" style="display:none;" />
                        <label for="star4" title="4 Yıldız" style="cursor:pointer; color:#555; font-size:32px; transition: color 0.2s;"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star3" name="rating" value="3" style="display:none;" />
                        <label for="star3" title="3 Yıldız" style="cursor:pointer; color:#555; font-size:32px; transition: color 0.2s;"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star2" name="rating" value="2" style="display:none;" />
                        <label for="star2" title="2 Yıldız" style="cursor:pointer; color:#555; font-size:32px; transition: color 0.2s;"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star1" name="rating" value="1" style="display:none;" />
                        <label for="star1" title="1 Yıldız" style="cursor:pointer; color:#555; font-size:32px; transition: color 0.2s;"><i class="fa-solid fa-star"></i></label>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <p style="color:#e0e0e0; font-size:14px; margin-bottom:10px;">Yorumunuz (İsteğe bağlı):</p>
                    <textarea name="comment" rows="3" style="width:100%; padding:12px; border-radius:8px; background:var(--bg-primary, #151515); border:1px solid rgba(255,255,255,0.1); color:#fff; resize:vertical; font-family:inherit; font-size:14px; outline:none;" placeholder="Deneyiminizi paylaşın..."></textarea>
                </div>

                <button type="submit" class="primary-button" style="width:100%; display:flex; align-items:center; justify-content:center;">Gönder</button>
            </form>
        </div>
    </div>
</main>

<div id="cancelOrderModal" class="modal" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; background:rgba(0,0,0,.65); backdrop-filter:blur(3px);">
    <div style="width:min(92%,460px); background:#1f232c; border:1px solid rgba(255,255,255,.12); border-radius:12px; padding:24px; box-shadow:0 12px 40px rgba(0,0,0,.45);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h3 style="margin:0; color:#fff;">Siparişi iptal et</h3>
            <button type="button" id="closeCancelOrderModal" class="detail-button" style="border:0; background:transparent; font-size:20px;">&times;</button>
        </div>
        <input type="hidden" id="cancelOrderId">
        <label for="cancelReasonId" style="display:block; color:#d8dde2; margin-bottom:7px;">İptal sebebi</label>
        <select id="cancelReasonId" style="width:100%; margin-bottom:12px;">
            <option value="">Manuel sebep yazacağım</option>
            <?php foreach (($cancellation_reasons ?? []) as $reason): ?>
                <option value="<?= (int)$reason->reason_id ?>"><?= html_escape($reason->reason_text) ?></option>
            <?php endforeach; ?>
        </select>
        <textarea id="cancelManualReason" rows="3" style="width:100%; resize:vertical;" placeholder="Manuel sebep (seçim yapılmadıysa zorunlu)"></textarea>
        <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">
            <button type="button" id="cancelOrderDismiss" class="secondary-button">Vazgeç</button>
            <button type="button" id="cancelOrderConfirm" class="primary-button" style="background:#c95d58;">İptal et</button>
        </div>
    </div>
</div>

<style>
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input[type="radio"]:checked ~ label {
    color: #ffc800 !important;
}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/tr.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dateInput = document.getElementById('flatpickr-date');
        if (dateInput && typeof flatpickr !== 'undefined') {
            flatpickr(dateInput, {
                mode: "range",
                locale: "tr",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d M Y",
                placeholder: "Tarih Aralığı Seçin",
                disableMobile: "true"
            });
        }

        const modal = document.getElementById('reviewModal');
        const cancelOrderModal = document.getElementById('cancelOrderModal');
        const closeBtn = document.querySelector('.close-review-modal');
        const orderIdInput = document.getElementById('review_order_id');

        function bindReviewButtons() {
            const reviewBtns = document.querySelectorAll('.review-btn');
            reviewBtns.forEach(btn => {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                newBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    orderIdInput.value = this.getAttribute('data-id');
                    modal.style.display = 'flex';
                });
            });

            document.querySelectorAll('.cancel-order-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    document.getElementById('cancelOrderId').value = this.dataset.id;
                    document.getElementById('cancelReasonId').value = '';
                    document.getElementById('cancelManualReason').value = '';
                    cancelOrderModal.style.display = 'flex';
                });
            });
            
            const clearBtns = document.querySelectorAll('.clear-filters-btn');
            clearBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchOrders('<?= base_url($this->uri->segment(1) . '/profile/orders') ?>');
                });
            });
        }

        // Global delegation for review and cancel buttons (ensures dynamic card clicks work reliably)
        document.addEventListener('click', function(e) {
            const reviewBtn = e.target.closest('.review-btn');
            if (reviewBtn) {
                e.preventDefault();
                e.stopPropagation();
                orderIdInput.value = reviewBtn.getAttribute('data-id');
                modal.style.display = 'flex';
                return;
            }

            const cancelBtn = e.target.closest('.cancel-order-btn');
            if (cancelBtn) {
                e.preventDefault();
                e.stopPropagation();
                document.getElementById('cancelOrderId').value = cancelBtn.dataset.id;
                document.getElementById('cancelReasonId').value = '';
                document.getElementById('cancelManualReason').value = '';
                cancelOrderModal.style.display = 'flex';
                return;
            }
        });

        document.getElementById('closeCancelOrderModal').addEventListener('click', () => {
            cancelOrderModal.style.display = 'none';
        });
        document.getElementById('cancelOrderDismiss').addEventListener('click', () => {
            cancelOrderModal.style.display = 'none';
        });
        document.getElementById('cancelOrderConfirm').addEventListener('click', function() {
            const reasonId = document.getElementById('cancelReasonId').value;
            const manualReason = document.getElementById('cancelManualReason').value.trim();
            if (!reasonId && !manualReason) {
                alert('Lütfen iptal sebebi seçin veya yazın.');
                return;
            }
            const button = this;
            button.disabled = true;
            const formData = new FormData();
            formData.append('order_id', document.getElementById('cancelOrderId').value);
            formData.append('reason_id', reasonId);
            formData.append('manual_reason', manualReason);
            fetch('<?= base_url($this->uri->segment(1) . '/profile/cancelOrder') ?>', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (!data.status) {
                        alert(data.message || 'İptal işlemi başarısız oldu.');
                        return;
                    }
                    cancelOrderModal.style.display = 'none';
                    fetchOrders(window.location.href);
                })
                .catch(() => alert('İptal işlemi sırasında bir hata oluştu.'))
                .finally(() => { button.disabled = false; });
        });
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                document.getElementById('reviewForm').reset();
            });
        }
        
        bindReviewButtons();

        window.addEventListener('click', function(e) {
            if (e.target == modal) {
                modal.style.display = 'none';
                document.getElementById('reviewForm').reset();
            }
        });

        // AJAX Filtering Logic
        function fetchOrders(url) {
            const container = document.getElementById('orders-list-container');
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
                
                if (typeof window.buildOrderCards === 'function') {
                    window.buildOrderCards();
                }
                bindReviewButtons();
                window.history.pushState({path: url}, '', url);
                
                // Update active classes on tabs and filters based on URL
                const urlObj = new URL(url, window.location.origin);
                const status = urlObj.searchParams.get('status') || 'all';
                const date = urlObj.searchParams.get('date');
                
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                let activeTab = document.querySelector(`.tab-button[href*="status=${status}"]`);
                if(activeTab) activeTab.classList.add('active');
                
                document.querySelectorAll('.quick-filter').forEach(btn => btn.classList.remove('active'));
                if(date) {
                    let activeDate = document.querySelector(`.quick-filter[href*="date=${date}"]`);
                    if(activeDate) activeDate.classList.add('active');
                }
                
                // Update form action and inputs
                document.querySelector('input[name="status"]').value = status;
            })
            .catch(err => {
                console.error(err);
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            });
        }

        document.querySelector('.tab-list').addEventListener('click', function(e) {
            const btn = e.target.closest('a.tab-button');
            if(btn) {
                e.preventDefault();
                fetchOrders(btn.href);
            }
        });

        document.querySelectorAll('.quick-filter').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                fetchOrders(this.href);
            });
        });

        const filterForm = document.querySelector('.filter-row');
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const url = new URL(this.action);
                const formData = new FormData(this);
                formData.forEach((value, key) => {
                    if (value) url.searchParams.set(key, value);
                });
                fetchOrders(url.toString());
            });
            
            const clearBtn = filterForm.querySelector('.icon-button.danger');
            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchOrders(this.href);
                    // Also clear flatpickr
                    const dateInput = document.getElementById('flatpickr-date');
                    if (dateInput && dateInput._flatpickr) {
                        dateInput._flatpickr.clear();
                    }
                });
            }
        }

        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('.profile-pagination-wrapper a');
            if(pageLink) {
                e.preventDefault();
                const href = pageLink.getAttribute('href');
                if (!href || href === '#' || pageLink.classList.contains('active')) {
                    return;
                }
                fetchOrders(pageLink.href);
                // Scroll to top of table
                document.getElementById('view-orders').scrollIntoView({behavior: 'smooth'});
            }
        });
        
        window.addEventListener('popstate', function(e) {
            if(e.state && e.state.path) {
                fetchOrders(e.state.path);
            }
        });

        // Form submit AJAX
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const rating = document.querySelector('input[name="rating"]:checked');
                if (!rating) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Eksik Bilgi',
                            text: 'Lütfen bir yıldız seçin.',
                            confirmButtonColor: '#ff4757',
                            background: '#1f1f2e',
                            color: '#fff'
                        });
                    } else {
                        alert('Lütfen bir yıldız seçin.');
                    }
                    return;
                }

                const formData = new FormData(this);

                fetch('<?= base_url($this->uri->segment(1) . '/profile/submit_review') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        modal.style.display = 'none';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Başarılı!',
                                text: data.message,
                                confirmButtonColor: '#ff4757',
                                background: '#1f1f2e',
                                color: '#fff'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            alert(data.message);
                            location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Hata!',
                                text: data.message,
                                confirmButtonColor: '#ff4757',
                                background: '#1f1f2e',
                                color: '#fff'
                            });
                        } else {
                            alert(data.message);
                        }
                    }
                })
                .catch(error => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata!',
                            text: 'Bir hata oluştu. Lütfen tekrar deneyin.',
                            confirmButtonColor: '#ff4757',
                            background: '#1f1f2e',
                            color: '#fff'
                        });
                    } else {
                        alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                    }
                });
            });
        }
    });
</script>