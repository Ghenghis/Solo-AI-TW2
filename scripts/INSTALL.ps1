# TWLan 2025 Docker - PowerShell Setup Script
# Run this as Administrator: Right-click -> Run with PowerShell

$ErrorActionPreference = "Stop"

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   TWLan 2025 Docker - Automated Setup    " -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$targetDir = "C:\Users\Admin\Downloads\twlan-2025-docker"
$currentDir = Get-Location

# Function to print colored messages
function Write-Status {
    param([string]$Message, [string]$Type = "Info")
    
    switch ($Type) {
        "Success" { Write-Host "✅ $Message" -ForegroundColor Green }
        "Error" { Write-Host "❌ $Message" -ForegroundColor Red }
        "Warning" { Write-Host "⚠️ $Message" -ForegroundColor Yellow }
        "Info" { Write-Host "ℹ️ $Message" -ForegroundColor Blue }
    }
}

# Create target directory
Write-Status "Creating target directory: $targetDir" "Info"
if (-not (Test-Path $targetDir)) {
    New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
    Write-Status "Directory created successfully" "Success"
} else {
    Write-Status "Directory already exists" "Warning"
}

# Copy all files
Write-Status "Copying project files to $targetDir..." "Info"
try {
    # Copy everything from current directory
    Get-ChildItem -Path $currentDir -Recurse | ForEach-Object {
        $targetPath = $_.FullName.Replace($currentDir, $targetDir)
        
        if ($_.PSIsContainer) {
            # Create directory
            if (-not (Test-Path $targetPath)) {
                New-Item -ItemType Directory -Path $targetPath -Force | Out-Null
            }
        } else {
            # Copy file
            $targetFolder = Split-Path $targetPath -Parent
            if (-not (Test-Path $targetFolder)) {
                New-Item -ItemType Directory -Path $targetFolder -Force | Out-Null
            }
            Copy-Item -Path $_.FullName -Destination $targetPath -Force
        }
    }
    Write-Status "Files copied successfully" "Success"
} catch {
    Write-Status "Failed to copy files: $_" "Error"
    exit 1
}

# Create required directories
Write-Status "Creating project structure..." "Info"
$directories = @(
    "$targetDir\TWLan-2.A3-linux64",
    "$targetDir\TWLan-2.A3-linux64\bin",
    "$targetDir\TWLan-2.A3-linux64\htdocs",
    "$targetDir\TWLan-2.A3-linux64\lib",
    "$targetDir\TWLan-2.A3-linux64\db",
    "$targetDir\config",
    "$targetDir\config\nginx",
    "$targetDir\config\mariadb",
    "$targetDir\config\redis",
    "$targetDir\app",
    "$targetDir\app\public",
    "$targetDir\app\src",
    "$targetDir\diagrams",
    "$targetDir\backups"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }
}
Write-Status "Project structure created" "Success"

# Check if TWLan files exist
$twlanZip = Get-ChildItem -Path "$env:USERPROFILE\Downloads" -Filter "*TWLan*.zip" -ErrorAction SilentlyContinue | Select-Object -First 1

if ($twlanZip) {
    Write-Status "Found TWLan zip: $($twlanZip.Name)" "Info"
    $extract = Read-Host "Extract TWLan files automatically? (Y/N)"
    
    if ($extract -eq 'Y' -or $extract -eq 'y') {
        Write-Status "Extracting TWLan files..." "Info"
        try {
            Expand-Archive -Path $twlanZip.FullName -DestinationPath "$targetDir\TWLan-2.A3-linux64" -Force
            Write-Status "TWLan files extracted successfully" "Success"
        } catch {
            Write-Status "Failed to extract: $_" "Warning"
            Write-Status "Please extract manually to: $targetDir\TWLan-2.A3-linux64" "Info"
        }
    }
} else {
    Write-Status "TWLan zip not found in Downloads" "Warning"
    Write-Host ""
    Write-Host "Please extract your TWLan-2.A3-linux64.zip to:" -ForegroundColor Yellow
    Write-Host "$targetDir\TWLan-2.A3-linux64" -ForegroundColor Cyan
}

# Check Docker installation
Write-Host ""
Write-Status "Checking Docker installation..." "Info"

$dockerInstalled = $false
try {
    $dockerVersion = docker --version 2>$null
    if ($dockerVersion) {
        Write-Status "Docker is installed: $dockerVersion" "Success"
        $dockerInstalled = $true
    }
} catch {
    Write-Status "Docker is not installed" "Warning"
}

if (-not $dockerInstalled) {
    Write-Host ""
    Write-Host "Docker Desktop is required to run TWLan" -ForegroundColor Yellow
    $installDocker = Read-Host "Would you like to download Docker Desktop? (Y/N)"
    
    if ($installDocker -eq 'Y' -or $installDocker -eq 'y') {
        Write-Status "Opening Docker Desktop download page..." "Info"
        Start-Process "https://www.docker.com/products/docker-desktop/"
        Write-Host ""
        Write-Host "Please install Docker Desktop and restart this script" -ForegroundColor Yellow
    }
}

# Create desktop shortcut
Write-Host ""
$createShortcut = Read-Host "Create desktop shortcut? (Y/N)"

if ($createShortcut -eq 'Y' -or $createShortcut -eq 'y') {
    $WshShell = New-Object -comObject WScript.Shell
    $Shortcut = $WshShell.CreateShortcut("$env:USERPROFILE\Desktop\TWLan 2025.lnk")
    $Shortcut.TargetPath = "$targetDir\scripts\start-windows.bat"
    $Shortcut.WorkingDirectory = $targetDir
    $Shortcut.IconLocation = "shell32.dll,13"
    $Shortcut.Description = "TWLan 2025 Docker Edition"
    $Shortcut.Save()
    Write-Status "Desktop shortcut created" "Success"
}

# Summary
Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "        Setup Complete!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Status "Project location: $targetDir" "Info"
Write-Host ""

if ($dockerInstalled) {
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "1. Extract TWLan files if not done" -ForegroundColor White
    Write-Host "2. Run the launcher:" -ForegroundColor White
    Write-Host "   $targetDir\scripts\start-windows.bat" -ForegroundColor Yellow
} else {
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "1. Install Docker Desktop" -ForegroundColor White
    Write-Host "2. Extract TWLan files to TWLan-2.A3-linux64 folder" -ForegroundColor White
    Write-Host "3. Run the launcher:" -ForegroundColor White
    Write-Host "   $targetDir\scripts\start-windows.bat" -ForegroundColor Yellow
}

Write-Host ""
$openFolder = Read-Host "Open project folder? (Y/N)"
if ($openFolder -eq 'Y' -or $openFolder -eq 'y') {
    Start-Process explorer.exe $targetDir
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
