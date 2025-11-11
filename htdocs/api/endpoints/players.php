<?php
/**
 * Players API Endpoint
 */

function handlePlayers($method, $action, $id, $response, $redis) {
    global $db;
    
    if ($method !== 'GET') {
        $response->error('Method not allowed', 405);
        return;
    }
    
    $cacheKey = "players:{$action}:{$id}";
    $cached = $redis->get($cacheKey);
    if ($cached !== null) {
        $response->success($cached, 'Players (cached)');
        return;
    }
    
    switch ($action) {
        case 'list':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = min((int)($_GET['per_page'] ?? 50), 100);
            $offset = ($page - 1) * $perPage;
            
            $total = $db->query("SELECT COUNT(*) as count FROM cache_players")->fetch_assoc()['count'];
            
            $stmt = $db->prepare("
                SELECT 
                    player_id,
                    username,
                    alliance_tag,
                    total_points,
                    village_count,
                    rank,
                    is_online
                FROM cache_players
                ORDER BY rank ASC
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param('ii', $perPage, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $players = [];
            while ($row = $result->fetch_assoc()) {
                $players[] = $row;
            }
            
            $redis->set($cacheKey, ['players' => $players, 'total' => $total], 300);
            $response->paginated($players, $total, $page, $perPage, "Page {$page} of players");
            break;
            
        case 'view':
            if (!$id) {
                $response->error('Player ID required', 400);
                return;
            }
            
            $stmt = $db->prepare("
                SELECT 
                    player_id,
                    username,
                    alliance_id,
                    alliance_tag,
                    total_points,
                    attack_points,
                    defense_points,
                    village_count,
                    rank,
                    attack_rank,
                    defense_rank,
                    last_activity,
                    is_online
                FROM cache_players
                WHERE player_id = ?
            ");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($player = $result->fetch_assoc()) {
                $redis->set($cacheKey, $player, 300);
                $response->success($player);
            } else {
                $response->error('Player not found', 404);
            }
            break;
            
        default:
            $response->error('Invalid action', 400);
    }
}
?>
