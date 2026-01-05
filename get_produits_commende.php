<?php
include 'connexion.php';

$id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT cp.Ref_Produit, cp.Quantite
    FROM commende_produit cp
    WHERE cp.Id_Commende = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
