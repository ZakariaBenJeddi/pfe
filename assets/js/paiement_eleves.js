document.addEventListener('DOMContentLoaded', function () {
  const editLinks = document.querySelectorAll('[data-bs-target="#paiementModal"]');

  editLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      // Récupération des attributs data-*
      document.getElementById('id_paiement').value = this.getAttribute('data-id') || '';
      document.getElementById('id_eleve').value = this.getAttribute('data-id-eleve') || '';
      document.getElementById('id_tarif').value = this.getAttribute('data-id-tarif') || '';
      document.getElementById('id_periode').value = this.getAttribute('data-id-periode') || '';
      document.getElementById('montant_base').value = this.getAttribute('data-montant-base') || '';
      document.getElementById('reduction_appliquee').value = this.getAttribute('data-reduction-appliquee') || '';
      document.getElementById('montant_final').value = this.getAttribute('data-montant-final') || '';
      document.getElementById('date_paiement').value = this.getAttribute('data-date-paiement') || '';
      document.getElementById('mode_paiement').value = this.getAttribute('data-mode-paiement') || '';
      document.getElementById('reference_paiement').value = this.getAttribute('data-reference-paiement') || '';
      document.getElementById('statut_paiement').value = this.getAttribute('data-statut-paiement') || '';
      document.getElementById('commentaire').value = this.getAttribute('data-commentaire') || '';
    });
  });

  const idTarifSelect = document.getElementById("id_tarif");
  const montantBaseInput = document.getElementById("montant_base");

  const idPeriodeSelect = document.getElementById("id_periode");
  const reductionAppliqueeInput = document.getElementById("reduction_appliquee");
  const montantFinalInput = document.getElementById("montant_final");

  // Mettre à jour le montant de base lorsque le tarif change
  idTarifSelect.addEventListener("change", function () {
      const selectedOption = this.options[this.selectedIndex];
      const montantBase = selectedOption.getAttribute("data-montant") || 0;
      montantBaseInput.value = parseFloat(montantBase).toFixed(2);

      // Recalculer le montant final
      updateMontantFinal();
  });

  // Mettre à jour la réduction appliquée lorsque la période change
  idPeriodeSelect.addEventListener("change", function () {
      const selectedOption = this.options[this.selectedIndex];
      const reduction = selectedOption.getAttribute("data-reduction") || 0;
      reductionAppliqueeInput.value = parseFloat(reduction).toFixed(2);

      // Recalculer le montant final
      updateMontantFinal();
  });

  // Fonction pour recalculer le montant final
  function updateMontantFinal() {
      const montantBase = parseFloat(montantBaseInput.value) || 0;
      const reduction = parseFloat(reductionAppliqueeInput.value) || 0;

      const montantFinal = montantBase - (montantBase * (reduction / 100));
      montantFinalInput.value = montantFinal.toFixed(2);
  }
});