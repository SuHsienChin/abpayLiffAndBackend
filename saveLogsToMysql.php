<?php

// 連接資料庫
require_once 'databaseConnection.php';

$connection = new DatabaseConnection();
$pdo = $connection->connect();


$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// 檢查是否有 API URL 參數
$api_url = '';
if (isset($data['api_url'])) {
  // 直接使用從 JavaScript 傳遞過來的 API URL
  $api_url = $data['api_url'];
} else if (isset($data['JSON'])) {
  // 如果沒有直接提供 API URL，則嘗試從 JSON 數據中構建
  $json_data = json_decode($data['JSON'], true);
  if ($json_data && isset($json_data['UserId']) && isset($json_data['Password'])) {
    $api_url = 'http://www.adp.idv.tw/api/Order?';
    $params = [];
    
    // 添加必要的參數
    if (isset($json_data['UserId'])) $params[] = 'UserId=' . $json_data['UserId'];
    if (isset($json_data['Password'])) $params[] = 'Password=' . $json_data['Password'];
    if (isset($json_data['Customer'])) $params[] = 'Customer=' . $json_data['Customer'];
    if (isset($json_data['GameAccount'])) $params[] = 'GameAccount=' . $json_data['GameAccount'];
    if (isset($json_data['Item'])) $params[] = 'Item=' . $json_data['Item'];
    if (isset($json_data['Count'])) $params[] = 'Count=' . $json_data['Count'];
    
    // 將參數組合成 URL
    $api_url .= implode('&', $params);
  }
}

try {
  var_dump($data['JSON']);
  //插入資料到 "system_logs" 資料表，增加 api_url 欄位
  $sql = "INSERT INTO system_logs (type, JSON, api_url) 
      VALUES 
      (:type, :JSON, :api_url)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
  $stmt->bindParam(':JSON', $data['JSON'], PDO::PARAM_STR);
  $stmt->bindParam(':api_url', $api_url, PDO::PARAM_STR);
  $stmt->execute();
} catch (Exception $e) {
  echo "saveLogsToMysql.php發生錯誤" . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode('資料庫新增成功');
