# KM Services - Site Web Corporatif

[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https://github.com/gabriel429/kmservices)

Déploiement serverless via Vercel (runtime PHP) et base de données Supabase (Postgres). Voir aussi [DEPLOYMENT-VERCEL.md](DEPLOYMENT-VERCEL.md).

Site web moderne pour KM Services, plateforme de présentation et vente en ligne de services de construction et forage.

## 📋 Vue d'ensemble

KM Services est une application web PHP permettant à KM Services de :
- Présenter son expertise en construction et forage
- Gérer une galerie de projets
- Vendre des matériels de construction en ligne
- Recevoir des demandes de devis
- Gérer les commandes en ligne

## 🚀 Démarrage Rapide

### Prérequis (local)
- **PHP** 8.1+ 
- **MySQL** 8.0+ (ou **Postgres** pour Supabase)
- Serveur local (WAMP, LAMP, XAMPP) ou `php -S`

### Déploiement (Vercel + Supabase)
1. Créez un projet Vercel et configurez les variables d'environnement:
   - `DB_DRIVER=pgsql`
   - `DB_HOST`, `DB_PORT=5432`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - `SUPABASE_URL`, `SUPABASE_SERVICE_ROLE_KEY`, `SUPABASE_BUCKET=uploads`
   - Optionnel: `APP_URL` (sinon auto via `VERCEL_URL`)
2. Lancez un déploiement:
   ```bash
   npm i -g vercel
   vercel login
   vercel
   vercel --prod
   ```
3. Configurez le bucket Storage Supabase (public read) et vérifiez les formulaires d'upload.

### Installation

#### 1. Configuration de la Base de Données

1. Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
2. Créez une nouvelle base de données : `km_services`
3. Allez dans l'onglet "SQL" et importez le fichier `database/schema.sql`
4. Vérifiez que toutes les tables ont été créées

#### 2. Configuration de l'Application

1. Modifiez le fichier `config/config.php` si nécessaire :
   ```php
   define('DB_HOST', 'localhost');    // Hôte de la base de données
   define('DB_USER', 'root');         // Utilisateur MySQL
   define('DB_PASS', '');             // Mot de passe MySQL
   define('DB_NAME', 'km_services');  // Nom de la base de données
   ```

2. Assurez-vous que le dossier `public/uploads/` existe et est accessible en écriture

#### 3. Accès à l'Application

- **Site Public** : http://localhost/kmservices/
- **Admin** : http://localhost/kmservices/admin/login

#### Credentials Admin par Défaut
```
Utilisateur : admin
Mot de passe : password
```

⚠️ **IMPORTANT** : Changez le mot de passe admin au premier accès!

## 📁 Structure du Projet

```
kmservices/
├── app/                    # Classes PHP principales
│   ├── Database.php       # Gestion de la base de données
│   ├── MySQL.php          # Utilitaires DB (PDO, MySQL/Postgres)
│   └── Router.php         # Routeur simple
│   └── Supabase.php       # Client minimal Supabase Storage
├── config/                # Fichiers de configuration
│   └── config.php         # Configuration générale
├── database/              # Scripts de base de données
│   └── schema.sql         # Schéma et données initiales
├── public/                # Fichiers accessibles publiquement
│   ├── index.php         # Point d'entrée principal
│   ├── img.php           # Redimensionnement d'images (local/URL Supabase)
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css  # Styles CSS
│   │   ├── js/
│   │   │   └── main.js    # Scripts JavaScript
│   │   └── images/        # Images statiques
│   └── uploads/           # Dossier pour uploads utilisateurs
├── views/                 # Templates PHP
│   ├── layout.php        # Mise en page principale
│   ├── accueil.php       # Page d'accueil
│   ├── services.php      # Page services
│   ├── projets.php       # Galerie de projets
│   ├── contact.php       # Formulaire de contact
│   ├── boutique.php      # Boutique en ligne
│   ├── project-detail.php # Détail d'un projet
│   ├── product-detail.php # Détail d'un produit
│   ├── panier.php        # Panier d'achat
│   ├── apropos.php       # À propos
│   ├── 404.php           # Page 404
│   └── admin/            # Pages d'administration
│       ├── layout.php    # Mise en page admin
│       └── login.php     # Formulaire de connexion
├── .htaccess             # Configuration Apache
└── README.md             # Ce fichier
└── DEPLOYMENT-VERCEL.md  # Guide Vercel/Supabase
```

## 🛠️ Fonctionnalités

### Frontend Public
- ✅ Page d'accueil avec présentation générale
- ✅ Page "À Propos" avec histoire et valeurs
- ✅ Page Services détaillée
- ✅ Galerie de projets avec filtrage
- ✅ Détail de chaque projet
- ✅ Boutique en ligne avec filtrage
- ✅ Détail des produits
- ✅ Panier d'achat
- ✅ Formulaire de contact
- ✅ Formulaire de demande de forage
- ✅ Design responsive (mobile-first)

### Backend Administratif
- ✅ Authentification sécurisée
- ✅ Gestion des projets (CRUD)
- ✅ Gestion des produits (CRUD)
- ✅ Gestion des catégories
- ✅ Gestion des demandes de contact
- ✅ Gestion des demandes de forage
- ✅ Gestion des commandes
- ✅ Gestion des utilisateurs
- ✅ Interface responsive

## 📝 Utilisation

### Ajouter un Projet

1. Connectez-vous à l'admin (http://localhost/kmservices/admin/login)
2. Allez dans "Projets"
3. Cliquez sur "Nouveau Projet"
4. Remplissez les informations :
   - Titre
   - Description
   - Localisation
   - Dates
   - Client
   - Budget
   - Images
   - Vidéo (URL YouTube/Vimeo)
5. Publiez le projet

### Ajouter un Produit

1. Dans l'admin, allez dans "Produits"
2. Cliquez sur "Nouveau Produit"
3. Remplissez :
   - Nom
   - Description
   - Catégorie
   - Prix
   - Prix en promotion (optionnel)
   - Stock
   - Image principale
4. Publiez le produit

### Gérer les Demandes

- **Messages de Contact** : Admin → Messages
- **Demandes de Forage** : Admin → Demandes de Forage
- **Commandes** : Admin → Commandes

## 🔒 Sécurité

### Mesures Implémentées
- ✅ Protection contre les injections SQL (requêtes préparées)
- ✅ Validation des formulaires
- ✅ Échappement des données affichées
- ✅ Sessions sécurisées
- ✅ Authentification pour l'admin

### À Faire
- [ ] Implémenter HTTPS
- [ ] Ajouter des tokens CSRF
- [ ] Implémenter rate limiting
- [ ] Ajouter captcha aux formulaires
- [ ] Sauvegardes automatiques

## 📊 Base de Données

### Tables Principales
- `users` - Administrateurs
- `projects` - Projets réalisés/en cours
- `project_images` - Images des projets
- `services` - Types de services
- `products` - Produits en vente
- `product_categories` - Catégories de produits
- `product_images` - Images des produits
- `contacts` - Messages de contact
- `drilling_requests` - Demandes de forage
- `orders` - Commandes en ligne
- `order_items` - Articles des commandes
- `cart` - Panier d'achat

## 🎨 Personnalisation

### Modifier les Couleurs

Éditez `public/assets/css/style.css` :
```css
:root {
    --primary-color: #1e3a8a;      /* Bleu foncé */
    --secondary-color: #f59e0b;    /* Orange */
    --accent-color: #10b981;       /* Vert */
}
```

### Modifier le Logo

Remplacez "KM Services" dans les fichiers selon vos besoins ou ajoutez une image logo.

### Contenu du Site

Éditez les fichiers `.php` dans le dossier `views/` pour modifier le contenu :
- Textes
- Images
- Descriptions

## 🚨 Troubleshooting

### "Erreur de connexion à la base de données"
- En local: vérifiez MySQL/Postgres et `config/config.php`
- Sur Vercel: vérifiez les variables d'environnement DB/Supabase

### "404 - Page non trouvée"
- Assurez-vous que mod_rewrite est activé dans Apache
- Vérifiez le fichier `.htaccess`
- Redémarrez Apache

### "Les fichiers uploadés ne s'affichent pas"
- En local: permissions sur `public/uploads/`
- Sur Vercel: utilisez Supabase Storage; vérifiez `SUPABASE_*` et que le bucket est public

## 📧 Support

Pour des questions ou problèmes, veuillez contacter :
- **Email** : contact@kmservices.cd
- **Téléphone** : +243 (0) XXX XXX XXX

## 📄 Licence

Tous droits réservés © 2024 KM Services

---

## 📋 Checklist de Déploiement

Avant de mettre en production :

- [ ] Changer le mot de passe admin par défaut
- [ ] Configurer HTTPS
- [ ] Mettre à jour les informations de contact
- [ ] Tester tous les formulaires
- [ ] Vérifier les permissions des fichiers
- [ ] Faire une sauvegarde de la base de données
- [ ] Configurer les sauvegardes automatiques
- [ ] Tester le site sur différents navigateurs
- [ ] Optimiser les images
- [ ] Valider le SEO basique
- [ ] Tester la vitesse du site

## 📈 Améliorations Futures (Phase 2)

- [ ] Paiement en ligne (Stripe, PayPal)
- [ ] Système de comptes utilisateurs
- [ ] Blog/Actualités
- [ ] Système d'avis clients
- [ ] Intégration CRM
- [ ] API REST
- [ ] Application mobile
- [ ] Multilangue
- [ ] SEO avancé
- [ ] Analytics

---

**Version** : 1.0  
**Date** : Janvier 2024  
**Créé pour** : KM Services - Haut-Katanga, RD Congo
