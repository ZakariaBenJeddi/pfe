<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:sign-in.php');
}

require '../includes/DatabaseConnexion.php';

//* deconnexion
$inactivity_limit = 300; // 5 minutes
if (isset($_SESSION['last_action'])) {
  $inactivity_duration = time() - $_SESSION['last_action'];
  if ($inactivity_duration > $inactivity_limit) {
    session_unset();
    session_destroy();
    header("Location: logout.php");
    exit();
  }
}
$_SESSION['last_action'] = time();


$sqlNiveau = "SELECT * FROM niveau";
$stmt = $dbh->query($sqlNiveau);
$AllNiveau = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
  // Récupérer les données du formulaire
  $nom_filiere = $_POST['nom_filiere'];
  $abriviation_filiere = $_POST['abriviation_filiere'];
  $code_filiere = $_POST['code_filiere'];
  $description = $_POST['description'];
  $niveau = $_POST['niveau'];
  $nombre_heures_max = $_POST['nombre_heures_max'];
  $date_creation = $_POST['date_creation'] . ' ' . date('H:i:s');


  // Préparer la requête d'insertion
  $sql = "INSERT INTO filiere (
      nom_filiere,id_niveau, abriviation_filiere, code_filiere, description, 
      nombre_heures_max, date_creation
  ) VALUES (
      :nom_filiere, :id_niveau ,:abriviation_filiere, :code_filiere, :description, 
      :nombre_heures_max, :date_creation
  )";

  try {
    // Préparer la requête et exécuter avec les valeurs
    $stmt = $dbh->prepare($sql);
    $stmt->execute([
      ':nom_filiere' => $nom_filiere,
      ':id_niveau' => $niveau,
      ':abriviation_filiere' => $abriviation_filiere,
      ':code_filiere' => $code_filiere,
      ':description' => $description,
      ':nombre_heures_max' => $nombre_heures_max,
      ':date_creation' => $date_creation,
    ]);

    echo "<script>
              alert('Filière ajoutée avec succès.');
              window.location.href = 'filiere.php';
          </script>";
  } catch (PDOException $e) {
    echo "Erreur lors de l'ajout de la filière : " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../includes/head.php' ?>

<body class="g-sidenav-show bg-gray-100">
  <div class="position-absolute w-100 min-height-300 top-0" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
    <span class="mask bg-primary opacity-6"></span>
  </div>
  <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href=" https://demos.creative-tim.com/argon-dashboard/pages/dashboard.html " target="_blank">
        <img src="https://elaraki.ac.ma/images/logo2.png" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold">
          <?= strtoupper($_SESSION['nom_admin'] . " " . $_SESSION['prenom_admin'])  ?>
        </span>

      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main">
      <?php include('../includes/navbar_admin.php'); ?>
    </div>
    <div class="sidenav-footer mx-3 ">
      <div class="card card-plain shadow-none" id="sidenavCard">
        <img class="w-50 mx-auto mt-5" src="https://elaraki.ac.ma/images/logo2.png" alt="sidebar_illustration">
        <div class="card-body text-center p-3 w-100 pt-0">
          <div class="docs-info">
            <h6 class="mb-0">ELARAKI School</h6>
            <p class="text-xs font-weight-bold mb-0">International School of Morocco</p>
          </div>
        </div>
      </div>
    </div>
  </aside>
  <div class="main-content position-relative max-height-vh-100 h-100">
    <!-- Navbar -->
    <!-- //TODO navbar deleted -->
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
                          <option value="<?= $value['id_niveau'] ?>"><?= $value['nom_niveau'] ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nombre_heures_max" class="form-control-label">Nombre d'heures max</label>
                      <input class="form-control" type="number" name="nombre_heures_max" id="nombre_heures_max" required>
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
        <div class="col-md-4">
          <div class="card card-profile">
            <img src="../assets/img/bg-profile.jpg" alt="Image placeholder" class="card-img-top">
            <div class="row justify-content-center">
              <div class="col-4 col-lg-4 order-lg-2">
                <div class="mt-n4 mt-lg-n6 mb-4 mb-lg-0">
                  <a href="javascript:;">
                    <img src="../assets/img/team-2.jpg" class="rounded-circle img-fluid border border-2 border-white">
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
      <?php include '../includes/footer.php' ?>

    </div>
  </div>
  <!-- FIXED PLUGIN  -->
  <?php include '../includes/fixedplugin.php' ?>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>

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
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>