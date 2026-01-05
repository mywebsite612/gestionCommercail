<?php
include_once 'connexion.php';

// Ajouter un devis
if (isset($_POST['addDevis'])) {

    $numDevis    = $_POST['numDevis'];
    $clientDevis = $_POST['clientDevis'];
    $dateDevis   = $_POST['dateDevis'];
    $remise = $_POST['remise'] ?? 0;


    // 1️⃣ Vérifier si le devis existe déjà
    $checkStmt = $conn->prepare(
        "SELECT id_devis FROM devis WHERE id_devis = ?"
    );
    $checkStmt->bind_param("s", $numDevis);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // Devis déjà existant
        $errorMessage = "❌ Ce numéro de devis existe déjà.";
        $checkStmt->close();
    } else {

        $checkStmt->close();

        // 2️⃣ Ajouter le devis
        $stmt = $conn->prepare(
           "INSERT INTO devis (id_devis, ICE, dateDevis, remise) VALUES (?, ?, ?, ?)"

        );
       $stmt->bind_param("sssd", $numDevis, $clientDevis, $dateDevis, $remise);

        if (!$stmt->execute()) {
            $errorMessage = "Erreur lors de l'ajout du devis : " . $stmt->error;
            $stmt->close();
            exit;
        }
        $stmt->close();

        // 3️⃣ Enregistrer les produits du devis
        if (!empty($_POST['produits'])) {

            $produits  = $_POST['produits'];
            $quantites = $_POST['quantites'];

            $stmt = $conn->prepare(
                "INSERT INTO devis_produit (id_devis, Ref_Produit, quantite)
                 VALUES (?, ?, ?)"
            );

            for ($i = 0; $i < count($produits); $i++) {
                if (!empty($produits[$i]) && $quantites[$i] > 0) {
                    $stmt->bind_param(
                        "ssi",
                        $numDevis,
                        $produits[$i],
                        $quantites[$i]
                    );
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        header("Location: devis.php");
        exit();
    }
}


//suprimer
if (isset($_GET['deleteDevis'])) {
    $devisId = $_GET['deleteDevis'];

    $stmt = $conn->prepare("DELETE FROM devis WHERE id_devis = ?");
    $stmt->bind_param("s", $devisId);
    $stmt->execute();
    $stmt->close();

    header("Location: devis.php");
    exit();
}

//modifier
$editMode = false;
$editDevis = null;
$editProduits = [];

if (isset($_GET['editDevis'])) {
    $editMode = true;
    $idEdit = $_GET['editDevis'];

    $stmt = $conn->prepare("SELECT * FROM devis WHERE id_devis = ?");
    $stmt->bind_param("s", $idEdit);
    $stmt->execute();
    $editDevis = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Récupérer les produits du devis en édition
    $stmt = $conn->prepare("
        SELECT dp.Ref_Produit, dp.quantite
        FROM devis_produit dp
        WHERE dp.id_devis = ?
    ");
    $stmt->bind_param("s", $idEdit);
    $stmt->execute();
    $editProduits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if (isset($_POST['updateDevis'])) {
    $idDevis     = $_POST['idDevis'];
    $clientDevis = $_POST['clientDevis'];
    $dateDevis   = $_POST['dateDevis'];
    $remise = $_POST['remise'];


    // 1️⃣ update devis
    $stmt = $conn->prepare(
        "UPDATE devis SET ICE = ?, dateDevis = ?, remise = ? WHERE id_devis = ?"
    );
   $stmt->bind_param("ssds", $clientDevis, $dateDevis, $remise, $idDevis);
    $stmt->execute();
    $stmt->close();

    // 2️⃣ supprimer anciens produits
    $stmt = $conn->prepare("DELETE FROM devis_produit WHERE id_devis = ?");
    $stmt->bind_param("s", $idDevis);
    $stmt->execute();
    $stmt->close();

    // 3️⃣ réinsérer les produits restants
    if (!empty($_POST['produits'])) {
        $stmt = $conn->prepare("
            INSERT INTO devis_produit (id_devis, Ref_Produit, quantite)
            VALUES (?, ?, ?)
        ");

        for ($i = 0; $i < count($_POST['produits']); $i++) {
            if ($_POST['produits'][$i] != "" && $_POST['quantites'][$i] > 0) {
                $stmt->bind_param(
                    "ssi",
                    $idDevis,
                    $_POST['produits'][$i],
                    $_POST['quantites'][$i]
                );
                $stmt->execute();
            }
        }
        $stmt->close();
    }

    header("Location: devis.php");
    exit();
}


  // LISTE DES DEVIS

$devisList = $conn->query("SELECT * FROM devis ORDER BY id_devis ASC");


 //  LISTE DES CLIENTS

$clients = $conn->query("SELECT ICE, Nom FROM client ORDER BY Nom ASC");

//  LISTE DES PRODUITS 
$produitsList = $conn->query("SELECT Ref_Produit, Nom, Designation FROM produit ORDER BY Nom ASC");

// GÉNÉRER AUTOMATIQUEMENT LE PROCHAIN NUMÉRO DE DEVIS
$nextDevisId = 10001;

$res = $conn->query("SELECT MAX(id_devis) AS max_id FROM devis");
if ($row = $res->fetch_assoc()) {
    if (!empty($row['max_id'])) {
        $nextDevisId = $row['max_id'] + 1;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="website icon" href="logo.png">
<title>Devis - Gestion</title>
<link rel="stylesheet" href="devis.css">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>

<div class="header-actions">
    <a href="facture.php" class="btn-back">← Retour</a>
    <div class="right-actions">
        <button onclick="window.print()" class="btn-print">🖨️ Imprimer</button>
        <button id="openModalBt">➕ Ajouter un devis</button>
    </div>
</div>

<div id="devisModal" class="modal">
    <div class="modal-content">
        <span class="closeDevis">&times;</span>
        <h2><?= $editMode ? 'Modifier' : 'Ajouter' ?> un Devis</h2>

        <form action="devis.php" method="POST">
            <?php if ($editMode): ?>
                <input type="hidden" name="idDevis" value="<?= $editDevis['id_devis'] ?>">
            <?php endif; ?>
<label>N° Devis :</label>
<input type="text"
       name="numDevis"
       id="numDevis"
       value="<?= $editMode ? $editDevis['id_devis'] : $nextDevisId ?>"
       <?= $editMode ? 'readonly' : 'readonly' ?>
><br><br>

            <label>Client (ICE) :</label>
            <select name="clientDevis" required>
                <option value="">-- choisir un client --</option>
                <?php
                $clients = $conn->query("SELECT ICE, Nom FROM client ORDER BY Nom ASC");
                while ($c = $clients->fetch_assoc()):
                ?>
                    <option value="<?= $c['ICE'] ?>"
                        <?= ($editMode && $c['ICE'] == $editDevis['ICE']) ? 'selected' : '' ?>>
                        <?= $c['ICE'] ?> - <?= $c['Nom'] ?>
                    </option>
                <?php endwhile; ?>
            </select><br><br>

            <h3>Produits</h3>
            <div id="products">
                <?php if ($editMode && !empty($editProduits)): ?>
                    <?php foreach ($editProduits as $ep): ?>
                        <div class="product-line">
                            <select name="produits[]">
                                <option value="">-- Produit --</option>
                                <?php
                                $prods = $conn->query("SELECT Ref_Produit, Nom, Designation FROM produit");
                                while ($p = $prods->fetch_assoc()):
                                ?>
                                    <option value="<?= $p['Ref_Produit'] ?>"
                                        <?= ($p['Ref_Produit'] == $ep['Ref_Produit']) ? 'selected' : '' ?>>
                                        <?= $p['Nom'] ?> - <?= $p['Designation'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <input type="number" name="quantites[]" min="1"
                                   value="<?= $ep['quantite'] ?>">

                            <button type="button" onclick="removeProduct(this)">❌</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="product-line">
                        <select name="produits[]">
                            <option value="">-- Produit --</option>
                            <?php
                            $prods = $conn->query("SELECT Ref_Produit, Nom, Designation FROM produit");
                            while ($p = $prods->fetch_assoc()):
                            ?>
                                <option value="<?= $p['Ref_Produit'] ?>">
                                    <?= $p['Nom'] ?> - <?= $p['Designation'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>

                        <input type="number" name="quantites[]" min="1" placeholder="Qté">
                        <button type="button" onclick="removeProduct(this)">❌</button>
                    </div>
                <?php endif; ?>
            </div>


            <button type="button" onclick="addProduct()">➕ Ajouter produit</button><br><br>

            <label>Remise (%) :</label>
<input type="number"
       name="remise"
       min="0"
       max="100"
       step="0.01"
       value="<?= $editMode ? $editDevis['remise'] : 0 ?>"
       placeholder="Ex: 5">
<br><br>

            <label>Date devis :</label>
            <input type="date" name="dateDevis"
                   value="<?= $editMode ? $editDevis['dateDevis'] : '' ?>"
                   required><br><br>

            <?php if ($editMode): ?>
                <button type="submit" name="updateDevis">💾 Modifier</button>
            <?php else: ?>
                <button type="submit" name="addDevis">➕ Ajouter</button>
            <?php endif; ?>

            
        </form>
    </div>
</div>

<h2>Liste des devis</h2>

<table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>N° Devis</th>
            <th>ICE Client</th>
            <th>Produits</th>
            <th>Date devis</th>
            <th>Remise</th>
            <th>Total TTC</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($dv = $devisList->fetch_assoc()): 
            // Calculer le total pour chaque devis
            $details = $conn->prepare("
                SELECT 
                    p.Ref_Produit,
                    p.Nom,
                    p.Designation,
                    p.Prix AS PU,
                    dp.quantite,
                    (p.Prix * dp.quantite) AS montant_ht
                FROM devis_produit dp
                JOIN produit p ON dp.Ref_Produit = p.Ref_Produit
                WHERE dp.id_devis = ?
            ");
            $details->bind_param("s", $dv['id_devis']);
            $details->execute();
            $rows = $details->get_result();
            
            $totalHT = 0;
            $produits = [];
            
            while ($r = $rows->fetch_assoc()) {
                $totalHT += $r['montant_ht'];
                $produits[] = $r;
            }
            
           $remisePourcent = $dv['remise'];
$montantRemise = $totalHT * ($remisePourcent / 100);
$totalHTRemise = $totalHT - $montantRemise;

$tva = $totalHTRemise * 0.20;
$ttc = $totalHTRemise + $tva;

        ?>
        <tr>
            <td><?= $dv['id_devis'] ?></td>
            <td><?= $dv['ICE'] ?></td>
            <td>
                <?php 
                if (!empty($produits)) {
                    foreach ($produits as $prod) {
                        echo "• " . $prod['Nom'] . " (" . $prod['Designation'] . ") ";
                        echo "× " . $prod['quantite'] . "<br>";
                    }
                } else {
                    echo "Aucun produit";
                }
                ?>
            </td>
            <td><?= $dv['dateDevis'] ?></td>
            <td><?= $dv['remise'] ?> %</td>
            <td align="right"><?= number_format($ttc, 2, ',', ' ') ?> MAD</td>
            <td>
                <a href="devis.php?editDevis=<?= $dv['id_devis'] ?>">✏️ Modifier</a> 
                |&nbsp;&nbsp;
                <a href="devis.php?deleteDevis=<?= $dv['id_devis'] ?>"
                   onclick="return confirm('Supprimer ce devis ?')">
                   🗑 Supprimer
                </a>
                 <a href="voir.php?id=<?= urlencode($dv['id_devis']) ?>"> 👁 Voir Devis </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
var devisModal = document.getElementById("devisModal");
var btnDevis = document.getElementById("openModalBt");
var closeDevis = document.getElementsByClassName("closeDevis")[0];

btnDevis.onclick = function () {
    devisModal.style.display = "block";
}

closeDevis.onclick = function () {
    devisModal.style.display = "none";
    window.location.href = "devis.php";
}

window.onclick = function (event) {
    if (event.target == devisModal) {
        devisModal.style.display = "none";
        window.location.href = "devis.php";
    }
}

function addProduct() {
    const div = document.createElement("div");
    div.className = "product-line";
    div.innerHTML = `
        <select name="produits[]">
            <option value="">-- Produit --</option>
            <?php
            $prods = $conn->query("SELECT Ref_Produit, Nom, Designation FROM produit");
            while ($p = $prods->fetch_assoc()):
            ?>
                <option value="<?= $p['Ref_Produit'] ?>">
                    <?= $p['Nom'] ?> - <?= $p['Designation'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="number" name="quantites[]" min="1" placeholder="Qté">
        <button type="button" onclick="removeProduct(this)">❌</button>
    `;
    document.getElementById("products").appendChild(div);
}

function removeProduct(btn) {
    btn.parentElement.remove();
}
var autoDevisId = "<?= $nextDevisId ?>";

btnDevis.onclick = function () {
    devisModal.style.display = "block";

    // seulement en mode ajout
    if (!<?= json_encode($editMode) ?>) {
        document.getElementById("numDevis").value = autoDevisId;
    }
};

<?php if ($editMode): ?>
    document.getElementById("devisModal").style.display = "block";
<?php endif; ?>
</script>

</body>
</html>

<?php
$conn->close();
?>