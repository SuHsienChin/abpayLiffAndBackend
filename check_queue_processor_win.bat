@echo off
REM 檢查訂單佇列處理器狀態腳本 (Windows 版本)
REM 此腳本用於在 Windows 環境下檢查訂單佇列處理器的運行狀態

setlocal enabledelayedexpansion

REM 設置變數
set SCRIPT_DIR=%~dp0
set PID_FILE=%SCRIPT_DIR%queue_processor.pid
set LOG_DIR=%SCRIPT_DIR%logs
set TODAY_LOG_FILE=%LOG_DIR%\order_queue_%date:~0,4%-%date:~5,2%-%date:~8,2%.log
set PROCESSOR_LOG_FILE=%LOG_DIR%\queue_processor.log

REM 顏色定義
set RED=[91m
set GREEN=[92m
set YELLOW=[93m
set NC=[0m

REM 檢查 Redis 服務狀態
echo %YELLOW%檢查 Redis 服務狀態...%NC%

REM 使用 Redis-CLI 測試連接
redis-cli ping >nul 2>&1
if %ERRORLEVEL% equ 0 (
    echo %GREEN%Redis 服務正在運行%NC%
) else (
    echo %RED%Redis 服務未運行或無法連接%NC%
    echo 請確保 Redis 服務已啟動
)

REM 檢查 Redis 佇列長度
echo.
echo %YELLOW%檢查 Redis 佇列長度...%NC%
for /f "tokens=*" %%a in ('redis-cli LLEN order_queue 2^>nul') do (
    set QUEUE_LENGTH=%%a
)

if not defined QUEUE_LENGTH (
    echo %RED%無法連接到 Redis 或獲取佇列長度%NC%
) else (
    echo 當前佇列中有 %GREEN%!QUEUE_LENGTH!%NC% 個訂單等待處理
)

REM 檢查處理器狀態
echo.
echo %YELLOW%檢查訂單佇列處理器狀態...%NC%

if not exist "%PID_FILE%" (
    echo %RED%訂單佇列處理器未運行 (PID 文件不存在)%NC%
) else (
    set /p PID=<"%PID_FILE%"
    tasklist /FI "PID eq !PID!" 2>nul | find /i "!PID!" >nul
    if !ERRORLEVEL! equ 0 (
        echo %GREEN%訂單佇列處理器正在運行 (PID: !PID!)%NC%
        
        REM 顯示進程啟動時間和資源使用情況
        echo.
        echo %YELLOW%進程資源使用情況:%NC%
        tasklist /FI "PID eq !PID!" /FO TABLE /V
        
        REM 顯示最近的日誌
        if exist "%TODAY_LOG_FILE%" (
            echo.
            echo %YELLOW%最近的處理器日誌 (最後 10 行):%NC%
            type "%TODAY_LOG_FILE%" | findstr /n "^" | findstr /r "[0-9][0-9]*:$" | sort /r | findstr /b /l /c:"1:" /c:"2:" /c:"3:" /c:"4:" /c:"5:" /c:"6:" /c:"7:" /c:"8:" /c:"9:" /c:"10:" | sort /+0 | for /f "tokens=1* delims=:" %%i in ('more') do echo %%j
        ) else (
            echo.
            echo %RED%今日日誌文件不存在: %TODAY_LOG_FILE%%NC%
        )
    ) else (
        echo %RED%訂單佇列處理器未運行 (PID: !PID! 不存在)%NC%
        echo 刪除過期的 PID 文件...
        del /f /q "%PID_FILE%"
    )
)

REM 顯示處理器日誌文件狀態
echo.
echo %YELLOW%處理器日誌文件狀態:%NC%
if exist "%LOG_DIR%" (
    echo 日誌目錄: %LOG_DIR%
    echo 日誌文件列表:
    dir /b /o-d "%LOG_DIR%" | findstr /i "order_queue queue_processor"
    
    REM 檢查日誌文件大小
    if exist "%PROCESSOR_LOG_FILE%" (
        for %%F in ("%PROCESSOR_LOG_FILE%") do set LOG_SIZE=%%~zF
        echo 處理器日誌文件大小: !LOG_SIZE! 字節
        
        REM 如果日誌文件過大，提示可能需要輪轉
        if !LOG_SIZE! gtr 10485760 (
            echo %YELLOW%警告: 日誌文件較大，可能需要考慮日誌輪轉%NC%
        )
    )
) else (
    echo %RED%日誌目錄不存在: %LOG_DIR%%NC%
)

echo.
echo %GREEN%檢查完成%NC%

pause