<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>確認下單</title>
    <!-- 引入Bootstrap 4的CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <!-- 引入使用者操作日誌模組 -->
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
                        <form>
                            <div class="form-group">
                                <fieldset class="border p-4">
                                    <legend class="w-auto">下單資料</legend>
                                    客戶編號: <label id='customerId'></label></br>
                                    下單時間: <label id='orderDateTime'></label></br>
                                    遊戲名稱: <label id='gameNameText'></label></br>
                                    登入方式: <label id='loginType'></label></br>
                                    遊戲帳號: <label id='gameAccount'></label></br>
                                    遊戲密碼: <label id='loginPassword'></label></br>
                                    伺 服 器: <label id='serverName'></label></br>
                                    角色名稱: <label id='characters'></label></br></br>

                                    <label id='gameItems'></label></br></br>
                                    總計: <label id='sumMoney'></label> <label id='customerCurrency'></label></br>
                                    備註: </br><label id='gameRemark'></label>
                                </fieldset>

                            </div>
                            <button type="button" class="btn btn-success btn-block"
                                onclick="confirmOrder()">確認下單</button>
                            <button type="button" class="btn btn-secondary btn-block mt-2"
                                onclick="Utils.goback()">回上一頁</button>
                        </form>
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
    <!-- 引入LINE LIFF SDK -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <!-- 引入訂單處理模組 -->
    <script src="js/orderModule.js"></script>

    <script>
        // 防止重複提交標記
        let isSubmitting = false;
        
        // 確認下單
        async function confirmOrder() {
            // 如果正在提交中，則直接返回
            if (isSubmitting) {
                console.log('訂單正在處理中，請勿重複提交');
                alert('訂單正在處理中，請勿重複提交');
                return;
            }
            
            logUserAction('order_check', '確認下單');
            if (confirm("確認下單？")) {
                // 設置提交標記為true
                isSubmitting = true;
                // 禁用所有按鈕，防止重複點擊
                $('.btn').prop('disabled', true);
                // 顯示處理中的提示
                const confirmBtn = $('.btn-success');
                const originalText = confirmBtn.text();
                confirmBtn.text('處理中...');
                try {
                    await OrderProcessor.sendOrder();
                } catch (error) {
                    console.error('訂單提交過程中發生錯誤:', error);
                    alert('訂單提交過程中發生錯誤，請稍後再試');
                } finally {
                    setTimeout(() => {
                        isSubmitting = false;
                        // 恢復按鈕文字
                        //confirmBtn.text(originalText);
                        // 啟用所有按鈕
                        //$('.btn').prop('disabled', false);
                    }, 5000); // 延遲3秒重置，防止快速重複點擊
                }
            }
        }

        // 頁面載入完成後執行
        $(function() {
            // 初始化LIFF應用
            LiffManager.initializeLiff('2000183731-BLmrAGPp');

            // 記錄使用者進入確認頁面的操作
            logUserAction('order_check', '進入確認頁面', {
                gameItems: sessionStorage.getItem('gameItemSelectedTexts'),
                sumMoney: sessionStorage.getItem('sumMoney')
            });

            // 獲取匯率和初始化訂單資料
            initializeOrderData();
        });

        // 初始化訂單資料
        function initializeOrderData() {
            // 獲取匯率資料
            axios.get('getRate.php')
                .then(function(response) {
                    const result = response.data.sort((a, b) => {
                        return a.Sid > b.Sid ? 1 : -1;
                    });

                    const orderDateTime = new Date().toLocaleString('en-ZA');

                    // 儲存匯率和訂單時間到sessionStorage
                    sessionStorage.setItem('rate', '');
                    sessionStorage.setItem('rate', JSON.stringify(result[4].Name.split(";;")));
                    sessionStorage.setItem('orderDateTime', orderDateTime);

                    // 計算下單商品價格
                    const itemArr = PriceCalculator.calculateTotalPrice();

                    // 儲存商品價格資訊到sessionStorage
                    const itemMoneyText = itemArr.itemMoneyText.slice(0, -1);
                    sessionStorage.setItem('itemMoney', itemArr.itemMoneyText);
                    sessionStorage.setItem('sumMoney', itemArr.sumMoney + itemArr.customerCurrency);

                    // 檢查餘額是否足夠
                    OrderProcessor.checkBalance(itemArr.sumMoney);

                    // 更新頁面顯示
                    updateOrderDisplay(itemArr, orderDateTime);

                    // 記錄訂單初始資料
                    logInitialOrderData(itemArr, orderDateTime);
                })
                .catch((error) => console.log(error));
        }

        // 更新訂單顯示
        function updateOrderDisplay(itemArr, orderDateTime) {
            $('#customerId').html(JSON.parse(sessionStorage.getItem('customerData')).Id);
            $('#orderDateTime').html(orderDateTime);
            $('#gameNameText').html(sessionStorage.getItem('gameNameText'));
            $('#gameAccount').html(sessionStorage.getItem('gameAccount'));
            $('#loginType').html(sessionStorage.getItem('login_type'));
            $('#loginPassword').html(sessionStorage.getItem('login_password'));
            $('#serverName').html(sessionStorage.getItem('server_name'));
            $('#characters').html(sessionStorage.getItem('characters'));
            $('#gameItems').html(itemArr.gameitemSLabelText);
            $('#sumMoney').html(itemArr.sumMoney);
            $('#customerCurrency').html(itemArr.customerCurrency);
            $('#gameRemark').html(sessionStorage.getItem('gameRemark').replaceAll('\n', '</br>'));
        }

        // 記錄初始訂單資料
        function logInitialOrderData(itemArr, orderDateTime) {
            try {
                const params_json_data = {
                    "gameName": sessionStorage.getItem('gameNameText'),
                    "UserId": "test01",
                    "Password": "111111",
                    "GameAccount": sessionStorage.getItem('gameAccount'),
                    "Item": itemArr.gameitemSLabelText,
                    "Count": sessionStorage.getItem('gameItemCounts'),
                    "lineId": sessionStorage.getItem('lineId'),
                    "customerId": JSON.parse(sessionStorage.getItem('customerData')).Id,
                    "gameItemsName": JSON.stringify(sessionStorage.getItem('gameItemSelectedTexts')),
                    "gameItemCounts": JSON.stringify(sessionStorage.getItem('gameItemCounts')),
                    "logintype": sessionStorage.getItem('login_type'),
                    "acount": sessionStorage.getItem('login_account'),
                    "password": sessionStorage.getItem('login_password'),
                    "serverName": sessionStorage.getItem('server_name'),
                    "gameAccountName": sessionStorage.getItem('characters'),
                    "gameAccountSid": sessionStorage.getItem('gameAccountSid'),
                    "status": "訂單處理中",
                    "itemsMoney": itemArr.itemMoneyText,
                    "sumMoney": itemArr.sumMoney,
                    "orderDateTime": orderDateTime,
                    "gameRemark": sessionStorage.getItem('gameRemark').replaceAll('\n', '</br>')
                };

                // 記錄使用者的參數log
                OrderProcessor.saveLogsToMysql('在order_check.php一進入時的訂單內容', params_json_data);
            } catch (e) {
                console.log('記錄初始訂單資料錯誤：\n' + e);
            }
        }
    </script>
</body>

</html>
