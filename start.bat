@echo off
title UpcycleConnect - Demarrage

echo.
echo  Demarrage de l'API Go...
cd /d "%~dp0api"
start "UpcycleConnect API" app.exe

timeout /t 2 /nobreak >nul

echo  API disponible sur http://localhost:8080
echo.
echo  Ouverture du navigateur...
start http://upcycleconnect.local

echo.
echo  Pour arreter l'API : fermer la fenetre "UpcycleConnect API"
echo  MAMP doit etre demarre avant de lancer ce script.
echo.
pause
