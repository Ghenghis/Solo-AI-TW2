#!/bin/bash
# TWLan System Status Script
# Displays current system and service status

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

print_header() {
    echo ""
    echo "======================================"
    echo "$1"
    echo "======================================"
}

check_service() {
    local name=$1
    local host=$2
    local port=$3
    
    if nc -z "$host" "$port" 2>/dev/null; then
        echo -e "${GREEN}✓${NC} $name is running ($host:$port)"
        return 0
    else
        echo -e "${RED}✗${NC} $name is not responding ($host:$port)"
        return 1
    fi
}

print_header "TWLan System Status"
echo "Generated: $(date)"

print_header "Service Status"

# Check Docker services
if command -v docker &> /dev/null; then
    echo ""
    echo "Docker Services:"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" 2>/dev/null || echo "  Docker not accessible"
fi

print_header "Network Connectivity"

# Check services
check_service "Web Server" "localhost" "8080" || true
check_service "Database" "localhost" "3307" || true
check_service "Redis" "localhost" "6379" || true
check_service "Prometheus" "localhost" "9090" || true
check_service "Grafana" "localhost" "3000" || true

print_header "Disk Usage"
df -h / | tail -n 1 | awk '{print "Root: " $3 " used / " $2 " total (" $5 " full)"}'

print_header "Memory Usage"
free -h | grep "Mem:" | awk '{print "Memory: " $3 " used / " $2 " total"}'

print_header "CPU Load"
uptime | awk -F'load average:' '{print "Load Average:" $2}'

echo ""
echo "Status check complete!"
