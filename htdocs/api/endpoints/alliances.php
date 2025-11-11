<?php
/**
 * Alliances API Endpoint
 */

function handleAlliances($method, $action, $id, $response, $redis) {
    global $db;
    
    if ($method !== 'GET') {
        $response->error('Method not allowed', 405);
        return;
    }
    
    $cacheKey = "alliances:{$action}:{$id}";
    $cached = $redis->get($cacheKey);
    if ($cached !== null) {
        $response->success($cached, 'Alliances (cached)');
        return;
    }
    
    switch ($action) {
        case 'list':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = min((int)($_GET['per_page'] ?? 50), 100);
            $offset = ($page - 1) * $perPage;
            
            $total = $db->query("SELECT COUNT(*) as count FROM cache_alliances")->fetch_assoc()['count'];
            
            $stmt = $db->prepare("
                SELECT 
                    alliance_id,
                    tag,
                    name,
                    total_points,
                    member_count,
                    village_count,
                    average_points_per_member,
                    rank
                FROM cache_alliances
                ORDER BY rank ASC
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param('ii', $perPage, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $alliances = [];
            while ($row = $result->fetch_assoc()) {
                $alliances[] = $row;
            }
            
            $redis->set($cacheKey, ['alliances' => $alliances, 'total' => $total], 300);
            $response->paginated($alliances, $total, $page, $perPage);
            break;
            
        case 'view':
            if (!$id) {
                $response->error('Alliance ID required', 400);
                return;
            }
            
            $stmt = $db->prepare("
                SELECT 
                    alliance_id,
                    tag,
                    name,
                    total_points,
                    member_count,
                    village_count,
                    average_points_per_member,
                    rank
                FROM cache_alliances
                WHERE alliance_id = ?
            ");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($alliance = $result->fetch_assoc()) {
                $redis->set($cacheKey, $alliance, 300);
                $response->success($alliance);
            } else {
                $response->error('Alliance not found', 404);
            }
            break;
            
        default:
            $response->error('Invalid action', 400);
    }
}
?>
