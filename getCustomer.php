<?php
require_once 'getApiJsonClass.php';
require_once 'RedisConnection.php';

// 獲取請求參數
$lineId = $_GET["lineId"];

// 設置Redis緩存鍵和過期時間
$cacheKey = 'customer_cache_' . $lineId;
$cacheTTL = 60; // 緩存1小時

// 嘗試從Redis獲取緩存數據
$redis = RedisConnection::getInstance();
$cachedData = $redis->get($cacheKey);

if ($cachedData) {
    // 如果有緩存數據，直接返回
    $data = json_decode($cachedData, true);
} else {
    // 如果沒有緩存數據，從API獲取
    $url = 'http://www.adp.idv.tw/api/Customer?Line=' . $lineId;
    $curlRequest = new CurlRequest($url);
    $response = $curlRequest->sendRequest();
    
    $data = json_decode($response, true);
    
    if ($data === null) {
        die("無法取得API資料");
    }
    
    // 將數據存入Redis緩存
    $redis->set($cacheKey, $response, $cacheTTL);
}

header('Content-Type: application/json');
echo json_encode($data);