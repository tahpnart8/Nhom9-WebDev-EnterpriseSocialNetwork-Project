<?php
class Department {
    private $conn;
    public $table_name = "departments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($limit = null, $offset = null) {
        $query = "SELECT id, dept_name, description, created_at FROM " . $this->table_name . " ORDER BY id ASC";
        
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);

        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt;
    }

    public function getTotalCount() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (dept_name, description, created_at) 
                  VALUES (:name, :desc, NOW())";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['dept_name']);
        $stmt->bindParam(':desc', $data['description']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET dept_name = :name, 
                      description = :desc 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['dept_name']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
