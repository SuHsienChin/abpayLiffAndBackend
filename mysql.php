<?php
// 連接資料庫
$host = '1.164.12.193';
$username = 'abpay';
$password = 'Aa.730216';
$database = 'abpay';

$mysqli = new mysqli($host, $username, $password, $database);

// 檢查連線是否成功
if ($mysqli->connect_error) {
    die("連線失敗: " . $mysqli->connect_error);
}

echo "連線成功";

// 關閉資料庫連線
$mysqli->close();