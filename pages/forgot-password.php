<?php
session_start();
include('../includes/DatabaseConnexion.php');

if (isset($_POST['reset'])) {
    $email = trim($_POST['email']);
    
    // Vérifier si l'email existe
    $sql = "SELECT * FROM administrateur WHERE email_admin = :email";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();
    
    if ($query->rowCount() > 0) {
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Enregistrer le token dans la base de données
        $sqlToken = "INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)";
        $queryToken = $dbh->prepare($sqlToken);
        $queryToken->bindParam(':email', $email, PDO::PARAM_STR);
        $queryToken->bindParam(':token', $token, PDO::PARAM_STR);
        $queryToken->bindParam(':expires', $expires, PDO::PARAM_STR);
        $queryToken->execute();
        
        // Construire l'URL de réinitialisation
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $token;
        
        // En production, envoyez ce lien par email
        // Pour cet exemple, nous l'affichons simplement
        echo "<script>alert('Un lien de réinitialisation a été envoyé à votre adresse email. Le lien est valide pendant 1 heure.');</script>";
        // Pour les tests uniquement, afficher le lien:
        echo "<div class='alert alert-info mt-3'>Pour les tests: <a href='$resetLink'>$resetLink</a></div>";
    } else {
        echo "<script>alert('Aucun compte n\'est associé à cette adresse email.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../includes/head.php' ?>

<body class="">
  <main class="main-content mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
              <div class="card card-plain">
                <div class="card-header pb-0 text-center">
                  <h4 class="font-weight-bolder">Mot de passe oublié</h4>
                  <p class="mb-0">Entrez votre adresse email pour réinitialiser votre mot de passe</p>
                </div>
                <div class="card-body">
                  <form role="form" method="post">
                    <div class="mb-3">
                      <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required>
                    </div>
                    <div class="text-center">
                      <input type="submit" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0" value="Réinitialiser" name="reset">
                    </div>
                  </form>
                </div>
                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                  <p class="mb-4 text-sm mx-auto">
                    <a href="sign-in.php" class="text-primary text-gradient font-weight-bold">Retour à la connexion</a>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  
  <!-- Core JS Files -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>
</html>