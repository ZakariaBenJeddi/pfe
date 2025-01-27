<?php
require_once('../../includes/DatabaseConnexion.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "<script> alert('Error: No data to save.'); location.replace('./') </script>";
    exit;
}

try {
    // Vérifier si les champs requis sont présents
    $required_fields = ['professeur_id', 'matiere_id', 'classe-select', 'start_datetime', 'end_datetime'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Le champ $field est requis");
        }
    }

    // Récupérer les noms des selects
    $professeur_nom = $_POST['professeur_value'];
    $matiere_nom = $_POST['matiere_value'];
    $classe_nom = $_POST['classe_value'];
    $salle_nom = $_POST['salle_value'];


    // Construction de la description
    $description = "Classe: " . $classe_nom . " Matiere: " . $matiere_nom . " En ".$salle_nom;

    // Valider que la salle n'est pas déjà occupée
    $check_sql = "SELECT COUNT(*) FROM timetable 
                  WHERE salle = :salle 
                  AND ((start_datetime BETWEEN :start AND :end) 
                  OR (end_datetime BETWEEN :start AND :end)
                  OR (:start BETWEEN start_datetime AND end_datetime))
                  AND id != :current_id";

    $check_stmt = $dbh->prepare($check_sql);
    $check_stmt->execute([
        'salle' => $salle_nom,
        'start' => $_POST['start_datetime'],
        'end' => $_POST['end_datetime'],
        'current_id' => $_POST['id'] ?? 0
    ]);

    if ($check_stmt->fetchColumn() > 0) {
        throw new Exception("La salle est déjà occupée pendant cette période");
    }

// Vérifier que la salle n'est pas déjà occupée
$check_sql = "SELECT COUNT(*) FROM timetable 
              WHERE salle = :salle 
              AND ((start_datetime BETWEEN :start AND :end) 
              OR (end_datetime BETWEEN :start AND :end)
              OR (:start BETWEEN start_datetime AND end_datetime))
              AND id != :current_id";

$check_stmt = $dbh->prepare($check_sql);
$check_stmt->execute([
    'salle' => $salle_nom,  // Utiliser le nom au lieu de l'ID
    'start' => $_POST['start_datetime'],
    'end' => $_POST['end_datetime'],
    'current_id' => $_POST['id'] ?? 0
]);

if ($check_stmt->fetchColumn() > 0) {
    throw new Exception("La salle " . $salle_nom . " est déjà occupée pendant cette période");
}

// Vérifier que l'enseignant n'a pas déjà un cours
$check_prof_sql = "SELECT COUNT(*) FROM timetable 
                   WHERE professeur = :prof_nom 
                   AND ((start_datetime BETWEEN :start AND :end) 
                   OR (end_datetime BETWEEN :start AND :end)
                   OR (:start BETWEEN start_datetime AND end_datetime))
                   AND id != :current_id";

$check_prof_stmt = $dbh->prepare($check_prof_sql);
$check_prof_stmt->execute([
    'prof_nom' => $professeur_nom,  // Utiliser le nom au lieu de l'ID
    'start' => $_POST['start_datetime'],
    'end' => $_POST['end_datetime'],
    'current_id' => $_POST['id'] ?? 0
]);

if ($check_prof_stmt->fetchColumn() > 0) {
    throw new Exception("L'enseignant " . $professeur_nom . " a déjà un cours pendant cette période");
}

// Vérifier que la classe n'a pas déjà un cours
$check_classe_sql = "SELECT COUNT(*) FROM timetable 
                    WHERE classe = :classe_nom 
                    AND ((start_datetime BETWEEN :start AND :end) 
                    OR (end_datetime BETWEEN :start AND :end)
                    OR (:start BETWEEN start_datetime AND end_datetime))
                    AND id != :current_id";

$check_classe_stmt = $dbh->prepare($check_classe_sql);
$check_classe_stmt->execute([
    'classe_nom' => $classe_nom,  // Utiliser le nom au lieu de l'ID
    'start' => $_POST['start_datetime'],
    'end' => $_POST['end_datetime'],
    'current_id' => $_POST['id'] ?? 0
]);

if ($check_classe_stmt->fetchColumn() > 0) {
    throw new Exception("La classe " . $classe_nom . " a déjà un cours pendant cette période");
}

    if (empty($_POST['id'])) {
        // Insert
        $sql = "INSERT INTO timetable (
            description,
            professeur,
            matiere,
            classe,
            salle,
            start_datetime,
            end_datetime
        ) VALUES (
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
            'description' => $description,
            'professeur' => $professeur_nom,
            'matiere' => $matiere_nom,
            'classe' => $classe_nom,
            'salle' => $salle_nom,
            'start_datetime' => $_POST['start_datetime'],
            'end_datetime' => $_POST['end_datetime']
        ]);
    } else {
        // Update
        $sql = "UPDATE timetable SET 
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
            'description' => $description,
            'professeur' => $professeur_nom,
            'matiere' => $matiere_nom,
            'classe' => $classe_nom,
            'salle' => $salle_nom,
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