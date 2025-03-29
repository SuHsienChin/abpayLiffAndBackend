<?php
// 載入 .env 檔案
$env = parse_ini_file(__DIR__ . '/.env');

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
    if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
        $userId = $event['source']['userId'];
        $messageText = $event['message']['text'];

        // 定義價目表數據
        $priceList = [
            ["name" => "毛孩形象全檔方案", "price" => "NT.5980", "description" => "專業攝影師為您的寵物拍攝完整形象照，包含多種場景和造型。"],
            ["name" => "毛孩親寫真", "price" => "NT.600", "description" => "為您的毛孩拍攝精美的個人寫真，捕捉最自然的一面。"],
            ["name" => "毛孩與你親子寫真", "price" => "NT.1200", "description" => "與毛孩一起入鏡，留下溫馨動人的合照回憶。"],
            ["name" => "毛孩BOOM起來", "price" => "NT.800", "description" => "活力四射的動態拍攝，展現毛孩最活潑的一面。"]
        ];

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
        } 
        // 處理各方案的詳細查詢
        elseif (strpos($messageText, "我想了解") === 0) {
            foreach ($priceList as $item) {
                if ($messageText === "我想了解" . $item["name"]) {
                    if ($item["name"] === "毛孩形象全檔方案") {
                        $flexMessage = [
                            "type" => "flex",
                            "altText" => "毛孩形象全檔方案詳細介紹",
                            "contents" => [
                                "type" => "bubble",
                                "hero" => [
                                    "type" => "image",
                                    "url" => "https://example.com/your-image.jpg", // 請替換為實際圖片網址
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
                                            "text" => "毛孩形象全檔方案",
                                            "weight" => "bold",
                                            "size" => "xl",
                                            "color" => "#1DB446"
                                        ],
                                        [
                                            "type" => "text",
                                            "text" => "NT.5980",
                                            "size" => "lg",
                                            "weight" => "bold",
                                            "margin" => "md"
                                        ],
                                        [
                                            "type" => "box",
                                            "layout" => "vertical",
                                            "margin" => "lg",
                                            "spacing" => "sm",
                                            "contents" => [
                                                ["type" => "text", "text" => "✦ 拍攝時數大約1~1.5hr", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "✦ 檔案當天拍攝全贈", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "✦ 自行挑選精修12張", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "✦ 4G USB", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "✦ 客製放大相框1組", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "✦ 贈每年週年照2張", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "✦ 限定毛孩隻數1隻", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "✦ 家人可一同入鏡(限定2人)", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "✦ 可拍攝三款造型(需自備兩款造型搭配)", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "✦ 引導師協助引導(視情況家人輔助)", "wrap" => true, "size" => "sm"],
                                                ["type" => "separator", "margin" => "md"],
                                                ["type" => "text", "text" => "加購項目：", "weight" => "bold", "margin" => "md", "size" => "sm"],
                                                ["type" => "text", "text" => "• 多加一隻毛孩加收500元", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "• 多一位大人加收1000", "wrap" => true, "size" => "sm"],
                                                ["type" => "text", "text" => "• 如需妝髮加收1200", "wrap" => true, "size" => "sm"]
                                            ]
                                        ],
                                        [
                                            "type" => "button",
                                            "style" => "primary",
                                            "action" => [
                                                "type" => "message",
                                                "label" => "立即預約",
                                                "text" => "預約毛孩形象全檔方案"
                                            ],
                                            "margin" => "lg"
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    } else {
                        // 保持原有的其他方案處理邏輯
                        $flexMessage = [
                            "type" => "flex",
                            "altText" => $item["name"] . "詳細介紹",
                            "contents" => [
                                "type" => "bubble",
                                "body" => [
                                    "type" => "box",
                                    "layout" => "vertical",
                                    "contents" => [
                                        [
                                            "type" => "text",
                                            "text" => $item["name"],
                                            "weight" => "bold",
                                            "size" => "xl",
                                            "color" => "#1DB446"
                                        ],
                                        [
                                            "type" => "text",
                                            "text" => $item["price"],
                                            "size" => "lg",
                                            "weight" => "bold",
                                            "margin" => "md"
                                        ],
                                        [
                                            "type" => "text",
                                            "text" => $item["description"],
                                            "wrap" => true,
                                            "margin" => "lg"
                                        ],
                                        [
                                            "type" => "button",
                                            "style" => "primary",
                                            "action" => [
                                                "type" => "message",
                                                "label" => "立即預約",
                                                "text" => "預約" . $item["name"]
                                            ],
                                            "margin" => "lg"
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    }
                    
                    replyMessage($event['replyToken'], $flexMessage, generateQuickReply($priceList));
                    return;
                }
            }
        } else {
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
    global $env;
    $url = "https://api.line.me/v2/bot/message/reply";
    $headers = [
        "Authorization: Bearer " . $env['LINE_CHANNEL_ACCESS_TOKEN'],
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