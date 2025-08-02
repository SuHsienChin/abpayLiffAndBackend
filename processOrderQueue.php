<?php
require_once 'RedisOrderQueue.php';

/**
 * 訂單佇列處理腳本
 * 此腳本應該通過 cron 作業每秒執行一次，或使用其他方式定時執行
 * 例如: * * * * * php /path/to/processOrderQueue.php
 */

// 設置執行時間限制
set_time_limit(30);

// 確保日誌目錄存在
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// 記錄腳本執行日誌
file_put_contents(
    $logDir . '/script_execution_' . date('Y-m-d') . '.log',
    date('Y-m-d H:i:s') . ' - 腳本執行' . PHP_EOL,
    FILE_APPEND
);

// 創建佇列處理器實例
$orderQueue = new RedisOrderQueue();

// 檢查佇列是否有訂單
$queueLength = $orderQueue->getQueueLength();

if ($queueLength > 0) {
    // 處理一個訂單
    $result = $orderQueue->processNextOrder();
    
    if ($result) {
        $queueItem = $result['queue_item'];
        
        // 記錄處理結果
        file_put_contents(
            $logDir . '/order_queue_' . date('Y-m-d') . '.log',
            date('Y-m-d H:i:s') . ' - 處理訂單: ' . $queueItem['id'] . ', 狀態: ' . $queueItem['status'] . PHP_EOL,
            FILE_APPEND
        );
        
        // 如果訂單處理成功，可以在這裡執行其他操作
        if ($queueItem['status'] === 'success' && isset($result['response']['OrderId'])) {
            $orderId = $result['response']['OrderId'];
            
            // 這裡可以添加其他處理邏輯，例如更新數據庫等
            file_put_contents(
                $logDir . '/successful_orders_' . date('Y-m-d') . '.log',
                date('Y-m-d H:i:s') . ' - 訂單成功: ' . $orderId . PHP_EOL,
                FILE_APPEND
            );
        }
    }
} else {
    // 佇列為空，記錄日誌
    file_put_contents(
        $logDir . '/order_queue_' . date('Y-m-d') . '.log',
        date('Y-m-d H:i:s') . ' - 佇列為空' . PHP_EOL,
        FILE_APPEND
    );
}

// 確保日誌目錄存在
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// 記錄腳本執行日誌
file_put_contents(
    $logDir . '/script_execution_' . date('Y-m-d') . '.log',
    date('Y-m-d H:i:s') . ' - 腳本執行' . PHP_EOL,
    FILE_APPEND
);