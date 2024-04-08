<?php

// 連接資料庫
require_once 'databaseConnection.php';

$connection = new DatabaseConnection();
$pdo = $connection->connect();


$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

var_dump($data);
//返回json
//echo json_encode($_POST);

// try {
//     foreach ($_POST as $key => $value) {
//         $$key = $value;
//         echo $$key;
//     }

//     // 插入資料到 "orders" 資料表
//     // $sql = "INSERT INTO orders (lineId, customerId, orderId, gameName, gameItemsName,
//     //  gameItemCounts,itemsMoney,	sumMoney, logintype, acount, password, serverName, gameAccountName,
//     //   gameAccountId, gameAccountSid, customerSid, status, orderDateTime,remark) 
//     //   VALUES 
//     //   (:lineId, :customerId, :orderId, :gameName, :gameItemsName,
//     //   :gameItemCounts,:itemsMoney,	:sumMoney, :logintype, :acount, :password, :serverName, :gameAccountName,
//     //   :gameAccountId, :gameAccountSid, :customerSid, :status, :orderDateTime,:remark)";
//     // //($lineId, $customerId, $orderId, 'gameName', $gameItemsName, $gameItemCounts, $logintype, $acount, $Password, $serverName, $gameAccountName, $gameAccountId, $gameAccountSid, $customerSid, NULL, NULL);";
//     // $stmt = $pdo->prepare($sql);
//     // $stmt->bindParam(':lineId', $lineId, PDO::PARAM_STR);
//     // $stmt->bindParam(':customerId', $customerId, PDO::PARAM_STR);
//     // $stmt->bindParam(':orderId', $orderId, PDO::PARAM_STR);
//     // $stmt->bindParam(':gameName', $gameName, PDO::PARAM_STR);
//     // $stmt->bindParam(':gameItemsName', $gameItemsName, PDO::PARAM_STR);
//     // $stmt->bindParam(':gameItemCounts', $gameItemCounts, PDO::PARAM_STR);
//     // $stmt->bindParam(':itemsMoney', $itemsMoney, PDO::PARAM_STR);
//     // $stmt->bindParam(':sumMoney', $sumMoney, PDO::PARAM_STR);
//     // $stmt->bindParam(':logintype', $logintype, PDO::PARAM_STR);
//     // $stmt->bindParam(':acount', $acount, PDO::PARAM_STR);
//     // $stmt->bindParam(':password', $password, PDO::PARAM_STR);
//     // $stmt->bindParam(':serverName', $serverName, PDO::PARAM_STR);
//     // $stmt->bindParam(':gameAccountName', $gameAccountName, PDO::PARAM_STR);
//     // $stmt->bindParam(':gameAccountId', $gameAccountId, PDO::PARAM_STR);
//     // $stmt->bindParam(':gameAccountSid', $gameAccountSid, PDO::PARAM_STR);
//     // $stmt->bindParam(':customerSid', $customerSid, PDO::PARAM_STR);
//     // $stmt->bindParam(':status', $status, PDO::PARAM_STR);
//     // $stmt->bindParam(':orderDateTime', $orderDateTime, PDO::PARAM_STR);
//     // $stmt->bindParam(':remark', $gameRemark, PDO::PARAM_STR);
//     // $stmt->execute();
// } catch (Exception $e) {
//     echo $e->getMessage();
// }

// header('Content-Type: application/json');
// echo json_encode('資料庫新增成功');
