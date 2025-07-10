@echo off
REM 訂單佇列處理器啟動腳本 (Windows 版本)
REM 此腳本用於在 Windows 環境下啟動訂單佇列處理器

setlocal enabledelayedexpansion

REM 設置變數
set SCRIPT_DIR=%~dp0
set PID_FILE=%SCRIPT_DIR%queue_processor.pid
set LOG_DIR=%SCRIPT_DIR%logs
set LOG_FILE=%LOG_DIR%\queue_processor.log
set PHP_PATH=php

REM 檢查 PHP 是否可用
where %PHP_PATH% >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo PHP 未找到，請確保 PHP 已安裝並添加到 PATH 環境變數中
    exit /b 1
)

REM 創建日誌目錄
if not exist "%LOG_DIR%" (
    mkdir "%LOG_DIR%"
    echo 創建日誌目錄: %LOG_DIR%
)

REM 檢查處理器是否已在運行
if exist "%PID_FILE%" (
    set /p PID=<"%PID_FILE%"
    
    REM 檢查進程是否存在
    tasklist /FI "PID eq !PID!" 2>nul | find /i "!PID!" >nul
    if !ERRORLEVEL! equ 0 (
        echo 訂單佇列處理器已在運行 (PID: !PID!)
        exit /b 0
    ) else (
        echo 刪除過期的 PID 文件...
        del /f /q "%PID_FILE%"
    )
)

echo 啟動訂單佇列處理器...
echo %date% %time% 啟動訂單佇列處理器 >> "%LOG_FILE%"

REM 啟動處理器
start /b %PHP_PATH% "%SCRIPT_DIR%processOrderQueue.php" > "%LOG_DIR%\queue_processor_output.log" 2>&1

REM 獲取進程 ID
for /f "tokens=2" %%a in ('tasklist /fi "imagename eq php.exe" /fo list ^| find "PID:"') do (
    set LAST_PID=%%a
)

REM 保存進程 ID
echo !LAST_PID! > "%PID_FILE%"
echo 訂單佇列處理器已啟動 (PID: !LAST_PID!)
echo %date% %time% 訂單佇列處理器已啟動，PID: !LAST_PID! >> "%LOG_FILE%"

echo.
echo 提示: 使用 stop_queue_processor_win.bat 停止處理器
echo 提示: 使用 check_queue_processor_win.bat 檢查處理器狀態
echo 提示: 訪問 http://localhost/abpay/queue_status.php 查看佇列狀態

exit /b 0