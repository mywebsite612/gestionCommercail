<?php
include_once 'connexion.php';

// Récupérer la valeur de recherche si elle existe
$search = $_GET['search'] ?? '';

// Préparer la requête SQL avec LIKE pour la recherche
$sql = "SELECT * FROM facturee WHERE 
        ICE LIKE ? OR 
        numero_facture LIKE ? OR 
        numero_bl LIKE ? 
        ORDER BY Id_facture DESC";

$stmt = $conn->prepare($sql);
$likeSearch = "%$search%";
$stmt->bind_param("sss", $likeSearch, $likeSearch, $likeSearch);
$stmt->execute();
$result = $stmt->get_result();

//supprimer factureæ
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM facturee WHERE id_facture = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: facture.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="website icon" href="logo.png">
<title>Facture - Gestion</title>
<link rel="stylesheet" href="fournisseur.css">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<style>
.modal {display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgba(0,0,0,0.5);}
.modal-content {background-color:#fefefe;margin:10% auto;padding:20px;border-radius:8px;width:400px;}
.close {color:#aaa;float:right;font-size:28px;cursor:pointer;}
tr:hover {background-color:#f0f0f0; cursor:pointer;}
</style>
</head>
<body>

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
                <form method="get" action="">
                    <input type="text" name="search" id="searchInput" placeholder="Rechercher par ICE, N° Facture ou N° BL" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" id="searchInput" style=" padding: 10px 100px;
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
            </div>
        </header>

        <header class="header">
            <h1>Facture</h1>
            <a href="devis.php">👉🏻 Devis</a>       
            <a href="devis.php" class="btn-add">
                <i class="fas fa-plus"></i> 
                <button id="openModalBtn" class="btn btn-primary"><i class="fas fa-plus" ></i> Pour Ajouter un facture il faut ajouter un devis</button>
            </a>
        </header>
        <br><br><br>

        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID Facture</th>
                    <th>ICE Client</th>
                    <th>Date Facture</th>
                    <th>N° BL</th>
                    <th>Total TTC</th>
                    <th>Statut</th>
                    <th>N° Facture</th>
                    <th>Actions</th>
                    </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows == 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center; color:red;">
                        Aucune facture trouvée
                    </td>
                </tr>
                <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><center><?= htmlspecialchars($row['id_facture']) ?></center></td>
                    <td><?= htmlspecialchars($row['ICE']) ?></td>
                    <td><?= htmlspecialchars($row['dateFacture']) ?></td>
                    <td><?= htmlspecialchars($row['numero_bl']) ?></td>
                    <td><?= htmlspecialchars($row['total_ttc']) ?></td>
                    <td><?= htmlspecialchars($row['statut']) ?></td>
                    <td><?= htmlspecialchars($row['numero_facture']) ?></td>
                    <td>
                        <center><a href="facture.php?delete=<?= $row['id_facture'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?');">
                            <i class="fas fa-trash" style="color:red;">Supprimer</i>
                        </a>
                       
                        </td>
                </tr>
                <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>
