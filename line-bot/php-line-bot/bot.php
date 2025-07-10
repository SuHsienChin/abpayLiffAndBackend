<?php
// 引入 Composer 自動載入的檔案
require_once __DIR__ . '/vendor/autoload.php';

// 引入 SDK 的必要類別
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\Event\MessageEvent\TextMessage;

// 從 LINE Developers Console 取得的 Channel Secret 和 Channel Access Token
$channelSecret = '1ccc9ceb2e4ca82d29fe91d1b6110ce2';
$channelAccessToken = 'WM8VEKbCYS6P0zJFEvMU1W+tQy+QDNVTCjIhJb5BB/mhX7r5AVTQygVwaaQb7EHZ7PrL4BO6USryXnOLys2+hC0lW0Bs75ccWth05p7lXTIzMISeLe/XjYLzgHpnpyqlrfCMpN9NBU4Emz0jUJxGJgdB04t89/1O/w1cDnyilFU=';

// 建立 HTTP Client 和 LINE Bot 實例
$httpClient = new CurlHTTPClient($channelAccessToken);
$bot = new LINEBot($httpClient, ['channelSecret' => $channelSecret]);

// 取得來自 LINE Webhook 的請求內容
$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];
$body = file_get_contents('php://input');

// 驗證簽章並解析事件 (SDK 會自動處理驗證)
try {
    $events = $bot->parseEventRequest($body, $signature);
} catch (\LINE\LINEBot\Exception\InvalidSignatureException $e) {
    // 簽章驗證失敗
    http_response_code(400);
    error_log('Invalid signature');
    exit();
} catch (\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
    // 事件請求格式錯誤
    http_response_code(400);
    error_log('Invalid event request');
    exit();
}

// 處理每一個事件
foreach ($events as $event) {
    // 只處理文字訊息事件
    if ($event instanceof TextMessage) {
        // 建立一個文字訊息來回覆
        $replyMessage = new TextMessageBuilder($event->getText());
        // 使用 replyMessage API 回覆
        $response = $bot->replyMessage($event->getReplyToken(), $replyMessage);

        // 如果 API 呼叫失敗，紀錄錯誤
        if (!$response->isSucceeded()) {
            error_log('Messages failed to send: ' . $response->getRawBody());
        }
    }
}

// 回應 200 OK 給 LINE 平台，表示處理成功
echo "OK";