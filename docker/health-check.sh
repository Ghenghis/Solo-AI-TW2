#!/bin/bash
# Health check script for TWLan legacy container
# Last Updated: November 10, 2025 (Pass 3 - Enhanced timeout handling)

set -e

# Configuration
HEALTH_ENDPOINT="${HEALTH_ENDPOINT:-http://localhost/health}"
TIMEOUT="${TIMEOUT:-5}"

# Function to check web service
check_web() {
    # Check if web server is responding (with 5 second timeout)
    if curl -f --max-time 5 --silent http://localhost:80/health 2>/dev/null; then
        if command -v curl &> /dev/null; then
            curl -f -s -m "$TIMEOUT" "$HEALTH_ENDPOINT" > /dev/null 2>&1
            return $?
        elif command -v wget &> /dev/null; then
            wget --spider --timeout="$TIMEOUT" -q "$HEALTH_ENDPOINT" > /dev/null 2>&1
            return $?
        else
            echo "ERROR: Neither curl nor wget available"
            return 1
        fi
    else
        echo "ERROR: Neither curl nor wget available"
        return 1
    fi
}

# Main health check
if check_web; then
    echo "OK: Service is healthy"
    exit 0
else
    echo "FAIL: Service health check failed"
    exit 1
fi
