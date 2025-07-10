<?php
// =================================================================================
// 核心架構區 (源自您提供的成功範本)
// =================================================================================

// 載入環境變數函式
function loadEnv() {
    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    } else {
        error_log("【錯誤】找不到 .env 設定檔！");
        http_response_code(500);
        exit;
    }
}

// 回覆用戶訊息函式
function replyMessage($replyToken, $messages) {
    $url = "https://api.line.me/v2/bot/message/reply";
    $headers = [
        "Authorization: Bearer " . ($_ENV['LINE_CHANNEL_ACCESS_TOKEN'] ?? ''),
        "Content-Type: application/json"
    ];
    $body = [
        "replyToken" => $replyToken,
        "messages" => $messages
    ];
    $options = [
        "http" => [
            "header" => implode("\r\n", $headers),
            "method" => "POST",
            "content" => json_encode($body),
            "ignore_errors" => true // 取得錯誤的回應內容
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    // 紀錄回應結果，方便除錯
    $status_line = $http_response_header[0];
    preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
    $status_code = $match[1];
    if ($status_code != 200) {
        error_log("LINE API 錯誤: " . $status_code . " " . $response);
    }
}

// =================================================================================
// 主程式執行區
// =================================================================================

// 初始化環境變數
loadEnv();

// 檢查必要的配置
if (empty($_ENV['LINE_CHANNEL_ACCESS_TOKEN']) || empty($_ENV['LINE_CHANNEL_SECRET'])) {
    error_log("【錯誤】LINE 金鑰未在 .env 檔案中設定完整。");
    http_response_code(500);
    exit;
}

// 驗證 LINE 的簽章
$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';
$body = file_get_contents('php://input');
if (!hash_equals(base64_encode(hash_hmac('sha256', $body, $_ENV['LINE_CHANNEL_SECRET'], true)), $signature)) {
    error_log("【錯誤】簽章驗證失敗！");
    http_response_code(403);
    exit;
}

// 解析請求
$data = json_decode($body, true);
if (isset($data['events'])) {
    foreach ($data['events'] as $event) {
        handleEvent($event);
    }
}

// 正常回覆 200 OK
http_response_code(200);
echo "OK";

// =================================================================================
// 事件與邏輯處理區 (植入「瑞士刀機器人」功能)
// =================================================================================

// 統一的事件處理函式
function handleEvent($event) {
    // 當機器人被加入群組/社群時的問候
    if ($event['type'] === 'join') {
        $joinMessage = '大家好！我是社群小幫手，很高興為大家服務！' . "\n" . '輸入「功能」或「哈囉」可以查看我的指令喔！';
        replyMessage($event['replyToken'], [['type' => 'text', 'text' => $joinMessage]]);
        return;
    }

    // 如果不是訊息事件，就忽略
    if ($event['type'] !== 'message') {
        return;
    }
    
    // 根據訊息類型做不同處理
    switch ($event['message']['type']) {
        case 'text':
            handleTextMessage($event);
            break;
        case 'sticker':
            $messages = [['type' => 'text', 'text' => '這個貼圖真可愛！']];
            replyMessage($event['replyToken'], $messages);
            break;
        default:
            $messages = [['type' => 'text', 'text' => '收到囉！但我目前還看不懂這個耶～']];
            replyMessage($event['replyToken'], $messages);
            break;
    }
}

// 處理文字訊息的函式
function handleTextMessage($event) {
    $userMessage = strtolower(trim($event['message']['text']));

    switch ($userMessage) {
        case '功能':
        case '哈囉':
        case '你好':
        case 'hi':
        case 'hello':
            $replyText = '哈囉！這是我會的功能：' . "\n" .
                         '➡️ 輸入「活動」：查看最新的優惠活動' . "\n" .
                         '➡️ 輸入「抽一張」：隨機回覆一張可愛的圖片' . "\n" .
                         '➡️ 點擊下方的圖文選單可以快速操作喔！';
            replyMessage($event['replyToken'], [['type' => 'text', 'text' => $replyText]]);
            break;
            
        case '活動':
            $flexMessage = generateActivityFlexMessage();
            replyMessage($event['replyToken'], [$flexMessage]);
            break;
            
        case '抽一張':
            $imageMessage = generateRandomImageMessage();
            replyMessage($event['replyToken'], [$imageMessage]);
            break;
    }
}

// =================================================================================
// 功能函式庫 - 您可以在這裡新增或修改功能
// =================================================================================

// 產生隨機圖片訊息
function generateRandomImageMessage() {
    $imageUrls = [
        'https://i.imgur.com/z4OJM7u.jpeg', // 貓咪1
        'https://i.imgur.com/2Mv4N3j.jpeg', // 貓咪2
        'https://i.imgur.com/R3r41GE.jpeg'  // 狗狗
    ];
    $randomIndex = array_rand($imageUrls);
    $selectedImageUrl = $imageUrls[$randomIndex];
    
    return [
        'type' => 'image',
        'originalContentUrl' => $selectedImageUrl,
        'previewImageUrl' => $selectedImageUrl
    ];
}

// 產生「活動」的 Flex Message
function generateActivityFlexMessage() {
    // 前往 LINE Flex Message Simulator (https://developers.line.biz/flex-simulator/) 設計你的卡片
    // 然後將產生的 JSON 結構，轉換成 PHP 陣列格式貼到這裡
    return [
        "type" => "flex",
        "altText" => "夏日清涼祭典活動通知",
        "contents" => [
            "type" => "bubble",
            "hero" => ["type" => "image", "url" => "https://i.imgur.com/4QJjKqM.jpeg", "size" => "full", "aspectRatio" => "20:13", "aspectMode" => "cover"],
            "body" => [
                "type" => "box", "layout" => "vertical", "contents" => [
                    ["type" => "text", "text" => "夏日清涼祭典", "weight" => "bold", "size" => "xl"],
                    ["type" => "box", "layout" => "vertical", "margin" => "lg", "spacing" => "sm", "contents" => [
                        ["type" => "box", "layout" => "baseline", "spacing" => "sm", "contents" => [
                            ["type" => "text", "text" => "地點", "color" => "#aaaaaa", "size" => "sm", "flex" => 1],
                            ["type" => "text", "text" => "LINE 公園中央廣場", "wrap" => true, "color" => "#666666", "size" => "sm", "flex" => 5]
                        ]],
                        ["type" => "box", "layout" => "baseline", "spacing" => "sm", "contents" => [
                            ["type" => "text", "text" => "時間", "color" => "#aaaaaa", "size" => "sm", "flex" => 1],
                            ["type" => "text", "text" => "8/1 (五) 10:00 - 18:00", "wrap" => true, "color" => "#666666", "size" => "sm", "flex" => 5]
                        ]]
                    ]]
                ]
            ],
            "footer" => ["type" => "box", "layout" => "vertical", "spacing" => "sm", "contents" => [
                ["type" => "button", "style" => "link", "height" => "sm", "action" => ["type" => "uri", "label" => "查看官網", "uri" => "https://line.me"]],
                ["type" => "button", "style" => "link", "height" => "sm", "action" => ["type" => "uri", "label" => "分享給朋友", "uri" => "https://line.me/R/share?text=快來參加夏日清涼祭典！"]]
            ], "flex" => 0]
        ]
    ];
}
?>