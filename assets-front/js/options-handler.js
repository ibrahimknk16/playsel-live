$(function () {
    // Ülke dropdown'u doldur
    const $select = $("#locationSelect");
    countries.forEach(function (element) {
        const $option = $("<option></option>")
            .val(element.code)
            .text(element.name + " " + getFlagEmoji(element.code));
        $select.append($option);
    });


    // Dil dropdown'u doldur
    const $langSelect = $("#languageSelect");
    languages.forEach(function (element) {
        const $option = $("<option></option>")
            .val(element.code)
            .text(element.name + " " + getFlagEmoji(element.code));
        $langSelect.append($option);
    });

    // Currency dropdown'u doldur
    const currencyData = currencies[0]
    const $currencySelect = $("#currencySelect");
    for (const code in currencyData) {
        const $option = $("<option></option>")
            .val(code)
            .text(currencyData[code].name + " " + currencyData[code].symbol_native);
        $currencySelect.append($option);
    }

    // Dil kontrolü
    const langCheck = localStorage.getItem("language");
    $("#current-lang").text(langCheck ? langCheck.toUpperCase() : "TR");

    // Şifre göster/gizle
    $('.toggle-password').on('click', function () {
        const targetId = $(this).data('target');
        const $input = $('#' + targetId);
        const type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // LocalStorage'dan verileri yükle
    const savedLang = localStorage.getItem("language");
    const savedCountry = localStorage.getItem("country");
    const savedCurrency = localStorage.getItem("currency");

    if (savedLang) $("#languageSelect").val(savedLang);
    if (savedCountry) $("#locationSelect").val(savedCountry);
    if (savedCurrency) $("#currencySelect").val(savedCurrency);

    // else updateSelectedCurrency("USD");

    renderPrices();
});


// // Seçilen para birimini göster
// function updateSelectedCurrency(code) {
//     const currency = currencies[code];
//     const display = `
//         <span class="currency-icon">${currency.symbol_native}</span> &nbsp- ${currency.name}
//     `;
//     $("#selectedCurrencyDisplay").html(display);

//     // Aktif class güncelle
//     $('#currencyDropdownMenu a').removeClass("active");
//     $(`#currencyDropdownMenu a[data-code="${code}"]`).addClass("active");

//     // renderPricesWithCurrency(code); // aktif değilse yoruma alabilirsin
// }

// Fiyatları göster
function renderPrices() {
    const currencyCode = localStorage.getItem("currency") || "USD";
    // renderPricesWithCurrency(currencyCode); // aktif değilse yoruma al
}

function renderPricesWithCurrency(code) {
    const currency = currencies[code];
    $(".price").each(function () {
        const amount = $(this).data("amount");
        $(this).html(`Fiyat: ${amount} ${currency.symbol_native}`);
    });
}

// Ayarları kaydet
function saveOptions() {
    const selectedLang = $("#languageSelect").val();
    const selectedCountry = $("#locationSelect").val();
    const selectedCurrency = $('#currencySelect').val() || "USD";

    localStorage.setItem("language", selectedLang);
    localStorage.setItem("country", selectedCountry);
    localStorage.setItem("currency", selectedCurrency);

    renderPrices();
    showToast();
}

// Bayrak emojisi üret
function getFlagEmoji(countryCode) {
    return countryCode
        .toUpperCase()
        .replace(/./g, char =>
            String.fromCodePoint(127397 + char.charCodeAt())
        );
}

// Sekme geçişi
function showTab(tabId, event) {
    $('.tab-content').hide();
    $('.tab-btn').removeClass('active');

    $('#' + tabId).show();
    $(event.target).addClass('active');
}

// Toast göster
function showToast() {
    const toastEl = $("#toastSave")[0];
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: 3000 // 3 saniye önerilir, istersen yine 110000 yapabilirsin
    });
    toast.show();
}
