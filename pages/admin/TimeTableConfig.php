<?php
// Connexion à la base de données
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=pfe1;charset=utf8',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Échec de connexion : " . $e->getMessage());
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les valeurs soumises
    $maxTeacherHours = (int)$_POST['max_enseignant_hours'];
    $minClassHours = (int)$_POST['min_class_hours'];
    $maxClassHours = (int)$_POST['max_class_hours'];
    $sessionDuration = (int)$_POST['session_duration'];
    //$autre_option = (int)$_POST['autre_option'];

    // Mettre à jour la table configuration_table
    $updateQuery = "UPDATE configuration_table SET 
        max_enseignant_hours = :max_enseignant_hours, 
        min_class_hours = :min_class_hours, 
        max_class_hours = :max_class_hours, 
        session_duration = :session_duration
        WHERE id = :id";

    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([
        ':max_enseignant_hours' => $maxTeacherHours,
        ':min_class_hours' => $minClassHours,
        ':max_class_hours' => $maxClassHours,
        ':session_duration' => $sessionDuration,
        ':id' => 1
    ]);

    echo "<div class='success-message'>Les valeurs ont été mises à jour avec succès.</div>";
    header('location:TimeTable.php');
}

// Récupérer les valeurs actuelles pour les afficher dans le formulaire
$query = "SELECT * FROM configuration_table WHERE id = 1";
$config = $pdo->query($query)->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la configuration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <form method="POST" action="">
        <h1>Modifier la configuration</h1>
        <label for="max_teacher_hours">Heures max des enseignants par semaine :</label>
        <input type="number" id="max_enseignant_hours" name="max_enseignant_hours" value="<?= htmlspecialchars($config['max_enseignant_hours']) ?>" required>

        <label for="min_class_hours">Heures min par classe par semaine :</label>
        <input type="number" id="min_class_hours" name="min_class_hours" value="<?= htmlspecialchars($config['min_class_hours']) ?>" required>

        <label for="max_class_hours">Heures max par classe par semaine :</label>
        <input type="number" id="max_class_hours" name="max_class_hours" value="<?= htmlspecialchars($config['max_class_hours']) ?>" required>

        <label for="session_duration">Durée de session (en minutes) :</label>
        <input type="number" id="session_duration" name="session_duration" value="<?= htmlspecialchars($config['session_duration']) ?>" required>

        <button type="submit">Mettre à jour</button>
    </form>
</body>
</html>
