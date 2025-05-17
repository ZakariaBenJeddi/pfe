    document.getElementById('professeur_id').addEventListener('change', function() {
      const profId = this.value;
      const groupeSelect = document.getElementById('groupe_id');
      const matiereSelect = document.getElementById('matiere_id');

      if (profId) {
        groupeSelect.disabled = false;
        fetch(`?action=get_groupes&professeur_id=${profId}`)
          .then(response => response.json())
          .then(data => {
            groupeSelect.innerHTML = '<option value="">Choisir un groupe</option>';
            data.forEach(groupe => {
              groupeSelect.innerHTML += `<option value="${groupe.id_classe}">${groupe.nom_classe}</option>`;
            });
          })
          .catch(error => console.error('Erreur:', error));
      } else {
        groupeSelect.disabled = true;
        matiereSelect.disabled = true;
        groupeSelect.innerHTML = '<option value="">Choisir un groupe</option>';
        matiereSelect.innerHTML = '<option value="">Choisir une matière</option>';
      }
    });

    // JavaScript corrections
    document.getElementById('groupe_id').addEventListener('change', function() {
      const groupeId = this.value;
      const matiereSelect = document.getElementById('matiere_id');

      if (groupeId) {
        matiereSelect.disabled = false;
        fetch(`?action=get_matieres&classe_id=${groupeId}`) // Changed from groupe_id to classe_id
          .then(response => response.json())
          .then(data => {
            matiereSelect.innerHTML = '<option value="">Choisir une matière</option>';
            data.forEach(matiere => {
              matiereSelect.innerHTML += `<option value="${matiere.id}">${matiere.nom}</option>`;
            });
          })
          .catch(error => console.error('Erreur:', error));
      }
    });

    document.getElementById('assignmentForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      formData.append('action', 'save');

      fetch('', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            this.reset();
            location.reload();
          } else {
            alert('Erreur : ' + data.message);
          }
        })
        .catch(error => console.error('Erreur:', error));
    });