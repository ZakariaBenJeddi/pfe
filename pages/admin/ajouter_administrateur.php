<?php

// require '../../includes/DatabaseConnexion.php';
include('../../includes/admin/controller/controller.php');

session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

// Traitement du formulaire d'ajout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
  // Gestion de l'image
  $admin_image = "default.jpg"; // Image par défaut
  
  // Si une image a été uploadée
  if(isset($_FILES['admin_image']) && $_FILES['admin_image']['error'] == 0) {
    $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
    $filename = $_FILES["admin_image"]["name"];
    $filetype = $_FILES["admin_image"]["type"];
    $filesize = $_FILES["admin_image"]["size"];
    
    // Vérifier l'extension du fichier
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if(!array_key_exists($ext, $allowed)) {
      $error_msg = "Erreur: Veuillez sélectionner un format de fichier valide.";
    }
    
    // Vérifier la taille du fichier - 5MB maximum
    $maxsize = 5 * 1024 * 1024;
    if($filesize > $maxsize) {
      $error_msg = "Erreur: La taille du fichier dépasse la limite autorisée.";
    }
    
    // Vérifier le type MIME du fichier
    if(in_array($filetype, $allowed)) {
      // Chemin de destination
      $image_name = time() . '_' . $filename;
      $destination = "../../uploads/admin/" . $image_name;
      
      if(move_uploaded_file($_FILES["admin_image"]["tmp_name"], $destination)) {
        $admin_image = $image_name;
      } else {
        $error_msg = "Erreur lors de l'upload du fichier.";
      }
    } else {
      $error_msg = "Erreur: Il y a eu un problème avec l'upload de votre fichier. Veuillez réessayer.";
    }
  }
  
  // Hasher le mot de passe
  $hashed_password = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
  
  // Préparer les données
  $adminData = [
    'nom_admin' => $_POST['nom_admin'],
    'prenom_admin' => $_POST['prenom_admin'],
    'user_name_admin' => $_POST['user_name_admin'],
    'email_admin' => $_POST['email_admin'],
    'mot_de_passe' => $hashed_password,
    'gender' => $_POST['gender'],
    'service' => $_POST['service'],
    'telephone' => $_POST['telephone'],
    'status' => $_POST['status'],
    'admin_image' => $admin_image
  ];

  // Appeler la fonction du controller
  $result = ajouterAdministrateur($dbh, $adminData);

  if ($result['success']) {
    header("Location: administrateur.php?success=1");
    exit();
  } else {
    $error_msg = $result['message'];
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>

<body class="g-sidenav-show bg-gray-100">
  <div class="position-absolute w-100 min-height-300 top-0" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
    <span class="mask bg-primary opacity-6"></span>
  </div>
  <?php require('../../includes/admin/aside_admin.php') ?>
  <div class="main-content position-relative max-height-vh-100 h-100">
    <!-- Navbar -->
    <?php require('../../includes/admin/navbar_admin.php') ?>
    <!-- End Navbar -->
    <div class="card shadow-lg mx-4 card-profile-bottom">
    </div>
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Ajouter un Administrateur</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <?php if(isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?= $error_msg ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
              <div class="card-body">
                <p class="text-uppercase text-sm">Informations de l'Administrateur</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_admin" class="form-control-label">Nom</label>
                      <input class="form-control" type="text" name="nom_admin" id="nom_admin" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="prenom_admin" class="form-control-label">Prénom</label>
                      <input class="form-control" type="text" name="prenom_admin" id="prenom_admin" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="user_name_admin" class="form-control-label">Nom d'utilisateur</label>
                      <input class="form-control" type="text" name="user_name_admin" id="user_name_admin" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email_admin" class="form-control-label">Email</label>
                      <input class="form-control" type="email" name="email_admin" id="email_admin" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="mot_de_passe" class="form-control-label">Mot de passe</label>
                      <input class="form-control" type="password" name="mot_de_passe" id="mot_de_passe" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="gender" class="form-control-label">Genre</label>
                      <select class="form-control" name="gender" id="gender" required>
                        <option value="Homme">Homme</option>
                        <option value="Femme">Femme</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="service" class="form-control-label">Service</label>
                      <select name="service" id="service" class="form-select" required>
                        <option value="directeur">directeur</option>
                        <option value="admin">admin</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="telephone" class="form-control-label">Téléphone</label>
                      <input class="form-control" type="text" name="telephone" id="telephone" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="status" class="form-control-label">Statut</label>
                      <select class="form-control" name="status" id="status" required>
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="admin_image" class="form-control-label">Photo de profil</label>
                      <input class="form-control" type="file" name="admin_image" id="admin_image">
                    </div>
                  </div>
                </div>
                <div class="row mt-4">
                  <div class="col-12">
                    <button type="submit" class="btn btn-primary" name="ajouter">Ajouter l'administrateur</button>
                    <a href="administrateur.php" class="btn btn-secondary">Annuler</a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- FOOTER -->
      <?php include '../../includes/footer.php' ?>
    </div>
  </div>
  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>
  <!--   Core JS Files   -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>