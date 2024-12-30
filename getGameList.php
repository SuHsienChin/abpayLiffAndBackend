<?php
// require_once 'getApiJsonClass.php';

// $url = 'http://www.adp.idv.tw/api/GameList';
// $curlRequest = new CurlRequest($url);
// $response = $curlRequest->sendRequest();

// $data = json_decode($response, true);

// if ($data === null) {
//     die("無法取得API資料");
// }

// header('Content-Type: application/json');
// echo json_encode($data);


require_once 'getApiJsonClass.php';

// 加載 Redis
$redis = new Redis();
$redis->connect('127.0.0.1', 6379); // 連接到本地 Redis 伺服器

// 設定 API 和快取相關參數
$url = 'http://www.adp.idv.tw/api/GameList';
$cacheKey = 'gameList';
$cacheTime = 1800; // 快取有效時間（秒）

// 檢查 Redis 是否已有資料
if (!$redis->exists($cacheKey)) {
    // 如果快取不存在，調用 API 獲取資料
    $curlRequest = new CurlRequest($url);
    $response = $curlRequest->sendRequest();
    $data = json_decode($response, true);

    if ($data === null) {
        die("無法取得API資料");
    }

    // 儲存資料到 Redis，並設置過期時間
    $redis->set($cacheKey, json_encode($data), $cacheTime);
} else {
    // 從 Redis 快取中讀取資料
    $data = json_decode($redis->get($cacheKey), true);
}

// 回傳資料
header('Content-Type: application/json');
echo json_encode($data);
?>
