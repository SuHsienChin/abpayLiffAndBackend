<?php
require_once 'RedisOrderQueue.php';
require_once 'databaseConnection.php';

// 設置響應頭，允許跨域請求
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// 檢查是否有 GET 參數存在，或者是否通過命令行傳遞參數
$params = [];

// 處理命令行參數
if (isset($argv) && count($argv) > 1) {
    parse_str($argv[1], $params);
}

// 處理 GET 參數
if (isset($_GET) && !empty($_GET)) {
    $params = $_GET;
}

// 檢查參數是否存在
if (!empty($params)) {
    // 構建 URL 參數字符串
    $urlParams = '';
    foreach ($params as $key => $value) {
        $urlParams .= $key . '=' . urlencode($value) . '&';
    }
    $urlParams = rtrim($urlParams, '&');
    
    // 獲取 POST 數據作為訂單數據
    $orderData = [];
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $orderData = $_POST;
    }
    
    // 驗證訂單參數
    $errors = [];
    
    // 檢查必要參數
    $requiredParams = ['UserId', 'Password', 'Customer', 'GameAccount', 'Item', 'Count'];
    foreach ($requiredParams as $param) {
        if (!isset($params[$param]) || $params[$param] === '') {
            $errors[] = "缺少必要參數: {$param}";
        }
    }
    
    // 特別檢查 Count 參數
    if (isset($params['Count'])) {
        $counts = explode(',', $params['Count']);
        foreach ($counts as $index => $count) {
            if (!is_numeric($count) || intval($count) <= 0) {
                $errors[] = "商品 {$index} 的數量無效: {$count}，數量必須大於 0";
            }
        }
    }
    
    // 如果有錯誤，返回錯誤響應
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '訂單參數無效',
            'errors' => $errors
        ]);
        exit;
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
        try {
            $dbConnection = new DatabaseConnection();
            $conn = $dbConnection->connect();
            
            // 檢查 system_logs 表是否存在，如果不存在則創建
            $stmt = $conn->query("SHOW TABLES LIKE 'system_logs'");
            if ($stmt->rowCount() == 0) {
                $createTableSql = "CREATE TABLE IF NOT EXISTS system_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    type VARCHAR(255) NOT NULL,
                    JSON TEXT,
                    api_url TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                $conn->exec($createTableSql);
                error_log("system_logs 表已自動創建");
            }
            
            $stmt = $conn->prepare("INSERT INTO system_logs (type, JSON, api_url, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bindParam(1, $logData['type']);
            $stmt->bindParam(2, $logData['JSON']);
            $stmt->bindParam(3, $logData['api_url']);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("數據庫記錄錯誤: " . $e->getMessage());
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