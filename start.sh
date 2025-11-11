#!/bin/bash
# TWLan 2.A3 + AI Bots - One-Click Launcher for Linux/macOS
# ===========================================================

set -e

echo ""
echo "========================================="
echo " TWLan 2.A3 - 2025 Edition + AI Bots"
echo "========================================="
echo ""

# Check Docker is running
if ! docker info >/dev/null 2>&1; then
    echo "[ERROR] Docker is not running!"
    echo "Please start Docker and try again."
    exit 1
fi

echo "[INFO] Docker is running..."
echo ""

# Check if TWLan legacy files exist
if [ ! -d "htdocs" ]; then
    echo "[WARNING] TWLan legacy files not found in current directory!"
    echo "Expected: htdocs/, lib/, bin/ folders"
    echo ""
    read -p "Continue anyway? (y/n): " CONTINUE
    if [ "$CONTINUE" != "y" ]; then
        exit 1
    fi
fi

echo ""
echo "Select deployment mode:"
echo ""
echo "  1) Legacy TWLan only (original 2.A3)"
echo "  2) Modern + Database only"
echo "  3) Full stack (Legacy + Modern + AI Bots)"
echo "  4) AI Bots only (requires existing database)"
echo "  5) Stop all services"
echo "  6) View logs"
echo ""
read -p "Your choice (1-6): " CHOICE

case $CHOICE in
    1)
        echo "[INFO] Starting Legacy TWLan..."
        docker compose --profile legacy up -d
        echo ""
        echo "[SUCCESS] Legacy TWLan started!"
        echo "Access at: http://localhost:8200"
        ;;
    2)
        echo "[INFO] Starting Modern stack..."
        docker compose up -d twlan-db twlan-redis
        echo ""
        echo "[SUCCESS] Modern stack started!"
        echo "Database: localhost:3307"
        ;;
    3)
        echo "[INFO] Starting FULL STACK (this may take a minute)..."
        docker compose --profile full up -d
        echo ""
        echo "[SUCCESS] Full stack started!"
        echo ""
        echo "Services:"
        echo "  - Legacy TWLan: http://localhost:8200"
        echo "  - Database: localhost:3307"
        echo "  - AI Bots Metrics: http://localhost:9090"
        ;;
    4)
        echo "[INFO] Starting AI Bots only..."
        
        # Check .env file
        if [ ! -f "ai-bots/.env" ]; then
            echo "[WARNING] ai-bots/.env not found!"
            echo "Creating from .env.example..."
            cp "ai-bots/.env.example" "ai-bots/.env"
            echo ""
            echo "[INFO] Please edit ai-bots/.env with your database credentials."
            echo "Then run this script again."
            exit 1
        fi
        
        docker compose up -d ai-bots
        echo ""
        echo "[SUCCESS] AI Bots started!"
        echo "Metrics: http://localhost:9090"
        ;;
    5)
        echo "[INFO] Stopping all services..."
        docker compose --profile full down
        echo ""
        echo "[SUCCESS] All services stopped."
        ;;
    6)
        echo "[INFO] Showing logs (Ctrl+C to exit)..."
        echo ""
        docker compose --profile full logs -f --tail=100
        ;;
    *)
        echo "[ERROR] Invalid choice!"
        exit 1
        ;;
esac

echo ""
echo "========================================="
echo ""
