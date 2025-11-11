-- ============================================
-- AI Memory System Tables
-- Learning & Adaptation for TWLan AI Bots
-- ============================================

USE twlan;

-- AI Relations Memory
-- Tracks how bots feel about other players over time
CREATE TABLE IF NOT EXISTS ai_relations (
    bot_player_id INT NOT NULL,
    other_player_id INT NOT NULL,
    score FLOAT DEFAULT 0 COMMENT 'Relation score: -100 (enemy) to +100 (close ally)',
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (bot_player_id, other_player_id),
    INDEX idx_bot_player (bot_player_id),
    INDEX idx_score (bot_player_id, score DESC),
    FOREIGN KEY (bot_player_id) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (other_player_id) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI learning: relations between bots and players';

-- AI Target Statistics
-- Learns which villages are good/bad farms
CREATE TABLE IF NOT EXISTS ai_target_stats (
    bot_player_id INT NOT NULL,
    target_village_id INT NOT NULL,
    attacks INT DEFAULT 0 COMMENT 'Total attacks sent',
    successful_attacks INT DEFAULT 0 COMMENT 'Attacks that succeeded',
    total_loot BIGINT DEFAULT 0 COMMENT 'Total resources looted',
    total_losses BIGINT DEFAULT 0 COMMENT 'Total unit cost lost',
    avg_payoff FLOAT DEFAULT 0 COMMENT 'Exponential moving average of (loot - losses)',
    last_attack TIMESTAMP NULL COMMENT 'Last time this target was attacked',
    PRIMARY KEY (bot_player_id, target_village_id),
    INDEX idx_bot_payoff (bot_player_id, avg_payoff DESC),
    INDEX idx_last_attack (bot_player_id, last_attack DESC),
    FOREIGN KEY (bot_player_id) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (target_village_id) REFERENCES villages(id_village) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI learning: farm target performance';

-- AI Strategy Statistics
-- Learns which strategies work in this world
CREATE TABLE IF NOT EXISTS ai_strategy_stats (
    bot_player_id INT NOT NULL,
    strategy_key VARCHAR(64) NOT NULL COMMENT 'Strategy identifier (e.g. eco_first, rush_offense)',
    uses INT DEFAULT 0 COMMENT 'Times this strategy was used',
    success_score FLOAT DEFAULT 0 COMMENT 'EMA of success: villages gained, points, K/D, survival',
    last_use TIMESTAMP NULL COMMENT 'Last time strategy was selected',
    PRIMARY KEY (bot_player_id, strategy_key),
    INDEX idx_bot_success (bot_player_id, success_score DESC),
    FOREIGN KEY (bot_player_id) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI learning: strategy performance';

-- AI Event Log (optional, for debugging)
CREATE TABLE IF NOT EXISTS ai_event_log (
    id_event BIGINT AUTO_INCREMENT PRIMARY KEY,
    bot_player_id INT NOT NULL,
    event_type VARCHAR(32) NOT NULL COMMENT 'Event type: attack, support, trade, etc.',
    other_player_id INT NULL COMMENT 'Other player involved',
    village_id INT NULL COMMENT 'Village involved',
    details JSON NULL COMMENT 'Event-specific data',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bot_time (bot_player_id, timestamp DESC),
    INDEX idx_event_type (event_type, timestamp DESC),
    FOREIGN KEY (bot_player_id) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI learning: event history for debugging';

-- ============================================
-- Indexes for Performance
-- ============================================

-- Optimize relation lookups
CREATE INDEX idx_relations_positive ON ai_relations(bot_player_id, score DESC) 
    WHERE score > 40;
    
CREATE INDEX idx_relations_negative ON ai_relations(bot_player_id, score ASC) 
    WHERE score < -20;

-- Optimize target selection
CREATE INDEX idx_targets_profitable ON ai_target_stats(bot_player_id, avg_payoff DESC, last_attack DESC)
    WHERE avg_payoff > 0;

-- ============================================
-- Initial Data (Optional)
-- ============================================

-- Pre-populate some common strategies
INSERT IGNORE INTO ai_strategy_stats (bot_player_id, strategy_key, uses, success_score)
SELECT id_user, 'eco_first', 0, 50
FROM users WHERE is_bot = 1;

INSERT IGNORE INTO ai_strategy_stats (bot_player_id, strategy_key, uses, success_score)
SELECT id_user, 'rush_offense', 0, 50
FROM users WHERE is_bot = 1;

INSERT IGNORE INTO ai_strategy_stats (bot_player_id, strategy_key, uses, success_score)
SELECT id_user, 'balanced_mid', 0, 50
FROM users WHERE is_bot = 1;

INSERT IGNORE INTO ai_strategy_stats (bot_player_id, strategy_key, uses, success_score)
SELECT id_user, 'heavy_defense', 0, 50
FROM users WHERE is_bot = 1;

INSERT IGNORE INTO ai_strategy_stats (bot_player_id, strategy_key, uses, success_score)
SELECT id_user, 'tribe_focus', 0, 50
FROM users WHERE is_bot = 1;

-- ============================================
-- Statistics & Monitoring Views
-- ============================================

-- View: Bot learning summary
CREATE OR REPLACE VIEW v_ai_learning_summary AS
SELECT 
    u.id_user,
    u.username,
    COUNT(DISTINCT ar.other_player_id) as known_players,
    COUNT(DISTINCT ats.target_village_id) as known_targets,
    AVG(ats.avg_payoff) as avg_farm_payoff,
    COUNT(DISTINCT ass.strategy_key) as strategies_tried,
    MAX(ass.success_score) as best_strategy_score
FROM users u
LEFT JOIN ai_relations ar ON u.id_user = ar.bot_player_id
LEFT JOIN ai_target_stats ats ON u.id_user = ats.bot_player_id
LEFT JOIN ai_strategy_stats ass ON u.id_user = ass.bot_player_id
WHERE u.is_bot = 1
GROUP BY u.id_user, u.username;

-- View: Top performing farms per bot
CREATE OR REPLACE VIEW v_ai_best_farms AS
SELECT 
    u.username as bot_name,
    v.name as target_name,
    v.x, v.y,
    ats.attacks,
    ats.successful_attacks,
    ats.avg_payoff,
    ats.last_attack
FROM ai_target_stats ats
JOIN users u ON ats.bot_player_id = u.id_user
JOIN villages v ON ats.target_village_id = v.id_village
WHERE ats.avg_payoff > 0
ORDER BY ats.bot_player_id, ats.avg_payoff DESC;

-- View: Bot alliances and enemies
CREATE OR REPLACE VIEW v_ai_relations_summary AS
SELECT 
    u.username as bot_name,
    COUNT(CASE WHEN ar.score >= 40 THEN 1 END) as allies,
    COUNT(CASE WHEN ar.score BETWEEN -10 AND 40 THEN 1 END) as neutral,
    COUNT(CASE WHEN ar.score <= -10 THEN 1 END) as enemies,
    AVG(ar.score) as avg_relation
FROM users u
LEFT JOIN ai_relations ar ON u.id_user = ar.bot_player_id
WHERE u.is_bot = 1
GROUP BY u.id_user, u.username;

-- ============================================
-- Cleanup Procedures
-- ============================================

-- Remove stale target data (targets that no longer exist or are very old)
DELIMITER //
CREATE PROCEDURE cleanup_ai_memory()
BEGIN
    -- Remove targets for deleted villages
    DELETE ats FROM ai_target_stats ats
    LEFT JOIN villages v ON ats.target_village_id = v.id_village
    WHERE v.id_village IS NULL;
    
    -- Remove relations to deleted players
    DELETE ar FROM ai_relations ar
    LEFT JOIN users u ON ar.other_player_id = u.id_user
    WHERE u.id_user IS NULL;
    
    -- Archive old event logs (keep last 7 days)
    DELETE FROM ai_event_log
    WHERE timestamp < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
    SELECT 'AI memory cleanup complete' as status;
END//
DELIMITER ;

-- ============================================
-- Verification
-- ============================================

-- Show created tables
SELECT 
    table_name, 
    table_rows, 
    ROUND(data_length/1024/1024, 2) as size_mb
FROM information_schema.tables
WHERE table_schema = 'twlan'
AND table_name LIKE 'ai_%'
ORDER BY table_name;

-- Show indexes
SELECT 
    table_name,
    index_name,
    GROUP_CONCAT(column_name ORDER BY seq_in_index) as columns
FROM information_schema.statistics
WHERE table_schema = 'twlan'
AND table_name LIKE 'ai_%'
GROUP BY table_name, index_name
ORDER BY table_name, index_name;

SELECT '✅ AI Memory System tables created successfully!' as status;
