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
        // Parcourir et ajouter chaque eleve
        if (response.status === 'success' && response.count > 0) {
          response.data.forEach(function(classe) {
            let statut = ''
            if (classe.statut === 'Active') {
              statut = '<span class="badge badge-sm bg-gradient-success">Active</span>'
            }else{
              statut = '<span class="badge badge-sm bg-gradient-success">Inactive</span>'
            }
            tableBody.append(`
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="../../assets/img/team-2.jpg" class="avatar avatar-sm me-3" alt="user1">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <p class="text-secondary text-xs font-weight-bold">${classe.nom_classe}</p>
                          </div>
                        </div>
                      </td>
                      <td  class="align-middle text-center">
                        <p class="text-secondary text-xs font-weight-bold">${classe.nom_niveau}</p>
                      </td>
                      <td>
                        <p class="text-secondary text-xs font-weight-bold">${classe.nom_filiere}</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        ${statut}
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${classe.capacite}</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${classe.date_creation}</span>
                      </td>
                      <td class="align-middle text-center">
                        <div class="d-flex">
                          <a href="edit_classe.php?id=${classe.id_classe}" class="dropdown-item">
                            <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                          </a>
                          <a href="description_classe.php?id=${classe.id_classe}" class="dropdown-item">
                            <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                          </a>
                          <a href="classes.php?id=${classe.id_classe}&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                            <i class="fas fa-trash fa-sm text-danger opacity-8" id="${classe.id_classe}"></i>
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
                            <td colspan="8" class="text-center">Aucune Classe trouvée pour cette période</td>
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