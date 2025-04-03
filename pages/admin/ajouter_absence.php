<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

// require '../../includes/DatabaseConnexion.php';
include('../../includes/admin/controller/controller.php');
//* deconnexion
// require('../../includes/deconnexion_5s.php');

//* CREATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
  $resultat = ajouterEleve($dbh, $_POST);

  if ($resultat['success']) {
    echo "<script>
          alert('Élève ajouté avec succès.');
          window.location.href = 'eleves.php';
      </script>";
  } else {
    echo "<script>
          alert('Erreur: " . addslashes($resultat['message']) . "');
      </script>";
  }
}

// niveau
try {
  $niveaux = get_all_niveau($dbh);
} catch (PDOException $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  die("Erreur lors de la récupération des données.");
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
        <div class="col-md-4">
          <div class="card card-profile">
            <div class="text-center">
              <img src="../../assets/img/school/eleve/download.png" alt="Image placeholder" class="card-img-top" style="width: 50%; height: auto; object-fit: cover;">
            </div>
            <div class="card-body pt-0 mb-5">
              <div class="row">
                <div class="text-center mt-3">
                  <h3>Chercher Eleve</h3>
                </div>
              </div>
              <div class="mt-4">
                <div class="my-4">
                  <label for="class_box">Choisir La Classe</label>
                  <select name="" id="classe_box" class="form-select" >
                    <option value="">Sélectionner un élève</option>
                    <option value="">Sélectionner un élève</option>
                  </select>
                </div>
                <div class="my-4">
                  <label for="class_box">Choisir La Classe</label>
                  <select name="" id="class_box" class="form-select">
                    <option value="">Sélectionner un élève</option>
                    <option value="">Sélectionner un élève</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Ajouter Absence</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Eleve Information</p>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="date_absence" class="form-control-label">Date Absence</label>
                      <input class="form-control" type="date" name="date_absence" id="date_absence" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="heure_debut" class="form-control-label">Heure Debut</label>
                      <input class="form-control" type="time" name="heure_debut" id="heure_debut" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="heure_fin" class="form-control-label">Heure Fin</label>
                      <input class="form-control" type="time" name="heure_fin" id="heure_fin" required>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="genre_eleve" class="form-control-label">motif</label>
                      <textarea class="form-control" name="motif" id="motif" rows="4" cols="14"></textarea>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="type_absence" class="form-control-label">Type Absence</label>
                      <select class="form-select" name="type_absence" id="type_absence" required>
                        <option value="excusee">excusee</option>
                        <option value="non_excusee">non_excusee</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="statut" class="form-control-label">Statut</label>
                      <select class="form-select" name="statut" id="statut" required>
                        <option value="en_attente">en_attente</option>
                        <option value="validee">validee</option>
                        <option value="annulee">annulee</option>
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
  <!-- FILIRE ET CLASSE SELON LE NIVEAU -->
  <script src="../../assets/js/niveau_filiere_classe.js"></script>
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