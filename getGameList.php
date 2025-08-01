<?php
require_once 'getApiJsonClass.php';
require_once 'RedisConnection.php';

// 設置Redis緩存鍵和過期時間
$cacheKey = 'game_list_cache';
$cacheTTL = 60; // 緩存1分鐘，方便測試

// 嘗試從Redis獲取緩存數據
$redis = RedisConnection::getInstance();
$cachedData = $redis->get($cacheKey);

// 添加來源標記
$source = 'unknown';

if ($cachedData) {
    // 如果有緩存數據，直接返回
    $data = json_decode($cachedData, true);
    $source = 'redis';
} else {
    // 如果沒有緩存數據，從API獲取
    $url = 'http://www.adp.idv.tw/api/GameList';
    $curlRequest = new CurlRequest($url);
    $response = $curlRequest->sendRequest();
    
    $data = json_decode($response, true);
    $source = 'api';
    
    if ($data === null) {
        die("無法取得API資料");
    }
    
    // 將數據存入Redis緩存
    $redis->set($cacheKey, $response, $cacheTTL);
}

// 在回應中添加來源標記
if (is_array($data)) {
    $data['_source'] = $source;
}

header('Content-Type: application/json');
echo json_encode($data);
