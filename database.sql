-- Drahmi Database Schema
-- Run this file to create all necessary tables

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for utilisateur
-- ----------------------------
DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `dernier_connexion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for categorie
-- ----------------------------
DROP TABLE IF EXISTS `categorie`;
CREATE TABLE `categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `icone` varchar(50) DEFAULT 'category',
  `type` enum('revenu','depense') NOT NULL,
  `couleur` varchar(20) DEFAULT '#6D76FF',
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default categories
INSERT INTO `categorie` (`nom`, `icone`, `type`, `couleur`) VALUES
('Alimentation', 'restaurant', 'depense', '#FF6B6B'),
('Transport', 'directions_car', 'depense', '#4ECDC4'),
('Loisirs', 'sports_esports', 'depense', '#45B7D1'),
('Shopping', 'shopping_bag', 'depense', '#96CEB4'),
('Santé', 'local_hospital', 'depense', '#FFEAA7'),
('Maison', 'home', 'depense', '#DDA0DD'),
('Factures', 'receipt', 'depense', '#98D8C8'),
('Autre Dépense', 'more_horiz', 'depense', '#B8B8B8'),
('Salaire', 'work', 'revenu', '#6D76FF'),
('Freelance', 'laptop', 'revenu', '#4ECDC4'),
('Investissement', 'trending_up', 'revenu', '#45B7D1'),
('Autre Revenu', 'attach_money', 'revenu', '#96CEB4');

-- ----------------------------
-- Table structure for transaction
-- ----------------------------
DROP TABLE IF EXISTS `transaction`;
CREATE TABLE `transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `type` enum('revenu','depense') NOT NULL,
  `categorie_id` int(11) DEFAULT NULL,
  `description` text,
  `date` date NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `categorie_id` (`categorie_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for revenu
-- ----------------------------
DROP TABLE IF EXISTS `revenu`;
CREATE TABLE `revenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `source` varchar(200) DEFAULT NULL,
  `date` date NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for objectif
-- ----------------------------
DROP TABLE IF EXISTS `objectif`;
CREATE TABLE `objectif` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `montant_cible` decimal(15,2) NOT NULL,
  `progression` decimal(15,2) DEFAULT 0,
  `date_cible` date DEFAULT NULL,
  `statut` enum('active','completed','paused') DEFAULT 'active',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for epargne
-- ----------------------------
DROP TABLE IF EXISTS `epargne`;
CREATE TABLE `epargne` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `taux` decimal(5,2) DEFAULT 0,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for dette
-- ----------------------------
DROP TABLE IF EXISTS `dette`;
CREATE TABLE `dette` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `montant_initial` decimal(15,2) NOT NULL,
  `montant_restant` decimal(15,2) NOT NULL,
  `taux_interet` decimal(5,2) DEFAULT 0,
  `statut` enum('active','paid') DEFAULT 'active',
  `cree_par` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for facture
-- ----------------------------
DROP TABLE IF EXISTS `facture`;
CREATE TABLE `facture` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `date_echeance` date NOT NULL,
  `paye` tinyint(1) DEFAULT 0,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `date_echeance` (`date_echeance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for alert
-- ----------------------------
DROP TABLE IF EXISTS `alert`;
CREATE TABLE `alert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `type` enum('budget','goal','bill') NOT NULL,
  `seuil` decimal(15,2) DEFAULT NULL,
  `message` text,
  `active` tinyint(1) DEFAULT 1,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for historique
-- ----------------------------
DROP TABLE IF EXISTS `historique`;
CREATE TABLE `historique` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `entite_type` varchar(50) DEFAULT NULL,
  `entite_id` int(11) DEFAULT NULL,
  `details` text,
  `lu` tinyint(1) DEFAULT 0,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for repartition
-- ----------------------------
DROP TABLE IF EXISTS `repartition`;
CREATE TABLE `repartition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `pourcentage` decimal(5,2) NOT NULL,
  `mois` int(11) NOT NULL,
  `annee` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_repartition` (`utilisateur_id`, `categorie_id`, `mois`, `annee`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `categorie_id` (`categorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for simulation
-- ----------------------------
DROP TABLE IF EXISTS `simulation`;
CREATE TABLE `simulation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `parametres` json DEFAULT NULL,
  `resultats` json DEFAULT NULL,
  `date_simulation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------
-- Sample test user (password: password123)
-- ----------------------------
INSERT INTO `utilisateur` (`nom`, `prenom`, `email`, `mot_de_passe`) VALUES
('Test', 'User', 'test@drahmi.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYWv0X8J7uO');