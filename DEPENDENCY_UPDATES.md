# Dependency Update Log

**Purpose:** Track dependency updates and changes

---

## 2025-11-10 - Pass 4 Audit

### Changes Made

#### Image Version Pinning
- **Prometheus:** `latest` → `v2.48.0`
- **Grafana:** `latest` → `10.2.2`
- **Reason:** Ensure reproducible builds

#### PHP Extensions
- **Removed:** xdebug (production)
- **Reason:** Dev-only tool, performance impact

### Current Versions

| Component | Version | Release Date | EOL Date |
|-----------|---------|--------------|----------|
| Debian | 12 (Bookworm) | 2023-06 | 2028-06 |
| PHP | 8.4 | 2024-11 | 2027-11 |
| MariaDB | 10.11 LTS | 2023-02 | 2028-02 |
| Redis | 7.2 | 2023-07 | - |
| Nginx | 1.27 | 2024-05 | - |
| Prometheus | 2.48.0 | 2023-11 | - |
| Grafana | 10.2.2 | 2023-11 | - |

### Security Status
✅ All components within support lifecycle  
✅ No known critical vulnerabilities  
✅ LTS versions used where available

---

## Update Schedule

### Monthly
- Check for security patches
- Review CVE databases

### Quarterly
- Update patch versions (e.g., 10.11.5 → 10.11.6)
- Test in staging

### Annually
- Consider minor version updates
- Plan major version migrations

---

## Update Procedure

### 1. Check for Updates
```bash
# Check Docker images
docker images --format "table {{.Repository}}\t{{.Tag}}\t{{.CreatedAt}}"

# Check for newer versions
docker search mariadb --limit 5
```

### 2. Test in Staging
```bash
# Update docker-compose.yml with new version
# Test in isolated environment
docker-compose -f docker-compose.staging.yml up -d
```

### 3. Validate
- Run health checks
- Test core functionality
- Check logs for errors
- Performance benchmarks

### 4. Deploy to Production
```bash
# Backup first!
./docker/scripts/backup.sh

# Pull new images
docker-compose pull

# Recreate containers
docker-compose up -d --force-recreate
```

### 5. Document
- Update this file
- Update .tool-versions
- Note any breaking changes

---

## Rollback Procedure

If update fails:
```bash
# Stop new containers
docker-compose down

# Restore previous images
docker-compose up -d

# Restore data if needed
./scripts/restore-backup.sh
```

---

## Next Review Date
**2025-02-10** (3 months)
