<!-- resources/views/admin/users/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>訂單紀錄</h1>
    <form method="POST" action="{{ route('orders.updateStatus') }}">
        @csrf
        <table class="table table-striped" id="myTable">
            <thead>
                <tr>
                    <th></th>
                    <th>客編</th>
                    <th>訂單編號</th>
                    <th>遊戲名稱</th>
                    <th>訂單狀態</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>
                            <input type="checkbox" name="selected_orders[]" value="{{ $order->orderId }}">
                        </td>
                        <td>{{ $order->customerId }}</td>
                        <td>
                        <a href="#" class="order-detail-link" data-order-id="{{ $order->orderId }}">
                            {{ $order->orderId }}
                        </a>
                        </td>
                        <td>{{ $order->gameName }}</td>
                        <td>
                            <select name="order_status_{{ $order->orderId }}">
                                <option value="{{ $order->status }}" selected>{{ $order->status }}</option>
                                <option value="訂單處理中">訂單處理中</option>
                                <option value="已完成">已完成</option>
                                <option value="已取消">已取消</option>
                            </select>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">更新訂單狀態</button>
    </form>
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
<script src="https:////cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>

    $(document).ready(function () {
        $('.order-detail-link').click(function (event) {
            event.preventDefault();
            var orderId = $(this).data('order-id');

            // 在這裡，您可以使用 AJAX 請求來獲取訂單的詳細內容，然後將內容填充到 modal 裡面。
            // 以下是一個簡單示例：

            axios.get('/orders/get-order-details/' + orderId)
                .then(function (response) {

                    $('#orderDetailContent').html(response.data.orderDetails);
                    $('#orderDetailModal').modal('show');
                })
                .catch((error) => console.log(error))
        });

        let table = new DataTable('#myTable');
    });

</script>
@endsection