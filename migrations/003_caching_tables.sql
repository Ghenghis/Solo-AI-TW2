-- Migration 003: Caching Tables
-- Purpose: Database-level caching for frequently accessed data
-- Date: 2025-11-10
-- Pass: 5 - Database Enhancement

USE twlan;

-- ============================================
-- TABLE: cache_villages
-- Purpose: Pre-computed village data for map display
-- ============================================
CREATE TABLE IF NOT EXISTS cache_villages (
    village_id INT UNSIGNED PRIMARY KEY,
    x INT NOT NULL,
    y INT NOT NULL,
    name VARCHAR(255),
    player_id INT UNSIGNED,
    player_name VARCHAR(255),
    alliance_id INT UNSIGNED,
    alliance_tag VARCHAR(10),
    points INT UNSIGNED DEFAULT 0,
    
    -- Pre-computed data
    continent TINYINT UNSIGNED GENERATED ALWAYS AS (FLOOR(x/100)*10 + FLOOR(y/100)) STORED,
    
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_coordinates (x, y),
    INDEX idx_player (player_id),
    INDEX idx_alliance (alliance_id),
    INDEX idx_continent (continent),
    INDEX idx_points (points DESC),
    INDEX idx_updated (last_updated DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: cache_players
-- Purpose: Pre-computed player rankings and stats
-- ============================================
CREATE TABLE IF NOT EXISTS cache_players (
    player_id INT UNSIGNED PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    alliance_id INT UNSIGNED,
    alliance_tag VARCHAR(10),
    
    -- Points
    total_points INT UNSIGNED DEFAULT 0,
    attack_points INT UNSIGNED DEFAULT 0,
    defense_points INT UNSIGNED DEFAULT 0,
    
    -- Rankings
    rank INT UNSIGNED DEFAULT 0,
    attack_rank INT UNSIGNED DEFAULT 0,
    defense_rank INT UNSIGNED DEFAULT 0,
    
    -- Counts
    village_count INT UNSIGNED DEFAULT 0,
    
    -- Activity
    last_activity TIMESTAMP,
    is_online BOOLEAN DEFAULT FALSE,
    
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_alliance (alliance_id),
    INDEX idx_rank (rank ASC),
    INDEX idx_points (total_points DESC),
    INDEX idx_attack_rank (attack_rank ASC),
    INDEX idx_defense_rank (defense_rank ASC),
    INDEX idx_updated (last_updated DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: cache_alliances
-- Purpose: Pre-computed alliance rankings
-- ============================================
CREATE TABLE IF NOT EXISTS cache_alliances (
    alliance_id INT UNSIGNED PRIMARY KEY,
    tag VARCHAR(10) NOT NULL,
    name VARCHAR(255),
    
    -- Stats
    total_points BIGINT UNSIGNED DEFAULT 0,
    member_count INT UNSIGNED DEFAULT 0,
    village_count INT UNSIGNED DEFAULT 0,
    average_points_per_member INT UNSIGNED DEFAULT 0,
    
    -- Rankings
    rank INT UNSIGNED DEFAULT 0,
    
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tag (tag),
    INDEX idx_rank (rank ASC),
    INDEX idx_points (total_points DESC),
    INDEX idx_members (member_count DESC),
    INDEX idx_updated (last_updated DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: cache_map_chunks
-- Purpose: Cache entire map sections for fast rendering
-- ============================================
CREATE TABLE IF NOT EXISTS cache_map_chunks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chunk_x INT NOT NULL, -- Chunk coordinate (e.g., 0-9 for x 0-99)
    chunk_y INT NOT NULL, -- Chunk coordinate (e.g., 0-9 for y 0-99)
    
    -- Cached JSON data
    villages_json LONGTEXT, -- JSON array of villages in this chunk
    
    -- Metadata
    village_count INT UNSIGNED DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_chunk (chunk_x, chunk_y),
    INDEX idx_updated (last_updated DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: query_cache
-- Purpose: Generic query result caching
-- ============================================
CREATE TABLE IF NOT EXISTS query_cache (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(255) NOT NULL UNIQUE,
    cache_value LONGTEXT,
    cache_type ENUM('json', 'serialized', 'text') DEFAULT 'json',
    
    -- Expiration
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    
    INDEX idx_key (cache_key),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: session_cache
-- Purpose: Fast session storage (fallback for Redis)
-- ============================================
CREATE TABLE IF NOT EXISTS session_cache (
    session_id VARCHAR(255) PRIMARY KEY,
    player_id INT UNSIGNED,
    session_data LONGTEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    
    INDEX idx_player (player_id),
    INDEX idx_activity (last_activity DESC),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Cleanup expired cache entries (scheduled job)
-- ============================================
DELIMITER $$

CREATE EVENT IF NOT EXISTS cleanup_expired_cache
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    -- Cleanup query cache
    DELETE FROM query_cache WHERE expires_at < NOW();
    
    -- Cleanup sessions
    DELETE FROM session_cache WHERE expires_at < NOW();
END$$

DELIMITER ;
