<?php
require_once('../../includes/DatabaseConnexion.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$start = $data['start'];
$end = $data['end'] ?? $start;

try {
    // Vérification que l'événement existe
    $stmt = $dbh->prepare("SELECT * FROM timetable WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current) {
        echo json_encode(['status' => 'error', 'message' => 'Événement non trouvé']);
        exit;
    }

    // Conversion des dates avec strtotime()
    $new_start = date('Y-m-d H:i:s', strtotime($start));
    $new_end = date('Y-m-d H:i:s', strtotime($end));
    
    // Mise à jour simple
    $update = $dbh->prepare("
        UPDATE timetable 
        SET start_datetime = ?,
            end_datetime = ?
        WHERE id = ?
    ");

    $result = $update->execute([$new_start, $new_end, $id]);

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception('Échec de la mise à jour');
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>