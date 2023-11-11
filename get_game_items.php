<?php
// 連接資料庫
require_once 'databaseConnection.php';

$connection = new DatabaseConnection();
$pdo = $connection->connect();

// 取得遊戲名稱對應的商品資料
$selectedGame = $_GET['Sid'];

if (!empty($selectedGame)) {
    $sql = "SELECT * FROM game_products WHERE game_category_id = :game";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':game', $selectedGame);
    $stmt->execute();
    $gameItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // 若未選擇遊戲名稱，回傳空陣列
    $gameItems = [];
}

// 將資料以 JSON 格式回傳
header('Content-Type: application/json');
echo json_encode($gameItems);