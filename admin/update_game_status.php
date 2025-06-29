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
    // 開始事務
    $pdo->beginTransaction();
    
    try {
        // 更新 switch_game_lists 表
        $query1 = "UPDATE switch_game_lists SET Flag = :flag, UpdateTime = NOW() WHERE Sid = :Sid";
        $stmt1 = $pdo->prepare($query1);
        $stmt1->bindValue(':flag', $status, PDO::PARAM_INT);
        $stmt1->bindValue(':Sid', $game_id, PDO::PARAM_INT);
        $result1 = $stmt1->execute();
        
        // 更新 games 表
        $query2 = "UPDATE games SET status = :flag, updated_at = NOW() WHERE id = :game_id";
        $stmt2 = $pdo->prepare($query2);
        $stmt2->bindValue(':flag', $status, PDO::PARAM_INT);
        $stmt2->bindValue(':game_id', $game_id, PDO::PARAM_INT);
        $result2 = $stmt2->execute();
        
        // 提交事務
        $pdo->commit();
        
        $result = $result1 && $result2;
    } catch (Exception $e) {
        // 回滾事務
        $pdo->rollBack();
        throw $e;
    }

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