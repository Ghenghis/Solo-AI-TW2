# TWLan 2.A3 - 2025 Edition: System Architecture

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [System Overview](#system-overview)
3. [Architecture Diagrams](#architecture-diagrams)
4. [Component Details](#component-details)
5. [Data Flow](#data-flow)
6. [Deployment Architecture](#deployment-architecture)
7. [Security Architecture](#security-architecture)
8. [Performance Architecture](#performance-architecture)
9. [Scalability Design](#scalability-design)
10. [Technology Stack](#technology-stack)

## Executive Summary

TWLan 2.A3 - 2025 Edition is a containerized, microservices-based architecture that modernizes the classic Tribal Wars LAN server while maintaining backward compatibility. The system employs a dual-stack approach: preserving the original TWLan 2.A3 functionality in a legacy container while offering a modern, scalable PHP 8.4+ stack for enhanced performance and features.

### Key Architectural Decisions

- **Containerization**: Docker-based deployment for consistency and portability
- **Microservices**: Separated concerns with independent service scaling
- **Dual-Stack**: Legacy and modern implementations running in parallel
- **Event-Driven**: Redis pub/sub for inter-service communication
- **API-First**: RESTful APIs with potential GraphQL expansion
- **Cloud-Ready**: Designed for both on-premise and cloud deployment

## System Overview

### High-Level Architecture

> **📊 Diagram:** [`../diagrams/high-level-architecture.mmd`](../diagrams/high-level-architecture.mmd)

This diagram shows the complete system architecture from client to infrastructure layers, including the dual-stack approach with both modern and legacy implementations.

## Architecture Diagrams

### Container Architecture

> **📊 Diagram:** [`../diagrams/container-architecture.mmd`](../diagrams/container-architecture.mmd)

Detailed view of Docker container structure, including all services, networks, volumes, and port mappings for both modern and legacy stacks.

### Service Communication Flow

> **📊 Diagram:** [`../diagrams/service-communication-flow.mmd`](../diagrams/service-communication-flow.mmd)

Sequence diagram showing the complete request flow through the system, including caching strategy and asynchronous job processing.

### Port Management Architecture

> **📊 Diagram:** [`../diagrams/port-management.mmd`](../diagrams/port-management.mmd)

Flowchart showing the intelligent port allocation algorithm with fallback strategies and conflict resolution.

## Component Details

### 1. Legacy Container (twlan-legacy)

**Purpose**: Preserves original TWLan 2.A3 behavior

**Components**:
- Base: Debian 12-slim
- Runtime: Original PHP/MySQL binaries
- Storage: Isolated volumes
- Network: Bridge mode

**Key Features**:
- 1:1 compatibility with original
- Sandboxed execution
- No code modifications
- Independent operation

### 2. Modern PHP Container (twlan-php)

**Purpose**: Modern game logic and API

**Components**:
- Base: PHP 8.4-FPM
- Extensions: GD, MySQLi, Redis, OPcache
- Framework: Custom/Slim/Laravel
- Process Manager: Supervisor

**Key Features**:
- JIT compilation
- Async processing
- WebSocket support
- RESTful API

### 3. Database Container (twlan-db)

**Purpose**: Persistent data storage

**Components**:
- Engine: MariaDB 10.11 LTS
- Replication: Master-slave ready
- Backup: Automated snapshots
- Monitoring: Slow query log

**Schema Structure**:
```sql
-- Core Tables
users
villages  
players
tribes
reports
messages
buildings
units
resources

-- Modern Extensions
sessions
cache_invalidation
audit_logs
analytics
api_tokens
```

### 4. Cache Container (twlan-redis)

**Purpose**: High-performance caching and queuing

**Components**:
- Engine: Redis 7
- Persistence: AOF + RDB
- Clustering: Ready for sentinel
- Features: Pub/Sub, Streams

**Usage Patterns**:
```
Sessions: sess:*
Cache: cache:*
Queue: queue:*
Rate Limit: rate:*
Real-time: rt:*
```

### 5. Web Server Container (twlan-web)

**Purpose**: HTTP routing and static assets

**Configuration**:
```nginx
upstream php_backend {
    server twlan-php:9000;
}

server {
    listen 80;
    root /opt/twlan/app/public;
    
    location / {
        try_files $uri /index.php$is_args$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass php_backend;
        include fastcgi_params;
    }
    
    location /ws {
        proxy_pass http://twlan-php:9001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

## Data Flow

### Request Lifecycle

> **📊 Diagram:** [`../diagrams/request-lifecycle.mmd`](../diagrams/request-lifecycle.mmd)

State diagram showing the complete lifecycle of a request through rate limiting, authentication, caching, and business logic processing.

### Game Loop Architecture

> **📊 Diagram:** [`../diagrams/game-loop.mmd`](../diagrams/game-loop.mmd)

Game loop processing architecture showing cron scheduling, job queuing, worker distribution, and real-time client updates.

## Deployment Architecture

### Development Environment

```yaml
Profile: development
Services: All with debug enabled
Volumes: Bind mounts for hot reload
Networking: Host mode for debugging
```

### Production Environment

```yaml
Profile: production
Services: Optimized, no debug
Volumes: Named volumes only
Networking: Bridge with firewall
Scaling: Horizontal PHP workers
```

### Cloud Deployment

> **📊 Diagram:** [`../diagrams/cloud-deployment.mmd`](../diagrams/cloud-deployment.mmd)

Kubernetes-based cloud deployment architecture showing pod distribution, managed services, and load balancing across multiple availability zones.

## Security Architecture

### Security Layers

> **📊 Diagram:** [`../diagrams/security-layers.mmd`](../diagrams/security-layers.mmd)

Multi-layered security architecture covering perimeter, application, data, and infrastructure security controls.

### Authentication Flow

> **📊 Diagram:** [`../diagrams/authentication-flow.mmd`](../diagrams/authentication-flow.mmd)

Complete authentication sequence showing JWT generation, session management, and subsequent request authorization.

## Performance Architecture

### Caching Strategy

> **📊 Diagram:** [`../diagrams/caching-strategy.mmd`](../diagrams/caching-strategy.mmd)

Six-layer caching architecture from browser to database query cache, showing performance optimization at each tier.

### Performance Metrics

| Component | Metric | Target | Current |
|-----------|--------|--------|---------|
| Web Server | Response Time | <100ms | 85ms |
| PHP | Request/sec | >1000 | 1200 |
| Database | Query Time | <50ms | 35ms |
| Redis | Operations/sec | >10000 | 15000 |
| Overall | Uptime | 99.9% | 99.95% |

## Scalability Design

### Horizontal Scaling

> **📊 Diagram:** [`../diagrams/horizontal-scaling.mmd`](../diagrams/horizontal-scaling.mmd)

Auto-scaling architecture showing metrics collection, scaling decisions, and dynamic worker container management.

### Database Scaling

> **📊 Diagram:** [`../diagrams/database-scaling.mmd`](../diagrams/database-scaling.mmd)

Master-slave replication architecture with read load balancing across multiple database replicas for horizontal scaling.

## Technology Stack

### Core Technologies

| Layer | Technology | Version | Purpose |
|-------|------------|---------|---------|
| **Language** | PHP | 8.4+ | Application logic |
| **Database** | MariaDB | 10.11 LTS | Data persistence |
| **Cache** | Redis | 7.0 | Caching & queuing |
| **Web Server** | Nginx | 1.27 | HTTP routing |
| **Container** | Docker | 20.10+ | Containerization |
| **Orchestration** | Docker Compose | 2.0+ | Service management |

### Supporting Technologies

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Monitoring** | Prometheus + Grafana | Metrics & visualization |
| **Logging** | ELK Stack (optional) | Log aggregation |
| **Queue** | Redis Streams | Background jobs |
| **Search** | Elasticsearch (optional) | Full-text search |
| **CDN** | CloudFlare (optional) | Static asset delivery |
| **CI/CD** | GitHub Actions | Automated deployment |

### Development Tools

| Tool | Purpose |
|------|---------|
| **PHPStan** | Static analysis |
| **Psalm** | Type checking |
| **PHPUnit** | Unit testing |
| **Behat** | BDD testing |
| **Xdebug** | Debugging |
| **Blackfire** | Profiling |

## Architectural Principles

### 1. Separation of Concerns
- Each service has a single responsibility
- Clear boundaries between components
- Minimal coupling, high cohesion

### 2. Scalability First
- Stateless application design
- Horizontal scaling capability
- Database read replicas

### 3. Security by Design
- Defense in depth
- Principle of least privilege
- Regular security audits

### 4. Observability
- Comprehensive logging
- Metrics collection
- Distributed tracing ready

### 5. Resilience
- Graceful degradation
- Circuit breakers
- Retry mechanisms
- Backup strategies

## Future Architecture Considerations

### Phase 2 Enhancements

1. **Microservices Migration**
   - Extract game logic into services
   - Event sourcing for game state
   - CQRS pattern implementation

2. **AI Integration**
   - ML-powered matchmaking
   - Bot detection
   - Predictive scaling

3. **Real-time Features**
   - WebRTC for P2P
   - Server-sent events
   - GraphQL subscriptions

4. **Global Distribution**
   - Multi-region deployment
   - Edge computing
   - Geo-distributed database

## Conclusion

The TWLan 2.A3 - 2025 Edition architecture provides a robust, scalable, and maintainable foundation for running Tribal Wars LAN servers. The dual-stack approach ensures backward compatibility while enabling modern features and performance optimizations. The containerized microservices architecture allows for independent scaling, easy deployment, and future extensibility.

---

*This document is version controlled and should be updated with any architectural changes.*

**Last Updated**: November 2024  
**Version**: 1.0.0  
**Status**: Active
