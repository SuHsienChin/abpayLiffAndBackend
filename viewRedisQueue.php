<?php
require_once 'RedisOrderQueue.php';
require_once 'RedisConnection.php';
require_once 'RedisSimulator.php';

// 設置響應頭，允許跨域請求
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// 創建佇列處理器實例
$orderQueue = new RedisOrderQueue();

// 獲取佇列長度
$queueLength = $orderQueue->getQueueLength();

// 獲取佇列內容
$redis = RedisConnection::getInstance()->getRedis();
$queueKey = 'order_queue';
$queueItems = [];

// 嘗試獲取佇列項目
try {
    // 使用 lRange 方法獲取所有佇列項目
    $rawItems = $redis->lRange($queueKey, 0, -1);
    
    // 如果是 RedisSimulator 且 lRange 返回空，但文件存在，則直接從文件讀取
    if (empty($rawItems) && $redis instanceof RedisSimulator) {
        $listPath = __DIR__ . '/redis_data/' . md5($queueKey) . '.list';
        if (file_exists($listPath)) {
            $data = file_get_contents($listPath);
            $rawItems = json_decode($data, true) ?: [];
        }
    }
    
    // 解析每個項目的JSON字符串
    foreach ($rawItems as $rawItem) {
        // 檢查是否已經是數組
        if (is_array($rawItem)) {
            $queueItems[] = $rawItem;
        } else {
            $queueItems[] = json_decode($rawItem, true);
        }
    }
    
    // 記錄日誌
    error_log("獲取佇列項目: " . count($queueItems) . " 個項目");
} catch (Exception $e) {
    error_log("獲取佇列項目時出錯: " . $e->getMessage());
    // 如果出錯且是 RedisSimulator，嘗試從文件直接讀取
    if ($redis instanceof RedisSimulator) {
        $listPath = __DIR__ . '/redis_data/' . md5($queueKey) . '.list';
        if (file_exists($listPath)) {
            $data = file_get_contents($listPath);
            $rawItems = json_decode($data, true) ?: [];
            foreach ($rawItems as $rawItem) {
                $queueItems[] = json_decode($rawItem, true);
            }
            error_log("從文件讀取佇列項目: " . count($queueItems) . " 個項目");
        }
    }
}

// 獲取 Redis 連接狀態
$connectionStatus = RedisConnection::getInstance()->getConnectionStatus();

// 返回佇列信息
echo json_encode([
    'success' => true,
    'queue_length' => $queueLength,
    'queue_items' => $queueItems,
    'connection_info' => $connectionStatus,
    'server_info' => [
        'os' => PHP_OS,
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'script_path' => __FILE__,
        'data_dir' => ($redis instanceof RedisSimulator) ? $redis->getDataDir() : null,
        'timestamp' => date('Y-m-d H:i:s')
    ]
], JSON_PRETTY_PRINT);