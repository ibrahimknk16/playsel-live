<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">

<head>
  <meta name="robots" content="noindex">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <title>Playsel Admin Paneli</title>
  <link rel="shortcut icon" href="https://qq.playsel.com/assets-front/images/icons/favicon.ico">
  <link href="<?= topup_url('assets/css/style.bundle.css') ?>" rel="stylesheet" type="text/css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="<?= topup_url('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') ?>" rel="stylesheet" type="text/css" />
  <link href="<?= topup_url('assets/plugins/custom/datatables/datatables.bundle.css') ?>" rel="stylesheet" type="text/css" />
  <link href="<?= topup_url('assets/plugins/global/plugins.bundle.css') ?>" rel="stylesheet" type="text/css" />
  <link href="<?= topup_url('assets/css/general.css?v=' . time()) ?>" rel="stylesheet" type="text/css" />
  <link href="<?= topup_url('assets/css/admin-shell.css?v=' . time()) ?>" rel="stylesheet" type="text/css" />
  <script src="<?= topup_url('assets/plugins/global/plugins.bundle.js') ?>"></script>
  <script>
    var baseURL = '<?= topup_url() ?>';
  </script>
</head>

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed">
  <div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
      <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
        <?= $this->template->widget("header", array()); ?>
        <div class="d-flex flex-column-fluid">
          <?= $this->template->widget("sidebar", array()); ?>
          <div class="d-flex flex-column flex-column-fluid container-fluid admin-page-content">
            <main class="admin-page-main">
              <?= $this->template->content; ?>
            </main>
            <?= $this->template->widget("footer", array()); ?>
          </div>
        </div>
      </div>
    </div>
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
      <i class="ki-duotone ki-arrow-up">
        <span class="path1"></span>
        <span class="path2"></span>
      </i>
    </div>


    <script src="<?= topup_url('assets/js/scripts.bundle.js?v=1.0.1') ?>"></script>
    <script src="<?= topup_url('assets/plugins/custom/datatables/datatables.bundle.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="<?= topup_url('assets/js/general.js?v=' . rand(0, 999999)) ?>"></script>
</body>

</html>

<?php if (!empty($this->session->flashdata('alert')['message'])) : ?>
  <script>
    toastr.<?= $this->session->flashdata('alert')['type'] ?>("<?= $this->session->flashdata('alert')['message'] ?>", "<?= $this->session->flashdata('alert')['title'] ?>");
  </script>
<?php endif; ?>

<script>
  //dom content loaded alert hello world
  document.addEventListener("DOMContentLoaded", function() {
    $("#kt_datatable_dom_positioning").DataTable({
      "language": {
        "lengthMenu": "Show _MENU_",
      },
      "dom": "<'row mb-2'" +
        "<'col-sm-6 d-flex align-items-center justify-conten-start dt-toolbar'l>" +
        "<'col-sm-6 d-flex align-items-center justify-content-end dt-toolbar'f>" +
        ">" +

        "<'table-responsive'tr>" +

        "<'row'" +
        "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
        "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
        ">"
    });
  });
</script>
<script>
  <?php if (!empty($this->session->flashdata('alert'))) : ?>
    toastr.<?= $this->session->flashdata('alert')['status'] ?>("<?= $this->session->flashdata('alert')['message'] ?>");
  <?php endif; ?>
</script>