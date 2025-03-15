<?php
require_once 'databaseConnection.php';

class AuthMiddleware {
    private $apiKey;
    private $requestLimit;
    private $timeWindow;
    private $requestCount;

    public function __construct() {
        $this->apiKey = $_ENV['API_KEY'] ?? 'k3345678';
        $this->requestLimit = 100; // 每個時間窗口的最大請求數
        $this->timeWindow = 3600; // 時間窗口（秒）
        $this->requestCount = [];
    }

    public function authenticate() {
        // 驗證 API 密鑰
        $providedApiKey = $_GET['X_API_KEY'] ?? $_POST['X_API_KEY'] ?? $_SERVER['X_API_KEY'] ?? '';
        if ($providedApiKey !== $this->apiKey) {
            http_response_code(401);
            echo json_encode(['error' => '未授權的訪問'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 檢查請求速率
        $clientIP = $_SERVER['REMOTE_ADDR'];
        $currentTime = time();
        
        // 清理過期的請求記錄
        $this->requestCount = array_filter($this->requestCount, function($timestamp) use ($currentTime) {
            return $timestamp > ($currentTime - $this->timeWindow);
        });

        // 檢查請求數量
        if (isset($this->requestCount[$clientIP]) && count($this->requestCount[$clientIP]) >= $this->requestLimit) {
            http_response_code(429);
            echo json_encode(['error' => '請求過於頻繁，請稍後再試'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 記錄新的請求
        if (!isset($this->requestCount[$clientIP])) {
            $this->requestCount[$clientIP] = [];
        }
        $this->requestCount[$clientIP][] = $currentTime;
    }
}

// 設置 CORS 頭
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // 根據需要設置允許的域名
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 驗證請求
$auth = new AuthMiddleware();
$auth->authenticate();

try {
    $db = new DatabaseConnection();
    $pdo = $db->connect();

    $stmt = $pdo->prepare("SELECT * FROM customers");
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($customers, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => '資料庫錯誤：' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => '系統錯誤：' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}