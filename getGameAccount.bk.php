<?php
require_once 'getApiJsonClass.php';
require_once 'ApiLogger.php';

$url = 'http://www.adp.idv.tw/api/GameAccount?Sid=' . $_GET["Sid"];
$curlRequest = new CurlRequest($url);
$response = $curlRequest->sendRequest();

$data = json_decode($response, true);

if ($data === null) {
    // 記錄失敗的API請求
    ApiLogger::logApiRequest('getGameAccount.bk.php', $url, ['sid' => $_GET["Sid"]], '', false);
    die("無法取得API資料");
}

// 記錄成功的API請求
ApiLogger::logApiRequest('getGameAccount.bk.php', $url, ['sid' => $_GET["Sid"]], $response, true);

header('Content-Type: application/json');
echo json_encode($data);