-- Table pour les paramètres du site
CREATE TABLE IF NOT EXISTS parametres (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cle VARCHAR(100) UNIQUE NOT NULL,
    valeur TEXT,
    description VARCHAR(255),
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion des paramètres par défaut
INSERT INTO parametres (cle, valeur, description) VALUES
('site_nom', 'KM SERVICES SARL', 'Nom du site'),
('site_email', 'contact@kmservicesarlu.cd', 'Email principal'),
('site_telephone', '+243 (0) 892 017 793 / 999 920 715', 'Téléphone'),
('site_adresse', 'Avenue Kabalo N°235, Quartier Makutano, Lubumbashi, RDC', 'Adresse complète'),
('whatsapp_number', '243892017793', 'Numéro WhatsApp (format international sans +)'),
('facebook_url', 'https://web.facebook.com/me/', 'Lien Facebook'),
('instagram_url', '#', 'Lien Instagram'),
('linkedin_url', '#', 'Lien LinkedIn'),
('site_maintenance', '0', 'Mode maintenance (0=non, 1=oui)')
ON DUPLICATE KEY UPDATE valeur=valeur;
