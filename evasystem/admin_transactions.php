<?php
require 'config.php'; // 引入資料庫連線設定

$phoneFilter = '';
if (isset($_GET['phone'])) {
    $phoneFilter = $_GET['phone'];
}

$pdo = getConnection();

// 查詢消費記錄
if ($phoneFilter) {
    $stmt = $pdo->prepare("
        SELECT t.*, c.name, c.phone 
        FROM transactions t
        JOIN customers c ON t.customer_id = c.id
        WHERE c.phone = ?
        ORDER BY t.date DESC
    ");
    $stmt->execute([$phoneFilter]);
} else {
    $stmt = $pdo->prepare("
        SELECT t.*, c.name, c.phone 
        FROM transactions t
        JOIN customers c ON t.customer_id = c.id
        ORDER BY t.date DESC
    ");
    $stmt->execute();
}

$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>後台 - 消費記錄查詢</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_home.php">後台管理</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_home.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_customer.php">建立客戶資料</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_balance.php">增加餘額</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_transaction.php">消費</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_transactions.php">消費記錄查詢</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_home.php">後台管理</a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="display-6">消費記錄查詢</h1>
                <p class="text-muted">查詢所有消費記錄或依客戶電話篩選。</p>
            </div>
        </div>

        <!-- 篩選表單 -->
        <form class="mb-4" method="GET" action="admin_transactions.php">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label for="phone" class="form-label">客戶電話</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($phoneFilter); ?>" placeholder="輸入客戶電話">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">篩選</button>
                </div>
                <div class="col-md-3">
                    <a href="admin_transactions.php" class="btn btn-secondary w-100">顯示全部</a>
                </div>
            </div>
        </form>

        <!-- 消費記錄表格 -->
        <div class="card">
            <div class="card-body">
                <?php if (count($transactions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>客戶姓名</th>
                                    <th>電話</th>
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
                                        <td><?php echo htmlspecialchars($record['name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['phone']); ?></td>
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
                    <p class="text-muted">目前無相關消費記錄。</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">© 2024 Eva微妝美學. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
