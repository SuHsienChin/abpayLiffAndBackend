<?php
require_once 'RedisSimulator.php';

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
            // 檢查 Redis 擴展是否可用
            if (!extension_loaded('redis')) {
                error_log("Redis擴展未安裝，使用模擬模式");
                $this->redis = new RedisSimulator();
                return $this->redis;
            }
            
            $this->redis = new Redis();
            $this->redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
            
            if (!empty($_ENV['REDIS_PASSWORD'])) {
                $this->redis->auth($_ENV['REDIS_PASSWORD']);
            }
            
            return $this->redis;
        } catch (Exception $e) {
            error_log("Redis連接失敗: " . $e->getMessage());
            // 使用模擬器作為後備
            $this->redis = new RedisSimulator();
            return $this->redis;
        }
    }
    
    public function getRedis() {
        return $this->redis;
    }
    
    /**
     * 檢查 Redis 連接狀態
     * @return array 連接狀態信息
     */
    public function getConnectionStatus() {
        $status = [
            'connected' => false,
            'type' => 'unknown',
            'host' => $_ENV['REDIS_HOST'] ?? 'unknown',
            'port' => $_ENV['REDIS_PORT'] ?? 'unknown',
            'error' => null
        ];
        
        try {
            if ($this->redis instanceof RedisSimulator) {
                $status['connected'] = true;
                $status['type'] = 'simulator';
                $status['data_dir'] = $this->redis->getDataDir();
            } else if ($this->redis instanceof Redis) {
                $status['connected'] = $this->redis->ping() ? true : false;
                $status['type'] = 'redis';
                $status['version'] = $this->redis->info('server')['redis_version'] ?? 'unknown';
            }
        } catch (Exception $e) {
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
    
    public function get($key) {
        try {
            return $this->redis->get($key);
        } catch (Exception $e) {
            error_log("Redis獲取數據失敗: " . $e->getMessage());
            return null;
        }
    }
    
    public function set($key, $value, $options = 60) {
        try {
            // 原生 Redis 擴展可直接接受 array 選項（NX/XX/EX/PX）
            if ($this->redis instanceof Redis) {
                // 為避免不同版本行為差異，若為整數直接轉為 EX 選項
                if (is_int($options)) {
                    $options = ['EX' => $options];
                }
                return $this->redis->set($key, $value, $options);
            }

            // 模擬器環境下，手動解析選項
            $ttlSeconds = null;
            $useNx = false;
            $useXx = false;

            if (is_array($options)) {
                // 可能的形式：['NX', 'EX' => 10] 或 ['EX' => 10] 等
                $upperKeys = array_change_key_case($options, CASE_UPPER);
                if (isset($upperKeys['EX'])) {
                    $ttlSeconds = (int)$upperKeys['EX'];
                } elseif (isset($upperKeys['PX'])) {
                    $ttlMs = (int)$upperKeys['PX'];
                    $ttlSeconds = (int)ceil($ttlMs / 1000);
                }
                $useNx = in_array('NX', $upperKeys, true) || (isset($upperKeys['NX']) && $upperKeys['NX']);
                $useXx = in_array('XX', $upperKeys, true) || (isset($upperKeys['XX']) && $upperKeys['XX']);
            } elseif (is_int($options)) {
                $ttlSeconds = $options;
            } elseif ($options === null) {
                $ttlSeconds = null;
            }

            // NX: 僅當不存在時設置；XX: 僅當存在時設置
            $exists = (bool)$this->redis->exists($key);
            if ($useNx && $exists) {
                return false;
            }
            if ($useXx && !$exists) {
                return false;
            }

            return $this->redis->set($key, $value, $ttlSeconds);
        } catch (Exception $e) {
            error_log("Redis設置數據失敗: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 取得鍵的剩餘壽命（秒）
     * @param string $key
     * @return int 剩餘秒數；-1 無過期；-2 不存在或錯誤
     */
    public function ttl($key) {
        try {
            if ($this->redis instanceof Redis) {
                return (int)$this->redis->ttl($key);
            }
            // 模擬器：讀取 expire 檔
            if ($this->redis instanceof RedisSimulator) {
                return $this->redis->ttl($key);
            }
        } catch (Exception $e) {
            error_log("Redis獲取TTL失敗: " . $e->getMessage());
        }
        return -2;
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