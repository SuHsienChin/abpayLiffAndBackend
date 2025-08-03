# Redis 佇列管理工具使用說明

## 概述

本文件提供了一系列用於查看和管理 Redis 訂單佇列的工具說明。這些工具可以幫助您監控佇列狀態、查看佇列內容以及手動處理佇列中的訂單。

## 可用工具

### 1. 命令行工具

#### viewRedisQueue.php

這是一個命令行工具，用於查看當前 Redis 佇列的狀態和內容。

**使用方法：**

```bash
php viewRedisQueue.php
```

**輸出示例：**

```json
{
    "success": true,
    "queue_length": 3,
    "queue_items": [
        {
            "id": "order_688e690acd54d6.91464076",
            "url_params": "UserId=test02&Password=3345678&Customer=3353&GameAccount=20540&Item=6273&Count=1",
            "order_data": [],
            "status": "pending",
            "created_at": "2025-08-02 19:37:46",
            "processed_at": null
        },
        // 更多訂單...
    ]
}
```

#### processQueueManually.php

這是一個命令行工具，用於手動處理佇列中的下一個訂單。

**使用方法：**

```bash
php processQueueManually.php
```

**輸出示例：**

```json
{
    "success": true,
    "queue_length_before": 3,
    "processed": true,
    "queue_item": {
        "id": "order_688e690acd54d6.91464076",
        "url_params": "UserId=test02&Password=3345678&Customer=3353&GameAccount=20540&Item=6273&Count=1",
        "order_data": [],
        "status": "success",
        "created_at": "2025-08-02 19:37:46",
        "processed_at": "2025-08-03 14:05:23"
    },
    "message": "成功處理一個訂單",
    "order_id": "12345",
    "queue_length_after": 2
}
```

### 2. Web 界面工具

#### viewRedisQueueUI.php

這是一個 Web 界面工具，提供了友好的用戶界面來查看佇列狀態和手動處理訂單。

**使用方法：**

1. 啟動 PHP 內建 Web 伺服器：

```bash
php -S localhost:8000
```

2. 在瀏覽器中訪問：

```
http://localhost:8000/viewRedisQueueUI.php
```

**功能：**

- 查看佇列長度和訂單列表
- 重新整理佇列狀態
- 手動處理下一個訂單
- 顯示訂單處理結果

## 日誌文件

佇列處理的日誌文件保存在 `logs` 目錄中：

- `order_queue_YYYY-MM-DD.log`: 佇列處理日誌
- `successful_orders_YYYY-MM-DD.log`: 成功處理的訂單日誌
- `script_execution_YYYY-MM-DD.log`: 腳本執行日誌

## 故障排除

### 1. 無法連接到 Redis

如果工具無法連接到 Redis 伺服器，系統會自動使用 `RedisSimulator` 作為替代方案，將數據存儲在本地文件系統中。

### 2. 佇列為空

如果佇列為空，`processQueueManually.php` 將返回相應的消息，並且不會執行任何處理操作。

### 3. 處理訂單失敗

如果處理訂單失敗，請檢查日誌文件以獲取詳細的錯誤信息。

## 注意事項

1. 這些工具主要用於開發和測試環境，不建議在生產環境中使用。
2. 在生產環境中，建議使用 cron 作業或其他定時任務來自動處理佇列。
3. 請確保 Redis 伺服器的安全性，設置強密碼並限制訪問 IP。