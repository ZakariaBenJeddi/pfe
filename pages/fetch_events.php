<?php
header('Content-Type: application/json');

// Connexion à la base de données
$host = 'localhost';
$db = 'dummy_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Récupérer les événements de la base de données
$sql = "SELECT id, title, description , professeur, start_datetime AS start, end_datetime AS end , salle FROM dipot";
$stmt = $pdo->query($sql);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retourner les données au format JSON
echo json_encode($events);
