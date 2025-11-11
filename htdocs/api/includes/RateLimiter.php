<?php
/**
 * Rate Limiter
 * Prevents API abuse with rate limiting
 */

class RateLimiter {
    private $redis;
    private $maxRequests = 100; // requests per window
    private $window = 60; // seconds
    
    public function __construct($redis) {
        $this->redis = $redis;
    }
    
    /**
     * Check if request is allowed
     */
    public function check($clientIP) {
        $key = 'ratelimit:' . $clientIP;
        
        // If Redis not available, allow request
        if (!$this->redis->isConnected()) {
            return true;
        }
        
        $requests = $this->redis->get($key);
        
        if ($requests === null) {
            // First request in window
            $this->redis->set($key, 1, $this->window);
            return true;
        }
        
        if ($requests >= $this->maxRequests) {
            // Rate limit exceeded
            return false;
        }
        
        // Increment counter
        $this->redis->increment($key);
        return true;
    }
    
    /**
     * Get remaining requests
     */
    public function getRemaining($clientIP) {
        $key = 'ratelimit:' . $clientIP;
        $requests = $this->redis->get($key) ?? 0;
        return max(0, $this->maxRequests - $requests);
    }
}
?>
