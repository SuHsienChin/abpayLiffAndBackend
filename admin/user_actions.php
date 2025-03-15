<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../databaseConnection.php';
$dbConnection = new DatabaseConnection();
$conn = $dbConnection->connect();
// 記錄管理員操作
function logAdminAction($admin_id, $action, $description) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO admin_action_logs (admin_id, action, description, ip_address) VALUES (:admin_id, :action, :description, :ip_address)");
        $stmt->execute([
            ':admin_id' => $admin_id,
            ':action' => $action,
            ':description' => $description,
            ':ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log admin action: " . $e->getMessage());
    }
}

function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function validateInput($data, $required = []) {
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            return ["error" => "缺少必要欄位：$field"];
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add':
                $validation = validateInput($_POST, ['username', 'password', 'role_id', 'status']);
                if ($validation) {
                    sendJsonResponse($validation, 400);
                }

                // 檢查用戶名是否已存在
                $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE username = :username");
                $stmt->execute([':username' => $_POST['username']]);
                if ($stmt->fetchColumn() > 0) {
                    sendJsonResponse(["error" => "用戶名已存在"], 400);
                }

                $username = trim($_POST['username']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role_id = (int)$_POST['role_id'];
                $status = (int)$_POST['status'];

                $stmt = $conn->prepare("INSERT INTO admin_users (username, password, role_id, status) VALUES (:username, :password, :role_id, :status)");
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $password,
                    ':role_id' => $role_id,
                    ':status' => $status
                ]);

                logAdminAction($_SESSION['admin_id'], 'create_user', "創建新使用者: $username");
                sendJsonResponse(["success" => true, "message" => "用戶創建成功"]);
                break;

            case 'edit':
                $validation = validateInput($_POST, ['user_id', 'username', 'role_id', 'status']);
                if ($validation) {
                    sendJsonResponse($validation, 400);
                }

                $user_id = (int)$_POST['user_id'];
                $username = trim($_POST['username']);
                $role_id = (int)$_POST['role_id'];
                $status = (int)$_POST['status'];

                // 檢查用戶名是否已被其他用戶使用
                $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE username = :username AND id != :id");
                $stmt->execute([':username' => $username, ':id' => $user_id]);
                if ($stmt->fetchColumn() > 0) {
                    sendJsonResponse(["error" => "用戶名已被使用"], 400);
                }

                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE admin_users SET username = :username, password = :password, role_id = :role_id, status = :status WHERE id = :id");
                    $params = [
                        ':username' => $username,
                        ':password' => $password,
                        ':role_id' => $role_id,
                        ':status' => $status,
                        ':id' => $user_id
                    ];
                } else {
                    $stmt = $conn->prepare("UPDATE admin_users SET username = :username, role_id = :role_id, status = :status WHERE id = :id");
                    $params = [
                        ':username' => $username,
                        ':role_id' => $role_id,
                        ':status' => $status,
                        ':id' => $user_id
                    ];
                }
                $stmt->execute($params);

                logAdminAction($_SESSION['admin_id'], 'edit_user', "編輯使用者: $username");
                sendJsonResponse(["success" => true, "message" => "用戶更新成功"]);
                break;

            case 'delete':
                $validation = validateInput($_POST, ['user_id']);
                if ($validation) {
                    sendJsonResponse($validation, 400);
                }

                $user_id = (int)$_POST['user_id'];
                
                // 檢查是否嘗試刪除自己
                if ($user_id === (int)$_SESSION['admin_id']) {
                    sendJsonResponse(["error" => "不能刪除當前登錄的用戶"], 400);
                }
                
                // 獲取使用者名稱用於記錄
                $stmt = $conn->prepare("SELECT username FROM admin_users WHERE id = :id");
                $stmt->execute([':id' => $user_id]);
                $username = $stmt->fetchColumn();

                if (!$username) {
                    sendJsonResponse(["error" => "用戶不存在"], 404);
                }

                // 刪除使用者
                $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = :id");
                $stmt->execute([':id' => $user_id]);

                logAdminAction($_SESSION['admin_id'], 'delete_user', "刪除使用者: $username");
                sendJsonResponse(["success" => true, "message" => "用戶刪除成功"]);
                break;

            default:
                sendJsonResponse(["error" => "無效的操作"], 400);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(["error" => "數據庫操作失敗"], 500);
    } catch (Exception $e) {
        error_log("General error: " . $e->getMessage());
        sendJsonResponse(["error" => "操作失敗"], 500);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        $validation = validateInput($_GET, ['user_id']);
        if ($validation) {
            sendJsonResponse($validation, 400);
        }

        $user_id = (int)$_GET['user_id'];
        $stmt = $conn->prepare("SELECT id, username, role_id, status FROM admin_users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            sendJsonResponse(["error" => "用戶不存在"], 404);
        }

        sendJsonResponse($user);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(["error" => "數據庫操作失敗"], 500);
    }
} else {
    sendJsonResponse(["error" => "不支持的請求方法"], 405);
}