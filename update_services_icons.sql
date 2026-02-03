USE km_services;

UPDATE services SET icone = 'fa-solid fa-building' WHERE nom_service = 'Construction';
UPDATE services SET icone = 'fa-solid fa-faucet' WHERE nom_service = 'Plomberie';
UPDATE services SET icone = 'fa-solid fa-bolt' WHERE nom_service = 'Électrification';
UPDATE services SET icone = 'fa-solid fa-industry' WHERE nom_service = 'Maintenance';
UPDATE services SET icone = 'fa-solid fa-water' WHERE nom_service = 'Forage';
UPDATE services SET icone = 'fa-solid fa-boxes-stacked' WHERE nom_service = 'Fourniture matériaux';
