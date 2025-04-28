<?php
// 連接資料庫
require_once 'databaseConnection.php';
// 假设您已经连接到数据库

$connection = new DatabaseConnection();
$pdo = $connection->connect();
// 获取订单ID
$orderId = $_GET['orderId'];

// 准备和执行查询
$query = $pdo->prepare('SELECT * FROM orders WHERE orderId = :orderId');
$query->bindParam(':orderId', $orderId, PDO::PARAM_STR);
$query->execute();

$order = $query->fetch(PDO::FETCH_ASSOC);


$orderDetails = "<fieldset class=\"border p-4\">";
$orderDetails .= "<legend class=\"w-auto\">下單資料</legend>";
$orderDetails .= "➤下單日期：<label>" . $order['created_at'] . "</label></br>";
$orderDetails .= "➤遊戲名稱：<label id='gameNameText'>" . $order['gameName'] . "</label></br>";
$orderDetails .= "➤登入方式：<label id='loginType'>" . $order['logintype'] . "</label></br>";
$orderDetails .= "➤遊戲帳號：<label id='gameAccount'>" . $order['acount'] . "</label></br>";
$orderDetails .= "➤遊戲密碼：<label id='loginPassword'>" . $order['password'] . "</label></br>";
$orderDetails .= "➤伺服器：<label id='serverName'>" . $order['serverName'] . "</label></br>";
$orderDetails .= "➤角色名稱：<label id='characters'>" . $order['gameAccountName'] . "</label></br>";
$orderDetails .= "➤ID編號：<label>" . $order['gameAccountId'] . "</label></br>";
$orderDetails .= "➤下單商品確認：</br></br>";
$orderDetails .= "<label id='gameItems'>" . processItemsTxt(json_decode($order['gameItemsName']), $order['gameItemCounts'], $order['itemsMoney']) . "</label></br></br>";
$orderDetails .= "總計: <label id='sumMoney'>" . $order['sumMoney'] . "</label></br> ";
$orderDetails .= "備註: </br><label id='sumMoney'>" . nl2br($order['remark']) . "</label> ";
$orderDetails .= "</fieldset>";

//echo $orderDetails;

echo json_encode(['orderDetails' => $orderDetails]);


function processItemsTxt($gameItemsNames, $gameItemCounts, $itemsMoney)
{
    $gameItemsNames = explode(',', $gameItemsNames);
    $gameItemCounts = explode(',', $gameItemCounts);
    $itemsMoney = explode(',', $itemsMoney);
    $items = '';
    for ($i = 0; $i < count($gameItemsNames); $i++) {
        $items .= $gameItemsNames[$i] . ' X ' . $gameItemCounts[$i] . ' = ' . $itemsMoney[$i] . '<br />';
    }

    return $items;
}
?>