<!DOCTYPE html>
<html lang="tr">

<head>
    <title>Dijipin Top-Up Panel</title>
    <meta charset="utf-8" />
    <meta name="description" content="Dijipin Top-Up Panel" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" /> <!--end::Fonts-->
    <link href="<?= topup_url() ?>assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= topup_url() ?>assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
</head>

<body id="kt_body" class="app-blank">
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }

            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }

            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
                <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                    <div class="w-lg-500px p-10">
                        <form class="form w-100" method="POST" novalidate="novalidate" id="kt_sign_in_form" action="<?= base_url('loginRequest') ?>">
                            <div class="text-center mb-11">
                                <h1 class="text-gray-900 fw-bolder mb-3">
                                    Giriş Yap
                                </h1>
                            </div>
                            <div class="fv-row mb-8">
                                <input type="text" placeholder="Email" name="email" autocomplete="off" class="form-control bg-transparent" />
                            </div>
                            <div class="fv-row mb-3">
                                <input type="password" placeholder="Password" name="password" autocomplete="off" class="form-control bg-transparent" />
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">
                                    Giriş</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script src="<?= topup_url() ?>assets/plugins/global/plugins.bundle.js"></script>
        <script src="<?= topup_url() ?>assets/js/scripts.bundle.js"></script>
        <script src="<?= topup_url() ?>assets/js/custom/authentication/sign-in/general.js"></script>
</body>

</html>