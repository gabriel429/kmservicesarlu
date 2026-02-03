-- Base de données KM SERVICES
-- Encodage recommandé
CREATE DATABASE IF NOT EXISTS km_services CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE km_services;

-- Table utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','editor') NOT NULL DEFAULT 'admin',
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table services
CREATE TABLE IF NOT EXISTS services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom_service VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  icone VARCHAR(120) DEFAULT NULL,
  display_order INT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- Table projets
CREATE TABLE IF NOT EXISTS projets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  service_id INT UNSIGNED DEFAULT NULL,
  images TEXT DEFAULT NULL,
  statut ENUM('en_cours','termine','planifie') NOT NULL DEFAULT 'en_cours',
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_projets_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table produits
CREATE TABLE IF NOT EXISTS produits (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom_produit VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  prix DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  image VARCHAR(255) DEFAULT NULL,
  reference VARCHAR(100) NOT NULL UNIQUE,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table commandes
CREATE TABLE IF NOT EXISTS commandes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  produit_id INT UNSIGNED NOT NULL,
  client_info JSON NOT NULL,
  statut ENUM('nouvelle','en_traitement','terminee') NOT NULL DEFAULT 'nouvelle',
  date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_commandes_produit FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table demandes_devis
CREATE TABLE IF NOT EXISTS demandes_devis (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_id INT UNSIGNED DEFAULT NULL,
  client_info JSON NOT NULL,
  description TEXT NOT NULL,
  statut ENUM('nouvelle','en_traitement','terminee') NOT NULL DEFAULT 'nouvelle',
  date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_devis_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table messages_contact
CREATE TABLE IF NOT EXISTS messages_contact (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  message TEXT NOT NULL,
  date_message TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table journal d'activité
CREATE TABLE IF NOT EXISTS journal_activite (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT UNSIGNED DEFAULT NULL,
  action VARCHAR(190) NOT NULL,
  contexte TEXT DEFAULT NULL,
  date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_journal_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Données de base (services)
INSERT INTO services (nom_service, description, icone) VALUES
('Construction', 'KM SERVICES prend en charge l\'intégralité des projets de construction, de la conception à la réalisation, en passant par la gestion de projet. L\'entreprise s\'occupe aussi bien des constructions résidentielles que commerciales et industrielles.', 'fa-solid fa-building'),
('Plomberie', 'Le département de plomberie de KM SERVICES propose des services complets pour l\'installation, la réparation et l\'entretien des systèmes de plomberie. Que ce soit pour des projets neufs ou de rénovation, l\'entreprise garantit un travail conforme aux normes de sécurité et de qualité.', 'fa-solid fa-faucet'),
('Électrification', 'KM SERVICES assure l\'installation, la maintenance et la mise à niveau des réseaux électriques pour les bâtiments résidentiels, commerciaux et industriels. L\'entreprise est experte en systèmes d\'électrification de haute performance et en solutions écoénergétiques.', 'fa-solid fa-bolt'),
('Maintenance', 'KM SERVICES propose des services de maintenance préventive et corrective pour assurer la pérennité et le bon fonctionnement des infrastructures. L\'objectif est d\'optimiser les performances tout en minimisant les coûts d\'exploitation.', 'fa-solid fa-industry'),
('Forage', 'Spécialiste du forage, KM SERVICES intervient dans les projets d\'approvisionnement en eau, avec des techniques modernes pour garantir un forage précis et sécurisé. L\'entreprise offre des solutions sur mesure adaptées aux besoins spécifiques de chaque projet.', 'fa-solid fa-water'),
('Fourniture matériaux', 'KM SERVICES fournit une large gamme de matériaux de construction de qualité ainsi que des équipements pour les professionnels du secteur. L\'entreprise travaille avec des fournisseurs de renom pour garantir la durabilité et la performance des produits proposés.', 'fa-solid fa-boxes-stacked');

-- Produits exemples
INSERT INTO produits (nom_produit, description, prix, image, reference) VALUES
('Ciment Portland 50kg', 'Ciment haute résistance pour travaux structurels.', 14.50, 'assets/images/produit-ciment.jpg', 'CIM-50K'),
('Fer à béton 12mm', 'Barres de fer pour armature béton.', 8.90, 'assets/images/produit-fer.jpg', 'FER-12'),
('Gravier lavé', 'Granulats pour béton et fondations.', 20.00, 'assets/images/produit-gravier.jpg', 'GRA-01');
