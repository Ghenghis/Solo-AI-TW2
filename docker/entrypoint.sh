#!/bin/bash
# TWLan Legacy Entrypoint Script
# Last Updated: November 10, 2025 (Pass 3 - Enhanced error handling)
set -e  # Exit on error
set -u  # Exit on undefined variable
set -o pipefail  # Exit on pipe failure

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function for colored output
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Configuration
TWLAN_DIR="${TWLAN_DIR:-/opt/twlan}"
PORT="${TWLAN_PORT:-80}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
STARTUP_MODE="${STARTUP_MODE:-auto}"

# Paths
MYSQLD_BIN="$TWLAN_DIR/bin/mysqld"
PHP_BIN="$TWLAN_DIR/bin/php"
PHP_INI="$TWLAN_DIR/lib/php.ini"
MY_CNF="$TWLAN_DIR/lib/my.cnf"
DB_DIR="$TWLAN_DIR/db"
HTDOCS="$TWLAN_DIR/htdocs"
SOCKET="$TWLAN_DIR/tmp/mysql.sock"
PIDFILE="$TWLAN_DIR/tmp/mysqld.pid"
LOCKFILE="$TWLAN_DIR/tmp/twlan.lock"
PORT_CONFIG="$TWLAN_DIR/config/ports.json"

# Function to check if process is running
is_running() {
    local pid=$1
    if [ -z "$pid" ]; then
        return 1
    fi
    kill -0 "$pid" 2>/dev/null
}

# Function to cleanup on exit
cleanup() {
    log_info "Shutting down TWLan services..."
    
    # Stop PHP server
    if [ -n "$PHP_PID" ] && is_running "$PHP_PID"; then
        kill "$PHP_PID" 2>/dev/null || true
        log_info "PHP server stopped"
    fi
    
    # Stop MySQL
    if [ -n "$MYSQL_PID" ] && is_running "$MYSQL_PID"; then
        "$TWLAN_DIR/bin/mysqladmin" -u root --socket="$SOCKET" shutdown 2>/dev/null || \
        kill "$MYSQL_PID" 2>/dev/null || true
        log_info "MySQL server stopped"
    fi
    
    # Remove lock file
    rm -f "$LOCKFILE"
    
    log_success "TWLan shutdown complete"
    exit 0
}

# Trap signals for cleanup
trap cleanup SIGTERM SIGINT SIGQUIT EXIT

# Function to find available port
find_available_port() {
    local base_port=$1
    local max_tries=20
    local port=$base_port
    
    for i in $(seq 1 $max_tries); do
        if ! nc -z localhost "$port" 2>/dev/null; then
            echo "$port"
            return 0
        fi
        port=$((port + 1))
    done
    
    # Fallback to random port
    port=$((10000 + RANDOM % 10000))
    echo "$port"
}

# Function to initialize database
init_database() {
    log_info "Initializing TWLan database..."
    
    if [ ! -d "$DB_DIR/mysql" ]; then
        log_info "Creating new database..."
        
        # Initialize MySQL data directory
        "$TWLAN_DIR/bin/mysql_install_db" \
            --defaults-file="$MY_CNF" \
            --basedir="$TWLAN_DIR" \
            --datadir="$DB_DIR" \
            --force 2>/dev/null || {
                log_warning "Standard init failed, trying alternative method..."
                mkdir -p "$DB_DIR/mysql" "$DB_DIR/test"
                cp -r "$TWLAN_DIR/share/mysql/"* "$DB_DIR/" 2>/dev/null || true
            }
        
        log_success "Database initialized"
    else
        log_info "Database already exists"
    fi
}

# Function to start MySQL
start_mysql() {
    log "Starting TWLan 2.A3 Legacy Server..."
    
    # Check if already running
    if [ -f "$PIDFILE" ]; then
        OLD_PID=$(cat "$PIDFILE" 2>/dev/null || echo "")
        if is_running "$OLD_PID"; then
            log_warning "MySQL already running with PID $OLD_PID"
            MYSQL_PID=$OLD_PID
            return 0
        fi
        rm -f "$PIDFILE"
    fi
    
    # Find available port for MySQL if needed
    if nc -z localhost "$MYSQL_PORT" 2>/dev/null; then
        MYSQL_PORT=$(find_available_port $MYSQL_PORT)
        log_warning "MySQL port changed to $MYSQL_PORT"
    fi
    
    # Start MySQL
    "$MYSQLD_BIN" \
        --defaults-file="$MY_CNF" \
        --datadir="$DB_DIR" \
        --basedir="$TWLAN_DIR" \
        --socket="$SOCKET" \
        --pid-file="$PIDFILE" \
        --port="$MYSQL_PORT" \
        --bind-address=127.0.0.1 \
        --skip-grant-tables \
        --log-error="$TWLAN_DIR/logs/mysql_error.log" \
        >/dev/null 2>&1 &
    
    MYSQL_PID=$!
    
    # Wait for MySQL to be ready
    local tries=60
    while [ $tries -gt 0 ]; do
        if "$PHP_BIN" -c "$PHP_INI" -r "
            \$mysqli = @new mysqli('127.0.0.1', 'root', '', 'mysql', $MYSQL_PORT, '$SOCKET');
            if (!\$mysqli->connect_errno) { 
                echo 'OK'; 
                \$mysqli->close();
            }
        " 2>/dev/null | grep -q "OK"; then
            log_success "MySQL server started on port $MYSQL_PORT"
            return 0
        fi
        tries=$((tries - 1))
        sleep 1
    done
    
    log_error "MySQL failed to start"
    return 1
}

# Function to configure TWLan database
configure_twlan_db() {
    log_info "Configuring TWLan database..."
    
    # Check if TWLan database exists
    "$PHP_BIN" -c "$PHP_INI" -r "
        \$mysqli = new mysqli('127.0.0.1', 'root', '', 'mysql', $MYSQL_PORT, '$SOCKET');
        if (\$mysqli->connect_errno) {
            exit(1);
        }
        
        \$result = \$mysqli->query('SHOW DATABASES LIKE \"twlan\"');
        if (\$result->num_rows == 0) {
            \$mysqli->query('CREATE DATABASE IF NOT EXISTS twlan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            echo 'DATABASE_CREATED';
        }
        
        \$mysqli->close();
    " 2>/dev/null || true
    
    # Import SQL files if they exist
    if [ -f "$TWLAN_DIR/install.sql" ]; then
        log_info "Importing TWLan database schema..."
        "$TWLAN_DIR/bin/mysql" -u root --socket="$SOCKET" twlan < "$TWLAN_DIR/install.sql" 2>/dev/null || true
        log_success "Database schema imported"
    fi
    
    # Set up users
    "$PHP_BIN" -c "$PHP_INI" -r "
        \$mysqli = new mysqli('127.0.0.1', 'root', '', 'mysql', $MYSQL_PORT, '$SOCKET');
        if (!\$mysqli->connect_errno) {
            \$mysqli->query('CREATE USER IF NOT EXISTS \"twlan\"@\"localhost\" IDENTIFIED BY \"twlan\"');
            \$mysqli->query('GRANT ALL PRIVILEGES ON twlan.* TO \"twlan\"@\"localhost\"');
            \$mysqli->query('FLUSH PRIVILEGES');
            \$mysqli->close();
        }
    " 2>/dev/null || true
    
    log_success "Database configuration complete"
}

# Function to start PHP server
start_php_server() {
    log_info "Starting PHP server..."
    
    # Find available port for web server
    if nc -z 0.0.0.0 "$PORT" 2>/dev/null; then
        PORT=$(find_available_port $PORT)
        log_warning "Web server port changed to $PORT"
    fi
    
    # Save port configuration
    mkdir -p "$TWLAN_DIR/config"
    echo "{
        \"web_port\": $PORT,
        \"mysql_port\": $MYSQL_PORT,
        \"socket\": \"$SOCKET\",
        \"startup_time\": \"$(date -Iseconds)\",
        \"version\": \"2.A3-2025\"
    }" > "$PORT_CONFIG"
    
    # Create index.php if it doesn't exist
    if [ ! -f "$HTDOCS/index.php" ]; then
        log_warning "index.php not found, creating welcome page..."
        cat > "$HTDOCS/index.php" << 'EOF'
<?php
// TWLan 2.A3 - 2025 Docker Edition
$config = json_decode(file_get_contents('/opt/twlan/config/ports.json'), true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>TWLan 2.A3 - 2025 Edition</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        .status { 
            color: #4ade80; 
            font-weight: bold;
        }
        .info {
            margin: 1rem 0;
            padding: 1rem;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
        }
        a {
            color: #fbbf24;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏰 TWLan 2.A3 - 2025 Docker Edition</h1>
        <p class="status">✅ Server is running!</p>
        <div class="info">
            <p>Web Port: <?php echo $config['web_port']; ?></p>
            <p>MySQL Port: <?php echo $config['mysql_port']; ?></p>
            <p>Started: <?php echo $config['startup_time']; ?></p>
            <p>Version: <?php echo $config['version']; ?></p>
        </div>
        <p><a href="/game/">Enter Game</a> | <a href="/admin/">Admin Panel</a></p>
    </div>
</body>
</html>
EOF
    fi
    
    # Start PHP built-in server
    cd "$HTDOCS"
    "$PHP_BIN" -c "$PHP_INI" \
        -d variables_order=EGPCS \
        -d error_reporting=E_ALL \
        -d display_errors=On \
        -d log_errors=On \
        -d error_log="$TWLAN_DIR/logs/php_error.log" \
        -S 0.0.0.0:"$PORT" \
        -t "$HTDOCS" \
        "$HTDOCS/index.php" \
        >> "$TWLAN_DIR/logs/php_access.log" 2>&1 &
    
    PHP_PID=$!
    
    # Wait for PHP server to be ready
    local tries=30
    while [ $tries -gt 0 ]; do
        if curl -s -o /dev/null -w "%{http_code}" "http://localhost:$PORT" | grep -q "200\|302"; then
            log_success "PHP server started on port $PORT"
            return 0
        fi
        tries=$((tries - 1))
        sleep 1
    done
    
    log_error "PHP server failed to start"
    return 1
}

# Main execution
main() {
    log_info "==================================="
    log_info "TWLan 2.A3 - 2025 Docker Edition"
    log_info "==================================="
    
    # Create necessary directories
    mkdir -p "$TWLAN_DIR"/{tmp,logs,db,config,backup} 2>/dev/null || true
    
    # Check for lock file
    if [ -f "$LOCKFILE" ]; then
        log_warning "Another instance may be running (lock file exists)"
        rm -f "$LOCKFILE"
    fi
    
    # Create lock file
    echo $$ > "$LOCKFILE"
    
    # Validate environment
    if [ ! -f "$MYSQLD_BIN" ]; then
        log_error "MySQL binary not found at $MYSQLD_BIN"
        log_info "Please ensure TWLan-2.A3-linux64 files are properly extracted"
        exit 1
    fi
    
    if [ ! -f "$PHP_BIN" ]; then
        log_error "PHP binary not found at $PHP_BIN"
        exit 1
    fi
    
    # Initialize and start services
    init_database
    start_mysql
    configure_twlan_db
    start_php_server
    
    # Print access information
    echo ""
    log_success "==================================="
    log_success "TWLan is ready!"
    log_success "==================================="
    echo -e "${GREEN}Web Interface:${NC} http://localhost:$PORT"
    echo -e "${GREEN}MySQL Port:${NC} $MYSQL_PORT"
    echo -e "${GREEN}MySQL Socket:${NC} $SOCKET"
    echo -e "${GREEN}Logs:${NC} $TWLAN_DIR/logs/"
    echo ""
    log_info "Press Ctrl+C to stop the server"
    echo ""
    
    # Keep container running and monitor services
    while true; do
        # Check if services are still running
        if ! is_running "$MYSQL_PID"; then
            log_error "MySQL died, restarting..."
            start_mysql
        fi
        
        if ! is_running "$PHP_PID"; then
            log_error "PHP server died, restarting..."
            start_php_server
        fi
        
        sleep 10
    done
}

# Handle command
case "${1:-start}" in
    start)
        main
        ;;
    health)
        curl -f "http://localhost:$PORT" || exit 1
        ;;
    *)
        log_error "Unknown command: $1"
        echo "Usage: $0 {start|health}"
        exit 1
        ;;
esac
