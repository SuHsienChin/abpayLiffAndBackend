<?php
require_once 'databaseConnection.php';

/**
 * API 日誌記錄類別
 * 用於記錄所有對外部 API 的請求
 */
class ApiLogger {
    
    /**
     * 記錄 API 請求到資料庫
     * @param string $source - 請求來源（檔案名或函數名）
     * @param string $apiUrl - 完整的 API URL
     * @param array $params - 請求參數
     * @param string $response - API 響應（可選）
     * @param bool $success - 請求是否成功
     */
    public static function logApiRequest($source, $apiUrl, $params = [], $response = '', $success = true) {
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
            }
            
            // 準備日誌數據
            $logType = $success ? 'API請求成功' : 'API請求失敗';
            $logData = [
                'source' => $source,
                'api_url' => $apiUrl,
                'params' => $params,
                'success' => $success,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            if ($response) {
                $logData['response'] = $response;
            }
            
            $logJson = json_encode($logData, JSON_UNESCAPED_UNICODE);
            
            // 插入日誌記錄
            $stmt = $conn->prepare("INSERT INTO system_logs (type, JSON, api_url) VALUES (?, ?, ?)");
            $stmt->bindParam(1, $logType);
            $stmt->bindParam(2, $logJson);
            $stmt->bindParam(3, $apiUrl);
            $stmt->execute();
            
        } catch (Exception $e) {
            // 如果資料庫記錄失敗，至少記錄到錯誤日誌
            error_log("API日誌記錄失敗: " . $e->getMessage());
        }
    }
}
