<?php 

//**Récupère le nombre total de salles
$query_nbr_salle = $dbh->query("SELECT COUNT(*) FROM salle ");
$nbr_salle = $query_nbr_salle->fetchColumn();
//**Récupère le nombre total de salles
$query_nbr_eleves = $dbh->query("SELECT COUNT(*) FROM eleves ");
$nbr_eleves = $query_nbr_eleves->fetchColumn();
//**Récupère le nombre total de enseignant
$query_nbr_enseignant = $dbh->query("SELECT COUNT(*) FROM enseignant ");
$nbr_enseignant = $query_nbr_enseignant->fetchColumn();
//**Récupère le nombre total d'abscence
$query_nbr_abscence = $dbh->query("SELECT COUNT(*) FROM absences ");
$nbr_abscence = $query_nbr_abscence->fetchColumn();

// Récupération des dates
$date_cette_anne = date('Y');
$date_anne_dernier = date('Y', strtotime('-1 year'));

// Fonction générique pour calculer le pourcentage de changement
function calculerPourcentageChangement($valeur_actuelle, $valeur_precedente)
{
  if ($valeur_precedente <= 0) {
    return null;
  }
  return (($valeur_actuelle - $valeur_precedente) / $valeur_precedente) * 100;
}

//* Calcul pour les élèves
$query = $dbh->prepare("SELECT COUNT(*) FROM eleves WHERE YEAR(date_inscription) = :date");
$query->bindParam(":date", $date_cette_anne);
$query->execute();
$nbr_eleves_inscrit_cette_anne = $query->fetchColumn();

$query->bindParam(":date", $date_anne_dernier);
$query->execute();
$nbr_eleves_inscrit_anne_dernier = $query->fetchColumn();

$pourcentage = calculerPourcentageChangement(
  $nbr_eleves_inscrit_cette_anne,
  $nbr_eleves_inscrit_anne_dernier
);

//* Calcul pour les enseignants
$query = $dbh->prepare("SELECT COUNT(*) FROM enseignant WHERE YEAR(date_creation) = :date");
$query->bindParam(":date", $date_cette_anne);
$query->execute();
$nbr_enseignants_inscrit_cette_anne = $query->fetchColumn();

$query->bindParam(":date", $date_anne_dernier);
$query->execute();
$nbr_enseignants_inscrit_anne_dernier = $query->fetchColumn();

$pourcentage_enseignant = calculerPourcentageChangement(
  $nbr_enseignants_inscrit_cette_anne,
  $nbr_enseignants_inscrit_anne_dernier
);

//* Calcul pour les absences
$date_aujourdhui = date('Y-m-d');
$date_hier = date('Y-m-d', strtotime('-1 day'));

$query = $dbh->prepare("SELECT COUNT(*) FROM absences WHERE date_absence = :date");
$query->bindParam(":date", $date_aujourdhui);
$query->execute();
$nbr_absences_aujourdhui = $query->fetchColumn();

$query->bindParam(":date", $date_hier);
$query->execute();
$nbr_absences_hier = $query->fetchColumn();

$pourcentage_absence = calculerPourcentageChangement(
  $nbr_absences_aujourdhui,
  $nbr_absences_hier
);

//* Affichage des résultats avec gestion des erreurs
if ($pourcentage === null) {
  setcookie("show_alert", "1", time() + 2); // Expire dans 2 secondes
}

// Requête SQL améliorée pour récupérer les absences de la semaine
$query_absc = "SELECT 
  e.genre,
  DATE_FORMAT(a.date_absence, '%a') AS jour,
  COUNT(DISTINCT a.id_absence) AS nb_absences
  FROM absences a
  JOIN eleves e ON a.id_eleve = e.id_eleve
  WHERE a.date_absence BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY) AND CURRENT_DATE
  AND a.statut = 'validee'
  GROUP BY e.genre, jour
  ORDER BY FIELD(jour, 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun')";

$stmt_absc = $dbh->prepare($query_absc);
$stmt_absc->execute();

// Initialiser le tableau avec tous les jours à 0
$jours = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$data = [
  'garçon' => array_fill_keys($jours, 0),
  'fille' => array_fill_keys($jours, 0)
];

// Remplir les données
while ($row = $stmt_absc->fetch(PDO::FETCH_ASSOC)) {
  if ($row['genre'] === 'Masculin') {
    $genre = 'garçon';
  } elseif ($row['genre'] === 'Féminin') {
    $genre = 'fille';
  } else {
    continue; // Ignorer les genres non reconnus
  }

  if (in_array($row['jour'], $jours)) {
    $data[$genre][$row['jour']] = (int)$row['nb_absences'];
  }
}

// Fonction pour traduire les jours en français
function translateDay($englishDay)
{
  $translations = [
    'Mon' => 'Lundi',
    'Tue' => 'Mardi',
    'Wed' => 'Mercredi',
    'Thu' => 'Jeudi',
    'Fri' => 'Vendredi',
    'Sat' => 'Samedi',
    'Sun' => 'Dimanche'
  ];

  return $translations[$englishDay] ?? $englishDay;
}

// Préparer les données pour le graphique ApexCharts
$chartData = [
  'garçon' => [],
  'fille' => []
];

foreach ($jours as $jour) {
  $chartData['garçon'][] = [
    'x' => translateDay($jour),
    'y' => $data['garçon'][$jour]
  ];
  $chartData['fille'][] = [
    'x' => translateDay($jour),
    'y' => $data['fille'][$jour]
  ];
}

$queryTotalValide = "SELECT SUM(montant_final) as total FROM paiements_eleves WHERE statut_paiement = 'Validé'";
$queryEnAttente = "SELECT COUNT(*) as nombre, SUM(montant_final) as montant FROM paiements_eleves WHERE statut_paiement = 'En attente'";
$queryMoyenneEleve = "SELECT AVG(total) as moyenne FROM (SELECT id_eleve, SUM(montant_final) as total FROM paiements_eleves GROUP BY id_eleve) as totaux_eleves";
$queryMoisActuel = "SELECT SUM(montant_final) as total FROM paiements_eleves WHERE MONTH(date_paiement) = MONTH(CURRENT_DATE()) AND YEAR(date_paiement) = YEAR(CURRENT_DATE()) AND statut_paiement = 'Validé'";

// Exécution des requêtes avec PDO
$resultTotal = $dbh->query($queryTotalValide);
$totalValide = $resultTotal->fetch()['total'];

$resultEnAttente = $dbh->query($queryEnAttente);
$enAttente = $resultEnAttente->fetch();

$resultMoyenne = $dbh->query($queryMoyenneEleve);
$moyenneEleve = $resultMoyenne->fetch()['moyenne'];

$resultMoisActuel = $dbh->query($queryMoisActuel);
$totalMois = $resultMoisActuel->fetch()['total'];

// Requête pour données du graphique par mois
$queryGraphMois = "SELECT 
                    YEAR(date_paiement) as annee, 
                    MONTH(date_paiement) as mois, 
                    SUM(montant_final) as total 
                  FROM paiements_eleves 
                  WHERE statut_paiement = 'Validé' 
                  GROUP BY YEAR(date_paiement), MONTH(date_paiement) 
                  ORDER BY annee, mois";

$resultGraphMois = $dbh->query($queryGraphMois);
$mois = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
$dataGraphMois = array_fill_keys($mois, 0); // Initialiser avec tous les mois à 0

while ($row = $resultGraphMois->fetch()) {
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

$resultModes = $dbh->query($queryModes);
$dataModes = [];
while ($row = $resultModes->fetch()) {
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

$resultTopEleves = $dbh->query($queryTopEleves);
$topEleves = [];
while ($row = $resultTopEleves->fetch()) {
  $topEleves[] = $row;
}


// Requête pour obtenir le total des paiements validés de l'année en cours
$queryTotalValideAnneeActuelle = "SELECT SUM(montant_final) as total 
                                FROM paiements_eleves 
                                WHERE statut_paiement = 'Validé' 
                                AND YEAR(date_paiement) = YEAR(CURRENT_DATE())";
$resultTotalAnneeActuelle = $dbh->query($queryTotalValideAnneeActuelle);
$totalValideAnneeActuelle = $resultTotalAnneeActuelle->fetch()['total'] ?: 0;

// Requête pour obtenir le total des paiements validés de l'année précédente
$queryTotalValideAnneePrecedente = "SELECT SUM(montant_final) as total 
                                  FROM paiements_eleves 
                                  WHERE statut_paiement = 'Validé' 
                                  AND YEAR(date_paiement) = YEAR(CURRENT_DATE()) - 1";
$resultTotalAnneePrecedente = $dbh->query($queryTotalValideAnneePrecedente);
$totalValideAnneePrecedente = $resultTotalAnneePrecedente->fetch()['total'] ?: 0;

// Calcul du pourcentage d'évolution
$pourcentage_evolution = 0;
if ($totalValideAnneePrecedente > 0) {
  $pourcentage_evolution = (($totalValideAnneeActuelle - $totalValideAnneePrecedente) / $totalValideAnneePrecedente) * 100;
} else {
  // Si pas de données pour l'année précédente, on considère une augmentation de 100%
  $pourcentage_evolution = $totalValideAnneeActuelle > 0 ? 100 : null;
}
?>