<?php

// 連接資料庫
require_once 'databaseConnection.php';
// 假设您已经连接到数据库

$connection = new DatabaseConnection();
$pdo = $connection->connect('utf8mb4');

// 准备和执行查询
$stmt = $pdo->prepare('SELECT * FROM switch_game_lists ');
$stmt->execute();
$game_lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 將遊戲名稱以 JSON 格式回傳
header('Content-Type: application/json');
echo json_encode($game_lists);
