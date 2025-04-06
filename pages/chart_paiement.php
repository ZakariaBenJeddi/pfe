<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "pfe1");

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Requêtes pour les métriques principales
$queryTotalValide = "SELECT SUM(montant_final) as total FROM paiements_eleves WHERE statut_paiement = 'Validé'";
$queryEnAttente = "SELECT COUNT(*) as nombre, SUM(montant_final) as montant FROM paiements_eleves WHERE statut_paiement = 'En attente'";
$queryMoyenneEleve = "SELECT AVG(total) as moyenne FROM (SELECT id_eleve, SUM(montant_final) as total FROM paiements_eleves GROUP BY id_eleve) as totaux_eleves";
$queryMoisActuel = "SELECT SUM(montant_final) as total FROM paiements_eleves WHERE MONTH(date_paiement) = MONTH(CURRENT_DATE()) AND YEAR(date_paiement) = YEAR(CURRENT_DATE()) AND statut_paiement = 'Validé'";

// Exécution des requêtes
$resultTotal = $conn->query($queryTotalValide);
$totalValide = $resultTotal->fetch_assoc()['total'];

$resultEnAttente = $conn->query($queryEnAttente);
$enAttente = $resultEnAttente->fetch_assoc();

$resultMoyenne = $conn->query($queryMoyenneEleve);
$moyenneEleve = $resultMoyenne->fetch_assoc()['moyenne'];

$resultMoisActuel = $conn->query($queryMoisActuel);
$totalMois = $resultMoisActuel->fetch_assoc()['total'];

// Requête pour données du graphique par mois
$queryGraphMois = "SELECT 
                    YEAR(date_paiement) as annee, 
                    MONTH(date_paiement) as mois, 
                    SUM(montant_final) as total 
                  FROM paiements_eleves 
                  WHERE statut_paiement = 'Validé' 
                  GROUP BY YEAR(date_paiement), MONTH(date_paiement) 
                  ORDER BY annee, mois";

$resultGraphMois = $conn->query($queryGraphMois);
$mois = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
$dataGraphMois = array_fill_keys($mois, 0); // Initialiser avec tous les mois à 0

while ($row = $resultGraphMois->fetch_assoc()) {
    $moisKey = date("M", mktime(0, 0, 0, $row['mois'], 1, $row['annee']));
    if (isset($dataGraphMois[$moisKey])) {
        $dataGraphMois[$moisKey] = $row['total'];
    }
}

// Requête pour répartition des modes de paiement
$queryModes = "SELECT 
                mode_paiement, 
                COUNT(*) as nombre, 
                SUM(montant_final) as total 
              FROM paiements_eleves 
              GROUP BY mode_paiement";

$resultModes = $conn->query($queryModes);
$dataModes = [];
while ($row = $resultModes->fetch_assoc()) {
    $dataModes[] = [
        'mode' => $row['mode_paiement'],
        'nombre' => $row['nombre'],
        'total' => $row['total']
    ];
}

// Requête pour les 4 élèves ayant payé le plus
$queryTopEleves = "SELECT 
                    e.id_eleve,
                    e.nom,
                    e.prenom,
                    SUM(p.montant_final) as total_paye,
                    COUNT(p.id_paiement) as nb_paiements
                FROM paiements_eleves p
                JOIN eleves e ON p.id_eleve = e.id_eleve
                WHERE p.statut_paiement = 'Validé'
                GROUP BY e.id_eleve, e.nom, e.prenom
                ORDER BY total_paye DESC
                LIMIT 4";

$resultTopEleves = $conn->query($queryTopEleves);
$topEleves = [];
while ($row = $resultTopEleves->fetch_assoc()) {
    $topEleves[] = $row;
}

// Fermer la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Paiements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Tableau de bord des paiements</h2>
        
        <!-- Cartes des métriques principales -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total des paiements validés</h5>
                        <h2><?= number_format($totalValide, 2) ?> €</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">En attente</h5>
                        <h2><?= $enAttente['nombre'] ?> (<?= number_format($enAttente['montant'], 2) ?> €)</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Moyenne par élève</h5>
                        <h2><?= number_format($moyenneEleve, 2) ?> €</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total du mois</h5>
                        <h2><?= number_format($totalMois, 2) ?> €</h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Graphiques -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Évolution des paiements par mois
                    </div>
                    <div class="card-body">
                        <canvas id="chart-line"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Répartition par mode de paiement
                    </div>
                    <div class="card-body">
                        <canvas id="graphModes"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tableau des élèves ayant payé le plus -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Top 4 des élèves avec les paiements les plus élevés
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Nombre de paiements</th>
                                    <th>Montant total payé</th>
                                    <th>Moyenne par paiement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topEleves as $eleve): ?>
                                <tr>
                                    <td><?= $eleve['prenom'] . ' ' . $eleve['nom'] ?></td>
                                    <td><?= $eleve['nb_paiements'] ?></td>
                                    <td><?= number_format($eleve['total_paye'], 2) ?> €</td>
                                    <td><?= number_format($eleve['total_paye'] / $eleve['nb_paiements'], 2) ?> €</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/chartjs.min.js"></script>
    <script>
        // Graphique d'évolution des paiements par mois avec le style demandé
        var ctx1 = document.getElementById("chart-line").getContext("2d");
        var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

        gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
        gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
        gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');
        
        new Chart(ctx1, {
            type: "line",
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [{
                    label: "Paiements mensuels",
                    tension: 0.4,
                    borderWidth: 0,
                    pointRadius: 0,
                    borderColor: "#5e72e4",
                    backgroundColor: gradientStroke1,
                    borderWidth: 3,
                    fill: true,
                    data: [
                        <?= $dataGraphMois["Jan"] ?>,
                        <?= $dataGraphMois["Feb"] ?>,
                        <?= $dataGraphMois["Mar"] ?>,
                        <?= $dataGraphMois["Apr"] ?>,
                        <?= $dataGraphMois["May"] ?>,
                        <?= $dataGraphMois["Jun"] ?>,
                        <?= $dataGraphMois["Jul"] ?>,
                        <?= $dataGraphMois["Aug"] ?>,
                        <?= $dataGraphMois["Sep"] ?>,
                        <?= $dataGraphMois["Oct"] ?>,
                        <?= $dataGraphMois["Nov"] ?>,
                        <?= $dataGraphMois["Dec"] ?>
                    ],
                    maxBarThickness: 6
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#fbfbfb',
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#ccc',
                            padding: 20,
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });

        // Graphique de répartition des modes de paiement
        const ctxModes = document.getElementById('graphModes').getContext('2d');
        new Chart(ctxModes, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($dataModes, 'mode')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($dataModes, 'total')) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    </script>
</body>
</html>