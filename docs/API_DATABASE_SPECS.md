# TWLan 2025 - API & Database Documentation
## Complete Technical Specifications

### Table of Contents
1. [RESTful API Specification](#restful-api-specification)
2. [GraphQL Schema](#graphql-schema)
3. [WebSocket Protocol](#websocket-protocol)
4. [Database Schema](#database-schema)
5. [Migration Guide](#migration-guide)
6. [Data Models](#data-models)
7. [API Security](#api-security)
8. [Rate Limiting](#rate-limiting)

---

## RESTful API Specification

### API Overview

```yaml
openapi: 3.0.3
info:
  title: TWLan 2025 API
  description: Complete API specification for TWLan game server
  version: 2.0.0
  contact:
    name: API Support
    email: api@twlan.com
  license:
    name: MIT
    url: https://opensource.org/licenses/MIT

servers:
  - url: https://api.twlan.com/v2
    description: Production server
  - url: https://staging-api.twlan.com/v2
    description: Staging server
  - url: http://localhost:8080/api/v2
    description: Development server

security:
  - bearerAuth: []
  - apiKeyAuth: []
```

### Authentication Endpoints

```yaml
paths:
  /auth/register:
    post:
      summary: Register new user
      tags:
        - Authentication
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - username
                - email
                - password
              properties:
                username:
                  type: string
                  minLength: 3
                  maxLength: 20
                  pattern: '^[a-zA-Z0-9_]+$'
                email:
                  type: string
                  format: email
                password:
                  type: string
                  minLength: 8
                  pattern: '^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$'
                referral_code:
                  type: string
      responses:
        201:
          description: User created successfully
          content:
            application/json:
              schema:
                type: object
                properties:
                  user_id:
                    type: integer
                  username:
                    type: string
                  email:
                    type: string
                  created_at:
                    type: string
                    format: date-time
        400:
          $ref: '#/components/responses/BadRequest'
        409:
          $ref: '#/components/responses/Conflict'

  /auth/login:
    post:
      summary: User login
      tags:
        - Authentication
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - username
                - password
              properties:
                username:
                  type: string
                password:
                  type: string
                remember_me:
                  type: boolean
                  default: false
      responses:
        200:
          description: Login successful
          content:
            application/json:
              schema:
                type: object
                properties:
                  access_token:
                    type: string
                  refresh_token:
                    type: string
                  token_type:
                    type: string
                    default: Bearer
                  expires_in:
                    type: integer
                  user:
                    $ref: '#/components/schemas/User'

  /auth/refresh:
    post:
      summary: Refresh access token
      tags:
        - Authentication
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - refresh_token
              properties:
                refresh_token:
                  type: string
      responses:
        200:
          description: Token refreshed
          content:
            application/json:
              schema:
                type: object
                properties:
                  access_token:
                    type: string
                  expires_in:
                    type: integer
```

### Village Management Endpoints

```yaml
paths:
  /villages:
    get:
      summary: Get user's villages
      tags:
        - Villages
      parameters:
        - in: query
          name: page
          schema:
            type: integer
            default: 1
        - in: query
          name: limit
          schema:
            type: integer
            default: 10
            maximum: 100
        - in: query
          name: sort
          schema:
            type: string
            enum: [name, points, created_at]
            default: points
        - in: query
          name: order
          schema:
            type: string
            enum: [asc, desc]
            default: desc
      responses:
        200:
          description: List of villages
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Village'
                  meta:
                    $ref: '#/components/schemas/PaginationMeta'

  /villages/{villageId}:
    get:
      summary: Get village details
      tags:
        - Villages
      parameters:
        - in: path
          name: villageId
          required: true
          schema:
            type: integer
      responses:
        200:
          description: Village details
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/VillageDetail'

  /villages/{villageId}/buildings:
    get:
      summary: Get village buildings
      tags:
        - Villages
        - Buildings
      parameters:
        - in: path
          name: villageId
          required: true
          schema:
            type: integer
      responses:
        200:
          description: List of buildings
          content:
            application/json:
              schema:
                type: array
                items:
                  $ref: '#/components/schemas/Building'

  /villages/{villageId}/build:
    post:
      summary: Upgrade building
      tags:
        - Villages
        - Buildings
      parameters:
        - in: path
          name: villageId
          required: true
          schema:
            type: integer
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - building_type
              properties:
                building_type:
                  type: string
                  enum: [main, barracks, stable, garage, church, academy, smithy, place, statue, market, timber_camp, clay_pit, iron_mine, farm, storage, wall]
      responses:
        200:
          description: Build order created
          content:
            application/json:
              schema:
                type: object
                properties:
                  order_id:
                    type: integer
                  building_type:
                    type: string
                  current_level:
                    type: integer
                  target_level:
                    type: integer
                  completion_time:
                    type: string
                    format: date-time
                  cost:
                    type: object
                    properties:
                      wood:
                        type: integer
                      clay:
                        type: integer
                      iron:
                        type: integer
```

### Combat System Endpoints

```yaml
paths:
  /combat/attack:
    post:
      summary: Send attack
      tags:
        - Combat
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - from_village
                - target
                - units
              properties:
                from_village:
                  type: integer
                target:
                  type: object
                  properties:
                    x:
                      type: integer
                    y:
                      type: integer
                units:
                  type: object
                  properties:
                    spear:
                      type: integer
                      minimum: 0
                    sword:
                      type: integer
                      minimum: 0
                    axe:
                      type: integer
                      minimum: 0
                    archer:
                      type: integer
                      minimum: 0
                    scout:
                      type: integer
                      minimum: 0
                    light:
                      type: integer
                      minimum: 0
                    heavy:
                      type: integer
                      minimum: 0
                    ram:
                      type: integer
                      minimum: 0
                    catapult:
                      type: integer
                      minimum: 0
                    noble:
                      type: integer
                      minimum: 0
      responses:
        200:
          description: Attack sent
          content:
            application/json:
              schema:
                type: object
                properties:
                  movement_id:
                    type: integer
                  arrival_time:
                    type: string
                    format: date-time
                  return_time:
                    type: string
                    format: date-time

  /combat/simulate:
    post:
      summary: Simulate battle
      tags:
        - Combat
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - attacker
                - defender
              properties:
                attacker:
                  $ref: '#/components/schemas/ArmyComposition'
                defender:
                  $ref: '#/components/schemas/ArmyComposition'
                wall_level:
                  type: integer
                  default: 0
                morale:
                  type: number
                  default: 100
                luck:
                  type: number
                  default: 0
      responses:
        200:
          description: Simulation results
          content:
            application/json:
              schema:
                type: object
                properties:
                  winner:
                    type: string
                    enum: [attacker, defender]
                  attacker_losses:
                    $ref: '#/components/schemas/ArmyComposition'
                  defender_losses:
                    $ref: '#/components/schemas/ArmyComposition'
                  attacker_survivors:
                    $ref: '#/components/schemas/ArmyComposition'
                  defender_survivors:
                    $ref: '#/components/schemas/ArmyComposition'
```

## GraphQL Schema

### Type Definitions

```graphql
type Query {
  # User queries
  me: User
  user(id: ID!): User
  users(filter: UserFilter, page: Int, limit: Int): UserConnection!
  
  # Village queries
  village(id: ID!): Village
  villages(filter: VillageFilter, page: Int, limit: Int): VillageConnection!
  
  # Map queries
  mapSector(x: Int!, y: Int!, radius: Int!): [MapTile!]!
  
  # Reports
  reports(filter: ReportFilter, page: Int, limit: Int): ReportConnection!
  report(id: ID!): Report
  
  # Rankings
  rankings(type: RankingType!, page: Int, limit: Int): RankingConnection!
}

type Mutation {
  # Authentication
  register(input: RegisterInput!): AuthPayload!
  login(input: LoginInput!): AuthPayload!
  logout: Boolean!
  refreshToken(token: String!): AuthPayload!
  
  # Village management
  buildBuilding(villageId: ID!, building: BuildingType!): BuildOrder!
  cancelBuild(orderId: ID!): Boolean!
  trainUnits(villageId: ID!, units: UnitTrainInput!): TrainOrder!
  
  # Combat
  sendAttack(input: AttackInput!): Movement!
  sendSupport(input: SupportInput!): Movement!
  recallTroops(movementId: ID!): Boolean!
  
  # Communication
  sendMessage(input: MessageInput!): Message!
  markMessageRead(messageId: ID!): Boolean!
  deleteMessage(messageId: ID!): Boolean!
}

type Subscription {
  # Real-time updates
  villageUpdated(villageId: ID!): Village!
  incomingAttack: Movement!
  reportReceived: Report!
  messageReceived: Message!
  buildingCompleted: BuildingCompletedEvent!
  unitTrainingCompleted: UnitTrainingCompletedEvent!
}

# Core Types
type User {
  id: ID!
  username: String!
  email: String!
  points: Int!
  rank: Int!
  tribe: Tribe
  villages: [Village!]!
  createdAt: DateTime!
  lastActivity: DateTime!
  premium: PremiumStatus
  achievements: [Achievement!]!
}

type Village {
  id: ID!
  name: String!
  coordinates: Coordinates!
  points: Int!
  owner: User!
  resources: Resources!
  buildings: [Building!]!
  units: UnitCounts!
  movements: [Movement!]!
  production: ResourceProduction!
  storage: StorageCapacity!
  population: Population!
  loyalty: Int!
  wall: WallInfo
}

type Building {
  id: ID!
  type: BuildingType!
  level: Int!
  maxLevel: Int!
  constructionTime: Int!
  requirements: BuildingRequirements!
  effects: BuildingEffects!
  upgradeCost: Resources!
  upgradeTime: Int!
}

type Resources {
  wood: Int!
  clay: Int!
  iron: Int!
}

type Movement {
  id: ID!
  type: MovementType!
  from: Village!
  to: Village!
  units: UnitCounts!
  resources: Resources
  departureTime: DateTime!
  arrivalTime: DateTime!
  returnTime: DateTime
  status: MovementStatus!
}

# Enums
enum BuildingType {
  MAIN
  BARRACKS
  STABLE
  GARAGE
  CHURCH
  ACADEMY
  SMITHY
  PLACE
  STATUE
  MARKET
  TIMBER_CAMP
  CLAY_PIT
  IRON_MINE
  FARM
  STORAGE
  WALL
}

enum MovementType {
  ATTACK
  SUPPORT
  RETURN
  TRADE
  RELOCATE
}

enum MovementStatus {
  OUTGOING
  INCOMING
  RETURNING
  STATIONED
  COMPLETED
}

enum RankingType {
  PLAYER
  TRIBE
  CONTINENT
  ODA
  ODD
}
```

## WebSocket Protocol

### Connection Establishment

```javascript
// WebSocket connection protocol
const ws = new WebSocket('wss://api.twlan.com/ws');

// Authentication
ws.on('open', () => {
  ws.send(JSON.stringify({
    type: 'auth',
    token: 'Bearer eyJhbGciOiJIUzI1NiIs...'
  }));
});

// Subscribe to channels
ws.send(JSON.stringify({
  type: 'subscribe',
  channels: [
    'village:123',
    'user:456',
    'tribe:789',
    'global'
  ]
}));

// Message types
const MessageTypes = {
  // System messages
  AUTH: 'auth',
  SUBSCRIBE: 'subscribe',
  UNSUBSCRIBE: 'unsubscribe',
  PING: 'ping',
  PONG: 'pong',
  
  // Game events
  RESOURCE_UPDATE: 'resource_update',
  BUILDING_COMPLETE: 'building_complete',
  UNIT_TRAINED: 'unit_trained',
  INCOMING_ATTACK: 'incoming_attack',
  BATTLE_RESULT: 'battle_result',
  MESSAGE_RECEIVED: 'message_received',
  
  // Real-time data
  MAP_UPDATE: 'map_update',
  RANKING_UPDATE: 'ranking_update',
  ONLINE_STATUS: 'online_status'
};
```

### Message Format

```typescript
interface WebSocketMessage {
  id: string;
  type: MessageType;
  channel?: string;
  timestamp: number;
  data: any;
}

// Example messages
{
  "id": "msg_123456",
  "type": "resource_update",
  "channel": "village:123",
  "timestamp": 1699564800000,
  "data": {
    "village_id": 123,
    "resources": {
      "wood": 5432,
      "clay": 4321,
      "iron": 3210
    }
  }
}

{
  "id": "msg_789012",
  "type": "incoming_attack",
  "channel": "user:456",
  "timestamp": 1699564860000,
  "data": {
    "movement_id": 9876,
    "from": {
      "village_id": 999,
      "village_name": "Enemy Village",
      "player_name": "Attacker123"
    },
    "to": {
      "village_id": 123,
      "village_name": "My Village"
    },
    "arrival_time": "2024-11-10T15:30:00Z",
    "units_visible": false
  }
}
```

## Database Schema

### Complete ERD

See: [../diagrams/API_DATABASE_SPECS-er-diagram-1.mmd](../diagrams/API_DATABASE_SPECS-er-diagram-1.mmd)

### Migration Scripts

```sql
-- Migration: 001_create_base_schema.sql
CREATE DATABASE IF NOT EXISTS twlan_2025
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE twlan_2025;

-- Create users table
CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  totp_secret VARCHAR(32) NULL,
  two_factor_enabled BOOLEAN DEFAULT FALSE,
  points INT UNSIGNED DEFAULT 0,
  rank INT UNSIGNED DEFAULT 0,
  tribe_id BIGINT UNSIGNED NULL,
  last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE,
  is_banned BOOLEAN DEFAULT FALSE,
  settings JSON DEFAULT '{}',
  PRIMARY KEY (id),
  UNIQUE KEY uk_username (username),
  UNIQUE KEY uk_email (email),
  KEY idx_points (points DESC),
  KEY idx_tribe (tribe_id),
  KEY idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create villages table
CREATE TABLE villages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  x_coord SMALLINT NOT NULL,
  y_coord SMALLINT NOT NULL,
  continent TINYINT GENERATED ALWAYS AS (
    FLOOR(y_coord / 100) * 10 + FLOOR(x_coord / 100)
  ) STORED,
  user_id BIGINT UNSIGNED NOT NULL,
  points INT UNSIGNED DEFAULT 26,
  loyalty TINYINT UNSIGNED DEFAULT 100,
  last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_capital BOOLEAN DEFAULT FALSE,
  PRIMARY KEY (id),
  UNIQUE KEY uk_coordinates (x_coord, y_coord),
  KEY idx_user (user_id),
  KEY idx_continent (continent),
  KEY idx_points (points DESC),
  CONSTRAINT fk_village_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create buildings table
CREATE TABLE buildings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  village_id BIGINT UNSIGNED NOT NULL,
  building_type ENUM('main', 'barracks', 'stable', 'garage', 'church', 
                     'academy', 'smithy', 'place', 'statue', 'market',
                     'timber_camp', 'clay_pit', 'iron_mine', 'farm',
                     'storage', 'wall') NOT NULL,
  level TINYINT UNSIGNED DEFAULT 0,
  upgrade_started TIMESTAMP NULL DEFAULT NULL,
  upgrade_completes TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_village_building (village_id, building_type),
  KEY idx_upgrade_completes (upgrade_completes),
  CONSTRAINT fk_building_village FOREIGN KEY (village_id)
    REFERENCES villages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create movements table
CREATE TABLE movements (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  movement_type ENUM('attack', 'support', 'return', 'trade', 'relocate') NOT NULL,
  from_village_id BIGINT UNSIGNED NOT NULL,
  to_village_id BIGINT UNSIGNED NOT NULL,
  from_user_id BIGINT UNSIGNED NOT NULL,
  to_user_id BIGINT UNSIGNED NOT NULL,
  departure_time TIMESTAMP NOT NULL,
  arrival_time TIMESTAMP NOT NULL,
  return_time TIMESTAMP NULL DEFAULT NULL,
  units JSON NOT NULL,
  resources JSON DEFAULT '{}',
  is_processed BOOLEAN DEFAULT FALSE,
  is_cancelled BOOLEAN DEFAULT FALSE,
  PRIMARY KEY (id),
  KEY idx_arrival (arrival_time, is_processed),
  KEY idx_from_user (from_user_id),
  KEY idx_to_user (to_user_id),
  CONSTRAINT fk_movement_from_village FOREIGN KEY (from_village_id)
    REFERENCES villages(id) ON DELETE CASCADE,
  CONSTRAINT fk_movement_to_village FOREIGN KEY (to_village_id)
    REFERENCES villages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration: 002_create_indices.sql
-- Performance indices
CREATE INDEX idx_village_resources ON villages(user_id, points DESC);
CREATE INDEX idx_movement_processing ON movements(arrival_time, is_processed)
  WHERE is_cancelled = FALSE;
CREATE INDEX idx_building_queue ON buildings(upgrade_completes)
  WHERE upgrade_completes IS NOT NULL;

-- Migration: 003_create_triggers.sql
DELIMITER $$

CREATE TRIGGER update_village_points
AFTER UPDATE ON buildings
FOR EACH ROW
BEGIN
  DECLARE total_points INT;
  
  SELECT SUM(
    CASE building_type
      WHEN 'main' THEN level * 10
      WHEN 'barracks' THEN level * 16
      WHEN 'stable' THEN level * 20
      WHEN 'garage' THEN level * 24
      WHEN 'church' THEN level * 10
      WHEN 'academy' THEN level * 512
      WHEN 'smithy' THEN level * 19
      WHEN 'place' THEN 0
      WHEN 'statue' THEN level * 24
      WHEN 'market' THEN level * 10
      WHEN 'timber_camp' THEN level * 6
      WHEN 'clay_pit' THEN level * 6
      WHEN 'iron_mine' THEN level * 6
      WHEN 'farm' THEN level * 5
      WHEN 'storage' THEN level * 6
      WHEN 'wall' THEN level * 8
      ELSE 0
    END
  ) INTO total_points
  FROM buildings
  WHERE village_id = NEW.village_id;
  
  UPDATE villages 
  SET points = total_points + 26
  WHERE id = NEW.village_id;
END$$

CREATE TRIGGER update_user_points
AFTER UPDATE ON villages
FOR EACH ROW
BEGIN
  UPDATE users u
  SET points = (
    SELECT SUM(points)
    FROM villages
    WHERE user_id = NEW.user_id
  )
  WHERE id = NEW.user_id;
END$$

DELIMITER ;
```

## API Security

### Security Implementation

See: [../diagrams/API_DATABASE_SPECS-flowchart-2.mmd](../diagrams/API_DATABASE_SPECS-flowchart-2.mmd)

### JWT Token Structure

```json
{
  "header": {
    "alg": "RS256",
    "typ": "JWT",
    "kid": "2024-11-key-1"
  },
  "payload": {
    "sub": "user:123456",
    "iss": "https://api.twlan.com",
    "aud": "https://game.twlan.com",
    "exp": 1699568400,
    "iat": 1699564800,
    "nbf": 1699564800,
    "jti": "jwt_abc123def456",
    "user": {
      "id": 123456,
      "username": "player123",
      "roles": ["player", "premium"],
      "tribe_id": 789
    },
    "permissions": [
      "village:read",
      "village:write",
      "tribe:read",
      "combat:execute"
    ],
    "session": {
      "ip": "192.168.1.100",
      "device": "Chrome/119.0",
      "fingerprint": "hash123..."
    }
  }
}
```

## Rate Limiting

### Rate Limit Configuration

```yaml
rate_limits:
  global:
    requests_per_second: 100
    burst: 200
    
  by_endpoint:
    auth_endpoints:
      /auth/login:
        requests_per_minute: 5
        lockout_duration: 900  # 15 minutes
      /auth/register:
        requests_per_hour: 3
        requests_per_day: 10
        
    game_endpoints:
      /villages:
        requests_per_minute: 60
      /combat/attack:
        requests_per_minute: 10
        cost_based: true  # Uses token bucket
        
    heavy_endpoints:
      /reports/export:
        requests_per_hour: 10
      /map/sector:
        requests_per_minute: 30
        cache_duration: 60
        
  by_user_tier:
    free:
      requests_per_hour: 1000
      concurrent_connections: 1
    premium:
      requests_per_hour: 10000
      concurrent_connections: 5
    vip:
      requests_per_hour: 100000
      concurrent_connections: 10
```

### Rate Limit Response Headers

```http
HTTP/1.1 200 OK
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1699565000
X-RateLimit-Policy: 100req/1m
Retry-After: 60

HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1699565000
Retry-After: 60
Content-Type: application/json

{
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests. Please retry after 60 seconds.",
    "details": {
      "limit": 100,
      "period": "1m",
      "retry_after": 60
    }
  }
}
```

---

## Summary

This comprehensive API and database documentation provides:

- ✅ **Complete REST API** specification with OpenAPI 3.0
- ✅ **GraphQL Schema** with queries, mutations, and subscriptions
- ✅ **WebSocket Protocol** for real-time updates
- ✅ **Database Schema** with complete ERD
- ✅ **Migration Scripts** for database setup
- ✅ **Security Implementation** with JWT and rate limiting
- ✅ **Detailed Examples** for all endpoints

### Implementation Checklist

1. ☐ Setup API gateway with rate limiting
2. ☐ Implement JWT authentication
3. ☐ Deploy database with migrations
4. ☐ Setup WebSocket server
5. ☐ Configure monitoring for all endpoints
6. ☐ Implement caching strategy
7. ☐ Setup API documentation portal
8. ☐ Configure automated testing

---

**Document Version**: 1.0.0  
**API Version**: 2.0.0  
**Last Updated**: November 2024
