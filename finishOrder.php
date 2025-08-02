<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>訂單完成</title>
    <!-- 引入Bootstrap 5的CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入Font Awesome圖標 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- 引入LIFF SDK -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="js/userActionLogger.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .order-card {
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border: none;
            transition: transform 0.3s;
        }
        .order-card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .order-id {
            font-weight: bold;
            color: #495057;
        }
        .message-box {
            background-color: #f1f8ff;
            border-left: 4px solid #2575fc;
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 0 5px 5px 0;
        }
        .btn-check-order {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            padding: 10px 20px;
            margin-top: 1rem;
        }
        .btn-check-order:hover {
            background: linear-gradient(135deg, #5a0cb6 0%, #1565e6 100%);
        }
        @media (max-width: 576px) {
            .card-header h3 {
                font-size: 1.5rem;
            }
            .success-icon {
                font-size: 3rem;
            }
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-12">
                <div class="card order-card">
                    <div class="card-header text-center">
                        <h3><i class="fas fa-shopping-cart me-2"></i>訂單完成</h3>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4 class="mb-4">您的訂單已成功提交！</h4>
                        
                        <div class="order-details p-3 bg-light rounded">
                            <div class="row mb-3">
                                <div class="col-5 text-start">訂單編號：</div>
                                <div class="col-7 text-start order-id">
                                    <?php echo htmlspecialchars($_GET["orderId"], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                            
                            <div class="message-box">
                                <p id='order_finish_display_message' class="mb-0"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 引入Bootstrap 5的JS和jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 初始化 LIFF
        $(document).ready(function() {
            // 記錄用戶操作
            logUserAction('finish_order', '完成下單', {
                orderId: new URLSearchParams(window.location.search).get('orderId')
            });
        });

        // 獲取訂單完成顯示訊息
        axios.get('get_order_finish_display_messages.php')
            .then(function(response) {
                // 嘗試從 sessionStorage 獲取客戶資料
                let customerData;
                try {
                    customerData = JSON.parse(sessionStorage.getItem('customerData'));
                } catch (e) {
                    console.error('無法解析客戶資料:', e);
                }

                // 如果客戶資料存在，根據客戶組別顯示對應訊息
                if (customerData && customerData.Id) {
                    const groupCode = customerData.Id.charAt(0);
                    let messageFound = false;
                    
                    response.data.forEach(function(item) {
                        if ((groupCode === 'S' && item.title === '晴子') ||
                            (groupCode === 'W' && item.title === '沐沐代儲') ||
                            (groupCode === 'A' && item.title === '艾比代')) {
                            try {
                                $('#order_finish_display_message').html(JSON.parse(item.content));
                                messageFound = true;
                            } catch (e) {
                                console.error('解析訊息內容錯誤:', e);
                            }
                        }
                    });
                    
                    // 如果沒有找到對應訊息，使用預設訊息
                    if (!messageFound) {
                        response.data.forEach(function(item) {
                            if (item.title === '艾比代') {
                                try {
                                    $('#order_finish_display_message').html(JSON.parse(item.content));
                                } catch (e) {
                                    console.error('解析預設訊息內容錯誤:', e);
                                }
                            }
                        });
                    }
                } else {
                    // 如果沒有客戶資料，顯示預設訊息
                    $('#order_finish_display_message').html('感謝您的訂購！我們將盡快處理您的訂單。<br>如有任何問題，請聯繫客服。');
                }
            })
            .catch((error) => {
                console.error('獲取訊息錯誤:', error);
                $('#order_finish_display_message').html('感謝您的訂購！我們將盡快處理您的訂單。<br>如有任何問題，請聯繫客服。');
            });

        sessionStorage.clear();
    </script>
</body>

</html>