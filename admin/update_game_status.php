<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../databaseConnection.php';
$dbConnection = new DatabaseConnection();
$pdo = $dbConnection->connect();

// 檢查必要參數
if (!isset($_POST['game_id']) || !isset($_POST['status'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

$game_id = intval($_POST['game_id']);
$status = intval($_POST['status']);

try {
    // 更新遊戲狀態
    $query = "UPDATE games SET status = :status, updated_at = NOW() WHERE id = :game_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    $stmt->bindValue(':game_id', $game_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => '遊戲狀態已更新']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '更新遊戲狀態失敗']);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '資料庫錯誤：' . $e->getMessage()]);
}