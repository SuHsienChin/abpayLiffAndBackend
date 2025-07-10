#!/bin/bash
# 檢查訂單佇列處理器狀態腳本
# 此腳本用於在 CentOS 7 上檢查訂單佇列處理器的運行狀態

# 設置變數
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PID_FILE="$SCRIPT_DIR/queue_processor.pid"
LOG_DIR="$SCRIPT_DIR/logs"
TODAY_LOG_FILE="$LOG_DIR/order_queue_$(date '+%Y-%m-%d').log"
PROCESSOR_LOG_FILE="$LOG_DIR/queue_processor.log"

# 顏色定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
NC='\033[0m' # No Color

# 檢查 Redis 服務狀態
echo -e "${YELLOW}檢查 Redis 服務狀態...${NC}"
if systemctl is-active --quiet redis; then
    echo -e "${GREEN}Redis 服務正在運行${NC}"
else
    echo -e "${RED}Redis 服務未運行${NC}"
    echo "嘗試啟動 Redis 服務..."
    sudo systemctl start redis
    if systemctl is-active --quiet redis; then
        echo -e "${GREEN}Redis 服務已成功啟動${NC}"
    else
        echo -e "${RED}無法啟動 Redis 服務${NC}"
    fi
fi

# 檢查 Redis 佇列長度
echo -e "\n${YELLOW}檢查 Redis 佇列長度...${NC}"
QUEUE_LENGTH=$(redis-cli LLEN order_queue 2>/dev/null)
if [ -z "$QUEUE_LENGTH" ]; then
    echo -e "${RED}無法連接到 Redis 或獲取佇列長度${NC}"
else
    echo -e "當前佇列中有 ${GREEN}$QUEUE_LENGTH${NC} 個訂單等待處理"
fi

# 檢查處理器狀態
echo -e "\n${YELLOW}檢查訂單佇列處理器狀態...${NC}"

if [ ! -f "$PID_FILE" ]; then
    echo -e "${RED}訂單佇列處理器未運行 (PID 文件不存在)${NC}"
else
    PID=$(cat "$PID_FILE")
    if ps -p "$PID" > /dev/null 2>&1; then
        echo -e "${GREEN}訂單佇列處理器正在運行 (PID: $PID)${NC}"
        
        # 顯示進程啟動時間
        PROCESS_START=$(ps -p "$PID" -o lstart=)
        echo "進程啟動時間: $PROCESS_START"
        
        # 顯示進程資源使用情況
        echo -e "\n${YELLOW}進程資源使用情況:${NC}"
        ps -p "$PID" -o pid,ppid,user,%cpu,%mem,vsz,rss,stat,start,time,command --width 100
        
        # 顯示最近的日誌
        if [ -f "$TODAY_LOG_FILE" ]; then
            echo -e "\n${YELLOW}最近的處理器日誌 (最後 10 行):${NC}"
            tail -n 10 "$TODAY_LOG_FILE"
        else
            echo -e "\n${RED}今日日誌文件不存在: $TODAY_LOG_FILE${NC}"
        fi
    else
        echo -e "${RED}訂單佇列處理器未運行 (PID: $PID 不存在)${NC}"
        echo "刪除過期的 PID 文件..."
        rm -f "$PID_FILE"
    fi
fi

# 顯示處理器日誌文件狀態
echo -e "\n${YELLOW}處理器日誌文件狀態:${NC}"
if [ -d "$LOG_DIR" ]; then
    echo "日誌目錄: $LOG_DIR"
    echo "日誌文件列表:"
    ls -lh "$LOG_DIR" | grep -E 'order_queue|queue_processor'
    
    # 檢查日誌文件大小
    if [ -f "$PROCESSOR_LOG_FILE" ]; then
        LOG_SIZE=$(du -h "$PROCESSOR_LOG_FILE" | cut -f1)
        echo "處理器日誌文件大小: $LOG_SIZE"
        
        # 如果日誌文件過大，提示可能需要輪轉
        if [ $(du -k "$PROCESSOR_LOG_FILE" | cut -f1) -gt 10240 ]; then  # 大於 10MB
            echo -e "${YELLOW}警告: 日誌文件較大，可能需要考慮日誌輪轉${NC}"
        fi
    fi
else
    echo -e "${RED}日誌目錄不存在: $LOG_DIR${NC}"
fi

echo -e "\n${GREEN}檢查完成${NC}"