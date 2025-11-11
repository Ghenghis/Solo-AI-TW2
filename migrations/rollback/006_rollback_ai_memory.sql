-- =============================================
-- Rollback Script for AI Memory Tables
-- Migration: 006_ai_memory_tables.sql
-- =============================================

-- Drop views first (dependent on tables)
DROP VIEW IF EXISTS ai_recent_battles;

-- Drop procedures
DROP PROCEDURE IF EXISTS cleanup_old_ai_events;

-- Drop tables (reverse order of creation)
DROP TABLE IF EXISTS ai_event_log;
DROP TABLE IF EXISTS ai_strategy_stats;
DROP TABLE IF EXISTS ai_target_stats;
DROP TABLE IF EXISTS ai_relations;

-- Remove schema version if tracking
-- DELETE FROM schema_migrations WHERE version = 6;

-- Done
SELECT 'AI Memory tables rolled back successfully' AS status;
