<?php
require '../includes/DatabaseConnexion.php';
session_start();
if (empty($_SESSION['user'])) {
  header('location:sign-up.php');
}

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


//* classe ajax
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
    $sql = "SELECT * FROM classe WHERE date_creation BETWEEN :start_date AND :end_date ORDER BY date_creation DESC";

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


$sql = "SELECT classe.* ,filiere.nom_filiere , niveau.nom_niveau FROM classe 
          JOIN filiere ON classe.filiere_id  = filiere.id_filiere 
          JOIN niveau ON classe.niveau_id  = niveau.id_niveau";
$query = $dbh->query($sql);
$results = $query->fetchAll(PDO::FETCH_OBJ);


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
    $sql = "DELETE FROM classe WHERE id_classe = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    // Exécution de la requête et gestion des erreurs
    if ($query->execute()) {
      echo "<script>alert('Classe Bien Supprimée');</script>";

      // Utilisez une redirection sécurisée
      header("Location: classes.php");
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
          <a class="nav-link" href="../pages/sign-out.php">
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
                <h6 class="text-primary">Ensaignant</h6>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
                <a class="btn btn-primary btn-sm" href="ajouter_enseignant.php">Ajouter Ensaignant</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
              </div>
            </div>
            <hr>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0" id="table_classe">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Classe</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Niveau</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">Filiere</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">capacite</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">date creation</th>
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
                                <img src="../assets/img/team-2.jpg" class="avatar avatar-sm me-3" alt="user1">
                              </div>
                              <div class="d-flex flex-column justify-content-center">
                                <p class="text-secondary text-xs font-weight-bold"><?= $result->nom_classe ?></p>
                              </div>
                            </div>
                          </td>
                          <td  class="align-middle text-center">
                            <p class="text-secondary text-xs font-weight-bold"><?= $result->nom_niveau ?></p>
                          </td>
                          <td>
                            <p class="text-secondary text-xs font-weight-bold"><?= $result->nom_filiere ?></p>
                          </td>
                          <?php if ($result->statut === 'Active') { ?>
                            <td class="align-middle text-center text-sm">
                              <span class="badge badge-sm bg-gradient-success">Active</span>
                            </td>
                          <?php } else { ?>
                            <td class="align-middle text-center text-sm">
                              <span class="badge badge-sm bg-gradient-success">Inactive</span>
                            </td>
                          <?php } ?>
                          <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= $result->capacite ?></span>
                          </td>
                          <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= $result->date_creation ?></span>
                          </td>
                          <td class="align-middle text-center">
                            <div class="d-flex">
                              <a href="edit_classe.php?id=<?= $result->id_classe ?>" class="dropdown-item">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <a href="description_classe.php?id=<?= $result->id_classe ?>" class="dropdown-item">
                                <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                              </a>
                              <a href="classes.php?id=<?= $result->id_classe ?>&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                                <i class="fas fa-trash fa-sm text-danger opacity-8" id="<?= $result->id_classe ?>"></i>
                              </a>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- FOOTER -->
      <?php include '../includes/footer.php' ?>

    </div>
  </main>

  <!-- FIXED PLUGIN  -->
  <?php include '../includes/fixedplugin.php' ?>

  <!-- call script export ensaignant -->
  <!-- <script src="../assets/js/ensaignant/export.js"></script> -->
  <script type="text/javascript">
    $(document).ready(function() {
      $('#table_classe').DataTable(); // Initialize DataTable
    });

    function expo() {
      // Obtain DataTable instance
      var table = $('#table_classe').DataTable();

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
        a.download = 'classess.xlsx';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
      });
    }
  </script>

  <!-- //* Date Picker -->
  <!-- //* AJAX eleves intervalle date  -->
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
              // Parcourir et ajouter chaque eleve
              response.data.forEach(function(enseignant) {
                tableBody.append(`
                        <tr>
                          <td>
                            <div class="d-flex px-2 py-1">
                              <div>
                                <img src="../assets/img/team-2.jpg" class="avatar avatar-sm me-3" alt="user1">
                              </div>
                              <div class="d-flex flex-column justify-content-center">
                                <p class="text-secondary text-xs font-weight-bold"><?= $result->nom_classe ?></p>
                              </div>
                            </div>
                          </td>
                          <td  class="align-middle text-center">
                            <p class="text-secondary text-xs font-weight-bold"><?= $result->nom_niveau ?></p>
                          </td>
                          <td>
                            <p class="text-secondary text-xs font-weight-bold"><?= $result->nom_filiere ?></p>
                          </td>
                          <?php if ($result->statut === 'Active') { ?>
                            <td class="align-middle text-center text-sm">
                              <span class="badge badge-sm bg-gradient-success">Active</span>
                            </td>
                          <?php } else { ?>
                            <td class="align-middle text-center text-sm">
                              <span class="badge badge-sm bg-gradient-success">Inactive</span>
                            </td>
                          <?php } ?>
                          <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= $result->capacite ?></span>
                          </td>
                          <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= $result->date_creation ?></span>
                          </td>
                          <td class="align-middle text-center">
                            <div class="d-flex">
                              <a href="edit_classe.php?id=<?= $result->id_classe ?>" class="dropdown-item">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <a href="description_classe.php?id=<?= $result->id_classe ?>" class="dropdown-item">
                                <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                              </a>
                              <a href="classes.php?id=<?= $result->id_classe ?>&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                                <i class="fas fa-trash fa-sm text-danger opacity-8" id="<?= $result->id_classe ?>"></i>
                              </a>
                            </div>
                          </td>
                        </tr>
                            `);
              });
            } else {
              // Aucun résultat
              tableBody.append(`
                            <tr>
                                <td colspan="8" class="text-center">Aucune Classe trouvée pour cette période</td>
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