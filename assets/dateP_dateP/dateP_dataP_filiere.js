$(function() {
  // Configuration du DateRangePicker
  $('#daterange').daterangepicker({
    opens: 'left',
    autoUpdateInput: true,
    locale: {
      format: 'MM/DD/YYYY', // Format attendu par votre code PHP
      applyLabel: 'Valider',
      cancelLabel: 'Annuler',
      fromLabel: 'Du',
      toLabel: 'Au',
      customRangeLabel: 'Période personnalisée',
      daysOfWeek: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
      monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
      firstDay: 1
    },
    startDate: moment().subtract(29, 'days'),
    endDate: moment()
  }, function(start, end, label) {
    // Callback pour la sélection de dates
    const tableBody = $('#tableBody');

    $.ajax({
      url: '', // Fichier actuel
      method: 'POST',
      data: {
        start_date: start.format('MM/DD/YYYY'),
        end_date: end.format('MM/DD/YYYY')
      },
      dataType: 'json',
      success: function(response) {
        // Vider le tableau
        tableBody.empty();

        // Vérifier s'il y a des résultats
        if (response.status === 'success' && response.count > 0) {
          // Parcourir et ajouter chaque filiere
          response.data.forEach(function(filiere) {
            tableBody.append(`
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="../../assets/img/small-logos/logo-invision.svg" class="avatar avatar-sm me-3" alt="filiere">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">${filiere.nom_filiere}</h6>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-xs font-weight-bold mb-0">${filiere.code_filiere}</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">${filiere.nom_niveau}</p>
                      </td>
                      <td class="align-middle text-center">
                        <p class="text-xs font-weight-bold mb-0">${filiere.nombre_heures_max}</p>
                      </td>
                      <td class="align-middle text-center">
                        <p class="text-xs font-weight-bold mb-0">
                          ${filiere.date_creation}
                        </p>
                      </td>
                      <td class="align-middle text-center">
                        <div class="d-flex">
                          <a href="edit_filiere.php?id=${filiere.id_filiere}${filiere.id_filiere}" class="dropdown-item">
                            <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                          </a>
                          <a href="description_filiere.php?id=${filiere.id_filiere}" class="dropdown-item">
                            <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                          </a>
                          <a href="filiere.php?id=${filiere.id_filiere}&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                            <i class="fas fa-trash fa-sm text-danger opacity-8"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                        `);
          });
        } else {
          // Aucun résultat
          tableBody.append(`
                        <tr>
                            <td colspan="8" class="text-center">Aucune Filiere trouvée pour cette période</td>
                        </tr>
                    `);
        }
      },
      error: function(xhr) {
        // Gestion des erreurs
        console.error('Erreur de requête:', xhr);
        tableBody.html(`
                    <tr>
                        <td colspan="9" class="text-center text-danger">
                            Erreur lors de la récupération des données
                        </td>
                    </tr>
                `);
      }
    });
  });
});