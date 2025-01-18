<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../../sign-in.php');
}

include('../../includes/admin/controller/controller.php');

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* Add
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
  $result = ajouter_salle($dbh, $_POST);
  if (!$result['success']) {
    echo "<script>alert('" . htmlspecialchars($result['message']) . "');</script>";
  }
  if ($result['redirect']) {
    header("Location: " . $result['redirect_url']);
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>

<body class="g-sidenav-show bg-gray-100">
  <div class="position-absolute w-100 min-height-300 top-0" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
    <span class="mask bg-primary opacity-6"></span>
  </div>
  <?php require('../../includes/admin/aside_admin.php') ?>
  <div class="main-content position-relative max-height-vh-100 h-100">
    <!-- Navbar -->
    <?php require('../../includes/admin/navbar_admin.php') ?>
    <!-- End Navbar -->
    <div class="card shadow-lg mx-4 card-profile-bottom">

    </div>
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Ajouter Salle</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Salle Information</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Nom Salle</label>
                      <input class="form-control" type="text" value="" name="nom_salle" id="nom_salle" onchange="nom_salleChange()">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Equipement</label>
                      <input class="form-control" type="text" value="" name="equipement" id="equipement" onchange="equipementChange()">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label" id="etage">Etage</label>
                      <select class="form-select" name="etage" id="etage" onchange="etageChange()">
                        <option value="0">Rez de chaussée</option>
                        <option value="1">Etage 1</option>
                        <option value="2">Etage 2</option>
                        <option value="3">Etage 3</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Capacite Eleve</label>
                      <input class="form-control" type="number" value="" name="capacite" min="10" max="30" id="capacite" onchange="capaciteChange()">
                    </div>
                  </div>
                </div>
                <hr class="horizontal dark">
                <p class="text-uppercase text-sm">Nombre Equipement </p>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Nombre Chaise</label>
                      <input class="form-control" type="number" min="10" max="15" name="nombre_chaise" id="chaise" onchange="chaiseChange()">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Nombre Bureau</label>
                      <input class="form-control" type="number" min="1" max="2" name="nombre_bureau" id="bureau" onchange="bureauChange()">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Nombre Tableau</label>
                      <input class="form-control" type="number" min="1" max="2" name="nombre_tableau" id="tableau" onchange="tableauChange()">
                    </div>
                  </div>
                </div>
                <hr class="horizontal dark">
                <div class="row">
                  <input class="btn btn-primary" type="submit" value="Ajouter" name="ajouter">
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-profile">
            <img src="../../assets/img/bg-profile.jpg" alt="Image placeholder" class="card-img-top">
            <div class="row justify-content-center">
              <div class="col-4 col-lg-4 order-lg-2">
                <div class="mt-n4 mt-lg-n6 mb-4 mb-lg-0">
                  <a href="javascript:;">
                    <img src="../../assets/img/team-2.jpg" class="rounded-circle img-fluid border border-2 border-white">
                  </a>
                </div>
              </div>
            </div>
            <div class="card-body pt-0 mb-5">
              <div class="row">
                <div class="col">
                  <div class="d-flex justify-content-center">
                    <div class="d-grid text-center">
                      <span class="text-lg font-weight-bolder" id="chaise_value"></span>
                      <span class="text-sm opacity-8">Chaise </span>
                    </div>
                    <div class="d-grid text-center mx-4">
                      <span class="text-lg font-weight-bolder" id="bureau_value"></span>
                      <span class="text-sm opacity-8">Bureau </span>
                    </div>
                    <div class="d-grid text-center">
                      <span class="text-lg font-weight-bolder" id="tableau_value"></span>
                      <span class="text-sm opacity-8">Tableau</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="text-center mt-4">
                <h5>
                  Nom Salle :<span class="font-weight-light" id="nom_salle_value"></span>
                </h5>
                <div class="h6 font-weight-300">
                  <i class="ni location_pin mr-2"></i>Etage : <span class="font-weight-light" id="etage_value"></span>
                </div>
                <div class="h6 font-weight-300">
                  <i class="ni location_pin mr-2"></i>
                  Equipement : <span class="font-weight-light" id="equipement_value"></span>
                </div>
                <div class="h6 font-weight-300">
                  <i class="ni location_pin mr-2"></i>
                  Capacite Eleve : <span class="font-weight-light" id="capacite_value"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- FOOTER -->
      <?php include '../../includes/footer.php' ?>

    </div>
  </div>
  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>
  <!--   Core JS Files   -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>