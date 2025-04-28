/**
 * 订单处理模块 - 包含所有与订单处理相关的功能
 */

// 价格计算模块
const PriceCalculator = {
    /**
     * 计算单个商品的价格
     * @param {number} gameRate - 商品单价
     * @param {number} bonus - 奖励倍数
     * @param {number} rateValue - 汇率值
     * @param {number} count - 购买数量
     * @param {string} customerCurrency - 客户币别
     * @returns {number} - 计算后的价格
     */
    calculateItemPrice: function(gameRate, bonus, rateValue, count, customerCurrency) {
        console.log('===== 价格计算开始 =====');
        console.log('输入参数：');
        console.log('- 商品单价(gameRate):', gameRate);
        console.log('- 奖励倍数(bonus):', bonus);
        console.log('- 汇率值(rateValue):', rateValue);
        console.log('- 购买数量(count):', count);
        console.log('- 客户币别(customerCurrency):', customerCurrency);

        let result;
        if (rateValue == 1) {
            const basePrice = gameRate * bonus * rateValue;
            console.log('基础价格计算：', gameRate, '*', bonus, '*', rateValue, '=', basePrice);
            result = Math.ceil(basePrice) * count;
            console.log('最终价格：', Math.ceil(basePrice), '*', count, '=', result);
        } else {
            if (customerCurrency.includes('新')) {
                const basePrice = gameRate * bonus / rateValue;
                console.log('基础价格计算(新币)：', gameRate, '*', bonus, '/', rateValue, '=', basePrice);
                result = this.roundUp(basePrice, 1) * count;
                console.log('最终价格：', this.roundUp(basePrice, 1), '*', count, '=', result);
            } else {
                const basePrice = gameRate * bonus / rateValue;
                console.log('基础价格计算：', gameRate, '*', bonus, '/', rateValue, '=', basePrice);
                result = Math.ceil(basePrice) * count;
                console.log('最终价格：', Math.ceil(basePrice), '*', count, '=', result);
            }
        }
        console.log('===== 价格计算结束 =====');
        return result;
    },

    /**
     * 四舍五入到指定小数位
     * @param {number} num - 要四舍五入的数字
     * @param {number} decimal - 小数位数
     * @returns {number} - 四舍五入后的结果
     */
    roundUp: function(num, decimal) {
        const result = Math.ceil((num + Number.EPSILON) * Math.pow(10, decimal)) / Math.pow(10, decimal);
        console.log(`四舍五入到${decimal}位小数：${num} → ${result}`);
        return result;
    },

    /**
     * 计算所有商品的价格并返回格式化的结果
     * @returns {Object} - 包含商品格式、总价和币别的对象
     */
    calculateTotalPrice: function() {
        // 设定rate汇率
        const rate = JSON.parse(sessionStorage.getItem('rate'));

        // 获取下单游戏里的gameRate
        const gameRate = JSON.parse(sessionStorage.getItem('gameRate'));

        // 获取客人资料
        const customerData = JSON.parse(sessionStorage.getItem('customerData'));

        // 获取客人的币别
        const customerCurrency = customerData.Currency;

        // 依客人币别来比对汇率并获取汇率值
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

// 订单处理模块
const OrderProcessor = {
    /**
     * 发送订单到服务器
     */
    sendOrder: function() {
        try {
            const orderData = this.prepareOrderData();
            const params = this.createOrderParams(orderData);
            const params_json_data = this.createOrderJsonData(orderData);

            // 记录用户的参数log
            this.saveLogsToMysql('在传送订单到官方LINE之前的params_json_data', params_json_data);

            // 传送订单内容到官方LINE
            this.sendMessageToLineOfficial(params_json_data);

            // 发送订单到API
            this.sendOrderToApi(orderData.UrlParametersString, params);
        } catch (e) {
            alert('发送订单时发生错误，请洽小编\n' + e);
            console.error('发送订单错误:', e);
        }
    },

    /**
     * 准备订单数据
     * @returns {Object} - 订单数据对象
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
        
        // 过滤游戏账号
        let customerGameAccount = this.filterGameAccount(
            JSON.parse(sessionStorage.getItem('customerGameAccounts')), 
            sessionStorage.getItem('gameAccountSid')
        );
        const customerGameAccounts = customerGameAccount[0];
        const orderDateTime = sessionStorage.getItem('orderDateTime');
        const gameRemark = sessionStorage.getItem('gameRemark');
        
        // 构建URL参数字符串
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
     * 创建订单参数
     * @param {Object} orderData - 订单数据
     * @returns {URLSearchParams} - 订单参数
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
            params.append('status', '订单处理中');
            params.append('itemsMoney', orderData.itemMoney);
            params.append('sumMoney', orderData.sumMoney);
            params.append('orderDateTime', orderData.orderDateTime);
            params.append('gameRemark', orderData.gameRemark);
        } catch (e) {
            alert('组参数发生错误，请洽小编\n' + e);
            console.error('组参数错误:', e);
        }
        
        return params;
    },

    /**
     * 创建订单JSON数据
     * @param {Object} orderData - 订单数据
     * @returns {Object} - 订单JSON数据
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
            "status": "订单处理中",
            "itemsMoney": orderData.itemMoney,
            "sumMoney": orderData.sumMoney,
            "orderDateTime": orderData.orderDateTime,
            "gameRemark": orderData.gameRemark
        };
    },

    /**
     * 发送订单到API
     * @param {string} urlParams - URL参数字符串
     * @param {URLSearchParams} params - 订单参数
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

                        alert('下单成功');

                        //sessionStorage.clear();
                        window.location = "finishOrder.php?orderId=" + orderId;
                    } else {
                        alert('下单发生错误，请洽小编');
                    }
                })
                .catch(function(error) {
                    console.error('Error fetching :', error);
                });
        } catch (e) {
            alert('API下单错误，请洽小编\n' + e);
            console.error('API下单错误:', e);
        }
    },

    /**
     * 新增订单数据到数据库
     * @param {URLSearchParams} params - 订单参数
     */
    insertOrderData: function(params) {
        axios.post('addOrderData.php', params)
            .then(function(response) {
                console.log('数据新增成功', response.data);
                console.log(response.data);
            })
            .catch(function(error) {
                console.error('数据新增失败', error);
            });
    },

    /**
     * 传送订单内容到官方LINE
     * @param {Object} params_json_data - 订单JSON数据
     */
    sendMessageToLineOfficial: function(params_json_data) {
        try {
            const jsonParams = params_json_data;
            const itemArr = PriceCalculator.calculateTotalPrice();

            // 输出 JSON 对象
            let txt = "";
            txt += "【自动下单】\n";
            txt += "客户编号: " + jsonParams.customerId + "\n";
            txt += "下单时间: " + jsonParams.orderDateTime + "\n";
            txt += "游戏名称: " + jsonParams.gameName + "\n";
            txt += "登入方式: " + jsonParams.logintype + "\n";
            txt += "游戏账号: " + jsonParams.acount + "\n";
            txt += "游戏密码: " + jsonParams.password + "\n";
            txt += "伺 服 器: " + jsonParams.serverName + "\n";
            txt += "角色名称: " + jsonParams.gameAccountName + "\n";
            txt += "\n";
            txt += itemArr.gameitemSLabelText + '\n\n';
            txt += '总计: $' + itemArr.sumMoney + "\n";
            txt += "币别: " + itemArr.customerCurrency + '\n\n';
            txt += "备注: \n" + jsonParams.gameRemark + '\n';

            txt = txt.replace(/<br\s*[\/]?>/gi, "\n");

            // 传送通知到官方LINE
            liff.sendMessages([{
                    type: "text",
                    text: txt,
                }, ])
                .then(() => {
                    alert('订单内容传送到官方');
                })
                .catch((err) => {
                    console.log("error", err);
                    //alert('下单错误 请截图洽小编' + err);
                });
        } catch (e) {
            alert('传送订单内容发生错误\n' + e);
            console.error('传送订单内容错误:', e);
        }
    },

    /**
     * 过滤游戏账号
     * @param {Array} json_data - 游戏账号数据
     * @param {string} gameAccountSid - 游戏账号SID
     * @returns {Array} - 过滤后的游戏账号
     */
    filterGameAccount: function(json_data, gameAccountSid) {
        // 找出 Sid 等于 gameAccountSid
        let result = json_data.filter(item => item.Sid == gameAccountSid);
        return result;
    },

    /**
     * 把要记录的logs存到数据库里面
     * @param {string} log_type - 日志类型
     * @param {Object} params_json_data - 参数JSON数据
     */
    saveLogsToMysql: function(log_type, params_json_data) {
        try {
            axios.post('saveLogsToMysql.php', {
                    type: log_type,
                    JSON: JSON.stringify(params_json_data)
                })
                .then(function(response) {
                    console.log('成功存数据库 1>', response.data);
                })
                .catch(function(error) {
                    console.log(error);
                });
        } catch (e) {
            alert('错误，请洽小编\n' + e);
            console.error('保存日志错误:', e);
        }
    },

    /**
     * 检查余额是否足够下单
     * @param {number} sumMoney - 订单总额
     */
    checkBalance: function(sumMoney) {
        // 客人的余额
        let customerBalance = JSON.parse(sessionStorage.getItem('customerData')).CurrentMoney;

        if (typeof customerBalance === "undefined") {
            customerBalance = 0;
            console.log("customerBalance=" + customerBalance);
        } else {
            customerBalance = JSON.parse(sessionStorage.getItem('customerData')).CurrentMoney;
        }

        // 订单总额大于客人余额不给下单
        // if (sumMoney > customerBalance) {
        //     alert('您的余额不足\n要自动下单\n请先至官方LINE\n找小编储值钱包哟');
        //     $('.btn').hide();
        //     sessionStorage.clear();
        //     window.location.href = 'https://liff.line.me/2000183731-BLmrAGPp';
        // }
    }
};

// 工具函数模块
const Utils = {
    /**
     * 把URL的QueryString转换成JSON
     * @param {string} queryString - 查询字符串
     * @returns {string} - JSON字符串
     */
    transferQueryStringToJSON: function(queryString) {
        // 使用 URLSearchParams 解析字符串
        const searchParams = new URLSearchParams(queryString);

        // 将 URLSearchParams 转换为 JavaScript 对象
        const obj = {};
        searchParams.forEach(function(value, key) {
            obj[key] = value;
        });

        // 将 JavaScript 对象转换为 JSON 字符串
        const jsonString = JSON.stringify(obj);
        return jsonString;
    },

    /**
     * 把一个新的参数加到JSON里面
     * @param {string} value - 参数值
     * @param {string} jsonString - JSON字符串
     * @returns {string} - 新的JSON字符串
     */
    addNewParameterToJson: function(value, jsonString) {
        // 将 JSON 字符串解析为 JavaScript 对象
        const obj = JSON.parse(jsonString);

        // 新增参数到 JavaScript 对象
        obj.orderId = value;

        // 再将 JavaScript 对象转换为 JSON 字符串
        const newJsonString = JSON.stringify(obj);

        return newJsonString;
    },

    /**
     * 返回上一页
     */
    goback: function() {
        window.location.href = 'https://liff.line.me/2000183731-BLmrAGPp';
    }
};

// LIFF模块
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
                console.log('启动失败。');
            });
    },

    /**
     * 初始化应用
     */
    initializeApp: function() {
        console.log('启动成功。');

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

// 导出模块
window.PriceCalculator = PriceCalculator;
window.OrderProcessor = OrderProcessor;
window.Utils = Utils;
window.LiffManager = LiffManager;