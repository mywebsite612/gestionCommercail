<?php
include_once 'connexion.php';

/* =========================
   RÉCUPÉRER ID DEVIS
========================= */
$id_devis = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id_devis) {
    die("ID devis manquant");
}
$id_devis = (int)$id_devis;

/* =========================
   DONNÉES DU DEVIS + CLIENT
========================= */
$stmt = $conn->prepare("
    SELECT d.*, c.Nom AS clientNom, c.Adress AS adressClient, c.ICE
    FROM devis d
    JOIN client c ON d.ICE = c.ICE
    WHERE d.id_devis = ?
");
$stmt->bind_param("i", $id_devis);
$stmt->execute();
$devis = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$devis) die("Bien enregistré ");

/* =========================
   PRODUITS DU DEVIS
========================= */
$stmt = $conn->prepare("
    SELECT p.Ref_Produit, p.Nom, p.Designation, p.Prix AS PU,
           dp.quantite, (p.Prix * dp.quantite) AS montant_ht
    FROM devis_produit dp
    JOIN produit p ON dp.Ref_Produit = p.Ref_Produit
    WHERE dp.id_devis = ?
");
$stmt->bind_param("i", $id_devis);
$stmt->execute();
$produits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* =========================
   CALCUL TOTAUX (AVEC REMISE)
========================= */
$totalHT = 0;
foreach ($produits as $p) {
    $totalHT += $p['montant_ht'];
}

// 🔴 Remise du devis
$remisePourcent = $devis['remise'] ?? 0;
$montantRemise = $totalHT * ($remisePourcent / 100);
$totalHTRemise = $totalHT - $montantRemise;

// TVA 20%
$tva = $totalHTRemise * 0.20;

// Total TTC
$totalTTC = $totalHTRemise + $tva;


/* =========================
   VÉRIFIER FACTURE EXISTANTE (SANS BLOQUER)
========================= */
$factureExiste = false;
$id_facture_existante = null;

$check = $conn->prepare("SELECT id_facture FROM facturee WHERE id_devis = ?");
$check->bind_param("i", $id_devis);
$check->execute();
$check->bind_result($id_facture_existante);

if ($check->fetch()) {
    $factureExiste = true;
}
$check->close();


/* =========================
   NUMÉROS FACTURE & BL
========================= */
$dernier = $conn->query("SELECT MAX(id_facture) AS max_id FROM facturee")->fetch_assoc();
$dernier_id = (int)($dernier['max_id'] ?? 0);
$numFacture = str_pad($dernier_id + 1, 3, "0", STR_PAD_LEFT) . "/" . date('Y');
$numBL = "BL" . str_pad($dernier_id + 1, 4, "0", STR_PAD_LEFT);

/* =========================
   FONCTION POUR MONTANT EN LETTRES
========================= */
function nombreEnLettres($nombre) {
    $unités = ['', 'un','deux','trois','quatre','cinq','six','sept','huit','neuf'];
    $spéciaux = [10=>'dix',11=>'onze',12=>'douze',13=>'treize',14=>'quatorze',15=>'quinze',16=>'seize',17=>'dix-sept',18=>'dix-huit',19=>'dix-neuf'];
    $dizaines = ['', 'dix','vingt','trente','quarante','cinquante','soixante','soixante-dix','quatre-vingt','quatre-vingt-dix'];

    if ($nombre == 0) return "zéro";

    $lettres = '';

    if ($nombre >= 1000) {
        $mille = floor($nombre / 1000);
        $lettres .= ($mille == 1 ? "mille " : nombreEnLettres($mille) . " mille ");
        $nombre %= 1000;
    }

    if ($nombre >= 100) {
        $cent = floor($nombre / 100);
        $lettres .= ($cent > 1 ? $unités[$cent] . " cent" : "cent");
        $nombre %= 100;
        if ($nombre > 0) $lettres .= " ";
    }

    if ($nombre > 0) {
        if ($nombre < 10) $lettres .= $unités[$nombre];
        elseif ($nombre < 20) $lettres .= $spéciaux[$nombre];
        else {
            $d = floor($nombre / 10);
            $u = $nombre % 10;
            if ($d == 7 || $d == 9) {
                $lettres .= $dizaines[$d-1] . "-" . $spéciaux[10+$u];
            } else {
                $lettres .= $dizaines[$d];
                if ($u > 0) $lettres .= "-" . $unités[$u];
            }
        }
    }

    return trim($lettres) . " DIRHAMS";
}

/* =========================
   MONTANT TTC EN LETTRES
========================= */
$ttcLettres = nombreEnLettres(round($totalTTC));

/* =========================
   ENREGISTRER FACTURE
========================= */
$message = "";

if (isset($_POST['enregistrer'])) {

    if ($factureExiste) {
        $message = "⚠️ Cette facture est déjà enregistrée.";
    } else {

        $dateFacture = $_POST['dateFacture'];
        $statut = "Impayée";

        // Insertion facture
        $stmt = $conn->prepare("
            INSERT INTO facturee (ICE, id_devis, dateFacture, total_ht, tva, total_ttc, statut)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sissdds",
            $devis['ICE'],
            $id_devis,
            $dateFacture,
            $totalHT,
            $tva,
            $totalTTC,
            $statut
        );
        $stmt->execute();
        $id_facture = $stmt->insert_id;
        $stmt->close();

        // Générer num facture & BL
        $numFacture = str_pad($id_facture, 3, "0", STR_PAD_LEFT) . "/" . date('Y');
        $numBL = "BL" . str_pad($id_facture, 4, "0", STR_PAD_LEFT);

        $stmt = $conn->prepare("
            UPDATE facturee 
            SET numero_facture = ?, numero_bl = ?
            WHERE id_facture = ?
        ");
        $stmt->bind_param("ssi", $numFacture, $numBL, $id_facture);
        $stmt->execute();
        $stmt->close();

        header("Location: voir_facture.php?id=" . $id_facture);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="facture_style.css">
<title>Facture N° <?= $numFacture ?></title>
 <style>
        @media print {
            @page {
                margin: 20mm;
                size: A4;
            }
        }
    </style>

    <link rel="website icon" href="logo.png">
</head>
<body>
<?php if (!empty($message)): ?>
    <div style="color:red; font-weight:bold; margin-bottom:10px;">
        <?= $message ?>
    </div>
<?php endif; ?>


<div class="devis-container">
    <!-- En-tête du devis -->
    <div class="devis-header">
        <div class="left-header">
             <img src="PICdataSof.PNG" class="logo" alt="Logo" style="width:250px;height:auto;border:none;">
        </div>

        <!-- Titre Facture -->
        <div class="devis-num" style="margin-top:60px; ">Facture N° : <?= $numFacture ?></div>

        <div class="right-header">
            <strong>Client :</strong> <?= htmlspecialchars($devis['clientNom']) ?><br>
            <strong>Adresse :</strong> <?= htmlspecialchars($devis['adressClient']) ?><br>
            <strong>ICE :</strong> <?= htmlspecialchars($devis['ICE']) ?>
        </div>
    </div>

   
        
       <table class="info-table">
    <tr>
        <th>Date</th>
        <th>N° BL</th>
        <th>Mode de règlement</th>
        <th>Échéance</th>
        <th>Commercial(e)</th>
    </tr>
    <tr>
        <td><?= date('d/m/Y', strtotime($devis['dateDevis'])) ?></td>
        <td><?= $numBL ?></td>
        <td>Chèque</td>
        <td><?= date('d/m/Y', strtotime($devis['echeance'])) ?></td>
        <td>Yassine</td>
    </tr>
</table>


        <!-- Produits -->
        <table class="products-table">
            <tr>
                <th>Code article</th>
                <th>Désignation</th>
                <th>Qté</th>
                <th>PU</th>
                <th>Montant HT</th>
            </tr>
            <?php foreach($produits as $p): ?>
            <tr>
                <td class="code-article"><?= $p['Ref_Produit'] ?></td>
                <td>
                    <span class="designation-line1"><?= $p['Nom'] ?></span>
                    <?php if(!empty($p['Designation'])): ?>
                    <span class="designation-line2"><?= $p['Designation'] ?></span>
                    <?php endif; ?>
                </td>
                <td class="center"><?= $p['quantite'] ?></td>
              <td class="right">
    <?= str_replace(' ', '&nbsp;', number_format($p['PU'], 2, ',', ' ')) ?>
</td>

<td class="right">
    <?= str_replace(' ', '&nbsp;', number_format($p['montant_ht'], 2, ',', ' ')) ?>
</td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- Totaux -->
      <div class="totaux">
    <div class="montant-ligne">
        <span class="label">Total HT</span>
        <span class="montant"><?= number_format($totalHT,2,',',' ') ?> MAD</span>
    </div>

    <div class="montant-ligne">
        <span class="label">Remise (<?= $remisePourcent ?> %)</span>
        <span class="montant">- <?= number_format($montantRemise,2,',',' ') ?> MAD</span>
    </div>

    <div class="montant-ligne">
        <span class="label">T.V.A 20%</span>
        <span class="montant"><?= number_format($tva,2,',',' ') ?> MAD</span>
    </div>

    <div class="montant-ligne total-ttc">
        <span class="label">Net à payer TTC</span>
        <span class="montant"><?= number_format($totalTTC,2,',',' ') ?> MAD</span>
    </div>
</div>


        <!-- Montant en lettres -->
        <div class="lettres">
            Arrêté le présent devis à la somme de :<br>
            <strong><?= $ttcLettres ?></strong>
        </div>
      
       <div class="form-group">
            <center>
         <strong>Siège social:</strong> 19 Rue 20 aout Quartier El Houda Berrechid <strong>Tél :</strong> 0522.51.69.93 <strong>Email:</strong> contact@datasoftware.info <strong>Site:</strong> www.datasoftware.info <strong>RC:</strong> 9719-<strong>Patente:</strong> 40774559-<strong>IF:</strong> 15254481-<strong>Cnss:</strong> 4471041
        <strong>ICE:</strong> 001689532000087 <strong>RIB:</strong> 007 621 0014438000000268 02
        <strong>CODE SWIFT:</strong> BCMAMAMC</p>
       </center>
    </div>
        </div>


        <!-- Boutons -->
        <div class="buttons no-print">
            <button type="submit" name="enregistrer"
    class="btn btn-enregistrer"
    <?= $factureExiste ? 'disabled' : '' ?>>
    Enregistrer
</button>
         <button onclick="printWithStyles()" class="btn btn-imprimer">Imprimer</button>

<script>
function printWithStyles() {
    // Ouvrir la boîte de dialogue d'impression
    window.print();
    
    // Option : forcer les styles d'impression dans certains navigateurs
    window.onafterprint = function() {
        // Revenir à la vue normale
        window.location.reload();
    };
}
</script>

        </div>
    </form>
</div>

</body>
</html>