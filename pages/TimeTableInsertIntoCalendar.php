<!-- insert data into schedule_list2  -->
<?php

require 'TimeTable.php';

class ScheduleDBInserter {
    private $pdo;
    private $dbHost;
    private $dbName;
    private $dbUser;
    private $dbPass;

    public function __construct($host = 'localhost', $dbname = 'pfe1', $user = 'root', $pass = '') {
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
    
    public function insertSchedules($allSchedules) {
        // Le code reste le même que dans votre exemple précédent
        $query = "INSERT INTO timetable (title, description, professeur, start_datetime, end_datetime,classe ,salle) 
                VALUES (:title, :description, :professeur, :start_datetime, :end_datetime,:classe, :salle)";
        $stmt = $this->pdo->prepare($query);
        
        // Vider d'abord la table
        $this->pdo->exec("TRUNCATE TABLE timetable");
        
        $insertedCount = 0;
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        foreach ($allSchedules as $classId => $classData) {
            $className = $classData['class_name'];
            
            foreach ($classData['schedule'] as $day => $slots) {
                foreach ($slots as $timeSlot => $lesson) {
                    if ($lesson !== null) {
                        // Convertir le jour en date
                        $dayMap = [
                            'Lundi' => 'Monday',
                            'Mardi' => 'Tuesday',
                            'Mercredi' => 'Wednesday',
                            'Jeudi' => 'Thursday',
                            'Vendredi' => 'Friday',
                            'Samedi' => 'Saturday'
                        ];
                        
                        // Obtenir la prochaine occurrence de ce jour de la semaine
                        $englishDay = $dayMap[$day];
                        $date = date('Y-m-d', strtotime("next $englishDay"));
                        
                        // Extraire les heures de début et de fin
                        list($startTime, $endTime) = explode('-', $timeSlot);
                        
                        // Créer les datetime complets
                        $startDateTime = "$date $startTime:00";
                        $endDateTime = "$date $endTime:00";
                        
                        // Préparer la description
                        $lesson['matiere'] = $lesson['professeur']
                            ? str_replace('Prof ', '', $lesson['professeur'])
                            : $lesson['matiere'];
                        $description = sprintf(
                            "Classe: %s\nMatière: %s\nProfesseur: %s\nSalle: %s",
                            $className,
                            $lesson['matiere'],
                            $lesson['professeur'],
                            $lesson['salle']
                        );
                        $classe = $className;
                        
                        // Préparer le titre
                        $title = sprintf(
                            "%s - %s",
                            $className,
                            $lesson['matiere']
                        );
                        
                        // Insérer dans la base de données
                        try {
                            $stmt->execute([
                                ':title' => $title,
                                ':description' => $description,
                                ':professeur' => $lesson['professeur'],
                                ':start_datetime' => $startDateTime,
                                ':end_datetime' => $endDateTime,
                                ':classe' => $classe,
                                ':salle' => $lesson['salle']
                            ]);
                            $insertedCount++;
                        } catch (PDOException $e) {
                            echo "Erreur lors de l'insertion: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
        }
        
        return $insertedCount;
    }
}

// Utilisation
try {
    // Générer les emplois du temps
    $generator = new MultiClassScheduleGenerator();
    $allSchedules = $generator->generateAllSchedules();
    
    // Insérer les emplois du temps dans la base de données
    $inserter = new ScheduleDBInserter();
    $insertedCount = $inserter->insertSchedules($allSchedules);
    
    echo "Succès! $insertedCount événements ont été insérés dans la base de données.";
} catch (Exception $e) {
    echo "Une erreur est survenue: " . $e->getMessage();
}
?>