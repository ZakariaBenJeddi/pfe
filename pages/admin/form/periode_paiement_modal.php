<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Gestion des Périodes de Paiement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="periodeForm" method="post">
          <div class="row mb-3">
            <div class="col-6">
              <label for="id_periode" class="form-label">ID de la Période</label>
              <input type="text" class="form-control" id="id_periode" name="id_periode" readonly>
            </div>
            <div class="col-6">
              <label for="nom_periode" class="form-label">Nom de la Période</label>
              <input type="text" class="form-control" id="nom_periode" name="nom_periode" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-6">
              <label for="nombre_mois" class="form-label">Nombre de Mois</label>
              <input type="number" class="form-control" id="nombre_mois" name="nombre_mois" required>
            </div>
            <div class="col-6">
              <label for="pourcentage_reduction" class="form-label">Pourcentage de Réduction</label>
              <input type="number" class="form-control" id="pourcentage_reduction" name="pourcentage_reduction" step="0.01" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" required></textarea>
          </div>

          <div class="row mb-3">
            <div class="col-6">
              <label for="est_actif" class="form-label">Actif</label>
              <select class="form-select" id="est_actif" name="est_actif">
                <option value="1">Oui</option>
                <option value="0">Non</option>
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