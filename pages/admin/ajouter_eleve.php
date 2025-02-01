<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:sign-in.php');
}

// require '../../includes/DatabaseConnexion.php';
include('../../includes/admin/controller/controller.php');
//* deconnexion
// require('../../includes/deconnexion_5s.php');

//* CREATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
  $resultat = ajouterEleve($dbh, $_POST);
  
  if ($resultat['success']) {
      echo "<script>
          alert('Élève ajouté avec succès.');
          window.location.href = 'eleves.php';
      </script>";
  } else {
      echo "<script>
          alert('Erreur: " . addslashes($resultat['message']) . "');
      </script>";
  }
}

// niveau
try {
  $niveaux = get_all_niveau($dbh);
} catch (PDOException $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  die("Erreur lors de la récupération des données.");
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
                <p class="mb-0">Ajouter Eleves</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Eleve Information</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_eleve" class="form-control-label">Nom Élève</label>
                      <input class="form-control" type="text" name="nom_eleve" id="nom_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="prenom_eleve" class="form-control-label">Prénom Élève</label>
                      <input class="form-control" type="text" name="prenom_eleve" id="prenom_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_naissance_eleve" class="form-control-label">Date de Naissance</label>
                      <input class="form-control" type="date" name="date_naissance_eleve" id="date_naissance_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="genre_eleve" class="form-control-label">Genre</label>
                      <!-- <input class="form-control" type="text" name="genre_eleve" id="genre_eleve" required> -->
                      <select class="form-select"  name="genre_eleve" id="genre_eleve">
                        <option value="Masculin">Masculin</option>  
                        <option value="Féminin">Féminin</option>  
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nationalite_eleve" class="form-control-label">Nationalité</label>
                      <input class="form-control" type="text" name="nationalite_eleve" id="nationalite_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="adresse_eleve" class="form-control-label">Adresse</label>
                      <input class="form-control" type="text" name="adresse_eleve" id="adresse_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="telephone_eleve" class="form-control-label">Téléphone</label>
                      <input class="form-control" type="text" value="+212" name="telephone_eleve" id="telephone_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email_eleve" class="form-control-label">Email</label>
                      <input class="form-control" type="email" name="email_eleve" id="email_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_inscription_eleve" class="form-control-label">Date d'Inscription</label>
                      <input class="form-control" type="date" name="date_inscription_eleve" id="date_inscription_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="statut_eleve" class="form-control-label">Statut</label>
                      <select class="form-select"  name="statut_eleve" id="statut_eleve" required>
                        <option value="Actif">Actif</option>
                        <option value="Inactif">Inactif</option>
                        <option value="Retraité">Retraité</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="historique_scolaire_eleve" class="form-control-label">Historique Scolaire</label>
                      <input class="form-control" type="text" name="historique_scolaire_eleve" id="historique_scolaire_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="langues_parlees_eleve" class="form-control-label">Langues Parlées</label>
                      <input class="form-control" type="text" name="langues_parlees_eleve" id="langues_parlees_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_tuteur_eleve" class="form-control-label">Nom Tuteur</label>
                      <input class="form-control" type="text" name="nom_tuteur_eleve" id="nom_tuteur_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="telephone_tuteur_eleve" class="form-control-label">Téléphone Tuteur</label>
                      <input class="form-control" type="text" name="telephone_tuteur_eleve" id="telephone_tuteur_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email_tuteur_eleve" class="form-control-label">Email Tuteur</label>
                      <input class="form-control" type="email" name="email_tuteur_eleve" id="email_tuteur_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="profession_tuteur_eleve" class="form-control-label">Profession Tuteur</label>
                      <input class="form-control" type="text" name="profession_tuteur_eleve" id="profession_tuteur_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="niveau_scolaire_eleve" class="form-control-label">Niveau Scolaire</label>
                        <select class="form-select" name="niveau_scolaire_eleve" id="niveau_scolaire_eleve" required>
                          <?php foreach($niveaux as $niveau): ?>
                            <option value="<?= $niveau->id_niveau ?>"><?= $niveau->nom_niveau ?></option>
                          <?php endforeach ?>
                        </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="filiere_eleve" class="form-control-label">Filière</label>
                      <select class="form-select" name="filiere_eleve" id="filiere_eleve" disabled>
                        <option value="">Sélectionner une filière</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="classe_eleve" class="form-control-label">Classe</label>
                      <select class="form-select" name="classe_eleve" id="classe_eleve" disabled>
                        <option value="">Sélectionner une classe</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="besoins_speciaux_eleve" class="form-control-label">Besoins Spéciaux</label>
                      <input class="form-control" type="text" name="besoins_speciaux_eleve" id="besoins_speciaux_eleve" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="langue_etrangere_eleve" class="form-control-label">Langue Étrangère</label>
                      <select class="form-select" name="langue_etrangere_eleve" id="langue_etrangere_eleve" required>
                        <option value="français">français</option>
                        <option value="anglais">anglais</option>
                        <option value="allemand">allemand</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="niveau_de_satisfaction_eleve" class="form-control-label">Niveau de Satisfaction</label>
                      <input class="form-control" type="text" name="niveau_de_satisfaction_eleve" id="niveau_de_satisfaction_eleve" required>
                    </div>
                  </div>

                </div>
                <!-- Ajoutez d'autres champs ici -->
                <div class="row">
                  <input class="btn btn-primary" type="submit" value="Ajouter" name="ajouter">
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
    <!-- FILIRE ET CLASSE SELON LE NIVEAU -->
    <script src="../../assets/js/niveau_filiere_classe.js"></script>
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