@echo off
REM TWLan 2.A3 + AI Bots - One-Click Launcher for Windows
REM ========================================================

setlocal enabledelayedexpansion

echo.
echo =========================================
echo  TWLan 2.A3 - 2025 Edition + AI Bots
echo =========================================
echo.

REM Check Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not running!
    echo Please start Docker Desktop and try again.
    pause
    exit /b 1
)

echo [INFO] Docker is running...
echo.

REM Check if TWLan legacy files exist
if not exist "htdocs" (
    echo [WARNING] TWLan legacy files not found in current directory!
    echo Expected: htdocs/, lib/, bin/ folders
    echo.
    set /p CONTINUE="Continue anyway? (y/n): "
    if /i not "!CONTINUE!"=="y" exit /b 1
)

echo.
echo Select deployment mode:
echo.
echo   1) Legacy TWLan only (original 2.A3)
echo   2) Modern + Database only
echo   3) Full stack (Legacy + Modern + AI Bots)
echo   4) AI Bots only (requires existing database)
echo   5) Stop all services
echo   6) View logs
echo.
set /p CHOICE="Your choice (1-6): "

if "%CHOICE%"=="1" (
    echo [INFO] Starting Legacy TWLan...
    docker compose --profile legacy up -d
    echo.
    echo [SUCCESS] Legacy TWLan started!
    echo Access at: http://localhost:8200
)

if "%CHOICE%"=="2" (
    echo [INFO] Starting Modern stack...
    docker compose up -d twlan-db twlan-redis
    echo.
    echo [SUCCESS] Modern stack started!
    echo Database: localhost:3307
)

if "%CHOICE%"=="3" (
    echo [INFO] Starting FULL STACK (this may take a minute)...
    docker compose --profile full up -d
    echo.
    echo [SUCCESS] Full stack started!
    echo.
    echo Services:
    echo   - Legacy TWLan: http://localhost:8200
    echo   - Database: localhost:3307
    echo   - AI Bots Metrics: http://localhost:9090
)

if "%CHOICE%"=="4" (
    echo [INFO] Starting AI Bots only...
    
    REM Check .env file
    if not exist "ai-bots\.env" (
        echo [WARNING] ai-bots\.env not found!
        echo Creating from .env.example...
        copy "ai-bots\.env.example" "ai-bots\.env"
        echo.
        echo [INFO] Please edit ai-bots\.env with your database credentials.
        echo Then run this script again.
        pause
        exit /b 1
    )
    
    docker compose up -d ai-bots
    echo.
    echo [SUCCESS] AI Bots started!
    echo Metrics: http://localhost:9090
)

if "%CHOICE%"=="5" (
    echo [INFO] Stopping all services...
    docker compose --profile full down
    echo.
    echo [SUCCESS] All services stopped.
)

if "%CHOICE%"=="6" (
    echo [INFO] Showing logs (Ctrl+C to exit)...
    echo.
    docker compose --profile full logs -f --tail=100
)

echo.
echo =========================================
echo.
pause
