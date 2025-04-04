<?php

use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Format;

session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}
require '../../includes/DatabaseConnexion.php';

//* deconnexion
require('../../includes/deconnexion_5s.php');

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
<?php include '../../includes/admin/head_admin.php' ?>
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
  <?php require('../../includes/admin/aside_admin.php') ?>
  <?php //require('../../includes/aside_admin.php') ?>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <?php require('../../includes/admin/navbar_admin.php') ?>
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
                      <?=  $nbr_eleves ?>
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

                <a href="absence.php" class="btn btn-primary d-flex align-items-center">
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
                <div class="carousel-item h-100" style="background-image: url('../../assets/img/school/projet\ presentation.png');
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
      <?php include '../../includes/footer.php' ?>
    </div>
  </main>

  <!-- //! DRIVER JS -->
  <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
  <script src="driver.js"></script>

  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>
  <!--   Core JS Files   -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/chartjs.min.js"></script>

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
  <script src="../../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>