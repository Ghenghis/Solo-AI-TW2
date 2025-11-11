<?php
/**
 * Redis Cache Handler
 * Manages Redis caching with fallback
 */

class RedisCache {
    private $redis;
    private $connected = false;
    private $prefix = 'twlan:';
    
    public function __construct() {
        $this->connect();
    }
    
    /**
     * Connect to Redis
     */
    private function connect() {
        try {
            if (class_exists('Redis')) {
                $this->redis = new Redis();
                $this->redis->connect(
                    getenv('REDIS_HOST') ?: 'twlan-redis',
                    getenv('REDIS_PORT') ?: 6379
                );
                $this->connected = true;
            }
        } catch (Exception $e) {
            error_log('Redis connection failed: ' . $e->getMessage());
            $this->connected = false;
        }
    }
    
    /**
     * Get cached value
     */
    public function get($key) {
        if (!$this->connected) {
            return null;
        }
        
        try {
            $value = $this->redis->get($this->prefix . $key);
            return $value !== false ? json_decode($value, true) : null;
        } catch (Exception $e) {
            error_log('Redis get error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Set cached value
     */
    public function set($key, $value, $ttl = 300) {
        if (!$this->connected) {
            return false;
        }
        
        try {
            return $this->redis->setex(
                $this->prefix . $key,
                $ttl,
                json_encode($value)
            );
        } catch (Exception $e) {
            error_log('Redis set error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete cached value
     */
    public function delete($key) {
        if (!$this->connected) {
            return false;
        }
        
        try {
            return $this->redis->del($this->prefix . $key);
        } catch (Exception $e) {
            error_log('Redis delete error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if Redis is connected
     */
    public function isConnected() {
        return $this->connected;
    }
    
    /**
     * Increment counter
     */
    public function increment($key, $amount = 1) {
        if (!$this->connected) {
            return false;
        }
        
        try {
            return $this->redis->incrBy($this->prefix . $key, $amount);
        } catch (Exception $e) {
            error_log('Redis increment error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set expiration
     */
    public function expire($key, $ttl) {
        if (!$this->connected) {
            return false;
        }
        
        try {
            return $this->redis->expire($this->prefix . $key, $ttl);
        } catch (Exception $e) {
            error_log('Redis expire error: ' . $e->getMessage());
            return false;
        }
    }
}
?>
