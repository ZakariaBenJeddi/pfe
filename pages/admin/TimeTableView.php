<?php
session_start();

if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

require_once __DIR__ . '/../../includes/DatabaseConnexion.php';
require_once __DIR__ . '/../../includes/admin/controller/controller_timeTable.php';

// if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
//   $timeTableData = new TimeTableData();
//   // Get week dates if provided
//   $weekStart = null;
//   $weekEnd = null;
//   if (isset($_GET['week'])) {
//       list($weekStart, $weekEnd) = explode(',', $_GET['week']);
//   }
//   if (isset($_GET['teacher'])) {
//       echo $timeTableData->getSpecificTeacherSchedule($_GET['teacher'], $weekStart, $weekEnd);
//   } elseif (isset($_GET['group'])) {
//       echo $timeTableData->getSpecificGroupSchedule($_GET['group'], $weekStart, $weekEnd);
//   } elseif (isset($_GET['room'])) {
//       echo $timeTableData->getSpecificRoomSchedule($_GET['room'], $weekStart, $weekEnd);
//   }
//   exit;
// }

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  try {
    $timeTableData = new TimeTableData();

    if (isset($_GET['teacher'])) {
      $html = $timeTableData->getSpecificTeacherSchedule($_GET['teacher']);
      echo $html ?: '<div class="alert alert-info">Aucun emploi du temps trouvé pour cet enseignant</div>';
    } elseif (isset($_GET['group'])) {
      $html = $timeTableData->getClassSchedule($_GET['group']);
      echo $html ?: '<div class="alert alert-info">Aucun emploi du temps trouvé pour ce groupe</div>';
    } elseif (isset($_GET['room'])) {
      $html = $timeTableData->getSpecificRoomSchedule($_GET['room']);
      echo $html ?: '<div class="alert alert-info">Aucun emploi du temps trouvé pour cette salle</div>';
    }
  } catch (Exception $e) {
    http_response_code(500);
    echo '<div class="alert alert-danger">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
  }
  exit;
}


$timeTableData = new TimeTableData();
// Récupérer tous les emplois du temps
$allSchedules = $timeTableData->getAllSchedules();
// Récupérer l'emploi du temps des professeurs
$teachersSchedule = $timeTableData->getTeachersSchedule();
// Récupérer l'emploi du temps des salles
$roomsSchedule = $timeTableData->getRoomsSchedule();
// Accéder aux jours et créneaux horaires
$days = $timeTableData->getDays();
$timeSlots = $timeTableData->getTimeSlots();


// Get lists for select dropdowns
try {
  $stmt = $dbh->prepare("CALL get_all_enseignant()");
  $stmt->execute();
  $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $stmt->closeCursor();

  // var_dump($professeurs);

  $stmt_groupes = $dbh->prepare("CALL get_all_classes()");
  $stmt_groupes->execute();
  $groupes = $stmt_groupes->fetchAll(PDO::FETCH_ASSOC);
  $stmt_groupes->closeCursor();

  $stmt_salles = $dbh->prepare("CALL get_all_salle()");
  $stmt_salles->execute();
  $salles = $stmt_salles->fetchAll(PDO::FETCH_ASSOC);
  $stmt_salles->closeCursor();
} catch (PDOException $e) {
  error_log("Database error: " . $e->getMessage());
  echo "Une erreur est survenue. Veuillez réessayer plus tard.";
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>
<!-- css -->
<link rel="stylesheet" href="../../assets/css/timetableview.css">

<style>
table td {
  height: 80px;
  min-height: 80px;
  max-height: 80px;
  vertical-align: middle;
}

.time-slot {
  height: 80px;
  min-height: 80px;
  max-height: 80px;
}

td[rowspan] {
  height: calc(80px * attr(rowspan integer));
}

.class-info {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
</style>

<body class="g-sidenav-show  bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php require('../../includes/admin/aside_admin.php') ?>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <?php require('../../includes/admin/navbar_admin.php') ?>
    <!-- End Navbar -->
    <div class=" pb-0 mt-5 me-5 text-end text-primary">
      <a href="TimeTableConfig.php" class="btn btn-light px-3">configurer donnes</a>
    </div>
    <div class="container">
      <div class="row mb-5">
        <div class="col-lg-4">
          <div class="filter-group mb-3">
            <h5 for="teacher-select" class="text-dark">Professeur:</h5>
            <select class="form-select" id="teacher-select">
              <option value="">Selectionner professeurs</option>
              <?php foreach ($professeurs as $prof) : ?>
                <option value="<?= htmlspecialchars($prof['nom_enseignant']) ?>">
                  <?= htmlspecialchars($prof['nom_enseignant']) . " " . htmlspecialchars($prof['prenom_enseignant']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="filter-group mb-3">
            <h5 for="group-select" class="text-dark">Groupe:</h5>
            <select class="form-select" id="group-select">
              <option value="">Selectionner groupes</option>
              <?php foreach ($groupes as $groupe) : ?>
                <option value="<?= htmlspecialchars($groupe['nom_classe']) ?>">
                  <?= htmlspecialchars($groupe['nom_classe']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="filter-group mb-3">
            <h5 for="room-select" class="text-dark">Salle:</h5>
            <select class="form-select" id="room-select">
              <option value="">Selectionner salles</option>
              <?php foreach ($salles as $salle) : ?>
                <option value="<?= htmlspecialchars($salle['nom_salle']) ?>">
                  <?= htmlspecialchars($salle['nom_salle']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <!-- Dans la partie HTML où se trouve le select des semaines -->
        <!-- <div class="col-lg-3">
          <div class="filter-group mb-3">
            <h5 for="week-select" class="text-dark">Semaine:</h5>
            <select class="form-select" id="week-select">
              <option value="">Selectionner Une Semaine</option>
              <?php
              // $weeks = $displayer->getWeeksList();
              // foreach ($weeks as $week) :
              //   $value = $week['start'] . ',' . $week['end'];
              ?>
                <option value="<?php //echo htmlspecialchars($value) 
                                ?>">
                  <?php //echo htmlspecialchars($week['display']) 
                  ?>
                </option>
              <?php //endforeach; 
              ?>
            </select>
          </div>
        </div> -->
      </div>
    </div>
    <div class="px-0 pt-0 ">
      <!-- section pour les classes -->
      <div id="classes-section" class="schedule-section active p-0">
        <?php foreach ($allSchedules as $classId => $data) : ?>
          <div class="schedule-container  table-responsive">
            <h2>Classe: <?= htmlspecialchars($data['class_name']) ?></h2>
            <table>
              <tr>
                <th>Horaire</th>
                <?php foreach (array_keys($data['schedule']) as $day) : ?>
                  <th><?= $day ?></th>
                <?php endforeach; ?>
              </tr>
              <?php
              $timeSlots = array_keys(reset($data['schedule']));
              foreach ($timeSlots as $timeSlot) : ?>
                <tr>
                  <td class="time-slot"><?= $timeSlot ?></td>
                  <?php foreach ($data['schedule'] as $day => $slots) : ?>
                    <?php if (isset($slots[$timeSlot]) && $slots[$timeSlot] !== 'occupied_by_previous') : ?>
                      <td class="<?= empty($slots[$timeSlot]) ? 'empty-slot' : '' ?>" 
                          rowspan="<?= isset($slots[$timeSlot]['rowspan']) ? $slots[$timeSlot]['rowspan'] : 1 ?>">
                        <?php if (!empty($slots[$timeSlot])) : ?>
                          <div class="class-info">
                            <div class="professeur"><?= htmlspecialchars($slots[$timeSlot]['professeur']) ?></div>
                            <div class="matiere"><?= htmlspecialchars($slots[$timeSlot]['matiere']) ?></div>
                            <div class="salle"><?= htmlspecialchars($slots[$timeSlot]['salle']) ?></div>
                          </div>
                        <?php else : ?>
                          Libre
                        <?php endif; ?>
                      </td>
                    <?php elseif ($slots[$timeSlot] === 'occupied_by_previous') : ?>
                      <!-- Ne rien afficher, ce créneau est couvert par un rowspan -->
                    <?php else : ?>
                      <td class="empty-slot">Libre</td>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </table>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- section pour les prof -->
      <div id="teachers-section" class="schedule-section table-responsive">
        <?php foreach ($teachersSchedule as $teacherId => $data) : ?>
          <div class="schedule-container">
            <h2>Professeur: <?= htmlspecialchars($data['nom']) ?></h2>
            <table>
              <tr>
                <th>Horaire</th>
                <?php foreach (array_keys($data['schedule']) as $day) : ?>
                  <th><?= $day ?></th>
                <?php endforeach; ?>
              </tr>
              <?php
              $timeSlots = array_keys(reset($data['schedule']));
              foreach ($timeSlots as $timeSlot) :
              ?>
                <tr>
                  <td class="time-slot"><?= $timeSlot ?></td>
                  <?php foreach ($data['schedule'] as $day => $slots) : ?>
                    <?php if (isset($slots[$timeSlot]) && $slots[$timeSlot] !== 'occupied_by_previous') : ?>
                      <td class="<?= empty($slots[$timeSlot]) ? 'empty-slot' : '' ?>"
                          rowspan="<?= isset($slots[$timeSlot]['rowspan']) ? $slots[$timeSlot]['rowspan'] : 1 ?>">
                        <?php
                        // On va parcourir toutes les classes pour trouver la séance de ce professeur
                        $sessionFound = false;
                        if (!empty($slots[$timeSlot])) {
                          $sessionFound = true;
                        ?>
                          <div class="class-info">
                            <div class="matiere"><?= htmlspecialchars($slots[$timeSlot]['matiere']) ?></div>
                            <div class="salle"><?= htmlspecialchars($slots[$timeSlot]['salle']) ?></div>
                            <div class="classe"><?= htmlspecialchars($slots[$timeSlot]['classe']) ?></div>
                          </div>
                        <?php
                        }
                        if (!$sessionFound) {
                          echo 'Disponible';
                        }
                        ?>
                      </td>
                    <?php elseif ($slots[$timeSlot] === 'occupied_by_previous') : ?>
                      <!-- Ne rien afficher, ce créneau est couvert par un rowspan -->
                    <?php else : ?>
                      <td class="empty-slot">Disponible</td>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </table>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- section pour les salles -->
      <div id="rooms-section" class="schedule-section table-responsive">
        <?php foreach ($roomsSchedule as $salleId => $data) : ?>
          <div class="schedule-container">
            <h2>Salle: <?= htmlspecialchars($data['nom']) ?></h2>
            <table>
              <tr>
                <th>Horaire</th>
                <?php foreach (array_keys($data['schedule']) as $day) : ?>
                  <th><?= $day ?></th>
                <?php endforeach; ?>
              </tr>
              <?php
              $timeSlots = array_keys(reset($data['schedule']));
              foreach ($timeSlots as $timeSlot) :
              ?>
                <tr>
                  <td class="time-slot"><?= $timeSlot ?></td>
                  <?php foreach ($data['schedule'] as $day => $slots) : ?>
                    <?php if (isset($slots[$timeSlot]) && $slots[$timeSlot] !== 'occupied_by_previous') : ?>
                      <td class="<?= empty($slots[$timeSlot]) ? 'empty-slot' : '' ?>"
                          rowspan="<?= isset($slots[$timeSlot]['rowspan']) ? $slots[$timeSlot]['rowspan'] : 1 ?>">
                        <?php
                        // On va parcourir toutes les classes pour trouver la séance dans cette salle
                        $sessionFound = false;
                        if (!empty($slots[$timeSlot])) {
                          $sessionFound = true;
                        ?>
                          <div class="class-info">
                            <div class="professeur"><?= htmlspecialchars($slots[$timeSlot]['professeur']) ?></div>
                            <div class="classe"><?= htmlspecialchars($slots[$timeSlot]['classe']) ?></div>
                            <div class="matiere"><?= htmlspecialchars($slots[$timeSlot]['matiere']) ?></div>
                          </div>
                        <?php
                        }
                        if (!$sessionFound) {
                          echo 'Disponible';
                        }
                        ?>
                      </td>
                    <?php elseif ($slots[$timeSlot] === 'occupied_by_previous') : ?>
                      <!-- Ne rien afficher, ce créneau est couvert par un rowspan -->
                    <?php else : ?>
                      <td class="empty-slot">Disponible</td>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </table>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- FOOTER -->
    <?php include '../../includes/footer.php' ?>

    <!-- </div> -->
  </main>
  <!-- FIXED PLUGIN  -->
  <?php // include '../../includes/fixedplugin.php' 
  ?>

  <!-- filtration d'emploi du temps -->
  <script src="../../assets/js/timetablefilter.js"></script>

  <!-- dezoumer la page si le type d'ecran est portable -->
  <script src="../../assets/js/dezoomer.js"></script>

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