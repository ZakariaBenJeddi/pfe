document.addEventListener('DOMContentLoaded', function() {
  const classeSelect = document.getElementById('classe_box');
  const eleveSelect = document.getElementById('eleve_box');
  const id_elevesX = document.getElementById('id_elevesX');
  const heuresAbsenceDiv = document.getElementById('heures_absence');
  const totalHeuresSpan = document.getElementById('total_heures');

  function loadEleves(classeId) {
        eleveSelect.innerHTML = '<option value="">Chargement...</option>';
        // console.log("Chargement des élèves pour la classe ID:", classeId);
        
        // Obtenir l'URL actuelle
        const url = window.location.pathname + `?action=getEleves&classe_id=${classeId}`;
        
        fetch(url)
            .then(response => {
                // console.log("Réponse reçue:", response.status);
                return response.json();
            })
            .then(data => {
                // console.log("Données reçues:", data);
                eleveSelect.innerHTML = '<option value="">Sélectionner un élève</option>';
                
                // Vérifier si data.data existe
                const eleves = data.data;
                
                // Vérifier si les données sont un tableau ou un objet
                if (data.success && eleves) {
                    // Si c'est un objet avec des indices numériques
                    if (typeof eleves === 'object' && !Array.isArray(eleves)) {
                        // Convertir l'objet en tableau
                        const elevesArray = Object.values(eleves).filter(item => typeof item === 'object');
                        
                        if (elevesArray.length > 0) {
                            elevesArray.forEach(eleve => {
                                if (eleve && eleve.id_eleve) {
                                    const option = document.createElement('option');
                                    option.value = eleve.id_eleve;
                                    option.textContent = eleve.nom + ' ' + eleve.prenom;
                                    eleveSelect.appendChild(option);
                                }
                            });
                            return;
                        }
                    }
                    // Si c'est un tableau standard
                    else if (Array.isArray(eleves) && eleves.length > 0) {
                        eleves.forEach(eleve => {
                            const option = document.createElement('option');
                            option.value = eleve.id_eleve;
                            option.textContent = eleve.nom + ' ' + eleve.prenom;
                            eleveSelect.appendChild(option);
                        });
                        return;
                    }
                }
                
                // Si on arrive ici, c'est qu'on n'a pas pu ajouter d'élèves
                eleveSelect.innerHTML = '<option value="">Aucun élève dans cette classe</option>';
                
                // Débogage supplémentaire
                // console.log("Structure de data:", JSON.stringify(data));
            })
            .catch(error => {
                console.error('Erreur:', error);
                eleveSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            });
  }
  
  // Écouter le changement de classe
  classeSelect.addEventListener('change', function() {
      const classeId = this.value;
      if (classeId) {
          loadEleves(classeId);
          id_elevesX.value = ''
          heuresAbsenceDiv.classList.add('d-none');
      } else {
          eleveSelect.innerHTML = '<option value="">Sélectionnez d\'abord une classe</option>';
          heuresAbsenceDiv.classList.add('d-none');
      }
  });
  
  // Écouter le changement d'élève
  eleveSelect.addEventListener('change', function() {
    const eleveId = this.value;
    if (eleveId) {
        // Récupère l'ID de l'élève sélectionné
        id_elevesX.value = eleveId;
        
        // Affiche le nom et prénom dans l'input correspondant
        document.getElementById('nom_prenom').value = this.options[this.selectedIndex].text;
        
        // Affiche la div des heures d'absence si elle était masquée
        if (heuresAbsenceDiv) {
            heuresAbsenceDiv.classList.remove('d-none');
        }
    } else {
        // Réinitialise les champs si aucun élève n'est sélectionné
        id_elevesX.value = '';
        document.getElementById('nom_prenom').value = '';
        
        // Cache la div des heures d'absence
        if (heuresAbsenceDiv) {
            heuresAbsenceDiv.classList.add('d-none');
        }
    }
});
});