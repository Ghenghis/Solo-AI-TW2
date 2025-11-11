# TWLan 2.A3 Docker - QUICK START GUIDE 🚀

## You Have 3 Simple Steps to Play:

### 1️⃣ Extract TWLan Files
```bash
# Put your TWLan-2.A3-linux64.zip contents in:
twlan-2a3-2025/TWLan-2.A3-linux64/
```

### 2️⃣ Run the Launcher

**Windows:**
```cmd
double-click: scripts\start-windows.bat
```

**Linux/Mac:**
```bash
chmod +x scripts/*.sh
./scripts/start-linux.sh
```

### 3️⃣ Play!
- Game: http://localhost:8080 ✅
- Admin: http://localhost:8100 ✅

---

## 🎯 What You Get:

### Automatic Everything
- ✅ Docker installation if needed
- ✅ Port selection (no conflicts)
- ✅ Container building
- ✅ Service startup
- ✅ Browser opening

### Zero Configuration Required
The launcher handles:
- Finding free ports
- Setting up database
- Configuring services
- Creating backups
- Health monitoring

### Menu Options (Windows Launcher)
```
1. Start Modern TWLan (PHP 8.4)
2. Start Legacy TWLan (Original)
3. Start Everything
4. Stop All
5. Show Status
6. View Logs
7. Backup
8. Restore
9. Open Game
0. Exit
```

---

## 🔥 Features Included:

| Feature | Status | Access |
|---------|--------|--------|
| Modern Game Server | ✅ Ready | http://localhost:8080 |
| Legacy Compatibility | ✅ Ready | http://localhost:8200 |
| Database Admin | ✅ Ready | http://localhost:8100 |
| Monitoring Dashboard | ✅ Ready | http://localhost:3000 |
| Auto Backup | ✅ Ready | Runs at 3 AM |
| Smart Ports | ✅ Active | Auto-detects free ports |

---

## 💻 Commands Cheat Sheet:

```bash
# Start everything
docker compose --profile full up -d

# Stop everything
docker compose down

# View logs
docker compose logs -f

# Just modern stack
docker compose up -d

# Just legacy
docker compose --profile legacy up -d

# Database access
docker compose exec twlan-db mysql -u twlan -p
# Password: twlan_secure_2025
```

---

## 🆘 Troubleshooting:

### Docker not found?
- Launcher will install it for you!

### Port in use?
- Launcher finds new ports automatically!

### Need to restart?
```bash
docker compose restart
```

### Full reset?
```bash
docker compose down -v
docker compose up -d --build
```

---

## 📦 What's Inside:

```
✅ Dockerfile.legacy    - Original TWLan preserved
✅ Dockerfile.modern    - PHP 8.4 + MariaDB 10.11
✅ Port Manager        - Intelligent port allocation
✅ Windows Launcher    - GUI with all features
✅ Docker Compose      - Complete orchestration
✅ Documentation       - Enterprise blueprints
```

---

## 🎮 JUST WANT TO PLAY?

### Fastest Path:
1. Extract TWLan files to `TWLan-2.A3-linux64/`
2. Double-click `scripts\start-windows.bat`
3. Press `1` for Modern or `2` for Legacy
4. Browser opens automatically!

**That's it! You're playing in 60 seconds!**

---

*Built with intelligence. Runs with simplicity. Scales with confidence.*
