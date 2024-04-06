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
            
                <div class="card">
                    <div class="card-header text-center">
                        <h3>遊戲下單</h3>
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
                                    <option value="">請稍候...</option>
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
                    console.log(profile.displayName);
                    console.log(profile.userId);
                    console.log(profile.statusMessage);
                    console.log(profile);

                    sessionStorage.setItem('lineUserId', profile.userId);
                    const mylineId = $("#lineId").val(sessionStorage.getItem('lineUserId'));
                })
                .catch((err) => {
                    console.log('error', err);
                });

            liff.sendMessages([
                {
                    type: 'text',
                    text: 'Hello, World!'
                }
            ])
                .then(() => {
                    console.log('message sent');
                })
                .catch((err) => {
                    console.log('error', err);
                });

        }

        //使用 LIFF_ID 初始化 LIFF 應用
        initializeLiff('2000183731-BLmrAGPp');


        //一開始進來清除所有暫存資料
        //sessionStorage.clear();


    </script>
</body>

</html>