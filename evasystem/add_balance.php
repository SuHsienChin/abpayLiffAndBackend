<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $amount = $_POST['amount'];

    $pdo = getConnection();
    try {
        $pdo->beginTransaction();

        // 查詢客戶
        $stmt = $pdo->prepare("SELECT id, balance FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch();

        if (!$customer) {
            echo "客戶不存在！";
            exit;
        }

        // 更新餘額
        $new_balance = $customer['balance'] + $amount;
        $stmt = $pdo->prepare("UPDATE customers SET balance = ? WHERE id = ?");
        $stmt->execute([$new_balance, $customer['id']]);

        // 新增交易記錄
        $stmt = $pdo->prepare("INSERT INTO transactions (customer_id, status, item, date, amount, balance) VALUES (?, '儲值', '儲值台幣', NOW(), ?, ?)");
        $stmt->execute([$customer['id'], $amount, $new_balance]);

        $pdo->commit();
        echo "餘額新增成功！";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "餘額新增失敗：" . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>增加餘額</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="text-center mb-4">增加餘額</h2>
        <form action="add_balance.php" method="POST" class="bg-white p-4 rounded shadow-sm">
            <div class="mb-3">
                <label for="phone" class="form-label">客戶電話</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">儲值金額 (台幣)</label>
                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">增加餘額</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>