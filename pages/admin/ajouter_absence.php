<?php
session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

// require '../../includes/DatabaseConnexion.php';
include('../../includes/admin/controller/controller.php');
//* deconnexion
// require('../../includes/deconnexion_5s.php');

//* CREATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
  $resultat = ajouterEleve($dbh, $_POST);

  if ($resultat['success']) {
    echo "<script>
          alert('Élève ajouté avec succès.');
          window.location.href = 'eleves.php';
      </script>";
  } else {
    echo "<script>
          alert('Erreur: " . addslashes($resultat['message']) . "');
      </script>";
  }
}

// niveau
try {
  $niveaux = get_all_niveau($dbh);
} catch (PDOException $e) {
  error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
  die("Erreur lors de la récupération des données.");
}


// classe
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


function getElevesByClasse($dbh, $classe_id) {
  try {
      $sql = "SELECT id_eleve, nom, prenom FROM eleves 
              WHERE id_classe = :classe_id 
              ORDER BY nom, prenom";
      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':classe_id', $classe_id, PDO::PARAM_INT);
      $stmt->execute();
      
      // Assurez-vous que fetchAll renvoie un tableau indexé et non un tableau associatif
      $results = $stmt->fetchAll(PDO::FETCH_OBJ);
      
      return $results; // Retourner simplement le tableau d'objets
  } catch (PDOException $e) {
      error_log($e->getMessage());
      return [];
  }
}

if ($_GET['action'] === 'getEleves' && isset($_GET['classe_id'])) {
  $eleves = getElevesByClasse($dbh, intval($_GET['classe_id']));
  
  // Afficher les données pour le débogage
  error_log('Elèves trouvés: ' . print_r($eleves, true));
  
  echo json_encode([
      'success' => true,
      'data' => $eleves, // Un tableau simple d'objets
      'count' => count($eleves)
  ]);
  exit;
}


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
                  <h3>Chercher Eleve</h3>
                </div>
              </div>
              <div class="mt-4">
                <div class="my-4">
                  <label for="classe_box">Choisir La Classe</label>
                  <select name="classe_id" id="classe_box" class="form-select">
                    <option value="">Sélectionnez une classe</option>
                    <?php foreach ($classe as $c) : ?>
                      <option value="<?= htmlspecialchars($c->id_classe) ?>">
                        <?= htmlspecialchars($c->nom_classe) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="my-4">
                  <label for="eleve_box">Choisir L'élève</label>
                  <select name="eleve_id" id="eleve_box" class="form-select">
                    <option value="">Sélectionnez d'abord une classe</option>
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
                <p class="mb-0">Ajouter Absence</p>
              </div>
            </div>
            <hr class="horizontal dark">
            <form method="post">
              <div class="card-body">
                <p class="text-uppercase text-sm">Eleve Information</p>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="date_absence" class="form-control-label">Date Absence</label>
                      <input class="form-control" type="date" name="date_absence" id="date_absence" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="heure_debut" class="form-control-label">Heure Debut</label>
                      <input class="form-control" type="time" name="heure_debut" id="heure_debut" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="heure_fin" class="form-control-label">Heure Fin</label>
                      <input class="form-control" type="time" name="heure_fin" id="heure_fin" required>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="genre_eleve" class="form-control-label">motif</label>
                      <textarea class="form-control" name="motif" id="motif" rows="4" cols="14"></textarea>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="type_absence" class="form-control-label">Type Absence</label>
                      <select class="form-select" name="type_absence" id="type_absence" required>
                        <option value="">Choisir Le Type</option>
                        <option value="excusee">excusee</option>
                        <option value="non_excusee">non_excusee</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="statut" class="form-control-label">Statut</label>
                      <select class="form-select" name="statut" id="statut" required>
                        <option value="">Choisir Le Statut</option>
                        <option value="en_attente">en_attente</option>
                        <option value="validee">validee</option>
                        <option value="annulee">annulee</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <input class="btn btn-primary" type="submit" value="Ajouter" name="ajouter">
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
            const classeSelect = document.getElementById('classe_box');
            const eleveSelect = document.getElementById('eleve_box');
            const heuresAbsenceDiv = document.getElementById('heures_absence');
            const totalHeuresSpan = document.getElementById('total_heures');
            
            // Fonction pour charger les élèves d'une classe

            // function loadEleves(classeId) {
            //   eleveSelect.innerHTML = '<option value="">Chargement...</option>';
            //   console.log("Chargement des élèves pour la classe ID:", classeId);
              
            //   // Obtenir l'URL actuelle
            //   const currentUrl = window.location.href.split('?')[0];
            //   const url = `${currentUrl}?action=getEleves&classe_id=${classeId}`;
              
            //   console.log("URL de requête:", url);
              
            //   fetch(url)
            //       .then(response => {
            //           console.log("Réponse reçue:", response.status);
            //           return response.json();
            //       })
            //       .then(data => {
            //           console.log("Données reçues:", data);
            //           eleveSelect.innerHTML = '<option value="">Sélectionner un élève</option>';
                      
            //           if (data.success && data.data.length > 0) {
            //               data.data.forEach(eleve => {
            //                   const option = document.createElement('option');
            //                   option.value = eleve.id_eleve;
            //                   option.textContent = eleve.nom + ' ' + eleve.prenom;
            //                   eleveSelect.appendChild(option);
            //               });
            //           } else {
            //               eleveSelect.innerHTML = '<option value="">Aucun élève dans cette classe</option>';
            //           }
            //       })
            //       .catch(error => {
            //           console.error('Erreur:', error);
            //           eleveSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            //       });
            // }

            function loadEleves(classeId) {
                  eleveSelect.innerHTML = '<option value="">Chargement...</option>';
                  console.log("Chargement des élèves pour la classe ID:", classeId);
                  
                  // Obtenir l'URL actuelle
                  const url = window.location.pathname + `?action=getEleves&classe_id=${classeId}`;
                  
                  fetch(url)
                      .then(response => {
                          console.log("Réponse reçue:", response.status);
                          return response.json();
                      })
                      .then(data => {
                          console.log("Données reçues:", data);
                          eleveSelect.innerHTML = '<option value="">Sélectionner un élève</option>';
                          
                          // Vérifier si data.data existe
                          const eleves = data.data;
                          
                          // Vérifier si les données sont un tableau ou un objet
                          if (data.success && eleves) {
                              // Si c'est un objet avec des indices numériques
                              if (typeof eleves === 'object' && !Array.isArray(eleves)) {
                                  // Convertir l'objet en tableau
                                  const elevesArray = Object.values(eleves).filter(item => typeof item === 'object');
                                  
                                  if (elevesArray.length > 0) {
                                      elevesArray.forEach(eleve => {
                                          if (eleve && eleve.id_eleve) {
                                              const option = document.createElement('option');
                                              option.value = eleve.id_eleve;
                                              option.textContent = eleve.nom + ' ' + eleve.prenom;
                                              eleveSelect.appendChild(option);
                                          }
                                      });
                                      return;
                                  }
                              }
                              // Si c'est un tableau standard
                              else if (Array.isArray(eleves) && eleves.length > 0) {
                                  eleves.forEach(eleve => {
                                      const option = document.createElement('option');
                                      option.value = eleve.id_eleve;
                                      option.textContent = eleve.nom + ' ' + eleve.prenom;
                                      eleveSelect.appendChild(option);
                                  });
                                  return;
                              }
                          }
                          
                          // Si on arrive ici, c'est qu'on n'a pas pu ajouter d'élèves
                          eleveSelect.innerHTML = '<option value="">Aucun élève dans cette classe</option>';
                          
                          // Débogage supplémentaire
                          console.log("Structure de data:", JSON.stringify(data));
                      })
                      .catch(error => {
                          console.error('Erreur:', error);
                          eleveSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                      });
            }
            
            // Fonction pour calculer les heures d'absence
            function calculateAbsence(eleveId) {
                totalHeuresSpan.textContent = "Chargement...";
                heuresAbsenceDiv.classList.remove('d-none');
                
                fetch('?action=calculateAbsence&eleve_id=' + eleveId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Formater le nombre avec 2 décimales
                            const heures = parseFloat(data.total_heures).toFixed(2);
                            totalHeuresSpan.textContent = heures + " heures";
                        } else {
                            totalHeuresSpan.textContent = "Erreur: " + data.message;
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        totalHeuresSpan.textContent = "Erreur lors du calcul";
                    });
            }
            
            // Écouter le changement de classe
            classeSelect.addEventListener('change', function() {
                const classeId = this.value;
                if (classeId) {
                    console.log(classeId)
                    loadEleves(classeId);
                    heuresAbsenceDiv.classList.add('d-none');
                } else {
                    eleveSelect.innerHTML = '<option value="">Sélectionnez d\'abord une classe</option>';
                    heuresAbsenceDiv.classList.add('d-none');
                }
            });
            
            // Écouter le changement d'élève
            eleveSelect.addEventListener('change', function() {
                const eleveId = this.value;
                if (eleveId) {
                    calculateAbsence(eleveId);
                    // hna 7et l'id dial eleve li mselectionner f input tkon disabled
                } else {
                    heuresAbsenceDiv.classList.add('d-none');
                }
            });
        });
    </script>
      <!-- FOOTER -->
      <?php include '../../includes/footer.php' ?>

    </div>
  </div>
  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>
  <!-- FILIRE ET CLASSE SELON LE NIVEAU -->
  <script src="../../assets/js/niveau_filiere_classe.js"></script>
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