// Gestionnaire d'événement pour le bouton d'importation
document.getElementById('import-btn').addEventListener('click', handleImportClick);

function handleImportClick() {
    // Créer un élément input de type "file" pour permettre à l'utilisateur de sélectionner un fichier Excel
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.xlsx, .xls'; // Autorise uniquement les fichiers Excel
    fileInput.addEventListener('change', handleFileSelect);
    fileInput.click(); // Ouvre le sélecteur de fichiers
}

/**
 * Gère la sélection d'un fichier Excel
 * @param {Event} event - L'événement de sélection de fichier
 */
function handleFileSelect(event) {
    const file = event.target.files[0];

    if (!file) {
        showNotification("Aucun fichier sélectionné.", "error");
        return;
    }

    if (file.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' &&
        file.type !== 'application/vnd.ms-excel') {
        showNotification("Veuillez sélectionner un fichier Excel valide.", "error");
        return;
    }

    // Afficher un indicateur de chargement
    showNotification("Traitement du fichier en cours...", "info");
    
    const reader = new FileReader();
    reader.onload = function(event) {
        const data = event.target.result;
        const workbook = XLSX.read(data, {
            type: 'binary'
        });

        const worksheet = workbook.Sheets[workbook.SheetNames[0]];
        let jsonData = XLSX.utils.sheet_to_json(worksheet, {
            header: 1
        });

        // Filtrer les lignes vides
        jsonData = jsonData.filter(row => row.length > 0);

        // Vérifier si le fichier contient des données
        if (jsonData.length <= 1) { // Seulement les en-têtes
            showNotification("Le fichier Excel est vide ou ne contient que des en-têtes.", "error");
            return;
        }

        // Vérifier les en-têtes attendus
        const expectedHeaders = ['Description', 'Professeur', 'Matière', 'Classe', 'Salle', 'Début', 'Fin'];
        const headers = jsonData[0];
        
        // Vérifier si tous les en-têtes attendus sont présents
        const missingHeaders = expectedHeaders.filter(header => !headers.includes(header));
        if (missingHeaders.length > 0) {
            showNotification(`En-têtes manquants: ${missingHeaders.join(', ')}`, "error");
            return;
        }

        // Envoyer les données au serveur
        fetch('insert_schedule.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(jsonData),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(responseData => {
            console.log('Réponse du serveur:', responseData);

            if (responseData.status === 'success') {
                showNotification(responseData.message, "success");
                // Recharger la page pour afficher les nouvelles données
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else if (responseData.status === 'warning') {
                // En cas d'avertissement (certaines lignes importées avec succès, d'autres non)
                if (responseData.errors && responseData.errors.length > 0) {
                    const errorDetails = responseData.errors.join('<br>');
                    showNotification(`${responseData.message}<br>${errorDetails}`, "warning");
                } else {
                    showNotification(responseData.message, "warning");
                }
                // Recharger la page après un délai plus long pour laisser le temps de lire les avertissements
                setTimeout(() => {
                    location.reload();
                }, 5000);
            } else {
                // Afficher les erreurs détaillées si disponibles
                if (responseData.errors && responseData.errors.length > 0) {
                    const errorDetails = responseData.errors.join('<br>');
                    showNotification(`${responseData.message}<br>${errorDetails}`, "error");
                } else {
                    showNotification(responseData.message, "error");
                }
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'importation :', error);
            // Vérifier si c'est une erreur réseau ou une erreur de parsing JSON
            showNotification('Une erreur est survenue lors de l\'importation. Les données pourraient avoir été partiellement importées. Veuillez rafraîchir la page pour vérifier.', "error");
            
            // Ajouter un bouton pour rafraîchir la page
            setTimeout(() => {
                if (confirm('Voulez-vous rafraîchir la page pour vérifier si l\'importation a réussi?')) {
                    location.reload();
                }
            }, 1000);
        });
    };

    reader.readAsBinaryString(file);
}

/**
 * Affiche une notification à l'utilisateur
 * @param {string} message - Le message à afficher
 * @param {string} type - Le type de notification (success, error, info, warning)
 */
function showNotification(message, type) {
    // Vérifier si vous avez déjà une fonction de notification
    if (typeof Swal !== 'undefined') {
        // Utiliser SweetAlert2 si disponible
        Swal.fire({
            title: type === 'error' ? 'Erreur' : type === 'success' ? 'Succès' : 'Information',
            html: message,
            icon: type,
            timer: type === 'error' ? 0 : 3000,
            timerProgressBar: true
        });
    } else {
        // Sinon, utiliser alert
        alert(message);
    }
}