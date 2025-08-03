<?php
require_once 'RedisOrderQueue.php';

// 設置響應頭，允許跨域請求
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// 確保日誌目錄存在
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// 創建佇列處理器實例
$orderQueue = new RedisOrderQueue();

// 檢查佇列是否有訂單
$queueLength = $orderQueue->getQueueLength();

$response = [
    'success' => true,
    'queue_length_before' => $queueLength,
    'processed' => false,
    'message' => ''
];

if ($queueLength > 0) {
    // 處理一個訂單
    $result = $orderQueue->processNextOrder();
    
    if ($result) {
        $queueItem = $result['queue_item'];
        
        // 記錄處理結果
        file_put_contents(
            $logDir . '/order_queue_' . date('Y-m-d') . '.log',
            date('Y-m-d H:i:s') . ' - 手動處理訂單: ' . $queueItem['id'] . ', 狀態: ' . $queueItem['status'] . PHP_EOL,
            FILE_APPEND
        );
        
        $response['processed'] = true;
        $response['queue_item'] = $queueItem;
        $response['message'] = '成功處理一個訂單';
        
        // 如果訂單處理成功，可以在這裡執行其他操作
        if ($queueItem['status'] === 'success' && isset($result['response']['OrderId'])) {
            $orderId = $result['response']['OrderId'];
            
            // 這裡可以添加其他處理邏輯，例如更新數據庫等
            file_put_contents(
                $logDir . '/successful_orders_' . date('Y-m-d') . '.log',
                date('Y-m-d H:i:s') . ' - 訂單成功: ' . $orderId . PHP_EOL,
                FILE_APPEND
            );
            
            $response['order_id'] = $orderId;
        }
    } else {
        $response['message'] = '處理訂單時發生錯誤';
    }
} else {
    // 佇列為空，記錄日誌
    file_put_contents(
        $logDir . '/order_queue_' . date('Y-m-d') . '.log',
        date('Y-m-d H:i:s') . ' - 佇列為空，無法手動處理' . PHP_EOL,
        FILE_APPEND
    );
    
    $response['message'] = '佇列為空，無法處理訂單';
}

// 獲取處理後的佇列長度
$response['queue_length_after'] = $orderQueue->getQueueLength();

// 返回處理結果
echo json_encode($response, JSON_PRETTY_PRINT);