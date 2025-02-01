document.addEventListener('DOMContentLoaded', function() {
    const niveauSelect = document.getElementById('niveau_scolaire_eleve');
    const filiereSelect = document.getElementById('filiere_eleve');
    const classeSelect = document.getElementById('classe_eleve');

    // Gestion du changement de niveau
    niveauSelect.addEventListener('change', function() {
        const niveauId = this.value;
        filiereSelect.disabled = !niveauId;
        classeSelect.disabled = true;
        classeSelect.innerHTML = '<option value="">Sélectionner une classe</option>';
        
        if(niveauId) {
            // Récupérer les filières du niveau
            fetch(`get_filieres.php?niveau_id=${niveauId}`)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">Sélectionner une filière</option>';
                    data.forEach(filiere => {
                        options += `<option value="${filiere.id_filiere}">${filiere.nom_filiere}</option>`;
                    });
                    filiereSelect.innerHTML = options;
                });
        } else {
            filiereSelect.innerHTML = '<option value="">Sélectionner une filière</option>';
        }
    });

    // Gestion du changement de filière
    filiereSelect.addEventListener('change', function() {
        const filiereId = this.value;
        classeSelect.disabled = !filiereId;
        
        if(filiereId) {
            // Récupérer les classes de la filière
            fetch(`get_classes.php?filiere_id=${filiereId}`)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">Sélectionner une classe</option>';
                    data.forEach(classe => {
                        options += `<option value="${classe.id_classe}">${classe.nom_classe}</option>`;
                    });
                    classeSelect.innerHTML = options;
                });
        } else {
            classeSelect.innerHTML = '<option value="">Sélectionner une classe</option>';
        }
    });
});