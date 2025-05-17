<?php
session_start();

include('../includes/DatabaseConnexion.php');

// Fonction pour journaliser les tentatives de connexion
function logLoginAttempt($dbh, $username, $email, $name, $lastname = '', $status = 0) {
    $uip = $_SERVER['REMOTE_ADDR'];
    $sql = "INSERT INTO userlog(userEmail, userip, status, username, name, lastname) VALUES (:email, :uip, :status, :username, :name, :lastname)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':name', $name, PDO::PARAM_STR);
    $query->bindParam(':lastname', $lastname, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':uip', $uip, PDO::PARAM_STR);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    $query->execute();
}

// Fonction pour vérifier le nombre de tentatives échouées dans les dernières 24 heures
function checkFailedAttempts($dbh, $username) {
    $uip = $_SERVER['REMOTE_ADDR'];
    $sql = "SELECT COUNT(*) as attempts FROM userlog WHERE (username = :username OR userip = :uip) AND status = 0 AND date_userlog > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':uip', $uip, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    return $result['attempts'];
}

if (isset($_POST['login'])) {
    // Protection contre les attaques par force brute
    $attempts = 100; // Nombre maximal de tentatives autorisées
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Vérifier le nombre de tentatives échouées
    $failedAttempts = checkFailedAttempts($dbh, $username);
    
    if ($failedAttempts >= $attempts) {
        echo "<script>alert('Trop de tentatives de connexion échouées. Veuillez réessayer plus tard ou contactez l\'administrateur.');document.location ='index.php';</script>";
        exit();
    }
    
    // Rechercher l'utilisateur dans la base de données
    $sql = "SELECT * FROM administrateur WHERE user_name_admin = :username";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            $motDePasseHacheBD = $result->mot_de_passe;
            $userId = $result->id_admin;
            $userStatus = $result->status;
            
            // Vérification temporaire pour les mots de passe non hachés (à supprimer après migration)
            if ($motDePasseHacheBD === $password) {
                // Le mot de passe correspond mais n'est pas haché, on le met à jour
                $motDePasseHache = password_hash($password, PASSWORD_DEFAULT);
                $updateSql = "UPDATE administrateur SET mot_de_passe = :motDePasse WHERE id_admin = :id";
                $updateQuery = $dbh->prepare($updateSql);
                $updateQuery->bindParam(':motDePasse', $motDePasseHache, PDO::PARAM_STR);
                $updateQuery->bindParam(':id', $userId, PDO::PARAM_INT);
                $updateQuery->execute();
                
                // Authentification réussie
                $loginSuccess = true;
            } 
            // Vérification du mot de passe haché
            else if (password_verify($password, $motDePasseHacheBD)) {
                // Authentification réussie
                $loginSuccess = true;
            } else {
                // Mot de passe incorrect
                $loginSuccess = false;
            }
            
            // Si l'authentification est réussie
            if ($loginSuccess) {
                // Vérifier si le compte est actif
                if ($userStatus != "1") {
                    logLoginAttempt($dbh, $username, $result->email_admin, $result->nom_admin, $result->prenom_admin, 0);
                    echo "<script>alert('Votre compte a été bloqué, veuillez contacter l\'administrateur');document.location ='index.php';</script>";
                    exit();
                }
                
                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['user'] = $userId;
                $_SESSION['nom_admin'] = $result->nom_admin;
                $_SESSION['prenom_admin'] = $result->prenom_admin;
                $_SESSION['email_admin'] = $result->email_admin;
                $_SESSION['service'] = $result->service;
                $_SESSION['admin_image'] = $result->admin_image;
                $_SESSION['login'] = $username;
                
                // Gérer "Remember Me"
                if (!empty($_POST["remember"])) {
                    // Ne jamais stocker de mot de passe en clair dans un cookie!
                    setcookie("user_login", $username, time() + (10 * 365 * 24 * 60 * 60), "/", "", true, true);
                    // Stocker un token unique pour le "se souvenir de moi" serait plus sécurisé
                } else {
                    if (isset($_COOKIE["user_login"])) {
                        setcookie("user_login", "", time() - 3600, "/");
                    }
                    if (isset($_COOKIE["userpassword"])) {
                        setcookie("userpassword", "", time() - 3600, "/");
                    }
                }
                
                // Journaliser la connexion réussie
                logLoginAttempt($dbh, $username, $result->email_admin, $result->nom_admin, $result->prenom_admin, 1);
                
                // Réinitialiser la dernière connexion
                $now = date('Y-m-d H:i:s');
                $updateLoginSql = "UPDATE administrateur SET dernier_login = :now WHERE id_admin = :id";
                $updateLoginQuery = $dbh->prepare($updateLoginSql);
                $updateLoginQuery->bindParam(':now', $now, PDO::PARAM_STR);
                $updateLoginQuery->bindParam(':id', $userId, PDO::PARAM_INT);
                $updateLoginQuery->execute();
                
                // Rediriger vers la page appropriée
                if (isset($_COOKIE['redirect_after_login'])) {
                    $redirect_url = $_COOKIE['redirect_after_login'];
                    setcookie("redirect_after_login", "", time() - 3600, "/"); // Effacer le cookie
                    header("location:" . $redirect_url);
                    exit();
                } else {
                    header("location:admin/dashboard.php");
                    exit();
                }
            } else {
                // Journaliser la tentative échouée
                logLoginAttempt($dbh, $username, $result->email_admin, $result->nom_admin, $result->prenom_admin, 0);
                echo "<script>alert('Nom d\'utilisateur ou mot de passe incorrect');document.location ='sign-in.php';</script>";
                exit();
            }
        }
    } else {
        // Utilisateur non trouvé
        logLoginAttempt($dbh, $username, 'Not registered in system', 'Potential User', '', 0);
        echo "<script>alert('Nom d\'utilisateur ou mot de passe incorrect');document.location ='sign-in.php';</script>";
        exit();
    }
}

// // Fonction pour journaliser les tentatives de connexion
// function logLoginAttempt($dbh, $username, $email, $name, $lastname = '', $status = 0) {
//     $uip = $_SERVER['REMOTE_ADDR'];
//     $sql = "INSERT INTO userlog(userEmail, userip, status, username, name, lastname) VALUES (:email, :uip, :status, :username, :name, :lastname)";
//     $query = $dbh->prepare($sql);
//     $query->bindParam(':username', $username, PDO::PARAM_STR);
//     $query->bindParam(':name', $name, PDO::PARAM_STR);
//     $query->bindParam(':lastname', $lastname, PDO::PARAM_STR);
//     $query->bindParam(':email', $email, PDO::PARAM_STR);
//     $query->bindParam(':uip', $uip, PDO::PARAM_STR);
//     $query->bindParam(':status', $status, PDO::PARAM_STR);
//     $query->execute();
// }

// // Fonction pour vérifier le nombre de tentatives échouées dans les dernières 24 heures
// function checkFailedAttempts($dbh, $username) {
//     $uip = $_SERVER['REMOTE_ADDR'];
//     $sql = "SELECT COUNT(*) as attempts FROM userlog WHERE (username = :username OR userip = :uip) AND status = 0 AND date_userlog > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
//     $query = $dbh->prepare($sql);
//     $query->bindParam(':username', $username, PDO::PARAM_STR);
//     $query->bindParam(':uip', $uip, PDO::PARAM_STR);
//     $query->execute();
//     $result = $query->fetch(PDO::FETCH_ASSOC);
//     return $result['attempts'];
// }

// if (isset($_POST['login'])) {
//     // Protection contre les attaques par force brute
//     $attempts = 100; // Nombre maximal de tentatives autorisées
    
//     $username = trim($_POST['username']);
//     $password = $_POST['password'];
    
//     // Vérifier le nombre de tentatives échouées
//     $failedAttempts = checkFailedAttempts($dbh, $username);
    
//     if ($failedAttempts >= $attempts) {
//         echo "<script>alert('Trop de tentatives de connexion échouées. Veuillez réessayer plus tard ou contactez l\'administrateur.');document.location ='index.php';</script>";
//         exit();
//     }
    
//     // Rechercher l'utilisateur dans la base de données
//     $sql = "SELECT * FROM administrateur WHERE user_name_admin = :username";
//     $query = $dbh->prepare($sql);
//     $query->bindParam(':username', $username, PDO::PARAM_STR);
//     $query->execute();
//     $results = $query->fetchAll(PDO::FETCH_OBJ);
    
//     if ($query->rowCount() > 0) {
//         foreach ($results as $result) {
//             $motDePasseHacheBD = $result->mot_de_passe;
//             $userId = $result->id_admin;
//             $userStatus = $result->status;
            
//             // Vérification du mot de passe avec password_verify
//             if (password_verify($password, $motDePasseHacheBD)) {
//                 // Authentification réussie
//                 $loginSuccess = true;
//             } else {
//                 // Mot de passe incorrect
//                 $loginSuccess = false;
//             }
            
//             // Si l'authentification est réussie
//             if ($loginSuccess) {
//                 // Vérifier si le compte est actif
//                 if ($userStatus != "1") {
//                     logLoginAttempt($dbh, $username, $result->email_admin, $result->nom_admin, $result->prenom_admin, 0);
//                     echo "<script>alert('Votre compte a été bloqué, veuillez contacter l\'administrateur');document.location ='index.php';</script>";
//                     exit();
//                 }
                
//                 // Stocker les informations de l'utilisateur dans la session
//                 $_SESSION['user'] = $userId;
//                 $_SESSION['nom_admin'] = $result->nom_admin;
//                 $_SESSION['prenom_admin'] = $result->prenom_admin;
//                 $_SESSION['email_admin'] = $result->email_admin;
//                 $_SESSION['service'] = $result->service;
//                 $_SESSION['admin_image'] = $result->admin_image;
//                 $_SESSION['login'] = $username;
                
//                 // Gérer "Remember Me"
//                 if (!empty($_POST["remember"])) {
//                     // Ne jamais stocker de mot de passe en clair dans un cookie!
//                     setcookie("user_login", $username, time() + (10 * 365 * 24 * 60 * 60), "/", "", true, true);
//                     // Stocker un token unique pour le "se souvenir de moi" serait plus sécurisé
//                 } else {
//                     if (isset($_COOKIE["user_login"])) {
//                         setcookie("user_login", "", time() - 3600, "/");
//                     }
//                     if (isset($_COOKIE["userpassword"])) {
//                         setcookie("userpassword", "", time() - 3600, "/");
//                     }
//                 }
                
//                 // Journaliser la connexion réussie
//                 logLoginAttempt($dbh, $username, $result->email_admin, $result->nom_admin, $result->prenom_admin, 1);
                
//                 // Réinitialiser la dernière connexion
//                 $now = date('Y-m-d H:i:s');
//                 $updateLoginSql = "UPDATE administrateur SET dernier_login = :now WHERE id_admin = :id";
//                 $updateLoginQuery = $dbh->prepare($updateLoginSql);
//                 $updateLoginQuery->bindParam(':now', $now, PDO::PARAM_STR);
//                 $updateLoginQuery->bindParam(':id', $userId, PDO::PARAM_INT);
//                 $updateLoginQuery->execute();
                
//                 // Rediriger vers la page appropriée
//                 if (isset($_COOKIE['redirect_after_login'])) {
//                     $redirect_url = $_COOKIE['redirect_after_login'];
//                     setcookie("redirect_after_login", "", time() - 3600, "/"); // Effacer le cookie
//                     header("location:" . $redirect_url);
//                     exit();
//                 } else {
//                     header("location:admin/dashboard.php");
//                     exit();
//                 }
//             } else {
//                 // Journaliser la tentative échouée
//                 logLoginAttempt($dbh, $username, $result->email_admin, $result->nom_admin, $result->prenom_admin, 0);
//                 echo "<script>alert('Nom d\'utilisateur ou mot de passe incorrect');document.location ='sign-in.php';</script>";
//                 exit();
//             }
//         }
//     } else {
//         // Utilisateur non trouvé
//         logLoginAttempt($dbh, $username, 'Not registered in system', 'Potential User', '', 0);
//         echo "<script>alert('Nom d\'utilisateur ou mot de passe incorrect');document.location ='sign-in.php';</script>";
//         exit();
//     }
// }
?>

<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../includes/head.php' ?>

<body class="">
  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
              <div class="card card-plain">
                <div class="card-header pb-0 text-start">
                  <h4 class="font-weight-bolder">Connexion</h4>
                  <p class="mb-0">Entrez votre nom d'utilisateur et mot de passe</p>
                </div>
                <div class="card-body">
                  <form role="form" method="post">
                    <div class="mb-3">
                      <input type="text" name="username" class="form-control form-control-lg" placeholder="Nom d'utilisateur" 
                        value="<?php if(isset($_COOKIE["user_login"])) { echo $_COOKIE["user_login"]; } ?>" required>
                    </div>
                    <div class="mb-3">
                      <input type="password" name="password" class="form-control form-control-lg" placeholder="Mot de passe" required>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="remember" name="remember" 
                        <?php if(isset($_COOKIE["user_login"])) { echo "checked"; } ?>>
                      <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <div class="text-center">
                      <input type="submit" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0" value="Connexion" name="login">
                    </div>
                  </form>
                </div>
                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                  <p class="mb-4 text-sm mx-auto">
                    <a href="forgot-password.php" class="text-primary text-gradient font-weight-bold">Mot de passe oublié?</a>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
              <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden">
                <span class="mask bg-gradient-primary opacity-6"></span>
                <h4 class="mt-5 text-white font-weight-bolder position-relative">Système de Gestion Scolaire</h4>
                <p class="text-white position-relative">Une plateforme complète pour gérer votre établissement scolaire.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <!--   Core JS Files   -->
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