/**
 * 訂單處理模組 - 包含所有與訂單處理相關的功能
 */

// 價格計算模組
const PriceCalculator = {
    /**
     * 計算單個商品的價格
     * @param {number} gameRate - 商品單價
     * @param {number} bonus - 獎勵倍數
     * @param {number} rateValue - 匯率值
     * @param {number} count - 購買數量
     * @param {string} customerCurrency - 客戶幣別
     * @returns {number} - 計算後的價格
     */
    calculateItemPrice: function(gameRate, bonus, rateValue, count, customerCurrency) {
        console.log('===== 價格計算開始 =====');
        console.log('輸入參數：');
        console.log('- 商品單價(gameRate):', gameRate);
        console.log('- 獎勵倍數(bonus):', bonus);
        console.log('- 匯率值(rateValue):', rateValue);
        console.log('- 購買數量(count):', count);
        console.log('- 客戶幣別(customerCurrency):', customerCurrency);

        let result;
        if (rateValue == 1) {
            const basePrice = gameRate * bonus * rateValue;
            console.log('基礎價格計算：', gameRate, '*', bonus, '*', rateValue, '=', basePrice);
            result = Math.ceil(basePrice) * count;
            console.log('最終價格：', Math.ceil(basePrice), '*', count, '=', result);
        } else {
            if (customerCurrency.includes('新')) {
                const basePrice = gameRate * bonus / rateValue;
                console.log('基礎價格計算(新幣)：', gameRate, '*', bonus, '/', rateValue, '=', basePrice);
                result = this.roundUp(basePrice, 1) * count;
                console.log('最終價格：', this.roundUp(basePrice, 1), '*', count, '=', result);
            } else {
                const basePrice = gameRate * bonus / rateValue;
                console.log('基礎價格計算：', gameRate, '*', bonus, '/', rateValue, '=', basePrice);
                result = Math.ceil(basePrice) * count;
                console.log('最終價格：', Math.ceil(basePrice), '*', count, '=', result);
            }
        }
        console.log('===== 價格計算結束 =====');
        return result;
    },

    /**
     * 四捨五入到指定小數位
     * @param {number} num - 要四捨五入的數字
     * @param {number} decimal - 小數位數
     * @returns {number} - 四捨五入後的結果
     */
    roundUp: function(num, decimal) {
        const result = Math.ceil((num + Number.EPSILON) * Math.pow(10, decimal)) / Math.pow(10, decimal);
        console.log(`四捨五入到${decimal}位小數：${num} → ${result}`);
        return result;
    },

    /**
     * 計算所有商品的價格並返回格式化的結果
     * @returns {Object} - 包含商品格式、總價和幣別的對象
     */
    calculateTotalPrice: function() {
        // 設定rate匯率
        const rate = JSON.parse(sessionStorage.getItem('rate'));

        // 獲取下單遊戲裡的gameRate
        const gameRate = JSON.parse(sessionStorage.getItem('gameRate'));

        // 獲取客人資料
        const customerData = JSON.parse(sessionStorage.getItem('customerData'));

        // 獲取客人的幣別
        const customerCurrency = customerData.Currency;

        // 依客人幣別來比對匯率並獲取匯率值
        let rateValue = 0.000;
        $.each(rate, function(i, item) {
            if (item.includes(customerCurrency)) {
                rateValue = item.split(",")[2];
            }
        });

        const gameItemSelectedValues = JSON.parse(sessionStorage.getItem('gameItemSelectedValues'));
        const gameItemSelectedTexts = JSON.parse(sessionStorage.getItem('gameItemSelectedTexts'));
        const gameItemCounts = JSON.parse(sessionStorage.getItem('gameItemCounts'));
        const gameItemBonus = JSON.parse(sessionStorage.getItem('gameItemBouns'));
        let gameitemSLabelText = '';
        let itemMoney = 0;
        let sumMoney = 0;
        let itemMoneyText = '';

        $.each(gameItemSelectedValues, function(i, item) {
            itemMoney = PriceCalculator.calculateItemPrice(gameRate, gameItemBonus[i], rateValue, gameItemCounts[i], customerCurrency);
            sumMoney += itemMoney;
            gameitemSLabelText += (i + 1) + '. ' + gameItemSelectedTexts[i] + ' X ' + gameItemCounts[i] + ' = ' + itemMoney + '<br />';
            itemMoneyText += itemMoney + ',';
        });

        itemMoneyText = itemMoneyText.slice(0, -1);

        return {
            gameitemSLabelText,
            sumMoney,
            customerCurrency,
            itemMoneyText
        };
    }
};

// 訂單處理模組
const OrderProcessor = {
    /**
     * 發送訂單到伺服器
     */
    sendOrder: function() {
        try {
            const orderData = this.prepareOrderData();
            const params = this.createOrderParams(orderData);
            const params_json_data = this.createOrderJsonData(orderData);

            // 記錄用戶的參數log
            this.saveLogsToMysql('在傳送訂單到官方LINE之前的params_json_data', params_json_data);

            // 傳送訂單內容到官方LINE
            this.sendMessageToLineOfficial(params_json_data);

            // 發送訂單到API
            this.sendOrderToApi(orderData.UrlParametersString, params);
        } catch (e) {
            alert('發送訂單時發生錯誤，請洽小編\n' + e);
            console.error('發送訂單錯誤:', e);
        }
    },

    /**
     * 準備訂單數據
     * @returns {Object} - 訂單數據對象
     */
    prepareOrderData: function() {
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
        
        // 過濾遊戲賬號
        let customerGameAccount = this.filterGameAccount(
            JSON.parse(sessionStorage.getItem('customerGameAccounts')), 
            sessionStorage.getItem('gameAccountSid')
        );
        const customerGameAccounts = customerGameAccount[0];
        const orderDateTime = sessionStorage.getItem('orderDateTime');
        const gameRemark = sessionStorage.getItem('gameRemark');
        
        // 構建URL參數字符串
        const UrlParametersString = 'UserId=test02&Password=3345678&Customer=' + customer +
            '&GameAccount=' + account +
            '&Item=' + item +
            '&Count=' + gameItemCounts;
            
        return {
            item,
            gameItemCounts,
            itemMoney,
            sumMoney,
            customer,
            account,
            lineId,
            customerData,
            gameName,
            gameItemsName,
            customerGameAccounts,
            orderDateTime,
            gameRemark,
            UrlParametersString
        };
    },

    /**
     * 創建訂單參數
     * @param {Object} orderData - 訂單數據
     * @returns {URLSearchParams} - 訂單參數
     */
    createOrderParams: function(orderData) {
        const params = new URLSearchParams();
        
        try {
            params.append('gameName', orderData.gameName);
            params.append('UserId', 'test01');
            params.append('Password', '111111');
            params.append('Customer', orderData.customer);
            params.append('GameAccount', orderData.account);
            params.append('Item', orderData.item);
            params.append('Count', orderData.gameItemCounts);
            params.append('lineId', orderData.lineId);
            params.append('customerId', orderData.customerData.Id);
            params.append('gameItemsName', orderData.gameItemsName);
            params.append('gameItemCounts', orderData.gameItemCounts);
            params.append('logintype', orderData.customerGameAccounts.LoginType);
            params.append('acount', orderData.customerGameAccounts.LoginAccount);
            params.append('password', orderData.customerGameAccounts.LoginPassword);
            params.append('serverName', orderData.customerGameAccounts.ServerName);
            params.append('gameAccountName', orderData.customerGameAccounts.Characters);
            params.append('gameAccountId', orderData.customerGameAccounts.Id);
            params.append('gameAccountSid', orderData.customerGameAccounts.Sid);
            params.append('customerSid', orderData.customerGameAccounts.CustomerId);
            params.append('status', '訂單處理中');
            params.append('itemsMoney', orderData.itemMoney);
            params.append('sumMoney', orderData.sumMoney);
            params.append('orderDateTime', orderData.orderDateTime);
            params.append('gameRemark', orderData.gameRemark);
        } catch (e) {
            alert('組參數發生錯誤，請洽小編\n' + e);
            console.error('組參數錯誤:', e);
        }
        
        return params;
    },

    /**
     * 創建訂單JSON數據
     * @param {Object} orderData - 訂單數據
     * @returns {Object} - 訂單JSON數據
     */
    createOrderJsonData: function(orderData) {
        return {
            "gameName": orderData.gameName,
            "UserId": "test01",
            "Password": "111111",
            "Customer": orderData.customer,
            "GameAccount": orderData.account,
            "Item": orderData.item,
            "Count": orderData.gameItemCounts,
            "lineId": orderData.lineId,
            "customerId": orderData.customerData.Id,
            "gameItemsName": orderData.gameItemsName,
            "gameItemCounts": orderData.gameItemCounts,
            "logintype": orderData.customerGameAccounts.LoginType,
            "acount": orderData.customerGameAccounts.LoginAccount,
            "password": orderData.customerGameAccounts.LoginPassword,
            "serverName": orderData.customerGameAccounts.ServerName,
            "gameAccountName": orderData.customerGameAccounts.Characters,
            "gameAccountId": orderData.customerGameAccounts.Id,
            "gameAccountSid": orderData.customerGameAccounts.Sid,
            "customerSid": orderData.customerGameAccounts.CustomerId,
            "status": "訂單處理中",
            "itemsMoney": orderData.itemMoney,
            "sumMoney": orderData.sumMoney,
            "orderDateTime": orderData.orderDateTime,
            "gameRemark": orderData.gameRemark
        };
    },

    /**
     * 發送訂單到API
     * @param {string} urlParams - URL參數字符串
     * @param {URLSearchParams} params - 訂單參數
     */
    sendOrderToApi: function(urlParams, params) {
        try {
            axios.get('sendOrderUrlByCORS.php?' + urlParams)
                .then(function(response) {
                    const resdata = response.data;
                    let orderId = '';
                    console.log(resdata);
                    console.log(resdata.Status);
                    if (resdata.Status == '1') {
                        orderId = resdata.OrderId;
                        params.append('orderId', orderId);
                        OrderProcessor.insertOrderData(params);

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
            console.error('API下單錯誤:', e);
        }
    },

    /**
     * 新增訂單數據到數據庫
     * @param {URLSearchParams} params - 訂單參數
     */
    insertOrderData: function(params) {
        axios.post('addOrderData.php', params)
            .then(function(response) {
                console.log('數據新增成功', response.data);
                console.log(response.data);
            })
            .catch(function(error) {
                console.error('數據新增失敗', error);
            });
    },

    /**
     * 傳送訂單內容到官方LINE
     * @param {Object} params_json_data - 訂單JSON數據
     */
    sendMessageToLineOfficial: function(params_json_data) {
        try {
            const jsonParams = params_json_data;
            const itemArr = PriceCalculator.calculateTotalPrice();

            // 輸出 JSON 對象
            let txt = "";
            txt += "【自動下單】\n";
            txt += "客戶編號: " + jsonParams.customerId + "\n";
            txt += "下單時間: " + jsonParams.orderDateTime + "\n";
            txt += "遊戲名稱: " + jsonParams.gameName + "\n";
            txt += "登入方式: " + jsonParams.logintype + "\n";
            txt += "遊戲賬號: " + jsonParams.acount + "\n";
            txt += "遊戲密碼: " + jsonParams.password + "\n";
            txt += "伺 服 器: " + jsonParams.serverName + "\n";
            txt += "角色名稱: " + jsonParams.gameAccountName + "\n";
            txt += "\n";
            txt += itemArr.gameitemSLabelText + '\n\n';
            txt += '總計: $' + itemArr.sumMoney + "\n";
            txt += "幣別: " + itemArr.customerCurrency + '\n\n';
            txt += "備註: \n" + jsonParams.gameRemark + '\n';

            txt = txt.replace(/<br\s*[\/]?>/gi, "\n");

            // 傳送通知到官方LINE
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
            console.error('傳送訂單內容錯誤:', e);
        }
    },

    /**
     * 過濾遊戲賬號
     * @param {Array} json_data - 遊戲賬號數據
     * @param {string} gameAccountSid - 遊戲賬號SID
     * @returns {Array} - 過濾後的遊戲賬號
     */
    filterGameAccount: function(json_data, gameAccountSid) {
        // 找出 Sid 等於 gameAccountSid
        let result = json_data.filter(item => item.Sid == gameAccountSid);
        return result;
    },

    /**
     * 把要記錄的logs存到數據庫裡面
     * @param {string} log_type - 日誌類型
     * @param {Object} params_json_data - 參數JSON數據
     */
    saveLogsToMysql: function(log_type, params_json_data) {
        try {
            axios.post('saveLogsToMysql.php', {
                    type: log_type,
                    JSON: JSON.stringify(params_json_data)
                })
                .then(function(response) {
                    console.log('成功存數據庫 1>', response.data);
                })
                .catch(function(error) {
                    console.log(error);
                });
        } catch (e) {
            alert('錯誤，請洽小編\n' + e);
            console.error('保存日誌錯誤:', e);
        }
    },

    /**
     * 檢查餘額是否足夠下單
     * @param {number} sumMoney - 訂單總額
     */
    checkBalance: function(sumMoney) {
        // 客人的餘額
        let customerBalance = JSON.parse(sessionStorage.getItem('customerData')).CurrentMoney;

        if (typeof customerBalance === "undefined") {
            customerBalance = 0;
            console.log("customerBalance=" + customerBalance);
        } else {
            customerBalance = JSON.parse(sessionStorage.getItem('customerData')).CurrentMoney;
        }

        // 訂單總額大於客人餘額不給下單
        // if (sumMoney > customerBalance) {
        //     alert('您的餘額不足\n要自動下單\n請先至官方LINE\n找小編儲值錢包哟');
        //     $('.btn').hide();
        //     sessionStorage.clear();
        //     window.location.href = 'https://liff.line.me/2000183731-BLmrAGPp';
        // }
    }
};

// 工具函數模組
const Utils = {
    /**
     * 把URL的QueryString轉換成JSON
     * @param {string} queryString - 查詢字符串
     * @returns {string} - JSON字符串
     */
    transferQueryStringToJSON: function(queryString) {
        // 使用 URLSearchParams 解析字符串
        const searchParams = new URLSearchParams(queryString);

        // 將 URLSearchParams 轉換為 JavaScript 對象
        const obj = {};
        searchParams.forEach(function(value, key) {
            obj[key] = value;
        });

        // 將 JavaScript 對象轉換為 JSON 字符串
        const jsonString = JSON.stringify(obj);
        return jsonString;
    },

    /**
     * 把一個新的參數加到JSON裡面
     * @param {string} value - 參數值
     * @param {string} jsonString - JSON字符串
     * @returns {string} - 新的JSON字符串
     */
    addNewParameterToJson: function(value, jsonString) {
        // 將 JSON 字符串解析為 JavaScript 對象
        const obj = JSON.parse(jsonString);

        // 新增參數到 JavaScript 對象
        obj.orderId = value;

        // 再將 JavaScript 對象轉換為 JSON 字符串
        const newJsonString = JSON.stringify(obj);

        return newJsonString;
    },

    /**
     * 返回上一頁
     */
    goback: function() {
        window.location.href = 'https://liff.line.me/2000183731-BLmrAGPp';
    }
};

// LIFF模組
const LiffManager = {
    /**
     * 初始化LIFF
     * @param {string} myLiffId - LIFF ID
     */
    initializeLiff: function(myLiffId) {
        liff
            .init({
                liffId: myLiffId,
                withLoginOnExternalBrowser: true, // Enable automatic login process
            })
            .then(() => {
                this.initializeApp();
            })
            .catch((err) => {
                console.log(err);
                console.log('啟動失敗。');
            });
    },

    /**
     * 初始化應用
     */
    initializeApp: function() {
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
};

// 導出模組
window.PriceCalculator = PriceCalculator;
window.OrderProcessor = OrderProcessor;
window.Utils = Utils;
window.LiffManager = LiffManager;