<?php
require_once 'getApiJsonClass.php';
require_once 'RedisConnection.php';
require_once 'ApiLogger.php';
require_once 'DistributedLock.php';

/**
 * 取得遊戲帳號資料
 * - 先讀 Redis 快取，未命中時使用分散式鎖避免快取雪崩
 * - 錯誤以 try/catch 捕捉，統一回傳 JSON 格式
 */
try {
// 獲取請求參數
$sid = $_GET["Sid"];

// 設置Redis緩存鍵和過期時間
$cacheKey = 'game_account_cache_' . $sid;
$cacheTTL = 10; // 緩存1小時

// 嘗試從Redis獲取緩存數據
$redis = RedisConnection::getInstance();
$cachedData = $redis->get($cacheKey);

if ($cachedData) {
    // 如果有緩存數據，直接返回
    $data = json_decode($cachedData, true);
    
    // 記錄從快取獲取數據
    ApiLogger::logApiRequest('getGameAccount.php', 'redis://game_account_cache_' . $sid, ['sid' => $sid], $cachedData, true, 'cache');
} else {
    // 快取未命中，使用分散式鎖防止快取雪崩
    $lockKey = $cacheKey . ':lock';
    $lockTimeout = 10; // 鎖超時時間（秒）
    $maxWaitTime = 5; // 最大等待時間（秒）
    
    // 嘗試獲取分散式鎖
    if (DistributedLock::acquireLock($lockKey, $lockTimeout)) {
        // 成功獲取鎖，負責更新快取
        try {
            // 再次檢查快取，防止在獲取鎖期間其他進程已經更新了快取
            $cachedData = $redis->get($cacheKey);
            if ($cachedData) {
                $data = json_decode($cachedData, true);
            } else {
                // 從API獲取新數據
                $url = 'http://www.adp.idv.tw/api/GameAccount?Sid=' . $sid;
                $curlRequest = new CurlRequest($url);
                $response = $curlRequest->sendRequest();
                
                $data = json_decode($response, true);
                
                if ($data === null) {
                    // 記錄失敗的API請求
                    ApiLogger::logApiRequest('getGameAccount.php', $url, ['sid' => $sid], '', false);
                    throw new Exception("無法取得API資料");
                }
                
                // 記錄成功的API請求
                ApiLogger::logApiRequest('getGameAccount.php', $url, ['sid' => $sid], $response, true, 'api');
                
                // 將數據存入Redis緩存
                $redis->set($cacheKey, $response, $cacheTTL);
            }
        } finally {
            // 無論成功或失敗，都要釋放鎖
            DistributedLock::releaseLock($lockKey);
        }
    } else {
        // 獲取鎖失敗，等待其他進程更新快取
        $cachedData = DistributedLock::waitForCache($cacheKey, $maxWaitTime, 200);
        
        if ($cachedData !== false) {
            // 等待成功，使用快取數據
            $data = json_decode($cachedData, true);
            
            // 記錄從快取獲取數據（等待後）
            ApiLogger::logApiRequest('getGameAccount.php', 'redis://game_account_cache_' . $sid, ['sid' => $sid], $cachedData, true, 'cache_wait');
        } else {
            // 等待超時，改為丟出例外，統一由外層處理
            throw new Exception("快取更新超時，請稍後再試");
        }
    }
}

header('Content-Type: application/json');
echo json_encode($data);
} catch (Throwable $e) {
    // 捕捉所有錯誤，回傳一致 JSON 並記錄
    http_response_code(500);
    header('Content-Type: application/json');
    ApiLogger::logApiRequest('getGameAccount.php', 'internal://exception', ['sid' => isset($sid) ? $sid : null], $e->getMessage(), false, 'internal');
    echo json_encode([
        'success' => false,
        'message' => '伺服器發生錯誤，請稍後再試',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}