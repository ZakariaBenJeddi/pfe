<?php
$host     = 'localhost';
$username = 'root';
$password = '';
$dbname   ='dummy_db';
$dbname2   ='emploi_du_temps_2acc';

$conn = new mysqli($host, $username, $password, $dbname);
$conn2 = new mysqli($host, $username, $password, $dbname2);
$matieres = $conn2->query("SELECT * FROM matieres2");
if(!$conn){
    die("Cannot connect to the database.". $conn->error);
}