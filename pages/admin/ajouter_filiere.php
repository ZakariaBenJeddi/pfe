<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

include('../../includes/admin/controller/controller.php');

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* Get All Niveau
try {
  $AllNiveau = get_all_niveau($dbh);
} catch (PDOException $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  die("Erreur lors de la récupération des données.");
}

//* Add
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
  try {
    $result = insert_filiere($dbh, $_POST);

    if ($result['success']) {
      header("Location: filiere.php?success=1");
      exit();
    } else {
      $errorMessage = urlencode($result['message']);
      header("Location: filiere.php?error={$errorMessage}");
      exit();
    }
  } catch (Exception $e) {
    error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
    echo "<script>
          alert('Une erreur est survenue. Veuillez réessayer plus tard.');
      </script>";
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
                <p class="mb-0">Ajouter Filière hhh</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Information Filière</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_filiere" class="form-control-label">Nom Filière</label>
                      <input class="form-control" type="text" name="nom_filiere" id="nom_filiere" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="abriviation_filiere" class="form-control-label">Abréviation Filière</label>
                      <input class="form-control" type="text" name="abriviation_filiere" id="abriviation_filiere" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="code_filiere" class="form-control-label">Code Filière</label>
                      <input class="form-control" type="text" name="code_filiere" id="code_filiere" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="description" class="form-control-label">Description</label>
                      <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="niveau" class="form-control-label">Niveau</label>
                      <select name="niveau" class="form-select" required>
                        <?php foreach ($AllNiveau as $value) : ?>
                          <option value="<?= $value->id_niveau ?>"><?= $value->nom_niveau ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nombre_heures_max" class="form-control-label">Nombre d'heures max</label>
                      <input class="form-control" type="number" min='0' name="nombre_heures_max" id="nombre_heures_max" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_creation" class="form-control-label">Date de Création</label>
                      <input class="form-control" type="date" name="date_creation" id="date_aujourdhui" required>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <input class="btn btn-primary" type="submit" value="Ajouter" name="ajouter">
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
    const dateInput = document.getElementById('date_aujourdhui');
    const today = new Date();
    const formattedDate = today.toISOString().split('T')[0]; // Format YYYY-MM-DD
    dateInput.value = formattedDate;
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