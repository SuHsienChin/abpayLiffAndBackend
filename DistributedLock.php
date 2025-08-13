<?php
require_once 'RedisConnection.php';

/**
 * 分散式鎖類別
 * 使用 Redis 實現，防止快取雪崩問題
 */
class DistributedLock {
    
    /**
     * 嘗試獲取分散式鎖
     * @param string $lockKey - 鎖的鍵名
     * @param int $timeout - 鎖的超時時間（秒）
     * @return bool - 是否成功獲取鎖
     */
    public static function acquireLock($lockKey, $timeout = 10) {
        try {
            $redis = RedisConnection::getInstance();
            
            // 使用 SET key value NX EX timeout 原子操作獲取鎖
            // NX: 只有當 key 不存在時才設置
            // EX: 設置過期時間（秒）
            $result = $redis->set($lockKey, 'locked', ['NX', 'EX' => $timeout]);
            
            return $result === true;
            
        } catch (Exception $e) {
            error_log("獲取分散式鎖失敗: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 釋放分散式鎖
     * @param string $lockKey - 鎖的鍵名
     * @return bool - 是否成功釋放鎖
     */
    public static function releaseLock($lockKey) {
        try {
            $redis = RedisConnection::getInstance();
            
            // 刪除鎖鍵
	            $result = $redis->delete($lockKey);
            
            return $result > 0;
            
        } catch (Exception $e) {
            error_log("釋放分散式鎖失敗: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 等待其他進程更新快取
     * @param string $cacheKey - 快取鍵名
     * @param int $maxWaitTime - 最大等待時間（秒）
     * @param int $checkInterval - 檢查間隔（毫秒）
     * @return string|false - 快取數據或 false（超時）
     */
    public static function waitForCache($cacheKey, $maxWaitTime = 5, $checkInterval = 200) {
        try {
            $redis = RedisConnection::getInstance();
            $startTime = time();
            $checkIntervalSeconds = $checkInterval / 1000; // 轉換為秒
            
            while (time() - $startTime < $maxWaitTime) {
                // 檢查快取是否存在
                $cachedData = $redis->get($cacheKey);
                if ($cachedData !== false) {
                    return $cachedData;
                }
                
                // 等待一段時間後再檢查
                usleep($checkInterval * 1000); // 轉換為微秒
            }
            
            // 超時
            return false;
            
        } catch (Exception $e) {
            error_log("等待快取更新失敗: " . $e->getMessage());
            return false;
        }
    }
}
