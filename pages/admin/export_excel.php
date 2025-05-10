<?php
// Inclure les fichiers nécessaires
include('../../includes/admin/controller/controller.php');
session_start();
if (empty($_SESSION['user'])) {
    header('location:../sign-in.php');
    exit;
}
// Déconnexion après inactivité
require('../../includes/deconnexion_5s.php');

require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    // Appel de la fonction d'exportation
    $result = export_timetable($dbh);
    
    if ($result['status'] === 'success') {
        $events = $result['data'];
        
        // Créer une feuille de calcul Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Emploi du temps');
        
        // Ajouter les en-têtes
        $headers = ['ID', 'Description', 'Professeur', 'Matière', 'Classe', 'Salle', 'Début', 'Fin'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style pour les en-têtes
        $headerStyle = $sheet->getStyle('A1:H1');
        $headerStyle->getFont()->setBold(true);
        
        // Ajouter les données des événements
        $row = 2; // Ligne de départ pour les données
        foreach ($events as $event) {
            // Ignorer les champs success et message qui viennent de la procédure
            if (isset($event['id'])) {
                $sheet->setCellValue('A' . $row, $event['id']);
                $sheet->setCellValue('B' . $row, $event['description']);
                $sheet->setCellValue('C' . $row, $event['professeur']);
                $sheet->setCellValue('D' . $row, $event['matiere']);
                $sheet->setCellValue('E' . $row, $event['classe']);
                $sheet->setCellValue('F' . $row, $event['salle']);
                $sheet->setCellValue('G' . $row, $event['start']);
                $sheet->setCellValue('H' . $row, $event['end']);
                $row++;
            }
        }
        
        // Ajuster automatiquement la largeur des colonnes
        foreach(range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Configurer les en-têtes HTTP pour le téléchargement
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="emploi_du_temps.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Générer le fichier Excel
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    } else {
        // En cas d'erreur, afficher un message
        echo "Erreur: " . $result['message'];
    }
    
} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage();
}
?>