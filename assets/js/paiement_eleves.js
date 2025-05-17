// document.addEventListener('DOMContentLoaded', function () {
//   const form = document.querySelector('#paiementModal form');
//   const idEleveSelect = document.getElementById('id_eleve');
//   const idTarifSelect = document.getElementById('id_tarif');
//   const idPeriodeSelect = document.getElementById('id_periode');
//   const montantBaseInput = document.getElementById('montant_base');
//   const reductionAppliqueeInput = document.getElementById('reduction_appliquee');
//   const montantFinalInput = document.getElementById('montant_final');
//   const allFormInputs = form.querySelectorAll('input, select, textarea');

//   function filterTarifs(eleveNiveau, eleveFiliere) {
//     console.log('Données reçues pour le filtrage:', {
//       eleveNiveau: eleveNiveau,
//       eleveFiliere: eleveFiliere,
//       typeEleveNiveau: typeof eleveNiveau,
//       typeEleveFiliere: typeof eleveFiliere
//     });

//     Array.from(idTarifSelect.options).forEach(option => {
//       if (option.value === '') return;

//       const tarifNiveau = option.getAttribute('data-niveau');
//       const tarifFiliere = option.getAttribute('data-filiere');

//       const correspondance = (
//         String(tarifNiveau) === String(eleveNiveau) && 
//         String(tarifFiliere) === String(eleveFiliere)
//       );

//       option.style.display = correspondance ? '' : 'none';
//     });

//     // Réinitialiser la sélection si nécessaire
//     if (!Array.from(idTarifSelect.options)
//         .filter(opt => opt.style.display !== 'none')
//         .includes(idTarifSelect.selectedOptions[0])) {
//       idTarifSelect.value = '';
//     }
    
//     // Mettre à jour les montants après le filtrage
//     updateMontants();
//   }

//   function updateMontants() {
//     const selectedTarif = idTarifSelect.options[idTarifSelect.selectedIndex];
//     const selectedPeriode = idPeriodeSelect.options[idPeriodeSelect.selectedIndex];
    
//     const montantBase = selectedTarif ? parseFloat(selectedTarif.getAttribute('data-montant')) || 0 : 0;
//     montantBaseInput.value = montantBase.toFixed(2);
    
//     const reduction = selectedPeriode ? parseFloat(selectedPeriode.getAttribute('data-reduction')) || 0 : 0;
//     reductionAppliqueeInput.value = reduction.toFixed(2);
    
//     const montantFinal = montantBase - (montantBase * (reduction / 100));
//     montantFinalInput.value = montantFinal.toFixed(2);
//   }

//   const modalElement = document.getElementById('paiementModal');
//   modalElement.addEventListener('show.bs.modal', function (event) {
//     const trigger = event.relatedTarget;
//     if (!trigger.hasAttribute('data-id')) {
//       form.reset();
//       disableAllFieldsExceptStudent();
//     }
//   });

//   idEleveSelect.addEventListener('change', function() {
//     const selectedOption = this.options[this.selectedIndex];
    
//     if (this.value) {
//       enableAllFields();
      
//       const niveau = selectedOption.getAttribute('data-niveau');
//       const filiere = selectedOption.getAttribute('data-filiere');
      
//       filterTarifs(niveau, filiere);
//     } else {
//       disableAllFieldsExceptStudent();
//       idTarifSelect.value = '';
//       Array.from(idTarifSelect.options).forEach(option => {
//         option.style.display = '';
//       });
//       updateMontants();
//     }
//   });

//   idTarifSelect.addEventListener('change', function() {
//     updateMontants();
//   });

//   idPeriodeSelect.addEventListener('change', function() {
//     updateMontants();
//   });

//   // Fonctions utilitaires
//   function disableAllFieldsExceptStudent() {
//     allFormInputs.forEach(input => {
//       if (input.id !== 'id_eleve') {
//         input.disabled = true;
//       }
//     });
//   }

//   function enableAllFields() {
//     allFormInputs.forEach(input => {
//       input.disabled = false;
//     });
//   }

//   // Initialisation au chargement
//   disableAllFieldsExceptStudent();
//   updateMontants();
// });

document.addEventListener('DOMContentLoaded', function () {
  // Éléments du formulaire
  const form = document.querySelector('#paiementModal form');
  const idEleveSelect = document.getElementById('id_eleve');
  const idTarifSelect = document.getElementById('id_tarif');
  const idPeriodeSelect = document.getElementById('id_periode');
  const montantBaseInput = document.getElementById('montant_base');
  const reductionAppliqueeInput = document.getElementById('reduction_appliquee');
  const montantFinalInput = document.getElementById('montant_final');
  const allFormInputs = form.querySelectorAll('input, select, textarea');
  
  // Éléments des sélecteurs cascadants
  const niveauSelect = document.getElementById('niveau_scolaire_eleve');
  const filiereSelect = document.getElementById('filiere_eleve');
  const classeSelect = document.getElementById('classe_eleve');

  // Fonction pour filtrer les tarifs en fonction du niveau et de la filière
  function filterTarifs(eleveNiveau, eleveFiliere) {
    console.log('Données reçues pour le filtrage:', {
      eleveNiveau: eleveNiveau,
      eleveFiliere: eleveFiliere,
      typeEleveNiveau: typeof eleveNiveau,
      typeEleveFiliere: typeof eleveFiliere
    });

    // Activer le select des tarifs
    idTarifSelect.disabled = false;

    // Filtrer les options des tarifs
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

  // Fonction pour calculer les montants
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

  // Réinitialiser le formulaire à l'ouverture du modal
  const modalElement = document.getElementById('paiementModal');
  modalElement.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    if (!trigger.hasAttribute('data-id')) {
      form.reset();
      disableAllFieldsExceptNiveau();
    }
  });

  // Activer les champs après sélection d'un élève
  idEleveSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (this.value) {
      // Activer le champ tarif et les autres champs
      idTarifSelect.disabled = false;
      idPeriodeSelect.disabled = false;
      
      // Activer les champs restants
      enableRemainingFields();
      
      // Récupérer les attributs data-niveau et data-filiere de l'élève
      const niveau = selectedOption.getAttribute('data-niveau');
      const filiere = selectedOption.getAttribute('data-filiere');
      
      // Filtrer les tarifs en fonction du niveau et de la filière
      filterTarifs(niveau, filiere);
    } else {
      // Désactiver tous les champs
      disableFieldsAfterEleve();
      
      // Réinitialiser les valeurs
      idTarifSelect.value = '';
      idPeriodeSelect.value = '';
      montantBaseInput.value = '';
      reductionAppliqueeInput.value = '';
      montantFinalInput.value = '';
    }
  });

  // Mettre à jour les montants à la sélection d'un tarif
  idTarifSelect.addEventListener('change', function() {
    if (this.value) {
      idPeriodeSelect.disabled = false;
    } else {
      idPeriodeSelect.disabled = true;
      idPeriodeSelect.value = '';
    }
    updateMontants();
  });

  // Mettre à jour les montants à la sélection d'une période
  idPeriodeSelect.addEventListener('change', function() {
    updateMontants();
    if (this.value) {
      // Activer les champs date et autres
      enableRemainingFields();
    }
  });

  // Fonctions utilitaires
  function disableAllFieldsExceptNiveau() {
    allFormInputs.forEach(input => {
      if (input.id !== 'niveau_scolaire_eleve') {
        input.disabled = true;
      }
    });
  }

  function enableRemainingFields() {
    allFormInputs.forEach(input => {
      // Ne pas activer les champs de cascade qui seront gérés par leur propre logique
      if (!['niveau_scolaire_eleve', 'filiere_eleve', 'classe_eleve'].includes(input.id)) {
        input.disabled = false;
      }
    });
  }

  function disableFieldsAfterEleve() {
    allFormInputs.forEach(input => {
      if (!['niveau_scolaire_eleve', 'filiere_eleve', 'classe_eleve', 'id_eleve'].includes(input.id)) {
        input.disabled = true;
      }
    });
  }

  // Initialisation au chargement
  disableAllFieldsExceptNiveau();
});