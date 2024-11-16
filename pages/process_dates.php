<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les valeurs envoyées via AJAX
    $dateDebut = $_POST['start_date'];
    $dateFin = $_POST['end_date'];

    // Traiter les dates
    echo "Date de début : " . $dateDebut . "\n";
    echo "Date de fin : " . $dateFin . "\n";

    // Vous pouvez les utiliser ou les enregistrer dans une base de données, par exemple
}
?>
