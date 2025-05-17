<?php
session_start();

if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}
include('../../includes/admin/controller/controller.php');

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* FILTER ABSENCES
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['daterange'])) {
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

    // Ajout des heures pour couvrir la journée entière
    $start_date = $start_date . " 00:00:00";
    $end_date = $end_date . " 23:59:59";

    // Requête SQL avec préparation
    $sql = "SELECT a.*, 
            e.nom AS nom_eleve, 
            e.prenom AS prenom_eleve, 
            e.telephone_tuteur, 
            c.nom_classe,
            en.nom_enseignant AS nom_enseignant, 
            en.prenom_enseignant AS prenom_enseignant
    FROM absences a
    LEFT JOIN eleves e ON a.id_eleve = e.id_eleve
    LEFT JOIN classe c ON e.id_classe = c.id_classe
    LEFT JOIN enseignant en ON a.id_enseignant = en.id_enseignant
    WHERE a.date_creation BETWEEN :start_date AND :end_date
    ORDER BY a.date_creation DESC";

    $stmt = $dbh->prepare($sql);
    $stmt->execute([
      ':start_date' => $start_date,
      ':end_date' => $end_date
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_OBJ);

    echo json_encode([
      'success' => true,  // Assurez-vous que c'est "success" et pas "status"
      'data' => $results,
      'count' => count($results)
    ]);
  } catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
      'success' => false,
      'message' => $e->getMessage()
    ]);
  }
  exit;
}

// Récupération des filtres
$filter_enseignant = isset($_GET['enseignant']) ? (int)$_GET['enseignant'] : null;
$filter_matiere = isset($_GET['matiere']) ? (int)$_GET['matiere'] : null;
$filter_classe = isset($_GET['classe']) ? (int)$_GET['classe'] : null;
$filter_statut = isset($_GET['statut']) ? $_GET['statut'] : null;
$filter_type_evaluation = isset($_GET['type']) ? $_GET['type'] : null;

// Pagination
$evaluations_par_page = 12; // 3 lignes de 4 cartes
$page_actuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;

//* read 
$eseignants = get_all_enseignant($dbh);
$classes = get_all_classes($dbh)['data'];
$matieres = get_all_matieres($dbh);

// Récupération des évaluations paginées et filtrées
$evaluationsInfo = getEvaluationsFiltered(
  $dbh,
  $page_actuelle,
  $evaluations_par_page,
  $filter_enseignant,
  $filter_matiere,
  $filter_classe,
  $filter_statut,
  $filter_type_evaluation
);

// Récupérer les types d'évaluation disponibles
function get_all_types_evaluation($dbh)
{
  try {
    $stmt = $dbh->query("SELECT DISTINCT type_evaluation FROM evaluation");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  } catch (PDOException $e) {
    return [];
  }
}

// Récupérer les statuts disponibles
function get_all_statuts($dbh)
{
  try {
    $stmt = $dbh->query("SELECT DISTINCT statut FROM evaluation");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  } catch (PDOException $e) {
    return [];
  }
}

$types_evaluation = get_all_types_evaluation($dbh);
$statuts = get_all_statuts($dbh);

if ($evaluationsInfo['success']) {
  $total_evaluations = $evaluationsInfo['count'];
  $total_pages = ceil($total_evaluations / $evaluations_par_page);
  $results = $evaluationsInfo['data'];
} else {
  $results = [];
  $total_pages = 0;
}

// Assurez-vous que $total_pages est bien défini comme un nombre
if (!is_numeric($total_pages)) {
  $total_pages = 0;
  error_log("total_pages n'est pas numérique, valeur forcée à 0");
}

// Fonction pour conserver les paramètres de filtrage dans l'URL de pagination
function get_pagination_url($page)
{
  $params = $_GET;
  $params['page'] = $page;
  return '?' . http_build_query($params);
}

//* delete
try {
  if (isset($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
    $id = $_GET['id'];
    $sql_delete = "DELETE FROM evaluation WHERE id = :id";
    $stmt_delete = $dbh->prepare($sql_delete);
    $stmt_delete->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_delete->execute();
    $result = $stmt_delete->fetch(PDO::FETCH_ASSOC);
    if ($stmt_delete->rowCount() > 0) {
      header("Location: evaluations.php?success=1");
      exit();
    } else {
      header("Location:absence.php?error=Evaluation NON supprimé");
      exit();
    }
  }
} catch (Exception $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  header("Location:absence.php?error=Une erreur est survenue. Veuillez réessayer plus tard.");
  exit;
}


//* create & update
// if (isset($_POST['save'])) {
//   $id_evaluation = isset($_POST['id_evaluation']) ? (int)$_POST['id_evaluation'] : null;
//   $titre = htmlspecialchars($_POST['titre']);
//   $description = htmlspecialchars($_POST['description']);
//   $id_enseignant = (int)$_POST['id_enseignant'];
//   $id_classe = (int)$_POST['id_classe'];
//   $id_matiere = (int)$_POST['id_matiere'];
//   $type_evaluation = htmlspecialchars($_POST['type_evaluation']);
//   $statut = htmlspecialchars($_POST['statut']);

//   // Gestion du fichier
//   $fichier_path = '';
//   $new_file_uploaded = false;

//   if ($_FILES['fichier_path']['size'] > 0) {
//     $upload_dir = 'uploads/evaluations/';
//     $file_name = time() . '_' . $_FILES['fichier_path']['name'];
//     $target_file = $upload_dir . $file_name;

//     // Vérifier si le dossier existe, sinon le créer
//     if (!file_exists($upload_dir)) {
//       mkdir($upload_dir, 0777, true);
//     }

//     if (move_uploaded_file($_FILES['fichier_path']['tmp_name'], $target_file)) {
//       $fichier_path = $target_file;
//       $new_file_uploaded = true;
//     } else {
//       $error_message = "Erreur lors du téléchargement du fichier.";
//     }
//   }

//   try {
//     if ($id_evaluation) {
//       // Mise à jour d'une évaluation existante

//       // Si un nouveau fichier a été téléchargé, on met à jour le chemin du fichier
//       if ($new_file_uploaded) {
//         // Obtenir l'ancien chemin de fichier pour le supprimer plus tard
//         $stmt = $dbh->prepare("SELECT fichier_path FROM evaluation WHERE id = ?");
//         $stmt->execute([$id_evaluation]);
//         $old_file = $stmt->fetchColumn();

//         $sql = "UPDATE evaluation SET 
//                       titre = ?, 
//                       description = ?, 
//                       id_enseignant = ?, 
//                       id_classe = ?, 
//                       id_matiere = ?, 
//                       type_evaluation = ?, 
//                       statut = ?, 
//                       fichier_path = ?, 
//                       date_modification = NOW() 
//                       WHERE id = ?";
//         $stmt = $dbh->prepare($sql);
//         $stmt->execute([$titre, $description, $id_enseignant, $id_classe, $id_matiere, $type_evaluation, $statut, $fichier_path, $id_evaluation]);

//         // Supprimer l'ancien fichier s'il existe
//         if (file_exists($old_file)) {
//           unlink($old_file);
//         }
//       } else {
//         // Pas de nouveau fichier, on ne met pas à jour le chemin du fichier
//         $sql = "UPDATE evaluation SET 
//                       titre = ?, 
//                       description = ?, 
//                       id_enseignant = ?, 
//                       id_classe = ?, 
//                       id_matiere = ?, 
//                       type_evaluation = ?, 
//                       statut = ?, 
//                       date_modification = NOW() 
//                       WHERE id = ?";
//         $stmt = $dbh->prepare($sql);
//         $stmt->execute([$titre, $description, $id_enseignant, $id_classe, $id_matiere, $type_evaluation, $statut, $id_evaluation]);
//       }

//       $success_message = "Évaluation mise à jour avec succès.";
//     } else {
//       // Ajout d'une nouvelle évaluation
//       if ($new_file_uploaded) {
//         $result = ajouter_evaluation($dbh, $_POST, $_FILES);
//         var_dump($result);
//         die();
//         if ($result['success']) {
//           echo "<script>alert('{$result['message']}'); window.location.href='{$result['redirect_url']}';</script>";
//         } else {
//           echo "<script>alert('{$result['message']}');</script>";
//         }
//       } else {
//         $error_message = "Veuillez sélectionner un fichier.";
//       }
//     }
//   } catch (PDOException $e) {
//     $error_message = "Erreur lors de l'enregistrement: " . $e->getMessage();
//   }

//   // Redirection pour éviter la soumission du formulaire en cas de rafraîchissement
//   header("Location: evaluations.php" . (isset($error_message) ? "?error=" . urlencode($error_message) : "?success=" . urlencode($success_message)));
//   exit;
// }

if (isset($_POST['save'])) {
  $id_evaluation = isset($_POST['id_evaluation']) ? (int)$_POST['id_evaluation'] : null;
  $titre = htmlspecialchars($_POST['titre']);
  $description = htmlspecialchars($_POST['description']);
  $id_enseignant = (int)$_POST['id_enseignant'];
  $id_classe = (int)$_POST['id_classe'];
  $id_matiere = (int)$_POST['id_matiere'];
  $type_evaluation = htmlspecialchars($_POST['type_evaluation']);
  $statut = htmlspecialchars($_POST['statut']);

  // Gestion du fichier
  $fichier_path = '';
  $new_file_uploaded = false;

  if ($_FILES['fichier_path']['size'] > 0) {
    // Utiliser le même chemin absolu que dans ajouterAbsence
    $dossier_destination = __DIR__ . '/../../assets/evaluation/';

    // Créer le dossier s'il n'existe pas
    if (!file_exists($dossier_destination)) {
      mkdir($dossier_destination, 0777, true);
    }

    // Générer un nom de fichier unique
    $extension = pathinfo($_FILES['fichier_path']['name'], PATHINFO_EXTENSION);
    $nom_fichier = 'eval_' . time() . '_' . $id_enseignant . '.' . $extension;

    // Le chemin enregistré en BDD (URL relative)
    $fichier_path = 'assets/evaluation/' . $nom_fichier;

    // Le chemin physique réel pour enregistrer le fichier
    $destination_physique = $dossier_destination . $nom_fichier;

    // Débogage
    error_log("Tentative d'upload - Chemin physique: $destination_physique");
    error_log("Dossier existe: " . (file_exists($dossier_destination) ? 'Oui' : 'Non'));
    error_log("Dossier accessible en écriture: " . (is_writable($dossier_destination) ? 'Oui' : 'Non'));

    if (move_uploaded_file($_FILES['fichier_path']['tmp_name'], $destination_physique)) {
      $new_file_uploaded = true;
    } else {
      $error_message = "Erreur lors du téléchargement du fichier.";
      error_log("Erreur d'upload - Code: " . $_FILES['fichier_path']['error']);
      error_log("Détails de l'erreur: " . (error_get_last() ? error_get_last()['message'] : 'Erreur inconnue'));
    }
  }

  try {
    if ($id_evaluation) {
      // Mise à jour d'une évaluation existante

      // Si un nouveau fichier a été téléchargé, on met à jour le chemin du fichier
      if ($new_file_uploaded) {
        // Obtenir l'ancien chemin de fichier pour le supprimer plus tard
        $stmt = $dbh->prepare("SELECT fichier_path FROM evaluation WHERE id = ?");
        $stmt->execute([$id_evaluation]);
        $old_file = $stmt->fetchColumn();

        $sql = "UPDATE evaluation SET 
                      titre = ?, 
                      description = ?, 
                      id_enseignant = ?, 
                      id_classe = ?, 
                      id_matiere = ?, 
                      type_evaluation = ?, 
                      statut = ?, 
                      fichier_path = ?, 
                      date_modification = NOW() 
                      WHERE id = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([$titre, $description, $id_enseignant, $id_classe, $id_matiere, $type_evaluation, $statut, $fichier_path, $id_evaluation]);

        // Supprimer l'ancien fichier s'il existe
        if (!empty($old_file)) {
          $old_file_physical = __DIR__ . '/../../' . $old_file;
          if (file_exists($old_file_physical)) {
            unlink($old_file_physical);
          }
        }
      } else {
        // Pas de nouveau fichier, on ne met pas à jour le chemin du fichier
        $sql = "UPDATE evaluation SET 
                      titre = ?, 
                      description = ?, 
                      id_enseignant = ?, 
                      id_classe = ?, 
                      id_matiere = ?, 
                      type_evaluation = ?, 
                      statut = ?, 
                      date_modification = NOW() 
                      WHERE id = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([$titre, $description, $id_enseignant, $id_classe, $id_matiere, $type_evaluation, $statut, $id_evaluation]);
      }

      $success_message = "Évaluation mise à jour avec succès.";
    } else {
      // Ajout d'une nouvelle évaluation
      if ($new_file_uploaded) {
        $result = ajouter_evaluation($dbh, $_POST, $_FILES);

        if ($result['success']) {
          $success_message = $result['message'];
          // Utiliser la redirection fournie par le résultat
          if ($result['redirect']) {
            header("Location: " . $result['redirect_url']);
            exit;
          }
        } else {
          $error_message = $result['message'];
        }
      } else {
        $error_message = "Veuillez sélectionner un fichier.";
      }
    }
  } catch (PDOException $e) {
    $error_message = "Erreur lors de l'enregistrement: " . $e->getMessage();
    error_log($e->getMessage());
  }

  // Redirection pour éviter la soumission du formulaire en cas de rafraîchissement
  if (!isset($result['redirect']) || !$result['redirect']) {
    header("Location: evaluations.php" . (isset($error_message) ? "?error=" . urlencode($error_message) : "?success=" . urlencode($success_message)));
    exit;
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>

<!-- style evaluation -->
<link rel="stylesheet" href="../../assets/css/evaluation.css">

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php require('../../includes/admin/aside_admin.php') ?>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <?php require('../../includes/admin/navbar_admin.php') ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <div class="pb-0 d-flex flex-wrap justify-content-between align-items-center text-center text-md-start">
                <div class="mb-2 mb-md-0 flex-grow-1 text-center text-md-start">
                  <h6 class="text-primary">Evaluation</h6>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                  <!-- <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" /> -->
                  <a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#evaluationModal">Ajouter Evaluation</a>
                  <!-- <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button> -->
                </div>
              </div>
              <div class="row my-4">
                <div class="col-lg-4">
                  <div class="filter-group">
                    <label for="teacher-select" class="text-dark">Professeur:</label>
                    <select class="form-select" id="teacher-select" name="enseignant">
                      <option value="">Tous les professeurs</option>
                      <?php foreach ($eseignants  as $prof) : ?>
                        <option value="<?= htmlspecialchars($prof->id_enseignant) ?>" <?= ($filter_enseignant == $prof->id_enseignant) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($prof->nom_enseignant) ?> <?= htmlspecialchars($prof->prenom_enseignant) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="filter-group">
                    <label for="group-select" class="text-dark">Groupe:</label>
                    <select class="form-select" id="group-select" name="classe">
                      <option value="">Tous les groupes</option>
                      <?php foreach ($classes as $groupe) : ?>
                        <option value="<?= htmlspecialchars($groupe->id_classe) ?>" <?= ($filter_classe == $groupe->id_classe) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($groupe->nom_classe) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="filter-group">
                    <label for="matiere-select" class="text-dark">Matière:</label>
                    <select class="form-select" id="matiere-select" name="matiere">
                      <option value="">Toutes les matières</option>
                      <?php foreach ($matieres as $matiere) : ?>
                        <option value="<?= htmlspecialchars($matiere->id_matiere) ?>" <?= ($filter_matiere == $matiere->id_matiere) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($matiere->code_matiere) ?> - <?= htmlspecialchars($matiere->nom_matiere) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="filter-group">
                    <label for="type-select" class="text-dark">Type d'évaluation:</label>
                    <select class="form-select" id="type-select" name="type">
                      <option value="">Tous les types</option>
                      <?php foreach ($types_evaluation as $type) : ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= ($filter_type_evaluation == $type) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($type) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="filter-group">
                    <label for="statut-select" class="text-dark">Statut:</label>
                    <select class="form-select" id="statut-select" name="statut">
                      <option value="">Tous les statuts</option>
                      <?php foreach ($statuts as $statut) : ?>
                        <option value="<?= htmlspecialchars($statut) ?>" <?= ($filter_statut == $statut) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($statut) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="filter-group">
                    <label for="filter-button" class="text-dark">Filtrage:</label>
                    <button type="button" id="filter-button" class="btn btn-primary btn-sm w-100">Filtrer</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body px-0 pt-0 pb-2">
            <?php if (count($results) > 0) { ?>
              <div class="row mx-3">
                <?php foreach ($results as $result) :
                  // Déterminer le type de fichier
                  $file_extension = pathinfo($result->fichier_path, PATHINFO_EXTENSION);
                  $icon_class = 'fa-file';
                  $extension_class = '';

                  if ($file_extension == 'pdf') {
                    $icon_class = 'fa-file-pdf';
                    $extension_class = 'pdf';
                  } elseif (in_array($file_extension, ['doc', 'docx'])) {
                    $icon_class = 'fa-file-word';
                    $extension_class = 'doc';
                  }
                ?>
                  <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card evaluation-card status-<?= htmlspecialchars($result->statut) ?>">
                      <div class="card-body position-relative">
                        <span class="evaluation-type type-<?= htmlspecialchars($result->type_evaluation) ?>">
                          <?= ucfirst(htmlspecialchars($result->type_evaluation)) ?>
                        </span>

                        <div class="text-center">
                          <i class="fas <?= $icon_class ?> file-icon <?= $extension_class ?>"></i>
                        </div>

                        <h5 class="card-title text-xs font-weight-bold"><?= htmlspecialchars($result->titre) ?></h5>

                        <p class="text-xs font-weight-bold mb-1 text-muted">
                          <i class="fas fa-chalkboard-teacher"></i>
                          <?php echo ($result->nom_enseignant || $result->prenom_enseignant) === NULL ? 'Aucun Enseignant' : $result->nom_enseignant . " " . $result->prenom_enseignant ?>
                        </p>

                        <p class="text-xs font-weight-bold mb-1">
                          <i class="fas fa-book"></i>
                          <?= htmlspecialchars($result->nom_matiere) ?>
                          <span class="badge bg-secondary"><?= htmlspecialchars($result->code_matiere) ?></span>
                        </p>

                        <p class="text-xs font-weight-bold mb-1">
                          <i class="fas fa-users"></i>
                          <?php echo $result->nom_classe === null ? 'Aucun Classe' : $result->nom_classe; ?>
                        </p>

                        <p class="text-xs font-weight-bold mb-1">
                          <i class="fas fa-graduation-cap"></i>
                          <?= htmlspecialchars($result->niveau_scolaire) ?>
                        </p>

                        <p class="text-xs font-weight-bold mb-1">
                          <i class="far fa-calendar-alt"></i>
                          <?= htmlspecialchars($result->date_creation) ?>
                        </p>
                        <?php if (!empty($result->description)) : ?>
                          <p class="text-xs font-weight-bold mb-1">
                            <i class="fas fa-info-circle"></i>
                            <?php echo $result->description === '' ? 'Aucun Description' : $result->description; ?>
                          </p>
                        <?php endif; ?>
                        <div class="mt-3 d-flex">
                          <a href="<?= $result->fichier_path ?>" target="_blank" class="btn btn-xs btn-primary px-2">
                            <i class="fas fa-download"></i> Télécharger
                          </a>
                          <a href="edit_evaluation.php?id=<?= $result->id ?>" class="btn btn-xs btn-info px-2 ms-1">
                            <i class="fas fa-pencil-alt"></i>
                          </a>
                          <a href="evaluations.php?id=<?= $result->id ?>&del=1" class="btn btn-xs btn-danger px-2 ms-1" onClick="return confirmDelete(event, this)">
                            <i class="fas fa-trash"></i>
                          </a>
                        </div>fichier_path
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <!-- Pagination -->
              <?php if ($total_pages > 1) : ?>
                <div class="pagination-container d-flex justify-content-center mt-4">
                  <nav aria-label="Navigation des évaluations">
                    <ul class="pagination">
                      <?php if ($page_actuelle > 1) : ?>
                        <li class="page-item">
                          <a class="page-link" href="<?= get_pagination_url($page_actuelle - 1) ?>" aria-label="Précédent">
                            <span aria-hidden="true">&laquo;</span>
                          </a>
                        </li>
                      <?php endif; ?>

                      <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <li class="page-item <?= ($i == $page_actuelle) ? 'active' : '' ?>">
                          <a class="page-link" href="<?= get_pagination_url($i) ?>"><?= $i ?></a>
                        </li>
                      <?php endfor; ?>

                      <?php if ($page_actuelle < $total_pages) : ?>
                        <li class="page-item">
                          <a class="page-link" href="<?= get_pagination_url($page_actuelle + 1) ?>" aria-label="Suivant">
                            <span aria-hidden="true">&raquo;</span>
                          </a>
                        </li>
                      <?php endif; ?>
                    </ul>
                  </nav>
                </div>
              <?php endif; ?>
            <?php } else { ?>
              <div class="text-center py-5">
                <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
                <h4>Aucune évaluation disponible</h4>
                <p class="text-muted">Il n'y a pas encore d'évaluations enregistrées dans le système.</p>
                <a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#evaluationModal">
                  <i class="fas fa-plus"></i> Ajouter une évaluation
                </a>
              </div>
            <?php } ?>
          </div>
          <!-- Modal -->
          <?php require_once("form/evaluation_modal.php") ?>
        </div>
      </div>
    </div>
    <!-- FOOTER -->
    <?php include '../../includes/footer.php' ?>
    </div>
  </main>


  <script>
    // Script pour basculer entre vue en grille et vue en tableau
    document.addEventListener('DOMContentLoaded', function() {
      // Gérer le clic sur le bouton de filtrage
      document.getElementById('filter-button').addEventListener('click', function() {
        const enseignant = document.getElementById('teacher-select').value;
        const classe = document.getElementById('group-select').value;
        const matiere = document.getElementById('matiere-select').value;
        const type = document.getElementById('type-select').value;
        const statut = document.getElementById('statut-select').value;

        // Construire l'URL avec les filtres
        let url = window.location.pathname + '?';
        if (enseignant) url += 'enseignant=' + enseignant + '&';
        if (classe) url += 'classe=' + classe + '&';
        if (matiere) url += 'matiere=' + matiere + '&';
        if (type) url += 'type=' + type + '&';
        if (statut) url += 'statut=' + statut + '&';

        // Rediriger vers la page 1 avec les filtres
        window.location.href = url + 'page=1';
      });
    });
  </script>

  <!-- Data table -->
  <script src="../../assets/js/datatable.js"></script>
  <!-- Export Functio -->
  <!-- <script src="../../assets/js/export.js"></script> -->

  <!-- sweet alert -->
  <script src="../../assets/js/alerts/sweet_alert.js"></script>

  <!-- //* Date Picker + AJAX eleves intervalle date 
  <script src="../../assets/dateP_dateP/dateP_dataP_absence.js"></script> -->

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