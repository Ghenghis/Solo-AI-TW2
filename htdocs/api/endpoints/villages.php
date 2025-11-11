<?php
/**
 * Villages API Endpoint
 */

function handleVillages($method, $action, $id, $response, $redis) {
    global $db;
    
    if ($method !== 'GET') {
        $response->error('Method not allowed', 405);
        return;
    }
    
    $cacheKey = "villages:{$action}:{$id}";
    $cached = $redis->get($cacheKey);
    if ($cached !== null) {
        $response->success($cached, 'Villages (cached)');
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
                    village_id,
                    x,
                    y,
                    name,
                    points,
                    continent
                FROM cache_villages
                WHERE player_id = ?
                ORDER BY points DESC
            ");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $villages = [];
            while ($row = $result->fetch_assoc()) {
                $villages[] = $row;
            }
            
            $redis->set($cacheKey, $villages, 300);
            $response->success($villages, count($villages) . ' villages');
            break;
            
        case 'map':
            $x = (int)($_GET['x'] ?? 500);
            $y = (int)($_GET['y'] ?? 500);
            $radius = min((int)($_GET['radius'] ?? 10), 50);
            
            $stmt = $db->prepare("
                SELECT 
                    village_id,
                    x,
                    y,
                    name,
                    player_name,
                    alliance_tag,
                    points
                FROM cache_villages
                WHERE x BETWEEN ? AND ?
                  AND y BETWEEN ? AND ?
                ORDER BY x, y
            ");
            $x1 = $x - $radius;
            $x2 = $x + $radius;
            $y1 = $y - $radius;
            $y2 = $y + $radius;
            $stmt->bind_param('iiii', $x1, $x2, $y1, $y2);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $villages = [];
            while ($row = $result->fetch_assoc()) {
                $villages[] = $row;
            }
            
            $redis->set($cacheKey, $villages, 600);
            $response->success($villages, count($villages) . ' villages in area');
            break;
            
        default:
            $response->error('Invalid action', 400);
    }
}
?>
