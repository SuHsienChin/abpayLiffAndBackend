<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $birthday = $_POST['birthday'];
    $line_id = $_POST['line_id'];
    $amount = $_POST['amount'];

    $pdo = getConnection();
    try {
        $pdo->beginTransaction();

        // 確認客戶是否已存在
        $stmt = $pdo->prepare("SELECT id, balance FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch();

        if ($customer) {
            // 更新餘額
            $new_balance = $customer['balance'] + $amount;
            $stmt = $pdo->prepare("UPDATE customers SET balance = ? WHERE id = ?");
            $stmt->execute([$new_balance, $customer['id']]);
            $customer_id = $customer['id'];
        } else {
            // 新增客戶
            $stmt = $pdo->prepare("INSERT INTO customers (name, phone, birthday, line_id, balance) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $birthday, $line_id, $amount]);
            $customer_id = $pdo->lastInsertId();
        }

        // 新增儲值記錄
        $stmt = $pdo->prepare("INSERT INTO transactions (customer_id, status, item, date, amount, balance) VALUES (?, '儲值', '儲值台幣', NOW(), ?, ?)");
        $stmt->execute([$customer_id, $amount, $amount]);

        $pdo->commit();
        echo "儲值成功！";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "儲值失敗：" . $e->getMessage();
    }
}
?>
