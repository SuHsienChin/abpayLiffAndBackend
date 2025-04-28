<?php
require 'vendor/autoload.php';

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Event\MessageEvent\TextMessageEvent;

$httpClient = new CurlHTTPClient('EjuJI5V8dkhxlf2UwyyTpFH2CCyfWfbZvQHlkEb++HCpuRebNZLMWiIb5261Ph2qG0NMBhXubY6ZnJ1wScafSYOjJlsf1GYz72m3sKZ3AZwYyG07pVCSxeUfvFYCyV4Ut+ztxrwUsHlm8wezhaYP0wdB04t89/1O/w1cDnyilFU=');
$bot = new LINEBot($httpClient, ['channelSecret' => '730bb8e5cc32933a019d58abbcdd61de']);
$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

$events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);

foreach ($events as $event) {
    if ($event instanceof TextMessageEvent) {
        $replyToken = $event->getReplyToken();
        $text = $event->getText();
        $bot->replyText($replyToken, "你說的是: " . $text);
    }
}
