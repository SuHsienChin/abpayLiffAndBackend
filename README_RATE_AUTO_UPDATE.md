# 匯率自動更新設定說明

## 概述
本系統實現了匯率資料的自動更新機制，每 10 秒自動從外部 API 獲取最新匯率資料並更新到 Redis 快取中。

## 檔案說明

### 1. CacheConfig.php
- 新增 `CACHE_UPDATE_INTERVAL_RATE = 10` 常數，定義自動更新間隔（秒）
- 統一管理所有快取相關設定

### 2. update_rate_cache.php
- 自動更新匯率快取的 cron 腳本
- 從 `http://www.adp.idv.tw/api/Rate` 獲取最新資料
- 更新 Redis 快取，TTL 為 10 秒
- 記錄 API 請求和執行時間

### 3. getRate.php（已修改）
- 優先從 Redis 快取獲取資料
- 快取未命中時才調用外部 API 作為備用方案
- 移除了分散式鎖機制，簡化邏輯

## CentOS 7 設定步驟

### 1. 設定 cron 任務

編輯 crontab：
```bash
crontab -e
```

添加以下行（每 10 秒執行一次）：
```bash
* * * * * /usr/bin/php /var/www/html/update_rate_cache.php
* * * * * sleep 10; /usr/bin/php /var/www/html/update_rate_cache.php
* * * * * sleep 20; /usr/bin/php /var/www/html/update_rate_cache.php
* * * * * sleep 30; /usr/bin/php /var/www/html/update_rate_cache.php
* * * * * sleep 40; /usr/bin/php /var/www/html/update_rate_cache.php
* * * * * sleep 50; /usr/bin/php /var/www/html/update_rate_cache.php
```

### 2. 替代方案：使用 systemd timer（推薦）

創建 systemd service 檔案：
```bash
sudo vim /etc/systemd/system/rate-update.service
```

內容：
```ini
[Unit]
Description=Rate Cache Update Service
After=network.target

[Service]
Type=oneshot
User=www-data
Group=www-data
WorkingDirectory=/path/to/your/project
ExecStart=/usr/bin/php /path/to/your/project/update_rate_cache.php
StandardOutput=journal
StandardError=journal
```

創建 systemd timer 檔案：
```bash
sudo vim /etc/systemd/system/rate-update.timer
```

內容：
```ini
[Unit]
Description=Rate Cache Update Timer
Requires=rate-update.service

[Timer]
OnCalendar=*:*:0/10
Persistent=true

[Install]
WantedBy=timers.target
```

啟用並啟動 timer：
```bash
sudo systemctl daemon-reload
sudo systemctl enable rate-update.timer
sudo systemctl start rate-update.timer
```

### 3. 檢查執行狀態

#### 使用 cron 時：
```bash
# 查看 cron 日誌
tail -f /var/log/cron

# 查看系統日誌
journalctl -f
```

#### 使用 systemd timer 時：
```bash
# 查看 timer 狀態
sudo systemctl status rate-update.timer

# 查看 service 執行記錄
sudo journalctl -u rate-update.service -f

# 手動執行一次測試
sudo systemctl start rate-update.service
```

### 4. 監控和除錯

#### 檢查 Redis 快取：
```bash
# 連接到 Redis
redis-cli

# 查看匯率快取
GET rate_cache

# 查看快取 TTL
TTL rate_cache
```

#### 檢查 API 日誌：
查看 `ApiLogger` 記錄的日誌檔案，確認 API 請求是否正常。

### 5. 注意事項

1. **路徑設定**：請將 `/path/to/your/project` 替換為實際的專案路徑
2. **權限設定**：確保 PHP 腳本有執行權限
3. **網路連線**：確保伺服器能正常訪問 `http://www.adp.idv.tw/api/Rate`
4. **Redis 連線**：確保 Redis 服務正常運行
5. **日誌監控**：定期檢查日誌，確保自動更新正常運行

### 6. 故障排除

#### 常見問題：

1. **cron 任務不執行**
   - 檢查 crontab 語法是否正確
   - 確認 PHP 路徑是否正確
   - 檢查檔案權限

2. **API 請求失敗**
   - 檢查網路連線
   - 確認 API 端點是否正常
   - 查看錯誤日誌

3. **Redis 連線失敗**
   - 檢查 Redis 服務狀態
   - 確認 Redis 連線設定
   - 檢查防火牆設定

#### 手動測試：
```bash
# 手動執行更新腳本
php /path/to/your/project/update_rate_cache.php

# 測試 getRate.php
curl http://your-domain/getRate.php
```

## 效能優化建議

1. **監控執行時間**：如果 API 響應時間過長，考慮調整更新頻率
2. **錯誤處理**：設定適當的錯誤重試機制
3. **資源使用**：監控 CPU 和記憶體使用情況
4. **日誌輪轉**：設定日誌檔案輪轉，避免磁碟空間不足
