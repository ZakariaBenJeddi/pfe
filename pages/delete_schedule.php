<?php
require_once('../includes/DatabaseConnexion.php');
if (!isset($_GET['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID non défini.'
    ]);
    exit;
}
try {
    $stmt = $dbh->prepare("DELETE FROM timetable WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Séance supprimée avec succès.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Séance non trouvée.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
    ]);
}
?>