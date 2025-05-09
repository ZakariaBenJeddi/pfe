<?php
include('../../includes/admin/controller/controller.php');
session_start();

if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
  $result = modifierEleve($dbh, $_POST, $_FILES['image_eleve'] ?? null);

  if ($result['success']) {
    header("Location: eleves.php?success=1");
    exit();
  } else {
    header("Location: eleves.php?error=1");
  }
}

//* get eleve by id
if (isset($_GET['id_eleve'])) {
  $resultat = getEleveById($dbh, $_GET['id_eleve']);

  if (!$resultat['success']) {
    echo $resultat['message'];
    exit;
  }

  $eleve = $resultat['data'];
}

$all_niveaux = get_all_niveau($dbh);
$niveau_courant = get_niveau_by_id($dbh, $eleve->id_niveau)['data'];
$filieres = get_filieres_with_niveaux($dbh)['data'];
$filiere_courante = get_filiere_by_id($dbh, $eleve->id_filiere)['data'];
$classes = get_all_classes($dbh)['data'];
$classe_courante = get_classe_by_id($dbh, $eleve->id_classe)['data'];
?>

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
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Modifier Eleve</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Eleve Information</p>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id_eleve" class="form-control-label">ID Élève</label>
                      <input class="form-control" type="text" readonly name="id_eleve" id="id_eleve" value="<?= $eleve->id_eleve ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_eleve" class="form-control-label">Nom Élève</label>
                      <input class="form-control" type="text" name="nom_eleve" id="nom_eleve" value="<?= $eleve->nom ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="prenom_eleve" class="form-control-label">Prénom Élève</label>
                      <input class="form-control" type="text" name="prenom_eleve" id="prenom_eleve" value="<?= $eleve->prenom ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_naissance_eleve" class="form-control-label">Date de Naissance</label>
                      <input class="form-control" type="date" name="date_naissance_eleve" id="date_naissance_eleve" value="<?= $eleve->date_naissance ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="genre_eleve" class="form-control-label">Genre</label>
                      <select class="form-control" name="genre_eleve" id="genre_eleve" required>
                        <option value="Masculin" <?= $eleve->genre === 'Masculin' ? 'selected' : '' ?>>Masculin</option>
                        <option value="Féminin" <?= $eleve->genre === 'Féminin' ? 'selected' : '' ?>>Féminin</option>
                      </select>
                    </div>
                  </div>


                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nationalite_eleve" class="form-control-label">Nationalité</label>
                      <input class="form-control" type="text" name="nationalite_eleve" id="nationalite_eleve" value="<?= $eleve->nationalite ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="adresse_eleve" class="form-control-label">Adresse</label>
                      <input class="form-control" type="text" name="adresse_eleve" id="adresse_eleve" value="<?= $eleve->adresse ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="telephone_eleve" class="form-control-label">Téléphone</label>
                      <input class="form-control" type="text" name="telephone_eleve" id="telephone_eleve" value="<?= $eleve->telephone ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email_eleve" class="form-control-label">Email</label>
                      <input class="form-control" type="email" name="email_eleve" id="email_eleve" value="<?= $eleve->email ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_inscription_eleve" class="form-control-label">Date d'Inscription</label>
                      <input class="form-control" type="date" name="date_inscription_eleve" id="date_inscription_eleve" value="<?= $eleve->date_inscription ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="statut_eleve" class="form-control-label">Statut</label>
                      <select class="form-control" name="statut_eleve" id="statut_eleve" required>
                        <option value="">-- Sélectionner un statut --</option>
                        <option value="Actif" <?= $eleve->statut == 'Actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="Inactif" <?= $eleve->statut == 'Inactif' ? 'selected' : '' ?>>Inactif</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="historique_scolaire_eleve" class="form-control-label">Historique Scolaire</label>
                      <input class="form-control" type="text" name="historique_scolaire_eleve" id="historique_scolaire_eleve" value="<?= $eleve->historique_scolaire ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="langues_parlees_eleve" class="form-control-label">Langues Parlées</label>
                      <input class="form-control" type="text" name="langues_parlees_eleve" id="langues_parlees_eleve" value="<?= $eleve->langues_parlees ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_tuteur_eleve" class="form-control-label">Nom Tuteur</label>
                      <input class="form-control" type="text" name="nom_tuteur_eleve" id="nom_tuteur_eleve" value="<?= $eleve->nom_tuteur ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="telephone_tuteur_eleve" class="form-control-label">Téléphone Tuteur</label>
                      <input class="form-control" type="text" name="telephone_tuteur_eleve" id="telephone_tuteur_eleve" value="<?= $eleve->telephone_tuteur ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email_tuteur_eleve" class="form-control-label">Email Tuteur</label>
                      <input class="form-control" type="email" name="email_tuteur_eleve" id="email_tuteur_eleve" value="<?= $eleve->email_tuteur ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="profession_tuteur_eleve" class="form-control-label">Profession Tuteur</label>
                      <input class="form-control" type="text" name="profession_tuteur_eleve" id="profession_tuteur_eleve" value="<?= $eleve->profession_tuteur ?>" required>
                    </div>
                  </div>

                  <!-- Niveau scolaire -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="niveau_scolaire_eleve" class="form-control-label">Niveau Scolaire</label>
                      <select class="form-control" name="niveau_scolaire_eleve" id="niveau_scolaire_eleve" required>
                        <option value="">-- Sélectionner un niveau --</option>
                        <?php foreach ($all_niveaux as $niveau) : ?>
                          <option value="<?= $niveau->id_niveau ?>" <?= $niveau->id_niveau == $eleve->id_niveau ? 'selected' : '' ?>>
                            <?= htmlspecialchars($niveau->nom_niveau) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <!-- Filière -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="filiere_eleve" class="form-control-label">Filière</label>
                      <select class="form-select" name="filiere_eleve" id="filiere_eleve" disabled required>
                        <option value="">Sélectionner une filière</option>
                        <?php foreach ($filieres as $filiere) : ?>
                          <?php if ($filiere->id_niveau == $eleve->id_niveau) : ?>
                            <option value="<?= $filiere->id_filiere ?>" <?= $filiere->id_filiere == $eleve->id_filiere ? 'selected' : '' ?>>
                              <?= htmlspecialchars($filiere->nom_filiere) ?>
                            </option>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <!-- Classe -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="classe_eleve" class="form-control-label">Classe</label>
                      <select class="form-select" name="classe_eleve" id="classe_eleve" disabled required>
                        <option value="">Sélectionner une classe</option>
                        <?php foreach ($classes as $classe) : ?>
                          <?php if ($classe->id_filiere == $eleve->id_filiere) : ?>
                            <option value="<?= $classe->id_classe ?>" <?= $classe->id_classe == $eleve->id_classe ? 'selected' : '' ?>>
                              <?= htmlspecialchars($classe->nom_classe) ?>
                            </option>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="besoins_speciaux_eleve" class="form-control-label">Besoins Spéciaux</label>
                      <input class="form-control" type="text" name="besoins_speciaux_eleve" id="besoins_speciaux_eleve" value="<?= $eleve->besoins_speciaux ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="langue_etrangere_eleve" class="form-control-label">Langue Étrangère</label>
                      <input class="form-control" type="text" name="langue_etrangere_eleve" id="langue_etrangere_eleve" value="<?= $eleve->langue_etrangere ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="niveau_de_satisfaction_eleve" class="form-control-label">Niveau de Satisfaction</label>
                      <input class="form-control" type="text" name="niveau_de_satisfaction_eleve" id="niveau_de_satisfaction_eleve" value="<?= $eleve->niveau_de_satisfaction ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="image_eleve" class="form-control-label">Image</label>
                      <input class="form-control" type="file" name="image_eleve" id="image_eleve" value="<?= $eleve->photo ?>">
                    </div>
                  </div>

                </div>
                <!-- Ajoutez d'autres champs ici -->
                <div class="row">
                  <input class="btn btn-primary" type="submit" value="Modifier" name="edit">
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