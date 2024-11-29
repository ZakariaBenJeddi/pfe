<?php
// $data = json_decode(file_get_contents('php://input'), true);
// $id = $data['id'];
// $start = $data['start'];
// $end = $data['end'];

// // Connexion à la base de données
// $host = 'localhost';
// $db = 'dummy_db';
// $user = 'root';
// $password = '';

// try {
//   $connexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
// } catch (PDOException $e) {
//   echo json_encode(['error' => $e->getMessage()]);
//   exit;
// }

// // Requête de mise à jour
// $requete = $connexion->prepare("UPDATE schedule_list SET start_datetime = ?, end_datetime = ? WHERE id = ?");
// $requete->execute([$start, $end, $id]);
?>

<?php
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$start = $data['start'];
$end = $data['end'];

// Connexion à la base de données
$host = 'localhost';
$db = 'dummy_db';
$user = 'root';
$password = '';
try {
  $connexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
} catch (PDOException $e) {
  echo json_encode(['error' => $e->getMessage()]);
  exit;
}

// Récupérer d'abord l'heure existante
$stmt = $connexion->prepare("SELECT TIME(start_datetime) as existing_start_time, TIME(end_datetime) as existing_end_time FROM schedule_list WHERE id = ?");
$stmt->execute([$id]);
$times = $stmt->fetch(PDO::FETCH_ASSOC);

// Nouvelle requête de mise à jour
$requete = $connexion->prepare("UPDATE schedule_list 
    SET start_datetime = CONCAT(?, ' ', ?),
        end_datetime = CONCAT(?, ' ', ?)
    WHERE id = ?");

$requete->execute([
  date('Y-m-d', strtotime($start)),  // Nouvelle date
  $times['existing_start_time'],     // Heure existante
  date('Y-m-d', strtotime($end)),    // Nouvelle date
  $times['existing_end_time'],       // Heure existante
  $id
]);
