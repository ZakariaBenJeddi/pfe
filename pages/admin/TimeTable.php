<?php

class MultiClassScheduleGenerator
{
  private $pdo;
  private $classes = [];
  private $matieres = [];
  private $professeurs = [];
  private $professeursAssignments = [];
  private $professeursTimetable = [];
  private $salles = [];
  private $sallesTimetable = [];
  private $filieres = [];
  private $matiereConstraints = [];

  // Configuration properties
  private $max_teacher_hours_per_week;
  private $min_class_hours_per_week;
  private $max_class_hours_per_week;
  private $session_duration = 150;


  private $teacherWeeklyHours = [];
  private $classWeeklyHours = [];

  // New properties for tracking
  private $matiereDailyCount = [];
  private $dailyWorkload = [];
  private $weeklyMatiereCount = [];

  public function __construct()
  {
    try {
      $this->pdo = new PDO(
        'mysql:host=localhost;dbname=pfe1;charset=utf8',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
      );

      // Load configuration
      $this->loadConfiguration();
      // Load all required data
      $this->loadData();
      // Initialize tracking arrays
      $this->initializeTrackingArrays();
    } catch (PDOException $e) {
      die("Connection failed: " . $e->getMessage());
    }
  }

  private function loadConfiguration()
  {
    $configQuery = $this->pdo->query("
            SELECT 
                max_enseignant_hours,
                min_class_hours,
                max_class_hours,
                session_duration
            FROM configuration_table
        ");
    $config = $configQuery->fetch(PDO::FETCH_ASSOC);
    $this->max_teacher_hours_per_week = $config['max_enseignant_hours'];
    $this->min_class_hours_per_week = $config['min_class_hours'];
    $this->max_class_hours_per_week = $config['max_class_hours'];
    $this->session_duration = $config['session_duration'];
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
    $this->classes = $this->fetchData("SELECT c.*, f.id_filiere FROM classe c JOIN filiere f ON c.filiere_id = f.id_filiere ORDER BY nom_classe");
    $this->matieres = $this->fetchData("SELECT * FROM matiere ORDER BY nom_matiere");
    $this->professeurs = $this->fetchData("SELECT * FROM enseignant ORDER BY nom_enseignant");
    $this->salles = $this->fetchData("SELECT * FROM salle");
    $this->filieres = $this->fetchData("SELECT * FROM filiere");

    $this->loadProfesseursAssignments();
    $this->loadMatiereConstraints();
    $this->loadTeachersTimetable();
  }


  private function loadMatiereConstraints()
  {
    $query = "SELECT m.*, f.nom_filiere 
                 FROM matiere m 
                 JOIN filiere f ON m.id_filiere = f.id_filiere";
    $constraints = $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($constraints as $constraint) {
      $key = $constraint['id_filiere'] . '-' . $constraint['id_matiere'];
      $this->matiereConstraints[$key] = [
        'nombre_seance_semaine' => $constraint['nombre_seance_semaine'],
        'volume_horaire' => $constraint['volume_horaire'],
        'coefficient' => $constraint['coefficient']
      ];
    }
  }


  public function testConstraintLoading()
  {
    // Print all loaded constraints
    echo "Loaded Matiere Constraints:<br>";
    foreach ($this->matiereConstraints as $key => $constraint) {
      echo "Key: $key, Sessions per week: {$constraint['nombre_seance_semaine']}<br>";
    }
  }

  public function testScheduleGeneration()
  {
    // Test constraint loading
    $this->testConstraintLoading();

    // Generate schedules with fixed constraints
    $allSchedules = $this->generateAllSchedules();

    // Debug output for all classes and their subjects
    foreach ($allSchedules as $classId => $classSchedule) {
      echo "<h2>Schedule for {$classSchedule['class_name']}</h2>";

      // Track sessions by subject
      $sessionsBySubject = [];

      foreach ($classSchedule['schedule'] as $day => $slots) {
        foreach ($slots as $timeSlot => $session) {
          if ($session && isset($session['matiere'])) {
            $matiere = $session['matiere'];
            if (!isset($sessionsBySubject[$matiere])) {
              $sessionsBySubject[$matiere] = 0;
            }
            $sessionsBySubject[$matiere]++;

            echo "Session: $matiere on $day at $timeSlot<br>";
          }
        }
      }

      echo "<strong>Subject summary:</strong><br>";
      foreach ($sessionsBySubject as $matiere => $count) {
        echo "$matiere: $count sessions<br>";
      }
      echo "<hr>";
    }

    return $allSchedules;
  }


  private function initializeTrackingArrays()
  {
    $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];

    foreach ($this->classes as $class) {
      $this->matiereDailyCount[$class['id_classe']] = array_fill_keys($days, []);
      $this->dailyWorkload[$class['id_classe']] = array_fill_keys($days, 0);
      $this->weeklyMatiereCount[$class['id_classe']] = [];

      foreach ($this->matieres as $matiere) {
        foreach ($days as $day) {
          $this->matiereDailyCount[$class['id_classe']][$day][$matiere['id_matiere']] = 0;
        }
        $this->weeklyMatiereCount[$class['id_classe']][$matiere['id_matiere']] = 0;
      }
    }
  }


  //! function1  => 13-04-2025
  // private function canAddSession($classId, $matiereId, $day)
  // {
  //   // Relax constraints significantly for debugging
  //   if ($this->matiereDailyCount[$classId][$day][$matiereId] >= 4) {
  //     return false;
  //   }

  //   // Remove or significantly increase weekly session limit
  //   $filiereId = $this->getFiliereForClass($classId);
  //   $constraintKey = $filiereId . '-' . $matiereId;

  //   if (isset($this->matiereConstraints[$constraintKey])) {
  //     $maxWeeklySession = $this->matiereConstraints[$constraintKey]['nombre_seance_semaine'] + 2;
  //     if ($this->weeklyMatiereCount[$classId][$matiereId] >= $maxWeeklySession) {
  //       return false;
  //     }
  //   }

  //   // Significantly increase daily workload
  //   $dailyLoad = $this->dailyWorkload[$classId][$day];
  //   $maxDailyLoad = 12; // Increased substantially

  //   if ($dailyLoad >= $maxDailyLoad) {
  //     return false;
  //   }

  //   return true;
  // }


  private function canAddSession($classId, $matiereId, $day)
  {
    // Limit sessions per day for each matiere (allow up to 2 per day if needed)
    if ($this->matiereDailyCount[$classId][$day][$matiereId] >= 2) {
      return false;
    }

    // Enforce weekly session limit from configuration
    $filiereId = $this->getFiliereForClass($classId);
    $constraintKey = $filiereId . '-' . $matiereId;

    if (isset($this->matiereConstraints[$constraintKey])) {
      $maxWeeklySession = $this->matiereConstraints[$constraintKey]['nombre_seance_semaine'];
      if ($this->weeklyMatiereCount[$classId][$matiereId] >= $maxWeeklySession) {
        return false;
      }
    }

    // Check daily workload with a reasonable limit
    $dailyLoad = $this->dailyWorkload[$classId][$day];
    $maxDailyLoad = 8; // Reasonable limit but more flexible

    if ($dailyLoad >= $maxDailyLoad) {
      return false;
    }

    return true;
  }



  private function updateSessionTracking($classId, $matiereId, $day)
  {
    $this->matiereDailyCount[$classId][$day][$matiereId]++;
    $this->weeklyMatiereCount[$classId][$matiereId]++;

    // Update daily workload
    $matiereCoeff = $this->getMatiereCoefficient($matiereId);
    $this->dailyWorkload[$classId][$day] += ($this->session_duration / 60) * $matiereCoeff;
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

  //! function5 updated
  // private function getMatieresForClass($classeId)
  // {
  //   $matieres = [];
  //   foreach ($this->professeursAssignments as $key => $assignment) {
  //     list($classId, $matiereId) = explode('-', $key);
  //     if ($classId == $classeId) {
  //       $matieres[] = $matiereId;
  //     }
  //   }
  //   return array_unique($matieres);
  // }

    private function getMatieresForClass($classeId)
  {
      $matieres = [];
      foreach ($this->professeursAssignments as $key => $assignment) {
          list($classId, $matiereId) = explode('-', $key);
          if ($classId == $classeId) {
              $matieres[] = $matiereId;
          }
      }
      
      // Debug statement
      if ($classeId == 14) { // For our problem class 2BACSP2
          error_log("Found " . count($matieres) . " subjects for class ID $classeId: " . implode(', ', $matieres));
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

  // private function findAvailableRoom($usedRooms, $day, $timeSlot)
  // {
  //   foreach ($this->salles as $room) {
  //     if ($this->isRoomAvailable($room['id_salle'], $usedRooms, $day, $timeSlot)) {
  //       return $room;
  //     }
  //   }
  //   return null;
  // }

  private function findAvailableRoom($usedRooms, $day, $timeSlot, $currentUsedRooms = [])
  {
    // Mélanger les salles pour introduire de l'aléatoire
    $shuffledRooms = $this->salles;
    shuffle($shuffledRooms);

    foreach ($shuffledRooms as $room) {
      // Vérifier si la salle est disponible dans le planning global
      $isAvailableInTimetable = $this->sallesTimetable[$room['id_salle']][$day][$timeSlot] === null;

      // Vérifier que la salle n'a pas déjà été utilisée dans ce même créneau
      $isNotRecentlyUsed = !isset($currentUsedRooms[$day][$timeSlot]) ||
        !in_array($room['id_salle'], $currentUsedRooms[$day][$timeSlot]);

      if ($isAvailableInTimetable && $isNotRecentlyUsed) {
        return $room;
      }
    }

    // Si aucune salle n'est disponible, retourner null
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

  //! function3 updated
  // public function generateAllSchedules()
  // {
  //   $allSchedules = [];
  //   $usedRooms = [];
  //   $timeSlots = $this->getDailyTimeSlots();
  //   $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];

  //   foreach ($this->classes as $class) {
  //     $schedule = [];
  //     $classMatieres = $this->getMatieresForClass($class['id_classe']);
  //     $targetHours = $this->getTargetHoursForClass($class['id_classe']);
  //     $currentHours = 0;
  //     $currentUsedRooms = []; // Tracker des salles utilisées par cette classe


  //     error_log("Generating schedule for class: {$class['nom_classe']}");
  //     error_log("Target Hours: {$targetHours}");
  //     error_log("Available Matieres: " . implode(', ', $classMatieres));


  //     foreach ($days as $day) {
  //       $schedule[$day] = [];

  //       foreach ($timeSlots as $timeSlot) {
  //         $timeSlotKey = $timeSlot['start'] . '-' . $timeSlot['end'];
  //         error_log("Day: {$day}, TimeSlot: {$timeSlot['start']}-{$timeSlot['end']}");

  //         if ($currentHours >= $targetHours) {
  //           $schedule[$day][$timeSlotKey] = null;
  //           continue;
  //         }

  //         // Sort matieres by priority (based on remaining required sessions)
  //         $prioritizedMatieres = $this->prioritizeMatieres($class['id_classe'], $classMatieres);

  //         foreach ($prioritizedMatieres as $matiereId) {
  //           if ($this->canAddSession($class['id_classe'], $matiereId, $day)) {
  //             $assignedProf = $this->getAssignedProfesseur($class['id_classe'], $matiereId);

  //             if ($this->isValidSession($assignedProf, $day, $timeSlotKey, $class['id_classe'])) {
  //               // Passer currentUsedRooms comme paramètre supplémentaire
  //               $availableRoom = $this->findAvailableRoom($usedRooms, $day, $timeSlotKey, $currentUsedRooms);

  //               if ($availableRoom) {
  //                 $schedule[$day][$timeSlotKey] = $this->createSessionEntry(
  //                   $assignedProf,
  //                   $availableRoom,
  //                   $timeSlot['duration']
  //                 );

  //                 $this->updateSessionTracking($class['id_classe'], $matiereId, $day);

  //                 // Mettre à jour les salles utilisées
  //                 $usedRooms = $this->bookRoom($availableRoom['id_salle'], $usedRooms, $day, $timeSlotKey);

  //                 // Tracker les salles utilisées par cette classe
  //                 if (!isset($currentUsedRooms[$day][$timeSlotKey])) {
  //                   $currentUsedRooms[$day][$timeSlotKey] = [];
  //                 }
  //                 $currentUsedRooms[$day][$timeSlotKey][] = $availableRoom['id_salle'];

  //                 $this->bookResources($assignedProf['enseignant_id'], $availableRoom['id_salle'], $day, $timeSlotKey);

  //                 $currentHours += $timeSlot['duration'] / 60;
  //                 break;
  //               }
  //             }
  //           }
  //         }

  //         if (!isset($schedule[$day][$timeSlotKey])) {
  //           $schedule[$day][$timeSlotKey] = null;
  //         }
  //       }
  //     }

  //     $allSchedules[$class['id_classe']] = [
  //       'class_name' => $class['nom_classe'],
  //       'target_hours' => $targetHours,
  //       'actual_hours' => $currentHours,
  //       'schedule' => $schedule
  //     ];
  //   }

  //   return $allSchedules;
  // }

  public function generateAllSchedules()
  {
    $allSchedules = [];
    $usedRooms = [];
    $timeSlots = $this->getDailyTimeSlots();
    $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];

    // Pre-allocate sessions for strict requirement subjects
    $preAllocatedSessions = $this->preAllocateRequiredSessions($days, $timeSlots);

    foreach ($this->classes as $class) {
      $schedule = isset($preAllocatedSessions[$class['id_classe']]) ?
        $preAllocatedSessions[$class['id_classe']] : [];

      // Initialize any missing days/slots
      foreach ($days as $day) {
        if (!isset($schedule[$day])) {
          $schedule[$day] = [];
        }

        foreach ($timeSlots as $timeSlot) {
          $timeSlotKey = $timeSlot['start'] . '-' . $timeSlot['end'];
          if (!isset($schedule[$day][$timeSlotKey])) {
            $schedule[$day][$timeSlotKey] = null;
          }
        }
      }

      $classMatieres = $this->getMatieresForClass($class['id_classe']);
      $targetHours = $this->getTargetHoursForClass($class['id_classe']);
      $currentHours = $this->calculateCurrentHours($schedule);
      $currentUsedRooms = [];

      // Fill remaining slots with other subjects
      foreach ($days as $day) {
        foreach ($timeSlots as $timeSlot) {
          $timeSlotKey = $timeSlot['start'] . '-' . $timeSlot['end'];

          // Skip if slot is already allocated
          if ($schedule[$day][$timeSlotKey] !== null) {
            continue;
          }

          if ($currentHours >= $targetHours) {
            continue;
          }

          // Try to add a session here
          $prioritizedMatieres = $this->prioritizeMatieres($class['id_classe'], $classMatieres);

          foreach ($prioritizedMatieres as $matiereId) {
            if ($this->canAddSession($class['id_classe'], $matiereId, $day)) {
              $assignedProf = $this->getAssignedProfesseur($class['id_classe'], $matiereId);

              if ($this->isValidSession($assignedProf, $day, $timeSlotKey, $class['id_classe'])) {
                $availableRoom = $this->findAvailableRoom($usedRooms, $day, $timeSlotKey, $currentUsedRooms);

                if ($availableRoom) {
                  $schedule[$day][$timeSlotKey] = $this->createSessionEntry(
                    $assignedProf,
                    $availableRoom,
                    $timeSlot['duration']
                  );

                  $this->updateSessionTracking($class['id_classe'], $matiereId, $day);
                  $usedRooms = $this->bookRoom($availableRoom['id_salle'], $usedRooms, $day, $timeSlotKey);

                  if (!isset($currentUsedRooms[$day][$timeSlotKey])) {
                    $currentUsedRooms[$day][$timeSlotKey] = [];
                  }
                  $currentUsedRooms[$day][$timeSlotKey][] = $availableRoom['id_salle'];

                  $this->bookResources($assignedProf['enseignant_id'], $availableRoom['id_salle'], $day, $timeSlotKey);
                  $currentHours += $timeSlot['duration'] / 60;
                  break;
                }
              }
            }
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

  //! function4 updated
  // private function preAllocateRequiredSessions($days, $timeSlots)
  // {
  //   $preAllocatedSessions = [];
  //   $usedRooms = [];
  //   $usedTimeSlots = [];

  //   foreach ($this->classes as $class) {
  //     $classId = $class['id_classe'];
  //     $filiereId = $this->getFiliereForClass($classId);
  //     $preAllocatedSessions[$classId] = [];

  //     // Initialize days
  //     foreach ($days as $day) {
  //       $preAllocatedSessions[$classId][$day] = [];
  //     }

  //     // Get all matieres for this class with their constraints
  //     $classMatiereIds = $this->getMatieresForClass($classId);

  //     foreach ($classMatiereIds as $matiereId) {
  //       $constraintKey = $filiereId . '-' . $matiereId;

  //       // Check if this matiere has specific session requirements
  //       if (isset($this->matiereConstraints[$constraintKey])) {
  //         $requiredSessions = $this->matiereConstraints[$constraintKey]['nombre_seance_semaine'];

  //         // Only pre-allocate for subjects with strict requirements (like Mathematics)
  //         if ($requiredSessions >= 3) {
  //           $assignedProf = $this->getAssignedProfesseur($classId, $matiereId);
  //           if (!$assignedProf) continue;

  //           // Try to spread these sessions throughout the week
  //           $sessionsAllocated = 0;
  //           $dayIndex = 0;

  //           while ($sessionsAllocated < $requiredSessions && $dayIndex < count($days)) {
  //             $day = $days[$dayIndex];

  //             // Try each time slot on this day
  //             foreach ($timeSlots as $timeSlot) {
  //               $timeSlotKey = $timeSlot['start'] . '-' . $timeSlot['end'];

  //               // Check if this slot is available
  //               if (
  //                 !isset($usedTimeSlots[$classId][$day][$timeSlotKey]) &&
  //                 $this->isTeacherAvailable($assignedProf['enseignant_id'], $day, $timeSlotKey)
  //               ) {

  //                 // Find an available room
  //                 $availableRoom = $this->findAvailableRoom($usedRooms, $day, $timeSlotKey);

  //                 if ($availableRoom) {
  //                   // Allocate this session
  //                   $preAllocatedSessions[$classId][$day][$timeSlotKey] = $this->createSessionEntry(
  //                     $assignedProf,
  //                     $availableRoom,
  //                     $timeSlot['duration']
  //                   );

  //                   // Mark resources as used
  //                   if (!isset($usedTimeSlots[$classId][$day])) {
  //                     $usedTimeSlots[$classId][$day] = [];
  //                   }
  //                   $usedTimeSlots[$classId][$day][$timeSlotKey] = true;

  //                   $usedRooms = $this->bookRoom($availableRoom['id_salle'], $usedRooms, $day, $timeSlotKey);
  //                   $this->bookResources($assignedProf['enseignant_id'], $availableRoom['id_salle'], $day, $timeSlotKey);

  //                   // Update tracking for constraints
  //                   $this->updateSessionTracking($classId, $matiereId, $day);

  //                   $sessionsAllocated++;
  //                   if ($sessionsAllocated >= $requiredSessions) {
  //                     break;
  //                   }
  //                 }
  //               }
  //             }

  //             $dayIndex++;
  //           }

  //           // If we couldn't allocate all required sessions in the first pass,
  //           // try again with less restrictions
  //           if ($sessionsAllocated < $requiredSessions) {
  //             // Implementation for fallback allocation - would be similar to above but with looser constraints
  //           }
  //         }
  //       }
  //     }
  //   }

  //   return $preAllocatedSessions;
  // }

    private function preAllocateRequiredSessions($days, $timeSlots) 
  {
      $preAllocatedSessions = [];
      $usedRooms = [];
      $usedTimeSlots = [];
      
      foreach ($this->classes as $class) {
          $classId = $class['id_classe'];
          $filiereId = $this->getFiliereForClass($classId);
          $preAllocatedSessions[$classId] = [];
          
          // Initialize days
          foreach ($days as $day) {
              $preAllocatedSessions[$classId][$day] = [];
          }
          
          // Get ALL assigned subjects for this class
          $classMatiereIds = $this->getMatieresForClass($classId);
          
          // Sort matieres by required sessions (descending) to schedule high-priority subjects first
          $matieresPriority = [];
          foreach ($classMatiereIds as $matiereId) {
              $constraintKey = $filiereId . '-' . $matiereId;
              $requiredSessions = isset($this->matiereConstraints[$constraintKey]) ? 
                                $this->matiereConstraints[$constraintKey]['nombre_seance_semaine'] : 1;
              $matieresPriority[$matiereId] = $requiredSessions;
          }
          
          // Sort by descending priority
          arsort($matieresPriority);
          
          // Now process each matiere
          foreach ($matieresPriority as $matiereId => $requiredSessions) {
              $assignedProf = $this->getAssignedProfesseur($classId, $matiereId);
              if (!$assignedProf) continue;
              
              // Try to allocate all required sessions for this subject
              $sessionsAllocated = 0;
              
              // First pass: Try to distribute evenly across days
              foreach ($days as $day) {
                  if ($sessionsAllocated >= $requiredSessions) break;
                  
                  // Try at most one session per day in first pass
                  foreach ($timeSlots as $timeSlot) {
                      if ($sessionsAllocated >= $requiredSessions) break;
                      
                      $timeSlotKey = $timeSlot['start'] . '-' . $timeSlot['end'];
                      
                      // Check if this slot is available
                      if (!isset($usedTimeSlots[$classId][$day][$timeSlotKey]) && 
                          $this->isTeacherAvailable($assignedProf['enseignant_id'], $day, $timeSlotKey)) {
                          
                          // Find an available room
                          $availableRoom = $this->findAvailableRoom($usedRooms, $day, $timeSlotKey);
                          
                          if ($availableRoom) {
                              // Allocate this session
                              $preAllocatedSessions[$classId][$day][$timeSlotKey] = $this->createSessionEntry(
                                  $assignedProf,
                                  $availableRoom,
                                  $timeSlot['duration']
                              );
                              
                              // Mark resources as used
                              if (!isset($usedTimeSlots[$classId][$day])) {
                                  $usedTimeSlots[$classId][$day] = [];
                              }
                              $usedTimeSlots[$classId][$day][$timeSlotKey] = true;
                              
                              $usedRooms = $this->bookRoom($availableRoom['id_salle'], $usedRooms, $day, $timeSlotKey);
                              $this->bookResources($assignedProf['enseignant_id'], $availableRoom['id_salle'], $day, $timeSlotKey);
                              
                              // Update tracking for constraints
                              $this->updateSessionTracking($classId, $matiereId, $day);
                              
                              $sessionsAllocated++;
                              // Only allocate one session per day in first pass
                              break;
                          }
                      }
                  }
              }
              
              // Second pass: Fill in remaining sessions wherever possible
              if ($sessionsAllocated < $requiredSessions) {
                  foreach ($days as $day) {
                      if ($sessionsAllocated >= $requiredSessions) break;
                      
                      // Try each time slot on this day
                      foreach ($timeSlots as $timeSlot) {
                          if ($sessionsAllocated >= $requiredSessions) break;
                          
                          $timeSlotKey = $timeSlot['start'] . '-' . $timeSlot['end'];
                          
                          // Check if this slot is available
                          if (!isset($usedTimeSlots[$classId][$day][$timeSlotKey]) && 
                              $this->isTeacherAvailable($assignedProf['enseignant_id'], $day, $timeSlotKey)) {
                              
                              // Find an available room
                              $availableRoom = $this->findAvailableRoom($usedRooms, $day, $timeSlotKey);
                              
                              if ($availableRoom) {
                                  // Allocate this session
                                  $preAllocatedSessions[$classId][$day][$timeSlotKey] = $this->createSessionEntry(
                                      $assignedProf,
                                      $availableRoom,
                                      $timeSlot['duration']
                                  );
                                  
                                  // Mark resources as used
                                  if (!isset($usedTimeSlots[$classId][$day])) {
                                      $usedTimeSlots[$classId][$day] = [];
                                  }
                                  $usedTimeSlots[$classId][$day][$timeSlotKey] = true;
                                  
                                  $usedRooms = $this->bookRoom($availableRoom['id_salle'], $usedRooms, $day, $timeSlotKey);
                                  $this->bookResources($assignedProf['enseignant_id'], $availableRoom['id_salle'], $day, $timeSlotKey);
                                  
                                  // Update tracking for constraints
                                  $this->updateSessionTracking($classId, $matiereId, $day);
                                  
                                  $sessionsAllocated++;
                              }
                          }
                      }
                  }
              }
          }
      }
      
      return $preAllocatedSessions;
  }

    public function debugClassAssignments()
  {
      echo "<h2>Class Subject Assignments Debug</h2>";
      
      foreach ($this->classes as $class) {
          $classId = $class['id_classe'];
          $className = $class['nom_classe'];
          
          if ($className == '2BACSP2') {  // Focus on our problem class
              echo "<h3>Class: $className (ID: $classId)</h3>";
              
              // Get all assigned subjects for this class
              $assignedMatieres = [];
              foreach ($this->professeursAssignments as $key => $assignment) {
                  list($cId, $mId) = explode('-', $key);
                  if ($cId == $classId) {
                      $matiereName = $assignment['matiere_nom'];
                      $profName = $assignment['professeur_nom'];
                      $assignedMatieres[$mId] = [
                          'matiere_nom' => $matiereName,
                          'professeur_nom' => $profName
                      ];
                  }
              }
              
              echo "Total assigned subjects: " . count($assignedMatieres) . "<br>";
              
              // Display all assignments
              echo "<table border='1'><tr><th>Matiere ID</th><th>Matiere Name</th><th>Teacher</th></tr>";
              foreach ($assignedMatieres as $mId => $info) {
                  echo "<tr><td>$mId</td><td>{$info['matiere_nom']}</td><td>{$info['professeur_nom']}</td></tr>";
              }
              echo "</table><br>";
              
              // Also debug the professeursAssignments directly
              echo "<h4>Raw Assignments Data</h4>";
              echo "<pre>";
              foreach ($this->professeursAssignments as $key => $data) {
                  list($cId, $mId) = explode('-', $key);
                  if ($cId == $classId) {
                      echo "Key: $key, Matiere: {$data['matiere_nom']}, Prof: {$data['professeur_nom']}\n";
                  }
              }
              echo "</pre>";
          }
      }
  }

  private function calculateCurrentHours($schedule)
  {
    $totalHours = 0;
    foreach ($schedule as $day => $slots) {
      foreach ($slots as $slot => $session) {
        if ($session !== null) {
          $totalHours += $session['duration'] / 60;
        }
      }
    }
    return $totalHours;
  }

    public function debugMatiereAllocation()
  {
      $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
      
      echo "<h2>Matière Constraint Debug</h2>";
      
      // Get all Math sessions by class
      foreach ($this->classes as $class) {
          $classId = $class['id_classe'];
          $filiereId = $this->getFiliereForClass($classId);
          
          echo "<h3>Class: {$class['nom_classe']} (ID: $classId)</h3>";
          
          // Get all Math matiere IDs for this class
          $mathMatiereIds = [];
          foreach ($this->matieres as $matiere) {
              if (strpos(strtolower($matiere['nom_matiere']), 'math') !== false) {
                  $mathMatiereIds[] = $matiere['id_matiere'];
              }
          }
          
          foreach ($mathMatiereIds as $mathId) {
              $constraintKey = $filiereId . '-' . $mathId;
              
              if (isset($this->matiereConstraints[$constraintKey])) {
                  $requiredSessions = $this->matiereConstraints[$constraintKey]['nombre_seance_semaine'];
                  echo "Mathematics (ID: $mathId) requires $requiredSessions sessions per week<br>";
                  
                  // Check current allocation
                  $weeklyCount = 0;
                  foreach ($days as $day) {
                      $dailyCount = isset($this->matiereDailyCount[$classId][$day][$mathId]) ? 
                                  $this->matiereDailyCount[$classId][$day][$mathId] : 0;
                      $weeklyCount += $dailyCount;
                      echo "- $day: $dailyCount sessions<br>";
                  }
                  echo "Total weekly sessions: $weeklyCount / $requiredSessions<br>";
              } else {
                  echo "No constraint found for Mathematics (ID: $mathId)<br>";
              }
          }
          
          echo "<hr>";
      }
  }


  //! function3 hadi kant deja m comentia
  // public function generateAllSchedules()
  // {
  //   $allSchedules = [];
  //   $usedRooms = [];
  //   $timeSlots = $this->getDailyTimeSlots();
  //   $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];

  //   foreach ($this->classes as $class) {
  //     $schedule = [];
  //     $classMatieres = $this->getMatieresForClass($class['id_classe']);
  //     $targetHours = $this->getTargetHoursForClass($class['id_classe']);
  //     $currentHours = 0;

  //     error_log("Generating schedule for class: {$class['nom_classe']}");
  //     error_log("Target Hours: {$targetHours}");
  //     error_log("Available Matieres: " . implode(', ', $classMatieres));


  //     foreach ($days as $day) {
  //       $schedule[$day] = [];

  //       foreach ($timeSlots as $timeSlot) {
  //         $timeSlotKey = $timeSlot['start'] . '-' . $timeSlot['end'];
  //         error_log("Day: {$day}, TimeSlot: {$timeSlot['start']}-{$timeSlot['end']}");

  //         if ($currentHours >= $targetHours) {
  //           $schedule[$day][$timeSlotKey] = null;
  //           continue;
  //         }

  //         // Sort matieres by priority (based on remaining required sessions)
  //         $prioritizedMatieres = $this->prioritizeMatieres($class['id_classe'], $classMatieres);

  //         foreach ($prioritizedMatieres as $matiereId) {
  //           if ($this->canAddSession($class['id_classe'], $matiereId, $day)) {
  //             $assignedProf = $this->getAssignedProfesseur($class['id_classe'], $matiereId);

  //             if ($this->isValidSession($assignedProf, $day, $timeSlotKey, $class['id_classe'])) {
  //               $availableRoom = $this->findAvailableRoom($usedRooms, $day, $timeSlotKey);

  //               if ($availableRoom) {
  //                 $schedule[$day][$timeSlotKey] = $this->createSessionEntry(
  //                   $assignedProf,
  //                   $availableRoom,
  //                   $timeSlot['duration']
  //                 );

  //                 $this->updateSessionTracking($class['id_classe'], $matiereId, $day);
  //                 $this->bookResources($assignedProf['enseignant_id'], $availableRoom['id_salle'], $day, $timeSlotKey);

  //                 $currentHours += $timeSlot['duration'] / 60;
  //                 break;
  //               }
  //             }
  //           }
  //         }

  //         if (!isset($schedule[$day][$timeSlotKey])) {
  //           $schedule[$day][$timeSlotKey] = null;
  //         }
  //       }
  //     }

  //     $allSchedules[$class['id_classe']] = [
  //       'class_name' => $class['nom_classe'],
  //       'target_hours' => $targetHours,
  //       'actual_hours' => $currentHours,
  //       'schedule' => $schedule
  //     ];
  //   }

  //   return $allSchedules;
  // }


  private function isValidSession($assignedProf, $day, $timeSlotKey, $classId)
  {
    if (!$assignedProf) {
      error_log("No professor assigned for class {$classId}");
      return false;
    }

    if (!$this->isTeacherAvailable($assignedProf['enseignant_id'], $day, $timeSlotKey)) {
      error_log("Teacher {$assignedProf['professeur_nom']} not available on {$day} at {$timeSlotKey}");
      return false;
    }

    // Similar logging for other conditions
    return true;
  }

  private function createSessionEntry($assignedProf, $availableRoom, $duration)
  {
    return [
      'matiere' => $assignedProf['matiere_nom'],
      'professeur' => $assignedProf['professeur_nom'],
      'professeur_id' => $assignedProf['enseignant_id'],
      'salle' => $availableRoom['nom_salle'],
      'salle_id' => $availableRoom['id_salle'],
      'duration' => $duration
    ];
  }

  private function bookResources($teacherId, $roomId, $day, $timeSlotKey)
  {
    // Book the teacher's time slot
    $this->professeursTimetable[$teacherId][$day][$timeSlotKey] = true;

    // Book the room's time slot
    $this->sallesTimetable[$roomId][$day][$timeSlotKey] = true;

    // Update teacher's weekly hours
    // $this->teacherWeeklyHours[$teacherId] += $this->session_duration / 60; // VOICI LA LIGNE 504
    if (!isset($this->teacherWeeklyHours[$teacherId])) {
      $this->teacherWeeklyHours[$teacherId] = 0;
    }
    $this->teacherWeeklyHours[$teacherId] += $this->session_duration / 60;
  }

  private function prioritizeMatieres($classId, $matieres)
  {
    $priorityList = [];
    foreach ($matieres as $matiereId) {
      $priority = $this->calculateMatierePriority($classId, $matiereId);
      $priorityList[$matiereId] = $priority;
    }
    arsort($priorityList);
    return array_keys($priorityList);
  }

  //! function2 updated
  // private function calculateMatierePriority($classId, $matiereId)
  // {
  //   $filiereId = $this->getFiliereForClass($classId);
  //   $constraintKey = $filiereId . '-' . $matiereId;

  //   if (!isset($this->matiereConstraints[$constraintKey])) {
  //     return 0;
  //   }

  //   $requiredSessions = $this->matiereConstraints[$constraintKey]['nombre_seance_semaine'];
  //   $currentSessions = $this->weeklyMatiereCount[$classId][$matiereId];
  //   $coefficient = $this->matiereConstraints[$constraintKey]['coefficient'];

  //   // Priority formula: (remaining_sessions * coefficient) + urgency_factor
  //   $remainingSessions = $requiredSessions - $currentSessions;
  //   $urgencyFactor = ($remainingSessions > 0) ? ($coefficient * 2) : 0;

  //   return ($remainingSessions * $coefficient) + $urgencyFactor;
  // }

  private function calculateMatierePriority($classId, $matiereId)
  {
    $filiereId = $this->getFiliereForClass($classId);
    $constraintKey = $filiereId . '-' . $matiereId;

    if (!isset($this->matiereConstraints[$constraintKey])) {
      return 0;
    }

    $requiredSessions = $this->matiereConstraints[$constraintKey]['nombre_seance_semaine'];
    $currentSessions = $this->weeklyMatiereCount[$classId][$matiereId];
    $coefficient = $this->matiereConstraints[$constraintKey]['coefficient'];

    // Higher priority for subjects that haven't met their session requirements
    $remainingSessions = max(0, $requiredSessions - $currentSessions);

    // Very high priority for core subjects that are under their session requirement
    $urgencyFactor = ($remainingSessions > 0) ? ($coefficient * 10) : 0;

    // Prioritize subjects that are further from their requirements
    $completionRatio = ($requiredSessions > 0) ? ($currentSessions / $requiredSessions) : 1;
    $completionPenalty = (1 - $completionRatio) * 20;

    return ($remainingSessions * $coefficient * 3) + $urgencyFactor + $completionPenalty;
  }

  private function getMatiereCoefficient($matiereId)
  {
    foreach ($this->matieres as $matiere) {
      if ($matiere['id_matiere'] == $matiereId) {
        return floatval($matiere['coefficient']);
      }
    }
    return 1.0;
  }

  private function getFiliereForClass($classId)
  {
    foreach ($this->classes as $class) {
      if ($class['id_classe'] == $classId) {
        return $class['id_filiere'];
      }
    }
    return null;
  }

  private function getTargetHoursForClass($classId)
  {
    $filiereId = $this->getFiliereForClass($classId);
    foreach ($this->filieres as $filiere) {
      if ($filiere['id_filiere'] == $filiereId) {
        return min(
          $filiere['nombre_heures_max'],
          $this->max_class_hours_per_week
        );
      }
    }
    return $this->min_class_hours_per_week;
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
// $generator->testScheduleGeneration(); // Test the fix first
$generator->debugClassAssignments(); // Debug class assignments
$generator->debugMatiereAllocation(); // Debug specific subject allocations
$allSchedules = $generator->generateAllSchedules();
$teachersSchedule = $generator->getTeachersSchedule();
$roomsSchedule = $generator->getRoomsSchedule();