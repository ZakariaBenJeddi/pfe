// document.addEventListener('DOMContentLoaded', function() {
//     const niveauSelect = document.getElementById('niveau_scolaire_eleve');
//     const filiereSelect = document.getElementById('filiere_eleve');
//     const classeSelect = document.getElementById('classe_eleve');

//     // Gestion du changement de niveau
//     niveauSelect.addEventListener('change', function() {
//         const niveauId = this.value;
//         filiereSelect.disabled = !niveauId;
//         classeSelect.disabled = true;
//         classeSelect.innerHTML = '<option value="">Sélectionner une classe</option>';
        
//         if(niveauId) {
//             console.log("niveauId")
//             console.log(niveauId)
//             // Récupérer les filières du niveau
//             fetch(`get_filieres.php?niveau_id=${niveauId}`)
//                 .then(response => response.json())
//                 .then(data => {
//                     let options = '<option value="">Sélectionner une filière</option>';
//                     data.forEach(filiere => {
//                         options += `<option value="${filiere.id_filiere}">${filiere.nom_filiere}</option>`;
//                     });
//                     filiereSelect.innerHTML = options;
//                 });
//         } else {
//             filiereSelect.innerHTML = '<option value="">Sélectionner une filière</option>';
//         }
//     });

//     // Gestion du changement de filière
//     filiereSelect.addEventListener('change', function() {
//         const filiereId = this.value;
//         classeSelect.disabled = !filiereId;
        
//         if(filiereId) {
//             // Récupérer les classes de la filière
//             fetch(`get_classes.php?filiere_id=${filiereId}`)
//                 .then(response => response.json())
//                 .then(data => {
//                     let options = '<option value="">Sélectionner une classe</option>';
//                     data.forEach(classe => {
//                         options += `<option value="${classe.id_classe}">${classe.nom_classe}</option>`;
//                     });
//                     classeSelect.innerHTML = options;
//                 });
//         } else {
//             classeSelect.innerHTML = '<option value="">Sélectionner une classe</option>';
//         }
//     });
// });

document.addEventListener('DOMContentLoaded', function() {
    const niveauSelect = document.getElementById('niveau_scolaire_eleve');
    const filiereSelect = document.getElementById('filiere_eleve');
    const classeSelect = document.getElementById('classe_eleve');
    const eleveSelect = document.getElementById('id_eleve');
    const form = document.querySelector('#paiementModal form');
    const allFormInputs = form.querySelectorAll('input, select, textarea');

    // Fonction pour désactiver tous les champs sauf le niveau
    function disableAllFieldsExceptNiveau() {
        allFormInputs.forEach(input => {
            if (input.id !== 'niveau_scolaire_eleve') {
                input.disabled = true;
            }
        });
    }

    // Initialisation - désactiver tous les champs sauf le niveau
    disableAllFieldsExceptNiveau();

    // Filtrer les options dans un select en fonction d'un attribut data
    function filterSelectOptions(selectElement, dataAttribute, dataValue) {
        Array.from(selectElement.options).forEach(option => {
            if (option.value === '') return; // Ignorer l'option vide
            
            const optionDataValue = option.getAttribute(`data-${dataAttribute}`);
            option.style.display = String(optionDataValue) === String(dataValue) ? '' : 'none';
        });
    }

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

    // Gestion du changement de classe
    classeSelect.addEventListener('change', function() {
        const classeId = this.value;
        
        // Réinitialiser et désactiver le select élève
        eleveSelect.disabled = !classeId;
        eleveSelect.value = '';
        
        if(classeId) {
            // Charger les élèves de cette classe via AJAX
            fetch(`get_eleves.php?classe_id=${classeId}`)
                .then(response => response.json())
                .then(data => {
                    // Vider et remplir le select des élèves
                    eleveSelect.innerHTML = '<option value="">Sélectionner un élève</option>';
                    
                    if(data.success && data.data && data.data.length > 0) {
                        data.data.forEach(eleve => {
                            const option = document.createElement('option');
                            option.value = eleve.id_eleve;
                            option.textContent = `${eleve.nom} ${eleve.prenom}`;
                            option.setAttribute('data-niveau', eleve.id_niveau);
                            option.setAttribute('data-filiere', eleve.id_filiere);
                            eleveSelect.appendChild(option);
                        });
                    } else {
                        eleveSelect.innerHTML = '<option value="">Aucun élève dans cette classe</option>';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des élèves:', error);
                    eleveSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                });
        }
    });
});