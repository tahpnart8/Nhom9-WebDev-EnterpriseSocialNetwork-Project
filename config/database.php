<?php
/**
 * Database Connection - Singleton Pattern
 * Đảm bảo chỉ có DUY NHẤT 1 kết nối DB trong toàn bộ vòng đời request.
 */
class Database {
    private static ?Database $instance = null;
    private ?PDO $conn = null;

    /**
     * Constructor cho phép tạo instance mới (backward compatibility).
     * Nhưng khuyến khích dùng Database::getInstance() để tái sử dụng kết nối.
     */
    public function __construct() {
        // Nếu đã có kết nối từ Singleton, tái sử dụng luôn
        if (self::$instance !== null && self::$instance->conn !== null) {
            $this->conn = self::$instance->conn;
        }
    }

    /**
     * Lấy Singleton instance duy nhất.
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Lấy kết nối PDO (tạo mới nếu chưa có, tái sử dụng nếu đã có).
     */
    public function getConnection(): PDO {
        if ($this->conn !== null) {
            return $this->conn;
        }

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

        // Lưu vào Singleton để các instance khác tái sử dụng
        if (self::$instance === null) {
            self::$instance = $this;
        } else {
            self::$instance->conn = $this->conn;
        }

        return $this->conn;
    }
}
?>
