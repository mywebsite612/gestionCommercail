<?php
// verify_user.php
// renvoie strictement "OK" ou "NO"

include_once "connexion.php";

// Empêcher tout output additionnel (éviter BOM/espaces)
ob_clean();

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_POST['user'])) {
    echo "NO";
    exit;
}

$user = trim($_POST['user']);

if ($user === '') {
    echo "NO";
    exit;
}


$tablesToCheck = ['login', 'users'];

$found = false;
foreach ($tablesToCheck as $tbl) {
    // Prépare la requête en s'assurant que la table existe (simple tentative)
    $sql = "SELECT 1 FROM `$tbl` WHERE `user` = ? LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $user);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $found = true;
            }
            $res && $res->free();
        }
        $stmt->close();
    }
    if ($found) break;
}

echo $found ? "OK" : "NO";
exit;
?>
