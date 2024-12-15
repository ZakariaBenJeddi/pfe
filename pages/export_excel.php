<?php
require '../vendor/autoload.php'; // Inclure l'autoloader de Composer
// vendor/autoload.php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Connexion à la base de données
$host = 'localhost';
$db = 'dummy_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Récupérer les données des événements
$sql = "SELECT id, title, description, professeur, start_datetime AS start, end_datetime AS end, salle FROM schedule_list";
$stmt = $pdo->query($sql);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Créer une feuille de calcul Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Événements');

// Ajouter les en-têtes
$headers = ['ID', 'Titre', 'Description', 'Professeur', 'Début', 'Fin', 'Salle'];
$sheet->fromArray($headers, null, 'A1');

// Ajouter les données des événements
$row = 2; // Ligne de départ pour les données
foreach ($events as $event) {
    $sheet->setCellValue('A' . $row, $event['id']);
    $sheet->setCellValue('B' . $row, $event['title']);
    $sheet->setCellValue('C' . $row, $event['description']);
    $sheet->setCellValue('D' . $row, $event['professeur']);
    $sheet->setCellValue('E' . $row, $event['start']);
    $sheet->setCellValue('F' . $row, $event['end']);
    $sheet->setCellValue('G' . $row, $event['salle']);
    $row++;
}

// Configurer les en-têtes HTTP pour le téléchargement
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="evenements.xlsx"');
header('Cache-Control: max-age=0');

// Générer le fichier Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
