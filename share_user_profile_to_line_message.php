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
                        <h3>領取介紹金</h3>
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
        $(function () {
            //使用 LIFF_ID 初始化 LIFF 應用
            initializeLiff('2000183731-pAvjl2Zg');

            sessionStorage.clear();
        });

        function initializeLiff(myLiffId) {
            liff
                .init({
                    liffId: myLiffId,
                    withLoginOnExternalBrowser: true, // Enable automatic login process
                })
                .then(() => {
                    console.log('啟動成功');
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
                    customerBtn(profile.userId);

                })
                .catch((err) => {
                    console.log('error', err);
                    alert('app錯誤' + err);
                });
        }


        function customerBtn(mylineId) {

            try {
                //取得客人資料
                axios.get('getCustomer.php?lineId=' + mylineId)
                    .then(function (response) {
                        const customerData = response.data;

                        var msgTxt = '💖【' + response.data.Id.split('-')[1] + '】是您的客編\n';
                        msgTxt += '🎊當介紹朋友來艾比代🎊\n';
                        msgTxt += '請對方下單前與成功儲值後\n';
                        msgTxt += '跟小編報上您的客編\n';
                        msgTxt += '您就能有介紹金100元喔🧧🧧\n';
                        msgTxt += '介紹無上限❤️累積獎金無上限🎆\n';
                        msgTxt += '艾比代儲ID：@abpay\n';
                        msgTxt += 'https://lin.ee/2xDro0B\n';

                        liff.sendMessages([{
                            type: 'text',
                            text: msgTxt
                        }]).then(function () {
                            console.log('訊息已成功發送');
                            alert('請複製這段訊息給您的朋友');
                            liff.closeWindow();
                        }).catch(function (error) {
                            console.log('無法發送訊息: ' + error);
                            alert('無法發送訊息: ' + error);
                            liff.closeWindow();
                        });


                    })
                    .catch((error) => {
                        console.log(error);
                        alert('請先成為會員');
                        liff.closeWindow();
                    });

            } catch (error) {
                console.log(error);
                alert('請先成為會員');
                liff.closeWindow();

            }



        }
    </script>
</body>

</html>