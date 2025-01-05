<?php
session_start();
require('../includes/DatabaseConnexion.php');

if (empty($_SESSION['user'])) {
    header('location:sign-in.php');
}

//* deconnexion 5s
// require('../includes/deconnexion_5s.php');

use function PHPSTORM_META\type;

require_once('db-connect.php');


$sql = "SELECT id_enseignant, nom_enseignant FROM enseignant ORDER BY nom_enseignant";
$prfs = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$sql_classes = "SELECT id_classe, nom_classe FROM classe ORDER BY nom_classe";
$clss = $dbh->query($sql_classes)->fetchAll(PDO::FETCH_ASSOC);

$sql_salles = "SELECT id_salle, nom_salle FROM salle ORDER BY nom_salle";
$slls = $dbh->query($sql_salles)->fetchAll(PDO::FETCH_ASSOC);

$sql_modls = "SELECT id_matiere, nom_matiere ,id_filiere,code_matiere FROM matiere ORDER BY code_matiere";
$modls = $dbh->query($sql_modls)->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    switch ($_GET['action']) {
        case 'get_matieres':
            if (isset($_GET['classe_id'])) {
                $classe_id = intval($_GET['classe_id']);
                $query = "SELECT DISTINCT m.id_matiere,code_matiere as id, 
                    CONCAT(m.code_matiere, ' - ', m.nom_matiere) as nom 
                    FROM matiere m 
                    WHERE m.id_filiere IN (
                        SELECT filiere_id FROM classe WHERE id_classe = ?
                        UNION 
                        SELECT 8
                    )
                    ORDER BY m.code_matiere";
                $stmt = $dbh->prepare($query);
                $stmt->execute([$classe_id]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            exit;

        case 'get_professeurs':
            if (isset($_GET['matiere_id'])) {
                $query = "SELECT DISTINCT e.id_enseignant as id, e.nom_enseignant as nom 
                         FROM enseignant e 
                         ORDER BY e.nom_enseignant";
                $stmt = $dbh->prepare($query);
                $stmt->execute();
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<?php require('../includes/calendrier_head.php'); ?>

<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-primary position-absolute w-100"></div>
    <?php require('../includes/aside_admin.php') ?>
    <main class="main-content position-relative border-radius-lg">
        <?php require('../includes/navbar_admin.php') ?>
        <div class="container-fluid py-4">
            <div class="container py-5 " style="margin-top: 13rem !important;" id="page-container">
                <div class="row mb-5">
                    <div class="col-lg-4">
                        <div class="filter-group">
                            <label for="teacher-select" class="text-light">Professeur:</label>
                            <select class="form-select" id="teacher-select">
                                <option value="">Tous les professeurs</option>
                                <?php foreach ($prfs as $prof) : ?>
                                    <option value="<?= htmlspecialchars($prof['nom_enseignant']) ?>"><?= htmlspecialchars($prof['nom_enseignant']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="filter-group">
                            <label for="group-select" class="text-light">Groupe:</label>
                            <select class="form-select" id="group-select">
                                <option value="">Tous les groupes</option>
                                <?php foreach ($clss as $groupe) : ?>
                                    <option value="<?= htmlspecialchars($groupe['nom_classe']) ?>"><?= htmlspecialchars($groupe['nom_classe']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="filter-group">
                            <label for="room-select" class="text-light">Salle:</label>
                            <select class="form-select" id="room-select">
                                <option value="">Toutes les salles</option>
                                <?php foreach ($slls as $salle) : ?>
                                    <option value="<?= htmlspecialchars($salle['nom_salle']) ?>"><?= htmlspecialchars($salle['nom_salle']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row ">
                    <div class="col-md-9">
                        <div id="loading-screen" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                        <div id="calendar"></div>
                    </div>
                    <div class="col-md-3 mt-lg-0 mt-5">
                        <div class="d-flex justify-content-center mb-2">
                            <button type="button" class="btn btn-primary btn-sm me-1" id="import-btn">Importer</button>
                            <a href="export_excel.php" class="btn btn-primary btn-sm ms-1">Exporter</a>
                        </div>
                        <div class="cardt rounded-0 shadow">
                            <div class="card-header bg-gradient bg-primary text-light">
                                <h5 class="card-title text-center">Schedule Form</h5>
                            </div>
                            <div class="card-body">
                                <div class="container-fluid">
                                    <form action="" method="get" id="matiere-form">
                                        <div class="form-group mb-2">
                                            <label for="classe-select" class="control-label">Classes</label><br>
                                            <select class="form-select form-select-sm text-sm" name="classe-select" id="classe-select">
                                                <option value="">Choisissez une Classe</option>
                                                <?php foreach ($clss as $classe) : ?>
                                                    <option value="<?= $classe['id_classe'] ?>"><?= htmlspecialchars($classe['nom_classe']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="matiere-select" class="control-label">Matière</label>
                                            <select class="form-select form-select-sm text-sm" name="matiere_id" id="matiere-select" disabled>
                                                <option value="">Sélectionnez une matière</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="professeur-select" class="control-label">Enseignant</label>
                                            <select class="form-select form-select-sm text-sm" name="professeur_id" id="professeur-select" disabled>
                                                <option value="">Choisissez un enseignant</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="salle-select" class="control-label">Salle</label>
                                            <select class="form-select form-select-sm text-sm" name="salle" id="salle-select">
                                                <option value="">Choisissez une salle</option>
                                                <?php foreach ($slls as $salle) : ?>
                                                    <option value="<?= $salle['id_salle'] ?>"><?= htmlspecialchars($salle['nom_salle']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </form>

                                    <form action="save_schedule.php" method="post" id="schedule-form">
                                        <input type="hidden" name="id" value="">
                                        <input type="hidden" name="professeur_id" id="professeur-value">
                                        <input type="hidden" name="matiere_id" id="matiere-value">
                                        <input type="hidden" name="classe-select" id="classe-value">

                                        <div class="form-group mb-2">
                                            <label for="title" class="control-label">Title</label>
                                            <input type="text" class="form-control form-control-sm rounded-0" name="title" id="title" required>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="description" class="control-label">Description</label>
                                            <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="description" required></textarea>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="start_datetime" class="control-label">Start</label>
                                            <input type="datetime-local" class="form-control form-control-sm rounded-0" name="start_datetime" id="start_datetime" required>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="end_datetime" class="control-label">End</label>
                                            <input type="datetime-local" class="form-control form-control-sm rounded-0" name="end_datetime" id="end_datetime" required>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-center">
                                    <button class="btn btn-primary btn-sm rounded-0" type="submit" form="schedule-form"><i class="fa fa-save"></i> Save</button>
                                    <button class="btn btn-default border btn-sm rounded-0" type="reset" form="schedule-form"><i class="fa fa-reset"></i> Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Event Details Modal -->
            <?php require('modal_calendrier.php') ?>
        </div>
    </main>
    <!-- Importer Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="../assets/js/import_excel_calendrier.js"></script>

    <script>
        const classeSelect = document.getElementById('classe-select');
        const matiereSelect = document.getElementById('matiere-select');
        const professeurSelect = document.getElementById('professeur-select');
        const salleSelect = document.getElementById('salle-select');

        // Hidden input fields
        const professeurValue = document.getElementById('professeur-value');
        const matiereValue = document.getElementById('matiere-value');
        const classeValue = document.getElementById('classe-value');

        function resetSelect(select, defaultText = "", disabled = true) {
            select.innerHTML = `<option value="">${defaultText}</option>`;
            select.disabled = disabled;
        }

        // Handle classe selection
        classeSelect.addEventListener('change', function() {
            const classeId = this.value;
            classeValue.value = classeId;

            // Reset subsequent selects
            resetSelect(matiereSelect, "Sélectionnez une matière");
            resetSelect(professeurSelect, "Choisissez un enseignant");

            if (classeId) {
                fetch(`?action=get_matieres&classe_id=${classeId}`)
                    .then(response => response.json())
                    .then(data => {
                        matiereSelect.disabled = false;
                        data.forEach(matiere => {
                            const option = document.createElement('option');
                            option.value = matiere.id_matiere;
                            option.textContent = matiere.nom;
                            matiereSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Erreur:', error));
            }
        });

        // Handle matière selection
        matiereSelect.addEventListener('change', function() {
            const matiereId = this.value;
            matiereValue.value = matiereId;

            // Reset professeur select
            resetSelect(professeurSelect, "Choisissez un enseignant");

            if (matiereId) {
                fetch(`?action=get_professeurs&matiere_id=${matiereId}`)
                    .then(response => response.json())
                    .then(data => {
                        professeurSelect.disabled = false;
                        data.forEach(prof => {
                            const option = document.createElement('option');
                            option.value = prof.id;
                            option.textContent = prof.nom;
                            professeurSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Erreur:', error));
            }
        });

        // Update hidden values when selections change
        professeurSelect.addEventListener('change', function() {
            professeurValue.value = this.value;
        });

        // Form validation before submit
        document.getElementById('schedule-form').addEventListener('submit', function(e) {
            const requiredSelects = {
                'classe-select': 'Veuillez sélectionner une classe',
                'matiere-select': 'Veuillez sélectionner une matière',
                'professeur-select': 'Veuillez sélectionner un enseignant',
                'salle-select': 'Veuillez sélectionner une salle'
            };

            for (const [id, message] of Object.entries(requiredSelects)) {
                const select = document.getElementById(id);
                if (!select.value) {
                    e.preventDefault();
                    alert(message);
                    return;
                }
            }

            // Validate datetime
            const start = new Date(document.getElementById('start_datetime').value);
            const end = new Date(document.getElementById('end_datetime').value);

            if (start >= end) {
                e.preventDefault();
                alert('La date de fin doit être postérieure à la date de début');
                return;
            }
        });
    </script>

    <!-- reload page pour 5s premiere chargement de page -->
    <script src="../assets/js/loading.js"></script>
    <!-- dezoumer la page si le type d'ecran est portable -->
    <script src="../assets/js/dezoomer.js"></script>
</body>
<script>
    var scheds = $.parseJSON('<?= json_encode($sched_res) ?>')
</script>
<script src="../assets/js/script.js"></script>
<!-- <script src="./javascript/js/script.js"></script> -->

</html>