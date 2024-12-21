<?php
// require '../includes/DatabaseConnexion.php';

// // Vérifier que la méthode de la requête est POST
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     // Vérification CSRF
//     session_start();
//     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//         echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
//         exit();
//     }

//     // Récupération des données depuis la requête AJAX
//     $code_matiere = filter_input(INPUT_POST, 'code_matiere', FILTER_SANITIZE_STRING);
//     $id_enseignant = filter_input(INPUT_POST, 'id_enseignant', FILTER_VALIDATE_INT);
//     $id_classe = filter_input(INPUT_POST, 'id_classe', FILTER_VALIDATE_INT);

//     // Debugging - Affichage des données reçues
//     error_log("code_matiere: " . $code_matiere);
//     error_log("id_enseignant: " . $id_enseignant);
//     error_log("id_classe: " . $id_classe);

//     // Vérifier si les données existent dans la base de données avant d'exécuter l'insertion
//     $check_enseignant = $dbh->prepare("SELECT COUNT(*) FROM enseignants WHERE id_enseignant = :id_enseignant");
//     $check_enseignant->bindParam(':id_enseignant', $id_enseignant, PDO::PARAM_INT);
//     $check_enseignant->execute();
//     $enseignant_exists = $check_enseignant->fetchColumn();

//     $check_classe = $dbh->prepare("SELECT COUNT(*) FROM classes WHERE id_classe = :id_classe");
//     $check_classe->bindParam(':id_classe', $id_classe, PDO::PARAM_INT);
//     $check_classe->execute();
//     $classe_exists = $check_classe->fetchColumn();

//     $check_matiere = $dbh->prepare("SELECT COUNT(*) FROM matieres WHERE code_matiere = :code_matiere");
//     $check_matiere->bindParam(':code_matiere', $code_matiere, PDO::PARAM_STR);
//     $check_matiere->execute();
//     $matiere_exists = $check_matiere->fetchColumn();

//     // Vérification de l'existence des données
//     if ($enseignant_exists && $classe_exists && $matiere_exists) {
//         try {
//             // SQL Query pour insérer les données
//             $sql = "INSERT INTO enseignant_classes_matieres (enseignant_id, classe_id, matiere_id) 
//                     VALUES (:enseignant_id, :classe_id, :matiere_id)";
//             error_log("SQL Query: " . $sql);

//             $stmt = $dbh->prepare($sql);
//             $stmt->bindParam(':enseignant_id', $id_enseignant, PDO::PARAM_INT);
//             $stmt->bindParam(':classe_id', $id_classe, PDO::PARAM_INT);
//             $stmt->bindParam(':matiere_id', $code_matiere, PDO::PARAM_STR);
//             $stmt->execute();

//             echo json_encode(['success' => true]);
//         } catch (PDOException $e) {
//             error_log("Erreur SQL : " . $e->getMessage());
//             echo json_encode(['success' => false, 'message' => 'Erreur SQL.']);
//         }
//     } else {
//         $error_message = '';
//         if (!$enseignant_exists) $error_message .= 'L\'enseignant n\'existe pas. ';
//         if (!$classe_exists) $error_message .= 'La classe n\'existe pas. ';
//         if (!$matiere_exists) $error_message .= 'La matière n\'existe pas. ';
//         echo json_encode(['success' => false, 'message' => $error_message]);
//     }
// } else {
//     echo json_encode(['success' => false, 'message' => 'Requête non autorisée.']);
// }
?>

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

require '../includes/DatabaseConnexion.php';

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
