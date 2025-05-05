<?php
include('../../includes/admin/controller/controller.php');
session_start();

if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

// Vérifier si l'ID est fourni
if (!isset($_GET['id'])) {
  echo json_encode(['success' => false, 'message' => 'ID de matière non fourni']);
  exit;
}

// Récupérer et nettoyer l'ID
$id_matiere = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
  // Requête pour récupérer les données de la matière
  $stmt = $dbh->prepare("SELECT * FROM matiere WHERE id_matiere = :id_matiere");
  $stmt->execute([':id_matiere' => $id_matiere]);
  $matiere = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$matiere) {
      echo json_encode(['success' => false, 'message' => 'Matière non trouvée']);
      exit;
  }
  
  // Retourner les données au format JSON
  echo json_encode(['success' => true, 'matiere' => $matiere]);
  
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>