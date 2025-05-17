<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

include('../../includes/admin/controller/controller.php');

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* Get salle by id
if (isset($_GET['id_salle'])) {
  $result = get_class_by_id($dbh, $_GET['id_salle']);
  if (!$result['success']) {
    die($result['message']);
  }
  $salle = $result['data'];
}

//* Edit
if (isset($_POST['modifier'])) {
  $result = update_class($dbh, $_POST);
  if ($result['success']) {
    header("Location: salle.php?success=1");
    exit();
  } else {
    $errorMessage = urlencode($result['message']);
    header("Location:salle.php?error={$errorMessage}");
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php require('../../includes/admin/aside_admin.php') ?>
  <main class="main-content position-relative border-radius-lg ">
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
                <p class="mb-0">Modifier Salle</p>

              </div>
            </div>
            <hr class="horizontal dark">
            <form method="POST">
              <div class="card-body">
                <p class="text-uppercase text-sm">Salle Information</p>
                <div class="row">
                  <input type="hidden" readonly name="id_salle" value="<?= $salle['id_salle'] ?>">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_salle" class="form-control-label">Nom Salle</label>
                      <input class="form-control" type="text" value="<?= htmlspecialchars($salle['nom_salle']) ?>" name="nom_salle" id="nom_salle" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="equipements" class="form-control-label">Équipement</label>
                      <input class="form-control" type="text" value="<?= htmlspecialchars($salle['equipements']) ?>" name="equipements" id="equipements" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="etage" class="form-control-label">Étage</label>
                      <select class="form-select" name="etage" id="etage">
                        <option value="0" <?= $salle['etage'] == 0 ? 'selected' : '' ?>>Rez de chaussée</option>
                        <option value="1" <?= $salle['etage'] == 1 ? 'selected' : '' ?>>Étage 1</option>
                        <option value="2" <?= $salle['etage'] == 2 ? 'selected' : '' ?>>Étage 2</option>
                        <option value="3" <?= $salle['etage'] == 3 ? 'selected' : '' ?>>Étage 3</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="capacite" class="form-control-label">Capacité Élève</label>
                      <input class="form-control" type="number" value="<?= htmlspecialchars($salle['capacite_salle']) ?>" name="capacite_salle" min="10" max="30" id="capacite" required>
                    </div>
                  </div>
                </div>
                <!-- Ajoutez d'autres champs ici -->
                <div class="row">
                  <input class="btn btn-primary" type="submit" value="Modifier" name="modifier">
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