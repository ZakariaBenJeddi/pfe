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
});