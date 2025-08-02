<?php
/**
 * 設置 cron 作業腳本
 * 此腳本用於設置定時任務，每秒執行一次 processOrderQueue.php 腳本
 * 注意：此腳本需要在 Linux 系統上運行，Windows 系統請使用計劃任務
 */

// 獲取當前腳本的絕對路徑
$scriptPath = __DIR__ . '/processOrderQueue.php';

// 檢查腳本是否存在
if (!file_exists($scriptPath)) {
    die("處理佇列腳本不存在: {$scriptPath}\n");
}

// 構建 cron 命令
$cronCommand = "* * * * * php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 1; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 2; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 3; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 4; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 5; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 6; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 7; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 8; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 9; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 10; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 11; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 12; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 13; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 14; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 15; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 16; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 17; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 18; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 19; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 20; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 21; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 22; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 23; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 24; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 25; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 26; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 27; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 28; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 29; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 30; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 31; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 32; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 33; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 34; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 35; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 36; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 37; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 38; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 39; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 40; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 41; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 42; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 43; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 44; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 45; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 46; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 47; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 48; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 49; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 50; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 51; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 52; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 53; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 54; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 55; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 56; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 57; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 58; php {$scriptPath}\n";
$cronCommand .= "* * * * * sleep 59; php {$scriptPath}\n";

// 輸出 cron 命令
echo "請將以下命令添加到 crontab 中：\n\n";
echo $cronCommand;
echo "\n";
echo "在 Linux 系統上，您可以使用以下命令添加到 crontab：\n";
echo "crontab -e\n";
echo "然後粘貼上面的命令並保存。\n\n";

echo "在 Windows 系統上，您可以使用計劃任務來實現類似功能：\n";
echo "1. 打開任務計劃程序\n";
echo "2. 創建基本任務\n";
echo "3. 設置每分鐘觸發一次\n";
echo "4. 操作選擇 '啟動程序'\n";
echo "5. 程序/腳本選擇 php.exe 路徑\n";
echo "6. 添加參數 {$scriptPath}\n";
echo "7. 完成設置\n";

// 創建 Windows 批處理文件
$batchFilePath = __DIR__ . '/process_queue.bat';
$batchContent = "@echo off\r\n";
$batchContent .= "REM 此批處理文件用於在 Windows 系統上每秒執行一次佇列處理腳本\r\n";
$batchContent .= "REM 請將此文件添加到 Windows 計劃任務中，設置為每分鐘執行一次\r\n\r\n";

$phpPath = 'php'; // 假設 PHP 已添加到 PATH 環境變量中

for ($i = 0; $i < 60; $i++) {
    $batchContent .= "php \"{$scriptPath}\"\r\n";
    $batchContent .= "timeout /t 1 /nobreak > nul\r\n";
}

file_put_contents($batchFilePath, $batchContent);

echo "已創建 Windows 批處理文件：{$batchFilePath}\n";
echo "您可以將此文件添加到 Windows 計劃任務中，設置為每分鐘執行一次。\n";