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

            // 構建完整的 API URL
            const apiBaseUrl = 'http://www.adp.idv.tw/api/Order?';
            const fullApiUrl = apiBaseUrl + orderData.UrlParametersString;

            // 記錄用戶的參數log，包含完整的 API URL
            this.saveLogsToMysql('在傳送訂單到官方LINE之前的params_json_data', params_json_data, fullApiUrl);

            // 傳送訂單內容到官方LINE
            this.sendMessageToLineOfficial(params_json_data);

            // 使用 Redis 佇列發送訂單到 API (每秒發送一次)
            this.saveLogsToMysql('訂單已添加到佇列，將按順序處理', params_json_data, fullApiUrl);
            this.sendOrderToQueueApi(orderData.UrlParametersString, params);
            console.log('訂單已添加到佇列，將按順序處理');
            console.log('API URL:', fullApiUrl);
            console.log('URL Parameters:', orderData.UrlParametersString);
            console.log('Params:', params);
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
        // 檢查並獲取 sessionStorage 中的數據，確保不會有 undefined 值
        const getSessionItem = (key, defaultValue = '') => {
            const value = sessionStorage.getItem(key);
            return value !== null ? value : defaultValue;
        };
        
        const getJsonSessionItem = (key, defaultValue = []) => {
            const value = sessionStorage.getItem(key);
            try {
                return value !== null ? JSON.parse(value) : defaultValue;
            } catch (e) {
                console.error(`解析 ${key} 時出錯:`, e);
                return defaultValue;
            }
        };
        
        const item = String(getJsonSessionItem('gameItemSelectedValues'));
        const gameItemCounts = String(getJsonSessionItem('gameItemCounts'));
        const itemMoney = getSessionItem('itemMoney', '0');
        const sumMoney = getSessionItem('sumMoney', '0');
        const customerData = getJsonSessionItem('customerData', {Sid: ''});
        const customer = customerData.Sid || '';
        const account = getSessionItem('gameAccountSid', '');
        const lineId = getSessionItem('lineId', '');
        const gameName = getSessionItem('gameNameText', '');
        const gameItemsName = String(getJsonSessionItem('gameItemSelectedTexts'));
        
        // 過濾遊戲賬號
        let customerGameAccount = this.filterGameAccount(
            getJsonSessionItem('customerGameAccounts'), 
            getSessionItem('gameAccountSid', '')
        );
        const customerGameAccounts = customerGameAccount.length > 0 ? customerGameAccount[0] : {};
        const orderDateTime = getSessionItem('orderDateTime', new Date().toISOString());
        const gameRemark = getSessionItem('gameRemark', '');
        
        // 檢查必要參數
        if (!customer || !account || !item || !gameItemCounts) {
            alert('訂單參數不完整，請重新下單');
            console.error('訂單參數不完整:', { customer, account, item, gameItemCounts });
            window.location = "order.php";
            throw new Error('訂單參數不完整');
        }
        
        // 檢查商品數量是否有效
        const counts = gameItemCounts.split(',');
        let hasInvalidCount = false;
        let invalidCountIndex = -1;
        
        for (let i = 0; i < counts.length; i++) {
            const count = counts[i];
            if (!count || isNaN(count) || parseInt(count) <= 0) {
                hasInvalidCount = true;
                invalidCountIndex = i;
                break;
            }
        }
        
        if (hasInvalidCount) {
            alert('商品數量無效，請確保所有商品數量大於 0');
            console.error('商品數量無效:', counts, '無效索引:', invalidCountIndex);
            window.location = "order.php";
            throw new Error('商品數量無效');
        }
        
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
            // 檢查 orderData 是否為有效對象
            if (!orderData || typeof orderData !== 'object') {
                throw new Error('訂單數據無效');
            }
            
            // 安全地獲取屬性值的輔助函數
            const safeGet = (obj, path, defaultValue = '') => {
                if (!obj) return defaultValue;
                const parts = path.split('.');
                let current = obj;
                
                for (let i = 0; i < parts.length; i++) {
                    if (current === null || current === undefined) {
                        return defaultValue;
                    }
                    current = current[parts[i]];
                }
                
                return current !== null && current !== undefined ? current : defaultValue;
            };
            
            // 安全地添加參數
            const safeAppend = (key, value, defaultValue = '') => {
                params.append(key, value !== null && value !== undefined ? value : defaultValue);
            };
            
            safeAppend('gameName', orderData.gameName);
            params.append('UserId', 'test02');
            params.append('Password', '3345678');
            safeAppend('Customer', orderData.customer);
            safeAppend('GameAccount', orderData.account);
            safeAppend('Item', orderData.item);
            safeAppend('Count', orderData.gameItemCounts);
            safeAppend('lineId', orderData.lineId);
            safeAppend('customerId', safeGet(orderData, 'customerData.Id'));
            safeAppend('gameItemsName', orderData.gameItemsName);
            safeAppend('gameItemCounts', orderData.gameItemCounts);
            safeAppend('logintype', safeGet(orderData, 'customerGameAccounts.LoginType'));
            safeAppend('acount', safeGet(orderData, 'customerGameAccounts.LoginAccount'));
            safeAppend('password', safeGet(orderData, 'customerGameAccounts.LoginPassword'));
            safeAppend('serverName', safeGet(orderData, 'customerGameAccounts.ServerName'));
            safeAppend('gameAccountName', safeGet(orderData, 'customerGameAccounts.Characters'));
            safeAppend('gameAccountId', safeGet(orderData, 'customerGameAccounts.Id'));
            safeAppend('gameAccountSid', safeGet(orderData, 'customerGameAccounts.Sid'));
            safeAppend('customerSid', safeGet(orderData, 'customerGameAccounts.CustomerId'));
            params.append('status', '訂單處理中');
            safeAppend('itemsMoney', orderData.itemMoney);
            safeAppend('sumMoney', orderData.sumMoney);
            safeAppend('orderDateTime', orderData.orderDateTime);
            safeAppend('gameRemark', orderData.gameRemark);
            
            console.log('訂單參數創建成功');
        } catch (e) {
            alert('組參數發生錯誤，請洽小編\n' + e);
            console.error('組參數錯誤:', e);
            window.location = "order.php";
        }
        
        return params;
    },

    /**
     * 創建訂單JSON數據
     * @param {Object} orderData - 訂單數據
     * @returns {Object} - 訂單JSON數據
     */
    createOrderJsonData: function(orderData) {
        try {
            // 檢查 orderData 是否為有效對象
            if (!orderData || typeof orderData !== 'object') {
                throw new Error('訂單數據無效');
            }
            
            // 安全地獲取屬性值的輔助函數
            const safeGet = (obj, path, defaultValue = '') => {
                if (!obj) return defaultValue;
                const parts = path.split('.');
                let current = obj;
                
                for (let i = 0; i < parts.length; i++) {
                    if (current === null || current === undefined) {
                        return defaultValue;
                    }
                    current = current[parts[i]];
                }
                
                return current !== null && current !== undefined ? current : defaultValue;
            };
            
            return {
                "gameName": orderData.gameName || '',
                "UserId": "test02",
                "Password": "3345678",
                "Customer": orderData.customer || '',
                "GameAccount": orderData.account || '',
                "Item": orderData.item || '',
                "Count": orderData.gameItemCounts || '',
                "lineId": orderData.lineId || '',
                "customerId": safeGet(orderData, 'customerData.Id'),
                "gameItemsName": orderData.gameItemsName || '',
                "gameItemCounts": orderData.gameItemCounts || '',
                "logintype": safeGet(orderData, 'customerGameAccounts.LoginType'),
                "acount": safeGet(orderData, 'customerGameAccounts.LoginAccount'),
                "password": safeGet(orderData, 'customerGameAccounts.LoginPassword'),
                "serverName": safeGet(orderData, 'customerGameAccounts.ServerName'),
                "gameAccountName": safeGet(orderData, 'customerGameAccounts.Characters'),
                "gameAccountId": safeGet(orderData, 'customerGameAccounts.Id'),
                "gameAccountSid": safeGet(orderData, 'customerGameAccounts.Sid'),
                "customerSid": safeGet(orderData, 'customerGameAccounts.CustomerId'),
                "status": "訂單處理中",
                "itemsMoney": orderData.itemMoney || '0',
                "sumMoney": orderData.sumMoney || '0',
                "orderDateTime": orderData.orderDateTime || new Date().toISOString(),
                "gameRemark": orderData.gameRemark || ''
            };
        } catch (e) {
            alert('創建訂單數據發生錯誤，請洽小編\n' + e);
            console.error('創建訂單數據錯誤:', e);
            window.location = "order.php";
            return {};
        }
    },

    /**
     * 發送訂單到API
     * @param {string} urlParams - URL參數字符串
     * @param {URLSearchParams} params - 訂單參數
     */
    sendOrderToApi: function(urlParams, params) {
        try {
            // 檢查 urlParams 是否為有效字符串
            if (!urlParams || typeof urlParams !== 'string') {
                alert('訂單參數無效，請重新下單');
                console.error('訂單參數無效:', urlParams);
                window.location = "order.php";
                return;
            }
            
            // 檢查參數中是否有 undefined 值
            if (urlParams.includes('undefined') || urlParams.includes('null')) {
                alert('訂單參數有誤，請重新下單');
                console.error('訂單參數有 undefined 或 null 值:', urlParams);
                // 返回到流程的第一步重新下單
                window.location = "order.php";
                return;
            }
            
            // 解析 URL 參數並檢查每個值
            const paramPairs = urlParams.split('&');
            const requiredParams = ['UserId', 'Password', 'Customer', 'GameAccount', 'Item', 'Count'];
            const foundParams = {};
            
            // 檢查每個參數對
            for (let i = 0; i < paramPairs.length; i++) {
                const pair = paramPairs[i].split('=');
                if (pair.length < 2 || pair[1] === '' || pair[1] === 'undefined' || pair[1] === 'null') {
                    alert('訂單參數 ' + pair[0] + ' 有誤，請重新下單');
                    console.error('訂單參數有空值或無效值:', pair[0], pair[1]);
                    // 返回到流程的第一步重新下單
                    window.location = "order.php";
                    return;
                }
                
                // 標記找到的必要參數
                if (requiredParams.includes(pair[0])) {
                    foundParams[pair[0]] = true;
                }
            }
            
            // 檢查是否所有必要參數都存在
            for (let i = 0; i < requiredParams.length; i++) {
                if (!foundParams[requiredParams[i]]) {
                    alert('缺少必要的訂單參數: ' + requiredParams[i] + '，請重新下單');
                    console.error('缺少必要的訂單參數:', requiredParams[i]);
                    window.location = "order.php";
                    return;
                }
            }
            
            // 記錄最終的 URL 參數
            console.log('發送訂單到 API，參數:', urlParams);
            
            // 構建完整的 API URL
            const apiBaseUrl = 'http://www.adp.idv.tw/api/Order?';
            const fullApiUrl = apiBaseUrl + urlParams;
            
            // 記錄 API 請求日誌
            OrderProcessor.saveLogsToMysql('發送訂單到 API 的請求', { urlParams: urlParams }, fullApiUrl);
            
            axios.get('sendOrderUrlByCORS.php?' + urlParams)
                .then(function(response) {
                    const resdata = response.data;
                    let orderId = '';
                    console.log(resdata);
                    console.log(resdata.Status);
                    
                    // 記錄 API 響應日誌
                    OrderProcessor.saveLogsToMysql('API 響應結果', resdata, fullApiUrl);
                    
                    if (resdata.Status == '1') {
                        orderId = resdata.OrderId;
                        params.append('orderId', orderId);
                        OrderProcessor.insertOrderData(params);

                        alert('下單成功');

                        //sessionStorage.clear();
                        window.location = "finishOrder.php?orderId=" + orderId; // 已註解跳轉到訂單完成頁面
                    } else {
                        alert('下單發生錯誤，請洽小編');
                    }
                })
                .catch(function(error) {
                    console.error('Error fetching :', error);
                    alert('API 請求失敗，請重新下單');
                    window.location = "order.php";
                });
        } catch (e) {
            alert('API下單錯誤，請洽小編\n' + e);
            console.error('API下單錯誤:', e);
            window.location = "order.php";
        }
    },
    
    /**
     * 將訂單添加到 Redis 佇列中，以每秒發送一次到 API
     * @param {string} urlParams - URL參數字符串
     * @param {URLSearchParams} params - 訂單參數
     */
    sendOrderToQueueApi: function(urlParams, params) {
        try {
            // 檢查 urlParams 是否為有效字符串
            if (!urlParams || typeof urlParams !== 'string') {
                alert('訂單參數無效，請重新下單');
                console.error('訂單參數無效:', urlParams);
                window.location = "order.php";
                return;
            }
            
            // 檢查參數中是否有 undefined 值
            if (urlParams.includes('undefined') || urlParams.includes('null')) {
                alert('訂單參數有誤，請重新下單');
                console.error('訂單參數有 undefined 或 null 值:', urlParams);
                // 返回到流程的第一步重新下單
                window.location = "order.php";
                return;
            }
            
            // 特別檢查 Count 參數
            const countMatch = urlParams.match(/Count=([^&]+)/);
            if (countMatch && countMatch[1]) {
                const counts = decodeURIComponent(countMatch[1]).split(',');
                let hasInvalidCount = false;
                let invalidCountIndex = -1;
                
                for (let i = 0; i < counts.length; i++) {
                    const count = counts[i];
                    if (!count || isNaN(count) || parseInt(count) <= 0) {
                        hasInvalidCount = true;
                        invalidCountIndex = i;
                        break;
                    }
                }
                
                if (hasInvalidCount) {
                    alert('商品數量無效，請確保所有商品數量大於 0');
                    console.error('商品數量無效:', counts, '無效索引:', invalidCountIndex);
                    window.location = "order.php";
                    return;
                }
            }
            
            // 解析 URL 參數並檢查每個值
            const paramPairs = urlParams.split('&');
            const requiredParams = ['UserId', 'Password', 'Customer', 'GameAccount', 'Item', 'Count'];
            const foundParams = {};
            
            // 檢查每個參數對
            for (let i = 0; i < paramPairs.length; i++) {
                const pair = paramPairs[i].split('=');
                if (pair.length < 2 || pair[1] === '' || pair[1] === 'undefined' || pair[1] === 'null') {
                    alert('訂單參數 ' + pair[0] + ' 有誤，請重新下單');
                    console.error('訂單參數有空值或無效值:', pair[0], pair[1]);
                    // 返回到流程的第一步重新下單
                    window.location = "order.php";
                    return;
                }
                
                // 標記找到的必要參數
                if (requiredParams.includes(pair[0])) {
                    foundParams[pair[0]] = true;
                }
            }
            
            // 檢查是否所有必要參數都存在
            for (let i = 0; i < requiredParams.length; i++) {
                if (!foundParams[requiredParams[i]]) {
                    alert('缺少必要的訂單參數: ' + requiredParams[i] + '，請重新下單');
                    console.error('缺少必要的訂單參數:', requiredParams[i]);
                    window.location = "order.php";
                    return;
                }
            }
            
            // 記錄最終的 URL 參數
            console.log('將訂單添加到佇列，參數:', urlParams);
            
            // 構建完整的 API URL
            const apiBaseUrl = 'http://www.adp.idv.tw/api/Order?';
            const fullApiUrl = apiBaseUrl + urlParams;
            
            // 記錄 API 請求日誌
            OrderProcessor.saveLogsToMysql('將訂單添加到佇列', { urlParams: urlParams }, fullApiUrl);
            
            // 將訂單添加到 Redis 佇列
            axios.post('addOrderToQueue.php?' + urlParams, params)
                .then(function(response) {
                    const resdata = response.data;
                    console.log('佇列響應:', resdata);
                    
                    // 記錄佇列響應日誌
                    OrderProcessor.saveLogsToMysql('佇列響應結果', resdata, 'addOrderToQueue.php');
                    
                    if (resdata.success) {
                        // 訂單已成功添加到佇列
                        console.log('訂單已成功添加到處理佇列');
                        
                        // 創建臨時訂單 ID
                        const tempOrderId = 'queue_' + resdata.queue_id;
                        params.append('orderId', tempOrderId);
                        params.append('queueId', resdata.queue_id);
                        OrderProcessor.insertOrderData(params);
                        
                        // 獲取訂單JSON數據並發送到官方LINE
                        try {
                            // 從 URL 參數中提取訂單數據
                            const orderParams = {};
                            urlParams.split('&').forEach(param => {
                                const [key, value] = param.split('=');
                                if (key && value) orderParams[key] = decodeURIComponent(value);
                            });
                            
                            // 從表單參數中提取更多訂單數據
                            const formData = {};
                            for (const [key, value] of params.entries()) {
                                formData[key] = value;
                            }
                            
                            // 合併數據並發送到官方LINE
                            const combinedData = {...orderParams, ...formData};
                            
                            // 使用Promise處理LINE訊息發送
                            OrderProcessor.sendMessageToLineOfficial(combinedData)
                                .then(() => {
                                    console.log('LINE訊息發送成功，準備跳轉到訂單完成頁面');
                                    // 訊息發送成功後跳轉到訂單完成頁面
                                    window.location = "finishOrder.php?orderId=" + tempOrderId;
                                })
                                .catch((error) => {
                                    alert('LINE訊息發送失敗:', error);
                                    // 即使LINE訊息發送失敗，仍然跳轉到訂單完成頁面，因為訂單已經成功添加到佇列
                                    setTimeout(() => {
                                        window.location = "finishOrder.php?orderId=" + tempOrderId;
                                    }, 2000); // 延遲2秒，讓用戶有時間看到錯誤訊息
                                });
                        } catch (e) {
                            console.error('準備發送LINE訊息時出錯:', e);
                            // 即使出錯，仍然跳轉到訂單完成頁面
                            setTimeout(() => {
                                window.location = "finishOrder.php?orderId=" + tempOrderId;
                            }, 2000);
                        }
                        
                        // 注意：跳轉已移至Promise處理中，確保在訊息發送完成後才跳轉
                    } else {
                        console.error('佇列錯誤:', resdata.message);
                        // 如果佇列添加失敗，嘗試直接發送訂單
                        console.log('佇列處理暫時不可用，將直接處理您的訂單');
                        OrderProcessor.sendOrderToApi(urlParams, params);
                    }
                })
                .catch(function(error) {
                    console.error('Error adding to queue:', error);
                    // 如果佇列添加失敗，嘗試直接發送訂單
                    console.log('佇列處理暫時不可用，將直接處理您的訂單');
                    OrderProcessor.sendOrderToApi(urlParams, params);
                });
        } catch (e) {
            alert('添加訂單到佇列時發生錯誤，請洽小編\n' + e);
            console.error('添加訂單到佇列錯誤:', e);
            window.location = "order.php";
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
     * @returns {Promise} - 返回一個Promise，表示訊息發送的結果
     */
    sendMessageToLineOfficial: function(params_json_data) {
        return new Promise((resolve, reject) => {
            try {
                // 檢查LIFF是否已初始化
                if (typeof liff === 'undefined' || !liff.isInClient()) {
                    const errorMsg = '無法傳送訊息到LINE，請確保在LINE應用內開啟此頁面';
                    console.error(errorMsg);
                    alert(errorMsg);
                    reject(new Error(errorMsg));
                    return;
                }
                
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

                // 記錄發送的訊息內容
                console.log('準備發送訊息到LINE:', txt);
                
                // 傳送通知到官方LINE
                liff.sendMessages([{
                        type: "text",
                        text: txt,
                    }])
                    .then(() => {
                        console.log('訊息成功發送到LINE');
                        alert('訂單內容已傳送到官方LINE');
                        resolve('訊息發送成功');
                    })
                    .catch((err) => {
                        const errorMsg = '無法傳送訊息到LINE: ' + err.message;
                        console.error("LINE訊息發送錯誤", err);
                        alert('LINE訊息發送失敗，請截圖洽小編\n' + errorMsg);
                        reject(err);
                    });
            } catch (e) {
                const errorMsg = '傳送訂單內容發生錯誤: ' + e.message;
                console.error('傳送訂單內容錯誤:', e);
                alert(errorMsg);
                reject(e);
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
        // 確保 json_data 是陣列且 gameAccountSid 有值
        if (!Array.isArray(json_data) || !json_data.length || !gameAccountSid) {
            console.error('過濾遊戲賬號時參數無效:', { json_data, gameAccountSid });
            return [];
        }
        
        // 找出 Sid 等於 gameAccountSid
        let result = json_data.filter(item => item && item.Sid == gameAccountSid);
        return result;
    },

    /**
     * 把要記錄的logs存到數據庫裡面
     * @param {string} log_type - 日誌類型
     * @param {Object} params_json_data - 參數JSON數據
     * @param {string} api_url - API URL (可選)
     */
    saveLogsToMysql: function(log_type, params_json_data, api_url) {
        try {
            // 構建日誌數據
            const logData = {
                type: log_type,
                JSON: JSON.stringify(params_json_data)
            };
            
            // 如果提供了 API URL，則添加到日誌數據中
            if (api_url) {
                logData.api_url = api_url;
            }
            
            axios.post('saveLogsToMysql.php', logData)
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