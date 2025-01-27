<?php
header('Content-Type: application/json');

// Connexion à la base de données
$host = 'localhost';
$db = 'pfe1';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur de connexion à la base de données']);
    exit;
}

// Récupérer les paramètres de filtrage
$professeur = isset($_GET['professeur']) ? $_GET['professeur'] : '';
$classe = isset($_GET['classe']) ? $_GET['classe'] : '';
$salle = isset($_GET['salle']) ? $_GET['salle'] : '';

// Construire la requête de base
$sql = "SELECT * FROM timetable WHERE 1=1";
$params = array();

// Ajouter les conditions de filtrage
if (!empty($professeur)) {
    $sql .= " AND professeur = :professeur";
    $params[':professeur'] = $professeur;
}

if (!empty($classe)) {
    $sql .= " AND classe = :classe";
    $params[':classe'] = $classe;
}

if (!empty($salle)) {
    $sql .= " AND salle = :salle";
    $params[':salle'] = $salle;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les événements pour FullCalendar
    $formattedEvents = array_map(function($event) {
        return [
            'id' => $event['id'],
            'title' => $event['classe'] . ' - ' . $event['matiere'],
            'start' => $event['start_datetime'],
            'end' => $event['end_datetime'],
            'extendedProps' => [
                'description' => $event['description'],
                'professeur' => $event['professeur'],
                'salle' => $event['salle'],
                'matiere' => $event['matiere'],
                'classe' => $event['classe']
            ]
        ];
    }, $events);

    echo json_encode($formattedEvents);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur lors de la récupération des données']);
}