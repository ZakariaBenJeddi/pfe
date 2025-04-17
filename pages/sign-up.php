<?php
session_start();
include('../includes/DatabaseConnexion.php');

// Vérifier si un utilisateur est déjà connecté
if (isset($_SESSION['user'])) {
    header("location:admin/dashboard.php");
    exit();
}

// Traitement du formulaire d'inscription
if (isset($_POST['signup'])) {
    // Récupération des données du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $gender = $_POST['gender'];
    $service = $_POST['service'];
    $telephone = trim($_POST['telephone']);
    
    // Validation des données
    $errors = [];
    
    // Vérifier si les mots de passe correspondent
    if ($password !== $confirmPassword) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    // Vérifier la complexité du mot de passe
    if (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }
    
    // Vérifier si le nom d'utilisateur existe déjà
    $sqlCheckUsername = "SELECT user_name_admin FROM administrateur WHERE user_name_admin = :username";
    $queryCheckUsername = $dbh->prepare($sqlCheckUsername);
    $queryCheckUsername->bindParam(':username', $username, PDO::PARAM_STR);
    $queryCheckUsername->execute();
    
    if ($queryCheckUsername->rowCount() > 0) {
        $errors[] = "Ce nom d'utilisateur est déjà utilisé.";
    }
    
    // Vérifier si l'email existe déjà
    $sqlCheckEmail = "SELECT email_admin FROM administrateur WHERE email_admin = :email";
    $queryCheckEmail = $dbh->prepare($sqlCheckEmail);
    $queryCheckEmail->bindParam(':email', $email, PDO::PARAM_STR);
    $queryCheckEmail->execute();
    
    if ($queryCheckEmail->rowCount() > 0) {
        $errors[] = "Cette adresse email est déjà utilisée.";
    }
    
    // Si aucune erreur, procéder à l'inscription
    if (empty($errors)) {
        // Hachage du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Date de création
        $dateCreation = date('Y-m-d');
        
        // Par défaut, les nouveaux comptes ont un statut "0" (en attente d'approbation)
        $status = 0;
        
        // Image par défaut
        $defaultImage = "default-avatar.png";
        
        try {
            // Insertion dans la base de données
            $sql = "INSERT INTO administrateur (nom_admin, prenom_admin, user_name_admin, email_admin, mot_de_passe, gender, service, date_creation, telephone, status, admin_image) 
                    VALUES (:nom, :prenom, :username, :email, :password, :gender, :service, :dateCreation, :telephone, :status, :adminImage)";
            
            $query = $dbh->prepare($sql);
            $query->bindParam(':nom', $nom, PDO::PARAM_STR);
            $query->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $query->bindParam(':gender', $gender, PDO::PARAM_STR);
            $query->bindParam(':service', $service, PDO::PARAM_STR);
            $query->bindParam(':dateCreation', $dateCreation, PDO::PARAM_STR);
            $query->bindParam(':telephone', $telephone, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_INT);
            $query->bindParam(':adminImage', $defaultImage, PDO::PARAM_STR);
            
            $query->execute();
            
            // Message de succès
            echo "<script>alert('Inscription réussie! Votre compte est en attente d\'approbation par l\'administrateur.');document.location ='sign-in.php';</script>";
            exit();
            
        } catch (PDOException $e) {
            echo "<script>alert('Erreur lors de l\'inscription: " . $e->getMessage() . "');</script>";
        }
    } else {
        // Afficher les erreurs
        $errorMessage = implode("\\n", $errors);
        echo "<script>alert('$errorMessage');</script>";
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
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
              <div class="card card-plain">
                <div class="card-header pb-0 text-start">
                  <h4 class="font-weight-bolder">Créer un compte</h4>
                  <p class="mb-0">Remplissez le formulaire pour vous inscrire</p>
                </div>
                <div class="card-body">
                  <form role="form" method="post">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <input type="text" name="nom" class="form-control form-control-lg" placeholder="Nom" required value="<?php echo isset($_POST['nom']) ? $_POST['nom'] : ''; ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <input type="text" name="prenom" class="form-control form-control-lg" placeholder="Prénom" required value="<?php echo isset($_POST['prenom']) ? $_POST['prenom'] : ''; ?>">
                      </div>
                    </div>
                    
                    <div class="mb-3">
                      <input type="text" name="username" class="form-control form-control-lg" placeholder="Nom d'utilisateur" required value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                      <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                      <input type="password" name="password" class="form-control form-control-lg" placeholder="Mot de passe" required>
                      <small class="form-text text-muted">Minimum 8 caractères</small>
                    </div>
                    
                    <div class="mb-3">
                      <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Confirmer le mot de passe" required>
                    </div>
                    
                    <div class="mb-3">
                      <select name="gender" class="form-control form-control-lg" required>
                        <option value="" disabled selected>Genre</option>
                        <option value="homme" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'homme') ? 'selected' : ''; ?>>Homme</option>
                        <option value="femme" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'femme') ? 'selected' : ''; ?>>Femme</option>
                      </select>
                    </div>
                    
                    <div class="mb-3">
                      <select name="service" class="form-control form-control-lg" required>
                        <option value="" disabled selected>Service</option>
                        <option value="admin" <?php echo (isset($_POST['service']) && $_POST['service'] == 'admin') ? 'selected' : ''; ?>>Administration</option>
                        <option value="enseignant" <?php echo (isset($_POST['service']) && $_POST['service'] == 'enseignant') ? 'selected' : ''; ?>>Enseignement</option>
                        <option value="finance" <?php echo (isset($_POST['service']) && $_POST['service'] == 'finance') ? 'selected' : ''; ?>>Finance</option>
                        <option value="autre" <?php echo (isset($_POST['service']) && $_POST['service'] == 'autre') ? 'selected' : ''; ?>>Autre</option>
                      </select>
                    </div>
                    
                    <div class="mb-3">
                      <input type="tel" name="telephone" class="form-control form-control-lg" placeholder="Téléphone" required value="<?php echo isset($_POST['telephone']) ? $_POST['telephone'] : ''; ?>">
                    </div>
                    
                    <div class="text-center">
                      <input type="submit" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0" value="S'inscrire" name="signup">
                    </div>
                  </form>
                </div>
                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                  <p class="mb-4 text-sm mx-auto">
                    Vous avez déjà un compte?
                    <a href="sign-in.php" class="text-primary text-gradient font-weight-bold">Connexion</a>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
              <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden">
                <span class="mask bg-gradient-primary opacity-6"></span>
                <h4 class="mt-5 text-white font-weight-bolder position-relative">Rejoignez notre système de gestion scolaire</h4>
                <p class="text-white position-relative">Une plateforme complète pour gérer votre établissement scolaire.</p>
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
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = { damping: '0.5' }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script src="../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>
</html>