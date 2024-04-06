<?php
// API URL
$url = 'http://www.adp.idv.tw/api/Order';

// 要傳送的資料
$data = array(
    'UserId' => 'test02',
    'Password' => '3345678',
    'Customer' => '3353',
    'GameAccount' => '20499',
    'Item' => '3531',
    'Count' => '1',
    'Note0' => 'test'
);

// 將資料編碼成 JSON 格式
$data_string = json_encode($data);

// 初始化 cURL 會話
$ch = curl_init($url);

// 設定 cURL 選項
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string))
);

// 執行 cURL 會話
$result = curl_exec($ch);

// 關閉 cURL 會話
curl_close($ch);

// 輸出結果
echo $result;
?>
