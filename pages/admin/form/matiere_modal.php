<!-- <div class="modal fade" id="MatiereModal" tabindex="-1" aria-labelledby="paiementModalLabel" aria-hidden="true">
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
</div> -->



<div class="modal fade" id="MatiereModal" tabindex="-1" aria-labelledby="paiementModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paiementModalLabel">Gérer la Matière</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post">
        <!-- Champ caché pour l'ID en mode édition -->
        <input type="hidden" name="id_matiere" id="id_matiere" value="">
        
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
                <select name="filiere" id="filiere" class="form-select" required>
                  <?php foreach ($filieres['data'] as $value) : ?>
                  <option value="<?= $value->id_filiere ?>">
                    <?= $value->nom_filiere ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="statut" class="form-control-label">Statut</label>
                <select name="statut" id="statut" class="form-select" required>
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
                <select name="type_matiere" id="type_matiere" class="form-select" required>
                  <option value="Principale">Principale</option>
                  <option value="Secondaire">Secondaire</option>
                  <option value="Optionnelle">Optionnelle</option>
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="description" class="form-control-label">Description</label>
                <textarea class="form-control" name="description" id="description" rows="3"></textarea>
              </div>
            </div>
          </div>

          <div class="modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" name="save" id="submitBtn" class="btn btn-primary">Enregistrer</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Script pour gérer l'ouverture automatique du modal en mode édition -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Si on est en mode édition, ouvrir le modal et remplir les champs
    <?php if ($editMode && $matiereData): ?>
    
    // Récupérer les données de la matière à éditer
    var matiereData = <?= json_encode($matiereData) ?>;
    
    // Remplir les champs du formulaire
    document.getElementById('id_matiere').value = matiereData.id_matiere;
    document.getElementById('nom_filiere').value = matiereData.nom_matiere;
    document.getElementById('code_filiere').value = matiereData.code_matiere;
    document.getElementById('filiere').value = matiereData.id_filiere;
    document.getElementById('statut').value = matiereData.statut;
    document.getElementById('coeficient').value = matiereData.coefficient;
    document.getElementById('nombre_seance').value = matiereData.nombre_seance_semaine;
    document.getElementById('nombre_heures_max').value = matiereData.nombre_heures_semaine;
    document.getElementById('volume_horaire').value = matiereData.volume_horaire;
    document.getElementById('type_matiere').value = matiereData.type_matiere;
    document.getElementById('description').value = matiereData.description;
    
    // Changer le bouton de soumission pour la mise à jour
    var submitBtn = document.getElementById('submitBtn');
    submitBtn.name = 'update';
    submitBtn.textContent = 'Mettre à jour';
    
    // Changer le titre du modal
    document.getElementById('paiementModalLabel').textContent = 'Modifier la Matière';
    
    // Ouvrir le modal
    var matiereModal = new bootstrap.Modal(document.getElementById('MatiereModal'));
    matiereModal.show();
    <?php endif; ?>
    
    // Ajouter des écouteurs d'événements pour les boutons d'édition
    document.querySelectorAll('.fas.fa-pencil-alt').forEach(function(button) {
        button.addEventListener('click', function(e) {
            // Prévenir le comportement par défaut du lien
            e.preventDefault();
            
            // Récupérer l'ID de la matière depuis l'attribut href
            var href = this.closest('a').getAttribute('href');
            var idMatiere = href.split('=')[1].split('&')[0];
            
            // Rediriger vers la page avec l'ID pour l'édition
            window.location.href = 'matiere.php?id=' + idMatiere;
        });
    });
});
</script>