#!/bin/bash
# TWLan Service Dependency Wait Script
# Waits for required services to be ready before starting

set -e

log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*"
}

error() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $*" >&2
}

# Wait for host:port with timeout
wait_for_service() {
    local host=$1
    local port=$2
    local service_name=$3
    local max_attempts=${4:-30}
    local attempt=0

    log "Waiting for $service_name ($host:$port)..."
    
    while [ $attempt -lt $max_attempts ]; do
        if nc -z "$host" "$port" 2>/dev/null; then
            log "✓ $service_name is ready!"
            return 0
        fi
        
        attempt=$((attempt + 1))
        log "  Attempt $attempt/$max_attempts - $service_name not ready yet..."
        sleep 2
    done
    
    error "$service_name failed to start after $max_attempts attempts"
    return 1
}

# Wait for MySQL/MariaDB
if [ -n "${DB_HOST:-}" ] && [ -n "${DB_PORT:-}" ]; then
    wait_for_service "${DB_HOST}" "${DB_PORT:-3306}" "Database" 60
fi

# Wait for Redis
if [ -n "${REDIS_HOST:-}" ] && [ -n "${REDIS_PORT:-}" ]; then
    wait_for_service "${REDIS_HOST}" "${REDIS_PORT:-6379}" "Redis" 30
fi

log "All required services are ready!"
exit 0
