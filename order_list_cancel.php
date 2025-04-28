<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <title>後台訂單管理</title>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <?php
                        require_once 'menu_temp.php';
                        ?>
                        <!-- 其他菜單項目 -->
                    </ul>
                </div>
            </nav>

            <!-- 主要內容 -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">訂單管理-已完成</h1>
                </div>

                <!-- 訂單表格 -->
                <div class="table-responsive">
                    <table id="myTable" class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>客人編號</th>
                                <th>訂單編號</th>
                                <th>遊戲名稱</th>
                                <th>遊戲帳號</th>
                                <th>角色名稱</th>
                                <th>訂單狀態</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once 'order_data.php';
                            $orders = new Orders();
                            $orders->getCancelData();
                            ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal for Order Details -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" role="dialog" aria-labelledby="orderDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailModalLabel">訂單詳細內容</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="orderDetailContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https:////cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
</body>
<script>

    $(document).ready(function () {

        $('.order-detail-link').click(function (event) {
            event.preventDefault();
            var orderId = $(this).data('order-id');

            // 在這裡，您可以使用 AJAX 請求來獲取訂單的詳細內容，然後將內容填充到 modal 裡面。
            // 以下是一個簡單示例：

            axios.get('order_details.php?orderId=' + orderId)
                .then(function (response) {

                    $('#orderDetailContent').html(response.data.orderDetails);
                    $('#orderDetailModal').modal('show');
                })
                .catch((error) => console.log(error))
        });

        $('.statusButton').click(function () {
            const orderId = $(this).attr("name");
            const status = $(this).val();
            axios.get('order_update.php?orderId=' + orderId + '&status=' + status)
                .then(function (response) {

                    alert(response.data.message);
                })
                .catch((error) => console.log(error))

        })

        let table = new DataTable('#myTable');
    });

</script>

</html>