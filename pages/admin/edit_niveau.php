<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

include('../../includes/admin/controller/controller.php');

if (isset($_GET['id'])) {
  $result = get_niveau_by_id($dbh, $_GET['id']);
  if ($result['success']) {
    $niveauSelected = $result['data'];
  } else {
    echo "<script>alert('" . $result['message'] . "');</script>";
    exit();
  }
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

// Récupérer les valeurs ENUM
$sqlEnum = "SELECT COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'niveau' AND COLUMN_NAME = 'statut'";
$stmtEnum = $dbh->query($sqlEnum);
$enumResult = $stmtEnum->fetch(PDO::FETCH_ASSOC);

// Extraire les valeurs de l'ENUM
$enumValues = [];
if ($enumResult) {
  preg_match("/^enum\('(.*)'\)$/", $enumResult['COLUMN_TYPE'], $matches);
  if (isset($matches[1])) {
    $enumValues = explode("','", $matches[1]);
  }
}

// Récupérer le niveau actuel (exemple)
$id = $_GET['id'] ?? null;
$niveauStatut = null;

if ($id) {
  $sqlNiveau = "SELECT statut FROM niveau WHERE id_niveau = :id";
  $stmtNiveau = $dbh->prepare($sqlNiveau);
  $stmtNiveau->execute([':id' => $id]);
  $niveau = $stmtNiveau->fetch(PDO::FETCH_OBJ);
  $niveauStatut = $niveau ? $niveau->statut : null;
}

//*Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
  $result = update_niveau(
    $dbh,
    $_POST['id_niveau'],
    $_POST['nom_niveau'],
    $_POST['description_niveau'],
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
                <p class="mb-0">Ajouter Ensaignant</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Modification de Niveau</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id_niveau" class="form-control-label">ID Niveau</label>
                      <input class="form-control" type="text" readonly name="id_niveau" id="id_niveau" value="<?= $niveauSelected->id_niveau  ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_niveau" class="form-control-label">Nom du Niveau</label>
                      <input class="form-control" type="text" name="nom_niveau" id="nom_niveau" value="<?= $niveauSelected->nom_niveau ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="description_niveau" class="form-control-label">Description</label>
                      <textarea rows="5" cols="33" class="form-control" name="description_niveau" id="description_niveau" required><?= $niveauSelected->description ?></textarea>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="statut" class="form-control-label">Statut</label>
                      <select name="statut" class="form-select" required>
                        <?php foreach ($enumValues as $value) : ?>
                          <option value="<?= $value ?>" <?= ($value === $niveauStatut) ? 'selected' : '' ?>>
                            <?= $value ?>
                          </option>
                        <?php endforeach; ?>
                      </select>

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