# TWLan 2.A3 - 2025 Docker Edition 🏰

[![Version](https://img.shields.io/badge/TWLan-2.A3--2025-blue)](https://github.com/yourusername/twlan-2a3-2025)
[![Docker](https://img.shields.io/badge/Docker-20.10%2B-blue)](https://www.docker.com/)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4)](https://www.php.net/)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.11_LTS-003545)](https://mariadb.org/)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

## 🌟 Overview

TWLan 2.A3 - 2025 Docker Edition is a modernized, containerized version of the classic Tribal Wars LAN server. This enterprise-grade solution provides both legacy compatibility and modern infrastructure, ensuring a seamless gaming experience with 2025 standards.

### ✨ Key Features

- **🎮 Dual Mode Operation**: Run original TWLan 2.A3 or modern PHP 8.4+ stack
- **🤖 Enterprise AI Bots**: Production-ready AI orchestrator with memory, learning, and human-like behavior
- **🚀 One-Click Installation**: Automated Docker setup for Windows/Linux/macOS
- **🔄 Intelligent Port Management**: Dynamic port allocation with conflict resolution
- **📊 Enterprise Monitoring**: Prometheus + Grafana dashboards
- **💾 Automated Backups**: Scheduled backups with retention policies
- **🔐 Security Hardened**: Modern security practices and isolated containers
- **📱 Responsive Admin Panel**: phpMyAdmin for database management
- **🛠️ Development Ready**: Hot-reload, debugging tools, and extensive logging

## 📋 Prerequisites

### Minimum Requirements

- **OS**: Windows 11/10 (with WSL2), Linux (Ubuntu 20.04+), macOS 11+
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 2GB free space
- **CPU**: 2 cores minimum, 4 cores recommended
- **Docker**: 20.10.0 or higher
- **Docker Compose**: v2.0.0 or higher

### Quick Check

```bash
# Check Docker
docker --version

# Check Docker Compose
docker compose version

# Check available resources
docker system info
```

## 🚀 Quick Start

### Windows Users

1. **Download and Extract**
   ```powershell
   # Download the repository
   git clone https://github.com/yourusername/twlan-2a3-2025.git
   cd twlan-2a3-2025
   ```

2. **Extract TWLan Files**
   - Place your `TWLan-2.A3-linux64.zip` contents in `TWLan-2.A3-linux64/` directory

3. **Run the Launcher**
   ```powershell
   # Double-click or run:
   .\scripts\start-windows.bat
   ```

4. **Access the Game**
   - Modern: http://localhost:8080
   - Legacy: http://localhost:8200
   - Admin: http://localhost:8100

### Linux/macOS Users

1. **Clone Repository**
   ```bash
   git clone https://github.com/yourusername/twlan-2a3-2025.git
   cd twlan-2a3-2025
   ```

2. **Extract TWLan Files**
   ```bash
   unzip TWLan-2.A3-linux64.zip -d TWLan-2.A3-linux64/
   ```

3. **Start Services**
   ```bash
   # Make scripts executable
   chmod +x scripts/*.sh
   
   # Start modern stack
   ./scripts/start-linux.sh modern
   
   # Or start legacy stack
   ./scripts/start-linux.sh legacy
   
   # Or start everything
   ./scripts/start-linux.sh full
   ```

## 📂 Project Structure

```
twlan-2a3-2025/
├── 📁 app/                    # Modern PHP application (ported)
│   ├── public/               # Web root
│   ├── src/                  # Source code
│   ├── config/               # Application config
│   └── vendor/               # Dependencies
├── 📁 ai-bots/                # 🤖 Enterprise AI Bot System
│   ├── core/                 # AI core (memory, guardrails, world)
│   ├── bots/                 # Bot logic (planners, personalities)
│   ├── strategies/           # AI strategies
│   └── orchestrator.py       # Main bot runner
├── 📁 TWLan-2.A3-linux64/     # Original TWLan files (untouched)
│   ├── bin/                  # Original binaries
│   ├── htdocs/               # Original web files
│   └── lib/                  # Original libraries
├── 📁 docker/                 # Docker configurations
│   ├── Dockerfile.legacy     # Legacy container
│   ├── Dockerfile.modern     # Modern PHP 8.4 container
│   ├── nginx/                # Nginx configs
│   └── supervisor/           # Process management
├── 📁 config/                 # Service configurations
│   ├── mariadb/              # Database config
│   ├── redis/                # Cache config
│   ├── prometheus/           # Monitoring config
│   └── grafana/              # Dashboards
├── 📁 scripts/                # Management scripts
│   ├── start-windows.bat     # Windows launcher
│   ├── start-linux.sh        # Linux launcher
│   └── backup/               # Backup scripts
├── 📁 docs/                   # Documentation
│   ├── ARCHITECTURE.md       # System architecture
│   ├── API.md                # API documentation
│   ├── DEVELOPMENT.md        # Developer guide
│   └── DEPLOYMENT.md         # Deployment guide
├── 📁 diagrams/               # Architecture diagrams
│   └── architecture.mmd      # Mermaid diagrams
├── 📄 docker-compose.yml      # Service orchestration
├── 📄 .env.example           # Environment template
└── 📄 README.md              # This file
```

## 🔧 Configuration

### Environment Variables

Create a `.env` file from `.env.example`:

```bash
cp .env.example .env
```

Key configurations:

```env
# Ports (auto-detected if in use)
TWLAN_WEB_PORT=8080
TWLAN_LEGACY_PORT=8200
TWLAN_DB_PORT=3307
TWLAN_REDIS_PORT=6379
TWLAN_ADMIN_PORT=8100

# Database
DB_NAME=twlan
DB_USER=twlan
DB_PASSWORD=twlan_secure_2025

# PHP Settings
PHP_MEMORY_LIMIT=256M
PHP_MAX_EXECUTION_TIME=300

# Backup
BACKUP_SCHEDULE="0 3 * * *"
RETENTION_DAYS=7
```

### Service Profiles

The system supports multiple deployment profiles:

- **`default`**: Modern stack only (PHP 8.4 + MariaDB + Redis + Nginx)
- **`legacy`**: Original TWLan 2.A3 only
- **`admin`**: Includes phpMyAdmin
- **`monitoring`**: Adds Prometheus + Grafana
- **`full`**: All services enabled

Start with a specific profile:

```bash
# Modern stack only
docker compose up -d

# Legacy only
docker compose --profile legacy up -d

# Everything
docker compose --profile full up -d
```

## 🎮 Usage Guide

### Starting the Server

#### Windows
Double-click `scripts/start-windows.bat` and select from menu:
1. Start TWLan (Modern)
2. Start TWLan (Legacy)
3. Start TWLan (Full)

#### Linux/macOS
```bash
# Modern stack
./scripts/start-linux.sh

# Specific profile
./scripts/start-linux.sh legacy
```

### Accessing Services

| Service | URL | Default Port |
|---------|-----|--------------|
| Modern Game | http://localhost:8080 | 8080 |
| Legacy Game | http://localhost:8200 | 8200 |
| phpMyAdmin | http://localhost:8100 | 8100 |
| Prometheus | http://localhost:9090 | 9090 |
| Grafana | http://localhost:3000 | 3000 |
| Redis Commander | http://localhost:8081 | 8081 |

### Database Access

```bash
# Connect via CLI
docker compose exec twlan-db mysql -u twlan -p

# Connection details
Host: localhost
Port: 3307
Database: twlan
Username: twlan
Password: twlan_secure_2025
```

### Viewing Logs

```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f twlan-web
docker compose logs -f twlan-php
docker compose logs -f twlan-db

# Last 100 lines
docker compose logs --tail=100
```

### Backup & Restore

#### Creating Backups

```bash
# Windows
.\scripts\start-windows.bat
# Select option 7 (Create Backup)

# Linux/macOS
./scripts/backup.sh create
```

#### Restoring Backups

```bash
# Windows
.\scripts\start-windows.bat
# Select option 8 (Restore Backup)

# Linux/macOS
./scripts/backup.sh restore backup-2025-01-15
```

Backups include:
- Complete database dump
- Uploaded files
- Configuration files
- Game data

### Monitoring

Access Grafana at http://localhost:3000

Default credentials:
- Username: `admin`
- Password: `admin` (change on first login)

Pre-configured dashboards:
- System Overview
- PHP Performance
- MySQL Metrics
- Redis Cache Stats
- Container Resources

## 🛠️ Development

### Hot Reload

The modern stack supports hot reload for development:

```bash
# Start in development mode
ENVIRONMENT=development docker compose up
```

### Adding Features

1. **New PHP Code**: Add to `app/src/`
2. **Database Migrations**: Place in `scripts/sql/`
3. **Configuration**: Update `config/` files
4. **Documentation**: Update `docs/`

### Running Tests

```bash
# PHP Unit tests
docker compose exec twlan-php composer test

# Integration tests
docker compose exec twlan-php composer test:integration

# Full test suite
./scripts/test.sh
```

### Code Quality

```bash
# Linting
docker compose exec twlan-php composer lint

# Static analysis
docker compose exec twlan-php composer analyze

# Code formatting
docker compose exec twlan-php composer format
```

## 🔒 Security

### Built-in Security Features

- **Container Isolation**: Each service runs in isolated containers
- **Network Segmentation**: Internal network for service communication
- **Secret Management**: Sensitive data in environment variables
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: Output escaping and CSP headers
- **CSRF Tokens**: Form protection
- **Rate Limiting**: API and login throttling
- **SSL/TLS Support**: HTTPS configuration included

### Security Checklist

- [ ] Change default passwords in `.env`
- [ ] Enable HTTPS for production
- [ ] Configure firewall rules
- [ ] Set up fail2ban
- [ ] Enable audit logging
- [ ] Regular security updates

## 🚨 Troubleshooting

### Common Issues

#### Port Already in Use
```bash
# The launcher automatically finds free ports
# Or manually specify in .env file
TWLAN_WEB_PORT=8090
```

#### Docker Not Starting
```powershell
# Windows: Ensure WSL2 is enabled
wsl --install
wsl --set-default-version 2

# Restart Docker Desktop
```

#### Permission Errors
```bash
# Linux: Add user to docker group
sudo usermod -aG docker $USER
newgrp docker
```

#### Database Connection Failed
```bash
# Check database container
docker compose ps twlan-db
docker compose logs twlan-db

# Restart database
docker compose restart twlan-db
```

### Debug Mode

Enable debug output:
```bash
# Set in .env
DEBUG=true
LOG_LEVEL=debug

# Restart services
docker compose restart
```

### Health Checks

```bash
# Check all services
docker compose ps

# Detailed health status
docker inspect twlan-web --format='{{.State.Health.Status}}'

# Manual health check
curl http://localhost:8080/health
```

## 📊 Performance Optimization

### Recommended Settings

```env
# PHP Optimization
PHP_MEMORY_LIMIT=512M
PHP_MAX_EXECUTION_TIME=600
PHP_OPCACHE_MEMORY=256
PHP_OPCACHE_JIT=1255

# MySQL Tuning
MYSQL_INNODB_BUFFER_POOL_SIZE=1G
MYSQL_MAX_CONNECTIONS=500
MYSQL_QUERY_CACHE_SIZE=128M

# Redis Cache
REDIS_MAXMEMORY=512mb
REDIS_MAXMEMORY_POLICY=allkeys-lru
```

### Resource Limits

Set in `docker-compose.yml`:
```yaml
services:
  twlan-php:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 1G
        reservations:
          cpus: '1'
          memory: 512M
```

## 🤖 AI Bot System

### Enterprise-Grade AI Orchestrator

Complete AI bot system with memory, learning, and human-like behavior (~4,550 lines).

#### Key Features
- **🧠 AI Memory**: Persistent learning from gameplay (3 DB tables)
- **🎯 Smart Targeting**: Memory-driven scouting and attack planning
- **⚔️ Coordinated Attacks**: Timed multi-village nukes
- **🏰 Village Specialization**: Auto-assigns offense/defense/farm/noble roles
- **🌙 Night Bonus**: Optimizes attacks for 2x loot
- **🛡️ Guardrails**: Human-like behavior (sleep, rate limits, anti-spam)
- **🎭 Personalities**: 5 distinct types (Turtle, Diplomat, Balanced, Warmonger, Chaos)
- **📈 Adaptive Learning**: Learns from successes/failures

#### Quick Start
```bash
cd ai-bots
pip install -r requirements.txt
python orchestrator_enhanced.py
```

#### Documentation
- [AI System Overview](docs/AI_IMPLEMENTATION_COMPLETE.md)
- [Memory System](docs/AI_MEMORY_SYSTEM.md)
- [Advanced Features](docs/AI_ADVANCED_FEATURES.md)
- [Guardrails](docs/GUARDRAILS_SYSTEM.md)

## 🗺️ Roadmap

### Version 2.A3-2025.1 (Current)
- ✅ Docker containerization
- ✅ Dual-mode operation (legacy/modern)
- ✅ Intelligent port management
- ✅ Automated setup scripts
- ✅ Basic monitoring
- ✅ **Enterprise AI Bot System** (Memory + Learning + Guardrails)

### Version 2.A3-2025.2 (Q2 2025)
- ⏳ AI HTTP Game Client (reverse-engineer endpoints)
- ⏳ AI Live Testing & Tuning
- ⏳ Kubernetes support
- ⏳ Cloud deployment (AWS/Azure/GCP)
- ⏳ Advanced analytics dashboard
- ⏳ Multi-language support

### Version 2.A3-2025.3 (Q3 2025)
- ⏳ AI Multi-bot coordination
- ⏳ Real-time multiplayer enhancements
- ⏳ Plugin system
- ⏳ Advanced modding support
- ⏳ GraphQL API

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](docs/CONTRIBUTING.md) for guidelines.

### Development Setup

1. Fork the repository
2. Create feature branch
3. Make changes
4. Run tests
5. Submit pull request

### Code Style

- PHP: PSR-12
- JavaScript: ESLint + Prettier
- Docker: Best practices
- Documentation: Markdown

## 📝 License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file.

## 🙏 Acknowledgments

- Original TWLan developers
- Docker community
- PHP and MariaDB teams
- All contributors

## 📞 Support

### Documentation
- [Full Documentation](docs/)
- [API Reference](docs/API.md)
- [FAQ](docs/FAQ.md)

### Community
- [Discord Server](https://discord.gg/twlan)
- [Forums](https://forum.twlan.com)
- [GitHub Issues](https://github.com/yourusername/twlan-2a3-2025/issues)

### Commercial Support
For enterprise support, contact: enterprise@twlan.com

---

<p align="center">
  Made with ❤️ for the TWLan Community
  <br>
  © 2025 TWLan 2.A3 Docker Edition
</p>
