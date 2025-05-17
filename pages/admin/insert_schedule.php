<?php
// // Inclure les fichiers nécessaires
// include('../../includes/admin/controller/controller.php');
// session_start();
// if (empty($_SESSION['user'])) {
//     header('location:../sign-in.php');
//     exit;
// }
// // Déconnexion après inactivité
// require('../../includes/deconnexion_5s.php');

// // Récupérer les données envoyées par le client
// $data = json_decode(file_get_contents('php://input'), true);

// // Appeler la fonction d'importation en masse
// $result = import_timetable_bulk($dbh, $data);

// // Retourner le résultat au format JSON
// header('Content-Type: application/json');
// echo json_encode($result);
?>

<?php
// Inclure les fichiers nécessaires
include('../../includes/admin/controller/controller.php');
session_start();
if (empty($_SESSION['user'])) {
    header('location:../sign-in.php');
    exit;
}
// Déconnexion après inactivité
require('../../includes/deconnexion_5s.php');

// Récupérer les données envoyées par le client
$data = json_decode(file_get_contents('php://input'), true);

// Appeler la fonction d'importation en masse
$result = import_timetable_bulk($dbh, $data);

// Assurer que la réponse est correctement formée en JSON
header('Content-Type: application/json');

// S'assurer que le buffer de sortie est vide avant d'envoyer la réponse
if (ob_get_length()) ob_clean();

// Vérifier si le résultat est bien un tableau
if (!is_array($result)) {
    $result = [
        'status' => 'error',
        'message' => 'Erreur inattendue lors du traitement de la requête'
    ];
}

// Envoyer la réponse JSON avec les options appropriées
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Terminer le script pour éviter toute sortie supplémentaire
exit;
?>