@echo off
setlocal EnableDelayedExpansion
chcp 65001 >nul
title Deploy PlacerSelecto

cd /d "%~dp0"

echo ================================================
echo            DEPLOY PLACERSELECTO
echo ================================================
echo.

if not exist "deploy-config.bat" (
    echo [ERROR] Falta deploy-config.bat
    pause & exit /b 1
)
call deploy-config.bat

echo Cambios detectados:
echo ----------------------------------------------
git status --short
echo ----------------------------------------------
echo.

set HAS_CHANGES=0
for /f %%i in ('git status --porcelain') do set HAS_CHANGES=1

if "!HAS_CHANGES!"=="0" (
    echo [INFO] Sin cambios locales.
    for /f %%i in ('git log "@{u}..HEAD" --oneline 2^>nul ^| find /c /v ""') do set UNPUSHED=%%i
    if "!UNPUSHED!"=="0" goto :DEPLOY
    echo [INFO] Hay commits sin push. Subiendo...
    goto :PUSH
)

set /p MSG="Mensaje del commit: "
if "!MSG!"=="" ( echo [ERROR] Mensaje vacio. & pause & exit /b 1 )

echo.
echo [1/4] Agregando archivos...
git add .

echo [2/4] Creando commit...
git commit -m "!MSG!"

:PUSH
echo [3/4] Subiendo a GitHub...
git push
if errorlevel 1 ( echo [ERROR] git push fallo. & pause & exit /b 1 )

:DEPLOY
echo [4/4] Disparando deploy en cPanel...
echo.

REM Probar endpoint de deployment (ejecuta .cpanel.yml que incluye git pull si lo configuras)
echo  - Llamando VersionControlDeployment::create...
curl -sk -X POST ^
  -H "Authorization: cpanel %CPANEL_USER%:%CPANEL_TOKEN%" ^
  --data-urlencode "repository_root=%REPO_PATH%" ^
  "https://%CPANEL_HOST%/execute/VersionControlDeployment/create" > .deploy-response.txt

echo  Respuesta:
echo ----------------------------------------------
type .deploy-response.txt
echo.
echo ----------------------------------------------

REM Comprobar si fue exitoso
findstr /c:"\"errors\":null" .deploy-response.txt >nul
if errorlevel 1 (
    findstr /c:"\"status\":1" .deploy-response.txt >nul
    if errorlevel 1 (
        echo.
        echo [ERROR] El deploy fallo. Revisa la respuesta arriba.
        del .deploy-response.txt
        pause
        exit /b 1
    )
)
del .deploy-response.txt

echo.
echo ================================================
echo  DEPLOY COMPLETO
echo  Sitio: https://placerselecto.com/
echo ================================================
echo.
pause
