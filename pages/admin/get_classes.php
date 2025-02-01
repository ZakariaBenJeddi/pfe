<?php
require_once '../../includes/DatabaseConnexion.php';

header('Content-Type: application/json');

if (isset($_GET['filiere_id'])) {
    try {
        $stmt = $dbh->prepare("SELECT id_classe, nom_classe FROM classe WHERE filiere_id = ?");
        $stmt->execute([$_GET['filiere_id']]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($classes);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur lors de la récupération des classes']);
    }
} else {
    echo json_encode(['error' => 'ID de la filière non fourni']);
}