# Démarrage Rapide - 5 Minutes

## ✅ Avant de Commencer
- WAMP/XAMPP installé et en cours d'exécution
- PHP et MySQL fonctionnent
- Le dossier `kmservices` est dans `www/` ou `htdocs/`

---

## 1️⃣ Créer la Base de Données (1 min)

### Via phpMyAdmin
```
1. Allez à http://localhost/phpmyadmin
2. Nouvelle base de données → "km_services" → Créer
3. Sélectionnez "km_services"
4. Onglet "Importer"
5. Fichier → database/schema.sql
6. Exécuter
```

---

## 2️⃣ Configurer l'Application (1 min)

Ouvrez le fichier **config/config.php** et vérifiez :

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');              // Votre mot de passe MySQL
define('DB_NAME', 'km_services');
```

Sauvegardez le fichier.

---

## 3️⃣ Lancer le Site (1 min)

Ouvrez votre navigateur et allez à :

- **Site public** : http://localhost/kmservices/
- **Admin** : http://localhost/kmservices/admin/login

---

## 4️⃣ Se Connecter à l'Admin (1 min)

**Admin Login** :
- Utilisateur : `admin`
- Mot de passe : `password`

⚠️ **Changez ce mot de passe immédiatement!**

---

## 5️⃣ Commencer à Utiliser (1 min)

### Dashboard
```
Admin → Tableau de Bord
```

### Ajouter un Projet
```
Admin → Projets → Nouveau Projet
Remplissez les champs et publiez
```

### Ajouter un Produit
```
Admin → Produits → Nouveau Produit
Remplissez les champs et publiez
```

---

## 🎯 Premiers Pas Recommandés

1. ✅ Changez le mot de passe admin
2. ✅ Mettez à jour les coordonnées (contact@kmservices.cd)
3. ✅ Ajoutez vos premiers projets
4. ✅ Ajoutez vos produits
5. ✅ Testez les formulaires

---

## 📞 Besoin d'Aide?

- Consultez **INSTALLATION.md** pour plus de détails
- Consultez **README.md** pour la documentation complète
- Vérifiez que MySQL et Apache sont lancés
- Vérifiez les permissions du dossier `uploads/`

---

## 🔄 Redémarrer les Services

### WAMP
Cliquez sur le logo WAMP → Redémarrer tous les services

### XAMPP
Control Panel → Stop Apache & MySQL → Start

---

**Vous êtes prêt!** 🚀
