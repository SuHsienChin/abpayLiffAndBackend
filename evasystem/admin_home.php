<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>後台管理 - Eva微妝美學</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <!-- 主標題 -->
        <h1 class="text-center mb-4">後台管理 - Eva微妝美學</h1>

        <!-- 選單卡片 -->
        <div class="row">
            <!-- 建立客戶資料 -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">建立客戶資料</h5>
                        <p class="card-text text-muted">新增客戶基本資訊。</p>
                        <a href="add_customer.php" class="btn btn-primary">進入</a>
                    </div>
                </div>
            </div>

            <!-- 增加餘額 -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">增加餘額</h5>
                        <p class="card-text text-muted">為客戶帳戶增加儲值金額。</p>
                        <a href="add_balance.php" class="btn btn-primary">進入</a>
                    </div>
                </div>
            </div>

            <!-- 消費 -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">消費</h5>
                        <p class="card-text text-muted">扣除客戶餘額並記錄消費。</p>
                        <a href="add_transaction.php" class="btn btn-primary">進入</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
