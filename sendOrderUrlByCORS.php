<?php
require_once 'getApiJsonClass.php';

$baseUrl = 'http://www.adp.idv.tw/api/Order';
$params = [];

// 檢查是否有 GET 參數存在
if (!empty($_GET)) {
    // 過濾並驗證參數
    foreach ($_GET as $key => $value) {
        // 對參數進行 URL 編碼
        $params[htmlspecialchars($key)] = $value;
    }
}

try {
    // 構建 URL
    $url = $baseUrl . '?' . http_build_query($params);
    
    $curlRequest = new CurlRequest($url);
    $response = $curlRequest->sendRequest();
    
    $data = json_decode($response, true);
    
    if ($data === null) {
        throw new Exception("API 回傳資料格式錯誤");
    }
    
    // 設定 HTTP 標頭
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    
    // 輸出 JSON 資料
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // 錯誤處理
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
