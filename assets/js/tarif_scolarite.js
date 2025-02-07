document.addEventListener('DOMContentLoaded', function() {
  var editLinks = document.querySelectorAll('[data-bs-target="#exampleModal"]');

  editLinks.forEach(function(link) {
    link.addEventListener('click', function() {
      // Récupérer les données depuis l'attribut data-*
      var id = this.getAttribute('data-id');
      var typeFrais = this.getAttribute('data-type-frais');
      var niveau = this.getAttribute('data-niveau');
      var filiere = this.getAttribute('data-filiere');
      var montant = this.getAttribute('data-montant');
      var annee = this.getAttribute('data-annee');

      // Injecter les valeurs dans le formulaire du modal
      document.getElementById('id_tarif').value = id;
      document.getElementById('id_type_frais').value = typeFrais;
      document.getElementById('id_niveau').value = niveau;
      document.getElementById('id_filiere').value = filiere;
      document.getElementById('montant_base').value = montant;
      document.getElementById('annee_scolaire').value = annee;
    });
  });
});
