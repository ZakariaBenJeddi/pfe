<?php
// require '../../includes/DatabaseConnexion.php';
include('../../includes/admin/controller/controller.php');
session_start();
// Désactiver tout output buffering to insert in enseignant_matiere
ob_clean();

if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

if (isset($_POST['matiere']) && isset($_POST['id_enseignant'])) {
  $matiere = $_POST['matiere'];
  $id_enseignant = $_POST['id_enseignant'];

  // Vérifier si l'enseignant est déjà associé à cette matière
  $sql = "SELECT * FROM enseignant_matiere WHERE id_enseignant = :id_enseignant";
  $query = $dbh->prepare($sql);
  $query->bindParam(':id_enseignant', $id_enseignant, PDO::PARAM_INT);
  $query->execute();

  header('Content-Type: application/json'); // Définir le type de contenu comme JSON

  if ($query->rowCount() > 0) {
    echo json_encode([
      "status" => "error",
      "message" => "Cet enseignant est déjà associé à une matière."
    ]);
    exit;
  }

  // Si l'enseignant et la matière ne sont pas encore associés
  $insert_sql = "INSERT INTO enseignant_matiere (id_enseignant, matiere) VALUES (:id_enseignant, :matiere)";
  $insert_query = $dbh->prepare($insert_sql);
  $insert_query->bindParam(':id_enseignant', $id_enseignant, PDO::PARAM_INT);
  $insert_query->bindParam(':matiere', $matiere, PDO::PARAM_STR);

  if ($insert_query->execute()) {
    echo json_encode([
      "status" => "success",
      "message" => "L'enseignant a été associé à la matière avec succès."
    ]);
  } else {
    echo json_encode([
      "status" => "error",
      "message" => "Une erreur est survenue lors de l'insertion."
    ]);
  }
  exit;
}

if (isset($_GET['id'])) {
  $resultat = getEnseignantById($dbh, $_GET['id']);

  if (!$resultat['success']) {
    if ($resultat['redirect']) {
      header('location:enseignant.php');
      exit;
    }
    echo $resultat['message'];
    exit;
  }

  $results = $resultat['data'];
} else {
  header('location:enseignant.php');
  exit;
}

// TODO chercher si deja l'enseignant a une matiere pour n'affiche pas le select box
$id_enseignant_selectionner = $_GET['id'];
$sqlChercheEnseignantMatiere = "SELECT id_enseignant FROM enseignant_matiere WHERE id_enseignant = :id_enseignant";
$queryChercheEnseignantMatiere = $dbh->prepare($sqlChercheEnseignantMatiere);
$queryChercheEnseignantMatiere->bindParam(':id_enseignant', $id_enseignant_selectionner, PDO::PARAM_INT);
$queryChercheEnseignantMatiere->execute();
$result_EM = $queryChercheEnseignantMatiere->fetch(PDO::FETCH_ASSOC);

// TODO si la matiere deja affecter afficher elle
$sql_afficher_matiere_affecter = "SELECT matiere FROM enseignant_matiere WHERE id_enseignant = :id_enseignant";
$squery_afficher_matiere_affecter = $dbh->prepare($sql_afficher_matiere_affecter);
$squery_afficher_matiere_affecter->bindParam(':id_enseignant', $id_enseignant_selectionner, PDO::PARAM_INT);
$squery_afficher_matiere_affecter->execute();
$result_afficher_matiere_affecter = $squery_afficher_matiere_affecter->fetch(PDO::FETCH_ASSOC);

// TODO afficher les matiere comme option pour affecter a enseignant
$sqlMatiere = "SELECT DISTINCT(nom_matiere) FROM matiere";
$queryMatiere = $dbh->query($sqlMatiere);
$resultsMatieres = $queryMatiere->fetchAll(PDO::FETCH_OBJ);


if (isset($_GET['delete_affectation']) && $_GET['delete_affectation'] == 1) {
  $id_enseignant = isset($_GET['id']) ? (int)$_GET['id'] : null;
  if ($id_enseignant) {
    $sql_sup_affec_enseigant_matiere = "DELETE FROM enseignant_matiere WHERE id_enseignant = :id_enseignant";
    $query_sup_affec_enseigant_matiere = $dbh->prepare($sql_sup_affec_enseigant_matiere);
    $query_sup_affec_enseigant_matiere->bindParam(':id_enseignant', $id_enseignant, PDO::PARAM_INT);
    $query_sup_affec_enseigant_matiere->execute();
    header('Location: description_enseignant.php?id=' . $id_enseignant);
    exit;
  } else {
    echo "Erreur : ID invalide.";
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
                <div class="overflow-hidden position-relative border-radius-xl" style="background-image: url('../../assets/img/team-3.jpg'); 
                  background-repeat: no-repeat; 
                  background-size: cover;
                  background-position: center;">
                  <span class="mask bg-gradient-dark"></span>
                  <div class="card-body position-relative z-index-1 p-3">
                    <i class="fas fa-user text-white p-2">&nbsp;&nbsp;<?= $results[0]->nom_enseignant . " " . $results[0]->prenom_enseignant ?></i>
                    <h5 class="text-white mt-4 mb-5 pb-2"></h5>
                    <div class="d-flex">
                      <div class="d-flex">
                        <div class="me-4">
                          <p class="text-white mb-0">Specialite</p>
                          <h6 class="text-white mb-0">Filiere</h6>
                        </div>
                        <div>
                          <p class="text-white mb-0"><?= $results[0]->specialite ?>&nbsp;&nbsp;&nbsp;<i class="fas fa-map"></i></p>
                          <h6 class="text-white mb-0">Filiere1 &nbsp;<i class="fas fa-users"></i></h6>
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
                      <h6 class="text-center mb-0">Salaire</h6>
                      <span class="text-xs">Salaire par mois</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= intval($results[0]->salaire) ?> DH</h5>
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
                      <h6 class="text-center mb-0">Telephone</h6>
                      <span class="text-xs">Num Telephone</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $results[0]->telephone_enseignant ?></h5>
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
                      <h6 class="text-center mb-0">Masse Horaire </h6>
                      <span class="text-xs">Par Semaine</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $results[0]->masse_horaire ?></h5>
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
                      <!-- <h5 class="mb-0"><?php //$results[0]->nom_tuteur 
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
              <button class="btn btn-primary brn-rounded">Afficher l'emploi du temps de ce Composant</button>
            </div>
            <div class="card-body pt-4 p-3">
              <ul class="list-group">
                <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                  <div class="d-flex">
                    <h6 class="me-5 text-sm"><?= $results[0]->nom_enseignant . ' ' . $results[0]->prenom_enseignant ?></h6>

                    <?php if ($result_EM === false) { ?>
                      <select name="matiere_select" id="matiere_select" class="form-select ml-3">
                        <option value="">Choisir Matiere</option>
                        <?php foreach ($resultsMatieres as $matiere) { ?>
                          <option value="<?= $matiere->nom_matiere ?>"><?= $matiere->nom_matiere ?></option>
                        <?php } ?>
                      </select>
                      <?php } else {
                      if ($result_afficher_matiere_affecter) { ?>
                        <input class="form-control" type="text" value="<?= $result_afficher_matiere_affecter['matiere'] ?>" readonly>
                    <?php } else {
                        echo "Matiere non trouve";
                      }
                    } ?>

                  </div>
                  <div class="ms-auto text-end">
                    <form method="post" action="description_enseignant.php">
                      <i class="far fa-trash-alt me-2 text-danger"></i>
                      <a href="description_enseignant.php?delete_affectation=1&id=<?php echo $results[0]->id_enseignant; ?>" class="btn btn-link text-danger px-3 mb-0">
                        Delete
                      </a>
                    </form>
                  </div>
                </li>
              </ul>
            </div>

          </div>
        </div>
        <div class="col-md-4 mt-4">
          <div class="card h-100 mb-4">
            <div class="card-header pb-0 px-3">
              <div class="row">
                <div class="col-md-6">
                  <h6 class="mb-0">informatio teur</h6>
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
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <button class="btn btn-icon-only btn-rounded btn-outline-success mb-0 me-3 btn-sm d-flex align-items-center justify-content-center"><i class="fas fa-arrow-up"></i></button>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">2023</h6>
                      <span class="text-xs">271 Sceance</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center text-success text-gradient text-sm font-weight-bold">
                    + 17%
                  </div>
                </li>
              </ul>
              <ul class="list-group">
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <button class="btn btn-icon-only btn-rounded btn-outline-success mb-0 me-3 btn-sm d-flex align-items-center justify-content-center"><i class="fas fa-arrow-up"></i></button>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">2022</h6>
                      <span class="text-xs">200 Sceance</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center text-success text-gradient text-sm font-weight-bold">
                    + 1%
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <button class="btn btn-icon-only btn-rounded btn-outline-danger mb-0 me-3 btn-sm d-flex align-items-center justify-content-center"><i class="fas fa-arrow-down"></i></button>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">2021</h6>
                      <span class="text-xs">191 Sceance</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center text-success text-gradient text-sm font-weight-bold">
                    + 7%
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <button class="btn btn-icon-only btn-rounded btn-outline-success mb-0 me-3 btn-sm d-flex align-items-center justify-content-center"><i class="fas fa-arrow-up"></i></button>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">2020</h6>
                      <span class="text-xs">151 Sceance</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center text-success text-gradient text-sm font-weight-bold">
                    + 100%
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

  <!--   Core JS Files   -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>

  <!-- //TODO apres chaque changement de matiere une self request envoyer -->
  <script>
    $(document).ready(function() {
      $('#matiere_select').change(function() {
        var selectedMatiere = $(this).val();
        if (selectedMatiere) {
          $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
              matiere: selectedMatiere,
              id_enseignant: <?= $results[0]->id_enseignant ?>
            },
            dataType: 'json',
            success: function(response) {
              if (response.status === "success") {
                alert(response.message);
              } else if (response.status === "error") {
                alert(response.message);
              }
              window.location.reload()
            },
            error: function(xhr, status, error) {
              // Afficher la réponse brute pour le debugging
              console.log("Réponse brute du serveur:", xhr.responseText);
              alert("Erreur de traitement de la réponse. Vérifiez la console pour plus de détails.");
            }
          });
        } else {
          alert("Veuillez sélectionner une matière.");
        }
      });
    });
  </script>

  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>