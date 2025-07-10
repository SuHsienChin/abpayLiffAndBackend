#!/bin/bash
# 啟動訂單佇列處理器腳本
# 此腳本用於在 CentOS 7 上啟動訂單佇列處理器

# 設置變數
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_SCRIPT="$SCRIPT_DIR/processOrderQueue.php"
LOG_DIR="$SCRIPT_DIR/logs"
PID_FILE="$SCRIPT_DIR/queue_processor.pid"
LOG_FILE="$LOG_DIR/queue_processor.log"

# 創建日誌目錄
mkdir -p "$LOG_DIR"

# 檢查處理器是否已經在運行
if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    if ps -p "$PID" > /dev/null 2>&1; then
        echo "訂單佇列處理器已經在運行，PID: $PID"
        exit 1
    else
        echo "發現過期的 PID 文件，將刪除"
        rm -f "$PID_FILE"
    fi
fi

# 啟動處理器
echo "啟動訂單佇列處理器..."
echo "$(date '+%Y-%m-%d %H:%M:%S') 啟動訂單佇列處理器" >> "$LOG_FILE"

# 使用 nohup 在後台運行 PHP 腳本
nohup php "$PHP_SCRIPT" >> "$LOG_FILE" 2>&1 &

# 獲取進程 PID
PID=$!

# 檢查進程是否成功啟動
if ps -p "$PID" > /dev/null 2>&1; then
    echo "$PID" > "$PID_FILE"
    echo "訂單佇列處理器已成功啟動，PID: $PID"
    echo "日誌文件: $LOG_FILE"
    echo "$(date '+%Y-%m-%d %H:%M:%S') 訂單佇列處理器已成功啟動，PID: $PID" >> "$LOG_FILE"
    exit 0
else
    echo "啟動訂單佇列處理器失敗"
    echo "$(date '+%Y-%m-%d %H:%M:%S') 啟動訂單佇列處理器失敗" >> "$LOG_FILE"
    exit 1
fi