/**
    * regionModal
*/

; (function ($) {

    "use strict";

    var isMobile = {
        Android: function () {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function () {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function () {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function () {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function () {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function () {
            return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
        }
    };

    /* =========================================
       REGION MODAL (DYNAMIC DATA LOAD)
    ========================================= */
    var regionModal = function () {
        var modal = $('#regionModal');
        var openBtn = $('#openRegionModal');
        var closeBtn = $('#closeRegionModal');
        var overlay = $('.region-modal-overlay');
        var tabs = $('.region-tab');
        var panes = $('.region-pane');
        var confirmBtn = $('.region-confirm-btn');
        var languageGrid = $('#dynamic-language-grid');
        var countryTabSubtitle = $('.region-tab[data-target="country-pane"] .tab-subtitle');
        var langTabSubtitle = $('.region-tab[data-target="language-pane"] .tab-subtitle');

        openBtn.off('click').on('click', function (e) {
            e.preventDefault();
            modal.addClass('open');
            $('body').css('overflow', 'hidden');
        });

        var closeModal = function () {
            modal.removeClass('open');
            $('body').css('overflow', '');
        };

        closeBtn.off('click').on('click', closeModal);
        overlay.off('click').on('click', closeModal);
        
        confirmBtn.off('click').on('click', function() {
            var selectedCountry = $('input[name="country"]:checked').val();
            var selectedLangRadio = $('input[name="language"]:checked');
            var selectedLangCode = selectedLangRadio.data('lang-code');

            if (!selectedCountry || !selectedLangCode) {
                alert('Lütfen ülke ve dil seçimi yapınız.');
                return;
            }

            var currentUri = window.location.pathname.split('/').filter(Boolean)[0] || 'tr-TR';
            var ajaxUrl = '/' + currentUri + '/profile/change-region';

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    country: selectedCountry,
                    lang: selectedLangCode
                },
                success: function(response) {
                    if (response.status && response.newUri) {
                        // Mevcut URI'yi yeni URI ile değiştir ve sayfayı yeniden yükle
                        var currentPath = window.location.pathname;
                        var newPath = currentPath.replace(/^\/[^\/]+/, '/' + response.newUri);
                        // Eğer sadece ana sayfadaysak
                        if (newPath === currentPath || newPath === '') {
                            newPath = '/' + response.newUri;
                        }
                        window.location.href = newPath;
                    } else {
                        alert(response.message || 'Bir hata oluştu.');
                    }
                },
                error: function() {
                    alert('İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.');
                }
            });
        });

        tabs.off('click').on('click', function () {
            var target = $(this).data('target');
            tabs.removeClass('active');
            $(this).addClass('active');
            panes.removeClass('active');
            $('#' + target).addClass('active');
        });

        $('input[name="country"]').off('change').on('change', function () {
            var countryName = $(this).data('name');
            var languagesJson = $(this).data('languages');
            countryTabSubtitle.text(countryName);
            languageGrid.empty();
            languagesJson.forEach(function(lang, index) {
                var isChecked = (index === 0) ? 'checked' : '';
                var displayName = lang.name + ' - ' + lang.code;
                
                var html = `
                    <label class="region-radio-label">
                        <input type="radio" name="language" value="${lang.id}" data-lang-code="${lang.code.toLowerCase()}" ${isChecked}>
                        <div class="region-card">
                            <span>${displayName}</span>
                        </div>
                    </label>
                `;
                languageGrid.append(html);
            });

            languageGrid.find('input[name="language"]:checked').trigger('change');
        });

        languageGrid.on('change', 'input[name="language"]', function () {
            var langName = $(this).siblings('.region-card').find('span').text();
            langTabSubtitle.text(langName);
        });

        if ($('input[name="country"]:checked').length > 0) {
            $('input[name="country"]:checked').trigger('change');
        }
    };

    // Dom Ready
    $(function () {

        regionModal();
    });

})(jQuery);

/* =========================================
   LAZY-LOAD FADE-IN HANDLER
========================================= */
(function () {
    'use strict';
    function onImgLoad(img) { img.classList.add('ps-loaded'); }
    document.querySelectorAll('img[loading="lazy"]').forEach(function (img) {
        if (img.complete) { onImgLoad(img); }
        else { img.addEventListener('load', function () { onImgLoad(img); }); }
    });
})();

/* ==========================================================================
   UNIFIED TOAST & ALERT HELPER (PLAYSEL DESIGN SYSTEM)
   ========================================================================== */
(function() {
    'use strict';

    function setupToastrOptions() {
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton: true,
                debug: false,
                newestOnTop: true,
                progressBar: false,
                positionClass: "toast-top-right",
                preventDuplicates: true,
                tapToDismiss: true,
                showDuration: 0,
                showMethod: "show",
                hideDuration: 200,
                hideMethod: "fadeOut",
                timeOut: 3500,
                extendedTimeOut: 1000,
                showEasing: "swing",
                hideEasing: "linear",
                onHidden: function() {
                    try {
                        var $el = $(this);
                        if ($el && $el.length) {
                            $el.remove();
                        }
                    } catch (e) {}
                }
            };
        }
    }

    setupToastrOptions();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupToastrOptions);
    }

    // Doğrudan tıklamayla veya kapat butonuyla kapanmayı garantiye al
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('click', '#toast-container .toast-close-button', function(e) {
            e.stopPropagation();
            e.preventDefault();
            var $toast = jQuery(this).closest('#toast-container > div');
            if ($toast.length) {
                $toast.addClass('toast-closing').stop(true, true).fadeOut(150, function() {
                    jQuery(this).remove();
                });
            }
        });

        jQuery(document).on('click', '#toast-container > div', function(e) {
            if (jQuery(e.target).closest('.toast-close-button').length) return;
            jQuery(this).addClass('toast-closing').stop(true, true).fadeOut(150, function() {
                jQuery(this).remove();
            });
        });
    }

    window.showToast = function(message, type) {
        if (!message) return;
        type = (type || 'info').toLowerCase();
        if (type === 'danger') type = 'error';

        if (typeof toastr !== 'undefined') {
            if (type === 'success') toastr.success(message);
            else if (type === 'error') toastr.error(message);
            else if (type === 'warning') toastr.warning(message);
            else toastr.info(message);
            return;
        }

        // Fallback if toastr script is not loaded
        let container = document.querySelector('.ps-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'ps-toast-container';
            document.body.appendChild(container);
        }

        const iconClass = type === 'success' ? 'fa-circle-check' :
                         (type === 'error' ? 'fa-circle-exclamation' :
                         (type === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-info'));

        const toast = document.createElement('div');
        toast.className = 'ps-toast ps-toast-' + (type === 'error' ? 'error' : (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info')));
        toast.innerHTML = `
            <div class="ps-toast-icon"><i class="fa-solid ${iconClass}"></i></div>
            <div class="ps-toast-message">${message}</div>
            <button type="button" class="ps-toast-close" title="Kapat"><i class="fa-solid fa-xmark"></i></button>
        `;

        const closeBtn = toast.querySelector('.ps-toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 250);
            });
        }

        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 250);
        }, 3500);
    };

    window.toast = window.showToast;
})();
