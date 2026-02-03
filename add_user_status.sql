-- Ajouter la colonne statut à la table utilisateurs
-- Exécuter ce script SQL dans phpMyAdmin ou via la ligne de commande MySQL

ALTER TABLE utilisateurs 
ADD COLUMN statut ENUM('actif', 'bloque') DEFAULT 'actif' AFTER role;

-- Mettre à jour tous les utilisateurs existants comme actifs
UPDATE utilisateurs SET statut = 'actif' WHERE statut IS NULL;
