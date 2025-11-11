#!/bin/bash
# TWLan Automated Backup Script
# Last Updated: November 10, 2025 (Pass 3 - Enhanced error handling)

set -e  # Exit on error
set -u  # Exit on undefined variable
set -o pipefail  # Exit on pipe failure

BACKUP_DIR="${BACKUP_DIR:-/backup}"
RETENTION_DAYS="${RETENTION_DAYS:-7}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Error handling
error_exit() {
    echo "[$(date)] ERROR: $1" >&2
    exit 1
}

# Validate environment
[ -z "${BACKUP_DIR:-}" ] && error_exit "BACKUP_DIR not set"
[ ! -d "$BACKUP_DIR" ] && error_exit "BACKUP_DIR does not exist: $BACKUP_DIR"
[ ! -w "$BACKUP_DIR" ] && error_exit "BACKUP_DIR not writable: $BACKUP_DIR"

echo "[$(date)] Starting backup process..."
echo "[$(date)] Backup directory: $BACKUP_DIR"
echo "[$(date)] Retention: ${RETENTION_DAYS:-7} days"

# Backup MariaDB
if [ -n "$DB_HOST" ]; then
    echo "[$(date)] Backing up database..."
    mysqldump -h "$DB_HOST" -u root -p"$DB_ROOT_PASSWORD" --all-databases | gzip > "$BACKUP_DIR/db_$TIMESTAMP.sql.gz"
    echo "[$(date)] Database backup complete: db_$TIMESTAMP.sql.gz"
fi

# Backup Redis (if configured)
if [ -n "$REDIS_HOST" ]; then
    echo "[$(date)] Backing up Redis..."
    redis-cli -h "$REDIS_HOST" --rdb "$BACKUP_DIR/redis_$TIMESTAMP.rdb"
    gzip "$BACKUP_DIR/redis_$TIMESTAMP.rdb"
    echo "[$(date)] Redis backup complete: redis_$TIMESTAMP.rdb.gz"
fi

# Cleanup old backups
echo "[$(date)] Cleaning up backups older than $RETENTION_DAYS days..."
find "$BACKUP_DIR" -name "*.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -name "*.rdb" -mtime +$RETENTION_DAYS -delete

echo "[$(date)] Backup process complete!"
