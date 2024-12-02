<?php
// Connexion à la base de données avec gestion des erreurs
try {
    $pdo = new PDO('mysql:host=localhost;dbname=emploi_du_temps_2acc;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500); // Code HTTP pour une erreur interne
    echo json_encode(['error' => 'Erreur de connexion à la base de données.']);
    exit();
}

// Vérifier si l'ID de la matière est transmis
if (isset($_POST['matiere_id'])) {
    $matiereId = $_POST['matiere_id'];

    // Requête pour récupérer les enseignants
    $query = "
        SELECT DISTINCT p.id, p.nom
        FROM professeurs2 p
        JOIN professeurs_classes_matieres2 pcm ON pcm.professeur_id = p.id
        WHERE pcm.matiere_id = :matiere_id
    ";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute(['matiere_id' => $matiereId]);

        $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Vérifier si des enseignants ont été trouvés
        if (empty($professeurs)) {
            echo json_encode(['message' => 'Aucun enseignant trouvé pour cette matière.']);
        } else {
            echo json_encode($professeurs);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de l\'exécution de la requête.']);
    }
} else {
    http_response_code(400); // Code HTTP pour une mauvaise requête
    echo json_encode(['error' => 'ID de la matière manquant.']);
}
