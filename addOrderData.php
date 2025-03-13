<?php
require_once 'databaseConnection.php';
require 'vendor/autoload.php';


try {
    $connection = new DatabaseConnection();
    $pdo = $connection->connect();
    
    // 驗證必要欄位
    $requiredFields = ['lineId', 'customerId', 'orderId', 'gameName'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("缺少必要欄位: {$field}");
        }
    }

    // 安全地取得 POST 資料
    $data = [];
    foreach ($_POST as $key => $value) {
        $data[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    // 特殊處理 gameItemsName（因為是 JSON）
    $data['gameItemsName'] = json_encode(
        is_array($_POST['gameItemsName']) ? $_POST['gameItemsName'] : [$_POST['gameItemsName']], 
        JSON_UNESCAPED_UNICODE
    );


    // 插入資料到 orders 資料表
    $sql = "INSERT INTO orders (
        lineId, customerId, orderId, gameName, gameItemsName,
        gameItemCounts, itemsMoney, sumMoney, logintype, acount, 
        password, serverName, gameAccountName, gameAccountId, 
        gameAccountSid, customerSid, status, orderDateTime, remark
    ) VALUES (
        :lineId, :customerId, :orderId, :gameName, :gameItemsName,
        :gameItemCounts, :itemsMoney, :sumMoney, :logintype, :acount,
        :password, :serverName, :gameAccountName, :gameAccountId,
        :gameAccountSid, :customerSid, :status, :orderDateTime, :remark
    )";

    $stmt = $pdo->prepare($sql);
    
    // 綁定參數並執行
    foreach ($data as $key => $value) {
        $stmt->bindValue(":{$key}", $value, PDO::PARAM_STR);
    }
    
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("訂單新增失敗");
    }


    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => '訂單新增成功',
        'orderId' => $data['orderId']
    ]);

} catch (Exception $e) {

    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}