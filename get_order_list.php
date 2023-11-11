<?php
// 連接資料庫
require_once 'databaseConnection.php';

$connection = new DatabaseConnection();
$pdo = $connection->connect();


// 取得客戶對應的遊戲名稱
// $Sid = $_GET['Sid'];
$lineId = $_GET['lineId'];

if (!empty($lineId)) {
    // 取得客戶所有遊戲名稱
    $sql = "SELECT * FROM orders WHERE lineId = :lineId ORDER BY `orders`.`created_at` DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':lineId', $lineId);
    $stmt->execute();
    $orderLists = $stmt->fetchAll(PDO::FETCH_ASSOC);
}else {
    // 若未選擇遊戲名稱，回傳空陣列
    $orderLists = [];
}


// 將遊戲名稱以 JSON 格式回傳
header('Content-Type: application/json');
echo json_encode($orderLists);