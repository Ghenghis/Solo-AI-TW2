<?php
/**
 * Database Configuration
 * Connection to MariaDB
 */

// Database credentials
define('DB_HOST', getenv('DB_HOST') ?: 'twlan-db');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'twlan');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'twlan_root_2025');

// Create connection
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($db->connect_error) {
        throw new Exception('Database connection failed: ' . $db->connect_error);
    }
    
    // Set charset
    $db->set_charset('utf8mb4');
    
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => $e->getMessage()
    ]));
}
?>
