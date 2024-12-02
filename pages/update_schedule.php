<?php
  $data = json_decode(file_get_contents('php://input'), true);
  $id = $data['id'];
  $start = $data['start'];
  $end = $data['end'];

  // Connexion à la base de données
  $host = 'localhost';
  $db = 'dummy_db';
  $user = 'root';
  $password = '';
  try {
    $connexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
  }

  try {
    // Récupérer les informations de la séance actuelle
    $stmt = $connexion->prepare("SELECT salle, TIME(start_datetime) as existing_start_time, TIME(end_datetime) as existing_end_time FROM schedule_list WHERE id = ?");
    $stmt->execute([$id]);
    $current_session = $stmt->fetch(PDO::FETCH_ASSOC);
    $salle = $current_session['salle'];

    // Vérifier les chevauchements dans la même salle
    $overlap_check = $connexion->prepare("SELECT id FROM schedule_list 
      WHERE id != ? AND salle = ? AND 
      (
        (? BETWEEN start_datetime AND end_datetime) OR 
        (? BETWEEN start_datetime AND end_datetime) OR 
        (start_datetime BETWEEN ? AND ?)
      )");
    
    $new_start = date('Y-m-d', strtotime($start));
    $new_end = date('Y-m-d', strtotime($end));
    
    $overlap_check->execute([
      $id, 
      $salle, 
      $new_start . ' ' . $current_session['existing_start_time'], 
      $new_end . ' ' . $current_session['existing_end_time'],
      $new_start . ' ' . $current_session['existing_start_time'], 
      $new_end . ' ' . $current_session['existing_end_time']
    ]);

    $overlapping_session = $overlap_check->fetch();
    
    if ($overlapping_session) {
      echo json_encode([
        'status' => 'error', 
        'message' => 'Chevauchement de séance détecté dans la même salle.'
      ]);
      exit;
    }

    // Mise à jour de la séance
    $update = $connexion->prepare("UPDATE schedule_list 
      SET start_datetime = CONCAT(?, ' ', ?),
          end_datetime = CONCAT(?, ' ', ?)
      WHERE id = ?");

    $update->execute([
      $new_start,  // Nouvelle date
      $current_session['existing_start_time'],  // Heure existante
      $new_end,    // Nouvelle date
      $current_session['existing_end_time'],    // Heure existante
      $id
    ]);

    // Journalisation de la mise à jour
    $log = $connexion->prepare("INSERT INTO update_logs (schedule_id, old_start, old_end, new_start, new_end, updated_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $log->execute([
      $id, 
      $start, 
      $end, 
      $new_start . ' ' . $current_session['existing_start_time'], 
      $new_end . ' ' . $current_session['existing_end_time']
    ]);

    echo json_encode([
      'status' => 'success', 
      'message' => 'Séance mise à jour avec succès.'
    ]);

  } catch (PDOException $e) {
    echo json_encode([
      'status' => 'error', 
      'message' => 'Erreur de mise à jour : ' . $e->getMessage()
    ]);
}
?>

<?php
  // $data = json_decode(file_get_contents('php://input'), true);
  // $id = $data['id'];
  // $start = new DateTime($data['start']);
  // $end = new DateTime($data['end']);
  
  // // Connexion à la base de données
  // $host = 'localhost';
  // $db = 'dummy_db';
  // $user = 'root';
  // $password = '';
  // try {
  //   $connexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
  //   $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // } catch (PDOException $e) {
  //   echo json_encode(['error' => $e->getMessage()]);
  //   exit;
  // }
  
  // try {
  //   // Vérification de chevauchement
  //   $overlap_check = $connexion->prepare("SELECT id FROM schedule_list 
  //     WHERE id != ? AND salle = ? AND 
  //     (
  //       (? BETWEEN start_datetime AND end_datetime) OR 
  //       (? BETWEEN start_datetime AND end_datetime) OR 
  //       (start_datetime BETWEEN ? AND ?)
  //     )");
    
  //   $overlap_check->execute([
  //     $id, 
  //     $salle = $data['salle'], 
  //     $start->format('Y-m-d H:i:s'), 
  //     $end->format('Y-m-d H:i:s'),
  //     $start->format('Y-m-d H:i:s'), 
  //     $end->format('Y-m-d H:i:s')
  //   ]);
  
  //   $overlapping_session = $overlap_check->fetch();
    
  //   if ($overlapping_session) {
  //     echo json_encode([
  //       'status' => 'error', 
  //       'message' => 'Chevauchement de séance détecté dans la même salle.'
  //     ]);
  //     exit;
  //   }
  
  //   // Mise à jour de la séance
  //   $update = $connexion->prepare("UPDATE schedule_list 
  //     SET start_datetime = ?, end_datetime = ?
  //     WHERE id = ?");
  
  //   $update->execute([
  //     $start->format('Y-m-d H:i:s'),
  //     $end->format('Y-m-d H:i:s'),
  //     $id
  //   ]);
  
  //   echo json_encode([
  //     'status' => 'success', 
  //     'message' => 'Séance mise à jour avec succès.'
  //   ]);
  
  // } catch (PDOException $e) {
  //   echo json_encode([
  //     'status' => 'error', 
  //     'message' => 'Erreur de mise à jour : ' . $e->getMessage()
  //   ]);
  // }
?>