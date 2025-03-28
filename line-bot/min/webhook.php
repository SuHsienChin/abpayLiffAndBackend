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

        // 定義價目表數據與回覆內容
        $services = [
            "毛孩形象全檔方案" => [
                "title" => "毛孩形象全檔方案",
                "description" => "記錄毛孩的每一個精彩瞬間！\n價格：NT.5980",
                "image_url" => "https://example.com/images/full_package.jpg"
            ],
            "毛孩親寫真" => [
                "title" => "毛孩親寫真",
                "description" => "專屬於毛孩的個人寫真集！\n價格：NT.600",
                "image_url" => "https://example.com/images/pet_portrait.jpg"
            ],
            "毛孩與你親子寫真" => [
                "title" => "毛孩與你親子寫真",
                "description" => "毛孩與主人的溫馨合照！\n價格：NT.1200",
                "image_url" => "https://example.com/images/family_portrait.jpg"
            ],
            "毛孩BOOM起來" => [
                "title" => "毛孩BOOM起來",
                "description" => "讓毛孩成為焦點的創意拍攝！\n價格：NT.800",
                "image_url" => "https://example.com/images/boom_pet.jpg"
            ]
        ];

        // 檢查用戶訊息是否符合格式
        foreach ($services as $key => $service) {
            if (strpos($messageText, "我想了解" . $key) !== false) {
                // 生成 Flex Message
                $flexMessage = generateServiceFlexMessage($service);
                replyMessage($event['replyToken'], $flexMessage);
                return;
            }
        }

        // 如果不是指定的訊息，回覆提示
        replyMessage($event['replyToken'], "請輸入正確的查詢格式，例如：我想了解毛孩形象全檔方案！");
    }
}

// 生成服務的 Flex Message
function generateServiceFlexMessage($service) {
    return [
        "type" => "flex",
        "altText" => $service["title"],
        "contents" => [
            "type" => "bubble",
            "hero" => [
                "type" => "image",
                "url" => $service["image_url"],
                "size" => "full",
                "aspectRatio" => "20:13",
                "aspectMode" => "cover"
            ],
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => $service["title"],
                        "weight" => "bold",
                        "size" => "xl",
                        "color" => "#1DB446"
                    ],
                    [
                        "type" => "separator",
                        "margin" => "xxl"
                    ],
                    [
                        "type" => "text",
                        "text" => $service["description"],
                        "wrap" => true,
                        "size" => "sm",
                        "color" => "#555555",
                        "margin" => "md"
                    ]
                ]
            ],
            "footer" => [
                "type" => "box",
                "layout" => "horizontal",
                "contents" => [
                    [
                        "type" => "button",
                        "action" => [
                            "type" => "uri",
                            "label" => "立即預約",
                            "uri" => "https://example.com/book-now"
                        ],
                        "style" => "primary",
                        "color" => "#1DB446"
                    ]
                ]
            ]
        ]
    ];
}

// 回覆用戶訊息
function replyMessage($replyToken, $message) {
    $url = "https://api.line.me/v2/bot/message/reply";
    $headers = [
        "Authorization: Bearer /tawKQINYfBLEp75MXH+HMsQ1Hw/IT1UZAnC0nxge0clIvgoBjBUE1Tr+LIhIhIpfa9TfYYgx1pTClW8z1UYK/iALlqXv6NDXe7G5PsemziQxAuDFOGpyHHqxP0b51gMjkz8Kmo0jCULhNm7A4P4VAdB04t89/1O/w1cDnyilFU=",
        "Content-Type: application/json"
    ];

    $body = [
        "replyToken" => $replyToken,
        "messages" => [$message]
    ];

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