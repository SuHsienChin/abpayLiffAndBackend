<?php
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $conn->prepare("INSERT INTO user_action_logs (line_id, customer_id, page, action, data) VALUES (?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssss", 
        $data['line_id'],
        $data['customer_id'],
        $data['page'],
        $data['action'],
        $data['data']
    );
    
    $stmt->execute();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}