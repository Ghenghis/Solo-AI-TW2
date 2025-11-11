<?php
/**
 * Achievements API Endpoint
 * Returns achievement data from our new achievements tables
 */

function handleAchievements($method, $action, $id, $response, $redis) {
    global $db;
    
    if ($method !== 'GET') {
        $response->error('Method not allowed', 405);
        return;
    }
    
    // Cache key
    $cacheKey = "achievements:{$action}:{$id}";
    
    // Try cache first
    $cached = $redis->get($cacheKey);
    if ($cached !== null) {
        $response->success($cached, 'Achievements (cached)');
        return;
    }
    
    switch ($action) {
        case 'list':
            // Get all achievements
            $result = $db->query("
                SELECT 
                    id,
                    achievement_key,
                    name,
                    description,
                    icon,
                    category,
                    points,
                    is_hidden
                FROM achievements
                WHERE is_hidden = 0
                ORDER BY category, points ASC
            ");
            
            $achievements = [];
            while ($row = $result->fetch_assoc()) {
                $achievements[] = $row;
            }
            
            // Cache for 1 hour (achievements don't change often)
            $redis->set($cacheKey, $achievements, 3600);
            
            $response->success($achievements, count($achievements) . ' achievements available');
            break;
            
        case 'player':
            // Get player achievements
            if (!$id) {
                $response->error('Player ID required', 400);
                return;
            }
            
            $stmt = $db->prepare("
                SELECT 
                    a.id,
                    a.achievement_key,
                    a.name,
                    a.description,
                    a.icon,
                    a.category,
                    a.points,
                    pa.earned_at,
                    pa.progress
                FROM achievements a
                INNER JOIN player_achievements pa ON a.id = pa.achievement_id
                WHERE pa.player_id = ?
                ORDER BY pa.earned_at DESC
            ");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $achievements = [];
            while ($row = $result->fetch_assoc()) {
                $achievements[] = $row;
            }
            
            // Cache for 5 minutes
            $redis->set($cacheKey, $achievements, 300);
            
            $response->success($achievements, count($achievements) . ' achievements earned');
            break;
            
        default:
            $response->error('Invalid action', 400);
    }
}
?>
