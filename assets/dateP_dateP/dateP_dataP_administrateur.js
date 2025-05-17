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
          // Parcourir et ajouter chaque administrateur
          response.data.forEach(function(admin) {
            const statusBadge = admin.status == 0 
                  ? `<span class="badge badge-sm bg-gradient-secondary">Inactif</span>` 
                  : `<span class="badge badge-sm bg-gradient-success">Actif</span>`;
            
            tableBody.append(`
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="../../uploads/admin/${admin.admin_image}" class="avatar avatar-sm me-3" alt="user1">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">${admin.nom_admin} ${admin.prenom_admin}</h6>
                            <p class="text-xs text-secondary mb-0">${admin.email_admin}</p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">${admin.user_name_admin}</p>
                        <p class="text-xs text-secondary mb-0">${admin.service}</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        ${statusBadge}
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${admin.telephone}</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${admin.date_creation}</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${admin.dernier_login}</span>
                      </td>
                      <td class="align-middle text-center">
                        <div class="d-flex justify-content-center">
                          <a href="edit_administrateur.php?id=${admin.id_admin}" class="dropdown-item">
                            <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                          </a>
                          <a href="administrateur.php?id=${admin.id_admin}&del=1" class="dropdown-item" onClick="return confirmDelete(event, this)">
                            <i class="fas fa-trash fa-sm text-danger opacity-8" id="${admin.id_admin}"></i>
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
                        <td colspan="7" class="text-center">Aucun administrateur trouvé pour cette période</td>
                    </tr>
                `);
        }
      },
      error: function(xhr) {
        // Gestion des erreurs
        console.error('Erreur de requête:', xhr);
        tableBody.html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            Erreur lors de la récupération des données
                        </td>
                    </tr>
                `);
      }
    });
  });
});