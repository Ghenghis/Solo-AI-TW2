# TWLan Scripts Documentation

**Last Updated:** November 10, 2025 (Pass 3)

---

## Script Inventory

### 🚀 Startup & Initialization

#### `docker/entrypoint.sh`
**Purpose:** Legacy container entrypoint  
**Usage:** Automatically run by Docker  
**Features:**
- MySQL initialization
- PHP server startup
- Port management
- Error handling

#### `docker/entrypoint-modern.sh`
**Purpose:** Modern stack entrypoint  
**Usage:** Automatically run by Docker  
**Features:**
- PHP-FPM + Nginx coordination
- Database connectivity validation
- Supervisor process management

#### `scripts/validate-environment.sh`
**Purpose:** Pre-startup environment validation  
**Usage:** `./scripts/validate-environment.sh`  
**Validates:**
- Required directories exist
- Required files present
- Directory permissions
- Environment variables set

#### `scripts/wait-for-services.sh`
**Purpose:** Wait for service dependencies  
**Usage:** `./scripts/wait-for-services.sh`  
**Environment Variables:**
- `DB_HOST` - Database hostname
- `DB_PORT` - Database port (default: 3306)
- `REDIS_HOST` - Redis hostname
- `REDIS_PORT` - Redis port (default: 6379)

---

### 🏥 Health & Monitoring

#### `docker/health-check.sh`
**Purpose:** Container health validation  
**Usage:** Automatically run by Docker HEALTHCHECK  
**Features:**
- HTTP endpoint checking
- 5-second timeout
- Fallback to wget if curl unavailable

#### `scripts/system-status.sh`
**Purpose:** System health dashboard  
**Usage:** `./scripts/system-status.sh`  
**Displays:**
- Docker container status
- Service connectivity
- Disk usage
- Memory usage
- CPU load

---

### 💾 Backup & Maintenance

#### `docker/scripts/backup.sh`
**Purpose:** Automated backup system  
**Usage:** Automatically via cron or manual  
**Environment Variables:**
- `BACKUP_DIR` - Backup destination (default: /backup)
- `RETENTION_DAYS` - Keep backups for N days (default: 7)
- `DB_HOST` - Database host
- `DB_ROOT_PASSWORD` - Database password
- `REDIS_HOST` - Redis host (optional)

**Features:**
- MariaDB full dump with compression
- Redis RDB backup
- Automatic cleanup of old backups
- Error handling and validation

#### `scripts/cleanup-logs.sh`
**Purpose:** Automated log cleanup  
**Usage:** `./scripts/cleanup-logs.sh`  
**Environment Variables:**
- `DAYS_TO_KEEP` - Log retention days (default: 7)

**Cleans:**
- `/opt/twlan/logs`
- `/var/log/nginx`
- `/var/log/mysql`
- `/var/log/php`

---

### 🛠️ Utilities

#### `scripts/extract-diagrams.ps1`
**Purpose:** Extract embedded Mermaid diagrams  
**Usage:** `.\scripts\extract-diagrams.ps1 [-DryRun]`  
**Platform:** PowerShell (Windows)  
**Features:**
- Extracts 44 diagrams from markdown
- Updates markdown with references
- Dry-run mode for preview

---

## Best Practices

### Error Handling
All scripts use:
```bash
set -e  # Exit on error
set -u  # Exit on undefined variable
set -o pipefail  # Exit on pipe failure
```

### Logging
Standardized logging function:
```bash
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*"
}
```

### Environment Validation
Always validate required variables:
```bash
[ -z "${VAR:-}" ] && error_exit "VAR not set"
```

---

## Common Tasks

### Manual Backup
```bash
docker exec twlan-backup /usr/local/bin/backup.sh
```

### Check System Status
```bash
docker exec twlan-legacy /opt/twlan/scripts/system-status.sh
```

### Clean Old Logs
```bash
docker exec twlan-legacy /opt/twlan/scripts/cleanup-logs.sh
```

### Validate Environment
```bash
docker exec twlan-legacy /opt/twlan/scripts/validate-environment.sh
```

---

## Cron Schedule

### Backup Service
```
0 3 * * *  # Daily at 3:00 AM
```

### Log Cleanup (Recommended)
```
0 4 * * 0  # Weekly on Sunday at 4:00 AM
```

---

## Troubleshooting

### Script Won't Execute
**Symptom:** Permission denied  
**Solution:** Check executable bit in Dockerfile

### Service Won't Start
**Symptom:** Timeout waiting for services  
**Solution:** Check service logs, verify network connectivity

### Backup Fails
**Symptom:** BACKUP_DIR errors  
**Solution:** Verify directory exists, check permissions, validate environment variables

---

## Adding New Scripts

1. Create script with proper shebang: `#!/bin/bash`
2. Add error handling: `set -e`, `set -u`, `set -o pipefail`
3. Add logging function
4. Validate inputs
5. Add to Dockerfile if needed
6. Document in this file
7. Add to scripts/ or docker/scripts/ directory
