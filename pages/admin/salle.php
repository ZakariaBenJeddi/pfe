<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}
include('../../includes/admin/controller/controller.php');

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* Date Range
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Définir l'en-tête de réponse JSON
  header('Content-Type: application/json');

  // Exécuter la recherche
  $result = get_salle_by_date_range(
    $dbh,
    $_POST['start_date'] ?? null,
    $_POST['end_date'] ?? null
  );

  // Définir le code de statut HTTP
  http_response_code($result['code']);

  // Supprimer le code du résultat final
  unset($result['code']);

  // Renvoyer le résultat en JSON
  echo json_encode($result);
  exit;
}

//* Read
$results = get_all_salles($dbh);

//* Delete
if (!empty($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
  $result = delete_class($dbh, $_GET['id']);

  if ($result['success']) {
    header("Location: salle.php?success=1");
    exit();
  } else {
    $errorMessage = urlencode($result['message']);
    header("Location:salle.php?error={$errorMessage}");
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>

<body class="g-sidenav-show  bg-gray-100">
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
                <h6 class="text-primary">Salles</h6>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
                <a class="btn btn-primary btn-sm" href="ajouter_salle.php">Ajouter Salle</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
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
                      <?php if (count($results) > 0) { ?>
                        <?php foreach ($results['data'] as $result) : ?>
                          <tr>
                            <td>
                              <div class="d-flex px-2 py-1">
                                <div>
                                  <img src="https://elaraki.ac.ma/images/logo2.png" class="avatar avatar-sm me-3" alt="user1">
                                </div>
                                <div class="d-flex flex-column justify-content-center text-sm">
                                  <?= $result->nom_salle; ?>
                                </div>
                              </div>
                            </td>
                            <td class="align-middle text-center text-sm">
                              <p class="text-xs font-weight-bold mb-0">Etage <?php echo $result->etage !== 0 ? $result->etage : 'Rez de chaussée'; ?></p>
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
                              <div class="d-flex">
                                <a href="edit_salle.php?id_salle=<?= $result->id_salle ?>" class="dropdown-item">
                                  <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                                </a>
                                <a href="description_salle.php?id=<?= $result->id_salle ?>" class="dropdown-item">
                                  <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                                </a>
                                <a href="salle.php?id=<?= $result->id_salle ?>&del=1" class="dropdown-item" onClick="return confirmDelete(event, this)">
                                  <i class="fas fa-trash fa-sm text-danger opacity-8" id="<?= $result->id_salle ?>"></i>
                                </a>
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
      <?php include '../../includes/footer.php' ?>
    </div>
  </main>

  <!-- Data table -->
  <script src="../../assets/js/datatable.js"></script>
  <!-- Export Functio -->
  <script src="../../assets/js/export.js"></script>

  <!-- sweet alert -->
  <script src="../../assets/js/alerts/sweet_alert.js"></script>

  <!-- date picker + salle intervalle date -->
  <script src="../../assets/dateP_dateP/dateP_dataP_salle.js"></script>

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