<?php
require_once 'getApiJsonClass.php';
require_once 'RedisConnection.php';
require_once 'ApiLogger.php';

// 獲取請求參數
$sid = $_GET["Sid"];

// 設置Redis緩存鍵和過期時間
$cacheKey = 'game_account_cache_' . $sid;
$cacheTTL = 60; // 緩存1小時

// 嘗試從Redis獲取緩存數據
$redis = RedisConnection::getInstance();
$cachedData = $redis->get($cacheKey);

if ($cachedData) {
    // 如果有緩存數據，直接返回
    $data = json_decode($cachedData, true);
} else {
    // 如果沒有緩存數據，從API獲取
    $url = 'http://www.adp.idv.tw/api/GameAccount?Sid=' . $sid;
    $curlRequest = new CurlRequest($url);
    $response = $curlRequest->sendRequest();
    
    $data = json_decode($response, true);
    
    if ($data === null) {
        // 記錄失敗的API請求
        ApiLogger::logApiRequest('getGameAccount.php', $url, ['sid' => $sid], '', false);
        die("無法取得API資料");
    }
    
    // 記錄成功的API請求
    ApiLogger::logApiRequest('getGameAccount.php', $url, ['sid' => $sid], $response, true);
    
    // 將數據存入Redis緩存
    $redis->set($cacheKey, $response, $cacheTTL);
}

header('Content-Type: application/json');
echo json_encode($data);