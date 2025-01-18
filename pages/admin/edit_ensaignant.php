<?php

// require '../../includes/DatabaseConnexion.php';
include('../../includes/admin/controller/controller.php');

session_start();
if (empty($_SESSION['user'])) {
  header('location:../../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

// get enseignant
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $sql = "SELECT * FROM enseignant WHERE id_enseignant = :id";
  $stmt = $dbh->prepare($sql);
  try {
    $stmt->execute([':id' => $id]);
    $enseignant = $stmt->fetch(PDO::FETCH_OBJ);
  } catch (PDOException $e) {
    echo "Erreur lors de la récupération des données : " . $e->getMessage();
  }
} else {
  echo "ID de l'enseignant non fourni.";
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
  // Préparer les données
  $enseignantData = [
      'id_enseignant' => $_POST['id_enseignant'],
      'nom_enseignant' => $_POST['nom_enseignant'],
      'prenom_enseignant' => $_POST['prenom_enseignant'],
      'email_enseignant' => $_POST['email_enseignant'],
      'telephone_enseignant' => $_POST['telephone_enseignant'],
      'date_naissance' => $_POST['date_naissance'],
      'specialite' => $_POST['specialite'],
      'masse_horaire' => $_POST['masse_horaire'],
      'date_embauche' => $_POST['date_embauche'],
      'adresse' => $_POST['adresse'],
      'genre' => $_POST['genre'],
      'niveau_education' => $_POST['niveau_education'],
      'salaire' => $_POST['salaire'],
      'date_creation' => $_POST['date_creation'],
      'est_connecte' => $_POST['est_connecte'],
      'degree' => $_POST['degree']
  ];

  // Appeler la fonction du controller
  $result = modifierEnseignant($dbh, $enseignantData);

  if ($result['success']) {
      echo "<script>
          alert('Les informations de l\'enseignant ont été mises à jour avec succès.');
          window.location.href = 'enseignant.php';
      </script>";
  } else {
      echo "<script>
          alert('" . $result['message'] . "');
      </script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

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
        <div class="col-md-8">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Modifier Enseignant</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Enseignant Information</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id_enseignant" class="form-control-label">ID Enseignant</label>
                      <input class="form-control" type="text" readonly name="id_enseignant" id="id_enseignant" value="<?= $enseignant->id_enseignant ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_enseignant" class="form-control-label">Nom</label>
                      <input class="form-control" type="text" name="nom_enseignant" id="nom_enseignant" value="<?= $enseignant->nom_enseignant ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="prenom_enseignant" class="form-control-label">Prénom</label>
                      <input class="form-control" type="text" name="prenom_enseignant" id="prenom_enseignant" value="<?= $enseignant->prenom_enseignant ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_naissance" class="form-control-label">Date de naissance</label>
                      <input class="form-control" type="date" name="date_naissance" id="date_naissance" value="<?= $enseignant->date_naissance ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="genre" class="form-control-label">Genre</label>
                      <input class="form-control" type="text" name="genre" id="genre" value="<?= $enseignant->genre ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="masse_horaire" class="form-control-label">Masse Horaire</label>
                      <input class="form-control" type="number" name="masse_horaire" id="masse_horaire" value="<?= $enseignant->masse_horaire ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="adresse" class="form-control-label">Adresse</label>
                      <input class="form-control" type="text" name="adresse" id="adresse" value="<?= $enseignant->adresse ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="telephone_enseignant" class="form-control-label">Téléphone</label>
                      <input class="form-control" type="text" name="telephone_enseignant" id="telephone_enseignant" value="<?= $enseignant->telephone_enseignant ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email_enseignant" class="form-control-label">Email</label>
                      <input class="form-control" type="email" name="email_enseignant" id="email_enseignant" value="<?= $enseignant->email_enseignant ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_embauche" class="form-control-label">Date d'embauche</label>
                      <input class="form-control" type="date" name="date_embauche" id="date_embauche" value="<?= $enseignant->date_embauche ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="est_connecte" class="form-control-label">Est Connecté</label>
                      <input class="form-control" type="text" name="est_connecte" id="est_connecte" value="<?= $enseignant->est_connecte ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="specialite" class="form-control-label">Spécialité</label>
                      <input class="form-control" type="text" name="specialite" id="specialite" value="<?= $enseignant->specialite ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="salaire" class="form-control-label">Salaire</label>
                      <input class="form-control" type="text" name="salaire" id="salaire" value="<?= $enseignant->salaire ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="niveau_education" class="form-control-label">Niveau d'Éducation</label>
                      <input class="form-control" type="text" name="niveau_education" id="niveau_education" value="<?= $enseignant->niveau_education ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_creation" class="form-control-label">Date de Création</label>
                      <input class="form-control" type="date" name="date_creation" id="date_creation" value="<?= date('Y-m-d', strtotime($enseignant->date_creation))  ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="degree" class="form-control-label">Degree</label>
                      <input class="form-control" type="number" name="degree" id="degree" value="<?= $enseignant->degree ?>" required>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <input class="btn btn-primary" type="submit" value="Modifier" name="edit">
                </div>
              </div>
            </form>

          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-profile">
            <img src="../../assets/img/bg-profile.jpg" alt="Image placeholder" class="card-img-top">
            <div class="row justify-content-center">
              <div class="col-4 col-lg-4 order-lg-2">
                <div class="mt-n4 mt-lg-n6 mb-4 mb-lg-0">
                  <a href="javascript:;">
                    <img src="../../assets/img/team-2.jpg" class="rounded-circle img-fluid border border-2 border-white">
                  </a>
                </div>
              </div>
            </div>
            <div class="card-body pt-0 mb-5">
              <div class="row">
                <div class="col">
                  <div class="d-flex justify-content-center">
                    <div class="d-grid text-center">
                      <span class="text-lg font-weight-bolder" id="chaise_value"></span>
                      <span class="text-sm opacity-8">Chaise </span>
                    </div>
                    <div class="d-grid text-center mx-4">
                      <span class="text-lg font-weight-bolder" id="bureau_value"></span>
                      <span class="text-sm opacity-8">Bureau </span>
                    </div>
                    <div class="d-grid text-center">
                      <span class="text-lg font-weight-bolder" id="tableau_value"></span>
                      <span class="text-sm opacity-8">Tableau</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="text-center mt-4">
                <h5>
                  Nom Salle :<span class="font-weight-light" id="nom_salle_value"></span>
                </h5>
                <div class="h6 font-weight-300">
                  <i class="ni location_pin mr-2"></i>Etage : <span class="font-weight-light" id="etage_value"></span>
                </div>
                <div class="h6 font-weight-300">
                  <i class="ni location_pin mr-2"></i>
                  Equipement : <span class="font-weight-light" id="equipement_value"></span>
                </div>
                <div class="h6 font-weight-300">
                  <i class="ni location_pin mr-2"></i>
                  Capacite Eleve : <span class="font-weight-light" id="capacite_value"></span>
                </div>
              </div>
            </div>
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