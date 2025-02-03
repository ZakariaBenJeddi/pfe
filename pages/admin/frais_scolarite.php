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
if (isset($_GET['id']) && isset($_GET['del']) && $_GET['del'] == 1) {
  $id_type_frais = $_GET['id'];

  $result = deleteTypeFrais($dbh, $id_type_frais);
  echo $result['message'];

  if ($result['success']) {
    header('Location: frais_scolarite.php');
    exit();
  }
}


if (isset($_POST['save'])) {
  // Vérifier s'il s'agit d'une mise à jour
  if (isset($_POST['id_type_frais']) && !empty($_POST['id_type_frais'])) {
    // Capture des données du formulaire pour mise à jour
    $id_type_frais = $_POST['id_type_frais'];
    $nom_frais = $_POST['nom_frais'];
    $description = $_POST['description'];
    $est_obligatoire = isset($_POST['est_obligatoire']) ? 1 : 0;
    $est_mensuel = isset($_POST['est_mensuel']) ? 1 : 0;
    $est_actif = $_POST['est_actif'];

    // Appel de la fonction pour mettre à jour le type de frais
    $result = updateTypeFrais($dbh, $id_type_frais, $nom_frais, $description, $est_obligatoire, $est_mensuel, $est_actif);

    if ($result['success']) {
      // Redirection en cas de succès
      header("Location: " . $result['redirect_url']);
      exit();
    } else {
      // Gestion de l'échec
      echo "<p>" . $result['message'] . "</p>";
    }
  } else {
    // Nouvelle insertion
    $nom_frais = $_POST['nom_frais'];
    $description = $_POST['description'];
    $est_obligatoire = isset($_POST['est_obligatoire']) ? 1 : 0;
    $est_mensuel = isset($_POST['est_mensuel']) ? 1 : 0;
    $est_actif = $_POST['est_actif'];

    // Appel de la fonction pour ajouter un nouveau type de frais
    $result = addTypeFrais($dbh, $nom_frais, $description, $est_obligatoire, $est_mensuel, $est_actif);

    if ($result['success']) {
      // Redirection en cas de succès
      header("Location: " . $result['redirect_url']);
      exit();
    } else {
      // Gestion de l'échec
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
                              <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModal" data-id="<?= $result->id_type_frais ?>" data-nom="<?= htmlspecialchars($result->nom_frais) ?>" data-description="<?= htmlspecialchars($result->description) ?>" data-obligatoire="<?= $result->est_obligatoire ?>" data-mensuel="<?= $result->est_mensuel ?>" data-actif="<?= $result->est_actif ?>">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <a href="frais_scolarite.php?id=<?= $result->id_type_frais ?>&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
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
                <?php require_once("form/types_frais_modal.php") ?>
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
  <script src="../../assets/js/frais_scolarite.js"></script>

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