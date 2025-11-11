<?php
/**
 * API Response Handler
 * Standardized JSON response format
 */

class ApiResponse {
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
    }
    
    /**
     * Send success response
     */
    public function success($data, $message = 'Success', $code = 200) {
        $this->send([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => time(),
            'execution_time' => round((microtime(true) - $this->startTime) * 1000, 2) . 'ms'
        ], $code);
    }
    
    /**
     * Send error response
     */
    public function error($message, $code = 400, $details = null) {
        $this->send([
            'success' => false,
            'error' => $message,
            'details' => $details,
            'timestamp' => time(),
            'execution_time' => round((microtime(true) - $this->startTime) * 1000, 2) . 'ms'
        ], $code);
    }
    
    /**
     * Send paginated response
     */
    public function paginated($data, $total, $page, $perPage, $message = 'Success') {
        $this->send([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ],
            'timestamp' => time(),
            'execution_time' => round((microtime(true) - $this->startTime) * 1000, 2) . 'ms'
        ], 200);
    }
    
    /**
     * Send raw response
     */
    private function send($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
}
?>
