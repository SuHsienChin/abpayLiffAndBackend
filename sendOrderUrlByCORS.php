<?php
require_once 'getApiJsonClass.php';

$url = 'http://www.adp.idv.tw/api/Order?';

$request_body = file_get_contents('php://input');
$post_data = json_decode($request_body, true);

$UserId = 'test02';
$Password = '3345678';
$Customer = $post_data['Customer'];
$GameAccount = $post_data['GameAccount'];
$Item = $post_data['Item'];
$Count = $post_data['Count'];
$Note0 = $_post_dataPOST['Note0'];

$fields = [
    'UserId' => $UserId,
    'Password' => $Password,
    'Customer' => $Customer,
    'GameAccount' => $GameAccount,
    'Item' => $Item,
    'Count' => $Count,
    'Note0' => $Note0
];

$postdata = http_build_query($fields);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);

$data = json_decode($result, true);

if ($data === null) {
    die ("無法取得API資料");
}
ob_start();
header('Content-Type: application/json');
ob_end_flush();
echo json_encode($data);