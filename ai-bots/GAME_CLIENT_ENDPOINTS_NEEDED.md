# GameClient HTTP Endpoints - What We Need to Implement

## Current Status
❌ **NOT IMPLEMENTED** - The `core/game_client.py` is a **STUB**.

Real TWLan 2.A3 endpoint mapping is **TODO**.

---

## Required Endpoints

### 1. Authentication / Session Management
```python
# Login
POST /game.php?village=XXXX&screen=login
Params: {username, password}
Returns: session_id (cookie or token?)

# Check session
GET /game.php?village=XXXX&screen=overview
Returns: HTML or JSON with village data
```

### 2. Village Overview
```python
# Get current village state
GET /game.php?village={village_id}&screen=overview
Returns:
  - resources (wood, clay, iron)
  - population
  - buildings
  - units
```

### 3. Building
```python
# Queue building upgrade
POST /game.php?village={village_id}&screen=main
Params: {building_type, action="upgrade"}
Returns: success/failure

# Get build queue
GET /game.php?village={village_id}&screen=main
Returns: current build queue
```

### 4. Recruitment
```python
# Train units
POST /game.php?village={village_id}&screen=barracks
Params: {unit_type, quantity}
Returns: success/failure

# Get recruitment queue
GET /game.php?village={village_id}&screen=barracks
Returns: current recruitment queue
```

### 5. Market (Trading)
```python
# Send resources
POST /game.php?village={village_id}&screen=market
Params: {target_village_id, wood, clay, iron}
Returns: success/failure

# Get market state
GET /game.php?village={village_id}&screen=market
Returns: available merchants
```

### 6. Rally Point (Attacks/Support)
```python
# Send attack
POST /game.php?village={village_id}&screen=place
Params: {
    target: {x, y} or village_id,
    units: {spear, sword, axe, ...},
    attack_type: "attack" | "support"
}
Returns: success/failure

# Get incoming/outgoing commands
GET /game.php?village={village_id}&screen=place
Returns: command list
```

### 7. Scouting
```python
# Send scouts
POST /game.php?village={village_id}&screen=place
Params: {target, units: {spy: X}}
Returns: success/failure

# Get scout reports
GET /game.php?village={village_id}&screen=report
Returns: list of reports
```

### 8. Map / Intel
```python
# Get village info
GET /game.php?village={village_id}&screen=info_village&id={target_village_id}
Returns: village details (player, points, etc.)

# Get player info
GET /game.php?village={village_id}&screen=info_player&id={player_id}
Returns: player details (villages, tribe, points)
```

---

## Reverse Engineering Steps

### Option 1: Manual Testing
1. Start legacy TWLan (docker compose --profile legacy up -d)
2. Play the game manually in browser
3. Open browser DevTools → Network tab
4. Perform actions (build, attack, trade)
5. Capture HTTP requests
6. Document params/responses

### Option 2: Code Reading
1. Read `htdocs/game.php` (main router)
2. Read `htdocs/include/actions/*.php` (action handlers)
3. Map screen→endpoint→params→response
4. Implement in `game_client.py`

### Option 3: Use Existing TWLan Bots (if any)
- Search GitHub for TWLan bots/scripts
- Reverse engineer their HTTP calls
- Port to our GameClient

---

## Implementation Priority

### Phase 1: READ-ONLY (get game state)
1. ✅ Session management (login)
2. ✅ Get village overview (resources, units)
3. ✅ Get incoming/outgoing commands
4. ✅ Get reports

**Why first:** Safest. Can't break anything. Allows AI to observe.

### Phase 2: SAFE WRITES (building/recruiting)
1. ✅ Queue buildings
2. ✅ Train units
3. ✅ Send resources (trading)

**Why second:** Low-risk actions. Worst case: waste resources.

### Phase 3: RISKY WRITES (attacks)
1. ✅ Send attacks
2. ✅ Send support
3. ✅ Send scouts

**Why last:** High-risk. Can grief players. Needs guardrails working perfectly.

---

## Testing Strategy

### Unit Tests
```python
# Mock HTTP responses
def test_get_village_overview():
    client = GameClient(base_url="http://mock")
    village = client.get_village_overview(village_id=123)
    assert village['wood'] >= 0
```

### Integration Tests (Against Real TWLan)
```python
# Requires running TWLan instance
def test_real_attack():
    client = GameClient(base_url="http://localhost:8200")
    client.login("testbot", "password")
    result = client.send_attack(from_village=1, to_village=2, units={...})
    assert result['success'] == True
```

### Smoke Tests
- Start AI orchestrator
- Let 1 bot run for 5 minutes
- Check logs for HTTP errors
- Verify no crashes

---

## Current GameClient Code

### Stub Implementation
```python
# ai-bots/core/game_client.py (CURRENT)
class GameClient:
    def attack(...):
        # TODO: Implement real HTTP call to TWLan
        logger.warning("attack() not implemented - STUB")
        return {"success": False, "error": "Not implemented"}
```

### What We Need
```python
# ai-bots/core/game_client.py (FUTURE)
class GameClient:
    def __init__(self, base_url, username, password):
        self.base_url = base_url
        self.session = requests.Session()
        self._login(username, password)
    
    def _login(self, username, password):
        resp = self.session.post(
            f"{self.base_url}/game.php?screen=login",
            data={"username": username, "password": password}
        )
        # Parse session cookie/token
        # Raise if login failed
    
    def get_village_overview(self, village_id):
        resp = self.session.get(
            f"{self.base_url}/game.php?village={village_id}&screen=overview"
        )
        # Parse HTML or JSON
        return {...parsed data...}
    
    def send_attack(self, from_village, target_coords, units):
        resp = self.session.post(
            f"{self.base_url}/game.php?village={from_village}&screen=place",
            data={
                "x": target_coords[0],
                "y": target_coords[1],
                "spear": units.get("spear", 0),
                # ...etc
            }
        )
        # Parse response
        return {"success": True/False}
```

---

## Next Steps (In Order)

1. **Spin up legacy TWLan** (docker compose --profile legacy up -d)
2. **Manual testing** (capture HTTP calls in browser)
3. **Document endpoints** (update this file with real params/responses)
4. **Implement GameClient** (core/game_client.py)
5. **Unit tests** (mocked responses)
6. **Integration tests** (against real TWLan)
7. **Wire into orchestrator** (replace stubs)
8. **Live testing** (1 bot, 5 minutes, monitor)
9. **Scale up** (50 bots, full guardrails)
10. **Victory** 🏆

---

## Help Needed
- [ ] Reverse engineer TWLan 2.A3 endpoints
- [ ] Map HTML parsing (if no JSON API)
- [ ] Handle CSRF tokens (if any)
- [ ] Session management (cookies? tokens?)
- [ ] Error handling (what does TWLan return on failure?)

**Status:** 🔴 **BLOCKED** - Need real endpoint mapping before AI can play!
