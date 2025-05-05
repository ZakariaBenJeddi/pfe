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


//* Read
try {
  $results = get_all_matieres($dbh);
} catch (Exception $e) {
  echo "<script>alert('" . htmlspecialchars($e->getMessage()) . "');</script>";
  $results = [];
}

//* Delete
if (!empty($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
  $result = delete_matiere($dbh, $_GET['id']);

  if ($result['success'] && $result['redirect']) {
    header("Location: " . $result['redirect_url'] . "?success=1");
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
    // echo "<script>alert('Matière ajoutée avec succès!'); window.location.href='matiere.php';</script>";

    header("Location: matiere.php?success=1");
    exit();
  } catch (Exception $e) {
    echo "<script>alert('Erreur: " . htmlspecialchars($e->getMessage()) . "');</script>";
  }
}

if (isset($_GET['id']) && !isset($_GET['del'])) {
  $editMode = true;
  $id_matiere = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

  try {
    $stmt = $dbh->prepare("SELECT * FROM matiere WHERE id_matiere = :id_matiere");
    $stmt->execute([':id_matiere' => $id_matiere]);
    $matiereData = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$matiereData) {
      echo "<script>alert('Cette matière n\'existe pas.');</script>";
      header("Location: matiere.php");
      exit();
    }
  } catch (PDOException $e) {
    echo "<script>alert('Erreur lors de la récupération des données: " . htmlspecialchars($e->getMessage()) . "');</script>";
  }
}

// Récupérer les filières pour le formulaire
try {
  $filieres = get_filieres_with_niveaux($dbh);
} catch (Exception $e) {
  echo "<script>alert('" . htmlspecialchars($e->getMessage()) . "');</script>";
  $filieres = ['data' => []];
}

$editMode = false;
$matiereData = null;

// Traitement du formulaire de mise à jour
if (isset($_POST['update'])) {
  try {
    // Récupérer et nettoyer les données du formulaire
    $id_matiere = filter_var($_POST['id_matiere'], FILTER_SANITIZE_NUMBER_INT);
    $nom_matiere = filter_var($_POST['nom_filiere'], FILTER_SANITIZE_STRING);
    $code_matiere = filter_var($_POST['code_filiere'], FILTER_SANITIZE_STRING);
    $id_filiere = filter_var($_POST['filiere'], FILTER_SANITIZE_NUMBER_INT);
    $statut = filter_var($_POST['statut'], FILTER_SANITIZE_STRING);
    $coefficient = filter_var($_POST['coeficient'], FILTER_SANITIZE_NUMBER_INT);
    $nombre_seance_semaine = filter_var($_POST['nombre_seance'], FILTER_SANITIZE_NUMBER_INT);
    $nombre_heures_semaine = filter_var($_POST['nombre_heures_max'], FILTER_SANITIZE_NUMBER_INT);
    $volume_horaire = filter_var($_POST['volume_horaire'], FILTER_SANITIZE_NUMBER_INT);
    $type_matiere = filter_var($_POST['type_matiere'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

    // Validation des données
    if (!$nom_matiere || !$code_matiere || !$id_filiere || !$coefficient) {
      throw new Exception("Tous les champs obligatoires doivent être remplis.");
    }

    // Appel de la fonction de mise à jour
    $result = updateMatiere(
      $dbh,
      $id_matiere,
      $id_filiere,
      $nom_matiere,
      $code_matiere,
      $coefficient,
      $description,
      $statut,
      $volume_horaire,
      $nombre_heures_semaine,
      $nombre_seance_semaine,
      $type_matiere
    );

    // Traitement du résultat
    if ($result['success']) {
      header("Location: matiere.php?success=1");
      exit();
    } else {
      throw new Exception($result['message']);
    }
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
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type Matiere</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre sceance semaine </th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre heure semaine </th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Volume Horaire</th>
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
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->type_matiere; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->nombre_seance_semaine; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->nombre_heures_semaine; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->volume_horaire; ?> h</p>
                          </td>
                          <td class="align-middle text-center">
                            <p class="text-xs font-weight-bold mb-0" title="<?= $result->description ?>">
                              <?= substr($result->description, 0, 20) . (strlen($result->description) > 20 ? '...' : ''); ?>
                            </p>
                          </td>
                          <td class="align-middle text-center">
                            <div class="d-flex">
                              <a href="matiere.php?id=<?= $result->id_matiere ?>" class="dropdown-item edit-btn">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <a href="matiere.php?id=<?= $result->id_matiere ?>&del=1" onClick="return confirmDelete(event, this)" class="dropdown-item">
                                <i class="fas fa-trash fa-sm text-danger opacity-8"></i>
                              </a>
                            </div>
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


  <script>
    // Script pour gérer l'ouverture directe du modal lors du clic sur l'icône d'édition
    document.addEventListener('DOMContentLoaded', function() {
      // Ajouter un écouteur d'événements pour les boutons d'édition dans le tableau
      document.querySelectorAll('.edit-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
          e.preventDefault(); // Empêcher la navigation par défaut

          // Récupérer l'ID de la matière depuis l'URL
          var href = this.getAttribute('href');
          var id = href.split('=')[1];

          // Appel AJAX pour récupérer les données de la matière
          fetch('get_matiere_data.php?id=' + id)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Remplir le formulaire avec les données
                document.getElementById('id_matiere').value = data.matiere.id_matiere;
                document.getElementById('nom_filiere').value = data.matiere.nom_matiere;
                document.getElementById('code_filiere').value = data.matiere.code_matiere;
                document.getElementById('filiere').value = data.matiere.id_filiere;
                document.getElementById('statut').value = data.matiere.statut;
                document.getElementById('coeficient').value = data.matiere.coefficient;
                document.getElementById('nombre_seance').value = data.matiere.nombre_seance_semaine;
                document.getElementById('nombre_heures_max').value = data.matiere.nombre_heures_semaine;
                document.getElementById('volume_horaire').value = data.matiere.volume_horaire;
                document.getElementById('type_matiere').value = data.matiere.type_matiere;
                document.getElementById('description').value = data.matiere.description;

                // Changer le bouton de soumission pour la mise à jour
                var submitBtn = document.getElementById('submitBtn');
                submitBtn.name = 'update';
                submitBtn.textContent = 'Mettre à jour';

                // Changer le titre du modal
                document.getElementById('paiementModalLabel').textContent = 'Modifier la Matière';

                // Ouvrir le modal
                var matiereModal = new bootstrap.Modal(document.getElementById('MatiereModal'));
                matiereModal.show();
              } else {
                alert('Erreur: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Erreur:', error);
              alert('Une erreur est survenue lors de la récupération des données.');
            });
        });
      });
    });
  </script>


  <!-- Data table -->
  <script src="../../assets/js/datatable.js"></script>
  <!-- Export Functio -->
  <script src="../../assets/js/export.js"></script>

  <!-- sweet alert -->
  <script src="../../assets/js/alerts/sweet_alert.js"></script>

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