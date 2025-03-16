<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ABPay 後台管理系統 - 遊戲管理</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/fontawesome-free/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/dist/css/adminlte.min.css?v=3.2.0">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> 登出
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light">ABPay 後台管理</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>儀表板</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="orders.php" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>訂單管理</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>使用者管理</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="games.php" class="nav-link active">
                            <i class="nav-icon fas fa-gamepad"></i>
                            <p>遊戲管理</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">遊戲管理</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">遊戲列表</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-game-modal">
                                        <i class="fas fa-plus"></i> 新增遊戲
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="games-table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>遊戲ID</th>
                                            <th>遊戲名稱</th>
                                            <th>狀態</th>
                                            <th>更新時間</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; 2024 ABPay</strong>
        All rights reserved.
    </footer>
</div>

<!-- Add Game Modal -->
<div class="modal fade" id="add-game-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">新增遊戲</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="add-game-form">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="game-name">遊戲名稱</label>
                        <input type="text" class="form-control" id="game-name" name="game_name" required>
                    </div>
                    <div class="form-group">
                        <label for="game-status">狀態</label>
                        <select class="form-control" id="game-status" name="status">
                            <option value="1">啟用</option>
                            <option value="0">停用</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">確定</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Game Modal -->
<div class="modal fade" id="edit-game-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">編輯遊戲</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="edit-game-form">
                <input type="hidden" id="edit-game-id" name="game_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-game-name">遊戲名稱</label>
                        <input type="text" class="form-control" id="edit-game-name" name="game_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-game-status">狀態</label>
                        <select class="form-control" id="edit-game-status" name="status">
                            <option value="1">啟用</option>
                            <option value="0">停用</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">確定</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://adminlte.io/themes/v3/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://adminlte.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables & Plugins -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="https://adminlte.io/themes/v3/dist/js/adminlte.js?v=3.2.0"></script>
<!-- Page specific script -->
<script>
function editGame(Sid) {
    $.ajax({
        url: 'get_games.php',
        type: 'GET',
        data: { id: Sid },
        success: function(response) {
            $('#edit-game-id').val(response.Sid);
            $('#edit-game-name').val(response.Name);
            $('#edit-game-status').val(response.flag);
            $('#edit-game-modal').modal('show');
        },
        error: function(xhr, status, error) {
            alert('獲取遊戲資訊失敗：' + error);
        }
    });
}

function toggleGameStatus(gameId, newStatus) {
    $.ajax({
        url: 'update_game_status.php',
        type: 'POST',
        data: { game_id: gameId, status: newStatus },
        success: function(response) {
            if (response.success) {
                $('#games-table').DataTable().ajax.reload();
            } else {
                alert('更新遊戲狀態失敗：' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('更新遊戲狀態失敗：' + error);
        }
    });
}

$(document).ready(function() {

    $.fn.DataTable.ext.errMode = 'throw';

    $('#games-table').DataTable({
        "responsive": true,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": "get_games.php",
        "columns": [
            { "data": "Sid" },
            { "data": "Name" },
            { "data": "flag" },
            { "data": "UpdateTime" },
            { "data": "actions" }
        ],
        "drawCallback": function() {
            $('.edit-game-btn').on('click', function() {
                editGame($(this).data('Sid'));
            });
            
            // 綁定狀態切換按鈕點擊事件
            $('.toggle-status').on('click', function() {
                var gameId = $(this).data('Sid');
                var newStatus = $(this).data('flag');
                toggleGameStatus(gameId, newStatus);
            });
        }
    });

    
    $('#add-game-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'add_game.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#add-game-modal').modal('hide');
                $('#games-table').DataTable().ajax.reload();
            },
            error: function(xhr, status, error) {
                alert('新增遊戲失敗：' + error);
            }
        });
    });
    


    $('#edit-game-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'update_game.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#edit-game-modal').modal('hide');
                $('#games-table').DataTable().ajax.reload();
            },
            error: function(xhr, status, error) {
                alert('更新遊戲失敗：' + error);
            }
        });
    });
});
</script>
</body>
</html>