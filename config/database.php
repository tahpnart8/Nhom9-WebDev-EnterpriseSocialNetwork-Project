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

        $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
        $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306');
        $db   = getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? 'relioo_db');
        $user = getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'root');
        $pass = getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? '');

        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
            $this->conn = new PDO($dsn, $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            // In lỗi chi tiết để debug (Xóa dòng này sau khi chạy thành công)
            die('Lỗi kết nối CSDL: ' . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>
