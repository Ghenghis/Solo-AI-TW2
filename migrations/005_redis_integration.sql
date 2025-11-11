-- Migration 005: Redis Integration Support
-- Purpose: Tables to support Redis caching layer
-- Date: 2025-11-10
-- Pass: 5 - Database Enhancement

USE twlan;

-- ============================================
-- TABLE: cache_invalidation
-- Purpose: Track what needs to be invalidated in Redis
-- ============================================
CREATE TABLE IF NOT EXISTS cache_invalidation (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cache_key_pattern VARCHAR(255) NOT NULL,
    invalidation_type ENUM('exact', 'pattern', 'tag') DEFAULT 'exact',
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed BOOLEAN DEFAULT FALSE,
    processed_at TIMESTAMP NULL,
    
    INDEX idx_processed (processed, created_at ASC),
    INDEX idx_pattern (cache_key_pattern)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: cache_stats
-- Purpose: Track cache hit/miss rates
-- ============================================
CREATE TABLE IF NOT EXISTS cache_stats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stat_date DATE NOT NULL,
    cache_type ENUM('redis', 'database', 'application') NOT NULL,
    cache_key_prefix VARCHAR(100),
    
    -- Metrics
    hit_count BIGINT UNSIGNED DEFAULT 0,
    miss_count BIGINT UNSIGNED DEFAULT 0,
    set_count BIGINT UNSIGNED DEFAULT 0,
    delete_count BIGINT UNSIGNED DEFAULT 0,
    
    -- Performance
    avg_hit_time_ms DECIMAL(10,2),
    avg_miss_time_ms DECIMAL(10,2),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_date_type_prefix (stat_date, cache_type, cache_key_prefix),
    INDEX idx_date (stat_date DESC),
    INDEX idx_type (cache_type, stat_date DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: hot_data_cache
-- Purpose: Identify frequently accessed data for Redis
-- ============================================
CREATE TABLE IF NOT EXISTS hot_data_cache (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data_type ENUM('player', 'village', 'alliance', 'report', 'ranking') NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    
    -- Access tracking
    access_count INT UNSIGNED DEFAULT 0,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    access_frequency DECIMAL(10,2), -- Accesses per hour
    
    -- Caching decision
    should_cache BOOLEAN DEFAULT TRUE,
    cache_priority TINYINT UNSIGNED DEFAULT 1, -- 1-10, higher = more important
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_type_entity (data_type, entity_id),
    INDEX idx_frequency (access_frequency DESC),
    INDEX idx_priority (cache_priority DESC, access_frequency DESC),
    INDEX idx_last_accessed (last_accessed DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Pre-populate hot data (common queries)
-- ============================================
-- These are always hot and should be in Redis
-- Note: These will only work once the game tables are created
-- Uncomment when game is initialized:

-- INSERT IGNORE INTO hot_data_cache (data_type, entity_id, cache_priority, access_frequency) 
-- SELECT 'player', id, 10, 100.0 
-- FROM users 
-- WHERE last_activity > DATE_SUB(NOW(), INTERVAL 1 DAY)
-- LIMIT 1000;

-- INSERT IGNORE INTO hot_data_cache (data_type, entity_id, cache_priority, access_frequency)
-- SELECT 'alliance', id, 8, 50.0
-- FROM ally
-- WHERE member_count > 5
-- LIMIT 100;
