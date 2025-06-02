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
$result = add_niveau(
    $dbh,
    $_POST['nom_niveau'],
    $_POST['description'],
    $_POST['statut']
);
if ($result['success']) {
    header("Location: niveau.php?success=1");
    exit();
} else {
  $errorMessage = urlencode($result['message']);
  header("Location: niveau.php?error={$errorMessage}");
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
              <p class="mb-0">Ajouter niveau</p>
            </div>
          </div>
          <hr class="horizontal dark">
          <form method="post">
            <div class="card-body">
              <p class="text-uppercase text-sm">Information Niveau</p>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="nom_niveau" class="form-label">Nom Niveau</label>
                    <input type="text" name="nom_niveau" id="nom_niveau" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-control"></textarea>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select" required>
                      <option value="Active">Active</option>
                      <option value="Inactive">Inactive</option>
                    </select>
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