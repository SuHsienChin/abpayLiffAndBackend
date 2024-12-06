<?php
require 'config.php'; // 包含資料庫連線設定

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $phone = $_GET['phone'];
    $pdo = getConnection();

    // 查詢客戶資料
    $stmt = $pdo->prepare("SELECT id, name, balance FROM customers WHERE phone = ?");
    $stmt->execute([$phone]);
    $customer = $stmt->fetch();

    if (!$customer) {
        echo "<div class='alert alert-danger mt-3'>找不到此客戶！請檢查輸入的電話號碼。</div>";
        exit;
    }

    // 查詢交易記錄
    $stmt = $pdo->prepare("SELECT status, item, date, amount, balance, signature FROM transactions WHERE customer_id = ?");
    $stmt->execute([$customer['id']]);
    $transactions = $stmt->fetchAll();

    // 顯示結果
    echo "<h4 class='mt-4'>客戶姓名：{$customer['name']}（餘額：{$customer['balance']}）</h4>";
    echo "<table class='table table-bordered mt-3'>
            <thead class='table-light'>
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
