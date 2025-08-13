<?php
require_once 'getApiJsonClass.php';
require_once 'ApiLogger.php';
require_once 'DistributedLock.php';

// 備份版本不支援快取，直接使用分散式鎖防止併發問題
$lockKey = 'game_list_bk_lock';
$lockTimeout = 10; // 鎖超時時間（秒）

// 嘗試獲取分散式鎖
if (DistributedLock::acquireLock($lockKey, $lockTimeout)) {
    try {
        $url = 'http://www.adp.idv.tw/api/GameList';
        $curlRequest = new CurlRequest($url);
        $response = $curlRequest->sendRequest();

        $data = json_decode($response, true);

        if ($data === null) {
            // 記錄失敗的API請求
            ApiLogger::logApiRequest('getGameList.bk.php', $url, [], '', false);
            throw new Exception("無法取得API資料");
        }

        // 記錄成功的API請求
        ApiLogger::logApiRequest('getGameList.bk.php', $url, [], $response, true);
    } finally {
        // 釋放鎖
        DistributedLock::releaseLock($lockKey);
    }
} else {
    // 獲取鎖失敗，返回錯誤
    die("系統繁忙，請稍後再試");
}

header('Content-Type: application/json');
echo json_encode($data);
