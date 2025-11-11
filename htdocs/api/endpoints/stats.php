<?php
/**
 * Statistics API Endpoint
 * Returns game statistics from our new statistics tables
 */

function handleStats($method, $action, $id, $response, $redis) {
    global $db;
    
    if ($method !== 'GET') {
        $response->error('Method not allowed', 405);
        return;
    }
    
    $cacheKey = "stats:{$action}:{$id}";
    $cached = $redis->get($cacheKey);
    if ($cached !== null) {
        $response->success($cached, 'Statistics (cached)');
        return;
    }
    
    switch ($action) {
        case 'player':
            if (!$id) {
                $response->error('Player ID required', 400);
                return;
            }
            
            $stmt = $db->prepare("
                SELECT 
                    stat_date,
                    attacks_sent,
                    attacks_won,
                    defenses_won,
                    villages_conquered,
                    total_points,
                    attack_points,
                    defense_points,
                    rank_position
                FROM player_statistics
                WHERE player_id = ?
                ORDER BY stat_date DESC
                LIMIT 30
            ");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $stats = [];
            while ($row = $result->fetch_assoc()) {
                $stats[] = $row;
            }
            
            $redis->set($cacheKey, $stats, 600);
            $response->success($stats, 'Player statistics (30 days)');
            break;
            
        case 'alliance':
            if (!$id) {
                $response->error('Alliance ID required', 400);
                return;
            }
            
            $stmt = $db->prepare("
                SELECT 
                    stat_date,
                    member_count,
                    active_members,
                    total_attacks,
                    total_conquers,
                    total_villages,
                    total_points,
                    rank_position
                FROM alliance_statistics
                WHERE alliance_id = ?
                ORDER BY stat_date DESC
                LIMIT 30
            ");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $stats = [];
            while ($row = $result->fetch_assoc()) {
                $stats[] = $row;
            }
            
            $redis->set($cacheKey, $stats, 600);
            $response->success($stats, 'Alliance statistics (30 days)');
            break;
            
        case 'global':
            // Global game statistics
            $result = $db->query("
                SELECT 
                    (SELECT COUNT(*) FROM cache_players) as total_players,
                    (SELECT COUNT(*) FROM cache_villages) as total_villages,
                    (SELECT COUNT(*) FROM cache_alliances) as total_alliances,
                    (SELECT SUM(primary_value) FROM leaderboards WHERE leaderboard_type = 'player_points') as total_points
            ");
            
            $stats = $result->fetch_assoc();
            $redis->set($cacheKey, $stats, 300);
            $response->success($stats, 'Global statistics');
            break;
            
        default:
            $response->error('Invalid action', 400);
    }
}
?>
