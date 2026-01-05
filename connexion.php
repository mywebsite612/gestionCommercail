<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_commerc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}
?>
