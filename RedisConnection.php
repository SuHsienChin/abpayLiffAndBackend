<?php

class RedisConnection {
    private $redis;
    private static $instance = null;
    
    private function __construct() {
        $this->loadEnv();
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadEnv() {
        $envPath = __DIR__ . '/.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;  // 跳過註釋行
                }
                list($key, $value) = explode('=', $line, 2);
                $_ENV[$key] = $value;
            }
        }
        
        // 設置默認值
        $_ENV['REDIS_HOST'] = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
        $_ENV['REDIS_PORT'] = $_ENV['REDIS_PORT'] ?? '6379';
        $_ENV['REDIS_PASSWORD'] = $_ENV['REDIS_PASSWORD'] ?? null;
    }
    
    private function connect() {
        try {
            $this->redis = new Redis();
            $this->redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
            
            if (!empty($_ENV['REDIS_PASSWORD'])) {
                $this->redis->auth($_ENV['REDIS_PASSWORD']);
            }
            
            return $this->redis;
        } catch (Exception $e) {
            error_log("Redis連接失敗: " . $e->getMessage());
            return null;
        }
    }
    
    public function getRedis() {
        return $this->redis;
    }
    
    public function get($key) {
        try {
            return $this->redis->get($key);
        } catch (Exception $e) {
            error_log("Redis獲取數據失敗: " . $e->getMessage());
            return null;
        }
    }
    
    public function set($key, $value, $ttl = 3600) {
        try {
            return $this->redis->set($key, $value, $ttl);
        } catch (Exception $e) {
            error_log("Redis設置數據失敗: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($key) {
        try {
            return $this->redis->del($key);
        } catch (Exception $e) {
            error_log("Redis刪除數據失敗: " . $e->getMessage());
            return false;
        }
    }
    
    public function exists($key) {
        try {
            return $this->redis->exists($key);
        } catch (Exception $e) {
            error_log("Redis檢查鍵是否存在失敗: " . $e->getMessage());
            return false;
        }
    }
}