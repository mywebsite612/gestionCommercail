<?php
include_once 'connexion.php';


// Supprimer un produit
if (isset($_GET['delete'])) {
    $refProduitToDelete = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM produit WHERE Ref_Produit= ?");
    $stmt->bind_param("s", $refProduitToDelete);
    $stmt->execute();
    $stmt->close();
    header("Location: produit.php");
    exit;
}

// Modifier un produit
if (isset($_POST['update'])) {
    $oldRefProduit = $_POST['oldRefProduit']; // ancienne référence
    $refProduit = $_POST['refProduit'];      // nouvelle référence
    $nomProduit = $_POST['nomProduit'];
    $designationProduit = $_POST['designationProduit'];
    $prix = $_POST['prix'];
    $prixAchat = $_POST['prixAchat'];


    $stmt = $conn->prepare("UPDATE produit SET Ref_Produit=?, Nom=?, Designation=?, prix=?, prix_achat=? WHERE Ref_Produit=?");
    $stmt->bind_param("ssssss", $refProduit, $nomProduit, $designationProduit, $prix, $prixAchat, $oldRefProduit);
    if ($stmt->execute()) {
        $successMessage = "Produit mis à jour avec succès.";
    } else {
        $errorMessage = "Erreur: " . $stmt->error;
    }
    $stmt->close();
}


// Ajouter un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update'])) {

    $refProduit = $_POST['refProduit'];
    $nomProduit = $_POST['nomProduit'];
    $designationProduit = $_POST['designationProduit'];
    $prix = $_POST['prix'];
    $prixAchat = $_POST['prixAchat'];

    // 1️⃣ Vérifier si la référence produit existe déjà
    $checkStmt = $conn->prepare("SELECT Ref_Produit FROM produit WHERE Ref_Produit = ?");
    $checkStmt->bind_param("s", $refProduit);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // Référence déjà existante
        $errorMessage = "❌ Ce produit existe déjà.";
    } else {
        // 2️⃣ Ajouter le produit
        $stmt = $conn->prepare(
            "INSERT INTO produit (Ref_Produit, Nom, Designation, prix, prix_achat) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssd", $refProduit, $nomProduit, $designationProduit, $prix, $prixAchat);

        if ($stmt->execute()) {
            $successMessage = "✅ Nouveau produit ajouté avec succès.";
        } else {
            $errorMessage = "Erreur : " . $stmt->error;
        }

        $stmt->close();
    }

    $checkStmt->close();
}


//sreach produit
$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $like = "%$search%";
    $sql = "SELECT * FROM produit
            WHERE Ref_Produit = ?
            OR Nom LIKE ?
            ORDER BY Ref_Produit ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM produit ORDER BY Ref_Produit ASC");
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="website icon" href="logo.png">
<title>Produits & Stock - Gestion</title>
<link rel="stylesheet" href="fournisseur.css">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

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
            <a href="devis.php" class="nav-item"><i class="fas fa-file-alt"></i> Devis</a>
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
           placeholder="Rechercher Ref Produit ou Nom"
           value="<?= htmlspecialchars($search) ?>">
    <button type="submit" style=" padding: 10px 110px;
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
            <h1>Produits & Stock</h1>
            <button id="openModalBtn" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau produit</button>
        </header>

        <!-- Modal Ajouter/Modifier produit -->
        <div id="productModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="modalTitle">Ajouter un produit</h2>
                <form id="productForm" action="produit.php" method="POST">
                    <input type="hidden" name="oldRefProduit" id="oldRefProduit" value="">
                    <label>Ref produit:</label><input type="text" name="refProduit" id="refProduit" required><br><br>
                    <label>Nom:</label><input type="text" name="nomProduit" id="nomProduit" required><br><br>
                    <label>Désignation:</label><input type="text" name="designationProduit" id="designationProduit" required><br><br>
                    <label>Prix:</label><input type="text" name="prix" id="prix" required><br><br>
                    <label>Prix d'achat:</label><input type="text" name="prixAchat" id="prixAchat" required><br><br>
                    <button type="submit" id="submitBtn">Ajouter</button>
                </form>
            </div>
        </div>

        <!--produits -->
        <h2>Liste des produits</h2><br><br>
        <table class="products-table" border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>Ref Produit</th>
                    <th>Nom</th>
                    <th>Désignation</th>
                    <th>Prix</th>
                    <th>Prix d'achat</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                   <td><?= htmlspecialchars($row['Ref_Produit'] ?? '') ?></td>
<td><?= htmlspecialchars($row['Nom'] ?? '') ?></td>
<td><?= htmlspecialchars($row['Designation'] ?? '') ?></td>
<td><?= htmlspecialchars($row['prix'] ?? '') ?></td>
<td><?= htmlspecialchars($row['prix_achat'] ?? '') ?></td>

                    <td>
                       <a href="#" class="editBtn"
   data-ref="<?= htmlspecialchars($row['Ref_Produit'] ?? '') ?>"
   data-nom="<?= htmlspecialchars($row['Nom'] ?? '') ?>"
   data-designation="<?= htmlspecialchars($row['Designation'] ?? '') ?>"
   data-prix="<?= htmlspecialchars($row['prix'] ?? '') ?>"
   data-prix-achat="<?= htmlspecialchars($row['prix_achat'] ?? '') ?>">
   Modifier
</a> | 
                        <a href="produit.php?delete=<?= urlencode($row['Ref_Produit']) ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');">Supprimer</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// feneter
var modal = document.getElementById("productModal");
var btn = document.getElementById("openModalBtn");
var span = document.getElementsByClassName("close")[0];
var form = document.getElementById("productForm");
var modalTitle = document.getElementById("modalTitle");
var submitBtn = document.getElementById("submitBtn");

btn.onclick = function() {
    modalTitle.innerText = "Ajouter un produit";
    submitBtn.innerText = "Ajouter";
    form.reset();
    var updateInput = form.querySelector('[name="update"]');
    if(updateInput) updateInput.remove();
    modal.style.display = "block";
};

span.onclick = function(){ modal.style.display = "none"; }
window.onclick = function(event){ if(event.target == modal){ modal.style.display = "none"; } }

// Modifier
var editButtons = document.getElementsByClassName("editBtn");
for (let i = 0; i < editButtons.length; i++) {
    editButtons[i].onclick = function() {
        modalTitle.innerText = "Modifier un produit";
        submitBtn.innerText = "Modifier";

        document.getElementById("refProduit").value = this.dataset.ref;
        document.getElementById("oldRefProduit").value = this.dataset.ref;
        document.getElementById("nomProduit").value = this.dataset.nom;
        document.getElementById("designationProduit").value = this.dataset.designation;
       document.getElementById("prix").value = this.dataset.prix;
       document.getElementById("prixAchat").value = this.dataset.prixAchat;


        if(!form.querySelector('[name="update"]')) {
            let inputUpdate = document.createElement('input');
            inputUpdate.type = 'hidden';
            inputUpdate.name = 'update';
            inputUpdate.value = '1';
            form.appendChild(inputUpdate);
        }

        modal.style.display = "block";
    }
}

// Recherche
var searchInput = document.getElementById("searchInput");
searchInput.addEventListener("keyup", function() {
    var filter = this.value.toLowerCase();
    var table = document.querySelector(".products-table tbody");
    var rows = table.getElementsByTagName("tr");

    for (var i = 0; i < rows.length; i++) {
        var cells = rows[i].getElementsByTagName("td");
        var match = false;
        for (var j = 0; j < cells.length - 1; j++) {
            if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? "" : "none";
    }
});
</script>
</body>
</html>
