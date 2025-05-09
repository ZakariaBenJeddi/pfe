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
  $id_niveau = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Validation stricte
  if ($id_niveau === false || $id_niveau === null) {
    // ID invalide, redirige vers la liste des niveaux
    header('location:niveau.php');
    exit();
  }
} else {
  // Redirection si aucun ID fourni
  header('location:niveau.php');
  exit();
}

try {
  // Requêtes préparées pour éviter les injections SQL
  $nbr_classe = "SELECT COUNT(niveau_id) FROM classe WHERE niveau_id = :idNiveau";
  $stmtClasse = $dbh->prepare($nbr_classe);
  $stmtClasse->execute([':idNiveau' => $id_niveau]);
  $nbrClasses = $stmtClasse->fetchColumn();

  $nbr_filiere = "SELECT COUNT(id_niveau) FROM filiere WHERE id_niveau = :idNiveau";
  $stmtFiliere = $dbh->prepare($nbr_filiere);
  $stmtFiliere->execute([':idNiveau' => $id_niveau]);
  $nbrFiliere = $stmtFiliere->fetchColumn();

  $sql = "SELECT * FROM niveau WHERE id_niveau = :id_niveau";
  $query = $dbh->prepare($sql);
  $query->bindParam(':id_niveau', $id_niveau, PDO::PARAM_INT);
  $query->execute();
  $results = $query->fetchAll(PDO::FETCH_OBJ);

  if (!$results) {
    // Aucun résultat trouvé, redirection
    header('location:niveau.php');
    exit();
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
                <div class="overflow-hidden position-relative border-radius-xl" style="background-image: url('../../assets/img/school/niveau/niveau.jpeg');
                    background-repeat: no-repeat; 
                    background-size: contain;
                    background-position: center;">
                  <span class="mask bg-gradient-dark"></span>
                  <div class="card-body position-relative z-index-1 p-3">
                    <i class="fas fa-building text-white p-2">&nbsp;&nbsp;<?= $results[0]->nom_niveau
                                                                          ?></i>
                    <h5 class="text-white mt-4 mb-5 pb-2"></h5>
                    <div class="d-flex">
                      <div class="d-flex">
                        <div class="me-4"></div>
                        <div>
                          <p class="text-white mb-0"><?php // $results[0]->etage 
                                                      ?> &nbsp;&nbsp;&nbsp;<!-- <i class="fas fa-map"></i></p> -->
                          <h6 class="text-white mb-0"><?php // $results[0]->capacite_salle 
                                                      ?> &nbsp;<!-- <i class="fas fa-users"></i></h6> -->
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
                        <i class="fa-solid fa-layer-group icon-container" style="transition: transform 0.4s ease; "></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Niveau</h6>
                      <span class="text-xs">Nom Niveau</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $results[0]->nom_niveau ?> </h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg  cursor-pointer">
                        <i class="fas fa-info-circle icon-container" style="transition: transform 0.4s ease;"></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Description</h6>
                      <span class="text-xs">Description Niveau </span>
                      <hr class="horizontal dark my-3">
                      <span class="text-xs" style="cursor: pointer;" title="<?= $results[0]->description ?>" data-bs-toggle="modal" data-bs-target="#exampleModal"><?= substr($results[0]->description, 0, 38) . (strlen($results[0]->description) > 38 ? '...' : ''); ?></span>
                    </div>
                  </div>
                </div>
                <!-- Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="text-center">
                        <h5 class="modal-title" id="exampleModalLabel">Description Niveau</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="text-center mt-2">
                        <?= $results[0]->description ?>
                      </div>
                      <div class="text-center">
                        <button type="button" class="btn btn-secondary mt-5" data-bs-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg cursor-pointer">
                        <i class="fas fa-check-circle icon-container" style="transition: transform 0.4s ease;"></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Statut</h6>
                      <span class="text-xs">Statut Niveau</span>
                      <hr class="horizontal dark my-3">
                      <br>
                      <h5 class="mb-0"><?= $results[0]->statut ?> </h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg cursor-pointer">
                        <i class="fas fa-calendar icon-container" style="transition: transform 0.4s ease; "></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Date</h6>
                      <span class="text-xs">Date Creation</span>
                      <hr class="horizontal dark my-3">
                      <br>
                      <h5 class="mb-0"><?= $results[0]->date_creation ?></h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row"> <!-- Delete this ligne if something wrong-->
            <div class="col-md-8 mb-lg-0 mb-4">
              <div class="card mt-4">
                <div class="row">
                  <div class="col-6">
                    <div class="card-header pb-0 p-3">
                      <h6 class="col-12 mb-0">Nombre de Classe Dans ce Niveau</h6>&nbsp;<i class="fa-solid fa-layer-group text-warning text-sm opacity-10"></i>
                      <i class="ni ni-building text-warning text-sm opacity-10"></i>
                    </div>
                    <div class="card-body p-3 text-center">
                      <h4><?= $nbrClasses ?></h4>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="card-header pb-0 p-3">
                      <h6 class="col-12 mb-0">Nombre de Filiere Dans ce Niveau</h6>&nbsp;&nbsp;<i class="fa-solid fa-layer-group text-warning text-sm opacity-10"></i>
                      <i class="ni ni-books text-warning text-sm opacity-10"></i>
                    </div>
                    <div class="card-body p-3 text-center">
                      <h4><?= $nbrFiliere ?></h4>
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
          <div class="card position-relative locked-card" style="opacity: 0.6; pointer-events: none;">
            <div class="card-body pt-4 p-3">
              <ul class="list-group">
                <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-3 text-sm">Oliver Liam <i class="fas fa-lock text-danger ms-2"></i></h6>
                    <span class="mb-2 text-xs">Company Name: <span class="text-dark font-weight-bold ms-sm-2">Viking Burrito</span></span>
                    <span class="mb-2 text-xs">Email Address: <span class="text-dark ms-sm-2 font-weight-bold">oliver@burrito.com</span></span>
                    <span class="text-xs">VAT Number: <span class="text-dark ms-sm-2 font-weight-bold">FRB1235476</span></span>
                  </div>
                  <div class="ms-auto text-end">
                    <a class="btn btn-link text-danger text-gradient px-3 mb-0" href="javascript:;"><i class="far fa-trash-alt me-2"></i>Delete</a>
                    <a class="btn btn-link text-dark px-3 mb-0" href="javascript:;"><i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true"></i>Edit</a>
                  </div>
                </li>
              </ul>
            </div>

            <!-- Overlay verrouillé -->
            <div class="locked-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(255,255,255,0.6);">
              <div class="text-center">
                <i class="fas fa-lock fa-2x text-secondary mb-2"></i>
                <p class="text-muted mb-0">Fiche verrouillée</p>
                <small class="text-muted">Accès restreint</small>
              </div>
            </div>
          </div>

        </div>
        <div class="col-md-4 mt-4">
          <div class="card h-100 mb-4 position-relative locked-card" style="opacity: 0.6; pointer-events: none;">
            <div class="card-header pb-0 px-3">
              <div class="row">
                <div class="col-md-6">
                  <h6 class="mb-0">Nombre de sceance chaque annees <i class="fas fa-lock text-danger ms-2"></i></h6>
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
              </ul>
            </div>
            <!-- Overlay verrouillé -->
            <div class="locked-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(255,255,255,0.6);">
              <div class="text-center">
                <i class="fas fa-lock fa-2x text-secondary mb-2"></i>
                <p class="text-muted mb-0">Contenu verrouillé</p>
                <small class="text-muted">Disponible bientôt</small>
              </div>
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