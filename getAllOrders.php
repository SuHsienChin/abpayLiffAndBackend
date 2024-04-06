<?php
// 連接資料庫
require_once 'databaseConnection.php';

$connection = new DatabaseConnection();
$pdo = $connection->connect();

$sql = "SELECT * FROM orders ORDER BY `orders`.`created_at` DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$orderLists = $stmt->fetchAll(PDO::FETCH_ASSOC);


// 將遊戲名稱以 JSON 格式回傳
header('Content-Type: application/json');
echo json_encode($orderLists);