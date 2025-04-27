<div class="modal fade" id="evaluationModal" tabindex="-1" aria-labelledby="paiementModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paiementModalLabel">Gérer le Paiement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" id="id_paiement" name="id_paiement">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="titre" class="form-label">Titre</label>
              <input type="text" class="form-control" id="titre" name="titre" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="id_tarif" class="form-label">Description</label>
              <input type="text" class="form-control" id="description" name="description" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="id_enseignant" class="form-label">Enseignant</label>
              <select class="form-select" id="id_enseignant" name="id_enseignant" required>
                <option value="">Sélectionner Enseignant</option>
                <?php
                $eseignants = get_all_enseignant($dbh);
                foreach ($eseignants as $eseignant) {
                  echo "<option value='{$eseignant->id_enseignant}'>{$eseignant->nom_enseignant} {$eseignant->prenom_enseignant}</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-12 mb-3">
              <label for="id_classe" class="form-label">Classe</label>
              <select class="form-select" id="id_classe" name="id_classe" required>
                <option value="">Sélectionner Classe</option>
                <?php
                $classes = get_all_classes($dbh)['data'];
                foreach ($classes as $classe) {
                  echo "<option value='{$classe->id_classe}'>{$classe->nom_classe}</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-12 mb-3">
              <label for="id_matiere" class="form-label">Matiere</label>
              <select class="form-select" id="id_matiere" name="id_matiere" required>
                <option value="">Sélectionner Matiere</option>
                <?php
                $matieres = get_all_matieres($dbh);
                foreach ($matieres as $matiere) {
                  echo "<option value='{$matiere->id_matiere}'>{$matiere->nom_matiere}  {$matiere->code_matiere}</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="type_evaluation" class="form-label">Type Evaluation</label>
              <select class="form-select" id="type_evaluation" name="type_evaluation" required>
                <option value="examen">Examen</option>
                <option value="devoir">devoir</option>
                <option value="quiz">quiz</option>
                <option value="autre">autre</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="statut" class="form-label">Statut</label>
              <select class="form-select" id="statut" name="statut" required>
                <option value="brouillon">brouillon</option>
                <option value="publie">publie</option>
                <option value="archive">archive</option>
              </select>
            </div>
            <div class="col-md-12 mb-3">
              <label for="fichier_path" class="form-label">Fichier</label>
              <input type="file" class="form-control" id="fichier_path" name="fichier_path" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" name="save" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>