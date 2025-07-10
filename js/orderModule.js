/**
 * 訂單處理模組 - 包含所有與訂單處理相關的功能
 * @version 1.1.0
 * @author ABPay Team
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
        // 參數驗證
        if (!gameRate || isNaN(gameRate) || !bonus || isNaN(bonus) || !rateValue || isNaN(rateValue) || !count || isNaN(count)) {
            console.error('價格計算錯誤：參數無效', { gameRate, bonus, rateValue, count });
            return 0; // 返回安全值
        }
        
        console.log('===== 價格計算開始 =====');
        console.log('輸入參數：');
        console.log('- 商品單價(gameRate):', gameRate);
        console.log('- 獎勵倍數(bonus):', bonus);
        console.log('- 匯率值(rateValue):', rateValue);
        console.log('- 購買數量(count):', count);
        console.log('- 客戶幣別(customerCurrency):', customerCurrency);

        let result;
        let basePrice;
        
        // 使用嚴格比較運算符
        if (rateValue === 1) {
            basePrice = gameRate * bonus * rateValue;
            console.log('基礎價格計算：', gameRate, '*', bonus, '*', rateValue, '=', basePrice);
            result = Math.ceil(basePrice) * count;
            console.log('最終價格：', Math.ceil(basePrice), '*', count, '=', result);
        } else {
            basePrice = gameRate * bonus / rateValue;
            if (customerCurrency && customerCurrency.includes('新')) {
                console.log('基礎價格計算(新幣)：', gameRate, '*', bonus, '/', rateValue, '=', basePrice);
                result = this.roundUp(basePrice, 1) * count;
                console.log('最終價格：', this.roundUp(basePrice, 1), '*', count, '=', result);
            } else {
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
        try {
            // 設定rate匯率
            const rate = JSON.parse(sessionStorage.getItem('rate') || '[]');

            // 獲取下單遊戲裡的gameRate
            const gameRate = JSON.parse(sessionStorage.getItem('gameRate') || '0');

            // 獲取客人資料
            const customerData = JSON.parse(sessionStorage.getItem('customerData') || '{}');

            // 獲取客人的幣別
            const customerCurrency = customerData.Currency || '';

            // 依客人幣別來比對匯率並獲取匯率值
            let rateValue = 0.000;
            if (Array.isArray(rate)) {
                $.each(rate, function(i, item) {
                    if (item && item.includes(customerCurrency)) {
                        const parts = item.split(",");
                        if (parts.length >= 3) {
                            rateValue = parseFloat(parts[2]) || 0.000;
                        }
                    }
                });
            }

            const gameItemSelectedValues = JSON.parse(sessionStorage.getItem('gameItemSelectedValues') || '[]');
            const gameItemSelectedTexts = JSON.parse(sessionStorage.getItem('gameItemSelectedTexts') || '[]');
            const gameItemCounts = JSON.parse(sessionStorage.getItem('gameItemCounts') || '[]');
            const gameItemBonus = JSON.parse(sessionStorage.getItem('gameItemBouns') || '[]');
            let gameitemSLabelText = '';
            let itemMoney = 0;
            let sumMoney = 0;
            let itemMoneyText = '';

            // 確保所有數組長度一致
            const itemCount = Math.min(
                gameItemSelectedValues.length,
                gameItemSelectedTexts.length,
                gameItemCounts.length,
                gameItemBonus.length
            );

            for (let i = 0; i < itemCount; i++) {
                itemMoney = PriceCalculator.calculateItemPrice(
                    gameRate, 
                    gameItemBonus[i], 
                    rateValue, 
                    gameItemCounts[i], 
                    customerCurrency
                );
                sumMoney += itemMoney;
                gameitemSLabelText += (i + 1) + '. ' + gameItemSelectedTexts[i] + ' X ' + gameItemCounts[i] + ' = ' + itemMoney + '<br />';
                itemMoneyText += itemMoney + ',';
            }

            if (itemMoneyText.length > 0) {
                itemMoneyText = itemMoneyText.slice(0, -1);
            }

            return {
                gameitemSLabelText,
                sumMoney,
                customerCurrency,
                itemMoneyText
            };
        } catch (e) {
            console.error('計算總價時發生錯誤:', e);
            return {
                gameitemSLabelText: '',
                sumMoney: 0,
                customerCurrency: '',
                itemMoneyText: ''
            };
        }
    }
};

// 訂單處理模組
const OrderProcessor = {
    /**
     * 發送訂單到伺服器
     */
    /**
     * 發送訂單到伺服器
     * @returns {Promise<void>}
     */
    sendOrder: async function() {
        try {
            // 檢查必要的會話數據是否存在
            if (!sessionStorage.getItem('customerData') || 
                !sessionStorage.getItem('gameItemSelectedValues') || 
                !sessionStorage.getItem('gameAccountSid')) {
                alert('訂單資料不完整，請重新操作');
                return;
            }
            
            const orderData = this.prepareOrderData();
            const params = this.createOrderParams(orderData);
            const params_json_data = this.createOrderJsonData(orderData);

            // 記錄用戶的參數log
            await this.saveLogsToMysql('在傳送訂單到官方LINE之前的params_json_data', params_json_data);

            // 傳送訂單內容到官方LINE
            await this.sendMessageToLineOfficial(params_json_data);

            // 將訂單添加到 Redis 佇列，而不是直接發送到 API
            await this.addToRedisQueue(orderData, params, params_json_data);
        } catch (e) {
            alert('發送訂單時發生錯誤，請洽小編\n' + e.message || e);
            console.error('發送訂單錯誤:', e);
        }
    },
    
    /**
     * 將訂單添加到 Redis 佇列
     * @param {Object} orderData - 訂單數據
     * @param {URLSearchParams} params - 訂單參數
     * @param {Object} params_json_data - 訂單 JSON 數據
     * @returns {Promise<void>}
     */
    addToRedisQueue: function(orderData, params, params_json_data) {
        return new Promise((resolve, reject) => {
            try {
                // 生成唯一訂單 ID
                const tempOrderId = 'temp_' + new Date().getTime() + '_' + Math.floor(Math.random() * 1000);
                
                // 構建要發送到佇列的數據
                const queueData = {
                    orderId: tempOrderId,
                    orderData: {
                        UrlParametersString: orderData.UrlParametersString,
                        params: this.paramsToObject(params),
                        params_json_data: params_json_data
                    },
                    timestamp: new Date().getTime()
                };
                
                // 記錄佇列數據到日誌
                this.saveLogsToMysql('添加訂單到Redis佇列', queueData);
                
                // 發送到 addToOrderQueue.php
                const axiosConfig = {
                    timeout: 10000, // 10秒超時
                    headers: {
                        'Content-Type': 'application/json'
                    }
                };
                
                axios.post('addToOrderQueue.php', queueData, axiosConfig)
                    .then(response => {
                        console.log('訂單已添加到佇列:', response.data);
                        
                        if (response.data && response.data.status === 'success') {
                            // 顯示成功消息
                            alert('訂單已成功添加到處理佇列，訂單號: ' + response.data.orderId);
                            
                            // 將臨時訂單 ID 存儲到會話中
                            sessionStorage.setItem('tempOrderId', response.data.orderId);
                            
                            // 跳轉到訂單完成頁面
                            window.location = "finishOrder.php?orderId=" + encodeURIComponent(response.data.orderId) + "&queued=1";
                            resolve();
                        } else {
                            const errorMsg = (response.data && response.data.message) ? response.data.message : '添加訂單到佇列失敗';
                            alert(errorMsg);
                            reject(new Error(errorMsg));
                        }
                    })
                    .catch(error => {
                        console.error('添加訂單到佇列失敗:', error);
                        alert('添加訂單到佇列失敗，請稍後重試');
                        reject(error);
                    });
            } catch (e) {
                console.error('添加訂單到佇列時發生錯誤:', e);
                alert('添加訂單到佇列時發生錯誤: ' + (e.message || e));
                reject(e);
            }
        });
    },
    
    /**
     * 將 URLSearchParams 轉換為普通對象
     * @param {URLSearchParams} params - 訂單參數
     * @returns {Object} - 轉換後的對象
     */
    paramsToObject: function(params) {
        const result = {};
        for (const [key, value] of params.entries()) {
            result[key] = value;
        }
        return result;
    },

    /**
     * 準備訂單數據
     * @returns {Object} - 訂單數據對象
     */
    /**
     * 準備訂單數據
     * @returns {Object} - 訂單數據對象
     */
    prepareOrderData: function() {
        try {
            // 安全地獲取會話數據
            const getSessionItem = (key, defaultValue = null, isJson = true) => {
                const value = sessionStorage.getItem(key);
                if (!value) return defaultValue;
                if (isJson) {
                    try {
                        return JSON.parse(value);
                    } catch (e) {
                        console.error(`解析${key}時出錯:`, e);
                        return defaultValue;
                    }
                }
                return value;
            };
            
            const gameItemSelectedValues = getSessionItem('gameItemSelectedValues', []);
            const gameItemCounts = getSessionItem('gameItemCounts', []);
            const customerData = getSessionItem('customerData', {});
            const customerGameAccounts = getSessionItem('customerGameAccounts', []);
            
            const item = String(gameItemSelectedValues);
            const gameItemCountsStr = String(gameItemCounts);
            const itemMoney = sessionStorage.getItem('itemMoney') || '0';
            const sumMoney = sessionStorage.getItem('sumMoney') || '0';
            const customer = customerData.Sid || '';
            const account = getSessionItem('gameAccountSid', '', false);
            const lineId = sessionStorage.getItem('lineId') || '';
            const gameName = sessionStorage.getItem('gameNameText') || '';
            const gameItemsName = String(getSessionItem('gameItemSelectedTexts', []));
            
            // 過濾遊戲賬號
            let customerGameAccount = this.filterGameAccount(
                customerGameAccounts, 
                account
            );
            
            // 確保至少有一個賬號
            const customerGameAccountData = customerGameAccount.length > 0 ? 
                customerGameAccount[0] : 
                { LoginType: '', LoginAccount: '', LoginPassword: '', ServerName: '', Characters: '', Id: '', Sid: '', CustomerId: '' };
                
            const orderDateTime = sessionStorage.getItem('orderDateTime') || new Date().toLocaleString();
            const gameRemark = sessionStorage.getItem('gameRemark') || '';
            
            // 構建URL參數字符串 - 使用encodeURIComponent防止注入
            const UrlParametersString = 'UserId=test02&Password=3345678' + 
                '&Customer=' + encodeURIComponent(customer) +
                '&GameAccount=' + encodeURIComponent(account) +
                '&Item=' + encodeURIComponent(item) +
                '&Count=' + encodeURIComponent(gameItemCountsStr);
                
            return {
                item,
                gameItemCounts: gameItemCountsStr,
                itemMoney,
                sumMoney,
                customer,
                account,
                lineId,
                customerData,
                gameName,
                gameItemsName,
                customerGameAccounts: customerGameAccountData,
                orderDateTime,
                gameRemark,
                UrlParametersString
            };
        } catch (e) {
            console.error('準備訂單數據時出錯:', e);
            throw new Error('準備訂單數據時出錯: ' + (e.message || e));
        }
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
    /**
     * 發送訂單到API
     * @param {string} urlParams - URL參數字符串
     * @param {URLSearchParams} params - 訂單參數
     * @returns {Promise<void>}
     */
    sendOrderToApi: function(urlParams, params) {
        return new Promise((resolve, reject) => {
            try {
                // 添加請求超時設置
                const axiosConfig = {
                    timeout: 30000, // 30秒超時
                };
                
                axios.get('sendOrderUrlByCORS.php?' + urlParams, axiosConfig)
                    .then(function(response) {
                        if (!response || !response.data) {
                            alert('伺服器回應無效，請稍後再試');
                            reject(new Error('伺服器回應無效'));
                            return;
                        }
                        
                        const resdata = response.data;
                        let orderId = '';
                        console.log('API回應:', resdata);
                        
                        // 使用嚴格比較
                        if (resdata.Status === '1') {
                            orderId = resdata.OrderId || '';
                            if (!orderId) {
                                alert('訂單ID無效，請洽小編');
                                reject(new Error('訂單ID無效'));
                                return;
                            }
                            
                            params.append('orderId', orderId);
                            OrderProcessor.insertOrderData(params)
                                .then(() => {
                                    alert('下單成功');
                                    //sessionStorage.clear();
                                    window.location = "finishOrder.php?orderId=" + encodeURIComponent(orderId);
                                    resolve();
                                })
                                .catch(err => {
                                    console.error('保存訂單數據失敗:', err);
                                    alert('訂單已建立但保存失敗，請洽小編');
                                    window.location = "finishOrder.php?orderId=" + encodeURIComponent(orderId);
                                    resolve();
                                });
                        } else {
                            const errorMsg = resdata.Message || '下單發生錯誤，請洽小編';
                            alert(errorMsg);
                            reject(new Error(errorMsg));
                        }
                    })
                    .catch(function(error) {
                        console.error('API請求錯誤:', error);
                        alert('網絡請求失敗，請檢查網絡連接後重試');
                        reject(error);
                    });
            } catch (e) {
                alert('API下單錯誤，請洽小編\n' + (e.message || e));
                console.error('API下單錯誤:', e);
                reject(e);
            }
        });
    },

    /**
     * 新增訂單數據到數據庫
     * @param {URLSearchParams} params - 訂單參數
     */
    /**
     * 新增訂單數據到數據庫
     * @param {URLSearchParams} params - 訂單參數
     * @returns {Promise<Object>} - 返回操作結果
     */
    insertOrderData: function(params) {
        return new Promise((resolve, reject) => {
            const axiosConfig = {
                timeout: 20000, // 20秒超時
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            };
            
            axios.post('addOrderData.php', params, axiosConfig)
                .then(function(response) {
                    console.log('數據新增成功', response.data);
                    resolve(response.data);
                })
                .catch(function(error) {
                    console.error('數據新增失敗', error);
                    reject(error);
                });
        });
    },

    /**
     * 傳送訂單內容到官方LINE
     * @param {Object} params_json_data - 訂單JSON數據
     */
    /**
     * 傳送訂單內容到官方LINE
     * @param {Object} params_json_data - 訂單JSON數據
     * @returns {Promise<void>} - 返回操作結果
     */
    sendMessageToLineOfficial: function(params_json_data) {
        return new Promise((resolve, reject) => {
            try {
                if (!liff || typeof liff.sendMessages !== 'function') {
                    const error = new Error('LIFF API未初始化或不可用');
                    console.error(error);
                    reject(error);
                    return;
                }
                
                const jsonParams = params_json_data || {};
                const itemArr = PriceCalculator.calculateTotalPrice();

                // 輸出 JSON 對象
                let txt = "";
                txt += "【自動下單】\n";
                txt += "客戶編號: " + (jsonParams.customerId || '未提供') + "\n";
                txt += "下單時間: " + (jsonParams.orderDateTime || new Date().toLocaleString()) + "\n";
                txt += "遊戲名稱: " + (jsonParams.gameName || '未提供') + "\n";
                txt += "登入方式: " + (jsonParams.logintype || '未提供') + "\n";
                txt += "遊戲賬號: " + (jsonParams.acount || '未提供') + "\n";
                txt += "遊戲密碼: " + (jsonParams.password ? '******' : '未提供') + "\n"; // 隱藏密碼
                txt += "伺 服 器: " + (jsonParams.serverName || '未提供') + "\n";
                txt += "角色名稱: " + (jsonParams.gameAccountName || '未提供') + "\n";
                txt += "\n";
                txt += (itemArr.gameitemSLabelText || '無商品資料') + '\n\n';
                txt += '總計: $' + (itemArr.sumMoney || '0') + "\n";
                txt += "幣別: " + (itemArr.customerCurrency || '未提供') + '\n\n';
                txt += "備註: \n" + (jsonParams.gameRemark || '無') + '\n';

                // 替換HTML標籤為換行符
                txt = txt.replace(/<br\s*[\/]?>/gi, "\n");

                // 傳送通知到官方LINE
                liff.sendMessages([{
                        type: "text",
                        text: txt,
                    }])
                    .then(() => {
                        console.log('訂單內容已傳送到官方LINE');
                        alert('訂單內容傳送到官方');
                        resolve();
                    })
                    .catch((err) => {
                        console.error("LINE訊息發送錯誤:", err);
                        // 即使LINE發送失敗，也允許訂單繼續處理
                        alert('LINE訊息發送失敗，但訂單將繼續處理');
                        resolve(); // 仍然解析Promise以繼續流程
                    });
            } catch (e) {
                console.error('傳送訂單內容錯誤:', e);
                alert('傳送訂單內容發生錯誤，但訂單將繼續處理\n' + (e.message || e));
                resolve(); // 仍然解析Promise以繼續流程
            }
        });
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
    /**
     * 把要記錄的logs存到數據庫裡面
     * @param {string} log_type - 日誌類型
     * @param {Object} params_json_data - 參數JSON數據
     * @returns {Promise<Object>} - 返回操作結果
     */
    saveLogsToMysql: function(log_type, params_json_data) {
        return new Promise((resolve, reject) => {
            try {
                // 移除敏感信息
                const safeData = { ...params_json_data };
                if (safeData.Password) safeData.Password = '******';
                if (safeData.password) safeData.password = '******';
                
                const axiosConfig = {
                    timeout: 10000, // 10秒超時
                    headers: {
                        'Content-Type': 'application/json'
                    }
                };
                
                axios.post('saveLogsToMysql.php', {
                        type: log_type,
                        JSON: JSON.stringify(safeData)
                    }, axiosConfig)
                    .then(function(response) {
                        console.log('成功存數據庫:', response.data);
                        resolve(response.data);
                    })
                    .catch(function(error) {
                        console.error('存數據庫失敗:', error);
                        reject(error);
                    });
            } catch (e) {
                console.error('保存日誌錯誤:', e);
                reject(e);
            }
        });
    },

    /**
     * 檢查餘額是否足夠下單
     * @param {number} sumMoney - 訂單總額
     */
    /**
     * 檢查餘額是否足夠下單
     * @param {number} sumMoney - 訂單總額
     * @returns {boolean} - 餘額是否足夠
     */
    checkBalance: function(sumMoney) {
        try {
            // 安全地獲取客戶數據
            let customerData;
            try {
                customerData = JSON.parse(sessionStorage.getItem('customerData') || '{}');
            } catch (e) {
                console.error('解析客戶數據時出錯:', e);
                customerData = {};
            }
            
            // 客人的餘額
            let customerBalance = customerData.CurrentMoney;

            if (typeof customerBalance === "undefined" || customerBalance === null) {
                customerBalance = 0;
                console.log("客戶餘額未定義，設為0");
            }
            
            // 確保是數字類型
            customerBalance = parseFloat(customerBalance) || 0;
            sumMoney = parseFloat(sumMoney) || 0;
            
            console.log("檢查餘額 - 訂單金額:", sumMoney, "客戶餘額:", customerBalance);
            
            // 訂單總額大於客人餘額不給下單
            if (sumMoney > customerBalance) {
                console.log("餘額不足");
                // 取消註釋以啟用餘額檢查
                /*
                alert('您的餘額不足\n要自動下單\n請先至官方LINE\n找小編儲值錢包哟');
                $('.btn').hide();
                sessionStorage.clear();
                window.location.href = 'https://liff.line.me/2000183731-BLmrAGPp';
                */
                return false;
            }
            
            return true;
        } catch (e) {
            console.error('檢查餘額時出錯:', e);
            return false;
        }
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
        try {
            if (!queryString) {
                console.warn('轉換QueryString時收到空值');
                return '{}';
            }
            
            // 使用 URLSearchParams 解析字符串
            const searchParams = new URLSearchParams(queryString);

            // 將 URLSearchParams 轉換為 JavaScript 對象
            const obj = {};
            searchParams.forEach(function(value, key) {
                // 過濾可能的XSS攻擊
                const safeKey = this.sanitizeString(key);
                const safeValue = this.sanitizeString(value);
                obj[safeKey] = safeValue;
            }.bind(this));

            // 將 JavaScript 對象轉換為 JSON 字符串
            const jsonString = JSON.stringify(obj);
            return jsonString;
        } catch (e) {
            console.error('轉換QueryString時出錯:', e);
            return '{}';
        }
    },

    /**
     * 把一個新的參數加到JSON裡面
     * @param {string} value - 參數值
     * @param {string} jsonString - JSON字符串
     * @param {string} paramName - 參數名稱，默認為'orderId'
     * @returns {string} - 新的JSON字符串
     */
    addNewParameterToJson: function(value, jsonString, paramName = 'orderId') {
        try {
            if (!jsonString) {
                console.warn('添加參數時收到空JSON字符串');
                const obj = {};
                obj[paramName] = value;
                return JSON.stringify(obj);
            }
            
            // 將 JSON 字符串解析為 JavaScript 對象
            const obj = JSON.parse(jsonString);

            // 新增參數到 JavaScript 對象
            obj[paramName] = value;

            // 再將 JavaScript 對象轉換為 JSON 字符串
            const newJsonString = JSON.stringify(obj);

            return newJsonString;
        } catch (e) {
            console.error('添加參數到JSON時出錯:', e);
            const obj = {};
            obj[paramName] = value;
            return JSON.stringify(obj);
        }
    },

    /**
     * 返回上一頁
     */
    goback: function() {
        try {
            window.location.href = 'https://liff.line.me/2000183731-BLmrAGPp';
        } catch (e) {
            console.error('返回上一頁時出錯:', e);
            alert('返回失敗，請手動返回');
        }
    },
    
    /**
     * 安全地從sessionStorage獲取數據
     * @param {string} key - 存儲鍵名
     * @param {*} defaultValue - 默認值
     * @param {boolean} isJson - 是否為JSON格式
     * @returns {*} - 獲取的數據或默認值
     */
    getSessionItem: function(key, defaultValue = null, isJson = true) {
        try {
            const value = sessionStorage.getItem(key);
            if (!value) return defaultValue;
            if (isJson) {
                return JSON.parse(value);
            }
            return value;
        } catch (e) {
            console.error(`獲取會話數據[${key}]時出錯:`, e);
            return defaultValue;
        }
    },
    
    /**
     * 安全地設置sessionStorage數據
     * @param {string} key - 存儲鍵名
     * @param {*} value - 要存儲的值
     * @param {boolean} isJson - 是否為JSON格式
     * @returns {boolean} - 是否成功
     */
    setSessionItem: function(key, value, isJson = true) {
        try {
            if (isJson && typeof value !== 'string') {
                sessionStorage.setItem(key, JSON.stringify(value));
            } else {
                sessionStorage.setItem(key, value);
            }
            return true;
        } catch (e) {
            console.error(`設置會話數據[${key}]時出錯:`, e);
            return false;
        }
    },
    
    /**
     * 清理字符串，防止XSS攻擊
     * @param {string} str - 輸入字符串
     * @returns {string} - 清理後的字符串
     */
    sanitizeString: function(str) {
        if (typeof str !== 'string') return str;
        return str
            .replace(/[<>]/g, '') // 移除可能導致HTML注入的字符
            .trim();
    }
};

// LIFF模組
const LiffManager = {
    /**
     * 初始化LIFF
     * @param {string} myLiffId - LIFF ID
     * @returns {Promise<void>} - 返回初始化結果
     */
    initializeLiff: function(myLiffId) {
        return new Promise((resolve, reject) => {
            if (!myLiffId) {
                const error = new Error('LIFF ID不能為空');
                console.error(error);
                alert('LIFF初始化失敗: LIFF ID不能為空');
                reject(error);
                return;
            }
            
            if (typeof liff === 'undefined') {
                const error = new Error('LIFF SDK未載入');
                console.error(error);
                alert('LIFF SDK未載入，請確認網絡連接並重新載入頁面');
                reject(error);
                return;
            }
            
            liff.init({
                    liffId: myLiffId,
                    withLoginOnExternalBrowser: true, // 啟用自動登錄流程
                })
                .then(() => {
                    console.log('LIFF初始化成功');
                    this.initializeApp()
                        .then(() => resolve())
                        .catch(err => reject(err));
                })
                .catch((err) => {
                    console.error('LIFF初始化失敗:', err);
                    alert('LIFF初始化失敗，請確認網絡連接並重新載入頁面');
                    reject(err);
                });
        });
    },

    /**
     * 初始化應用
     * @returns {Promise<Object>} - 返回用戶資料
     */
    initializeApp: function() {
        return new Promise((resolve, reject) => {
            console.log('應用初始化中...');
            
            // 檢查LIFF是否已登入
            if (!liff.isLoggedIn()) {
                console.log('用戶未登入，嘗試登入');
                liff.login();
                reject(new Error('用戶未登入'));
                return;
            }
            
            liff.getProfile()
                .then(profile => {
                    console.log('獲取用戶資料成功');
                    // 存儲用戶ID到會話
                    if (profile && profile.userId) {
                        sessionStorage.setItem('lineUserId', profile.userId);
                        // 如果頁面上有lineId輸入框，則設置值
                        const lineIdInput = $("#lineId");
                        if (lineIdInput.length > 0) {
                            lineIdInput.val(profile.userId);
                        }
                    }
                    resolve(profile);
                })
                .catch((err) => {
                    console.error('獲取用戶資料失敗:', err);
                    reject(err);
                });
        });
    },
    
    /**
     * 檢查LIFF環境
     * @returns {Object} - LIFF環境信息
     */
    checkLiffEnvironment: function() {
        if (typeof liff === 'undefined') {
            return { isInClient: false, isLoggedIn: false, error: 'LIFF SDK未載入' };
        }
        
        return {
            isInClient: liff.isInClient(),
            isLoggedIn: liff.isLoggedIn(),
            context: liff.getContext(),
            version: liff.getVersion()
        };
    }
};

// 導出模組
window.PriceCalculator = PriceCalculator;
window.OrderProcessor = OrderProcessor;
window.Utils = Utils;
window.LiffManager = LiffManager;
