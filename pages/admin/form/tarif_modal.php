<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Gérer les Tarifs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="POST">
        <div class="modal-body">
          <input type="hidden" id="id_tarif" name="id_tarif">

          <div class="mb-3">
            <label for="id_type_frais" class="form-label">Type de Frais</label>
            <select class="form-select" id="id_type_frais" name="id_type_frais" required>
              <option value="">Sélectionner</option>
              <?php
              $types_frais = get_all_types_frais($dbh);
              foreach ($types_frais as $type) {
                echo "<option value='{$type->id_type_frais}'>{$type->nom_frais}</option>";
              }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="id_niveau" class="form-label">Niveau</label>
            <select class="form-select" id="id_niveau" name="id_niveau" required>
              <option value="">Sélectionner</option>
              <?php
              $niveaux = get_all_niveau($dbh);
              foreach ($niveaux as $niveau) {
                echo "<option value='{$niveau->id_niveau}'>{$niveau->nom_niveau}</option>";
              }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="id_filiere" class="form-label">Filière</label>
            <select class="form-select" id="id_filiere" name="id_filiere" required>
              <option value="">Sélectionner</option>
              <?php
              $filieres = get_filieres_with_niveaux($dbh)['data'];
              foreach ($filieres as $filiere) {
                echo "<option value='{$filiere->id_filiere}'>{$filiere->nom_filiere}</option>";
              }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="montant_base" class="form-label">Montant de Base</label>
            <input type="number" class="form-control" id="montant_base" name="montant_base" step="0.01" required>
          </div>

          <div class="mb-3">
            <label for="annee_scolaire" class="form-label">Année Scolaire</label>
            <input type="text" class="form-control" id="annee_scolaire" name="annee_scolaire" placeholder="ex: 2024-2025" required>
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
