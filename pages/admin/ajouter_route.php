<?php
session_start();
if (empty($_SESSION['user'])) {
  header('../location:sign-in.php');
}
include('../../includes/admin/controller/controller.php');

//* Gestion de l'inactivité
require('../../includes/deconnexion_5s.php');

//* Add
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
  $result = add_route(
    $dbh,
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
                <p class="mb-0">Ajouter Route</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Information Route</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="route_name" class="form-label">Nom de la Route</label>
                      <input type="text" name="route_name" id="route_name" class="form-control" placeholder="Ex: Route Principale A" required>
                    </div>
                  </div>
                </div>

                <p class="text-uppercase text-sm mt-4">Coordonnées de Départ</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="start_latitude" class="form-label">Latitude de Départ</label>
                      <input type="number" step="0.00000001" name="start_latitude" id="start_latitude" class="form-control" placeholder="Ex: 48.8566" min="-90" max="90" required>
                      <small class="text-muted">Valeur entre -90 et 90</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="start_longitude" class="form-label">Longitude de Départ</label>
                      <input type="number" step="0.00000001" name="start_longitude" id="start_longitude" class="form-control" placeholder="Ex: 2.3522" min="-180" max="180" required>
                      <small class="text-muted">Valeur entre -180 et 180</small>
                    </div>
                  </div>
                </div>

                <p class="text-uppercase text-sm mt-4">Coordonnées d'Arrivée</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="end_latitude" class="form-label">Latitude d'Arrivée</label>
                      <input type="number" step="0.00000001" name="end_latitude" id="end_latitude" class="form-control" placeholder="Ex: 45.7640" min="-90" max="90" required>
                      <small class="text-muted">Valeur entre -90 et 90</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="end_longitude" class="form-label">Longitude d'Arrivée</label>
                      <input type="number" step="0.00000001" name="end_longitude" id="end_longitude" class="form-control" placeholder="Ex: 4.8357" min="-180" max="180" required>
                      <small class="text-muted">Valeur entre -180 et 180</small>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <button type="button" class="btn btn-info btn-sm" onclick="getLocation()">
                        <i class="fas fa-map-marker-alt"></i> Obtenir ma localisation
                      </button>
                      <small class="form-text text-muted">
                        Cliquez pour remplir automatiquement les coordonnées GPS
                      </small>
                    </div>
                  </div>
                </div>

                <div class="row mt-4">
                  <div class="col-md-12">
                    <input class="btn btn-primary" type="submit" value="Ajouter Route" name="ajouter">
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
    function getLocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function(position) {
            document.getElementById('start_latitude').value = position.coords.latitude.toFixed(6);
            document.getElementById('start_longitude').value = position.coords.longitude.toFixed(6);
            document.getElementById('end_latitude').value = position.coords.latitude.toFixed(6);
            document.getElementById('end_longitude').value = position.coords.longitude.toFixed(6);
            alert('Coordonnées GPS obtenues avec succès !');
          },
          function(error) {
            switch (error.code) {
              case error.PERMISSION_DENIED:
                alert("L'accès à la géolocalisation a été refusé.");
                break;
              case error.POSITION_UNAVAILABLE:
                alert("Les informations de localisation ne sont pas disponibles.");
                break;
              case error.TIMEOUT:
                alert("La demande de géolocalisation a expiré.");
                break;
              default:
                alert("Une erreur inconnue s'est produite.");
                break;
            }
          }
        );
      } else {
        alert("La géolocalisation n'est pas supportée par ce navigateur.");
      }
    }
  </script>

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