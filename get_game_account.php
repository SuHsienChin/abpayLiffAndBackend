<?php
// 連接資料庫
require_once 'databaseConnection.php';

$connection = new DatabaseConnection();
$pdo = $connection->connect();

// 取得遊戲名稱對應的商品資料
$Customer = $_GET['GameSid'];

if (!empty($Customer)) {
    $sql = "SELECT * FROM game_account WHERE game_sid = :GameSid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':GameSid', $Customer);
    $stmt->execute();
    $gameAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // 若未選擇遊戲名稱，回傳空陣列
    $gameAccounts = [];
}

// 將資料以 JSON 格式回傳
header('Content-Type: application/json');
echo json_encode($gameAccounts);