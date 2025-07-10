<?php
/**
 * 將訂單添加到 Redis 佇列
 * 此腳本接收訂單數據並將其添加到 Redis 佇列中
 */

// 引入 Redis 連接類
require_once 'RedisConnection.php';

// 設置響應頭為 JSON
header('Content-Type: application/json');

// 獲取 POST 數據
$postData = file_get_contents('php://input');
$orderData = json_decode($postData, true);

// 檢查數據是否有效
if (!$orderData || !isset($orderData['orderData'])) {
    echo json_encode([
        'status' => 'error',
        'message' => '無效的訂單數據'
    ]);
    exit;
}

// 獲取 Redis 連接
$redisConnection = RedisConnection::getInstance();
$redis = $redisConnection->getRedis();

if (!$redis) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Redis 連接失敗'
    ]);
    error_log('Redis 連接失敗，無法添加訂單到佇列');
    exit;
}

try {
    // 生成唯一訂單 ID (如果沒有提供)
    if (!isset($orderData['orderId'])) {
        $orderData['orderId'] = uniqid('order_', true);
    }
    
    // 添加時間戳
    $orderData['timestamp'] = time();
    
    // 將訂單數據序列化為 JSON 字符串
    $orderJson = json_encode($orderData);
    
    // 將訂單添加到 Redis 佇列
    $result = $redis->rPush('order_queue', $orderJson);
    
    if ($result) {
        // 獲取當前佇列長度
        $queueLength = $redis->lLen('order_queue');
        
        echo json_encode([
            'status' => 'success',
            'message' => '訂單已添加到佇列',
            'orderId' => $orderData['orderId'],
            'queueLength' => $queueLength
        ]);
        
        // 記錄日誌
        error_log("訂單 {$orderData['orderId']} 已添加到佇列，當前佇列長度: $queueLength");
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => '添加訂單到佇列失敗'
        ]);
        error_log('添加訂單到佇列失敗: ' . print_r($orderData, true));
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => '處理訂單時發生錯誤: ' . $e->getMessage()
    ]);
    error_log('處理訂單時發生錯誤: ' . $e->getMessage());
}