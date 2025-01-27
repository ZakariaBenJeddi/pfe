<?php


class TimeTableData
{
  private $pdo;
  private $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
  private $timeSlots = [
    '08:30 - 10:30',
    '10:30 - 12:30',
    '14:30 - 16:30',
    '16:30 - 18:30'
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
      $timeSlot = $this->formatTimeSlot($event['start_time'], $event['end_time']);

      if (in_array($timeSlot, $this->timeSlots)) {
        $schedule[$day][$timeSlot] = [
          'professeur' => $event['professeur'],
          'matiere' => $event['matiere'],
          'salle' => $event['salle'],
          'description' => $event['description']
        ];
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
      $timeSlot = $this->formatTimeSlot($event['start_time'], $event['end_time']);

      if (in_array($timeSlot, $this->timeSlots)) {
        $schedule[$day][$timeSlot] = [
          'classe' => $event['classe'],
          'matiere' => $event['matiere'],
          'salle' => $event['salle'],
          'description' => $event['description']
        ];
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
      $timeSlot = $this->formatTimeSlot($event['start_time'], $event['end_time']);

      if (in_array($timeSlot, $this->timeSlots)) {
        $schedule[$day][$timeSlot] = [
          'professeur' => $event['professeur'],
          'classe' => $event['classe'],
          'matiere' => $event['matiere'],
          'description' => $event['description']
        ];
      }
    }

    return $schedule;
  }

  // Formater le créneau horaire
  private function formatTimeSlot($startTime, $endTime)
  {
    return substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5);
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