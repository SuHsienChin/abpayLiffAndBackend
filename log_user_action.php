<?php
/**
 * log_user_action.php
 *
 * 接收前端傳來的 action 與 data，並寫入 user_action_logs 資料表。
 *
 * @author AI
 * @date 2024-06-09
 */

header('Content-Type: application/json');

// 引入資料庫連線設定
require_once 'databaseConnection.php';

// 取得 POST 資料
$input = json_decode(file_get_contents('php://input'), true);
$action = isset($input['action']) ? $input['action'] : '';
$data = isset($input['data']) ? json_encode($input['data'], JSON_UNESCAPED_UNICODE) : '';

$response = ["success" => false];

if ($action !== '') {
    try {
        $stmt = $conn->prepare("INSERT INTO user_action_logs (action, data, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param('ss', $action, $data);
        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Log saved.";
        } else {
            $response["message"] = "DB insert failed.";
        }
        $stmt->close();
    } catch (Exception $e) {
        $response["message"] = $e->getMessage();
    }
} else {
    $response["message"] = "Missing action.";
}

// 關閉資料庫連線
$conn->close();

echo json_encode($response, JSON_UNESCAPED_UNICODE); 