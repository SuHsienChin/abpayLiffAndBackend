<?php
require_once 'RedisConnection.php';
require_once 'getApiJsonClass.php';

class RedisOrderQueue {
    private $redis;
    private $queueKey = 'order_queue';
    
    public function __construct() {
        $this->redis = RedisConnection::getInstance()->getRedis();
    }
    
    /**
     * 將訂單添加到佇列中
     * @param string $urlParams - API URL 參數
     * @param array $orderData - 訂單數據
     * @return string - 佇列 ID
     */
    public function addToQueue($urlParams, $orderData = []) {
        // 生成唯一的佇列 ID
        $queueId = uniqid('order_', true);
        
        // 準備佇列項目數據
        $queueItem = [
            'id' => $queueId,
            'url_params' => $urlParams,
            'order_data' => $orderData,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'processed_at' => null
        ];
        
        // 將訂單添加到佇列
        $this->redis->rPush($this->queueKey, json_encode($queueItem));
        
        // 記錄佇列狀態
        error_log("訂單已添加到佇列: {$queueId}");
        
        return $queueId;
    }
    
    /**
     * 處理佇列中的下一個訂單
     * @return array|null - 處理結果
     */
    public function processNextOrder() {
        // 從佇列中獲取下一個訂單
        $queueItemJson = $this->redis->lPop($this->queueKey);
        
        if (!$queueItemJson) {
            return null; // 佇列為空
        }
        
        $queueItem = json_decode($queueItemJson, true);
        
        // 更新處理時間
        $queueItem['processed_at'] = date('Y-m-d H:i:s');
        $queueItem['status'] = 'processing';
        
        try {
            // 檢查訂單參數
            $urlParams = $queueItem['url_params'];
            $paramErrors = $this->validateOrderParams($urlParams);
            
            if (!empty($paramErrors)) {
                // 參數驗證失敗
                $queueItem['status'] = 'invalid_params';
                $queueItem['error'] = $paramErrors;
                
                // 記錄錯誤
                error_log("訂單參數無效: {$queueItem['id']}, 錯誤: " . json_encode($paramErrors));
                
                return [
                    'queue_item' => $queueItem,
                    'error' => $paramErrors
                ];
            }
            
            // 構建完整的 API URL
            $apiBaseUrl = 'http://www.adp.idv.tw/api/Order?';
            $fullApiUrl = $apiBaseUrl . $queueItem['url_params'];
            
            // 發送 API 請求
            $curlRequest = new CurlRequest($fullApiUrl);
            $response = $curlRequest->sendRequest();
            $responseData = json_decode($response, true);
            
            // 更新佇列項目狀態
            $queueItem['status'] = ($responseData && isset($responseData['Status']) && $responseData['Status'] == '1') ? 'success' : 'failed';
            $queueItem['response'] = $responseData;
            
            // 記錄處理結果
            error_log("訂單處理完成: {$queueItem['id']}, 狀態: {$queueItem['status']}");
            
            return [
                'queue_item' => $queueItem,
                'response' => $responseData
            ];
        } catch (Exception $e) {
            // 處理失敗，更新狀態
            $queueItem['status'] = 'error';
            $queueItem['error'] = $e->getMessage();
            
            // 記錄錯誤
            error_log("訂單處理錯誤: {$queueItem['id']}, 錯誤: {$e->getMessage()}");
            
            return [
                'queue_item' => $queueItem,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 驗證訂單參數
     * @param string $urlParams - URL 參數字符串
     * @return array - 錯誤訊息陣列
     */
    private function validateOrderParams($urlParams) {
        $errors = [];
        
        // 解析 URL 參數
        $params = [];
        parse_str($urlParams, $params);
        
        // 檢查必要參數
        $requiredParams = ['UserId', 'Password', 'Customer', 'GameAccount', 'Item', 'Count'];
        foreach ($requiredParams as $param) {
            if (!isset($params[$param]) || $params[$param] === '') {
                $errors[] = "缺少必要參數: {$param}";
            }
        }
        
        // 特別檢查 Count 參數
        if (isset($params['Count'])) {
            $counts = explode(',', $params['Count']);
            foreach ($counts as $index => $count) {
                if (!is_numeric($count) || intval($count) <= 0) {
                    $errors[] = "商品 {$index} 的數量無效: {$count}，數量必須大於 0";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * 獲取佇列長度
     * @return int - 佇列長度
     */
    public function getQueueLength() {
        return $this->redis->lLen($this->queueKey);
    }
    
    /**
     * 獲取佇列項目狀態
     * @param string $queueId - 佇列 ID
     * @return array|null - 佇列項目狀態
     */
    public function getQueueItemStatus($queueId) {
        // 這個方法需要額外的實現，因為 Redis 列表不支持直接按 ID 查詢
        // 可以使用額外的 Redis 哈希表來存儲佇列項目狀態
        // 這裡僅作為示例
        return null;
    }
}