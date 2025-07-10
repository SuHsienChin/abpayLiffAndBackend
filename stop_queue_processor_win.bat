@echo off
REM 停止訂單佇列處理器腳本 (Windows 版本)
REM 此腳本用於在 Windows 環境下停止訂單佇列處理器

setlocal enabledelayedexpansion

REM 設置變數
set SCRIPT_DIR=%~dp0
set PID_FILE=%SCRIPT_DIR%queue_processor.pid
set LOG_DIR=%SCRIPT_DIR%logs
set LOG_FILE=%LOG_DIR%\queue_processor.log
set STOP_SIGNAL_FILE=%SCRIPT_DIR%stop_queue_processor

REM 檢查處理器是否在運行
if not exist "%PID_FILE%" (
    echo 訂單佇列處理器未運行
    exit /b 0
)

REM 讀取 PID
set /p PID=<"%PID_FILE%"

REM 檢查進程是否存在
tasklist /FI "PID eq !PID!" 2>nul | find /i "!PID!" >nul
if !ERRORLEVEL! neq 0 (
    echo 訂單佇列處理器未運行 (PID: !PID! 不存在)
    del /f /q "%PID_FILE%"
    exit /b 0
)

echo 停止訂單佇列處理器 (PID: !PID!)...
echo %date% %time% 嘗試停止訂單佇列處理器，PID: !PID! >> "%LOG_FILE%"

REM 創建停止信號文件
type nul > "%STOP_SIGNAL_FILE%"

REM 嘗試優雅地停止進程
taskkill /PID !PID! /T /F >nul 2>&1

REM 等待進程結束
set MAX_WAIT=30
set WAITED=0
:WAIT_LOOP
tasklist /FI "PID eq !PID!" 2>nul | find /i "!PID!" >nul
if !ERRORLEVEL! equ 0 (
    if !WAITED! geq !MAX_WAIT! (
        echo 進程未在 !MAX_WAIT! 秒內停止，嘗試強制終止
        echo %date% %time% 進程未在 !MAX_WAIT! 秒內停止，嘗試強制終止 >> "%LOG_FILE%"
        taskkill /PID !PID! /T /F >nul 2>&1
        goto CHECK_STOPPED
    )
    echo 等待進程結束... (!WAITED!/!MAX_WAIT!)
    timeout /t 1 /nobreak >nul
    set /a WAITED+=1
    goto WAIT_LOOP
)

:CHECK_STOPPED
REM 檢查進程是否已經停止
tasklist /FI "PID eq !PID!" 2>nul | find /i "!PID!" >nul
if !ERRORLEVEL! equ 0 (
    echo 無法停止訂單佇列處理器 (PID: !PID!)
    echo %date% %time% 無法停止訂單佇列處理器，PID: !PID! >> "%LOG_FILE%"
    exit /b 1
) else (
    echo 訂單佇列處理器已停止
    echo %date% %time% 訂單佇列處理器已停止，PID: !PID! >> "%LOG_FILE%"
    del /f /q "%PID_FILE%"
    if exist "%STOP_SIGNAL_FILE%" del /f /q "%STOP_SIGNAL_FILE%"
    exit /b 0
)