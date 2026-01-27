# Fix de la table quote_requests - Guide de déploiement

## Problème
La table `quote_requests` n'existe pas sur le serveur de production, ce qui causa l'erreur:
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'u424760992_kmservices.quote_requests' doesn't exist
```

## Solutions implémentées

### 1. Ajout à schema.sql
La table `quote_requests` a été ajoutée à `database/schema.sql` avec tous les champs nécessaires.

### 2. Scripts de migration et vérification
Deux nouveaux scripts ont été créés:

- **`database/migrate.php`** - Script de migration pour créer toutes les tables manquantes
- **`public/api/db_status.php`** - API pour vérifier l'état de la base de données

### 3. Amélioration du code
- Le dashboard admin (`views/admin/dashboard.php`) a été amélioré pour gérer les erreurs quand la table n'existe pas
- Ajout de try-catch imbriqués pour éviter les erreurs silencieuses

## Instructions de déploiement

### Option A: Automatique (Recommandé)
Accéder à cette URL une seule fois après le déploiement:
```
https://kmservices-vercel-app.vercel.app/database/migrate.php
```

Cela créera automatiquement la table `quote_requests` et les autres tables manquantes.

### Option B: Vérification manuelle
Pour vérifier l'état de la base de données sans créer les tables:
```
https://kmservices-vercel-app.vercel.app/public/api/db_status.php
```

Réponse attendue:
```json
{
  "success": true,
  "tables_status": {
    "quote_requests": {
      "exists": true,
      "accessible": true
    },
    ...
  }
}
```

### Option C: Via phpmyadmin/cPanel
Si vous avez accès direct à cPanel:
1. Ouvrir phpMyAdmin
2. Exécuter le contenu de `database/schema.sql`
3. La table sera créée automatiquement

## Vérification après déploiement

1. Accéder à: `https://kmservices.fr/admin/` 
2. Se connecter
3. Le dashboard devrait charger sans erreurs
4. Cliquer sur "Demandes de Devis" dans le menu
5. La page admin/devis.php devrait afficher la liste des demandes (vide initialement)

## Notes

- Le script `database/migrate.php` peut être exécuté plusieurs fois sans problème (idempotent)
- Les tables existantes ne seront pas affectées par les migrations
- Tous les CREATE TABLE utilisent la clause `IF NOT EXISTS`
- Les erreurs sont loggées dans `database/migration.log` si accès disponible

## Fichiers modifiés

- `database/schema.sql` - Ajout de la table quote_requests
- `database/migrate.php` - Nouveau script de migration
- `public/api/db_status.php` - Nouveau endpoint de vérification
- `views/admin/dashboard.php` - Amélioration de la gestion d'erreurs

## Fichiers à exécuter sur production

1. **Immédiat (post-déploiement)**: Accéder une fois à `/database/migrate.php`
2. **Optionnel (vérification)**: Accéder à `/public/api/db_status.php` pour confirmer
