<?php
session_start();

require_once __DIR__ . '/../../includes/admin/controller/controller_timeTable.php';


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

?>


<!DOCTYPE html>
<html lang="en">
<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>
<style>
  body {
    font-family: Arial, sans-serif;
  }

  .schedule-container {
    margin-bottom: 50px;
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  h1 {
    color: #2c5282;
    text-align: center;
    margin-bottom: 30px;
  }

  h2 {
    color: #4a5568;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
  }

  table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 20px;
    background-color: white;
  }

  th,
  td {
    border: 1px solid #e2e8f0;
    padding: 12px;
    text-align: center;
  }

  th {
    background-color: #2c5282;
    color: white;
    font-weight: bold;
  }

  tr:nth-child(even) {
    background-color: #f8fafc;
  }

  .time-slot {
    font-weight: bold;
    color: #2d3748;
    background-color: #edf2f7;
  }

  .class-info {
    margin-bottom: 5px;
  }

  .matiere {
    color: #2c5282;
    font-weight: bold;
    margin-bottom: 5px;
  }

  .professeur {
    color: #805ad5;
    margin-bottom: 3px;
  }

  .salle {
    color: #38a169;
    font-style: italic;
  }

  .empty-slot {
    color: #a0aec0;
    font-style: italic;
  }

  .total-hours {
    text-align: right;
    margin-top: 10px;
    color: #2d3748;
    font-size: 0.9em;
  }

  @media print {
    .schedule-container {
      page-break-after: always;
    }
  }

  button {
    color: white;
    border-radius: 5px;
    border: 0px solid white;
    background-color: #2c5282;
    width: 6rem;
    height: 3rem;
    cursor: pointer;
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
              foreach ($timeSlots as $timeSlot) :
              ?>
                <tr>
                  <td class="time-slot"><?= $timeSlot ?></td>
                  <?php foreach ($data['schedule'] as $day => $slots) : ?>
                    <td class="<?= empty($slots[$timeSlot]) ? 'empty-slot' : '' ?>">
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
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </table>

          </div>
        <?php endforeach; ?>
      </div>

      <!-- section pour les prof -->
      <div id="teachers-section" class="schedule-section table-responsive ">
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
                    <td class="<?= $slots[$timeSlot] === null ? 'empty-slot' : '' ?>">
                      <?php
                      // On va parcourir toutes les classes pour trouver la séance de ce professeur
                      $sessionFound = false;
                      foreach ($allSchedules as $classSchedule) {
                        if (isset($classSchedule['schedule'][$day][$timeSlot])) {
                          $session = $classSchedule['schedule'][$day][$timeSlot];
                          if ($session && $session['professeur'] === $data['nom']) {
                            $sessionFound = true;
                      ?>
                            <div class="class-info">
                              <div class="matiere"><?= htmlspecialchars($session['matiere']) ?></div>
                              <div class="salle"><?= htmlspecialchars($session['salle']) ?></div>
                              <div class="classe"><?= htmlspecialchars($classSchedule['class_name']) ?></div>
                            </div>
                      <?php
                            break;
                          }
                        }
                      }

                      if (!$sessionFound) {
                        echo 'Disponible';
                      }
                      ?>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </table>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- section pour les salles -->
      <div id="rooms-section" class="schedule-section table-responsive ">
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
                    <td class="<?= $slots[$timeSlot] === null ? 'empty-slot' : '' ?>">
                      <?php
                      // On va parcourir toutes les classes pour trouver la séance dans cette salle
                      $sessionFound = false;
                      foreach ($allSchedules as $classSchedule) {
                        if (isset($classSchedule['schedule'][$day][$timeSlot])) {
                          $session = $classSchedule['schedule'][$day][$timeSlot];
                          if ($session && $session['salle'] === $data['nom']) {
                            $sessionFound = true;
                      ?>
                            <div class="class-info">
                              <div class="professeur"><?= htmlspecialchars($session['professeur']) ?></div>
                              <div class="classe"><?= htmlspecialchars($classSchedule['class_name']) ?></div>
                              <div class="matiere"><?= htmlspecialchars($session['matiere']) ?></div>
                            </div>
                      <?php
                            break;
                          }
                        }
                      }

                      if (!$sessionFound) {
                        echo 'Disponible';
                      }
                      ?>
                    </td>
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
  <!--   Core JS Files   -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    // Dézoomer l'écran à 80% (0.8)
    function zoomOutScreen(scale) {
      document.body.style.transform = `scale(${scale})`; // Applique le zoom-out
      document.body.style.transformOrigin = 'top left'; // Définit le point d'origine pour le zoom
      document.body.style.width = `${100 / scale}%`; // Ajuste la largeur pour éviter les barres de défilement
    }

    if (window.innerWidth <= 768) {
      // Appeler la fonction pour dézoomer à 50%
      zoomOutScreen(0.5);
    }
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