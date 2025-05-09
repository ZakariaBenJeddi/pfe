<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

include('../../includes/admin/controller/controller.php');

if (isset($_GET['id'])) {
  try {
    $result = get_filiere_by_id($dbh, $_GET['id']);
    if ($result['success']) {
      $filiere = $result['data'];
    } else {
      echo htmlspecialchars($result['message']);
      exit();
    }
  } catch (Exception $e) {
    error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
    echo "Une erreur est survenue. Veuillez réessayer plus tard.";
    exit();
  }
} else {
  echo "ID de Filière non fourni.";
  exit();
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* Niveau For Filiere
$AllNiveau = get_all_niveau($dbh);

//* Edit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
  try {
    $result = update_filiere($dbh, $_POST);
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
        <div class="col-md-8">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Ajouter Ensaignant</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Modification de Filière</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id_filiere" class="form-control-label">ID Filière</label>
                      <input class="form-control" type="text" readonly name="id_filiere" id="id_filiere" value="<?= $filiere->id_filiere ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_filiere" class="form-control-label">Nom de la Filière</label>
                      <input class="form-control" type="text" name="nom_filiere" id="nom_filiere" value="<?= $filiere->nom_filiere ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="abriviation_filiere" class="form-control-label">Abréviation Filière</label>
                      <input class="form-control" type="text" name="abriviation_filiere" id="abriviation_filiere" required value="<?= $filiere->abriviation_filiere ?>">
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="code_filiere" class="form-control-label">Code Filière</label>
                      <input class="form-control" type="text" name="code_filiere" id="code_filiere" required value="<?= $filiere->code_filiere ?>">
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="description_filiere" class="form-control-label">Description</label>
                      <textarea class="form-control" name="description_filiere" id="description_filiere" required><?= $filiere->description ?></textarea>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="niveau" class="form-control-label">Niveau</label>
                      <select name="niveau" class="form-select" required>
                        <?php foreach ($AllNiveau as $value) : ?>
                          <option value="<?= $value->id_niveau ?>" <?= ($value->nom_niveau === $filiere->nom_niveau) ? 'selected' : '' ?>>
                            <?= $value->nom_niveau ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nombre_heures_max" class="form-control-label">Nombre d'heures max</label>
                      <input class="form-control" type="number" name="nombre_heures_max" id="nombre_heures_max" required value="<?= $filiere->nombre_heures_max ?>">
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_creation" class="form-control-label">Date de Création</label>
                      <input class="form-control" type="date" name="date_creation" id="date_creation" required value="<?= date('Y-m-d', strtotime($filiere->date_creation)) ?>">
                    </div>
                  </div>
                </div>

                <div class="row">
                  <input class="btn btn-primary" type="submit" value="Modifier" name="edit">
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
    // Récupérer l'élément input
    const dateInput = document.getElementById('date_aujourdhui');

    // Obtenir la date d'aujourd'hui
    const today = new Date();
    const formattedDate = today.toISOString().split('T')[0]; // Format YYYY-MM-DD

    // Définir la date par défaut
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