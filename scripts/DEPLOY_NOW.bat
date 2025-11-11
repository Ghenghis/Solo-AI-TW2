@echo off
REM TWLan Docker 2025 - Quick Deploy to C:\Users\Admin\Downloads\twlan-docker-2025

setlocal EnableDelayedExpansion

echo ============================================
echo   TWLan Docker 2025 - Quick Deploy
echo ============================================
echo.

set "TARGET=C:\Users\Admin\Downloads\twlan-docker-2025"

REM Create target directory
echo Creating %TARGET%...
mkdir "%TARGET%" 2>nul
mkdir "%TARGET%\docker" 2>nul
mkdir "%TARGET%\scripts" 2>nul
mkdir "%TARGET%\utils" 2>nul
mkdir "%TARGET%\docs" 2>nul
mkdir "%TARGET%\config" 2>nul
mkdir "%TARGET%\TWLan-2.A3-linux64" 2>nul

echo.
echo ✅ Directory structure created!
echo.
echo ============================================
echo  MANUAL STEPS REQUIRED:
echo ============================================
echo.
echo 1. DOWNLOAD the project folder from Claude
echo.
echo 2. COPY all files to: %TARGET%
echo.
echo 3. EXTRACT your TWLan-2.A3-linux64.zip to:
echo    %TARGET%\TWLan-2.A3-linux64\
echo.
echo 4. RUN the launcher:
echo    %TARGET%\scripts\start-windows.bat
echo.
echo ============================================
echo  WHAT YOU GET:
echo ============================================
echo.
echo ✅ Automatic Docker installation
echo ✅ Smart port management (no conflicts)
echo ✅ One-click start/stop
echo ✅ Both Legacy and Modern modes
echo ✅ Web interface at http://localhost:8080
echo ✅ Admin panel at http://localhost:8100
echo.
echo Press any key to open the target folder...
pause >nul

explorer "%TARGET%"

echo.
echo Folder opened! Copy your files there.
echo Press any key to exit...
pause >nul
