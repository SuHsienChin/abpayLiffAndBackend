<?php
header('Content-Type: application/json');

try {
    // 獲取訂單資料（支持 POST 和 GET 請求）
    $itemId = trim($_REQUEST['item_id'] ?? '');
    $itemCount = trim($_REQUEST['quantity'] ?? '');
    $customerSid = trim($_REQUEST['customer_id'] ?? '');
    $gameAccount = trim($_REQUEST['game_account'] ?? '');
    $url = 'http://www.adp.idv.tw/api/Order?UserId=test02&Password=3345678';

    if (!$itemId || !$itemCount || !$customerSid || !$gameAccount) {
        throw new Exception('缺少必要參數');
    }

    // 驗證參數合法性
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $itemId)) {
        throw new Exception('商品ID包含不合法字符');
    }
    if (!is_numeric($itemCount) || $itemCount <= 0) {
        throw new Exception('商品數量必須為正整數');
    }
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $customerSid)) {
        throw new Exception('客戶ID包含不合法字符');
    }
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $gameAccount)) {
        throw new Exception('遊戲帳號包含不合法字符');
    }

    // 構建API請求URL
    $apiUrl = $url . '&Customer=' . urlencode($customerSid) 
                  . '&GameAccount=' . urlencode($gameAccount) 
                  . '&Item=' . urlencode($itemId) 
                  . '&Count=' . urlencode($itemCount);

    // 發送API請求
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        throw new Exception('API請求失敗：' . curl_error($ch));
    }

    if ($httpCode !== 200) {
        throw new Exception('API返回異常狀態碼：' . $httpCode);
    }

    $result = json_decode($response, true);
    if ($result === null) {
        throw new Exception('API返回數據格式錯誤');
    }

    if (!isset($result['OrderId']) || !isset($result['Status'])) {
        throw new Exception('API返回數據缺少必要欄位');
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'order_id' => $result['OrderId'],
            'status' => $result['Status']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'params' => [
            'item_id' => $itemId ?? '',
            'quantity' => $itemCount ?? '',
            'customer_id' => $customerSid ?? '',
            'game_account' => $gameAccount ?? ''
        ]
    ]);
}