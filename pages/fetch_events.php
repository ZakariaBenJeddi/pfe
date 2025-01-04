<?php
    // header('Content-Type: application/json');

    // // Connexion à la base de données
    // $host = 'localhost';
    // $db = 'dummy_db';
    // $user = 'root';
    // $password = '';
    
    // try {
    //     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    // } catch (PDOException $e) {
    //     echo json_encode(['error' => $e->getMessage()]);
    //     exit;
    // }
    
    // $professeur = isset($_POST['professeur']) ? $_POST['professeur'] : null;
    // // error_log("Professeur reçu : " . $professeur);

    // if ($professeur) {
    //     $sql = "SELECT id, title, professeur, start_datetime AS start, end_datetime AS end, salle 
    //             FROM schedule_list 
    //             WHERE professeur = :professeur";
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->bindParam(':professeur', $professeur);
    //     $stmt->execute();
    //     $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // } else {
    //     $sql = "SELECT id, title, professeur, start_datetime AS start, end_datetime AS end, salle 
    //             FROM schedule_list";
    //     $stmt = $pdo->query($sql);
    //     $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }
    
    // // Exécuter la requête
    // $stmt->execute();
    // $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // // Retourner les données au format JSON
    // echo json_encode($events);
    
?>

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