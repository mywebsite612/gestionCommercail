<?php 
include_once "connexion.php";

if(isset($_POST["user"]) && isset($_POST["password"])) {
    $user = $_POST["user"];
    $pass = $_POST["password"];

    $stmt = $conn->prepare("UPDATE login SET password=? WHERE user=?");
    $stmt->bind_param("ss", $pass, $user);

    echo ($stmt->execute()) ? "OK" : "ERROR";
}
?>
