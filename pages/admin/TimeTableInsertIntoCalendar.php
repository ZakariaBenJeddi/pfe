<?php
ob_start();

require 'TimeTable.php';

class ScheduleDBInserter
{
    private $pdo;
    private $dbHost;
    private $dbName;
    private $dbUser;
    private $dbPass;

    public function __construct($host = 'localhost', $dbname = 'pfe1', $user = 'root', $pass = '')
    {
        $this->dbHost = $host;
        $this->dbName = $dbname;
        $this->dbUser = $user;
        $this->dbPass = $pass;

        try {
            $this->pdo = new PDO(
                "mysql:host={$this->dbHost};dbname={$this->dbName};charset=utf8",
                $this->dbUser,
                $this->dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function insertSchedules($allSchedules)
    {
        $query = "INSERT INTO timetable (description, professeur, matiere, classe, salle, start_datetime, end_datetime) 
                VALUES (:description, :professeur, :matiere, :classe, :salle, :start_datetime, :end_datetime)";
        $stmt = $this->pdo->prepare($query);

        // Clear the table first
        $this->pdo->exec("TRUNCATE TABLE timetable");

        $insertedCount = 0;
        $currentYear = date('Y');
        $currentMonth = date('m');


        $insertedCount = 0;
        $errorCount = 0;

        foreach ($allSchedules as $classId => $classData) {
            $className = $classData['class_name'];

            foreach ($classData['schedule'] as $day => $slots) {
                foreach ($slots as $timeSlot => $lesson) {
                    if ($lesson !== null) {
                        // Convert day to date
                        $dayMap = [
                            'Lundi' => 'Monday',
                            'Mardi' => 'Tuesday',
                            'Mercredi' => 'Wednesday',
                            'Jeudi' => 'Thursday',
                            'Vendredi' => 'Friday',
                            // 'Samedi' => 'Saturday'
                        ];

                        $englishDay = $dayMap[$day];
                        $date = date('Y-m-d', strtotime("next $englishDay"));

                        // Extract start and end times
                        list($startTime, $endTime) = explode('-', $timeSlot);

                        // Create complete datetime
                        $startDateTime = "$date $startTime:00";
                        $endDateTime = "$date $endTime:00";

                        // Prepare description with the new format
                        $description = sprintf(
                            "Classe: %s\nMatiere: %s\nEnseignant: %s\nSalle: %s",
                            $className,
                            $lesson['matiere'],
                            $lesson['professeur'],
                            $lesson['salle']
                        );

                        // Insert into database
                        try {
                            $stmt->execute([
                                ':description' => $description,
                                ':professeur' => $lesson['professeur'],
                                ':matiere' => $lesson['matiere'],
                                ':classe' => $className,
                                ':salle' => $lesson['salle'],
                                ':start_datetime' => $startDateTime,
                                ':end_datetime' => $endDateTime
                            ]);
                            $insertedCount++;
                        } catch (PDOException $e) {
                            $errorCount++;
                            // echo "Erreur lors de l'insertion: " . $e->getMessage() . "\n";
                            error_log("Insertion error for class {$className}: " . $e->getMessage());
                        }
                    }
                }
            }
        }
        echo "Inserted: $insertedCount, Errors: $errorCount\n";
        return $insertedCount;
        // return $insertedCount;
    }
}

// Usage
// try {
//     // Generate schedules
//     $generator = new MultiClassScheduleGenerator();
//     $allSchedules = $generator->generateAllSchedules();

//     // Insert schedules into database
//     $inserter = new ScheduleDBInserter();
//     $insertedCount = $inserter->insertSchedules($allSchedules);
//     echo "Succès! $insertedCount événements ont été insérés dans la base de données.";
//     header('Location:TimeTableView.php');
// } catch (Exception $e) {
//     echo "Une erreur est survenue: " . $e->getMessage();
// }

// In TimeTableInsertIntoCalendar.php
try {
    // Generate schedules
    $generator = new MultiClassScheduleGenerator();
    $allSchedules = $generator->generateAllSchedules();

    // Debug: Check if schedules are generated
    if (empty($allSchedules)) {
        echo "No schedules were generated. Check constraints and generation logic.";
        exit;
    }

    // Print out some debug information
    foreach ($allSchedules as $classId => $classSchedule) {
        echo "Class: " . $classSchedule['class_name'] . "\n";
        echo "Target Hours: " . $classSchedule['target_hours'] . "\n";
        echo "Actual Hours: " . $classSchedule['actual_hours'] . "\n";
        
        // Print detailed schedule
        foreach ($classSchedule['schedule'] as $day => $slots) {
            foreach ($slots as $timeSlot => $lesson) {
                if ($lesson !== null) {
                    print_r($lesson);
                }
            }
        }
    }

    // Insert schedules into database
    $inserter = new ScheduleDBInserter();
    $insertedCount = $inserter->insertSchedules($allSchedules);
    echo "Succès! $insertedCount événements ont été insérés dans la base de données.";
    header('Location:TimeTableView.php');
} catch (Exception $e) {
    echo "Une erreur est survenue: " . $e->getMessage();
}