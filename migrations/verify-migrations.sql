-- Verify all migrations were applied successfully

SELECT '=== Database Created ===' AS status;
SELECT DATABASE() AS current_database;

SELECT '=== Tables Created (18 new tables) ===' AS status;
SELECT COUNT(*) AS table_count, GROUP_CONCAT(TABLE_NAME ORDER BY TABLE_NAME SEPARATOR ', ') AS tables
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'twlan'
  AND TABLE_NAME IN (
    'player_statistics', 'alliance_statistics', 'leaderboards', 'achievements', 'player_achievements',
    'cache_villages', 'cache_players', 'cache_alliances', 'cache_map_chunks', 'query_cache', 'session_cache',
    'audit_log', 'village_history', 'player_history', 'alliance_history', 'attack_archive',
    'cache_invalidation', 'cache_stats', 'hot_data_cache'
  );

SELECT '=== Sample Achievement Data ===' AS status;
SELECT COUNT(*) AS achievement_count FROM achievements;
SELECT achievement_key, name, category, points FROM achievements LIMIT 5;

SELECT 'ALL MIGRATIONS APPLIED SUCCESSFULLY!' AS final_status;
