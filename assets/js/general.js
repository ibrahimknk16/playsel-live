$(document).on('click', '[data-dynamic-table-row]', function (e) {
	load_page_loading();
	e.preventDefault();
	var btn = $(this);
	var table = $(this).closest('table');


	table.find('[data-dynamic-table-row]').find('.fa-fw').removeClass('fa-chevron-up').addClass('fa-chevron-down');
	if (btn.closest('tr').next('.dynamic-tr').length > 0) {
		btn.closest('tr').next('.dynamic-tr').find('.content').slideUp(250, function () {

			$(this).closest('.dynamic-tr').remove();
		});
		remove_page_loading();
	}
	else {


		$.get(btn.attr('data-dynamic-table-row'), function (data) {
			$('.dynamic-tr').find('.content').slideUp(250, function () {
				$(this).closest('.dynamic-tr').remove();
			});
			btn.closest('tr').after(data);
			btn.find('.fa-fw').removeClass('fa-chevron-down').addClass('fa-chevron-up');
			btn.closest('tr').next('.dynamic-tr').find('.content').slideDown(250);
			remove_page_loading();
		});

	}
});

function load_page_loading() {
	var body = $('body');
	if (body.hasClass('page-loading')) {
		return false;
	}
	body.addClass('page-loading');
	var div = $('<div class="page-loader page-loader-1 flex-column bg-dark bg-opacity-50"> <span class="spinner-border text-primary" role="status"></span> <span class="text-gray-800 fs-6 fw-semibold mt-5">Lütfen Bekleyiniz...</span> </div>');
	$('body').append(div);
	div.fadeIn(100);
}
function remove_page_loading() {
	var body = $('body');
	var div = $('.page-loader');
	div.fadeOut(100, function () {
		div.remove();
		body.removeClass('page-loading');
	});

}

document.addEventListener('DOMContentLoaded', () => {
	toastr.options = {
		"closeButton": true,
		"debug": false,
		"newestOnTop": true,
		"progressBar": true,
		"positionClass": "toastr-top-right",
		"preventDuplicates": false,
		"onclick": null,
		"showDuration": "300",
		"hideDuration": "1000",
		"timeOut": "5000",
		"extendedTimeOut": "1000",
		"showEasing": "swing",
		"hideEasing": "linear",
		"showMethod": "fadeIn",
		"hideMethod": "fadeOut",
	};

	// Sidebar Toggle Logic (Desktop & Mobile)
	var toggleBtn = document.getElementById('kt_aside_toggle');
	var asideEl = document.getElementById('kt_aside');
	if (toggleBtn && asideEl) {
		function updateToggleIcon(isCollapsed) {
			var icon = toggleBtn.querySelector('i');
			if (icon) {
				if (isCollapsed) {
					icon.classList.remove('fa-angles-left');
					icon.classList.add('fa-angles-right');
				} else {
					icon.classList.remove('fa-angles-right');
					icon.classList.add('fa-angles-left');
				}
			}
		}

		// Restore desktop state
		if (window.innerWidth >= 992) {
			if (localStorage.getItem('admin_aside_collapsed') === '1') {
				document.body.classList.add('aside-collapsed');
				updateToggleIcon(true);
			}
		}

		// Create backdrop for mobile
		var backdrop = document.querySelector('.aside-mobile-backdrop');
		if (!backdrop) {
			backdrop = document.createElement('div');
			backdrop.className = 'aside-mobile-backdrop';
			document.body.appendChild(backdrop);
			backdrop.addEventListener('click', function() {
				document.body.classList.remove('aside-mobile-open');
			});
		}

		toggleBtn.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			if (window.innerWidth < 992) {
				document.body.classList.toggle('aside-mobile-open');
			} else {
				var isCollapsed = document.body.classList.toggle('aside-collapsed');
				localStorage.setItem('admin_aside_collapsed', isCollapsed ? '1' : '0');
				updateToggleIcon(isCollapsed);
			}
		});

		window.addEventListener('resize', function() {
			if (window.innerWidth >= 992) {
				document.body.classList.remove('aside-mobile-open');
			}
		});
	}
});
