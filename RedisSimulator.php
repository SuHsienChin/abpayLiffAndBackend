<?php

/**
 * Redis 模擬器類
 * 當 Redis 擴展不可用時，提供基本的 Redis 功能模擬
 * 使用文件系統來存儲數據
 */
class RedisSimulator {
    private $dataDir;
    
    public function __construct() {
        $this->dataDir = __DIR__ . '/redis_data';
        
        // 確保數據目錄存在
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
        
        error_log("使用 RedisSimulator 作為 Redis 的替代方案");
    }
    
    /**
     * 獲取鍵的值
     */
    public function get($key) {
        $filePath = $this->getFilePath($key);
        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        }
        return false;
    }
    
    /**
     * 設置鍵的值
     */
    public function set($key, $value, $ttl = null) {
        $filePath = $this->getFilePath($key);
        file_put_contents($filePath, $value);
        
        // 如果設置了 TTL，記錄過期時間
        if ($ttl !== null) {
            $expirePath = $this->getExpirePath($key);
            $expireTime = time() + $ttl;
            file_put_contents($expirePath, $expireTime);
        }
        
        return true;
    }
    
    /**
     * 刪除鍵
     */
    public function del($key) {
        $filePath = $this->getFilePath($key);
        $expirePath = $this->getExpirePath($key);
        
        $result = 0;
        if (file_exists($filePath)) {
            unlink($filePath);
            $result = 1;
        }
        
        if (file_exists($expirePath)) {
            unlink($expirePath);
        }
        
        return $result;
    }
    
    /**
     * 檢查鍵是否存在
     */
    public function exists($key) {
        $filePath = $this->getFilePath($key);
        return file_exists($filePath) ? 1 : 0;
    }
    
    /**
     * 將值推入列表右側
     */
    public function rPush($key, $value) {
        $list = $this->getList($key);
        $list[] = $value;
        $this->saveList($key, $list);
        return count($list);
    }
    
    /**
     * 從列表左側彈出值
     */
    public function lPop($key) {
        $list = $this->getList($key);
        if (empty($list)) {
            return null;
        }
        
        $value = array_shift($list);
        $this->saveList($key, $list);
        return $value;
    }
    
    /**
     * 獲取列表長度
     */
    public function lLen($key) {
        $list = $this->getList($key);
        return count($list);
    }
    
    /**
     * 驗證密碼（模擬方法，始終返回 true）
     */
    public function auth($password) {
        return true;
    }
    
    /**
     * 獲取存儲文件路徑
     */
    private function getFilePath($key) {
        return $this->dataDir . '/' . md5($key) . '.data';
    }
    
    /**
     * 獲取過期時間文件路徑
     */
    private function getExpirePath($key) {
        return $this->dataDir . '/' . md5($key) . '.expire';
    }
    
    /**
     * 獲取列表文件路徑
     */
    private function getListPath($key) {
        return $this->dataDir . '/' . md5($key) . '.list';
    }
    
    /**
     * 獲取列表數據
     */
    private function getList($key) {
        $listPath = $this->getListPath($key);
        if (file_exists($listPath)) {
            $data = file_get_contents($listPath);
            return json_decode($data, true) ?: [];
        }
        return [];
    }
    
    /**
     * 保存列表數據
     */
    private function saveList($key, $list) {
        $listPath = $this->getListPath($key);
        file_put_contents($listPath, json_encode($list));
    }
}