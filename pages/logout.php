<?php
session_start();
require '../includes/DatabaseConnexion.php';
date_default_timezone_set('Africa/Kampala');
$ldate=date( 'd-m-Y h:i:s A', time () );
$email=$_SESSION['email_admin'];
$sql="UPDATE userlog  SET date_uselogout=:ldate WHERE userEmail = '$email' ORDER BY id DESC LIMIT 1";
$query=$dbh->prepare($sql);
$query->bindParam(':ldate',$ldate,PDO::PARAM_STR);
$query->execute();
$_SESSION['errmsg']="You have successfully logout";
unset($_SESSION['user']);
// session_destroy(); // destroy session
header("location:../index.php"); 
?>