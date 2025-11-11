# TWLan REST API Documentation

**Version:** 1.0  
**Base URL:** `http://localhost:8080/api/`  
**Format:** JSON

---

## Features

✅ RESTful design  
✅ JSON responses  
✅ Redis caching (5-10 min TTL)  
✅ Rate limiting (100 req/min)  
✅ Standardized responses  
✅ Error handling  
✅ Uses new database tables (Pass 5)

---

## Endpoints

### 🏠 Index
```
GET /api/
```
Returns API information and available endpoints.

---

### 👥 Players

#### List Players
```
GET /api/players/list?page=1&per_page=50
```
Returns paginated list of players with rankings.

**Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Results per page (default: 50, max: 100)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "player_id": 1,
      "username": "Player1",
      "alliance_tag": "ABC",
      "total_points": 10000,
      "village_count": 5,
      "rank": 1,
      "is_online": true
    }
  ],
  "pagination": {
    "total": 500,
    "page": 1,
    "per_page": 50,
    "total_pages": 10
  }
}
```

#### View Player
```
GET /api/players/view/123
```
Returns detailed player information.

---

### 🏘️ Villages

#### Player Villages
```
GET /api/villages/player/123
```
Returns all villages owned by a player.

#### Map Area
```
GET /api/villages/map?x=500&y=500&radius=10
```
Returns villages in a map area.

**Parameters:**
- `x`: Center X coordinate
- `y`: Center Y coordinate
- `radius`: Search radius (max: 50)

---

### 🛡️ Alliances

#### List Alliances
```
GET /api/alliances/list?page=1
```
Returns paginated alliance list.

#### View Alliance
```
GET /api/alliances/view/123
```
Returns detailed alliance information.

---

### 🏆 Leaderboard

#### Player Leaderboard
```
GET /api/leaderboard/players?type=player_points&limit=100
```

**Types:**
- `player_points` - Total points
- `player_attack` - Attack points
- `player_defense` - Defense points
- `player_villages` - Village count

#### Alliance Leaderboard
```
GET /api/leaderboard/alliances?limit=50
```

---

### 📊 Statistics

#### Player Statistics
```
GET /api/stats/player/123
```
Returns 30-day player statistics history.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "stat_date": "2025-11-10",
      "attacks_sent": 50,
      "attacks_won": 45,
      "total_points": 10000,
      "rank_position": 15
    }
  ]
}
```

#### Alliance Statistics
```
GET /api/stats/alliance/123
```
Returns 30-day alliance statistics history.

#### Global Statistics
```
GET /api/stats/global
```
Returns game-wide statistics.

---

### 🎖️ Achievements

#### List All Achievements
```
GET /api/achievements/list
```
Returns all available achievements.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "achievement_key": "first_village",
      "name": "Settler",
      "description": "Founded your first village",
      "category": "special",
      "points": 10
    }
  ]
}
```

#### Player Achievements
```
GET /api/achievements/player/123
```
Returns achievements earned by a player.

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Success",
  "data": { ... },
  "timestamp": 1699999999,
  "execution_time": "15.23ms"
}
```

### Error Response
```json
{
  "success": false,
  "error": "Error message",
  "details": null,
  "timestamp": 1699999999,
  "execution_time": "5.12ms"
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [ ... ],
  "pagination": {
    "total": 500,
    "page": 1,
    "per_page": 50,
    "total_pages": 10
  }
}
```

---

## Rate Limiting

**Limit:** 100 requests per minute per IP  
**Status Code:** 429 (Too Many Requests)

---

## Caching

All endpoints use Redis caching:
- Players/Villages/Alliances: 5 minutes
- Leaderboards: 5 minutes
- Statistics: 10 minutes
- Achievements: 1 hour (rarely change)

Cache status is indicated in response message: `(cached)`

---

## Error Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 400 | Bad Request |
| 404 | Not Found |
| 405 | Method Not Allowed |
| 429 | Rate Limit Exceeded |
| 500 | Internal Server Error |

---

## Examples

### Get Top 10 Players
```bash
curl http://localhost:8080/api/leaderboard/players?limit=10
```

### Get Player Statistics
```bash
curl http://localhost:8080/api/stats/player/123
```

### Get Villages on Map
```bash
curl "http://localhost:8080/api/villages/map?x=500&y=500&radius=20"
```

---

## Benefits

✅ **Fast:** Redis caching = sub-100ms responses  
✅ **Scalable:** Uses optimized cache tables from Pass 5  
✅ **Protected:** Rate limiting prevents abuse  
✅ **Modern:** RESTful, JSON, standard HTTP codes  
✅ **Documented:** Complete API documentation  

---

## Next Steps

1. Add authentication (JWT tokens)
2. Add POST/PUT/DELETE endpoints
3. Add WebSocket for real-time updates
4. Add API versioning (v2, v3)
5. Add GraphQL endpoint (optional)
