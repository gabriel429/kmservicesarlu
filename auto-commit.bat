@echo off
REM Script de lancement du moniteur Git auto-commit
REM Lance le script PowerShell en arrière-plan

setlocal enabledelayedexpansion

echo Démarrage du moniteur Git auto-commit...
echo.

REM Obtenir le chemin du répertoire courant
cd /d "%~dp0"

REM Exécuter le script PowerShell avec les droits d'exécution
powershell -NoProfile -ExecutionPolicy Bypass -File "auto-commit.ps1"

pause
