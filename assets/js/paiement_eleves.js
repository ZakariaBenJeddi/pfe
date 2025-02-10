document.addEventListener('DOMContentLoaded', function () {
  // Récupérer tous les éléments du formulaire
  const form = document.querySelector('#paiementModal form');
  const idEleveSelect = document.getElementById('id_eleve');
  const idTarifSelect = document.getElementById('id_tarif');
  const allFormInputs = form.querySelectorAll('input, select, textarea');
  
  // Gestion des événements d'édition
  const editLinks = document.querySelectorAll('[data-bs-target="#paiementModal"][data-id]');
  editLinks.forEach(link => {
    link.addEventListener('click', function() {
      console.log("Attributs dataset :", this.dataset);
      // Activer tous les champs pour l'édition
      enableAllFields();
      
      // Remplir les champs avec les données
      document.getElementById('id_paiement').value = this.getAttribute('data-id');
      document.getElementById('id_eleve').value = this.getAttribute('data-id-eleve');
      document.getElementById('id_tarif').value = this.getAttribute('data-id-tarif');
      document.getElementById('id_periode').value = this.getAttribute('data-id-periode');
      document.getElementById('montant_base').value = this.getAttribute('data-montant-base');
      document.getElementById('reduction_appliquee').value = this.getAttribute('data-reduction-appliquee');
      document.getElementById('montant_final').value = this.getAttribute('data-montant-final');
      document.getElementById('date_paiement').value = this.getAttribute('data-date-paiement');
      document.getElementById('date_debut_periode').value = this.getAttribute('data-date-debut-periode');
      document.getElementById('mode_paiement').value = this.getAttribute('data-mode-paiement');
      document.getElementById('reference_paiement').value = this.getAttribute('data-reference-paiement');
      document.getElementById('statut_paiement').value = this.getAttribute('data-statut-paiement');
      document.getElementById('commentaire').value = this.getAttribute('data-commentaire');
      
      // Déclencher l'événement change sur l'élève pour mettre à jour les tarifs
      const event = new Event('change');
      idEleveSelect.dispatchEvent(event);
    });
  });
  
  // Fonction pour désactiver tous les champs sauf la sélection de l'élève
  function disableAllFieldsExceptStudent() {
    allFormInputs.forEach(input => {
      if (input.id !== 'id_eleve') {
        input.disabled = true;
      }
    });
  }
  
  // Fonction pour activer tous les champs
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
  }
  
  // Désactiver les champs au chargement initial (nouveau paiement)
  const modalElement = document.getElementById('paiementModal');
  modalElement.addEventListener('show.bs.modal', function (event) {
    // Si c'est un nouveau paiement (pas de data-id)
    if (!event.relatedTarget.getAttribute('data-id')) {
      disableAllFieldsExceptStudent();
      form.reset();
    }
  });
  
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
  
  // Gestion des montants
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