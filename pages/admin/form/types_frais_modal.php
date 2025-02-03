<!-- form/type_frais_modal.php -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Gestion des Types de Frais</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="typesFraisForm" method="post">
          <div class="row mb-3">
            <div class="col-6">
              <label for="id_type_frais" class="form-label">ID du Type de Frais</label>
              <input type="text" class="form-control" id="id_type_frais" name="id_type_frais" readonly>
            </div>
            <div class="col-6">
              <label for="nom_frais" class="form-label">Nom des Frais</label>
              <input type="text" class="form-control" id="nom_frais" name="nom_frais" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" required></textarea>
          </div>

          <div class="row mb-3">
            <div class="col-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="est_obligatoire" name="est_obligatoire">
                <label class="form-check-label" for="est_obligatoire">
                  Obligatoire
                </label>
              </div>
            </div>
            <div class="col-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="est_mensuel" name="est_mensuel">
                <label class="form-check-label" for="est_mensuel">
                  Mensuel
                </label>
              </div>
            </div>
            <div class="col-4">
              <label for="est_actif" class="form-label">Statut</label>
              <select class="form-select" id="est_actif" name="est_actif">
                <option value="1">Actif</option>
                <option value="0">Inactif</option>
              </select>
            </div>
          </div>
          
          <input type="submit" name="save" class="btn btn-primary mt-3 float-end" id="saveChanges" value="Enregistrer les changements">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>