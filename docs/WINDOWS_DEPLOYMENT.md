# 📁 HOW TO GET FILES TO: C:\Users\Admin\Downloads\twlan-2025-docker

## Option 1: PowerShell Auto-Install (RECOMMENDED) 🚀

1. **Download the project folder** from the outputs
2. **Open PowerShell as Administrator**
3. **Navigate to downloaded folder and run**:
   ```powershell
   Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process
   .\INSTALL.ps1
   ```

This will:
- ✅ Copy all files to `C:\Users\Admin\Downloads\twlan-2025-docker`
- ✅ Create proper directory structure
- ✅ Check for Docker installation
- ✅ Extract TWLan files if found
- ✅ Create desktop shortcut

---

## Option 2: Manual Copy 📂

1. **Download entire `twlan-2a3-2025` folder** from outputs
2. **Copy to**: `C:\Users\Admin\Downloads\`
3. **Rename folder to**: `twlan-2025-docker`
4. **Extract your TWLan zip** to: `C:\Users\Admin\Downloads\twlan-2025-docker\TWLan-2.A3-linux64\`
5. **Run**: `C:\Users\Admin\Downloads\twlan-2025-docker\scripts\start-windows.bat`

---

## Option 3: Use Archive 📦

1. **Download**: `twlan-2025-docker.tar.gz`
2. **Extract using 7-Zip or WinRAR to**: `C:\Users\Admin\Downloads\`
3. **Rename extracted folder** from `twlan-2a3-2025` to `twlan-2025-docker`
4. **Add your TWLan files**
5. **Run the launcher**

---

## File Structure You'll Have:

```
C:\Users\Admin\Downloads\twlan-2025-docker\
├── TWLan-2.A3-linux64\         ← Extract your TWLan zip here
│   ├── bin\
│   ├── htdocs\
│   └── lib\
├── docker\
│   ├── Dockerfile.legacy
│   ├── Dockerfile.modern
│   └── entrypoint.sh
├── scripts\
│   └── start-windows.bat       ← Double-click this to start!
├── utils\
│   └── port_manager.py
├── docs\
│   └── ARCHITECTURE.md
├── docker-compose.yml
├── README.md
├── INSTALL.ps1                 ← Or run this for auto-setup
└── QUICK_START.md
```

---

## Quick Commands After Setup:

### Start TWLan:
```cmd
C:\Users\Admin\Downloads\twlan-2025-docker\scripts\start-windows.bat
```

### Or via PowerShell:
```powershell
cd C:\Users\Admin\Downloads\twlan-2025-docker
docker compose up -d
```

### Access Game:
- Modern: http://localhost:8080
- Legacy: http://localhost:8200
- Admin: http://localhost:8100

---

## ⚡ FASTEST METHOD:

1. Run `INSTALL.ps1` in PowerShell (as Admin)
2. Extract TWLan files when prompted
3. Double-click desktop shortcut
4. Play!

Total time: ~2 minutes including Docker check! 🎮
