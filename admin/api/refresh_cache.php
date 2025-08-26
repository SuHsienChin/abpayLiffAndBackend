<?php
require_once dirname(__DIR__, 2) . '/getApiJsonClass.php';
require_once dirname(__DIR__, 2) . '/RedisConnection.php';
require_once dirname(__DIR__, 2) . '/ApiLogger.php';

/**
 * 強制刷新指定資源的 Redis 快取，直接調用外部 API 更新
 * 支援資源：customer, game_account, game_item, game_list, rate
 *
 * 輸入參數：
 * - type: string 資源類型
 * - lineId: string 當 type=customer
 * - sid: string 當 type=game_account 或 game_item
 */
header('Content-Type: application/json');

try {
    $type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
    $lineId = isset($_REQUEST['lineId']) ? trim($_REQUEST['lineId']) : null;
    $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : null;

    if ($type === '') {
        throw new Exception('缺少參數 type');
    }

    $redis = RedisConnection::getInstance();

    $resource = null;
    $cacheKey = null;
    $url = null;
    $ttl = null;

    switch ($type) {
        case 'customer':
            if ($lineId === null || $lineId === '') {
                throw new Exception('缺少參數 lineId');
            }
            $resource = 'getCustomer.php';
            $cacheKey = 'customer_cache_' . $lineId;
            $url = 'http://www.adp.idv.tw/api/Customer?Line=' . urlencode($lineId);
            $ttl = 300; // 與 getCustomer.php 一致
            break;
        case 'game_account':
            if ($sid === null || $sid === '') {
                throw new Exception('缺少參數 sid');
            }
            $resource = 'getGameAccount.php';
            $cacheKey = 'game_account_cache_' . $sid;
            $url = 'http://www.adp.idv.tw/api/GameAccount?Sid=' . urlencode($sid);
            $ttl = 86400; // 與 getGameAccount.php 一致
            break;
        case 'game_item':
            if ($sid === null || $sid === '') {
                throw new Exception('缺少參數 sid');
            }
            $resource = 'getGameItem.php';
            $cacheKey = 'game_item_cache_' . $sid;
            $url = 'http://www.adp.idv.tw/api/GameItem?Sid=' . urlencode($sid);
            $ttl = 300; // 與 getGameItem.php 一致
            break;
        case 'game_list':
            $resource = 'getGameList.php';
            $cacheKey = 'game_list_cache';
            $url = 'http://www.adp.idv.tw/api/GameList';
            $ttl = 86400; // 與 getGameList.php 一致
            break;
        case 'rate':
            $resource = 'getRate.php';
            $cacheKey = 'rate_cache';
            $url = 'http://www.adp.idv.tw/api/Rate';
            $ttl = 5; // 與 getRate.php 一致
            break;
        default:
            throw new Exception('不支援的 type');
    }

    $curl = new CurlRequest($url);
    $response = $curl->sendRequest();
    $data = json_decode($response, true);

    if ($data === null) {
        ApiLogger::logApiRequest('admin/api/refresh_cache.php', $url, $_REQUEST, '', false, 'api');
        throw new Exception('API 回傳無法解析');
    }

    // 直接覆寫快取
    $redis->set($cacheKey, $response, $ttl);

    ApiLogger::logApiRequest('admin/api/refresh_cache.php', $url, $_REQUEST, $response, true, 'api');

    echo json_encode([
        'success' => true,
        'message' => '快取已刷新',
        'cacheKey' => $cacheKey,
        'type' => $type,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}


