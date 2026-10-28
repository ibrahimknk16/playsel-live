<div id="kt_header" class="header">
	<div class="container-fluid d-flex flex-stack align-items-center">
		<!-- Left: Logo & Badges -->
		<div class="d-flex align-items-center me-3">
			<div class="header-brand-wrap">
				<a href="<?= base_url() ?>" class="d-flex align-items-center text-decoration-none">
					<img src="https://cdn.epinium.net/v2/epinium/playcell.com/Theme/ade95f0c-0e35-4f09-a43c-14ab010fdafb/Playsel-Logo-beyaz.webp"
					     class="header-brand-logo"
					     alt="Playsel">
				</a>
				<button type="button" class="header-aside-btn" id="kt_aside_toggle" title="Menüyü Kapat / Aç">
					<i class="fa-solid fa-angles-left fs-8"></i>
				</button>
			</div>

			<div class="topbar-badges d-flex align-items-center ms-3" aria-label="Bekleyen işlemler">
				<a href="<?= base_url('orders') ?>" class="topbar-badge-item" data-counter-key="active_orders" title="Aktif siparişler"><i class="fa-solid fa-cart-shopping"></i><span class="topbar-badge-label">Siparişler</span><span class="topbar-badge-count">0</span></a>
				<a href="<?= base_url('balance') ?>" class="topbar-badge-item" data-counter-key="waiting_orders" title="Ödemesi bekleyen işlemler"><i class="fa-solid fa-money-bill-transfer"></i><span class="topbar-badge-label">Ödemeler</span><span class="topbar-badge-count">0</span></a>
				<a href="<?= base_url('products/reviews') ?>" class="topbar-badge-item" data-counter-key="reviews" title="Onay bekleyen yorumlar"><i class="fa-solid fa-star"></i><span class="topbar-badge-label">Yorum</span><span class="topbar-badge-count">0</span></a>
				<a href="<?= base_url('tickets') ?>" class="topbar-badge-item" data-counter-key="tickets" title="Bekleyen destek talepleri"><i class="fa-solid fa-headset"></i><span class="topbar-badge-label">Destek Talepleri</span><span class="topbar-badge-count">0</span></a>
				<a href="<?= base_url('users/nicknameRequests') ?>" class="topbar-badge-item" data-counter-key="nickname_requests" title="Bekleyen nickname değişiklik talepleri"><i class="fa-solid fa-user-clock"></i><span class="topbar-badge-label">Nickname talebi</span><span class="topbar-badge-count">0</span></a>
				<a href="<?= base_url('users/streamers') ?>" class="topbar-badge-item" data-counter-key="streamer_requests" title="Bekleyen yayıncı talepleri"><i class="fa-solid fa-video"></i><span class="topbar-badge-label">Yayıncı Başvuruları</span><span class="topbar-badge-count">0</span></a>
			</div>
		</div>

		<!-- Right: Tools, Currency, Admin, Logout -->
		<div class="d-flex align-items-center flex-shrink-0 gap-2">
			<!-- Siteye Git -->
			<a href="<?= topupcenter(); ?>" target="_blank" class="header-tool-btn d-none d-md-inline-flex" title="Site Ön Yüzüne Git">
				<i class="fa-solid fa-arrow-up-right-from-square fs-7"></i>
				<span>Siteye Git</span>
			</a>

			<!-- Günlük Kurlar Dropdown -->
			<div class="app-navbar-item" id="kt_header_user_menu_toggle">
				<a href="#" class="header-tool-btn" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end" title="Günlük Kurlar">
					<i class="fa-duotone fa-wallet text-warning fs-6"></i>
					<span class="d-none d-xl-inline">Kurlar</span>
				</a>

				<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-250px" data-kt-menu="true" style="background-color: #1a1b29; border: 1px solid rgba(255,255,255,0.08);">
					<div class="menu-item px-3">
						<div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase fw-bold">Günlük Kurlar</div>
					</div>

					<div id="live_currency_items">
						<div class="menu-item px-3 text-center text-muted fs-8">Yükleniyor...</div>
					</div>
				</div>
			</div>

			<!-- Admin Adı -->
			<span class="badge badge-light-primary fw-semibold px-3 py-2 d-none d-sm-inline-flex align-items-center">
				<i class="fa-solid fa-user-shield me-2 fs-8 text-primary"></i>
				<?= $this->session->userdata('admin')['admin_name'] ?? 'Admin' ?>
			</span>

			<!-- Çıkış Butonu -->
			<form method="post" action="<?= base_url('user/logout'); ?>" class="m-0">
				<input type="hidden" name="admin_action_csrf" value="<?= html_escape($this->session->userdata('admin_action_csrf_token')) ?>">
				<button type="submit" class="header-tool-btn btn-outline-danger" title="Çıkış Yap">
				<i class="fa-solid fa-right-from-bracket fs-7 text-danger"></i>
				<span class="d-none d-md-inline">Çıkış</span>
				</button>
			</form>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		$.get('<?= base_url("dashboard/currencies") ?>', function(response) {
			// Sadece item'ların basılacağı div'i hedefliyoruz
			$('#live_currency_items').html(response);
		});

		var notificationRequest;
		function refreshNotificationCounts() {
			if (notificationRequest) {
				notificationRequest.abort();
			}
			notificationRequest = $.ajax({
				url: '<?= base_url("dashboard/notificationCounts") ?>',
				dataType: 'json',
				cache: false
			}).done(function(response) {
				if (!response || !response.success || !response.counts) {
					return;
				}
				$('[data-counter-key]').each(function() {
					var key = $(this).data('counter-key');
					var count = parseInt(response.counts[key], 10) || 0;
					$(this).find('.topbar-badge-count').text(count > 99 ? '99+' : count);
					$(this).toggleClass('has-pending', count > 0);
				});
			}).fail(function(xhr, status) {
				if (status !== 'abort') {
					console.warn('Admin bildirim sayaçları güncellenemedi.', xhr.status);
				}
			});
		}
		refreshNotificationCounts();
		window.setInterval(refreshNotificationCounts, 5000);
	});
</script>