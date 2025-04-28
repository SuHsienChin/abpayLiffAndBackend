<?php
// 假设您已经连接到数据库
$pdo = new PDO('mysql:host=localhost;dbname=abpaytw_abpay', 'abpaytw_abpay', 'Aa.730216');

// 准备和执行查询
$query = $pdo->query('SELECT * FROM orders ORDER BY `orders`.`created_at` DESC');
$orders = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($orders as $order) {
    echo '<tr>';
    echo '<td>' . $order['customerId'] . '</td>';
    echo '<td><a href="#" class="order-detail-link" data-order-id="' . $order['orderId'] . '">' . $order['orderId'] . '</a></td>';
    echo '<td>' . $order['gameName'] . '</td>';
    echo '<td>' . $order['status'] . '</td>';
    echo '</tr>';
}
?>
