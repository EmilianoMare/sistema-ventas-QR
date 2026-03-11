@echo off
REM Ejecuta el script PowerShell para actualizar el tipo de cambio
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0update_cambio.ps1"
exit /b %ERRORLEVEL%
