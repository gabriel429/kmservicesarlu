# Déploiement sur Vercel avec Supabase

Ce projet PHP peut être déployé sur Vercel en utilisant le runtime communautaire `vercel-php` et en se connectant à une base Postgres Supabase via PDO.

## Prérequis
- Compte Vercel avec CLI (`npm i -g vercel`)
- Projet Supabase (Postgres) créé
- Identifiants DB Supabase: host, port, database, user, password

## Étapes
1. **Configurer les variables d'environnement**
   - Copiez `.env.example` et renseignez vos valeurs.
   - Sur Vercel, définissez:
     - `DB_DRIVER=pgsql`
     - `DB_HOST`, `DB_PORT=5432`, `DB_NAME`, `DB_USER`, `DB_PASS`
     - (optionnel) `APP_URL` si vous utilisez un domaine personnalisé
     - (optionnel) `SUPABASE_URL`, `SUPABASE_ANON_KEY` pour Storage/Auth ultérieurs
   - (recommandé) `SUPABASE_SERVICE_ROLE_KEY` pour upload côté serveur
   - (recommandé) `SUPABASE_BUCKET=uploads` (ou votre nom de bucket)

2. **Structure Vercel**
   - Le fichier `vercel.json` configure le runtime PHP et les routes:
     - `api/index.php` est le point d'entrée (front controller)
     - Les assets sont servis depuis `public/assets` et `public/uploads`

3. **Déployer**
   ```bash
   vercel login
   vercel
   # puis pour production
   vercel --prod
   ```

4. **Migrer la base de données**
   - Utilisez le script Postgres fourni: `database/schema_postgres.sql` (coller dans Supabase → SQL Editor → Run).
   - Si vous partez de MySQL, adaptez vos schémas (types, identités, contraintes) ou prenez ce script comme base.
   - Supabase fournit un SQL Editor et un pool de connexion; pas de `CREATE DATABASE` nécessaire.

5. **Configurer le bucket Storage**
    - Dans Supabase → Storage, créez un bucket (ex: `uploads`).
    - Activez l'accès **public** pour les lectures.
    - Politiques (exemples):
       - Lecture publique: `allow read for all using (true);`
       - Écriture serveur: utiliser la `SERVICE_ROLE_KEY` côté serveur (déjà géré par l'app) ou permettre upload aux utilisateurs authentifiés selon votre besoin.

## Limitations & TODO
- Le filesystem des fonctions Vercel est éphémère: **les uploads locaux ne sont pas persistants**.
  - Remplacer `public/uploads/...` par **Supabase Storage** est recommandé.
  - Intégrer l'API Storage Supabase (upload, get URL) dans les endpoints.
   - Déjà implémenté pour le formulaire **Devis**: l'URL du document joint pointe désormais vers Supabase Storage si configuré.
- `APP_URL` est auto-déduit via `VERCEL_URL`. Configurez un domaine si nécessaire.

## Notes techniques
- La couche DB utilise **PDO** et supporte `mysql` ou `pgsql` via `DB_DRIVER`.
- Les anciennes références à `MySQLCore` fonctionnent désormais via PDO (MySQL/Postgres).
- Pour développer localement, conservez `DB_DRIVER=mysql` et votre WAMP.
