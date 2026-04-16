<?php
class Role {
    private $conn;
    public $table_name = "roles";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT id, role_name FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
