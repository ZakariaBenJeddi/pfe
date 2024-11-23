<<<<<<< HEAD
<?php
require '../includes/DatabaseConnexion.php';
// update
// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier'])) {
  // Récupérer les données du formulaire
  $id_eleve = $_POST['id_eleve'];
  $nom = $_POST['nom_eleve'];
  $prenom = $_POST['prenom_eleve'];
  $date_naissance = $_POST['date_naissance_eleve'];
  $genre = $_POST['genre_eleve'];
  $nationalite = $_POST['nationalite_eleve'];
  $adresse = $_POST['adresse_eleve'];
  $telephone = $_POST['telephone_eleve'];
  $email = $_POST['email_eleve'];
  $date_inscription = $_POST['date_inscription_eleve'];
  $statut = $_POST['statut_eleve'];
  $historique_scolaire = $_POST['historique_scolaire_eleve'];
  $langues_parlees = $_POST['langues_parlees_eleve'];
  $nom_tuteur = $_POST['nom_tuteur_eleve'];
  $telephone_tuteur = $_POST['telephone_tuteur_eleve'];
  $email_tuteur = $_POST['email_tuteur_eleve'];
  $profession_tuteur = $_POST['profession_tuteur_eleve'];
  $niveau_scolaire = $_POST['niveau_scolaire_eleve'];
  $besoins_speciaux = $_POST['besoins_speciaux_eleve'];
  $langue_etrangere = $_POST['langue_etrangere_eleve'];
  $niveau_de_satisfaction = $_POST['niveau_de_satisfaction_eleve'];

  // Préparer la requête de mise à jour
  $sql = "UPDATE eleves 
            SET nom = :nom,
                prenom = :prenom,
                date_naissance = :date_naissance,
                genre = :genre,
                nationalite = :nationalite,
                adresse = :adresse,
                telephone = :telephone,
                email = :email,
                date_inscription = :date_inscription,
                statut = :statut,
                historique_scolaire = :historique_scolaire,
                langues_parlees = :langues_parlees,
                nom_tuteur = :nom_tuteur,
                telephone_tuteur = :telephone_tuteur,
                email_tuteur = :email_tuteur,
                profession_tuteur = :profession_tuteur,
                niveau_scolaire = :niveau_scolaire,
                besoins_speciaux = :besoins_speciaux ,
                langue_etrangere = :langue_etrangere,
                niveau_de_satisfaction = :niveau_de_satisfaction,
                date_derniere_mise_a_jour = NOW()
            WHERE id_eleve = :id_eleve";

  try {
    $stmt = $dbh->prepare($sql);
    $stmt->execute([
      ':nom' => $nom,
      ':prenom' => $prenom,
      ':date_naissance' => $date_naissance,
      ':genre' => $genre,
      ':nationalite' => $nationalite,
      ':adresse' => $adresse,
      ':telephone' => $telephone,
      ':email' => $email,
      ':id_eleve' => $id_eleve,
      ':date_inscription' => $date_inscription,
      ':statut' => $statut,
      ':historique_scolaire' => $historique_scolaire,
      ':langues_parlees' => $langues_parlees,
      ':nom_tuteur' => $nom_tuteur,
      ':telephone_tuteur' => $telephone_tuteur,
      ':email_tuteur' => $email_tuteur,
      ':profession_tuteur' => $profession_tuteur,
      ':niveau_scolaire' => $niveau_scolaire,
      ':besoins_speciaux' => $besoins_speciaux,
      ':langue_etrangere' => $langue_etrangere,
      ':niveau_de_satisfaction' => $niveau_de_satisfaction,
    ]);

    echo "Les informations de l'élève ont été mises à jour avec succès.";
  } catch (PDOException $e) {
    echo "Erreur lors de la mise à jour : " . $e->getMessage();
  }
}

if (isset($_GET['id_eleve'])) {
  $id_eleve = $_GET['id_eleve'];

  $sql = "SELECT * FROM eleves WHERE id_eleve = :id_eleve";
  $stmt = $dbh->prepare($sql);

  try {
    $stmt->execute([':id_eleve' => $id_eleve]);
    $eleve = $stmt->fetch(PDO::FETCH_OBJ);
  } catch (PDOException $e) {
    echo "Erreur lors de la récupération des données : " . $e->getMessage();
  }
} else {
  echo "ID de l'élève non fourni.";
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <form method="post">
    <input type="text" readonly name="id_eleve" value="<?= $eleve->id_eleve ?>"><br>
    nom <input type="text" name="nom_eleve" value="<?= $eleve->nom ?>"><br>
    prenom <input type="text" name="prenom_eleve" value="<?= $eleve->prenom ?>"><br>
    date_naissance <input type="date" name="date_naissance_eleve" value="<?= $eleve->date_naissance ?>"><br>
    genre <input type="text" name="genre_eleve" value="<?= $eleve->genre ?>"><br>
    nationalite <input type="text" name="nationalite_eleve" value="<?= $eleve->nationalite ?>"><br>
    adresse <input type="text" name="adresse_eleve" value="<?= $eleve->adresse ?>"><br>
    telephone <input type="text" name="telephone_eleve" value="<?= $eleve->telephone ?>"><br>
    email <input type="email" name="email_eleve" value="<?= $eleve->email ?>"><br>
    date_inscription <input type="date" name="date_inscription_eleve" value="<?= $eleve->date_inscription ?>"><br>
    statut <input type="text" name="statut_eleve" value="<?= $eleve->statut ?>"><br>
    historique_scolaire <input type="text" name="historique_scolaire_eleve" value="<?= $eleve->historique_scolaire ?>"><br>
    langues_parlees <input type="text" name="langues_parlees_eleve" value="<?= $eleve->langues_parlees ?>"><br>
    nom_tuteur <input type="text" name="nom_tuteur_eleve" value="<?= $eleve->nom_tuteur ?>"><br>
    telephone_tuteur <input type="text" name="telephone_tuteur_eleve" value="<?= $eleve->telephone_tuteur ?>"><br>
    email_tuteur <input type="email" name="email_tuteur_eleve" value="<?= $eleve->email_tuteur ?>"><br>
    profession_tuteur <input type="text" name="profession_tuteur_eleve" value="<?= $eleve->profession_tuteur ?>"><br>
    niveau_scolaire <input type="text" name="niveau_scolaire_eleve" value="<?= $eleve->niveau_scolaire ?>"><br>
    besoins_speciaux <input type="text" name="besoins_speciaux_eleve" value="<?= $eleve->besoins_speciaux ?>"><br>
    langue_etrangere <input type="text" name="langue_etrangere_eleve" value="<?= $eleve->langue_etrangere ?>"><br>
    niveau_de_satisfaction <input type="text" name="niveau_de_satisfaction_eleve" value="<?= $eleve->niveau_de_satisfaction ?>"><br>
    <input type="submit" value="modifier" name="modifier">
  </form>
</body>

=======
<?php
require '../includes/DatabaseConnexion.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier'])) {
    // Récupérer les données du formulaire
    $id_eleve = $_POST['id_eleve'];
    $nom = $_POST['nom_eleve'];
    $prenom = $_POST['prenom_eleve'];
    $date_naissance = $_POST['date_naissance_eleve'];
    $genre = $_POST['genre_eleve'];
    $nationalite = $_POST['nationalite_eleve'];
    $adresse = $_POST['adresse_eleve'];
    $telephone = $_POST['telephone_eleve'];
    $email = $_POST['email_eleve'];
    $date_inscription = $_POST['date_inscription_eleve'];
    $statut = $_POST['statut_eleve'];
    $historique_scolaire = $_POST['historique_scolaire_eleve'];
    $langues_parlees = $_POST['langues_parlees_eleve'];
    $nom_tuteur = $_POST['nom_tuteur_eleve'];
    $telephone_tuteur = $_POST['telephone_tuteur_eleve'];
    $email_tuteur = $_POST['email_tuteur_eleve'];
    $profession_tuteur = $_POST['profession_tuteur_eleve'];
    $niveau_scolaire = $_POST['niveau_scolaire_eleve'];
    $besoins_speciaux = $_POST['besoins_speciaux_eleve'];
    $langue_etrangere = $_POST['langue_etrangere_eleve'];
    $niveau_de_satisfaction = $_POST['niveau_de_satisfaction_eleve'];

    // Préparer la requête de mise à jour
    $sql = "UPDATE eleves 
            SET nom = :nom,
                prenom = :prenom,
                date_naissance = :date_naissance,
                genre = :genre,
                nationalite = :nationalite,
                adresse = :adresse,
                telephone = :telephone,
                email = :email,
                date_inscription = :date_inscription,
                statut = :statut,
                historique_scolaire = :historique_scolaire,
                langues_parlees = :langues_parlees,
                nom_tuteur = :nom_tuteur,
                telephone_tuteur = :telephone_tuteur,
                email_tuteur = :email_tuteur,
                profession_tuteur = :profession_tuteur,
                niveau_scolaire = :niveau_scolaire,
                besoins_speciaux = :besoins_speciaux ,
                langue_etrangere = :langue_etrangere,
                niveau_de_satisfaction = :niveau_de_satisfaction,
                date_derniere_mise_a_jour = NOW()
            WHERE id_eleve = :id_eleve";

    try {
        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':date_naissance' => $date_naissance,
            ':genre' => $genre,
            ':nationalite' => $nationalite,
            ':adresse' => $adresse,
            ':telephone' => $telephone,
            ':email' => $email,
            ':id_eleve' => $id_eleve,
            ':date_inscription' => $date_inscription,
            ':statut' => $statut,
            ':historique_scolaire' => $historique_scolaire,
            ':langues_parlees' => $langues_parlees,
            ':nom_tuteur' => $nom_tuteur,
            ':telephone_tuteur' => $telephone_tuteur,
            ':email_tuteur' => $email_tuteur,
            ':profession_tuteur' => $profession_tuteur,
            ':niveau_scolaire' => $niveau_scolaire,
            ':besoins_speciaux' => $besoins_speciaux,
            ':langue_etrangere' => $langue_etrangere,
            ':niveau_de_satisfaction' => $niveau_de_satisfaction,
        ]);

        echo "Les informations de l'élève ont été mises à jour avec succès.";
    } catch (PDOException $e) {
        echo "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}

if (isset($_GET['id_eleve'])) {
  $id_eleve = $_GET['id_eleve'];

  $sql = "SELECT * FROM eleves WHERE id_eleve = :id_eleve";
  $stmt = $dbh->prepare($sql);

  try {
      $stmt->execute([':id_eleve' => $id_eleve]);
      $eleve = $stmt->fetch(PDO::FETCH_OBJ);
  } catch (PDOException $e) {
      echo "Erreur lors de la récupération des données : " . $e->getMessage();
  }
} else {
  echo "ID de l'élève non fourni.";
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <form method="post">
    <input type="text" readonly name="id_eleve" value="<?= $eleve->id_eleve ?>"><br>
    nom <input type="text" name="nom_eleve" value="<?= $eleve->nom ?>"><br>
    prenom <input type="text" name="prenom_eleve" value="<?= $eleve->prenom ?>"><br>
    date_naissance <input type="date" name="date_naissance_eleve" value="<?= $eleve->date_naissance ?>"><br>
    genre <input type="text" name="genre_eleve" value="<?= $eleve->genre ?>"><br>
    nationalite <input type="text" name="nationalite_eleve" value="<?= $eleve->nationalite ?>"><br>
    adresse <input type="text" name="adresse_eleve" value="<?= $eleve->adresse ?>"><br>
    telephone <input type="text" name="telephone_eleve" value="<?= $eleve->telephone ?>"><br>
    email <input type="email" name="email_eleve" value="<?= $eleve->email ?>"><br>
    date_inscription <input type="date" name="date_inscription_eleve" value="<?= $eleve->date_inscription ?>"><br>
    statut <input type="text" name="statut_eleve" value="<?= $eleve->statut ?>"><br>
    historique_scolaire <input type="text" name="historique_scolaire_eleve" value="<?= $eleve->historique_scolaire ?>"><br>
    langues_parlees <input type="text" name="langues_parlees_eleve" value="<?= $eleve->langues_parlees ?>"><br>
    nom_tuteur <input type="text" name="nom_tuteur_eleve" value="<?= $eleve->nom_tuteur ?>"><br>
    telephone_tuteur <input type="text" name="telephone_tuteur_eleve" value="<?= $eleve->telephone_tuteur ?>"><br>
    email_tuteur <input type="email" name="email_tuteur_eleve" value="<?= $eleve->email_tuteur ?>"><br>
    profession_tuteur <input type="text" name="profession_tuteur_eleve" value="<?= $eleve->profession_tuteur ?>"><br>
    niveau_scolaire <input type="text" name="niveau_scolaire_eleve" value="<?= $eleve->niveau_scolaire ?>"><br>
    besoins_speciaux <input type="text" name="besoins_speciaux_eleve" value="<?= $eleve->besoins_speciaux ?>"><br>
    langue_etrangere <input type="text" name="langue_etrangere_eleve" value="<?= $eleve->langue_etrangere ?>"><br>
    niveau_de_satisfaction <input type="text" name="niveau_de_satisfaction_eleve" value="<?= $eleve->niveau_de_satisfaction ?>"><br>
    <input type="submit" value="modifier" name="modifier">
  </form>
</body>
>>>>>>> 203f3cd426ea16764c18d102b7d84075161f3b26
</html>