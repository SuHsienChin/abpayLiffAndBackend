<?php

// 連接資料庫
require_once 'databaseConnection.php';
// 假设您已经连接到数据库


try {
    $connection = new DatabaseConnection();
    $pdo = $connection->connect();



    // 接收前端傳來的遊戲列表資料
    $gameList = json_decode(file_get_contents('php://input'), true);

    // 先查詢資料庫中所有的遊戲資料
    $stmt = $pdo->prepare("SELECT * FROM switch_game_lists");
    $stmt->execute();
    $database_games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $database_games_by_sid = array_column($database_games, null, 'Sid');

    // 更新遊戲列表到資料庫
    foreach ($gameList as $game) {
        $id = $game['Id'];
        $name = $game['Name'];
        $sellNote = $game['SellNote'];
        $enable = $game['Enable'];
        $gameRate = $game['GameRate'];
        $sid = $game['Sid'];
        $flag = false;
        $updateTime = $game['UpdateTime'];
        $userSid = $game['UserSid'];

        // 檢查資料庫中是否已經存在相同的 Sid
        if (array_key_exists($sid, $database_games_by_sid)) {
            // 如果 Sid 已存在,則跳過更新
            continue;
        }

        $stmt = $pdo->prepare("INSERT INTO switch_game_lists (Id, Name, SellNote, Enable, GameRate, Sid, flag, UpdateTime, UserSid) 
                           VALUES (:id, :name, :sellNote, :enable, :gameRate, :sid, :flag, :updateTime, :userSid)");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':sellNote', $sellNote);
        $stmt->bindParam(':enable', $enable);
        $stmt->bindParam(':gameRate', $gameRate);
        $stmt->bindParam(':sid', $sid);
        $stmt->bindParam(':flag', $flag);
        $stmt->bindParam(':updateTime', $updateTime);
        $stmt->bindParam(':userSid', $userSid);
        $stmt->execute();
    }

    echo json_encode(['status' => 'success', 'message' => '訂單狀態已成功更新']);
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
