@echo off
REM TWLan 2.A3 - 2025 Docker Edition - Windows Launcher
REM Automated setup, installation, and management

setlocal EnableDelayedExpansion
set "VERSION=2.A3-2025"
set "PROJECT_NAME=twlan-2a3-2025"
set "SCRIPT_DIR=%~dp0"
cd /d "%SCRIPT_DIR%"

REM Color codes
set "RED=[91m"
set "GREEN=[92m"
set "YELLOW=[93m"
set "BLUE=[94m"
set "MAGENTA=[95m"
set "CYAN=[96m"
set "WHITE=[97m"
set "RESET=[0m"

REM Configuration
set "DOCKER_REQUIRED_VERSION=20.10.0"
set "DEFAULT_PROFILE=modern"
set "LOG_FILE=twlan-launcher.log"

REM Functions
:PRINT_HEADER
echo.
echo %CYAN%=============================================%RESET%
echo %CYAN%     TWLan 2.A3 - 2025 Docker Edition      %RESET%
echo %CYAN%            Version: %VERSION%              %RESET%
echo %CYAN%=============================================%RESET%
echo.
goto :EOF

:LOG
echo [%date% %time%] %~1 >> "%LOG_FILE%"
goto :EOF

:PRINT_INFO
echo %BLUE%[INFO]%RESET% %~1
call :LOG "INFO: %~1"
goto :EOF

:PRINT_SUCCESS
echo %GREEN%[SUCCESS]%RESET% %~1
call :LOG "SUCCESS: %~1"
goto :EOF

:PRINT_WARNING
echo %YELLOW%[WARNING]%RESET% %~1
call :LOG "WARNING: %~1"
goto :EOF

:PRINT_ERROR
echo %RED%[ERROR]%RESET% %~1
call :LOG "ERROR: %~1"
goto :EOF

:CHECK_ADMIN
REM Check for admin privileges
net session >nul 2>&1
if %errorLevel% neq 0 (
    call :PRINT_WARNING "Not running as administrator. Some features may be limited."
    echo.
    echo Would you like to restart with admin privileges? (Y/N)
    choice /C YN /T 10 /D N >nul
    if !errorlevel! equ 1 (
        powershell -Command "Start-Process '%~f0' -Verb RunAs"
        exit /b
    )
)
goto :EOF

:CHECK_DOCKER
call :PRINT_INFO "Checking Docker installation..."

REM Check if Docker is installed
where docker >nul 2>&1
if %errorLevel% neq 0 (
    call :PRINT_ERROR "Docker is not installed!"
    echo.
    echo Docker Desktop is required to run TWLan.
    echo.
    echo Options:
    echo 1. Install Docker Desktop automatically
    echo 2. Open Docker download page
    echo 3. Exit
    echo.
    choice /C 123 /N /M "Select option: "
    
    if !errorlevel! equ 1 goto :INSTALL_DOCKER
    if !errorlevel! equ 2 (
        start https://www.docker.com/products/docker-desktop/
        call :PRINT_INFO "Please install Docker Desktop and run this script again."
        pause
        exit /b 1
    )
    exit /b 1
)

REM Check if Docker is running
docker info >nul 2>&1
if %errorLevel% neq 0 (
    call :PRINT_WARNING "Docker is installed but not running."
    call :PRINT_INFO "Starting Docker Desktop..."
    
    REM Try to start Docker Desktop
    start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe" 2>nul
    if %errorLevel% neq 0 (
        start "" "%ProgramFiles%\Docker\Docker\Docker Desktop.exe" 2>nul
    )
    
    call :PRINT_INFO "Waiting for Docker to start (this may take a minute)..."
    set "tries=60"
    :DOCKER_WAIT_LOOP
    docker info >nul 2>&1
    if %errorLevel% equ 0 (
        call :PRINT_SUCCESS "Docker is now running!"
        goto :EOF
    )
    
    timeout /t 2 /nobreak >nul
    set /a tries-=1
    if !tries! gtr 0 goto :DOCKER_WAIT_LOOP
    
    call :PRINT_ERROR "Docker failed to start. Please start Docker Desktop manually."
    pause
    exit /b 1
)

call :PRINT_SUCCESS "Docker is installed and running!"

REM Check Docker version
for /f "tokens=3" %%i in ('docker --version 2^>nul') do set "DOCKER_VERSION=%%i"
call :PRINT_INFO "Docker version: %DOCKER_VERSION%"

REM Check Docker Compose
docker compose version >nul 2>&1
if %errorLevel% neq 0 (
    call :PRINT_WARNING "Docker Compose v2 not found, trying legacy version..."
    docker-compose version >nul 2>&1
    if %errorLevel% neq 0 (
        call :PRINT_ERROR "Docker Compose is not available!"
        exit /b 1
    )
    set "COMPOSE_CMD=docker-compose"
) else (
    set "COMPOSE_CMD=docker compose"
)

goto :EOF

:INSTALL_DOCKER
call :PRINT_INFO "Installing Docker Desktop..."

REM Download Docker Desktop installer
set "DOCKER_URL=https://desktop.docker.com/win/main/amd64/Docker%%20Desktop%%20Installer.exe"
set "INSTALLER_PATH=%TEMP%\DockerDesktopInstaller.exe"

call :PRINT_INFO "Downloading Docker Desktop installer..."
powershell -Command "Invoke-WebRequest -Uri '%DOCKER_URL%' -OutFile '%INSTALLER_PATH%'"

if not exist "%INSTALLER_PATH%" (
    call :PRINT_ERROR "Failed to download Docker Desktop installer!"
    exit /b 1
)

call :PRINT_INFO "Running Docker Desktop installer..."
"%INSTALLER_PATH%" install --quiet --accept-license

if %errorLevel% neq 0 (
    call :PRINT_ERROR "Docker Desktop installation failed!"
    exit /b 1
)

call :PRINT_SUCCESS "Docker Desktop installed successfully!"
call :PRINT_INFO "Please restart your computer and run this script again."
pause
exit /b 0

:CHECK_WSL
call :PRINT_INFO "Checking WSL2 installation..."

wsl --version >nul 2>&1
if %errorLevel% neq 0 (
    call :PRINT_WARNING "WSL2 is not installed. Installing..."
    
    REM Enable WSL feature
    dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart >nul
    
    REM Enable Virtual Machine Platform
    dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart >nul
    
    REM Download and install WSL2 kernel
    call :PRINT_INFO "Downloading WSL2 kernel..."
    powershell -Command "Invoke-WebRequest -Uri 'https://wslstorestorage.blob.core.windows.net/wslblob/wsl_update_x64.msi' -OutFile '%TEMP%\wsl_update_x64.msi'"
    msiexec /i "%TEMP%\wsl_update_x64.msi" /quiet
    
    REM Set WSL2 as default
    wsl --set-default-version 2 >nul 2>&1
    
    call :PRINT_SUCCESS "WSL2 installed successfully!"
)

goto :EOF

:FIND_AVAILABLE_PORT
set "base_port=%~1"
set "port_found=0"
set "current_port=%base_port%"

:PORT_CHECK_LOOP
netstat -an | findstr ":%current_port%" >nul 2>&1
if %errorLevel% neq 0 (
    set "port_found=1"
    set "%~2=%current_port%"
    goto :EOF
)

set /a current_port+=1
if %current_port% lss %base_port%+20 goto :PORT_CHECK_LOOP

REM Fallback to random port
set /a current_port=10000+%RANDOM%%%10000
set "%~2=%current_port%"
goto :EOF

:SETUP_ENVIRONMENT
call :PRINT_INFO "Setting up environment..."

REM Create .env file with port configuration
call :FIND_AVAILABLE_PORT 8080 WEB_PORT
call :FIND_AVAILABLE_PORT 8200 LEGACY_PORT
call :FIND_AVAILABLE_PORT 8300 MODERN_PORT
call :FIND_AVAILABLE_PORT 3307 DB_PORT
call :FIND_AVAILABLE_PORT 6379 REDIS_PORT
call :FIND_AVAILABLE_PORT 8100 ADMIN_PORT

echo # TWLan 2.A3 - 2025 Docker Edition Configuration > .env
echo # Generated: %date% %time% >> .env
echo. >> .env
echo # Ports (automatically selected to avoid conflicts) >> .env
echo TWLAN_WEB_PORT=%WEB_PORT% >> .env
echo TWLAN_LEGACY_PORT=%LEGACY_PORT% >> .env
echo TWLAN_MODERN_PORT=%MODERN_PORT% >> .env
echo TWLAN_DB_PORT=%DB_PORT% >> .env
echo TWLAN_REDIS_PORT=%REDIS_PORT% >> .env
echo TWLAN_ADMIN_PORT=%ADMIN_PORT% >> .env
echo. >> .env
echo # Database Configuration >> .env
echo DB_ROOT_PASSWORD=twlan_root_2025 >> .env
echo DB_NAME=twlan >> .env
echo DB_USER=twlan >> .env
echo DB_PASSWORD=twlan_secure_2025 >> .env
echo. >> .env
echo # PHP Configuration >> .env
echo PHP_MEMORY_LIMIT=256M >> .env
echo PHP_MAX_EXECUTION_TIME=300 >> .env
echo. >> .env
echo # Monitoring >> .env
echo GRAFANA_PASSWORD=admin >> .env
echo TWLAN_PROMETHEUS_PORT=9090 >> .env
echo TWLAN_GRAFANA_PORT=3000 >> .env
echo. >> .env
echo # Other >> .env
echo TZ=UTC >> .env
echo BACKUP_SCHEDULE="0 3 * * *" >> .env
echo RETENTION_DAYS=7 >> .env

call :PRINT_SUCCESS "Environment configured!"
call :PRINT_INFO "Web port: %WEB_PORT%"
call :PRINT_INFO "Legacy port: %LEGACY_PORT%"
call :PRINT_INFO "Modern port: %MODERN_PORT%"

goto :EOF

:BUILD_CONTAINERS
call :PRINT_INFO "Building Docker containers..."

%COMPOSE_CMD% build --no-cache

if %errorLevel% neq 0 (
    call :PRINT_ERROR "Failed to build containers!"
    exit /b 1
)

call :PRINT_SUCCESS "Containers built successfully!"
goto :EOF

:START_SERVICES
call :PRINT_INFO "Starting TWLan services..."

REM Determine which profile to use
if "%~1"=="" (
    set "PROFILE=%DEFAULT_PROFILE%"
) else (
    set "PROFILE=%~1"
)

call :PRINT_INFO "Using profile: %PROFILE%"

if "%PROFILE%"=="legacy" (
    %COMPOSE_CMD% --profile legacy up -d
) else if "%PROFILE%"=="full" (
    %COMPOSE_CMD% --profile full up -d
) else (
    %COMPOSE_CMD% up -d
)

if %errorLevel% neq 0 (
    call :PRINT_ERROR "Failed to start services!"
    exit /b 1
)

call :PRINT_SUCCESS "Services started successfully!"

REM Wait for services to be ready
call :PRINT_INFO "Waiting for services to be ready..."
timeout /t 5 /nobreak >nul

REM Check service health
call :CHECK_SERVICE_HEALTH

goto :EOF

:CHECK_SERVICE_HEALTH
call :PRINT_INFO "Checking service health..."

REM Check modern web service
curl -s -o nul -w "%%{http_code}" http://localhost:%WEB_PORT%/health >nul 2>&1
if %errorLevel% equ 0 (
    call :PRINT_SUCCESS "Modern web service is healthy!"
) else (
    call :PRINT_WARNING "Modern web service may still be starting..."
)

REM Check database
%COMPOSE_CMD% exec -T twlan-db mysql -u root -ptwlan_root_2025 -e "SELECT 1" >nul 2>&1
if %errorLevel% equ 0 (
    call :PRINT_SUCCESS "Database is healthy!"
) else (
    call :PRINT_WARNING "Database may still be starting..."
)

goto :EOF

:STOP_SERVICES
call :PRINT_INFO "Stopping TWLan services..."

%COMPOSE_CMD% down

if %errorLevel% neq 0 (
    call :PRINT_ERROR "Failed to stop services!"
    exit /b 1
)

call :PRINT_SUCCESS "Services stopped!"
goto :EOF

:SHOW_LOGS
call :PRINT_INFO "Showing logs (press Ctrl+C to exit)..."
%COMPOSE_CMD% logs -f
goto :EOF

:SHOW_STATUS
call :PRINT_INFO "Service Status:"
echo.
%COMPOSE_CMD% ps
echo.

REM Show URLs
echo %CYAN%Access URLs:%RESET%
echo   Modern Web:    http://localhost:%WEB_PORT%
echo   Legacy Server: http://localhost:%LEGACY_PORT%
echo   phpMyAdmin:    http://localhost:%ADMIN_PORT%
echo   Database:      localhost:%DB_PORT%
echo.

goto :EOF

:BACKUP_DATA
call :PRINT_INFO "Creating backup..."

set "BACKUP_DIR=backups\%date:~-4%-%date:~4,2%-%date:~7,2%_%time:~0,2%-%time:~3,2%"
set "BACKUP_DIR=%BACKUP_DIR: =0%"
mkdir "%BACKUP_DIR%" 2>nul

REM Backup database
call :PRINT_INFO "Backing up database..."
%COMPOSE_CMD% exec -T twlan-db mysqldump -u root -ptwlan_root_2025 --all-databases > "%BACKUP_DIR%\database.sql"

REM Backup volumes
call :PRINT_INFO "Backing up volumes..."
docker run --rm -v twlan-db-data:/source -v "%CD%\%BACKUP_DIR%":/backup alpine tar czf /backup/db-data.tar.gz -C /source .
docker run --rm -v twlan-uploads:/source -v "%CD%\%BACKUP_DIR%":/backup alpine tar czf /backup/uploads.tar.gz -C /source .

call :PRINT_SUCCESS "Backup completed: %BACKUP_DIR%"
goto :EOF

:RESTORE_BACKUP
call :PRINT_INFO "Available backups:"
dir /b backups 2>nul

echo.
set /p BACKUP_NAME="Enter backup folder name to restore: "

if not exist "backups\%BACKUP_NAME%" (
    call :PRINT_ERROR "Backup not found!"
    exit /b 1
)

call :PRINT_WARNING "This will overwrite current data! Continue? (Y/N)"
choice /C YN /T 10 /D N >nul
if !errorlevel! neq 1 (
    call :PRINT_INFO "Restore cancelled."
    exit /b 0
)

REM Stop services
call :STOP_SERVICES

REM Restore database
if exist "backups\%BACKUP_NAME%\database.sql" (
    call :PRINT_INFO "Restoring database..."
    %COMPOSE_CMD% up -d twlan-db
    timeout /t 10 /nobreak >nul
    %COMPOSE_CMD% exec -T twlan-db mysql -u root -ptwlan_root_2025 < "backups\%BACKUP_NAME%\database.sql"
)

REM Restore volumes
if exist "backups\%BACKUP_NAME%\db-data.tar.gz" (
    call :PRINT_INFO "Restoring database volume..."
    docker run --rm -v twlan-db-data:/target -v "%CD%\backups\%BACKUP_NAME%":/backup alpine tar xzf /backup/db-data.tar.gz -C /target
)

if exist "backups\%BACKUP_NAME%\uploads.tar.gz" (
    call :PRINT_INFO "Restoring uploads volume..."
    docker run --rm -v twlan-uploads:/target -v "%CD%\backups\%BACKUP_NAME%":/backup alpine tar xzf /backup/uploads.tar.gz -C /target
)

call :PRINT_SUCCESS "Restore completed!"

REM Restart services
call :START_SERVICES

goto :EOF

:MAIN_MENU
cls
call :PRINT_HEADER

echo Select an option:
echo.
echo %GREEN%1.%RESET% Start TWLan (Modern)
echo %GREEN%2.%RESET% Start TWLan (Legacy)
echo %GREEN%3.%RESET% Start TWLan (Full - All Services)
echo %YELLOW%4.%RESET% Stop All Services
echo %BLUE%5.%RESET% Show Status
echo %BLUE%6.%RESET% Show Logs
echo %CYAN%7.%RESET% Create Backup
echo %CYAN%8.%RESET% Restore Backup
echo %MAGENTA%9.%RESET% Open Web Interface
echo %MAGENTA%A.%RESET% Open Admin Panel
echo %RED%0.%RESET% Exit
echo.

choice /C 1234567890A /N /M "Enter your choice: "

if %errorlevel% equ 1 (
    call :START_SERVICES modern
    timeout /t 3 /nobreak >nul
    start http://localhost:%WEB_PORT%
) else if %errorlevel% equ 2 (
    call :START_SERVICES legacy
    timeout /t 3 /nobreak >nul
    start http://localhost:%LEGACY_PORT%
) else if %errorlevel% equ 3 (
    call :START_SERVICES full
    timeout /t 3 /nobreak >nul
    start http://localhost:%WEB_PORT%
) else if %errorlevel% equ 4 (
    call :STOP_SERVICES
) else if %errorlevel% equ 5 (
    call :SHOW_STATUS
    pause
) else if %errorlevel% equ 6 (
    call :SHOW_LOGS
) else if %errorlevel% equ 7 (
    call :BACKUP_DATA
    pause
) else if %errorlevel% equ 8 (
    call :RESTORE_BACKUP
    pause
) else if %errorlevel% equ 9 (
    start http://localhost:%WEB_PORT%
) else if %errorlevel% equ 11 (
    start http://localhost:%ADMIN_PORT%
) else if %errorlevel% equ 10 (
    exit /b 0
)

goto :MAIN_MENU

REM ============================================
REM Main Execution
REM ============================================

:MAIN
call :PRINT_HEADER
call :CHECK_ADMIN
call :CHECK_DOCKER
call :CHECK_WSL
call :SETUP_ENVIRONMENT

REM Check if first run
if not exist ".twlan-initialized" (
    call :PRINT_INFO "First run detected. Building containers..."
    call :BUILD_CONTAINERS
    echo. > .twlan-initialized
)

goto :MAIN_MENU

REM Entry point
:ENTRY
call :MAIN
