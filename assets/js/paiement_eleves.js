document.addEventListener('DOMContentLoaded', function () {
  // Récupérer tous les éléments du formulaire
  const form = document.querySelector('#paiementModal form');
  const idEleveSelect = document.getElementById('id_eleve');
  const idTarifSelect = document.getElementById('id_tarif');
  const allFormInputs = form.querySelectorAll('input, select, textarea');
  
  // Désactiver tous les champs sauf la sélection de l'élève
  function disableAllFieldsExceptStudent() {
    allFormInputs.forEach(input => {
      if (input.id !== 'id_eleve') {
        input.disabled = true;
      }
    });
  }
  
  // Activer tous les champs
  function enableAllFields() {
    allFormInputs.forEach(input => {
      input.disabled = false;
    });
  }
  
  // Filtrer les tarifs en fonction du niveau et de la filière
  function filterTarifs(niveau, filiere) {
    const options = idTarifSelect.querySelectorAll('option');
    options.forEach(option => {
      if (option.value === '') return; // Garder l'option "Sélectionner"
      
      const tarifNiveau = option.getAttribute('data-niveau');
      const tarifFiliere = option.getAttribute('data-filiere');
      
      if (tarifNiveau === niveau && tarifFiliere === filiere) {
        option.style.display = '';
      } else {
        option.style.display = 'none';
      }
    });
    
    // Réinitialiser la sélection
    idTarifSelect.value = '';
  }
  
  // Désactiver les champs au chargement
  disableAllFieldsExceptStudent();
  
  // Gérer le changement d'élève
  idEleveSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (this.value) {
      // Activer tous les champs
      enableAllFields();
      
      // Récupérer le niveau et la filière de l'élève sélectionné
      const niveau = selectedOption.getAttribute('data-niveau');
      const filiere = selectedOption.getAttribute('data-filiere');
      
      // Filtrer les tarifs
      filterTarifs(niveau, filiere);
    } else {
      // Désactiver tous les champs si aucun élève n'est sélectionné
      disableAllFieldsExceptStudent();
    }
  });
  
  // Garder le reste de votre code existant pour la gestion des montants
  const montantBaseInput = document.getElementById("montant_base");
  const idPeriodeSelect = document.getElementById("id_periode");
  const reductionAppliqueeInput = document.getElementById("reduction_appliquee");
  const montantFinalInput = document.getElementById("montant_final");

  idTarifSelect.addEventListener("change", function () {
    const selectedOption = this.options[this.selectedIndex];
    const montantBase = selectedOption.getAttribute("data-montant") || 0;
    montantBaseInput.value = parseFloat(montantBase).toFixed(2);
    updateMontantFinal();
  });

  idPeriodeSelect.addEventListener("change", function () {
    const selectedOption = this.options[this.selectedIndex];
    const reduction = selectedOption.getAttribute("data-reduction") || 0;
    reductionAppliqueeInput.value = parseFloat(reduction).toFixed(2);
    updateMontantFinal();
  });

  function updateMontantFinal() {
    const montantBase = parseFloat(montantBaseInput.value) || 0;
    const reduction = parseFloat(reductionAppliqueeInput.value) || 0;
    const montantFinal = montantBase - (montantBase * (reduction / 100));
    montantFinalInput.value = montantFinal.toFixed(2);
  }
});
