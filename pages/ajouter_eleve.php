<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:sign-in.php');
}

require '../includes/DatabaseConnexion.php';

//* deconnexion
$inactivity_limit = 300; // 5 minutes
if (isset($_SESSION['last_action'])) {
  $inactivity_duration = time() - $_SESSION['last_action'];
  if ($inactivity_duration > $inactivity_limit) {
    session_unset();
    session_destroy();
    header("Location: logout.php");
    exit();
  }
}
$_SESSION['last_action'] = time();


// update
// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
  // Récupérer les données du formulaire
  $nom = $_POST['nom_eleve'];
  $prenom = $_POST['prenom_eleve'];
  $date_naissance = $_POST['date_naissance_eleve'];
  $genre = $_POST['genre_eleve'];
  $nationalite = $_POST['nationalite_eleve'];
  $adresse = $_POST['adresse_eleve'];
  $telephone = $_POST['telephone_eleve'];
  $email = $_POST['email_eleve'];
  $date_inscription = $_POST['date_inscription_eleve'];
  $statut = $_POST['statut_eleve'];
  $historique_scolaire = $_POST['historique_scolaire_eleve'];
  $langues_parlees = $_POST['langues_parlees_eleve'];
  $nom_tuteur = $_POST['nom_tuteur_eleve'];
  $telephone_tuteur = $_POST['telephone_tuteur_eleve'];
  $email_tuteur = $_POST['email_tuteur_eleve'];
  $profession_tuteur = $_POST['profession_tuteur_eleve'];
  $niveau_scolaire = $_POST['niveau_scolaire_eleve'];
  $besoins_speciaux = $_POST['besoins_speciaux_eleve'];
  $langue_etrangere = $_POST['langue_etrangere_eleve'];
  $niveau_de_satisfaction = $_POST['niveau_de_satisfaction_eleve'];

  // Préparer la requête de mise à jour
  $sql = "INSERT INTO eleves (
              nom,prenom,date_naissance,genre,nationalite,adresse,telephone,email,date_inscription,
              statut,historique_scolaire,langues_parlees,nom_tuteur,telephone_tuteur,email_tuteur,
              profession_tuteur,niveau_scolaire,besoins_speciaux,langue_etrangere,
              niveau_de_satisfaction,date_derniere_mise_a_jour
          )
          VALUES (
              :nom,:prenom,:date_naissance,:genre,:nationalite,:adresse,:telephone,
              :email,:date_inscription,:statut,:historique_scolaire,:langues_parlees,
              :nom_tuteur,:telephone_tuteur,:email_tuteur,:profession_tuteur,:niveau_scolaire,
              :besoins_speciaux,:langue_etrangere,:niveau_de_satisfaction,NOW()
          )";


  try {
    $stmt = $dbh->prepare($sql);
    $stmt->execute([
      ':nom' => $nom,
      ':prenom' => $prenom,
      ':date_naissance' => $date_naissance,
      ':genre' => $genre,
      ':nationalite' => $nationalite,
      ':adresse' => $adresse,
      ':telephone' => $telephone,
      ':email' => $email,
      ':date_inscription' => $date_inscription,
      ':statut' => $statut,
      ':historique_scolaire' => $historique_scolaire,
      ':langues_parlees' => $langues_parlees,
      ':nom_tuteur' => $nom_tuteur,
      ':telephone_tuteur' => $telephone_tuteur,
      ':email_tuteur' => $email_tuteur,
      ':profession_tuteur' => $profession_tuteur,
      ':niveau_scolaire' => $niveau_scolaire,
      ':besoins_speciaux' => $besoins_speciaux,
      ':langue_etrangere' => $langue_etrangere,
      ':niveau_de_satisfaction' => $niveau_de_satisfaction,
    ]);

    echo "<script>
        alert('élève ajouter avec succès.');
        window.location.href = 'eleves.php';
      </script>";
  } catch (PDOException $e) {
    echo "Erreur lors de la mise à jour : " . $e->getMessage();
  }
}

?>




<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../includes/head.php' ?>



<body class="g-sidenav-show bg-gray-100">
  <div class="position-absolute w-100 min-height-300 top-0" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
    <span class="mask bg-primary opacity-6"></span>
  </div>
  <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href=" https://demos.creative-tim.com/argon-dashboard/pages/dashboard.html " target="_blank">
        <img src="https://elaraki.ac.ma/images/logo2.png" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold">
          <?= strtoupper($_SESSION['nom_admin'] . " " . $_SESSION['prenom_admin'])  ?>
        </span>

      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <!-- Section Dashboard -->
        <li class="nav-item">
          <a class="nav-link active" href="../pages/dashboard.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>

        <!-- Section Gestion des utilisateurs -->
        <li class="nav-item">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestion des Utilisateurs</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/eleves.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-hat-3 text-success text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Élèves</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/enseignant.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Enseignants</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/administration.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-badge text-info text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Administration</span>
          </a>
        </li>

        <!-- Section Gestion pédagogique -->
        <li class="nav-item">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestion Pédagogique</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/niveau.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-building text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Niveau</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/classes.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-building text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Classes</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/filiere.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-books text-info text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Filière</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/matiere.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-book-bookmark text-danger text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Matières</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/calendrier.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Emplois du Temps</span>
          </a>
        </li>

        <!-- Section Suivi -->
        <li class="nav-item">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Suivi</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/absences.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-user-run text-danger text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Absences</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/evaluations.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-chart-bar-32 text-success text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Évaluations</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/bulletins.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-folder-17 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Bulletins</span>
          </a>
        </li>

        <!-- Section Gestion des ressources -->
        <li class="nav-item">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestion des Ressources</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/salle.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-building text-info text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Salles</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/equipements.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-laptop text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Équipements</span>
          </a>
        </li>

        <!-- Section Comptabilité -->
        <li class="nav-item">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Comptabilité</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/payements.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-credit-card text-success text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Paiements</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/frais-scolarite.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-money-coins text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Frais de scolarité</span>
          </a>
        </li>

        <!-- Section Compte -->
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Mon Compte</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/profile.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Profil</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/logout.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-button-power text-danger text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Déconnexion</span>
          </a>
        </li>
      </ul>
    </div>
    <div class="sidenav-footer mx-3 ">
      <div class="card card-plain shadow-none" id="sidenavCard">
        <!-- <img class="w-50 mx-auto" src="../assets/img/illustrations/icon-documentation.svg" alt="sidebar_illustration"> -->
        <img class="w-50 mx-auto mt-5" src="https://elaraki.ac.ma/images/logo2.png" alt="sidebar_illustration">
        <div class="card-body text-center p-3 w-100 pt-0">
          <div class="docs-info">
            <h6 class="mb-0">ELARAKI School</h6>
            <p class="text-xs font-weight-bold mb-0">International School of Morocco</p>
          </div>
        </div>
      </div>
      <!-- <a href="https://www.creative-tim.com/learning-lab/bootstrap/license/argon-dashboard" target="_blank" class="btn btn-dark btn-sm w-100 mb-3">Documentation</a>
      <a class="btn btn-primary btn-sm mb-0 w-100" href="https://www.creative-tim.com/product/argon-dashboard-pro?ref=sidebarfree" type="button">Upgrade to pro</a> -->
    </div>
  </aside>
  <div class="main-content position-relative max-height-vh-100 h-100">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg bg-transparent shadow-none position-absolute px-4 w-100 z-index-2 mt-n11">
      <div class="container-fluid py-1">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 ps-2 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="text-white opacity-5" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-white active" aria-current="page">Profile</li>
          </ol>
          <h6 class="text-white font-weight-bolder ms-2">Profile</h6>
        </nav>
        <div class="collapse navbar-collapse me-md-0 me-sm-4 mt-sm-0 mt-2" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <div class="input-group">
              <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
              <input type="text" class="form-control" placeholder="Type here...">
            </div>
          </div>
          <ul class="navbar-nav justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-white font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
                <span class="d-sm-inline d-none">Sign In</span>
              </a>
            </li>
            <li class="nav-item d-xl-none ps-3 pe-0 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-white p-0">
                <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                  <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line bg-white"></i>
                    <i class="sidenav-toggler-line bg-white"></i>
                    <i class="sidenav-toggler-line bg-white"></i>
                  </div>
                </a>
              </a>
            </li>
            <li class="nav-item px-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-white p-0">
                <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
              </a>
            </li>
            <li class="nav-item dropdown pe-2 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell cursor-pointer"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end px-2 py-3 ms-n4" aria-labelledby="dropdownMenuButton">
                <li class="mb-2">
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                      <div class="my-auto">
                        <img src="../assets/img/team-2.jpg" class="avatar avatar-sm me-3">
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                          <span class="font-weight-bold">New message</span> from Laur
                        </h6>
                        <p class="text-xs text-secondary mb-0">
                          <i class="fa fa-clock me-1"></i>
                          13 minutes ago
                        </p>
                      </div>
                    </div>
                  </a>
                </li>
                <li class="mb-2">
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                      <div class="my-auto">
                        <img src="../assets/img/small-logos/logo-spotify.svg" class="avatar avatar-sm bg-gradient-dark me-3">
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                          <span class="font-weight-bold">New album</span> by Travis Scott
                        </h6>
                        <p class="text-xs text-secondary mb-0">
                          <i class="fa fa-clock me-1"></i>
                          1 day
                        </p>
                      </div>
                    </div>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                      <div class="avatar avatar-sm bg-gradient-secondary me-3 my-auto">
                        <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                          <title>credit-card</title>
                          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF" fill-rule="nonzero">
                              <g transform="translate(1716.000000, 291.000000)">
                                <g transform="translate(453.000000, 454.000000)">
                                  <path class="color-background" d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z" opacity="0.593633743"></path>
                                  <path class="color-background" d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z"></path>
                                </g>
                              </g>
                            </g>
                          </g>
                        </svg>
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                          Payment successfully completed
                        </h6>
                        <p class="text-xs text-secondary mb-0">
                          <i class="fa fa-clock me-1"></i>
                          2 days
                        </p>
                      </div>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>
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
                      <input class="form-control" type="text" name="genre_eleve" id="genre_eleve" required>
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
                      <input class="form-control" type="text" name="telephone_eleve" id="telephone_eleve" required>
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
                      <input class="form-control" type="text" name="statut_eleve" id="statut_eleve" required>
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
                      <input class="form-control" type="text" name="niveau_scolaire_eleve" id="niveau_scolaire_eleve" required>
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
                      <input class="form-control" type="text" name="langue_etrangere_eleve" id="langue_etrangere_eleve" required>
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
            <img src="../assets/img/bg-profile.jpg" alt="Image placeholder" class="card-img-top">
            <div class="row justify-content-center">
              <div class="col-4 col-lg-4 order-lg-2">
                <div class="mt-n4 mt-lg-n6 mb-4 mb-lg-0">
                  <a href="javascript:;">
                    <img src="../assets/img/team-2.jpg" class="rounded-circle img-fluid border border-2 border-white">
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
      <?php include '../includes/footer.php' ?>

    </div>
  </div>
  <!-- FIXED PLUGIN  -->
  <?php include '../includes/fixedplugin.php' ?>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script>
    const nom_salleChange = () => {
      document.getElementById("nom_salle_value").innerText = document.getElementById("nom_salle").value;
    }
    const capaciteChange = () => {
      document.getElementById("capacite_value").innerText = document.getElementById("capacite").value;
    }
    const etageChange = () => {
      console.log(document.getElementById("etage").value);
      document.getElementById("etage_value").innerText = document.getElementById("etage").value;
    };
    const equipementChange = () => {
      document.getElementById("equipement_value").innerText = document.getElementById("equipement").value;
    }
    const chaiseChange = () => {
      document.getElementById("chaise_value").innerText = document.getElementById("chaise").value;
    }
    const bureauChange = () => {
      document.getElementById("bureau_value").innerText = document.getElementById("bureau").value;
    }
    const tableauChange = () => {
      document.getElementById("tableau_value").innerText = document.getElementById("tableau").value;
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>