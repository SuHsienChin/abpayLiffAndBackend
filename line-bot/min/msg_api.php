<?php
function pushMessage($userId, $text) {
    $url = "https://api.line.me/v2/bot/message/push";
    $headers = [
        "Authorization: /tawKQINYfBLEp75MXH+HMsQ1Hw/IT1UZAnC0nxge0clIvgoBjBUE1Tr+LIhIhIpfa9TfYYgx1pTClW8z1UYK/iALlqXv6NDXe7G5PsemziQxAuDFOGpyHHqxP0b51gMjkz8Kmo0jCULhNm7A4P4VAdB04t89/1O/w1cDnyilFU=",
        "Content-Type: application/json"
    ];
    $body = [
        "to" => $userId,
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
    error_log("推播結果：" . $response);
}

// 測試推播
pushMessage("USER_ID", "這是一則推播訊息！");
