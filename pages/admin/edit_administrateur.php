<?php

// require '../../includes/DatabaseConnexion.php';
include('../../includes/admin/controller/controller.php');

session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

// get administrateur
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $result = getAdministrateurById($dbh, $id);
  
  if ($result['success']) {
    $administrateur = $result['data'][0];
  } else {
    // Rediriger si l'administrateur n'existe pas
    header("Location: administrateur.php?error=" . urlencode($result['message']));
    exit();
  }
} else {
  echo "ID de l'administrateur non fourni.";
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
  // Gestion de l'image
  $admin_image = $administrateur->admin_image; // Conserver l'image existante par défaut
  
  // Si une nouvelle image a été uploadée
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
        // Supprimer l'ancienne image s'il ne s'agit pas de l'image par défaut
        if($administrateur->admin_image !== "default.jpg" && file_exists("../../uploads/admin/" . $administrateur->admin_image)) {
          unlink("../../uploads/admin/" . $administrateur->admin_image);
        }
        $admin_image = $image_name;
      } else {
        $error_msg = "Erreur lors de l'upload du fichier.";
      }
    } else {
      $error_msg = "Erreur: Il y a eu un problème avec l'upload de votre fichier. Veuillez réessayer.";
    }
  }
  
  // Préparer les données
  $adminData = [
    'id_admin' => $_POST['id_admin'],
    'nom_admin' => $_POST['nom_admin'],
    'prenom_admin' => $_POST['prenom_admin'],
    'user_name_admin' => $_POST['user_name_admin'],
    'email_admin' => $_POST['email_admin'],
    'gender' => $_POST['gender'],
    'service' => $_POST['service'],
    'telephone' => $_POST['telephone'],
    'status' => $_POST['status'],
    'admin_image' => $admin_image
  ];

  // Appeler la fonction du controller
  $result = modifierAdministrateur($dbh, $adminData);

  if ($result['success']) {
    header("Location: administrateur.php?success=1");
    exit();
  } else {
    $errorMessage = urlencode($result['message']);
    header("Location: administrateur.php?error={$errorMessage}");
    exit();
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
                <p class="mb-0">Modifier l'Administrateur</p>
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
                <p class="text-uppercase text-sm">Information de l'Administrateur</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id_admin" class="form-control-label">ID Administrateur</label>
                      <input class="form-control" type="text" readonly name="id_admin" id="id_admin" value="<?= $administrateur->id_admin ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_admin" class="form-control-label">Nom</label>
                      <input class="form-control" type="text" name="nom_admin" id="nom_admin" value="<?= $administrateur->nom_admin ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="prenom_admin" class="form-control-label">Prénom</label>
                      <input class="form-control" type="text" name="prenom_admin" id="prenom_admin" value="<?= $administrateur->prenom_admin ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="user_name_admin" class="form-control-label">Nom d'utilisateur</label>
                      <input class="form-control" type="text" name="user_name_admin" id="user_name_admin" value="<?= $administrateur->user_name_admin ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email_admin" class="form-control-label">Email</label>
                      <input class="form-control" type="email" name="email_admin" id="email_admin" value="<?= $administrateur->email_admin ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="gender" class="form-control-label">Genre</label>
                      <select class="form-control" name="gender" id="gender" required>
                        <option value="Homme" <?= $administrateur->gender === 'Homme' ? 'selected' : '' ?>>Homme</option>
                        <option value="Femme" <?= $administrateur->gender === 'Femme' ? 'selected' : '' ?>>Femme</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="service" class="form-control-label">Service</label>
                      <input class="form-control" type="text" name="service" id="service" value="<?= $administrateur->service ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="telephone" class="form-control-label">Téléphone</label>
                      <input class="form-control" type="text" name="telephone" id="telephone" value="<?= $administrateur->telephone ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="status" class="form-control-label">Statut</label>
                      <select class="form-control" name="status" id="status" required>
                        <option value="1" <?= $administrateur->status === 1 ? 'selected' : '' ?>>Actif</option>
                        <option value="0" <?= $administrateur->status === 0 ? 'selected' : '' ?>>Inactif</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_creation" class="form-control-label">Date de Création</label>
                      <input class="form-control" type="text" readonly value="<?= $administrateur->date_creation ?>" disabled>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="dernier_login" class="form-control-label">Dernier Login</label>
                      <input class="form-control" type="text" readonly value="<?= $administrateur->dernier_login ?>" disabled>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="admin_image" class="form-control-label">Photo de profil</label>
                      <input class="form-control" type="file" name="admin_image" id="admin_image">
                      <?php if($administrateur->admin_image): ?>
                      <div class="mt-2">
                        <img src="../../uploads/admin/<?= $administrateur->admin_image ?>" alt="Photo de profil" style="max-width: 100px;">
                        <small class="d-block">Image actuelle: <?= $administrateur->admin_image ?></small>
                      </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="row mt-4">
                  <div class="col-12">
                    <input class="btn btn-primary" type="submit" value="Modifier" name="edit">
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