#!/bin/bash
# TWLan Modern Stack Entrypoint Script
# Last Updated: November 10, 2025 (Pass 3 - Enhanced error handling)
set -e  # Exit on error
set -u  # Exit on undefined variable
set -o pipefail  # Exit on pipe failure

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

log_info "=================================="
log_info "TWLan 2.A3 - Modern Stack Starting"
log_info "=================================="

# Environment variables
DB_HOST="${DB_HOST:-twlan-db}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:-twlan}"
REDIS_HOST="${REDIS_HOST:-twlan-redis}"
REDIS_PORT="${REDIS_PORT:-6379}"

# Wait for database
log_info "Waiting for database..."
max_tries=30
counter=0
while ! nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null; do
    counter=$((counter + 1))
    if [ $counter -gt $max_tries ]; then
        log_error "Database connection timeout"
        exit 1
    fi
    log_info "Attempt $counter/$max_tries: Waiting for database..."
    sleep 2
done
log_success "Database is ready"

# Wait for Redis
log_info "Waiting for Redis..."
counter=0
while ! nc -z "$REDIS_HOST" "$REDIS_PORT" 2>/dev/null; do
    counter=$((counter + 1))
    if [ $counter -gt $max_tries ]; then
        log_error "Redis connection timeout"
        exit 1
    fi
    log_info "Attempt $counter/$max_tries: Waiting for Redis..."
    sleep 2
done
log_success "Redis is ready"

# Set permissions
log_info "Setting permissions..."
chown -R www-data:www-data /opt/twlan/{app,logs,cache,sessions,uploads}
chmod -R 755 /opt/twlan/app
chmod -R 777 /opt/twlan/{logs,cache,sessions,uploads}

# Run migrations if needed
if [ -f "/opt/twlan/app/migrate.php" ]; then
    log_info "Running database migrations..."
    php /opt/twlan/app/migrate.php || log_warning "Migrations failed or not needed"
fi

# Clear caches
log_info "Clearing caches..."
rm -rf /opt/twlan/cache/* 2>/dev/null || true
php -r "opcache_reset();" 2>/dev/null || true

log_success "=================================="
log_success "TWLan Modern Stack is ready!"
log_success "=================================="

# Execute the main command
exec "$@"
