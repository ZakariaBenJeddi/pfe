<?php
require '../includes/DatabaseConnexion.php';
session_start();


if ($_SERVER["REQUEST_METHOD"] === "POST") { 
  header('Content-Type: application/json'); 
  try {
      // Validation des dates
      if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
          throw new Exception("Les dates sont requises");
      }

      // Nettoyage et validation des dates
      $start_date = filter_var($_POST['start_date'], FILTER_SANITIZE_STRING);
      $end_date = filter_var($_POST['end_date'], FILTER_SANITIZE_STRING);

      if (!$start_date || !$end_date) {
          throw new Exception("Format de date invalide");
      }

      // Conversion des dates au format MySQL
      $start_date = date("Y-m-d", strtotime($start_date));
      $end_date = date("Y-m-d", strtotime($end_date));

      // Requête SQL avec préparation
      $sql = "SELECT 
                  id_salle,
                  nom_salle,
                  etage,
                  capacite_salle,
                  nbr_chaise,
                  nbr_bureau,
                  nbr_tableau,
                  equipements,
                  date_creation
              FROM salle 
              WHERE date_creation BETWEEN :start_date AND :end_date
              ORDER BY date_creation DESC";

      $stmt = $dbh->prepare($sql);
      $stmt->execute([
          ':start_date' => $start_date,
          ':end_date' => $end_date
      ]);

      $results = $stmt->fetchAll(PDO::FETCH_OBJ);

      echo json_encode([
          'status' => 'success',
          'data' => $results,
          'count' => count($results)
      ]);

  } catch (Exception $e) {
      http_response_code(400);
      echo json_encode([
          'status' => 'error',
          'message' => $e->getMessage()
      ]);
  }
  exit;
}

if (empty($_SESSION['user'])) {
  header('location:sign-up.php');
}

$_SESSION['last_activity'] = time();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 300)) {
    session_unset();
    session_destroy();
    header("location:logout.php");
    exit;
}

//premier code 
$sql = "SELECT * FROM salle";
$query = $dbh->query($sql);
$results = $query->fetchAll(PDO::FETCH_OBJ);

//suppresion
try {
  // Configuration de PDO pour lever des exceptions en cas d'erreur
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Vérification de l'existence des paramètres GET et validation de l'ID
  if (!empty($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    // Si l'ID n'est pas valide, redirigez vers une page d'erreur ou arrêtez le script
    if ($id === false) {
      echo "<script>alert('ID invalide. Opération annulée.');</script>";
      exit;
    }

    // Requête sécurisée avec PDO
    $sql = "DELETE FROM salle WHERE id_salle = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);

    // Exécution de la requête et gestion des erreurs
    if ($query->execute()) {
      echo "<script>alert('Salle Bien Supprimée');</script>";

      // Utilisez une redirection sécurisée
      header("Location: salle.php");
      exit;
    } else {
      // Affichage d'un message d'erreur générique pour éviter de donner des détails à un attaquant
      echo "<script>alert('Erreur lors de la suppression.');</script>";
    }
  }
} catch (PDOException $e) {
  // Journalisez l'erreur dans un fichier sécurisé
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  echo "<script>alert('Une erreur est survenue. Veuillez réessayer plus tard.');</script>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<style>
  /* Customize the 'Show entries' select dropdown */
  .dataTables_length {
    margin-left: 15px !important;
  }

  .dataTables_length select {
    margin-left: 13px !important;
    margin-right: 5px !important;
    width: 60px;
    /* Adjust width */
    height: 35px;
    /* Adjust height */
    border: 1px solid #fff;
    border-radius: 10px;
    padding: 5px;
    color: #fff;
    background-color: #5e72e4;
    font-size: 14px;
  }

  /* Customize the search input */
  .dataTables_filter input {
    margin-right: 1.5rem !important;
    width: 200px;
    /* Adjust width */
    height: 35px;
    /* Adjust height */
    border: 1px solid #ccc;
    border-radius: 5px;
    padding-left: 10px;
    color: #333;
    font-size: 14px;
  }

  /* Customize pagination buttons */
  .dataTables_paginate .paginate_button {
    background-color: #007bff;
    /* Set background color */
    color: #fff;
    padding: 5px 10px;
    border-radius: 5px;
    margin: 0 2px;
    font-size: 14px;
    transition: background-color 0.3s;
  }

  .dataTables_paginate .paginate_button:hover {
    background-color: #0056b3;
    /* Darker color on hover */
  }

  /* Customize active pagination button */
  .dataTables_paginate .paginate_button.current {
    background-color: #0056b3;
    color: #fff;
    font-weight: bold;
  }

  .dataTables_paginate .paginate_button {
    background-color: #5e72e3;
  }


  #table_salle_info {
    margin-left: 15px !important;
  }

  .dataTables_wrapper .dataTables_length,
  .dataTables_wrapper .dataTables_filter,
  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_processing,
  .dataTables_wrapper .dataTables_paginate {
    color: #cfd3db !important;
  }

  /* Remove border between table rows */
  .dataTable tbody tr {
    border-bottom: none;
    border-color: #f4f5f7;
    /* Remove bottom border for each row */
  }

  #table_salle {
    border-bottom: 1px solid #f4f5f7;
  }

  /* display action button */
  #dropdownMenuButton {
    box-shadow: none !important;
  }

  .dropdown .dropdown-menu {
    display: auto !important;
  }

  #changewidth {
    width: 6rem !important;
    min-width: 0 !important;
  }
</style>
<!-- HEAD -->
<?php include '../includes/head.php' ?>

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
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
        <li class="nav-item">
          <a class="nav-link active" href="../pages/dashboard.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="../pages/tables.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Tables</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="../pages/salle.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-building text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Salles</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="../pages/enseignant.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Enseignant</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="../pages/billing.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-credit-card text-success text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Billing</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="../pages/calendrier.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Emplois du Temps</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="../pages/virtual-reality.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-app text-info text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Virtual Reality</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="../pages/rtl.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-world-2 text-danger text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">RTL</span>
          </a>
        </li>
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account pages</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="../pages/profile.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Profile</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="../pages/sign-in.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-copy-04 text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Sign In</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="../pages/sign-up.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-collection text-info text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Sign Up</span>
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
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-white active" aria-current="page">Tables</li>
          </ol>
          <h6 class="font-weight-bolder text-white mb-0">Tables</h6>
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
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0 d-flex flex-wrap justify-content-between align-items-center text-center text-md-start">
              <div class="mb-2 mb-md-0 flex-grow-1 text-center text-md-start">
                <h6 class="text-primary">Salles</h6>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
                <a class="btn btn-primary btn-sm" href="ajouter_salle.php">Ajouter Salle</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
              </div>
            </div>


            <!-- Edit data -->
            <div id="editData" class="modal fade text-center" tabindex="-1">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Modifier Salle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body" id="info_update">
                    <!-- Le contenu sera chargé ici par AJAX -->
                    <?php
                    include("edit_salle.php");
                    ?>
                  </div>
                  <div class="modal-footer ">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                  </div>
                </div>
              </div>
            </div>
            <form method="post">
              <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                  <table class="table align-items-center mb-0" id="table_salle">
                    <thead>
                      <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nom Salle</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Etage</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Capacite Eleve</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">N° Chaise</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">N° Bureau</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">N° Tableau</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Equipement</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                      </tr>
                    </thead>
                    <tbody id="tableBody">
                      <?php if ($query->rowCount() > 0) { ?>
                        <?php foreach ($results as $result) : ?>
                          <tr>
                            <td>
                              <div class="d-flex px-2 py-1">
                                <div>
                                  <img src="https://elaraki.ac.ma/images/logo2.png" class="avatar avatar-sm me-3" alt="user1">
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                  <?= $result->nom_salle; ?>
                                </div>
                              </div>
                            </td>
                            <td class="align-middle text-center text-sm">
                              <p class="text-xs font-weight-bold mb-0"><?= $result->etage; ?></p>
                            </td>
                            <td>
                              <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->capacite_salle; ?></p>
                            </td>
                            <td class="align-middle text-center">
                              <p class="text-xs font-weight-bold mb-0"><?= $result->nbr_chaise; ?></p>
                            </td>
                            <td class="align-middle text-center">
                              <p class="text-xs font-weight-bold mb-0"><?= $result->nbr_bureau; ?></p>
                            </td>
                            <td class="align-middle text-center">
                              <p class="text-xs font-weight-bold mb-0"><?= $result->nbr_tableau; ?></p>
                            </td>
                            <td class="align-middle text-center">
                              <?php
                              $equipements = $result->equipements;
                              $equipement = explode("-", $equipements);
                              foreach ($equipement as $equi) :
                              ?>
                                <p class="text-xs font-weight-bold mb-0">-<?= $equi; ?></p>
                              <?php endforeach; ?>
                            </td>
                            <td class="align-middle text-center">
                              <div class="">
                                <div class="dropdown">
                                  <button id="dropdownMenuButton" type="button" class="btn btn-sm dropdown-toggle border-none " data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v text-xs" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-expanded="false"></i>
                                  </button>
                                  <ul id="changewidth" class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <li class="text-center">
                                      <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true" id="<?php echo $result->id_salle ?>"></i>
                                      </a>
                                    </li>
                                    <li class="text-center">
                                      <a href="description_salle.php?id=<?= $result->id_salle ?>" class="dropdown-item">
                                        <i class="fas fa-eye text-primary opacity-10 fa-sm"></i>
                                      </a>
                                    </li>
                                    <li class="text-center">
                                      <a href="salle.php?id=<?= $result->id_salle ?>&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                                        <i class="ni ni-fat-remove text-danger opacity-10" id="<?= $result->id_salle ?>"></i>
                                      </a>
                                    </li>
                                  </ul>
                                </div>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php } else { ?>
                        <tr rowspan="7" class="text-center">
                          <td class="text-center">
                            No Content
                          </td>
                        </tr>
                      <?php  } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- FOOTER -->
      <?php include '../includes/footer.php' ?>

    </div>
  </main>

  <!-- Ton script AJAX pour l'édition de la salle -->
  <!-- Export Functio -->
  <script>
    $(document).ready(function() {
      $('#table_salle').DataTable(); // Initialize DataTable
    });

    function expo() {
      // Obtain DataTable instance
      var table = $('#table_salle').DataTable();

      // Create data array for headers and rows
      var data = [];
      var headers = [];

      // Extract headers, skipping "Action" column
      table.columns().every(function() {
        if (this.header().textContent !== "Action") {
          headers.push(this.header().textContent.trim()); // Trim to remove extra whitespace
        }
      });
      data.push(headers);

      // Extract filtered data
      var filteredData = table.rows({
        filter: 'applied'
      }).data();

      filteredData.each(function(valueArray) {
        var rowData = [];
        valueArray.forEach(function(value, index) {
          if (index !== 7) { // Skip "Action" column
            // Use jQuery to get the text content directly
            rowData.push($('<div>').html(value).text().trim()); // Wrap value in a div to extract text
          }
        });
        data.push(rowData);
      });

      // Export to Excel with ExcelJS
      var workbook = new ExcelJS.Workbook();
      var worksheet = workbook.addWorksheet('Data Export');

      data.forEach(function(row) {
        worksheet.addRow(row);
      });

      workbook.xlsx.writeBuffer().then(function(buffer) {
        var blob = new Blob([buffer], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'exported_data.xlsx';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
      });
    }
  </script>

  <!-- Edit data -->
  <script type="text/javascript">
    $(document).ready(function() {
      $(document).on('click', '.edit_data', function() {
        var edit_id = $(this).attr('id');
        $.ajax({
          url: "edit_salle.php",
          type: "post",
          data: {
            edit_id: edit_id
          },
          success: function(data) {
            $("#info_update").html(data);
            $("#editData").modal('show');
          }
        });
      });
    });
  </script>

  <!-- DropDown Actions -->
  <script>
    document.getElementById('dropdownMenuButton').addEventListener('click', function() {
      var dropdownMenu = document.querySelector('.dropdown-menu');
      dropdownMenu.classList.toggle('show'); // Affiche ou cache le menu au clic du bouton
    });
  </script>

  <!-- //* Date Picker -->
  <!-- //* AJAX salle intervalle date  -->
    <script>
      $(function() {
        // Configuration du DateRangePicker
        $('#daterange').daterangepicker({
            opens: 'left',
            autoUpdateInput: true,
            locale: {
                format: 'MM/DD/YYYY', // Format attendu par votre code PHP
                applyLabel: 'Valider',
                cancelLabel: 'Annuler',
                fromLabel: 'Du',
                toLabel: 'Au',
                customRangeLabel: 'Période personnalisée',
                daysOfWeek: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
                monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                firstDay: 1
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        }, function(start, end, label) {
            // Callback pour la sélection de dates
            const tableBody = $('#tableBody');
            
            $.ajax({
                url: '', // Fichier actuel
                method: 'POST',
                data: { 
                    start_date: start.format('MM/DD/YYYY'), 
                    end_date: end.format('MM/DD/YYYY') 
                },
                dataType: 'json',
                success: function(response) {
                    // Vider le tableau
                    tableBody.empty();
                    
                    // Vérifier s'il y a des résultats
                    if (response.status === 'success' && response.count > 0) {
                        // Parcourir et ajouter chaque salle
                        response.data.forEach(function(salle) {
                            tableBody.append(`
                                <tr>
                                    <td  class="align-middle text-center text-sm">
                                      <p class="text-xs font-weight-bold mb-0">${salle.nom_salle}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                      <p class="text-xs font-weight-bold mb-0">${salle.etage}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                      <p class="text-xs font-weight-bold mb-0">${salle.capacite_salle}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                      <p class="text-xs font-weight-bold mb-0">${salle.nbr_chaise}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                      <p class="text-xs font-weight-bold mb-0">${salle.nbr_bureau}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                      <p class="text-xs font-weight-bold mb-0">${salle.nbr_tableau}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                      <?php
                                      $equipements = $result->equipements;
                                      $equipement = explode("-", $equipements);
                                      foreach ($equipement as $equi) :
                                      ?>
                                        <p class="text-xs font-weight-bold mb-0">-<?= $equi; ?></p>
                                      <?php endforeach; ?>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                      <div class="">
                                        <div class="dropdown">
                                          <button id="dropdownMenuButton" type="button" class="btn btn-sm dropdown-toggle border-none " data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v text-xs" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-expanded="false"></i>
                                          </button>
                                          <ul id="changewidth" class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <li class="text-center">
                                              <a href="javascript:void(0);" class="dropdown-item">
                                                <i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true" id="<?php echo $result->id_salle ?>"></i>
                                              </a>
                                            </li>
                                            <li class="text-center">
                                              <a href="description_salle.php?id=<?= $result->id_salle ?>" class="dropdown-item">
                                                <i class="fas fa-eye text-primary opacity-10 fa-sm"></i>
                                              </a>
                                            </li>
                                            <li class="text-center">
                                              <a href="salle.php?id=<?= $result->id_salle ?>&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                                                <i class="ni ni-fat-remove text-danger opacity-10" id="<?= $result->id_salle ?>"></i>
                                              </a>
                                            </li>
                                          </ul>
                                        </div>
                                      </div>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        // Aucun résultat
                        tableBody.append(`
                            <tr>
                                <td colspan="9" class="text-center">Aucune salle trouvée pour cette période</td>
                            </tr>
                        `);
                    }
                },
                error: function(xhr) {
                    // Gestion des erreurs
                    console.error('Erreur de requête:', xhr);
                    tableBody.html(`
                        <tr>
                            <td colspan="9" class="text-center text-danger">
                                Erreur lors de la récupération des données
                            </td>
                        </tr>
                    `);
                }
            });
        });
      });
    </script>


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
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>