<?php
include('../../includes/admin/controller/controller.php');
session_start();

if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['start_date']) && isset($_POST['end_date'])) {
  header('Content-Type: application/json');
  try {
    // Validation des dates
    if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
      throw new Exception("Les dates sont requises");
    }

    // Nettoyage et validation des dates
    $start_date = filter_var($_POST['start_date'], FILTER_SANITIZE_STRING);
    $end_date = filter_var($_POST['end_date'], FILTER_SANITIZE_STRING);

    if (!$start_date || !$end_date) {
      throw new Exception("Format de date invalide");
    }

    // Conversion des dates au format MySQL
    $start_date = date("Y-m-d", strtotime($start_date));
    $end_date = date("Y-m-d", strtotime($end_date));

    // Requête SQL avec préparation
    $sql = "SELECT matiere.* , filiere.nom_filiere FROM matiere JOIN filiere ON  filiere.id_filiere = matiere.id_filiere
              WHERE annee_creation BETWEEN :start_date AND :end_date
              ORDER BY annee_creation DESC";

    $stmt = $dbh->prepare($sql);
    $stmt->execute([
      ':start_date' => $start_date,
      ':end_date' => $end_date
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_OBJ);

    echo json_encode([
      'status' => 'success',
      'data' => $results,
      'count' => count($results)
    ]);
  } catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);
  }
  exit;
}

// if ($_SERVER["REQUEST_METHOD"] === "POST") {
//   header('Content-Type: application/json');
//   $result = get_matiere_by_date_range(
//       $dbh,
//       $_POST['start_date'] ?? null,
//       $_POST['end_date'] ?? null
//   );
//   http_response_code($result['code']);
//   unset($result['code']);
//   echo json_encode($result);
//   exit;
// }

//* Read
try {
  $results = get_all_matieres($dbh);
} catch (Exception $e) {
  echo "<script>alert('" . htmlspecialchars($e->getMessage()) . "');</script>";
  $results = [];
}

try {
  $filieres = get_filieres_with_niveaux($dbh);
} catch (Exception $e) {
  echo "<script>alert('" . htmlspecialchars($e->getMessage()) . "');</script>";
  $filieres = [];
}

//* Delete
if (!empty($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
  $result = delete_matiere($dbh, $_GET['id']);

  echo "<script>alert('" . htmlspecialchars($result['message']) . "');</script>";

  if ($result['success'] && $result['redirect']) {
    header("Location: " . $result['redirect_url']);
    exit;
  }
}

if (isset($_POST['save'])) {
  try {
      // Récupérer et nettoyer les données du formulaire
      $nom_matiere = filter_var($_POST['nom_filiere'], FILTER_SANITIZE_STRING); // Note: le champ s'appelle nom_filiere dans le formulaire
      $code_matiere = filter_var($_POST['code_filiere'], FILTER_SANITIZE_STRING); // Note: le champ s'appelle code_filiere dans le formulaire
      $id_filiere = filter_var($_POST['filiere'], FILTER_SANITIZE_NUMBER_INT);
      $statut = filter_var($_POST['statut'], FILTER_SANITIZE_STRING);
      $coefficient = filter_var($_POST['coeficient'], FILTER_SANITIZE_NUMBER_INT); // Note: le champ s'appelle nombre_heures_max dans le formulaire
      $nombre_seance_semaine = filter_var($_POST['nombre_seance'], FILTER_SANITIZE_NUMBER_INT); // Même problème de nommage
      $nombre_heures_semaine = filter_var($_POST['nombre_heures_max'], FILTER_SANITIZE_NUMBER_INT); // Même problème de nommage
      $volume_horaire = filter_var($_POST['volume_horaire'], FILTER_SANITIZE_NUMBER_INT); // Même problème de nommage
      $type_matiere = filter_var($_POST['type_matiere'], FILTER_SANITIZE_STRING);
      $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
      
      // Validation des données (vérification que les champs requis sont présents)
      if (!$nom_matiere || !$code_matiere || !$id_filiere || !$coefficient) {
          throw new Exception("Tous les champs obligatoires doivent être remplis.");
      }
      
      // Insérer la matière dans la base de données
      $sql = "INSERT INTO matiere (
          id_filiere, 
          nom_matiere, 
          code_matiere, 
          coefficient, 
          description, 
          statut, 
          volume_horaire, 
          nombre_heures_semaine, 
          nombre_seance_semaine, 
          type_matiere, 
          annee_creation
      ) VALUES (
          :id_filiere, 
          :nom_matiere, 
          :code_matiere, 
          :coefficient, 
          :description, 
          :statut, 
          :volume_horaire, 
          :nombre_heures_semaine, 
          :nombre_seance_semaine, 
          :type_matiere, 
          CURDATE()
      )";
      
      $stmt = $dbh->prepare($sql);
      $stmt->execute([
          ':id_filiere' => $id_filiere,
          ':nom_matiere' => $nom_matiere,
          ':code_matiere' => $code_matiere,
          ':coefficient' => $coefficient,
          ':description' => $description,
          ':statut' => $statut,
          ':volume_horaire' => $volume_horaire,
          ':nombre_heures_semaine' => $nombre_heures_semaine,
          ':nombre_seance_semaine' => $nombre_seance_semaine,
          ':type_matiere' => $type_matiere
      ]);
      
      // Redirection ou message de succès
      echo "<script>alert('Matière ajoutée avec succès!'); window.location.href='matiere.php';</script>";
      
  } catch (Exception $e) {
      echo "<script>alert('Erreur: " . htmlspecialchars($e->getMessage()) . "');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>

<body class="g-sidenav-show  bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php require('../../includes/admin/aside_admin.php') ?>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <?php require('../../includes/admin/navbar_admin.php') ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0 d-flex flex-wrap justify-content-between align-items-center text-center text-md-start">
              <div class="mb-2 mb-md-0 flex-grow-1 text-center text-md-start">
                <h6 class="text-primary">Filière</h6>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
                <!-- <a class="btn btn-primary btn-sm" href="ajouter_matiere.php">Ajouter Matiere</a> -->
                <a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#MatiereModal">Ajouter Matiere</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
              </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nom Matiere</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">code Matiere</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Filiere</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">coeficient</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre sceance semaine </th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre heure semaine </th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                    </tr>
                  </thead>
                  <tbody id="tableBody">
                    <?php if (count($results) > 0) { ?>
                      <?php foreach ($results as $result) : ?>
                        <tr>
                          <td>
                            <div class="d-flex px-2 py-1">
                              <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm"><?= $result->nom_matiere ?></h6>
                              </div>
                            </div>
                          </td>
                          <td class="align-middle text-center text-sm">
                            <p class="text-xs font-weight-bold mb-0"><?= $result->code_matiere; ?></p>
                          </td>
                          <td class="align-middle text-center text-sm">
                            <p class="text-xs font-weight-bold mb-0"><?= $result->nom_filiere; ?></p>
                          </td>
                          <td class="align-middle text-center text-sm">
                            <p class="text-xs font-weight-bold mb-0"><?= intval($result->coefficient); ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->statut; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->nombre_seance_semaine; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->nombre_heures_semaine; ?></p>
                          </td>
                          <td class="align-middle text-center">
                            <p class="text-xs font-weight-bold mb-0" title="<?= $result->description ?>">
                              <?= substr($result->description, 0, 20) . (strlen($result->description) > 20 ? '...' : ''); ?>
                            </p>
                          </td>
                          <td class="align-middle text-center">
                            <div class="d-flex">
                              <a href="edit_matiere.php?id=<?= $result->id_matiere ?>" class="dropdown-item">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <a href="description_matiere.php?id=<?= $result->id_matiere ?>" class="dropdown-item">
                                <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                              </a>
                              <a href="matiere.php?id=<?= $result->id_matiere ?>&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                                <i class="fas fa-trash fa-sm text-danger opacity-8"></i>
                            </div>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php } else { ?>
                      <tr rowspan="7" class="text-center">
                        <td class="text-center">
                          No Content
                        </td>
                      </tr>
                    <?php  } ?>
                  </tbody>
                </table>
              </div>
              <?php require_once("form/matiere_modal.php") ?>
            </div>
          </div>
        </div>
      </div>
      <!-- FOOTER -->
      <?php include '../../includes/footer.php' ?>

    </div>
  </main>

  <!-- Data table -->
  <script src="../../assets/js/datatable.js"></script>
  <!-- Export Functio -->
  <script src="../../assets/js/export.js"></script>

  <!-- //* Date Picker -->
  <!-- //* AJAX matiere intervalle date  -->
  <script src="../../assets/dateP_dateP/dateP_dataP_matiere.js"></script>

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