#!/bin/bash
# Script pour créer les tables manquantes directement via SQL

# Configuration
DB_HOST="srv996.hstgr.io"
DB_USER="u424760992_kmservices_use"
DB_PASS="Kmservices@@Kin243"
DB_NAME="u424760992_kmservices"

# SQL pour créer la table
SQL="CREATE TABLE IF NOT EXISTS quote_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_devis VARCHAR(50) UNIQUE NOT NULL,
    nom VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    localisation VARCHAR(255),
    service VARCHAR(100),
    type_service VARCHAR(100),
    description LONGTEXT,
    delai_souhaite VARCHAR(100),
    budget_estime DECIMAL(12, 2),
    document_joint VARCHAR(255),
    statut ENUM('nouveau', 'en_attente', 'contacte', 'accepte', 'refuse') DEFAULT 'nouveau',
    lu TINYINT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    treated_by INT DEFAULT NULL,
    FOREIGN KEY (treated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_statut (statut),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"

# Exécuter
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$SQL"

echo "Table quote_requests créée ou vérifiée!"
