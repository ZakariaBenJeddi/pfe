<?php
session_start();
require('../../includes/DatabaseConnexion.php');

if (empty($_SESSION['user'])) {
    header('location:sign-in.php');
}

//* deconnexion 5s
require('../../includes/deconnexion_5s.php');

try {
    // Vider d'abord la table de copie
    $stmt = $dbh->prepare("TRUNCATE TABLE timetable_copy");
    $stmt->execute();
    
    // Copier les données de timetable vers timetable_copy
    $stmt = $dbh->prepare("INSERT INTO timetable_copy (original_id, description, professeur, matiere, classe, salle, start_datetime, end_datetime)
                          SELECT id, description, professeur, matiere, classe, salle, start_datetime, end_datetime 
                          FROM timetable");
    $stmt->execute();
    
    echo json_encode(['status' => 'success', 'message' => 'Emploi du temps copié avec succès']);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la copie : ' . $e->getMessage()]);
}
?>