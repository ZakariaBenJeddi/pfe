<?php
session_start();
require('../../includes/DatabaseConnexion.php');

if (empty($_SESSION['user'])) {
    header('location:sign-in.php');
}

//* deconnexion 5s
require('../../includes/deconnexion_5s.php');

try {
    $dbh->beginTransaction();
    
    // Récupérer les données de la table de copie
    $stmt = $dbh->prepare("SELECT * FROM timetable_copy");
    $stmt->execute();
    $copied_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Insérer les événements copiés dans la table principale
    $stmt = $dbh->prepare("INSERT INTO timetable (description, professeur, matiere, classe, salle, start_datetime, end_datetime)
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach($copied_events as $event) {
        $stmt->execute([
            $event['description'],
            $event['professeur'],
            $event['matiere'],
            $event['classe'],
            $event['salle'],
            $event['start_datetime'],
            $event['end_datetime']
        ]);
    }
    
    $dbh->commit();
    echo json_encode(['status' => 'success', 'message' => 'Emploi du temps collé avec succès']);
} catch(PDOException $e) {
    $dbh->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors du collage : ' . $e->getMessage()]);
}
?>