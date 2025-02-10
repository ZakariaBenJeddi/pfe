<?php
$filePath = __DIR__ . '/../../DatabaseConnexion.php';
if (file_exists($filePath)) {
    require($filePath);
} else {
    echo "Fichier introuvable : $filePath";
    exit;
}

// =============== Enseignant ================
    function get_all_enseignant($dbh)
    {
        try {
            $sql = "CALL get_all_enseignant()";
            $stmt = $dbh->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $result;
        } catch (PDOException $e) {
            // En cas d'erreur, retournez le message d'erreur ou gérez-le selon vos besoins.
            echo "Erreur : " . $e->getMessage();
            return [];
        }
    }

    function getEnseignantById($dbh, $id_enseignant) {
        try {
            // Validation de l'ID
            if (!$id_enseignant || !filter_var($id_enseignant, FILTER_VALIDATE_INT)) {
                return [
                    'success' => false,
                    'message' => "ID d'enseignant invalide",
                    'redirect' => true
                ];
            }

            // Appel de la procédure stockée
            $sql = "CALL get_enseignant_by_id(:id_enseignant)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id_enseignant', $id_enseignant, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (empty($results)) {
                return [
                    'success' => false,
                    'message' => "Enseignant non trouvé",
                    'redirect' => true
                ];
            }

            return [
                'success' => true,
                'data' => $results
            ];

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'message' => "Erreur lors de la récupération de l'enseignant",
                'redirect' => true
            ];
        }
    }


    function ajouterEnseignant($dbh, $data)
    {
        try {
            // Préparer l'appel de la procédure stockée
            $sql = "CALL ajouter_enseignant(
                :nom_enseignant, :prenom_enseignant, :email_enseignant,
                :telephone_enseignant, :date_naissance, :specialite,
                :masse_horaire, :date_embauche, :adresse, :genre,
                :niveau_education, :salaire, :est_connecte, :degree
            )";

            $stmt = $dbh->prepare($sql);

            // Exécuter la procédure avec les paramètres
            $result = $stmt->execute([
                ':nom_enseignant' => $data['nom_enseignant'],
                ':prenom_enseignant' => $data['prenom_enseignant'],
                ':email_enseignant' => $data['email_enseignant'],
                ':telephone_enseignant' => $data['telephone_enseignant'],
                ':date_naissance' => $data['date_naissance'],
                ':specialite' => $data['specialite'],
                ':masse_horaire' => $data['masse_horaire'],
                ':date_embauche' => $data['date_embauche'],
                ':adresse' => $data['adresse'],
                ':genre' => $data['genre'],
                ':niveau_education' => $data['niveau_education'],
                ':salaire' => $data['salaire'],
                ':est_connecte' => $data['est_connecte'],
                ':degree' => $data['degree']
            ]);

            return ['success' => true, 'message' => 'Enseignant ajouté avec succès'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout : ' . $e->getMessage()];
        }
    }

    function modifierEnseignant($dbh, $data)
    {
        try {
            $sql = "CALL modifier_enseignant(
            :id_enseignant, :nom_enseignant, :prenom_enseignant,
            :email_enseignant, :telephone_enseignant, :date_naissance,
            :specialite, :masse_horaire, :date_embauche,
            :adresse, :genre, :niveau_education,
            :salaire, :date_creation, :est_connecte, :degree
        )";

            $stmt = $dbh->prepare($sql);
            $result = $stmt->execute([
                ':id_enseignant' => $data['id_enseignant'],
                ':nom_enseignant' => $data['nom_enseignant'],
                ':prenom_enseignant' => $data['prenom_enseignant'],
                ':email_enseignant' => $data['email_enseignant'],
                ':telephone_enseignant' => $data['telephone_enseignant'],
                ':date_naissance' => $data['date_naissance'],
                ':specialite' => $data['specialite'],
                ':masse_horaire' => $data['masse_horaire'],
                ':date_embauche' => $data['date_embauche'],
                ':adresse' => $data['adresse'],
                ':genre' => $data['genre'],
                ':niveau_education' => $data['niveau_education'],
                ':salaire' => $data['salaire'],
                ':date_creation' => $data['date_creation'],
                ':est_connecte' => $data['est_connecte'],
                ':degree' => $data['degree']
            ]);

            return ['success' => true, 'message' => 'Enseignant modifié avec succès'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la modification : ' . $e->getMessage()];
        }
    }

    function supprimerEnseignant($dbh, $id)
    {
        try {
            // Valider l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                return ['success' => false, 'message' => 'ID invalide. Opération annulée.'];
            }

            // Appeler la procédure stockée
            $sql = "CALL supprimer_enseignant(:id)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Enseignant supprimé avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la suppression'];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return ['success' => false, 'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'];
        }
    }

    function filtrerEnseignantsParDate($dbh, $start_date, $end_date)
    {
        try {
            // Validation des dates
            if (empty($start_date) || empty($end_date)) {
                throw new Exception("Les dates sont requises");
            }

            // Nettoyage et validation des dates
            $start_date = filter_var($start_date, FILTER_SANITIZE_STRING);
            $end_date = filter_var($end_date, FILTER_SANITIZE_STRING);

            if (!$start_date || !$end_date) {
                throw new Exception("Format de date invalide");
            }

            // Conversion des dates au format MySQL
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            // Appel de la procédure stockée
            $sql = "CALL filtrer_enseignants_par_date(:start_date, :end_date)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);

            $results = $stmt->fetchAll(PDO::FETCH_OBJ);

            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
// =============== Enseignant ================

// =============== Eleve ================
    function getElevesInfo($dbh)
    {
        try {
            // Appel de la procédure stockée
            $sql = "CALL get_eleves_info()";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_OBJ);

            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des données élèves'
            ];
        }
    }

    function getEleveById($dbh, $id_eleve) {
        try {
            if (!filter_var($id_eleve, FILTER_VALIDATE_INT)) {
                return [
                    'success' => false,
                    'message' => "ID d'élève invalide"
                ];
            }

            $sql = "CALL get_eleve_by_id(:id_eleve)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([':id_eleve' => $id_eleve]);
            
            $eleve = $stmt->fetch(PDO::FETCH_OBJ);
            
            if (!$eleve) {
                return [
                    'success' => false,
                    'message' => "Élève non trouvé"
                ];
            }

            return [
                'success' => true,
                'data' => $eleve
            ];

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'message' => "Erreur lors de la récupération de l'élève"
            ];
        }
    }

    function filtrerElevesParDate($dbh, $start_date, $end_date) {
        try {
            // Validation des dates
            if (!$start_date || !$end_date) {
                return ['success' => false, 'message' => 'Les dates sont requises'];
            }

            // Nettoyage et validation des dates
            $start_date = filter_var($start_date, FILTER_SANITIZE_STRING);
            $end_date = filter_var($end_date, FILTER_SANITIZE_STRING);

            if (!$start_date || !$end_date) {
                return ['success' => false, 'message' => 'Format de date invalide'];
            }

            // Conversion des dates au format MySQL
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            // Appel de la procédure stockée
            $sql = "CALL filtrer_eleves_par_date(:start_date, :end_date)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
            $stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $results = $stmt->fetchAll(PDO::FETCH_OBJ);
                return [
                    'success' => true, 
                    'data' => $results,
                    'count' => count($results),
                    'date' => [$start_date,$end_date]
                ];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la recherche'];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return ['success' => false, 'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'];
        }
    }

    function ajouterEleve($dbh, $donnees_eleve) {
        try {
            // Validation des données requises
            $champs_requis = ['nom_eleve', 'prenom_eleve', 'date_naissance_eleve', 'email_eleve'];
            foreach ($champs_requis as $champ) {
                if (empty($donnees_eleve[$champ])) {
                    return [
                        'success' => false,
                        'message' => "Le champ $champ est requis."
                    ];
                }
            }
    
            // Validation de l'email
            if (!filter_var($donnees_eleve['email_eleve'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => "L'adresse email n'est pas valide."
                ];
            }
    
            // Appel de la procédure stockée
            $sql = "CALL ajouter_eleve(
                :id_niveau, :id_classe, :id_filiere, :code_massare, :nom, :prenom, :date_naissance, :genre, :nationalite,
                :adresse, :telephone, :email, :date_inscription, :statut,
                :historique_scolaire, :langues_parlees, :nom_tuteur,
                :telephone_tuteur, :email_tuteur, :profession_tuteur,
                :besoins_speciaux, :langue_etrangere,
                :niveau_de_satisfaction
            )";
    
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':id_niveau' => $donnees_eleve['niveau_scolaire_eleve'],
                ':id_classe' => $donnees_eleve['classe_eleve'],
                ':id_filiere' => $donnees_eleve['filiere_eleve'],
                ':code_massare' => $donnees_eleve['code_massare'],
                ':nom' => $donnees_eleve['nom_eleve'],
                ':prenom' => $donnees_eleve['prenom_eleve'],
                ':date_naissance' => $donnees_eleve['date_naissance_eleve'],
                ':genre' => $donnees_eleve['genre_eleve'],
                ':nationalite' => $donnees_eleve['nationalite_eleve'],
                ':adresse' => $donnees_eleve['adresse_eleve'],
                ':telephone' => $donnees_eleve['telephone_eleve'],
                ':email' => $donnees_eleve['email_eleve'],
                ':date_inscription' => $donnees_eleve['date_inscription_eleve'],
                ':statut' => $donnees_eleve['statut_eleve'],
                ':historique_scolaire' => $donnees_eleve['historique_scolaire_eleve'],
                ':langues_parlees' => $donnees_eleve['langues_parlees_eleve'],
                ':nom_tuteur' => $donnees_eleve['nom_tuteur_eleve'],
                ':telephone_tuteur' => $donnees_eleve['telephone_tuteur_eleve'],
                ':email_tuteur' => $donnees_eleve['email_tuteur_eleve'],
                ':profession_tuteur' => $donnees_eleve['profession_tuteur_eleve'],
                ':besoins_speciaux' => $donnees_eleve['besoins_speciaux_eleve'],
                ':langue_etrangere' => $donnees_eleve['langue_etrangere_eleve'],
                ':niveau_de_satisfaction' => $donnees_eleve['niveau_de_satisfaction_eleve']
            ]);
    
            // Récupérer l'ID du nouvel élève
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_eleve = $result['id_eleve'];
    
            return [
                'success' => true,
                'message' => 'Élève ajouté avec succès',
                'id_eleve' => $id_eleve
            ];
    
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'ajout de l\'élève.'
            ];
        }
    }

    function supprimerEleve($dbh, $id)
    {
        try {
            // Valider l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                return ['success' => false, 'message' => 'ID invalide. Opération annulée.'];
            }

            // Appeler la procédure stockée
            $sql = "CALL supprimer_eleve(:id)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Élève supprimé avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la suppression'];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return ['success' => false, 'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'];
        }
    }

    function modifierEleve($dbh, $donnees_eleve, $fichier_image = null) {
        try {
            // Validation des données requises
            $champs_requis = ['id_eleve', 'nom_eleve', 'prenom_eleve', 'email_eleve'];
            foreach ($champs_requis as $champ) {
                if (empty($donnees_eleve[$champ])) {
                    return [
                        'success' => false,
                        'message' => "Le champ $champ est requis."
                    ];
                }
            }

            // Gestion de l'upload d'image
            $photo = null;
            if ($fichier_image && !empty($fichier_image['name'])) {
                $resultat_upload = gererUploadImage($fichier_image);
                if (!$resultat_upload['success']) {
                    return $resultat_upload;
                }
                $photo = $resultat_upload['filename'];
            }

            // Appel de la procédure stockée
            $sql = "CALL modifier_eleve(
                :id_eleve, :nom, :prenom, :date_naissance, :genre, 
                :nationalite, :adresse, :telephone, :email, :date_inscription,
                :statut, :historique_scolaire, :langues_parlees, :nom_tuteur,
                :telephone_tuteur, :email_tuteur, :profession_tuteur,
                :niveau_scolaire, :besoins_speciaux, :langue_etrangere,
                :niveau_de_satisfaction, :photo
            )";

            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':id_eleve' => $donnees_eleve['id_eleve'],
                ':nom' => $donnees_eleve['nom_eleve'],
                ':prenom' => $donnees_eleve['prenom_eleve'],
                ':date_naissance' => $donnees_eleve['date_naissance_eleve'],
                ':genre' => $donnees_eleve['genre_eleve'],
                ':nationalite' => $donnees_eleve['nationalite_eleve'],
                ':adresse' => $donnees_eleve['adresse_eleve'],
                ':telephone' => $donnees_eleve['telephone_eleve'],
                ':email' => $donnees_eleve['email_eleve'],
                ':date_inscription' => $donnees_eleve['date_inscription_eleve'],
                ':statut' => $donnees_eleve['statut_eleve'],
                ':historique_scolaire' => $donnees_eleve['historique_scolaire_eleve'],
                ':langues_parlees' => $donnees_eleve['langues_parlees_eleve'],
                ':nom_tuteur' => $donnees_eleve['nom_tuteur_eleve'],
                ':telephone_tuteur' => $donnees_eleve['telephone_tuteur_eleve'],
                ':email_tuteur' => $donnees_eleve['email_tuteur_eleve'],
                ':profession_tuteur' => $donnees_eleve['profession_tuteur_eleve'],
                ':niveau_scolaire' => $donnees_eleve['niveau_scolaire_eleve'],
                ':besoins_speciaux' => $donnees_eleve['besoins_speciaux_eleve'],
                ':langue_etrangere' => $donnees_eleve['langue_etrangere_eleve'],
                ':niveau_de_satisfaction' => $donnees_eleve['niveau_de_satisfaction_eleve'],
                ':photo' => $photo
            ]);

            return [
                'success' => true,
                'message' => 'Élève modifié avec succès'
            ];

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'message' => "Erreur lors de la modification de l'élève"
            ];
        }
    }

    function gererUploadImage($fichier) {
        $targetDir = "../../assets/img/school/eleve/";
        $fileName = uniqid('eleve_', true) . '.' . pathinfo($fichier['name'], PATHINFO_EXTENSION);
        $targetFilePath = $targetDir . $fileName;

        // Validation du type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileMime = finfo_file($finfo, $fichier['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($fileMime, $allowedMimeTypes)) {
            return [
                'success' => false,
                'message' => 'Type de fichier non autorisé'
            ];
        }

        if (!move_uploaded_file($fichier['tmp_name'], $targetFilePath)) {
            return [
                'success' => false,
                'message' => 'Erreur lors du téléchargement de l\'image'
            ];
        }

        return [
            'success' => true,
            'filename' => $fileName
        ];
    }
// =============== Eleve ================

// =============== Niveau ================
    function get_all_niveau($dbh)
    {
        try {
            $sql = "CALL get_all_niveau()";
            $stmt = $dbh->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $result;
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            die("Erreur lors de la récupération des niveaux.");
            return [];
        }
    }
    function get_niveau_by_id($dbh, $id) {
        try {
            // Validation de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id === null) {
                throw new Exception('ID invalide');
            }
    
            // Appel de la procédure stockée
            $sql = "CALL get_niveau_by_id(:id)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            if ($result) {
                return [
                    'success' => true,
                    'data' => $result
                ];
            } else {
                throw new Exception('Niveau non trouvé');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération du niveau'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    function search_niveau_by_date($dbh, $start_date, $end_date) {
        try {
            // Validation des dates
            if (empty($start_date) || empty($end_date)) {
                throw new Exception("Les deux dates sont requises.");
            }
    
            // Nettoyage et validation des dates
            $start_date = filter_var($start_date, FILTER_SANITIZE_STRING);
            $end_date = filter_var($end_date, FILTER_SANITIZE_STRING);
    
            if (!$start_date || !$end_date || !strtotime($start_date) || !strtotime($end_date)) {
                throw new Exception("Format de date invalide.");
            }
    
            // Conversion au format MySQL
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
    
            // Vérification que la date de début est antérieure à la date de fin
            if (strtotime($start_date) > strtotime($end_date)) {
                throw new Exception("La date de début doit être antérieure à la date de fin.");
            }
    
            // Appel de la procédure stockée
            $sql = "CALL search_niveau_by_date(:start_date, :end_date)";
            $stmt = $dbh->prepare($sql);
            
            $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
            $stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
    
            return [
                'status' => 'success',
                'data' => $results,
                'count' => count($results)
            ];
            
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la recherche'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    function delete_niveau($dbh, $id) {
        try {
            // Validation de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id === null) {
                throw new Exception('ID invalide');
            }
    
            // Appel de la procédure stockée
            $sql = "CALL delete_niveau(:id)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Niveau supprimé avec succès'
                ];
            } else {
                throw new Exception('Échec de la suppression');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    function add_niveau($dbh, $nom_niveau, $description, $statut) {
        try {
            // Validation des données
            if (empty($nom_niveau)) {
                throw new Exception('Le nom du niveau est requis');
            }
    
            // Nettoyage des données
            $nom_niveau = trim(strip_tags($nom_niveau));
            $description = trim(strip_tags($description));
            $statut = filter_var($statut, FILTER_VALIDATE_INT);
            $date_creation = date('Y-m-d');
    
            // Appel de la procédure stockée
            $sql = "CALL add_niveau(:nom_niveau, :description, :statut, :date_creation)";
            $stmt = $dbh->prepare($sql);
            
            $stmt->bindParam(':nom_niveau', $nom_niveau, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':statut', $statut, PDO::PARAM_INT);
            $stmt->bindParam(':date_creation', $date_creation, PDO::PARAM_STR);
    
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Niveau ajouté avec succès'
                ];
            } else {
                throw new Exception('Échec de l\'ajout du niveau');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'ajout du niveau'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    function update_niveau($dbh, $id_niveau, $nom_niveau, $description_niveau, $statut) {
        try {
            // Validation des données
            if (empty($nom_niveau)) {
                throw new Exception('Le nom du niveau est requis');
            }
    
            $id_niveau = filter_var($id_niveau, FILTER_VALIDATE_INT);
            if ($id_niveau === false || $id_niveau === null) {
                throw new Exception('ID invalide');
            }
    
            // Nettoyage des données
            $nom_niveau = trim(strip_tags($nom_niveau));
            $description_niveau = trim(strip_tags($description_niveau));
            $statut = filter_var($statut, FILTER_VALIDATE_INT);
    
            // Appel de la procédure stockée
            $sql = "CALL update_niveau(:id_niveau, :nom_niveau, :description_niveau, :statut)";
            $stmt = $dbh->prepare($sql);
            
            $stmt->bindParam(':id_niveau', $id_niveau, PDO::PARAM_INT);
            $stmt->bindParam(':nom_niveau', $nom_niveau, PDO::PARAM_STR);
            $stmt->bindParam(':description_niveau', $description_niveau, PDO::PARAM_STR);
            $stmt->bindParam(':statut', $statut, PDO::PARAM_INT);
    
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Niveau modifié avec succès'
                ];
            } else {
                throw new Exception('Échec de la modification du niveau');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la modification du niveau'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
// =============== Niveau ================

// =============== Classe (Ajouter/Modifier ❌)================
    function get_all_classes($dbh) {
        try {
            // Appel de la procédure stockée
            $sql = "CALL get_all_classes()";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des classes'
            ];
        }
    }

    function delete_classe($dbh, $id) {
        try {
            // Validation de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                return [
                    'success' => false,
                    'message' => 'ID invalide'
                ];
            }
    
            // Appel de la procédure stockée
            $sql = "CALL delete_classe(:id, @success)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
    
            // Vérification du résultat
            $result = $dbh->query("SELECT @success as success")->fetch(PDO::FETCH_ASSOC);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Classe supprimée avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la suppression'
                ];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression'
            ];
        }
    }

    function get_classe_by_id($dbh, $id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                return [
                    'success' => false,
                    'message' => 'ID invalide'
                ];
            }
    
            $sql = "CALL get_classe_by_id(:id)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([':id' => $id]);
    
            $classe = $stmt->fetch(PDO::FETCH_OBJ);
    
            if ($classe) {
                return [
                    'success' => true,
                    'data' => $classe
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Classe non trouvée'
                ];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération'
            ];
        }
    }
    

    function get_classes_by_date_range($dbh, $start_date, $end_date) {
        try {
            // Validation et nettoyage des dates
            $start_date = filter_var($start_date, FILTER_SANITIZE_STRING);
            $end_date = filter_var($end_date, FILTER_SANITIZE_STRING);
    
            if (!$start_date || !$end_date) {
                return [
                    'success' => false,
                    'message' => 'Format de date invalide'
                ];
            }
    
            // Conversion des dates au format MySQL
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
    
            // Appel de la procédure stockée
            $sql = "CALL get_classes_by_date_range(:start_date, :end_date)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);
    
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
    
            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la recherche'
            ];
        }
    }
// =============== Classe ================

// =============== Filiere ================
    function get_filieres_with_niveaux($dbh) {
        try {
            // Appel de la procédure stockée
            $sql = "CALL get_filieres_with_niveaux()";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des filières'
            ];
        }
    }
    function delete_filiere($dbh, $id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                return [
                    'success' => false,
                    'message' => 'ID invalide'
                ];
            }
    
            $sql = "CALL delete_filiere(:id, @success)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
    
            $result = $dbh->query("SELECT @success as success")->fetch(PDO::FETCH_ASSOC);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Filière supprimée avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la suppression'
                ];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression'
            ];
        }
    }
    function insert_filiere($dbh, $data) {
        try {
            // Validation des données
            $niveau = filter_var($data['niveau'], FILTER_VALIDATE_INT);
            $nombre_heures_max = filter_var($data['nombre_heures_max'], FILTER_VALIDATE_INT);
    
            if ($niveau === false || $nombre_heures_max === false) {
                return [
                    'success' => false,
                    'message' => 'Données invalides'
                ];
            }
    
            // Formatage de la date
            $date_creation = $data['date_creation'] . ' ' . date('H:i:s');
    
            // Appel de la procédure stockée
            $sql = "CALL insert_filiere(:nom_filiere, :niveau, :abriviation_filiere, 
                                        :code_filiere, :description, :nombre_heures_max, 
                                        :date_creation, @success)";
            
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':nom_filiere' => $data['nom_filiere'],
                ':niveau' => $niveau,
                ':abriviation_filiere' => $data['abriviation_filiere'],
                ':code_filiere' => $data['code_filiere'],
                ':description' => $data['description'],
                ':nombre_heures_max' => $nombre_heures_max,
                ':date_creation' => $date_creation
            ]);
    
            $result = $dbh->query("SELECT @success as success")->fetch(PDO::FETCH_ASSOC);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Filière ajoutée avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'ajout'
                ];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'ajout'
            ];
        }
    }
    function get_filieres_by_date_range($dbh, $start_date, $end_date) {
        try {
            // Validation et nettoyage des dates
            $start_date = filter_var($start_date, FILTER_SANITIZE_STRING);
            $end_date = filter_var($end_date, FILTER_SANITIZE_STRING);
            
            if (!$start_date || !$end_date) {
                return [
                    'success' => false,
                    'message' => 'Format de date invalide'
                ];
            }
            
            // Conversion des dates au format MySQL
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            // Appel de la procédure stockée
            $sql = "CALL get_filieres_by_date_range(:start_date, :end_date)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);
            
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la recherche'
            ];
        }
    }
    function get_filiere_by_id($dbh, $id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                return [
                    'success' => false,
                    'message' => 'ID invalide'
                ];
            }
    
            $sql = "CALL get_filiere_by_id(:id)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $filiere = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($filiere) {
                return [
                    'success' => true,
                    'data' => $filiere
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Filière non trouvée'
                ];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération'
            ];
        }
    }
    function update_filiere($dbh, $data) {
        try {
            // Validation des données
            $id_filiere = filter_var($data['id_filiere'], FILTER_VALIDATE_INT);
            $niveau = filter_var($data['niveau'], FILTER_VALIDATE_INT);
            $nombre_heures_max = filter_var($data['nombre_heures_max'], FILTER_VALIDATE_INT);
    
            if ($id_filiere === false || $niveau === false || $nombre_heures_max === false) {
                return [
                    'success' => false,
                    'message' => 'Données invalides'
                ];
            }
    
            // Appel de la procédure stockée
            $sql = "CALL update_filiere(:id_filiere, :nom_filiere, :niveau, 
                                        :abriviation_filiere, :code_filiere, :description, 
                                        :nombre_heures_max, :date_creation, @success)";
            
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':id_filiere' => $id_filiere,
                ':nom_filiere' => $data['nom_filiere'],
                ':niveau' => $niveau,
                ':abriviation_filiere' => $data['abriviation_filiere'],
                ':code_filiere' => $data['code_filiere'],
                ':description' => $data['description_filiere'],
                ':nombre_heures_max' => $nombre_heures_max,
                ':date_creation' => $data['date_creation']
            ]);
    
            $result = $dbh->query("SELECT @success as success")->fetch(PDO::FETCH_ASSOC);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Filière modifiée avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la modification'
                ];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la modification'
            ];
        }
    }
// =============== Filiere ================

// =============== Salle ================
    function get_all_salles($dbh) {
        try {
            // Appel de la procédure stockée
            $sql = "CALL get_all_salle()";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (PDOException $e) {
            // Log l'erreur de manière sécurisée
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des classes'
            ];
        }
    }
    function get_class_by_id($dbh, $id_salle) {
        try {
            // Validation de l'ID
            $id_salle = filter_var($id_salle, FILTER_VALIDATE_INT);
            if ($id_salle === false) {
                return [
                    'success' => false,
                    'message' => 'Identifiant de salle invalide'
                ];
            }
    
            // Appel de la procédure stockée
            $sql = "CALL get_class_by_id(:id_salle)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id_salle', $id_salle, PDO::PARAM_INT);
            $stmt->execute();
    
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'data' => $stmt->fetch(PDO::FETCH_ASSOC)
                ];
            }
    
            return [
                'success' => false,
                'message' => 'Aucune salle trouvée avec cet identifiant'
            ];
    
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des données'
            ];
        }
    }
    function get_salle_by_date_range($dbh, $start_date, $end_date) {
        try {
            // Validation de base
            if (empty($start_date) || empty($end_date)) {
                return [
                    'status' => 'error',
                    'message' => 'Les dates sont requises',
                    'code' => 400
                ];
            }
    
            // Nettoyage et validation des dates
            $start_date = filter_var($start_date, FILTER_SANITIZE_STRING);
            $end_date = filter_var($end_date, FILTER_SANITIZE_STRING);
    
            if (!$start_date || !$end_date) {
                return [
                    'status' => 'error',
                    'message' => 'Format de date invalide',
                    'code' => 400
                ];
            }
    
            // Validation du format des dates
            $start_timestamp = strtotime($start_date);
            $end_timestamp = strtotime($end_date);
    
            if ($start_timestamp === false || $end_timestamp === false) {
                return [
                    'status' => 'error',
                    'message' => 'Format de date invalide',
                    'code' => 400
                ];
            }
    
            // Conversion des dates au format MySQL
            $start_date = date("Y-m-d", $start_timestamp);
            $end_date = date("Y-m-d", $end_timestamp);
    
            // Appel de la procédure stockée
            $sql = "CALL get_salle_by_date_range(:start_date, :end_date)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);
    
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
    
            return [
                'status' => 'success',
                'data' => $results,
                'count' => count($results),
                'code' => 200
            ];
    
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la recherche',
                'code' => 500
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => 400
            ];
        }
    }
    function ajouter_salle($dbh, $data) {
        try {
            // Nettoyage et validation des données
            $cleanData = [
                'nom_salle' => htmlspecialchars(trim($data['nom_salle'] ?? '')),
                'equipement' => htmlspecialchars(trim($data['equipement'] ?? '')),
                'etage' => filter_var($data['etage'] ?? null, FILTER_VALIDATE_INT),
                'capacite' => filter_var($data['capacite'] ?? null, FILTER_VALIDATE_INT),
                'nombre_chaise' => filter_var($data['nombre_chaise'] ?? null, FILTER_VALIDATE_INT),
                'nombre_bureau' => filter_var($data['nombre_bureau'] ?? null, FILTER_VALIDATE_INT),
                'nombre_tableau' => filter_var($data['nombre_tableau'] ?? null, FILTER_VALIDATE_INT)
            ];

            // Vérification des valeurs requises
            if (!$cleanData['capacite'] || !$cleanData['nombre_chaise'] || 
                !$cleanData['nombre_bureau'] || !$cleanData['nombre_tableau']) {
                return [
                    'success' => false,
                    'message' => 'Veuillez remplir tous les champs numériques',
                    'redirect' => false
                ];
            }

            // Appel de la procédure stockée
            $sql = "CALL add_salle(:nom_salle, :capacite, :etage, :equipement, :nombre_chaise, :nombre_bureau, :nombre_tableau)";
            $stmt = $dbh->prepare($sql);

            // Liaison des paramètres
            $stmt->bindParam(':nom_salle', $cleanData['nom_salle']);
            $stmt->bindParam(':capacite', $cleanData['capacite'], PDO::PARAM_INT);
            $stmt->bindParam(':etage', $cleanData['etage'], PDO::PARAM_INT);
            $stmt->bindParam(':equipement', $cleanData['equipement']);
            $stmt->bindParam(':nombre_chaise', $cleanData['nombre_chaise'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre_bureau', $cleanData['nombre_bureau'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre_tableau', $cleanData['nombre_tableau'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Salle ajoutée avec succès',
                    'redirect' => true,
                    'redirect_url' => 'salle.php'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la salle',
                'redirect' => false
            ];

        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'ajout de la salle',
                'redirect' => false
            ];
        }
    }
    function delete_class($dbh, $id) {
        try {
            // Configuration de PDO
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Validation de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                return [
                    'success' => false,
                    'message' => 'ID invalide. Opération annulée.',
                    'redirect' => false
                ];
            }
            
            // Appel de la procédure stockée
            $sql = "CALL delete_class(:id)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Salle bien supprimée',
                    'redirect' => true,
                    'redirect_url' => 'salle.php'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la suppression.',
                    'redirect' => false
                ];
            }
        } catch (PDOException $e) {
            // Log l'erreur de manière sécurisée
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.',
                'redirect' => false
            ];
        }
    }
    function update_class($dbh, $data) {
        try {
            // Validation des données
            $cleanData = [
                'id_salle' => filter_var($data['id_salle'], FILTER_VALIDATE_INT),
                'nom_salle' => htmlspecialchars(trim($data['nom_salle'] ?? '')),
                'capacite_salle' => filter_var($data['capacite_salle'], FILTER_VALIDATE_INT),
                'etage' => filter_var($data['etage'], FILTER_VALIDATE_INT),
                'equipements' => htmlspecialchars(trim($data['equipements'] ?? '')),
                'nbr_chaise' => filter_var($data['nbr_chaise'], FILTER_VALIDATE_INT),
                'nbr_bureau' => filter_var($data['nbr_bureau'], FILTER_VALIDATE_INT),
                'nbr_tableau' => filter_var($data['nbr_tableau'], FILTER_VALIDATE_INT),
                'est_climatisee' => filter_var($data['est_climatisee'], FILTER_VALIDATE_BOOLEAN),
                'nombre_prises' => filter_var($data['nombre_prises'], FILTER_VALIDATE_INT),
                'nombre_fenetres' => filter_var($data['nombre_fenetres'], FILTER_VALIDATE_INT),
                'responsable_salle' => htmlspecialchars(trim($data['responsable_salle'] ?? '')),
                'disponibilite' => htmlspecialchars(trim($data['disponibilite'] ?? '')),
                'image_salle' => htmlspecialchars(trim($data['image_salle'] ?? ''))
            ];
    
            // Vérification de l'ID
            if (!$cleanData['id_salle']) {
                return [
                    'success' => false,
                    'message' => 'Identifiant de salle invalide'
                ];
            }
    
            // Appel de la procédure stockée
            $sql = "CALL update_class(:id_salle, :nom_salle, :capacite_salle, :etage, :equipements, 
                :nbr_chaise, :nbr_bureau, :nbr_tableau, :est_climatisee, :nombre_prises, 
                :nombre_fenetres, :responsable_salle, :disponibilite, :image_salle)";
            
            $stmt = $dbh->prepare($sql);
    
            // Liaison des paramètres
            foreach ($cleanData as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : 
                        (is_bool($value) ? PDO::PARAM_BOOL : PDO::PARAM_STR);
                $stmt->bindValue(":$key", $value, $type);
            }
    
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Salle mise à jour avec succès',
                    'redirect' => true,
                    'redirect_url' => 'salle.php'
                ];
            }
    
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la salle'
            ];
    
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour'
            ];
        }
    }
// =============== Salle ================

// =============== Matiere ================
    function get_matiere_by_date_range($dbh, $start_date, $end_date) {
        try {
            if (empty($start_date) || empty($end_date)) {
                return [
                    'status' => 'error',
                    'message' => 'Les dates sont requises',
                    'code' => 400
                ];
            }

            // Nettoyage et validation des dates
            $start_date = filter_var($start_date, FILTER_SANITIZE_STRING);
            $end_date = filter_var($end_date, FILTER_SANITIZE_STRING);

            if (!$start_date || !$end_date) {
                return [
                    'status' => 'error',
                    'message' => 'Format de date invalide',
                    'code' => 400
                ];
            }

            // Conversion des dates
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            // Appel de la procédure stockée
            $sql = "CALL get_matiere_by_date_range(:start_date, :end_date)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);

            $results = $stmt->fetchAll(PDO::FETCH_OBJ);

            return [
                'status' => 'success',
                'data' => $results,
                'count' => count($results),
                'code' => 200
            ];

        } catch (Exception $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'status' => 'error',
                'message' => 'Une erreur est survenue',
                'code' => 500
            ];
        }
    }
    function get_all_matieres($dbh) {
        try {
            $sql = "CALL get_all_matieres()";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            throw new Exception('Erreur lors de la récupération des matières');
        }
    }
    function delete_matiere($dbh, $id) {
        try {
            // Validation de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                return [
                    'success' => false,
                    'message' => 'ID invalide. Opération annulée.'
                ];
            }

            // Appel de la procédure stockée
            $sql = "CALL delete_matiere(:id)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Matière bien supprimée',
                    'redirect' => true,
                    'redirect_url' => 'matiere.php'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ];

        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
            ];
        }
    }
// =============== Matiere ================

// =============== calendar ================
    function delete_seance($dbh, $id) {
        try {
            // Validation de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                return [
                    'status' => 'error',
                    'message' => 'ID invalide'
                ];
            }

            // Appel de la procédure stockée
            $stmt = $dbh->prepare("CALL delete_seance(:id)");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['success']) {
                return [
                    'status' => 'success',
                    'message' => $result['message']
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => $result['message']
                ];
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la suppression'
            ];
        }
    }
// =============== calendar ================

// =============== periode payement ================
    function get_all_periodes_paiement($dbh) {
        try {
            $sql = "CALL get_all_periodes_paiement()";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            throw new Exception('Erreur lors de la récupération des matières');
        }
    }
    function delete_periode_paiement($dbh, $id) {
        try {
            // Validation de l'ID
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                return [
                    'success' => false,
                    'message' => 'ID invalide. Opération annulée.'
                ];
            }

            // Appel de la procédure stockée
            $sql = "CALL delete_periode_paiement(:id)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Periode Paiement bien supprimée',
                    'redirect' => true,
                    'redirect_url' => 'periodes_paiement.php'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ];

        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
            ];
        }
    }

    function addPeriodePaiement($dbh, $nom_periode, $nombre_mois, $pourcentage_reduction, $description, $est_actif) {
        try {
            // Validation des entrées
            if (empty($nom_periode) || $nombre_mois <= 0 || $pourcentage_reduction < 0) {
                return [
                    'success' => false,
                    'message' => 'Veuillez remplir tous les champs correctement.'
                ];
            }
    
            // Requête SQL pour appeler la procédure stockée
            $sql = "CALL add_periode_paiement(:nom_periode, :nombre_mois, :pourcentage_reduction, :description, :est_actif)";
            $stmt = $dbh->prepare($sql);
    
            // Exécution avec les paramètres sécurisés
            $stmt->execute([
                ':nom_periode' => $nom_periode,
                ':nombre_mois' => $nombre_mois,
                ':pourcentage_reduction' => $pourcentage_reduction,
                ':description' => $description,
                ':est_actif' => $est_actif
            ]);
    
            return [
                'success' => true,
                'message' => 'Période de paiement ajoutée avec succès !',
                'redirect' => true,
                'redirect_url' => 'periodes_paiement.php'
            ];
    
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log'); // Log pour le debug
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'ajout.'
            ];
        }
    }
    
    function updatePeriodePaiement($dbh, $id_periode, $nom_periode, $nombre_mois, $pourcentage_reduction, $description, $est_actif) {
        try {
            // Validation des entrées
            if (empty($id_periode) || empty($nom_periode) || $nombre_mois <= 0 || $pourcentage_reduction < 0) {
                return [
                    'success' => false,
                    'message' => 'Veuillez remplir tous les champs correctement.'
                ];
            }
    
            // Requête SQL pour appeler la procédure stockée
            $sql = "CALL update_periode_paiement(:id_periode, :nom_periode, :nombre_mois, :pourcentage_reduction, :description, :est_actif)";
            $stmt = $dbh->prepare($sql);
    
            // Exécution avec les paramètres sécurisés
            $stmt->execute([
                ':id_periode' => $id_periode,
                ':nom_periode' => $nom_periode,
                ':nombre_mois' => $nombre_mois,
                ':pourcentage_reduction' => $pourcentage_reduction,
                ':description' => $description,
                ':est_actif' => $est_actif
            ]);
    
            return [
                'success' => true,
                'message' => 'Période de paiement mise à jour avec succès !',
                'redirect' => true,
                'redirect_url' => 'periodes_paiement.php'
            ];
    
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log'); // Log pour le debug
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }
// =============== periode payement ================

// =============== frais payement ================
    function get_all_types_frais($dbh) {
        try {
            $sql = "CALL get_all_types_frais()"; // Calling the stored procedure
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_OBJ); // Return result as an array of objects
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            throw new Exception('Erreur lors de la récupération des types de frais');
        }
    }
    function deleteTypeFrais($dbh, $id_type_frais) {
        try {
            // Prepare the SQL to call the stored procedure
            $sql = "CALL delete_type_frais(:type_frais_id)";
            $stmt = $dbh->prepare($sql);
    
            // Bind the parameter to the stored procedure
            $stmt->bindParam(':type_frais_id', $id_type_frais, PDO::PARAM_INT);
    
            // Execute the statement
            $stmt->execute();
    
            // Return success
            return [
                'success' => true,
                'message' => 'Le type de frais a été supprimé avec succès.'
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression.'
            ];
        }
    }

    function addTypeFrais($dbh, $nom_frais, $description, $est_obligatoire, $est_mensuel, $est_actif) {
        try {
            // Validation des entrées
            if (empty($nom_frais)) {
                return [
                    'success' => false,
                    'message' => 'Veuillez remplir tous les champs correctement.'
                ];
            }
    
            // Requête SQL pour appeler la procédure stockée
            $sql = "CALL add_type_frais(:nom_frais, :description, :est_obligatoire, :est_mensuel, :est_actif)";
            $stmt = $dbh->prepare($sql);
    
            // Exécution avec les paramètres sécurisés
            $stmt->execute([
                ':nom_frais' => $nom_frais,
                ':description' => $description,
                ':est_obligatoire' => $est_obligatoire,
                ':est_mensuel' => $est_mensuel,
                ':est_actif' => $est_actif
            ]);
    
            return [
                'success' => true,
                'message' => 'Type de frais ajouté avec succès !',
                'redirect' => true,
                'redirect_url' => 'frais_scolarite.php'
            ];
    
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log'); // Log pour le debug
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'ajout : ' . $e->getMessage()
            ];
        }
    }
    
    function updateTypeFrais($dbh, $id_type_frais, $nom_frais, $description, $est_obligatoire, $est_mensuel, $est_actif) {
        try {
            // Validation des entrées
            if (empty($id_type_frais) || empty($nom_frais)) {
                return [
                    'success' => false,
                    'message' => 'Veuillez remplir tous les champs correctement.'
                ];
            }
    
            // Requête SQL pour appeler la procédure stockée
            $sql = "CALL update_type_frais(:id_type_frais, :nom_frais, :description, :est_obligatoire, :est_mensuel, :est_actif)";
            $stmt = $dbh->prepare($sql);
    
            // Exécution avec les paramètres sécurisés
            $stmt->execute([
                ':id_type_frais' => $id_type_frais,
                ':nom_frais' => $nom_frais,
                ':description' => $description,
                ':est_obligatoire' => $est_obligatoire,
                ':est_mensuel' => $est_mensuel,
                ':est_actif' => $est_actif
            ]);
    
            return [
                'success' => true,
                'message' => 'Type de frais mis à jour avec succès !',
                'redirect' => true,
                'redirect_url' => 'frais_scolarite.php'
            ];
    
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log'); // Log pour le debug
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }
// =============== frais payement ================

// =============== tarif payement ================
    function addTarif($dbh, $id_type_frais, $id_niveau, $id_filiere, $montant_base, $annee_scolaire) {
        try {
            $sql = "CALL add_tarif(:id_type_frais, :id_niveau, :id_filiere, :montant_base, :annee_scolaire)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':id_type_frais' => $id_type_frais,
                ':id_niveau' => $id_niveau,
                ':id_filiere' => $id_filiere,
                ':montant_base' => $montant_base,
                ':annee_scolaire' => $annee_scolaire
            ]);
            return ['success' => true, 'message' => 'Tarif ajouté avec succès !'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout du tarif: ' . $e->getMessage()];
        }
    }

    function updateTarif($dbh, $id_tarif, $id_type_frais, $id_niveau, $id_filiere, $montant_base, $annee_scolaire) {
        try {
            $sql = "CALL update_tarif(:id_tarif, :id_type_frais, :id_niveau, :id_filiere, :montant_base, :annee_scolaire)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':id_tarif' => $id_tarif,
                ':id_type_frais' => $id_type_frais,
                ':id_niveau' => $id_niveau,
                ':id_filiere' => $id_filiere,
                ':montant_base' => $montant_base,
                ':annee_scolaire' => $annee_scolaire
            ]);
            return ['success' => true, 'message' => 'Tarif mis à jour avec succès !'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du tarif: ' . $e->getMessage()];
        }
    }

    function deleteTarif($dbh, $id_tarif) {
        try {
            $sql = "CALL delete_tarif(:id_tarif)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([':id_tarif' => $id_tarif]);
            return ['success' => true, 'message' => 'Tarif supprimé avec succès !'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la suppression du tarif: ' . $e->getMessage()];
        }
    }

    function getAllTarifs($dbh) {
        try {
            $sql = "CALL get_all_tarifs()";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }
    

// =============== tarif payement ================

// =============== payement eleves ================
    function addPaiement($dbh, $id_eleve, $id_tarif, $id_periode, $montant_base, $reduction_appliquee, $montant_final, $date_paiement, $mode_paiement, $reference_paiement, $commentaire, $id_admin, $statut_paiement) {
        try {
            $stmt = $dbh->prepare("CALL add_paiement(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id_eleve, 
                $id_tarif, 
                $id_periode, 
                $montant_base, 
                $reduction_appliquee, 
                $montant_final, 
                $date_paiement, 
                $mode_paiement, 
                $reference_paiement, 
                $commentaire, 
                $id_admin, 
                $statut_paiement
            ]);
            return ['success' => true, 'message' => 'Paiement ajouté avec succès !'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout du paiement: ' . $e->getMessage()];
        }
    }

    function updatePaiement($dbh, $id_paiement, $id_eleve, $id_tarif, $id_periode, $montant_base, $reduction_appliquee, $montant_final, $date_paiement, $date_debut_periode, $date_fin_periode, $mode_paiement, $reference_paiement, $commentaire, $id_admin, $statut_paiement) {
        try {
            $sql = "CALL update_paiement(:id_paiement, :id_eleve, :id_tarif, :id_periode, :montant_base, :reduction_appliquee, :montant_final, :date_paiement, :date_debut_periode, :date_fin_periode, :mode_paiement, :reference_paiement, :commentaire, :id_admin, :statut_paiement)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':id_paiement' => $id_paiement,
                ':id_eleve' => $id_eleve,
                ':id_tarif' => $id_tarif,
                ':id_periode' => $id_periode,
                ':montant_base' => $montant_base,
                ':reduction_appliquee' => $reduction_appliquee,
                ':montant_final' => $montant_final,
                ':date_paiement' => $date_paiement,
                ':date_debut_periode' => $date_debut_periode,
                ':date_fin_periode' => $date_fin_periode,
                ':mode_paiement' => $mode_paiement,
                ':reference_paiement' => $reference_paiement,
                ':commentaire' => $commentaire,
                ':id_admin' => $_SESSION['user'],
                ':statut_paiement' => $statut_paiement
            ]);
            return ['success' => true, 'message' => 'Paiement mis à jour avec succès !'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du paiement: ' . $e->getMessage()];
        }
    }

    function deletePaiement($dbh, $id_paiement) {
        try {
            $sql = "CALL delete_paiement(:id_paiement)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([':id_paiement' => $id_paiement]);
            return ['success' => true, 'message' => 'Paiement supprimé avec succès !'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la suppression du paiement: ' . $e->getMessage()];
        }
    }

    function getAllPaiements($dbh) {
        try {
            $sql = "CALL get_all_paiements()";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }
    function getAllPaiementsByID($dbh,$id) {
        try {
            // Validation de l'ID
            $id_paiement = filter_var($id, FILTER_VALIDATE_INT);
            if ($id_paiement === false) {
                return [
                    'success' => false,
                    'message' => 'Identifiant de salle invalide'
                ];
            }
    
            // Appel de la procédure stockée
            $sql = "CALL get_paiement_by_id(:id_paiement)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id_paiement', $id_paiement, PDO::PARAM_INT);
            $stmt->execute();
    
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'data' => $stmt->fetch(PDO::FETCH_ASSOC)
                ];
            }
    
            return [
                'success' => false,
                'message' => 'Aucune Paiement trouvée avec cet identifiant'
            ];
    
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des données'
            ];
        }
    }
    function get_payments_by_date_range($dbh, $start_date, $end_date) {
        try {
            $start_date = filter_var($start_date, FILTER_SANITIZE_STRING);
            $end_date = filter_var($end_date, FILTER_SANITIZE_STRING);
    
            if (!$start_date || !$end_date) {
                return [
                    'success' => false,
                    'message' => 'Format de date invalide'
                ];
            }
    
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
    
            $sql = "CALL get_payments_by_date_range(:start_date, :end_date)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);
    
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
    
            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '/path/to/secure_log_file.log');
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la recherche'
            ];
        }
    }
// =============== payement eleves ================