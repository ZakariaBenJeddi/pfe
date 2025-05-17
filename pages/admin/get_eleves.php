<?php
require_once '../../includes/DatabaseConnexion.php';

header('Content-Type: application/json');


// Get classe_id from request
if (!isset($_GET['classe_id']) || empty($_GET['classe_id'])) {
  header('Content-Type: application/json');
  echo json_encode(['success' => false, 'message' => 'Classe ID is required']);
  exit;
}

$classe_id = intval($_GET['classe_id']);

try {
  // Get students for the selected class
  $query = "SELECT e.id_eleve, e.nom, e.prenom, e.id_niveau, e.id_filiere, e.id_classe, e.code_massare 
            FROM eleves e
            WHERE e.id_classe = :classe_id 
            ORDER BY e.nom, e.prenom";
  
  $stmt = $dbh->prepare($query);
  $stmt->bindParam(':classe_id', $classe_id, PDO::PARAM_INT);
  $stmt->execute();
  
  $eleves = $stmt->fetchAll(PDO::FETCH_OBJ);
  
  header('Content-Type: application/json');
  echo json_encode(['success' => true, 'data' => $eleves]);
  
} catch (PDOException $e) {
  header('Content-Type: application/json');
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}