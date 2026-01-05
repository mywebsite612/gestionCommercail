-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 05 jan. 2026 à 08:56
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_commerc`
--

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `ICE` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Nom` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tele` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Adress` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ICE`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`ICE`, `Nom`, `Tele`, `Adress`, `Email`) VALUES
('765455', 'salma salma', '07421234', 'hay faraj', 'saidasalma626@gmail.com'),
('13456767700', 'SBBC', '06434222', 'casa', 'SBBC@gmail.com');

-- --------------------------------------------------------

--
-- Structure de la table `commende`
--

DROP TABLE IF EXISTS `commende`;
CREATE TABLE IF NOT EXISTS `commende` (
  `Id_Commende` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_Forrnisseur` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DateCommende` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modeReglement` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `echeance` date DEFAULT NULL,
  PRIMARY KEY (`Id_Commende`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commende`
--

INSERT INTO `commende` (`Id_Commende`, `ID_Forrnisseur`, `DateCommende`, `modeReglement`, `echeance`) VALUES
('10001', '000001', '2026-01-13', 'Virement', '2026-01-31'),
('10002', '133444', '2026-01-01', 'Espèces', '2026-01-31'),
('10003', '000001', '2026-01-03', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `commende_produit`
--

DROP TABLE IF EXISTS `commende_produit`;
CREATE TABLE IF NOT EXISTS `commende_produit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Id_Commende` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Ref_Produit` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Quantite` int NOT NULL,
  `prix_achat` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Id_Commende` (`Id_Commende`),
  KEY `Ref_Produit` (`Ref_Produit`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commende_produit`
--

INSERT INTO `commende_produit` (`id`, `Id_Commende`, `Ref_Produit`, `Quantite`, `prix_achat`) VALUES
(15, '10001', '1900', 3, 0.00),
(16, '10002', '1900', 1, 0.00),
(14, '10001', '1900', 2, 0.00),
(17, '10003', '1900', 2, 0.00),
(18, '10003', 'DS-2CD1063G2-LUI', 4, 0.00);

-- --------------------------------------------------------

--
-- Structure de la table `devis`
--

DROP TABLE IF EXISTS `devis`;
CREATE TABLE IF NOT EXISTS `devis` (
  `id_devis` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ICE` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dateDevis` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `modeReglement` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `echeance` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remise` decimal(5,2) DEFAULT '0.00',
  PRIMARY KEY (`id_devis`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `devis`
--

INSERT INTO `devis` (`id_devis`, `ICE`, `dateDevis`, `modeReglement`, `echeance`, `remise`) VALUES
('10001', '765455', '2025-12-29', 'Virement', '2026-01-07', 9.99),
('10002', '765455', '2025-12-29', 'Virement', '2026-01-28', 10.00),
('10003', '13456767700', '2026-01-03', 'Virement', '2026-02-28', 0.00);

-- --------------------------------------------------------

--
-- Structure de la table `devis_produit`
--

DROP TABLE IF EXISTS `devis_produit`;
CREATE TABLE IF NOT EXISTS `devis_produit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_devis` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Ref_Produit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantite` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_devis` (`id_devis`),
  KEY `Ref_Produit` (`Ref_Produit`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `devis_produit`
--

INSERT INTO `devis_produit` (`id`, `id_devis`, `Ref_Produit`, `quantite`) VALUES
(6, '987655', 'hgvgh3000', 23),
(26, '866', 'hgvgh3000', 2),
(24, '34556776', '1900', 1),
(7, '987655', 'hgvgh37766', 344),
(23, '34556776', 'hgvgh37766', 4),
(11, '0875', 'hgvgh3000', 1),
(12, '0875', 'hgvgh3000', 1),
(13, '0875', '1900', 1),
(27, '867', 'hgvgh3000', 1),
(15, '868', 'hgvgh3000', 1),
(22, '34556776', 'hgvgh3000', 1),
(37, '10001', '1900', 9),
(31, '10002', 'hgvgh3000', 1),
(36, '10001', 'hgvgh3000', 1),
(38, '10001', '1900', 7),
(39, '10001', '1900', 1),
(40, '10001', 'hgvgh37766', 1),
(41, '10001', 'hgvgh3000', 1),
(60, '10003', '5E200VA', 2),
(59, '10003', 'HDD6000GB', 1),
(58, '10003', 'DS-2CD1163G2-LIU', 4),
(57, '10003', 'DS-2CD1063G2-LUI', 4),
(56, '10003', 'DS-7764NI-M4', 1),
(55, '10003', 'FS1010PG', 2),
(61, '10003', 'PLEUG', 100);

-- --------------------------------------------------------

--
-- Structure de la table `facturee`
--

DROP TABLE IF EXISTS `facturee`;
CREATE TABLE IF NOT EXISTS `facturee` (
  `id_facture` int NOT NULL AUTO_INCREMENT,
  `ICE` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dateFacture` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_devis` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_bl` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_ht` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tva` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_ttc` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_facture` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_facture`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `facturee`
--

INSERT INTO `facturee` (`id_facture`, `ICE`, `dateFacture`, `id_devis`, `numero_bl`, `total_ht`, `tva`, `total_ttc`, `statut`, `numero_facture`) VALUES
(17, '765455', '2025-12-24', '987655', 'BL0017', '45880', '9176', '55056', 'Impayée', '017/2025'),
(14, '13456767700', '2025-12-27', '875', 'BL0014', '690', '138', '828', 'Impayée', '014/2025'),
(15, '13456767700', '2025-12-27', '34556776', 'BL0015', '970', '194', '1164', 'Impayée', '015/2025'),
(16, '765455', '2025-12-27', '866', 'BL0016', '400', '80', '480', 'Impayée', '016/2025');

-- --------------------------------------------------------

--
-- Structure de la table `fournisseurs`
--

DROP TABLE IF EXISTS `fournisseurs`;
CREATE TABLE IF NOT EXISTS `fournisseurs` (
  `ID_Forrnisseur` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Nom` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tele` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Adresse` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID_Forrnisseur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `fournisseurs`
--

INSERT INTO `fournisseurs` (`ID_Forrnisseur`, `Nom`, `Tele`, `Email`, `Adresse`) VALUES
('000001', 'jamal', '0533455', 'jamal98@gmail.com', 'sale'),
('133444', 'kamal', '0789987878', 'kamal@gmail.com', 'marjan, casa');

-- --------------------------------------------------------

--
-- Structure de la table `login`
--

DROP TABLE IF EXISTS `login`;
CREATE TABLE IF NOT EXISTS `login` (
  `user` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `login`
--

INSERT INTO `login` (`user`, `password`) VALUES
('data', '0000');

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

DROP TABLE IF EXISTS `produit`;
CREATE TABLE IF NOT EXISTS `produit` (
  `Ref_Produit` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Nom` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Designation` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix_achat` decimal(10,2) NOT NULL,
  `prix` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Ref_Produit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`Ref_Produit`, `Nom`, `Designation`, `prix_achat`, `prix`) VALUES
('DS-2CD1163G2-LIU', 'NETWORK CAMER', 'HIKVISION 6MP SMART HYBRID LIGHT FIXED DOME  DS-2CD1163G2-LIUNETWORK CAMER ', 600.00, '790'),
('1900', 'Pc Gaming, Nexus Vulcan', '     Processeur (CPU) : AMD Ryzen 7 7800X3D        ', 200.00, '400'),
('DS-2CD1063G2-LUI', 'NETWORK CAMER', 'HIKVISION 6MP SMART HYBRID LIGHT FIXED BULLET NETWORK CAMER', 600.00, '790'),
('DS-7764NI-M4', 'NVR HIKVISION', 'NVR HIKVISION 64 CHANNEL DS-7764NI-M4', 1000.00, '7400'),
('FS1010PG', 'FS1010PG ', 'FS1010PG 8-PORT 10/100M POE+ SWITCH WITH 2 GIGABIT UPLINK PORTS 120W CUDY FS1010PG', 400.00, '680'),
('HDD6000GB', 'HDD600GB', 'WESTERN DIGITAL PURPLE DESKTOP WD6000GB VIDEO', 900.00, '1750'),
('5E200VA', 'ONDULEUR', 'ONDULEUR EATON 5E200VA', 1000.00, '3300'),
('PLEUG', 'PLEUG M', 'CONNECTEUR RG45 MATALLIQUE PLEUG', 1.00, '2');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
