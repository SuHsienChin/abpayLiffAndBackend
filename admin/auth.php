<?php
session_start();
require_once '../databaseConnection.php';
require_once 'login_logger.php';

$dbConnection = new DatabaseConnection();
$conn = $dbConnection->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 記錄登入嘗試到資料庫
    logLoginAttempt($username, 'failed', "登入嘗試");

    // 防止 SQL 注入
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 記錄用戶查詢結果到資料庫
    $userFound = $user ? "User found" : "User not found";
    logLoginAttempt($username, 'failed', "查詢結果: " . $userFound);
    
    if ($user) {
        $passwordVerified = password_verify($password, $user['password']) ? "Success" : "Failed";
        logLoginAttempt($username, $passwordVerified == "Success" ? 'success' : 'failed', "密碼驗證結果: " . $passwordVerified);
    }

    if ($user && password_verify($password, $user['password'])) {
        // 登入成功，更新用戶的最後登入時間
        $updateStmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = :id");
        $updateStmt->execute([':id' => $user['id']]);
        
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        
        // 記錄成功登入
        logLoginAttempt($username, 'success', "登入成功");
        
        header('Location: dashboard.php');
        exit;
    } else {
        header('Location: index.php?error=1');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}