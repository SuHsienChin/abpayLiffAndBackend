<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $phone = $_GET['phone'];
    $pdo = getConnection();

    // 查詢客戶
    $stmt = $pdo->prepare("SELECT id, name, balance FROM customers WHERE phone = ?");
    $stmt->execute([$phone]);
    $customer = $stmt->fetch();

    if (!$customer) {
        echo "找不到客戶！";
        exit;
    }

    // 查詢交易記錄
    $stmt = $pdo->prepare("SELECT status, item, date, amount, balance, signature FROM transactions WHERE customer_id = ?");
    $stmt->execute([$customer['id']]);
    $transactions = $stmt->fetchAll();

    // 顯示結果
    echo "<h3>客戶姓名：" . $customer['name'] . "（餘額：" . $customer['balance'] . "）</h3>";
    echo "<table class='table table-bordered'>
            <thead>
                <tr>
                    <th>狀態</th>
                    <th>使用項目</th>
                    <th>日期</th>
                    <th>金額</th>
                    <th>餘額</th>
                    <th>簽名</th>
                </tr>
            </thead>
            <tbody>";
    foreach ($transactions as $record) {
        echo "<tr>
                <td>{$record['status']}</td>
                <td>{$record['item']}</td>
                <td>{$record['date']}</td>
                <td>{$record['amount']}</td>
                <td>{$record['balance']}</td>
                <td>" . ($record['signature'] ? "<a href='data:image/png;base64,{$record['signature']}'>查看</a>" : "無") . "</td>
              </tr>";
    }
    echo "</tbody></table>";
}
?>
