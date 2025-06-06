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
if (!isset($_POST['Sid']) || !isset($_POST['flag'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

$game_id = intval($_POST['Sid']);
$status = intval($_POST['flag']);

try {
    // 更新遊戲狀態
    $query = "UPDATE switch_game_lists SET flag = :flag, updateTime = NOW() WHERE Sid = :Sid";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':flag', $status, PDO::PARAM_INT);
    $stmt->bindValue(':Sid', $game_id, PDO::PARAM_INT);
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