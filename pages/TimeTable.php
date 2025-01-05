<?php
class MultiClassScheduleGenerator
{
  private $pdo;
  private $classes = [];
  private $matieres = [];
  private $professeurs = [];
  private $professeursAssignments = [];
  private $professeursTimetable = []; // propriete pour le planning des professeur
  private $salles = [];
  private $sallesTimetable = []; // propriete pour le planning des salles

  // Nouvelles constantes de configuration
  // private const MAX_TEACHER_HOURS_PER_WEEK = 26; // 26h par semaine pour les profs
  // private const MIN_CLASS_HOURS_PER_WEEK = 20;   // Minimum 20h par semaine par classe
  // private const MAX_CLASS_HOURS_PER_WEEK = 30;   // Maximum 30h par semaine par classe
  // private const SESSION_DURATION = 150;          // 2.5h = 150 minutes par seance
  private $max_teacher_hours_per_week;
  private $min_class_hours_per_week;
  private $max_class_hours_per_week;
  private $session_duration = 150;

  private $teacherWeeklyHours = [];
  private $classWeeklyHours = [];

  public function __construct()
  {
    try {
      $this->pdo = new PDO(
        'mysql:host=localhost;dbname=pfe1;charset=utf8',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
      );

      $configQuery = $this->pdo->query("
            SELECT 
                max_enseignant_hours,
                min_class_hours,
                max_class_hours,
                session_duration
            FROM configuration_table
      ");
      $config = $configQuery->fetch(PDO::FETCH_ASSOC);

      // Store as instance properties
      $this->max_teacher_hours_per_week = $config['max_enseignant_hours'];
      $this->min_class_hours_per_week = $config['min_class_hours'];
      $this->max_class_hours_per_week = $config['max_class_hours'];
      $this->session_duration = $config['session_duration'];

      $this->loadData();
      $this->initializeWeeklyHours();
    } catch (PDOException $e) {
      die("Connection failed: " . $e->getMessage());
    }
  }

  private function initializeWeeklyHours()
  {
    foreach ($this->professeurs as $prof) {
      $this->teacherWeeklyHours[$prof['id_enseignant']] = 0;
    }
    foreach ($this->classes as $class) {
      // Assigner un nombre aleatoire d'heures entre MIN et MAX pour chaque classe
      $this->classWeeklyHours[$class['id_classe']] = rand(
        // self::MIN_CLASS_HOURS_PER_WEEK,
        // self::MAX_CLASS_HOURS_PER_WEEK
        $this->min_class_hours_per_week,
        $this->max_class_hours_per_week,
      );
    }
  }


  private function loadData()
  {
    $this->classes = $this->fetchData("SELECT * FROM classe ORDER BY nom_classe");
    $this->matieres = $this->fetchData("SELECT * FROM matiere ORDER BY nom_matiere");
    $this->professeurs = $this->fetchData("SELECT * FROM enseignant ORDER BY nom_enseignant");
    $this->salles = $this->fetchData("SELECT * FROM salle");
    $this->loadProfesseursAssignments();
    $this->loadTeachersTimetable();
  }

  private function fetchData($query)
  {
    return $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
  }

  private function loadProfesseursAssignments()
  {
    $query = "SELECT 
            ecm.*,
            e.nom_enseignant as professeur_nom,
            m.nom_matiere as matiere_nom, 
            c.nom_classe as classe_nom 
            FROM enseignant_classes_matieres ecm
            JOIN enseignant e ON ecm.enseignant_id = e.id_enseignant
            JOIN matiere m ON ecm.matiere_id = m.id_matiere
            JOIN classe c ON ecm.classe_id = c.id_classe";

    $assignments = $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($assignments as $assignment) {
      $key = $assignment['classe_id'] . '-' . $assignment['matiere_id'];
      $this->professeursAssignments[$key] = [
        'enseignant_id' => $assignment['enseignant_id'],
        'professeur_nom' => $assignment['professeur_nom'],
        'matiere_nom' => $assignment['matiere_nom']
      ];
    }
  }

  //! first loadTeachersTimetable
  // private function loadTeachersTimetable()
  // {
  //   $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
  //   $timeSlots = $this->getDailyTimeSlots();
  //   foreach ($this->professeurs as $prof) {
  //     $this->professeursTimetable[$prof['id']] = [];
  //     foreach ($days as $day) {
  //       $this->professeursTimetable[$prof['id']][$day] = array_fill_keys(
  //         array_map(function ($slot) {
  //           return $slot['start'] . '-' . $slot['end'];
  //         }, $timeSlots),
  //         null
  //       );
  //     }
  //   }
  // }

  //! second loadTeachersTimetable
  private function loadTeachersTimetable()
  {
    // $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
    $timeSlots = $this->getDailyTimeSlots();

    // Initialisation du planning des professeurs
    foreach ($this->professeurs as $prof) {
      $this->professeursTimetable[$prof['id_enseignant']] = [];
      foreach ($days as $day) {
        $this->professeursTimetable[$prof['id_enseignant']][$day] = array_fill_keys(
          array_map(function ($slot) {
            return $slot['start'] . '-' . $slot['end'];
          }, $timeSlots),
          null
        );
      }
    }

    // Initialisation du planning des salles
    foreach ($this->salles as $salle) {
      $this->sallesTimetable[$salle['id_salle']] = [];
      foreach ($days as $day) {
        $this->sallesTimetable[$salle['id_salle']][$day] = array_fill_keys(
          array_map(function ($slot) {
            return $slot['start'] . '-' . $slot['end'];
          }, $timeSlots),
          null
        );
      }
    }
  }


  private function getDailyTimeSlots()
  {
    if ($this->session_duration === 150) {
      return [
        ['start' => '08:30', 'end' => '11:00', 'duration' => $this->session_duration],
        ['start' => '11:00', 'end' => '13:30', 'duration' => $this->session_duration],
        ['start' => '13:30', 'end' => '16:00', 'duration' => $this->session_duration],
        ['start' => '16:00', 'end' => '18:30', 'duration' => $this->session_duration]
      ];
    }
    if ($this->session_duration === 120) {
      return [
        ['start' => '08:30', 'end' => '10:30', 'duration' => $this->session_duration],
        ['start' => '10:30', 'end' => '12:30', 'duration' => $this->session_duration],
        ['start' => '14:30', 'end' => '16:30', 'duration' => $this->session_duration],
        ['start' => '16:30', 'end' => '18:30', 'duration' => $this->session_duration]
      ];
    }
  }

  private function canAddTeacherHours($teacherId, $duration)
  {
    $newHours = $this->teacherWeeklyHours[$teacherId] + ($duration / 60);
    // return $newHours <= self::MAX_TEACHER_HOURS_PER_WEEK;
    return $newHours <= $this->max_teacher_hours_per_week;
  }

  private function canAddClassHours($classId, $duration)
  {
    $targetHours = $this->classWeeklyHours[$classId];
    $currentHours = $this->getCurrentClassHours($classId);
    return ($currentHours + ($duration / 60)) <= $targetHours;
  }

  private function getCurrentClassHours($classId)
  {
    // Cette methode calcule les heures actuellement planifiees pour une classe
    // À implementer selon vos besoins specifiques
    return 0; // Pour l'exemple
  }

  private function getAssignedProfesseur($classeId, $matiereId)
  {
    $key = $classeId . '-' . $matiereId;
    return isset($this->professeursAssignments[$key]) ?
      $this->professeursAssignments[$key] : null;
  }

  private function getMatieresForClass($classeId)
  {
    $matieres = [];
    foreach ($this->professeursAssignments as $key => $assignment) {
      list($classId, $matiereId) = explode('-', $key);
      if ($classId == $classeId) {
        $matieres[] = $matiereId;
      }
    }
    return array_unique($matieres);
  }

  private function getRandomItem($array)
  {
    return $array[array_rand($array)];
  }

  private function isTeacherAvailable($teacherId, $day, $timeSlot)
  {
    return $this->professeursTimetable[$teacherId][$day][$timeSlot] === null;
  }

  private function isRoomAvailable($roomId, $usedRooms, $day, $timeSlot)
  {
    return !isset($usedRooms[$day][$timeSlot]) ||
      !in_array($roomId, $usedRooms[$day][$timeSlot]);
  }

  private function findAvailableRoom($usedRooms, $day, $timeSlot)
  {
    foreach ($this->salles as $room) {
      if ($this->isRoomAvailable($room['id_salle'], $usedRooms, $day, $timeSlot)) {
        return $room;
      }
    }
    return null;
  }

  private function bookTeacher($teacherId, $day, $timeSlot)
  {
    $this->professeursTimetable[$teacherId][$day][$timeSlot] = true;
  }

  private function bookRoom($roomId, $usedRooms, $day, $timeSlot)
  {
    if (!isset($usedRooms[$day][$timeSlot])) {
      $usedRooms[$day][$timeSlot] = [];
    }
    $usedRooms[$day][$timeSlot][] = $roomId;
    //! (ligne ajouter) Mettre à jour le planning des salles
    $this->sallesTimetable[$roomId][$day][$timeSlot] = true;
    return $usedRooms;
  }

  public function generateAllSchedules()
  {
    $allSchedules = [];
    $usedRooms = [];
    $timeSlots = $this->getDailyTimeSlots();
    // $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];

    foreach ($this->classes as $class) {
      $schedule = [];
      $classMatieres = $this->getMatieresForClass($class['id_classe']);
      $targetHours = $this->classWeeklyHours[$class['id_classe']];
      $currentHours = 0;

      foreach ($days as $day) {
        $schedule[$day] = [];

        foreach ($timeSlots as $timeSlot) {
          $timeSlotKey = $timeSlot['start'] . '-' . $timeSlot['end'];

          if ($currentHours >= $targetHours) {
            $schedule[$day][$timeSlotKey] = null;
            continue;
          }

          if (rand(1, 100) <= 80 && !empty($classMatieres)) {
            $matiereId = $this->getRandomItem($classMatieres);
            $assignedProf = $this->getAssignedProfesseur($class['id_classe'], $matiereId);

            if (
              $assignedProf &&
              $this->isTeacherAvailable($assignedProf['enseignant_id'], $day, $timeSlotKey) &&
              $this->canAddTeacherHours($assignedProf['enseignant_id'], $this->session_duration) &&
              $this->canAddClassHours($class['id_classe'], $this->session_duration)
            ) {

              $availableRoom = $this->findAvailableRoom($usedRooms, $day, $timeSlotKey);

              if ($availableRoom) {
                $this->bookTeacher($assignedProf['enseignant_id'], $day, $timeSlotKey);
                $usedRooms = $this->bookRoom($availableRoom['id_salle'], $usedRooms, $day, $timeSlotKey);

                $schedule[$day][$timeSlotKey] = [
                  'matiere' => $assignedProf['matiere_nom'],
                  'professeur' => $assignedProf['professeur_nom'],
                  'salle' => $availableRoom['nom_salle'],
                  'duration' => $timeSlot['duration']
                ];

                $currentHours += $timeSlot['duration'] / 60;
                $this->teacherWeeklyHours[$assignedProf['enseignant_id']] += $timeSlot['duration'] / 60;
              }
            }
          }

          if (!isset($schedule[$day][$timeSlotKey])) {
            $schedule[$day][$timeSlotKey] = null;
          }
        }
      }

      $allSchedules[$class['id_classe']] = [
        'class_name' => $class['nom_classe'],
        'target_hours' => $targetHours,
        'actual_hours' => $currentHours,
        'schedule' => $schedule
      ];
    }

    return $allSchedules;
  }

  // all teachers
  public function getTeachersSchedule()
  {
    $teachersSchedule = [];

    foreach ($this->professeurs as $prof) {
      $teachersSchedule[$prof['id_enseignant']] = [
        'nom' => $prof['nom_enseignant'],
        'schedule' => $this->professeursTimetable[$prof['id_enseignant']]
      ];
    }

    return $teachersSchedule;
  }

  public function getRoomsSchedule()
  {
    $roomsSchedule = [];
    foreach ($this->salles as $salle) {
      $roomsSchedule[$salle['id_salle']] = [
        'nom' => $salle['nom_salle'],
        'schedule' => $this->sallesTimetable[$salle['id_salle']]
      ];
    }
    return $roomsSchedule;
  }
}

// Initialize and generate schedules
$generator = new MultiClassScheduleGenerator();
$allSchedules = $generator->generateAllSchedules();
$teachersSchedule = $generator->getTeachersSchedule();
$roomsSchedule = $generator->getRoomsSchedule();

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Emplois du temps - Toutes les classes</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background-color: #f5f5f5;
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
</head>

<body>
  <a href="TimeTableConfig.php">configurer donnes</a>
  <!-- section pour les classes -->
  <div id="classes-section" class="schedule-section active">
    <?php foreach ($allSchedules as $classId => $data) : ?>
      <div class="schedule-container">
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
        <div class="total-hours">
          <?php
          foreach ($data['schedule'] as $day => $slots) {
            $totalHours = array_reduce($slots, function ($carry, $slot) {
              return $carry + ($slot ? $slot['duration'] / 60 : 0);
            }, 0);
            echo "$day: " . number_format($totalHours, 1) . " heures<br>";
          }
          ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- section pour les prof -->
  <div id="teachers-section" class="schedule-section">
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
  <div id="rooms-section" class="schedule-section">
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

  <button id="imprimer">Imprimer</button>

  <script>
    document.getElementById('imprimer').addEventListener('click', () => {
      window.print();
    })
  </script>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#teacher-select').change(function() {
        var teacherName = $(this).val();

        if (teacherName) {
          $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
              action: 'get_teacher_schedule',
              teacher_name: teacherName
            },
            success: function(response) {
              $('#teacher-schedule-container').html(response);
            },
            error: function() {
              $('#teacher-schedule-container').html('Erreur lors du chargement de l\'emploi du temps.');
            }
          });
        } else {
          $('#teacher-schedule-container').empty();
        }
      });
    });
  </script>
</body>

</html>