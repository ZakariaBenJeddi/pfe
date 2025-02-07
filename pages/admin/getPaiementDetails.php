<?php
include('../../includes/admin/controller/controller.php');

if (isset($_POST['id_paiement'])) {
  $id_paiement = intval($_POST['id_paiement']);

  try {
    $paiement = getAllPaiementsByID($dbh, $id_paiement);
    echo json_encode($paiement);
    // if ($paiement['success']) {
    //   echo json_encode($paiement['data']);
    // } else {
    //   echo json_encode(null);
    // }
  } catch (PDOException $e) {
    echo json_encode(null);
  }
} else {
  echo json_encode(null);
}
