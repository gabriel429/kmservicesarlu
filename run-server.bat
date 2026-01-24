@echo off
REM Script de lancement rapide KM Services
REM Exécutez ce fichier .bat pour démarrer le serveur

echo.
echo ╔════════════════════════════════════════════════════╗
echo ║        KM Services - Serveur de Développement      ║
echo ╚════════════════════════════════════════════════════╝
echo.

REM Vérifier si nous sommes dans le bon répertoire
if not exist "config\config.php" (
    echo ERREUR: Fichier config.php non trouvé!
    echo Assurez-vous d'exécuter ce script depuis le dossier kmservices
    pause
    exit /b 1
)

REM Obtenir le répertoire courant
for /f %%i in ('cd') do set "CURRENT_DIR=%%i"

REM Démarrer le serveur PHP intégré
echo Démarrage du serveur PHP sur http://localhost:8000
echo.
echo Appuyez sur Ctrl+C pour arrêter le serveur
echo.

"C:\wamp64\bin\php\php8.4.0\php.exe" -n -d extension_dir="C:\wamp64\bin\php\php8.4.0\ext" -d extension=mysqli -S localhost:8000 -t public

pause
