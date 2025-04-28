<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eva微妝美學</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <!-- 主標題 -->
        <h1 class="text-center mb-4">Eva微妝美學 - 客戶消費記錄查詢</h1>

        <!-- 查詢表單 -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-center mb-3">請輸入電話號碼查詢</h5>
                <form action="get_transactions.php" method="GET">
                    <div class="mb-3">
                        <label for="phone" class="form-label">電話號碼</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="例如：0912345678" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">查詢紀錄</button>
                </form>
            </div>
        </div>

        <!-- 查詢結果 -->
        <div id="results" class="mt-5">
            <!-- 查詢結果會顯示在這裡 -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
