<?php

// 連接資料庫
require_once 'databaseConnection.php';
// 假设您已经连接到数据库


try {
    $connection = new DatabaseConnection();
    $pdo = $connection->connect();

    // 接收前端傳來的選取項目 Sid 陣列和對應的 Flag 陣列
    $selectedSids = json_decode(file_get_contents('php://input'), true)['selectedSids'];
    $selectedFlags = json_decode(file_get_contents('php://input'), true)['selectedFlags'];


    // 更新選取項目
    for ($i = 0; $i < count($selectedSids); $i++) {
        $sid = $selectedSids[$i];
        $flag = $selectedFlags[$i];
        $stmt = $pdo->prepare("UPDATE switch_game_lists SET Flag = :flag WHERE Sid = :sid");
        $stmt->bindParam(':flag', $flag);
        $stmt->bindParam(':sid', $sid);
        $stmt->execute();
    }
    echo json_encode(['status' => 'success', 'message' => '成功更新']);
    // // 使用 prepared statement 避免 SQL 注入攻擊
    // $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE orderId = :orderId");
    // $stmt->bindParam(':orderId', $orderID);
    // $stmt->bindParam(':status', $newStatus);

    // // 執行更新
    // $stmt->execute();

    //echo json_encode(['status' => 'success', 'message' => '訂單狀態已成功更新']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '更新失敗：' . $e->getMessage()]);
    //echo json_encode(['status' => 'error', 'message' => '更新失敗：' . $e->getMessage()]);
} finally {
    // 關閉資料庫連接
    $conn = null;
}
