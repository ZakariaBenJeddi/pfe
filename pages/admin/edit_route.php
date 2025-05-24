<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

include('../../includes/admin/controller/controller.php');

if (isset($_GET['id'])) {
  $result = get_route_by_id($dbh, $_GET['id']);
  if ($result['success']) {
    $routeSelected = $result['data'];
  } else {
    echo "<script>alert('" . $result['message'] . "');</script>";
    header("Location: routes.php");
    exit();
  }
} else {
  header("Location: routes.php");
  exit();
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

//*Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
  $result = update_route(
    $dbh,
    $_POST['route_id'],
    $_POST['route_name'],
    $_POST['start_latitude'],
    $_POST['start_longitude'],
    $_POST['end_latitude'],
    $_POST['end_longitude']
  );

  if ($result['success']) {
    header("Location: routes.php?success=1");
    exit();
  } else {
    $errorMessage = urlencode($result['message']);
    header("Location: routes.php?error={$errorMessage}");
    exit();
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
        <div class="col-md-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Modifier Route</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Modification de Route</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="route_id" class="form-control-label">ID Route</label>
                      <input class="form-control" type="text" readonly name="route_id" id="route_id" value="<?= $routeSelected->route_id ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="route_name" class="form-control-label">Nom de la Route</label>
                      <input class="form-control" type="text" name="route_name" id="route_name" value="<?= htmlspecialchars($routeSelected->route_name) ?>" required>
                    </div>
                  </div>
                </div>
                
                <p class="text-uppercase text-sm mt-4">Coordonnées de Départ</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="start_latitude" class="form-control-label">Latitude de Départ</label>
                      <input class="form-control" type="number" step="0.00000001" name="start_latitude" id="start_latitude" value="<?= $routeSelected->start_latitude ?>" min="-90" max="90" required>
                      <small class="text-muted">Valeur entre -90 et 90</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="start_longitude" class="form-control-label">Longitude de Départ</label>
                      <input class="form-control" type="number" step="0.00000001" name="start_longitude" id="start_longitude" value="<?= $routeSelected->start_longitude ?>" min="-180" max="180" required>
                      <small class="text-muted">Valeur entre -180 et 180</small>
                    </div>
                  </div>
                </div>

                <p class="text-uppercase text-sm mt-4">Coordonnées d'Arrivée</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="end_latitude" class="form-control-label">Latitude d'Arrivée</label>
                      <input class="form-control" type="number" step="0.00000001" name="end_latitude" id="end_latitude" value="<?= $routeSelected->end_latitude ?>" min="-90" max="90" required>
                      <small class="text-muted">Valeur entre -90 et 90</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="end_longitude" class="form-control-label">Longitude d'Arrivée</label>
                      <input class="form-control" type="number" step="0.00000001" name="end_longitude" id="end_longitude" value="<?= $routeSelected->end_longitude ?>" min="-180" max="180" required>
                      <small class="text-muted">Valeur entre -180 et 180</small>
                    </div>
                  </div>
                </div>

                <div class="row mt-4">
                  <div class="col-md-12">
                    <input class="btn btn-primary" type="submit" value="Modifier" name="edit">
                    <a href="routes.php" class="btn btn-secondary ms-2">Annuler</a>
                  </div>
                </div>
              </div>
            </form>
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
    // Validation côté client pour les coordonnées
    document.addEventListener('DOMContentLoaded', function() {
      const latitudeInputs = document.querySelectorAll('input[name$="_latitude"]');
      const longitudeInputs = document.querySelectorAll('input[name$="_longitude"]');
      
      latitudeInputs.forEach(input => {
        input.addEventListener('input', function() {
          const value = parseFloat(this.value);
          if (value < -90 || value > 90) {
            this.setCustomValidity('La latitude doit être comprise entre -90 et 90');
          } else {
            this.setCustomValidity('');
          }
        });
      });
      
      longitudeInputs.forEach(input => {
        input.addEventListener('input', function() {
          const value = parseFloat(this.value);
          if (value < -180 || value > 180) {
            this.setCustomValidity('La longitude doit être comprise entre -180 et 180');
          } else {
            this.setCustomValidity('');
          }
        });
      });
    });
  </script>

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