# TWLan Dependencies Documentation

**Last Updated:** November 10, 2025 (Pass 4)

---

## Docker Base Images

### Production Images

| Service | Image | Version | Purpose |
|---------|-------|---------|---------|
| Legacy | debian:12-slim | 12 (bookworm) | Original binary compatibility |
| Modern PHP | php:8.4-fpm-bookworm | 8.4 | Modern PHP stack |
| Database | mariadb | 10.11 LTS | Database server |
| Cache | redis:7-alpine | 7 | Session & cache storage |
| Web | nginx:1.27-alpine | 1.27 | Reverse proxy & static files |
| Backup | alpine | 3.19 | Lightweight backup service |
| Prometheus | prom/prometheus | v2.48.0 | Metrics collection |
| Grafana | grafana/grafana | 10.2.2 | Metrics visualization |
| phpMyAdmin | phpmyadmin | latest | Database management UI |

**Note:** All production images use specific version tags for reproducibility.

---

## PHP Extensions (Modern Stack)

### Core Extensions (15)
Installed via `docker-php-ext-install`:

| Extension | Purpose |
|-----------|---------|
| gd | Image processing (JPEG, PNG, GIF) |
| mysqli | MySQL improved extension |
| pdo | Database abstraction layer |
| pdo_mysql | MySQL PDO driver |
| zip | ZIP archive handling |
| intl | Internationalization |
| opcache | PHP opcode cache |
| bcmath | Arbitrary precision math |
| sockets | Socket communication |
| pcntl | Process control |
| mbstring | Multibyte string handling |
| xml | XML parsing |
| curl | HTTP client |

### PECL Extensions (2)
Installed via `pecl install`:

| Extension | Purpose |
|-----------|---------|
| redis | Redis client |
| apcu | User-cache (alternative to APC) |

### Development Only
- **xdebug** - Removed from production build (add in docker-compose override for dev)

---

## System Packages

### Debian/Ubuntu Packages (Modern Stack)

#### Build Dependencies
- libpng-dev
- libjpeg-dev
- libfreetype6-dev
- libzip-dev
- libicu-dev
- libonig-dev
- libxml2-dev
- libcurl4-openssl-dev
- libssl-dev
- zlib1g-dev

#### Runtime Dependencies
- nginx
- supervisor
- curl
- wget
- git
- unzip
- python3
- python3-pip
- mariadb-client
- redis-tools

### Legacy Stack (32-bit Support)
- libc6:i386
- libstdc++6:i386
- libncurses5:i386
- zlib1g:i386
- libaio1t64
- libncurses5
- libreadline8
- ca-certificates
- tzdata
- procps
- netcat-openbsd

---

## Composer Packages

**Note:** No composer.json currently in project.  
For future PHP dependency management, use Composer.

**Recommended packages for modernization:**
```json
{
  "require": {
    "php": ">=8.4",
    "predis/predis": "^2.2",
    "monolog/monolog": "^3.5",
    "vlucas/phpdotenv": "^5.6"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.5",
    "phpstan/phpstan": "^1.10"
  }
}
```

---

## Node.js Packages

**Not currently used.** Consider adding for frontend build tools:
- webpack / vite
- tailwindcss
- postcss

---

## Python Packages

### Current Usage
- Python 3 used in utility scripts
- No pip requirements.txt yet

### Recommended for Enhanced Scripts
```
requests>=2.31.0
pyyaml>=6.0
docker>=7.0.0
```

---

## Security Considerations

### Regular Updates
1. **Monthly:** Check for security updates
2. **Quarterly:** Update base images
3. **Annually:** Major version upgrades

### Update Commands
```bash
# Check for outdated Docker images
docker images --format "{{.Repository}}:{{.Tag}}" | xargs -I {} docker pull {}

# Rebuild with latest patches
docker-compose build --no-cache

# Update PHP extensions
docker exec twlan-php pecl upgrade redis apcu
```

---

## Version Compatibility Matrix

| Component | Minimum | Recommended | Maximum Tested |
|-----------|---------|-------------|----------------|
| PHP | 8.0 | 8.4 | 8.4 |
| MariaDB | 10.6 | 10.11 LTS | 11.2 |
| Redis | 6.0 | 7.2 | 7.2 |
| Nginx | 1.24 | 1.27 | 1.27 |
| Debian | 11 | 12 | 12 |

---

## Dependency Management Best Practices

### DO:
✅ Pin versions in production  
✅ Test updates in staging first  
✅ Document all dependencies  
✅ Use LTS versions when available  
✅ Regularly scan for vulnerabilities  

### DON'T:
❌ Use `:latest` in production  
❌ Update without testing  
❌ Mix package sources  
❌ Ignore security advisories  
❌ Install unnecessary packages  

---

## Vulnerability Scanning

### Tools
- `docker scan` - Built-in Docker scanner
- Trivy - Comprehensive vulnerability scanner
- Snyk - Continuous monitoring

### Scan Commands
```bash
# Scan image for vulnerabilities
docker scan twlan-php:latest

# Or with Trivy
trivy image twlan-php:latest
```

---

## Future Improvements

1. Add composer.json for PHP dependency management
2. Implement automated security scanning in CI/CD
3. Add requirements.txt for Python utilities
4. Consider containerizing legacy binaries separately
5. Add package lock files for reproducibility
