<?php
require '../../includes/DatabaseConnexion.php';

//** Configurer les options de sécurité pour les sessions
ini_set('session.cookie_secure', 1); // Cookie accessible uniquement via HTTPS
ini_set('session.cookie_httponly', 1); // Cookie inaccessible via JavaScript
ini_set('session.use_strict_mode', 1); // Empêche l'utilisation de sessions non valides

session_start();

//** Activer le verrouillage des sessions
if (!isset($_SESSION['initialized'])) {
  session_regenerate_id(true);
  $_SESSION['initialized'] = true;
}

//** Vérification de l'authentification de l'utilisateur
if (empty($_SESSION['user'])) {
  header('location:sign-in.php');
  exit();
}

//** Déconnexion après inactivité
require('../../includes/deconnexion_5s.php');

//** Validation stricte de l'ID passé dans l'URL
if (isset($_GET['id'])) {
  $id_salle = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Validation stricte
  if ($id_salle === false || $id_salle === null) {
    // ID invalide, redirige vers la liste des salles
    header('location:salle.php');
    exit();
  }
} else {
  // Redirection si aucun ID fourni
  header('location:salle.php');
  exit();
}

try {
  // Requête préparée pour éviter les injections SQL
  $sql = "SELECT * FROM salle WHERE id_salle = :id_salle";
  $query = $dbh->prepare($sql);
  $query->bindParam(':id_salle', $id_salle, PDO::PARAM_INT);
  $query->execute();
  $results = $query->fetchAll(PDO::FETCH_OBJ);

  if (!$results) {
    // Aucun résultat trouvé, redirige vers la liste des salles
    header('location:salle.php');
    exit();
  }

  // Vérification de la présence du projecteur
  $projecteur = 0;
  if (strpos(strtolower($results[0]->equipements ?? ''), "projecteur") !== false) {
    $projecteur = 1;
  }
} catch (PDOException $e) {
  // Journaliser les erreurs sans les afficher
  error_log("Erreur SQL : " . $e->getMessage());
  header('location:error.php');
  exit();
}

// Fonction pour échapper les données avant de les afficher (protection XSS)
function escape($data)
{
  return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>
<style>
  .icon-container:hover {
    transform: translateY(-10px);
    /* Déplace l'élément de 10px vers le haut */
  }
</style>

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php require('../../includes/admin/aside_admin.php') ?>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <?php require('../../includes/admin/navbar_admin.php') ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-lg-12">
          <div class="row">
            <div class="col-xl-4 mb-xl-0 mb-4">
              <div class="card bg-transparent shadow-xl">
                <div class="overflow-hidden position-relative border-radius-xl" style="background-image: url('../../assets/img/school/salle/salle1.jpg');
                  background-repeat: no-repeat; 
                  background-size: cover;
                  background-position: center;">
                  <span class="mask bg-gradient-dark"></span>
                  <div class="card-body position-relative z-index-1 p-3">
                    <i class="fas fa-building text-white p-2">&nbsp;&nbsp;<?= $results[0]->nom_salle ?></i>
                    <h5 class="text-white mt-4 mb-5 pb-2">
                      <!-- 4562&nbsp;&nbsp;&nbsp;1122&nbsp;&nbsp;&nbsp;4594&nbsp;&nbsp;&nbsp;7852 -->
                    </h5>
                    <div class="d-flex">
                      <div class="d-flex">
                        <div class="me-4">
                          <p class="text-white mb-0">Etage</p>
                          <h6 class="text-white mb-0">Capacite</h6>
                        </div>
                        <div>
                          <p class="text-white mb-0"><?= $results[0]->etage ?> &nbsp;&nbsp;&nbsp;<i class="fas fa-map"></i></p>
                          <h6 class="text-white mb-0"><?= $results[0]->capacite_salle ?> &nbsp;<i class="fas fa-users"></i></h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-8">
              <div class="row">
                <div class="col-md-3">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape  icon-lg bg-gradient-primary shadow text-center border-radius-lg cursor-pointer">
                        <i class="fas fa-chair icon-container" style="transition: transform 0.4s ease; "></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Chaise</h6>
                      <span class="text-xs">Chaise d'eleves</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $results[0]->nbr_chaise ?> </h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg cursor-pointer">
                        <!-- <i class="fab fa-paypal opacity-10 " ></i> -->
                        <i class="fas fa-video icon-container" style="transition: transform 0.4s ease;"></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Projecteur</h6>
                      <span class="text-xs">Video Projecteur</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $projecteur ?> </h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg  cursor-pointer">
                        <!-- <i class="fas fa-chalkboard"></i> -->
                        <i class="fas fa-chalkboard-teacher icon-container" style="transition: transform 0.4s ease;"></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Tableau</h6>
                      <span class="text-xs">Tableau marquere</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $results[0]->nbr_tableau ?> </h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg cursor-pointer">
                        <i class="fas fa-wind icon-container" style="transition: transform 0.4s ease; "></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Climat</h6>
                      <span class="text-xs">salle climatise</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $results[0]->est_climatisee ?></h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row"> <!-- Delete this ligne if something wrong-->
            <div class="col-md-8 mb-lg-0 mb-4">
              <div class="card mt-4">
                <div class="card-header pb-0 p-3">
                  <div class="row">
                    <div class="col-6 d-flex align-items-center">
                      <h6 class="mb-0">Ensaignant de cette salles</h6>&nbsp;&nbsp;<i class="fas fa-users text-primary"></i>
                    </div>
                  </div>
                </div>
                <div class="card-body p-3">
                  <div class="row">
                    <div class="col-md-6 mb-md-0 ">
                      <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                          <tbody>
                            <tr>
                              <td>
                                <div class="d-flex px-2 py-1">
                                  <div>
                                    <img src="../../assets/img/team-2.jpg" class="avatar avatar-sm me-3" alt="user1">
                                  </div>
                                  <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">John Michael</h6>
                                    <p class="text-xs text-secondary mb-0">john@creative-tim.com</p>
                                  </div>
                                </div>
                              </td>
                              <td>
                                <p class="text-xs font-weight-bold mb-0">Manager</p>
                                <p class="text-xs text-secondary mb-0">Organization</p>
                              </td>

                            </tr>
                            <tr>
                              <td>
                                <div class="d-flex px-2 py-1">
                                  <div>
                                    <img src="../../assets/img/team-2.jpg" class="avatar avatar-sm me-3" alt="user1">
                                  </div>
                                  <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">John Michael</h6>
                                    <p class="text-xs text-secondary mb-0">john@creative-tim.com</p>
                                  </div>
                                </div>
                              </td>
                              <td>
                                <p class="text-xs font-weight-bold mb-0">Manager</p>
                                <p class="text-xs text-secondary mb-0">Organization</p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <div class="col-md-6 mb-md-0 mb-4">
                      <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                          <tbody>
                            <tr>
                              <td>
                                <div class="d-flex px-2 py-1">
                                  <div>
                                    <img src="../../assets/img/team-2.jpg" class="avatar avatar-sm me-3" alt="user1">
                                  </div>
                                  <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">John Michael</h6>
                                    <p class="text-xs text-secondary mb-0">john@creative-tim.com</p>
                                  </div>
                                </div>
                              </td>
                              <td>
                                <p class="text-xs font-weight-bold mb-0">Manager</p>
                                <p class="text-xs text-secondary mb-0">Organization</p>
                              </td>

                            </tr>
                            <tr>
                              <td>
                                <div class="d-flex px-2 py-1">
                                  <div>
                                    <img src="../../assets/img/team-2.jpg" class="avatar avatar-sm me-3" alt="user1">
                                  </div>
                                  <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">John Michael</h6>
                                    <p class="text-xs text-secondary mb-0">john@creative-tim.com</p>
                                  </div>
                                </div>
                              </td>
                              <td>
                                <p class="text-xs font-weight-bold mb-0">Manager</p>
                                <p class="text-xs text-secondary mb-0">Organization</p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3 mb-lg-0 mb-2">
              <!-- <div class="card mt-4"> -->
              <div class="alert alert-danger mt-4 h-75">
                <h5 class="text-center text-light">
                  Alert
                </h5>
                <hr>
                <div class="text-light">
                  Aucun alert
                </div>
              </div>
              <!-- </div> -->
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-8 mt-4">
          <div class="card">

            <div class="card-header pb-0 px-3">
              <button class="btn btn-primary brn-rounded">Afficher l'emploi du temps de cette Salle</button>
            </div>
            <div class="card-body pt-4 p-3">
              <ul class="list-group">
                <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-3 text-sm">Oliver Liam</h6>
                    <span class="mb-2 text-xs">Company Name: <span class="text-dark font-weight-bold ms-sm-2">Viking Burrito</span></span>
                    <span class="mb-2 text-xs">Email Address: <span class="text-dark ms-sm-2 font-weight-bold">oliver@burrito.com</span></span>
                    <span class="text-xs">VAT Number: <span class="text-dark ms-sm-2 font-weight-bold">FRB1235476</span></span>
                  </div>
                  <div class="ms-auto text-end">
                    <a class="btn btn-link text-danger text-gradient px-3 mb-0" href="javascript:;"><i class="far fa-trash-alt me-2"></i>Delete</a>
                    <a class="btn btn-link text-dark px-3 mb-0" href="javascript:;"><i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true"></i>Edit</a>
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex p-4 mb-2 mt-3 bg-gray-100 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-3 text-sm">Lucas Harper</h6>
                    <span class="mb-2 text-xs">Company Name: <span class="text-dark font-weight-bold ms-sm-2">Stone Tech Zone</span></span>
                    <span class="mb-2 text-xs">Email Address: <span class="text-dark ms-sm-2 font-weight-bold">lucas@stone-tech.com</span></span>
                    <span class="text-xs">VAT Number: <span class="text-dark ms-sm-2 font-weight-bold">FRB1235476</span></span>
                  </div>
                  <div class="ms-auto text-end">
                    <a class="btn btn-link text-danger text-gradient px-3 mb-0" href="javascript:;"><i class="far fa-trash-alt me-2"></i>Delete</a>
                    <a class="btn btn-link text-dark px-3 mb-0" href="javascript:;"><i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true"></i>Edit</a>
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex p-4 mb-2 mt-3 bg-gray-100 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-3 text-sm">Ethan James</h6>
                    <span class="mb-2 text-xs">Company Name: <span class="text-dark font-weight-bold ms-sm-2">Fiber Notion</span></span>
                    <span class="mb-2 text-xs">Email Address: <span class="text-dark ms-sm-2 font-weight-bold">ethan@fiber.com</span></span>
                    <span class="text-xs">VAT Number: <span class="text-dark ms-sm-2 font-weight-bold">FRB1235476</span></span>
                  </div>
                  <div class="ms-auto text-end">
                    <a class="btn btn-link text-danger text-gradient px-3 mb-0" href="javascript:;"><i class="far fa-trash-alt me-2"></i>Delete</a>
                    <a class="btn btn-link text-dark px-3 mb-0" href="javascript:;"><i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true"></i>Edit</a>
                  </div>
                </li>
              </ul>
            </div>

          </div>
        </div>
        <div class="col-md-4 mt-4">
          <div class="card h-100 mb-4">
            <div class="card-header pb-0 px-3">
              <div class="row">
                <div class="col-md-6">
                  <h6 class="mb-0">Nombre de sceance chaque annees</h6>
                </div>
                <div class="col-md-6 d-flex justify-content-end align-items-center">
                  <i class="far fa-calendar-alt me-2"></i>
                  <small><?php echo (new DateTime('now'))->format('d/m/Y'); ?></small>
                </div>
              </div>
            </div>
            <div class="card-body pt-4 p-3">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Annees</h6>
              <ul class="list-group">
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <button class="btn btn-icon-only btn-rounded btn-outline-danger mb-0 me-3 btn-sm d-flex align-items-center justify-content-center"><i class="fas fa-arrow-down"></i></button>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">2024</h6>
                      <span class="text-xs">244 Sceance</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center text-danger text-gradient text-sm font-weight-bold">
                    - 4%
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <button class="btn btn-icon-only btn-rounded btn-outline-success mb-0 me-3 btn-sm d-flex align-items-center justify-content-center"><i class="fas fa-arrow-up"></i></button>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">2023</h6>
                      <span class="text-xs">271 Sceance</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center text-success text-gradient text-sm font-weight-bold">
                    + 17%
                  </div>
                </li>
              </ul>
              <ul class="list-group">
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <button class="btn btn-icon-only btn-rounded btn-outline-success mb-0 me-3 btn-sm d-flex align-items-center justify-content-center"><i class="fas fa-arrow-up"></i></button>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">2022</h6>
                      <span class="text-xs">200 Sceance</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center text-success text-gradient text-sm font-weight-bold">
                    + 1%
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <button class="btn btn-icon-only btn-rounded btn-outline-danger mb-0 me-3 btn-sm d-flex align-items-center justify-content-center"><i class="fas fa-arrow-down"></i></button>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">2021</h6>
                      <span class="text-xs">191 Sceance</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center text-success text-gradient text-sm font-weight-bold">
                    + 7%
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <button class="btn btn-icon-only btn-rounded btn-outline-success mb-0 me-3 btn-sm d-flex align-items-center justify-content-center"><i class="fas fa-arrow-up"></i></button>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">2020</h6>
                      <span class="text-xs">151 Sceance</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center text-success text-gradient text-sm font-weight-bold">
                    + 100%
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