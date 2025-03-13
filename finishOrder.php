<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>確認下單</title>
    <!-- 引入Bootstrap 4的CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="js/userActionLogger.js"></script>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>確認下單內容</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <fieldset class="border p-4">
                                <h4>訂單編號：
                                    <?php echo htmlspecialchars($_GET["orderId"], ENT_QUOTES, 'UTF-8'); ?>
                                </h4>
                                <label id='order_finish_display_message'></label>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 引入Bootstrap 4的JS和jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"
        integrity="sha512-2rNj2KJ+D8s1ceNasTIex6z4HWyOnEYLVC3FigGOmyQCZc2eBXKgOxQmo3oKLHyfcj53uz4QMsRCWNbLd32Q1g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // 在頁面載入時記錄
        $(document).ready(function() {
            logUserAction('finish_order', '完成下單', {
                orderId: new URLSearchParams(window.location.search).get('orderId')
            });
        });

        axios.get('get_order_finish_display_messages.php')
            .then(function(response) {
                //console.log(JSON.parse(response.data[0].content));

                //取得客人資料
                const customerData = JSON.parse(sessionStorage.getItem('customerData'));
                const groupCode = customerData.Id.charAt(0);

                if (groupCode === 'S') {
                    response.data.forEach(function(item, i) {
                        if (item.title === '晴子') {
                            $('#order_finish_display_message').text(JSON.parse(item.content))
                        }
                    });
                }
                if (groupCode === 'W') {
                    response.data.forEach(function(item, i) {
                        if (item.title === '沐沐代儲') {
                            $('#order_finish_display_message').text(JSON.parse(item.content))
                        }
                    });
                }
                if (groupCode === 'A') {
                    response.data.forEach(function(item, i) {
                        if (item.title === '艾比代') {
                            $('#order_finish_display_message').text(JSON.parse(item.content))
                        }
                    });
                } else {
                    if (item.title === '艾比代') {
                        $('#order_finish_display_message').text(JSON.parse(item.content))
                    }
                }
            })
            .catch((error) => console.log(error))

        sessionStorage.clear();
    </script>
</body>

</html>