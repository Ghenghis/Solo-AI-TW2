<?php
/**
 * TWLan REST API v1.0
 * Modern REST API for TWLan 2.A3
 * 
 * Features:
 * - RESTful endpoints
 * - JSON responses
 * - Rate limiting
 * - Redis caching
 * - Authentication
 * - API documentation
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// CORS headers for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include configuration
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/Redis.php';
require_once __DIR__ . '/includes/RateLimiter.php';
require_once __DIR__ . '/includes/ApiResponse.php';

// Initialize
$redis = new RedisCache();
$rateLimiter = new RateLimiter($redis);
$response = new ApiResponse();

// Rate limiting
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!$rateLimiter->check($clientIP)) {
    $response->error('Rate limit exceeded', 429);
    exit();
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api/', '', $path);
$parts = explode('/', trim($path, '/'));

// Route the request
$endpoint = $parts[0] ?? 'index';
$action = $parts[1] ?? 'list';
$id = $parts[2] ?? null;

try {
    switch ($endpoint) {
        case 'players':
            require_once __DIR__ . '/endpoints/players.php';
            handlePlayers($method, $action, $id, $response, $redis);
            break;
            
        case 'villages':
            require_once __DIR__ . '/endpoints/villages.php';
            handleVillages($method, $action, $id, $response, $redis);
            break;
            
        case 'alliances':
            require_once __DIR__ . '/endpoints/alliances.php';
            handleAlliances($method, $action, $id, $response, $redis);
            break;
            
        case 'leaderboard':
            require_once __DIR__ . '/endpoints/leaderboard.php';
            handleLeaderboard($method, $action, $id, $response, $redis);
            break;
            
        case 'stats':
            require_once __DIR__ . '/endpoints/stats.php';
            handleStats($method, $action, $id, $response, $redis);
            break;
            
        case 'achievements':
            require_once __DIR__ . '/endpoints/achievements.php';
            handleAchievements($method, $action, $id, $response, $redis);
            break;
            
        case 'index':
        case '':
            // API documentation
            $response->success([
                'name' => 'TWLan REST API',
                'version' => '1.0',
                'endpoints' => [
                    '/api/players' => 'Player management',
                    '/api/villages' => 'Village information',
                    '/api/alliances' => 'Alliance data',
                    '/api/leaderboard' => 'Rankings and leaderboards',
                    '/api/stats' => 'Game statistics',
                    '/api/achievements' => 'Achievement system'
                ],
                'documentation' => '/api/docs'
            ]);
            break;
            
        default:
            $response->error('Endpoint not found', 404);
    }
} catch (Exception $e) {
    $response->error($e->getMessage(), 500);
}
?>
