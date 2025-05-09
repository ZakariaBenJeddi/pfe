<?php
session_start();
if (empty($_SESSION['user'])) {
  header('../location:sign-in.php');
}
include('../../includes/admin/controller/controller.php');

//* Gestion de l'inactivité
require('../../includes/deconnexion_5s.php');

// update
if (isset($_GET['id_classe'])) {
  $id_classe = intval($_GET['id_classe']);
  $stmt = $dbh->prepare("SELECT * FROM classe WHERE id_classe = :id_classe");
  $stmt->bindParam(':id_classe', $id_classe, PDO::PARAM_INT);
  $stmt->execute();
  $classeSelected = $stmt->fetch(PDO::FETCH_OBJ);

  if (!$classeSelected) {
    die("Classe non trouvée");
  }
} else {
  die("ID de classe manquant");
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_classe'])) {
  $result = update_classe(
    $dbh,
    $_POST['id_classe'],
    $_POST['nom_classe'],
    $_POST['id_filiere'],
    $_POST['id_niveau'],
    $_POST['annee_scolaire'],
    $_POST['capacite'],
    $_POST['statut'],
    $_POST['date_creation'],
    $_POST['nom_responsable']
  );

  if ($result['success']) {
    header("Location: classes.php?success=1");
    exit();
  } else {
    $errorMessage = urlencode($result['message']);
    header("Location: classes.php?error={$errorMessage}");
    exit();
  }
}


// Récupération des filières
$filieresById = get_filieres_with_niveaux($dbh);
$filieres = $filieresById['data'];

// Récupération des niveaux
$niveaux = get_all_niveau($dbh);

?>

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
                <p class="mb-0">Modifier Classe</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Modification de Classe</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id_classe" class="form-control-label">ID Classe</label>
                      <input class="form-control" type="text" readonly name="id_classe" value="<?= $classeSelected->id_classe ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_classe" class="form-control-label">Nom de la Classe</label>
                      <input class="form-control" type="text" name="nom_classe" value="<?= $classeSelected->nom_classe ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id_filiere" class="form-control-label">Filière</label>
                      <select class="form-select" name="id_filiere" required>
                        <?php foreach ($filieres as $filiere) : ?>
                          <option value="<?= $filiere->id_filiere ?>" <?= ($classeSelected->filiere_id == $filiere->id_filiere) ? 'selected' : '' ?>>
                            <?= $filiere->nom_filiere ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id_niveau" class="form-control-label">Niveau</label>
                      <select class="form-select" name="id_niveau" required>
                        <?php foreach ($niveaux as $niveau) : ?>
                          <option value="<?= $niveau->id_niveau ?>" <?= ($classeSelected->niveau_id == $niveau->id_niveau) ? 'selected' : '' ?>>
                            <?= $niveau->nom_niveau ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="annee_scolaire" class="form-control-label">Année Scolaire</label>
                      <input class="form-control" type="text" name="annee_scolaire" value="<?= $classeSelected->annee_scolaire ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="capacite" class="form-control-label">Capacité</label>
                      <input class="form-control" type="number" name="capacite" value="<?= $classeSelected->capacite ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="statut" class="form-control-label">Statut</label>
                      <select class="form-select" name="statut" required>
                        <option value="active" <?= ($classeSelected->statut == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($classeSelected->statut == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_creation" class="form-control-label">Date de création</label>
                      <input class="form-control" type="date" name="date_creation" value="<?= $classeSelected->date_creation ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_responsable" class="form-control-label">Nom du Responsable</label>
                      <input class="form-control" type="text" name="nom_responsable" value="<?= $classeSelected->nom_responsable ?>" required>
                    </div>
                  </div>

                </div>

                <div class="row">
                  <input class="btn btn-primary" type="submit" value="Modifier" name="edit_classe">
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