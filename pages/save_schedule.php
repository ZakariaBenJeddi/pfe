<?php
require_once('../includes/DatabaseConnexion.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "<script> alert('Error: No data to save.'); location.replace('./') </script>";
    exit;
}

try {
    // Vérifier si les champs requis sont présents
    $required_fields = ['professeur_id', 'matiere_id', 'classe-select', 'salle', 'start_datetime', 'end_datetime'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Le champ $field est requis");
        }
    }

    // Valider que la salle n'est pas déjà occupée
    $check_sql = "SELECT COUNT(*) FROM timetable 
                  WHERE salle = :salle 
                  AND ((start_datetime BETWEEN :start AND :end) 
                  OR (end_datetime BETWEEN :start AND :end)
                  OR (:start BETWEEN start_datetime AND end_datetime))
                  AND id != :current_id";

    $check_stmt = $dbh->prepare($check_sql);
    $check_stmt->execute([
        'salle' => $_POST['salle'],
        'start' => $_POST['start_datetime'],
        'end' => $_POST['end_datetime'],
        'current_id' => $_POST['id'] ?? 0
    ]);

    if ($check_stmt->fetchColumn() > 0) {
        throw new Exception("La salle est déjà occupée pendant cette période");
    }

    // Vérifier que l'enseignant n'a pas déjà un cours
    $check_prof_sql = "SELECT COUNT(*) FROM timetable 
                       WHERE professeur = :prof_id 
                       AND ((start_datetime BETWEEN :start AND :end) 
                       OR (end_datetime BETWEEN :start AND :end)
                       OR (:start BETWEEN start_datetime AND end_datetime))
                       AND id != :current_id";

    $check_prof_stmt = $dbh->prepare($check_prof_sql);
    $check_prof_stmt->execute([
        'prof_id' => $_POST['professeur_id'],
        'start' => $_POST['start_datetime'],
        'end' => $_POST['end_datetime'],
        'current_id' => $_POST['id'] ?? 0
    ]);

    if ($check_prof_stmt->fetchColumn() > 0) {
        throw new Exception("L'enseignant a déjà un cours pendant cette période");
    }

    // Vérifier que la classe n'a pas déjà un cours
    $check_classe_sql = "SELECT COUNT(*) FROM timetable 
                        WHERE classe = :classe_id 
                        AND ((start_datetime BETWEEN :start AND :end) 
                        OR (end_datetime BETWEEN :start AND :end)
                        OR (:start BETWEEN start_datetime AND end_datetime))
                        AND id != :current_id";

    $check_classe_stmt = $dbh->prepare($check_classe_sql);
    $check_classe_stmt->execute([
        'classe_id' => $_POST['classe-select'],
        'start' => $_POST['start_datetime'],
        'end' => $_POST['end_datetime'],
        'current_id' => $_POST['id'] ?? 0
    ]);

    if ($check_classe_stmt->fetchColumn() > 0) {
        throw new Exception("La classe a déjà un cours pendant cette période");
    }

    if (empty($_POST['id'])) {
        // Insert
        $sql = "INSERT INTO timetable (
            title,
            description, 
            professeur, 
            matiere,
            classe,
            salle,
            start_datetime,
            end_datetime
        ) VALUES (
            :title,
            :description,
            :professeur,
            :matiere,
            :classe,
            :salle,
            :start_datetime,
            :end_datetime
        )";

        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'professeur' => $_POST['professeur_id'],
            'matiere' => $_POST['matiere_id'],
            'classe' => $_POST['classe-select'],
            'salle' => $_POST['salle'],
            'start_datetime' => $_POST['start_datetime'],
            'end_datetime' => $_POST['end_datetime']
        ]);
    } else {
        // Update
        $sql = "UPDATE timetable SET 
            title = :title,
            description = :description,
            professeur = :professeur,
            matiere = :matiere,
            classe = :classe,
            salle = :salle,
            start_datetime = :start_datetime,
            end_datetime = :end_datetime
            WHERE id = :id";

        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'professeur' => $_POST['professeur_id'],
            'matiere' => $_POST['matiere_id'],
            'classe' => $_POST['classe-select'],
            'salle' => $_POST['salle'],
            'start_datetime' => $_POST['start_datetime'],
            'end_datetime' => $_POST['end_datetime'],
            'id' => $_POST['id']
        ]);
    }

    echo "<script> alert('Séance enregistrée avec succès.'); location.replace('calendrier.php') </script>";
} catch (Exception $e) {
    echo "<script> alert('Erreur: " . addslashes($e->getMessage()) . "'); history.back(); </script>";
} catch (PDOException $e) {
    echo "<script> alert('Erreur de base de données: " . addslashes($e->getMessage()) . "'); history.back(); </script>";
}
