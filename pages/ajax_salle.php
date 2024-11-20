<?php
// config.php - À placer dans un fichier séparé
class Database {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    "mysql:host=localhost;dbname=pfe1;charset=utf8",
                    "root",
                    "",
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}

// rooms.php - Logique de traitement des requêtes AJAX
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json');
    
    try {
        // Validation des dates
        if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
            throw new Exception("Les dates sont requises");
        }

        // Nettoyage et validation des dates
        $start_date = filter_var($_POST['start_date'], FILTER_SANITIZE_STRING);
        $end_date = filter_var($_POST['end_date'], FILTER_SANITIZE_STRING);

        if (!$start_date || !$end_date) {
            throw new Exception("Format de date invalide");
        }

        // Conversion des dates
        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        $dbh = Database::getInstance();
        
        $sql = "SELECT 
                    id_salle,
                    nom_salle,
                    etage,
                    capacite_salle,
                    nbr_chaise,
                    nbr_bureau,
                    nbr_tableau,
                    equipements,
                    date_creation
                FROM salle 
                WHERE date_creation BETWEEN :start_date AND :end_date
                ORDER BY date_creation DESC";

        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode([
            'status' => 'success',
            'data' => $results,
            'count' => count($results)
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Salles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        .loading {
            opacity: 0.5;
            pointer-events: none;
        }
        .error-message {
            color: red;
            margin: 10px 0;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Gestion des Salles</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label for="daterange" class="form-label">Période de création :</label>
                        <input type="text" id="daterange" name="daterange" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <div id="error-container" class="error-message"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Nom de la Salle</th>
                                <th>Étage</th>
                                <th>Capacité</th>
                                <th>Chaises</th>
                                <th>Bureaux</th>
                                <th>Tableaux</th>
                                <th>Équipements</th>
                                <th>Date création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="9" class="text-center">Sélectionnez une période pour afficher les salles</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    
    <script>
        $(function() {
            // Configuration du DateRangePicker
            $('#daterange').daterangepicker({
                opens: 'left',
                locale: {
                    format: 'DD/MM/YYYY',
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
            });

            // Fonction pour afficher les erreurs
            function showError(message) {
                $('#error-container').html(message).show();
                setTimeout(() => $('#error-container').fadeOut(), 5000);
            }

            // Fonction pour formatter une date
            function formatDate(dateString) {
                return moment(dateString).format('DD/MM/YYYY');
            }

            // Gestionnaire d'événement pour la sélection de dates
            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                const tableBody = $('#tableBody');
                tableBody.closest('.card').addClass('loading');

                // Conversion des dates au format attendu par le serveur
                const startDate = picker.startDate.format('MM/DD/YYYY');
                const endDate = picker.endDate.format('MM/DD/YYYY');

                $.ajax({
                    url: window.location.href,
                    method: 'POST',
                    data: { start_date: startDate, end_date: endDate },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            tableBody.empty();
                            
                            if (response.count === 0) {
                                tableBody.html('<tr><td colspan="9" class="text-center">Aucune salle trouvée pour cette période</td></tr>');
                                return;
                            }

                            response.data.forEach(room => {
                                tableBody.append(`
                                    <tr>
                                        <td>${room.nom_salle}</td>
                                        <td>${room.etage}</td>
                                        <td>${room.capacite_salle}</td>
                                        <td>${room.nbr_chaise}</td>
                                        <td>${room.nbr_bureau}</td>
                                        <td>${room.nbr_tableau}</td>
                                        <td>${room.equipements || '-'}</td>
                                        <td>${formatDate(room.date_creation)}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary me-1" onclick="editRoom(${room.id})">
                                                <i class="bi bi-pencil"></i> Modifier
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteRoom(${room.id})">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </button>
                                        </td>
                                    </tr>
                                `);
                            });
                        } else {
                            showError(response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Une erreur est survenue';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                        } catch(e) {}
                        showError(errorMessage);
                    },
                    complete: function() {
                        tableBody.closest('.card').removeClass('loading');
                    }
                });
            });

            // Fonctions pour gérer les actions (à implémenter selon vos besoins)
            window.editRoom = function(id) {
                // Implémentez la logique de modification
                console.log('Édition de la salle:', id);
            };

            window.deleteRoom = function(id) {
                if (confirm('Êtes-vous sûr de vouloir supprimer cette salle ?')) {
                    // Implémentez la logique de suppression
                    console.log('Suppression de la salle:', id);
                }
            };
        });
    </script>
</body>
</html>