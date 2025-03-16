<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => '未授權的訪問']);
    exit;
}

require_once '../../databaseConnection.php';

// 設置 CORS 頭
header('Content-Type: application/json; charset=utf-8');

try {
    $db = new DatabaseConnection();
    $pdo = $db->connect();

    $stmt = $pdo->prepare("SELECT * FROM customers");
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($customers, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => '資料庫錯誤：' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => '系統錯誤：' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}