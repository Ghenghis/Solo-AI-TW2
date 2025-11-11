# SQL Initialization Scripts

This directory contains SQL scripts that are automatically executed when the MariaDB container is first initialized.

## Usage

Place `.sql` files in this directory. They will be executed in alphabetical order during database initialization.

## Example Files

- `01_schema.sql` - Database schema creation
- `02_seed.sql` - Initial data seeding
- `03_indexes.sql` - Index creation for performance

## Note

Files are only executed on **first initialization**. To re-run, you must remove the Docker volume:
```bash
docker-compose down -v
docker-compose up -d
```
