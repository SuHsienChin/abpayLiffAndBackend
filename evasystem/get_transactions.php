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
        echo "<div class='container py-5'>
                <div class='alert alert-danger text-center'>找不到此客戶！請檢查輸入的電話號碼。</div>
              </div>";
        exit;
    }

    // 查詢交易記錄
    $stmt = $pdo->prepare("SELECT status, item, date, amount, balance, signature FROM transactions WHERE customer_id = ?");
    $stmt->execute([$customer['id']]);
    $transactions = $stmt->fetchAll();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>消費記錄查詢結果</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <!-- 客戶資訊 -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">客戶資訊</h5>
                    <p class="card-text">
                        <strong>姓名：</strong> <?php echo htmlspecialchars($customer['name']); ?><br>
                        <strong>餘額：</strong> <?php echo htmlspecialchars($customer['balance']); ?> 台幣
                    </p>
                </div>
            </div>

            <!-- 消費記錄表格 -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">消費記錄</h5>
                    <?php if (count($transactions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>狀態</th>
                                        <th>使用項目</th>
                                        <th>日期</th>
                                        <th>金額</th>
                                        <th>餘額</th>
                                        <th>簽名</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['status']); ?></td>
                                            <td><?php echo htmlspecialchars($record['item']); ?></td>
                                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                                            <td><?php echo htmlspecialchars($record['amount']); ?></td>
                                            <td><?php echo htmlspecialchars($record['balance']); ?></td>
                                            <td>
                                                <?php if (!empty($record['signature']) && file_exists($record['signature'])): ?>
                                                    <img src="<?php echo htmlspecialchars($record['signature']); ?>" alt="簽名圖片" class="img-fluid" style="max-width: 150px;">
                                                <?php else: ?>
                                                    無
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">目前無任何消費記錄。</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 返回按鈕 -->
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-secondary">返回查詢頁</a>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
<?php
}
?>
