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
          // Parcourir et ajouter chaque salle
          response.data.forEach(function(salle) {
             const equipementsArray = salle.equipements.split("-"); // Séparer les équipements par le séparateur "-"
  const equipementsHTML = equipementsArray.map(equi => `<p class="text-xs font-weight-bold mb-0">- ${equi}</p>`).join("");
            tableBody.append(`
                            <tr>
                              <td>
                                <div class="d-flex px-2 py-1">
                                  <div>
                                    <img src="https://elaraki.ac.ma/images/logo2.png" class="avatar avatar-sm me-3" alt="user1">
                                  </div>
                                  <div class="d-flex flex-column justify-content-center text-sm">
                                    ${salle.nom_salle}
                                  </div>
                                </div>
                              </td>
                                <td class="align-middle text-center text-sm">
                                  <p class="text-xs font-weight-bold mb-0">${salle.etage}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                  <p class="text-xs font-weight-bold mb-0">${salle.capacite_salle}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                  <p class="text-xs font-weight-bold mb-0">${salle.nbr_chaise}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                  <p class="text-xs font-weight-bold mb-0">${salle.nbr_bureau}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                  <p class="text-xs font-weight-bold mb-0">${salle.nbr_tableau}</p>
                                </td>
                                <td class="align-middle text-center">
                                  ${equipementsHTML}
                                </td>
                                <td class="align-middle text-center">
                          <div class="d-flex">
                            <a href="edit_salle.php?id_salle=${salle.id_salle}" class="dropdown-item">
                              <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                            </a>
                            <a href="description_salle.php?id=${salle.id_salle}" class="dropdown-item">
                              <i class="fas fa-eye text-primary opacity-8 fa-sm"></i>
                            </a>
                            <a href="salle.php?id=${salle.id_salle}&del=1" class="dropdown-item" onClick="return confirm('Etes-vous sûr que vous voulez supprimer?')">
                              <i class="fas fa-trash fa-sm text-danger opacity-8" id="${salle.id_salle}"></i>
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
                            <td colspan="9" class="text-center">Aucune salle trouvée pour cette période</td>
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