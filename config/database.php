<?php
class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;

        // Đọc biến môi trường từ file .env (nếu có) - dùng khi chạy local
        if (file_exists(__DIR__ . '/../.env')) {
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $_ENV[trim($parts[0])] = trim($parts[1]);
                }
            }
        }

        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '5432';
        $db   = $_ENV['DB_DATABASE'] ?? 'postgres';
        $user = $_ENV['DB_USERNAME'] ?? 'postgres';
        $pass = $_ENV['DB_PASSWORD'] ?? '';

        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$db";
            $this->conn = new PDO($dsn, $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log('DB Connection Error: ' . $exception->getMessage());
            die('Lỗi kết nối CSDL. Vui lòng kiểm tra cấu hình.');
        }

        return $this->conn;
    }
}
?>
