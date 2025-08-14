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
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 接收前端傳來的遊戲列表資料
    $gameList = json_decode(file_get_contents('php://input'), true);

    // 先查詢資料庫中所有的遊戲資料
    $stmt = $pdo->prepare("SELECT * FROM games");
    $stmt->execute();
    $database_games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $database_games_by_sid = array_column($database_games, null, 'Sid');
    $database_games_by_id = array_column($database_games, null, 'id');

    // 更新遊戲列表到資料庫
    $inserted_count = 0;
    foreach ($gameList as $game) {
        $id = $game['Id'];
        $name = $game['Name'];
        $sellNote = $game['SellNote'] ?? '';
        $enable = $game['Enable'] ?? 1;
        $gameRate = $game['GameRate'] ?? 0;
        $sid = $game['Sid'];
        $flag = 0; // 默認為停用
        $updateTime = date('Y-m-d H:i:s');
        $userSid = $game['UserSid'] ?? 0;

        // 檢查資料庫中是否已經存在相同的 Sid 或 id
        if (array_key_exists($sid, $database_games_by_sid) || array_key_exists($id, $database_games_by_id)) {
            // 如果 Sid 或 id 已存在,則跳過更新
            continue;
        }

        $stmt = $pdo->prepare("INSERT INTO games (id, game_name, status, created_at, updated_at) 
                           VALUES (:id, :name, :flag, :updateTime, :updateTime)");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':flag', $flag, PDO::PARAM_STR);
        $stmt->bindParam(':updateTime', $updateTime, PDO::PARAM_STR);
        $stmt->execute();
        $inserted_count++;
        
        // 同時更新switch_game_lists表以保持同步
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM switch_game_lists WHERE Sid = :sid");
        $check_stmt->bindParam(':sid', $sid);
        $check_stmt->execute();
        if ($check_stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO switch_game_lists (Id, Name, SellNote, Enable, GameRate, Sid, Flag, UpdateTime, UserSid) 
                               VALUES (:id, :name, :sellNote, :enable, :gameRate, :sid, :flag, :updateTime, :userSid)");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':sellNote', $sellNote, PDO::PARAM_STR);
            $stmt->bindParam(':enable', $enable, PDO::PARAM_STR);
            $stmt->bindParam(':gameRate', $gameRate, PDO::PARAM_STR);
            $stmt->bindParam(':sid', $sid, PDO::PARAM_STR);
            $stmt->bindParam(':flag', $flag, PDO::PARAM_STR);
            $stmt->bindParam(':updateTime', $updateTime, PDO::PARAM_STR);
            $stmt->bindParam(':userSid', $userSid, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    echo json_encode(['success' => true, 'message' => '成功更新遊戲列表', 'inserted' => $inserted_count]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '更新失敗：' . $e->getMessage()]);
} finally {
    // 關閉資料庫連接
    $pdo = null;
}