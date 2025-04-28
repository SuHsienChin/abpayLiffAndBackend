<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>後台管理 - Eva微妝美學</title>
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

    <div class="container py-5">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h1 class="display-6">後台管理</h1>
                <p class="text-muted">管理客戶資料、儲值與消費記錄。</p>
            </div>
        </div>

        <div class="row">
            <!-- 建立客戶資料 -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">建立客戶資料</h5>
                        <p class="card-text text-muted">新增新客戶的基本資料。</p>
                        <a href="add_customer.php" class="btn btn-primary">進入</a>
                    </div>
                </div>
            </div>

            <!-- 增加餘額 -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">增加餘額</h5>
                        <p class="card-text text-muted">為客戶新增儲值金額。</p>
                        <a href="add_balance.php" class="btn btn-primary">進入</a>
                    </div>
                </div>
            </div>

            <!-- 消費記錄查詢 -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">消費記錄查詢</h5>
                        <p class="card-text text-muted">查看客戶消費與儲值記錄。</p>
                        <a href="admin_transactions.php" class="btn btn-primary">進入</a>
                    </div>
                </div>
            </div>

            <!-- 消費 -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">消費</h5>
                        <p class="card-text text-muted">為客戶新增消費記錄。</p>
                        <a href="add_transaction.php" class="btn btn-primary">進入</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">© 2024 Eva微妝美學. All rights reserved. </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
