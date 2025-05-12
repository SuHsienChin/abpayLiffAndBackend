<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../databaseConnection.php';

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
        
        // 更新games表
        $stmt = $pdo->prepare("UPDATE games SET flag = :flag WHERE Sid = :sid");
        $stmt->bindParam(':flag', $flag);
        $stmt->bindParam(':sid', $sid);
        $stmt->execute();
        
        // 同時更新switch_game_lists表以保持同步
        $stmt = $pdo->prepare("UPDATE switch_game_lists SET Flag = :flag WHERE Sid = :sid");
        $stmt->bindParam(':flag', $flag);
        $stmt->bindParam(':sid', $sid);
        $stmt->execute();
    }
    
    echo json_encode(['status' => 'success', 'message' => '成功更新']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '更新失敗：' . $e->getMessage()]);
} finally {
    // 關閉資料庫連接
    $pdo = null;
}