# Backup Scripts

This directory contains backup scripts used by the TWLan backup service.

## Scripts

### backup.sh
Main backup script that handles:
- Database dumps
- File system backups
- Rotation based on retention policy
- Compression and encryption

## Usage

Backups are automatically executed by the Docker backup service based on the `BACKUP_SCHEDULE` environment variable (default: daily at 3 AM).

## Manual Backup

```bash
docker-compose exec twlan-backup /scripts/backup.sh
```

## Restore

```bash
# Database restore
docker-compose exec -T twlan-db mysql -u root -p < backup/database_YYYYMMDD.sql

# File restore
tar xzf backup/files_YYYYMMDD.tar.gz -C /target/path
```

## Configuration

- `BACKUP_SCHEDULE`: Cron schedule for automated backups (default: "0 3 * * *")
- `RETENTION_DAYS`: Number of days to keep backups (default: 7)
