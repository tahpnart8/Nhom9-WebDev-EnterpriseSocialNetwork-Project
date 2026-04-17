<?php
require_once __DIR__ . '/BaseModel.php';

class Role extends BaseModel {
    protected string $table_name = "roles";

    public function getAll() {
        $query = "SELECT id, role_name FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
