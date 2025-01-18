<?php
use PhpOffice\PhpSpreadsheet\Shared\OLE\PPS;

include('../../includes/admin/controller/controller.php');
session_start();
if (empty($_SESSION['user'])) {
  header('location:../../sign-up.php');
}

//* DECONEXION
require('../../includes/deconnexion_5s.php');

//*FILTER
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  header('Content-Type: application/json');
  
  try {
      $result = filtrerEnseignantsParDate(
          $dbh,
          $_POST['start_date'] ?? null,
          $_POST['end_date'] ?? null
      );

      if ($result['success']) {
          echo json_encode([
              'status' => 'success',
              'data' => $result['data'],
              'count' => $result['count']
          ]);
      } else {
          http_response_code(400);
          echo json_encode([
              'status' => 'error',
              'message' => $result['message']
          ]);
      }
  } catch (Exception $e) {
      http_response_code(500);
      echo json_encode([
          'status' => 'error',
          'message' => 'Une erreur est survenue lors du filtrage'
      ]);
  }
  exit;
}

//*READ
$results = get_all_enseignant($dbh);

//*DELETE 
try {
  if (!empty($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
      // Appeler la fonction du controller
      $result = supprimerEnseignant($dbh, $_GET['id']);

      if ($result['success']) {
          echo "<script>
              alert('Enseignant bien supprimé');
              window.location.href = 'enseignant.php';
          </script>";
          exit;
      } else {
          echo "<script>
              alert('" . $result['message'] . "');
              window.location.href = 'enseignant.php';
          </script>";
          exit;
      }
  }
} catch (Exception $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  echo "<script>
      alert('Une erreur est survenue. Veuillez réessayer plus tard.');
      window.location.href = 'enseignant.php';
  </script>";
  exit;
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
                <table class="table align-items-center mb-0" id="table_ensaignant">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Author</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">specialite</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">Completion</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">date embauche</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">salaire</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                    </tr>
                  </thead>
                  <tbody id="tableBody">
                    <?php if (count($results) > 0) { ?>
                      <?php foreach ($results as $result) : ?>
                        <tr>
                          <td>
                            <div class="d-flex px-2 py-1">
                              <div>
                                <img src="../../assets/img/team-2.jpg" class="avatar avatar-sm me-3" alt="user1">
                              </div>
                              <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm"><?= $result->nom_enseignant . ' ' . $result->prenom_enseignant ?></h6>
                                <p class="text-xs text-secondary mb-0"><?= $result->email_enseignant ?></p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0">Enseignant</p>
                            <p class="text-xs text-secondary mb-0"><?= $result->specialite ?></p>
                          </td>
                          <td class="align-middle text-center">
                            <div class="d-flex align-items-center justify-content-center">
                              <span class="me-2 text-xs font-weight-bold"><?= $result->degree ?>%</span>
                              <div>
                                <div class="progress">
                                  <div class="progress-bar 
                                    <?php if ($result->degree <= 30) {
                                      echo 'bg-gradient-danger';
                                    }
                                    if ($result->degree <= 50 && $result->degree > 30) {
                                      echo 'bg-gradient-warning';
                                    }
                                    if ($result->degree >= 30 && $result->degree < 90) {
                                      echo 'bg-gradient-info';
                                    }
                                    if ($result->degree >= 90) {
                                      echo 'bg-gradient-success';
                                    } ?>" role="progressbar" aria-valuenow="<?= $result->degree ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $result->degree ?>%;">
                                  </div>
                                </div>
                              </div>
                            </div>
                          </td>
                          <?php if ($result->est_connecte === 0) { ?>
                            <td class="align-middle text-center text-sm">
                              <span class="badge badge-sm bg-gradient-secondary">Offline</span>
                            </td>
                          <?php } else { ?>
                            <td class="align-middle text-center text-sm">
                              <span class="badge badge-sm bg-gradient-success">Online</span>
                            </td>
                          <?php } ?>
                          <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= $result->date_naissance ?></span>
                          </td>
                          <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= $result->salaire ?> DH</span>
                          </td>
                          <td class="align-middle text-center">
                            <div class="d-flex">
                              <a href="edit_ensaignant.php?id=<?= $result->id_enseignant ?>" class="dropdown-item">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <a href="description_enseignant.php?id=<?= $result->id_enseignant ?>" class="dropdown-item">
                                <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                              </a>
                              <a href="enseignant.php?id=<?= $result->id_enseignant ?>&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                                <i class="fas fa-trash fa-sm text-danger opacity-8" id="<?= $result->id_enseignant ?>"></i>
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
      <?php include '../../includes/footer.php' ?>

    </div>
  </main>

  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>

  <!-- script export -->
  <script src="../../assets/js/datatable.js"></script>
  <!-- Export -->
  <script src="../../assets/js/export.js"></script>

  <!-- date picker + data interval date picker -->
  <script src="../../assets/dateP_dateP/dateP_dataP_enseignant.js"></script>

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