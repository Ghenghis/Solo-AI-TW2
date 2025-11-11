#!/bin/bash
# TWLan Environment Validation Script
# Validates all required environment variables and paths before startup

set -e

log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*"
}

error() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $*" >&2
}

log "Starting environment validation..."

# Check required directories
REQUIRED_DIRS=(
    "/opt/twlan"
    "/opt/twlan/logs"
    "/opt/twlan/tmp"
)

for dir in "${REQUIRED_DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        error "Required directory not found: $dir"
        exit 1
    fi
    log "✓ Directory exists: $dir"
done

# Check required files
REQUIRED_FILES=(
    "/usr/local/bin/entrypoint.sh"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        error "Required file not found: $file"
        exit 1
    fi
    log "✓ File exists: $file"
done

# Check permissions
if [ ! -w "/opt/twlan/logs" ]; then
    error "Logs directory not writable"
    exit 1
fi
log "✓ Log directory is writable"

# Validate environment variables
REQUIRED_VARS=(
    "TWLAN_DIR"
)

for var in "${REQUIRED_VARS[@]}"; do
    if [ -z "${!var:-}" ]; then
        error "Required environment variable not set: $var"
        exit 1
    fi
    log "✓ Environment variable set: $var=${!var}"
done

log "Environment validation complete - all checks passed!"
exit 0
