<?php

// 載入環境變數
function loadEnv() {
    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $_ENV[$key] = $value;
        }
    } else {
        error_log("警告：未找到 .env 文件");
        exit;
    }
}

// 初始化環境變數
loadEnv();

// 設定 LINE 配置
define('LINE_CONFIG', [
    'LINE_CHANNEL_ACCESS_TOKEN' => $_ENV['LINE_CHANNEL_ACCESS_TOKEN'] ?? '',
    'LINE_CHANNEL_SECRET' => $_ENV['LINE_CHANNEL_SECRET'] ?? ''
]);

// 檢查必要的配置
if (empty(LINE_CONFIG['LINE_CHANNEL_ACCESS_TOKEN']) || empty(LINE_CONFIG['LINE_CHANNEL_SECRET'])) {
    error_log("錯誤：LINE 配置未設定完整");
    exit;
}

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
            ["name" => "毛腳腳單張系列", "price" => "NT.2980", "description" => "毛腳腳單張系列。", "title" => "毛腳腳單張系列"],
            ["name" => "毛腳腳合成系列", "price" => "NT.3980", "description" => "毛腳腳合成系列。", "title" => "毛腳腳合成系列"],
            ["name" => "毛孩形象全檔方案", "price" => "NT.5980", "description" => "專業攝影師為您的寵物拍攝完整形象照，包含多種場景和造型。", "title" => "專業形象攝影"],
            ["name" => "毛孩親寫真", "price" => "NT.600", "description" => "為您的毛孩拍攝精美的個人寫真，捕捉最自然的一面。", "title" => "個人寫真精選"],
            ["name" => "毛孩與你親子寫真", "price" => "NT.1200", "description" => "與毛孩一起入鏡，留下溫馨動人的合照回憶。", "title" => "溫馨親子合照"],
            ["name" => "毛孩BOOM起來", "price" => "NT.800", "description" => "活力四射的動態拍攝，展現毛孩最活潑的一面。", "title" => "動態活力拍攝"]            
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
                    $content = "";
                    $imagePath = "";
                    
                    switch($item["name"]) {
                        case "毛孩形象全檔方案":
                            $content = "NT.5980\n\n拍攝時數大約1~1.5hr\n檔案當天拍攝全贈\n自行挑選精修12張\n4G USB\n客製放大相框1組\n贈每年週年照2張\n限定毛孩隻數1隻\n家人可一同入鏡(限定2人)\n可拍攝三款造型(需自備兩款造型搭配)\n引導師協助引導(視情況家人輔助)\n\n加購項目：\n多加一隻毛孩加收500元\n多一位大人加收1000\n如需妝髮加收1200";
                            $imagePath = "https://abpay.tw/line-bot/min/images/allfile.jpg";
                            break;
                        case "毛孩親寫真":
                            $content = "少張數的單拍方案\n僅限一隻毛孩拍攝\n2隻毛孩需兩個方案\nNT.600";
                            $imagePath = "https://abpay.tw/line-bot/min/images/onepet.jpg";
                            break;
                        case "毛孩與你親子寫真":
                            $content = "少張數單拍方案(限一隻毛孩)\nNT.1200\n加購項目:\n多加一隻毛孩加收500元\n多一位大人加收1000\n如需妝髮加收1200";
                            $imagePath = "https://abpay.tw/line-bot/min/images/famile_and_pet.jpg";
                            break;
                        case "毛孩BOOM起來":
                            $content = "爆破系列拍攝\n拍攝詳情需了解discussion\nNT.800\n多加一隻毛孩500元";
                            $imagePath = "https://abpay.tw/line-bot/min/images/boom.jpg";
                            break;
                        case "毛腳腳單張系列":
                            $content = "毛腳腳單張系列\n毛腳腳照片1張\n可自行挑選顏色\n12时照片含框\n贈全家福拍攝(限2位)\nNT.2980";
                            $imagePath = "https://abpay.tw/line-bot/min/images/70381.jpg";
                            break;
                        case "毛腳腳合成系列":
                            $content = "毛腳腳合成系列\n毛腳腳照片7張\n可自行挑選顏色\n12时照片含框\n贈全家福拍攝(限2位)\nNT.3980";
                            $imagePath = "https://abpay.tw/line-bot/min/images/70379.jpg";
                            break;
                    }

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
                                        "type" => "image",
                                        "url" => $imagePath,
                                        "size" => "full",
                                        "aspectMode" => "cover",
                                        "aspectRatio" => "1:1",
                                        "gravity" => "center"
                                    ],
                                    [
                                        "type" => "box",
                                        "layout" => "vertical",
                                        "contents" => [
                                            [
                                                "type" => "text",
                                                "text" => $item["name"],
                                                "weight" => "bold",
                                                "size" => "xl",
                                                "color" => "#1DB446",
                                                "wrap" => true
                                            ],
                                            [
                                                "type" => "text",
                                                "text" => $item["title"],
                                                "weight" => "bold",
                                                "size" => "lg",
                                                "color" => "#000000",
                                                "wrap" => true,
                                                "margin" => "md"
                                            ],
                                            [
                                                "type" => "text",
                                                "text" => $content,
                                                "wrap" => true,
                                                "margin" => "lg",
                                                "size" => "md",
                                                "color" => "#666666"
                                            ]
                                        ],
                                        "paddingAll" => "lg"
                                    ]
                                ]
                            ],
                            "footer" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => [
                                    [
                                        "type" => "button",
                                        "style" => "primary",
                                        "action" => [
                                            "type" => "message",
                                            "label" => "立即預約",
                                            "text" => "預約" . $item["name"]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                    
                    replyMessage($event['replyToken'], $flexMessage, generateQuickReply($priceList));
                    return;
                }
            }
        } 
        // 處理預約請求
        elseif (strpos($messageText, "預約拍攝") === 0) {
            // 找出對應的方案
            foreach ($priceList as $item) {
                if ($messageText === "預約拍攝" . $item["name"]) {
                    // 回覆預約表單和付款資訊
                    $replyText = "確認訂購的話\n麻煩先幫我留個資料唷\n姓名：\n電話：\n地址：\n寵物品種：\n主人入鏡人數：\nEmail：\n拍攝地點：\n\n第一銀行(007)\n帳號：14757038557\n\n匯款完成後再麻煩跟我們說後五碼唷";
                    
                    replyMessage($event['replyToken'], [
                        "type" => "text",
                        "text" => $replyText
                    ]);
                    return;
                }
            }
        } elseif (trim($messageText) == "常見問題") {
            $faqMessage = [
                "type" => "flex",
                "altText" => "常見問題",
                "contents" => [
                    "type" => "bubble",
                    "body" => [
                        "type" => "box",
                        "layout" => "vertical",
                        "contents" => [
                            [
                                "type" => "text",
                                "text" => "常見問題",
                                "weight" => "bold",
                                "size" => "xl",
                                "color" => "#1DB446",
                                "margin" => "md"
                            ],
                            [
                                "type" => "box",
                                "layout" => "vertical",
                                "margin" => "lg",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "Q: 如果想外拍會在哪裡?",
                                        "weight" => "bold",
                                        "color" => "#1DB446",
                                        "size" => "md",
                                        "wrap" => true
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "A: 外拍地點在工作室後面走路3分鐘",
                                        "size" => "sm",
                                        "color" => "#666666",
                                        "wrap" => true,
                                        "margin" => "sm"
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "Q: 我們家寶貝很失控可以拍嗎?",
                                        "weight" => "bold",
                                        "color" => "#1DB446",
                                        "size" => "md",
                                        "wrap" => true,
                                        "margin" => "lg"
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "A: 貼心小建議 拍攝前可以先帶寶貝先去放電喔",
                                        "size" => "sm",
                                        "color" => "#666666",
                                        "wrap" => true,
                                        "margin" => "sm"
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "Q: 只有棚拍嗎?還是可以戶外拍?",
                                        "weight" => "bold",
                                        "color" => "#1DB446",
                                        "size" => "md",
                                        "wrap" => true,
                                        "margin" => "lg"
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "A: 我們內外景都有喔外景拍攝我們全程牽繩拍攝",
                                        "size" => "sm",
                                        "color" => "#666666",
                                        "wrap" => true,
                                        "margin" => "sm"
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "Q: 會有攝影助理引導師嗎?",
                                        "weight" => "bold",
                                        "color" => "#1DB446",
                                        "size" => "md",
                                        "wrap" => true,
                                        "margin" => "lg"
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "A: 會喔",
                                        "size" => "sm",
                                        "color" => "#666666",
                                        "wrap" => true,
                                        "margin" => "sm"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            replyMessage($event['replyToken'], $faqMessage);
        } elseif (trim($messageText) == "溫馨提醒") {
            $reminderMessage = [
                "type" => "flex",
                "altText" => "溫馨提醒",
                "contents" => [
                    "type" => "bubble",
                    "body" => [
                        "type" => "box",
                        "layout" => "vertical",
                        "contents" => [
                            [
                                "type" => "text",
                                "text" => "溫馨提醒",
                                "weight" => "bold",
                                "size" => "xl",
                                "color" => "#1DB446",
                                "margin" => "md"
                            ],
                            [
                                "type" => "box",
                                "layout" => "vertical",
                                "margin" => "lg",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "1. 拍攝時間約1.5～2小時，依照寶貝當天狀況而定喔\n2. 如果寶貝屬精力旺盛好動型，建議拍攝前先帶寶貝消耗一些體力，會讓拍攝過程更流暢些喔\n3. 拍攝日前1~2天內洗澡美容拍攝效果最佳喔\n4. 重點整理：以臉上毛髮不遮到眼睛為主\n5. 寶貝睡飽喝飽，脫光光到現場拍照即可，如要自備寶貝入鏡的衣服配件可帶來唷\n6. 可以提早5~10分鐘到攝影棚，讓寶貝有時間認識環境\n7. 準備一些毛寶貝習慣的玩偶、玩具、食物...等等\n8. 建議隨身攜帶個人衛生物品：飲水器、寵物梳、禮貌帶唷",
                                        "wrap" => true,
                                        "size" => "sm"
                                    ],
                                    
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            replyMessage($event['replyToken'], $reminderMessage);
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
// 將 Token 移至 .env 檔案中


function replyMessage($replyToken, $message, $quickReply = null) {
    $url = "https://api.line.me/v2/bot/message/reply";
    $headers = [
        "Authorization: Bearer " . LINE_CONFIG['LINE_CHANNEL_ACCESS_TOKEN'],
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
    $response = file_get_contents($url, false, $context);
    
    if ($response === FALSE) {
        throw new Exception("API 請求失敗：" . error_get_last()['message']);
    }
    
    $result = json_decode($response, true);
    if (isset($result['message'])) {
        throw new Exception("LINE API 錯誤：" . $result['message']);
    }
    
    error_log("回覆成功：" . $response);
}


// 在檔案開頭加入
if (!isset($_SERVER['HTTP_X_LINE_SIGNATURE'])) {
    http_response_code(403);
    exit;
}

// 驗證 LINE 的簽章
$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];
$body = file_get_contents('php://input');
if (!hash_equals(base64_encode(hash_hmac('sha256', $body, LINE_CONFIG['LINE_CHANNEL_SECRET'], true)), $signature)) {
    http_response_code(403);
    exit;
}

$data = json_decode($body, true);
