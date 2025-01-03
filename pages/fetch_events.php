<?php
    // header('Content-Type: application/json');

    // // Connexion à la base de données
    // $host = 'localhost';
    // $db = 'dummy_db';
    // $user = 'root';
    // $password = '';
    // //schedule_list
    // //dipot

    // try {
    //     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    // } catch (PDOException $e) {
    //     echo json_encode(['error' => $e->getMessage()]);
    //     exit;
    // }

    // // Récupérer les événements de la base de données
    // $sql = "SELECT id, title, description , professeur, start_datetime AS start, end_datetime AS end , salle FROM schedule_list";
    // $stmt = $pdo->query($sql);
    // $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // // Retourner les données au format JSON
    // echo json_encode($events);
?>

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
    
    $professeur = isset($_POST['professeur']) ? $_POST['professeur'] : null;
    // error_log("Professeur reçu : " . $professeur);

    if ($professeur) {
        $sql = "SELECT id, title, professeur, start_datetime AS start, end_datetime AS end, salle 
                FROM schedule_list 
                WHERE professeur = :professeur";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':professeur', $professeur);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sql = "SELECT id, title, professeur, start_datetime AS start, end_datetime AS end, salle 
                FROM schedule_list";
        $stmt = $pdo->query($sql);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Exécuter la requête
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retourner les données au format JSON
    echo json_encode($events);
    
?>