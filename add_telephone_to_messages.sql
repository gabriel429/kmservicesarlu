-- Ajouter la colonne téléphone à la table messages_contact
-- Exécuter ce script SQL dans phpMyAdmin ou via la ligne de commande MySQL

ALTER TABLE messages_contact 
ADD COLUMN telephone VARCHAR(20) DEFAULT NULL AFTER email;
