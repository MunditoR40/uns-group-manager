@echo off
title Gestor de Grupos UNS - MODO DEMO / PRESENTACION (Puerto 8000)
cls
echo =========================================================================
echo    GESTOR DE GRUPOS UNS - MODO DEMO ANONIMIZADO (PARA SUSTENTACION)
echo =========================================================================
echo Base de Datos: uns_groups_db (MySQL)
echo URL Local:     http://127.0.0.1:8000
echo Datos:         100%% Anonimizados / Ficticios (Apto para exposicion publica)
echo.
echo Presiona Ctrl+C para detener el servidor en cualquier momento.
echo Abriendo navegador en http://127.0.0.1:8000 ...
echo =========================================================================
start http://127.0.0.1:8000
php artisan serve --port=8000
pause