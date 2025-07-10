# 訂單佇列系統使用說明

## 系統概述

訂單佇列系統使用 Redis 作為佇列伺服器，將訂單發送到 API 的過程改為每隔 1 秒發送一個訂單，以避免同時發送大量訂單可能導致的系統壓力和 API 限制問題。

## 系統組件

1. **前端 JavaScript (orderModule.js)**
   - `OrderProcessor.sendOrder()`: 處理訂單提交
   - `OrderProcessor.addToRedisQueue()`: 將訂單添加到 Redis 佇列

2. **後端 PHP 文件**
   - `addToOrderQueue.php`: 接收訂單並添加到 Redis 佇列
   - `processOrderQueue.php`: 佇列處理器，從佇列中取出訂單並處理
   - `RedisConnection.php`: Redis 連接類

3. **管理腳本**
   - `start_queue_processor.sh`: 啟動佇列處理器
   - `stop_queue_processor.sh`: 停止佇列處理器
   - `check_queue_processor.sh`: 檢查佇列處理器狀態

## 安裝與配置

### 前提條件

- CentOS 7 作業系統
- PHP 7.2 或更高版本
- Redis 伺服器
- PHP Redis 擴展

### 安裝 Redis 和 PHP Redis 擴展

```bash
# 安裝 Redis
sudo yum install -y redis

# 啟動 Redis 並設置為開機自啟動
sudo systemctl start redis
sudo systemctl enable redis

# 安裝 PHP Redis 擴展
sudo yum install -y php-pecl-redis

# 重啟 PHP-FPM (如果使用)
sudo systemctl restart php-fpm
```

### Redis 連接配置

編輯 `.env` 文件，添加以下配置：

```
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=your_redis_password  # 如果有設置密碼
```

### 設置腳本權限

```bash
# 設置腳本可執行權限
chmod +x start_queue_processor.sh
chmod +x stop_queue_processor.sh
chmod +x check_queue_processor.sh
```

## 使用方法

### 啟動佇列處理器

```bash
./start_queue_processor.sh
```

### 停止佇列處理器

```bash
./stop_queue_processor.sh
```

### 檢查佇列處理器狀態

```bash
./check_queue_processor.sh
```

### 設置開機自啟動

1. 創建 systemd 服務文件：

```bash
sudo nano /etc/systemd/system/order-queue-processor.service
```

2. 添加以下內容：

```
[Unit]
Description=Order Queue Processor Service
After=network.target redis.service

[Service]
Type=forking
User=www-data  # 替換為您的 Web 伺服器用戶
Group=www-data  # 替換為您的 Web 伺服器用戶組
ExecStart=/path/to/your/abpay/start_queue_processor.sh
ExecStop=/path/to/your/abpay/stop_queue_processor.sh
Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
```

3. 重新加載 systemd 配置並啟用服務：

```bash
sudo systemctl daemon-reload
sudo systemctl enable order-queue-processor.service
sudo systemctl start order-queue-processor.service
```

## 故障排除

### 佇列處理器無法啟動

1. 檢查 Redis 伺服器是否運行：

```bash
systemctl status redis
```

2. 檢查 PHP Redis 擴展是否安裝：

```bash
php -m | grep redis
```

3. 檢查日誌文件：

```bash
cat logs/queue_processor.log
cat logs/order_queue_$(date '+%Y-%m-%d').log
```

### Redis 連接問題

1. 檢查 Redis 連接配置：

```bash
cat .env | grep REDIS
```

2. 測試 Redis 連接：

```bash
redis-cli ping
```

## 監控與維護

### 監控佇列長度

```bash
redis-cli LLEN order_queue
```

### 查看佇列內容

```bash
redis-cli LRANGE order_queue 0 -1
```

### 清空佇列

```bash
redis-cli DEL order_queue
```

### 日誌輪轉

為避免日誌文件過大，建議設置日誌輪轉：

```bash
sudo nano /etc/logrotate.d/order-queue
```

添加以下內容：

```
/path/to/your/abpay/logs/*.log {
    daily
    missingok
    rotate 7
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

## 安全注意事項

1. 確保 Redis 伺服器只接受本地連接或設置強密碼
2. 定期備份 Redis 數據
3. 監控系統資源使用情況，特別是 CPU 和內存使用
4. 定期檢查日誌文件中的錯誤和警告

## 備份方案

### Redis 數據備份

```bash
# 創建備份目錄
mkdir -p /backup/redis

# 備份 Redis 數據
redis-cli SAVE
cp /var/lib/redis/dump.rdb /backup/redis/dump_$(date '+%Y%m%d').rdb
```

### 自動備份腳本

創建自動備份腳本 `backup_redis.sh`：

```bash
#!/bin/bash
BACKUP_DIR="/backup/redis"
DATE=$(date '+%Y%m%d')

# 創建備份目錄
mkdir -p "$BACKUP_DIR"

# 備份 Redis 數據
redis-cli SAVE
cp /var/lib/redis/dump.rdb "$BACKUP_DIR/dump_$DATE.rdb"

# 刪除 7 天前的備份
find "$BACKUP_DIR" -name "dump_*.rdb" -mtime +7 -delete
```

設置定時任務：

```bash
chmod +x backup_redis.sh
crontab -e
```

添加以下內容：

```
0 1 * * * /path/to/your/backup_redis.sh
```

## 系統升級與維護

在系統升級或維護時，請按照以下步驟操作：

1. 停止佇列處理器：

```bash
./stop_queue_processor.sh
```

2. 備份 Redis 數據：

```bash
redis-cli SAVE
```

3. 進行系統升級或維護

4. 啟動佇列處理器：

```bash
./start_queue_processor.sh
```

5. 檢查佇列處理器狀態：

```bash
./check_queue_processor.sh
```