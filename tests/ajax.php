<?php
if (isset($_GET['prof_id'])) {
    $profId = intval($_GET['prof_id']);

    // Connexion à la base de données
    $conn = new mysqli('localhost', 'root', '', 'pfe1');

    if ($conn->connect_error) {
        die('Erreur de connexion : ' . $conn->connect_error);
    }

    // Requête pour récupérer les classes
    $stmt = $conn->prepare("SELECT nom_enseignant FROM enseignant WHERE id_enseignant = ?");
    $stmt->bind_param("i", $profId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['nom_enseignant']) . "</li>";
        }
    } else {
        echo "<li>Aucune classe trouvée pour ce professeur.</li>";
    }

    $stmt->close();
    $conn->close();
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professeurs et Classes</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Liste des Professeurs</h1>
    <label for="prof-select">Choisissez un professeur :</label>
    <select id="prof-select">
        <option value="">-- Sélectionnez un professeur --</option>
        <?php
        // Charger les professeurs depuis la base de données
        $conn = new mysqli('localhost', 'root', '', 'pfe1');
        $result = $conn->query("SELECT * FROM enseignant");
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id_enseignant']}'>{$row['nom_enseignant']}</option>";
        }
        ?>
    </select>

    <h2>Classes associées :</h2>
    <ul id="classe-list"></ul>

    <script>
        $(document).ready(function () {
            $('#prof-select').change(function () {
                const profId = $(this).val();
                if (profId) {
                    $.ajax({
                        url: 'featch.php',
                        type: 'GET',
                        data: { prof_id: profId },
                        success: function (data) {
                          $('#classe-list').html(data);
                        }
                    });
                } else {
                    $('#classe-list').html('');
                }
            });
        });
    </script>
</body>
</html>