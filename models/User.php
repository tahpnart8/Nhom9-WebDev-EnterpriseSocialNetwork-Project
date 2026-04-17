<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    protected string $table_name = "users";

    /**
     * Xác thực đăng nhập. Trả về mảng thông tin user nếu thành công, false nếu thất bại.
     * @return array|false
     */
    public function login($username, $password) {
        $query = "SELECT id, username, password_hash, role_id, company_id, department_id, full_name, avatar_url, is_active FROM " . $this->table_name . " WHERE username = :username LIMIT 1";

        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":username", $username);
        
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check password and if account is active
            if(password_verify($password, $row['password_hash']) && $row['is_active'] == 1) {
                return [
                    'id' => $row['id'],
                    'username' => $row['username'],
                    'role_id' => $row['role_id'],
                    'company_id' => $row['company_id'],
                    'department_id' => $row['department_id'],
                    'full_name' => $row['full_name'],
                    'avatar_url' => $row['avatar_url']
                ];
            }
        }
        return false;
    }

    // Danh sách cho Admin sử dụng FETCH JOIN kèm Phân trang
    public function getAllUsersWithDetails($company_id, $limit = null, $offset = null) {
        $query = "SELECT u.id, u.username, u.full_name, u.email, u.avatar_url, u.department_id, u.role_id, d.dept_name, r.role_name, u.is_active 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN departments d ON u.department_id = d.id 
                  LEFT JOIN roles r ON u.role_id = r.id
                  WHERE u.company_id = :company_id
                  ORDER BY u.id ASC";
        
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id);
        
        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt;
    }

    public function getTotalCount($company_id) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getById($id, $company_id = null) {
        $query = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.avatar_url, u.cover_url, u.birthdate, u.hide_birthdate, u.location, u.link_facebook, u.link_instagram, u.link_tiktok, u.is_active, d.dept_name, r.role_name, u.created_at
                  FROM " . $this->table_name . " u
                  LEFT JOIN departments d ON u.department_id = d.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  WHERE u.id = :id";
        if ($company_id) {
            $query .= " AND u.company_id = :company_id";
        }
        $query .= " LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($company_id) {
            $stmt->bindParam(':company_id', $company_id);
        }
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
        // Profile update typically doesn't need company_id check if ID is unique, 
        // but for multi-tenant safety we should ensure the user belongs to the company.
        // However, usually we update the 'current user' Profile.
        // I'll keep it simple for now as it's usually own-profile.
        
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

    public function create($data, $company_id) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, full_name, email, department_id, role_id, is_active, password_hash, company_id) 
                  VALUES (:username, :full_name, :email, :department_id, :role_id, :is_active, :password_hash, :company_id)";
        
        $stmt = $this->conn->prepare($query);
        
        // Mật khẩu mặc định 123456
        $password_hash = password_hash('123456', PASSWORD_DEFAULT);
        
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':department_id', $data['department_id']);
        $stmt->bindParam(':role_id', $data['role_id']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':company_id', $company_id);
        
        return $stmt->execute();
    }

    public function search($keyword, $company_id) {
        $query = "SELECT u.id, u.full_name, u.avatar_url, d.dept_name, r.role_name, u.username
                  FROM " . $this->table_name . " u
                  LEFT JOIN departments d ON u.department_id = d.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  WHERE (u.full_name LIKE :keyword OR u.username LIKE :keyword)
                  AND u.company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':keyword', '%' . $keyword . '%');
        $stmt->bindParam(':company_id', $company_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data, $company_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET full_name = :full_name, 
                      email = :email, 
                      department_id = :department_id, 
                      role_id = :role_id, 
                      is_active = :is_active 
                  WHERE id = :id AND company_id = :company_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':department_id', $data['department_id']);
        $stmt->bindParam(':role_id', $data['role_id']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':company_id', $company_id);
        
        return $stmt->execute();
    }

    public function delete($id, $company_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':company_id', $company_id);
        return $stmt->execute();
    }

    public function searchUsers($keyword, $company_id, $limit = null, $offset = null) {
        // Tối ưu: chỉ tìm theo tên, tài khoản, phòng ban - không tìm email
        $query = "SELECT u.id, u.username, u.full_name, u.email, u.avatar_url, u.department_id, u.role_id, d.dept_name, r.role_name, u.is_active 
                   FROM " . $this->table_name . " u 
                   LEFT JOIN departments d ON u.department_id = d.id 
                   LEFT JOIN roles r ON u.role_id = r.id
                   WHERE (u.full_name LIKE :keyword OR u.username LIKE :keyword OR d.dept_name LIKE :keyword)
                   AND u.company_id = :company_id
                   ORDER BY u.full_name ASC";
        
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':keyword', '%' . $keyword . '%');
        $stmt->bindParam(':company_id', $company_id);
        
        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function getSearchCount($keyword, $company_id) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " u 
                   LEFT JOIN departments d ON u.department_id = d.id 
                   WHERE (u.full_name LIKE :keyword OR u.username LIKE :keyword OR d.dept_name LIKE :keyword)
                   AND u.company_id = :company_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':keyword', '%' . $keyword . '%');
        $stmt->bindParam(':company_id', $company_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getCountByDepartment($deptId, $company_id) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE department_id = :dept_id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $deptId);
        $stmt->bindParam(':company_id', $company_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
?>
