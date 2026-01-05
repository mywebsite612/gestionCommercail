<?php
include_once 'connexion.php';

// Récupérer l'ID du devis depuis l'URL
$id_devis = $_GET['id'] ?? '';

// Récupérer les informations du devis
$stmt = $conn->prepare("
    SELECT d.*, c.Nom AS clientNom, c.Adress AS adressClient 
    FROM devis d 
    LEFT JOIN client c ON d.ICE = c.ICE 
    WHERE d.id_devis = ?
");
$stmt->bind_param("s", $id_devis);
$stmt->execute();
$devis = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$devis) {
    die("Devis non trouvé");
}

// Récupérer les produits du devis
$stmt = $conn->prepare("
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
    ORDER BY p.Nom
");
$stmt->bind_param("s", $id_devis);
$stmt->execute();
$produits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculer les totaux
$totalHT = 0;
foreach ($produits as $p) {
    $totalHT += $p['montant_ht'];
}

// 🔴 Appliquer la remise
$remisePourcent = $devis['remise'] ?? 0;
$montantRemise = $totalHT * ($remisePourcent / 100);
$totalHTRemise = $totalHT - $montantRemise;

// TVA 20%
$tva = $totalHTRemise * 0.20;

// Total TTC
$totalTTC = $totalHTRemise + $tva;


// Convertir le montant en lettres
function nombreEnLettres($nombre) {
    $unites = array('', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf');
    $dizaines = array('', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix');
    $centaines = array('', 'cent', 'deux cent', 'trois cent', 'quatre cent', 'cinq cent', 'six cent', 'sept cent', 'huit cent', 'neuf cent');
    
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

// Mettre à jour le mode de règlement et l'échéance si le formulaire est soumis
if (isset($_POST['modifier_paiement'])) {
    $modeReglement = $_POST['modeReglement'];
    $echeance = $_POST['echeance'];
    
    $stmt = $conn->prepare("
        UPDATE devis 
        SET modeReglement = ?, echeance = ? 
        WHERE id_devis = ?
    ");
    $stmt->bind_param("sss", $modeReglement, $echeance, $id_devis);
    $stmt->execute();
    $stmt->close();
    
    // Recharger les données
    $stmt = $conn->prepare("
        SELECT d.*, c.Nom AS clientNom, c.Adress AS adressClient 
        FROM devis d 
        LEFT JOIN client c ON d.ICE = c.ICE 
        WHERE d.id_devis = ?
    ");
    $stmt->bind_param("s", $id_devis);
    $stmt->execute();
    $devis = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="voirdevis.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis N° <?= htmlspecialchars($id_devis) ?></title>
       <link rel="website icon" href="logo.png">
</head>
<body>
<div class="devis-container">
    <!-- En-tête du devis -->
    <div class="devis-header">
        <div class="left-header">
            <img src="PICdataSof.PNG" class="logo" alt="Logo" style="width:250px;height:auto;border:none;">
            <div class="devis-num" style="margin-left:50px;">Devis N° : <?= $id_devis ?></div>
        </div>
        
        <div class="right-header">
            <strong><?= htmlspecialchars($devis['clientNom'] ?? 'Non spécifié') ?></strong><br>
            <strong><?= htmlspecialchars($devis['adressClient'] ?? '') ?></strong><br>
        </div>
    </div>

    <!-- Informations du devis -->
    <table class="info-table">
        <tr>
            <th>Date</th>
            <th>Mode de règlement</th>
            <th>Échéance</th>
            <th>Commercial(e)</th>
        </tr>
        <tr>
            <td><?= htmlspecialchars($devis['dateDevis']) ?></td>
            <td><?= htmlspecialchars($devis['modeReglement'] ?? '—') ?></td>
            <td><?= htmlspecialchars($devis['echeance'] ?? '—') ?></td>
            <td>Yassine</td>
        </tr>
    </table>

    <!-- Tableau des articles AVEC LIGNES DE SÉPARATION -->
    <table class="product-table">
        <thead>
            <tr>
                <th>Code article</th>
                <th>Designation</th>
                <th>Qté</th>
                <th>PU</th>
                <th>Montant HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $index => $p): 
                $nom = htmlspecialchars($p['Nom']);
                $designation = htmlspecialchars($p['Designation']);
                
                // Si la désignation est longue, on peut l'afficher sur deux lignes
                $showTwoLines = !empty($designation) && strlen($nom . ' ' . $designation) > 30;
            ?>
            <tr>
                <td class="left code-article">
                    <?= htmlspecialchars($p['Ref_Produit']) ?>
                </td>
                <td class="left">
                    <?php if ($showTwoLines): ?>
                        <span class="designation-line1"><?= $nom ?></span>
                        <?php if (!empty($designation)): ?>
                            <span class="designation-line2"><?= $designation ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= $nom ?>
                        <?php if (!empty($designation)): ?>
                            <br><span class="designation-line2"><?= $designation ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td class="center"><?= htmlspecialchars($p['quantite']) ?></td>
               <td class="right">
    <?= str_replace(' ', '&nbsp;', number_format($p['PU'], 2, ',', ' ')) ?>
</td>

<td class="right">
    <?= str_replace(' ', '&nbsp;', number_format($p['montant_ht'], 2, ',', ' ')) ?>
</td>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totaux -->
   <table class="total-table">
    <tr>
        <td>Total HT</td>
        <td><?= number_format($totalHT, 2, ',', ' ') ?> MAD</td>
    </tr>

    <tr>
        <td>Remise (<?= $remisePourcent ?> %)</td>
        <td>- <?= number_format($montantRemise, 2, ',', ' ') ?> MAD</td>
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


    <!-- Montant en lettres -->
    <div class="lettres">
   
        <strong>la somme de : <?= $ttcLettres ?></strong>
    </div>
  

    <!-- Informations de l'entreprise -->
   <div class="form-group">
            <center>
         <strong>Siège social:</strong> 19 Rue 20 aout Quartier El Houda Berrechid <strong>Tél :</strong> 0522.51.69.93 <strong>Email:</strong> contact@datasoftware.info <strong>Site:</strong> www.datasoftware.info <strong>RC:</strong> 9719-<strong>Patente:</strong> 40774559-<strong>IF:</strong> 15254481-<strong>Cnss:</strong> 4471041
        <strong>ICE:</strong> 001689532000087 <strong>RIB:</strong> 007 621 0014438000000268 02
        <strong>CODE SWIFT:</strong> BCMAMAMC
       </center>
    </div>
        </div>
<!-- Actions (non imprimables) -->
<div class="no-print">
    <div class="actions">
        <button onclick="window.print()">
            🖨️ Imprimer
        </button>
<form action="voir_facture.php" method="get">
    <input type="hidden" name="id" value="<?= $id_devis ?>">
    <button type="submit">🔁 Transférer en facture</button>
</form>

        <button class="btn edit" onclick="document.getElementById('modal').style.display='block'">
            ✏️ Modifier paiement
        </button>
    </div>
</div>

<!-- Modal pour modifier le paiement -->
<div id="modal" class="modal">
    <div class="modal-content">
        <h3>Modifier les informations de paiement</h3>
        <form method="post">
            <label>Mode de règlement</label>
            <select name="modeReglement" required>
                <option value="">-- Sélectionner --</option>
                <option value="Chèque" <?= ($devis['modeReglement'] ?? '') == 'Chèque' ? 'selected' : '' ?>>Chèque</option>
                <option value="Virement" <?= ($devis['modeReglement'] ?? '') == 'Virement' ? 'selected' : '' ?>>Virement</option>
                <option value="Espèces" <?= ($devis['modeReglement'] ?? '') == 'Espèces' ? 'selected' : '' ?>>Espèces</option>
                <option value="Carte Bancaire" <?= ($devis['modeReglement'] ?? '') == 'Carte Bancaire' ? 'selected' : '' ?>>Carte Bancaire</option>
            </select>

            <label>Échéance</label>
            <input type="date" name="echeance" value="<?= htmlspecialchars($devis['echeance'] ?? '') ?>" required>

            <div class="modal-buttons">
                <button type="submit" name="modifier_paiement">
                    💾 Enregistrer
                </button>
                <button type="button" onclick="document.getElementById('modal').style.display='none'">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Fermer le modal en cliquant en dehors
    window.onclick = function(event) {
        var modal = document.getElementById('modal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    
    // Optionnel : formatage automatique de la date
    document.addEventListener('DOMContentLoaded', function() {
        // Si la date d'échéance est vide, mettre la date actuelle + 30 jours
        var echeanceInput = document.querySelector('input[name="echeance"]');
        if (!echeanceInput.value) {
            var today = new Date();
            var futureDate = new Date(today);
            futureDate.setDate(today.getDate() + 30);
            var formattedDate = futureDate.toISOString().split('T')[0];
            echeanceInput.value = formattedDate;
        }
    });
</script>

</body>
</html>

<?php
$conn->close();
?>