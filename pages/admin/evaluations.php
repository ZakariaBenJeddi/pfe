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

// Pagination
$evaluations_par_page = 12; // 3 lignes de 4 cartes
$page_actuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Récupération des évaluations paginées
$evaluationsInfo = getEvaluationsinfoPaginated($dbh, $page_actuelle, $evaluations_par_page);

//* read 
if ($evaluationsInfo['success']) {
    $total_evaluations = $evaluationsInfo['count'];
    // $total_evaluations = 0;
    $total_pages = ceil($total_evaluations / $evaluations_par_page);
    $results = $evaluationsInfo['data'];
} else {
    $results = [];
    $total_pages = 0;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save'])) {
    $result = ajouter_evaluation($dbh, $_POST, $_FILES);
    if ($result['success']) {
        echo "<script>alert('{$result['message']}'); window.location.href='{$result['redirect_url']}';</script>";
    } else {
        echo "<script>alert('{$result['message']}');</script>";
    }
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
            echo "<script>
              alert('Evaluation bien supprimé');
              window.location.href = 'evaluations.php';
          </script>";
        } else {
            echo "<script>
            alert('Evaluation NON supprimé');
            window.location.href = 'evaluations.php';
            </script>";
        }
    }
} catch (Exception $e) {
    error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
    echo "<script>
      alert('Une erreur est survenue. Veuillez réessayer plus tard.');
      window.location.href = 'eleves.php';
  </script>";
    exit;
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
                    <div class="card mb-4">
                        <div class="card-header pb-0 mb-5 d-flex flex-wrap justify-content-between align-items-center text-center text-md-start">
                            <div class="mb-2 mb-md-0 flex-grow-1 text-center text-md-start">
                                <h6 class="text-primary">Absence</h6>
                            </div>
                            <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                                <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
                                <a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#evaluationModal">Ajouter Evaluation</a>
                                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
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
                                                        <a href="evaluations.php?id=<?= $result->id ?>&del=1" class="btn btn-xs btn-danger px-2 ms-1" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
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
                                                        <a class="page-link" href="?page=<?= $page_actuelle - 1 ?>" aria-label="Précédent">
                                                            <span aria-hidden="true">&laquo;</span>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                                    <li class="page-item <?= ($i == $page_actuelle) ? 'active' : '' ?>">
                                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>

                                                <?php if ($page_actuelle < $total_pages) : ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?= $page_actuelle + 1 ?>" aria-label="Suivant">
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
        document.getElementById('toggle-view').addEventListener('click', function() {
            const gridView = document.getElementById('grid-view');
            const tableView = document.getElementById('table-view');

            if (gridView.style.display === 'none') {
                gridView.style.display = 'block';
                tableView.style.display = 'none';
                this.innerHTML = '<i class="fas fa-table"></i> Vue tableau';
            } else {
                gridView.style.display = 'none';
                tableView.style.display = 'block';
                this.innerHTML = '<i class="fas fa-th-large"></i> Vue grille';
            }
        });
    </script>

    <!-- Data table -->
    <script src="../../assets/js/datatable.js"></script>
    <!-- Export Functio -->
    <script src="../../assets/js/export.js"></script>

    <!-- //* Date Picker + AJAX eleves intervalle date  -->
    <script src="../../assets/dateP_dateP/dateP_dataP_absence.js"></script>

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