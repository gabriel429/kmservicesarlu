USE km_services;

UPDATE services SET description = 'KM SERVICES prend en charge l\'intégralité des projets de construction, de la conception à la réalisation, en passant par la gestion de projet. L\'entreprise s\'occupe aussi bien des constructions résidentielles que commerciales et industrielles.'
WHERE nom_service = 'Construction';

UPDATE services SET description = 'Le département de plomberie de KM SERVICES propose des services complets pour l\'installation, la réparation et l\'entretien des systèmes de plomberie. Que ce soit pour des projets neufs ou de rénovation, l\'entreprise garantit un travail conforme aux normes de sécurité et de qualité.'
WHERE nom_service = 'Plomberie';

UPDATE services SET description = 'KM SERVICES assure l\'installation, la maintenance et la mise à niveau des réseaux électriques pour les bâtiments résidentiels, commerciaux et industriels. L\'entreprise est experte en systèmes d\'électrification de haute performance et en solutions écoénergétiques.'
WHERE nom_service = 'Électrification';

UPDATE services SET description = 'KM SERVICES propose des services de maintenance préventive et corrective pour assurer la pérennité et le bon fonctionnement des infrastructures. L\'objectif est d\'optimiser les performances tout en minimisant les coûts d\'exploitation.'
WHERE nom_service = 'Maintenance';

UPDATE services SET description = 'Spécialiste du forage, KM SERVICES intervient dans les projets d\'approvisionnement en eau, avec des techniques modernes pour garantir un forage précis et sécurisé. L\'entreprise offre des solutions sur mesure adaptées aux besoins spécifiques de chaque projet.'
WHERE nom_service = 'Forage';

UPDATE services SET description = 'KM SERVICES fournit une large gamme de matériaux de construction de qualité ainsi que des équipements pour les professionnels du secteur. L\'entreprise travaille avec des fournisseurs de renom pour garantir la durabilité et la performance des produits proposés.'
WHERE nom_service = 'Fourniture matériaux';
