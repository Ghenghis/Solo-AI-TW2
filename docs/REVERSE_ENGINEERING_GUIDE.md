# TWLan 2.A3 - Reverse Engineering Guide
## Complete Technical Analysis & System Blueprints

### Table of Contents
1. [Executive Summary](#executive-summary)
2. [System Architecture Analysis](#system-architecture-analysis)
3. [Binary Structure Analysis](#binary-structure-analysis)
4. [Network Protocol Reverse Engineering](#network-protocol-reverse-engineering)
5. [Database Schema Reconstruction](#database-schema-reconstruction)
6. [Game Logic Decompilation](#game-logic-decompilation)
7. [Security Analysis](#security-analysis)
8. [Modernization Pathways](#modernization-pathways)

---

## Executive Summary

TWLan 2.A3 represents a self-contained LAMP stack implementation designed for local network gameplay. This guide provides complete reverse engineering documentation for understanding, modernizing, and extending the system.

### Key Findings
- **Architecture**: Monolithic PHP 5.x application with MySQL 5.x backend
- **Bundled Components**: Custom-compiled 32-bit binaries for PHP, MySQL, Apache
- **Game Engine**: Turn-based strategy with tick-based resource calculation
- **Security Model**: LAN-only with minimal authentication
- **Protocol**: HTTP/1.1 with form-based interactions

## System Architecture Analysis

### Component Hierarchy

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-1.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-1.mmd)

### Directory Structure Analysis

```
TWLan-2.A3-linux64/
├── bin/                    # Binary executables
│   ├── mysqld             # MySQL daemon (32-bit)
│   ├── mysql              # MySQL client
│   ├── mysqladmin         # MySQL admin tool
│   ├── mysqlcheck         # Database checker
│   ├── mysqldump          # Database backup
│   ├── mysql_install_db   # DB initializer
│   ├── php                # PHP interpreter
│   └── php-cgi            # PHP CGI binary
├── htdocs/                # Web root
│   ├── index.php          # Entry point
│   ├── game.php           # Game controller
│   ├── ajax.php           # AJAX handler
│   ├── admin.php          # Admin interface
│   ├── include/           # PHP includes
│   ├── templates/         # HTML templates
│   ├── graphic/           # Images/sprites
│   ├── js/                # JavaScript files
│   └── lang/              # Language files
├── lib/                   # Libraries
│   ├── php.ini            # PHP configuration
│   ├── my.cnf             # MySQL configuration
│   ├── php/               # PHP extensions
│   └── mysql/             # MySQL libraries
├── db/                    # Database files
├── tmp/                   # Temporary files
└── logs/                  # Log files
```

## Binary Structure Analysis

### PHP Binary Analysis

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-2.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-2.mmd)

### Binary Dependencies

```bash
# Analyzing bin/php dependencies
ldd bin/php
    linux-gate.so.1
    libcrypt.so.1
    librt.so.1
    libmysqlclient.so.16
    libz.so.1
    libm.so.6
    libc.so.6
    libpthread.so.0

# Binary Information
file bin/php
    ELF 32-bit LSB executable, Intel 80386
    dynamically linked
    for GNU/Linux 2.6.9
    stripped

# PHP Version embedded
strings bin/php | grep "PHP"
    PHP 5.2.17
    Zend Engine v2.2.0
```

## Network Protocol Reverse Engineering

### Request Flow Analysis

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-sequence-3.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-sequence-3.mmd)

### Protocol Specifications

```yaml
HTTP Protocol:
  Version: HTTP/1.1
  Methods: GET, POST
  Content-Types:
    - application/x-www-form-urlencoded
    - text/html
    - application/json (AJAX)
  
Session Management:
  Type: PHP Sessions (file-based)
  Cookie: PHPSESSID
  Storage: /tmp/sess_*
  Timeout: 1440 seconds

AJAX Endpoints:
  /ajax.php:
    - action=get_resources
    - action=get_reports  
    - action=check_attacks
    - action=update_map
    - action=get_messages

Form Submissions:
  /game.php:
    - screen=overview
    - screen=map
    - screen=report
    - screen=mail
    - action=build
    - action=train
    - action=attack
```

## Database Schema Reconstruction

### Core Tables Structure

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-er-diagram-4.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-er-diagram-4.mmd)

### Database Queries Analysis

```sql
-- Resource Calculation Query (runs every tick)
UPDATE villages 
SET 
    wood = LEAST(
        wood + (wood_production * time_diff), 
        wood_storage_capacity
    ),
    clay = LEAST(
        clay + (clay_production * time_diff), 
        clay_storage_capacity
    ),
    iron = LEAST(
        iron + (iron_production * time_diff), 
        iron_storage_capacity
    ),
    last_update = NOW()
WHERE 
    user_id > 0;

-- Battle Resolution Query
SELECT 
    m.*,
    v1.name as from_name,
    v2.name as to_name,
    u1.user_id as attacker_id,
    u2.user_id as defender_id
FROM movements m
JOIN villages v1 ON m.from_village = v1.id
JOIN villages v2 ON m.to_village = v2.id
JOIN users u1 ON v1.user_id = u1.id
JOIN users u2 ON v2.user_id = u2.id
WHERE 
    m.arrival_time <= NOW()
    AND m.processed = 0;

-- Ranking Calculation
UPDATE users u
SET 
    points = (
        SELECT SUM(points) 
        FROM villages 
        WHERE user_id = u.id
    ),
    rank = (
        SELECT COUNT(*) + 1 
        FROM users u2 
        WHERE u2.points > u.points
    );
```

## Game Logic Decompilation

### Core Game Loop

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-5.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-5.mmd)

### Resource Production Algorithm

```php
// Reverse-engineered resource production
class ResourceCalculator {
    const BASE_PRODUCTION = [
        'wood' => 30,
        'clay' => 30,
        'iron' => 30
    ];
    
    const BUILDING_MULTIPLIERS = [
        'timber_camp' => ['resource' => 'wood', 'factor' => 1.163118],
        'clay_pit' => ['resource' => 'clay', 'factor' => 1.163118],
        'iron_mine' => ['resource' => 'iron', 'factor' => 1.163118]
    ];
    
    public function calculateProduction($building_level, $resource_type) {
        $base = self::BASE_PRODUCTION[$resource_type];
        $multiplier = self::BUILDING_MULTIPLIERS["{$resource_type}_building"];
        
        // Formula: base * (multiplier ^ level)
        return round($base * pow($multiplier['factor'], $building_level));
    }
    
    public function calculateStorage($storage_level) {
        // Storage formula: 1000 * 1.2294934 ^ level
        return round(1000 * pow(1.2294934, $storage_level));
    }
    
    public function calculateBuildTime($building_level, $building_type) {
        $base_time = $this->building_config[$building_type]['base_time'];
        $time_factor = $this->building_config[$building_type]['time_factor'];
        
        // Time formula: base_time * (time_factor ^ level)
        $seconds = round($base_time * pow($time_factor, $building_level));
        
        // Apply world speed
        return $seconds / $this->world_speed;
    }
}
```

### Battle Calculation System

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-state-diagram-6.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-state-diagram-6.mmd)

## Security Analysis

### Vulnerability Assessment

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-7.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-7.mmd)

### Security Hardening Plan

```yaml
Immediate Fixes:
  SQL Injection:
    - Replace: mysql_query() with prepared statements
    - Implement: Input validation layer
    - Add: Query parameterization
    
  Authentication:
    - Replace: MD5 with BCrypt
    - Implement: Password policies
    - Add: Account lockout mechanism
    
  Session Security:
    - Enable: session.use_only_cookies
    - Set: session.cookie_httponly = true
    - Implement: Session regeneration on login
    
  XSS Prevention:
    - Add: htmlspecialchars() on all outputs
    - Implement: Content Security Policy
    - Use: Template engine with auto-escaping

Architecture Improvements:
  - Add: Web Application Firewall
  - Implement: Rate limiting
  - Enable: HTTPS only
  - Add: Security headers
  - Implement: Audit logging
```

## Modernization Pathways

### Migration Strategy

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-8.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-8.mmd)

### Code Modernization Examples

```php
// Original Code (PHP 5.2)
function get_user_villages($user_id) {
    $user_id = mysql_real_escape_string($user_id);
    $query = "SELECT * FROM villages WHERE user_id = '$user_id'";
    $result = mysql_query($query);
    $villages = array();
    while ($row = mysql_fetch_assoc($result)) {
        $villages[] = $row;
    }
    return $villages;
}

// Modernized Code (PHP 8.4)
class VillageRepository {
    private PDO $db;
    
    public function getUserVillages(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM villages WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// With Modern ORM (Doctrine)
class VillageService {
    public function getUserVillages(User $user): Collection {
        return $this->entityManager
            ->getRepository(Village::class)
            ->findBy(['user' => $user]);
    }
}
```

## Performance Optimization Blueprint

### Current Bottlenecks

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-9.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-9.mmd)

### Optimization Implementation

```sql
-- Add Missing Indexes
CREATE INDEX idx_villages_user ON villages(user_id);
CREATE INDEX idx_movements_arrival ON movements(arrival_time, processed);
CREATE INDEX idx_messages_recipient ON messages(recipient_id, read);
CREATE INDEX idx_reports_user ON reports(user_id, created_at);

-- Optimize Resource Calculation
CREATE VIEW village_production AS
SELECT 
    v.id,
    v.user_id,
    GREATEST(0, TIMESTAMPDIFF(SECOND, v.last_update, NOW())) as seconds_elapsed,
    (30 * POW(1.163118, COALESCE(b1.level, 0))) as wood_rate,
    (30 * POW(1.163118, COALESCE(b2.level, 0))) as clay_rate,
    (30 * POW(1.163118, COALESCE(b3.level, 0))) as iron_rate
FROM villages v
LEFT JOIN buildings b1 ON v.id = b1.village_id AND b1.type = 'timber_camp'
LEFT JOIN buildings b2 ON v.id = b2.village_id AND b2.type = 'clay_pit'
LEFT JOIN buildings b3 ON v.id = b3.village_id AND b3.type = 'iron_mine';
```

## Advanced Features Blueprint

### Real-time WebSocket Architecture

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-10.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-10.mmd)

### API Design Specification

```yaml
openapi: 3.0.0
info:
  title: TWLan 2025 API
  version: 2.0.0
  
paths:
  /api/villages:
    get:
      summary: Get user villages
      security:
        - bearerAuth: []
      responses:
        200:
          content:
            application/json:
              schema:
                type: array
                items:
                  $ref: '#/components/schemas/Village'
                  
  /api/villages/{id}/build:
    post:
      summary: Build in village
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                building:
                  type: string
                level:
                  type: integer
                  
  /api/battle/attack:
    post:
      summary: Send attack
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                from_village:
                  type: integer
                to_village:
                  type: integer
                units:
                  type: object

components:
  schemas:
    Village:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
        coordinates:
          type: object
          properties:
            x:
              type: integer
            y:
              type: integer
        resources:
          type: object
          properties:
            wood:
              type: integer
            clay:
              type: integer
            iron:
              type: integer
```

## Testing Strategy

### Test Coverage Blueprint

See: [../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-11.mmd](../diagrams/REVERSE_ENGINEERING_GUIDE-flowchart-11.mmd)

## Conclusion

This reverse engineering guide provides a complete blueprint for understanding, modernizing, and extending TWLan 2.A3. The analysis covers:

- ✅ Complete system architecture
- ✅ Binary structure and dependencies
- ✅ Network protocol specifications
- ✅ Database schema and queries
- ✅ Game logic algorithms
- ✅ Security vulnerabilities and fixes
- ✅ Modernization pathways
- ✅ Performance optimizations
- ✅ Advanced feature blueprints

### Next Steps

1. **Immediate**: Deploy containerized version
2. **Short-term**: Apply security patches
3. **Medium-term**: Migrate to modern PHP
4. **Long-term**: Implement microservices architecture

---

**Document Version**: 1.0.0  
**Classification**: Technical Reference  
**Last Updated**: November 2024
