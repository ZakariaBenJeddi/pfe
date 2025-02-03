    document.addEventListener('DOMContentLoaded', function() {
      var editLinks = document.querySelectorAll('[data-bs-target="#exampleModal"]');

      editLinks.forEach(function(link) {
        link.addEventListener('click', function() {
          // Get the data attributes
          var id = this.getAttribute('data-id');
          var nom = this.getAttribute('data-nom');
          var mois = this.getAttribute('data-mois');
          var reduction = this.getAttribute('data-reduction');
          var description = this.getAttribute('data-description');
          var actif = this.getAttribute('data-actif');

          // Populate the modal form
          document.getElementById('id_periode').value = id;
          document.getElementById('nom_periode').value = nom;
          document.getElementById('nombre_mois').value = mois;
          document.getElementById('pourcentage_reduction').value = reduction;
          document.getElementById('description').value = description;
          document.getElementById('est_actif').value = actif;
        });
      });
    });