-- Migration 004: Audit & History Tracking
-- Purpose: Track all important game actions for security and analytics
-- Date: 2025-11-10
-- Pass: 5 - Database Enhancement

USE twlan;

-- ============================================
-- TABLE: audit_log
-- Purpose: Comprehensive audit trail
-- ============================================
CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Who
    player_id INT UNSIGNED,
    username VARCHAR(255),
    session_id VARCHAR(255),
    ip_address VARCHAR(45),
    
    -- What
    action_type ENUM(
        'login', 'logout', 'register',
        'attack_send', 'attack_cancel', 'village_rename', 'village_abandon',
        'alliance_join', 'alliance_leave', 'alliance_create', 'alliance_disband',
        'trade_send', 'trade_receive',
        'building_upgrade', 'unit_recruit',
        'settings_change', 'password_change',
        'admin_action', 'ban', 'unban'
    ) NOT NULL,
    action_category ENUM('auth', 'combat', 'economic', 'social', 'admin') NOT NULL,
    
    -- Details
    details JSON,
    affected_entity_type VARCHAR(50),
    affected_entity_id INT UNSIGNED,
    
    -- When & Where
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    server_time BIGINT UNSIGNED, -- Game time for replays
    
    -- Result
    success BOOLEAN DEFAULT TRUE,
    error_message TEXT,
    
    -- Indexes
    INDEX idx_player (player_id, created_at DESC),
    INDEX idx_action (action_type, created_at DESC),
    INDEX idx_category (action_category, created_at DESC),
    INDEX idx_time (created_at DESC),
    INDEX idx_ip (ip_address, created_at DESC),
    INDEX idx_affected (affected_entity_type, affected_entity_id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: village_history
-- Purpose: Track village ownership changes
-- ============================================
CREATE TABLE IF NOT EXISTS village_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    village_id INT UNSIGNED NOT NULL,
    village_name VARCHAR(255),
    
    -- Ownership change
    old_owner_id INT UNSIGNED,
    new_owner_id INT UNSIGNED,
    old_owner_name VARCHAR(255),
    new_owner_name VARCHAR(255),
    
    -- Change details
    change_type ENUM('conquered', 'abandoned', 'created', 'deleted') NOT NULL,
    points_at_time INT UNSIGNED,
    
    -- Context
    attack_report_id INT UNSIGNED,
    
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_village (village_id, changed_at DESC),
    INDEX idx_old_owner (old_owner_id, changed_at DESC),
    INDEX idx_new_owner (new_owner_id, changed_at DESC),
    INDEX idx_type (change_type, changed_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: player_history
-- Purpose: Track player account changes
-- ============================================
CREATE TABLE IF NOT EXISTS player_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT UNSIGNED NOT NULL,
    
    -- What changed
    change_type ENUM('registered', 'deleted', 'banned', 'unbanned', 'name_change', 'settings_change') NOT NULL,
    
    -- Old and new values
    old_value TEXT,
    new_value TEXT,
    
    -- Who made the change
    changed_by_id INT UNSIGNED, -- NULL = system or self
    changed_by_name VARCHAR(255),
    
    -- Why
    reason TEXT,
    
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_player (player_id, changed_at DESC),
    INDEX idx_type (change_type, changed_at DESC),
    INDEX idx_changer (changed_by_id, changed_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: alliance_history
-- Purpose: Track alliance changes
-- ============================================
CREATE TABLE IF NOT EXISTS alliance_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alliance_id INT UNSIGNED NOT NULL,
    
    -- What changed
    change_type ENUM('created', 'disbanded', 'member_join', 'member_leave', 'member_kick', 'war_declared', 'peace_declared', 'settings_change') NOT NULL,
    
    -- Who
    player_id INT UNSIGNED,
    player_name VARCHAR(255),
    
    -- Details
    details JSON,
    
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_alliance (alliance_id, changed_at DESC),
    INDEX idx_type (change_type, changed_at DESC),
    INDEX idx_player (player_id, changed_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: attack_archive
-- Purpose: Archived old attack reports for analytics
-- ============================================
CREATE TABLE IF NOT EXISTS attack_archive (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    original_report_id INT UNSIGNED,
    
    -- Participants
    attacker_id INT UNSIGNED,
    attacker_name VARCHAR(255),
    attacker_village_id INT UNSIGNED,
    
    defender_id INT UNSIGNED,
    defender_name VARCHAR(255),
    defender_village_id INT UNSIGNED,
    
    -- Result
    result ENUM('attacker_win', 'defender_win', 'draw') NOT NULL,
    
    -- Units (JSON for flexibility)
    attacker_units_sent JSON,
    attacker_units_lost JSON,
    defender_units_lost JSON,
    
    -- Resources
    resources_looted JSON,
    
    -- Timing
    sent_at TIMESTAMP,
    arrived_at TIMESTAMP,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_attacker (attacker_id, arrived_at DESC),
    INDEX idx_defender (defender_id, arrived_at DESC),
    INDEX idx_result (result, arrived_at DESC),
    INDEX idx_archived (archived_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Create partitions for audit_log (by month)
-- This keeps queries fast even with millions of rows
-- ============================================
-- Note: Partitioning syntax - implement if needed for large-scale deployment
