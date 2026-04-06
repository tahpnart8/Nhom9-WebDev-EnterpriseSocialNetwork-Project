<?php
class Database {
    // Để tích hợp Tailscale sau này, bạn có thể thay '127.0.0.1' bằng IP Tailscale tĩnh của Máy Host (vd: '100.x.x.x')
    private $host = "127.0.0.1";
    private $db_name = "relioo_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Default fetch mode as associative array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Lỗi kết nối CSDL: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
