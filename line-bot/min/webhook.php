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

        // 檢查用戶是否傳送 價目表
        if (trim($messageText) == "價目表") {
            $flexMessage = [
                "type" => "flex",
                "altText" => "價目表",
                "contents" => [
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
                                "contents" => [
                                    [
                                        "type" => "box",
                                        "layout" => "horizontal",
                                        "contents" => [
                                            ["type" => "text", "text" => "毛孩形象全檔方案", "size" => "sm", "color" => "#555555"],
                                            ["type" => "text", "text" => "NT.5980", "size" => "sm", "color" => "#111111", "align" => "end"]
                                        ]
                                    ],
                                    [
                                        "type" => "box",
                                        "layout" => "horizontal",
                                        "contents" => [
                                            ["type" => "text", "text" => "毛孩親寫真", "size" => "sm", "color" => "#555555"],
                                            ["type" => "text", "text" => "NT.600", "size" => "sm", "color" => "#111111", "align" => "end"]
                                        ]
                                    ],
                                    [
                                        "type" => "box",
                                        "layout" => "horizontal",
                                        "contents" => [
                                            ["type" => "text", "text" => "毛孩與你親子寫真", "size" => "sm", "color" => "#555555"],
                                            ["type" => "text", "text" => "NT.1200", "size" => "sm", "color" => "#111111", "align" => "end"]
                                        ]
                                    ],
                                    [
                                        "type" => "box",
                                        "layout" => "horizontal",
                                        "contents" => [
                                            ["type" => "text", "text" => "毛孩BOOM起來", "size" => "sm", "color" => "#555555"],
                                            ["type" => "text", "text" => "NT.800", "size" => "sm", "color" => "#111111", "align" => "end"]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            
            $body = [
                "replyToken" => $event['replyToken'],
                "messages" => [$flexMessage],
                "quickReply" => [
                    "items" => [
                        [
                            "type" => "action",
                            "action" => [
                                "type" => "message",
                                "label" => "毛孩形象全檔",
                                "text" => "我想了解毛孩形象全檔方案"
                            ]
                        ],
                        [
                            "type" => "action",
                            "action" => [
                                "type" => "message",
                                "label" => "毛孩親寫真",
                                "text" => "我想了解毛孩親寫真"
                            ]
                        ],
                        [
                            "type" => "action",
                            "action" => [
                                "type" => "message",
                                "label" => "毛孩與你親子寫真",
                                "text" => "我想了解毛孩與你親子寫真"
                            ]
                        ],
                        [
                            "type" => "action",
                            "action" => [
                                "type" => "message",
                                "label" => "毛孩BOOM起來",
                                "text" => "我想了解毛孩BOOM起來方案"
                            ]
                        ]
                    ]
                ]
            ];
            
            replyMessage($event['replyToken'], $body, true);
        } else {
            // 如果不是 [價目表]，回覆其他提示
            replyMessage($event['replyToken'], "請輸入 [價目表] 查看詳細資訊！");
        }
    }
}

// 回覆用戶訊息
function replyMessage($replyToken, $content, $isJson = false) {
    $url = "https://api.line.me/v2/bot/message/reply";
    $headers = [
        "Authorization: Bearer /tawKQINYfBLEp75MXH+HMsQ1Hw/IT1UZAnC0nxge0clIvgoBjBUE1Tr+LIhIhIpfa9TfYYgx1pTClW8z1UYK/iALlqXv6NDXe7G5PsemziQxAuDFOGpyHHqxP0b51gMjkz8Kmo0jCULhNm7A4P4VAdB04t89/1O/w1cDnyilFU=",
        "Content-Type: application/json"
    ];
    
    $body = $isJson ? $content : [
        "replyToken" => $replyToken,
        "messages" => [
            ["type" => "text", "text" => $content]
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