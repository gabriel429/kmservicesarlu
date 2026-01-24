# 📋 Structure Complète du Projet - KM Services

```
kmservices/
│
├── 📄 Fichiers de Configuration & Documentation
│   ├── .htaccess                    # Configuration Apache pour routage
│   ├── .gitignore                   # Fichiers ignorés par Git
│   ├── config/
│   │   └── config.php              # Configuration générale (DB, APP)
│   │
│   ├── README.md                   # Documentation complète
│   ├── QUICKSTART.md               # Démarrage rapide (5 min)
│   ├── INSTALLATION.md             # Guide d'installation détaillé
│   ├── RESUME.md                   # Résumé du projet
│   ├── STRUCTURE.md                # Ce fichier
│
├── 🚀 Fichiers de Démarrage
│   ├── run-server.bat              # Script de démarrage (Windows)
│   ├── run-server.sh               # Script de démarrage (Linux/Mac)
│   ├── check.php                   # Vérification d'installation
│   └── admin_password.php          # Gestion des mots de passe
│
├── 📂 app/ - Classes PHP Principales
│   ├── Database.php                # Gestion PDO et requêtes
│   ├── Router.php                  # Routeur simple
│   └── helpers.php                 # Fonctions utilitaires
│
├── 🗄️ database/ - Base de Données
│   └── schema.sql                  # Schéma complet et données initiales
│
├── 🌐 public/ - Fichiers Accessibles Publiquement
│   ├── index.php                   # Point d'entrée principal
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css           # Styles CSS (1200+ lignes)
│   │   │       - Variables CSS (couleurs, espacements)
│   │   │       - Navigation responsive
│   │   │       - Sections (hero, services, projets, etc.)
│   │   │       - Formulaires
│   │   │       - Responsive design (mobile-first)
│   │   │
│   │   ├── js/
│   │   │   └── main.js             # JavaScript principal
│   │   │       - Menu mobile
│   │   │       - Validation formulaires
│   │   │       - Gestion panier
│   │   │       - Animations
│   │   │
│   │   └── images/
│   │       └── (images statiques)
│   │
│   └── uploads/                    # Dossier uploads utilisateurs
│       └── .gitkeep
│
├── 👁️ views/ - Templates PHP
│   │
│   ├── layout.php                  # Mise en page principale
│   │   - Navigation
│   │   - Footer
│   │   - Structure HTML
│   │
│   ├── 📄 Pages Principales
│   │   ├── accueil.php             # Page d'accueil
│   │   ├── apropos.php             # À propos avec histoire
│   │   ├── services.php            # Détail des services
│   │   ├── contact.php             # Formulaires de contact et forage
│   │   ├── projets.php             # Galerie de projets
│   │   ├── project-detail.php      # Détail d'un projet
│   │   ├── boutique.php            # Boutique en ligne
│   │   ├── product-detail.php      # Détail d'un produit
│   │   ├── panier.php              # Panier d'achat
│   │   └── 404.php                 # Page d'erreur 404
│   │
│   └── admin/                      # Pages d'administration
│       ├── layout.php              # Mise en page admin
│       └── login.php               # Formulaire de connexion
│
└── 📊 Base de Données (schema.sql)
    │
    ├── 👥 Gestion Utilisateurs
    │   └── users                   # Administrateurs (id, username, email, role, etc.)
    │
    ├── 🏗️ Gestion Projets
    │   ├── projects                # Projets (titre, description, localisation, etc.)
    │   ├── project_images          # Images des projets
    │   └── project_categories      # Catégories (Bâtiments, Infrastructures, Forages)
    │
    ├── 🛍️ Gestion Boutique
    │   ├── products                # Produits (nom, prix, stock, etc.)
    │   ├── product_images          # Images des produits
    │   └── product_categories      # Catégories produits
    │
    ├── 📞 Gestion Requêtes
    │   ├── contacts                # Messages de contact
    │   └── drilling_requests       # Demandes de forage
    │
    ├── 🛒 Panier & Commandes
    │   ├── orders                  # Commandes
    │   ├── order_items             # Articles des commandes
    │   └── cart                    # Panier temporaire (sessions)
    │
    └── 🔧 Données de Référence
        ├── services                # Types de services
        └── (données initiales)
```

## 📐 Architecture Complète

### Frontend Public
```
URL: /
│
├── / (accueil)
├── /apropos
├── /services
├── /projets
├── /projets/{slug}
├── /boutique
├── /produit/{slug}
├── /contact (GET/POST)
├── /panier
└── /check.php (vérification installation)
```

### Backend Administration
```
URL: /admin/
│
├── /login (GET/POST)
├── /dashboard
├── /projets (CRUD)
├── /produits (CRUD)
├── /contacts
├── /forages (demandes)
├── /commandes
├── /utilisateurs
├── /parametres
└── /logout
```

## 🔐 Sécurité Implémentée

### Dans `app/Database.php`
- Requêtes préparées (protection SQL injection)
- Gestion PDO appropriée
- Gestion des erreurs

### Dans `public/index.php`
- Validation des entrées
- Échappement des données
- Sessions sécurisées

### Dans les formulaires
- Validation côté serveur
- Vérification des données
- Messages d'erreur sécurisés

## 💾 Base de Données

### Nombre de Tables: 11
1. **users** - 6 champs
2. **services** - 5 champs
3. **project_categories** - 4 champs
4. **projects** - 14 champs
5. **project_images** - 5 champs
6. **product_categories** - 6 champs
7. **products** - 12 champs
8. **product_images** - 4 champs
9. **contacts** - 10 champs
10. **drilling_requests** - 13 champs
11. **orders** - 10 champs (+ order_items, cart)

### Total: ~120 champs de base de données

## 📦 Dépendances

### Aucune dépendance externe requise!
- ✅ PHP pur (pas de framework)
- ✅ MySQL natif
- ✅ CSS/JS vanilla
- ✅ Font Awesome CDN (optionnel)

### Technologies Utilisées
- PHP 7.4+
- MySQL 8.0+
- PDO (extension PHP)
- HTML5
- CSS3
- JavaScript (vanilla)
- Apache (mod_rewrite)

## 📊 Statistiques du Projet

| Métrique | Valeur |
|----------|--------|
| Fichiers PHP | 15+ |
| Fichiers Vue | 12+ |
| Lignes CSS | 1200+ |
| Lignes JavaScript | 100+ |
| Tables Base de Données | 11 |
| Champs BD | 120+ |
| Pages Publiques | 9 |
| Pages Admin | 8+ |
| Formulaires | 3 |

## 🎨 Éléments Personnalisables

### Couleurs (dans style.css)
```css
--primary-color: #1e3a8a      /* À modifier */
--secondary-color: #f59e0b    /* À modifier */
--accent-color: #10b981       /* À modifier */
```

### Textes Statiques
- En-têtes dans les fichiers .php
- Descriptions de services
- Coordonnées de contact
- Contenu à propos

### Images & Uploads
- Logo/bannière
- Projets
- Produits
- Images de fond

## 🚀 Points de Déploiement

Pour passer en production:
1. Changer DB credentials en variables d'environnement
2. Configurer HTTPS
3. Mettre à jour les chemins de base
4. Configurer les sauvegardes automatiques
5. Mettre en place le monitoring

## 📚 Fichiers à Consulter en Priorité

1. **QUICKSTART.md** - Pour démarrer rapidement
2. **config/config.php** - Pour configurer la BD
3. **public/index.php** - Pour comprendre le routage
4. **views/layout.php** - Pour modifier l'affichage
5. **public/assets/css/style.css** - Pour personnaliser le design

---

**Version**: 1.0  
**Date**: Janvier 2024  
**Pour**: KM Services
