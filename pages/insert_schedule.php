<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$database = "dummy_db";
$conn = new mysqli($servername, $username, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion à la base de données : " . $conn->connect_error);
}

// Récupérer les données envoyées par le client
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si les données sont valides
if (!is_array($data) || count($data) <= 1) {
    echo json_encode(['status' => 'error', 'message' => 'Aucune donnée reçue ou données invalides.']);
    exit();
}

// Préparer la requête SQL d'insertion
$sql = "INSERT INTO schedule_list (title, description, professeur, start_datetime, end_datetime, salle) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// Initialiser une variable pour suivre si tout est bien inséré
$success = true;

// Insérer chaque ligne de données dans la table (en sautant la première ligne)
for ($i = 1; $i < count($data); $i++) {
    $row = $data[$i];
    // Vérifiez que chaque ligne contient exactement 6 colonnes (correspondant à chaque champ de la table)
    if (count($row) == 6) {
        $stmt->bind_param("ssssss", $row[0], $row[1], $row[2], $row[3], $row[4], $row[5]);
        if (!$stmt->execute()) {
            $success = false;  // Si une erreur se produit, nous arrêterons le processus d'insertion
            break;
        }
    }
}

// Fermer la déclaration préparée
$stmt->close();
$conn->close();

// Envoyer une réponse en JSON
if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Données importées avec succès !']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'importation des données.']);
}
?>