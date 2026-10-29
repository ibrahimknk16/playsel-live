<?php
$pending_reviews_count = $this->db->where('review_status', 0)->count_all_results('reviews');
$pending_tickets_count = $this->db->where('tickets_status', 0)->count_all_results('tickets');
$pending_referral_count = $this->db->where('request_status', 2)->count_all_results('reference_change_requests');
$pending_streamers_count = $this->db->where('user_streamer_status', 2)->count_all_results('users');
$total_pending_users = ($pending_referral_count ?? 0) + ($pending_streamers_count ?? 0);
?>
<div id="kt_aside" class="aside">
	<div class="aside-menu flex-column-fluid px-3">
		<div class="side-search">
			<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
			<input type="search" id="adminMenuSearch" placeholder="Menüde ara..." aria-label="Menüde ara">
		</div>
		<div class="hover-scroll-overlay-y my-4 pe-2" id="kt_aside_menu_wrapper" data-kt-scroll="true"
			data-kt-scroll-activate="true" data-kt-scroll-height="auto"
			data-kt-scroll-dependencies="{default: '#kt_aside_footer', lg: '#kt_header, #kt_aside_footer'}"
			data-kt-scroll-wrappers="#kt_aside, #kt_aside_menu" data-kt-scroll-offset="{default: '5px', lg: '75px'}">
			<div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-7" id="kt_aside_menu"
				data-kt-menu="true">

				<!-- ========================================================
				     1. ALIŞ-SATIŞ YÖNETİMİ
				     ======================================================== -->
				<div class="menu-item">
					<div class="menu-content pb-1">
						<span class="menu-section-header">ALIŞ-SATIŞ YÖNETİMİ</span>
					</div>
				</div>

				<script>
					$(document).on('input', '#adminMenuSearch', function() {
						var query = $.trim($(this).val()).toLocaleLowerCase('tr-TR');
						$('#kt_aside_menu .menu-item').each(function() {
							var isSection = $(this).find('.menu-section-header').length > 0;
							if (isSection) {
								$(this).toggle(!query);
								return;
							}
							var matches = !query || $(this).text().toLocaleLowerCase('tr-TR').indexOf(query) !== -1;
							$(this).toggle(matches);
						});
					});
				</script>

				<!-- Dashboard -->
				<div class="menu-item">
					<a href="<?= base_url('') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-gauge-high"></i>
						</span>
						<span class="menu-title">Dashboard</span>
					</a>
				</div>

				<!-- Raporlar Accordion -->
				<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
					<span class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-chart-line"></i>
						</span>
						<span class="menu-title">Raporlar</span>
						<span class="menu-arrow"></span>
					</span>
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('reports?status[]=1') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Satış Raporları</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('reports/paymentMethods') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Ödeme Yöntemleri</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('dashboard/userKpi') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Kullanıcı Raporları</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('reports/paymentReports') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Ödeme Raporları</span>
							</a>
						</div>
					</div>
				</div>

				<!-- Siparişler & Ödemeler -->
				<div class="menu-item">
					<a href="<?= base_url('orders') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-cart-shopping"></i>
						</span>
						<span class="menu-title">Sipariş & Ödemeler</span>
					</a>
				</div>
				<div class="menu-item">
					<a href="<?= base_url('orders/legacy') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-clock-rotate-left"></i>
						</span>
						<span class="menu-title">Legacy Geçmiş</span>
					</a>
				</div>

				<!-- Ödeme ve Bakiye Yönetimi -->
				<div class="menu-item">
					<a href="<?= base_url('balance') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-wallet"></i>
						</span>
						<span class="menu-title">Bakiye & Ödeme Yönetimi</span>
					</a>
				</div>
				<div class="menu-item">
					<a href="<?= base_url('balance/refunds') ?>" class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-rotate-left"></i></span>
						<span class="menu-title">İade Bekleyen Ödemeler</span>
					</a>
				</div>

				<!-- ========================================================
				     2. ÜRÜN YÖNETİMİ
				     ======================================================== -->
				<div class="menu-item pt-3">
					<div class="menu-content pb-1">
						<span class="menu-section-header">ÜRÜN YÖNETİMİ</span>
					</div>
				</div>

				<!-- Ürün Yönetimi Accordion -->
				<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
					<span class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-box-archive"></i>
						</span>
						<span class="menu-title">
							Ürün Yönetimi
							<?php if ($pending_reviews_count > 0): ?>
								<span class="badge badge-light-danger ms-2" style="padding: 0.25rem 0.45rem; font-size: 0.7rem;"><?= $pending_reviews_count ?></span>
							<?php endif; ?>
						</span>
						<span class="menu-arrow"></span>
					</span>
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('products') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Ürün Listesi</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('products/productPrices') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Ürün Fiyatları</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('products/productSorts') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Ürün Sıralamaları</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('products/reviews') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">
									Ürün Yorumları
									<?php if ($pending_reviews_count > 0): ?>
										<span class="badge badge-light-danger ms-2" style="padding: 0.2rem 0.4rem; font-size: 0.7rem;"><?= $pending_reviews_count ?></span>
									<?php endif; ?>
								</span>
							</a>
						</div>
					</div>
				</div>

				<!-- Kategori Yönetimi Accordion -->
				<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
					<span class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-layer-group"></i>
						</span>
						<span class="menu-title">Kategori Yönetimi</span>
						<span class="menu-arrow"></span>
					</span>
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('categories') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Kategori Listesi</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('categories/categorySorts') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Kategori Sıralamaları</span>
							</a>
						</div>
					</div>
				</div>

				<!-- ========================================================
				     3. ÜYE YÖNETİMİ
				     ======================================================== -->
				<div class="menu-item pt-3">
					<div class="menu-content pb-1">
						<span class="menu-section-header">ÜYE YÖNETİMİ</span>
					</div>
				</div>

				<!-- Müşteri Yönetimi Accordion -->
				<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
					<span class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-users"></i>
						</span>
						<span class="menu-title">
							Müşteri Yönetimi
							<?php if ($total_pending_users > 0): ?>
								<span class="badge badge-light-danger ms-2" style="padding: 0.25rem 0.45rem; font-size: 0.7rem;"><?= $total_pending_users ?></span>
							<?php endif; ?>
						</span>
						<span class="menu-arrow"></span>
					</span>
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('users') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Son Kullanıcılar</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('users/acquisitionReport') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Kullanıcı Edinim Raporu</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('users/referenceRequests') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">
									Referans İstekleri
									<?php if ($pending_referral_count > 0): ?>
										<span class="badge badge-light-danger ms-2" style="padding: 0.2rem 0.4rem; font-size: 0.7rem;"><?= $pending_referral_count ?></span>
									<?php endif; ?>
								</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('users/referralReport') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Referans Raporu</span>
							</a>
						</div>
						<div class="menu-item">
							<a class="menu-link" href="<?= base_url('users/restrictions') ?>">
								<span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
								<span class="menu-title">Müşteri Kısıtlamaları</span>
							</a>
						</div>
					</div>
				</div>

				<!-- Yayıncı Yönetimi -->
				<div class="menu-item">
					<a href="<?= base_url('users/streamers') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-video"></i>
						</span>
						<span class="menu-title">
							Yayıncı Yönetimi
							<?php if ($pending_streamers_count > 0): ?>
								<span class="badge badge-light-danger ms-2" style="padding: 0.25rem 0.45rem; font-size: 0.7rem;"><?= $pending_streamers_count ?></span>
							<?php endif; ?>
						</span>
					</a>
				</div>
				<div class="menu-item">
					<a href="<?= base_url('users/nicknameRequests') ?>" class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-at"></i></span>
						<span class="menu-title">Nickname Talepleri</span>
					</a>
				</div>

				<!-- Personel & Admin Yönetimi -->
				<div class="menu-item">
					<a href="<?= base_url('admins') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-user-shield"></i>
						</span>
						<span class="menu-title">Personel & Admin</span>
					</a>
				</div>

				<!-- ========================================================
				     4. İÇERİK YÖNETİMİ
				     ======================================================== -->
				<div class="menu-item pt-3">
					<div class="menu-content pb-1">
						<span class="menu-section-header">İÇERİK YÖNETİMİ</span>
					</div>
				</div>

				<!-- Destek Sistemi -->
				<div class="menu-item">
					<a href="<?= base_url('tickets') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-headset"></i>
						</span>
						<span class="menu-title">
							Destek Sistemi
							<?php if ($pending_tickets_count > 0): ?>
								<span class="badge badge-light-danger ms-2" style="padding: 0.25rem 0.45rem; font-size: 0.7rem;"><?= $pending_tickets_count ?></span>
							<?php endif; ?>
						</span>
					</a>
				</div>

				<!-- Kupon Kodları -->
				<div class="menu-item">
					<a href="<?= base_url('coupon') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-ticket"></i>
						</span>
						<span class="menu-title">Kupon Kodları</span>
					</a>
				</div>

				<!-- Haber Yönetimi -->
				<div class="menu-item">
					<a href="<?= base_url('news') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-newspaper"></i>
						</span>
						<span class="menu-title">Haber Yönetimi</span>
					</a>
				</div>

				<!-- Slider Yönetimi -->
				<div class="menu-item">
					<a href="<?= base_url('sliders') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-sliders"></i>
						</span>
						<span class="menu-title">Slider Yönetimi</span>
					</a>
				</div>

				<!-- Vitrin Yönetimi -->
				<div class="menu-item">
					<a href="<?= base_url('showcase') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-table-cells-large"></i>
						</span>
						<span class="menu-title">Vitrin Yönetimi</span>
					</a>
				</div>

				<!-- CDN Galerisi -->
				<div class="menu-item">
					<a href="<?= base_url('cdn') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-photo-film"></i>
						</span>
						<span class="menu-title">CDN Galerisi</span>
					</a>
				</div>

				<!-- Site Ayarları -->
				<div class="menu-item">
					<a href="<?= base_url('settings') ?>" class="menu-link">
						<span class="menu-icon">
							<i class="fa-solid fa-gears"></i>
						</span>
						<span class="menu-title">Site Ayarları</span>
					</a>
				</div>

			</div>
		</div>
	</div>
	<div class="side-bottom"><i aria-hidden="true"></i><span>Playsel Yönetim Paneli</span></div>
	<script>
		$(function() {
			var path = window.location.pathname.replace(/\/+$/, '');
			var best = null, bestLen = -1;
			$('#kt_aside_menu a.menu-link[href]').each(function() {
				var href = this.getAttribute('href');
				if (!href || href.charAt(0) === '#') { return; }
				var p;
				try { p = new URL(href, window.location.origin).pathname.replace(/\/+$/, ''); } catch (e) { return; }
				if (p === '' || p === '/') { return; }
				if ((path === p || path.indexOf(p + '/') === 0) && p.length > bestLen) { best = this; bestLen = p.length; }
			});
			if (best) {
				$(best).addClass('active');
				$(best).parents('.menu-item.menu-accordion').addClass('here show');
			}
		});
	</script>
</div>