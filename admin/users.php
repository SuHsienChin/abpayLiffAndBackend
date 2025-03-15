<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../databaseConnection.php';
$dbConnection = new DatabaseConnection();
$conn = $dbConnection->connect();

// 獲取所有使用者
$stmt = $conn->prepare("SELECT u.*, r.role_name 
                       FROM admin_users u 
                       LEFT JOIN admin_roles r ON u.role_id = r.id");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 獲取所有角色
$stmt = $conn->prepare("SELECT * FROM admin_roles");
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ABPay 後台管理系統 - 使用者管理</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
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
        <a href="dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light">ABPay 後台管理</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>儀表板</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link active">
                            <i class="nav-icon fas fa-users"></i>
                            <p>使用者管理</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="orders.php" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>訂單管理</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="games.php" class="nav-link">
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
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>使用者管理</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                                    <i class="fas fa-plus"></i> 新增使用者
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="usersTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>使用者名稱</th>
                                            <th>角色</th>
                                            <th>狀態</th>
                                            <th>最後登入</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo $user['status'] === 'active' ? '啟用' : '停用'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : '從未登入'; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" onclick="editUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- 新增使用者 Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">新增使用者</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addUserForm" action="user_actions.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="username">使用者名稱</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">密碼</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">角色</label>
                        <select class="form-control" id="role" name="role_id" required>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">狀態</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active">啟用</option>
                            <option value="inactive">停用</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">新增</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 編輯使用者 Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">編輯使用者</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editUserForm" action="user_actions.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="form-group">
                        <label for="edit_username">使用者名稱</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_password">密碼 (留空表示不修改)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="edit_role">角色</label>
                        <select class="form-control" id="edit_role" name="role_id" required>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">狀態</label>
                        <select class="form-control" id="edit_status" name="status">
                            <option value="active">啟用</option>
                            <option value="inactive">停用</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">儲存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        "responsive": true,
        "autoWidth": false
    });
});

function editUser(userId) {
    // 通過 AJAX 獲取使用者資料
    $.get('user_actions.php', { action: 'get', user_id: userId }, function(response) {
        const user = JSON.parse(response);
        $('#edit_user_id').val(user.id);
        $('#edit_username').val(user.username);
        $('#edit_role').val(user.role_id);
        $('#edit_status').val(user.status);
        $('#editUserModal').modal('show');
    });
}

function deleteUser(userId) {
    if (confirm('確定要刪除這個使用者嗎？')) {
        $.post('user_actions.php', { action: 'delete', user_id: userId }, function(response) {
            location.reload();
        });
    }
}
</script>
</body>
</html>