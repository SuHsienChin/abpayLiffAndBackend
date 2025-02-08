<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>遊戲下單</title>
    <!-- 引入 Bootstrap 的 CSS 檔案 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="preconnect" href="https://maxcdn.bootstrapcdn.com">
    <link rel="preconnect" href="https://static.line-scdn.net">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>遊戲下單</h3>
                    </div>
                    <div id="loading" class="modal" style="display: none;">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-body text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" action="order_check.php">
                            <div class="form-group">
                                <label>LineId：</label>
                                <input id="lineId" value=""></input>
                                <!-- <input id="lineId" value="" readonly></input> 上線再改readonly -->
                                <!-- <button type="button" class="btn btn-primary" onclick="customerBtn()">確定</button> -->
                            </div>
                            <div class="form-group">
                                <label>客戶名稱：</label>
                                <label id="customerData"></label>
                            </div>
                            <div class="form-group">
                                <label>錢包餘額：</label>
                                <label id="walletBalance"></label>
                            </div>
                            <div class="form-group">
                                <label for="gameName">遊戲名稱</label>
                                <select class="form-control" id="gameName" name="gameName"
                                    onchange="gameNameOnchange()">
                                    <option value="">請選擇...</option>
                                    <!-- 此處的選項會由前端 JavaScript 在 AJAX 回傳後動態生成 -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label>遊戲帳號</label>
                                <select class="form-control" id="gameAccount" name="gameAccount">
                                    <option value="">請選擇</option>
                                    <!-- 此處的選項會由前端 JavaScript 在 AJAX 回傳後動態生成 -->
                                </select>
                            </div>
                            <div class="form-group" id="gameItemsGroup">
                                <label for="gameItem">遊戲商品</label>
                                <div class="d-flex align-items-center">
                                    <select class="form-control mr-2 gameItems" id="gameItem" name="gameItem">
                                        <option value="">請先選擇遊戲名稱</option>
                                        <!-- 此處的選項會由前端 JavaScript 在 AJAX 回傳後動態生成 -->
                                    </select>
                                    <input type="number" class="form-control mr-2 gameItemCount" id="quantity"
                                        name="quantity" style="max-width: 70px;" placeholder="數量" value="1">
                                    <!-- <button type="button" class="btn btn-danger">X</button> -->
                                </div>
                                <br />
                            </div>
                            <div class="form-group" id="gameItemsGroup">
                                <label for="gameItem">禮包名稱(並請提供截圖於對話內)</label>
                                <div class="d-flex align-items-center">
                                    <textarea id="gameRemark" name="gameRemark" rows="3" cols="50"></textarea>
                                </div>
                                <br />
                            </div>
                            <div class="form-group" id="gameItemsGroup">
                                <h5 class="text-danger">無餘額：<br />下單成功➡️等候小編提供收款帳戶➡️收到款項後安排儲值</h5>
                                <h5 class="text-danger">有餘額：<br />下單成功➡️直接安排儲值</h5>
                                <br />
                                <h5 class="text-danger">❤️兌換介紹金請洽小編❤️</h5>
                            </div>

                            <button type="button" class="btn btn-primary btn-block mb-3"
                                onclick="addGameItem()">新增遊戲商品</button>
                            <button type="button" class="btn btn-success btn-block"
                                onclick="confirmOrder()">確定送出</button>
                            <button type="button" class="btn btn-secondary btn-block mt-2">取消</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 引入 Bootstrap 的 JavaScript 檔案（注意順序：先引入 jQuery，再引入 Bootstrap 的 JS） -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- 以下是liff 要上線時需打開 -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script>
        let loadingModal;
        let cachedGameLists = null; // 新增遊戲列表快取
        
        // 移除重複的初始化代碼
        $(document).ready(function() {
            // 初始化 loading modal
            loadingModal = new bootstrap.Modal(document.getElementById('loading'), {
                backdrop: 'static',
                keyboard: false
            });
            
            // 清除舊的 session 資料
            sessionStorage.clear();
            
            // 初始化 LIFF
            initializeLiff('2000183731-BLmrAGPp');
        });

        function initializeLiff(myLiffId) {
            liff.init({
                liffId: myLiffId,
                withLoginOnExternalBrowser: true
            })
            .then(() => {
                // 顯示 loading
                loadingModal.show();
                
                // 初始化應用
                return initializeApp();
            })
            .catch((err) => {
                console.error('LIFF 初始化失敗:', err);
                alert('系統初始化失敗，請重新整理頁面');
            });
        }

        function initializeApp() {
            console.log('啟動成功。');
            
            // 檢查維護時間
            if (isMaintenanceTime()) {
                alert('系統正在維護，維護時間為早上7點到8點之間');
                if (typeof liff !== 'undefined' && liff.closeWindow) {
                    liff.closeWindow();
                }
                return;
            }
            
            // 取得用戶資料
            return liff.getProfile()
                .then(profile => {
                    sessionStorage.setItem('lineUserId', profile.userId);
                    $("#lineId").val(profile.userId);
                    return customerBtn(profile.userId);
                })
                .catch(err => {
                    console.error('取得用戶資料失敗:', err);
                    loadingModal.hide();
                    alert('無法取得用戶資料，請重新整理頁面');
                });
        }

        // customerBtn 函數修改
        async function customerBtn(mylineId) {
            try {
                // 平行請求客戶資料和遊戲帳號
                const [customerResponse, accountsResponse] = await Promise.all([
                    axios.get('getCustomer.php?lineId=' + mylineId),
                    axios.get('getGameAccount.php?Sid=' + mylineId)
                ]);

                const customerData = customerResponse.data;
                
                // 處理客戶資料
                const customer = document.getElementById("customerData");
                const walletBalance = document.getElementById("walletBalance");
                const currentMoney = customerData.CurrentMoney || 0;
                
                customer.innerHTML = customerData.Id + ' ' + customerData.Name;
                walletBalance.innerHTML = currentMoney + ' ' + customerData.Currency;
                
                // 儲存到 sessionStorage
                sessionStorage.setItem('customerData', JSON.stringify(customerData));
                sessionStorage.setItem('lineId', mylineId);

                // 處理遊戲帳號資料 - 修改這部分的判斷邏輯
                if (!accountsResponse.data || !Array.isArray(accountsResponse.data) || accountsResponse.data.length === 0) {
                    console.error('無遊戲帳號資料');
                    alert('您還沒建立遊戲資料\n請點確定後將LINE ID複製給小編\n請洽小編建立資料');
                    loadingModal.hide();
                    return;
                }

                // 處理遊戲帳號
                const uniqueGames = {};
                const filteredData = accountsResponse.data.filter(item => {
                    if (!uniqueGames[item.GameSid]) {
                        uniqueGames[item.GameSid] = true;
                        return true;
                    }
                    return false;
                });

                // 確保有過濾後的資料
                if (filteredData.length > 0) {
                    sessionStorage.setItem('customerGameAccounts', JSON.stringify(accountsResponse.data));
                    sessionStorage.setItem('customerGameNames', JSON.stringify(filteredData));
                    
                    // 載入遊戲列表
                    await loadGameLists(filteredData);
                } else {
                    console.error('無有效的遊戲帳號資料');
                    alert('無有效的遊戲帳號資料，請聯繫客服');
                    loadingModal.hide();
                    return;
                }
                
                // 最後隱藏 loading
                loadingModal.hide();
            } catch (error) {
                console.error('載入資料錯誤:', error);
                loadingModal.hide();
                alert('載入資料失敗，請重新整理頁面');
            }
        }

        // 優化遊戲列表載入
        async function loadGameLists(customerGameAccounts) {
            try {
                // 使用快取的遊戲列表
                if (!cachedGameLists) {
                    const [switchResponse, gameListResponse] = await Promise.all([
                        axios.get('get_switch_game_lists.php'),
                        axios.get('../getGameList.php')
                    ]);
                    
                    const switchGameListsData = switchResponse.data;
                    const allGameLists = gameListResponse.data;
                    cachedGameLists = filterGames(allGameLists, switchGameListsData);
                }

                // 生成遊戲選項
                const gameNameSelect = document.getElementById("gameName");
                let options = '<option value="">請選擇遊戲</option>';
                
                customerGameAccounts.forEach(item => {
                    const gameData = cachedGameLists.find(game => game.Sid === parseInt(item.GameSid));
                    if (gameData) {
                        options += `<option value="${gameData.Sid}" data-gameRate="${gameData.GameRate}">${gameData.Name}</option>`;
                    }
                });
                
                gameNameSelect.innerHTML = options;
            } catch (error) {
                console.error('載入遊戲列表錯誤:', error);
            }
        }

        // 優化遊戲商品載入
        async function getGameItems() {
            const selectedGame = document.getElementById("gameName").value;
            const gameItemDropdown = document.getElementById("gameItem");
            
            // 清空選項
            gameItemDropdown.innerHTML = '<option value="">請選擇...</option>';
            removeElementsByClass("dropdownDiv");
            
            // 檢查快取
            const cacheKey = `gameItems_${selectedGame}`;
            const cachedItems = sessionStorage.getItem(cacheKey);
            
            try {
                let gameItems;
                if (cachedItems) {
                    gameItems = JSON.parse(cachedItems);
                } else {
                    const response = await axios.get('getGameItem.php?Sid=' + selectedGame);
                    gameItems = response.data;
                    sessionStorage.setItem(cacheKey, JSON.stringify(gameItems));
                }
                
                // 處理商品資料
                gameItems = processGameItems(gameItems);
                
                // 生成選項
                let options = '<option value="-1">請選擇遊戲商品</option>';
                gameItems.forEach(item => {
                    if (item.Enable === 1) {
                        options += `<option value="${item.Sid}" data-bouns="${item.Bonus}">${item.Name}</option>`;
                    }
                });
                
                gameItemDropdown.innerHTML = options;
                sessionStorage.setItem('gemeItems', JSON.stringify(gameItems));
            } catch (error) {
                console.error('Error:', error);
                gameItemDropdown.innerHTML = '<option value="">無法取得商品資料</option>';
            }
        }

        // 處理遊戲商品資料
        function processGameItems(gameItems) {
            gameItems = removeAfterOnderLineWords(gameItems);
            const hkdFlag = checkHkdCurrencyAndHkdGameItems(gameItems);
            return hkdFlag ? returnHkdGameItems(gameItems) : gameItems;
        }

        // 遊戲名稱選項變更時執行的function
        function gameNameOnchange() {
            getGameAccounts();
            getGameItems();
        }

        //取得遊戲裡面的帳密資料
        function getGameAccounts() {
            const selectedGame = document.getElementById("gameName").value;
            const gameItemDropdown = document.getElementById("gameAccount");

            const customerGameAccounts = JSON.parse(sessionStorage.getItem('customerGameAccounts'));
            // 清空商品下拉選單
            gameItemDropdown.innerHTML = '<option value="">請選擇...</option>';

            // 清空新增的下拉選單
            removeElementsByClass("dropdownDiv");

            let options = '<option value="">請選擇帳號</option>';
            $.each(customerGameAccounts, function (i, item) {
                if (item.GameSid === selectedGame) {
                    options += `<option 
                        data-login_account="${item.LoginAccount}" 
                        data-login_password="${item.LoginPassword}" 
                        data-login_type="${item.LoginType}" 
                        data-characters="${item.Characters}" 
                        data-server_name="${item.ServerName}" 
                        data-login_account_Sid="${item.Sid}" 
                        value="${item.LoginAccount}" 
                        >${item.LoginAccount}　${item.Characters}</option>`;
                    gameItemDropdown.innerHTML = options;
                }
            });
        }

        // 新增遊戲商品
        function addGameItem() {
            const selectedGame = document.getElementById("gameName").value;

            const dropdownDiv = document.createElement("div");
            dropdownDiv.classList.add("dropdownDiv", "d-flex", "align-items-center");

            const count = document.createElement("input");
            count.setAttribute("type", "number");
            count.setAttribute("value", "1");
            count.setAttribute("style", "max-width: 70px;");
            count.classList.add("form-control", "mr-2", "gameItemCount");

            const deleteButton = document.createElement("button");
            deleteButton.setAttribute("type", "button");
            deleteButton.classList.add("btn", "btn-danger", "delete-btn");
            deleteButton.innerText = "X";
            deleteButton.onclick = function () {
                deleteDropdown(dropdownDiv);
            };

            const newGameItem = document.createElement("select");
            newGameItem.classList.add("form-control", "mr-2", "gameItems");

            // 從 sessionStorage 取資料
            const gameItemsFromSession = sessionStorage.getItem('gemeItems');
            if (gameItemsFromSession) {
                try {
                    let gameItems = JSON.parse(gameItemsFromSession);

                    // 如果需要過濾商品，可在這裡進行處理
                    gameItems = removeAfterOnderLineWords(gameItems);

                    // 確認港幣客人只能顯示港幣商品
                    const hkdFlag = checkHkdCurrencyAndHkdGameItems(gameItems);

                    if (hkdFlag === true) {
                        gameItems = returnHkdGameItems(gameItems);
                    }

                    let options = '<option value="-1">請選擇遊戲商品</option>';
                    $.each(gameItems, function (i, item) {
                        if (item.Enable === 1) {
                            options += `<option value="${item.Sid}" data-bouns="${item.Bonus}">${item.Name}</option>`;
                        }
                    });
                    newGameItem.innerHTML = options;
                } catch (e) {
                    console.error("Error parsing sessionStorage data:", e);
                    newGameItem.innerHTML = '<option value="">無法取得商品資料</option>';
                }
            } else {
                console.error('No game items found in sessionStorage.');
                newGameItem.innerHTML = '<option value="">無法取得商品資料</option>';
            }

            dropdownDiv.appendChild(newGameItem);
            dropdownDiv.appendChild(count);
            dropdownDiv.appendChild(deleteButton);
            dropdownDiv.appendChild(document.createElement("br"));
            dropdownDiv.appendChild(document.createElement("br"));

            document.getElementById("gameItemsGroup").appendChild(dropdownDiv);
        }

        // 刪除下拉選單
        function deleteDropdown(dropdownDiv) {
            const gameItemsGroup = document.getElementById("gameItemsGroup");
            gameItemsGroup.removeChild(dropdownDiv);
        }

        // 移除遊戲商品
        function removeElementsByClass(className) {
            const elements = document.getElementsByClassName(className);
            while (elements.length > 0) {
                elements[0].parentNode.removeChild(elements[0]);
            }
        }

        function getGemeItemsDataToJson(selectedGame) {
            axios.get('getGameItem.php?Sid=' + selectedGame)
                .then(function (response) {
                    return response;
                })
                .catch(function (error) {
                    console.error('Error fetching game items:', error);
                    return '<option value="">無法取得商品資料</option>';
                    gameItemDropdown.innerHTML = '<option value="">無法取得商品資料</option>';
                });
        }

        // 存已選擇的遊戲商品跟數量到sessionStorage
        function setGameItemsToSessionStorage() {
            const gameItemSelectedValues = [];
            const gameItemSelectedTexts = [];
            const gameItemCounts = [];
            const gameItemBouns = [];

            // const gameItemSelects = document.querySelectorAll('.gameItems'); // 選取特定class的下拉選單
            // gameItemSelects.forEach(gameItemSelect => {
            //     gameItemSelectedValues.push(gameItemSelect.value);
            //     gameItemSelectedTexts.push(gameItemSelect.options[gameItemSelect.selectedIndex].textContent);
            // });

            $('.gameItems').each(function () {
                gameItemSelectedValues.push($(this).find(':selected').val());
                gameItemSelectedTexts.push($(this).find(':selected').text());
                gameItemBouns.push($(this).find(':selected').attr('data-bouns'));
            });

            $('.gameItemCount').each(function () {
                gameItemCounts.push($(this).val());
            });

            sessionStorage.setItem('gameItemSelectedValues', JSON.stringify(gameItemSelectedValues));
            sessionStorage.setItem('gameItemSelectedTexts', JSON.stringify(gameItemSelectedTexts));
            sessionStorage.setItem('gameItemBouns', JSON.stringify(gameItemBouns));
            sessionStorage.setItem('gameItemCounts', JSON.stringify(gameItemCounts));
        }

        // 確認是否下單
        function confirmOrder() {
            if (confirm("前往下一步？")) {

                // 確認遊戲名稱是否有選擇
                if (document.getElementById("gameName").value === "") {
                    alert("請選擇遊戲名稱");
                    return false;
                }

                // 確認遊戲帳號是否有選擇
                if (document.getElementById("gameAccount").value === "") {
                    alert("請選擇遊戲帳號");
                    return false;
                }

                // 確認遊戲商品是否有選擇  
                const gameItemSelects = document.querySelectorAll('.gameItems'); // 選取特定class的下拉選單
                let gameItemSelectCount = 0;
                gameItemSelects.forEach(gameItemSelect => {
                    if (gameItemSelect.value !== "-1") {
                        gameItemSelectCount++;
                    }
                });

                if (gameItemSelectCount === 0) {
                    alert("請選擇遊戲商品");
                    return false;
                }

                // 確認遊戲商品數量是否有填寫
                const gameItemCounts = document.querySelectorAll('.gameItemCount'); // 選取特定class的數量欄位
                let gameItemCountCount = 0;

                gameItemCounts.forEach(gameItemCount => {
               
                    if (parseInt(gameItemCount.value) > 0 && parseInt(gameItemCount.value) !== 0) {
                        gameItemCountCount++;
                    }
              
                });

                if (gameItemCountCount === 0) {
                    alert("請填寫遊戲商品數量");
                    return false;
                }

                //移除無效的下拉選單商品
                const gameItemsGroup = document.getElementById("gameItemsGroup");
                const gameItems = gameItemsGroup.querySelectorAll('.gameItems');
                gameItems.forEach(gameItem => {
                    if (gameItem.value === "-1") {
                        gameItem.parentNode.parentNode.removeChild(gameItem.parentNode);
                    }
                });

                //移除遊戲店品數量為0的下拉選單商品
                const removeGameItemCounts = document.querySelectorAll('.gameItemCount');
                removeGameItemCounts.forEach(gameItemCount => {
                    if (parseInt(gameItemCount.value) === 0) {
                        gameItemCount.parentNode.parentNode.removeChild(gameItemCount.parentNode);
                    }
                });

                sessionStorage.setItem('gameAccount', document.getElementById("gameAccount").value);
                sessionStorage.setItem('gameAccountSid', $('#gameAccount').find(':selected').attr('data-login_account_Sid'));
                sessionStorage.setItem('login_account', $('#gameAccount').find(':selected').attr('data-login_account'));
                sessionStorage.setItem('login_password', $('#gameAccount').find(':selected').attr('data-login_password'));
                sessionStorage.setItem('login_type', $('#gameAccount').find(':selected').attr('data-login_type'));
                sessionStorage.setItem('characters', $('#gameAccount').find(':selected').attr('data-characters'));
                sessionStorage.setItem('server_name', $('#gameAccount').find(':selected').attr('data-server_name'));
                sessionStorage.setItem('walletBalance', document.getElementById("walletBalance").innerText);
                sessionStorage.setItem('gameNameValue', document.getElementById("gameName").value);
                sessionStorage.setItem('gameNameText', $('#gameName').find(':selected').text());
                sessionStorage.setItem('name', document.getElementById("gameAccount").value);
                sessionStorage.setItem('gameRate', $('#gameName').find(':selected').attr('data-gamerate'));
                sessionStorage.setItem('gameRemark', document.getElementById("gameRemark").value);
                setGameItemsToSessionStorage();

                // 若使用者確認下單，則提交表單
                document.querySelector('form').submit();
            } else {
                return false;
            }
        }

        // 港幣客人只顯示港幣商品
        // 如果是港幣客人而且遊戲商品也有港幣專用的商品
        // 就回傳true 反之回傳 false
        function checkHkdCurrencyAndHkdGameItems(gameItems) {
            const customerCurrency = JSON.parse(sessionStorage.getItem('customerData')).Currency;
            const currency = "港";
            let currencyFlag = false;
            let itemFlag = false;

          

            if (customerCurrency.includes(currency)) {
                currencyFlag = true;
            }

            $.each(gameItems, function (i, item) {
                if (item.Name.includes(currency)) {
                    itemFlag = true;
                }
            });

            if (currencyFlag === true && itemFlag === true) {
                return true;
            } else {
                return false;
            }
        }

        // 港幣客人只顯示港幣商品
        // 如果是港幣客人而且遊戲商品也有港幣專用的商品
        // 就回傳港幣專用的商品
        function returnHkdGameItems(gameItems) {
            const returnGameItems = [];
            const currency = '港';
            $.each(gameItems, function (i, item) {

                if (item.Name.includes(currency)) {
                    returnGameItems.push(item);
                }

                // if (item.Name.search('港') != -1) {
                //     returnGameItems.push(item);
                // }
            });
            return returnGameItems;
        }

        // 去掉商品底線後面的字
        function removeAfterOnderLineWords(gameItems) {
            const returnItems = [];
            $.each(gameItems, function (i, item) {

                if (item.Name.split('_').length > 1) {
                    item.Name = item.Name.split('_')[0];
                    returnItems.push(item);
                } else {
                    returnItems.push(item);
                }
            });
            return returnItems;
        }

        // 取得控制遊戲是否要顯示在下拉的遊戲清單
        function switchGameLists() {
            return axios.get('get_switch_game_lists.php')
                .then(function (response) {
                    return response.data;
                })
                .catch(function (error) {
                    console.log('無法取得遊戲資料:', error);
                    return ('無法取得遊戲資料:', error);
                });
        }

        //篩選出有打開的遊戲
        function filterGames(jsonA, jsonB) {
            // 將 jsonB 轉換為以 Id 為 key 的物件
            const jsonBMap = jsonB.reduce((acc, curr) => {
                acc[curr.Id] = curr;
                return acc;
            }, {});

            // 篩選出 jsonA 中 Id 對應到 jsonB 的 Id 且 jsonB 的 flag 為 1 的資料
            const filteredJsonA = jsonA.filter(item => {
                const jsonBItem = jsonBMap[item.Id];
                return jsonBItem && jsonBItem.flag === 1;
            });

            return (filteredJsonA);
        }

        /*
            判斷是不是在早上7點到8點之間
            是的話就顯示維護中且並關閉LIFF
        */
        function isMaintenanceTime() {
            const now = new Date();
            const dayOfWeek = now.getDay(); // 0-6, 0 是星期日
            const hour = now.getHours();
            
            // 判斷是否為星期二(2)且時間是早上 7 點
            return dayOfWeek === 2 && hour === 7;
        }
    </script>
</body>

</html>