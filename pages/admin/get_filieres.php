<?php
require_once '../../includes/DatabaseConnexion.php';

header('Content-Type: application/json');

if (isset($_GET['niveau_id'])) {
    try {
        $stmt = $dbh->prepare("SELECT id_filiere, nom_filiere FROM filiere WHERE id_niveau = ?");
        $stmt->execute([$_GET['niveau_id']]);
        $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($filieres);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur lors de la récupération des filières']);
    }
} else {
    echo json_encode(['error' => 'ID du niveau non fourni']);
}