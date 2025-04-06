<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}
include('../../includes/admin/controller/controller.php');

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* Filter
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  header('Content-Type: application/json');
  try {
      // Validation des dates
      if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
          throw new Exception("Les dates sont requises");
      }

      $result = get_classes_by_date_range($dbh, $_POST['start_date'], $_POST['end_date']);

      if ($result['success']) {
          echo json_encode([
              'status' => 'success',
              'data' => $result['data'],
              'count' => $result['count']
          ]);
      } else {
          throw new Exception($result['message']);
      }
  } catch (Exception $e) {
      http_response_code(400);
      echo json_encode([
          'status' => 'error',
          'message' => $e->getMessage()
      ]);
  }
  exit;
}

//* Read
$result = get_all_classes($dbh);
if ($result['success']) {
    $results = $result['data'];
} else {
    echo "Erreur : " . $result['message'];
}

//* Delete
try {
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  if (!empty($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
      $result = delete_classe($dbh, $_GET['id']);
      
      if ($result['success']) {
          echo "<script>alert('Classe Bien Supprimée');</script>";
          header("Location: classes.php");
          exit;
      } else {
          echo "<script>alert('" . htmlspecialchars($result['message']) . "');</script>";
      }
  }
} catch (Exception $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  echo "<script>alert('Une erreur est survenue. Veuillez réessayer plus tard.');</script>";
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
                <h6 class="text-primary">Classe</h6>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
                <a class="btn btn-primary btn-sm" href="ajouter_classe.php">Ajouter Classe</a>
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
                    <?php if (count($results) > 0) { ?>
                      <?php foreach ($results as $result) : ?>
                        <tr>
                          <td>
                            <div class="d-flex px-2 py-1">
                              <div>
                                <img src="../../assets/img/team-2.jpg" class="avatar avatar-sm me-3" alt="user1">
                              </div>
                              <div class="d-flex flex-column justify-content-center">
                                <p class="text-secondary text-xs font-weight-bold"><?= $result->nom_classe ?></p>
                              </div>
                            </div>
                          </td>
                          <td class="align-middle text-center">
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