<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>查詢訂單</title>
    <!-- 引入Bootstrap 4的CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">訂單列表</h2>
        <table class="table table-striped table-bordered table-responsive">
            <thead>
                <tr>
                    <th>訂單日期</th>
                    <th>訂單編號</th>
                    <th>訂單內容</th>
                    <th>訂單狀態</th>
                </tr>
            </thead>
            <tbody id="orderTableBody">

                <!-- 在這裡添加其他訂單行 -->
            </tbody>
        </table>
    </div>

    <!-- 引入 Bootstrap 的 JavaScript 檔案（注意順序：先引入 jQuery，再引入 Bootstrap 的 JS） -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
<!-- 引入Bootstrap 4的JS和jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"
    integrity="sha512-2rNj2KJ+D8s1ceNasTIex6z4HWyOnEYLVC3FigGOmyQCZc2eBXKgOxQmo3oKLHyfcj53uz4QMsRCWNbLd32Q1g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!-- 以下是liff 要上線時需打開 -->
<!-- <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script> -->

<script>
getOrderList();
    function getOrderList(lineId) {
        axios.get('get_order_list.php?lineId=U628aae282e484f49fb905ac0d17dd860')
            .then(function (response) {
                const orders = response.data;
                const tableBody = document.getElementById('orderTableBody');

                if (orders.length > 0) {
                    orders.forEach(function (order) {
                        var row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${order.created_at}</td>
                            <td>${order.orderId}</td>
                            <td>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#orderDetails${order.orderId}">查看詳細</button>
                                <!-- Modal -->
                                <div class="modal" id="orderDetails${order.orderId}" tabindex="-1" role="dialog" aria-labelledby="orderDetailsLabel${order.orderId}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="orderDetailsLabel${order.orderId}">訂單內容</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                            <fieldset class="border p-4">
                                                <legend class="w-auto">下單資料</legend>
                                                ➤下單日期：<label>${order.created_at}</label></br>
                                                ➤遊戲名稱：<label id='gameNameText'>${order.gameName}</label></br>
                                                ➤登入方式：<label id='loginType'>${order.logintype}</label></br>
                                                ➤遊戲帳號：<label id='gameAccount'>${order.acount}</label></br>
                                                ➤遊戲密碼：<label id='loginPassword'>${order.password}</label></br>
                                                ➤伺服器：<label id='serverName'>${order.serverName}</label></br>
                                                ➤角色名稱：<label id='characters'>${order.gameAccountName}</label></br>
                                                ➤ID編號：<label></label>${order.gameAccountId}</br>
                                                ➤下單商品確認：</br></br>
                                                <label id='gameItems'>${splitItemsAndCounts(JSON.parse(order.gameItemsName), order.gameItemCounts, order.itemsMoney)}</label></br></br>
                                                總計: <label id='sumMoney'>${order.sumMoney}</label> <label id='customerCurrency'></label>
                                            </fieldset>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>${order.status}</td>
                        `;

                        tableBody.appendChild(row);
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="4">沒有訂單</td></tr>';
                }
            })
            .catch((error) => console.log(error))
    }


    // function initializeLiff(myLiffId) {
    //     liff
    //         .init({
    //             liffId: myLiffId,
    //             withLoginOnExternalBrowser: true, // Enable automatic login process
    //         })
    //         .then(() => {
    //             initializeApp();
    //         })
    //         .catch((err) => {
    //             console.log(err);
    //             console.log('啟動失敗。');
    //         });
    // }

    // function initializeApp() {
    //     console.log('啟動成功。');
    //     liff.getProfile()
    //         .then(profile => {
    //             console.log(profile.displayName);
    //             console.log(profile.userId);
    //             console.log(profile.statusMessage);
    //             console.log(profile);
    //             sessionStorage.setItem('lineUserId', profile.userId);
    //             const lineId = $("#lineId").val(sessionStorage.getItem('lineUserId'));
    //             getOrderList(profile.userId);
    //         })
    //         .catch((err) => {
    //             console.log('error', err);
    //         });

    // }

    // //使用 LIFF_ID 初始化 LIFF 應用
    // initializeLiff('2000183731-nVr0b25p');

    function splitItemsAndCounts(items, counts, itemsMoney) {

        if (items.split(',').length > 1) {
            const myitems = items.split(',');
            const mycounts = counts.split(',');
            const myitemsMoney = itemsMoney.split(',');
            let str = '';
            for (let i = 0; i < myitems.length; i++) {
                str += myitems[i] + ' X ' + mycounts[i] + ' = ' + myitemsMoney[i] + '<br />';
            }

            return str;
        } else {
            return items + ' X ' + counts + ' = ' + itemsMoney;
        }

    }
</script>

</html>