<?php

// 連接資料庫
require_once 'databaseConnection.php';

$connection = new DatabaseConnection();
$pdo = $connection->connect();


$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);


try {
    
var_dump( $data['JSON'] );
    //插入資料到 "orders" 資料表
    $sql = "INSERT INTO system_logs (type, JSON) 
      VALUES 
      (:type, :JSON)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
    $stmt->bindParam(':JSON', $data['JSON'], PDO::PARAM_STR);
    $stmt->execute();
} catch (Exception $e) {
    echo $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode('資料庫新增成功');
