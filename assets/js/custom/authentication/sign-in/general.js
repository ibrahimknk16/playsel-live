var responseSmsToken;
var isValid = true;

$(".smsInput").first().focus();

function setTooltip(element, message) {
    $(element).removeClass("is-valid");
    $(element).addClass("is-invalid");
    element.tooltip({
        title: message,
        trigger: "hover",
        template: '<div class="tooltip my-custom-tooltip" role="tooltip" data-placement="right"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
    });
    isValid = false;
}

function hideTooltip(element) {
    $(element).removeClass("is-invalid");
    $(element).addClass("is-valid");
    element.tooltip('dispose');
    isValid = true;
}

$(".smsInput").on("input", function () {
    var value = $(this).val();
    if (isNaN(value)) {
        $(this).val("");
    } else if (value.length > 1) {
        $(this).val(value.charAt(0));
    }
    var nextInput = $(this).next(".smsInput");
    if (nextInput.length) {
        nextInput.focus();
    } else {
        $(".smsInput").prop("disabled", true);
        var code = "";
        $(".smsInput").each(function () {
            code += $(this).val();
        });

        var currentURL = window.location.href;
        var url = new URL(currentURL);
        token = url.searchParams.get("token");

        $.ajax({
            url: siteURL + "user/phoneVerificationRequest",
            type: "POST",
            data: {
                smsCode: code
            },
            success: function (response) {
                //var response = JSON.parse(response);
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                if (response.status == true) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    setTimeout(function () {
                        window.location.href = response.redirectURL;
                    }, 1500);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message
                    });
                    $(".smsInput").prop("disabled", false);
                    $(".smsInput").val("");
                    $(".smsInput").css("border", "1px solid red");
                    $(".smsInput").first().focus();
                }
            },
            error: function (xhr) {
                console.log(xhr);
                Swal.fire({
                    title: 'Hata!',
                    text: 'Bir hata oluştu. Lütfen tekrar deneyiniz.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            }
        });
    }
});

var interval;

function timer(reset = false) {
    var timer2 = "03:01";
    clearInterval(interval);
    if (reset) {
        $(".smsInput").prop("disabled", false);
        $(".smsInput").val("");
    }
    interval = setInterval(function () {
        var timer = timer2.split(':');
        var minutes = parseInt(timer[0], 10);
        var seconds = parseInt(timer[1], 10);
        --seconds;
        minutes = (seconds < 0) ? --minutes : minutes;
        if (minutes < 0) clearInterval(interval);
        seconds = (seconds < 0) ? 59 : seconds;
        seconds = (seconds < 10) ? '0' + seconds : seconds;
        $('.timer').html(minutes + ':' + seconds);
        timer2 = minutes + ':' + seconds;
        if (minutes == 0 && seconds == 0) {
            clearInterval(interval);
            $(".smsInput").prop("disabled", true);
        }
    }, 1000);
}

$(".reSend").click(function () {
    timer(true);
    $(".smsInput").first().focus();
    $(".smsInput").css("border", "none");
    var currentURL = window.location.href;
    var url = new URL(currentURL);
    token = url.searchParams.get("token");
    $.ajax({
        url: siteURL + "user/reSendSmsRequest",
        type: "POST",
        data: {
            token: token
        },
        success: function (response) {
            //var response = JSON.parse(response);
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            if (response.status == true) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                setTimeout(function () {
                    window.location.href = response.redirectURL;
                }, 1500);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message
                });
            }
        },
        error: function (xhr) {
            console.log(xhr);
            Swal.fire({
                title: 'Hata!',
                text: 'Bir hata oluştu. Lütfen tekrar deneyiniz.',
                icon: 'error',
                confirmButtonText: 'Tamam'
            });
        }
    });
});

function getCookie(name) {
    var cookieArr = document.cookie.split(";");
    for (var i = 0; i < cookieArr.length; i++) {
        var cookiePair = cookieArr[i].split("=");
        if (name == cookiePair[0].trim()) {
            return decodeURIComponent(cookiePair[1]);
        }
    }
    return null;
}

$("#loginButton").click(function () {
    userEmailValidation();
    userPassValidation();
    if (!isValid) {
        return false;
    } else {
        var currentURL = window.location.href;
        var url = new URL(currentURL);
        var ref;
        url.searchParams.get("ref") != null ? ref = url.searchParams.get("ref") : ref = '';
        $.ajax({
            url: siteURL + "user/loginRequest",
            type: "POST",
            data: {
                email: $("input[name=userEmail]").val().trim(),
                password: $("input[name=userPass]").val().trim(),
                ref: ref
            },
            success: function (response) {
                //var response = JSON.parse(response);
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                if (response.status == true) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    setTimeout(function () {
                        window.location.href = response.redirectURL;
                    }, 500);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message
                    });
                    return false;
                }
            },
            error: function (xhr) {
                console.log(xhr);
                Swal.fire({
                    title: 'Hata!',
                    text: 'Bir hata oluştu. Lütfen tekrar deneyiniz.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            }
        });
    }
});

$("#submitButton").click(function () {

    userNameValidation();
    userEmailValidation();
    userPhoneValidation();
    userPassValidation();
    userPass2Validation();
    userSendQtyValidation();
    userPlatformValidation();
    if (!isValid) {
        return false;
    } else {
        $.ajax({
            url: siteURL + "user/registerRequest",
            type: "POST",
            data: {
                userNameAndSurname: $("input[name=userNameAndSurname]").val().trim(),
                userEmail: $("input[name=userEmail]").val().trim(),
                userPhone: $("input[name=userPhone]").val().trim(),
                userPass: $("input[name=userPass]").val().trim(),
                userPass2: $("input[name=userPass2]").val().trim(),
                userSendQty: $("select[name=userSendQty] option:selected").val(),
                userPlatform: $("select[name=userPlatform] option:selected").val()
            },
            success: function (response) {
                //var response = JSON.parse(response);
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                })
                if (response.status == true) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message,
                    });
                    window.location.href = response.redirectURL;
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message
                    });
                    return false;
                }
            },
            error: function (xhr) {
                console.log(xhr);
                Swal.fire({
                    title: 'Hata!',
                    text: 'Bir hata oluştu. Lütfen tekrar deneyiniz.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            }
        });
    }
});

//name=memberType change function
$("select[name=memberType]").change(function () {
    var memberType = $("select[name=memberType] option:selected").val();
    if (memberType == 1) {
        $(".companyDiv").addClass("d-none");
    } else {
        $(".companyDiv").removeClass("d-none");
    }
});

function getDistricts() {

    var cityID = $("select[name=city] option:selected").data("id");

    $.ajax({
        url: siteURL + "user/getDistricts",
        type: "POST",
        data: {
            city: cityID
        },
        success: function (response) {
            //var response = JSON.parse(response);
            var html = "";
            $.each(response.districts, function (key, value) {
                html += '<option value="' + value.title + '">' + value.title + '</option>';
            });
            $("select[name=district]").html(html);

        },
        error: function (xhr) {
            console.log(xhr);
            Swal.fire({
                title: 'Hata!',
                text: 'Bir hata oluştu. Lütfen tekrar deneyiniz.',
                icon: 'error',
                confirmButtonText: 'Tamam'
            });
        }
    });
}



$("input[name=userNameAndSurname]").on("input", function () {
    var val = $(this).val();
    var string = val;
    string = string.replace(/[^a-zA-ZçÇğĞıİöÖşŞüÜ\s']/g, "");
    var letters = { "i": "İ", "ş": "Ş", "ğ": "Ğ", "ü": "Ü", "ö": "Ö", "ç": "Ç", "ı": "I" };
    string = string.replace(/(([iışğüçö]))/g, function (letter) { return letters[letter]; })
    val = string.toUpperCase();
    $(this).val(val);
});

function userNameValidation() {
    var userNameAndSurname = $("input[name=userNameAndSurname]").val().trim();
    if (userNameAndSurname === '' || isNameValidation(userNameAndSurname) === false) {
        setTooltip($("input[name=userNameAndSurname]"), "Lütfen Adınızı ve Soyadınızı Giriniz.");
        return false;
    } else {
        hideTooltip($("input[name=userNameAndSurname]"));
    }
}

function userEmailValidation() {
    var userEmail = $("input[name=userEmail]").val().trim();
    if (!isValidEmail(userEmail)) {
        setTooltip($("input[name=userEmail]"), "Lütfen Geçerli Bir E-Posta Adresi Giriniz.");
        return false;
    } else {
        hideTooltip($("input[name=userEmail]"));
    }
}

function userPhoneValidation() {
    var userPhone = $("input[name=userPhone]").val().trim();
    if (!isValidPhoneNumber(userPhone)) {
        setTooltip($("input[name=userPhone]"), "Lütfen Geçerli Bir Telefon Numarası Giriniz.");
        return false;
    } else {
        hideTooltip($("input[name=userPhone]"));
    }
}

function userPassValidation() {
    var userPassInput = $("input[name=userPass]");
    var userPass = userPassInput.val().trim();

    if (userPass === '' || passwordValidate(userPass) === false) {
        setTooltip(userPassInput, "Şifreniz en az 8 karakterden oluşmalıdır ve en az bir büyük harf, bir küçük harf ve bir sembol içermelidir.");
    } else {
        hideTooltip(userPassInput);
    }
}


function userPass2Validation() {
    var userPassInput = $("input[name=userPass2]"); // userPass2 elementini seçiyoruz
    var userPass = $("input[name=userPass]").val().trim();
    var userPass2 = userPassInput.val().trim();

    if (userPass !== userPass2 || userPass2 === '' || passwordValidate(userPass2) === false) {
        setTooltip(userPassInput, "Şifreleriniz eşleşmiyor.");
    } else {
        hideTooltip(userPassInput);
    }
}

function userSendQtyValidation() {
    var userSendQty = $("select[name=userSendQty] option:selected").val();
    if (userSendQty == 'Lütfen Seçin') {
        setTooltip($("select[name=userSendQty]"), "Lütfen Gönderim Adetinizi Seçiniz.");
    } else {
        hideTooltip($("select[name=userSendQty]"));
    }
}

function userPlatformValidation() {
    var userPlatform = $("select[name=userPlatform] option:selected").val();
    if (userPlatform == 'Lütfen Seçin') {
        setTooltip($("select[name=userPlatform]"), "Lütfen Platformunuzu Seçiniz.");
    } else {
        hideTooltip($("select[name=userPlatform]"));
    }
}

function userCityValidation() {
    var userPlatform = $("select[name=city] option:selected").val();
    if (userPlatform == 'Lütfen Seçin') {
        setTooltip($("select[name=city]"), "Lütfen Şehir Seçiniz.");
    } else {
        hideTooltip($("select[name=city]"));
    }
}

function userDistrictValidation() {
    var userPlatform = $("select[name=district] option:selected").val();
    if (userPlatform == 'Lütfen Seçin') {
        setTooltip($("select[name=district]"), "Lütfen İlçe Seçiniz.");
    } else {
        hideTooltip($("select[name=district]"));
    }
}

function userTCValidation() {
    var userTC = $("input[name=tc]").val().trim();
    if (userTC === '' || tcValidation(userTC) === false) {
        setTooltip($("input[name=tc]"), "Lütfen Geçerli Bir TC Kimlik Numarası Giriniz.");
    } else {
        hideTooltip($("input[name=tc]"));
    }
}

function userDateValidation() {
    var userDate = $("input[name=date]").val().trim();
    if (userDate === '' || dateValidate(userDate) === false) {
        setTooltip($("input[name=date]"), "Lütfen Geçerli Bir Doğum Tarihi Giriniz.");
    } else {
        hideTooltip($("input[name=date]"));
    }
}

function isValidEmail(email) {
    var emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
    return emailRegex.test(email);
}

function isValidPhoneNumber(phone) {
    var regex = /^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{2}[-.\s]?\d{2}$/;
    return regex.test(phone);
}

function tcValidation(tc) {
    var tcRegex = /^[1-9]{1}[0-9]{9}[02468]{1}$/;
    return tcRegex.test(tc);
}

function dateValidate(date) {
    //date format dd/mm/yyyy
    //ay 12 yi geçemez ve 2 basamaklı olmalı
    //gün 31 i geçemez ve 2 basamaklı olmalı
    //yıl 4 basamaklı olmalı ve 2023 ten küçük olmalı
    var dateRegex = /^(0?[1-9]|[12][0-9]|3[01])[\/.](0?[1-9]|1[012])[\/.](19|20)\d\d$/;
    return dateRegex.test(date);
}

// function passwordValidate($pass) {
//     var passRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,}$/;
//     return passRegex.test($pass);
// }

function passwordValidate(password) {
    // Büyük harf, küçük harf, semboller ve en az 8 karakter içeren regex
    var passRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\-]).{8,}$/;
    return passRegex.test(password);
}

function isNameValidation(name) {
    // İsim ve soyisim için geçerli olması gereken regex
    var nameRegex = /^[a-zA-ZçÇğĞıİöÖşŞüÜ\s']+$/;

    // İsim veya soyisim regex ile kontrol et
    var isValidName = nameRegex.test(name);

    // İsimi boşluklara göre ayır ve her kelimenin uzunluğunu kontrol et
    var words = name.split(/\s+/);
    var isValidLength = words.every(function (word) {
        return word.length >= 2;
    });

    // İsim iki kelime içeriyor mu ve her kelime iki harf veya daha fazla mı?
    return isValidName && isValidLength && words.length >= 2;
}

function trUpper(text) {
    if (typeof text === 'string') {
        var upperCaseMap = {
            'a': 'A',
            'b': 'B',
            'c': 'C',
            'ç': 'Ç',
            'd': 'D',
            'e': 'E',
            'f': 'F',
            'g': 'G',
            'ğ': 'Ğ',
            'h': 'H',
            'ı': 'I',
            'i': 'İ',
            'j': 'J',
            'k': 'K',
            'l': 'L',
            'm': 'M',
            'n': 'N',
            'o': 'O',
            'ö': 'Ö',
            'p': 'P',
            'q': 'Q',
            'r': 'R',
            's': 'S',
            'ş': 'Ş',
            't': 'T',
            'u': 'U',
            'ü': 'Ü',
            'v': 'V',
            'w': 'W',
            'x': 'X',
            'y': 'Y',
            'z': 'Z'
        };

        return text.replace(/./g, function (char) {
            return upperCaseMap[char] || char;
        });
    } else {
        return text; // Eğer gelen değer bir metin değilse, olduğu gibi döndür.
    }
}


$(document).ready(function () {
    timer(true);
});

function updateCheckboxValue(checkbox) {
    if (checkbox.checked) {
        return 1;
    } else {
        return 0;
    }
}

// $('#city').change(function () {
//     var parentID = $(this).find(':selected').data('id');
//     var formData = [];
//     formData.push({
//         name: 'parentID',
//         value: parentID
//     });
//     if (parentID == 0 || parentID == '') {
//         $('#district').prop('disabled', true);
//         $('#district').empty();
//         $('#district').append('<option selected>Lütfen Şehir Seçiniz</option>');
//         return false;
//     }
//     $.ajax({
//         url: siteURL + 'api/getCities',
//         type: 'POST',
//         data: formData,
//         success: function (data) {
//             var cities = data;
//             var selectElement = $('#district');
//             selectElement.empty();
//             $.each(cities, function (index, element) {
//                 selectElement.append('<option data-id="' + element.id + '" value="' + element.title + '">' + element.title + '</option>');
//             });
//             selectElement.prop('disabled', false);
//         }
//     });
// });

$('#registerCompletionSubmit').click(function (e) {
    e.preventDefault();
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    //name=contractApproval olan checkbox  checked ise value=1 değilse value=0
    var checkbox = $("input[name=contractApproval]")[0];
    checkbox = updateCheckboxValue(checkbox);
    if (checkbox == false) {
        Toast.fire({
            icon: 'error',
            title: 'Lütfen Sözleşmeyi Onaylayınız'
        });
        return false;
    }

    var formData = [];
    var allSelectElements = $('select');
    allSelectElements.each(function (index, element) {
        var name = $(element).attr('name');
        var value = $(element).val();
        formData.push({
            name: name,
            value: value
        });
    });
    var allInputElements = $('input');
    allInputElements.each(function (index, element) {
        var name = $(element).attr('name');
        var value = $(element).val();
        formData.push({
            name: name,
            value: value
        });
    });
    var allCheckboxElements = $('input[type="checkbox"]');
    allCheckboxElements.each(function (index, element) {
        var name = $(element).attr('name');
        var value = $(element).val();
        formData.push({
            name: name,
            value: value
        });
    });



    var city = formData.find(element => element.name == 'city');
    if (city.value == 'Seçiniz' || city.value == '') {
        Toast.fire({
            icon: 'error',
            title: 'Lütfen Şehir Seçiniz'
        });
        return false;
    }

    var district = formData.find(element => element.name == 'district');
    if (district.value == 'Lütfen Şehir Seçiniz' || district.value == '') {
        Toast.fire({
            icon: 'error',
            title: 'Lütfen İlçe Seçiniz'
        });
        return false;
    }

    $.ajax({
        url: siteURL + "user/registerCompletionRequest",
        type: "POST",
        data: formData,
        success: function (response) {
            //var response = JSON.parse(response);
            if (response.status == true) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                //tüm inputları temizle
                $('input').val('');
                $('input').removeClass('is-invalid');
                $('input').removeClass('is-valid');
                //formDatayı döngüye sok ve tüm tooltip'ları kaldır 
                formData.forEach(element => {
                    hideTooltip($("input[name=" + element.name + "]"));
                });
                var city = $('#city');
                var district = $('#district');
                var selectedCity = formData.find(element => element.name == 'city');
                city.find('option[value="' + selectedCity.value + '"]').prop('selected', false);
                city.find('option[value="Seçiniz"]').prop('selected', true);
                $('#select2-city-container').text('Seçiniz');
                district.prop('disabled', true);
                district.empty();
                district.append('<option selected>Lütfen Şehir Seçiniz</option>');
                setTimeout(function () {
                    window.location.href = response.redirectURL;
                }, 1500);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message
                });
                $('input').removeClass('is-invalid');
                $('input').removeClass('is-valid');
                var invalidInputs = response.invalidFields;
                $.each(invalidInputs, function (index, element) {
                    var input = $("input[name=" + index + "]");
                    input.addClass('is-invalid');
                    setTooltip(input, element);
                });
            }
        },
        error: function (xhr) {
            console.log(xhr);
            Swal.fire({
                title: 'Hata!',
                text: 'Bir hata oluştu. Lütfen tekrar deneyiniz.',
                icon: 'error',
                confirmButtonText: 'Tamam'
            });
        }
    });
});
