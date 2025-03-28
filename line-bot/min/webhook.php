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

        // 定義價目表數據
        $priceList = [
            ["name" => "毛孩形象全檔方案", "price" => "NT.5980"],
            ["name" => "毛孩親寫真", "price" => "NT.600"],
            ["name" => "毛孩與你親子寫真", "price" => "NT.1200"],
            ["name" => "毛孩BOOM起來", "price" => "NT.800"]
        ];

        // 檢查用戶是否傳送「價目表」
        if (trim($messageText) == "價目表") {
            // 生成 Flex Message
            $flexMessage = generateFlexMessage($priceList);

            // 生成 Quick Reply
            $quickReply = generateQuickReply($priceList);

            // 發送回覆
            replyMessage($event['replyToken'], [
                "type" => "flex",
                "altText" => "價目表",
                "contents" => $flexMessage
            ], $quickReply);
        } else {
            // 如果不是「價目表」，回覆其他提示
            replyMessage($event['replyToken'], "請輸入「價目表」查看詳細資訊！");
        }
    }
}

// 生成 Flex Message
function generateFlexMessage($priceList) {
    $contents = [];
    foreach ($priceList as $item) {
        $contents[] = [
            "type" => "box",
            "layout" => "horizontal",
            "contents" => [
                ["type" => "text", "text" => $item["name"], "size" => "sm", "color" => "#555555"],
                ["type" => "text", "text" => $item["price"], "size" => "sm", "color" => "#111111", "align" => "end"]
            ]
        ];
    }

    return [
        "type" => "bubble",
        "body" => [
            "type" => "box",
            "layout" => "vertical",
            "contents" => [
                [
                    "type" => "text",
                    "text" => "寵物攝影服務價目表",
                    "weight" => "bold",
                    "size" => "xl",
                    "color" => "#1DB446"
                ],
                [
                    "type" => "separator",
                    "margin" => "xxl"
                ],
                [
                    "type" => "box",
                    "layout" => "vertical",
                    "margin" => "xxl",
                    "spacing" => "sm",
                    "contents" => $contents
                ]
            ]
        ]
    ];
}

// 生成 Quick Reply
function generateQuickReply($priceList) {
    $items = [];
    foreach ($priceList as $item) {
        $items[] = [
            "type" => "action",
            "action" => [
                "type" => "message",
                "label" => $item["name"],
                "text" => "我想了解" . $item["name"]
            ]
        ];
    }

    return [
        "items" => $items
    ];
}

// 回覆用戶訊息
function replyMessage($replyToken, $message, $quickReply = null) {
    $url = "https://api.line.me/v2/bot/message/reply";
    $headers = [
        "Authorization: Bearer /tawKQINYfBLEp75MXH+HMsQ1Hw/IT1UZAnC0nxge0clIvgoBjBUE1Tr+LIhIhIpfa9TfYYgx1pTClW8z1UYK/iALlqXv6NDXe7G5PsemziQxAuDFOGpyHHqxP0b51gMjkz8Kmo0jCULhNm7A4P4VAdB04t89/1O/w1cDnyilFU=",
        "Content-Type: application/json"
    ];

    // 組合回覆內容
    $body = [
        "replyToken" => $replyToken,
        "messages" => [$message]
    ];

    // 如果有 Quick Reply，加入到回覆中
    if ($quickReply) {
        $body["messages"][0]["quickReply"] = $quickReply;
    }

    $options = [
        "http" => [
            "header" => implode("\r\n", $headers),
            "method" => "POST",
            "content" => json_encode($body)
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    // 錯誤處理
    if ($response === FALSE) {
        error_log("API 請求失敗：" . print_r($http_response_header, true));
    } else {
        error_log("回覆結果：" . $response);
    }
}
?>