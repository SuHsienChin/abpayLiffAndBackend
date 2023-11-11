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
                    <div class="card-body">
                        <form method="post" action="order_check.php">
                            <div class="form-group">
                                <label>輸入LineId：</label>
                                <input id="lineId" value="@A03"></input>
                                <button type="button" class="btn btn-primary" onclick="customerBtn()">確定</button>
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
    <script src="/js/liff.js"></script>
    <script>
        var liffID = '2000183731-BLmrAGPp';

        liff.init({
            liffId: liffID
        }).then(function () {
            console.log('LIFF init');
            alert('init');

            // 這邊開始寫使用其他功能
            // 引用 LIFF SDK 的頁面，頁面中的 lang 值
            liff.getLanguage();

            // LIFF SDK 的版本
            liff.getVersion();

            // 回傳是否由 LINE App 存取
            liff.isInClient();

            // 使用者是否登入 LINE 帳號
            liff.isLoggedIn();

            // 回傳使用者作業系統：ios、android、web
            liff.getOS();

            // 使用者的 LINE 版本
            liff.getLineVersion();

        }).catch(function (error) {
            console.log(error);
        });

        // 取得使用者類型資料
        var context = liff.getContext();
        console.log(context);

        // 取得使用者公開資料
        // 後台的「Scopes」要設定開啟 profile, openid
        liff.getProfile()
            .then(function (profile) {
                console.log(profile);
                alert(profile);
            });

        // 取得使用者 email
        // 後台的 Email address permission 要是「Applied」
        // LIFF 的設定，Scopes 的「email*」要打勾
        // 使用者在登入時，「電子郵件帳號」也要是「許可」的
        var user = liff.getDecodedIDToken();
        var email = user.email;
        console.log(email);
    </script>
</body>

</html>