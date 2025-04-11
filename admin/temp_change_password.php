<?php
require_once '../databaseConnection.php';
$dbConnection = new DatabaseConnection();
$conn = $dbConnection->connect();

$error = '';
$success = '';
$users = [];

// 獲取所有管理員用戶
try {
    $stmt = $conn->prepare("SELECT id, username FROM admin_users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = '獲取用戶列表失敗: ' . $e->getMessage();
}

// 處理密碼修改請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // 驗證輸入
    if (empty($admin_id) || empty($new_password) || empty($confirm_password)) {
        $error = '所有欄位都必須填寫';
    } elseif ($new_password !== $confirm_password) {
        $error = '新密碼與確認密碼不符';
    } elseif (strlen($new_password) < 8) {
        $error = '新密碼長度必須至少為8個字符';
    } else {
        // 更新密碼
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE admin_users SET password = :password WHERE id = :id");
        $result = $updateStmt->execute([
            ':password' => $hashed_password,
            ':id' => $admin_id
        ]);
        
        if ($result) {
            $success = '密碼已成功更新';
            
            // 記錄管理員操作
            try {
                $logStmt = $conn->prepare("INSERT INTO admin_action_logs (admin_id, action, description, ip_address) VALUES (:admin_id, :action, :description, :ip_address)");
                $logStmt->execute([
                    ':admin_id' => $admin_id,
                    ':action' => 'temp_change_password',
                    ':description' => "臨時頁面修改了管理員密碼",
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
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ABPay 臨時密碼修改頁面</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/dist/css/adminlte.min.css?v=3.2.0">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <style>
        .warning-banner {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body class="hold-transition">
<div class="wrapper">
    <div class="content-wrapper" style="margin-left: 0;">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>臨時密碼修改頁面</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="warning-banner">
                    <i class="fas fa-exclamation-triangle"></i> 警告：此頁面為臨時使用，無需登入驗證。請在使用完畢後移除此頁面！
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">修改管理員密碼</h3>
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
                                        <label for="admin_id">選擇管理員</label>
                                        <select class="form-control" id="admin_id" name="admin_id" required>
                                            <option value="">-- 請選擇管理員 --</option>
                                            <?php foreach ($users as $user): ?>
                                            <option value="<?php echo htmlspecialchars($user['id']); ?>">
                                                <?php echo htmlspecialchars($user['username']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
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
                                    <button type="submit" class="btn btn-danger">更新密碼</button>
                                    <a href="index.php" class="btn btn-secondary ml-2">返回登入頁面</a>
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