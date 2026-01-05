<?php
include_once 'connexion.php';

// Supprimer un fournisseur
if (isset($_GET['delete'])) {
    $reffournisseurToDelete = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM fournisseurs WHERE ID_Forrnisseur= ?");
    $stmt->bind_param("s", $reffournisseurToDelete);
    $stmt->execute();
    $stmt->close();
    header("Location: fournisseur.php");
    exit;
}

// Modifier un fournisseur
if (isset($_POST['update'])) {
     $oldRefFournisseure = $_POST['oldRefFournisseure']; // ancienne référence
    $reffournisseur = trim($_POST['reffournisseur']);
    $nomFournisseur = trim($_POST['nomFournisseur']);
    $Tele = trim($_POST['Tele']);
    $Email = trim($_POST['Email']);
    $Adresse = trim($_POST['Adresse']);
   
  $stmt = $conn->prepare("UPDATE fournisseurs SET ID_Forrnisseur=?, Nom=?, Tele=?, Email=?, Adresse=? WHERE ID_Forrnisseur=?");
$stmt->bind_param("ssssss", $reffournisseur, $nomFournisseur, $Tele, $Email, $Adresse, $oldRefFournisseure);


    if ($stmt->execute()) {
        $successMessage = "Fournisseur mis à jour avec succès.";
    } else {
        $errorMessage = "Erreur: " . $stmt->error;
    }
    $stmt->close();
}

// Ajouter un fournisseur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update'])) {

    $reffournisseur = trim($_POST['reffournisseur']);
    $nomFournisseur = trim($_POST['nomFournisseur']);
    $Tele = trim($_POST['Tele']);
    $Email = trim($_POST['Email']);
    $Adresse = trim($_POST['Adresse']);

    //  Vérifier si le fournisseur existe déjà
    $checkStmt = $conn->prepare(
        "SELECT ID_Forrnisseur FROM fournisseurs WHERE ID_Forrnisseur = ?"
    );
    $checkStmt->bind_param("s", $reffournisseur);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // Fournisseur déjà existant
        $errorMessage = "❌ Ce fournisseur existe déjà.";
    } else {
        // 2️⃣ Ajouter le fournisseur
        $stmt = $conn->prepare(
            "INSERT INTO fournisseurs (ID_Forrnisseur, Nom, Tele, Email, Adresse)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $reffournisseur, $nomFournisseur, $Tele, $Email, $Adresse);

        if ($stmt->execute()) {
            $successMessage = "✅ Nouveau fournisseur ajouté avec succès.";
        } else {
            $errorMessage = "Erreur : " . $stmt->error;
        }

        $stmt->close();
    }

    $checkStmt->close();
}

$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $like = "%$search%";
    $sql = "SELECT * FROM fournisseurs
            WHERE ID_Forrnisseur  = ?
            OR Nom LIKE ?
            ORDER BY ID_Forrnisseur  ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM fournisseurs ORDER BY ID_Forrnisseur  ASC");
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="website icon" href="logo.png">
<title>Fournisseurs - Gestion</title>
<link rel="stylesheet" href="fournisseur.css">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<style>
.modal {display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgba(0,0,0,0.5);}
.modal-content {background-color:#fefefe;margin:10% auto;padding:20px;border-radius:8px;width:400px;}
.close {color:#aaa;float:right;font-size:28px;cursor:pointer;}
</style>
</head>
<body>
<?php if (!empty($successMessage)) : ?>
    <div id="successMessage" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
        <?= $successMessage ?>
    </div>
<?php endif; ?>

<?php if (!empty($errorMessage)) : ?>
    <div id="errorMessage" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
        <?= $errorMessage ?>
    </div>
<?php endif; ?>

<script>
setTimeout(function() {
    var successDiv = document.getElementById('successMessage');
    var errorDiv = document.getElementById('errorMessage');
    if(successDiv) successDiv.style.display = 'none';
    if(errorDiv) errorDiv.style.display = 'none';
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
           placeholder="Rechercher Ref Produit ou Nom"
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
            <h1>Fournisseurs</h1>
            <button id="openModalBtn" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle Fournisseurs</button>
        </header>

        <!-- Modal Ajouter/Modifier fournisseur -->
        <div id="productModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="modalTitle">Ajouter un fournisseur</h2>
                <form id="productForm" action="fournisseur.php" method="POST">
                     <input type="hidden" name="oldRefFournisseure" id="oldRefFournisseure" value="">
                    <label>Ref Fournisseurs:</label><input type="text" name="reffournisseur" id="reffournisseur" required><br><br>
                    <label>Nom:</label><input type="text" name="nomFournisseur" id="nomFournisseur" required><br><br>
                    <label>Tele:</label><input type="text" name="Tele" id="Tele" required><br><br>
                    <label>Email:</label><input type="text" name="Email" id="Email" required><br><br>
                    <label>Adresse:</label><input type="text" name="Adresse" id="Adresse" required><br><br>
                    <button type="submit" id="submitBtn">Ajouter</button>
                </form>
            </div>
        </div>

        <h2>Liste des fournisseurs</h2><br><br>
        <table class="products-table" border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>Ref Fournisseur</th>
                    <th>Nom</th>
                    <th>Tele</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['ID_Forrnisseur']) ?></td>
                    <td><?= htmlspecialchars($row['Nom']) ?></td>
                    <td><?= htmlspecialchars($row['Tele']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                    <td><?= htmlspecialchars($row['Adresse']) ?></td>
                    <td>
                        <a href="#" class="editBtn"
                           data-ref="<?= $row['ID_Forrnisseur'] ?>"
                           data-nom="<?= htmlspecialchars($row['Nom']) ?>"
                           data-tele="<?= htmlspecialchars($row['Tele']) ?>"
                           data-email="<?= htmlspecialchars($row['Email']) ?>"
                           data-adresse="<?= htmlspecialchars($row['Adresse']) ?>">Modifier</a> | 
                        <a href="fournisseur.php?delete=<?= urlencode($row['ID_Forrnisseur']) ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce fournisseur ?');">Supprimer</a>
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
    modalTitle.innerText = "Ajouter un Fournisseur";
    submitBtn.innerText = "Ajouter";
    form.reset();
    var updateInput = form.querySelector('[name="update"]');
    if(updateInput) updateInput.remove();
    modal.style.display = "block";
};

span.onclick = function(){ modal.style.display = "none"; }
window.onclick = function(event){ if(event.target == modal){ modal.style.display = "none"; } }

// Modifier un fournisseur
var editButtons = document.getElementsByClassName("editBtn");
for (let i = 0; i < editButtons.length; i++) {
    editButtons[i].onclick = function() {
        modalTitle.innerText = "Modifier un Fournisseur";
        submitBtn.innerText = "Modifier";

        document.getElementById("reffournisseur").value = this.dataset.ref;
          document.getElementById("oldRefFournisseure").value = this.dataset.ref;
        document.getElementById("nomFournisseur").value = this.dataset.nom;
        document.getElementById("Tele").value = this.dataset.tele;
        document.getElementById("Email").value = this.dataset.email;
        document.getElementById("Adresse").value = this.dataset.adresse;

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
</script>

</body>
</html>
