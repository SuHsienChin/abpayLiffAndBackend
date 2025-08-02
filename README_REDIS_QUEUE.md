# Redis 訂單佇列系統說明文件

## 概述

本系統實現了一個基於 Redis 的訂單佇列功能，用於控制向 API 伺服器發送請求的速率，確保每秒只發送一個請求，避免對 API 伺服器造成過大壓力。

## 系統組件

1. **RedisConnection.php**: Redis 連接類，提供與 Redis 伺服器的連接和基本操作。
2. **RedisOrderQueue.php**: 訂單佇列類，提供將訂單添加到佇列和處理佇列中訂單的功能。
3. **addOrderToQueue.php**: API 端點，用於將訂單添加到 Redis 佇列中。
4. **processOrderQueue.php**: 佇列處理腳本，用於從佇列中取出訂單並發送到 API 伺服器。
5. **setup_cron.php**: 設置 cron 作業的腳本，用於定時執行佇列處理腳本。
6. **orderModule.js**: 前端訂單處理模組，已修改為使用 Redis 佇列功能。

## 安裝與配置

### 1. 安裝 Redis 伺服器

如果尚未安裝 Redis 伺服器，請按照以下步驟安裝：

#### Windows 系統

1. 下載 Redis for Windows: https://github.com/microsoftarchive/redis/releases
2. 解壓縮並安裝
3. 啟動 Redis 伺服器: `redis-server.exe`

#### Linux 系統

```bash
sudo apt-get update
sudo apt-get install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### 2. 安裝 PHP Redis 擴展

#### Windows 系統

1. 在 php.ini 中添加 Redis 擴展: `extension=redis`
2. 重啟 Web 伺服器

#### Linux 系統

```bash
sudo apt-get install php-redis
sudo systemctl restart apache2  # 或 nginx
```

### 3. 配置 Redis 連接

在專案根目錄創建 `.env` 文件，並設置 Redis 連接參數：

```
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=your_password  # 如果有設置密碼
```

### 4. 設置定時任務

#### Linux 系統 (使用 cron)

執行 `setup_cron.php` 腳本，獲取 cron 命令：

```bash
php setup_cron.php
```

然後按照提示將命令添加到 crontab 中。

#### Windows 系統 (使用計劃任務)

1. 執行 `setup_cron.php` 腳本，生成批處理文件：

```bash
php setup_cron.php
```

2. 按照提示將生成的批處理文件添加到 Windows 計劃任務中。

## 使用方法

### 前端使用

前端已經修改為使用 Redis 佇列功能，無需額外操作。當用戶下單時，系統會自動將訂單添加到佇列中，並按照每秒一個請求的速率發送到 API 伺服器。

### 佇列狀態監控

佇列處理日誌保存在 `logs` 目錄中，可以通過查看日誌文件來監控佇列處理狀態：

- `order_queue_YYYY-MM-DD.log`: 佇列處理日誌
- `successful_orders_YYYY-MM-DD.log`: 成功處理的訂單日誌

## 故障排除

### 1. Redis 連接失敗

- 檢查 Redis 伺服器是否正在運行
- 檢查 `.env` 文件中的連接參數是否正確
- 檢查防火牆設置是否允許連接到 Redis 端口

### 2. 佇列處理腳本未執行

- 檢查 cron 作業或計劃任務是否正確設置
- 檢查 PHP 執行路徑是否正確
- 檢查腳本權限是否正確

### 3. 訂單未成功發送到 API

- 檢查佇列處理日誌中的錯誤信息
- 檢查 API 伺服器是否可訪問
- 檢查訂單參數是否正確

## 開發者說明

### 添加新功能

如果需要添加新功能或修改現有功能，請參考以下文件：

- `RedisOrderQueue.php`: 訂單佇列類，可以添加新的佇列操作方法
- `processOrderQueue.php`: 佇列處理腳本，可以修改處理邏輯
- `orderModule.js`: 前端訂單處理模組，可以修改前端邏輯

### 測試

在修改代碼後，請進行充分的測試，確保功能正常運行：

1. 測試訂單添加到佇列
2. 測試佇列處理
3. 測試 API 響應處理

## 注意事項

1. 請確保 Redis 伺服器的安全性，設置強密碼並限制訪問 IP
2. 定期備份 Redis 數據
3. 監控佇列長度，避免佇列過長導致處理延遲
4. 定期檢查日誌文件，及時發現並解決問題