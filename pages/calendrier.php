<?php
session_start();
// $username = $_SESSION['lastname'];

if (empty($_SESSION['user'])) {
    header('location:sign-in.php');
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

use function PHPSTORM_META\type;

require_once('db-connect.php');

// Connexion à la base de données
$pdo_matiere_prof_classe = new PDO('mysql:host=localhost;dbname=emploi_du_temps_2acc;charset=utf8', 'root', '');

// Vérifier si l'ID de la matière est transmis
if (isset($_GET['matiere_id'])) {
    $matiereId = $_GET['matiere_id'];

    // Requête pour récupérer les enseignants
    $query = "
        SELECT DISTINCT p.id, p.nom
        FROM professeurs2 p
        JOIN professeurs_classes_matieres2 pcm ON pcm.professeur_id = p.id
        WHERE pcm.matiere_id = :matiere_id
    ";

    $stmt = $pdo_matiere_prof_classe->prepare($query);
    $stmt->execute(['matiere_id' => $matiereId]);

    $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les données en JSON
    echo json_encode($professeurs);
    exit();
}

if (isset($_GET['professeur_id'])) {
    $professerId = $_GET['professeur_id'];

    // Requête pour récupérer les classes
    $query = "
        SELECT DISTINCT c.id, c.nom
        FROM classes2 c
        JOIN professeurs_classes_matieres2 pcm ON pcm.classe_id = c.id
        WHERE pcm.professeur_id = :professeur_id
    ";

    $stmt = $pdo_matiere_prof_classe->prepare($query);
    $stmt->execute(['professeur_id' => $professerId]);

    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les données en JSON
    header('Content-Type: application/json');
    echo json_encode($classes);
    exit();
}


$conn3 = new mysqli('localhost', 'root', '', 'dummy_db');
$prof3 = $conn3->query("SELECT DISTINCT professeur FROM schedule_list");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/icons/flags/AU.png"><?php //! changer lien   
                                                                                ?>
    <title>
        Emplois du temps
    </title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="../assets/fullcalendar/lib/main.min.css">
    <!-- jQuery -->
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="../assets/js/bootstrap.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="../assets/fullcalendar/lib/main.min.js"></script>

    <!-- argon -->
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/argon-dashboard.css?v=2.0.4" rel="stylesheet" />


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <!-- //!DATA -->
    <script src="calendar.js"></script>
    <style>
        :root {
            --bs-success-rgb: 71, 222, 152 !important;
        }

        html,
        body {
            height: 100%;
            width: 100%;
            font-family: "Open Sans", sans-serif;
            ;
        }

        .btn-info.text-light:hover,
        .btn-info.text-light:focus {
            background: #000;
        }

        table,
        tbody,
        td,
        tfoot,
        th,
        thead,
        tr {
            border-color: #ededed !important;
            border-style: solid;
            border-width: 1px !important;
        }

        /* Styles pour le conteneur de chargement */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Fond semi-transparent */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        /* Styles pour l'animation (spinner) */
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top: 5px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Style pour la version PC */
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="g-sidenav-show bg-gray-100">
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
        <!-- <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main"> -->
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
                <a class="nav-link" href="../pages/classes.php">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-building text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Classes</span>
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
                <a class="nav-link" href="../pages/sign-in.php">
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
    <main class="main-content position-relative border-radius-lg">
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
        <div class="container-fluid py-4">
            <div class="container py-5 " style="margin-top: 13rem !important;" id="page-container">
                <div class="row mb-5">
                    <p>ajouter le padding au seance et le text ne sort pas hors de sont container</p>
                    
                    <div class="col-lg-4">
                        <div class="filter-group">
                            <label for="teacher-select" class="text-light">Professeur:</label>
                            <select class="form-select" id="teacher-select">
                                <option value="">Tous les professeurs</option>
                                <?php foreach ($prfs as $prof) : ?>
                                    <option value="<?= htmlspecialchars($prof['nom']) ?>"><?= htmlspecialchars($prof['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="filter-group">
                            <label for="group-select" class="text-light">Groupe:</label>
                            <select class="form-select" id="group-select">
                                <option value="">Tous les groupes</option>
                                <?php foreach ($grps as $groupe) : ?>
                                    <option value="<?= htmlspecialchars($groupe['nom']) ?>"><?= htmlspecialchars($groupe['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="filter-group">
                            <label for="room-select" class="text-light">Salle:</label>
                            <select class="form-select" id="room-select">
                                <option value="">Toutes les salles</option>
                                <?php foreach ($slls as $salle) : ?>
                                    <option value="<?= htmlspecialchars($salle['nom']) ?>"><?= htmlspecialchars($salle['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                        <div id="loading-screen" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                        <div id="calendar"></div>
                    </div>
                    <div class="col-md-3 mt-lg-0 mt-5">
                        <div class="d-flex justify-content-center mb-2">
                            <button type="button" class="btn btn-primary btn-sm me-1" id="import-btn">Importer</button>
                            <a href="export_excel.php" class="btn btn-primary btn-sm ms-1">Exporter</a>
                        </div>
                        <div class="cardt rounded-0 shadow">
                            <div class="card-header bg-gradient bg-primary text-light">
                                <h5 class="card-title text-center">Schedule Form</h5>
                            </div>
                            <div class="card-body">
                                <div class="container-fluid">
                                    <form action="" method="get" id="matiere-form">
                                        <div class="form-group mb-2">
                                            <label for="matiere-select" class="control-label">Matière</label>
                                            <select class="text-sm" name="matiere_id" id="matiere-select">
                                                <option value="">Sélectionnez une matière</option>
                                                <?php foreach ($matieres as $matiere) : ?>
                                                    <option value="<?= $matiere['id'] ?>"><?= $matiere['nom'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="professeur-select" class="control-label">Enseignant</label>
                                            <select class="text-sm" name="professeur_id" id="professeur-select">
                                                <option value="">Choisissez un enseignant</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="classe-select" class="control-label">Classes</label><br>
                                            <select class="text-sm" name="classe-select" id="classe-select">
                                                <option value="">Choisissez une Classes</option>
                                            </select>
                                        </div>
                                    </form>
                                    <form action="save_schedule.php" method="post" id="schedule-form">
                                        <input type="hidden" name="id" value="">
                                        <input type="hidden" name="professeur" id="professeur-value">
                                        <div class="form-group mb-2">
                                            <label for="title" class="control-label">Title</label>
                                            <input type="text" class="form-control form-control-sm rounded-0" name="title" id="title" value='a' required>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="description" class="control-label">Description</label>
                                            <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="description" required>a</textarea>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="title" class="control-label">salle</label>
                                            <input type="text" class="form-control form-control-sm rounded-0" name="salle" id="salle" value='b' required>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="start_datetime" class="control-label">Start</label>
                                            <input type="datetime-local" value="2024-11-13T09:00" class="form-control form-control-sm rounded-0" name="start_datetime" id="start_datetime" required>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="end_datetime" class="control-label">End</label>
                                            <input type="datetime-local" value="2024-11-13T17:00" class="form-control form-control-sm rounded-0" name="end_datetime" id="end_datetime" required>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-center">
                                    <button class="btn btn-primary btn-sm rounded-0" type="submit" form="schedule-form"><i class="fa fa-save"></i> Save</button>
                                    <button class="btn btn-default border btn-sm rounded-0" type="reset" form="schedule-form"><i class="fa fa-reset"></i> Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Event Details Modal -->
            <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-details-modal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-0">
                        <div class="modal-header rounded-0">
                            <h5 class="modal-title">Schedule Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body rounded-0">
                            <div class="container-fluid">
                                <dl>
                                    <dt class="text-muted">Title</dt>
                                    <dd id="title" class="fw-bold fs-4"></dd>
                                    <dt class="text-muted">Description</dt>
                                    <dd id="description" class=""></dd>
                                    <dt class="text-muted">salle</dt>
                                    <dd id="salle" class=""></dd>
                                    <dt class="text-muted">Prof</dt>
                                    <dd id="professeur" class=""></dd>
                                    <dt class="text-muted">Start</dt>
                                    <dd id="start" class=""></dd>
                                    <dt class="text-muted">End</dt>
                                    <dd id="end" class=""></dd>
                                </dl>

                            </div>
                        </div>
                        <div class="modal-footer rounded-0">
                            <div class="text-end">
                                <button type="button" class="btn btn-primary btn-sm rounded-0" id="edit" data-id="">Edit</button>
                                <button type="button" class="btn btn-danger btn-sm rounded-0" id="delete" data-id="">Delete</button>
                                <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Event Details Modal -->
    </main>
    <?php
    $schedules = $conn->query("SELECT * FROM `schedule_list`");
    $sched_res = [];
    foreach ($schedules->fetch_all(MYSQLI_ASSOC) as $row) {
        $row['sdate'] = date("F d, Y h:i A", strtotime($row['start_datetime']));
        $row['edate'] = date("F d, Y h:i A", strtotime($row['end_datetime']));
        $sched_res[$row['id']] = $row;
    }
    ?>
    <?php
    if (isset($conn)) $conn->close();
    ?>

    <!-- AJAX GET PROF DEPUIS MATIERE  -->
    <script>
        // Lorsque la matière est sélectionnée, soumettre le formulaire et obtenir les enseignants
        document.getElementById('matiere-select').addEventListener('change', function() {
            const matiereId = this.value;

            if (matiereId) {
                fetch("<?php echo $_SERVER['PHP_SELF']; ?>?matiere_id=" + matiereId)
                    .then((response) => response.json())
                    .then((data) => {
                        const professeurSelect = document.getElementById("professeur-select");
                        const professeurValue = document.getElementById("professeur-value");
                        professeurSelect.innerHTML = '';

                        // Ajouter les options des enseignants
                        data.forEach((professeur) => {
                            const option = document.createElement("option");
                            option.value = professeur.id;
                            option.textContent = professeur.nom;
                            professeurSelect.appendChild(option);
                            professeurValue.value = professeur.nom
                        });
                    })
                    .catch(error => {
                        console.error("Erreur lors de la récupération des enseignants:", error);
                    });
            }
        });
        document.getElementById('professeur-select').addEventListener('click', function() {
            const professeurId = this.value;
            if (professeurId) {
                console.log("Fetching classes for professeur ID:", professeurId);

                fetch("<?php echo $_SERVER['PHP_SELF']; ?>?professeur_id=" + professeurId)
                    .then((response) => response.json())
                    .then((data) => {
                        console.log("Classes received:", data);

                        const classeSelect = document.getElementById("classe-select");
                        classeSelect.innerHTML = '';

                        data.forEach((classe) => {
                            const option = document.createElement("option");
                            option.value = classe.id;
                            option.textContent = classe.nom;
                            classeSelect.appendChild(option);
                        });
                    })
                    .catch((error) => {
                        console.error("Erreur lors de la récupération des classes :", error);
                    });
            } else {
                console.log("Aucun professeur sélectionné.");
            }
        });
    </script>

    <!-- Importer Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script>
        document.getElementById('import-btn').addEventListener('click', handleImportClick);

        function handleImportClick() {
            // Créez un élément input de type "file" pour permettre à l'utilisateur de sélectionner un fichier Excel
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.xlsx, .xls'; // Autorise uniquement les fichiers Excel
            fileInput.addEventListener('change', handleFileSelect);
            fileInput.click(); // Ouvre le sélecteur de fichiers
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];

            if (!file) {
                alert("Aucun fichier sélectionné.");
                return;
            }

            if (file.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' &&
                file.type !== 'application/vnd.ms-excel') {
                alert("Veuillez sélectionner un fichier Excel valide.");
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                const data = event.target.result;
                const workbook = XLSX.read(data, {
                    type: 'binary'
                });

                const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                let jsonData = XLSX.utils.sheet_to_json(worksheet, {
                    header: 1
                });

                // Filtrer les lignes vides
                jsonData = jsonData.filter(row => row.length > 0);

                console.log(jsonData);

                fetch('insert_schedule.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(jsonData), // Les données à envoyer
                    })
                    .then(response => response.json()) // Attendre la réponse JSON du serveur
                    .then(responseData => {
                        // Afficher la réponse dans la console pour le débogage
                        console.log('Réponse du serveur:', responseData);

                        if (responseData.status === 'success') {
                            alert(responseData.message);
                        } else {
                            alert(responseData.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors de l\'importation :', error);
                        alert('Une erreur est survenue lors de l\'importation.');
                    });

            };

            reader.readAsBinaryString(file);
        }
    </script>

    <!--//TODO reload page pour 5s premiere chargement de page -->
    <script>
        window.onload = function() {
            const loadingScreen = document.getElementById('loading-screen');
            timeReload = 5000
            // Affiche l'animation de chargement
            loadingScreen.style.display = 'flex';

            if (!sessionStorage.getItem('pageReloaded')) {
                // Si la page n'a pas encore été rechargée
                setTimeout(function() {
                    location.reload(); // Recharge la page
                }, timeReload); // Temps en millisecondes
                location.reload(); // Recharge la page

                // Marque la page comme "déjà rechargée"
                sessionStorage.setItem('pageReloaded', true);
            } else {
                // Cache l'animation après le chargement
                setTimeout(function() {
                    loadingScreen.style.display = 'none';
                }, timeReload); // Cache l'animation après 1 seconde
            }
        };
    </script>

    <!-- dezoumer la page si le type d'ecran est portable -->
    <script>
        // Dézoomer l'écran à 80% (0.8)
        function zoomOutScreen(scale) {
            document.body.style.transform = `scale(${scale})`; // Applique le zoom-out
            document.body.style.transformOrigin = 'top left'; // Définit le point d'origine pour le zoom
            document.body.style.width = `${100 / scale}%`; // Ajuste la largeur pour éviter les barres de défilement
        }

        if (window.innerWidth <= 768) {
            // Appeler la fonction pour dézoomer à 50%
            zoomOutScreen(0.5);
        }
    </script>

    <script>
        $(document).ready(function() {
            console.log('FullCalendar initialized');
            $('#professeur2').on('change', function() {
                const professeur = $(this).val();
                console.log('Professeur sélectionné :', professeur);

                $('#calendar').fullCalendar('removeEvents');
                $('#calendar').fullCalendar('addEventSource', {
                    url: 'fetch_events.php',
                    type: 'POST',
                    data: {
                        professeur: professeur
                    },
                    success: function(data) {
                        console.log('Données reçues :', data);
                    },
                    error: function() {
                        alert('Erreur lors du chargement des événements.');
                    }
                });
            });
        });
    </script>
    <!-- //TODO changer l'affichage de calendrier si utilisateur entrer avec le telephone -->
</body>
<script>
    var scheds = $.parseJSON('<?= json_encode($sched_res) ?>')
</script>
<script src="../assets/js/script.js"></script>
<!-- <script src="./javascript/js/script.js"></script> -->

</html>