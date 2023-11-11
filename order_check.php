<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>確認下單</title>
    <!-- 引入Bootstrap 4的CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
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
                                    ➤下單日期：<label></label></br>
                                    ➤遊戲名稱：<label id='gameNameText'></label></br>
                                    ➤登入方式：<label id='loginType'></label></br>
                                    ➤遊戲帳號：<label id='gameAccount'></label></br>
                                    ➤遊戲密碼：<label id='loginPassword'></label></br>
                                    ➤伺服器：<label id='serverName'></label></br>
                                    ➤角色名稱：<label id='characters'></label></br>
                                    ➤ID編號：<label></label></br>
                                    ➤下單商品確認：</br></br>
                                    <label id='gameItems'></label></br></br>
                                    總計: <label id='sumMoney'></label> <label id='customerCurrency'></label>
                                </fieldset>

                            </div>
                            <button type="button" class="btn btn-success btn-block"
                                onclick="confirmOrder()">確認下單</button>
                            <button type="button" class="btn btn-secondary btn-block mt-2"
                                onclick="window.history.go(-1);">回上一頁</button>
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
    <script>
        function confirmOrder() {
            if (confirm("確認下單？")) {
                sendOrder();
            }
        }


        //取得rate匯率
        axios.get('getRate.php')
            .then(function (response) {

                setTimeout(function () {
                    // 在這裡執行您的程式碼
                }, 1000); // 2000 毫秒 = 2 秒

                const result = response.data.sort((a, b) => {
                    return a.Sid > b.Sid
                        ? 1
                        : -1;
                });
                sessionStorage.setItem('rate', '');
                sessionStorage.setItem('rate', JSON.stringify(result[4].Name.split(";;")));

                //設定rate匯率
                const rate = JSON.parse(sessionStorage.getItem('rate'));

                //取得下單遊戲裡的gameRate
                const gameRate = JSON.parse(sessionStorage.getItem('gameRate'));

                //取得客人資料
                const customerData = JSON.parse(sessionStorage.getItem('customerData'));

                //取得客人的幣別
                const customerCurrency = customerData.Currency;

                //依客人幣別來比對匯率並取得匯率值
                let rateValue = 0.000;
                $.each(rate, function (i, item) {
                    if (item.includes(customerCurrency)) {
                        rateValue = item.split(",")[2];
                    }
                });

                const gameItemSelectedValues = JSON.parse(sessionStorage.getItem('gameItemSelectedValues'));
                const gameItemSelectedTexts = JSON.parse(sessionStorage.getItem('gameItemSelectedTexts'));
                const gameItemCounts = JSON.parse(sessionStorage.getItem('gameItemCounts'));
                const gameItemBouns = JSON.parse(sessionStorage.getItem('gameItemBouns'));
                let gameitemSLabelText = '';
                let itemMoney = 0;
                let sumMoney = 0;
                let itemMoneyText = '';

                $.each(gameItemSelectedValues, function (i, item) {
                    itemMoney = calculateMoney(gameRate, gameItemBouns[i], rateValue, gameItemCounts[i]);
                    sumMoney += itemMoney;
                    gameitemSLabelText += (i + 1) + '. ' + gameItemSelectedTexts[i] + ' X ' + gameItemCounts[i] + ' = ' + itemMoney + '<br />';
                    itemMoneyText += itemMoney + ','
                });

                itemMoneyText = itemMoneyText.slice(0, -1);
                sessionStorage.setItem('itemMoney', itemMoneyText);
                sessionStorage.setItem('sumMoney', sumMoney + customerCurrency);

                $('#gameNameText').html(sessionStorage.getItem('gameNameText'));
                $('#gameAccount').html(sessionStorage.getItem('gameAccount'));
                $('#loginType').html(sessionStorage.getItem('login_type'));
                $('#loginPassword').html(sessionStorage.getItem('login_password'));
                $('#serverName').html(sessionStorage.getItem('server_name'));
                $('#characters').html(sessionStorage.getItem('characters'));
                $('#gameItems').html(gameitemSLabelText);
                $('#sumMoney').html(sumMoney);
                $('#customerCurrency').html(customerCurrency);

            })
            .catch((error) => console.log(error))




        //計算每個商品乘上數量後的價格
        function calculateMoney(gameRate, bouns, rateValue, count) {
            //console.log('gameRate=' + gameRate + 'bouns=' + bouns + 'rateValue=' + rateValue + 'count=' + count);
            if (rateValue == 1) {
                return Math.ceil(gameRate * bouns * rateValue) * count;
            } else {
                return Math.ceil(gameRate * bouns / rateValue) * count;
            }
        }

        // 以下為訂單送出時要做的事
        // 1.檢查商品是不是可以在同一筆訂單
        // 2.存訂單狀態在自己的資料庫
        // 3.組合下單字串


        function sendOrder() {
            const item = String(JSON.parse(sessionStorage.getItem('gameItemSelectedValues')));
            const gameItemCounts = String(JSON.parse(sessionStorage.getItem('gameItemCounts')));
            const itemMoney = sessionStorage.getItem('itemMoney');
            const sumMoney = sessionStorage.getItem('sumMoney');
            const customer = JSON.parse(sessionStorage.getItem('customerData')).Sid;
            const account = JSON.parse(sessionStorage.getItem('gameAccountSid'));
            const lineId = sessionStorage.getItem('lineId');
            const customerData = JSON.parse(sessionStorage.getItem('customerData'));
            const gameName = sessionStorage.getItem('gameNameText');
            const gameItemsName = String(JSON.parse(sessionStorage.getItem('gameItemSelectedTexts')));
            const customerGameAccounts = JSON.parse(sessionStorage.getItem('customerGameAccounts'))[0];
            const UrlParametersString = 'UserId=test01&Password=111111&Customer=' + customer +
                '&GameAccount=' + account +
                '&Item=' + item +
                '&Count=' + gameItemCounts
                // '&lineId=' + lineId +
                // '&customerId=' + customerData.Id +
                // '&gameName=' + gameName +
                // '&gameItemsName=' + gameItemsName +
                // '&gameItemCounts=' + gameItemCounts +
                // '&logintype=' + customerGameAccounts.LoginType +
                // '&acount=' + customerGameAccounts.LoginAccount +
                // '&password=' + customerGameAccounts.LoginPassword +
                // '&serverName=' + customerGameAccounts.ServerName +
                // '&gameAccountName=' + customerGameAccounts.Name +
                // '&gameAccountId=' + customerGameAccounts.Id +
                // '&gameAccountSid=' + customerGameAccounts.Sid +
                // '&customerSid=' + customerGameAccounts.CustomerId
                ;

            // let addOrderDataParametersJson = transferQueryStringToJSON(UrlParametersString)
            // orderId = (Math.random()*20000);
            // addOrderDataParametersJson = addNewParameterToJson(orderId, addOrderDataParametersJson)
            // insertOrderData(addOrderDataParametersJson);

            // 客人LINE的ID唯一值
            // 官方LINE的客編
            // 訂單編號
            // 遊戲名稱
            // 遊戲商品名稱複數
            // 遊戲商品數量複數
            // 登入方式
            // 遊戲帳號
            // 遊戲密碼
            // 遊戲伺服器
            // 遊戲角色名
            // 遊戲裡面的ID
            // 對應系統的遊戲帳號ID
            // 對應系統的客人ID

            var params = new URLSearchParams();

            params.append('gameName', gameName);
            params.append('UserId', 'test01');
            params.append('Password', '111111');
            params.append('Customer', customer);
            params.append('GameAccount', account);
            params.append('Item', item);
            params.append('Count', gameItemCounts);
            params.append('lineId', lineId);
            params.append('customerId', customerData.Id);
            params.append('gameItemsName', gameItemsName);
            params.append('gameItemCounts', gameItemCounts);
            params.append('logintype', customerGameAccounts.LoginType);
            params.append('acount', customerGameAccounts.LoginAccount);
            params.append('password', customerGameAccounts.LoginPassword);
            params.append('serverName', customerGameAccounts.ServerName);
            params.append('gameAccountName', customerGameAccounts.Characters);
            params.append('gameAccountId', customerGameAccounts.Id);
            params.append('gameAccountSid', customerGameAccounts.Sid);
            params.append('customerSid', customerGameAccounts.CustomerId);
            params.append('status', '訂單處理中');
            params.append('itemsMoney', itemMoney);
            params.append('sumMoney', sumMoney);


            axios.get('sendOrderUrlByCORS.php?' + UrlParametersString)
                .then(function (response) {
                    const resdata = response.data
                    let orderId = '';
                    console.log(resdata);
                    console.log(resdata.Status);
                    if (resdata.Status == '1') {
                        //let addOrderDataParametersJson = transferQueryStringToJSON(UrlParametersString)
                        orderId = resdata.OrderId;
                        params.append('orderId', orderId);
                        insertOrderData(params);
                        // addOrderDataParametersJson = addNewParameterToJson(orderId, addOrderDataParametersJson)
                        // insertOrderData(addOrderDataParametersJson);

                        alert('下單成功');
                        sessionStorage.clear();
                        window.location = "finishOrder.php?orderId=" + orderId;

                    } else {
                        alert('下單發生錯誤，請洽小編');
                    }
                })
                .catch(function (error) {
                    console.error('Error fetching :', error);
                });
        }

        // 新增訂單資料到資料庫
        function insertOrderData(params) {

            axios.post('addOrderData.php', params)
                .then(function (response) {
                    console.log('資料新增成功', response.data);
                    console.log(response.data);
                })
                .catch(function (error) {
                    console.error('資料新增失敗', error);
                });

        }

        // 把URL的QueryString轉換成JSON
        function transferQueryStringToJSON(queryString) {
            // 使用 URLSearchParams 解析字串
            const searchParams = new URLSearchParams(queryString);

            // 將 URLSearchParams 轉換為 JavaScript 物件
            const obj = {};
            searchParams.forEach(function (value, key) {
                obj[key] = value;
            });

            // 將 JavaScript 物件轉換為 JSON 字串
            const jsonString = JSON.stringify(obj);
            return (jsonString)
        }

        // 把一個新的參數加到JSON裡面
        function addNewParameterToJson(value, jsonString) {
            // 將 JSON 字串解析為 JavaScript 物件
            const obj = JSON.parse(jsonString);

            // 新增參數到 JavaScript 物件
            obj.orderId = value;

            // 再將 JavaScript 物件轉換為 JSON 字串
            const newJsonString = JSON.stringify(obj);

            return (newJsonString);
        }


    </script>
</body>

</html>