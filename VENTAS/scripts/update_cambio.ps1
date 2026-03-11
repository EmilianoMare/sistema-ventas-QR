<#
  Script: update_cambio.ps1
  Llama al endpoint /app/ajax/cambioAjax.php?obtener=cambio_api y guarda un log
  Uso sugerido desde Task Scheduler:
    PowerShell -NoProfile -ExecutionPolicy Bypass -File "C:\xampp\htdocs\VENTAS\scripts\update_cambio.ps1"
#>

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Definition
$logDir = Join-Path $projectRoot "..\logs" | Resolve-Path -ErrorAction SilentlyContinue
if(-not $logDir){
    $logDir = Join-Path $projectRoot "..\logs"
    New-Item -ItemType Directory -Path $logDir -Force | Out-Null
}
$logFile = Join-Path $logDir "cambio_update.log"

$uri = "http://localhost/VENTAS/app/ajax/cambioAjax.php?obtener=cambio_api"
try {
    $resp = Invoke-RestMethod -Uri $uri -TimeoutSec 30
    $line = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - SUCCESS - $($resp | ConvertTo-Json -Compress)"
} catch {
    $err = $_.Exception.Message -replace '\r|\n',' '
    $line = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - ERROR - $err"
}

Add-Content -Path $logFile -Value $line
Write-Output $line
