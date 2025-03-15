<?php
require_once '../databaseConnection.php';

try {
    $dbConnection = new DatabaseConnection();
    $conn = $dbConnection->connect();

    // 讀取並執行 SQL 文件
    $sqlFiles = ['create_admin_users.sql', 'create_user_management_tables.sql'];
    
    foreach ($sqlFiles as $file) {
        $sql = file_get_contents(__DIR__ . '/sql/' . $file);
        $conn->exec($sql);
    }

    echo "資料庫表初始化成功\n";
} catch (PDOException $e) {
    die("資料庫初始化失敗：" . $e->getMessage());
}