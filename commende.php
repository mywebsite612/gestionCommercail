<?php
include_once 'connexion.php';
$produits = $conn->query(
    "SELECT Ref_Produit, Nom, prix_achat FROM produit ORDER BY Nom"
);


$successMessage = "";
$errorMessage = "";

// SUPPRIMER
if (isset($_GET['deleteCommande'])) {

    $id = $_GET['deleteCommande'];

    $stmt = $conn->prepare("DELETE FROM commende WHERE Id_Commende = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: commende.php");
    exit();
}

// MODIFIER
$editMode = false;
$editCommande = null;
$editProduits = [];

if (isset($_GET['editCommande'])) {

    $editMode = true;
    $idEdit = $_GET['editCommande'];

    // commande
    $stmt = $conn->prepare("SELECT * FROM commende WHERE Id_Commende = ?");
    $stmt->bind_param("s", $idEdit);
    $stmt->execute();
    $editCommande = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // produits
    $stmt = $conn->prepare(
        "SELECT Ref_Produit, Quantite
         FROM commende_produit
         WHERE Id_Commende = ?"
    );
    $stmt->bind_param("s", $idEdit);
    $stmt->execute();
    $editProduits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}


/* =======================
   AJOUT COMMANDE
======================= */
if (isset($_POST['addCommande'])) {

    $idCommande  = $_POST['idCommande'];
    $fournisseur = $_POST['fournisseur'];
    $date        = $_POST['dateCommande'];

    // 1️⃣ vérifier si la commande existe
    $check = $conn->prepare(
        "SELECT Id_Commende FROM commende WHERE Id_Commende = ?"
    );
    $check->bind_param("s", $idCommande);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $errorMessage = "❌ Cette commande existe déjà.";
        $check->close();
    } else {

        $check->close();

        // 2️⃣ insérer la commande
        $stmt = $conn->prepare(
            "INSERT INTO commende (Id_Commende, ID_Forrnisseur, DateCommende)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $idCommande, $fournisseur, $date);

        if (!$stmt->execute()) {
            die("Erreur commande : " . $stmt->error);
        }
        $stmt->close();

        // 3️⃣ insérer les produits
        if (!empty($_POST['produits'])) {

            $stmt = $conn->prepare(
                "INSERT INTO commende_produit (Id_Commende, Ref_Produit, Quantite)
                 VALUES (?, ?, ?)"
            );

            for ($i = 0; $i < count($_POST['produits']); $i++) {
                if ($_POST['produits'][$i] != "" && $_POST['quantites'][$i] > 0) {
                    $stmt->bind_param(
                        "ssi",
                        $idCommande,
                        $_POST['produits'][$i],
                        $_POST['quantites'][$i]
                    );
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        header("Location: commende.php");
        exit();
    }
}


$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $like = "%$search%";
    $sql = "SELECT * FROM commende 
            WHERE Id_Commende = ?
            OR ID_Forrnisseur LIKE ?
            OR DateCommende LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM commende ORDER BY Id_Commende DESC");
}

$fournisseurs = $conn->query("SELECT ID_Forrnisseur , Nom FROM fournisseurs ORDER BY Nom ASC");


// GÉNÉRER AUTOMATIQUEMENT LE PROCHAIN ID COMMANDE
$nextCommandeId = 10001;

$resultId = $conn->query("SELECT MAX(Id_Commende) AS max_id FROM commende");
if ($rowId = $resultId->fetch_assoc()) {
    if (!empty($rowId['max_id'])) {
        $nextCommandeId = $rowId['max_id'] + 1;
    }
}

$editMode = false;
$editCommande = null;
$editProduits = [];

if (isset($_GET['editCommande'])) {
    $editMode = true;
    $idEdit = $_GET['editCommande'];

    // commande
    $stmt = $conn->prepare("SELECT * FROM commende WHERE Id_Commende = ?");
    $stmt->bind_param("s", $idEdit);
    $stmt->execute();
    $editCommande = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // produits commande
    $stmt = $conn->prepare("
        SELECT Ref_Produit, Quantite
        FROM commende_produit
        WHERE Id_Commende = ?
    ");
    $stmt->bind_param("s", $idEdit);
    $stmt->execute();
    $editProduits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// metrre a jour commande
if (isset($_POST['updateCommande'])) {

    $idCommande  = $_POST['idCommande'];
    $fournisseur = $_POST['fournisseur'];
    $date        = $_POST['dateCommande'];

    // 1️⃣ update commande
    $stmt = $conn->prepare(
        "UPDATE commende
         SET ID_Forrnisseur = ?, DateCommende = ?
         WHERE Id_Commende = ?"
    );
    $stmt->bind_param("sss", $fournisseur, $date, $idCommande);
    $stmt->execute();
    $stmt->close();

    // 2️⃣ supprimer anciens produits
    $stmt = $conn->prepare(
        "DELETE FROM commende_produit WHERE Id_Commende = ?"
    );
    $stmt->bind_param("s", $idCommande);
    $stmt->execute();
    $stmt->close();

    // 3️⃣ réinsérer produits
    if (!empty($_POST['produits'])) {

        $stmt = $conn->prepare(
            "INSERT INTO commende_produit (Id_Commende, Ref_Produit, Quantite)
             VALUES (?, ?, ?)"
        );

        for ($i = 0; $i < count($_POST['produits']); $i++) {
            if ($_POST['produits'][$i] != "" && $_POST['quantites'][$i] > 0) {
                $stmt->bind_param(
                    "ssi",
                    $idCommande,
                    $_POST['produits'][$i],
                    $_POST['quantites'][$i]
                );
                $stmt->execute();
            }
        }
        $stmt->close();
    }

    header("Location: commende.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="website icon" href="logo.png">
<title>commende - Gestion</title>
<link rel="stylesheet" href="fournisseur.css">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
<style>/* MODAL STYLES */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 25px;
    border-radius: 10px;
    width: 90%;
    max-width: 700px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    border: 1px solid #ddd;
    animation: slideIn 0.3s ease;
}

.closeCommande {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s;
}

.closeCommande:hover {
    color: #ff4444;
}

/* FORM STYLES */
form h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

form h3 {
    color: #34495e;
    margin: 20px 0 15px 0;
}

label {
    display: inline-block;
    width: 150px;
    font-weight: bold;
    color: #333;
    margin-bottom: 8px;
}

input[type="text"],
input[type="number"],
input[type="date"],
select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 300px;
    font-size: 14px;
    transition: border 0.3s;
    background-color: #fff;
}

input[type="text"]:focus,
input[type="number"]:focus,
input[type="date"]:focus,
select:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
}

input[readonly] {
    background-color: #f8f9fa;
    color: #666;
}

/* PRODUCT LINES */
.product-line {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
    padding: 10px;
    background-color: #f9f9f9;
    border-radius: 5px;
    border-left: 4px solid #3498db;
}

.product-line select {
    flex: 3;
    width: auto;
    min-width: 200px;
}

.product-line input {
    flex: 1;
    width: auto;
    min-width: 100px;
}

.product-line button {
    background-color: #e74c3c;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 8px 12px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.product-line button:hover {
    background-color: #c0392b;
}

/* BUTTONS */
button[type="button"],
button[type="submit"] {
    padding: 12px 25px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: background-color 0.3s;
    margin: 5px;
}

button[type="button"]:hover,
button[type="submit"]:hover {
    background-color: #2980b9;
}

button[type="button"] {
    background-color: #2ecc71;
}

button[type="button"]:hover {
    background-color: #27ae60;
}

/* TABLE STYLES */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    background-color: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

thead {
    background: linear-gradient(135deg,  #667eea,  #764ba2);
    color: white;
}

thead th {
    padding: 15px;
    text-align: left;
    font-weight: bold;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

tbody tr {
    border-bottom: 1px solid #eee;
    transition: background-color 0.3s;
}

tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

tbody tr:hover {
    background-color: #f0f7ff;
}

tbody td {
    padding: 12px 15px;
    color: #333;
    font-size: 14px;
}

/* ACTION LINKS */
td a {
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s;
    display: inline-block;
}

td a:first-child {
    color: #3498db;
    border: 1px solid #3498db;
}

td a:first-child:hover {
    background-color: #667eea;
    color: white;
}

td a:last-child {
    color: #e74c3c;
    border: 1px solid #e74c3c;
}

td a:last-child:hover {
    background-color: #e74c3c;
    color: white;
}

/* ANIMATIONS */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 10% auto;
        padding: 15px;
    }
    
    label {
        display: block;
        width: 100%;
        margin-bottom: 5px;
    }
    
    input[type="text"],
    input[type="number"],
    input[type="date"],
    select {
        width: 100%;
        max-width: 100%;
    }
    
    .product-line {
        flex-direction: column;
        gap: 8px;
    }
    
    .product-line select,
    .product-line input {
        width: 100%;
    }
    
    table {
        display: block;
        overflow-x: auto;
    }
    
    thead th,
    tbody td {
        padding: 10px 8px;
        font-size: 13px;
    }
}

/* STATUS INDICATORS */
[readonly] {
    cursor: not-allowed;
}

select:required:invalid {
    color: #999;
    border-color: #ff9999;
}

</style>


<?php if (!empty($successMessage)) : ?>
    <div id="message" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
        <?= $successMessage ?>
    </div>
<?php endif; ?>

<?php if (!empty($errorMessage)) : ?>
    <div id="message" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
        <?= $errorMessage ?>
    </div>
<?php endif; ?>

<script>
setTimeout(function() {
    var messageDiv = document.getElementById('message');
    if (messageDiv) { messageDiv.style.display = 'none'; }
}, 3000);
</script>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-boxes"></i> <span>GestionPro</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="premierpage.php" class="nav-item"><i class="fas fa-home"></i> Tableau de Bord</a>
            <a href="produit.php" class="nav-item active"><i class="fas fa-box"></i> Produits</a>
            <a href="client.php" class="nav-item"><i class="fas fa-users"></i> Clients</a>
            <a href="fournisseur.php" class="nav-item"><i class="fas fa-truck"></i> Fournisseurs</a>
            <a href="commende.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Commandes</a>
            <a href="facture.php" class="nav-item"><i class="fas fa-file-alt"></i> Facture & Devis</a>
               <a href="devis.php" class="nav-item"><i class="fas fa-file-alt"></i>Devis</a>
        </nav>
        <div class="sidebar-footer">
            <a href="user.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header class="header">
             <div class="header-actions">
                DATA SOFTWARE
                <div class="user-profile">
                    <img src="logo.png" alt="Profile">
                </div>
            </div>
             <div class="search-bar">
                <i class="fas fa-search"></i>
                <form method="get">
    <input type="text" name="search"
           placeholder="Rechercher par Id, Fournisseur ou Date"
           value="<?= htmlspecialchars($search) ?>">
    <button type="submit" style=" padding: 10px 95px;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    background: #667eea;
    color: white;">Rechercher</button>
</form>

           
        </header>

        <header class="header">
            <h1>Les Commandes
            </h1>
            <button id="openModalBtn" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau Commande</button>
        </header>


<!-- MODAL COMMANDE -->
<div id="commandeModal" class="modal">
    <div class="modal-content">
        <span class="closeCommande">&times;</span>

        <h2><?= $editMode ? 'Modifier' : 'Ajouter' ?> une Commande</h2>
        <br>

        <form action="commende.php" method="POST">

            <?php if ($editMode): ?>
                <input type="hidden" name="updateCommande" value="1">
                <input type="hidden" name="idCommande" value="<?= $editCommande['Id_Commende'] ?>">
            <?php endif; ?>

            <label>N° Commande :</label>
            <input type="text"
                   name="idCommande"
                   value="<?= $editMode ? $editCommande['Id_Commende'] : $nextCommandeId ?>"
                   readonly>
            <br><br>

            <label>Fournisseur :</label>
            <select name="fournisseur" required>
                <option value="">-- Choisir --</option>
                <?php while ($f = $fournisseurs->fetch_assoc()): ?>
                    <option value="<?= $f['ID_Forrnisseur'] ?>"
                        <?= ($editMode && $f['ID_Forrnisseur'] == $editCommande['ID_Forrnisseur']) ? 'selected' : '' ?>>
                        <?= $f['ID_Forrnisseur'] ?> - <?= $f['Nom'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <br><br>

            <h3>Produits</h3>

            <div id="products">

                <?php if ($editMode && !empty($editProduits)): ?>
                    <?php foreach ($editProduits as $ep): ?>
                        <div class="product-line">
                            <select name="produits[]">
                                <option value="">-- Produit --</option>
                                <?php
                                $prods = $conn->query("SELECT Ref_Produit, Nom FROM produit");
                                while ($p = $prods->fetch_assoc()):
                                ?>
                                    <option value="<?= $p['Ref_Produit'] ?>"
                                        <?= ($p['Ref_Produit'] == $ep['Ref_Produit']) ? 'selected' : '' ?>>
                                        <?= $p['Nom'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <input type="number"
                                   name="quantites[]"
                                   min="1"
                                   value="<?= $ep['Quantite'] ?>">

                            <button type="button" onclick="removeProduct(this)">❌</button>
                        </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <div class="product-line">
                        <select name="produits[]">
                            <option value="">-- Produit --</option>
                            <?php
                            $prods = $conn->query("SELECT Ref_Produit, Nom FROM produit");
                            while ($p = $prods->fetch_assoc()):
                            ?>
                                <option value="<?= $p['Ref_Produit'] ?>">
                                    <?= $p['Nom'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>

                        <input type="number" name="quantites[]" min="1" placeholder="Qté">
                        <button type="button" onclick="removeProduct(this)">❌</button>
                    </div>
                <?php endif; ?>

            </div>

            <button type="button" onclick="addProduct()">➕ Ajouter produit</button>
            <br><br>

            <label>Date commande :</label>
            <input type="date"
                   name="dateCommande"
                   value="<?= $editMode ? $editCommande['DateCommende'] : '' ?>"
                   required>
            <br><br>

            <?php if ($editMode): ?>
                <button type="submit">💾 Modifier</button>
            <?php else: ?>
                <button type="submit" name="addCommande">➕ Ajouter</button>
            <?php endif; ?>

        </form>
    </div>
</div>

<!-- LISTE DES COMMANDES -->
   <table border="1" cellpadding="8">
<thead>
<tr>
    <th>N°</th>
    <th>Fournisseur</th>
    <th>Produits</th>
    <th>Date</th>
    <th><Center>Action</center></th>
</tr>
</thead>
<tbody>

<?php
$cmds = $conn->query("SELECT * FROM commende ORDER BY Id_Commende DESC");

while ($c = $cmds->fetch_assoc()):
    $stmt = $conn->prepare("
      SELECT p.Nom, p.prix_achat, cp.Quantite
FROM commende_produit cp
JOIN produit p ON cp.Ref_Produit = p.Ref_Produit
WHERE cp.Id_Commende = ?

    ");
    $stmt->bind_param("s", $c['Id_Commende']);
    $stmt->execute();
    $rows = $stmt->get_result();
?>
<tr>
    <td><?= $c['Id_Commende'] ?></td>
    <td><?= $c['ID_Forrnisseur'] ?></td>
    <td>
        <?php
        if ($rows->num_rows > 0) {
            while ($r = $rows->fetch_assoc()) {
              echo "• {$r['Nom']} × {$r['Quantite']} (PU Achat : {$r['prix_achat']})<br>";
            }
        } else {
            echo "Aucun produit";
        }
        ?>
    </td>
    <td><?= $c['DateCommende'] ?></td>
    <td>
        <center>
        <a href="commende.php?editCommande=<?= $c['Id_Commende'] ?>">✏️ Modifier</a> 
           <a href="BC.php?id=<?= $c['Id_Commende'] ?>" target="_blank">📜 Voir BC</a>
            <a href="commende.php?deleteCommande=<?= $c['Id_Commende'] ?>"
           onclick="return confirm('Supprimer cette commande ?')">🗑 Supprimer</a>
           </center> 
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<script>
var commandeModal = document.getElementById("commandeModal");
var btnCommande = document.getElementById("openModalBtn");
var closeCommande = document.querySelector(".closeCommande");

btnCommande.onclick = () => commandeModal.style.display = "block";

closeCommande.onclick = () => {
    commandeModal.style.display = "none";
    window.location.href = "commende.php";
};

window.onclick = e => {
    if (e.target == commandeModal) {
        commandeModal.style.display = "none";
        window.location.href = "commende.php";
    }
};

function addProduct() {
    const div = document.createElement("div");
    div.className = "product-line";
    div.innerHTML = `
        <select name="produits[]">
            <option value="">-- Produit --</option>
            <?php
            $prods = $conn->query("SELECT Ref_Produit, Nom FROM produit");
            while ($p = $prods->fetch_assoc()):
            ?>
                <option value="<?= $p['Ref_Produit'] ?>">
                    <?= $p['Nom'] ?>
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

<?php if ($editMode): ?>
    document.getElementById("commandeModal").style.display = "block";
<?php endif; ?>


// Fonctions pour gérer le modal
function openModal() {
    document.getElementById('commandeModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('commandeModal').style.display = 'none';
}

// Fermer le modal en cliquant sur la croix
document.querySelector('.closeCommande').onclick = closeModal;

// Fermer le modal en cliquant en dehors
window.onclick = function(event) {
    const modal = document.getElementById('commandeModal');
    if (event.target == modal) {
        closeModal();
    }
}

// Ajouter une ligne produit
function addProduct() {
    const container = document.getElementById('products');
    const newLine = document.querySelector('.product-line').cloneNode(true);
    
    // Réinitialiser les valeurs
    newLine.querySelector('select').selectedIndex = 0;
    newLine.querySelector('input').value = '';
    
    container.appendChild(newLine);
}

// Supprimer une ligne produit
function removeProduct(button) {
    const lines = document.querySelectorAll('.product-line');
    if (lines.length > 1) {
        button.closest('.product-line').remove();
    } else {
        alert('Au moins un produit est requis');
    }
}

</script>

</body>
</html>
