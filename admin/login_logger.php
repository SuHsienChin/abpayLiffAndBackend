<?php
require_once '../databaseConnection.php';

/**
 * 將登入日誌記錄到資料庫
 * 
 * @param string $username 嘗試登入的用戶名
 * @param string $status 登入狀態 (success/failed)
 * @param string $details 詳細信息
 * @return bool 操作是否成功
 */
function logLoginAttempt($username, $status, $details = '') {
    try {
        $dbConnection = new DatabaseConnection();
        $conn = $dbConnection->connect();
        
        $stmt = $conn->prepare("INSERT INTO admin_login_logs (username, status, ip_address, user_agent, details) VALUES (:username, :status, :ip_address, :user_agent, :details)");
        $stmt->execute([
            ':username' => $username,
            ':status' => $status,
            ':ip_address' => $_SERVER['REMOTE_ADDR'],
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ':details' => $details
        ]);
        
        return true;
    } catch (PDOException $e) {
        // 如果資料庫操作失敗，仍然使用error_log記錄錯誤
        error_log("Failed to log login attempt: " . $e->getMessage());
        return false;
    }
}