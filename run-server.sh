#!/bin/bash
# Script de lancement rapide KM Services
# Exécutez: bash run-server.sh

echo ""
echo "╔════════════════════════════════════════════════════╗"
echo "║        KM Services - Serveur de Développement      ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""

# Vérifier si nous sommes dans le bon répertoire
if [ ! -f "config/config.php" ]; then
    echo "ERREUR: Fichier config.php non trouvé!"
    echo "Assurez-vous d'exécuter ce script depuis le dossier kmservices"
    exit 1
fi

# Vérifier si PHP est installé
if ! command -v php &> /dev/null; then
    echo "ERREUR: PHP n'est pas installé ou non trouvable!"
    echo "Veuillez installer PHP et ajouter le à votre PATH"
    exit 1
fi

# Démarrer le serveur PHP intégré
echo "Démarrage du serveur PHP sur http://localhost:8000"
echo ""
echo "Appuyez sur Ctrl+C pour arrêter le serveur"
echo ""

php -S localhost:8000 -t public

exit 0
