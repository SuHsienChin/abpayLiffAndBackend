<?php
try {
    $db = new PDO('mysql:host=localhost;port=3306;dbname=abpaytw_abpay', 'abpay', 'Aa.730216');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 創建 system_logs 表
    $sql = "CREATE TABLE IF NOT EXISTS system_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(255) NOT NULL,
        JSON TEXT,
        api_url TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->exec($sql);
    echo "system_logs 表已成功創建或已存在\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}