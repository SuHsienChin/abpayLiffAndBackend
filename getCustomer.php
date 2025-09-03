<?php
/**
 * 客戶資料取得程式
 * 功能：根據 LINE ID 取得客戶資料，支援 Redis 快取和外部 API 備援機制
 * 特色：Redis Hash 結構 + 過期快取備援 + 分散式鎖 + 資料來源追蹤
 */

// 關閉錯誤顯示，避免在 JSON 輸出前顯示警告
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 引入必要的程式檔案
require_once 'RedisConnection.php';       // Redis 連線類別
require_once 'CacheConfig.php';          // 快取設定檔
require_once 'ApiLogger.php';            // API 記錄器
require_once 'DistributedLock.php';      // 分散式鎖機制
require_once 'PerformanceMonitor.php';   // 效能監控類別

/**
 * 取得客戶資料的主要函式（支援過期快取備援）
 * 
 * @param string $lineId 使用者的 LINE ID
 * @param int $freshWindowSeconds 資料保鮮期（秒），預設5秒
 * @param int $cacheTTLSeconds Redis 快取存活時間（秒）
 * @return array 客戶資料陣列
 */
function getCustomerDataWithFallback($lineId, $freshWindowSeconds = 5, $cacheTTLSeconds = null)
{
    // 如果沒有指定快取 TTL，使用設定檔的預設值
    if ($cacheTTLSeconds === null) {
        $cacheTTLSeconds = defined('CACHE_TTL_CUSTOMER') ? CACHE_TTL_CUSTOMER : 30;
    }

    // 取得 Redis 連線實例
    $conn = RedisConnection::getInstance();
    $raw = $conn->getRedis();

    // 設定 Redis 的 key 名稱
    $hashKey = 'customer_data:' . $lineId;        // 客戶資料的 key，格式：customer_data:LINE_ID
    $lockKey = $hashKey . ':lock';                // 分散式鎖的 key

    /**
     * 讀取函式：從 Redis 讀取快取資料
     * 支援兩種模式：Redis Hash 和 Redis 模擬器
     */
    $read = function () use ($raw, $hashKey) {
        if ($raw instanceof Redis) {
            // 如果是真正的 Redis 連線，使用 Hash 結構讀取
            $h = $raw->hGetAll($hashKey);
            if (!empty($h) && isset($h['data'], $h['timestamp'])) {
                return ['data' => $h['data'], 'timestamp' => (int)$h['timestamp']];
            }
            return null;
        }
        // 如果是 Redis 模擬器，使用傳統的 get 方法
        $json = $raw->get($hashKey);
        if (!$json) return null;
        $arr = json_decode($json, true);
        return (is_array($arr) && isset($arr['data'], $arr['timestamp'])) ? ['data' => $arr['data'], 'timestamp' => (int)$arr['timestamp']] : null;
    };

    /**
     * 寫入函式：將資料寫入 Redis 快取
     * 支援兩種模式：Redis Hash 和 Redis 模擬器
     */
    $write = function ($dataJson) use ($raw, $hashKey, $cacheTTLSeconds) {
        $now = time();  // 取得當前時間戳
        if ($raw instanceof Redis) {
            // 如果是真正的 Redis 連線，使用 Hash 結構寫入
            $raw->hMSet($hashKey, ['data' => $dataJson, 'timestamp' => $now]);
            $raw->expire($hashKey, (int)$cacheTTLSeconds);  // 設定過期時間
            return true;
        }
        // 如果是 Redis 模擬器，使用傳統的 set 方法
        $payload = json_encode(['data' => $dataJson, 'timestamp' => $now], JSON_UNESCAPED_UNICODE);
        return $raw->set($hashKey, $payload, (int)$cacheTTLSeconds);
    };

    // 第一步：嘗試讀取新鮮的快取資料
    $cached = $read();
    if ($cached && (time() - $cached['timestamp']) <= $freshWindowSeconds) {
        // 如果快取資料存在且還在保鮮期內，直接回傳
        $arr = json_decode($cached['data'], true) ?: [];
        $arr['data_source'] = 'redis_cache_fresh';  // 標記資料來源為新鮮快取
        return $arr;
    }

    // 第二步：嘗試取得分散式鎖來更新資料
    if (DistributedLock::acquireLock($lockKey, 10)) {  // 嘗試取得鎖，最多等待10秒
        try {
            // 取得鎖後，再次檢查是否有其他程序已經更新了快取
            $again = $read();
            if ($again && (time() - $again['timestamp']) <= $freshWindowSeconds) {
                $arr = json_decode($again['data'], true) ?: [];
                $arr['data_source'] = 'redis_cache_fresh';
                return $arr;
            }

            // 呼叫外部 API 取得最新資料
            $url = 'http://www.adp.idv.tw/api/Customer?Line=' . urlencode($lineId);
            
            // 使用 cURL 發送 HTTP 請求
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);                    // 設定請求 URL
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            // 回傳結果而不是直接輸出
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);            // 連線超時時間（秒）
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);                   // 總超時時間（秒）
            $response = curl_exec($ch);                             // 執行請求
            $errNo = curl_errno($ch);                               // 檢查是否有錯誤
            $errMsg = $errNo ? curl_error($ch) : '';                // 取得錯誤訊息
            curl_close($ch);                                        // 關閉 cURL 連線

            // 檢查 API 請求是否成功
            if ($errNo !== 0 || !$response) {
                // API 失敗 → 嘗試使用過期快取作為備援
                $stale = $read();
                if ($stale) {
                    $arr = json_decode($stale['data'], true) ?: [];
                    $arr['data_source'] = 'redis_cache_stale';  // 標記資料來源為過期快取
                    ApiLogger::logApiRequest('getCustomer.php', $url, ['lineId' => $lineId], $errMsg, true, 'stale_fallback');
                    return $arr;
                }
                throw new Exception('外部 API 失敗且無備援快取');
            }

            // 解析 API 回傳的 JSON 資料
            $api = json_decode($response, true);
            if (!is_array($api)) {
                // JSON 格式錯誤 → 嘗試使用過期快取作為備援
                $stale = $read();
                if ($stale) {
                    $arr = json_decode($stale['data'], true) ?: [];
                    $arr['data_source'] = 'redis_cache_stale';
                    ApiLogger::logApiRequest('getCustomer.php', $url, ['lineId' => $lineId], 'invalid_json', true, 'stale_fallback');
                    return $arr;
                }
                throw new Exception('API 回傳格式錯誤');
            }

            // API 成功 → 將新資料寫入快取並回傳
            $write(json_encode($api, JSON_UNESCAPED_UNICODE));
            ApiLogger::logApiRequest('getCustomer.php', $url, ['lineId' => $lineId], json_encode($api, JSON_UNESCAPED_UNICODE), true, 'api');
            $api['data_source'] = 'live_api';  // 標記資料來源為即時 API
            return $api;
            
        } finally {
            // 無論成功或失敗，都要釋放鎖
            DistributedLock::releaseLock($lockKey);
        }
    }

    // 第三步：如果無法取得鎖，嘗試使用現有的快取資料（可能是過期的）
    $cached = $read();
    if ($cached) {
        $arr = json_decode($cached['data'], true) ?: [];
        $arr['data_source'] = 'redis_cache_stale';  // 標記資料來源為過期快取
        return $arr;
    }

    // 如果所有嘗試都失敗，拋出例外
    throw new Exception('無法取得資料');
}

// 程式主要執行邏輯
try {
    // 記錄請求開始時間
    $requestStartTime = microtime(true);
    
    // 從 GET 參數取得 LINE ID
    if (!isset($_GET['lineId']) || empty($_GET['lineId'])) {
        throw new Exception('缺少必要的 lineId 參數');
    }
    $lineId = $_GET['lineId'];
    
    // 呼叫主要函式取得客戶資料
    $result = getCustomerDataWithFallback($lineId, 5, defined('CACHE_TTL_CUSTOMER') ? CACHE_TTL_CUSTOMER : 30);
    
    // 計算回應時間並記錄效能數據
    $responseTime = round((microtime(true) - $requestStartTime) * 1000);
    $dataSource = $result['data_source'] ?? 'unknown';
    PerformanceMonitor::recordRequest('getCustomer', $responseTime, $dataSource, true, ['lineId' => $lineId]);
    
    // 設定 HTTP 標頭，防止瀏覽器快取
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: application/json');
    
    // 回傳 JSON 格式的客戶資料
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (Throwable $e) {
    // 計算回應時間並記錄錯誤
    $responseTime = round((microtime(true) - ($requestStartTime ?? microtime(true))) * 1000);
    PerformanceMonitor::recordRequest('getCustomer', $responseTime, 'error', false, [
        'lineId' => isset($lineId) ? $lineId : null,
        'error' => $e->getMessage()
    ]);
    
    // 錯誤處理：回傳 HTTP 500 錯誤和錯誤訊息
    http_response_code(500);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: application/json');
    
    // 記錄錯誤到 API 記錄器
    ApiLogger::logApiRequest('getCustomer.php', 'internal://exception', ['lineId' => isset($lineId) ? $lineId : null], $e->getMessage(), false, 'internal');
    
    // 回傳錯誤訊息
    echo json_encode([
        'success' => false,
        'message' => '伺服器發生錯誤，請稍後再試',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}