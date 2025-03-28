<?php
// 取得 LINE 傳送的請求資料
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 驗證請求結構
if (isset($data['events'])) {
    foreach ($data['events'] as $event) {
        handleEvent($event);
    }
}

// 處理單個事件
function handleEvent($event) {
    // 檢查是否為文字訊息
    if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
        $userId = $event['source']['userId'];
        $messageText = $event['message']['text'];

        // 檢查用戶是否傳送 [價目表]
        if (trim($messageText) == "[價目表]") {
            $replyText = "毛孩形象全檔方案 NT.5980\n"
                       . "毛孩親寫真 NT.600\n"
                       . "毛孩與你親子寫真 NT.1200\n"
                       . "毛孩BOOM起來 NT.800";
            replyMessage($event['replyToken'], $replyText);
        } else {
            // 如果不是 [價目表]，回覆其他提示
            replyMessage($event['replyToken'], "請輸入 [價目表] 查看詳細資訊！");
        }
    }
}

// 回覆用戶訊息
function replyMessage($replyToken, $text) {
    $url = "https://api.line.me/v2/bot/message/reply";
    $headers = [
        "Authorization: Bearer /tawKQINYfBLEp75MXH+HMsQ1Hw/IT1UZAnC0nxge0clIvgoBjBUE1Tr+LIhIhIpfa9TfYYgx1pTClW8z1UYK/iALlqXv6NDXe7G5PsemziQxAuDFOGpyHHqxP0b51gMjkz8Kmo0jCULhNm7A4P4VAdB04t89/1O/w1cDnyilFU=",
        "Content-Type: application/json"
    ];
    $body = [
        "replyToken" => $replyToken,
        "messages" => [
            ["type" => "text", "text" => $text]
        ]
    ];

    $options = [
        "http" => [
            "header" => implode("\r\n", $headers),
            "method" => "POST",
            "content" => json_encode($body)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    error_log("回覆結果：" . $response);
}
?>