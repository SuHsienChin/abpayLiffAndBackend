<?php
try {
    $db = new PDO('mysql:host=localhost;port=3306;dbname=abpaytw_abpay', 'abpay', 'Aa.730216');
    $stmt = $db->query("SHOW TABLES LIKE 'system_logs'");
    echo $stmt->rowCount() > 0 ? "Table exists\n" : "Table does not exist\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}