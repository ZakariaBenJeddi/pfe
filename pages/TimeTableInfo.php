<?php
include_once '../includes/DatabaseConnexion.php';
session_start();

if (empty($_SESSION['user'])) {
  header('location:sign-in.php');
}

$_SESSION['last_activity'] = time();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 300)) {
  session_unset();
  session_destroy();
  header("location:logout.php");
  exit;
}

$sql = "SELECT ecm.* ,e.nom_enseignant , m.nom_matiere ,c.nom_classe
        FROM enseignant_classes_matieres ecm
        JOIN enseignant e ON e.id_enseignant = ecm.enseignant_id
        JOIN matiere m ON m.id_matiere = ecm.matiere_id
        JOIN classe c ON c.id_classe = ecm.classe_id
        ORDER BY e.nom_enseignant ASC";
$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_ASSOC);
// var_dump($results);

if (isset($_GET['action'])) {
  header('Content-Type: application/json');

  switch ($_GET['action']) {
    case 'get_classes':
      if (isset($_GET['enseignant_id'])) {
        $prof_id = intval($_GET['enseignant_id']);
        $query = "SELECT DISTINCT c.id_classe, c.nom_classe 
                  FROM classe c 
                  JOIN enseignant_classes_matieres ecm ON c.id_classe = ecm.classe_id 
                  WHERE ecm.enseignant_id = ?
                  ORDER BY c.nom_classe";
        $stmt = $dbh->prepare($query);
        $stmt->execute([$prof_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      }
      exit;

    case 'get_matieres':
      if (isset($_GET['classe_id'])) {
        $classe_id = intval($_GET['classe_id']);
        $query = "SELECT DISTINCT m.id_matiere, m.nom_matiere 
                  FROM matiere m 
                  JOIN enseignant_classes_matieres ecm ON m.id_matiere = ecm.matiere_id 
                  WHERE ecm.classe_id = ?
                  ORDER BY m.nom_matiere";
        $stmt = $dbh->prepare($query);
        $stmt->execute([$classe_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      }
      exit;
  }
}

if (isset($_POST['valid'])) {
  try {
    $enseignant_id = isset($_POST['enseignant_id']) ? intval($_POST['enseignant_id']) : 0;
    $classe_id = isset($_POST['classe_id']) ? intval($_POST['classe_id']) : 0;
    $matiere_id = isset($_POST['matiere_id']) ? intval($_POST['matiere_id']) : 0;

    if ($enseignant_id && $classe_id && $matiere_id) {
      // Vérifier si cette affectation existe déjà
      $check_query = "SELECT id FROM enseignant_classes_matieres 
                          WHERE enseignant_id = ? AND classe_id = ? AND matiere_id = ?";
      $check_stmt = $dbh->prepare($check_query);
      $check_stmt->execute([$enseignant_id, $classe_id, $matiere_id]);

      if ($check_stmt->rowCount() > 0) {
        echo "<script>alert('Cette affectation existe déjà!');</script>";
      } else {
        $query = "INSERT INTO enseignant_classes_matieres 
                         (enseignant_id, classe_id, matiere_id) 
                         VALUES (?, ?, ?)";
        $stmt = $dbh->prepare($query);
        $stmt->execute([$enseignant_id, $classe_id, $matiere_id]);
        echo "<script>alert('Affectation ajoutée avec succès!');</script>";
      }
      echo "<script>window.location.href = window.location.href;</script>";
    } else {
      echo "<script>alert('Veuillez remplir tous les champs.');</script>";
    }
  } catch (PDOException $e) {
    echo "<script>alert('Erreur lors de l\'enregistrement: " . addslashes($e->getMessage()) . "');</script>";
  }
}

// Code pour la suppression
if (isset($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
  try {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id === false) {
      echo "<script>alert('Paramètre invalide. Opération annulée.');</script>";
      exit;
    }

    $sql = "DELETE FROM enseignant_classes_matieres WHERE id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);

    if ($query->execute()) {
      echo "<script>
                    alert('Affectation bien supprimée');
                    window.location.href = 'TimeTableInfo.php';
                  </script>";
      exit;
    } else {
      echo "<script>alert('Erreur lors de la suppression.');</script>";
    }
  } catch (PDOException $e) {
    error_log($e->getMessage(), 3, 'error.log');
    echo "<script>alert('Une erreur est survenue. Veuillez réessayer plus tard.');</script>";
    exit;
  }
}
?>


<!DOCTYPE html>
<html lang="en">
<!-- HEAD -->
<?php include '../includes/head.php' ?>
<style>
  body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
  }

  h1 {
    text-align: center;
    color: #333;
  }

  form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);

  }

  label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #555;
  }


  button {
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
  }


  .success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    margin: 15px 0;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    text-align: center;
  }
</style>

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
        <!-- <img class="w-50 mx-auto" src="../assets/img/illustrations/icon-documentation.svg" alt="sidebar_illustration"> -->
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
    <div class=" pb-0 mt-5 me-5 text-end text-primary">
      <a href="TimeTableConfig.php" class="btn btn-light px-3">configurer donnes</a>
    </div>
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0 text-center">
              <h4>Affecter Enseignant & Matiere & groupe</h4>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <form method="POST">
                <!-- Professeurs Select -->
                <label for="professeur_id">Sélectionner un Formatuer :</label>
                <select class="form-control mb-3" id="professeur_id" name="professeur_id" require>
                  <option value="">Choisir un Formatuer</option>
                  <?php
                  $query = "SELECT id_enseignant, nom_enseignant FROM enseignant ORDER BY nom_enseignant";
                  $stmt = $pdo->query($query);
                  while ($prof = $stmt->fetch()) {
                    echo "<option value='" . htmlspecialchars($prof['id_enseignant']) . "'>" .
                      htmlspecialchars($prof['nom_enseignant']) . "</option>";
                  }
                  ?>
                </select>

                <!-- Groupes Select -->
                <label for="groupe_id">Sélectionner un groupe :</label>
                <select class="form-control mb-3" id="groupe_id" name="groupe_id" disabled require>
                  <option value="">Choisir un groupe</option>
                </select>

                <!-- Matières Select -->
                <label for="matiere_id">Sélectionner un Matiere :</label>
                <select class="form-control mb-3" id="matiere_id" name="matiere_id" disabled require>
                  <option value="">Choisir un Matiere</option>
                </select>

                <div class="row">
                  <div class="col-lg-6">
                    <!-- Nombre de séances -->
                    <label for="nbr_seance">Nombre de séances :</label>
                    <input class="form-control mb-5" type="number" id="nbr_seance" name="nbr_seance" min="1" require>
                  </div>
                  <div class="col-lg-6">
                    <!-- date senance -->
                    <label for="nbr_seance">Date :</label>
                    <input type="text" class="form-control text-center  mb-5" id="daterange" name="daterange" value="" require />
                  </div>
                </div>
                <input type="submit" name="valid" class="btn btn-primary w-100 py-3 px-5" value="Valider">
                <a href="TimeTableInsertIntoCalendar.php" class="btn btn-success w-100 py-3 px-5 mt-3">Generer</a>
              </form>
            </div>
          </div>

        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header text-center">

            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Enseignant</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">groupe</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Matiere</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acion</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($results as $result) { ?>
                      <tr>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div>
                              <i class="ni ni-single-02 text-primary opacity-10"></i>
                            </div>
                            <div class="d-flex flex-column justify-content-center ms-3">
                              <h6 class="mb-0 text-sm"><?= $result['nom_p'] ?></h6>
                            </div>
                          </div>
                        </td>
                        <td>
                          <p class="text-xs font-weight-bold mb-0"><?= $result['nom_g'] ?></p>
                        </td>
                        <td class="align-middle text-center text-sm">
                          <p class="text-xs font-weight-bold mb-0"><?= $result['nom_m'] ?></p>
                        </td>
                        <td class="align-middle text-center text-sm">
                          <div class="d-flex justify-content-center align-items-center">
                            <a href="TimeTableInfo.php?id=<?= $result['id'] ?>&professeur_id=<?= $result['professeur_id'] ?>&groupe_id=<?= $result['groupe_id'] ?>&matiere_id=<?= $result['matiere_id'] ?>&nbr_seance=<?= $result['nbr_seance'] ?>&date_debut=<?= $result['date_debut'] ?>&date_fin=<?= $result['date_fin'] ?>&del=1" onClick="return confirm('Etes-vous sûr que vous voulez supprimer Affectation de?\nFotmateur(trice): <?= addslashes($result['nom_p']) ?>\nGroupe: <?= addslashes($result['nom_g']) ?>\nNombre Seance :<?= addslashes($result['nbr_seance']) ?> ')">
                              <i class="fas fa-trash fa-sm text-danger opacity-8"></i>
                            </a>
                          </div>
                        </td>
                      </tr>
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

    <!-- FOOTER -->
    <?php include '../includes/footer.php' ?>
  </main>
  <!-- FIXED PLUGIN  -->
  <?php // include '../includes/fixedplugin.php' 
  ?>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <!-- formateur groupe modules ajax -->
  <script>
    document.getElementById('professeur_id').addEventListener('change', function() {
      const profId = this.value;
      const groupeSelect = document.getElementById('groupe_id');
      const matiereSelect = document.getElementById('matiere_id');

      if (profId) {
        groupeSelect.disabled = false;
        fetch(`?action=get_groupes&professeur_id=${profId}`)
          .then(response => response.json())
          .then(data => {
            groupeSelect.innerHTML = '<option value="">Choisir un groupe</option>';
            data.forEach(groupe => {
              groupeSelect.innerHTML += `<option value="${groupe.id}">${groupe.nom}</option>`;
            });
          })
          .catch(error => console.error('Erreur:', error));
      } else {
        groupeSelect.disabled = true;
        matiereSelect.disabled = true;
        groupeSelect.innerHTML = '<option value="">Choisir un groupe</option>';
        matiereSelect.innerHTML = '<option value="">Choisir une matière</option>';
      }
    });

    document.getElementById('groupe_id').addEventListener('change', function() {
      const groupeId = this.value;
      const matiereSelect = document.getElementById('matiere_id');

      if (groupeId) {
        matiereSelect.disabled = false;
        fetch(`?action=get_matieres&groupe_id=${groupeId}`)
          .then(response => response.json())
          .then(data => {
            matiereSelect.innerHTML = '<option value="">Choisir une matière</option>';
            data.forEach(matiere => {
              matiereSelect.innerHTML += `<option value="${matiere.id}">${matiere.nom}</option>`;
            });
          })
          .catch(error => console.error('Erreur:', error));
      } else {
        matiereSelect.disabled = true;
        matiereSelect.innerHTML = '<option value="">Choisir une matière</option>';
      }
    });

    document.getElementById('assignmentForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      formData.append('action', 'save');

      fetch('', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            this.reset();
            location.reload();
          } else {
            alert('Erreur : ' + data.message);
          }
        })
        .catch(error => console.error('Erreur:', error));
    });
  </script>
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