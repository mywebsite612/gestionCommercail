<?php
include "connexion.php";

// Nombre de clients
$sqlClients = "SELECT COUNT(*) AS totalClients FROM client";
$resultClients = mysqli_query($conn, $sqlClients);
$dataClients = mysqli_fetch_assoc($resultClients);

// Nombre de commandes
$sqlCommandes = "SELECT COUNT(*) AS totalCommandes FROM commende";
$resultCommandes = mysqli_query($conn, $sqlCommandes);
$dataCommandes = mysqli_fetch_assoc($resultCommandes);
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion</title>
    <link rel="stylesheet" href="premierpage.css">
    <link rel="website icon" href="logo.png">
</head>

<body>
    <div class="dashboard-container">
    <!-- SIDEBAR -->
     <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-boxes"></i> <span>GestionPro</span>
            </div>
        </div>
        <nav class="sidebar-nav">
           <a href="premierpage.php" class="nav-item">
<i class="fas fa-home"></i> Tableau de Bord</a>
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
            <!-- Header -->
            <header class="header">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Rechercher un produit, client, fournisseur...">
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                     <a href="commende.php" style=" color: white;
    text-decoration: none;">   Nouvelle Commande</a>
                    </button>
                    <div class="user-profile">
                        <img src="logo.png" alt="Profile">
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Vue d'ensemble -->
                <section class="overview-section">
                    <h1>Vue d'ensemble</h1>
                    <p>Bienvenue sur votre tableau de bord de gestion.</p>
                </section>

                <!-- Search Results -->
                <div id="searchResults" class="search-results" style="display: none;"></div>

                <!-- Stats Grid -->
               <a href="commende.php" style="text-decoration: none;">
                 <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <img src="commande.png" alt="Orders Icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Commandes </h3>
                            <div class="stat-number"></div>
                            <div class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i>
                               
                            </div>
                        </div>
                    </div>
                </a>
    
                    <a href="client.php" style="text-decoration: none;"> 
                    <div class="stat-card">
                        <div class="stat-icon clients">
                            <img src="clientgestion.png" alt="Orders Icon">
                        </div>
                        <div class="stat-info">
                            <h3>Nouveaux Clients</h3>
                            <div class="stat-number"></div>
                            <div class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i>
                               
                            </div>
                        </div>
                    </div>
                    </a>

                    <div class="stat-card">
                        <div class="stat-icon products">
                             <img src="produit.png" alt="Orders Icon">
                        
                        </div>
                        <a href="produit.php" style="text-decoration: none;">
                        <div class="stat-info" href="produit.php">
                            <h3>Produits en stock</h3>
                            <div class="stat-number"></div>
                            <div class="stat-trend negative">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                        </a>
                
                    </div>

                    
                    <div class="stat-card">
                        
                        <div class="stat-icon suppliers">
                              <img src="fournisseur.png" alt="Orders Icon">
                              <a href="fournisseur.php" style="text-decoration: none;">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Fournisseurs </h3>
                            <div class="stat-number"></div>
                            <div class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i>
                           
                            </div>
                        </div>
                        </a>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="content-grid">
                    <!-- Dernières Commandes -->
                    <div class="card recent-orders">
                        <div class="card-header">
                            <h2>Dernières Commandes</h2>
                            <a href="commende.php" class="view-all">Voir tout</a>
                        </div>
                        <div class="card-content">
                            <div class="orders-list" id="recentOrdersList">
                            </div>
                            <div class="stat-number">
    <?php echo $dataCommandes['totalCommandes']; ?> Commandes
</div>

                        </div>
                    </div>

                    <!-- Nouveaux Clients -->
                    <div class="card recent-clients">
                        <div class="card-header">
                            <h2>Nouveaux Clients</h2>
                            <a href="client.php" class="view-all">Voir tout</a>
                        </div>
                        <div class="stat-number" style="font-size: 1.8rem;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <?php echo $dataClients['totalClients']; ?> Clients
</div>
                    </div>

                    <!-- Rapports Rapides -->
                    <div class="card quick-reports">
                        <div class="card-header">
                            <h2>Rapports Rapides</h2>
                        </div>
                        <div class="card-content">
                            <div class="reports-list">
                                <a href="#" class="report-item">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Rapport des Ventes</span>
                                </a>
                                <a href="#" class="report-item">
                                    <i class="fas fa-users"></i>
                                    <span>Rapport Clients</span>
                                </a>
                                <a href="#" class="report-item">
                                    <i class="fas fa-truck"></i>
                                    <span>Rapport Fournisseurs</span>
                                </a>
                                <a href="#" class="report-item">
                                    <i class="fas fa-box"></i>
                                    <span>Rapport Stock</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

   
</body>
</html>