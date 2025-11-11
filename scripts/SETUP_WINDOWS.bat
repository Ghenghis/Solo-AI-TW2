@echo off
REM TWLan 2025 Docker - Windows Setup Script
REM This script copies all files to your desired location

setlocal EnableDelayedExpansion

echo ============================================
echo TWLan 2025 Docker - Setup Script
echo ============================================
echo.

REM Set target directory
set "TARGET_DIR=C:\Users\Admin\Downloads\twlan-2025-docker"

echo Target directory: %TARGET_DIR%
echo.

REM Create target directory if it doesn't exist
if not exist "%TARGET_DIR%" (
    echo Creating directory: %TARGET_DIR%
    mkdir "%TARGET_DIR%"
    if !errorlevel! neq 0 (
        echo ERROR: Failed to create directory
        pause
        exit /b 1
    )
)

echo Copying TWLan Docker files to %TARGET_DIR%...
echo.

REM Copy all files from current directory to target
xcopy /E /I /Y "." "%TARGET_DIR%\" >nul 2>&1

if !errorlevel! equ 0 (
    echo SUCCESS: Files copied to %TARGET_DIR%
    echo.
    
    REM Create directory structure
    mkdir "%TARGET_DIR%\TWLan-2.A3-linux64" 2>nul
    mkdir "%TARGET_DIR%\config" 2>nul
    mkdir "%TARGET_DIR%\app" 2>nul
    mkdir "%TARGET_DIR%\diagrams" 2>nul
    
    echo Directory structure created!
    echo.
    echo ============================================
    echo NEXT STEPS:
    echo ============================================
    echo.
    echo 1. Extract your TWLan-2.A3-linux64.zip contents to:
    echo    %TARGET_DIR%\TWLan-2.A3-linux64\
    echo.
    echo 2. Run the launcher:
    echo    %TARGET_DIR%\scripts\start-windows.bat
    echo.
    echo 3. The game will start automatically!
    echo.
    echo Would you like to open the target folder now? (Y/N)
    choice /C YN /T 10 /D Y >nul
    if !errorlevel! equ 1 (
        explorer "%TARGET_DIR%"
    )
) else (
    echo ERROR: Failed to copy files
    echo Please try running this script as Administrator
    pause
    exit /b 1
)

echo.
echo Setup complete! Press any key to exit...
pause >nul
