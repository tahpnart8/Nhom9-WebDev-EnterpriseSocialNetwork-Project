<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password_hash;
    public $role_id;
    public $department_id;
    public $full_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password_hash, role_id, department_id, full_name, is_active FROM " . $this->table_name . " WHERE username = :username LIMIT 1";

        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":username", $username);
        
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check password and if account is active
            if(password_verify($password, $row['password_hash']) && $row['is_active'] == 1) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->role_id = $row['role_id'];
                $this->department_id = $row['department_id'];
                $this->full_name = $row['full_name'];
                return true;
            }
        }
        return false;
    }

    // Danh sách cho Admin sử dụng FETCH JOIN
    public function getAllUsersWithDetails() {
        $query = "SELECT u.id, u.username, u.full_name, u.email, d.dept_name, r.role_name, u.is_active 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN departments d ON u.department_id = d.id 
                  LEFT JOIN roles r ON u.role_id = r.id
                  ORDER BY u.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
