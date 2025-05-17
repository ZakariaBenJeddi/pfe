<?php
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
      $result = filtrerAdministrateursParDate(
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
$results = get_all_administrateur($dbh);

//*DELETE 
try {
  if (!empty($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
      // Appeler la fonction du controller
      $result = supprimerAdministrateur($dbh, $_GET['id']);

      if ($result['success']) {
        header("Location: administrateur.php?success=1");
      } else {
        $errorMessage = urlencode($result['message']);
        header("Location: administrateur.php?error={$errorMessage}");
        exit();
      }
  }
} catch (Exception $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  echo "<script>
      alert('Une erreur est survenue. Veuillez réessayer plus tard.');
      window.location.href = 'administrateur.php';
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
                <h6 class="text-primary">Administrateurs</h6>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
                <a class="btn btn-primary btn-sm" href="ajouter_administrateur.php">Ajouter Administrateur</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
              </div>
            </div>
            <hr>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0" id="table_administrateur">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Utilisateur</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Service</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Statut</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Telephone</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date de création</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Dernière connexion</th>
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
                                <img src="../../assets/img/admin/<?= !empty($result->admin_image) ? $result->admin_image : 'default-avatar.jpg' ?>" class="avatar avatar-sm me-3" alt="user">
                              </div>
                              <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm"><?= $result->nom_admin . ' ' . $result->prenom_admin ?></h6>
                                <p class="text-xs text-secondary mb-0"><?= $result->email_admin ?></p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0">Administrateur</p>
                            <p class="text-xs text-secondary mb-0"><?= $result->service ?></p>
                          </td>
                          <td class="align-middle text-center text-sm">
                            <?php if ($result->status == 0) { ?>
                              <span class="badge badge-sm bg-gradient-secondary">Inactif</span>
                            <?php } else { ?>
                              <span class="badge badge-sm bg-gradient-success">Actif</span>
                            <?php } ?>
                          </td>
                          <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= $result->telephone ?></span>
                          </td>
                          <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= $result->date_creation ?></span>
                          </td>
                          <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= $result->dernier_login ?></span>
                          </td>
                          <td class="align-middle text-center">
                            <div class="d-flex justify-content-center">
                              <a href="edit_administrateur.php?id=<?= $result->id_admin ?>" class="dropdown-item">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <a href="administrateur.php?id=<?= $result->id_admin ?>&del=1" class="dropdown-item" onClick="return confirmDelete(event, this)">
                                <i class="fas fa-trash fa-sm text-danger opacity-8" id="<?= $result->id_admin ?>"></i>
                              </a>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php } else { ?>
                      <tr>
                        <td colspan="6" class="text-center">Aucun administrateur trouvé</td>
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
      <?php include '../../includes/footer.php' ?>

    </div>
  </main>

  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>

  <!-- sweet alert -->
  <script src="../../assets/js/alerts/sweet_alert.js"></script>

  <!-- script export -->
  <script src="../../assets/js/datatable.js"></script>
  <!-- Export -->
  <script src="../../assets/js/export.js"></script>

  <!-- date picker + data interval date picker -->
  <script src="../../assets/dateP_dateP/dateP_dataP_administrateur.js"></script>

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