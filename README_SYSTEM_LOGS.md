# System Logs 表說明

## 問題描述

在使用 `addOrderToQueue.php` API 時，可能會遇到 500 Internal Server Error 錯誤。這個錯誤的主要原因是系統嘗試將日誌寫入到 `system_logs` 表，但該表在數據庫中不存在。

## 解決方案

我們已經對系統進行了以下改進：

1. 修改了 `addOrderToQueue.php` 文件，添加了自動檢查和創建 `system_logs` 表的功能。如果表不存在，系統會自動創建它。

2. 改進了 `databaseConnection.php` 中的錯誤處理，使其在數據庫連接失敗時不會直接終止腳本，而是拋出異常，這樣 `addOrderToQueue.php` 可以捕獲這個異常並返回適當的錯誤響應。

## system_logs 表結構

```sql
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    JSON TEXT,
    api_url TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 使用說明

`system_logs` 表用於記錄系統操作日誌，特別是訂單處理相關的操作。目前，`addOrderToQueue.php` 會在將訂單添加到佇列時記錄日誌到這個表中。

日誌記錄包括：
- 操作類型（type）
- JSON 格式的詳細信息（JSON）
- API URL（api_url）
- 創建時間（created_at）

## 維護建議

1. 定期檢查 `system_logs` 表的大小，避免表過大影響系統性能。
2. 考慮添加日誌清理機制，定期刪除過舊的日誌記錄。
3. 如果需要更詳細的日誌記錄，可以擴展表結構，添加更多字段。