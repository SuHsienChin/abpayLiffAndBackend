<?php
require_once 'databaseConnection.php';

$connection = new DatabaseConnection();
$pdo = $connection->connect();

$data = json_decode(file_get_contents('php://input'), true);

try {
    $sql = "INSERT INTO user_action_logs (line_id, customer_id, page, action, data) VALUES (:line_id, :customer_id, :page, :action, :data)";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':line_id' => $data['line_id'],
        ':customer_id' => $data['customer_id'],
        ':page' => $data['page'],
        ':action' => $data['action'],
        ':data' => $data['data']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Data saved successfully!'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}