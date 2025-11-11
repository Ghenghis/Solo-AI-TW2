#!/bin/bash
# TWLan Log Cleanup Script
# Removes old log files to prevent disk space issues

set -e
set -u
set -o pipefail

log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*"
}

error() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $*" >&2
}

# Configuration
DAYS_TO_KEEP=${DAYS_TO_KEEP:-7}
LOG_DIRS=(
    "/opt/twlan/logs"
    "/var/log/nginx"
    "/var/log/mysql"
    "/var/log/php"
)

log "Starting log cleanup (keeping last $DAYS_TO_KEEP days)..."

total_freed=0

for dir in "${LOG_DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        log "  Skipping (not found): $dir"
        continue
    fi
    
    log "  Cleaning: $dir"
    
    # Find and delete old log files
    if find "$dir" -name "*.log" -type f -mtime +$DAYS_TO_KEEP -delete 2>/dev/null; then
        log "    ✓ Cleaned log files older than $DAYS_TO_KEEP days"
    fi
    
    # Find and delete old compressed logs
    if find "$dir" -name "*.gz" -type f -mtime +$DAYS_TO_KEEP -delete 2>/dev/null; then
        log "    ✓ Cleaned compressed logs older than $DAYS_TO_KEEP days"
    fi
done

log "Log cleanup complete!"
exit 0
