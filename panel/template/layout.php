<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="/assets/img/favicon.png">
  <title>
    <?=$title?>
  </title>
  <!-- Fonts and icons -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- CSS Files -->
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

  <link id="pagestyle" href="/assets/css/material-dashboard.css?v=3.0.0" rel="stylesheet" />
  
  <link href="/assets/css/jquery-jvectormap-2.0.5.css" rel="stylesheet" />
  <link href="//cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css" rel="stylesheet" />
  <link href="/assets/css/custom.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js" crossorigin="anonymous"></script>
  <script src="//cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>

  <script src="https://cdn.jsdelivr.net/npm/axios@0.12.0/dist/axios.min.js"></script>
  <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  
  <script src="/assets/js/plugins/selectize/js/standalone/selectize.js"></script>
  <link rel="stylesheet" type="text/css" href="/assets/js/plugins/selectize/css/selectize.css"/>
  <link rel="stylesheet" type="text/css" href="/assets/js/plugins/selectize/css/selectize.bootstrap5.css"/>

  <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
</head>

<body class="g-sidenav-show bg-gray-200 overflow-hidden">
  <?php
    if ($color_settings["dark_version"] == 1) {
      echo "<div class='preloader preloader-dark'> <img src='/assets/img/preloader.svg' /> </div>";
    } else {
      echo "<div class='preloader bg-white'> <img src='/assets/img/preloader.svg' /> </div>";
    }
  ?>

  <?php
    require_once("sidebar.php");
  ?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <?php
      require_once("header.php");
    ?>
    <div class="container-fluid py-4">
      <?php
        require_once($pagefile);
      ?>

      <?php
        require_once("footer.php");
      ?>
    </div>
  </main>

  <?php
    require_once("color-settings.php");
  ?>

  
  <!--   Core JS Files   -->
  <script src="/assets/js/core/popper.min.js"></script>
  <script src="/assets/js/core/bootstrap.min.js"></script>

  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="/assets/js/material-dashboard.js?v=3.0.0"></script>

  <?php 
    if ($color_settings["dark_version"] == 1) echo "<script> setDarkMode(); </script>";

    if ($color_settings["menu_color"] == 0) echo "<script> sidebarType('gradient-dark') </script>";
    if ($color_settings["menu_color"] == 1) echo "<script> sidebarType('transparent') </script>";
    if ($color_settings["menu_color"] == 2) echo "<script> sidebarType('white') </script>";

    if ($color_settings["active_color"] == 0) echo "<script> sidebarColor('primary') </script>";
    if ($color_settings["active_color"] == 1) echo "<script> sidebarColor('dark') </script>";
    if ($color_settings["active_color"] == 2) echo "<script> sidebarColor('info') </script>";
    if ($color_settings["active_color"] == 3) echo "<script> sidebarColor('success') </script>";
    if ($color_settings["active_color"] == 4) echo "<script> sidebarColor('warning') </script>";
    if ($color_settings["active_color"] == 5) echo "<script> sidebarColor('danger') </script>";
  ?>

  <script>
    setTimeout(function() {
      $("body").removeClass("overflow-hidden");
      $(".preloader").fadeOut();
    }, 1500)
  </script>

  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</body>

</html>