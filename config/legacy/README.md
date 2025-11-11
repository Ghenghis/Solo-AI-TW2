# Legacy Configuration

This directory contains configuration overrides for the TWLan legacy container.

## Usage

Place configuration files here to override defaults in the legacy container:
- `php.ini` - PHP configuration overrides
- `my.cnf` - MySQL configuration overrides
- Custom game configuration files

## Mounting

Files in this directory are mounted read-only to `/opt/twlan/config` in the legacy container.

## Example

```bash
# config/legacy/php.ini
memory_limit = 512M
max_execution_time = 600
```
