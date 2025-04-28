<?php

// 連接資料庫
require_once 'databaseConnection.php';
// 假设您已经连接到数据库


class Orders
{
    //取得訂單所有資料
    public function getAllData()
    {
        $connection = new DatabaseConnection();
        $pdo = $connection->connect();
        // 准备和执行查询
        $query = $pdo->query('SELECT * FROM orders ORDER BY `orders`.`created_at` DESC');
        $orders = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as $order) {
            echo '<tr>';
            echo '<td>' . $order['customerId'] . '</td>';
            echo '<td><a href="#" class="customer-balance-link" data-customer-lineId="' . $order['lineId'] . '">餘額</a></td>';
            echo '<td><a href="#" class="order-detail-link" data-order-id="' . $order['orderId'] . '">' . $order['orderId'] . '</a></td>';
            echo '<td>' . $order['gameName'] . '</td>';
            echo '<td>' . $order['acount'] . '</td>';
            echo '<td>' . $order['gameAccountName'] . '</td>';
            //echo '<td>' . $order['status'] . '</td>';
            echo '<td>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input statusButton"  type="radio" name="' . $order['orderId'] . '" value="訂單處理中" ';
            if ($order['status'] == '訂單處理中') {
                echo ' checked ';
            }

            echo '}>
                                        <label class="form-check-label">訂單處理中</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input statusButton" type="radio" name="' . $order['orderId'] . '" value="已完成" ';
            if ($order['status'] == '已完成') {
                echo ' checked ';
            }
            echo '}>
                                        <label class="form-check-label">已完成</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input statusButton" type="radio" name="' . $order['orderId'] . '" value="已取消" ';
            if ($order['status'] == '已取消') {
                echo ' checked ';
            }
            echo '}>
                                        <label class="form-check-label">已取消</label>
                                    </div>
                                </td>';
            echo '</tr>';
        }
    }

    //取得訂單已完成資料
    public function getFinishData()
    {
        $connection = new DatabaseConnection();
        $pdo = $connection->connect();
        // 准备和执行查询
        $query = $pdo->query("SELECT * FROM orders WHERE `status` =  '已完成'  ORDER BY `orders`.`created_at` DESC");
        $orders = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as $order) {
            echo '<tr>';
            echo '<td>' . $order['customerId'] . '</td>';
            echo '<td><a href="#" class="order-detail-link" data-order-id="' . $order['orderId'] . '">' . $order['orderId'] . '</a></td>';
            echo '<td>' . $order['gameName'] . '</td>';
            echo '<td>' . $order['acount'] . '</td>';
            echo '<td>' . $order['gameAccountName'] . '</td>';
            //echo '<td>' . $order['status'] . '</td>';
            echo '<td>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input statusButton"  type="radio" name="' . $order['orderId'] . '" value="訂單處理中" ';
            if ($order['status'] == '訂單處理中') {
                echo ' checked ';
            }

            echo '}>
                                        <label class="form-check-label">訂單處理中</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input statusButton" type="radio" name="' . $order['orderId'] . '" value="已完成" ';
            if ($order['status'] == '已完成') {
                echo ' checked ';
            }
            echo '}>
                                        <label class="form-check-label">已完成</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input statusButton" type="radio" name="' . $order['orderId'] . '" value="已取消" ';
            if ($order['status'] == '已取消') {
                echo ' checked ';
            }
            echo '}>
                                        <label class="form-check-label">已取消</label>
                                    </div>
                                </td>';
            echo '</tr>';
        }
    }

    //取得訂單已取消資料
    public function getCancelData()
    {
        $connection = new DatabaseConnection();
        $pdo = $connection->connect();
        // 准备和执行查询
        $query = $pdo->query("SELECT * FROM orders WHERE `status` =  '已取消'  ORDER BY `orders`.`created_at` DESC");
        $orders = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as $order) {
            echo '<tr>';
            echo '<td>' . $order['customerId'] . '</td>';
            echo '<td><a href="#" class="order-detail-link" data-order-id="' . $order['orderId'] . '">' . $order['orderId'] . '</a></td>';
            echo '<td>' . $order['gameName'] . '</td>';
            echo '<td>' . $order['acount'] . '</td>';
            echo '<td>' . $order['gameAccountName'] . '</td>';
            //echo '<td>' . $order['status'] . '</td>';
            echo '<td>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input statusButton"  type="radio" name="' . $order['orderId'] . '" value="訂單處理中" ';
            if ($order['status'] == '訂單處理中') {
                echo ' checked ';
            }

            echo '}>
                                        <label class="form-check-label">訂單處理中</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input statusButton" type="radio" name="' . $order['orderId'] . '" value="已完成" ';
            if ($order['status'] == '已完成') {
                echo ' checked ';
            }
            echo '}>
                                        <label class="form-check-label">已完成</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input statusButton" type="radio" name="' . $order['orderId'] . '" value="已取消" ';
            if ($order['status'] == '已取消') {
                echo ' checked ';
            }
            echo '}>
                                        <label class="form-check-label">已取消</label>
                                    </div>
                                </td>';
            echo '</tr>';
        }
    }
}


?>