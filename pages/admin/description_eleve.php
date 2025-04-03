<?php
include('../../includes/admin/controller/controller.php');
session_start();

if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

if (isset($_GET['id'])) {
  $id_eleve = isset($_GET['id']) ? $_GET['id'] : null;
  if ($id_eleve && filter_var($id_eleve, FILTER_VALIDATE_INT)) {
    $results = getEleveById($dbh, $_GET['id']);
    if ($results['success']) {
      $results = $results['data'];
      // nom niveau
      if (isset($results->id_niveau)) {
        $niveau = get_niveau_by_id($dbh, $results->id_niveau);
        if ($niveau['success']) {
          $niveauSelected = $niveau['data'];
        } else {
          echo "<script>alert('" . $niveau['message'] . "');</script>";
          exit();
        }
      }
      // nom filiere
      $filiere_info_par_id = get_filiere_by_id($dbh, $results->id_filiere);
      if ($filiere_info_par_id['success']) {
        $nom_filiere = $filiere_info_par_id['data'];
      } else {
        echo htmlspecialchars($filiere_info_par_id['message']);
        exit();
      }
      //nom classe
      $classe_info_par_id = get_classe_by_id($dbh, $results->id_classe);
      if ($classe_info_par_id['success']) {
        $nom_classe = $classe_info_par_id['data'];
      } else {
        echo htmlspecialchars($classe_info_par_id['message']);
        exit();
      }
    } else {
      echo "No data found or operation failed.";
    }
  } else {
    header('location:eleves.php');
  }
} else {
  header('location:eleves.php');
}

try {
  $eleve_id = $_GET['id']; // Remplacez par l'ID de l'élève souhaité
  $result = getHeuresAbsenceEleve($dbh, $eleve_id);
  
  if ($result['success']) {
      $donnees_absence = $result['data'];
      // Utiliser les données ici, par exemple:
      if (count($donnees_absence) > 0) {
          $total_heures = $donnees_absence[0]->{'Total Heures Absence'};
          // echo "L'élève a été absent pendant " . $total_heures . " heures.";
      }
  } else {
      echo "<script>alert('" . $result['message'] . "');</script>";
  }
} catch (Exception $e) {
  echo "<script>alert('Une erreur est survenue lors du calcul des heures d\'absence.');</script>";
}


?>

<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>

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
                <div class="overflow-hidden position-relative border-radius-xl" style="background-image: url('../../assets/img/school/eleve/eleve1.jpg'); 
                  background-repeat: no-repeat; 
                  background-size: cover;
                  background-position: center;">
                  <span class="mask bg-gradient-dark"></span>
                  <div class="card-body position-relative z-index-1 p-3">
                    <i class="fas fa-user text-white p-2">&nbsp;&nbsp;<?= $results->nom . " " . $results->prenom ?></i>
                    <h5 class="text-white mt-4 mb-5 pb-2"></h5>
                    <div class="d-flex">
                      <div class="d-flex">
                        <div class="me-4">
                          <p class="text-white mb-0">Class</p>
                          <h6 class="text-white mb-0">Filiere</h6>
                        </div>
                        <div>
                          <p class="text-white mb-0"><?= $nom_classe->nom_classe ?> &nbsp;&nbsp;&nbsp;<i class="fas fa-map"></i></p>
                          <h6 class="text-white mb-0"><?= $nom_filiere->nom_filiere ?> &nbsp;<i class="fas fa-users"></i></h6>
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
                        <i class="fas fa-book icon-container" style="transition: transform 0.4s ease;"></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Niveau</h6>
                      <span class="text-xs">Niveau Scolaire</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $niveauSelected->nom_niveau ?> </h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg cursor-pointer">
                        <i class="fas fa-smile icon-container" style="transition: transform 0.4s ease;"></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Satisfaction</h6>
                      <span class="text-xs">Happy</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $results->niveau_de_satisfaction ?>% </h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg  cursor-pointer">
                        <i class="fas fa-globe icon-container" style="transition: transform 0.4s ease;"></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Absence</h6>
                      <span class="text-xs">Nombre d'heure </span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $total_heures ?> <br> Heure</h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg cursor-pointer">
                        <i class="fas fa-user-tie icon-container" style="transition: transform 0.4s ease;"></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Notes</h6>
                      <span class="text-xs">Decouvrir notes</span>
                      <hr class="horizontal dark my-3">
                      <!-- <h5 class="mb-0"><?php //$results->nom_tuteur 
                                            ?></h5> -->
                      <div class="icon icon-shape icon-sm bg-gradient-primary shadow text-center cursor-pointer" style="border-radius:100%;">
                        <a href="notes.php?id_eleves=0"><i class="fas fa-arrow-right"></i></a>
                      </div>
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
                      <h6 class="mb-0">Dernier notes :</h6>&nbsp;&nbsp;<i class="fas fa-users text-primary"></i>
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
              <!-- <div class="col-12">
                <button class="btn btn-primary brn-rounded">l'emploi du temps</button>
              </div> -->
              <div class="mb-4 mb-md-0 flex-grow-1 text-center text-md-start">
                <h4 class="text-primary">Payement</h4>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="d-flex flex-row justify-content-center justify-content-md-start align-items-center gap-2 w-100 w-md-auto mt-0 mt-md-4">
                  <a class="btn btn-primary btn-sm" href="ajouter_classe.php">Ajouter Payement</a>
                  <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
                </div>
                <div class="w-100 w-md-auto text-center text-md-end mt-2 mt-md-0">
                  <input type="text" class="form-control w-100 w-md-auto" id="daterange" name="daterange" value="" />
                </div>
              </div>
            </div>
            <div class="card-body pt-4 p-3">
              <table class="table align-items-center mb-0" id="table_payement">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Classe</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Niveau</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Niveau</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Niveau</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Payment Methode</th>
                  </tr>
                </thead>
                <tbody id="tableBody">
                  <td class="align-middle text-center">
                    <p class="text-secondary text-xs font-weight-bold"></p>
                  </td>
                  <td class="align-middle text-center">
                    <p class="text-secondary text-xs font-weight-bold"></p>
                  </td>
                  <td class="align-middle text-center">
                    <p class="text-secondary text-xs font-weight-bold"></p>
                  </td>
                  <td class="align-middle text-center">
                    <p class="text-secondary text-xs font-weight-bold"></p>
                  </td>
                  <td class="align-middle text-center">
                    <p class="text-secondary text-xs font-weight-bold"></p>
                  </td>
                </tbody>
              </table>
            </div>

          </div>
        </div>
        <div class="col-md-4 mt-4">
          <div class="card h-100 mb-4">
            <div class="card-header pb-0 px-3">
              <div class="row">
                <div class="col-md-6">
                  <h6 class="mb-0">Notes</h6>
                </div>
                <div class="col-md-6 d-flex justify-content-end align-items-center">
                  <i class="far fa-calendar-alt me-2"></i>
                  <small><?php echo (new DateTime('now'))->format('d/m/Y'); ?></small>
                </div>
              </div>
            </div>
            <div class="card-body pt-4 p-3">
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
          </div>
        </div>
      </div>
      <!-- FOOTER -->
      <?php include '../../includes/footer.php' ?>
    </div>
  </main>
  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>
  <!-- Data table -->
  <script src="../../assets/js/datatable.js"></script>
  <!-- Export Functio -->
  <script src="../../assets/js/export.js"></script>
  <!-- //* Date Picker + AJAX eleves intervalle date  -->
  <script src="../../assets/dateP_dateP/dateP_dataP_classe.js"></script>

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