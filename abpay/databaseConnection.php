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
        $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            list($key, $value) = explode('=', $line);
            $_ENV[$key] = $value;
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
