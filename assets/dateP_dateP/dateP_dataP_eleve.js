$(function() {
  // Configuration du DateRangePicker
  $('#daterange').daterangepicker({
    opens: 'left',
    autoUpdateInput: true,
    locale: {
      format: 'YYYY-MM-DD', // Changé pour correspondre au format MySQL
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
  }).on('apply.daterangepicker', function(ev, picker) {
    // Déclenchement de la recherche lors de la sélection des dates
    const tableBody = $('#tableBody');
    
    $.ajax({
      url: window.location.href, // URL actuelle
      method: 'POST',
      data: {
        start_date: picker.startDate.format('YYYY-MM-DD'),
        end_date: picker.endDate.format('YYYY-MM-DD')
      },
      dataType: 'json',
      success: function(response) {
        tableBody.empty();

        // Vérifier si la réponse est un succès et contient des données
        if (response.success && response.data.length > 0) {
          response.data.forEach(function(eleve) {
            let badgeClass = '';
            let badgeText = '';
            
            if (eleve.statut === 'Actif') {
              badgeClass = 'bg-gradient-success';
              badgeText = 'Online';
            } else if (eleve.statut === 'Inactif') {
              badgeClass = 'bg-gradient-secondary';
              badgeText = 'Offline';
            } else if (eleve.statut === 'Retraité') {
              badgeClass = 'bg-gradient-secondary';
              badgeText = 'Retraité';
            }
            
            tableBody.append(`
              <tr>
                <td>
                  <div class="d-flex px-2 py-1">
                    <div>
                      <img src="<?= $result->genre === 'Masculin' ? '../../assets/img/team-4.jpg' : ($result->genre === 'Féminin' ? '../../assets/img/marie.jpg' : '../../assets/img/default.jpg') ?>"
                                class="avatar avatar-sm me-3" alt="eleve">
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                      <h6 class="mb-0 text-sm">${eleve.nom} ${eleve.prenom}</h6>
                      <p class="text-xs text-secondary mb-0">${eleve.email}</p>
                    </div>
                  </div>
                </td>
                <td class="align-middle text-center text-sm">
                  <p class="text-xs font-weight-bold mb-0">${eleve.niveau_scolaire}</p>
                </td>
                <td>
                  <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">${eleve.id_classe || 'N/A'}</p>
                </td>
                <td class="align-middle text-center">
                  <p class="text-xs font-weight-bold mb-0">${eleve.filiere || 'N/A'}</p>
                </td>
                <td class="align-middle text-center">
                  <p class="text-xs font-weight-bold mb-0">${eleve.telephone}</p>
                  <p class="text-xs font-weight-bold mb-0">${eleve.telephone_tuteur}</p>
                </td>
                <td class="align-middle text-center">
                  <p class="text-xs font-weight-bold mb-0">${eleve.date_inscription}</p>
                </td>
                <td class="align-middle text-center text-sm">
                  <span class="badge badge-sm ${badgeClass}">${badgeText}</span>
                </td>
                <td class="align-middle text-center">
                  <div class="d-flex">
                    <a href="edit_eleve.php?id_eleve=${eleve.id_eleve}" class="dropdown-item">
                      <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                    </a>
                    <a href="description_eleve.php?id=${eleve.id_eleve}" class="dropdown-item">
                      <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                    </a>
                    <a href="eleve.php?id=${eleve.id_eleve}&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                      <i class="fas fa-trash fa-sm text-danger opacity-8" id="${eleve.id_eleve}"></i>
                    </a>
                  </div>
                </td>
              </tr>
            `);
          });
        } else {
          tableBody.append(`
            <tr>
              <td colspan="8" class="text-center">Aucun élève trouvé pour cette période</td>
            </tr>
          `);
        }
      },
      error: function(xhr, status, error) {
        console.error('Erreur AJAX:', error);
        tableBody.html(`
          <tr>
            <td colspan="8" class="text-center text-danger">
              Erreur lors de la récupération des données: ${error}
            </td>
          </tr>
        `);
      }
    });
  });
});