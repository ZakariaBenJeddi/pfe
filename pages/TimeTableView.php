<?php
session_start();

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
    $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
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
        ['start' => '12:30', 'end' => '16:30', 'duration' => $this->session_duration],
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
    $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

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
<html lang="en">
<!-- HEAD -->
<?php include '../includes/head.php' ?>
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

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href=" https://demos.creative-tim.com/argon-dashboard/pages/dashboard.html " target="_blank">
        <img src="https://elaraki.ac.ma/images/logo2.png" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold">
          <?= strtoupper($_SESSION['nom_admin'] . " " . $_SESSION['prenom_admin'])  ?>
        </span>

      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <!-- Section Dashboard -->
        <li class="nav-item">
          <a class="nav-link active" href="../pages/dashboard.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>

        <!-- Section Gestion des utilisateurs -->
        <li class="nav-item">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestion des Utilisateurs</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/eleves.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-hat-3 text-success text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Élèves</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/enseignant.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Enseignants</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/administration.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-badge text-info text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Administration</span>
          </a>
        </li>

        <!-- Section Gestion pédagogique -->
        <li class="nav-item">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestion Pédagogique</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/niveau.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fa-solid fa-layer-group text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Niveau</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/classes.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-building text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Classes</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/filiere.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-books text-info text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Filière</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/matiere.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-book-bookmark text-danger text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Matières</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/calendrier.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Emplois du Temps</span>
          </a>
        </li>

        <!-- Section Suivi -->
        <li class="nav-item">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Suivi</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/absences.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-user-run text-danger text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Absences</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/evaluations.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-chart-bar-32 text-success text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Évaluations</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/bulletins.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-folder-17 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Bulletins</span>
          </a>
        </li>

        <!-- Section Gestion des ressources -->
        <li class="nav-item">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestion des Ressources</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/salle.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-building text-info text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Salles</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/equipements.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-laptop text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Équipements</span>
          </a>
        </li>

        <!-- Section Comptabilité -->
        <li class="nav-item">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Comptabilité</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/payements.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-credit-card text-success text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Paiements</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/frais-scolarite.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-money-coins text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Frais de scolarité</span>
          </a>
        </li>

        <!-- Section Compte -->
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Mon Compte</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/profile.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Profil</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/sign-out.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-button-power text-danger text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Déconnexion</span>
          </a>
        </li>
      </ul>
    </div>
    <div class="sidenav-footer mx-3 ">
      <div class="card card-plain shadow-none" id="sidenavCard">
        <!-- <img class="w-50 mx-auto" src="../assets/img/illustrations/icon-documentation.svg" alt="sidebar_illustration"> -->
        <img class="w-50 mx-auto mt-5" src="https://elaraki.ac.ma/images/logo2.png" alt="sidebar_illustration">
        <div class="card-body text-center p-3 w-100 pt-0">
          <div class="docs-info">
            <h6 class="mb-0">ELARAKI School</h6>
            <p class="text-xs font-weight-bold mb-0">International School of Morocco</p>
          </div>
        </div>
      </div>
      <!-- <a href="https://www.creative-tim.com/learning-lab/bootstrap/license/argon-dashboard" target="_blank" class="btn btn-dark btn-sm w-100 mb-3">Documentation</a>
      <a class="btn btn-primary btn-sm mb-0 w-100" href="https://www.creative-tim.com/product/argon-dashboard-pro?ref=sidebarfree" type="button">Upgrade to pro</a> -->
    </div>
  </aside>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-white active" aria-current="page">Tables</li>
          </ol>
          <h6 class="font-weight-bolder text-white mb-0">Tables</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <div class="input-group">
              <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
              <input type="text" class="form-control" placeholder="Type here...">
            </div>
          </div>
          <ul class="navbar-nav  justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-white font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
                <span class="d-sm-inline d-none">Sign In</span>
              </a>
            </li>
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line bg-white"></i>
                  <i class="sidenav-toggler-line bg-white"></i>
                  <i class="sidenav-toggler-line bg-white"></i>
                </div>
              </a>
            </li>
            <li class="nav-item px-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-white p-0">
                <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
              </a>
            </li>
            <li class="nav-item dropdown pe-2 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell cursor-pointer"></i>
              </a>
              <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                <li>
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                      <div class="avatar avatar-sm bg-gradient-secondary  me-3  my-auto">
                        <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                          <title>credit-card</title>
                          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF" fill-rule="nonzero">
                              <g transform="translate(1716.000000, 291.000000)">
                                <g transform="translate(453.000000, 454.000000)">
                                  <path class="color-background" d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z" opacity="0.593633743"></path>
                                  <path class="color-background" d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z"></path>
                                </g>
                              </g>
                            </g>
                          </g>
                        </svg>
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                          Payment successfully completed
                        </h6>
                        <p class="text-xs text-secondary mb-0">
                          <i class="fa fa-clock me-1"></i>
                          2 days
                        </p>
                      </div>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- End Navbar -->
    <div class=" pb-0 mt-5 me-5 text-end text-primary">
      <a href="TimeTableInfo.php" class="btn btn-light px-3">configurer donnes</a>
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
    <?php include '../includes/footer.php' ?>

    <!-- </div> -->
  </main>
  <!-- FIXED PLUGIN  -->
  <?php include '../includes/fixedplugin.php' ?>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>