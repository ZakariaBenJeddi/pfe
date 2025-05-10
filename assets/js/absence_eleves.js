document.addEventListener('DOMContentLoaded', function() {
  const classeSelect = document.getElementById('classe_box');
  const eleveSelect = document.getElementById('eleve_box');
  const id_elevesX = document.getElementById('id_elevesX');
  const nomPrenomInput = document.getElementById('nom_prenom');
  const heuresAbsenceDiv = document.getElementById('heures_absence');

  function loadEleves(classeId) {
    eleveSelect.innerHTML = '<option value="">Chargement...</option>';
    console.log("Chargement des élèves pour la classe ID:", classeId);
    
    // Construire l'URL correctement en conservant le paramètre id_absence si présent
    let currentUrl = window.location.href;
    
    // Extraire le chemin de base (sans les paramètres de requête)
    const baseUrl = currentUrl.split('?')[0];
    
    // Construire l'URL pour l'action getEleves
    let url = `${baseUrl}?action=getEleves&classe_id=${classeId}`;
    
    // Si nous sommes sur la page edit_absence.php, préserver l'id_absence
    if (window.location.pathname.includes('edit_absence.php')) {
      // Extraire l'id_absence de l'URL actuelle si présent
      const urlParams = new URLSearchParams(window.location.search);
      const id_absence = urlParams.get('id_absence') || urlParams.get('id');
      if (id_absence) {
        url += `&id_absence=${id_absence}`;
      }
    }
    
    console.log("URL de requête:", url);
    
    fetch(url)
      .then(response => {
        console.log("Réponse reçue:", response.status);
        if (!response.ok) {
          throw new Error('Erreur réseau: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log("Données reçues:", data);
        eleveSelect.innerHTML = '<option value="">Sélectionner un élève</option>';
        
        // Vérifier si data.data existe et contient des élèves
        const eleves = data.data;
        
        if (data.success && eleves && eleves.length > 0) {
          eleves.forEach(eleve => {
            if (eleve && eleve.id_eleve) {
              const option = document.createElement('option');
              option.value = eleve.id_eleve;
              option.textContent = eleve.nom + ' ' + eleve.prenom;
              // Ajouter les attributs data- pour l'usage dans edit_absence.php
              option.setAttribute('data-nom', eleve.nom);
              option.setAttribute('data-prenom', eleve.prenom);
              eleveSelect.appendChild(option);
            }
          });
        } else {
          eleveSelect.innerHTML = '<option value="">Aucun élève dans cette classe</option>';
        }
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
      // Ne pas réinitialiser id_elevesX et nom_prenom immédiatement
      // pour éviter les problèmes lors du chargement
      if (heuresAbsenceDiv) {
        heuresAbsenceDiv.classList.add('d-none');
      }
    } else {
      eleveSelect.innerHTML = '<option value="">Sélectionnez d\'abord une classe</option>';
      if (heuresAbsenceDiv) {
        heuresAbsenceDiv.classList.add('d-none');
      }
    }
  });
  
  // Écouter le changement d'élève
  eleveSelect.addEventListener('change', function() {
    const eleveId = this.value;
    if (eleveId) {
      // Récupère l'ID de l'élève sélectionné
      id_elevesX.value = eleveId;
      
      // Affiche le nom et prénom dans l'input correspondant
      const selectedOption = this.options[this.selectedIndex];
      if (nomPrenomInput) {
        nomPrenomInput.value = selectedOption.textContent;
      }
      
      // Affiche la div des heures d'absence si elle existe
      if (heuresAbsenceDiv) {
        heuresAbsenceDiv.classList.remove('d-none');
      }
    } else {
      // Réinitialise les champs si aucun élève n'est sélectionné
      id_elevesX.value = '';
      if (nomPrenomInput) {
        nomPrenomInput.value = '';
      }
      
      // Cache la div des heures d'absence
      if (heuresAbsenceDiv) {
        heuresAbsenceDiv.classList.add('d-none');
      }
    }
  });
});