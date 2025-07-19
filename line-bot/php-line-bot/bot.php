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
function replyMessage($replyToken, $messages, $quickReply = null) {
    $url = "https://api.line.me/v2/bot/message/reply";
    $headers = [
        "Authorization: Bearer " . ($_ENV['LINE_CHANNEL_ACCESS_TOKEN'] ?? ''),
        "Content-Type: application/json"
    ];
    $body = [
        "replyToken" => $replyToken,
        "messages" => $messages
    ];

    if ($quickReply) {
        // 確保第一個訊息物件存在
        if (isset($body["messages"][0])) {
            $body["messages"][0]["quickReply"] = $quickReply;
        }
    }

    $options = [
        "http" => [
            "header" => implode("\r\n", $headers),
            "method" => "POST",
            "content" => json_encode($body),
            "ignore_errors" => true
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    $status_line = $http_response_header[0] ?? 'HTTP/1.1 500 Internal Server Error';
    preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
    $status_code = $match[1] ?? 500;
    if ($status_code != 200) {
        error_log("LINE API 錯誤: " . $status_code . " " . $response);
    }
}

// =================================================================================
// 主程式執行區
// =================================================================================

loadEnv();

if (empty($_ENV['LINE_CHANNEL_ACCESS_TOKEN']) || empty($_ENV['LINE_CHANNEL_SECRET'])) {
    error_log("【錯誤】LINE 金鑰未在 .env 檔案中設定完整。");
    http_response_code(500);
    exit;
}

$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';
$body = file_get_contents('php://input');
if (!hash_equals(base64_encode(hash_hmac('sha256', $body, $_ENV['LINE_CHANNEL_SECRET'], true)), $signature)) {
    error_log("【錯誤】簽章驗證失敗！");
    http_response_code(403);
    exit;
}

$data = json_decode($body, true);
if (isset($data['events'])) {
    foreach ($data['events'] as $event) {
        handleEvent($event);
    }
}

http_response_code(200);
echo "OK";


// =================================================================================
// 事件與邏輯處理區 (已整合新舊功能)
// =================================================================================

function handleEvent($event) {
    // 當機器人被加入群組/社群時的問候
    if ($event['type'] === 'join') {
        $joinMessage = '大家好！我是社群小幫手，很高興為大家服務！' . "\n" . '輸入「功能」或「哈囉」可以查看我的指令喔！';
        replyMessage($event['replyToken'], [['type' => 'text', 'text' => $joinMessage]]);
        return;
    }

    if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
        $messageText = trim($event['message']['text']);

        // 定義價目表數據 (來自您的腳本)
        $priceList = [
            ["name" => "毛孩形象全檔方案", "price" => "NT.5980", "description" => "專業攝影師為您的寵物拍攝完整形象照...", "title" => "專業形象攝影"],
            ["name" => "毛孩親寫真", "price" => "NT.600", "description" => "為您的毛孩拍攝精美的個人寫真...", "title" => "個人寫真精選"],
            ["name" => "毛孩與你親子寫真", "price" => "NT.1200", "description" => "與毛孩一起入鏡，留下溫馨動人的合照...", "title" => "溫馨親子合照"],
            ["name" => "毛孩BOOM起來", "price" => "NT.800", "description" => "活力四射的動態拍攝，展現毛孩最活潑的一面...", "title" => "動態活力拍攝"]
        ];
        
        // ---- 整合「瑞士刀機器人」的關鍵字 ----
        $swissKnifeKeywords = ['功能', '哈囉', '你好', 'hi', 'hello'];
        if (in_array(strtolower($messageText), $swissKnifeKeywords)) {
            $replyText = '哈囉！這是我會的功能：' . "\n" .
                         '➡️ 輸入「價目表」：查看攝影服務項目' . "\n" .
                         '➡️ 輸入「活動」：查看最新的優惠活動' . "\n" .
                         '➡️ 輸入「抽一張」：隨機回覆一張可愛的圖片';
            replyMessage($event['replyToken'], [['type' => 'text', 'text' => $replyText]]);
            return;
        }
        if (strtolower($messageText) === '活動') {
            replyMessage($event['replyToken'], [generateActivityFlexMessage()]);
            return;
        }
        if (strtolower($messageText) === '抽一張') {
            replyMessage($event['replyToken'], [generateRandomImageMessage()]);
            return;
        }
        // ---- 瑞士刀功能結束 ----


        // ---- 您原有的價目表邏輯 ----
        if ($messageText == "價目表") {
            $flexMessage = generatePriceListFlexMessage($priceList); // 使用您的函式
            $quickReply = generateQuickReply($priceList); // 使用您的函式
            replyMessage($event['replyToken'], [$flexMessage], $quickReply);
        } elseif (strpos($messageText, "我想了解") === 0) {
            // ... (此處省略您原本的「我想了解」詳細回覆邏輯，因為它已經很完整)
        } elseif (strpos($messageText, "預約") === 0) {
            // ... (此處省略您原本的「預約」回覆邏輯)
        } elseif ($messageText == "常見問題") {
            // ... (此處省略您原本的「常見問題」回覆邏輯)
        }
        // ---- 價目表邏輯結束 ----
    }
}

// =================================================================================
// 功能函式庫 (整合了新舊函式)
// =================================================================================

// 產生隨機圖片訊息 (新加入)
function generateRandomImageMessage() {
    $imageUrls = [
        'https://i.imgur.com/z4OJM7u.jpeg', // 貓咪1
        'https://i.imgur.com/2Mv4N3j.jpeg', // 貓咪2
        'https://i.imgur.com/R3r41GE.jpeg'  // 狗狗
    ];
    $randomIndex = array_rand($imageUrls);
    return [
        'type' => 'image',
        'originalContentUrl' => $imageUrls[$randomIndex],
        'previewImageUrl' => $imageUrls[$randomIndex]
    ];
}

// 產生「活動」的 Flex Message (新加入)
function generateActivityFlexMessage() {
    return [
        "type" => "flex",
        "altText" => "夏日清涼祭典活動通知",
        "contents" => [ "type" => "bubble", "hero" => [ "type" => "image", "url" => "https://i.imgur.com/4QJjKqM.jpeg", "size" => "full", "aspectRatio" => "20:13", "aspectMode" => "cover" ], "body" => [ "type" => "box", "layout" => "vertical", "contents" => [ [ "type" => "text", "text" => "夏日清涼祭典", "weight" => "bold", "size" => "xl" ], [ "type" => "box", "layout" => "vertical", "margin" => "lg", "spacing" => "sm", "contents" => [ [ "type" => "box", "layout" => "baseline", "spacing" => "sm", "contents" => [ [ "type" => "text", "text" => "地點", "color" => "#aaaaaa", "size" => "sm", "flex" => 1 ], [ "type" => "text", "text" => "LINE 公園中央廣場", "wrap" => true, "color" => "#666666", "size" => "sm", "flex" => 5 ] ] ], [ "type" => "box", "layout" => "baseline", "spacing" => "sm", "contents" => [ [ "type" => "text", "text" => "時間", "color" => "#aaaaaa", "size" => "sm", "flex" => 1 ], [ "type" => "text", "text" => "8/1 (五) 10:00 - 18:00", "wrap" => true, "color" => "#666666", "size" => "sm", "flex" => 5 ] ] ] ] ] ] ], "footer" => [ "type" => "box", "layout" => "vertical", "spacing" => "sm", "contents" => [ [ "type" => "button", "style" => "link", "height" => "sm", "action" => [ "type" => "uri", "label" => "查看官網", "uri" => "https://line.me" ] ], [ "type" => "button", "style" => "link", "height" => "sm", "action" => [ "type" => "uri", "label" => "分享給朋友", "uri" => "https://line.me/R/share?text=快來參加夏日清涼祭典！" ] ] ], "flex" => 0 ] ]
    ];
}

// ---- 以下是您原本就有的函式，為了完整性，建議保留 ----
// 為了版面簡潔，此處省略 generateFlexMessage (價目表), generateQuickReply 等您已有的函式
// 請您將您原本程式碼中的這些函式，複製貼到這個區塊
function generatePriceListFlexMessage($priceList) {
    // ... 您原本產生價目表的程式碼 ...
}

function generateQuickReply($priceList) {
    // ... 您原本產生 Quick Reply 的程式碼 ...
}
?>
