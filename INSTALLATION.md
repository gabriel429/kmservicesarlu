# Guide d'Installation - KM Services

## Étape 1 : Vérifier les Prérequis

Avant de commencer, assurez-vous d'avoir :
- ✅ PHP 7.4 ou supérieur
- ✅ MySQL 8.0 ou supérieur
- ✅ Apache avec mod_rewrite activé
- ✅ WAMP, XAMPP ou un serveur local

Pour vérifier la version de PHP, exécutez dans un terminal:
```bash
php -v
```

## Étape 2 : Configuration du Serveur Local

### Si vous utilisez WAMP
1. Installez WampServer (https://www.wampserver.com/)
2. Lancez WampServer
3. Assurez-vous que les services Apache et MySQL sont verts (✅)
4. Vérifiez que mod_rewrite est activé

### Si vous utilisez XAMPP
1. Installez XAMPP (https://www.apachefriends.org/)
2. Lancez le Control Panel XAMPP
3. Démarrez Apache et MySQL
4. mod_rewrite devrait être activé par défaut

## Étape 3 : Placer les Fichiers

1. Copiez le dossier `kmservices` dans:
   - **WAMP** : `C:\wamp64\www\`
   - **XAMPP** : `C:\xampp\htdocs\`

2. Assurez-vous que la structure est :
   ```
   www/ ou htdocs/
   └── kmservices/
       ├── app/
       ├── config/
       ├── database/
       ├── public/
       ├── views/
       └── ... autres fichiers
   ```

## Étape 4 : Créer la Base de Données

### Méthode 1 : phpMyAdmin (Recommandé)

1. Ouvrez votre navigateur à l'adresse : http://localhost/phpmyadmin
2. Connectez-vous (généralement root/sans mot de passe)
3. Cliquez sur l'onglet "Bases de données"
4. Créez une nouvelle base de données nommée `km_services`
5. Sélectionnez la base créée
6. Cliquez sur l'onglet "Importer"
7. Cliquez sur "Choisir fichier"
8. Sélectionnez le fichier `database/schema.sql`
9. Cliquez sur "Exécuter"

### Méthode 2 : Ligne de Commande

Ouvrez un terminal/invite de commande et exécutez :

```bash
# Accédez au répertoire MySQL
cd C:\wamp64\bin\mysql\mysql8.0.0\bin

# Connectez-vous à MySQL
mysql -u root -p

# Dans la console MySQL
CREATE DATABASE km_services;
USE km_services;
SOURCE C:/wamp64/www/kmservices/database/schema.sql;
```

## Étape 5 : Configurer l'Application

### Vérifier les Paramètres de Base de Données

Ouvrez le fichier `config/config.php` et vérifiez :

```php
define('DB_HOST', 'localhost');     // Adresse du serveur MySQL
define('DB_USER', 'root');          // Utilisateur MySQL
define('DB_PASS', '');              // Mot de passe MySQL (vide par défaut)
define('DB_NAME', 'km_services');   // Nom de la base de données
```

**Si vous avez un mot de passe MySQL**, modifiez `DB_PASS` en conséquence.

### Créer le Dossier Uploads

Assurez-vous que le dossier `public/uploads/` a les bonnes permissions :

1. Clic droit sur le dossier `public/uploads/`
2. Propriétés → Sécurité
3. Assurez-vous que les permissions d'écriture sont activées

## Étape 6 : Tester l'Installation

1. Ouvrez votre navigateur
2. Allez à : `http://localhost/kmservices/`
3. Vous devriez voir la page d'accueil

## Étape 7 : Se Connecter à l'Administration

1. Allez à : `http://localhost/kmservices/admin/login`
2. Identifiants par défaut :
   - **Utilisateur** : `admin`
   - **Mot de passe** : `password`
3. ⚠️ **IMPORTANT** : Changez le mot de passe immédiatement!

### Changer le Mot de Passe Admin

Pour changer le mot de passe de l'admin, connectez-vous à phpMyAdmin :

1. Ouvrez phpMyAdmin
2. Sélectionnez la base `km_services`
3. Ouvrez la table `users`
4. Cliquez sur "Modifier" pour l'utilisateur `admin`
5. Modifiez le champ `password`
6. Générez un hash bcrypt du nouveau mot de passe :

```php
<?php
echo password_hash("votre_nouveau_mot_de_passe", PASSWORD_BCRYPT);
?>
```

7. Collez le hash dans la base de données
8. Cliquez sur "Exécuter"

## Résolution des Problèmes

### Problème : "Erreur de connexion à la base de données"

**Solution** :
1. Vérifiez que MySQL est en cours d'exécution
2. Vérifiez les identifiants dans `config/config.php`
3. Assurez-vous que la base de données `km_services` existe
4. Vérifiez que le fichier `schema.sql` a été correctement importé

### Problème : "404 Not Found" ou problèmes de routage

**Solution** :
1. Vérifiez que mod_rewrite est activé dans Apache
2. Essayez de régénérer le fichier `.htaccess` ou vérifiez son contenu
3. Redémarrez Apache

### Problème : Les fichiers uploadés ne s'affichent pas

**Solution** :
1. Vérifiez que le dossier `public/uploads/` existe
2. Vérifiez les permissions du dossier (lecture/écriture)
3. Vérifiez le chemin dans `config/config.php`

### Problème : Erreur 403 Forbidden

**Solution** :
1. Vérifiez les permissions du dossier `kmservices`
2. Assurez-vous qu'Apache a accès au dossier
3. Vérifiez la configuration dans `httpd.conf`

## Points de Vérification Finaux

Avant de considérer l'installation terminée :

- [ ] Le site s'affiche correctement à `http://localhost/kmservices/`
- [ ] L'admin est accessible à `http://localhost/kmservices/admin/login`
- [ ] Vous pouvez vous connecter avec admin/password
- [ ] Vous pouvez voir les pages principales (Accueil, Services, Projets, etc.)
- [ ] Les formulaires de contact s'affichent
- [ ] Le dossier `uploads` existe et est accessible en écriture
- [ ] La base de données `km_services` existe et contient les tables

## Prochaines Étapes

Après l'installation réussie :

1. **Changez le mot de passe admin**
2. **Mettez à jour les informations de contact** dans les fichiers de contenu
3. **Ajoutez vos projets** via l'administration
4. **Uploadez vos produits** et images
5. **Testez tous les formulaires**
6. **Customisez le design** selon vos couleurs et logos

## Support

En cas de problème, consultez :
- Le fichier README.md
- Les fichiers de configuration
- Vérifiez les logs Apache et MySQL

---

**Support** : contact@kmservicesarlu.cd
**Documentation** : Consultez le README.md
