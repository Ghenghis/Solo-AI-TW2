-- Migration 002: Player Statistics & Leaderboard Tables
-- Purpose: Track player performance and enable real-time leaderboards
-- Date: 2025-11-10
-- Pass: 5 - Database Enhancement

USE twlan;

-- ============================================
-- TABLE: player_statistics
-- Purpose: Track detailed player stats over time
-- ============================================
CREATE TABLE IF NOT EXISTS player_statistics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT UNSIGNED NOT NULL,
    stat_date DATE NOT NULL,
    
    -- Combat Stats
    attacks_sent INT UNSIGNED DEFAULT 0,
    attacks_won INT UNSIGNED DEFAULT 0,
    defenses_won INT UNSIGNED DEFAULT 0,
    villages_conquered INT UNSIGNED DEFAULT 0,
    villages_lost INT UNSIGNED DEFAULT 0,
    
    -- Economic Stats
    resources_gathered BIGINT UNSIGNED DEFAULT 0,
    resources_traded BIGINT UNSIGNED DEFAULT 0,
    buildings_upgraded INT UNSIGNED DEFAULT 0,
    units_recruited BIGINT UNSIGNED DEFAULT 0,
    units_lost BIGINT UNSIGNED DEFAULT 0,
    
    -- Points & Rankings
    total_points INT UNSIGNED DEFAULT 0,
    attack_points INT UNSIGNED DEFAULT 0,
    defense_points INT UNSIGNED DEFAULT 0,
    rank_position INT UNSIGNED DEFAULT 0,
    
    -- Activity
    login_count INT UNSIGNED DEFAULT 0,
    online_time_minutes INT UNSIGNED DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_player_date (player_id, stat_date DESC),
    INDEX idx_date (stat_date DESC),
    INDEX idx_total_points (total_points DESC),
    INDEX idx_attack_points (attack_points DESC),
    INDEX idx_defense_points (defense_points DESC),
    
    -- Unique constraint
    UNIQUE KEY uk_player_date (player_id, stat_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: alliance_statistics
-- Purpose: Track alliance performance
-- ============================================
CREATE TABLE IF NOT EXISTS alliance_statistics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alliance_id INT UNSIGNED NOT NULL,
    stat_date DATE NOT NULL,
    
    -- Member Stats
    member_count INT UNSIGNED DEFAULT 0,
    active_members INT UNSIGNED DEFAULT 0,
    
    -- Combat Stats
    total_attacks INT UNSIGNED DEFAULT 0,
    total_conquers INT UNSIGNED DEFAULT 0,
    wars_won INT UNSIGNED DEFAULT 0,
    
    -- Territory
    total_villages INT UNSIGNED DEFAULT 0,
    total_points BIGINT UNSIGNED DEFAULT 0,
    average_points_per_member INT UNSIGNED DEFAULT 0,
    
    -- Rankings
    rank_position INT UNSIGNED DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_alliance_date (alliance_id, stat_date DESC),
    INDEX idx_date (stat_date DESC),
    INDEX idx_total_points (total_points DESC),
    INDEX idx_rank (rank_position ASC),
    
    -- Unique constraint
    UNIQUE KEY uk_alliance_date (alliance_id, stat_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: leaderboards
-- Purpose: Pre-computed leaderboard data for fast display
-- ============================================
CREATE TABLE IF NOT EXISTS leaderboards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leaderboard_type ENUM('player_points', 'player_attack', 'player_defense', 'player_villages', 'alliance_points', 'alliance_members') NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    entity_name VARCHAR(255) NOT NULL,
    
    -- Values
    primary_value BIGINT UNSIGNED DEFAULT 0,
    secondary_value BIGINT UNSIGNED DEFAULT 0,
    rank_position INT UNSIGNED DEFAULT 0,
    rank_change INT DEFAULT 0, -- Positive = moved up, negative = moved down
    
    -- Metadata
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_type_rank (leaderboard_type, rank_position ASC),
    INDEX idx_entity (entity_id, leaderboard_type),
    INDEX idx_updated (last_updated DESC),
    
    -- Unique constraint
    UNIQUE KEY uk_type_entity (leaderboard_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: achievements
-- Purpose: Player achievement system
-- ============================================
CREATE TABLE IF NOT EXISTS achievements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    achievement_key VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    category ENUM('combat', 'economic', 'social', 'special') DEFAULT 'special',
    points INT UNSIGNED DEFAULT 0,
    is_hidden BOOLEAN DEFAULT FALSE,
    
    -- Requirements (JSON format)
    requirements JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_points (points DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: player_achievements
-- Purpose: Track which players earned which achievements
-- ============================================
CREATE TABLE IF NOT EXISTS player_achievements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT UNSIGNED NOT NULL,
    achievement_id INT UNSIGNED NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progress INT UNSIGNED DEFAULT 0, -- For progressive achievements
    
    INDEX idx_player (player_id, earned_at DESC),
    INDEX idx_achievement (achievement_id),
    
    UNIQUE KEY uk_player_achievement (player_id, achievement_id),
    
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Sample Achievements
-- ============================================
INSERT IGNORE INTO achievements (achievement_key, name, description, category, points) VALUES
('first_village', 'Settler', 'Founded your first village', 'special', 10),
('10_villages', 'Baron', 'Control 10 villages', 'special', 50),
('first_attack', 'Warrior', 'Launch your first attack', 'combat', 10),
('100_attacks', 'Warlord', 'Launch 100 attacks', 'combat', 100),
('first_defense', 'Guardian', 'Successfully defend an attack', 'combat', 10),
('join_alliance', 'Diplomat', 'Join an alliance', 'social', 10),
('alliance_founder', 'Founder', 'Create an alliance', 'social', 25),
('1000_points', 'Rising Star', 'Reach 1,000 points', 'special', 20),
('10000_points', 'Noble', 'Reach 10,000 points', 'special', 50),
('top_10', 'Elite Player', 'Reach top 10 ranking', 'special', 200);
