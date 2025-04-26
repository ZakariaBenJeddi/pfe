<?php
require '../../includes/DatabaseConnexion.php';

//** Configurer les options de sécurité pour les sessions
ini_set('session.cookie_secure', 1); // Cookie accessible uniquement via HTTPS
ini_set('session.cookie_httponly', 1); // Cookie inaccessible via JavaScript
ini_set('session.use_strict_mode', 1); // Empêche l'utilisation de sessions non valides

session_start();

//** Activer le verrouillage des sessions (réduction des risques de fixation de session)
if (!isset($_SESSION['initialized'])) {
  session_regenerate_id(true);
  $_SESSION['initialized'] = true;
}

//** Vérification de l'authentification de l'utilisateur
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
  exit();
}

//** Protection contre les attaques CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('location:error.php');
    exit();
  }
}
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

//** Déconnexion après inactivité
require('../../includes/deconnexion_5s.php'); // Mise à jour du timestamp

//** Validation de l'ID passé dans l'URL
if (isset($_GET['id'])) {
  $id_classe = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Validation stricte
  if ($id_classe === false || $id_classe === null) {
    header('location:classe.php');
    exit();
  }
  try {
    $sql = "SELECT * FROM classe WHERE id_classe = :id_classe";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id_classe', $id_classe, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetch(PDO::FETCH_OBJ);
    if (!$results) {
      header('location:classe.php');
      exit();
    }
    $nom_niveau = "SELECT classe.id_classe, niveau.nom_niveau FROM classe JOIN niveau ON niveau.id_niveau = classe.niveau_id WHERE classe.id_classe = :idClasse";
    $stmtNomNiveau = $dbh->prepare($nom_niveau);
    $stmtNomNiveau->bindParam(':idClasse', $id_classe, PDO::PARAM_INT);
    $stmtNomNiveau->execute();
    $NomNiveau = $stmtNomNiveau->fetch(PDO::FETCH_OBJ);


    $nom_filiere = "SELECT classe.id_classe, filiere.id_filiere , filiere.nom_filiere FROM classe JOIN filiere ON filiere.id_filiere = classe.filiere_id WHERE classe.id_classe = :idClasse";
    $stmtNomFiliere = $dbh->prepare($nom_filiere);
    $stmtNomFiliere->bindParam(':idClasse', $id_classe, PDO::PARAM_INT);
    $stmtNomFiliere->execute();
    $NomFiliere = $stmtNomFiliere->fetch(PDO::FETCH_OBJ);

    // TODO tous les enseignant 
    //! apres il faut afficher les enseignant de chaque matier
    $sql_all_enseignant = "SELECT * FROM enseignant";
    $query_all_enseignant = $dbh->query($sql_all_enseignant);
    $results_all_enseignant = $query_all_enseignant->fetchAll(PDO::FETCH_OBJ);

    //TODO les matiere scientifique de cette filiere de cette classe
    $sql_matiere_deFiliere_classe = "SELECT code_matiere FROM matiere WHERE id_filiere = :id_filiere";
    $query_matiere_deFiliere_classe = $dbh->prepare($sql_matiere_deFiliere_classe);
    $query_matiere_deFiliere_classe->bindParam(':id_filiere', $NomFiliere->id_filiere, PDO::PARAM_INT);
    $query_matiere_deFiliere_classe->execute();
    $results_matiere_deFiliere_classe = $query_matiere_deFiliere_classe->fetchAll(PDO::FETCH_OBJ);

    //TODO les matiere scientifique de cette filiere de cette classe
    $sql_all_matiere = "SELECT code_matiere FROM matiere WHERE id_filiere = 8";
    $query_all_matiere = $dbh->query($sql_all_matiere);
    $results_all_matiere = $query_all_matiere->fetchAll(PDO::FETCH_OBJ);

    //TODO Créer une liste pour combiner les deux résultats
    $combined_results = array_merge($results_matiere_deFiliere_classe, $results_all_matiere);
    $combined_results = array_unique($combined_results, SORT_REGULAR);
  } catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage());
    header('location:error.php');
    exit();
  }
} else {
  // Redirection si aucun ID fourni
  header('location:classes.php');
  exit();
}

//TODO Traitement de la suppression d'une affectation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_affectation'])) {
  if (isset($_POST['code_matiere'])) {
    try {
      // Récupérer l'id_matiere
      $stmt_matiere = $dbh->prepare("SELECT id_matiere FROM matiere WHERE code_matiere = ?");
      $stmt_matiere->execute([$_POST['code_matiere']]);
      $matiere = $stmt_matiere->fetch(PDO::FETCH_OBJ);

      if ($matiere) {
        // Supprimer l'affectation
        $stmt_delete = $dbh->prepare("DELETE FROM enseignant_classes_matieres WHERE matiere_id = ? AND classe_id = ?");
        $stmt_delete->execute([$matiere->id_matiere, $id_classe]);

        // Rediriger pour éviter la resoumission
        // header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id_classe . "&success=1");
        echo '<script>alert("L\'affectation a été supprimée avec succès.")</script>';
      }
    } catch (PDOException $e) {
      echo "Erreur lors de la suppression : " . $e->getMessage();
    }
  }
}

// Fonction pour échapper les données avant de les afficher (protection XSS)
function escape($data)
{
  return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>



<!DOCTYPE html>
<html lang="en">
<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>
<style>
  .icon-container:hover {
    transform: translateY(-10px);
    /* Déplace l'élément de 10px vers le haut */
  }
</style>

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
                <div class="overflow-hidden position-relative border-radius-xl" style="background-image: url('../../assets/img/school/filiere/filiere.png');
                  background-repeat: no-repeat; 
                  background-size: contain;
                  background-position: center;">
                  <span class="mask bg-gradient-dark"></span>
                  <div class="card-body position-relative z-index-1 p-3">
                    <i class="fas fa-university text-white p-2">&nbsp;&nbsp;<?= $results->nom_classe ?></i>
                    <h5 class="text-white mt-4 mb-5 pb-2"></h5>
                    <div class="d-flex">
                      <div class="d-flex">
                        <div class="me-4">
                        </div>
                        <div>
                          <p class="text-white mb-0"><?php // $results->etage 
                                                      ?> &nbsp;<!-- <i class="fas fa-map"></i></p> -->
                          <h6 class="text-white mb-0"><?php // $results->capacite_salle 
                                                      ?> &nbsp;<!--<i class="fas fa-users"></i></h6>-->
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
                        <i class="ni ni-building icon-container" style="transition: transform 0.4s ease; "></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Classe</h6>
                      <span class="text-xs">Nom Classe</span>
                      <hr class="horizontal dark my-3">
                      <br>
                      <h5 class="mb-0"><?= $results->nom_classe ?> </h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg cursor-pointer">
                        <i class="fa-solid fa-layer-group icon-container" style="transition: transform 0.4s ease;"></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Niveau</h6>
                      <span class="text-xs">Nom Niveau</span>
                      <hr class="horizontal dark my-3">
                      <h5 class="mb-0"><?= $NomNiveau->nom_niveau ?> </h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg  cursor-pointer">
                        <i class="ni ni-books icon-container" style="transition: transform 0.4s ease;"></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Filiere</h6>
                      <span class="text-xs">Nom filiere</span>
                      <hr class="horizontal dark my-3">
                      <br>
                      <h5 class="mb-0"><?= $NomFiliere->nom_filiere ?> </h5>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 mt-md-0 mt-4">
                  <div class="card">
                    <div class="card-header mx-4 p-3 text-center">
                      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg cursor-pointer">
                        <i class="fas fa-calendar icon-container" style="transition: transform 0.4s ease; "></i>
                      </div>
                    </div>
                    <div class="card-body pt-0 p-3 text-center">
                      <h6 class="text-center mb-0">Date</h6>
                      <span class="text-xs">Date Creation</span>
                      <hr class="horizontal dark my-3">
                      <br>
                      <h5 class="mb-0"><?= date("d/m/Y", strtotime($results->date_creation)) ?></h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row"> <!-- Delete this ligne if something wrong-->
            <div class="col-md-8 mb-lg-0 mb-4">
              <div class="card mt-4">
                <div class="row">
                  <div class="col-6">
                    <div class="card-header pb-0 p-3">
                      <h6 class="col-12 mb-0">Nombre de Classe Dans cette Filiere</h6>&nbsp;<i class="fa-solid fa-layer-group text-warning text-sm opacity-10"></i>
                      <i class="ni ni-building text-warning text-sm opacity-10"></i>
                    </div>
                    <div class="card-body p-3 text-center">
                      <h4><?php // $nbrClasses 
                          ?></h4>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="card-header pb-0 p-3">
                      <h6 class="col-12 mb-0">Nombre de Niveau Dans cette Filiere </h6>&nbsp;&nbsp;<i class="fa-solid fa-layer-group text-warning text-sm opacity-10"></i>
                      <i class="ni ni-books text-warning text-sm opacity-10"></i>
                    </div>
                    <div class="card-body p-3 text-center">
                      <h4><?php // $nbrNiveau 
                          ?></h4>
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
            <div class="card-header pb-0 border-bottom">
              <button class="btn btn-primary brn-rounded">Afficher l'emploi du temps de cette Filiere </button>
            </div>
            <div class="card-body pt-4 p-3">
              <h4 class="text-center mt-3">Configuer Classe Enseignant</h4>
              <ul class="list-group">
                <?php foreach ($combined_results as $result) : ?>
                  <?php if (isset($result->code_matiere)) : ?>
                    <li class="list-group-item border-0 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center p-4 mb-2 bg-gray-100 border-radius-lg">
                      <div class="d-md-flex d-block justify-content-md-around w-100">
                        <span class="font-weight-bold text-dark mb-5 mb-md-0 me-md-4">
                          <?php echo htmlspecialchars($result->code_matiere); ?>
                        </span>

                        <?php
                        // D'abord obtenir l'ID de la matière à partir du code
                        $stmt_matiere = $dbh->prepare("SELECT id_matiere FROM matiere WHERE code_matiere = ?");
                        $stmt_matiere->execute([$result->code_matiere]);
                        $matiere = $stmt_matiere->fetch(PDO::FETCH_OBJ);

                        if ($matiere) {
                          // Vérifier si un enseignant est déjà affecté
                          $stmt = $dbh->prepare("SELECT e.nom_enseignant, e.prenom_enseignant 
                                FROM enseignant_classes_matieres ecm 
                                JOIN enseignant e ON ecm.enseignant_id = e.id_enseignant 
                                WHERE ecm.matiere_id = ? AND ecm.classe_id = ?");
                          $stmt->execute([$matiere->id_matiere, $id_classe]);
                          $enseignant_affecte = $stmt->fetch(PDO::FETCH_OBJ);

                          if ($enseignant_affecte) :
                        ?>
                            <input type="text" class="form-control w-100 w-md-auto ms-md-3 px-lg-5 mt-4 mt-md-0" value="<?= htmlspecialchars($enseignant_affecte->nom_enseignant . ' ' . $enseignant_affecte->prenom_enseignant) ?>" readonly>
                          <?php else : ?>
                            <select name="enseignant" class="form-select select-sm w-100 w-md-auto ms-md-3 px-lg-5" onchange="assignEnseignant('<?= htmlspecialchars($result->code_matiere, ENT_QUOTES, 'UTF-8') ?>', this.value)">
                              <option value="">Sélectionner un enseignant</option>
                              <?php foreach ($results_all_enseignant as $enseignant) : ?>
                                <option value="<?= htmlspecialchars($enseignant->id_enseignant, ENT_QUOTES, 'UTF-8') ?>">
                                  <?= htmlspecialchars($enseignant->nom_enseignant . ' ' . $enseignant->prenom_enseignant) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                        <?php endif;
                        }
                        ?>
                      </div>
                      <form method="post" action="" class="mt-3 mt-md-0 w-100 w-md-auto text-md-end text-start">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="code_matiere" value="<?= htmlspecialchars($result->code_matiere, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="id_classe" value="<?= htmlspecialchars($id_classe, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" name="delete_affectation" value="1" class="btn btn-link text-danger mb-0" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette affectation ?');">
                          <i class="far fa-trash-alt me-2"></i> Supprimer
                        </button>
                      </form>
                    </li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-4 mt-4">
          <div class="card h-100 mb-4">
            <div class="card-header pb-0 px-3">
              <div class="row">
                <div class="col-md-6">
                  <h6 class="mb-0">Nombre de sceance chaque annees</h6>
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

  <script>
    function assignEnseignant(codeMatiere, idEnseignant) {
      if (idEnseignant === "") return;

      const idClasse = "<?= htmlspecialchars($results->id_classe, ENT_QUOTES, 'UTF-8') ?>";

      console.log("Données envoyées:", {
        code_matiere: codeMatiere,
        id_enseignant: idEnseignant,
        id_classe: idClasse
      });

      const data = new FormData();
      data.append('code_matiere', codeMatiere);
      data.append('id_enseignant', idEnseignant);
      data.append('id_classe', idClasse);
      data.append('csrf_token', "<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>");

      fetch('insert_enseignant_classe_matiere.php', {
          method: 'POST',
          body: data
        })
        .then(response => {
          // Vérifier d'abord le type de contenu
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.includes('application/json')) {
            return response.json();
          }
          // Si ce n'est pas du JSON, lire le texte et afficher l'erreur
          return response.text().then(text => {
            console.error('Réponse non-JSON reçue:', text);
            throw new Error('Réponse invalide du serveur');
          });
        })
        .then(result => {
          console.log('Réponse serveur:', result);
          if (result.success) {
            alert('Assignation enregistrée avec succès.');
            window.location.reload()
          } else {
            alert('Erreur : ' + (result.message || 'Erreur inconnue'));
          }
        })
        .catch(error => {
          console.error('Erreur complète:', error);
          alert('Erreur lors de la requête. Vérifiez la console pour plus de détails.');
        });
    }
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
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>