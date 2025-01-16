<?php
include_once '../includes/DatabaseConnexion.php';
session_start();

if (empty($_SESSION['user'])) {
  header('location:sign-in.php');
}

$_SESSION['last_activity'] = time();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 300)) {
  session_unset();
  session_destroy();
  header("location:logout.php");
  exit;
}

$sql = "SELECT ecm.* , e.nom_enseignant ,e.prenom_enseignant , m.nom_matiere ,m.code_matiere ,c.nom_classe
        FROM enseignant_classes_matieres ecm
        JOIN enseignant e ON e.id_enseignant = ecm.enseignant_id
        JOIN matiere m ON m.id_matiere = ecm.matiere_id
        JOIN classe c ON c.id_classe = ecm.classe_id
        ORDER BY e.nom_enseignant ASC";
$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['action'])) {
  header('Content-Type: application/json');
  switch ($_GET['action']) {
      // PHP section corrections
    case 'get_groupes':
      if (isset($_GET['professeur_id'])) {
        $query = "SELECT id_classe, nom_classe FROM classe";
        $stmt = $dbh->prepare($query);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      }
      exit;

    case 'get_matieres':
      if (isset($_GET['classe_id'])) {
        $classe_id = intval($_GET['classe_id']);
        $query = "SELECT DISTINCT m.id_matiere as id, 
              CONCAT(m.nom_matiere, ' (', m.code_matiere, ')') as nom 
              FROM matiere m 
              WHERE m.id_filiere IN (
                SELECT filiere_id FROM classe WHERE id_classe = ?
                UNION 
                SELECT 8
              )
              ORDER BY m.nom_matiere";
        $stmt = $dbh->prepare($query);
        $stmt->execute([$classe_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      }
      exit;
  }
}


function ajouterAffectation($dbh, $enseignant_id, $classe_id, $matiere_id)
{
  // 1. Vérifier si la même affectation existe déjà
  $check1 = $dbh->prepare("SELECT id FROM enseignant_classes_matieres 
                        WHERE enseignant_id = ? AND classe_id = ? AND matiere_id = ?");
  $check1->execute([$enseignant_id, $classe_id, $matiere_id]);
  if ($check1->rowCount() > 0) {
    $_SESSION['error'] = "Cette affectation existe déjà!";
    return;
  }

  // 2. Vérifier si la matière est déjà affectée à cette classe
  $check2 = $dbh->prepare("SELECT ecm.id, CONCAT(e.nom_enseignant, ' ', e.prenom_enseignant) as nom_complet 
                        FROM enseignant_classes_matieres ecm
                        JOIN enseignant e ON e.id_enseignant = ecm.enseignant_id 
                        WHERE classe_id = ? AND matiere_id = ?");
  $check2->execute([$classe_id, $matiere_id]);
  if ($check2->rowCount() > 0) {
    $result = $check2->fetch(PDO::FETCH_ASSOC);
    $_SESSION['error'] = "Cette matière est déjà affectée à cette classe par l'enseignant " . $result['nom_complet'];
    return;
  }

  // 3. Vérifier si l'enseignant est déjà affecté à cette classe
  $check3 = $dbh->prepare("SELECT ecm.id, m.nom_matiere 
                        FROM enseignant_classes_matieres ecm
                        JOIN matiere m ON m.id_matiere = ecm.matiere_id 
                        WHERE enseignant_id = ? AND classe_id = ?");
  $check3->execute([$enseignant_id, $classe_id]);
  if ($check3->rowCount() > 0) {
    $result = $check3->fetch(PDO::FETCH_ASSOC);
    $_SESSION['error'] = "Cet enseignant est déjà affecté à cette classe pour la matière " . $result['nom_matiere'];
    return;
  }

  // Si toutes les vérifications sont passées, insérer
  try {
    $query = "INSERT INTO enseignant_classes_matieres (enseignant_id, classe_id, matiere_id) 
              VALUES (?, ?, ?)";
    $stmt = $dbh->prepare($query);
    $stmt->execute([$enseignant_id, $classe_id, $matiere_id]);
    $_SESSION['success'] = "Affectation ajoutée avec succès!";
  } catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de l'affectation : " . $e->getMessage();
  }
}

// Traitement du formulaire
if (isset($_POST['valid'])) {
  $enseignant_id = filter_var($_POST['professeur_id'], FILTER_VALIDATE_INT);
  $classe_id = filter_var($_POST['groupe_id'], FILTER_VALIDATE_INT);
  $matiere_id = filter_var($_POST['matiere_id'], FILTER_VALIDATE_INT);

  ajouterAffectation($dbh, $enseignant_id, $classe_id, $matiere_id);
  header('Location: TimeTableInfo.php');
  exit;
}

//* Suppression 
if (isset($_GET['id']) && isset($_GET['del'])) {
  try {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $query = $dbh->prepare("DELETE FROM enseignant_classes_matieres WHERE id = ?");

    if ($query->execute([$id])) {
      $_SESSION['success'] = "Affectation supprimée avec succès";
    } else {
      $_SESSION['error'] = "Erreur lors de la suppression";
    }
  } catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression";
  }
  header('Location: TimeTableInfo.php');
  exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<!-- HEAD -->
<?php include '../includes/head.php' ?>


<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <!-- aside -->
  <?php require('../includes/aside_admin.php') ?>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <?php require('../includes/navbar_admin.php') ?>
    <div class="container">
      <?php if (isset($_SESSION['success'])) : ?>
        <div class="alert alert-success py-1"><?= $_SESSION['success'];
                                              unset($_SESSION['success']); ?></div>
      <?php endif; ?>
      <?php if (isset($_SESSION['error'])) : ?>
        <div class="alert alert-danger py-1"><?= $_SESSION['error'];
                                              unset($_SESSION['error']); ?></div>
      <?php endif; ?>
    </div>
    <div class=" pb-0 mt-5 me-5 text-end text-primary">
      <a href="TimeTableConfig.php" class="btn btn-light px-3">configurer donnes</a>
    </div>
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0 text-center">
              <h4>Affecter Enseignant & Matiere & groupe</h4>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <form method="POST" class="p-4">
                <!-- Professeurs Select -->
                <label for="professeur_id">Sélectionner un Formatuer :</label>
                <select class="form-control mb-3" id="professeur_id" name="professeur_id" require>
                  <option value="">Choisir un Formatuer</option>
                  <?php
                  $query = "SELECT id_enseignant, nom_enseignant , prenom_enseignant FROM enseignant ORDER BY nom_enseignant";
                  $stmt = $dbh->query($query);
                  while ($prof = $stmt->fetch()) {
                    echo "<option value='" . htmlspecialchars($prof['id_enseignant']) . "'>" .
                      htmlspecialchars($prof['nom_enseignant'] . ' ' . $prof['prenom_enseignant']) . "</option>";
                  }
                  ?>
                </select>

                <!-- Groupes Select -->
                <label for="groupe_id">Sélectionner un groupe :</label>
                <select class="form-control mb-3" id="groupe_id" name="groupe_id" disabled require>
                  <option value="">Choisir un groupe</option>
                </select>

                <!-- Matières Select -->
                <label for="matiere_id">Sélectionner un Matiere :</label>
                <select class="form-control mb-3" id="matiere_id" name="matiere_id" disabled require>
                  <option value="">Choisir un Matiere</option>
                </select>
                <input type="submit" name="valid" class="btn btn-primary w-100 py-3 px-5" value="Valider">
                <a href="TimeTableInsertIntoCalendar.php" class="btn btn-success w-100 py-3 px-5 mt-3">Generer</a>
              </form>
            </div>
          </div>

        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0 d-flex flex-wrap justify-content-between align-items-center text-center text-md-start">
              <div class="mb-2 mb-md-0 flex-grow-1 text-center text-md-start">
                <h4 class="text-primary">Affectations</h4>
              </div>
              <div class="">
                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
              </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0 px-4">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Enseignant</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">groupe</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nom Matiere</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cod Matiere</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acion</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($results as $result) { ?>
                      <tr>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div>
                              <i class="ni ni-single-02 text-primary opacity-10"></i>
                            </div>
                            <div class="d-flex flex-column justify-content-center ms-3">
                              <h6 class="mb-0 text-sm"><?= $result['nom_enseignant'] . " " . $result['prenom_enseignant']  ?></h6>
                            </div>
                          </div>
                        </td>
                        <td>
                          <p class="text-xs font-weight-bold mb-0"><?= $result['nom_classe'] ?></p>
                        </td>
                        <td class="align-middle text-center text-sm">
                          <p class="text-xs font-weight-bold mb-0"><?= $result['nom_matiere'] ?></p>
                        </td>
                        <td class="align-middle text-center text-sm">
                          <p class="text-xs font-weight-bold mb-0"><?= $result['code_matiere'] ?></p>
                        </td>
                        <td class="align-middle text-center text-sm">
                          <div class="d-flex justify-content-center align-items-center">
                            <a href="TimeTableInfo.php?id=<?= $result['id'] ?>&del=1" onClick="return confirm('Etes-vous sûr que vous voulez supprimer Affectation de?\nFotmateur(trice): <?= addslashes($result['nom_enseignant'] . " " . $result['prenom_enseignant']) ?>\nClasse: <?= addslashes($result['nom_classe']) ?>\nMatiere : <?= addslashes($result['nom_matiere']) ?>')">
                              <i class="fas fa-trash fa-sm text-danger opacity-8"></i>
                            </a>
                          </div>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- FOOTER -->
      <?php include '../includes/footer.php' ?>

    </div>

    <!-- FOOTER -->
    <?php include '../includes/footer.php' ?>
  </main>

  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>

  <!-- exporter -->
  <script src="../assets/js/export.js"></script>

  <!-- formateur groupe modules ajax et datatable -->
  <script>
    $(document).ready(function() {
      $('table:first').DataTable(); // Initialiser DataTable pour la première table
    });
    document.getElementById('professeur_id').addEventListener('change', function() {
      const profId = this.value;
      const groupeSelect = document.getElementById('groupe_id');
      const matiereSelect = document.getElementById('matiere_id');

      if (profId) {
        groupeSelect.disabled = false;
        fetch(`?action=get_groupes&professeur_id=${profId}`)
          .then(response => response.json())
          .then(data => {
            groupeSelect.innerHTML = '<option value="">Choisir un groupe</option>';
            data.forEach(groupe => {
              groupeSelect.innerHTML += `<option value="${groupe.id_classe}">${groupe.nom_classe}</option>`;
            });
          })
          .catch(error => console.error('Erreur:', error));
      } else {
        groupeSelect.disabled = true;
        matiereSelect.disabled = true;
        groupeSelect.innerHTML = '<option value="">Choisir un groupe</option>';
        matiereSelect.innerHTML = '<option value="">Choisir une matière</option>';
      }
    });

    // JavaScript corrections
    document.getElementById('groupe_id').addEventListener('change', function() {
      const groupeId = this.value;
      const matiereSelect = document.getElementById('matiere_id');

      if (groupeId) {
        matiereSelect.disabled = false;
        fetch(`?action=get_matieres&classe_id=${groupeId}`) // Changed from groupe_id to classe_id
          .then(response => response.json())
          .then(data => {
            matiereSelect.innerHTML = '<option value="">Choisir une matière</option>';
            data.forEach(matiere => {
              matiereSelect.innerHTML += `<option value="${matiere.id}">${matiere.nom}</option>`;
            });
          })
          .catch(error => console.error('Erreur:', error));
      }
    });

    document.getElementById('assignmentForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      formData.append('action', 'save');

      fetch('', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            this.reset();
            location.reload();
          } else {
            alert('Erreur : ' + data.message);
          }
        })
        .catch(error => console.error('Erreur:', error));
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
  <script src="../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>