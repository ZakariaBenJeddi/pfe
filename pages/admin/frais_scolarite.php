<?php
include('../../includes/admin/controller/controller.php');
session_start();

if (empty($_SESSION['user'])) {
  header('location:../../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* Read
try {
  $results = get_all_types_frais($dbh); // Call the function to fetch the data
} catch (Exception $e) {
  echo "<script>alert('" . htmlspecialchars($e->getMessage()) . "');</script>";
  $results = []; // In case of error, set the results to an empty array
}


//* Delete
if (!empty($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
  $result = delete_periode_paiement($dbh, $_GET['id']);

  if ($result['success']) {
    echo "<script>
          alert('" . htmlspecialchars($result['message']) . "');
          window.location.href = '" . $result['redirect_url'] . "';
      </script>";
    // exit;
  } else {
    echo "<script>alert('" . htmlspecialchars($result['message']) . "');</script>";
  }
}

if (isset($_POST['save'])) {
  // Check if this is an update operation
  if (isset($_POST['id_periode']) && !empty($_POST['id_periode'])) {
    // Capture form data for update
    $id_periode = $_POST['id_periode'];
    $nom_periode = $_POST['nom_periode'];
    $nombre_mois = $_POST['nombre_mois'];
    $pourcentage_reduction = $_POST['pourcentage_reduction'];
    $description = $_POST['description'];
    $est_actif = $_POST['est_actif'];

    // Call the function to update the payment period
    $result = updatePeriodePaiement($dbh, $id_periode, $nom_periode, $nombre_mois, $pourcentage_reduction, $description, $est_actif);

    if ($result['success']) {
      // Redirect on success
      header("Location: " . $result['redirect_url']);
      exit();
    } else {
      // Handle failure
      echo "<p>" . $result['message'] . "</p>";
    }
  } else {
    // This is a new record insertion
    $nom_periode = $_POST['nom_periode'];
    $nombre_mois = $_POST['nombre_mois'];
    $pourcentage_reduction = $_POST['pourcentage_reduction'];
    $description = $_POST['description'];
    $est_actif = $_POST['est_actif'];

    // Call the function to add a new payment period
    $result = addPeriodePaiement($dbh, $nom_periode, $nombre_mois, $pourcentage_reduction, $description, $est_actif);

    if ($result['success']) {
      // Redirect on success
      header("Location: " . $result['redirect_url']);
      exit();
    } else {
      // Handle failure
      echo "<p>" . $result['message'] . "</p>";
    }
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
                <h6 class="text-primary">Filière</h6>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                <a class="btn btn-primary btn-sm" href="ajouter_filiere.php" data-bs-toggle="modal" data-bs-target="#exampleModal">Ajouter Filière</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
              </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nom de Frais</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Obligatoire</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mensuel</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actif</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                    </tr>
                  </thead>
                  <tbody id="tableBody">
                    <?php if (count($results) > 0) { ?>
                      <?php foreach ($results as $result) : ?>
                        <tr>
                          <td>
                            <div class="d-flex px-2 py-1">
                              <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm"><?= $result->nom_frais ?></h6>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->description; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->est_obligatoire == 1 ? 'Oui' : 'Non' ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->est_mensuel == 1 ? 'Oui' : 'Non' ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->est_actif == 1 ? 'Oui' : 'Non' ?></p>
                          </td>
                          <td class="align-middle text-center">
                            <div class="d-flex">
                              <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModal" data-id="<?= $result->id_periode ?>" data-nom="<?= $result->nom_periode ?>" data-mois="<?= $result->nombre_mois ?>" data-reduction="<?= $result->pourcentage_reduction ?>" data-description="<?= $result->description ?>" data-actif="<?= $result->est_actif ?>">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <a href="periodes_paiement.php?id=<?= $result->id_periode ?>&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                                <i class="fas fa-trash fa-sm text-danger opacity-8"></i>
                            </div>
                            </a>
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
                <!-- Modal -->
                <?php require_once("form/periode_paiement_modal.php") ?>
              </div>
            </div>
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

  <!-- periode paiement passer les info a modal -->
  <script src="../../assets/js/periode_paiement.js"></script>

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