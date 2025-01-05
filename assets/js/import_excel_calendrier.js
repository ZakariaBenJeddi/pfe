document.getElementById('import-btn').addEventListener('click', handleImportClick);

        function handleImportClick() {
            // Créez un élément input de type "file" pour permettre à l'utilisateur de sélectionner un fichier Excel
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.xlsx, .xls'; // Autorise uniquement les fichiers Excel
            fileInput.addEventListener('change', handleFileSelect);
            fileInput.click(); // Ouvre le sélecteur de fichiers
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];

            if (!file) {
                alert("Aucun fichier sélectionné.");
                return;
            }

            if (file.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' &&
                file.type !== 'application/vnd.ms-excel') {
                alert("Veuillez sélectionner un fichier Excel valide.");
                return;
            }

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

                console.log(jsonData);

                fetch('insert_schedule.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(jsonData), // Les données à envoyer
                    })
                    .then(response => response.json()) // Attendre la réponse JSON du serveur
                    .then(responseData => {
                        // Afficher la réponse dans la console pour le débogage
                        console.log('Réponse du serveur:', responseData);

                        if (responseData.status === 'success') {
                            alert(responseData.message);
                        } else {
                            alert(responseData.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors de l\'importation :', error);
                        alert('Une erreur est survenue lors de l\'importation.');
                    });

            };

            reader.readAsBinaryString(file);
        }