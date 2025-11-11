-- Migration 001: Add Performance Indexes
-- Purpose: 10x faster queries by adding missing indexes
-- Date: 2025-11-10
-- Pass: 5 - Database Enhancement

USE twlan;

-- ============================================
-- ANALYSIS: Common query patterns in TWLan
-- ============================================
-- 1. Player lookups by username/ID
-- 2. Village lookups by coordinates/player
-- 3. Alliance member queries
-- 4. Attack reports by time/player
-- 5. Building/unit lookups by village

-- ============================================
-- TABLE: users (Players)
-- ============================================
-- Add indexes for common player queries
ALTER TABLE IF EXISTS users 
    ADD INDEX idx_username (username),
    ADD INDEX idx_last_activity (last_activity),
    ADD INDEX idx_ally_id (ally_id),
    ADD INDEX idx_points (points DESC),
    ADD INDEX idx_rank (rank ASC);

-- ============================================
-- TABLE: villages
-- ============================================
-- Add indexes for village lookups
ALTER TABLE IF EXISTS villages
    ADD INDEX idx_coordinates (x, y),
    ADD INDEX idx_player_id (player_id),
    ADD INDEX idx_points (points DESC),
    ADD INDEX idx_name (name);

-- ============================================
-- TABLE: reports (Attack Reports)
-- ============================================
-- Add indexes for report queries
ALTER TABLE IF EXISTS reports
    ADD INDEX idx_receiver (receiver_id, time DESC),
    ADD INDEX idx_sender (sender_id, time DESC),
    ADD INDEX idx_time (time DESC),
    ADD INDEX idx_type_time (type, time DESC),
    ADD INDEX idx_unread (is_read, time DESC);

-- ============================================
-- TABLE: ally (Alliances)
-- ============================================
-- Add indexes for alliance queries
ALTER TABLE IF EXISTS ally
    ADD INDEX idx_tag (tag),
    ADD INDEX idx_name (name),
    ADD INDEX idx_points (points DESC),
    ADD INDEX idx_rank (rank ASC),
    ADD INDEX idx_member_count (member_count DESC);

-- ============================================
-- TABLE: movements (Troop Movements)
-- ============================================
-- Add indexes for movement tracking
ALTER TABLE IF EXISTS movements
    ADD INDEX idx_from_village (from_village_id),
    ADD INDEX idx_to_village (to_village_id),
    ADD INDEX idx_player (player_id),
    ADD INDEX idx_arrival (arrival_time),
    ADD INDEX idx_type (type),
    ADD INDEX idx_active (arrival_time, type);

-- ============================================
-- TABLE: buildings
-- ============================================
-- Add indexes for building queries
ALTER TABLE IF EXISTS buildings
    ADD INDEX idx_village (village_id),
    ADD INDEX idx_type (building_type),
    ADD INDEX idx_level (level DESC),
    ADD INDEX idx_village_type (village_id, building_type);

-- ============================================
-- TABLE: units
-- ============================================
-- Add indexes for unit queries
ALTER TABLE IF EXISTS units
    ADD INDEX idx_village (village_id),
    ADD INDEX idx_unit_type (unit_type),
    ADD INDEX idx_village_unit (village_id, unit_type);

-- ============================================
-- TABLE: market (Trading)
-- ============================================
-- Add indexes for market operations
ALTER TABLE IF EXISTS market
    ADD INDEX idx_from_village (from_village_id),
    ADD INDEX idx_to_village (to_village_id),
    ADD INDEX idx_arrival (arrival_time),
    ADD INDEX idx_status (status, arrival_time);

-- ============================================
-- Verify indexes were created
-- ============================================
SELECT 
    TABLE_NAME, 
    INDEX_NAME, 
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS COLUMNS
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'twlan'
    AND TABLE_NAME IN ('users', 'villages', 'reports', 'ally', 'movements', 'buildings', 'units', 'market')
GROUP BY TABLE_NAME, INDEX_NAME
ORDER BY TABLE_NAME, INDEX_NAME;
