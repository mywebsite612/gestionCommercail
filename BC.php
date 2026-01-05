<?php
include_once 'connexion.php';

// Récupérer l'ID de la commande depuis l'URL
$id_commande = $_GET['id'] ?? '';

if (!$id_commande) {
    die("Commande non spécifiée");
}

// Récupérer les infos de la commande et fournisseur
$stmt = $conn->prepare("
    SELECT 
        c.Id_Commende,
        c.DateCommende,
        c.modeReglement,
        c.echeance,
        f.Nom AS fournisseurNom,
        f.Adresse AS fournisseurAdresse,
        f.Tele AS fournisseurTel
    FROM commende c
    LEFT JOIN fournisseurs f ON c.ID_Forrnisseur = f.ID_Forrnisseur
    WHERE c.Id_Commende = ?
");
$stmt->bind_param("s", $id_commande);
$stmt->execute();
$commande = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$commande) {
    die("Commande introuvable");
}


// Récupérer les produits de la commande
$stmt = $conn->prepare("
    SELECT 
        p.Ref_Produit,
        p.Nom,
        p.Designation,
        p.prix_achat AS prix_achat,
        cp.Quantite AS quantite,
        (p.prix_achat * cp.Quantite) AS montant_ht
    FROM commende_produit cp
    JOIN produit p ON cp.Ref_Produit = p.Ref_Produit
    WHERE cp.Id_Commende = ?
    ORDER BY p.Nom
");
$stmt->bind_param("s", $id_commande);
$stmt->execute();
$produits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcul des totaux (sans remise)
$totalHT = 0;
foreach ($produits as $p) {
    $totalHT += $p['prix_achat'] * $p['quantite']; // <-- prix_achat au lieu de PU
}


// TVA 20%
$tva = $totalHT * 0.20;

// Total TTC
$totalTTC = $totalHT + $tva;

// Fonction pour convertir nombre en lettres (simplifiée)
function nombreEnLettres($nombre) {
    $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
    $dizaines = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
    $centaines = ['', 'cent', 'deux cent', 'trois cent', 'quatre cent', 'cinq cent', 'six cent', 'sept cent', 'huit cent', 'neuf cent'];
    
    $millions = floor($nombre / 1000000);
    $reste = $nombre % 1000000;
    $mille = floor($reste / 1000);
    $cent = $reste % 1000;
    
    $lettres = '';
    
    if ($millions > 0) {
        $lettres .= nombreEnLettres($millions) . ' million' . ($millions > 1 ? 's' : '') . ' ';
    }
    
    if ($mille > 0) {
        if ($mille == 1) {
            $lettres .= 'mille ';
        } else {
            $lettres .= nombreEnLettres($mille) . ' mille ';
        }
    }
    
    if ($cent > 0) {
        $c = floor($cent / 100);
        $d = floor(($cent % 100) / 10);
        $u = $cent % 10;
        
        if ($c > 0) {
            $lettres .= $centaines[$c] . ' ';
        }
        
        if ($d > 0) {
            $lettres .= $dizaines[$d] . ' ';
        }
        
        if ($u > 0) {
            $lettres .= $unites[$u] . ' ';
        }
    }
    
    return trim($lettres);
}

$ttcLettres = nombreEnLettres(round($totalTTC)) . ' DIRHAMS';

// Modifier paiement (modeReglement + echeance)
if (isset($_POST['modifier_paiement'])) {
    $modeReglement = $_POST['modeReglement'];
    $echeance = $_POST['echeance'];
    
    $stmt = $conn->prepare("
        UPDATE commende 
        SET modeReglement = ?, echeance = ? 
        WHERE Id_Commende = ?
    ");
    $stmt->bind_param("sss", $modeReglement, $echeance, $id_commande);
    $stmt->execute();
    $stmt->close();
    
    // Recharger les données
    $stmt = $conn->prepare("
        SELECT c.*, f.Nom AS fournisseurNom, f.Adresse AS fournisseurAdresse, f.Tele AS fournisseurTel
        FROM commende c
        LEFT JOIN fournisseurs f ON c.ID_Forrnisseur = f.ID_Forrnisseur
        WHERE c.Id_Commende = ?
    ");
    $stmt->bind_param("s", $id_commande);
    $stmt->execute();
    $commande = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bon de commande N° <?= htmlspecialchars($id_commande) ?></title>
<link rel="website icon" href="logo.png">
<link rel="stylesheet" href="BCstyle.css">
</head>
<body>


<div class="header">
    <div class="logo">
        <img src="PICdataSof.PNG" alt="Logo" width="250" height="100">
    </div>
    <div class="box">
        <strong>Fournisseur</strong><br>
        <?= htmlspecialchars($commande['fournisseurNom'] ?? 'Non spécifié') ?><br>
        <?= htmlspecialchars($commande['fournisseurAdresse'] ?? '') ?><br>
        Tél : <?= htmlspecialchars($commande['fournisseurTel'] ?? '') ?>
    </div>
</div>

<h2>Bon de commande</h2>

<table>
<tr>
    <td><strong>N° BC</strong></td>
   
    <td><strong>Date</strong></td>
      <th>Mode de règlement</th>
            <th>Échéance</th>
            <th>Commercial(e)</th>

</tr>
<tr>
    <td><?= htmlspecialchars($id_commande) ?></td>
    <td><?= htmlspecialchars($commande['DateCommende'] ?? 'Non spécifié') ?></td>
    <td><?= htmlspecialchars($commande['modeReglement'] ?? 'Non spécifié') ?></td>
    <td><?= htmlspecialchars($commande['echeance'] ?? 'Non spécifié') ?></td>
    <td>Yassine</td>
</table>

<table>
<thead>
   <tr>
    <th>Code article</th>
    <th>Désignation</th>
    <th>Qté</th>
    <th>Prix Achat</th> 
    <th>Montant HT</th>
</tr>
</thead>
<tbody>
<?php foreach ($produits as $p): ?>
<tr>
    <td class="left"><?= htmlspecialchars($p['Ref_Produit']) ?></td>
    <td class="left">
        <?= htmlspecialchars($p['Nom']) ?>
        <?php if (!empty($p['Designation'])): ?><br><small><?= htmlspecialchars($p['Designation']) ?></small><?php endif; ?>
    </td>
    <td class="center"><?= htmlspecialchars($p['quantite']) ?></td>
    <td class="right"><?= number_format($p['prix_achat'], 2, ',', ' ') ?></td> 
    <td class="right"><?= number_format($p['montant_ht'], 2, ',', ' ') ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<table class="totaux">
    <tr>
        <td>Total HT</td>
        <td><?= number_format($totalHT, 2, ',', ' ') ?> MAD</td>
    </tr>
    <tr>
        <td>TVA 20%</td>
        <td><?= number_format($tva, 2, ',', ' ') ?> MAD</td>
    </tr>
    <tr>
        <td><strong>Net à payer TTC</strong></td>
        <td><strong><?= number_format($totalTTC, 2, ',', ' ') ?> MAD</strong></td>
    </tr>
</table>

<div style="clear: both"></div>

<div class="lettres" style="margin-top: 30px;">
    Arrêté le présent bon de commande à la somme de :<br>
    <strong><?= $ttcLettres ?></strong>
</div>

<hr>


<p><strong>Conditions de paiement :</strong> Paiement à 30 jours fin de mois</p>
<br>
<!-- Informations société -->
<div class="company-info" style="font-size: 12px; color: #555;">
<center>    
    <strong>DATA SOFTWARE</strong> – 19 Rue 20 août, Quartier El Houda, Berrechid
    <strong>Tél :</strong> 0522.51.69.93 - <strong>Email : contact@datasoftware.info - Site : www.datasoftware.info</strong>
    <strong>RC: 9719 - Patente: 40774559 - IF: 15254481 - CNSS: 4471041 - ICE: 001689532000087</strong>
    RIB: 007 621 0014438000000268 02 - Code SWIFT: BCMAMAMC</p>
    </center>
</div>

<!-- mprimante -->
<div class="no-print">
    <button onclick="window.print()">🖨️ Imprimer</button>
    <button onclick="document.getElementById('modal').style.display='block'">✏️ Modifier paiement</button>
</div>

<!-- Modal modification paiement -->
<div id="modal" class="modal">
    <div class="modal-content">
        <h3>Modifier les informations de paiement</h3>
        <form method="post">
            <label>Mode de règlement</label>
            <select name="modeReglement" required>
                <option value="">-- Sélectionner --</option>
                <option value="Chèque" <?= (isset($commande['modeReglement']) && $commande['modeReglement']=='Chèque') ? 'selected' : '' ?>>Chèque</option>
                <option value="Virement" <?= (isset($commande['modeReglement']) && $commande['modeReglement']=='Virement') ? 'selected' : '' ?>>Virement</option>
                <option value="Espèces" <?= (isset($commande['modeReglement']) && $commande['modeReglement']=='Espèces') ? 'selected' : '' ?>>Espèces</option>
                <option value="Carte Bancaire" <?= (isset($commande['modeReglement']) && $commande['modeReglement']=='Carte Bancaire') ? 'selected' : '' ?>>Carte Bancaire</option>
            </select>

            <label>Échéance</label>
            <input type="date" name="echeance" value="<?= htmlspecialchars($commande['echeance'] ?? '') ?>" required>

            <div class="modal-buttons">
                <button type="submit" name="modifier_paiement">💾 Enregistrer</button>
                <button type="button" onclick="document.getElementById('modal').style.display='none'">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Fermer le modal au clic en dehors
    window.onclick = function(event) {
        let modal = document.getElementById('modal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };

    // Par défaut, échéance = aujourd'hui + 30 jours si vide
    document.addEventListener('DOMContentLoaded', function() {
        let echeanceInput = document.querySelector('input[name="echeance"]');
        if (!echeanceInput.value) {
            let today = new Date();
            let futureDate = new Date(today);
            futureDate.setDate(today.getDate() + 30);
            let formattedDate = futureDate.toISOString().split('T')[0];
            echeanceInput.value = formattedDate;
        }
    });
</script>

</body>
</html>

<?php
$conn->close();
?>
