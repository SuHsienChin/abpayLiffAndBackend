<?php
require_once 'getApiJsonClass.php';
require_once 'ApiLogger.php';

$url = 'http://www.adp.idv.tw/api/Customer?Line=' . $_GET["lineId"];
$curlRequest = new CurlRequest($url);
$response = $curlRequest->sendRequest();

$data = json_decode($response, true);

if ($data === null) {
    // 記錄失敗的API請求
    ApiLogger::logApiRequest('getCustomer.bk.php', $url, ['lineId' => $_GET["lineId"]], '', false);
    die("無法取得API資料");
}

// 記錄成功的API請求
ApiLogger::logApiRequest('getCustomer.bk.php', $url, ['lineId' => $_GET["lineId"]], $response, true);

header('Content-Type: application/json');
echo json_encode($data);