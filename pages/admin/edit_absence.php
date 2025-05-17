<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

include('../../includes/admin/controller/controller.php');
//* deconnexion
require('../../includes/deconnexion_5s.php');

// Récupérer l'ID de l'absence à éditer
$id_absence = isset($_GET['id_absence']) ? intval($_GET['id_absence']) : 0;

if (!$id_absence) {
  header('Location: absence.php?error=' . urlencode("ID d'absence non valide"));
  exit();
}

// Récupérer les informations de l'absence existante
function getAbsenceById($dbh, $id_absence) {
  try {
    $sql = "SELECT a.*, e.nom, e.prenom, e.id_classe 
            FROM absences a
            JOIN eleves e ON a.id_eleve = e.id_eleve
            WHERE a.id_absence = :id_absence";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':id_absence', $id_absence, PDO::PARAM_INT);
    $stmt->execute();
    
    return [
      'success' => true,
      'data' => $stmt->fetch(PDO::FETCH_OBJ)
    ];
  } catch (PDOException $e) {
    error_log($e->getMessage());
    return [
      'success' => false,
      'message' => "Erreur lors de la récupération des données de l'absence: " . $e->getMessage()
    ];
  }
}

// Fonction pour mettre à jour une absence
function modifierAbsence($dbh, $donnees_absence) {
  try {
    // Validation des données
    if (empty($donnees_absence['id_absence']) || empty($donnees_absence['id_elevesX'])) {
      return [
        'success' => false,
        'message' => "Données d'absence incomplètes"
      ];
    }

    // Récupération de l'absence existante pour conserver la justification si pas de nouveau fichier
    $sql_get = "SELECT justification FROM absences WHERE id_absence = :id_absence";
    $stmt_get = $dbh->prepare($sql_get);
    $stmt_get->bindParam(':id_absence', $donnees_absence['id_absence'], PDO::PARAM_INT);
    $stmt_get->execute();
    $current_absence = $stmt_get->fetch(PDO::FETCH_OBJ);
    $justification_path = $current_absence->justification;

    // Traitement du fichier de justification si présent
    if ($donnees_absence['type_absence'] === 'excusee' && isset($_FILES['justification']) && $_FILES['justification']['size'] > 0) {
      // Définir le chemin du répertoire pour les justifications
      // CORRECTION: utiliser un chemin relatif à la racine du site
      $base_path = $_SERVER['DOCUMENT_ROOT'];
      // $target_dir = $base_path . '/assets/justification_eleves/';
      $target_dir = __DIR__ . '/../../assets/justification_eleves/';
      
      // Vérifier si le répertoire existe, sinon le créer
      if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
      }
      
      $file_extension = pathinfo($_FILES['justification']['name'], PATHINFO_EXTENSION);
      $new_filename = "justif_" . $donnees_absence['id_absence'] . "_" . date('Ymd_His') . "." . $file_extension;
      $target_file = $target_dir . $new_filename;
      
      // Vérifier le type de fichier
      $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
      if (!in_array(strtolower($file_extension), $allowed_types)) {
        return [
          'success' => false,
          'message' => "Seuls les fichiers PDF, JPG et PNG sont autorisés."
        ];
      }
      
      if (move_uploaded_file($_FILES['justification']['tmp_name'], $target_file)) {
        // Mettre à jour le chemin du fichier - chemin relatif pour accéder depuis le navigateur
        $justification_path = 'assets/justification_eleves/' . $new_filename;
        // $justification_path = '/assets/justification_eleves/' . $new_filename;
      } else {
        $upload_error = error_get_last();
        error_log("Échec du téléchargement du fichier de justification: " . print_r($upload_error, true));
        return [
          'success' => false,
          'message' => "Erreur lors du téléchargement du fichier de justification."
        ];
      }
    } else if ($donnees_absence['type_absence'] === 'non_excusee') {
      // Si le type d'absence est non excusée, on supprime la justification
      $justification_path = null;
    }
    // Sinon on garde la justification existante (déjà récupérée plus haut)

    // Ajout du champ date_modification
    $date_modification = date('Y-m-d H:i:s');

    // Préparation de la requête SQL avec tous les champs nécessaires
    $sql = "UPDATE absences SET 
            id_eleve = :id_eleve,
            date_absence = :date_absence,
            heure_debut = :heure_debut,
            heure_fin = :heure_fin,
            motif = :motif,
            type_absence = :type_absence,
            statut = :statut,
            justification = :justification,
            date_modification = :date_modification
            WHERE id_absence = :id_absence";
    
    $stmt = $dbh->prepare($sql);
    
    // Liaison des paramètres
    $stmt->bindParam(':id_eleve', $donnees_absence['id_elevesX'], PDO::PARAM_INT);
    $stmt->bindParam(':date_absence', $donnees_absence['date_absence'], PDO::PARAM_STR);
    $stmt->bindParam(':heure_debut', $donnees_absence['heure_debut'], PDO::PARAM_STR);
    $stmt->bindParam(':heure_fin', $donnees_absence['heure_fin'], PDO::PARAM_STR);
    $stmt->bindParam(':motif', $donnees_absence['motif'], PDO::PARAM_STR);
    $stmt->bindParam(':type_absence', $donnees_absence['type_absence'], PDO::PARAM_STR);
    $stmt->bindParam(':statut', $donnees_absence['statut'], PDO::PARAM_STR);
    $stmt->bindParam(':justification', $justification_path, PDO::PARAM_STR);
    $stmt->bindParam(':date_modification', $date_modification, PDO::PARAM_STR);
    $stmt->bindParam(':id_absence', $donnees_absence['id_absence'], PDO::PARAM_INT);
    
    // Exécution de la requête
    $stmt->execute();
    
    // Ajouter des logs pour déboguer
    error_log("Absence modifiée avec succès. Justification: " . $justification_path);
    
    return [
      'success' => true,
      'message' => "L'absence a été modifiée avec succès."
    ];
    
  } catch (PDOException $e) {
    error_log($e->getMessage());
    return [
      'success' => false,
      'message' => "Erreur lors de la modification de l'absence: " . $e->getMessage()
    ];
  }
}


// Récupérer les informations de l'absence
$absenceResult = getAbsenceById($dbh, $id_absence);
if (!$absenceResult['success'] || !$absenceResult['data']) {
  header('Location: absence.php?error=' . urlencode("Absence non trouvée"));
  exit();
}
$absence = $absenceResult['data'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier'])) {
  // Fusionner $_POST et ajouter l'ID de l'absence
  $donnees_absence = $_POST;
  $donnees_absence['id_absence'] = $id_absence;
  
  // Pour le débogage
  error_log("Données soumises: " . print_r($donnees_absence, true));
  error_log("Fichiers soumis: " . print_r($_FILES, true));
  
  $resultat = modifierAbsence($dbh, $donnees_absence);

  if ($resultat['success']) {
    header("Location: absence.php?success=" . urlencode($resultat['message']));
    exit();
  } else {
    $errorMessage = urlencode($resultat['message']);
    // CORRECTION: Utiliser id_absence pour la redirection d'erreur
    header("Location: edit_absence.php?id_absence={$id_absence}&error={$errorMessage}");
    exit();
  }
}

// Récupérer toutes les classes
try {
  $result = get_all_classes($dbh);
  if ($result['success']) {
    $classe = $result['data'];
  } else {
    echo "Erreur : " . $result['message'];
  }
} catch (PDOException $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  die("Erreur lors de la récupération des données.");
}

// Fonction pour récupérer les élèves par classe
function getElevesByClasse($dbh, $classe_id)
{
  try {
    $sql = "SELECT id_eleve, nom, prenom FROM eleves 
              WHERE id_classe = :classe_id 
              ORDER BY nom, prenom";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':classe_id', $classe_id, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_OBJ);

    return $results;
  } catch (PDOException $e) {
    error_log($e->getMessage());
    return [];
  }
}

// API pour récupérer les élèves d'une classe
if (isset($_GET['action']) && $_GET['action'] === 'getEleves' && isset($_GET['classe_id'])) {
  $eleves = getElevesByClasse($dbh, intval($_GET['classe_id']));

  // Pour le débogage
  error_log('Classe ID: ' . $_GET['classe_id']);
  error_log('Elèves trouvés: ' . count($eleves));

  echo json_encode([
    'success' => true,
    'data' => $eleves,
    'count' => count($eleves)
  ]);
  exit;
}

// Récupérer les élèves de la classe de l'élève concerné
$eleves_classe = getElevesByClasse($dbh, $absence->id_classe);

?>

<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>

<body class="g-sidenav-show bg-gray-100">
  <div class="position-absolute w-100 min-height-300 top-0" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
    <span class="mask bg-primary opacity-6"></span>
  </div>
  <?php require('../../includes/admin/aside_admin.php') ?>
  <div class="main-content position-relative max-height-vh-100 h-100">
    <!-- Navbar -->
    <?php require('../../includes/admin/navbar_admin.php') ?>
    <!-- End Navbar -->
    <div class="card shadow-lg mx-4 card-profile-bottom">
      <!-- Alert pour les messages d'erreur ou de succès -->
      <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span class="alert-text"><?= htmlspecialchars($_GET['error']) ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="alert-text"><?= htmlspecialchars($_GET['success']) ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
    </div>
    
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-md-4">
          <div class="card card-profile">
            <div class="text-center">
              <img src="../../assets/img/school/eleve/download.png" alt="Image placeholder" class="card-img-top" style="width: 50%; height: auto; object-fit: cover;">
            </div>
            <div class="card-body pt-0 mb-5">
              <div class="row">
                <div class="text-center mt-3">
                  <h3>Modifier Absence</h3>
                  <p>ID d'absence: <?= htmlspecialchars($id_absence) ?></p>
                </div>
              </div>
              <div class="mt-4">
                <div class="my-4">
                  <label for="classe_box">Classe de l'élève</label>
                  <select name="classe_id" id="classe_box" class="form-select">
                    <option value="">Sélectionnez une classe</option>
                    <?php foreach ($classe as $c) : ?>
                      <option value="<?= htmlspecialchars($c->id_classe) ?>" <?= ($c->id_classe == $absence->id_classe) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c->nom_classe) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="my-4">
                  <label for="eleve_box">Élève concerné</label>
                  <select name="eleve_id" id="eleve_box" class="form-select">
                    <?php if (!empty($eleves_classe)): ?>
                      <?php foreach ($eleves_classe as $eleve): ?>
                        <option value="<?= htmlspecialchars($eleve->id_eleve) ?>" 
                                data-nom="<?= htmlspecialchars($eleve->nom) ?>" 
                                data-prenom="<?= htmlspecialchars($eleve->prenom) ?>"
                                <?= ($eleve->id_eleve == $absence->id_eleve) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($eleve->nom) ?> <?= htmlspecialchars($eleve->prenom) ?>
                        </option>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <option value="">Aucun élève trouvé</option>
                    <?php endif; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <p class="mb-0">Modifier l'Absence</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post" enctype="multipart/form-data">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom_prenom" class="form-control-label">Nom & Prénom</label>
                      <input class="form-control" type="text" name="nom_prenom" id="nom_prenom" value="<?= htmlspecialchars($absence->nom . ' ' . $absence->prenom) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="id_elevesX" class="form-control-label">ID Élève</label>
                      <input class="form-control" type="text" name="id_elevesX" id="id_elevesX" value="<?= htmlspecialchars($absence->id_eleve) ?>" required readonly>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="date_absence" class="form-control-label">Date Absence</label>
                      <input class="form-control" type="date" value="<?= htmlspecialchars($absence->date_absence) ?>" name="date_absence" id="date_absence" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="heure_debut" class="form-control-label">Heure Début</label>
                      <input class="form-control" type="time" value="<?= htmlspecialchars($absence->heure_debut) ?>" name="heure_debut" id="heure_debut" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="heure_fin" class="form-control-label">Heure Fin</label>
                      <input class="form-control" type="time" value="<?= htmlspecialchars($absence->heure_fin) ?>" name="heure_fin" id="heure_fin" required>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="motif" class="form-control-label">Motif</label>
                      <textarea class="form-control" name="motif" id="motif" rows="4" cols="14"><?= htmlspecialchars($absence->motif ?? '') ?></textarea>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="type_absence" class="form-control-label">Type Absence</label>
                      <select class="form-select" name="type_absence" id="type_absence" required>
                        <option value="">Choisir Le Type</option>
                        <option value="excusee" <?= ($absence->type_absence === 'excusee') ? 'selected' : '' ?>>Excusée</option>
                        <option value="non_excusee" <?= ($absence->type_absence === 'non_excusee') ? 'selected' : '' ?>>Non Excusée</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="statut" class="form-control-label">Statut</label>
                      <select class="form-select" name="statut" id="statut" required>
                        <option value="">Choisir Le Statut</option>
                        <option value="en_attente" <?= ($absence->statut === 'en_attente') ? 'selected' : '' ?>>En Attente</option>
                        <option value="validee" <?= ($absence->statut === 'validee') ? 'selected' : '' ?>>Validée</option>
                        <option value="annulee" <?= ($absence->statut === 'annulee') ? 'selected' : '' ?>>Annulée</option>
                      </select>
                    </div>
                  </div>

                  <!-- Conteneur de justification -->
                  <div class="col-md-12" id="justification_container" style="<?= ($absence->type_absence === 'excusee') ? 'display: block;' : 'display: none;' ?>">
                    <div class="form-group">
                      <label for="justification" class="form-control-label">Justification (PDF, JPG, PNG)</label>
                      <input class="form-control" type="file" name="justification" id="justification">
                      <?php if (!empty($absence->justification)): ?>
                        <small class="text-muted">Fichier actuel: <?= htmlspecialchars($absence->justification) ?></small>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

                <div class="row mt-4">
                  <div class="col-md-6">
                    <input class="btn btn-primary w-100" type="submit" value="Modifier" name="modifier">
                  </div>
                  <div class="col-md-6">
                    <a href="absence.php" class="btn btn-secondary w-100">Annuler</a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- FOOTER -->
      <?php include '../../includes/footer.php' ?>
    </div>
  </div>
  
  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>

  <!-- script affichage eleve seulon la classe seletionne -->
  <script src="../../assets/js/absence_eleves.js"></script>

  <script>
    // Script pour mettre à jour les données de l'élève quand un élève est sélectionné
    document.addEventListener('DOMContentLoaded', function() {
      const eleveBox = document.getElementById('eleve_box');
      const nomPrenomInput = document.getElementById('nom_prenom');
      const idElevesInput = document.getElementById('id_elevesX');
      
      // Fonction pour mettre à jour les champs lors de la sélection d'un élève
      function updateEleveFields() {
        const selectedOption = eleveBox.options[eleveBox.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
          const nom = selectedOption.getAttribute('data-nom') || '';
          const prenom = selectedOption.getAttribute('data-prenom') || '';
          
          nomPrenomInput.value = `${nom} ${prenom}`.trim();
          idElevesInput.value = selectedOption.value;
        } else {
          nomPrenomInput.value = '';
          idElevesInput.value = '';
        }
      }
      
      // Exécuter au chargement pour initialiser les champs
      updateEleveFields();
      
      // Ajouter un écouteur d'événement pour les changements futurs
      eleveBox.addEventListener('change', updateEleveFields);
      
      // Gestion du formulaire de justification
      const typeAbsenceSelect = document.getElementById('type_absence');
      const justificationContainer = document.getElementById('justification_container');
      
      // Vérifier l'état initial et mettre à jour l'affichage
      if (typeAbsenceSelect.value === 'excusee') {
        justificationContainer.style.display = 'block';
      } else {
        justificationContainer.style.display = 'none';
      }
      
      // Ajouter un écouteur d'événement pour les changements de type d'absence
      typeAbsenceSelect.addEventListener('change', function() {
        if (this.value === 'excusee') {
          justificationContainer.style.display = 'block';
        } else {
          justificationContainer.style.display = 'none';
        }
      });
    });
  </script>


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