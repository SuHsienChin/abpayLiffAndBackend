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
if (!isset($_POST['game_id']) || !isset($_POST['game_name'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

$game_id = intval($_POST['game_id']);
$game_name = trim($_POST['game_name']);
$status = isset($_POST['status']) ? intval($_POST['status']) : 1;

// 驗證數據
if (empty($game_name)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '遊戲名稱不能為空']);
    exit;
}

try {
    // 更新遊戲信息
    $query = "UPDATE switch_game_lists SET Name = :name, flag = :flag, UpdateTime = NOW() WHERE Sid = :Sid";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':name', $game_name, PDO::PARAM_STR);
    $stmt->bindValue(':flag', $status, PDO::PARAM_INT);
    $stmt->bindValue(':Sid', $game_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => '遊戲信息已更新']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '更新遊戲信息失敗']);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '資料庫錯誤：' . $e->getMessage()]);
}