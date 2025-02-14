document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('#paiementModal form');
  const idEleveSelect = document.getElementById('id_eleve');
  const idTarifSelect = document.getElementById('id_tarif');
  const idPeriodeSelect = document.getElementById('id_periode');
  const montantBaseInput = document.getElementById('montant_base');
  const reductionAppliqueeInput = document.getElementById('reduction_appliquee');
  const montantFinalInput = document.getElementById('montant_final');
  const allFormInputs = form.querySelectorAll('input, select, textarea');

  function filterTarifs(eleveNiveau, eleveFiliere) {
    console.log('Données reçues pour le filtrage:', {
      eleveNiveau: eleveNiveau,
      eleveFiliere: eleveFiliere,
      typeEleveNiveau: typeof eleveNiveau,
      typeEleveFiliere: typeof eleveFiliere
    });

    Array.from(idTarifSelect.options).forEach(option => {
      if (option.value === '') return;

      const tarifNiveau = option.getAttribute('data-niveau');
      const tarifFiliere = option.getAttribute('data-filiere');

      const correspondance = (
        String(tarifNiveau) === String(eleveNiveau) && 
        String(tarifFiliere) === String(eleveFiliere)
      );

      option.style.display = correspondance ? '' : 'none';
    });

    // Réinitialiser la sélection si nécessaire
    if (!Array.from(idTarifSelect.options)
        .filter(opt => opt.style.display !== 'none')
        .includes(idTarifSelect.selectedOptions[0])) {
      idTarifSelect.value = '';
    }
    
    // Mettre à jour les montants après le filtrage
    updateMontants();
  }

  function updateMontants() {
    const selectedTarif = idTarifSelect.options[idTarifSelect.selectedIndex];
    const selectedPeriode = idPeriodeSelect.options[idPeriodeSelect.selectedIndex];
    
    const montantBase = selectedTarif ? parseFloat(selectedTarif.getAttribute('data-montant')) || 0 : 0;
    montantBaseInput.value = montantBase.toFixed(2);
    
    const reduction = selectedPeriode ? parseFloat(selectedPeriode.getAttribute('data-reduction')) || 0 : 0;
    reductionAppliqueeInput.value = reduction.toFixed(2);
    
    const montantFinal = montantBase - (montantBase * (reduction / 100));
    montantFinalInput.value = montantFinal.toFixed(2);
  }

  const modalElement = document.getElementById('paiementModal');
  modalElement.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    if (!trigger.hasAttribute('data-id')) {
      form.reset();
      disableAllFieldsExceptStudent();
    }
  });

  idEleveSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (this.value) {
      enableAllFields();
      
      const niveau = selectedOption.getAttribute('data-niveau');
      const filiere = selectedOption.getAttribute('data-filiere');
      
      filterTarifs(niveau, filiere);
    } else {
      disableAllFieldsExceptStudent();
      idTarifSelect.value = '';
      Array.from(idTarifSelect.options).forEach(option => {
        option.style.display = '';
      });
      updateMontants();
    }
  });

  idTarifSelect.addEventListener('change', function() {
    updateMontants();
  });

  idPeriodeSelect.addEventListener('change', function() {
    updateMontants();
  });

  // Fonctions utilitaires
  function disableAllFieldsExceptStudent() {
    allFormInputs.forEach(input => {
      if (input.id !== 'id_eleve') {
        input.disabled = true;
      }
    });
  }

  function enableAllFields() {
    allFormInputs.forEach(input => {
      input.disabled = false;
    });
  }

  // Initialisation au chargement
  disableAllFieldsExceptStudent();
  updateMontants();
});