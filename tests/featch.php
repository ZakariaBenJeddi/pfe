
<?php
if (isset($_GET['prof_id'])) {
    $profId = intval($_GET['prof_id']);

    // Connexion à la base de données
    $conn = new mysqli('localhost', 'root', '', 'pfe1');

    if ($conn->connect_error) {
        die('Erreur de connexion : ' . $conn->connect_error);
    }

    // Requête pour récupérer les classes
    $stmt = $conn->prepare("SELECT matiere.nom_matiere
      FROM enseignant_classes_matieres
      JOIN matiere ON enseignant_classes_matieres.id_matiere = matiere.id_matiere
      WHERE enseignant_classes_matieres.id_enseignant = ?;");
    $stmt->bind_param("i", $profId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['nom_matiere']) . "</li>";
        }
    } else {
        echo "<li>Aucune classe trouvée pour ce professeur.</li>";
    }

    $stmt->close();
    $conn->close();
}
?>