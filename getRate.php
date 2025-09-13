<?php
require_once 'getApiJsonClass.php';
require_once 'RedisConnection.php';
require_once 'CacheConfig.php';
require_once 'ApiLogger.php';

try {
    // 設置Redis緩存鍵
    $cacheKey = 'rate_cache';
    
    // 嘗試從Redis獲取緩存數據
    $redis = RedisConnection::getInstance();
    $cachedData = $redis->get($cacheKey);
    
    if ($cachedData) {
        // 如果有緩存數據，直接返回
        $data = json_decode($cachedData, true);
        
        // 記錄從快取獲取數據
        ApiLogger::logApiRequest('getRate.php', 'redis://rate_cache', [], $cachedData, true, 'cache');
    } else {
        // 快取未命中，從API獲取新數據作為備用方案
        $url = 'http://www.adp.idv.tw/api/Rate';
        $curlRequest = new CurlRequest($url);
        $response = $curlRequest->sendRequest();
        
        $data = json_decode($response, true);
        
        if ($data === null) {
            // 記錄失敗的API請求
            ApiLogger::logApiRequest('getRate.php', $url, [], '', false);
            throw new Exception("無法取得API資料");
        }
        
        // 記錄成功的API請求
        ApiLogger::logApiRequest('getRate.php', $url, [], $response, true, 'api');
        
        // 將數據存入Redis緩存（使用較短的TTL，因為有自動更新機制）
        $redis->set($cacheKey, $response, CACHE_TTL_RATE);
    }


header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/json');
echo json_encode($data);
} catch (Throwable $e) {
    http_response_code(500);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: application/json');
    ApiLogger::logApiRequest('getRate.php', 'internal://exception', [], $e->getMessage(), false, 'internal');
    echo json_encode([
        'success' => false,
        'message' => '伺服器發生錯誤，請稍後再試',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
