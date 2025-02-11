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
  $results = getAllTarifs($dbh);
} catch (Exception $e) {
  echo "<script>alert('" . htmlspecialchars($e->getMessage()) . "');</script>";
  $results = [];
}

//* Delete
if (isset($_GET['id']) && isset($_GET['del']) && $_GET['del'] == 1) {
  $id_tarif = $_GET['id'];

  $result = deleteTarif($dbh, $id_tarif);
  echo $result['message'];

  if ($result['success']) {
    header('Location: tarif_scolarite.php?success=1');
  } else {
    header('Location: tarif_scolarite.php?error=' . urlencode($result['message']));
  }
  exit();
}

//* add & update
if (isset($_POST['save'])) {
    $id_tarif = isset($_POST['id_tarif']) ? $_POST['id_tarif'] : null;
    $id_type_frais = $_POST['id_type_frais'];
    $id_niveau = $_POST['id_niveau'];
    $id_filiere = $_POST['id_filiere'];
    $montant_base = $_POST['montant_base'];
    $annee_scolaire = $_POST['annee_scolaire'];

    if (!empty($id_tarif)) {
        // Mise à jour si l'ID du tarif existe
        $result = updateTarif($dbh, $id_tarif, $id_type_frais, $id_niveau, $id_filiere, $montant_base, $annee_scolaire);
    } else {
        // Ajout d'un nouveau tarif
        $result = addTarif($dbh, $id_type_frais, $id_niveau, $id_filiere, $montant_base, $annee_scolaire);
    }

    if ($result['success']) {
      header("Location: tarif_scolarite.php?success=1");
      exit();
    } else {
      header("Location: tarif_scolarite.php?error=" . urlencode($result['message']));
      exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                <h6 class="text-primary">Tarif Scolarite</h6>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
                <a class="btn btn-primary btn-sm" href="ajouter_filiere.php" data-bs-toggle="modal" data-bs-target="#exampleModal">Ajouter Filière</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
              </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type Frais</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Niveau</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Filiere</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Montant Base</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Annee Scolaire</th>
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
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->nom_niveau; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->nom_filiere; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->montant_base; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->annee_scolaire; ?></p>
                          </td>
                          <td class="align-middle text-center">
                            <div class="d-flex">
                              <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModal" data-id="<?= $result->id_tarif ?>" data-type-frais="<?= $result->id_type_frais ?>" data-niveau="<?= $result->id_niveau ?>" data-filiere="<?= $result->id_filiere ?>" data-montant="<?= $result->montant_base ?>" data-annee="<?= $result->annee_scolaire ?>">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <!-- <a href="tarif_scolarite.php?id=<?php //$result->id_tarif ?>&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr de vouloir supprimer?')">
                                <i class="fas fa-trash fa-sm text-danger opacity-8"></i>
                              </a> -->
                              <a href="tarif_scolarite.php?id=<?= $result->id_tarif ?>&del=1" class="dropdown-item" onClick="return confirmDelete(event, this)">
                                <i class="fas fa-trash fa-sm text-danger opacity-8"></i>
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
                <!-- Modal -->
                <?php require_once("form/tarif_modal.php") ?>
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

  <!-- Tarif passer les info a modal -->
  <script src="../../assets/js/tarif_scolarite.js"></script>

  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>

  <!-- sweet alert -->
  <script src="../../assets/js/alerts/sweet_alert.js"></script>

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