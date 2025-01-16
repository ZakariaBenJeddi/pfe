<?php

use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Format;

session_start();
if (empty($_SESSION['user'])) {
  header('location:sign-in.php');
}
require '../includes/DatabaseConnexion.php';

//* deconnexion
require('../includes/deconnexion_5s.php');

//**Récupère le nombre total de salles
$query_nbr_salle = $dbh->query("SELECT COUNT(*) FROM salle ");
$nbr_salle = $query_nbr_salle->fetchColumn();
//**Récupère le nombre total de salles
$query_nbr_eleves = $dbh->query("SELECT COUNT(*) FROM eleves ");
$nbr_eleves = $query_nbr_eleves->fetchColumn();
//**Récupère le nombre total de enseignant
$query_nbr_enseignant = $dbh->query("SELECT COUNT(*) FROM enseignant ");
$nbr_enseignant = $query_nbr_enseignant->fetchColumn();
//**Récupère le nombre total d'abscence
$query_nbr_abscence = $dbh->query("SELECT COUNT(*) FROM absences ");
$nbr_abscence = $query_nbr_abscence->fetchColumn();

// Récupération des dates
$date_cette_anne = date('Y');
$date_anne_dernier = date('Y', strtotime('-1 year'));

// Fonction générique pour calculer le pourcentage de changement
function calculerPourcentageChangement($valeur_actuelle, $valeur_precedente)
{
  if ($valeur_precedente <= 0) {
    return null;
  }
  return (($valeur_actuelle - $valeur_precedente) / $valeur_precedente) * 100;
}

//* Calcul pour les élèves
$query = $dbh->prepare("SELECT COUNT(*) FROM eleves WHERE YEAR(date_inscription) = :date");
$query->bindParam(":date", $date_cette_anne);
$query->execute();
$nbr_eleves_inscrit_cette_anne = $query->fetchColumn();

$query->bindParam(":date", $date_anne_dernier);
$query->execute();
$nbr_eleves_inscrit_anne_dernier = $query->fetchColumn();

$pourcentage = calculerPourcentageChangement(
  $nbr_eleves_inscrit_cette_anne,
  $nbr_eleves_inscrit_anne_dernier
);

//* Calcul pour les enseignants
$query = $dbh->prepare("SELECT COUNT(*) FROM enseignant WHERE YEAR(date_creation) = :date");
$query->bindParam(":date", $date_cette_anne);
$query->execute();
$nbr_enseignants_inscrit_cette_anne = $query->fetchColumn();

$query->bindParam(":date", $date_anne_dernier);
$query->execute();
$nbr_enseignants_inscrit_anne_dernier = $query->fetchColumn();

$pourcentage_enseignant = calculerPourcentageChangement(
  $nbr_enseignants_inscrit_cette_anne,
  $nbr_enseignants_inscrit_anne_dernier
);

//* Calcul pour les absences
$date_aujourdhui = date('Y-m-d');
$date_hier = date('Y-m-d', strtotime('-1 day'));

$query = $dbh->prepare("SELECT COUNT(*) FROM absences WHERE date_absence = :date");
$query->bindParam(":date", $date_aujourdhui);
$query->execute();
$nbr_absences_aujourdhui = $query->fetchColumn();

$query->bindParam(":date", $date_hier);
$query->execute();
$nbr_absences_hier = $query->fetchColumn();

$pourcentage_absence = calculerPourcentageChangement(
  $nbr_absences_aujourdhui,
  $nbr_absences_hier
);

//* Affichage des résultats avec gestion des erreurs
if ($pourcentage === null) {
  setcookie("show_alert", "1", time() + 2); // Expire dans 2 secondes
}

if ($pourcentage_enseignant === null) {
  // echo "<script>alert(`Impossible de calculer le pourcentage d'enseignants (pas de données l'année dernière)`)\n</script>";
}

if ($pourcentage_absence === null) {
  // echo "<script>alert(`Impossible de calculer le pourcentage d'absences (pas de données hier)`)\n</script>";
}


// Fonction pour traduire les jours en français
function translateDay($englishDay)
{
  $translations = [
    'Mon' => 'Lun',
    'Tue' => 'Mar',
    'Wed' => 'Mer',
    'Thu' => 'Jeu',
    'Fri' => 'Ven',
    'Sat' => 'Sam',
    'Sun' => 'Dim'
  ];
  return $translations[$englishDay] ?? $englishDay;
}

// Requête SQL améliorée pour récupérer les absences de la semaine
$query_absc = "SELECT 
  e.genre,
  DATE_FORMAT(a.date_absence, '%a') AS jour,
  COUNT(DISTINCT a.id_absence) AS nb_absences
  FROM absences a
  JOIN eleves e ON a.id_eleve = e.id_eleve
  WHERE a.date_absence BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY) AND CURRENT_DATE
  AND a.statut = 'validee'
  GROUP BY e.genre, jour
  ORDER BY FIELD(jour, 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun')";

$stmt_absc = $dbh->prepare($query_absc);
$stmt_absc->execute();

// Initialiser le tableau avec tous les jours à 0
$jours = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$data = [
  'garçon' => array_fill_keys($jours, 0),
  'fille' => array_fill_keys($jours, 0)
];

// Remplir les données
while ($row = $stmt_absc->fetch(PDO::FETCH_ASSOC)) {
  $genre = $row['genre'] === 'Masculin' ? 'garçon' : 'fille';
  $jour = $row['jour'];
  $data[$genre][$jour] = (int)$row['nb_absences'];
}

// Préparer les données pour le graphique
$chartData = [
  'garçon' => [],
  'fille' => []
];

foreach ($jours as $jour) {
  $chartData['garçon'][] = [
    'x' => translateDay($jour),
    'y' => $data['garçon'][$jour]
  ];
  $chartData['fille'][] = [
    'x' => translateDay($jour),
    'y' => $data['fille'][$jour]
  ];
}


?>

<!DOCTYPE html>
<html lang="en">
<!-- HEAD -->
<?php include '../includes/head.php' ?>
<style>
  .dropdown-item {
    padding: 0.5rem 1rem;
  }

  .dropdown-item:hover {
    background-color: rgba(13, 110, 253, 0.1);
  }

  .dropdown-item.active {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
  }
</style>

<body class="g-sidenav-show  bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="#" target="_blank">
        <!-- <img src="../assets/img/icons/flags/AU.png" class="navbar-brand-img h-100" alt="main_logo"> -->
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
              <i class="fa-solid fa-layer-group text-warning text-sm opacity-10"></i>
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
        <li class="nav-item">
          <a class="nav-link " href="../pages/TimeTableInfo.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fa fa-cog text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Configuration TimeTable</span>
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
        <img class="w-50 mx-auto mt-5" src="https://elaraki.ac.ma/images/logo2.png" alt="sidebar_illustration">
        <div class="card-body text-center p-3 w-100 pt-0">
          <div class="docs-info">
            <h6 class="mb-0">ELARAKI School</h6>
            <p class="text-xs font-weight-bold mb-0">International School of Morocco</p>
          </div>
        </div>
      </div>
      <div class="text-center">
        <button class="btn btn-primary btn-sm ms-auto" id="startTourButton">Tour Gide</button>
      </div>
    </div>
  </aside>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-white active" aria-current="page">Dashboard</li>
          </ol>
          <h6 class="font-weight-bolder text-white mb-0">Dashboard</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <div class="input-group">
              <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
              <input type="text" class="form-control" placeholder="Type here...">
            </div>
          </div>
          <ul class="navbar-nav  justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-white font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
                <span class="d-sm-inline d-none">Sign In</span>
              </a>
            </li>
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line bg-white"></i>
                  <i class="sidenav-toggler-line bg-white"></i>
                  <i class="sidenav-toggler-line bg-white"></i>
                </div>
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
              <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                <li class="mb-2">
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                      <div class="my-auto">
                        <img src="../assets/img/team-2.jpg" class="avatar avatar-sm  me-3 ">
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
                        <img src="../assets/img/small-logos/logo-spotify.svg" class="avatar avatar-sm bg-gradient-dark  me-3 ">
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
                      <div class="avatar avatar-sm bg-gradient-secondary  me-3  my-auto">
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
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card" id="nombre_eleve">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Nombre eleve</p>
                    <h5 class="font-weight-bolder">
                      <?= $nbr_eleves ?>
                    </h5>
                    <?php if ($pourcentage !== null) { ?>
                      <p class="mb-0">
                        <span class="<?= $pourcentage < 0 ? 'text-danger' : 'text-success'; ?> text-sm font-weight-bolder">
                          <?= number_format($pourcentage, 2) . "%"; ?>
                        </span>
                        l'année précédente
                      </p>
                    <?php } else { ?>
                      <p class="text-danger text-sm mb-0 mb-3">
                        <i class="fas fa-exclamation-circle text-danger text-lg"></i> Error pourcentage élèves
                      </p>
                    <?php } ?>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                    <i class="ni ni-single-02 text-light text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card" id="ensaignant">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Ensaignant</p>
                    <h5 class="font-weight-bolder">
                      <?= $nbr_enseignant ?>
                    </h5>
                    <?php if ($pourcentage_enseignant !== null) { ?>
                      <p class="mb-0">
                        <span class="<?= $pourcentage_enseignant < 0 ? 'text-danger' : 'text-success'; ?> text-sm font-weight-bolder">
                          <?= number_format($pourcentage_enseignant, 2) . "%"; ?>
                        </span>
                        l'année précédente
                      </p>
                    <?php } else { ?>
                      <p class="text-danger text-sm mb-0 mb-3">
                        <i class="fas fa-exclamation-circle text-danger text-lg"></i> Error pourcentage
                      </p>
                    <?php } ?>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                    <!-- <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i> -->
                    <i class="ni ni-single-02 text-light text-lg opacity-10"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6">
          <div class="card" id="abscence">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Abscence</p>
                    <h5 class="font-weight-bolder">
                      <?= $nbr_abscence; ?>
                    </h5>
                    <?php if ($pourcentage_absence !== null) { ?>
                      <p class="mb-0">
                        <span class="<?= $pourcentage_absence < 0 ? 'text-danger' : 'text-success'; ?> text-sm font-weight-bolder">
                          <?= number_format($pourcentage_absence, 2) . "%"; ?>
                        </span>
                        par rapport à hier
                      </p>
                    <?php } else { ?>
                      <p class="text-danger text-sm mb-0 mb-3">
                        <i class="fas fa-exclamation-circle text-danger text-lg"></i> Error pourcentage
                      </p>
                    <?php } ?>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                    <i class="fas fa-user-slash text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card" id="nombre_salle">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Nombre Salle</p>
                    <h5 class="font-weight-bolder">
                      <?= $nbr_salle; ?>
                    </h5>
                    <p class="mb-0">
                      <span class="text-success text-sm font-weight-bolder"><?= $nbr_salle ?></span>
                      Salles actuellement
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                    <i class="ni ni-building text-light text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <!-- Absence chart card -->
        <div class="col-lg-7 mb-lg-0 mb-5">
          <div class="card shadow-sm hover:shadow-lg transition-shadow duration-300">
            <div class="card-body">
              <!-- Header -->
              <div class="row mb-4">
                <div class="col-sm-6">
                  <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="me-3">
                      <span class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                        <i class="fas fa-chart-bar"></i>
                      </span>
                    </div>
                    <div>
                      <p class="card-title mb-0 text-secondary">Statistiques des Absences</p>
                      <?php
                      // Calcul du total des absences aujourd'hui
                      $total_absences = $data['garçon']['Mon'] + $data['fille']['Mon']; // Supposons que 'Mon' représente aujourd'hui
                      ?>
                      <span class="fw-semibold text-dark"><?= $total_absences ?> absence(s)</span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Chart -->
              <div id="column-chart" class="mt-2"></div>
              <!-- Footer -->
              <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="dropdown">
                  <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <span>7 derniers jours</span>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="fas fa-clock me-2 text-secondary"></i>
                        Aujourd'hui
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="fas fa-calendar-day me-2 text-secondary"></i>
                        Hier
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center active" href="#">
                        <i class="fas fa-calendar-week me-2 text-secondary"></i>
                        7 derniers jours
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="fas fa-calendar me-2 text-secondary"></i>
                        30 derniers jours
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="fas fa-calendar-alt me-2 text-secondary"></i>
                        90 derniers jours
                      </a>
                    </li>
                  </ul>
                </div>

                <a href="#" class="btn btn-primary d-flex align-items-center">
                  <span>Rapport détaillé</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right ms-2" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" />
                  </svg>
                </a>
              </div>

            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="card card-carousel overflow-hidden h-100 p-0">
            <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">
              <div class="carousel-inner border-radius-lg h-100">
                <div class="carousel-item h-100 active" style="background-image: url('https://plus.unsplash.com/premium_photo-1687128298182-6a60a37af6d4?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8ODl8fHNjaG9vbHxlbnwwfHwwfHx8MA%3D%3D');
            background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-camera-compact text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Get started with ELARAKI School</h5>
                  </div>
                </div>
                <div class="carousel-item h-100" style="background-image: url('https://plus.unsplash.com/premium_photo-1671070290623-d6f76bdbb3db?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8c2Nob29sfGVufDB8fDB8fHww');
              background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-bulb-61 text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Get started with ELARAKI School</h5>
                  </div>
                </div>
                <div class="carousel-item h-100" style="background-image: url('https://plus.unsplash.com/premium_photo-1680807869780-e0876a6f3cd5?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8c2Nob29sfGVufDB8fDB8fHww');
                    background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-trophy text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Faster way to create web pages</h5>
                  </div>
                </div>
                <div class="carousel-item h-100" style="background-image: url('../assets/img/school/projet\ presentation.png');
                    background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-trophy text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Presentaion Projet</h5>
                    <!-- <p>Don’t be afraid to be wrong because you can’t learn anything from a compliment.</p> -->
                  </div>
                </div>
              </div>
              <button class="carousel-control-prev w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <!-- //! Abscence chart -->
        <div class="col-lg-7 mb-lg-0 mb-4">
          <div class="card z-index-2 h-100">
            <div class="card-header pb-0 pt-3 bg-transparent">
              <h6 class="text-capitalize">Abscence Chart</h6>
              <p class="text-sm mb-0">
                <i class="fa fa-arrow-up text-success"></i>
                <span class="font-weight-bold">4% plus</span> in 2021
              </p>
            </div>
            <div class="card-body p-3">
              <div class="chart">
                <canvas id="chart-bar" class="chart-canvas" height="300"></canvas>
              </div>
            </div>
          </div>

        </div>
        <div class="col-lg-5">
          <div class="card">
            <div class="card-header pb-0 p-3">
              <h6 class="mb-0">Categories</h6>
            </div>
            <div class="card-body p-3">
              <ul class="list-group">
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                      <i class="ni ni-mobile-button text-white opacity-10"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Abscence</h6>
                      <span class="text-xs">250 in stock, <span class="font-weight-bold">346+ sold</span></span>
                    </div>
                  </div>
                  <div class="d-flex">
                    <button class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto"><i class="ni ni-bold-right" aria-hidden="true"></i></button>
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                      <i class="ni ni-tag text-white opacity-10"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Evaluation</h6>
                      <span class="text-xs">123 closed, <span class="font-weight-bold">15 open</span></span>
                    </div>
                  </div>
                  <div class="d-flex">
                    <button class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto"><i class="ni ni-bold-right" aria-hidden="true"></i></button>
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                      <i class="ni ni-box-2 text-white opacity-10"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Payement</h6>
                      <span class="text-xs">1 is active, <span class="font-weight-bold">40 closed</span></span>
                    </div>
                  </div>
                  <div class="d-flex">
                    <button class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto"><i class="ni ni-bold-right" aria-hidden="true"></i></button>
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                      <i class="ni ni-satisfied text-white opacity-10"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Happy users</h6>
                      <span class="text-xs font-weight-bold">+ 430</span>
                    </div>
                  </div>
                  <div class="d-flex">
                    <button class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto"><i class="ni ni-bold-right" aria-hidden="true"></i></button>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- FOOTER -->
      <?php include '../includes/footer.php' ?>
    </div>
  </main>

  <!-- //! DRIVER JS -->
  <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
  <script src="driver.js"></script>

  <!-- FIXED PLUGIN  -->
  <?php include '../includes/fixedplugin.php' ?>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/chartjs.min.js"></script>

  <!-- //! CHART JS Sales -->
  <script>
    var ctx1 = document.getElementById("chart-line").getContext("2d");

    var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

    gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
    gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
    gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');
    new Chart(ctx1, {
      type: "line",
      data: {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Mobile apps",
          tension: 0.4,
          borderWidth: 0,
          pointRadius: 0,
          borderColor: "#5e72e4",
          backgroundColor: gradientStroke1,
          borderWidth: 3,
          fill: true,
          data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
          maxBarThickness: 6

        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#fbfbfb',
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#ccc',
              padding: 20,
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });
  </script>

  <!-- //! CHART JS ABSCENCE -->
  <script>
    const ctx = document.getElementById("chart-bar").getContext("2d");
    const labels = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"];
    const data = {
      labels: labels,
      datasets: [{
        label: 'Abscence',
        data: [65, 59, 80, 81, 56, ],
        backgroundColor: [
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 159, 64, 0.2)',
          'rgba(255, 205, 86, 0.2)',
          'rgba(75, 192, 192, 0.2)',
          'rgba(54, 162, 235, 0.2)',
        ],
        borderColor: [
          'rgb(255, 99, 132)',
          'rgb(255, 159, 64)',
          'rgb(255, 205, 86)',
          'rgb(75, 192, 192)',
          'rgb(54, 162, 235)',
        ],
        borderWidth: 0
      }]
    };

    const config = {
      type: 'bar',
      data: data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top',
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              display: true,
              drawBorder: false,
              color: 'rgba(200, 200, 200, 0.2)',
            },
            ticks: {
              color: '#666',
              font: {
                size: 10,
              },
            },
          },
          x: {
            grid: {
              display: false,
            },
            ticks: {
              color: '#666',
              font: {
                size: 10,
              },
            },
            // Ajout de barThickness pour réduire la largeur des barres
            ticks: {
              callback: function(value, index, values) {
                return this.getLabelForValue(value);
              },
            },
            // Configurer la largeur des barres
            categoryPercentage: 0.5, // Réduit la largeur des barres
            barPercentage: 0.5, // Encore plus de réduction de la largeur
          },
        },
      },
    };
    new Chart(ctx, config);
  </script>

  <!-- //! LINE CHART BLEU ROSE -->
  <script>
    const options = {
      colors: ["#0d6efd", "#f62459"],
      series: [{
          name: "Garçons",
          color: "#0d6efd",
          data: <?= json_encode($chartData['garçon']) ?>
        },
        {
          name: "Filles",
          color: "#f62459",
          data: <?= json_encode($chartData['fille']) ?>
        }
      ],
      chart: {
        type: "bar",
        height: 320,
        fontFamily: "system-ui, -apple-system, sans-serif",
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: "70%",
          borderRadiusApplication: "end",
          borderRadius: 4
        }
      },
      tooltip: {
        shared: true,
        intersect: false,
        style: {
          fontFamily: "system-ui, -apple-system, sans-serif"
        },
        y: {
          formatter: function(value) {
            return value + " absence(s)";
          }
        }
      },
      states: {
        hover: {
          filter: {
            type: "darken",
            value: 1
          }
        }
      },
      stroke: {
        show: true,
        width: 0,
        colors: ["transparent"]
      },
      grid: {
        show: false,
        padding: {
          left: 2,
          right: 2,
          top: -14
        }
      },
      dataLabels: {
        enabled: false
      },
      legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'left',
        offsetY: -5,
        labels: {
          colors: '#6c757d'
        }
      },
      xaxis: {
        labels: {
          style: {
            fontFamily: "system-ui, -apple-system, sans-serif",
            colors: '#6c757d'
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        show: true,
        labels: {
          formatter: function(value) {
            return Math.floor(value);
          },
          style: {
            colors: '#6c757d'
          }
        }
      }
    };

    if (document.getElementById("column-chart") && typeof ApexCharts !== 'undefined') {
      const chart = new ApexCharts(document.getElementById("column-chart"), options);
      chart.render();
    }
  </script>


  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var option = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>

  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>