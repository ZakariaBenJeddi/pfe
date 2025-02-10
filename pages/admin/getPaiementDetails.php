<?php
include('../../includes/admin/controller/controller.php');

if (isset($_POST['id_paiement'])) {
  $id_paiement = intval($_POST['id_paiement']);

  try {
    $paiement = getAllPaiementsByID($dbh, $id_paiement);
    echo json_encode($paiement);
  } catch (PDOException $e) {
    echo json_encode(null);
  }
} else {
  echo json_encode(null);
}
