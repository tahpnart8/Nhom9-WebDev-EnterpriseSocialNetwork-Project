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
    public $avatar_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password_hash, role_id, department_id, full_name, avatar_url, is_active FROM " . $this->table_name . " WHERE username = :username LIMIT 1";

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
                $this->avatar_url = $row['avatar_url'];
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
    public function getById($id) {
        $query = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.avatar_url, u.cover_url, u.birthdate, u.hide_birthdate, u.location, u.link_facebook, u.link_instagram, u.link_tiktok, u.is_active, d.dept_name, r.role_name, u.created_at
                  FROM " . $this->table_name . " u
                  LEFT JOIN departments d ON u.department_id = d.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  WHERE u.id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $full_name, $email, $phone, $birthdate, $hide_birthdate, $location, $link_facebook, $link_instagram, $link_tiktok, $avatar_url, $cover_url) {
        // Query cơ bản
        $query = "UPDATE " . $this->table_name . " 
                  SET full_name = :full_name, 
                      email = :email, 
                      phone = :phone, 
                      birthdate = :birthdate,
                      hide_birthdate = :hide_birthdate,
                      location = :location,
                      link_facebook = :link_facebook,
                      link_instagram = :link_instagram,
                      link_tiktok = :link_tiktok";
        
        // Chỉ update ảnh nếu có truyền vào URL mới (nhằm giữ ảnh cũ nếu user ko sửa ảnh)
        if ($avatar_url !== null) {
            $query .= ", avatar_url = :avatar_url";
        }
        if ($cover_url !== null) {
            $query .= ", cover_url = :cover_url";
        }
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':birthdate', $birthdate);
        $stmt->bindParam(':hide_birthdate', $hide_birthdate, PDO::PARAM_INT);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':link_facebook', $link_facebook);
        $stmt->bindParam(':link_instagram', $link_instagram);
        $stmt->bindParam(':link_tiktok', $link_tiktok);
        
        if ($avatar_url !== null) {
            $stmt->bindParam(':avatar_url', $avatar_url);
        }
        if ($cover_url !== null) {
            $stmt->bindParam(':cover_url', $cover_url);
        }
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>
