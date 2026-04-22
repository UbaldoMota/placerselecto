@echo off
setlocal EnableDelayedExpansion
chcp 65001 >nul
title Deploy PlacerSelecto

cd /d "%~dp0"

echo ================================================
echo            DEPLOY PLACERSELECTO
echo ================================================
echo.

REM Cargar config local
if not exist ".deploy-config" (
    echo [ERROR] Falta .deploy-config
    echo Copia .deploy-config.example y renombralo a .deploy-config
    echo Rellena CPANEL_HOST, CPANEL_USER, CPANEL_TOKEN y REPO_PATH.
    pause
    exit /b 1
)
call .deploy-config

REM Mostrar archivos modificados
echo Cambios detectados:
echo ----------------------------------------------
git status --short
echo ----------------------------------------------
echo.

REM Verificar si hay cambios pendientes
git status --porcelain >nul
for /f %%i in ('git status --porcelain') do set HAS_CHANGES=1
if not defined HAS_CHANGES (
    echo [INFO] No hay cambios para commitear.
    echo Verificando si hay commits sin push...
    for /f %%i in ('git log "@{u}..HEAD" --oneline 2^>nul ^| find /c /v ""') do set UNPUSHED=%%i
    if "!UNPUSHED!"=="0" (
        echo [INFO] Repo sincronizado con remoto. Disparando deploy en cPanel...
        goto :DEPLOY
    )
    echo [INFO] Hay commits sin push. Subiendo...
    goto :PUSH
)

REM Pedir mensaje de commit
set /p MSG="Mensaje del commit: "
if "!MSG!"=="" (
    echo [ERROR] El mensaje no puede estar vacio.
    pause
    exit /b 1
)

echo.
echo [1/4] Agregando archivos...
git add .
if errorlevel 1 ( echo [ERROR] git add fallo. & pause & exit /b 1 )

echo [2/4] Creando commit...
git commit -m "!MSG!"
if errorlevel 1 ( echo [ERROR] git commit fallo. & pause & exit /b 1 )

:PUSH
echo [3/4] Subiendo a GitHub...
git push
if errorlevel 1 ( echo [ERROR] git push fallo. & pause & exit /b 1 )

:DEPLOY
echo [4/4] Pull + Deploy en cPanel...
echo.

REM URL-encode el repo_path (reemplazar / por %%2F)
set ENCODED_PATH=%REPO_PATH:/=%%2F%

REM 1. Pull desde GitHub al servidor
echo  - Pull desde GitHub...
curl -s -X GET ^
  -H "Authorization: cpanel %CPANEL_USER%:%CPANEL_TOKEN%" ^
  "https://%CPANEL_HOST%/execute/VersionControl/update?repository_root=%ENCODED_PATH%" > .deploy-result.json
type .deploy-result.json | findstr /c:"\"status\":1" >nul
if errorlevel 1 (
    echo [ERROR] Fallo el pull. Respuesta cPanel:
    type .deploy-result.json
    del .deploy-result.json
    pause
    exit /b 1
)

REM 2. Deploy HEAD commit
echo  - Deploy HEAD Commit...
curl -s -X POST ^
  -H "Authorization: cpanel %CPANEL_USER%:%CPANEL_TOKEN%" ^
  "https://%CPANEL_HOST%/execute/VersionControlDeployment/create?repository_root=%ENCODED_PATH%" > .deploy-result.json
type .deploy-result.json | findstr /c:"\"status\":1" >nul
if errorlevel 1 (
    echo [ERROR] Fallo el deploy. Respuesta cPanel:
    type .deploy-result.json
    del .deploy-result.json
    pause
    exit /b 1
)
del .deploy-result.json

echo.
echo ================================================
echo  DEPLOY COMPLETO
echo  Sitio actualizado: https://placerselecto.com/
echo ================================================
echo.
pause
