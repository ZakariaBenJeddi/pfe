$(function() {
  // Configuration du DateRangePicker
  $('#daterange').daterangepicker({
    opens: 'left',
    autoUpdateInput: true,
    locale: {
      format: 'YYYY-MM-DD',
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
    const tableBody = $('#absencesTableBody');
    
    // Afficher les dates sélectionnées dans la console pour debug
    console.log("Dates sélectionnées:", picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
    
    $.ajax({
      url: window.location.href,
      method: 'POST',
      data: {
        start_date: picker.startDate.format('YYYY-MM-DD'),
        end_date: picker.endDate.format('YYYY-MM-DD')
      },
      dataType: 'json',
      success: function(response) {
        // Afficher la réponse dans la console pour debug
        console.log("Réponse du serveur:", response);
        
        tableBody.empty();

        // Vérifier si la réponse est un succès et contient des données
        if (response.success && response.data && response.data.length > 0) {
          console.log("Nombre d'absences trouvées:", response.data.length);
          
          response.data.forEach(function(absence) {
            console.log("Traitement d'une absence:", absence);
            
            let statutHTML = '';
            
            if (absence.statut === 'validee') {
              statutHTML = `<td class="align-middle text-center text-sm">
                <span class="badge badge-sm bg-gradient-success">Validee</span>
              </td>`;
            } else if (absence.statut === 'en_attente') {
              statutHTML = `<td class="align-middle text-center text-sm">
                <span class="badge badge-sm bg-gradient-secondary">En Attente</span>
              </td>`;
            } else if (absence.statut === 'annulee') {
              statutHTML = `<td class="align-middle text-center text-sm">
                <span class="badge badge-sm bg-gradient-secondary">Annulee</span>
              </td>`;
            }
            const imageSrc = absence.genre === 'Masculin'
              ? '../../assets/img/team-4.jpg'
              : (absence.genre === 'Féminin'
                ? '../../assets/img/marie.jpg'
                : '../../assets/img/marie.jpg');
            
            tableBody.append(`
              <tr>
                <td>
                  <div class="d-flex px-2 py-1">
                    <div>
                      <img src="${imageSrc}" class="avatar avatar-sm me-3" alt="eleve">
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                      <h6 class="mb-0 text-sm">${absence.nom_eleve || ''} ${absence.prenom_eleve || ''}</h6>
                      <p class="text-xs text-secondary mb-0">${absence.telephone_tuteur || ''}</p>
                    </div>
                  </div>
                </td>
                <td class="align-middle text-center text-sm">
                  <p class="text-xs font-weight-bold mb-0">${absence.niveau_scolaire ? absence.niveau_scolaire : 'Aucun Niveau'}</p>
                </td>
                <td>
                  <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">${absence.nom_classe ? absence.nom_classe : 'Aucun Classe'}</p>
                </td>
                <td class="align-middle text-center">
                  <p class="text-xs font-weight-bold mb-0 ms-lg-5 ms-5">${(absence.nom_enseignant && absence.prenom_enseignant) ? absence.nom_enseignant + " " + absence.prenom_enseignant : 'Aucun Enseignant'}</p>
                </td>
                <td class="align-middle text-center">
                  <p class="text-xs font-weight-bold mb-0">${absence.date_absence || ''}</p>
                </td>
                <td class="align-middle text-center">
                  <p class="text-xs font-weight-bold mb-0">${absence.heure_debut || ''}</p>
                </td>
                <td class="align-middle text-center">
                  <p class="text-xs font-weight-bold mb-0">${absence.heure_fin || ''}</p>
                </td>
                <td class="align-middle text-center">
                  <p class="text-xs font-weight-bold mb-0">${absence.motif || ''}</p>
                </td>
                <td class="align-middle text-center">
                  ${absence.justification
                    ? `<a href="${absence.justification}" target="_blank" class="btn btn-xs btn-info px-4">Voir</a>`
                    : `<span class="text-muted">-</span>`
                  }
                </td>
                <td class="align-middle text-center">
                  <p class="text-xs font-weight-bold mb-0">${absence.type_absence || ''}</p>
                </td>
                ${statutHTML}
                <td class="align-middle text-center">
                  <p class="text-xs font-weight-bold mb-0">${absence.date_creation || ''}</p>
                </td>
                <td class="align-middle text-center">
                  <div class="d-flex">
                    <a href="edit_absence.php?id_absence=${absence.id_absence}" class="dropdown-item">
                      <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                    </a>
                    <a href="absence.php?id=${absence.id_absence}&del=1" onClick="return confirmDelete(event, this)" class="dropdown-item">
                      <i class="fas fa-trash fa-sm text-danger opacity-8" id="${absence.id_absence}"></i>
                    </a>
                  </div>
                </td>
              </tr>
            `);
          });
        } else {
          console.log("Aucune absence trouvée ou erreur dans la réponse");
          tableBody.append(`
            <tr>
              <td colspan="12" class="text-center">Aucune absence trouvée pour cette période</td>
            </tr>
          `);
        }
      },
      error: function(xhr, status, error) {
        console.error('Erreur AJAX:', xhr.responseText, status, error);
        tableBody.html(`
          <tr>
            <td colspan="12" class="text-center text-danger">
              Erreur lors de la récupération des données: ${error}
            </td>
          </tr>
        `);
      }
    });
  });
});