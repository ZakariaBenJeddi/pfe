<?php
session_start();

if (empty($_SESSION['user'])) {
  header('location:../../sign-in.php');
}
include('../../includes/admin/controller/controller.php');

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* FILTER
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  header('Content-Type: application/json');
  try {
    // Validation des dates
    if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
      throw new Exception("Les dates sont requises");
    }

    // Nettoyage et validation des dates
    $start_date = filter_var($_POST['start_date'], FILTER_SANITIZE_STRING);
    $end_date = filter_var($_POST['end_date'], FILTER_SANITIZE_STRING);

    if (!$start_date || !$end_date) {
      throw new Exception("Format de date invalide");
    }

    // Conversion des dates au format MySQL
    $start_date = date("Y-m-d", strtotime($start_date));
    $end_date = date("Y-m-d", strtotime($end_date));

    // Requête SQL avec préparation
    $sql = "SELECT * FROM eleves 
              WHERE date_inscription BETWEEN :start_date AND :end_date
              ORDER BY date_inscription DESC";

    $stmt = $dbh->prepare($sql);
    $stmt->execute([
      ':start_date' => $start_date,
      ':end_date' => $end_date
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_OBJ);

    echo json_encode([
      'status' => 'success',
      'data' => $results,
      'count' => count($results)
    ]);
  } catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);
  }
  exit;
}
// if ($_SERVER["REQUEST_METHOD"] === "POST") {
//     header('Content-Type: application/json');
//     $resultat = filtrerElevesParDate(
//         $dbh,
//         $_POST['start_date'] ?? null,
//         $_POST['end_date'] ?? null
//     );
//     if (!$resultat['success']) {
//         http_response_code(400);
//     }
//     echo json_encode($resultat);
//     exit;
// }

//* read 
try {
  $result = getElevesInfo($dbh);
  if ($result['success']) {
    $results = $result['data'];
  } else {
    echo "<script>alert('" . $result['message'] . "');</script>";
  }
} catch (Exception $e) {
  echo "<script>alert('Une erreur est survenue lors de la récupération des données.');</script>";
}

//* delete
try {
  if (isset($_GET['id']) && isset($_GET['del']) && $_GET['del'] === '1') {
    // Appeler la fonction du controller
    $result = supprimerEleve($dbh, $_GET['id']);
    if ($result['success']) {
      echo "<script>
              alert('Élève bien supprimé');
              window.location.href = 'eleves.php';
          </script>";
      exit;
    } else {
      echo "<script>
              alert('" . $result['message'] . "');
              window.location.href = 'eleves.php';
          </script>";
      exit;
    }
  }
} catch (Exception $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  echo "<script>
      alert('Une erreur est survenue. Veuillez réessayer plus tard.');
      window.location.href = 'eleves.php';
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
                <h6 class="text-primary">Eleves</h6>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
                <a class="btn btn-primary btn-sm" href="ajouter_eleve.php">Ajouter Eleve</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
              </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nom & prenom</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">niveau scolaire</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">classe</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">filiere</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">telephone</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">date inscription</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">statut</th>
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
                                <img src="../../assets/img/team-4.jpg" class="avatar avatar-sm me-3" alt="user1">
                              </div>
                              <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm"><?= $result->nom . ' ' . $result->prenom ?></h6>
                                <p class="text-xs text-secondary mb-0"><?= $result->email ?></p>
                              </div>
                            </div>
                          </td>
                          <td class="align-middle text-center text-sm">
                            <p class="text-xs font-weight-bold mb-0"><?php echo  $result->id_niveau === NULL ? 'Aucun Niveau' : $result->nom_niveau; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?php echo $result->nom_classe === null ? 'Aucun Classe' : $result->nom_classe;  ?></p>
                          </td>
                          <td class="align-middle text-center">
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?php echo $result->id_filiere === NULL ? 'Aucun Filiere' : $result->nom_filiere ?></p>
                          </td>
                          <td class="align-middle text-center">
                            <p class="text-xs font-weight-bold mb-0"><?= $result->telephone; ?></p>
                            <p class="text-xs font-weight-bold mb-0"><?= $result->telephone_tuteur; ?></p>
                          </td>
                          <td class="align-middle text-center">
                            <p class="text-xs font-weight-bold mb-0"><?= $result->date_inscription; ?></p>
                          </td>
                          <?php if ($result->statut === 'Actif') { ?>
                            <td class="align-middle text-center text-sm">
                              <span class="badge badge-sm bg-gradient-success">Online</span>
                            </td>
                          <?php } ?>
                          <?php if ($result->statut === 'Inactif'){ ?>
                            <td class="align-middle text-center text-sm">
                              <span class="badge badge-sm bg-gradient-secondary">Offline</span>
                            </td>
                          <?php } ?>
                          <?php if ($result->statut === 'Retraité') { ?>
                            <td class="align-middle text-center text-sm">
                              <span class="badge badge-sm bg-gradient-secondary">Retraité</span>
                            </td>
                          <?php }  ?>
                          <td class="align-middle text-center">
                            <div class="d-flex">
                              <a href="edit_eleve.php?id_eleve=<?= $result->id_eleve ?>" class="dropdown-item">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <a href="description_eleve.php?id=<?= $result->id_eleve ?>" class="dropdown-item">
                                <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                              </a>
                              <a href="eleves.php?id=<?= $result->id_eleve ?>&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                                <i class="fas fa-trash fa-sm text-danger opacity-8" id="<?= $result->id_eleve ?>"></i>
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

  <!-- //* Date Picker + AJAX eleves intervalle date  -->
  <script src="../../assets/dateP_dateP/dateP_dataP_eleve.js"></script>

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