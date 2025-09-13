<?php
/**
 * 自動更新匯率快取的 cron 腳本
 * 每 10 秒執行一次，從外部 API 獲取最新匯率資料並更新到 Redis
 * 使用 JSDoc 註解
 */

require_once 'getApiJsonClass.php';
require_once 'RedisConnection.php';
require_once 'CacheConfig.php';
require_once 'ApiLogger.php';

try {
    // 設定快取鍵和過期時間
    $cacheKey = 'rate_cache';
    $cacheTTL = CACHE_TTL_RATE;
    $url = 'http://www.adp.idv.tw/api/Rate';
    
    // 記錄開始時間
    $startTime = microtime(true);
    
    // 從 API 獲取最新資料
    $curlRequest = new CurlRequest($url);
    $response = $curlRequest->sendRequest();
    
    $data = json_decode($response, true);
    
    if ($data === null) {
        // 記錄失敗的 API 請求
        ApiLogger::logApiRequest('update_rate_cache.php', $url, [], '', false, 'api');
        throw new Exception("無法取得 API 資料");
    }
    
    // 連接到 Redis 並更新快取
    $redis = RedisConnection::getInstance();
    $redis->set($cacheKey, $response, $cacheTTL);
    
    // 計算執行時間
    $executionTime = round((microtime(true) - $startTime) * 1000, 2);
    
    // 記錄成功的 API 請求
    ApiLogger::logApiRequest('update_rate_cache.php', $url, [], $response, true, 'api', $executionTime);
    
    // 輸出成功訊息（用於 cron 日誌）
    echo date('Y-m-d H:i:s') . " - 匯率快取更新成功 (執行時間: {$executionTime}ms)\n";
    
} catch (Throwable $e) {
    // 記錄錯誤
    ApiLogger::logApiRequest('update_rate_cache.php', 'internal://exception', [], $e->getMessage(), false, 'internal');
    
    // 輸出錯誤訊息（用於 cron 日誌）
    echo date('Y-m-d H:i:s') . " - 匯率快取更新失敗: " . $e->getMessage() . "\n";
    
    // 返回非零退出碼表示失敗
    exit(1);
}
