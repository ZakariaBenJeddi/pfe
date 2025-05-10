<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

include('../../includes/admin/controller/controller.php');
//* deconnexion
require('../../includes/deconnexion_5s.php');

//* CREATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
  // Fusionner $_POST et $_FILES pour la fonction ajouterAbsence
  $donnees_absence = $_POST;
  
  // Débogage - Vérifier les données reçues
  error_log("Données d'absence reçues: " . print_r($donnees_absence, true));
  
  $resultat = ajouterAbsence($dbh, $donnees_absence);
  
  // Débogage - Vérifier le résultat
  error_log("Résultat de l'ajout: " . print_r($resultat, true));

  if ($resultat['success']) {
    header("Location: absence.php?success=" . urlencode($resultat['message']));
    exit();
  } else {
    $errorMessage = urlencode($resultat['message']); // Correction: $resultat au lieu de $result
    header("Location: absence.php?error={$errorMessage}");
    exit();
  }
}

// classe
try {
  $result = get_all_classes($dbh);
  if ($result['success']) {
    $classe = $result['data'];
  } else {
    echo "Erreur : " . $result['message'];
  }
} catch (PDOException $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  die("Erreur lors de la récupération des données.");
}


function getElevesByClasse($dbh, $classe_id)
{
  try {
    $sql = "SELECT id_eleve, nom, prenom FROM eleves 
              WHERE id_classe = :classe_id 
              ORDER BY nom, prenom";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':classe_id', $classe_id, PDO::PARAM_INT);
    $stmt->execute();

    // Assurez-vous que fetchAll renvoie un tableau indexé et non un tableau associatif
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);

    return $results; // Retourner simplement le tableau d'objets
  } catch (PDOException $e) {
    error_log($e->getMessage());
    return [];
  }
}

if (isset($_GET['action']) && $_GET['action'] === 'getEleves' && isset($_GET['classe_id'])) {
  $eleves = getElevesByClasse($dbh, intval($_GET['classe_id']));

  // Afficher les données pour le débogage
  error_log('Elèves trouvés: ' . print_r($eleves, true));

  echo json_encode([
    'success' => true,
    'data' => $eleves, // Un tableau simple d'objets
    'count' => count($eleves)
  ]);
  exit;
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
                  <label for="classe_box">Choisir La Classe</label>
                  <select name="classe_id" id="classe_box" class="form-select">
                    <option value="">Sélectionnez une classe</option>
                    <?php foreach ($classe as $c) : ?>
                      <option value="<?= htmlspecialchars($c->id_classe) ?>">
                        <?= htmlspecialchars($c->nom_classe) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="my-4">
                  <label for="eleve_box">Choisir L'élève</label>
                  <select name="eleve_id" id="eleve_box" class="form-select">
                    <option value="">Sélectionnez d'abord une classe</option>
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
            <form method="post" enctype="multipart/form-data">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id" class="form-control-label">Nom & Prenom</label>
                      <input class="form-control" type="text" name="nom_prenom" id="nom_prenom" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id" class="form-control-label">id eleves</label>
                      <input class="form-control" type="text" name="id_elevesX" id="id_elevesX" required readonly>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="date_absence" class="form-control-label">Date Absence</label>
                      <input class="form-control" type="date" value="<?php echo date('Y-m-d'); ?>" name="date_absence" id="date_absence" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="heure_debut" class="form-control-label">Heure Debut</label>
                      <input class="form-control" type="time" value="<?php echo date('H:i'); ?>" name="heure_debut" id="heure_debut" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="heure_fin" class="form-control-label">Heure Fin</label>
                      <input class="form-control" type="time" value="<?php echo date('H:i', strtotime('+1 hour')); ?>" name="heure_fin" id="heure_fin" required>
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
                        <option value="">Choisir Le Type</option>
                        <option value="excusee">excusee</option>
                        <option value="non_excusee">non_excusee</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="statut" class="form-control-label">Statut</label>
                      <select class="form-select" name="statut" id="statut" required>
                        <option value="">Choisir Le Statut</option>
                        <option value="en_attente">en_attente</option>
                        <option value="validee">validee</option>
                        <option value="annulee">annulee</option>
                      </select>
                    </div>
                  </div>

                  <!-- Après le select pour le statut, ajoutez ceci -->
                  <div class="col-md-12" id="justification_container" style="display: none;">
                    <div class="form-group">
                      <label for="justification" class="form-control-label">Justification (PDF, JPG, PNG)</label>
                      <input class="form-control" type="file" name="justification" id="justification">
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

      <!-- script affichage eleve seulon la classe seletionne -->
      <script src="../../assets/js/absence_eleves.js"></script>
      <!-- FOOTER -->
      <?php include '../../includes/footer.php' ?>

    </div>
  </div>
  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>

  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const typeAbsenceSelect = document.getElementById('type_absence');
      const justificationContainer = document.getElementById('justification_container');

      // Vérifier l'état initial
      if (typeAbsenceSelect.value === 'excusee') {
        justificationContainer.style.display = 'block';
      }

      // Ajouter un écouteur d'événement
      typeAbsenceSelect.addEventListener('change', function() {
        if (this.value === 'excusee') {
          justificationContainer.style.display = 'block';
        } else {
          justificationContainer.style.display = 'none';
        }
      });
    });
  </script>

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