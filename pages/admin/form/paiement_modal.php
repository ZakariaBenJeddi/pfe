<?php
$all_niveaux = get_all_niveau($dbh);
$filieres = get_filieres_with_niveaux($dbh)['data'];
$classes = get_all_classes($dbh)['data'];
?>
<div class="modal fade" id="paiementModal" tabindex="-1" aria-labelledby="paiementModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paiementModalLabel">Gérer le Paiement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="POST">
        <div class="modal-body">
          <input type="hidden" id="id_paiement" name="id_paiement">
          <div class="row">
            <!-- Niveau scolaire -->
            <div class="col-md-6 mb-3">
              <div class="form-group">
                <label for="niveau_scolaire_eleve" class="form-control-label">Niveau Scolaire</label>
                <select class="form-control" name="niveau_scolaire_eleve" id="niveau_scolaire_eleve" required>
                  <option value="">-- Sélectionner un niveau --</option>
                  <?php foreach ($all_niveaux as $niveau) : ?>
                    <option value="<?= $niveau->id_niveau ?>">
                      <?=  htmlspecialchars($niveau->nom_niveau) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Filière -->
            <div class="col-md-6 mb-3">
              <div class="form-group">
                <label for="filiere_eleve" class="form-control-label">Filière</label>
                <select class="form-select" name="filiere_eleve" id="filiere_eleve" disabled required>
                  <option value="">Sélectionner une filière</option>
                  <?php foreach ($filieres as $filiere) : ?>
                      <option value="<?= $filiere->id_filiere ?>" >
                        <?=  htmlspecialchars($filiere->nom_filiere) ?>
                      </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Classe -->
            <div class="col-md-6 mb-3">
              <div class="form-group">
                <label for="classe_eleve" class="form-control-label">Classe</label>
                <select class="form-select" name="classe_eleve" id="classe_eleve" disabled required>
                  <option value="">Sélectionner une classe</option>
                  <?php foreach ($classes as $classe) : ?>
                      <option value="<?= $classe->id_classe ?>">
                        <?=  htmlspecialchars($classe->nom_classe) ?>
                      </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="id_eleve" class="form-label">Élève</label>
              <select class="form-select" id="id_eleve" name="id_eleve" required>
                <option value="">Sélectionner Élève</option>
                <?php
                $eleves = getElevesInfo($dbh)['data'];
                foreach ($eleves as $eleve) {
                  echo "<option value='{$eleve->id_eleve}' 
                  data-niveau='{$eleve->id_niveau}' 
                  data-filiere='{$eleve->id_filiere}'>
                  {$eleve->nom} {$eleve->prenom} 
                </option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="id_tarif" class="form-label">Tarif</label>
              <select class="form-select" id="id_tarif" name="id_tarif" required>
                <option value="">Sélectionner</option>
                <?php
                $tarifs = getAllTarifs($dbh);
                foreach ($tarifs as $tarif) {
                  echo "<option value='{$tarif->id_type_frais}' 
                  data-niveau='{$tarif->id_niveau}' 
                  data-filiere='{$tarif->id_filiere}' 
                  data-montant='{$tarif->montant_base}'>
                  {$tarif->nom_frais} 
                </option>";
                }
                ?>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="id_periode" class="form-label">Période</label>
              <select class="form-select" id="id_periode" name="id_periode" required>
                <option value="">Sélectionner</option>
                <?php
                $periodes = get_all_periodes_paiement($dbh);
                foreach ($periodes as $periode) {
                  if ($periode->est_actif == '1') {
                    echo "<option value='{$periode->id_periode}' data-reduction='{$periode->pourcentage_reduction}'>{$periode->nom_periode} - Réduction {$periode->pourcentage_reduction}%</option>";
                  }
                }
                ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="montant_base" class="form-label">Montant de Base</label>
              <input type="number" class="form-control" id="montant_base" name="montant_base" required readonly>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="reduction_appliquee" class="form-label">Réduction Appliquée</label>
              <input type="number" class="form-control" id="reduction_appliquee" name="reduction_appliquee" readonly>
            </div>
            <div class="col-md-6 mb-3">
              <label for="montant_final" class="form-label">Montant Final</label>
              <input type="number" class="form-control" id="montant_final" name="montant_final" step="0.01" required readonly>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="date_paiement" class="form-label">Date Paiement</label>
              <input type="date" class="form-control" id="date_paiement" name="date_paiement" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="date_debut_periode" class="form-label">Date Debut Periode</label>
              <input type="date" class="form-control" id="date_debut_periode" name="date_debut_periode" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mode_paiement" class="form-label">Mode Paiement</label>
              <select class="form-select" id="mode_paiement" name="mode_paiement" required>
                <option value="">Sélectionner</option>
                <option value="Espèces">Espèces</option>
                <option value="Chèque">Chèque</option>
                <option value="Virement">Virement</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="statut_paiement" class="form-label">Statut</label>
              <select class="form-select" id="statut_paiement" name="statut_paiement">
                <option value="Validé">Validé</option>
                <option value="En attente">En attente</option>
                <option value="Annulé">Annulé</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="reference_paiement" class="form-label">Référence Paiement</label>
              <input type="text" class="form-control" id="reference_paiement" name="reference_paiement">
            </div>
          </div>
          <div class="mb-3">
            <label for="commentaire" class="form-label">Commentaire</label>
            <textarea class="form-control" id="commentaire" name="commentaire" rows="2"></textarea>
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