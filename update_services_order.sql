USE km_services;

ALTER TABLE services ADD COLUMN display_order INT UNSIGNED NOT NULL DEFAULT 1;

UPDATE services SET display_order = 1 WHERE nom_service = 'Construction';
UPDATE services SET display_order = 2 WHERE nom_service = 'Plomberie';
UPDATE services SET display_order = 3 WHERE nom_service = 'Électrification';
UPDATE services SET display_order = 4 WHERE nom_service = 'Maintenance';
UPDATE services SET display_order = 5 WHERE nom_service = 'Forage';
UPDATE services SET display_order = 6 WHERE nom_service = 'Fourniture matériaux';
