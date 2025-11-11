<?php
/**
 * Leaderboard API Endpoint
 * Returns rankings using our new leaderboard tables
 */

function handleLeaderboard($method, $action, $id, $response, $redis) {
    global $db;
    
    if ($method !== 'GET') {
        $response->error('Method not allowed', 405);
        return;
    }
    
    // Cache key
    $cacheKey = "leaderboard:{$action}";
    
    // Try cache first
    $cached = $redis->get($cacheKey);
    if ($cached !== null) {
        $response->success($cached, 'Leaderboard (cached)');
        return;
    }
    
    switch ($action) {
        case 'players':
            $type = $_GET['type'] ?? 'player_points';
            $limit = min((int)($_GET['limit'] ?? 100), 1000);
            
            $stmt = $db->prepare("
                SELECT 
                    entity_id as player_id,
                    entity_name as player_name,
                    primary_value as points,
                    rank_position as rank,
                    rank_change
                FROM leaderboards
                WHERE leaderboard_type = ?
                ORDER BY rank_position ASC
                LIMIT ?
            ");
            $stmt->bind_param('si', $type, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $leaderboard = [];
            while ($row = $result->fetch_assoc()) {
                $leaderboard[] = $row;
            }
            
            // Cache for 5 minutes
            $redis->set($cacheKey, $leaderboard, 300);
            
            $response->success($leaderboard, "Top {$limit} players");
            break;
            
        case 'alliances':
            $limit = min((int)($_GET['limit'] ?? 50), 500);
            
            $stmt = $db->prepare("
                SELECT 
                    entity_id as alliance_id,
                    entity_name as alliance_name,
                    primary_value as points,
                    secondary_value as member_count,
                    rank_position as rank,
                    rank_change
                FROM leaderboards
                WHERE leaderboard_type = 'alliance_points'
                ORDER BY rank_position ASC
                LIMIT ?
            ");
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $leaderboard = [];
            while ($row = $result->fetch_assoc()) {
                $leaderboard[] = $row;
            }
            
            // Cache for 5 minutes
            $redis->set($cacheKey, $leaderboard, 300);
            
            $response->success($leaderboard, "Top {$limit} alliances");
            break;
            
        default:
            $response->error('Invalid leaderboard type', 400);
    }
}
?>
