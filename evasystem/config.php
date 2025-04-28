<?php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_DATABASE', 'EvaMakeup');
define('DB_USERNAME', 'eva');
define('DB_PASSWORD', 'Aa.730216');

// 建立資料庫連線
function getConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE . ";charset=utf8";
    try {
        return new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        die("資料庫連線失敗：" . $e->getMessage());
    }
}
?>
