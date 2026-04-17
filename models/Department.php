<?php
class Department {
    private $conn;
    public $table_name = "departments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($company_id, $limit = null, $offset = null) {
        $query = "SELECT id, dept_name, description, created_at FROM " . $this->table_name . " WHERE company_id = :company_id ORDER BY id ASC";
        
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

    public function searchDepartments($keyword, $company_id, $limit = null, $offset = null) {
        // Tối ưu: chỉ tìm theo tên phòng ban và mô tả
        $query = "SELECT id, dept_name, description, created_at FROM " . $this->table_name . " 
                   WHERE (dept_name LIKE :keyword OR description LIKE :keyword)
                   AND company_id = :company_id
                   ORDER BY dept_name ASC";
        
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
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                   WHERE (dept_name LIKE :keyword OR description LIKE :keyword)
                   AND company_id = :company_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':keyword', '%' . $keyword . '%');
        $stmt->bindParam(':company_id', $company_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function create($data, $company_id) {
        $query = "INSERT INTO " . $this->table_name . " (dept_name, description, company_id, created_at) 
                  VALUES (:name, :desc, :company_id, NOW())";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['dept_name']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':company_id', $company_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($id, $data, $company_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET dept_name = :name, 
                      description = :desc 
                  WHERE id = :id AND company_id = :company_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['dept_name']);
        $stmt->bindParam(':desc', $data['description']);
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
}
?>
