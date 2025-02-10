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

        if (response.count > 0) {
          response.data.forEach(function(paiement) {
            let statut = ''
            if (paiement.statut_paiement === 'Validé') {
              statut = '<span class="badge badge-sm bg-gradient-success">Validé</span>'
            } else if(paiement.statut_paiement === "Annulé"){
              statut = '<span class="badge badge-sm bg-gradient-danger">Annulé</span>'
            }else {
              statut = '<span class="badge badge-sm bg-gradient-secondary">En attente</span>'
            }
            tableBody.append(`
                    <tr>
                      <td  onclick='genererPDFPaiement(${paiement.id_paiement})' style='cursor:pointer'>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <p class="text-secondary text-xs font-weight-bold">${paiement.id_paiement}</p>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle text-center">
                        <p class="text-secondary text-xs font-weight-bold">${paiement.nom_eleve} ${paiement.prenom_eleve}</p>
                      </td>
                      <td>
                        <p class="text-secondary text-xs font-weight-bold">${paiement.nom_filiere} - ${paiement.nom_niveau}</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        ${paiement.type_frais}
                      </td>
                      <td class="align-middle text-center text-sm">
                        ${paiement.nom_periode} <br>  ${paiement.nombre_mois} 
                      </td>
                      <td class="align-middle text-center text-sm">
                        ${paiement.tarif_montant_base}
                      </td>
                      <td class="align-middle text-center text-sm">
                        ${paiement.paiement_montant_base}
                      </td>
                      <td class="align-middle text-center text-sm" title='${paiement.description_periode}'>
                        ${paiement.reduction_appliquee}
                      </td>
                      <td class="align-middle text-center text-sm">
                        ${paiement.montant_final}
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${paiement.montant_final}</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${paiement.mode_paiement}</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${paiement.reference_paiement}</span>
                      </td>
                      <td class="align-middle text-center text-sm">
                        ${statut}
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${paiement.date_paiement}</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${paiement.date_debut_periode}</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">${paiement.date_validation}</span>
                      </td>
                      <td class="align-middle text-center">
                        <div class="d-flex">
                          <a href="#" class="dropdown-item" 
                            data-bs-toggle="modal"
                            data-bs-target="#paiementModal" 
                            data-id="${paiement.id_paiement}"
                            data-id-eleve="${paiement.id_eleve}"
                            data-id-tarif="${paiement.id_tarif}"
                            data-periode="${paiement.id_periode}"
                            data-montant-base="${paiement.paiement_montant_base}"
                            data-reduction-appliquee="${paiement.reduction_appliquee}"
                            data-montant-final="${paiement.montant_final}"
                            data-mode-paiement="${paiement.mode_paiement}"
                            data-reference-paiement="${paiement.reference_paiement}"
                            data-statut-paiement="${paiement.statut_paiement}"
                            data-commentaire="${paiement.commentaire}"
                          >
                            <i class="fas fa-pencil-alt text-dark opacity-8 fa-sm" aria-hidden="true"></i>
                          </a>
                          <a href="paiements_eleves.php?id=${paiement.id_paiement}&del=1" class="dropdown-item" onClick="return confirmDelete(event, this)">
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
                            <td colspan="8" class="text-center">Aucun Paiement trouvé pour cette période</td>
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
