<?php
require_once 'RedisOrderQueue.php';
require_once 'databaseConnection.php';

// 設置響應頭，允許跨域請求
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// 檢查是否有 GET 參數存在
if (isset($_GET) && !empty($_GET)) {
    // 構建 URL 參數字符串
    $urlParams = '';
    foreach ($_GET as $key => $value) {
        $urlParams .= $key . '=' . urlencode($value) . '&';
    }
    $urlParams = rtrim($urlParams, '&');
    
    // 獲取 POST 數據作為訂單數據
    $orderData = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $orderData = $_POST;
    }
    
    try {
        // 創建佇列處理器實例
        $orderQueue = new RedisOrderQueue();
        
        // 將訂單添加到佇列
        $queueId = $orderQueue->addToQueue($urlParams, $orderData);
        
        // 記錄到系統日誌
        $logData = [
            'type' => '訂單已添加到佇列',
            'JSON' => json_encode([
                'queue_id' => $queueId,
                'url_params' => $urlParams,
                'order_data' => $orderData
            ]),
            'api_url' => 'http://www.adp.idv.tw/api/Order?' . $urlParams
        ];
        
        // 連接數據庫並記錄日誌
        $conn = connectToDatabase();
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO system_logs (type, JSON, api_url, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $logData['type'], $logData['JSON'], $logData['api_url']);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
        
        // 返回成功響應
        echo json_encode([
            'success' => true,
            'message' => '訂單已添加到佇列',
            'queue_id' => $queueId,
            'queue_length' => $orderQueue->getQueueLength()
        ]);
    } catch (Exception $e) {
        // 返回錯誤響應
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '添加訂單到佇列時發生錯誤',
            'error' => $e->getMessage()
        ]);
    }
} else {
    // 沒有參數，返回錯誤
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => '缺少必要的參數'
    ]);
}