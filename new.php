<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>遊戲下單</title>
    <!-- 引入Bootstrap 4的CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h2 class="text-center mb-4">遊戲下單</h2>
                <div class="form-group">
                    <label for="wallet_balance">錢包餘額：</label>
                    <span id="wallet_balance">1000</span> <!-- 這裡用span展示錢包餘額 -->
                </div>
                <div class="form-group">
                    <label for="game_name">遊戲名稱：</label>
                    <select class="form-control" id="game_name">
                        <option value="game1">遊戲1</option>
                        <option value="game2">遊戲2</option>
                        <option value="game3">遊戲3</option>
                        <!-- 其他遊戲選項 -->
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="form-group d-flex align-items-center">
                    <label for="game_product" class="mr-3">遊戲商品：</label>
                    <div class="flex-grow-1 mr-3">
                        <select class="form-control" id="game_product">
                            <option value="product1">商品1</option>
                            <option value="product2">商品2</option>
                            <option value="product3">商品3</option>
                            <!-- 其他商品選項 -->
                        </select>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="number" class="form-control mr-2" id="quantity" style="max-width: 70px;" placeholder="數量">
                        <button type="button" class="btn btn-danger">刪除</button>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-block">下單</button>
            </div>
        </div>
    </div>

    <!-- 引入Bootstrap 4的JS和jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
