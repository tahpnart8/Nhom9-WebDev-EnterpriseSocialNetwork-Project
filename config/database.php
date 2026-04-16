<?php
class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;

        // Đọc biến môi trường từ file .env (nếu có)
        $env = [];
        if (file_exists(__DIR__ . '/../.env')) {
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $env[trim($parts[0])] = trim($parts[1]);
                }
            }
        }

        $host = $env['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
        $port = $env['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
        $db   = $env['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'relioo_db';
        $user = $env['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
        $pass = $env['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
            $this->conn = new PDO($dsn, $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // Thiết lập múi giờ cho MySQL
            $this->conn->exec("SET time_zone = '+07:00';");
        } catch(PDOException $exception) {
            die("Lỗi kết nối CSDL! <br> Host đang thử: <b>$host</b> <br> Database: <b>$db</b> <br> Lỗi: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>
