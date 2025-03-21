<?php
session_start();
require_once '../databaseConnection.php';

$dbConnection = new DatabaseConnection();
$conn = $dbConnection->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 記錄登入嘗試
    error_log("Login attempt for username: " . $username);

    // 防止 SQL 注入
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 記錄用戶查詢結果
    error_log("User query result: " . ($user ? "User found" : "User not found"));
    if ($user) {
        error_log("Password verification result: " . (password_verify($password, $user['password']) ? "Success" : "Failed"));
    }

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
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