<?php
$host     = 'localhost';
$username = 'root';
$password = '';
$dbname   ='dummy_db';
$dbname2   ='pfe1';

$conn = new mysqli($host, $username, $password, $dbname);
$conn2 = new mysqli($host, $username, $password, $dbname2);
$prof = $conn2->query("SELECT * FROM enseignant");
if(!$conn){
    die("Cannot connect to the database.". $conn->error);
}