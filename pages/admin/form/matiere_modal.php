<?php
//* Read
// try {
//   $filieres = get_filieres_with_niveaux($dbh);
// } catch (Exception $e) {
//   echo "<script>alert('" . htmlspecialchars($e->getMessage()) . "');</script>";
//   $filieres = [];
// }
?>
<div class="modal fade" id="MatiereModal" tabindex="-1" aria-labelledby="paiementModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paiementModalLabel">Gérer le Paiement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post">
        <div class="card-body">
          <p class="text-uppercase text-sm">Information Matiere</p>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="nom_filiere" class="form-control-label">Nom Matiere</label>
                <input class="form-control" type="text" name="nom_filiere" id="nom_filiere" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="code_filiere" class="form-control-label">Code Matiere</label>
                <input class="form-control" type="text" name="code_filiere" id="code_filiere" required>
              </div>
            </div>


            <div class="col-md-6">
              <div class="form-group">
                <label for="filiere" class="form-control-label">Filiere</label>
                <select name="filiere" class="form-select" required>
                  <?php foreach ($filieres['data'] as $value) : 
                  ?>
                  <option value="<?= $value->id_filiere ?>">
                    <?= $value->nom_filiere ?>
                  </option>
                  <?php endforeach; 
                  ?>
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="statut" class="form-control-label">Statut</label>
                <select name="statut" class="form-select" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="coeficient" class="form-control-label">Coeficient</label>
                <input class="form-control" type="number" min="1" name="coeficient" id="coeficient" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="nombre_seance" class="form-control-label">Nombre Seance Par Semaine</label>
                <input class="form-control" type="number" min="0" name="nombre_seance" id="nombre_seance" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="nombre_heures_max" class="form-control-label">Nombre Heure Par Semaine</label>
                <input class="form-control" type="number" min="1" name="nombre_heures_max" id="nombre_heures_max" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="volume_horaire" class="form-control-label">Volume Horaire</label>
                <input class="form-control" type="number" min="1" name="volume_horaire" id="volume_horaire" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="type_matiere" class="form-control-label">Type Matiere</label>
                <select name="type_matiere" class="form-select" required>
                  <option value="Principale">Principale</option>
                  <option value="Secondaire">Secondaire</option>
                  <option value="Optionnelle">Optionnelle</option>
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="date_creation" class="form-control-label">Description</label>
                <textarea class="form-control" name="description" id="description" rows="3"></textarea>
              </div>
            </div>
          </div>

          <div class="modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" name="save" class="btn btn-primary">Enregistrer</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>