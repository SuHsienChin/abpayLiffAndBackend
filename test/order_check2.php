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
                            <button type="button" class="btn btn-success btn-block" onclick="confirmOrder()">確認下單</button>
                            <!-- <button type="button" class="btn btn-secondary btn-block mt-2" onclick="window.history.go(-1);">回上一頁</button> -->
                            <button type="button" class="btn btn-secondary btn-block mt-2" onclick="goback()">回上一頁</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 引入Bootstrap 4的JS和jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js" integrity="sha512-2rNj2KJ+D8s1ceNasTIex6z4HWyOnEYLVC3FigGOmyQCZc2eBXKgOxQmo3oKLHyfcj53uz4QMsRCWNbLd32Q1g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- 以下是liff 要上線時需打開 -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <!-- <script>
        function initializeLiff(myLiffId) {
            liff
                .init({
                    liffId: myLiffId,
                    withLoginOnExternalBrowser: true, // Enable automatic login process
                })
                .then(() => {
                    initializeApp();
                })
                .catch((err) => {
                    console.log(err);
                    console.log('啟動失敗。');
                });
        }

        function initializeApp() {
            console.log('啟動成功。');
            liff.getProfile()
                .then(profile => {
                    // sessionStorage.setItem('lineUserId', profile.userId);
                    // const mylineId = $("#lineId").val(sessionStorage.getItem('lineUserId'));
                })
                .catch((err) => {
                    console.log('error', err);
                });
        }

        $(function () {
            //使用 LIFF_ID 初始化 LIFF 應用
            initializeLiff('2000183731-BLmrAGPp');
        });
    </script> -->
    <script>
        function confirmOrder() {
            if (confirm("確認下單？")) {
                sendOrder();
            }
        }


        //取得rate匯率
        axios.get('../getRate.php')
            .then(function(response) {

                setTimeout(function() {
                    // 在這裡執行您的程式碼
                }, 1000); // 2000 毫秒 = 2 秒

                const result = response.data.sort((a, b) => {
                    return a.Sid > b.Sid ?
                        1 :
                        -1;
                });

                const orderDateTime = new Date().toLocaleString('en-ZA');

                sessionStorage.setItem('rate', '');
                sessionStorage.setItem('rate', JSON.stringify(result[4].Name.split(";;")));
                sessionStorage.setItem('orderDateTime', orderDateTime);

                // 計算下單商品價格後回傳商品格式跟價格還有總計
                const itemArr = calculateMoneyAndReturn();

                itemMoneyText = itemArr.itemMoneyText.slice(0, -1);
                sessionStorage.setItem('itemMoney', itemArr.itemMoneyText);
                sessionStorage.setItem('sumMoney', itemArr.sumMoney + itemArr.customerCurrency);

                // 看餘額夠不夠下單，不夠的話不給下單
                checkBalance(itemArr.sumMoney);

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
                        //"gameAccountId": customerGameAccounts.Id,
                        "gameAccountSid": sessionStorage.getItem('gameAccountSid'),
                        //"customerSid": customerGameAccounts.CustomerId,
                        "status": "訂單處理中",
                        "itemsMoney": itemArr.itemMoneyText,
                        "sumMoney": itemArr.sumMoney,
                        "orderDateTime": orderDateTime,
                        "gameRemark": sessionStorage.getItem('gameRemark').replaceAll('\n', '</br>')
                    };

                    //紀錄使用者的參數log
                    saveLogsToMysql('在order_check.php一進入時的訂單內容', params_json_data);
                } catch (e) {
                    console.log('第172行錯誤：\n' + e);
                }

            })
            .catch((error) => console.log(error))


        //計算每個商品乘上數量後的價格
        function calculateMoney(gameRate, bouns, rateValue, count, customerCurrency) {
            //console.log('gameRate=' + gameRate + 'bouns=' + bouns + 'rateValue=' + rateValue + 'count=' + count);

            roundUp = function(num, decimal) {
                return Math.ceil((num + Number.EPSILON) * Math.pow(10, decimal)) / Math.pow(10, decimal);
            }

            if (rateValue == 1) {
                return Math.ceil(gameRate * bouns * rateValue) * count;
            } else {

                if (customerCurrency.includes('新')) {
                    return roundUp(gameRate * bouns / rateValue, 1) * count;
                }
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
            //這裡有問題
            //要比對 sessionStorage裡面的 gameAccount 需要跟 customerGameAccounts 一樣 才能放到 customerGameAccounts

            let customerGameAccount = filterGemeAccount(JSON.parse(sessionStorage.getItem('customerGameAccounts')), sessionStorage.getItem('gameAccountSid'));
            const customerGameAccounts = customerGameAccount[0];
            const orderDateTime = sessionStorage.getItem('orderDateTime');
            const gameRemark = sessionStorage.getItem('gameRemark');
            // const UrlParametersString = 'UserId=test01&Password=111111&Customer=' + customer +
            const UrlParametersString = 'UserId=test02&Password=3345678&Customer=' + customer +
                '&GameAccount=' + account +
                '&Item=' + item +
                '&Count=' + gameItemCounts;

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
            // 組參數
            try {
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
                params.append('orderDateTime', orderDateTime);
                params.append('gameRemark', gameRemark);
            } catch (e) {
                alert('組參數發生錯誤，請洽小編\n' + e);
            }

            //把參數用的json格式
            const params_json_data = {
                "gameName": gameName,
                "UserId": "test01",
                "Password": "111111",
                "Customer": customer,
                "GameAccount": account,
                "Item": item,
                "Count": gameItemCounts,
                "lineId": lineId,
                "customerId": customerData.Id,
                "gameItemsName": gameItemsName,
                "gameItemCounts": gameItemCounts,
                "logintype": customerGameAccounts.LoginType,
                "acount": customerGameAccounts.LoginAccount,
                "password": customerGameAccounts.LoginPassword,
                "serverName": customerGameAccounts.ServerName,
                "gameAccountName": customerGameAccounts.Characters,
                "gameAccountId": customerGameAccounts.Id,
                "gameAccountSid": customerGameAccounts.Sid,
                "customerSid": customerGameAccounts.CustomerId,
                "status": "訂單處理中",
                "itemsMoney": itemMoney,
                "sumMoney": sumMoney,
                "orderDateTime": orderDateTime,
                "gameRemark": gameRemark
            };

            //紀錄使用者的參數log
            saveLogsToMysql('在傳送訂單到官方LINE之前的params_json_data', params_json_data);


            // 傳送訂單內容到官方LINE
            sendMessagetoLineOfficial(params_json_data);

            try {
                axios.get('../sendOrderUrlByCORS.php?' + UrlParametersString)
                    .then(function(response) {
                        const resdata = response.data
                        let orderId = '';
                        console.log(resdata);
                        console.log(resdata.Status);
                        if (resdata.Status == '1') {
                            orderId = resdata.OrderId;
                            params.append('orderId', orderId);
                            insertOrderData(params);

                            alert('下單成功');

                            //sessionStorage.clear();
                            window.location = "finishOrder.php?orderId=" + orderId;

                        } else {
                            alert('下單發生錯誤，請洽小編');
                        }
                    })
                    .catch(function(error) {
                        console.error('Error fetching :', error);
                    });
            } catch (e) {
                alert('API下單錯誤，請洽小編\n' + e);
            }

        }

        // 新增訂單資料到資料庫
        function insertOrderData(params) {

            axios.post('addOrderData.php', params)
                .then(function(response) {
                    console.log('資料新增成功', response.data);
                    console.log(response.data);
                })
                .catch(function(error) {
                    console.error('資料新增失敗', error);
                });

        }

        // 把URL的QueryString轉換成JSON
        function transferQueryStringToJSON(queryString) {
            // 使用 URLSearchParams 解析字串
            const searchParams = new URLSearchParams(queryString);

            // 將 URLSearchParams 轉換為 JavaScript 物件
            const obj = {};
            searchParams.forEach(function(value, key) {
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

        // 傳送訂單內容到官方LINE
        function sendMessagetoLineOfficial(params_json_data) {
            try {
                // 將 URLSearchParams 轉換為 JSON 對象
                //const jsonParams = Object.fromEntries(Array.from(params.entries()));
                const jsonParams = params_json_data;


                // 計算下單商品價格後回傳商品格式跟價格還有總計
                const itemArr = calculateMoneyAndReturn();

                // 輸出 JSON 對象
                let txt = "";
                txt += "【自動下單】\n";
                txt += "客戶編號: " + jsonParams.customerId + "\n";
                txt += "下單時間: " + jsonParams.orderDateTime + "\n";
                txt += "遊戲名稱: " + jsonParams.gameName + "\n";
                txt += "登入方式: " + jsonParams.logintype + "\n";
                txt += "遊戲帳號: " + jsonParams.acount + "\n";
                txt += "遊戲密碼: " + jsonParams.password + "\n";
                txt += "伺 服 器: " + jsonParams.serverName + "\n";
                txt += "角色名稱: " + jsonParams.gameAccountName + "\n";
                //txt += "➤ID編號：" + jsonParams.gameAccountSid + "\n";
                txt += "\n";
                txt += itemArr.gameitemSLabelText + '\n\n';
                txt += '總計: $' + itemArr.sumMoney + "\n";
                txt += "幣別: " + itemArr.customerCurrency + '\n\n';
                txt += "備註: \n" + jsonParams.gameRemark + '\n';

                txt = txt.replace(/<br\s*[\/]?>/gi, "\n");

                //傳送通知到官方LINE
                liff.sendMessages([{
                        type: "text",
                        text: txt,
                    }, ])
                    .then(() => {
                        alert('訂單內容傳送到官方');
                    })
                    .catch((err) => {
                        console.log("error", err);
                        //alert('下單錯誤 請截圖洽小編' + err);
                    });
            } catch (e) {
                alert('傳送訂單內容發生錯誤\n' + e);
            }

        }

        // 計算下單商品價格後回傳商品格式跟價格還有總計
        function calculateMoneyAndReturn() {

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
            $.each(rate, function(i, item) {
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


            $.each(gameItemSelectedValues, function(i, item) {
                itemMoney = calculateMoney(gameRate, gameItemBouns[i], rateValue, gameItemCounts[i], customerCurrency);
                sumMoney += itemMoney;
                gameitemSLabelText += (i + 1) + '. ' + gameItemSelectedTexts[i] + ' X ' + gameItemCounts[i] + ' = ' + itemMoney + '<br />';
                itemMoneyText += itemMoney + ','
            });

            itemMoneyText = itemMoneyText.slice(0, -1);

            return {
                gameitemSLabelText,
                sumMoney,
                customerCurrency,
                itemMoneyText
            };
        }

        // 看餘額夠不夠下單，不夠的話不給下單
        function checkBalance(sumMoney) {
            // 客人的餘額
            let customerBalance = JSON.parse(sessionStorage.getItem('customerData')).CurrentMoney;

            if (typeof customerBalance === "undefined") {
                customerBalance = 0;
                console.log("customerBalance=" + customerBalance);
            } else {
                customerBalance = JSON.parse(sessionStorage.getItem('customerData')).CurrentMoney;
            }


            // 訂單總額

            // 訂單總額大於客人餘額不給下單
            if (sumMoney > customerBalance) {
                alert('您的餘額不足\n要自動下單\n請先至官方LINE\n找小編儲值錢包唷');
                $('.btn').hide();
                sessionStorage.clear();
                window.location.href = 'https://liff.line.me/2000183731-BLmrAGPp';
            }
        }

        /*
         *
         */
        function filterGemeAccount(json_data, gameAccountSid) {

            // 找出 Sid 等於 gameAccountSid
            let result = json_data.filter(item => item.Sid == gameAccountSid);

            return result;
        }

        /*
         * 把要紀錄的logs存到資料庫裡面
         */
        function saveLogsToMysql(log_type, params_json_data) {
            try {
                axios.post('../saveLogsToMysql.php', {
                        type: log_type,
                        JSON: JSON.stringify(params_json_data)
                    })
                    .then(function(response) {
                        console.log('成功存資料庫 1>', response.data);
                    })
                    .catch(function(error) {
                        console.log(error);
                    });
            } catch (e) {
                alert('錯誤，請洽小編\n' + e);
            }
        }

        function goback() {
            window.location.href = 'order2.php';

        }
    </script>


</body>

</html>