# TWLan AI Bot Orchestrator

Enterprise-grade AI system that plays TWLan authentically through HTTP requests.

## Architecture

```
ai-bots/
├── core/              # Core infrastructure
│   ├── config.py      # Configuration management
│   ├── database.py    # Direct DB access (read game state)
│   └── game_client.py # HTTP client (POST/GET like browser)
├── bots/              # Bot management
│   ├── bot_manager.py
│   └── personalities/ # 5 AI personality types
├── strategies/        # Decision engines
│   ├── war_engine.py
│   ├── economy_engine.py
│   └── diplomacy_engine.py
└── orchestrator.py    # Main process
```

## Quick Start

```bash
# 1. Configure
cp .env.example .env
# Edit .env with your settings

# 2. Build & Run
docker-compose up ai-bots

# 3. Monitor
http://localhost:9090/metrics  # Prometheus metrics
```

## Personality Types

1. **Warmonger** (20%) - Aggressive raiding
2. **Turtle** (20%) - Defensive economy
3. **Balanced** (30%) - Standard gameplay
4. **Diplomat** (15%) - Alliance support
5. **Chaos** (15%) - Unpredictable raids

## How It Works

1. **Read State**: Query DB for villages, resources, units
2. **Make Decisions**: Each bot's personality decides actions
3. **Execute**: Send HTTP requests to game (like real player)
4. **Wait**: Human-like delays between actions

## Safety Features

- Request rate limiting
- Action validation
- Human-like randomness
- Graceful failure handling
