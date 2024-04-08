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
                        <h3>111</h3>
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
        // 假設要新增的資料
        const logData = {
            type: 'error',
            JSON: '{"message": "This is an error log"}'
        };

        try {
            axios.get('sendOrderUrlByCORS.php?' + UrlParametersString)
                .then(function(response) {
                    const resdata = response.data
                    let orderId = '';
                    console.log(resdata);
                    console.log(resdata.Status);
                    if (resdata.Status == '1') {
                        orderId = resdata.OrderId;
                        params.append('orderId', orderId);
                        insertOrderData(params);

                        alert('下單成功');

                        //sessionStorage.clear();
                        window.location = "finishOrder.php?orderId=" + orderId;

                    } else {
                        alert('下單發生錯誤，請洽小編');
                    }
                })
                .catch(function(error) {
                    console.error('Error fetching :', error);
                });
        } catch (e) {
            alert('API下單錯誤，請洽小編\n' + e);
        }
    </script>
    <script>

    </script>
</body>

</html>