<?php
// 測試 URL 參數處理

// 模擬參數
$params = [
    'UserId' => 'test02',
    'Password' => '3345678',
    'Customer' => '1308',
    'GameAccount' => '10475',
    'Item' => '6274,6273',
    'Count' => '1,1'
];

// 原始方法
echo "原始方法 (使用 urlencode):\n";
$urlParams1 = '';
foreach ($params as $key => $value) {
    $urlParams1 .= $key . '=' . urlencode($value) . '&';
}
$urlParams1 = rtrim($urlParams1, '&');
echo $urlParams1 . "\n\n";

// 修改後的方法
echo "修改後的方法 (對 Item 和 Count 特殊處理):\n";
$urlParams2 = '';
foreach ($params as $key => $value) {
    // 對於 Item 和 Count 參數，避免對逗號進行編碼
    if ($key === 'Item' || $key === 'Count') {
        // 先對值進行編碼，然後將編碼後的逗號 %2C 替換回原始逗號
        $encodedValue = urlencode($value);
        $encodedValue = str_replace('%2C', ',', $encodedValue);
        $urlParams2 .= $key . '=' . $encodedValue . '&';
    } else {
        $urlParams2 .= $key . '=' . urlencode($value) . '&';
    }
}
$urlParams2 = rtrim($urlParams2, '&');
echo $urlParams2 . "\n";

// 比較兩個 URL
echo "\n兩個 URL 是否相同: " . ($urlParams1 === $urlParams2 ? "是" : "否") . "\n";

// 構建完整的 API URL
$apiBaseUrl = 'http://www.adp.idv.tw/api/Order?';
$fullApiUrl1 = $apiBaseUrl . $urlParams1;
$fullApiUrl2 = $apiBaseUrl . $urlParams2;

echo "\n原始 API URL:\n{$fullApiUrl1}\n";
echo "\n修改後 API URL:\n{$fullApiUrl2}\n";