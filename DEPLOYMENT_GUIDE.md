# 🚀 TWLan AI Bot System - Deployment Guide

## Quick Start (5 Minutes)

### **Prerequisites**
```bash
# Required
- Python 3.9+
- PostgreSQL or MySQL
- TWLan game server running
- Git

# Recommended
- Docker (optional but recommended)
- 4GB RAM minimum
- 4 CPU cores minimum
```

### **Step 1: Environment Setup**
```bash
# Clone repository (if not already done)
cd c:\Users\Admin\TWLan\TWLan-2.A3-linux64

# Install Python dependencies
cd ai-bots
pip install -r requirements.txt

# Install additional dependencies
pip install httpx beautifulsoup4 structlog prometheus-client
```

### **Step 2: Configure Environment**
```bash
# Create .env file
cp .env.example ai-bots/.env

# Edit ai-bots/.env with your settings:
# - DATABASE_URL=postgresql://user:pass@localhost/twlan
# - GAME_BASE_URL=http://localhost:8080
# - BOT_COUNT=10
# - ENABLE_METRICS=true
# - METRICS_PORT=9090
```

### **Step 3: Initialize Database**
```bash
# Run migrations (if needed)
cd migrations
./apply-all.sh  # Linux/Mac
# or
apply-all.bat   # Windows

# Verify database connection
python -c "from ai-bots.core.database import Database; print('DB OK')"
```

### **Step 4: Start TWLan Game Server**
```bash
# Start the game server (if not already running)
cd scripts
./start-windows.bat  # Windows
# or
./start-linux.sh     # Linux

# Verify game is accessible
# Open browser: http://localhost:8080
```

### **Step 5: Launch AI Bot Orchestrator**
```bash
# Start the orchestrator
cd ai-bots
python orchestrator_enhanced.py

# You should see:
# - "orchestrator_initializing"
# - "memory_system_initialized"
# - "orchestrator_initialized" with bot count
# - "cycle_start" repeating every tick
```

---

## 📊 **Monitoring Setup**

### **Prometheus Metrics** (Port 9090)
```bash
# Access metrics
curl http://localhost:9090/metrics

# Key metrics to watch:
# - ai_decisions_made_total
# - guardrail_blocks_total
# - tick_duration_seconds
# - active_bots
```

### **Log Monitoring**
```bash
# Follow logs in real-time
tail -f ai-bots/logs/orchestrator.log

# Filter by bot
grep "bot=AI-Bot1" ai-bots/logs/orchestrator.log

# Filter by action
grep "attack" ai-bots/logs/orchestrator.log
```

### **Database Monitoring**
```sql
-- Check memory learning
SELECT * FROM ai_relations ORDER BY updated_at DESC LIMIT 10;
SELECT * FROM ai_target_stats ORDER BY success_rate DESC LIMIT 10;
SELECT * FROM ai_strategy_stats ORDER BY confidence DESC LIMIT 10;

-- Check bot activity
SELECT username, COUNT(*) as actions 
FROM bot_actions 
GROUP BY username 
ORDER BY actions DESC;
```

---

## 🎯 **Scaling Guide**

### **Start Small (10 Bots)**
```bash
# Edit .env
BOT_COUNT=10
MAX_CONCURRENT_BOTS=5
BOT_TICK_RATE=60

# Monitor resource usage
# - RAM: ~2GB
# - CPU: ~30%
# - Network: Moderate
```

### **Scale to Medium (50 Bots)**
```bash
# Edit .env
BOT_COUNT=50
MAX_CONCURRENT_BOTS=10
BOT_TICK_RATE=120

# Monitor resource usage
# - RAM: ~4GB
# - CPU: ~60%
# - Network: High
```

### **Scale to Large (100+ Bots)**
```bash
# Edit .env
BOT_COUNT=100
MAX_CONCURRENT_BOTS=20
BOT_TICK_RATE=180

# Recommended: Use multiple orchestrators
# - Split bots across multiple instances
# - Use load balancer for game server
# - Monitor: RAM 8GB+, CPU 80%+
```

---

## 🔧 **Configuration Guide**

### **Personality Distribution**
```python
# In ai-bots/core/config.py or .env
PERSONALITY_WARMONGER=20   # 20% aggressive
PERSONALITY_TURTLE=20      # 20% defensive
PERSONALITY_BALANCED=30    # 30% standard
PERSONALITY_DIPLOMAT=15    # 15% supportive
PERSONALITY_CHAOS=15       # 15% unpredictable
```

### **Guardrails Configuration**
```python
# Rate limiting
MAX_ATTACKS_PER_HOUR=50       # Per bot
MAX_ATTACKS_PER_TARGET_DAY=5  # Per target
MIN_ACTION_INTERVAL=5         # Seconds

# Safety limits
MIN_DEFENSIVE_UNITS=1000      # Keep home
DEFENSIVE_RESERVE_RATIO=0.3   # Keep 30% home
MAX_RESOURCE_SEND_RATIO=0.8   # Max 80% send

# Sleep windows (prevent night spam)
SLEEP_START_HOUR=0   # Midnight
SLEEP_END_HOUR=6     # 6 AM
```

### **Advanced Features Toggle**
```python
# Enable/disable features in config
ENABLE_SCOUTING=true
ENABLE_TRADING=true
ENABLE_TIMED_ATTACKS=true
ENABLE_VILLAGE_SPECIALIZATION=true
ENABLE_NIGHT_BONUS=true
ENABLE_DEFENSIVE_RESERVES=true
ENABLE_RESOURCE_BALANCING=true
```

---

## 🐛 **Troubleshooting**

### **Problem: Bots not starting**
```bash
# Check database connection
python -c "from ai-bots.core.database import Database; print('OK')"

# Check game server
curl http://localhost:8080

# Check logs
tail -f ai-bots/logs/orchestrator.log | grep ERROR
```

### **Problem: High CPU usage**
```bash
# Reduce concurrent bots
MAX_CONCURRENT_BOTS=5

# Increase tick rate (slower but less CPU)
BOT_TICK_RATE=120

# Reduce bot count
BOT_COUNT=10
```

### **Problem: Memory leaks**
```bash
# Monitor memory usage
ps aux | grep orchestrator

# Restart orchestrator periodically
# Add to cron or task scheduler
```

### **Problem: Bots not learning**
```sql
-- Check if memory tables exist
SHOW TABLES LIKE 'ai_%';

-- Check if data is being written
SELECT COUNT(*) FROM ai_relations;
SELECT COUNT(*) FROM ai_target_stats;

# If 0, check orchestrator logs for memory errors
```

---

## 🔒 **Production Checklist**

### **Before Deployment**
- [ ] Database backups configured
- [ ] Logging configured (rotate logs)
- [ ] Monitoring dashboard setup
- [ ] Alerting configured (CPU, memory, errors)
- [ ] Rate limits tested
- [ ] Guardrails validated
- [ ] Emergency stop procedure documented

### **After Deployment**
- [ ] Monitor for first 1 hour continuously
- [ ] Check memory learning is working
- [ ] Verify no bot is stuck in loop
- [ ] Confirm rate limits are enforced
- [ ] Watch for any errors in logs
- [ ] Validate resource usage is within limits

---

## 📈 **Performance Tuning**

### **Optimize Database**
```sql
-- Add indexes for performance
CREATE INDEX idx_bot_actions_timestamp ON bot_actions(timestamp);
CREATE INDEX idx_ai_relations_updated ON ai_relations(updated_at);
CREATE INDEX idx_ai_target_stats_score ON ai_target_stats(success_rate);

-- Analyze query performance
EXPLAIN ANALYZE SELECT * FROM ai_relations WHERE bot_id = 123;
```

### **Optimize Orchestrator**
```python
# Batch database queries
# Use connection pooling (already implemented)
# Cache world snapshots (5 minute TTL)
# Limit decision queue size

# In config:
WORLD_SNAPSHOT_CACHE_TTL=300  # 5 minutes
MAX_DECISIONS_PER_TICK=50
DATABASE_POOL_SIZE=20
```

---

## 🎮 **Testing Workflow**

### **Phase 1: Single Bot Test (10 minutes)**
```bash
BOT_COUNT=1
python orchestrator_enhanced.py

# Watch for:
# - Bot initializes correctly
# - Makes decisions
# - Executes actions
# - Updates memory
# - No errors in logs
```

### **Phase 2: Small Group Test (30 minutes)**
```bash
BOT_COUNT=5
python orchestrator_enhanced.py

# Watch for:
# - All bots active
# - No resource conflicts
# - Memory learning working
# - CPU/RAM within limits
```

### **Phase 3: Production Test (2 hours)**
```bash
BOT_COUNT=50
python orchestrator_enhanced.py

# Watch for:
# - Stable operation
# - No memory leaks
# - Learning progressing
# - Game server stable
```

---

## ✅ **Success Metrics**

### **System Health**
- ✅ All bots active and making decisions
- ✅ No errors in logs (or < 1% error rate)
- ✅ CPU usage < 80%
- ✅ Memory stable (no leaks)
- ✅ Response times < 2 seconds

### **AI Performance**
- ✅ Memory tables growing (learning active)
- ✅ Decision variety (not stuck in loops)
- ✅ Guardrails enforcing limits
- ✅ Personality variety in actions
- ✅ Strategic depth visible (advanced features)

### **Game Impact**
- ✅ Bots successfully building/recruiting
- ✅ Attacks being executed
- ✅ Resources being managed
- ✅ Villages growing
- ✅ No game server crashes

---

**🚀 System is now ready for deployment!**

For support or issues, check logs first, then review this guide. The system is designed to be resilient and self-healing, but monitoring is essential for the first 24 hours.
