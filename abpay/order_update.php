<?php
// 連接資料庫
require_once 'databaseConnection.php';
// 假设您已经连接到数据库


try {
    $connection = new DatabaseConnection();
    $pdo = $connection->connect();


    // 從請求數據中獲取訂單編號和新的訂單狀態
    $orderID = $_GET['orderId'];
    $newStatus = $_GET['status'];

    // 使用 prepared statement 避免 SQL 注入攻擊
    $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE orderId = :orderId");
    $stmt->bindParam(':orderId', $orderID);
    $stmt->bindParam(':status', $newStatus);

    // 執行更新
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => '訂單狀態已成功更新']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '更新失敗：' . $e->getMessage()]);
} finally {
    // 關閉資料庫連接
    $conn = null;
}