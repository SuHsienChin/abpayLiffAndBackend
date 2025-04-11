<?php


require_once '../databaseConnection.php';
$dbConnection = new DatabaseConnection();
$conn = $dbConnection->connect();

$error = '';
$success = '';

// 處理密碼修改請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // 驗證輸入
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = '所有欄位都必須填寫';
    } elseif ($new_password !== $confirm_password) {
        $error = '新密碼與確認密碼不符';
    } elseif (strlen($new_password) < 8) {
        $error = '新密碼長度必須至少為8個字符';
    } else {
        // 驗證當前密碼
        $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $_SESSION['admin_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($current_password, $user['password'])) {
            $error = '當前密碼不正確';
        } else {
            // 更新密碼
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE admin_users SET password = :password WHERE id = :id");
            $result = $updateStmt->execute([
                ':password' => $hashed_password,
                ':id' => $_SESSION['admin_id']
            ]);
            
            if ($result) {
                $success = '密碼已成功更新';
                
                // 記錄管理員操作
                try {
                    $logStmt = $conn->prepare("INSERT INTO admin_action_logs (admin_id, action, description, ip_address) VALUES (:admin_id, :action, :description, :ip_address)");
                    $logStmt->execute([
                        ':admin_id' => $_SESSION['admin_id'],
                        ':action' => 'change_password',
                        ':description' => "管理員修改了自己的密碼",
                        ':ip_address' => $_SERVER['REMOTE_ADDR']
                    ]);
                } catch (PDOException $e) {
                    error_log("Failed to log admin action: " . $e->getMessage());
                }
            } else {
                $error = '密碼更新失敗，請稍後再試';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ABPay 後台管理系統 - 修改密碼</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/dist/css/adminlte.min.css?v=3.2.0">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
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
                        <a href="games.php" class="nav-link">
                            <i class="nav-icon fas fa-gamepad"></i>
                            <p>遊戲管理</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="strategy_order.php" class="nav-link">
                            <i class="nav-icon fas fa-file-upload"></i>
                            <p>戰略自動發單</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="change_password.php" class="nav-link active">
                            <i class="nav-icon fas fa-key"></i>
                            <p>修改密碼</p>
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
                        <h1>修改密碼</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">修改您的管理員密碼</h3>
                            </div>
                            <?php if ($error): ?>
                            <div class="alert alert-danger m-3">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                            <div class="alert alert-success m-3">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                            <?php endif; ?>
                            
                            <form method="post" action="">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="current_password">當前密碼</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="new_password">新密碼</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <small class="text-muted">密碼長度必須至少為8個字符</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password">確認新密碼</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">更新密碼</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- jQuery -->
<script src="https://adminlte.io/themes/v3/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://adminlte.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://adminlte.io/themes/v3/dist/js/adminlte.min.js"></script>
</body>
</html>