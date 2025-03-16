<?php

class DatabaseConnection {
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;

    public function __construct() {
        $this->loadEnv();
    }

    private function loadEnv() {
        $envPath = __DIR__ . '/.env';  // 使用絕對路徑
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;  // 跳過註釋行
                }
                list($key, $value) = explode('=', $line, 2);
                $_ENV[$key] = $value;
            }
        } else {
            error_log("警告：未找到 .env 文件，將使用默認配置");
        }

        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        $this->database = $_ENV['DB_DATABASE'] ?? 'abpaytw_abpay';
        $this->username = $_ENV['DB_USERNAME'] ?? 'abpay';
        $this->password = $_ENV['DB_PASSWORD'] ?? 'Aa.730216';
    }

    public function connect() {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("連線失敗：" . $e->getMessage());
        }
    }
}