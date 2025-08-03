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

// 如果是RedisSimulator，直接從文件讀取
if ($redis instanceof RedisSimulator) {
    $listPath = __DIR__ . '/redis_data/' . md5($queueKey) . '.list';
    if (file_exists($listPath)) {
        $data = file_get_contents($listPath);
        $rawItems = json_decode($data, true) ?: [];
        // 解析每個項目的JSON字符串
        foreach ($rawItems as $rawItem) {
            $queueItems[] = json_decode($rawItem, true);
        }
    }
} else {
    // 如果是真實的Redis，使用LRANGE獲取所有佇列項目
    $rawItems = $redis->lRange($queueKey, 0, -1);
    // 解碼每個項目
    foreach ($rawItems as $rawItem) {
        $queueItems[] = json_decode($rawItem, true);
    }
}

// 返回佇列信息
echo json_encode([
    'success' => true,
    'queue_length' => $queueLength,
    'queue_items' => $queueItems
], JSON_PRETTY_PRINT);