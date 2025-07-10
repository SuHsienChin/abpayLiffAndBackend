#!/bin/bash
# 停止訂單佇列處理器腳本
# 此腳本用於在 CentOS 7 上停止訂單佇列處理器

# 設置變數
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PID_FILE="$SCRIPT_DIR/queue_processor.pid"
LOG_DIR="$SCRIPT_DIR/logs"
LOG_FILE="$LOG_DIR/queue_processor.log"
STOP_SIGNAL_FILE="$SCRIPT_DIR/stop_queue_processor"

# 檢查處理器是否在運行
if [ ! -f "$PID_FILE" ]; then
    echo "訂單佇列處理器未運行"
    exit 0
fi

# 讀取 PID
PID=$(cat "$PID_FILE")

# 檢查進程是否存在
if ! ps -p "$PID" > /dev/null 2>&1; then
    echo "訂單佇列處理器未運行 (PID: $PID 不存在)"
    rm -f "$PID_FILE"
    exit 0
fi

echo "停止訂單佇列處理器 (PID: $PID)..."
echo "$(date '+%Y-%m-%d %H:%M:%S') 嘗試停止訂單佇列處理器，PID: $PID" >> "$LOG_FILE"

# 創建停止信號文件
touch "$STOP_SIGNAL_FILE"

# 嘗試優雅地停止進程
kill -TERM "$PID" 2>/dev/null

# 等待進程結束
MAX_WAIT=30
WAITED=0
while ps -p "$PID" > /dev/null 2>&1; do
    if [ "$WAITED" -ge "$MAX_WAIT" ]; then
        echo "進程未在 $MAX_WAIT 秒內停止，嘗試強制終止"
        echo "$(date '+%Y-%m-%d %H:%M:%S') 進程未在 $MAX_WAIT 秒內停止，嘗試強制終止" >> "$LOG_FILE"
        kill -9 "$PID" 2>/dev/null
        break
    fi
    echo "等待進程結束... ($WAITED/$MAX_WAIT)"
    sleep 1
    WAITED=$((WAITED + 1))
done

# 檢查進程是否已經停止
if ps -p "$PID" > /dev/null 2>&1; then
    echo "無法停止訂單佇列處理器 (PID: $PID)"
    echo "$(date '+%Y-%m-%d %H:%M:%S') 無法停止訂單佇列處理器，PID: $PID" >> "$LOG_FILE"
    exit 1
else
    echo "訂單佇列處理器已停止"
    echo "$(date '+%Y-%m-%d %H:%M:%S') 訂單佇列處理器已停止，PID: $PID" >> "$LOG_FILE"
    rm -f "$PID_FILE"
    rm -f "$STOP_SIGNAL_FILE" 2>/dev/null
    exit 0
fi