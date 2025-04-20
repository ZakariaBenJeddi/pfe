<?php
class TimeTableData
{
  private $pdo;
  private $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
  private $timeSlots = [
    '08:30 - 09:30',
    '09:30 - 10:30',
    '10:30 - 11:30',
    '11:30 - 12:30',
    '14:30 - 15:30',
    '15:30 - 16:30',
    '16:30 - 17:30',
    '17:30 - 18:30'
  ];

  public function __construct()
  {
    $filePath = __DIR__ . '/../../DatabaseConnexion.php';
    if (file_exists($filePath)) {
      require($filePath);
      $this->pdo = $dbh; // On utilise la connexion du fichier externe
    } else {
      throw new Exception("Fichier de connexion introuvable : $filePath");
    }
  }

  // Récupérer tous les emplois du temps
  public function getAllSchedules()
  {
    $allSchedules = [];

    $query = $this->pdo->query("SELECT DISTINCT classe FROM timetable ORDER BY classe");
    $classes = $query->fetchAll(PDO::FETCH_COLUMN);

    foreach ($classes as $class) {
      $schedule = $this->getClassSchedule($class);
      $allSchedules[$class] = [
        'class_name' => $class,
        'schedule' => $schedule
      ];
    }

    return $allSchedules;
  }

  // Initialiser un emploi du temps vide avec tous les créneaux
  private function initializeEmptySchedule()
  {
    $schedule = [];
    foreach ($this->days as $day) {
      $schedule[$day] = [];
      foreach ($this->timeSlots as $timeSlot) {
        $schedule[$day][$timeSlot] = null;
      }
    }
    return $schedule;
  }

  // Récupérer l'emploi du temps d'une classe
    public function getClassSchedule($className)
  {
    $schedule = $this->initializeEmptySchedule();

    $query = $this->pdo->prepare("
        SELECT description, professeur, matiere, classe, salle,
        DATE(start_datetime) as date,
        TIME(start_datetime) as start_time,
        TIME(end_datetime) as end_time
        FROM timetable 
        WHERE classe = :classe 
        ORDER BY start_datetime
    ");

    $query->execute([':classe' => $className]);
    $events = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $event) {
      $day = date('l', strtotime($event['date']));
      $day = $this->getDayFrench($day);
      
      // Déterminer les créneaux couverts par cet événement
      $eventStartTime = substr($event['start_time'], 0, 5);
      $eventEndTime = substr($event['end_time'], 0, 5);
      
      $coveredSlots = [];
      foreach ($this->timeSlots as $timeSlot) {
        list($slotStartTime, $slotEndTime) = explode(' - ', $timeSlot);
        
        // Si ce créneau est couvert par l'événement, l'ajouter à la liste
        if (($slotStartTime >= $eventStartTime && $slotStartTime < $eventEndTime) ||
            ($slotEndTime > $eventStartTime && $slotEndTime <= $eventEndTime) ||
            ($slotStartTime <= $eventStartTime && $slotEndTime >= $eventEndTime)) {
          $coveredSlots[] = $timeSlot;
        }
      }
      
      // Si l'événement couvre au moins un créneau
      if (!empty($coveredSlots)) {
        // Stocker les informations sur l'événement et les créneaux qu'il couvre
        $eventInfo = [
          'professeur' => $event['professeur'],
          'matiere' => $event['matiere'],
          'salle' => $event['salle'],
          'description' => $event['description'],
          'start_time' => $eventStartTime,
          'end_time' => $eventEndTime,
          'rowspan' => count($coveredSlots) // Pour l'affichage HTML
        ];
        
        // Marquer le premier créneau avec les informations complètes
        $firstSlot = reset($coveredSlots);
        $schedule[$day][$firstSlot] = $eventInfo;
        
        // Marquer les créneaux suivants comme occupés par cet événement
        $isFirst = true;
        foreach ($coveredSlots as $slot) {
          if ($isFirst) {
            $isFirst = false;
            continue; // Déjà traité
          }
          $schedule[$day][$slot] = 'occupied_by_previous';
        }
      }
    }

    return $schedule;
  }

  // Récupérer l'emploi du temps des professeurs
  public function getTeachersSchedule()
  {
    $teachersSchedule = [];

    $query = $this->pdo->query("SELECT DISTINCT professeur FROM timetable ORDER BY professeur");
    $teachers = $query->fetchAll(PDO::FETCH_COLUMN);

    foreach ($teachers as $teacher) {
      $schedule = $this->getTeacherSchedule($teacher);
      $teachersSchedule[$teacher] = [
        'nom' => $teacher,
        'schedule' => $schedule
      ];
    }

    return $teachersSchedule;
  }

  // Récupérer l'emploi du temps d'un professeur
  private function getTeacherSchedule($teacherName)
  {
    $schedule = $this->initializeEmptySchedule();

    $query = $this->pdo->prepare("
          SELECT description, matiere, classe, salle,
                DATE(start_datetime) as date,
                TIME(start_datetime) as start_time,
                TIME(end_datetime) as end_time
          FROM timetable 
          WHERE professeur = :professeur 
          ORDER BY start_datetime
      ");

    $query->execute([':professeur' => $teacherName]);
    $events = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $event) {
      $day = date('l', strtotime($event['date']));
      $day = $this->getDayFrench($day);
      
      // Déterminer les créneaux couverts par cet événement
      $eventStartTime = substr($event['start_time'], 0, 5);
      $eventEndTime = substr($event['end_time'], 0, 5);
      
      $coveredSlots = [];
      foreach ($this->timeSlots as $timeSlot) {
        list($slotStartTime, $slotEndTime) = explode(' - ', $timeSlot);
        
        // Si ce créneau est couvert par l'événement, l'ajouter à la liste
        if (($slotStartTime >= $eventStartTime && $slotStartTime < $eventEndTime) ||
            ($slotEndTime > $eventStartTime && $slotEndTime <= $eventEndTime) ||
            ($slotStartTime <= $eventStartTime && $slotEndTime >= $eventEndTime)) {
          $coveredSlots[] = $timeSlot;
        }
      }
      
      // Si l'événement couvre au moins un créneau
      if (!empty($coveredSlots)) {
        // Stocker les informations sur l'événement et les créneaux qu'il couvre
        $eventInfo = [
          'classe' => $event['classe'],
          'matiere' => $event['matiere'],
          'salle' => $event['salle'],
          'description' => $event['description'],
          'start_time' => $eventStartTime,
          'end_time' => $eventEndTime,
          'rowspan' => count($coveredSlots) // Pour l'affichage HTML
        ];
        
        // Marquer le premier créneau avec les informations complètes
        $firstSlot = reset($coveredSlots);
        $schedule[$day][$firstSlot] = $eventInfo;
        
        // Marquer les créneaux suivants comme occupés par cet événement
        $isFirst = true;
        foreach ($coveredSlots as $slot) {
          if ($isFirst) {
            $isFirst = false;
            continue; // Déjà traité
          }
          $schedule[$day][$slot] = 'occupied_by_previous';
        }
      }
    }

    return $schedule;
  }

  // Récupérer l'emploi du temps des salles
  public function getRoomsSchedule()
  {
    $roomsSchedule = [];

    $query = $this->pdo->query("SELECT DISTINCT salle FROM timetable ORDER BY salle");
    $rooms = $query->fetchAll(PDO::FETCH_COLUMN);

    foreach ($rooms as $room) {
      $schedule = $this->getRoomSchedule($room);
      $roomsSchedule[$room] = [
        'nom' => $room,
        'schedule' => $schedule
      ];
    }

    return $roomsSchedule;
  }

  // Récupérer l'emploi du temps d'une salle
  private function getRoomSchedule($roomName)
  {
    $schedule = $this->initializeEmptySchedule();

    $query = $this->pdo->prepare("
          SELECT description, professeur, matiere, classe,
                DATE(start_datetime) as date,
                TIME(start_datetime) as start_time,
                TIME(end_datetime) as end_time
          FROM timetable 
          WHERE salle = :salle 
          ORDER BY start_datetime
      ");

    $query->execute([':salle' => $roomName]);
    $events = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $event) {
      $day = date('l', strtotime($event['date']));
      $day = $this->getDayFrench($day);
      
      // Déterminer les créneaux couverts par cet événement
      $eventStartTime = substr($event['start_time'], 0, 5);
      $eventEndTime = substr($event['end_time'], 0, 5);
      
      $coveredSlots = [];
      foreach ($this->timeSlots as $timeSlot) {
        list($slotStartTime, $slotEndTime) = explode(' - ', $timeSlot);
        
        // Si ce créneau est couvert par l'événement, l'ajouter à la liste
        if (($slotStartTime >= $eventStartTime && $slotStartTime < $eventEndTime) ||
            ($slotEndTime > $eventStartTime && $slotEndTime <= $eventEndTime) ||
            ($slotStartTime <= $eventStartTime && $slotEndTime >= $eventEndTime)) {
          $coveredSlots[] = $timeSlot;
        }
      }
      
      // Si l'événement couvre au moins un créneau
      if (!empty($coveredSlots)) {
        // Stocker les informations sur l'événement et les créneaux qu'il couvre
        $eventInfo = [
          'professeur' => $event['professeur'],
          'classe' => $event['classe'],
          'matiere' => $event['matiere'],
          'description' => $event['description'],
          'start_time' => $eventStartTime,
          'end_time' => $eventEndTime,
          'rowspan' => count($coveredSlots) // Pour l'affichage HTML
        ];
        
        // Marquer le premier créneau avec les informations complètes
        $firstSlot = reset($coveredSlots);
        $schedule[$day][$firstSlot] = $eventInfo;
        
        // Marquer les créneaux suivants comme occupés par cet événement
        $isFirst = true;
        foreach ($coveredSlots as $slot) {
          if ($isFirst) {
            $isFirst = false;
            continue; // Déjà traité
          }
          $schedule[$day][$slot] = 'occupied_by_previous';
        }
      }
    }

    return $schedule;
  }

  public function getSpecificTeacherSchedule($teacherName, $weekStart = null, $weekEnd = null)
  {
    $schedule = $this->initializeEmptySchedule();

    $query = "SELECT description, matiere, classe, salle,
              DATE(start_datetime) as date,
              TIME(start_datetime) as start_time,
              TIME(end_datetime) as end_time
              FROM timetable 
              WHERE professeur = :professeur";

    $params = [':professeur' => $teacherName];

    if ($weekStart && $weekEnd) {
      $query .= " AND DATE(start_datetime) BETWEEN :start_date AND :end_date";
      $params[':start_date'] = $weekStart;
      $params[':end_date'] = $weekEnd;
    }

    $query .= " ORDER BY start_datetime";

    $stmt = $this->pdo->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $event) {
      $day = $this->getDayFrench(date('l', strtotime($event['date'])));
      
      // Déterminer les créneaux couverts par cet événement
      $eventStartTime = substr($event['start_time'], 0, 5);
      $eventEndTime = substr($event['end_time'], 0, 5);
      
      $coveredSlots = [];
      foreach ($this->timeSlots as $timeSlot) {
        list($slotStartTime, $slotEndTime) = explode(' - ', $timeSlot);
        
        // Si ce créneau est couvert par l'événement, l'ajouter à la liste
        if (($slotStartTime >= $eventStartTime && $slotStartTime < $eventEndTime) ||
            ($slotEndTime > $eventStartTime && $slotEndTime <= $eventEndTime) ||
            ($slotStartTime <= $eventStartTime && $slotEndTime >= $eventEndTime)) {
          $coveredSlots[] = $timeSlot;
        }
      }
      
      // Si l'événement couvre au moins un créneau
      if (!empty($coveredSlots)) {
        // Stocker les informations sur l'événement et les créneaux qu'il couvre
        $eventInfo = [
          'classe' => $event['classe'],
          'matiere' => $event['matiere'],
          'salle' => $event['salle'],
          'description' => $event['description'],
          'start_time' => $eventStartTime,
          'end_time' => $eventEndTime,
          'rowspan' => count($coveredSlots) // Pour l'affichage HTML
        ];
        
        // Marquer le premier créneau avec les informations complètes
        $firstSlot = reset($coveredSlots);
        $schedule[$day][$firstSlot] = $eventInfo;
        
        // Marquer les créneaux suivants comme occupés par cet événement
        $isFirst = true;
        foreach ($coveredSlots as $slot) {
          if ($isFirst) {
            $isFirst = false;
            continue; // Déjà traité
          }
          $schedule[$day][$slot] = 'occupied_by_previous';
        }
      }
    }

    $scheduleHtml = "<div class='schedule-container'>";
    $scheduleHtml .= "<h2>Emploi du temps : " . htmlspecialchars($teacherName) . "</h2>";
    if ($weekStart && $weekEnd) {
      $scheduleHtml .= "<h4>Semaine du " . date('d/m/Y', strtotime($weekStart)) .
        " au " . date('d/m/Y', strtotime($weekEnd)) . "</h4>";
    }

    $scheduleHtml .= "<table class='schedule-table'><tr><th>Horaire</th>";
    foreach ($this->days as $day) {
      $scheduleHtml .= "<th>" . htmlspecialchars($day) . "</th>";
    }
    $scheduleHtml .= "</tr>";

    foreach ($this->timeSlots as $timeSlot) {
      $scheduleHtml .= "<tr><td class='time-slot'>" . htmlspecialchars($timeSlot) . "</td>";
      foreach ($this->days as $day) {
        if (isset($schedule[$day][$timeSlot]) && $schedule[$day][$timeSlot] !== 'occupied_by_previous') {
          $session = $schedule[$day][$timeSlot];
          $rowspan = isset($session['rowspan']) ? $session['rowspan'] : 1;
          $scheduleHtml .= "<td class='session-info' rowspan='" . $rowspan . "'>";
          $scheduleHtml .= "<div class='classe'>" . htmlspecialchars($session['classe']) . "</div>";
          $scheduleHtml .= "<div class='matiere'>" . htmlspecialchars($session['matiere']) . "</div>";
          $scheduleHtml .= "<div class='salle'>Salle " . htmlspecialchars($session['salle']) . "</div>";
          $scheduleHtml .= "</td>";
        } elseif (isset($schedule[$day][$timeSlot]) && $schedule[$day][$timeSlot] === 'occupied_by_previous') {
          // Ne rien afficher, cette cellule est déjà couverte par un rowspan
        } else {
          $scheduleHtml .= "<td class='empty-slot'>Libre</td>";
        }
      }
      $scheduleHtml .= "</tr>";
    }

    $scheduleHtml .= "</table></div>";
    return $scheduleHtml;
  }

  public function getSpecificGroupSchedule($groupName, $weekStart = null, $weekEnd = null)
  {
    $schedule = $this->initializeEmptySchedule();

    $query = "SELECT description, professeur, matiere, salle,
                    DATE(start_datetime) as date,
                    TIME(start_datetime) as start_time,
                    TIME(end_datetime) as end_time
              FROM timetable 
              WHERE classe = :classe";

    $params = [':classe' => $groupName];

    if ($weekStart && $weekEnd) {
      $query .= " AND DATE(start_datetime) BETWEEN :start_date AND :end_date";
      $params[':start_date'] = $weekStart;
      $params[':end_date'] = $weekEnd;
    }

    $query .= " ORDER BY start_datetime";

    $stmt = $this->pdo->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $event) {
      $day = $this->getDayFrench(date('l', strtotime($event['date'])));
      
      // Déterminer les créneaux couverts par cet événement
      $eventStartTime = substr($event['start_time'], 0, 5);
      $eventEndTime = substr($event['end_time'], 0, 5);
      
      $coveredSlots = [];
      foreach ($this->timeSlots as $timeSlot) {
        list($slotStartTime, $slotEndTime) = explode(' - ', $timeSlot);
        
        // Si ce créneau est couvert par l'événement, l'ajouter à la liste
        if (($slotStartTime >= $eventStartTime && $slotStartTime < $eventEndTime) ||
            ($slotEndTime > $eventStartTime && $slotEndTime <= $eventEndTime) ||
            ($slotStartTime <= $eventStartTime && $slotEndTime >= $eventEndTime)) {
          $coveredSlots[] = $timeSlot;
        }
      }
      
      // Si l'événement couvre au moins un créneau
      if (!empty($coveredSlots)) {
        // Stocker les informations sur l'événement et les créneaux qu'il couvre
        $eventInfo = [
          'professeur' => $event['professeur'],
          'matiere' => $event['matiere'],
          'salle' => $event['salle'],
          'description' => $event['description'],
          'start_time' => $eventStartTime,
          'end_time' => $eventEndTime,
          'rowspan' => count($coveredSlots) // Pour l'affichage HTML
        ];
        
        // Marquer le premier créneau avec les informations complètes
        $firstSlot = reset($coveredSlots);
        $schedule[$day][$firstSlot] = $eventInfo;
        
        // Marquer les créneaux suivants comme occupés par cet événement
        $isFirst = true;
        foreach ($coveredSlots as $slot) {
          if ($isFirst) {
            $isFirst = false;
            continue; // Déjà traité
          }
          $schedule[$day][$slot] = 'occupied_by_previous';
        }
      }
    }

    $scheduleHtml = "<div class='schedule-container'>";
    $scheduleHtml .= "<h2>Emploi du temps : " . htmlspecialchars($groupName) . "</h2>";
    if ($weekStart && $weekEnd) {
      $scheduleHtml .= "<h4>Semaine du " . date('d/m/Y', strtotime($weekStart)) .
        " au " . date('d/m/Y', strtotime($weekEnd)) . "</h4>";
    }

    $scheduleHtml .= "<table class='schedule-table'><tr><th>Horaire</th>";
    foreach ($this->days as $day) {
      $scheduleHtml .= "<th>" . htmlspecialchars($day) . "</th>";
    }
    $scheduleHtml .= "</tr>";

    foreach ($this->timeSlots as $timeSlot) {
      $scheduleHtml .= "<tr><td class='time-slot'>" . htmlspecialchars($timeSlot) . "</td>";
      foreach ($this->days as $day) {
        if (isset($schedule[$day][$timeSlot]) && $schedule[$day][$timeSlot] !== 'occupied_by_previous') {
          $session = $schedule[$day][$timeSlot];
          $rowspan = isset($session['rowspan']) ? $session['rowspan'] : 1;
          $scheduleHtml .= "<td class='session-info' rowspan='" . $rowspan . "'>";
          $scheduleHtml .= "<div class='professeur'>" . htmlspecialchars($session['professeur']) . "</div>";
          $scheduleHtml .= "<div class='matiere'>" . htmlspecialchars($session['matiere']) . "</div>";
          $scheduleHtml .= "<div class='salle'>Salle " . htmlspecialchars($session['salle']) . "</div>";
          $scheduleHtml .= "</td>";
        } elseif (isset($schedule[$day][$timeSlot]) && $schedule[$day][$timeSlot] === 'occupied_by_previous') {
          // Ne rien afficher, cette cellule est déjà couverte par un rowspan
        } else {
          $scheduleHtml .= "<td class='empty-slot'>Libre</td>";
        }
      }
      $scheduleHtml .= "</tr>";
    }

    $scheduleHtml .= "</table></div>";
    return $scheduleHtml;
  }

  public function getSpecificRoomSchedule($roomName, $weekStart = null, $weekEnd = null)
  {
    $schedule = $this->initializeEmptySchedule();

    $query = "SELECT description, professeur, matiere, classe,
              DATE(start_datetime) as date,
              TIME(start_datetime) as start_time,
              TIME(end_datetime) as end_time
              FROM timetable 
              WHERE salle = :salle";

    $params = [':salle' => $roomName];

    if ($weekStart && $weekEnd) {
      $query .= " AND DATE(start_datetime) BETWEEN :start_date AND :end_date";
      $params[':start_date'] = $weekStart;
      $params[':end_date'] = $weekEnd;
    }

    $query .= " ORDER BY start_datetime";

    $stmt = $this->pdo->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $event) {
      $day = $this->getDayFrench(date('l', strtotime($event['date'])));
      
      // Déterminer les créneaux couverts par cet événement
      $eventStartTime = substr($event['start_time'], 0, 5);
      $eventEndTime = substr($event['end_time'], 0, 5);
      
      $coveredSlots = [];
      foreach ($this->timeSlots as $timeSlot) {
        list($slotStartTime, $slotEndTime) = explode(' - ', $timeSlot);
        
        // Si ce créneau est couvert par l'événement, l'ajouter à la liste
        if (($slotStartTime >= $eventStartTime && $slotStartTime < $eventEndTime) ||
            ($slotEndTime > $eventStartTime && $slotEndTime <= $eventEndTime) ||
            ($slotStartTime <= $eventStartTime && $slotEndTime >= $eventEndTime)) {
          $coveredSlots[] = $timeSlot;
        }
      }
      
      // Si l'événement couvre au moins un créneau
      if (!empty($coveredSlots)) {
        // Stocker les informations sur l'événement et les créneaux qu'il couvre
        $eventInfo = [
          'professeur' => $event['professeur'],
          'classe' => $event['classe'],
          'matiere' => $event['matiere'],
          'description' => $event['description'],
          'start_time' => $eventStartTime,
          'end_time' => $eventEndTime,
          'rowspan' => count($coveredSlots) // Pour l'affichage HTML
        ];
        
        // Marquer le premier créneau avec les informations complètes
        $firstSlot = reset($coveredSlots);
        $schedule[$day][$firstSlot] = $eventInfo;
        
        // Marquer les créneaux suivants comme occupés par cet événement
        $isFirst = true;
        foreach ($coveredSlots as $slot) {
          if ($isFirst) {
            $isFirst = false;
            continue; // Déjà traité
          }
          $schedule[$day][$slot] = 'occupied_by_previous';
        }
      }
    }

    $scheduleHtml = "<div class='schedule-container'>";
    $scheduleHtml .= "<h2>Emploi du temps : Salle " . htmlspecialchars($roomName) . "</h2>";
    if ($weekStart && $weekEnd) {
      $scheduleHtml .= "<h4>Semaine du " . date('d/m/Y', strtotime($weekStart)) .
        " au " . date('d/m/Y', strtotime($weekEnd)) . "</h4>";
    }

    $scheduleHtml .= "<table class='schedule-table'><tr><th>Horaire</th>";
    foreach ($this->days as $day) {
      $scheduleHtml .= "<th>" . htmlspecialchars($day) . "</th>";
    }
    $scheduleHtml .= "</tr>";

    foreach ($this->timeSlots as $timeSlot) {
      $scheduleHtml .= "<tr><td class='time-slot'>" . htmlspecialchars($timeSlot) . "</td>";
      foreach ($this->days as $day) {
        if (isset($schedule[$day][$timeSlot]) && $schedule[$day][$timeSlot] !== 'occupied_by_previous') {
          $session = $schedule[$day][$timeSlot];
          $rowspan = isset($session['rowspan']) ? $session['rowspan'] : 1;
          $scheduleHtml .= "<td class='session-info' rowspan='" . $rowspan . "'>";
          $scheduleHtml .= "<div class='professeur'>" . htmlspecialchars($session['professeur']) . "</div>";
          $scheduleHtml .= "<div class='classe'>" . htmlspecialchars($session['classe']) . "</div>";
          $scheduleHtml .= "<div class='matiere'>" . htmlspecialchars($session['matiere']) . "</div>";
          $scheduleHtml .= "</td>";
        } elseif (isset($schedule[$day][$timeSlot]) && $schedule[$day][$timeSlot] === 'occupied_by_previous') {
          // Ne rien afficher, cette cellule est déjà couverte par un rowspan
        } else {
          $scheduleHtml .= "<td class='empty-slot'>Libre</td>";
        }
      }
      $scheduleHtml .= "</tr>";
    }

    $scheduleHtml .= "</table></div>";
    return $scheduleHtml;
  }

  private function formatTimeSlot($startTime, $endTime)
  {
    $startHour = substr($startTime, 0, 5);
    $endHour = substr($endTime, 0, 5);
    
    // Trouver le(s) créneau(x) correspondant(s)
    foreach ($this->timeSlots as $timeSlot) {
      list($slotStart, $slotEnd) = explode(' - ', $timeSlot);
      if ($slotStart == $startHour && $slotEnd == $endHour) {
        return $timeSlot;
      }
    }
    
    // Si on ne trouve pas de correspondance exacte, renvoyer un format générique
    return $startHour . ' - ' . $endHour;
  }

  // Convertir les jours en français
  private function getDayFrench($englishDay)
  {
    $dayMapping = [
      'Monday' => 'Lundi',
      'Tuesday' => 'Mardi',
      'Wednesday' => 'Mercredi',
      'Thursday' => 'Jeudi',
      'Friday' => 'Vendredi'
    ];
    return $dayMapping[$englishDay] ?? $englishDay;
  }

  // Getters pour les propriétés privées
  public function getDays()
  {
    return $this->days;
  }

  public function getTimeSlots()
  {
    return $this->timeSlots;
  }
}