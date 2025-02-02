<?php
session_start();
require '../../includes/DatabaseConnexion.php';

// Définir le fuseau horaire
date_default_timezone_set('Africa/Kampala');

// Récupérer la date et l'email de l'utilisateur
$ldate = date('Y-m-d H:i:s'); // Format MySQL correct
$email = $_SESSION['email_admin'];

try {
  // Étape 1 : Récupérer l'ID de la dernière entrée
  $sql = "SELECT id FROM userlog WHERE userEmail = :email ORDER BY id DESC LIMIT 1";
  $query = $dbh->prepare($sql);
  $query->bindParam(':email', $email, PDO::PARAM_STR);
  $query->execute();
  $result = $query->fetch(PDO::FETCH_ASSOC);

  if ($result) {
    // Étape 2 : Mettre à jour la date de déconnexion
    $id = $result['id'];
    
    $sqlUpdate = "UPDATE userlog SET date_uselogout = :ldate WHERE id = :id";
    $updateQuery = $dbh->prepare($sqlUpdate);
    $updateQuery->bindParam(':ldate', $ldate, PDO::PARAM_STR);
    $updateQuery->bindParam(':id', $id, PDO::PARAM_INT);
    $updateQuery->execute();

  } else {
    echo "<script>alert('Aucun enregistrement trouvé pour cet utilisateur !');</script>";
  }
} catch (PDOException $e) {
  echo "<script>alert('Erreur: " . $e->getMessage() . "');</script>";
}

// Supprimer la session et rediriger
$_SESSION['errmsg'] = "You have successfully logged out";
unset($_SESSION['user']);
session_destroy();

// Attendre un peu pour voir l'alert avant redirection
// echo "<script>setTimeout(function(){ window.location.href='../../index.php'; }, 2000);</script>";
header("location:../../index.php");