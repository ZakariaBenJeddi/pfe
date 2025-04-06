<?php
include('../../includes/admin/controller/controller.php');
session_start();

if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}
if (!isset($_GET['id'])) {
  echo json_encode([
    'status' => 'error',
    'message' => 'ID non défini'
  ]);
  exit;
}

$result = delete_seance($dbh, $_GET['id']);
echo json_encode($result);
