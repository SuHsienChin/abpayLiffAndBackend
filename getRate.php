<?php
require_once 'getApiJsonClass.php';

$url = 'http://www.adp.idv.tw/api/Rate';
$curlRequest = new CurlRequest($url);
$response = $curlRequest->sendRequest();

$data = json_decode($response, true);


if ($data === null) {
    die("無法取得API資料");
}

header('Content-Type: application/json');
echo json_encode($data);
