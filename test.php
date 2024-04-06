<?php

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 接收 POST 資料
    $postData = file_get_contents('php://input');

    // 解析 JSON 資料
    $data = json_decode($postData, true);

    // 假設我們要回傳接收到的資料
    $response = array(
        'status' => 'success',
        'postData' => $data
    );

    // 設定回傳的 Content-Type 為 JSON
    header('Content-Type: application/json');

    // 將回應資料編碼為 JSON 並回傳
    echo json_encode($response);
}
