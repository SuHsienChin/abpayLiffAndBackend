<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>遊戲下單</title>
    <!-- 引入 Bootstrap 的 CSS 檔案 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="js/userActionLogger.js"></script>
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
                                <input id="lineId" value="" readonly></input>
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
                            <div class="form-group">
                                <label for="gameItem">禮包名稱(並請提供截圖於對話內)</label>
                                <div class="d-flex align-items-center">
                                    <textarea id="gameRemark" name="gameRemark" rows="3" cols="50"></textarea>
                                </div>
                                <br />
                            </div>
                            <div class="form-group">
                                <h5 class="text-danger">無餘額：<br />下單成功➡️等候小編提供收款帳戶➡️收到款項後安排儲值</h5>
                                <h5 class="text-danger">有餘額：<br />下單成功➡️直接安排儲值</h5>
                                <br />
                                <h5 class="text-danger">❤️兌換介紹金請洽小編❤️</h5>
                            </div>

                            <button type="button" class="btn btn-primary btn-block mb-3"
                                onclick="addGameItem()">新增遊戲商品</button>
                            <button type="button" class="btn btn-success btn-block"
                                onclick="confirmOrder()">查看價格</button>
                            <button type="button" class="btn btn-secondary btn-block mt-2">取消</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 引入 Bootstrap 的 JavaScript 檔案（注意順序：先引入 jQuery，再引入 Bootstrap 的 JS） -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- 以下是liff 要上線時需打開 -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script>



        const loadingModal = new bootstrap.Modal(document.getElementById('loading'), {
            backdrop: 'static',
            keyboard: false
        });

        // 顯示 Loading 動畫
        loadingModal.show();

        $(function () {
            //使用 LIFF_ID 初始化 LIFF 應用
            initializeLiff('2000183731-BLmrAGPp');
            sessionStorage.clear();
        });



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

            /*
            判斷當下時間是不是在星期二的早上7點整到8點整之間，是的話就alert顯示：<h2>系統正在維護，維護時間為早上7點到8點之間</h2>，並且關閉LIFF
           */
            // 使用函數並處理結果
            if (isMaintenanceTime()) {
                // 顯示提示訊息
                alert('系統正在維護，維護時間為早上7點到8點之間');

                // 檢查並關閉 LIFF 視窗
                if (typeof liff !== 'undefined' && liff.closeWindow) {
                    liff.closeWindow();
                } else {
                    console.log("LIFF 未初始化或不支援關閉功能");
                }
            } else {
                console.log("系統未處於維護時間");
            }

            liff.getProfile()
                .then(profile => {
                    console.log(profile.displayName);
                    console.log(profile.userId);
                    console.log(profile.statusMessage);
                    console.log(profile);

                    sessionStorage.setItem('lineUserId', profile.userId);
                    const mylineId = $("#lineId").val(sessionStorage.getItem('lineUserId'));
                    customerBtn(profile.userId);

                })
                .catch((err) => {
                    console.log('error', err);
                });

        }

        //一開始進來清除所有暫存資料
        //sessionStorage.clear();


        
        // 在各個函數中加入記錄
        function customerBtn(mylineId) {
            logUserAction('order', '進入訂單頁面', { lineId: mylineId });
            // const mylineId = $("#lineId").val();
            if(mylineId === undefined){
                alert('系統檢測到無效參數，請重新整理頁面或聯繫客服');
                return;
            }
            //取得客人資料
            axios.get('getCustomer.php?lineId=' + mylineId)
                .then(function (response) {
                    const customerData = response.data;
                    const customer = document.getElementById("customerData");
                    const walletBalance = document.getElementById("walletBalance");
                    const lineId = $('#lineId').val();
                    let currentMoney = 0;
                    //預防餘額還沒有設定的客人會出現undefined
                    if (typeof customerData.CurrentMoney === 'undefined') {
                        currentMoney = 0;
                    } else {
                        currentMoney = customerData.CurrentMoney;
                    }
                    customer.innerHTML = customerData.Id + ' ' + customerData.Name;
                    walletBalance.innerHTML = currentMoney + ' ' + customerData.Currency;
                    sessionStorage.setItem('customerData', JSON.stringify(response.data));
                    sessionStorage.setItem('lineId', lineId);

                    //取得客人所有的遊戲帳號
                    getCustomerGameAccounts(customerData.Sid);

                    //轉圈圈Loading隱藏
                    loadingModal.hide();

                    // if(currentMoney <= 0){
                    //     alert('要自動下單\n請先至官方LINE\n找小編儲值錢包唷');
                    //     $('.btn').hide();
                    // }
                })
                .catch((error) => console.log(error))
        }

        //取得客人所有的遊戲帳號
        function getCustomerGameAccounts(Sid) {
            if(Sid === undefined){
                alert('系統檢測到無效參數，請重新整理頁面或聯繫客服');
                return;
            }
            axios.get('getGameAccount.php?Sid=' + Sid)
                .then(function (response) {
                    const accountData = response.data;
                    let uniqueGames = {};
                    let filteredData = [];
                    response.data.forEach(item => {
                        // 如果這個遊戲 ID 還沒出現過，就將它加入 filteredData 並標記為已經出現過
                        if (!uniqueGames[item.GameSid]) {
                            uniqueGames[item.GameSid] = true;
                            filteredData.push(item);
                        }
                    });
                    console.log(filteredData);
                    //沒有遊戲資料的客人 要請小編建立
                    if (response.data.length === 0) {
                        alert('您還沒建立遊戲資料\n請點確定後將LINE ID複製給小編\n請洽小編建立資料');
                    } else {
                        sessionStorage.setItem('customerGameAccounts', JSON.stringify(response.data));
                        sessionStorage.setItem('customerGameNames', JSON.stringify(filteredData));
                        getCustomerGameLists();
                    }

                })
                .catch((error) => console.log(error))
        }

        //取得客人所屬的遊戲
        function getCustomerGameLists() {
            const customerGameAccounts = JSON.parse(sessionStorage.getItem('customerGameNames'));

            // const showGameLists = switchGameLists();
            // console.log('顯示遊戲的開關');
            // console.log(showGameLists);

            switchGameLists()
                .then(function (switchGameListsData) {
                    axios.get('../getGameList.php')
                        .then(function (response) {
                            const allGameLists = response.data;
                            const filterGameLists = filterGames(response.data, switchGameListsData);

                            const searchGameBySid = (Sid) => {
                                return filterGameLists.find(filterGameList => filterGameList.Sid === Sid);
                            };

                            let options = '<option value="">請選擇遊戲</option>';
                            $.each(customerGameAccounts, function (i, item) {
                                const gameData = searchGameBySid(parseInt(item.GameSid));
                                console.log(gameData);
                                if (gameData != undefined) {
                                    const selectedGame = document.getElementById("gameName")
                                    options +=
                                        `<option value="${gameData.Sid}" data-gameRate="${gameData.GameRate}">${gameData.Name}</option>`;
                                    selectedGame.innerHTML = options;
                                }
                            });


                            // let options = '<option value="">請選擇遊戲</option>';
                            // $.each(filterGameLists, function(i, item) {
                            //     const selectedGame = document.getElementById("gameName")
                            //     options +=
                            //         `<option value="${item.Sid}" data-gameRate="${item.GameRate}">${item.Name}</option>`;
                            //     selectedGame.innerHTML = options;
                            // });


                            //以下是原本還沒設定遊戲開關的程式碼
                            // const searchGameBySid = (Sid) => {
                            //     return allGameLists.find(allGameList => allGameList.Sid === Sid);
                            // };

                            // let options = '<option value="">請選擇遊戲</option>';
                            // $.each(customerGameAccounts, function(i, item) {
                            //     const gameData = searchGameBySid(parseInt(item.GameSid));

                            //     const selectedGame = document.getElementById("gameName")
                            //     options +=
                            //         `<option value="${gameData.Sid}" data-gameRate="${gameData.GameRate}">${gameData.Name}</option>`;
                            //     selectedGame.innerHTML = options;
                            // });
                            //以上是原本還沒設定遊戲開關的程式碼

                        })
                        .catch((error) => console.log(error))
                })
                .catch(function (error) {
                    console.error(error);
                });

        }

        // 遊戲名稱選項變更時執行的function
        function gameNameOnchange() {

        logUserAction('order', '選擇遊戲', {
            gameName: $('#gameName option:selected').text(),
            gameValue: $('#gameName').val()
        });
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

        //取得遊戲下的所有商品
        function getGameItems() {
            const selectedGame = document.getElementById("gameName").value;
            const gameItemDropdown = document.getElementById("gameItem");


            // 清空商品下拉選單
            gameItemDropdown.innerHTML = '<option value="">請選擇...</option>';

            // 清空新增的下拉選單
            removeElementsByClass("dropdownDiv");

            // 使用axios進行後端請求
            if(selectedGame === undefined){
                alert('系統檢測到無效參數，請重新整理頁面或聯繫客服');
                return;
            }
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
                        //options += `<option value="${item.Sid}" data-bouns="${item.Bonus}">${item.Name}</option>`;
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
            logUserAction('order', '新增遊戲商品');
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

            if(selectedGame === undefined){
                alert('系統檢測到無效參數，請重新整理頁面或聯繫客服');
                return;
            }
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
                        //options += `<option value="${item.Sid}" data-bouns="${item.Bonus}">${item.Name}</option>`;
                        if (item.Enable === 1) {
                            options +=
                                `<option value="${item.Sid}" data-bouns="${item.Bonus}">${item.Name}</option>`;
                        }
                    });
                    newGameItem.innerHTML = options;
                })
                .catch(function (error) {
                    console.error('Error fetching game items:', error);
                    newGameItem.innerHTML = '<option value="">無法取得商品資料</option>';
                });

            //123
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
            if(selectedGame === undefined){
                alert('系統檢測到無效參數，請重新整理頁面或聯繫客服');
                return;
            }
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
                    console.log(gameItemCount.value);
                    if (parseInt(gameItemCount.value) > 0 && parseInt(gameItemCount.value) !== 0) {
                        gameItemCountCount++;
                    }
                    console.log(gameItemCountCount);
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

            console.log(customerCurrency);

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
            if (dayOfWeek === 2 && hour >= 7 && hour < 8) {
                // 返回 true 表示維護時間內
                return true;
            }

            // 返回 false 表示不在維護時間內
            return false;
        }
    </script>
</body>

</html>