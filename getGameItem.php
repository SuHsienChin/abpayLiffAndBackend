<?php
/**
 * 遊戲物品API接口
 * 直接從API獲取遊戲物品數據，不使用緩存
 * 
 * @param string $Sid 遊戲服務器ID
 * @return string JSON格式的遊戲物品數據
 */
require_once 'getApiJsonClass.php';
require_once 'ApiLogger.php';

try {
    // 獲取請求參數
    $sid = $_GET["Sid"];
    
    // 從API獲取新數據
    $url = 'http://www.adp.idv.tw/api/GameItem?Sid=' . $sid;
    $curlRequest = new CurlRequest($url);
    $response = $curlRequest->sendRequest();
    
    $data = json_decode($response, true);
    
    if ($data === null) {
        // 記錄失敗的API請求
        ApiLogger::logApiRequest('getGameItem.php', $url, ['sid' => $sid], '', false);
        throw new Exception("無法取得API資料");
    }
    
    // 記錄成功的API請求
    ApiLogger::logApiRequest('getGameItem.php', $url, ['sid' => $sid], $response, true, 'api');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/json');
echo json_encode($data);
} catch (Throwable $e) {
    http_response_code(500);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: application/json');
    ApiLogger::logApiRequest('getGameItem.php', 'internal://exception', ['sid' => isset($sid) ? $sid : null], $e->getMessage(), false, 'internal');
    echo json_encode([
        'success' => false,
        'message' => '伺服器發生錯誤，請稍後再試',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}