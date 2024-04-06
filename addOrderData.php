<?php

// 連接資料庫
require_once 'databaseConnection.php';

$connection = new DatabaseConnection();
$pdo = $connection->connect();


// UserId: test01
// Password: 111111
// Customer: 6
// GameAccount: 17878
// Item: 1362
// Count: 1
// lineId: null
// customerId: A-DA03
// gameItemsName: ---台人港馬新幣---
// gameItemCounts: 1
// logintype: 登入方式
// acount: 遊戲帳號
// password: 密碼
// serverName: 伺服器
// gameAccountName: 
// gameAccountId: 
// gameAccountSid: 17878
// customerSid: A-DA03
// orderId: 230923-A004

try {

    foreach ($_POST as $key => $value) {
        $$key = $value;
    }
    $gameItemsName = json_encode($gameItemsName);
    // 接收 POST 請求中的 JSON 數據
    //$data = json_decode(file_get_contents('php://input'));

    //var_dump($data);

    // 插入資料到 "orders" 資料表
    $sql = "INSERT INTO orders (lineId, customerId, orderId, gameName, gameItemsName,
     gameItemCounts,itemsMoney,	sumMoney, logintype, acount, password, serverName, gameAccountName,
      gameAccountId, gameAccountSid, customerSid, status, orderDateTime,remark) 
      VALUES 
      (:lineId, :customerId, :orderId, :gameName, :gameItemsName,
      :gameItemCounts,:itemsMoney,	:sumMoney, :logintype, :acount, :password, :serverName, :gameAccountName,
      :gameAccountId, :gameAccountSid, :customerSid, :status, :orderDateTime,:remark)";
    //($lineId, $customerId, $orderId, 'gameName', $gameItemsName, $gameItemCounts, $logintype, $acount, $Password, $serverName, $gameAccountName, $gameAccountId, $gameAccountSid, $customerSid, NULL, NULL);";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':lineId', $lineId, PDO::PARAM_STR);
    $stmt->bindParam(':customerId', $customerId, PDO::PARAM_STR);
    $stmt->bindParam(':orderId', $orderId, PDO::PARAM_STR);
    $stmt->bindParam(':gameName', $gameName, PDO::PARAM_STR);
    $stmt->bindParam(':gameItemsName', $gameItemsName, PDO::PARAM_STR);
    $stmt->bindParam(':gameItemCounts', $gameItemCounts, PDO::PARAM_STR);
    $stmt->bindParam(':itemsMoney', $itemsMoney, PDO::PARAM_STR);
    $stmt->bindParam(':sumMoney', $sumMoney, PDO::PARAM_STR);
    $stmt->bindParam(':logintype', $logintype, PDO::PARAM_STR);
    $stmt->bindParam(':acount', $acount, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':serverName', $serverName, PDO::PARAM_STR);
    $stmt->bindParam(':gameAccountName', $gameAccountName, PDO::PARAM_STR);
    $stmt->bindParam(':gameAccountId', $gameAccountId, PDO::PARAM_STR);
    $stmt->bindParam(':gameAccountSid', $gameAccountSid, PDO::PARAM_STR);
    $stmt->bindParam(':customerSid', $customerSid, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':orderDateTime', $orderDateTime, PDO::PARAM_STR);
    $stmt->bindParam(':remark', $gameRemark, PDO::PARAM_STR);
    $stmt->execute();


    // if ($stmt->execute()) {
    //     $id = $pdo->lastInsertId();
    //     $sql_details = "INSERT INTO order_details (orderId, gameItems, gameItemCounts) VALUES (:orderId, :gameItems, :gameItemCounts)";
    //     $stmt_details = $pdo->prepare($sql_details);
    //     $stmt_details->bindParam(':orderId', $id, PDO::PARAM_STR);
    //     $stmt_details->bindParam(':gameItems', $data->Item, PDO::PARAM_STR);
    //     $stmt_details->bindParam(':gameItemCounts', $data->Count, PDO::PARAM_STR);
    //     $stmt_details->execute();
    // }

    header('Content-Type: application/json');
    echo json_encode('資料庫新增成功');

} catch (Exception $e) {
    echo 'Message:' . $e->getMessage();
}



?>