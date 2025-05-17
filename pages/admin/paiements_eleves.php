<?php
include('../../includes/admin/controller/controller.php');
session_start();

if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}

//* deconnexion
require('../../includes/deconnexion_5s.php');

//* Read
try {
  $results = getAllPaiements($dbh);
} catch (Exception $e) {
  echo "<script>alert('" . htmlspecialchars($e->getMessage()) . "');</script>";
  $results = [];
}

//* Filter
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['start_date']) && isset($_POST['end_date'])) {
  header('Content-Type: application/json');
  try {
    // Validation des dates
    if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
      throw new Exception("Les dates sont requises");
    }

    $result = get_payments_by_date_range($dbh, $_POST['start_date'], $_POST['end_date']);

    if ($result['success']) {
      echo json_encode([
        'status' => 'success',
        'data' => $result['data'],
        'count' => $result['count']
      ]);
    } else {
      throw new Exception($result['message']);
    }
  } catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);
  }
  exit;
}

//* Delete
if (isset($_GET['id']) && isset($_GET['del']) && $_GET['del'] == 1) {
  $id_paiement = $_GET['id'];

  $result = deletePaiement($dbh, $id_paiement);
  echo $result['message'];

  if ($result['success']) {
    header('Location: paiements_eleves.php?success=1');
  } else {
    header('Location: paiements_eleves.php?error=' . urlencode($result['message']));
  }
  exit();
}

if (isset($_POST['save'])) {
  $id_paiement = isset($_POST['id_paiement']) ? $_POST['id_paiement'] : null;
  $id_eleve = $_POST['id_eleve'];
  $id_tarif = $_POST['id_tarif'];
  $id_periode = $_POST['id_periode'];
  $montant_base = $_POST['montant_base'];
  $reduction_appliquee = $_POST['reduction_appliquee'];
  $montant_final = $_POST['montant_final'];
  $date_paiement = $_POST['date_paiement'];
  $date_debut_periode = $_POST['date_debut_periode'];
  $mode_paiement = $_POST['mode_paiement'];
  $reference_paiement = $_POST['reference_paiement'];
  $commentaire = $_POST['commentaire'];
  $id_admin = $_SESSION['user'] ?? NULL;
  $statut_paiement = $_POST['statut_paiement'];

  if (!empty($id_paiement)) {
    $result = updatePaiement($dbh, $id_paiement, $id_eleve, $id_tarif, $id_periode, $montant_base, $reduction_appliquee, $montant_final, $date_paiement, $date_debut_periode, null, $mode_paiement, $reference_paiement, $commentaire, $id_admin, $statut_paiement);
  } else {
    $result = addPaiement($dbh, $id_eleve, $id_tarif, $id_periode, $montant_base, $reduction_appliquee, $montant_final, $date_paiement, $date_debut_periode, $mode_paiement, $reference_paiement, $commentaire, $id_admin, $statut_paiement);
  }

  if ($result['success']) {
    header("Location: paiements_eleves.php?success=1");
    exit();
  } else {
      $errorMessage = urlencode($result['message']);
      header("Location: paiements_eleves.php?error={$errorMessage}");
      exit();
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
                <h6 class="text-primary">Paiement Eleves</h6>
              </div>
              <div class="d-flex flex-column flex-md-row justify-content-center justify-content-md-end align-items-center gap-2 w-100">
                <input type="text" class="form-control w-100 w-md-auto mb-3" id="daterange" name="daterange" value="" />
                <a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paiementModal">Ajouter Paiement</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="expo()" id="btnexp">Exporter</button>
              </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Eleve</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Filiere & Niveau</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type Frais</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nom Periode</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tarif Montant Base</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Paiment Montant Base</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reduction Applique</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Montant Final</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mode Paiement</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reference Paiement</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Statut</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">date paiement</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">date debut periode</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">date fin periode</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">date validation</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                    </tr>
                  </thead>
                  <tbody id="tableBody">
                    <?php if (count($results) > 0) { ?>
                      <?php foreach ($results as $result) : ?>
                        <tr>
                        <td style="cursor:pointer" 
                            <?php if ($result->statut_paiement === "Validé") { ?> 
                                onclick="genererPDFPaiement(<?= $result->id_paiement ?>)"
                            <?php } else { ?>
                                onclick="window.location.href='paiements_eleves.php?error=<?= urlencode('Paiement non validé') ?>'"
                            <?php } ?>>
                            <div class="d-flex px-2 py-1">
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm"><?= $result->id_paiement ?></h6>
                                </div>
                                <?php if ($result->statut_paiement === "Validé") { ?>
                                    <i class="fas fa-file-pdf text-primary ms-2"></i> <!-- Icône PDF -->
                                <?php } ?>
                            </div>
                        </td>

                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">
                              <?= strtoupper($result->nom_eleve . " " . $result->prenom_eleve) . "<br>" . $result->code_massare; ?>
                            </p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">
                              <?= $result->nom_filiere . "<br>" . $result->nom_niveau ?>
                            </p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->type_frais; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">
                              <?= $result->nom_periode . "<br>" . $result->nombre_mois; ?>
                            </p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->tarif_montant_base; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->paiement_montant_base; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5" title="<?= $result->description_periode ?>">
                              <?= $result->reduction_appliquee; ?>
                            </p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->montant_final; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->mode_paiement; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->reference_paiement; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">
                              <?php if ($result->statut_paiement === "En attente") { ?>
                                <span class="badge badge-md bg-gradient-secondary"><?= $result->statut_paiement ?></span>
                              <?php } ?>
                              <?php if ($result->statut_paiement === "Validé") { ?>
                                <span class="badge badge-md bg-gradient-success"><?= $result->statut_paiement ?></span>
                              <?php } ?>
                              <?php if ($result->statut_paiement === "Annulé") { ?>
                                <span class="badge badge-md bg-gradient-danger"><?= $result->statut_paiement ?></span>
                              <?php } ?>
                            </p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->date_paiement; ?></p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5"><?= $result->date_debut_periode; ?></p>
                          </td>
                          <td>
                            <?php
                            $date_fin = new DateTime($result->date_fin_periode);
                            $date_aujourdhui = new DateTime();
                            $diff = $date_aujourdhui->diff($date_fin);
                            $jours_restants = $diff->days;
                            if ($date_fin < $date_aujourdhui) {
                              $message = "Date dépassée !";
                            } else {
                              $message = "Il reste $jours_restants jour(s)";
                            }
                            ?>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5" title="<?= $message ?>">
                              <?= $result->date_fin_periode; ?>
                            </p>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">
                              <?php echo $result->date_validation === NULL ? "En attente de validation" : $result->date_validation; ?>
                            </p>
                          </td>
                          <td class="align-middle text-center">
                            <div class="d-flex">
                              <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#paiementModal" data-id="<?= $result->id_paiement ?>" data-id-eleve="<?= $result->id_eleve ?>" data-id-tarif="<?= $result->id_tarif ?>" data-id-periode="<?= $result->id_periode ?>" data-montant-base="<?= $result->paiement_montant_base ?>" data-reduction-appliquee="<?= $result->reduction_appliquee ?>" data-montant-final="<?= $result->montant_final ?>" data-date-paiement="<?php echo  $data_date_paiement = date('Y-m-d', strtotime($result->date_paiement)); ?>" data-date-debut-periode="<?php echo  $data_date_debut_periode = date('Y-m-d', strtotime($result->date_debut_periode)); ?>" data-mode-paiement="<?= $result->mode_paiement ?>" data-reference-paiement="<?= $result->reference_paiement ?>" data-statut-paiement="<?= $result->statut_paiement ?>" data-commentaire="<?= $result->commentaire ?>">
                                <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                              </a>
                              <a href="paiements_eleves.php?id=<?= $result->id_paiement ?>&del=1" class="dropdown-item" onClick="return confirmDelete(event, this)">
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
                <?php require_once("form/paiement_modal.php") ?>
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
  <script src="../../assets/js/paiement_eleves.js"></script>
  <!-- FILIRE ET CLASSE SELON LE NIVEAU -->
  <script src="../../assets/js/niveau_filiere_classe.js"></script>

  <!-- //* Date Picker + AJAX eleves intervalle date  -->
  <script src="../../assets/dateP_dateP/dateP_dataP_paiement.js"></script>

  <!-- pdf generation -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script src="../../assets/js/generation_pdf.js"></script>

  <!-- sweet alert -->
  <script src="../../assets/js/alerts/sweet_alert.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
  // Gestionnaire d'événement pour l'ouverture du modal de paiement
  $('#paiementModal').on('show.bs.modal', function(event) {
    const button = $(event.relatedTarget); // Bouton qui a déclenché le modal
    
    // Récupérer les données de l'attribut data-*
    const id_paiement = button.data('id');
    const id_eleve = button.data('id-eleve');
    const id_tarif = button.data('id-tarif');
    const id_periode = button.data('id-periode');
    const montant_base = button.data('montant-base');
    const reduction_appliquee = button.data('reduction-appliquee');
    const montant_final = button.data('montant-final');
    const date_paiement = button.data('date-paiement');
    const date_debut_periode = button.data('date-debut-periode');
    const mode_paiement = button.data('mode-paiement');
    const reference_paiement = button.data('reference-paiement');
    const statut_paiement = button.data('statut-paiement');
    const commentaire = button.data('commentaire');

    // Si id_paiement existe, c'est une modification
    if (id_paiement) {
      // Mettre à jour le titre du modal
      $(this).find('.modal-title').text('Modifier le Paiement');
      
      // Remplir les champs avec les valeurs existantes
      $('#id_paiement').val(id_paiement);
      $('#id_eleve').val(id_eleve).trigger('change');
      $('#id_tarif').val(id_tarif).trigger('change');
      $('#id_periode').val(id_periode).trigger('change');
      $('#montant_base').val(montant_base);
      $('#reduction_appliquee').val(reduction_appliquee);
      $('#montant_final').val(montant_final);
      $('#date_paiement').val(date_paiement);
      $('#date_debut_periode').val(date_debut_periode);
      $('#mode_paiement').val(mode_paiement);
      $('#reference_paiement').val(reference_paiement);
      $('#statut_paiement').val(statut_paiement);
      $('#commentaire').val(commentaire);
      
      // Récupérer les informations de l'élève pour mettre à jour les champs niveau, filière et classe
      const eleve_option = $('#id_eleve option[value="' + id_eleve + '"]');
      if (eleve_option.length) {
        const niveau_id = eleve_option.data('niveau');
        const filiere_id = eleve_option.data('filiere');
        
        // Mettre à jour les sélecteurs de niveau et filière
        $('#niveau_scolaire_eleve').val(niveau_id).change();
        setTimeout(() => {
          $('#filiere_eleve').val(filiere_id).change();
          // Permettre un délai pour que la liste des classes se mette à jour
          setTimeout(() => {
            // La classe sera mise à jour automatiquement si vous avez une logique pour cela
          }, 100);
        }, 100);
      }
    } else {
      // C'est un nouveau paiement
      $(this).find('.modal-title').text('Nouveau Paiement');
      $(this).find('form')[0].reset();
      $('#id_paiement').val('');
    }
  });

  // Calcul automatique du montant final lors du changement de tarif ou de période
  $('#id_tarif, #id_periode').change(function() {
    calculateMontantFinal();
  });

  function calculateMontantFinal() {
    const tarif = $('#id_tarif option:selected');
    const periode = $('#id_periode option:selected');
    
    if (tarif.length && periode.length) {
      const montant_base = parseFloat(tarif.data('montant')) || 0;
      const reduction_pourcentage = parseFloat(periode.data('reduction')) || 0;
      
      $('#montant_base').val(montant_base);
      
      const reduction_montant = (montant_base * reduction_pourcentage) / 100;
      $('#reduction_appliquee').val(reduction_montant.toFixed(2));
      
      const montant_final = montant_base - reduction_montant;
      $('#montant_final').val(montant_final.toFixed(2));
    }
  }

  // Gestion des dépendances entre niveau, filière et classe
  $('#niveau_scolaire_eleve').change(function() {
    const niveau_id = $(this).val();
    
    // Filtrer les filières disponibles pour ce niveau
    $('#filiere_eleve').prop('disabled', !niveau_id);
    $('#filiere_eleve option').hide();
    $('#filiere_eleve option[value=""]').show();
    $('#filiere_eleve option[data-niveau="' + niveau_id + '"]').show();
    $('#filiere_eleve').val('');
    
    // Réinitialiser la classe
    $('#classe_eleve').prop('disabled', true);
    $('#classe_eleve').val('');
  });

  $('#filiere_eleve').change(function() {
    const filiere_id = $(this).val();
    
    // Filtrer les classes disponibles pour cette filière
    $('#classe_eleve').prop('disabled', !filiere_id);
    $('#classe_eleve option').hide();
    $('#classe_eleve option[value=""]').show();
    $('#classe_eleve option[data-filiere="' + filiere_id + '"]').show();
    $('#classe_eleve').val('');
    
    // Filtrer les élèves selon le niveau et la filière sélectionnés
    const niveau_id = $('#niveau_scolaire_eleve').val();
    $('#id_eleve option').hide();
    $('#id_eleve option[value=""]').show();
    $('#id_eleve option[data-niveau="' + niveau_id + '"][data-filiere="' + filiere_id + '"]').show();
    $('#id_eleve').val('');
  });

  // Filtrer les tarifs selon le niveau et la filière sélectionnés
  $('#id_eleve').change(function() {
    const eleve_option = $('#id_eleve option:selected');
    if (eleve_option.length) {
      const niveau_id = eleve_option.data('niveau');
      const filiere_id = eleve_option.data('filiere');
      
      $('#id_tarif option').hide();
      $('#id_tarif option[value=""]').show();
      $('#id_tarif option[data-niveau="' + niveau_id + '"][data-filiere="' + filiere_id + '"]').show();
      $('#id_tarif').val('');
    }
  });
});
  </script>

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