<?php
// Désactiver l'affichage des erreurs dans la sortie
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Forcer le type de contenu en JSON
header('Content-Type: application/json');

// En cas d'erreur, la renvoyer en JSON
function handleError($errno, $errstr, $errfile, $errline) {
    echo json_encode([
        'success' => false,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    exit;
}
set_error_handler('handleError');

require '../../includes/DatabaseConnexion.php';

// Vérifier que la méthode de la requête est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Récupération et nettoyage des données
  $code_matiere = htmlspecialchars($_POST['code_matiere'] ?? '', ENT_QUOTES, 'UTF-8');
  $id_enseignant = filter_input(INPUT_POST, 'id_enseignant', FILTER_VALIDATE_INT);
  $id_classe = filter_input(INPUT_POST, 'id_classe', FILTER_VALIDATE_INT);

  try {
      // Récupérer l'ID de la matière - Notez le changement de "id" à "id_matiere"
      $get_matiere_id = $dbh->prepare("SELECT id_matiere FROM matiere WHERE code_matiere = :code_matiere");
      $get_matiere_id->bindParam(':code_matiere', $code_matiere, PDO::PARAM_STR);
      $get_matiere_id->execute();
      $matiere_id = $get_matiere_id->fetchColumn();

      if (!$matiere_id) {
          echo json_encode([
              'success' => false, 
              'message' => 'Matière non trouvée',
              'code_matiere' => $code_matiere
          ]);
          exit();
      }

      // Vérifier si l'association existe déjà
      $check_existing = $dbh->prepare("SELECT COUNT(*) FROM enseignant_classes_matieres 
          WHERE enseignant_id = :enseignant_id 
          AND classe_id = :classe_id 
          AND matiere_id = :matiere_id");
      
      $check_existing->bindParam(':enseignant_id', $id_enseignant, PDO::PARAM_INT);
      $check_existing->bindParam(':classe_id', $id_classe, PDO::PARAM_INT);
      $check_existing->bindParam(':matiere_id', $matiere_id, PDO::PARAM_INT);
      $check_existing->execute();

      if ($check_existing->fetchColumn() > 0) {
          echo json_encode([
              'success' => false, 
              'message' => 'Cette association existe déjà'
          ]);
          exit();
      }

      // Insertion
      $sql = "INSERT INTO enseignant_classes_matieres (enseignant_id, classe_id, matiere_id) 
              VALUES (:enseignant_id, :classe_id, :matiere_id)";
      
      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':enseignant_id', $id_enseignant, PDO::PARAM_INT);
      $stmt->bindParam(':classe_id', $id_classe, PDO::PARAM_INT);
      $stmt->bindParam(':matiere_id', $matiere_id, PDO::PARAM_INT);
      
      if ($stmt->execute()) {
          echo json_encode([
              'success' => true,
              'message' => 'Assignation réussie',
              'details' => [
                  'matiere_id' => $matiere_id,
                  'enseignant_id' => $id_enseignant,
                  'classe_id' => $id_classe
              ]
          ]);
      } else {
          echo json_encode([
              'success' => false,
              'message' => 'Échec de l\'insertion',
              'error_info' => $stmt->errorInfo()
          ]);
      }

  } catch (PDOException $e) {
      error_log("Erreur PDO : " . $e->getMessage());
      echo json_encode([
          'success' => false,
          'message' => 'Erreur de base de données',
          'error' => $e->getMessage()
      ]);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
