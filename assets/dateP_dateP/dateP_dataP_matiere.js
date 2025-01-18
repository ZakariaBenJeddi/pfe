$(function() {
  // Configuration du DateRangePicker
  $('#daterange').daterangepicker({
    opens: 'left',
    autoUpdateInput: true,
    locale: {
      format: 'YYYY-MM-DD', // Format attendu par votre code PHP
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
        start_date: start.format('YYYY-MM-DD'),
        end_date: end.format('YYYY-MM-DD')
      },
      dataType: 'json',
      success: function(response) {
        // Vider le tableau
        tableBody.empty();

        // Vérifier s'il y a des résultats
        if (response.status === 'success' && response.count > 0) {
          // Parcourir et ajouter chaque matiere
          response.data.forEach(function(matiere) {
            tableBody.append(`
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">${matiere.nom_matiere}</h6>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-xs font-weight-bold mb-0">${matiere.code_matiere}</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-xs font-weight-bold mb-0">${matiere.nom_filiere}</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-xs font-weight-bold mb-0">${matiere.coefficient}</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">${matiere.statut}</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">${matiere.nombre_seance_semaine}</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">${matiere.nombre_heures_semaine}</p>
                      </td>
                      <td class="align-middle text-center">
                        <p class="text-xs font-weight-bold mb-0" title="${matiere.description}">
                        ${matiere.description.length > 20 ? `${matiere.description.substring(0, 20)}...` : matiere.description}
                        </p>
                      </td>
                      <td class="align-middle text-center">
                        <div class="d-flex">
                          <a href="edit_matiere.php?id=${matiere.id_matiere}" class="dropdown-item">
                            <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                          </a>
                          <a href="description_matiere.php?id=${matiere.id_matiere}" class="dropdown-item">
                            <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                          </a>
                          <a href="matiere.php?id=${matiere.id_matiere}&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                            <i class="fas fa-trash fa-sm text-danger opacity-8"></i>
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