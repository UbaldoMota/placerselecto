@echo off
chcp 65001 >nul
cd /d "%~dp0"

if not exist "deploy-config.bat" ( echo [ERROR] Falta deploy-config.bat & pause & exit /b 1 )
call deploy-config.bat

echo ================================================
echo  TEST 1: Verificar que curl funciona
echo ================================================
curl --version
echo.

echo ================================================
echo  TEST 2: Conectividad basica con cPanel
echo ================================================
curl -sk -o nul -w "HTTP Status: %%{http_code}\n" https://%CPANEL_HOST%/
echo.

echo ================================================
echo  TEST 3: Listar repos (UAPI VersionControl::retrieve)
echo ================================================
echo URL: https://%CPANEL_HOST%/execute/VersionControl/retrieve
echo.
curl -sk -v ^
  -H "Authorization: cpanel %CPANEL_USER%:%CPANEL_TOKEN%" ^
  "https://%CPANEL_HOST%/execute/VersionControl/retrieve" 2>&1
echo.
echo.

echo ================================================
echo  TEST 4: Mismo con GET y param
echo ================================================
curl -sk ^
  -H "Authorization: cpanel %CPANEL_USER%:%CPANEL_TOKEN%" ^
  "https://%CPANEL_HOST%/execute/VersionControlDeployment/create?repository_root=/home/placerse/repositories/placerselecto"
echo.

echo.
pause
