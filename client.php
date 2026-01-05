<?php
include_once 'connexion.php';

// Supprimer un produit
if (isset($_GET['delete'])) {
    $refProduitToDelete = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM client WHERE ICE= ?");
    $stmt->bind_param("s", $refProduitToDelete);
    $stmt->execute();
    $stmt->close();
    header("Location: client.php");
    exit;
}

// Modifier un produit
if (isset($_POST['update'])) {
    $oldRefICE = $_POST['oldReICE']; // ancienne référence
    $ICE = $_POST['ICE'];      // nouvelle référence
    $nom = $_POST['nom'];
    $tele = $_POST['tele'];
    $adresse = $_POST['adresse'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE client SET ICE=?, Nom=?, Tele=?,Adress=?,Email=? WHERE ICE=?");
    $stmt->bind_param("ssssss", $ICE, $nom, $tele,$adresse,$email, $oldRefICE);

    if ($stmt->execute()) {
        $successMessage = "client mis à jour avec succès.";
    } else {
        $errorMessage = "Erreur: " . $stmt->error;
    }
    $stmt->close();
}

// Ajouter  client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update'])) {

    $ICE = $_POST['ICE'];
    $nom = $_POST['nom'];
    $tele = $_POST['tele'];
    $adresse = $_POST['adresse'];
    $email = $_POST['email'];

    // 1️⃣ Vérifier si ICE existe déjà
    $checkStmt = $conn->prepare("SELECT ICE FROM client WHERE ICE = ?");
    $checkStmt->bind_param("s", $ICE);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // ICE existe déjà
        $errorMessage = "❌ Ce client existe déjà.";
    } else {
      
        $stmt = $conn->prepare(
            "INSERT INTO client (ICE, Nom, Tele, Adress, Email) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $ICE, $nom, $tele, $adresse, $email);

        if ($stmt->execute()) {
            $successMessage = "✅ Nouveau client ajouté avec succès.";
        } else {
            $errorMessage = "Erreur : " . $stmt->error;
        }

        $stmt->close();
    }

    $checkStmt->close();
}


// 🔍 Recherche client
$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $like = "%$search%";
    $sql = "SELECT * FROM client
            WHERE ICE = ?
            OR Nom LIKE ?
            OR Tele LIKE ?
            ORDER BY ICE ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM client ORDER BY ICE ASC");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Client - Gestion</title>
<link rel="stylesheet" href="fournisseur.css">
    <link rel="website icon" href="logo.png">
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
           placeholder="Rechercher ICE ou Nom"
           value="<?= htmlspecialchars($search) ?>">
    <button type="submit" style=" padding: 10px 100px;
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
            <h1>Client </h1>
            <button id="openModalBtn" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau Client</button>
        </header>

        <!--  ajouter Modifier produit -->
        <div id="productModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="modalTitle">Ajouter un client</h2>
                <form id="productForm" action="client.php" method="POST">
                    <input type="hidden" name="oldReICE" id="oldReICE" value="">
                    <label>ICE:</label><input type="text" name="ICE" id="ICE" required><br><br>
                    <label>Nom:</label><input type="text" name="nom" id="nom" required><br><br>
                    <label>Tele:</label><input type="text" name="tele" id="tele" required><br><br>
                     <label>Address :</label><input type="text" name="adresse" id="adresse" required><br><br>
                      <label>Email :</label><input type="text" name="email" id="email" required><br><br>
                    <button type="submit" id="submitBtn">Ajouter</button>
                </form>
            </div>
        </div>

        <!-- Liste produits -->
        <h2>Liste des clients</h2><br><br>
        <table class="products-table" border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>ICE</th>
                    <th>Nom</th>
                    <th>Tele</th>
                    <th>Adresse</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['ICE']) ?></td>
                    <td><?= htmlspecialchars($row['Nom']) ?></td>
                    <td><?= htmlspecialchars($row['Tele']) ?></td>
                    <td><?= htmlspecialchars($row['Adress']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                    <td>
                        <a href="#" class="editBtn"
                           data-ref="<?= $row['ICE'] ?>"
                           data-nom="<?= htmlspecialchars($row['Nom']) ?>"
                           data-tele="<?= htmlspecialchars($row['Tele']) ?>"
                            data-Adress="<?= htmlspecialchars($row['Adress']) ?>"
                             data-Email="<?= htmlspecialchars($row['Email']) ?>">Modifier</a> | 
                        <a href="client.php?delete=<?= urlencode($row['ICE']) ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce client ?');">Supprimer</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Modal JS
var modal = document.getElementById("productModal");
var btn = document.getElementById("openModalBtn");
var span = document.getElementsByClassName("close")[0];
var form = document.getElementById("productForm");
var modalTitle = document.getElementById("modalTitle");
var submitBtn = document.getElementById("submitBtn");

btn.onclick = function() {
    modalTitle.innerText = "Ajouter un client";
    submitBtn.innerText = "Ajouter";
    form.reset();
    var updateInput = form.querySelector('[name="update"]');
    if(updateInput) updateInput.remove();
    modal.style.display = "block";
};

span.onclick = function(){ modal.style.display = "none"; }
window.onclick = function(event){ if(event.target == modal){ modal.style.display = "none"; } }

// Modifier un produit
var editButtons = document.getElementsByClassName("editBtn");
for (let i = 0; i < editButtons.length; i++) {
    editButtons[i].onclick = function() {
        modalTitle.innerText = "Modifier un client";
        submitBtn.innerText = "Modifier";

        document.getElementById("ICE").value = this.dataset.ref;
        document.getElementById("oldReICE").value = this.dataset.ref;
        document.getElementById("nom").value = this.dataset.nom;
        document.getElementById("tele").value = this.dataset.tele;
         document.getElementById("adresse").value = this.dataset.adresse;
          document.getElementById("email").value = this.dataset.Email;

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

// Recherche en temps réel
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
