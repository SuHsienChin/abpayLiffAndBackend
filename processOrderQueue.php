<?php
/**
 * 訂單佇列處理器
 * 此腳本從 Redis 佇列中獲取訂單並處理
 * 設計為在後台運行，每隔 1 秒處理一個訂單
 */

// 引入 Redis 連接類
require_once 'RedisConnection.php';

// 設置腳本可以長時間運行
set_time_limit(0);
ignore_user_abort(true);

// 檢查是否有停止信號
$shouldRun = true;

// 註冊信號處理函數，用於優雅地停止腳本
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGTERM, 'handleSignal');
    pcntl_signal(SIGINT, 'handleSignal');
}

function handleSignal($signal) {
    global $shouldRun;
    echo "收到停止信號 ($signal)，正在優雅地停止...\n";
    $shouldRun = false;
}

// 創建日誌目錄
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// 日誌文件路徑
$logFile = $logDir . '/order_queue_' . date('Y-m-d') . '.log';

// 寫入日誌函數
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage; // 同時輸出到控制台
}

writeLog("訂單佇列處理器已啟動");

// 獲取 Redis 連接
$redisConnection = RedisConnection::getInstance();
$redis = $redisConnection->getRedis();

if (!$redis) {
    writeLog("錯誤: Redis 連接失敗，處理器無法啟動");
    exit(1);
}

writeLog("成功連接到 Redis 伺服器");

// 處理訂單函數
function processOrder($orderJson) {
    global $redis;
    
    try {
        // 解析訂單數據
        $orderData = json_decode($orderJson, true);
        
        if (!$orderData) {
            writeLog("錯誤: 無效的訂單數據格式");
            return false;
        }
        
        $orderId = $orderData['orderId'] ?? 'unknown';
        writeLog("開始處理訂單: $orderId");
        
        // 提取 URL 參數和訂單參數
        $urlParams = $orderData['orderData']['UrlParametersString'] ?? '';
        $params = $orderData['orderData']['params'] ?? [];
        
        // 發送訂單到 API
        $apiUrl = 'sendOrderUrlByCORS.php?' . $urlParams;
        writeLog("發送訂單到 API: $apiUrl");
        
        // 使用 cURL 發送請求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            writeLog("API 請求錯誤: $error");
            return false;
        }
        
        if ($httpCode != 200) {
            writeLog("API 返回非 200 狀態碼: $httpCode");
            return false;
        }
        
        // 解析 API 響應
        $responseData = json_decode($response, true);
        
        if (!$responseData) {
            writeLog("無法解析 API 響應: $response");
            return false;
        }
        
        // 檢查 API 響應狀態
        if (isset($responseData['Status']) && $responseData['Status'] === '1') {
            $apiOrderId = $responseData['OrderId'] ?? '';
            writeLog("API 返回成功，訂單 ID: $apiOrderId");
            
            // 如果有參數，保存訂單數據
            if (!empty($params)) {
                // 添加 API 返回的訂單 ID
                $params['orderId'] = $apiOrderId;
                
                // 構建 POST 數據
                $postFields = http_build_query($params);
                
                // 發送到 addOrderData.php
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'addOrderData.php');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/x-www-form-urlencoded'
                ]);
                
                $insertResponse = curl_exec($ch);
                $insertError = curl_error($ch);
                curl_close($ch);
                
                if ($insertError) {
                    writeLog("保存訂單數據錯誤: $insertError");
                } else {
                    writeLog("訂單數據已保存: $insertResponse");
                }
            }
            
            // 將處理成功的訂單添加到歷史記錄
            $redis->rPush('order_history', $orderJson);
            writeLog("訂單 $orderId 處理成功並添加到歷史記錄");
            return true;
        } else {
            $errorMsg = $responseData['Message'] ?? '未知錯誤';
            writeLog("API 返回錯誤: $errorMsg");
            return false;
        }
    } catch (Exception $e) {
        writeLog("處理訂單時發生異常: " . $e->getMessage());
        return false;
    }
}

// 主處理循環
writeLog("開始處理佇列中的訂單...");

while ($shouldRun) {
    try {
        // 檢查是否有停止信號文件
        if (file_exists(__DIR__ . '/stop_queue_processor')) {
            writeLog("檢測到停止信號文件，正在停止處理器...");
            unlink(__DIR__ . '/stop_queue_processor');
            break;
        }
        
        // 從佇列左側取出一個訂單（先進先出）
        $orderJson = $redis->lPop('order_queue');
        
        if ($orderJson) {
            // 處理訂單
            $success = processOrder($orderJson);
            
            if (!$success) {
                writeLog("訂單處理失敗，將重新添加到佇列末尾");
                // 將失敗的訂單重新添加到佇列末尾
                $redis->rPush('order_queue', $orderJson);
            }
            
            // 處理完一個訂單後等待 1 秒
            sleep(1);
        } else {
            // 佇列為空，等待一段時間再檢查
            writeLog("佇列為空，等待新訂單...");
            sleep(5);
        }
        
        // 如果支持 pcntl，處理掛起的信號
        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
        }
    } catch (Exception $e) {
        writeLog("處理佇列時發生錯誤: " . $e->getMessage());
        sleep(5); // 發生錯誤時等待一段時間再繼續
    }
}

writeLog("訂單佇列處理器已停止");