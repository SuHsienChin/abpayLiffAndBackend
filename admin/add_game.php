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

// 檢查請求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// 獲取表單數據
$game_name = isset($_POST['game_name']) ? trim($_POST['game_name']) : '';
$status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

// 驗證數據
if (empty($game_name)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Game name is required']);
    exit;
}

try {
    // 插入新遊戲
    $query = "INSERT INTO switch_game_lists (Name, flag, UpdateTime) VALUES (:name, :flag, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':name', $game_name, PDO::PARAM_STR);
    $stmt->bindValue(':flag', $status, PDO::PARAM_INT);
    $stmt->execute();
    
    // 返回成功響應
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => '遊戲新增成功']);
} catch (PDOException $e) {
    // 返回錯誤響應
    header('Content-Type: application/json');
    echo json_encode(['error' => '新增遊戲失敗: ' . $e->getMessage()]);
}