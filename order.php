<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>遊戲下單</title>
    <!-- 引入 Bootstrap 的 CSS 檔案 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
                                </select>
                            </div>
                            <div class="form-group">
                                <label>遊戲帳號</label>
                                <select class="form-control" id="gameAccount" name="gameAccount">
                                    <option value="">請選擇</option>
                                </select>
                            </div>
                            <div class="form-group" id="gameItemsGroup">
                                <label for="gameItem">遊戲商品</label>
                                <div class="d-flex align-items-center">
                                    <select class="form-control mr-2 gameItems" id="gameItem" name="gameItem">
                                        <option value="">請先選擇遊戲名稱</option>
                                    </select>
                                    <input type="number" class="form-control mr-2 gameItemCount" id="quantity"
                                        name="quantity" style="max-width: 70px;" placeholder="數量" value="1">
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

    <!-- 引入 Bootstrap 的 JavaScript 檔案 -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script>
        // 建立快取對象
        const cache = {
            gameItems: {},
            gameAccounts: {},
            customerData: null,
            gameList: null
        };

        const loadingModal = new bootstrap.Modal(document.getElementById('loading'), {
            backdrop: 'static',
            keyboard: false
        });

        loadingModal.show();

        $(function() {
            initializeLiff('2000183731-BLmrAGPp');
            sessionStorage.clear();
        });

        function initializeLiff(myLiffId) {
            liff.init({
                liffId: myLiffId,
                withLoginOnExternalBrowser: true,
            })
            .then(() => {
                initializeApp();
            })
            .catch((err) => {
                console.log('啟動失敗。', err);
            });
        }

        function initializeApp() {
            if (isMaintenanceTime()) {
                alert('系統正在維護，維護時間為早上7點到8點之間');
                if (liff?.closeWindow) {
                    liff.closeWindow();
                }
                return;
            }

            liff.getProfile()
                .then(profile => {
                    sessionStorage.setItem('lineUserId', profile.userId);
                    $("#lineId").val(profile.userId);
                    customerBtn(profile.userId);
                })
                .catch(err => console.log('error', err));
        }

        async function customerBtn(mylineId) {
            try {
                // 如果快取中有客戶資料，直接使用
                if (cache.customerData) {
                    updateCustomerDisplay(cache.customerData);
                    return;
                }

                const response = await axios.get('getCustomer.php?lineId=' + mylineId);
                const customerData = response.data;
                
                // 儲存到快取
                cache.customerData = customerData;
                
                updateCustomerDisplay(customerData);
                await getCustomerGameAccounts(customerData.Sid);
                
                loadingModal.hide();
            } catch (error) {
                console.error('取得客戶資料失敗:', error);
                loadingModal.hide();
            }
        }

        function updateCustomerDisplay(customerData) {
            const currentMoney = typeof customerData.CurrentMoney === 'undefined' ? 0 : customerData.CurrentMoney;
            
            document.getElementById("customerData").innerHTML = customerData.Id + ' ' + customerData.Name;
            document.getElementById("walletBalance").innerHTML = currentMoney + ' ' + customerData.Currency;
            
            sessionStorage.setItem('customerData', JSON.stringify(customerData));
            sessionStorage.setItem('lineId', $('#lineId').val());
        }

        async function getCustomerGameAccounts(Sid) {
            try {
                // 檢查快取
                if (cache.gameAccounts[Sid]) {
                    processGameAccounts(cache.gameAccounts[Sid]);
                    return;
                }

                const response = await axios.get('getGameAccount.php?Sid=' + Sid);
                const accountData = response.data;
                
                // 儲存到快取
                cache.gameAccounts[Sid] = accountData;
                
                processGameAccounts(accountData);
            } catch (error) {
                console.error('取得遊戲帳號失敗:', error);
            }
        }

        function processGameAccounts(accountData) {
            if (accountData.length === 0) {
                alert('您還沒建立遊戲資料\n請點確定後將LINE ID複製給小編\n請洽小編建立資料');
                return;
            }

            let uniqueGames = {};
            let filteredData = accountData.filter(item => {
                if (!uniqueGames[item.GameSid]) {
                    uniqueGames[item.GameSid] = true;
                    return true;
                }
                return false;
            });

            sessionStorage.setItem('customerGameAccounts', JSON.stringify(accountData));
            sessionStorage.setItem('customerGameNames', JSON.stringify(filteredData));
            
            getCustomerGameLists();
        }

        async function getCustomerGameLists() {
            try {
                const customerGameAccounts = JSON.parse(sessionStorage.getItem('customerGameNames'));
                
                // 檢查快取
                if (!cache.gameList) {
                    const [switchGameListsData, response] = await Promise.all([
                        switchGameLists(),
                        axios.get('../getGameList.php')
                    ]);
                    
                    cache.gameList = {
                        all: response.data,
                        filtered: filterGames(response.data, switchGameListsData)
                    };
                }

                updateGameNameDropdown(customerGameAccounts, cache.gameList.filtered);
            } catch (error) {
                console.error('取得遊戲清單失敗:', error);
            }
        }

        function updateGameNameDropdown(customerGameAccounts, filterGameLists) {
            const searchGameBySid = (Sid) => filterGameLists.find(game => game.Sid === Sid);
            
            let options = '<option value="">請選擇遊戲</option>';
            customerGameAccounts.forEach(item => {
                const gameData = searchGameBySid(parseInt(item.GameSid));
                if (gameData) {
                    options += `<option value="${gameData.Sid}" data-gameRate="${gameData.GameRate}">${gameData.Name}</option>`;
                }
            });
            
            document.getElementById("gameName").innerHTML = options;
        }

        function gameNameOnchange() {
            getGameAccounts();
            getGameItems();
        }

        function getGameAccounts() {
            const selectedGame = document.getElementById("gameName").value;
            const customerGameAccounts = JSON.parse(sessionStorage.getItem('customerGameAccounts'));
            
            updateGameAccountsDropdown(selectedGame, customerGameAccounts);
            removeElementsByClass("dropdownDiv");
        }

        function updateGameAccountsDropdown(selectedGame, accounts) {
            let options = '<option value="">請選擇帳號</option>';
            accounts.forEach(item => {
                if (item.GameSid === selectedGame) {
                    options += `<option 
                        data-login_account="${item.LoginAccount}" 
                        data-login_password="${item.LoginPassword}" 
                        data-login_type="${item.LoginType}" 
                        data-characters="${item.Characters}" 
                        data-server_name="${item.ServerName}" 
                        data-login_account_Sid="${item.Sid}" 
                        value="${item.LoginAccount}">${item.LoginAccount}　${item.Characters}</option>`;
                }
            });
            
            document.getElementById("gameAccount").innerHTML = options;
        }

        async function getGameItems() {
            const selectedGame = document.getElementById("gameName").value;
            const gameItemDropdown = document.getElementById("gameItem");

            gameItemDropdown.innerHTML = '<option value="">請選擇...</option>';
            removeElementsByClass("dropdownDiv");

            try {
                // 檢查快取
                if (!cache.gameItems[selectedGame]) {
                    const response = await axios.get('getGameItem.php?Sid=' + selectedGame);
                    cache.gameItems[selectedGame] = response.data;
                }

                let gameItems = cache.gameItems[selectedGame];
                gameItems = removeAfterOnderLineWords(gameItems);

                const hkdFlag = checkHkdCurrencyAndHkdGameItems(gameItems);
                if (hkdFlag) {
                    gameItems = returnHkdGameItems(gameItems);
                }

                updateGameItemsDropdown(gameItems);
                sessionStorage.setItem('gemeItems', JSON.stringify(gameItems));
            } catch (error) {
                console.error('取得遊戲商品失敗:', error);
                gameItemDropdown.innerHTML = '<option value="">無法取得商品資料</option>';
            }
        }

        function updateGameItemsDropdown(gameItems) {
            let options = '<option value="-1">請選擇遊戲商品</option>';
            gameItems.forEach(item => {
                if (item.Enable === 1) {
                    options += `<option value="${item.Sid}" data-bouns="${item.Bonus}">${item.Name}</option>`;
                }
            });
            document.getElementById("gameItem").innerHTML = options;
        }

        function addGameItem() {
            const dropdownDiv = document.createElement("div");
            dropdownDiv.classList.add("dropdownDiv", "d-flex", "align-items-center");

            const count = createCountInput();
            const deleteButton = createDeleteButton(dropdownDiv);
            const newGameItem = createGameItemSelect();

            dropdownDiv.appendChild(newGameItem);
            dropdownDiv.appendChild(count);
            dropdownDiv.appendChild(deleteButton);
            dropdownDiv.appendChild(document.createElement("br"));
            dropdownDiv.appendChild(document.createElement("br"));

            document.getElementById("gameItemsGroup").appendChild(dropdownDiv);
        }

        function createCountInput() {
            const count = document.createElement("input");
            count.setAttribute("type", "number");
            count.setAttribute("value", "1");
            count.setAttribute("style", "max-width: 70px;");
            gameItemDropdown.innerHTML = '<option value="">請選擇...</option>';

            // 清空新增的下拉選單
            removeElementsByClass("dropdownDiv");

            // 使用axios進行後端請求
            axios.get('getGameItem.php?Sid=' + selectedGame)
                .then(function (response) {
                    // 從回傳的資料中生成商品下拉選單選項
                    let gameItems = response.data;

                    // 去掉商品底線後面的字
                    gameItems = removeAfterOnderLineWords(gameItems);

                    //確認港幣客人只能顯示港幣商品
                    const hkdFlag = checkHkdCurrencyAndHkdGameItems(gameItems);

                    //回傳專用的港幣商品
                    if (hkdFlag === true) {
                        gameItems = returnHkdGameItems(gameItems);
                    }

                    let options = '<option value="-1">請選擇遊戲商品</option>';
                    $.each(gameItems, function (i, item) {
                        if (item.Enable === 1) {
                            options +=
                                `<option value="${item.Sid}" data-bouns="${item.Bonus}">${item.Name}</option>`;
                        }
                    });

                    // 存這個遊戲的遊戲商品到sessionStorage
                    sessionStorage.setItem('gemeItems', JSON.stringify(gameItems));
                    gameItemDropdown.innerHTML = options;
                })
                .catch(function (error) {
                    console.error('Error fetching game items:', error);
                    gameItemDropdown.innerHTML = '<option value="">無法取得商品資料</option>';
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

        // function addGameItem() {

        //     const selectedGame = document.getElementById("gameName").value;

        //     const dropdownDiv = document.createElement("div");
        //     dropdownDiv.classList.add("dropdownDiv", "d-flex", "align-items-center");

        //     const count = document.createElement("input");
        //     count.setAttribute("type", "number");
        //     count.setAttribute("value", "1");
        //     count.setAttribute("style", "max-width: 70px;");
        //     count.classList.add("form-control", "mr-2", "gameItemCount");

        //     const deleteButton = document.createElement("button");
        //     deleteButton.setAttribute("type", "button");
        //     deleteButton.classList.add("btn", "btn-danger", "delete-btn");
        //     deleteButton.innerText = "X";
        //     deleteButton.onclick = function () {
        //         deleteDropdown(dropdownDiv);
        //     };

        //     const newGameItem = document.createElement("select");
        //     newGameItem.classList.add("form-control", "mr-2", "gameItems");

        //     axios.get('getGameItem.php?Sid=' + selectedGame)
        //         .then(function (response) {
        //             // 從回傳的資料中生成商品下拉選單選項
        //             let gameItems = response.data;

        //             // 去掉商品底線後面的字
        //             gameItems = removeAfterOnderLineWords(gameItems);

        //             //確認港幣客人只能顯示港幣商品
        //             const hkdFlag = checkHkdCurrencyAndHkdGameItems(gameItems);

        //             //回傳專用的港幣商品
        //             if (hkdFlag === true) {
        //                 gameItems = returnHkdGameItems(gameItems);
        //             }

        //             let options = '<option value="-1">請選擇遊戲商品</option>';
        //             $.each(gameItems, function (i, item) {
        //                 //options += `<option value="${item.Sid}" data-bouns="${item.Bonus}">${item.Name}</option>`;
        //                 if (item.Enable === 1) {
        //                     options +=
        //                         `<option value="${item.Sid}" data-bouns="${item.Bonus}">${item.Name}</option>`;
        //                 }
        //             });
        //             newGameItem.innerHTML = options;
        //         })
        //         .catch(function (error) {
        //             console.error('Error fetching game items:', error);
        //             newGameItem.innerHTML = '<option value="">無法取得商品資料</option>';
        //         });

        //     //123
        //     dropdownDiv.appendChild(newGameItem);
        //     dropdownDiv.appendChild(count);
        //     dropdownDiv.appendChild(deleteButton);
        //     dropdownDiv.appendChild(document.createElement("br"));
        //     dropdownDiv.appendChild(document.createElement("br"));

        //     document.getElementById("gameItemsGroup").appendChild(dropdownDiv);
        // }

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
            // 獲取當前時間
            var now = new Date();
            var dayOfWeek = now.getDay(); // 取得星期幾 (0 是星期天, 1 是星期一, 2 是星期二...)
            var hour = now.getHours(); // 取得小時

            // 判斷是否是星期二 且 時間在 7:00 到 8:00 之間
            if (dayOfWeek === 2 && hour === 7) {
                // 返回 true 表示維護時間內
                return true;
            }

            // 返回 false 表示不在維護時間內
            return false;
        }
    </script>
</body>

</html>