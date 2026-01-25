# Script d'auto-commit et push pour Git
# Exécuter en boucle infinie pour surveiller les changements

$repoPath = (Get-Location).Path
$commitMessage = Get-Date -Format "yyyy-MM-dd HH:mm:ss - Auto-commit"

Write-Host "🔍 Surveillance des modifications du dépôt Git..." -ForegroundColor Cyan
Write-Host "Dossier: $repoPath" -ForegroundColor Green
Write-Host "Appuyez sur Ctrl+C pour arrêter" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

while ($true) {
    # Attendre 10 secondes avant de vérifier
    Start-Sleep -Seconds 10
    
    # Vérifier s'il y a des changements
    $status = & git status --porcelain
    
    if ($status) {
        Write-Host "`n$(Get-Date -Format 'HH:mm:ss') - Changements détectés!" -ForegroundColor Yellow
        Write-Host "Fichiers modifiés:" -ForegroundColor Cyan
        $status | ForEach-Object { Write-Host "  $_" }
        
        # Stage tous les changements
        & git add -A
        Write-Host "✅ Fichiers ajoutés au staging" -ForegroundColor Green
        
        # Commit
        $commitMsg = "Auto-commit: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
        & git commit -m $commitMsg
        Write-Host "✅ Commit effectué: $commitMsg" -ForegroundColor Green
        
        # Push
        & git push
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Push effectué avec succès" -ForegroundColor Green
        } else {
            Write-Host "⚠️  Erreur lors du push - Vérifiez votre connexion" -ForegroundColor Red
        }
        Write-Host "---" -ForegroundColor Gray
    }
}
