<?php
// 引入 Composer 自動載入的檔案
require_once __DIR__ . '../vendor/autoload.php';

// 引入所有會用到的 SDK 類別
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\Flex\Container\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\Component\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\Component\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\Component\ImageComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\Component\ButtonComponentBuilder;
use LINE\LINEBot\Action\URIAction;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use LINE\LINEBot\Event\JoinEvent;

// 從 LINE Developers Console 取得的 Channel Secret 和 Channel Access Token
$channelSecret = '1ccc9ceb2e4ca82d29fe91d1b6110ce2';
$channelAccessToken = 'WM8VEKbCYS6P0zJFEvMU1W+tQy+QDNVTCjIhJb5BB/mhX7r5AVTQygVwaaQb7EHZ7PrL4BO6USryXnOLys2+hC0lW0Bs75ccWth05p7lXTIzMISeLe/XjYLzgHpnpyqlrfCMpN9NBU4Emz0jUJxGJgdB04t89/1O/w1cDnyilFU=';

// 建立 HTTP Client 和 LINE Bot 實例
$httpClient = new CurlHTTPClient($channelAccessToken);
$bot = new LINEBot($httpClient, ['channelSecret' => $channelSecret]);

// 取得來自 LINE Webhook 的請求內容
$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];
$body = file_get_contents('php://input');

// 驗證並解析事件
try {
    $events = $bot->parseEventRequest($body, $signature);
} catch (Exception $e) {
    http_response_code(400);
    error_log('Error: ' . $e->getMessage());
    exit();
}

// 處理每一個事件
foreach ($events as $event) {
    // 當機器人被加入群組/社群時的問候
    if ($event instanceof JoinEvent) {
        $replyText = '大家好！我是社群小幫手，很高興為大家服務！' . "\n" . '輸入「功能」或「哈囉」可以查看我的指令喔！';
        $bot->replyText($event->getReplyToken(), $replyText);
        continue; // 處理完畢，跳到下一個事件
    }
    
    // 處理文字訊息
    if ($event instanceof TextMessage) {
        $userMessage = strtolower(trim($event->getText()));

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
                $bot->replyText($event->getReplyToken(), $replyText);
                break;

            case '活動':
                // 使用函式建立並回覆 Flex Message
                $flexMessage = createActivityFlexMessage();
                $bot->replyMessage($event->getReplyToken(), $flexMessage);
                break;
            
            case '抽一張':
                // 使用函式建立並回覆隨機圖片
                $imageMessage = createRandomImageMessage();
                $bot->replyMessage($event->getReplyToken(), $imageMessage);
                break;
        }
    }

    // 處理貼圖訊息
    if ($event instanceof StickerMessage) {
        $bot->replyText($event->getReplyToken(), '這個貼圖真可愛！');
    }
}

echo "OK";


// =================================================================================
// 功能函式庫 - 您可以在這裡新增或修改功能
// =================================================================================

/**
 * 建立並回傳一個隨機圖片訊息
 * @return ImageMessageBuilder
 */
function createRandomImageMessage() {
    $imageUrls = [
        'https://i.imgur.com/z4OJM7u.jpeg', // 貓咪1
        'https://i.imgur.com/2Mv4N3j.jpeg', // 貓咪2
        'https://i.imgur.com/R3r41GE.jpeg'  // 狗狗
    ];
    $randomIndex = array_rand($imageUrls);
    $selectedImageUrl = $imageUrls[$randomIndex];
    
    return new ImageMessageBuilder($selectedImageUrl, $selectedImageUrl);
}

/**
 * 建立並回傳一個活動的 Flex Message
 * @return FlexMessageBuilder
 */
function createActivityFlexMessage() {
    // 使用 Flex Message Simulator (https://developers.line.biz/flex-simulator/) 設計
    // 然後用 PHP SDK 的 Builder 來建立，這樣更安全、更結構化
    $bubble = BubbleContainerBuilder::builder()
        ->setHero(ImageComponentBuilder::builder()
            ->setUrl('https://i.imgur.com/4QJjKqM.jpeg')
            ->setSize('full')
            ->setAspectRatio('20:13')
            ->setAspectMode('cover')
        )
        ->setBody(BoxComponentBuilder::builder()
            ->setLayout('vertical')
            ->setContents([
                TextComponentBuilder::builder()->setText('夏日清涼祭典')->setWeight('bold')->setSize('xl'),
                BoxComponentBuilder::builder()->setLayout('vertical')->setMargin('lg')->setSpacing('sm')->setContents([
                    BoxComponentBuilder::builder()->setLayout('baseline')->setSpacing('sm')->setContents([
                        TextComponentBuilder::builder()->setText('地點')->setColor('#aaaaaa')->setSize('sm')->setFlex(1),
                        TextComponentBuilder::builder()->setText('LINE 公園中央廣場')->setWrap(true)->setColor('#666666')->setSize('sm')->setFlex(5)
                    ]),
                    BoxComponentBuilder::builder()->setLayout('baseline')->setSpacing('sm')->setContents([
                        TextComponentBuilder::builder()->setText('時間')->setColor('#aaaaaa')->setSize('sm')->setFlex(1),
                        TextComponentBuilder::builder()->setText('8/1 (五) 10:00 - 18:00')->setWrap(true)->setColor('#666666')->setSize('sm')->setFlex(5)
                    ])
                ])
            ])
        )
        ->setFooter(BoxComponentBuilder::builder()
            ->setLayout('vertical')
            ->setSpacing('sm')
            ->setContents([
                ButtonComponentBuilder::builder()->setStyle('link')->setHeight('sm')->setAction(new URIAction('查看官網', 'https://line.me')),
                ButtonComponentBuilder::builder()->setStyle('link')->setHeight('sm')->setAction(new URIAction('分享給朋友', 'https://line.me/R/share?text=快來參加夏日清涼祭典！'))
            ])
        );

    return new FlexMessageBuilder('這是一則活動訊息', $bubble);
}