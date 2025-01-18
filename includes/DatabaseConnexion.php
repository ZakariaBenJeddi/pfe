<?php
	define('DB_SERVER', 'localhost');
	define('DB_USER', 'root');
	define('DB_PASS', '');
	define('DB_NAME', 'pfe1');

	try {
	    // Connexion à la base de données avec des options supplémentaires
	    $dbh = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME, DB_USER, DB_PASS, array(
	        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
	        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Activer les exceptions en cas d'erreur
	        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Récupérer les résultats sous forme de tableau associatif
	    ));
	}
	catch (PDOException $e) {
	    // En environnement de développement, afficher le message d'erreur
	    // En production, vous pouvez commenter la ligne ci-dessous et enregistrer les erreurs dans un fichier log
	    exit("Erreur de connexion à la base de données : " . $e->getMessage());
	}