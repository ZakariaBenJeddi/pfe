// JavaScript pour types_frais.js
document.addEventListener('DOMContentLoaded', function() {
  var editLinks = document.querySelectorAll('[data-bs-target="#exampleModal"]');

  editLinks.forEach(function(link) {
    link.addEventListener('click', function() {
      // Récupérer les attributs de données
      var id = this.getAttribute('data-id');
      var nom = this.getAttribute('data-nom');
      var description = this.getAttribute('data-description');
      var obligatoire = this.getAttribute('data-obligatoire');
      var mensuel = this.getAttribute('data-mensuel');
      var actif = this.getAttribute('data-actif');

      // Remplir le formulaire du modal
      document.getElementById('id_type_frais').value = id;
      document.getElementById('nom_frais').value = nom;
      document.getElementById('description').value = description;
      
      // Cocher les cases à cocher
      document.getElementById('est_obligatoire').checked = obligatoire == 1;
      document.getElementById('est_mensuel').checked = mensuel == 1;
      
      // Définir le statut
      document.getElementById('est_actif').value = actif;
    });
  });
});